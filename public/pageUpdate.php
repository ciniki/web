<?php
//
// Description
// -----------
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:			The ID of the business to update the page for.
//
// Returns
// -------
// <rsp stat='ok' />
//
function ciniki_web_pageUpdate(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'page_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Page ID'), 
        'parent_id'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Parent'), 
        'title'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Title'), 
        'permalink'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Permalink'), 
		'category'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Category'),
		'sequence'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Sequence'),
		'page_type'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Type'),
		'page_redirect_url'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Redirect'),
		'page_module'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Module'),
		'flags'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Options'),
		'primary_image_id'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Image'),
		'primary_image_caption'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Image Caption'),
		'primary_image_url'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Image URL'),
		'child_title'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Children Title'),
		'synopsis'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Synopsis'),
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
    $rc = ciniki_web_checkAccess($ciniki, $args['business_id'], 'ciniki.web.pageUpdate'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }

	//
	// Get the existing page details 
	//
	$strsql = "SELECT id, parent_id, uuid, sequence "
		. "FROM ciniki_web_pages "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND id = '" . ciniki_core_dbQuote($ciniki, $args['page_id']) . "' "
		. "";
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.web', 'item');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['item']) ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'2202', 'msg'=>'Page not found'));
	}
	$item = $rc['item'];
	$old_sequence = $rc['item']['sequence'];
	$parent_id = $rc['item']['parent_id'];

	if( isset($args['title']) ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');
		$args['permalink'] = ciniki_core_makePermalink($ciniki, $args['title']);
		//
		// Make sure the permalink is unique
		//
		$strsql = "SELECT id, title, permalink "
			. "FROM ciniki_web_pages "
			. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "AND parent_id = '" . ciniki_core_dbQuote($ciniki, $item['parent_id']) . "' "
			. "AND permalink = '" . ciniki_core_dbQuote($ciniki, $args['permalink']) . "' "
			. "AND id <> '" . ciniki_core_dbQuote($ciniki, $args['page_id']) . "' "
			. "";
		$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.web', 'image');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( $rc['num_rows'] > 0 ) {
			return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'2203', 'msg'=>'You already have page with this title, please choose another title.'));
		}
	}

	//
	// Grab the old sequence
	//
/*	if( isset($args['sequence']) ) {
		$strsql = "SELECT id, parent_id, sequence "
			. "FROM ciniki_web_pages "
			. "WHERE id = '" . ciniki_core_dbQuote($ciniki, $args['page_id']) . "' "
			. "AND business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "";
		$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.web', 'item');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( !isset($rc['item']) ) {
			return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'2204', 'msg'=>'Unable to find page'));
		}
	}
*/

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
	$rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.web');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	//
	// Update the page in the database
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
	$rc = ciniki_core_objectUpdate($ciniki, $args['business_id'], 'ciniki.web.page', $args['page_id'], $args);
	if( $rc['stat'] != 'ok' ) {
		ciniki_core_dbTransactionRollback($ciniki, 'ciniki.web');
		return $rc;
	}

	//
	// Update any sequences
	//
	if( isset($args['sequence']) && $parent_id > 0 ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'pageUpdateSequences');
		$rc = ciniki_web_pageUpdateSequences($ciniki, $args['business_id'], 
			$parent_id, $args['sequence'], $old_sequence);
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

	return array('stat'=>'ok');
}
?>
