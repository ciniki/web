<?php
//
// Description
// -----------
// This method will return the list of settings for a specific page.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:		The ID of the business to get the page settings for.
// page:			The page to get the settings for.  It can be one of the
// 					following values:
//
// 					- home
// 					- about
//					- contact
//					- gallery
//					- events
//					- links
//
// content:			(optional) Should the content for the page be returned as well.  (yes or no)
//
// Returns
// -------
// <rsp stat="ok">
//		<settings page-about-active="yes" page-about-image="27">
//			<page-about-content>The about page content</page-about-content>
//		</settings>
// </rsp>
//
function ciniki_web_pageSettingsGet($ciniki) {
	//
	// Find all the required and optional arguments
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
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
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'checkAccess');
	$ac = ciniki_web_checkAccess($ciniki, $args['business_id'], 'ciniki.web.pageSettingsGet');
	if( $ac['stat'] != 'ok' ) {
		return $ac;
	}

	//
	// Get the settings from the database
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash');
	$rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_web_settings', 'business_id', $args['business_id'], 'ciniki.web', 'settings', 'page-' . $args['page']);
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
		$rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_web_content', 'business_id', $args['business_id'], 'ciniki.web', 'content', 'page-' . $args['page']);
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
