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
function ciniki_web_domainList($ciniki) {
	//
	// Find all the required and optional arguments
	//
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/prepareArgs.php');
	$rc = ciniki_core_prepareArgs($ciniki, 'no', array(
		'business_id'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No business specified'),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$args = $rc['args'];

	//
	// Check access to business_id as owner
	//
	require_once($ciniki['config']['core']['modules_dir'] . '/web/private/checkAccess.php');
	$ac = ciniki_web_checkAccess($ciniki, $args['business_id'], 'ciniki.web.domainList');
	if( $ac['stat'] != 'ok' ) {
		return $ac;
	}

	//
	// Query the database for the domain
	//
	$strsql = "SELECT id, domain, flags, status, "
		. "IF((flags&0x01)=0x01, 'yes', 'no') AS issite, "
		. "IF((flags&0x10)=0x10, 'yes', 'no') AS isdomain, "
		. "IF((flags&0x20)=0x20, 'yes', 'no') AS isprimary, "
		. "status "
		. "FROM ciniki_web_domains "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "";

	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbRspQuery.php');
	return ciniki_core_dbRspQuery($ciniki, $strsql, 'web', 'domains', 'domain', array('stat'=>'ok', 'domains'=>array()));
}
?>
