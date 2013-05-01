<?php
//
// Description
// ===========
// When a subscription is added or updated, this function should be called
// to update the web settings either turning on or off the sign in feature.
//
// If there are subscriptions available for customer updating,
// page-subscriptions-public is set to 'yes'.
//
// The web module uses these flags to determine if there should be a menu option
// enabled for Downloads.
//
// Arguments
// =========
// ciniki:
// modules:		The array of modules enabled for the business.  This is returned by the 
//				ciniki_web_checkAccess function.
// business_id:	The ID of the business to check for downloads.
//
// Returns
// =======
//
function ciniki_web_settingsUpdateSubscriptions(&$ciniki, $modules, $business_id) {

	//
	// Default set the flags to 'no'
	//
	$public = 'no';

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbCount');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbInsert');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbUpdate');

	if( isset($modules['ciniki.subscriptions']) ) {
		//
		// Check for public files
		//
		$strsql = "SELECT 'page-subscriptions-public' AS name, COUNT(*) "
			. "FROM ciniki_subscriptions "
			. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
			. "AND (flags&0x01) = 0x01 "
			. "";
		$rc = ciniki_core_dbCount($ciniki, $strsql, 'ciniki.subscriptions', 'public');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( isset($rc['public']['page-subscriptions-public']) && $rc['public']['page-subscriptions-public'] > 0 ) {
			$public = 'yes';
		}
	}

	//
	// Get the current settings
	//
	$strsql = "SELECT detail_value "
		. "FROM ciniki_web_settings "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
		. "AND detail_key = 'page-subscriptions-public' "
		. "";
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.web', 'detail');
	if( $rc['stat'] != 'ok' ) {	
		return $rc;
	}
	if( isset($rc['detail']) ) {
		//
		// Update the public settings, if there has been a change
		//
		if( $rc['detail']['detail_value'] != $public ) {
			$strsql = "UPDATE ciniki_web_settings SET "
				. "detail_value = '" . ciniki_core_dbQuote($ciniki, $public) . "' "
				. ", last_updated = UTC_TIMESTAMP() "
				. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
				. "AND detail_key = 'page-subscriptions-public' "
				. "";
			$rc = ciniki_core_dbUpdate($ciniki, $strsql, 'ciniki.web');
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.web', 'ciniki_web_history', $business_id, 
				2, 'ciniki_web_settings', 'page-subscriptions-public', 'detail_value', $public);
			$ciniki['syncqueue'][] = array('push'=>'ciniki.web.setting',
				'args'=>array('id'=>$field));
		}
	} else {
		//
		// Add the public settings
		//
		$strsql = "INSERT INTO ciniki_web_settings (business_id, detail_key, detail_value, date_added, last_updated) "
			. "VALUES ('" . ciniki_core_dbQuote($ciniki, $business_id) . "'"
			. ", '" . ciniki_core_dbQuote($ciniki, 'page-subscriptions-public') . "' "
			. ", '" . ciniki_core_dbQuote($ciniki, $public) . "'"
			. ", UTC_TIMESTAMP(), UTC_TIMESTAMP()) "
			. "";
		$rc = ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.web');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.web', 'ciniki_web_history', $business_id, 
			1, 'ciniki_web_settings', 'page-subscriptions-public', 'detail_value', $public);
		$ciniki['syncqueue'][] = array('push'=>'ciniki.web.setting',
			'args'=>array('id'=>$field));
	}

	//
	// Update the last_change date in the business modules
	// Ignore the result, as we don't want to stop user updates if this fails.
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'updateModuleChangeDate');
	ciniki_businesses_updateModuleChangeDate($ciniki, $business_id, 'ciniki', 'web');

	return array('stat'=>'ok');
}
?>