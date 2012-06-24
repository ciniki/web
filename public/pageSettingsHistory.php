<?php
//
// Description
// -----------
// This function will get the history of a field from the ciniki_core_change_logs table.
// This allows the user to view what has happened to a data element, and if they
// choose, revert to a previous version.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:			The ID of the business to get the details for.
// key:					The detail key to get the history for.
//
// Returns
// -------
//
function ciniki_web_pageSettingsHistory($ciniki) {
	//
	// Find all the required and optional arguments
	//
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/prepareArgs.php');
	$rc = ciniki_core_prepareArgs($ciniki, 'no', array(
		'business_id'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No business specified'), 
		'field'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No field specified'), 
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$args = $rc['args'];
	
	//
	// Check access to business_id as owner, or sys admin
	//
	require_once($ciniki['config']['core']['modules_dir'] . '/web/private/checkAccess.php');
	$rc = ciniki_web_checkAccess($ciniki, $args['business_id'], 'ciniki.web.pageSettingsHistory');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbGetModuleHistory.php');
	// Check if the history is for the content or the settings
	if( preg_match('/.*-content/', $args['field']) ) {
		return ciniki_core_dbGetModuleHistory($ciniki, 'web', 'ciniki_web_history', $args['business_id'], 'ciniki_web_content', $args['field'], 'detail_value', 'setting');
	}
	return ciniki_core_dbGetModuleHistory($ciniki, 'web', 'ciniki_web_history', $args['business_id'], 'ciniki_web_settings', $args['field'], 'detail_value', 'setting');
}
?>
