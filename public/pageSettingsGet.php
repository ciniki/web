<?php
//
// Description
// -----------
// This method will return the list of settings for a specific page.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:     The ID of the tenant to get the page settings for.
// page:            The page to get the settings for.  It can be one of the
//                  following values:
//
//                  - home
//                  - about
//                  - contact
//                  - gallery
//                  - events
//                  - links
//
// content:         (optional) Should the content for the page be returned as well.  (yes or no)
//
// Returns
// -------
// <rsp stat="ok">
//      <settings page-about-active="yes" page-about-image="27">
//          <page-about-content>The about page content</page-about-content>
//      </settings>
// </rsp>
//
function ciniki_web_pageSettingsGet($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'page'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Page'),
        'content'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Content'),
        'sponsors'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Sponsors'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to tnid as owner, and load module list
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'checkAccess');
    $ac = ciniki_web_checkAccess($ciniki, $args['tnid'], 'ciniki.web.pageSettingsGet');
    if( $ac['stat'] != 'ok' ) {
        return $ac;
    }

    //
    // Get the settings from the database
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash');
    $rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_web_settings', 'tnid', $args['tnid'], 'ciniki.web', 'settings', 'page-' . $args['page']);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['settings']) ) {
        $settings = array();
    } else {
        $settings = $rc['settings'];
    }

    //
    // If requested, also get the page content
    //
    if( isset($args['content']) && $args['content'] == 'yes' ) {
        $rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_web_content', 'tnid', $args['tnid'], 'ciniki.web', 'content', 'page-' . $args['page']);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['content']) ) {
            $settings = array_merge($settings, $rc['content']);
        }
    }

    $rsp = array('stat'=>'ok', 'settings'=>$settings);

    //
    // Get the tenant address if page is contact
    //
    if( isset($args['page']) && $args['page'] == 'contact' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQuery');
        $rc = ciniki_core_dbDetailsQuery($ciniki, 'ciniki_tenant_details', 'tnid', $args['tnid'], 'ciniki.tenants', 'settings', 'contact');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( !isset($rc['settings']) ) {
            $settings = array();
        } else {
            $settings = $rc['settings'];
        }
        $address = '';
        $address .= ((isset($settings['contact.address.street1'])&&$settings['contact.address.street1']!='')?($address!=''?', ':'').$settings['contact.address.street1']:'');
        $address .= ((isset($settings['contact.address.street2'])&&$settings['contact.address.street2']!='')?($address!=''?', ':'').$settings['contact.address.street2']:'');
        $address .= ((isset($settings['contact.address.city'])&&$settings['contact.address.city']!='')?($address!=''?', ':'').$settings['contact.address.city']:'');
        $address .= ((isset($settings['contact.address.province'])&&$settings['contact.address.province']!='')?($address!=''?', ':'').$settings['contact.address.province']:'');
        $address .= ((isset($settings['contact.address.postal'])&&$settings['contact.address.postal']!='')?($address!=''?', ':'').$settings['contact.address.postal']:'');
        $address .= ((isset($settings['contact.address.country'])&&$settings['contact.address.country']!='')?($address!=''?', ':'').$settings['contact.address.country']:'');
        $rsp['tenant_address'] = $address;
    }

    //
    // Check if sliders should be included
    //
    $slider_pages = array('home');
    if( in_array($args['page'], $slider_pages) ) {
        $strsql = "SELECT id, name "
            . "FROM ciniki_web_sliders "
            . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "ORDER BY name "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
        $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.web', array(
            array('container'=>'sliders', 'fname'=>'id', 'name'=>'slider',
                'fields'=>array('id', 'name')),
                ));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['sliders']) ) {
            $rsp['sliders'] = $rc['sliders'];
        }
    }

    //
    // Check if home page links should be included
    //
    if( $args['page'] == 'home' ) {
        $strsql = "SELECT id, title "
            . "FROM ciniki_web_hplinks "
            . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND parent_id = 0 "
            . "ORDER BY sequence, title "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
        $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.web', array(
            array('container'=>'hplinks', 'fname'=>'id', 'fields'=>array('id', 'title')),
            ));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['hplinks']) ) {
            $rsp['hplinks'] = $rc['hplinks'];
        }
    }

    //
    // Check if sponsors should be included
    //
    if( isset($args['sponsors']) && $args['sponsors'] == 'yes'
        && isset($ciniki['tenant']['modules']['ciniki.sponsors']) 
        && ($ciniki['tenant']['modules']['ciniki.sponsors']['flags']&0x02) == 0x02
        ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'sponsors', 'hooks', 'sponsorList');
        $rc = ciniki_sponsors_hooks_sponsorList($ciniki, $args['tnid'], 
            array('object'=>'ciniki.web.page', 'object_id'=>$args['page']));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['sponsors']) ) {
            $rsp['sponsors'] = $rc['sponsors'];
        }
    }

    return $rsp;
}
?>
