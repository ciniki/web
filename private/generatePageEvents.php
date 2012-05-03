<?php
//
// Description
// -----------
// This function will generate the events page for the website
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_web_generatePageEvents($ciniki, $settings) {

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
	$rc = ciniki_web_generatePageHeader($ciniki, $settings, 'Upcoming Events');
	if( $rc['stat'] != 'ok' ) {	
		return $rc;
	}
	$content .= $rc['content'];

	
	//
	// Generate the content of the page
	//
	require_once($ciniki['config']['core']['modules_dir'] . '/events/web/upcoming.php');
	$rc = ciniki_events_webUpcoming($ciniki, $ciniki['request']['business_id'], 0);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$events = $rc['events'];

	$content .= "<div id='content'>\n"
		. "<article class='page'>\n"
		. "<header class='entry-title'><h1 class='entry-title'>Upcoming Events</h1></header>\n"
		. "<div class='entry-content'>\n"
		. "";

	require_once($ciniki['config']['core']['modules_dir'] . '/web/private/processEvents.php');
	$rc = ciniki_web_processEvents($ciniki, $settings, $events);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$content .= $rc['content'];

	$content .= "</div>\n"
		. "</article>\n"
		. "</div>\n"
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
