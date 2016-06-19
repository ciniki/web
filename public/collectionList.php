<?php
//
// Description
// -----------
// This method will return the list of website collections for a business.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:     The ID of the business to get collection list for.
//
// Returns
// -------
//
function ciniki_web_collectionList($ciniki) {
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
    $ac = ciniki_web_checkAccess($ciniki, $args['business_id'], 'ciniki.web.collectionList');
    if( $ac['stat'] != 'ok' ) { 
        return $ac;
    }   

    //
    // Query for the collections
    //
    $strsql = "SELECT id, name, permalink, status, sequence, image_id "
        . "FROM ciniki_web_collections "
        . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "ORDER BY sequence, name "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
    $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.web', array(
        array('container'=>'collections', 'fname'=>'id', 'name'=>'collection',
            'fields'=>array('id', 'name', 'permalink', 'status', 'sequence', 'image_id')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    return $rc;
}
?>
