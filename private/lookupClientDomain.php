<?php
//
// Description
// -----------
// This function will lookup the client domain in the database, and return the business id.
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_web_lookupClientDomain($ciniki, $domain, $type) {

	//
	// FIXME: Add timezone information
	//
	date_default_timezone_set('America/Toronto');

	//
	// Strip the www from the domain before looking up
	//
	$domain = preg_replace('/^www\./', '', $domain);

	//
	// Query the database for the domain
	//
	$strsql = "SELECT business_id, flags "
		. "FROM ciniki_web_domains "
		. "WHERE domain = '" . ciniki_core_dbQuote($ciniki, $domain) . "' "
		. "";
	if( $type == 'sitename' ) {
		$strsql .= "AND (flags&0x01) = 0x01 ";
	} else {
		$strsql .= "AND (flags&0x10) = 0x10 ";
	}
	$strsql .= "AND status < 50 "
		. "";
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbHashQuery.php');
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'businesses', 'business');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	//
	// Make sure only one row was returned, otherwise there's a config error 
	// and we don't know which business the domain actually belongs to
	//
	if( !isset($rc['business']) || !isset($rc['business']['business_id']) ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'610', 'msg'=>'Configuration error'));
	}
	$business_id = $rc['business']['business_id'];

	//
	// Get the list of active modules for the business
	//
	$strsql = "SELECT ruleset "
		. ", CONCAT_WS('.', ciniki_business_modules.package, ciniki_business_modules.module) AS module_id "
		. "FROM ciniki_businesses, ciniki_business_modules "
		. "WHERE ciniki_businesses.id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
		. "AND ciniki_businesses.status = 1 "														// Business is active
		. "AND ciniki_businesses.id = ciniki_business_modules.business_id "
		. "AND ciniki_business_modules.status = 1 "														// Business is active
		. "";
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbHashIDQuery.php');
	$rc = ciniki_core_dbHashIDQuery($ciniki, $strsql, 'businesses', 'modules', 'module_id');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['modules']) || !isset($rc['modules']['ciniki.web']) ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'613', 'msg'=>'Website not activated.'));
	}
	$modules = $rc['modules'];

	return array('stat'=>'ok', 'business_id'=>$business_id, 'modules'=>$modules);
}
?>
