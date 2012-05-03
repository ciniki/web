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
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbAddChangeLog.php');
	$rc = ciniki_core_dbTransactionStart($ciniki, 'web');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	//
	// Check to see if an image was uploaded
	//
	if( isset($_FILES['uploadfile']['error']) && $_FILES['uploadfile']['error'] == UPLOAD_ERR_INI_SIZE ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'640', 'msg'=>'Upload failed, file too large.'));
	}
	// FIXME: Add other checkes for $_FILES['uploadfile']['error']

	$image_id = 0;
	if( isset($_FILES) && isset($_FILES['page-about-image']) && $_FILES['page-about-image']['tmp_name'] != '' ) {
		//
		// Add the image into the database
		//
		require_once($ciniki['config']['core']['modules_dir'] . '/images/private/insertFromUpload.php');
		$rc = ciniki_images_insertFromUpload($ciniki, $args['business_id'], $ciniki['session']['user']['id'], 
			$_FILES['page-about-image'], 1, 'About webpage image', '', 'no');
		// If a duplicate image is found, then use that id instead of uploading a new one
		if( $rc['stat'] != 'ok' && $rc['err']['code'] != '330' ) {
			ciniki_core_dbTransactionRollback($ciniki, 'users');
			return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'641', 'msg'=>'Internal Error', 'err'=>$rc['err']));
		}

		if( !isset($rc['id']) ) {
			ciniki_core_dbTransactionRollback($ciniki, 'users');
			return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'642', 'msg'=>'Invalid file type'));
		}
		//
		// Set the request variable, so it will be updated in the database
		//
		$ciniki['request']['args']['page-about-image'] = $rc['id'];
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
		'page-friends-active',
		'page-links-active',
		'page-contact-active',
		'page-contact-name-display',
		'page-contact-addr-ss-display',
		'page-contact-phone-display',
		'page-contact-fax-display',
		'page-contact-email-display',
		'page-signup-active',
		'page-api-active',
		'site-theme',
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
			ciniki_core_dbAddChangeLog($ciniki, 'web', $args['business_id'], 'ciniki_web_settings', $field, 
				'detail_value', $ciniki['request']['args'][$field]);
		}
	}

	//
	// The list of valid content for web pages
	//
	$content_fields = array(
		'page-home-content',
		'page-about-content',
		'page-contact-content',
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
			ciniki_core_dbAddChangeLog($ciniki, 'web', $args['business_id'], 'ciniki_web_content', $field, 
				'detail_value', $ciniki['request']['args'][$field]);
		}
	}

	//
	// Commit the changes to the database
	//
	$rc = ciniki_core_dbTransactionCommit($ciniki, 'wineproduction');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}


	return array('stat'=>'ok');
}
?>
