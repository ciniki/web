<?php
//
// Description
// -----------
// This function will generate the events page for the website.
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_generatePageClasses($ciniki, $settings) {

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
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageHeader');
	$rc = ciniki_web_generatePageHeader($ciniki, $settings, 'Classes');
	if( $rc['stat'] != 'ok' ) {	
		return $rc;
	}
	$content .= $rc['content'];

	
	$content .= "<div id='content'>\n"
		. "";
	//
	// Generate the content of the page
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'classes', 'web', 'list');
	$rc = ciniki_classes_web_list($ciniki, $ciniki['request']['business_id'], 'upcoming', 0);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$events = $rc['events'];

	$content .= "<article class='page'>\n"
		. "<header class='entry-title'><h1 class='entry-title'>Upcoming Events</h1></header>\n"
		. "<div class='entry-content'>\n"
		. "";

	if( count($events) > 0 ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processEvents');
		$rc = ciniki_web_processEvents($ciniki, $settings, $events, 0);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$content .= $rc['content'];
	} else {
		$content .= "<p>No upcoming events</p>";
	}

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
		ciniki_core_loadMethod($ciniki, 'ciniki', 'events', 'web', 'list');
		$rc = ciniki_events_web_list($ciniki, $ciniki['request']['business_id'], 'past', 10);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$events = $rc['events'];

		$content .= "<article class='page'>\n"
			. "<header class='entry-title'><h1 class='entry-title'>Past Events</h1></header>\n"
			. "<div class='entry-content'>\n"
			. "";

		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processEvents');
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
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageFooter');
	$rc = ciniki_web_generatePageFooter($ciniki, $settings);
	if( $rc['stat'] != 'ok' ) {	
		return $rc;
	}
	$content .= $rc['content'];

	return array('stat'=>'ok', 'content'=>$content);
}
?>
