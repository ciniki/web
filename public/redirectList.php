<?php
//
// Description
// -----------
// This method will return the list of Web Redirects for a business.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:        The ID of the business to get Web Redirect for.
//
// Returns
// -------
//
function ciniki_web_redirectList($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to business_id as owner, or sys admin.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'checkAccess');
    $rc = ciniki_web_checkAccess($ciniki, $args['business_id'], 'ciniki.web.redirectList');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the list of redirects
    //
    $strsql = "SELECT ciniki_web_redirects.id, "
        . "ciniki_web_redirects.oldurl, "
        . "ciniki_web_redirects.newurl "
        . "FROM ciniki_web_redirects "
        . "WHERE ciniki_web_redirects.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.web', array(
        array('container'=>'redirects', 'fname'=>'id', 
            'fields'=>array('id', 'oldurl', 'newurl')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['redirects']) ) {
        $redirects = $rc['redirects'];
    } else {
        $redirects = array();
    }

    return array('stat'=>'ok', 'redirects'=>$redirects);
}
?>
