<?php
//
// Description
// -----------
// This function will generate the gallery page for the website
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_web_generatePageGallery($ciniki, $settings) {

	//
	// Store the content created by the page
	// Make sure everything gets generated ok before returning the content
	//
	$content = '';

	//
	// FIXME: Check if anything has changed, and if not load from cache
	//
	

	//
	// Add the header
	//
	require_once($ciniki['config']['core']['modules_dir'] . '/web/private/generatePageHeader.php');
	$rc = ciniki_web_generatePageHeader($ciniki, $settings, 'Gallery');
	if( $rc['stat'] != 'ok' ) {	
		return $rc;
	}
	$content .= $rc['content'];

	
	//
	// Generate the content of the page
	//
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbDetailsQuery.php');
	$rc = ciniki_core_dbDetailsQuery($ciniki, 'ciniki_web_content', 'business_id', $ciniki['request']['business_id'], 'web', 'content', 'page.gallery');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$page_content = $rc['content'];

	$processed_content = '<p>' . preg_replace('/$/m', '</p><p>', $page_content['page.gallery.content']) . '</p>';

	$content .= "<div id='content'>\n"
		. "<article class='page'>\n"
		. "<header class='entry-title'><h1 class='entry-title'>Gallery</h1></header>\n"
		. "<div class='entry-content'>\n"
		. $processed_content
		. "</div>"
		. "</article>"
		. "</div>"
		. "";

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
