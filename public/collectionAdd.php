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
//
function ciniki_web_collectionAdd(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'name'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Name'), 
        'permalink'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'name'=>'Permalink'), 
        'status'=>array('required'=>'no', 'default'=>'10', 'blank'=>'yes', 'name'=>'Status'), 
        'sequence'=>array('required'=>'no', 'default'=>'0', 'blank'=>'yes', 'name'=>'Sequence'),
        'image_id'=>array('required'=>'no', 'default'=>'0', 'blank'=>'yes', 'name'=>'Image'),
        'image_caption'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'name'=>'Image Caption'),
        'synopsis'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'name'=>'Synopsis'),
        'description'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'name'=>'Description'),
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
    $rc = ciniki_web_checkAccess($ciniki, $args['business_id'], 'ciniki.web.collectionAdd'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    } 

    if( !isset($args['permalink']) || $args['permalink'] == '' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');
        $args['permalink'] = ciniki_core_makePermalink($ciniki, $args['name']);
    }

    //
    // Start transaction
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
    $rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.web');
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    //
    // Add the collection to the database
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
    $rc = ciniki_core_objectAdd($ciniki, $args['business_id'], 'ciniki.web.collection', $args, 0x07);
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'ciniki.web');
        return $rc;
    }
    $collection_id = $rc['id'];

    //
    // Update the objects
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'collectionObjSettingsUpdate');
    $rc = ciniki_web_collectionObjSettingsUpdate($ciniki, $args['business_id'], $collection_id, $ciniki['request']['args']);
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'ciniki.web');
        return $rc;
    }

    //
    // Commit the transaction
    //
    $rc = ciniki_core_dbTransactionCommit($ciniki, 'ciniki.web');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Update the last_change date in the business modules
    // Ignore the result, as we don't want to stop user updates if this fails.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'updateModuleChangeDate');
    ciniki_businesses_updateModuleChangeDate($ciniki, $args['business_id'], 'ciniki', 'web');

    return array('stat'=>'ok', 'id'=>$collection_id);
}
?>
