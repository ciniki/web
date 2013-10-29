<?php
//
// Description
// -----------
// This method will get the history for a site setting or content.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:			The ID of the business to get the details for.
// field:				The field to get the change history for.
//
// Returns
// -------
// <rsp stat="ok">
//		<history>
//			<action user_id="2" date="Jul 20, 2012 12:38 AM" value="yes" user_display_name="Andrew">&lt; 1 min</action>
//			<action user_id="2" date="Jul 20, 2012 12:38 AM" value="no" user_display_name="Andrew">&lt; 1 min</action>
//		</history>
// </rsp>
//
function ciniki_web_pageSettingsHistory($ciniki) {
	//
	// Find all the required and optional arguments
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
	$rc = ciniki_core_prepareArgs($ciniki, 'no', array(
		'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
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
	$rc = ciniki_web_checkAccess($ciniki, $args['business_id'], 'ciniki.web.pageSettingsHistory');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbGetModuleHistory');
	// Check if the history is for the content or the settings
	if( preg_match('/.*-content/', $args['field']) ) {
		return ciniki_core_dbGetModuleHistory($ciniki, 'ciniki.web', 'ciniki_web_history', $args['business_id'], 'ciniki_web_content', $args['field'], 'detail_value', 'setting');
	}
	return ciniki_core_dbGetModuleHistory($ciniki, 'ciniki.web', 'ciniki_web_history', $args['business_id'], 'ciniki_web_settings', $args['field'], 'detail_value', 'setting');
}
?>
