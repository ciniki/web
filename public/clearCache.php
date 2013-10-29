<?php
//
// Description
// -----------
// This method will clear the web cache for a business.  All files in the cache will
// get recreated the next time they are required.  This will slow down page loads,
// and should be done sparingly, if at all.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:		The ID of the business to clear the cache for.
//
// Returns
// -------
// <rsp stat="ok" />
//
function ciniki_web_clearCache($ciniki) {
	//
	// Find all the required and optional arguments
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
	$rc = ciniki_core_prepareArgs($ciniki, 'no', array(
		'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$args = $rc['args'];
	
	//
	// Check access to business_id as owner
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'checkAccess');
	$ac = ciniki_web_checkAccess($ciniki, $args['business_id'], 'ciniki.web.clearCache');
	if( $ac['stat'] != 'ok' ) {
		return $ac;
	}

	//
	// Remove the business cache directory
	//
	$business_cache_dir = $ciniki['config']['core']['modules_dir'] . '/web/cache'
		. '/' . sprintf('%02d', ($args['business_id']%100)) . '/'
		. sprintf('%07d', $args['business_id']);
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'recursiveRmdir');
	$rc = ciniki_core_recursiveRmdir($ciniki, $business_cache_dir);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	return array('stat'=>'ok');
}
?>
