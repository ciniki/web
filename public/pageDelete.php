<?php
//
// Description
// -----------
// This method will delete a page from the tenant.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:         The ID of the tenant the page is attached to.
// page_id:         The ID of the page to be removed.
//
// Returns
// -------
// <rsp stat="ok">
//
function ciniki_web_pageDelete(&$ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'page_id'=>array('required'=>'yes', 'default'=>'', 'blank'=>'yes', 'name'=>'Page'), 
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];
    
    //
    // Check access to tnid as owner
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'checkAccess');
    $ac = ciniki_web_checkAccess($ciniki, $args['tnid'], 'ciniki.web.pageDelete');
    if( $ac['stat'] != 'ok' ) {
        return $ac;
    }

    //
    // Get the uuid of the page to be deleted
    //
    $strsql = "SELECT uuid FROM ciniki_web_pages "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND id = '" . ciniki_core_dbQuote($ciniki, $args['page_id']) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.web', 'item');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['item']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.136', 'msg'=>'The page does not exist'));
    }
    $item = $rc['item'];

    //
    // Start transaction
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDelete');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectDelete');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
    $rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.web');
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }

    //
    // Check if there are subpages
    //
    $strsql = "SELECT COUNT(id) AS num_pages "
        . "FROM ciniki_web_pages "
        . "WHERE parent_id = '" . ciniki_core_dbQuote($ciniki, $args['page_id']) . "' "
        . "AND tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.web', 'pages');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['pages']['num_pages']) && $rc['pages']['num_pages'] > 0 ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.137', 'msg'=>'There are still children, you must remove them first.'));
    }

    //
    // Remove the images
    //
    $strsql = "SELECT id, uuid, image_id FROM ciniki_web_page_images "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND page_id = '" . ciniki_core_dbQuote($ciniki, $args['page_id']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.web', 'image');
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'ciniki.web');
        return $rc;
    }
    if( isset($rc['rows']) && count($rc['rows']) > 0 ) {
        $images = $rc['rows'];
        
        foreach($images as $iid => $image) {
            $rc = ciniki_core_objectDelete($ciniki, $args['tnid'], 'ciniki.web.page_image', 
                $image['id'], $image['uuid'], 0x04);
            if( $rc['stat'] != 'ok' ) {
                ciniki_core_dbTransactionRollback($ciniki, 'ciniki.web');
                return $rc; 
            }
        }
    }

    //
    // Remove the files for the page
    //
    $strsql = "SELECT id, uuid "
        . "FROM ciniki_web_page_files "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND page_id = '" . ciniki_core_dbQuote($ciniki, $args['page_id']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.web', 'file');
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'ciniki.web');
        return $rc;
    }
    if( isset($rc['rows']) && count($rc['rows']) > 0 ) {
        $files = $rc['rows'];
        foreach($files as $fid => $file) {
            $rc = ciniki_core_objectDelete($ciniki, $args['tnid'], 'ciniki.web.page_file', 
                $file['id'], $file['uuid'], 0x04);
            if( $rc['stat'] != 'ok' ) {
                ciniki_core_dbTransactionRollback($ciniki, 'ciniki.web');
                return $rc; 
            }
        }
    }

    //
    // Remove the page
    //
    $rc = ciniki_core_objectDelete($ciniki, $args['tnid'], 'ciniki.web.page', 
        $args['page_id'], $item['uuid'], 0x04);
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
    // Update the last_change date in the tenant modules
    // Ignore the result, as we don't want to stop user updates if this fails.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'updateModuleChangeDate');
    ciniki_tenants_updateModuleChangeDate($ciniki, $args['tnid'], 'ciniki', 'web');

    return array('stat'=>'ok');
}
?>
