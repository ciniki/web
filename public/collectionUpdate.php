<?php
//
// Description
// -----------
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:         The ID of the business to add the image to.
// name:                The name of the collection.  
//
// Returns
// -------
// <rsp stat='ok' />
//
function ciniki_web_collectionUpdate(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'collection_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Collection'), 
        'name'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Name'), 
        'permalink'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Permalink'), 
        'status'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Status'), 
        'sequence'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Sequence'),
        'image_id'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Image'),
        'image_caption'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Image Caption'),
        'synopsis'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Synopsis'),
        'description'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Description'),
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
    $rc = ciniki_web_checkAccess($ciniki, $args['business_id'], 'ciniki.web.collectionUpdate'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }

    if( isset($args['name']) && (!isset($args['permalink']) || $args['permalink'] == '') ) {
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
    // Update the collection in the database
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
    $rc = ciniki_core_objectUpdate($ciniki, $args['business_id'], 'ciniki.web.collection', $args['collection_id'], $args);
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'ciniki.web');
        return $rc;
    }

    //
    // Update the objects
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'collectionObjSettingsUpdate');
    $rc = ciniki_web_collectionObjSettingsUpdate($ciniki, $args['business_id'], $args['collection_id'], $ciniki['request']['args']);
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

    return array('stat'=>'ok');
}
?>
