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
function ciniki_web_lookupBusinessURL($ciniki, $business_id) {

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');

	$strsql = "SELECT domain, "
		. "(flags&0x01) AS isprimary "
		. "FROM ciniki_business_domains "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
		. "AND status < 50 "
		. "ORDER BY isprimary "
		. "LIMIT 1"
		. "";
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.businesses', 'business');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( isset($rc['business']) ) {
		return array('stat'=>'ok', 'url'=>"http://" . $rc['business']['domain']);
	}

	//
	// Get the sitename if no domain is specified
	//
	$strsql = "SELECT sitename FROM ciniki_businesses "
		. "WHERE id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
		. "";
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.businesses', 'business');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( isset($rc['business']) ) {
		return array('stat'=>'ok', 'url'=>"http://" . $ciniki['config']['ciniki.web']['master.domain'] . '/' . $rc['business']['sitename']);
	}

	return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1052', 'msg'=>'Unable to find business URL'));
}
?>
