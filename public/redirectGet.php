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
// business_id:         The ID of the business the web redirect is attached to.
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
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'),
        'redirect_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Web Redirect'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Make sure this module is activated, and
    // check permission to run this function for this business
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'checkAccess');
    $rc = ciniki_web_checkAccess($ciniki, $args['business_id'], 'ciniki.web.redirectGet');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Load business settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'intlSettings');
    $rc = ciniki_businesses_intlSettings($ciniki, $args['business_id']);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $intl_timezone = $rc['settings']['intl-default-timezone'];
    $intl_currency_fmt = numfmt_create($rc['settings']['intl-default-locale'], NumberFormatter::CURRENCY);
    $intl_currency = $rc['settings']['intl-default-currency'];

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
            . "WHERE ciniki_web_redirects.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
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
