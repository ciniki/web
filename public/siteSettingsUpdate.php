<?php
//
// Description
// -----------
// This method will update any valid page settings and content in the database.
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_web_siteSettingsUpdate($ciniki) {
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
	// Check access to business_id as owner, and load module list
	//
	require_once($ciniki['config']['core']['modules_dir'] . '/web/private/checkAccess.php');
	$ac = ciniki_web_checkAccess($ciniki, $args['business_id'], 'ciniki.web.pageSettingsUpdate');
	if( $ac['stat'] != 'ok' ) {
		return $ac;
	}

	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbTransactionStart.php');
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbTransactionRollback.php');
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbTransactionCommit.php');
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbQuote.php');
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbInsert.php');
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbAddModuleHistory.php');
	$rc = ciniki_core_dbTransactionStart($ciniki, 'web');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	//
	// The list of valid settings for web pages
	//
	$settings_fields = array(
		'page-home-active',
		'page-about-active',
		'page-about-image',
		'page-gallery-active',
		'page-events-active',
		'page-events-past',
		'page-friends-active',
		'page-links-active',
		'page-contact-active',
		'page-contact-name-display',
		'page-contact-addr-ss-display',
		'page-contact-phone-display',
		'page-contact-fax-display',
		'page-contact-email-display',
		'page-downloads-active',
		'page-account-active',
		'page-signup-active',
		'page-api-active',
		'site-theme',
		'site-header-image',
		'site-google-analytics-account',
		'site-featured',
		);

	//
	// Check if the field was passed, and then try an insert, but if that fails, do an update
	//
	foreach($settings_fields as $field) {
		if( isset($ciniki['request']['args'][$field]) ) {
			$strsql = "INSERT INTO ciniki_web_settings (business_id, detail_key, detail_value, date_added, last_updated) "
				. "VALUES ('" . ciniki_core_dbQuote($ciniki, $ciniki['request']['args']['business_id']) . "'"
				. ", '" . ciniki_core_dbQuote($ciniki, $field) . "' "
				. ", '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['args'][$field]) . "'"
				. ", UTC_TIMESTAMP(), UTC_TIMESTAMP()) "
				. "ON DUPLICATE KEY UPDATE detail_value = '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['args'][$field]) . "' "
				. ", last_updated = UTC_TIMESTAMP() "
				. "";
			$rc = ciniki_core_dbInsert($ciniki, $strsql, 'web');
			if( $rc['stat'] != 'ok' ) {
				ciniki_core_dbTransactionRollback($ciniki, 'web');
				return $rc;
			}
			ciniki_core_dbAddModuleHistory($ciniki, 'web', 'ciniki_web_history', $args['business_id'], 
				2, 'ciniki_web_settings', $field, 'detail_value', $ciniki['request']['args'][$field]);
		}
	}

	//
	// The list of valid content for web pages
	//
	$content_fields = array(
		'page-home-content',
		'page-about-content',
		'page-contact-content',
		'page-signup-content',
		'page-signup-agreement',
		'page-signup-submit',
		'page-signup-success',
		);

	//
	// Check if the field was passed, and then try an insert, but if that fails, do an update
	//
	foreach($content_fields as $field) {
		if( isset($ciniki['request']['args'][$field]) ) {
			$strsql = "INSERT INTO ciniki_web_content (business_id, detail_key, detail_value, date_added, last_updated) "
				. "VALUES ('" . ciniki_core_dbQuote($ciniki, $ciniki['request']['args']['business_id']) . "'"
				. ", '" . ciniki_core_dbQuote($ciniki, $field) . "' "
				. ", '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['args'][$field]) . "'"
				. ", UTC_TIMESTAMP(), UTC_TIMESTAMP()) "
				. "ON DUPLICATE KEY UPDATE detail_value = '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['args'][$field]) . "' "
				. ", last_updated = UTC_TIMESTAMP() "
				. "";
			$rc = ciniki_core_dbInsert($ciniki, $strsql, 'web');
			if( $rc['stat'] != 'ok' ) {
				ciniki_core_dbTransactionRollback($ciniki, 'web');
				return $rc;
			}
			ciniki_core_dbAddModuleHistory($ciniki, 'web', 'ciniki_web_history', $args['business_id'], 
				2, 'ciniki_web_content', $field, 'detail_value', $ciniki['request']['args'][$field]);
		}
	}

	//
	// Commit the changes to the database
	//
	$rc = ciniki_core_dbTransactionCommit($ciniki, 'web');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	return array('stat'=>'ok');
}
?>
