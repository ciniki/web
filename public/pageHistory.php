<?php
//
// Description
// -----------
// This function will return the history for an element in the web images.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:			The ID of the business to get the history for.
// page_image_id:	The ID of the page image to get the history for.
// field:				The field to get the history for.
//
// Returns
// -------
//	<history>
//		<action date="2011/02/03 00:03:00" value="Value field set to" user_id="1" />
//		...
//	</history>
//	<users>
//		<user id="1" name="users.display_name" />
//		...
//	</users>
//
function ciniki_web_pageHistory($ciniki) {
	//
	// Find all the required and optional arguments
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
	$rc = ciniki_core_prepareArgs($ciniki, 'no', array(
		'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
		'page_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Page'), 
		'field'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Field'), 
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$args = $rc['args'];
	
	//
	// Check access to business_id as owner, or sys admin
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'checkAccess');
	$rc = ciniki_web_checkAccess($ciniki, $args['business_id'], 'ciniki.web.pageHistory');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbGetModuleHistory');
	return ciniki_core_dbGetModuleHistory($ciniki, 'ciniki.web', 'ciniki_web_history', 
		$args['business_id'], 'ciniki_web_pages', $args['page_id'], $args['field']);
}
?>
