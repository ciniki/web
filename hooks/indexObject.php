<?php
//
// Description
// -----------
// This function will add/modify/delete an object in the web index.
//
// Arguments
// ---------
// ciniki:
//
// Returns
// -------
//
function ciniki_web_hooks_indexObject($ciniki, $tnid, $args) {
    
    if( !isset($args['object']) || $args['object'] == '' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.1', 'msg'=>'No object specified'));
    }

    if( !isset($args['object_id']) || $args['object_id'] == '' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.2', 'msg'=>'No object ID specified'));
    }

    //
    // Check if web is enabled
    //
    if( !isset($ciniki['tenant']['modules']['ciniki.web']) ) {
        return array('stat'=>'ok');
    }

    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'indexUpdateObject');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'indexUpdateObjectImage');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'indexModuleBaseURL');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'indexObjectBaseURL');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectDelete');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'checkModuleFlags');

    //
    // Load INTL settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'intlSettings');
    $rc = ciniki_tenants_intlSettings($ciniki, $tnid);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    return ciniki_web_indexUpdateObject($ciniki, $tnid, $args);
}
?>
