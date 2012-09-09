<?php
//
// Description
// -----------
// This function will lookup the client domain or sitename in the database, and return the business id.
// The request for the business website can be in the form of businessdomain.com or ciniki.com/businesssitename.
//
// Arguments
// ---------
// ciniki:
// domain:		The domain or sitename to lookup.
// type:		The type of lookup: domain or sitename.
//
// Returns
// -------
//
function ciniki_web_lookupClientDomain($ciniki, $domain, $type) {

	//
	// Strip the www from the domain before looking up
	//
	$domain = preg_replace('/^www\./', '', $domain);

	//
	// Query the database for the domain
	//
	if( $type == 'sitename' ) {
		$strsql = "SELECT id AS business_id, 'no' AS isprimary "
			. "FROM ciniki_businesses "
			. "WHERE sitename = '" . ciniki_core_dbQuote($ciniki, $domain) . "' "
			. "AND status = 1 "
			. "";
	} else {
		$strsql = "SELECT business_id, "
			. "IF((flags&0x01)=0x01, 'yes', 'no') AS isprimary "
			. "FROM ciniki_business_domains "
			. "WHERE domain = '" . ciniki_core_dbQuote($ciniki, $domain) . "' "
			. "AND status < 50 "
			. "";
	}
	require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/core/private/dbHashQuery.php');
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.businesses', 'business');
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
	// Get primary domain if not primary
	//
	$redirect = '';
	if( $rc['business']['isprimary'] == 'no' ) {
		$strsql = "SELECT domain "
			. "FROM ciniki_business_domains "
			. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
			. "AND (flags&0x01) = 0x01 "
			. "AND status < 50 "
			. "";
		$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.businesses', 'business');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( isset($rc['business']) && $rc['business']['domain'] != '' ) {
			$redirect = $rc['business']['domain'];
		}
	}

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
	require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/core/private/dbHashIDQuery.php');
	$rc = ciniki_core_dbHashIDQuery($ciniki, $strsql, 'ciniki.businesses', 'modules', 'module_id');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['modules']) || !isset($rc['modules']['ciniki.web']) ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'613', 'msg'=>'Website not activated.'));
	}
	$modules = $rc['modules'];

	return array('stat'=>'ok', 'business_id'=>$business_id, 'modules'=>$modules, 'redirect'=>$redirect);
}
?>
