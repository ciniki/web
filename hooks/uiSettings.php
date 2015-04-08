<?php
//
// Description
// -----------
// This function will return a list of user interface settings for the module.
//
// Arguments
// ---------
// ciniki:
// business_id:		The ID of the business to get events for.
//
// Returns
// -------
//
function ciniki_web_hooks_uiSettings($ciniki, $business_id, $args) {

	$settings = array();

	//
	// Get the base url for their website
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'lookupBusinessURL');
	$rc = ciniki_web_lookupBusinessURL($ciniki, $business_id);
	if( $rc['stat'] != 'ok' ) {	
		return $rc;
	}
	if( isset($rc['url']) ) {
		$settings['base_url'] = $rc['url'];
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
		$settings['sitename'] = $rc['business']['sitename'];
	}

	return array('stat'=>'ok', 'settings'=>$settings);	
}
?>
