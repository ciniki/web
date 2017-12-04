<?php
//
// Description
// ===========
//
// Arguments
// ---------
// 
// Returns
// -------
// <rsp stat='ok' />
//
function ciniki_web_collectionDelete(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'collection_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Slider'),
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
    $rc = ciniki_web_checkAccess($ciniki, $args['tnid'], 'ciniki.web.collectionDelete'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
    $rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.web');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the objrefs
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectGetSubs');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectDelete');
    $rc = ciniki_core_objectGetSubs($ciniki, $args['tnid'], 'ciniki.web.collection', $args['collection_id'], 'ciniki.web.collection_objref');
    if( $rc['stat'] != 'ok' ) {
        $rc = ciniki_core_dbTransactionRollback($ciniki, 'ciniki.web');
        return $rc;
    }
    if( isset($rc['refs']) ) {
        $refs = $rc['refs'];
        foreach($refs as $rid => $ref) {
            $rc = ciniki_core_objectDelete($ciniki, $args['tnid'], 'ciniki.web.collection_objref', $ref['ref']['id'], NULL, 0x04);       
        }
    }
    
    //
    // Delete the object
    //
    $rc = ciniki_core_objectDelete($ciniki, $args['tnid'], 'ciniki.web.collection', $args['collection_id'], NULL, 0x04);
    if( $rc['stat'] != 'ok' ) {
        $rc = ciniki_core_dbTransactionRollback($ciniki, 'ciniki.web');
        return $rc;
    }

    //
    // Commit the changes to the database
    //
    $rc = ciniki_core_dbTransactionCommit($ciniki, 'ciniki.web');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Update the last_change date in the tenant modules
    // Ignore the result, as we don't want to stop user updates if this fails.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'updateModuleChangeDate');
    ciniki_tenants_updateModuleChangeDate($ciniki, $args['tnid'], 'ciniki', 'web');

    return array('stat'=>'ok'); 
}
?>
