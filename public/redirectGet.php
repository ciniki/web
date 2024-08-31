<?php
//
// Description
// ===========
// This method will return all the information about an web redirect.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:         The ID of the tenant the web redirect is attached to.
// redirect_id:          The ID of the web redirect to get the details for.
//
// Returns
// -------
//
function ciniki_web_redirectGet($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'redirect_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Web Redirect'),
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
    $rc = ciniki_web_checkAccess($ciniki, $args['tnid'], 'ciniki.web.redirectGet');
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

    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'datetimeFormat');
    $datetime_format = ciniki_users_datetimeFormat($ciniki, 'php');

    //
    // Return default for new Web Redirect
    //
    if( $args['redirect_id'] == 0 ) {
        $redirect = array('id'=>0,
            'oldurl'=>'',
            'newurl'=>'',
        );
    }

    //
    // Get the details for an existing Web Redirect
    //
    else {
        $strsql = "SELECT ciniki_web_redirects.id, "
            . "ciniki_web_redirects.oldurl, "
            . "ciniki_web_redirects.newurl "
            . "FROM ciniki_web_redirects "
            . "WHERE ciniki_web_redirects.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND ciniki_web_redirects.id = '" . ciniki_core_dbQuote($ciniki, $args['redirect_id']) . "' "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.web', 'redirect');
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.167', 'msg'=>'Web Redirect not found', 'err'=>$rc['err']));
        }
        if( !isset($rc['redirect']) ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.168', 'msg'=>'Unable to find Web Redirect'));
        }
        $redirect = $rc['redirect'];
    }

    return array('stat'=>'ok', 'redirect'=>$redirect);
}
?>
