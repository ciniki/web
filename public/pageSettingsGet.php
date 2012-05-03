<?php
//
// Description
// -----------
// This method will return the list of settings for page.
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_web_pageSettingsGet($ciniki) {
	//
	// Find all the required and optional arguments
	//
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/prepareArgs.php');
	$rc = ciniki_core_prepareArgs($ciniki, 'no', array(
		'business_id'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No business specified'),
		'page'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No page specified'),
		'content'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No content specified'),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$args = $rc['args'];

	//
	// Check access to business_id as owner, and load module list
	//
	require_once($ciniki['config']['core']['modules_dir'] . '/web/private/checkAccess.php');
	$ac = ciniki_web_checkAccess($ciniki, $args['business_id'], 'ciniki.web.pageSettingsGet');
	if( $ac['stat'] != 'ok' ) {
		return $ac;
	}

	//
	// Get the settings from the database
	//
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbDetailsQueryDash.php');
	$rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_web_settings', 'business_id', $args['business_id'], 'web', 'settings', 'page-' . $args['page']);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['settings']) ) {
		$settings = array();
	} else {
		$settings = $rc['settings'];
	}

	//
	// If requested, also get the page content
	//
	if( isset($args['content']) && $args['content'] == 'yes' ) {
		$rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_web_content', 'business_id', $args['business_id'], 'web', 'content', 'page-' . $args['page']);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( isset($rc['content']) ) {
			$settings = array_merge($settings, $rc['content']);
		}
	}

	return array('stat'=>'ok', 'settings'=>$settings);
}
?>
