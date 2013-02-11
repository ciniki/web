<?php
//
// Description
// -----------
// This method will return the list of settings for a site.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:		The ID of the business to get the site settings for.
// content:			(optional) Should the site content be returned as well. (yes or no)
//
// Returns
// -------
// <rsp stat="ok">
//		<settings site-google-analytics-account="UA-812942303-1" site-header-image="0" site-theme="black" />
// </rsp>
// 
//
function ciniki_web_siteSettingsGet($ciniki) {
	//
	// Find all the required and optional arguments
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
	$rc = ciniki_core_prepareArgs($ciniki, 'no', array(
		'business_id'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No business specified'),
		'content'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No content specified'),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$args = $rc['args'];

	//
	// Check access to business_id as owner, and load module list
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'checkAccess');
	$ac = ciniki_web_checkAccess($ciniki, $args['business_id'], 'ciniki.web.siteSettingsGet');
	if( $ac['stat'] != 'ok' ) {
		return $ac;
	}

	//
	// Get the settings from the database
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash');
	$rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_web_settings', 'business_id', $args['business_id'], 'ciniki.web', 'settings', 'site');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['settings']) ) {
		$settings = array();
	} else {
		$settings = $rc['settings'];
	}

	//
	// If requested, also get the site content
	//
	if( isset($args['content']) && $args['content'] == 'yes' ) {
		$rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_web_content', 'business_id', $args['business_id'], 'ciniki.web', 'content', 'site');
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
