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
// <rsp stat='ok' id='34' />
//
function ciniki_web_pageAdd(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'parent_id'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'0', 'name'=>'Parent'), 
        'title'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Title'), 
        'permalink'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'name'=>'Permalink'), 
		'category'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'name'=>'Category'),
		'sequence'=>array('required'=>'no', 'default'=>'0', 'blank'=>'yes', 'name'=>'Sequence'),
		'flags'=>array('required'=>'no', 'default'=>'1', 'blank'=>'yes', 'name'=>'Options'),
		'primary_image_id'=>array('required'=>'yes', 'blank'=>'yes', 'name'=>'Image'),
		'primary_image_caption'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'name'=>'Image Caption'),
		'primary_image_url'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'name'=>'Image URL'),
		'child_title'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'name'=>'Children Title'),
		'synopsis'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'name'=>'Synopsis'),
        'content'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'name'=>'Content'), 
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
    $rc = ciniki_web_checkAccess($ciniki, $args['business_id'], 'ciniki.web.pageAdd'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    } 

	//
	// Get a UUID for use in permalink
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbUUID');
	$rc = ciniki_core_dbUUID($ciniki, 'ciniki.web');
	if( $rc['stat'] != 'ok' ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'2213', 'msg'=>'Unable to get a new UUID', 'err'=>$rc['err']));
	}
	$args['uuid'] = $rc['uuid'];

	//
	// Determine the permalink
	//
	if( !isset($args['permalink']) || $args['permalink'] == '' ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');
		$args['permalink'] = ciniki_core_makePermalink($ciniki, $args['title']);
	}

	//
	// Check the permalink doesn't already exist
	//
	$strsql = "SELECT id, title, permalink FROM ciniki_web_pages "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND parent_id = '" . ciniki_core_dbQuote($ciniki, $args['parent_id']) . "' "
		. "AND permalink = '" . ciniki_core_dbQuote($ciniki, $args['permalink']) . "' "
		. "";
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.web', 'page');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( $rc['num_rows'] > 0 ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'2214', 'msg'=>'You already have page with this name, you must choose another.'));
	}

	//
	// Check the sequence
	//
	if( !isset($args['sequence']) || $args['sequence'] == '' || $args['sequence'] == '0' ) {
		$strsql = "SELECT MAX(sequence) AS max_sequence "
			. "FROM ciniki_web_page_images "
			. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "AND page_id = '" . ciniki_core_dbQuote($ciniki, $args['page_id']) . "' "
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

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
	$rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.web');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	//
	// Add the image to the database
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
	$rc = ciniki_core_objectAdd($ciniki, $args['business_id'], 'ciniki.web.page', $args, 0x07);
	if( $rc['stat'] != 'ok' ) {
		ciniki_core_dbTransactionRollback($ciniki, 'ciniki.web');
		return $rc;
	}
	$page_id = $rc['id'];

	//
	// Update any sequences
	//
	if( isset($args['sequence']) ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'pageUpdateSequences');
		$rc = ciniki_web_pageUpdateSequences($ciniki, $args['business_id'], 
			$args['parent_id'], $args['sequence'], -1);
		if( $rc['stat'] != 'ok' ) {
			ciniki_core_dbTransactionRollback($ciniki, 'ciniki.web');
			return $rc;
		}
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

	return array('stat'=>'ok', 'id'=>$page_id);
}
?>
