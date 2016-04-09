<?php
//
// Description
// -----------
// This method will update the web index for a business.
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
function ciniki_web_indexUpdateNow($ciniki) {
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
	$rc = ciniki_web_checkAccess($ciniki, $args['business_id'], 'ciniki.web.indexUpdateNow');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'indexUpdate');
    return ciniki_web_indexUpdate($ciniki, $args['business_id']);
}
?>
