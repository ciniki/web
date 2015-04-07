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
	// Get the settings
	//
/*	$rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_web_settings', 'business_id', 
		$business_id, 'ciniki.web', 'settings', '');
	if( $rc['stat'] == 'ok' && isset($rc['settings']) ) {
		$settings = $rc['settings'];
	}*/

	return array('stat'=>'ok', 'settings'=>$settings);	
}
?>
