<?php
//
// Description
// -----------
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:         The ID of the tenant to add the collection image to.
//
// Returns
// -------
//
function ciniki_web_collectionGet($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'collection_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Collection'),
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
    $rc = ciniki_web_checkAccess($ciniki, $args['tnid'], 'ciniki.web.collectionGet'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    //
    // Load the object
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectGet');
    $rc = ciniki_core_objectGet($ciniki, $args['tnid'], 'ciniki.web.collection', $args['collection_id']);
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.131', 'msg'=>'Unable to find the collection image you requested.', 'err'=>$rc['err']));
    }
    $collection = $rc['collection'];

    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'collectionObjSettingsGet');
    $rc = ciniki_web_collectionObjSettingsGet($ciniki, $args['tnid'], $args['collection_id']);
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }
    if( isset($rc['settings']) ) {
        $collection = array_merge($collection, $rc['settings']);
    }

    return array('stat'=>'ok', 'collection'=>$collection);
}
?>
