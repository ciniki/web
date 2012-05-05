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

	
	$content .= "<div id='content'>\n"
		. "";
	//
	// Generate the content of the page
	//
	require_once($ciniki['config']['core']['modules_dir'] . '/events/web/list.php');
	$rc = ciniki_events_web_list($ciniki, $ciniki['request']['business_id'], 'upcoming', 0);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$events = $rc['events'];

	$content .= "<article class='page'>\n"
		. "<header class='entry-title'><h1 class='entry-title'>Upcoming Events</h1></header>\n"
		. "<div class='entry-content'>\n"
		. "";

	require_once($ciniki['config']['core']['modules_dir'] . '/web/private/processEvents.php');
	$rc = ciniki_web_processEvents($ciniki, $settings, $events, 0);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$content .= $rc['content'];

	$content .= "</div>\n"
		. "</article>\n"
		. "";

	//
	// Include past events if the user wants
	//
	if( isset($settings['page-events-past']) && $settings['page-events-past'] == 'yes' ) {
		//
		// Generate the content of the page
		//
		require_once($ciniki['config']['core']['modules_dir'] . '/events/web/list.php');
		$rc = ciniki_events_web_list($ciniki, $ciniki['request']['business_id'], 'past', 10);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$events = $rc['events'];

		$content .= "<article class='page'>\n"
			. "<header class='entry-title'><h1 class='entry-title'>Past Events</h1></header>\n"
			. "<div class='entry-content'>\n"
			. "";

		require_once($ciniki['config']['core']['modules_dir'] . '/web/private/processEvents.php');
		$rc = ciniki_web_processEvents($ciniki, $settings, $events, 0);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$content .= $rc['content'];

		$content .= "</div>\n"
			. "</article>\n"
			. "";
	}

	$content .= "</div>\n"
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
