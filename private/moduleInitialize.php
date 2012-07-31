<?php
//
// Description
// ===========
// This function will initialize a the website for a business who just activated the module.
// This function is used by the web signup process.
//
// Arguments
// =========
// ciniki:
// business_id: 		The ID of the business the request is for.
// 
// Returns
// =======
//
function ciniki_web_moduleInitialize($ciniki, $business_id) {

	//
	// Get the list of modules activated for this business
	//
	$strsql = "SELECT ruleset, CONCAT_WS('.', ciniki_business_modules.package, ciniki_business_modules.module) AS module_id "
		. "FROM ciniki_business_modules "
		. "WHERE ciniki_business_modules.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
		. "AND ciniki_business_modules.status = 1 "														// Business is active
		. "";
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbHashIDQuery.php');
	$rc = ciniki_core_dbHashIDQuery($ciniki, $strsql, 'ciniki.businesses', 'modules', 'module_id');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['modules']) || !isset($rc['modules']['ciniki.web']) ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'672', 'msg'=>'Access denied.'));
	}
	$modules = $rc['modules'];

	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbInsert.php');

	//
	// Active the home page
	//
	$strsql = "INSERT INTO ciniki_web_settings (business_id, detail_key, detail_value, date_added, last_updated) VALUES ("
		. "'" . ciniki_core_dbQuote($ciniki, $business_id) . "', "
		. "'page-home-active', 'yes', UTC_TIMESTAMP(), UTC_TIMESTAMP())";
	ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.web');

	//
	// Active about page
	//
	$strsql = "INSERT INTO ciniki_web_settings (business_id, detail_key, detail_value, date_added, last_updated) VALUES ("
		. "'" . ciniki_core_dbQuote($ciniki, $business_id) . "', "
		. "'page-about-active', 'yes', UTC_TIMESTAMP(), UTC_TIMESTAMP())";
	ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.web');
	$strsql = "INSERT INTO ciniki_web_content (business_id, detail_key, detail_value, date_added, last_updated) VALUES ("
		. "'" . ciniki_core_dbQuote($ciniki, $business_id) . "', "
		. "'page-about-content', 'Sample about page', UTC_TIMESTAMP(), UTC_TIMESTAMP())";
	ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.web');

	//
	// Active contact page 
	//
	$strsql = "INSERT INTO ciniki_web_settings (business_id, detail_key, detail_value, date_added, last_updated) VALUES ("
		. "'" . ciniki_core_dbQuote($ciniki, $business_id) . "', "
		. "'page-contact-active', 'yes', UTC_TIMESTAMP(), UTC_TIMESTAMP())";
	ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.web');
	$strsql = "INSERT INTO ciniki_web_settings (business_id, detail_key, detail_value, date_added, last_updated) VALUES ("
		. "'" . ciniki_core_dbQuote($ciniki, $business_id) . "', "
		. "'page-contact-name-display', 'yes', UTC_TIMESTAMP(), UTC_TIMESTAMP())";
	ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.web');
	$strsql = "INSERT INTO ciniki_web_settings (business_id, detail_key, detail_value, date_added, last_updated) VALUES ("
		. "'" . ciniki_core_dbQuote($ciniki, $business_id) . "', "
		. "'page-contact-email-display', 'yes', UTC_TIMESTAMP(), UTC_TIMESTAMP())";
	ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.web');

	//
	// Active artcatalog gallery
	//
	if( isset($modules['ciniki.artcatalog']) ) {
		$strsql = "INSERT INTO ciniki_web_settings (business_id, detail_key, detail_value, date_added, last_updated) VALUES ("
			. "'" . ciniki_core_dbQuote($ciniki, $business_id) . "', "
			. "'page-gallery-active', 'yes', UTC_TIMESTAMP(), UTC_TIMESTAMP())";
		ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.web');
		//
		// Set the theme to blue on black by default if artcatalog specified
		//
		$strsql = "INSERT INTO ciniki_web_settings (business_id, detail_key, detail_value, date_added, last_updated) VALUES ("
			. "'" . ciniki_core_dbQuote($ciniki, $business_id) . "', "
			. "'site-theme', 'black', UTC_TIMESTAMP(), UTC_TIMESTAMP())";
		ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.web');
	}

	//
	// Active events
	//
	if( isset($modules['ciniki.events']) ) {
		$strsql = "INSERT INTO ciniki_web_settings (business_id, detail_key, detail_value, date_added, last_updated) VALUES ("
			. "'" . ciniki_core_dbQuote($ciniki, $business_id) . "', "
			. "'page-events-active', 'yes', UTC_TIMESTAMP(), UTC_TIMESTAMP())";
		ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.web');
		$strsql = "INSERT INTO ciniki_web_settings (business_id, detail_key, detail_value, date_added, last_updated) VALUES ("
			. "'" . ciniki_core_dbQuote($ciniki, $business_id) . "', "
			. "'page-events-past', 'yes', UTC_TIMESTAMP(), UTC_TIMESTAMP())";
		ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.web');
	}

	//
	// Active links
	//
	if( isset($modules['ciniki.links']) ) {
		$strsql = "INSERT INTO ciniki_web_settings (business_id, detail_key, detail_value, date_added, last_updated) VALUES ("
			. "'" . ciniki_core_dbQuote($ciniki, $business_id) . "', "
			. "'page-links-active', 'yes', UTC_TIMESTAMP(), UTC_TIMESTAMP())";
		ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.web');
	}

	//
	// Update the last_change date in the business modules
	// Ignore the result, as we don't want to stop user updates if this fails.
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'updateModuleChangeDate');
	ciniki_businesses_updateModuleChangeDate($ciniki, $args['business_id'], 'ciniki', 'web');

	return array('stat'=>'ok');
}
?>
