<?php
//
// Description
// -----------
// This method will return the list of Web Redirects for a tenant.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:        The ID of the tenant to get Web Redirect for.
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
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to tnid as owner, or sys admin.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'checkAccess');
    $rc = ciniki_web_checkAccess($ciniki, $args['tnid'], 'ciniki.web.redirectList');
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
        . "WHERE ciniki_web_redirects.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
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
