<?php
//
// Description
// -----------
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:         The ID of the business to update the theme content for.
//
// Returns
// -------
// <rsp stat='ok' />
//
function ciniki_web_privateThemeContentUpdate(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'content_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Content'), 
        'name'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Name'), 
        'status'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Status'),
        'sequence'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Sequence'),
        'content_type'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Type'),
        'media'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Media'),
        'content'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Content'),
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
    $rc = ciniki_web_checkAccess($ciniki, $args['business_id'], 'ciniki.web.privateThemeContentUpdate'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }

    //
    // Get the content
    //
    $strsql = "SELECT id, theme_id, uuid, content_type, sequence "
        . "FROM ciniki_web_theme_content "
        . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "AND id = '" . ciniki_core_dbQuote($ciniki, $args['content_id']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.web', 'item');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['item']) ) {
        return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1554', 'msg'=>'Content not found'));
    }
    $item = $rc['item'];

    //
    // Start the transaction
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
    // Update the theme in the database
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
    $rc = ciniki_core_objectUpdate($ciniki, $args['business_id'], 'ciniki.web.theme_content', $args['content_id'], $args);
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'ciniki.web');
        return $rc;
    }

    //
    // Update any sequences
    //
    if( isset($args['sequence']) && $item['theme_id'] > 0 ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'themeContentUpdateSequences');
        $rc = ciniki_web_themeContentUpdateSequences($ciniki, $args['business_id'], $item['theme_id'], $item['content_type'], $args['sequence'], $item['sequence']);
        if( $rc['stat'] != 'ok' ) {
            ciniki_core_dbTransactionRollback($ciniki, 'ciniki.web');
            return $rc;
        }
    }

    //
    // Update theme last_updated
    //
    $strsql = "UPDATE ciniki_web_themes SET last_updated = UTC_TIMESTAMP() "
        . "WHERE id = '" . ciniki_core_dbQuote($ciniki, $item['theme_id']) . "' "
        . "AND business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbUpdate');
    $rc = ciniki_core_dbUpdate($ciniki, $strsql, 'ciniki.web');
    if( $rc['stat'] != 'ok' ) {
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
    // Update the last_change date in the business modules
    // Ignore the result, as we don't want to stop user updates if this fails.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'updateModuleChangeDate');
    ciniki_businesses_updateModuleChangeDate($ciniki, $args['business_id'], 'ciniki', 'web');

    return array('stat'=>'ok');
}
?>
