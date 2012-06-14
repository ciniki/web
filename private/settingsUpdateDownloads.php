<?php
//
// Description
// ===========
// This function will check the revelant modules which are used by the downloads page.
//
// Returns
// =======
//
function ciniki_web_settingsUpdateDownloads($ciniki, $modules, $business_id) {

	$public = 'no';
	$customers = 'no';

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbCount');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbInsert');

	if( isset($modules['ciniki.filedepot']) ) {
		//
		// Check for public files
		//
		$strsql = "SELECT 'page-downloads-public' AS name, COUNT(*) "
			. "FROM ciniki_filedepot_files "
			. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
			. "AND (sharing_flags&0x01) = 0x01 "
			. "AND status = 1 "
			. "";
		$rc = ciniki_core_dbCount($ciniki, $strsql, 'filedepot', 'public');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( isset($rc['public']['page-downloads-public']) && $rc['public']['page-downloads-public'] > 0 ) {
			$public = 'yes';
		}

		//
		// Check for customer only files
		//
		$strsql = "SELECT 'page-downloads-customers' AS name, COUNT(*) "
			. "FROM ciniki_filedepot_files "
			. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
			. "AND (sharing_flags&0x02) = 0x02 "
			. "AND status = 1 "
			. "";
		$rc = ciniki_core_dbCount($ciniki, $strsql, 'filedepot', 'customers');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( isset($rc['customers']['page-downloads-customers']) && $rc['customers']['page-downloads-customers'] > 0 ) {
			$customers = 'yes';
		}
	}

	//
	// Future: Add other module checks here
	//

	//
	// Update the public settings
	//
	$strsql = "INSERT INTO ciniki_web_settings (business_id, detail_key, detail_value, date_added, last_updated) "
		. "VALUES ('" . ciniki_core_dbQuote($ciniki, $business_id) . "'"
		. ", '" . ciniki_core_dbQuote($ciniki, 'page-downloads-public') . "' "
		. ", '" . ciniki_core_dbQuote($ciniki, $public) . "'"
		. ", UTC_TIMESTAMP(), UTC_TIMESTAMP()) "
		. "ON DUPLICATE KEY UPDATE detail_value = '" . ciniki_core_dbQuote($ciniki, $public) . "' "
		. ", last_updated = UTC_TIMESTAMP() "
		. "";
	$rc = ciniki_core_dbInsert($ciniki, $strsql, 'web');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	//
	// Update the customers settings
	//
	$strsql = "INSERT INTO ciniki_web_settings (business_id, detail_key, detail_value, date_added, last_updated) "
		. "VALUES ('" . ciniki_core_dbQuote($ciniki, $business_id) . "'"
		. ", '" . ciniki_core_dbQuote($ciniki, 'page-downloads-customers') . "' "
		. ", '" . ciniki_core_dbQuote($ciniki, $customers) . "'"
		. ", UTC_TIMESTAMP(), UTC_TIMESTAMP()) "
		. "ON DUPLICATE KEY UPDATE detail_value = '" . ciniki_core_dbQuote($ciniki, $customers) . "' "
		. ", last_updated = UTC_TIMESTAMP() "
		. "";
	$rc = ciniki_core_dbInsert($ciniki, $strsql, 'web');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	return array('stat'=>'ok');
}
?>
