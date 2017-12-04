<?php
//
// Description
// -----------
// This method will return the list of page and their titles for use in the interface.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:     The ID of the tenant to get testimonials for.
//
// Returns
// -------
//
function ciniki_web_pageList($ciniki) {
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
    $rc = ciniki_web_checkAccess($ciniki, $args['tnid'], 'ciniki.web.pageList');
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }
    $modules = $rc['modules'];

    //
    // Get the list of titles from the database
    //
    $strsql = "SELECT id, title "
        . "FROM ciniki_web_pages "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND parent_id = 0 "
        . "ORDER BY sequence, title "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
    $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.web', array(
        array('container'=>'pages', 'fname'=>'id', 'name'=>'page',
            'fields'=>array('id', 'title')),
        ));
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }
    if( isset($rc['pages']) ) {
        $pages = $rc['pages'];
    } else {
        $pages = array();
    }
    
    return array('stat'=>'ok', 'pages'=>$pages);
}
?>
