<?php
//
// Description
// -----------
// This function will generate the home page for the website
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_web_generatePageHome($ciniki, $settings) {

	//
	// Store the content created by the page
	// Make sure everything gets generated ok before returning the content
	//
	$content = '';
	$page_content = '';

	//
	// FIXME: Check if anything has changed, and if not load from cache
	//
	

	//
	// Add the header
	//
	require_once($ciniki['config']['core']['modules_dir'] . '/web/private/generatePageHeader.php');
	$rc = ciniki_web_generatePageHeader($ciniki, $settings, 'Home');
	if( $rc['stat'] != 'ok' ) {	
		return $rc;
	}
	$content .= $rc['content'];

	
	//
	// Generate the content of the page
	//
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbDetailsQueryDash.php');
	$rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_web_content', 'business_id', $ciniki['request']['business_id'], 'web', 'content', 'page-home');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	if( isset($rc['content']['page-home-content']) ) {
		require_once($ciniki['config']['core']['modules_dir'] . '/web/private/processContent.php');
		$rc = ciniki_web_processContent($ciniki, $rc['content']['page-home-content']);	
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$page_content = $rc['content'];
	}

	$content .= "<div id='content'>"
		. $page_content
		. "</div>";

	//
	// Add the footer
	//
	require_once($ciniki['config']['core']['modules_dir'] . '/web/private/generatePageFooter.php');
	$rc = ciniki_web_generatePageFooter($ciniki, $settings);
	if( $rc['stat'] != 'ok' ) {	
		return $rc;
	}
	$content .= $rc['content'];

	return array('stat'=>'ok', 'content'=>$content);
}
?>
