<?php
//
// Description
// ===========
// This method will return all the information about an home page link.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:         The ID of the tenant the home page link is attached to.
// hplink_id:          The ID of the home page link to get the details for.
//
// Returns
// -------
//
function ciniki_web_hplinkGet($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'hplink_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Home Page Link'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Make sure this module is activated, and
    // check permission to run this function for this tenant
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'checkAccess');
    $rc = ciniki_web_checkAccess($ciniki, $args['tnid'], 'ciniki.web.hplinkGet');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Load tenant settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'intlSettings');
    $rc = ciniki_tenants_intlSettings($ciniki, $args['tnid']);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $intl_timezone = $rc['settings']['intl-default-timezone'];
    $intl_currency_fmt = numfmt_create($rc['settings']['intl-default-locale'], NumberFormatter::CURRENCY);
    $intl_currency = $rc['settings']['intl-default-currency'];

    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
    $date_format = ciniki_users_dateFormat($ciniki, 'php');

    //
    // Return default for new Home Page Link
    //
    if( $args['hplink_id'] == 0 ) {
        $hplink = array('id'=>0,
            'parent_id'=>'0',
            'title'=>'',
            'url'=>'',
            'sequence'=>'1',
            'image_id'=>'0',
        );
    }

    //
    // Get the details for an existing Home Page Link
    //
    else {
        $strsql = "SELECT ciniki_web_hplinks.id, "
            . "ciniki_web_hplinks.parent_id, "
            . "ciniki_web_hplinks.title, "
            . "ciniki_web_hplinks.url, "
            . "ciniki_web_hplinks.sequence, "
            . "ciniki_web_hplinks.image_id "
            . "FROM ciniki_web_hplinks "
            . "WHERE ciniki_web_hplinks.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND ciniki_web_hplinks.id = '" . ciniki_core_dbQuote($ciniki, $args['hplink_id']) . "' "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
        $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.web', array(
            array('container'=>'hplinks', 'fname'=>'id', 
                'fields'=>array('parent_id', 'title', 'url', 'sequence', 'image_id'),
                ),
            ));
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.186', 'msg'=>'Home Page Link not found', 'err'=>$rc['err']));
        }
        if( !isset($rc['hplinks'][0]) ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.187', 'msg'=>'Unable to find Home Page Link'));
        }
        $hplink = $rc['hplinks'][0];

        //
        // Get the children
        //
        $strsql = "SELECT ciniki_web_hplinks.id, "
            . "ciniki_web_hplinks.parent_id, "
            . "ciniki_web_hplinks.title, "
            . "ciniki_web_hplinks.url, "
            . "ciniki_web_hplinks.sequence, "
            . "ciniki_web_hplinks.image_id "
            . "FROM ciniki_web_hplinks "
            . "WHERE ciniki_web_hplinks.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND ciniki_web_hplinks.parent_id = '" . ciniki_core_dbQuote($ciniki, $args['hplink_id']) . "' "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
        $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.web', array(
            array('container'=>'hplinks', 'fname'=>'id', 
                'fields'=>array('id', 'parent_id', 'title', 'url', 'sequence', 'image_id'),
                ),
            ));
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.178', 'msg'=>'Home Page Link not found', 'err'=>$rc['err']));
        }
        if( isset($rc['hplinks']) ) {
            $hplink['hplinks'] = $rc['hplinks'];
        }
    }

    $rsp = array('stat'=>'ok', 'hplink'=>$hplink, 'parents'=>array());

    //
    // Get the list of parents
    //
    $strsql = "SELECT ciniki_web_hplinks.id, "
        . "ciniki_web_hplinks.parent_id, "
        . "ciniki_web_hplinks.title, "
        . "ciniki_web_hplinks.url, "
        . "ciniki_web_hplinks.sequence, "
        . "ciniki_web_hplinks.image_id "
        . "FROM ciniki_web_hplinks "
        . "WHERE ciniki_web_hplinks.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND ciniki_web_hplinks.parent_id = 0 "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.web', array(
        array('container'=>'hplinks', 'fname'=>'id', 
            'fields'=>array('id', 'parent_id', 'title', 'url', 'sequence', 'image_id'),
            ),
        ));
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.190', 'msg'=>'Home Page Link not found', 'err'=>$rc['err']));
    }
    if( isset($rc['hplinks']) ) {
        $rsp['parents'] = $rc['hplinks'];
    }

    return $rsp;
}
?>
