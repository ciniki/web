<?php
//
// Description
// ===========
// This method will add a new theme to the business. Content and images need to be 
// added after the theme has been created.
//
// Arguments
// ---------
// 
// Returns
// -------
// <rsp stat='ok' id='34' />
//
function ciniki_web_privateThemeContentAdd(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'theme_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Theme'), 
        'name'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Name'), 
        'status'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Status'),
        'sequence'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'0', 'name'=>'Sequence'),
        'content_type'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Type'),
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
    $rc = ciniki_web_checkAccess($ciniki, $args['business_id'], 'ciniki.web.privateThemeContentAdd'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    } 

    //
    // Check for an updated name
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');
    $args['permalink'] = ciniki_core_makePermalink($ciniki, $args['name']);

    //
    // Check the sequence
    //
    if( !isset($args['sequence']) || $args['sequence'] == '' || $args['sequence'] == '0' ) {
        $strsql = "SELECT MAX(sequence) AS max_sequence "
            . "FROM ciniki_web_theme_content "
            . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
            . "AND theme_id = '" . ciniki_core_dbQuote($ciniki, $args['theme_id']) . "' "
            . "AND content_type = '" . ciniki_core_dbQuote($ciniki, $args['content_type']) . "' "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.web', 'seq');
        if( $rc['stat'] != 'ok' ) { 
            return $rc;
        }
        if( isset($rc['seq']) && isset($rc['seq']['max_sequence']) ) {
            $args['sequence'] = $rc['seq']['max_sequence'] + 1;
        } else {
            $args['sequence'] = 1;
        }
    }

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
    // Add the theme to the database
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
    $rc = ciniki_core_objectAdd($ciniki, $args['business_id'], 'ciniki.web.theme_content', $args, 0x04);
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'ciniki.web');
        return $rc;
    }
    $content_id = $rc['id'];

    //
    // Update any sequences
    //
    if( isset($args['sequence']) ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'themeContentUpdateSequences');
        $rc = ciniki_web_themeContentUpdateSequences($ciniki, $args['business_id'], $args['theme_id'], $args['content_type'], $args['sequence'], -1);
        if( $rc['stat'] != 'ok' ) {
            ciniki_core_dbTransactionRollback($ciniki, 'ciniki.web');
            return $rc;
        }
    }

    //
    // Update theme last_updated
    //
    $strsql = "UPDATE ciniki_web_themes SET last_updated = UTC_TIMESTAMP() "
        . "WHERE id = '" . ciniki_core_dbQuote($ciniki, $args['theme_id']) . "' "
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

    return array('stat'=>'ok', 'id'=>$content_id);
}
?>
