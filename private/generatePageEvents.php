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
	$rc = ciniki_events_webUpcoming($ciniki, $ciniki['request']['business_id']);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$events = $rc['events'];

	$content .= "<div id='content'>\n"
		. "<article class='page'>\n"
		. "<header class='entry-title'><h1 class='entry-title'>Upcoming Events</h1></header>\n"
		. "<div class='entry-content'>\n"
//		. "<dl class='event-list'>\n"
		. "<table class='event-list'>\n"
		. "";
	foreach($events as $event_num => $e) {
		$event = $e['event'];
		$event_date = $event['start_month'];
		$event_date .= " " . $event['start_day'];
		if( $event['end_day'] != '' && $event['start_day'] != $event['end_day'] ) {
			if( $event['end_month'] != '' && $event['end_month'] == $event['start_month'] ) {
				$event_date .= " - " . $event['end_day'];
			} else {
				$event_date .= " - " . $event['end_month'] . " " . $event['end_day'];
			}
		}
		$event_date .= ", " . $event['start_year'];
		if( $event['end_year'] != '' && $event['start_year'] != $event['end_year'] ) {
			$event_date .= "/" . $event['end_year'];
		}
	//	$content .= "<dt>$event_date</dt>"
	//		. "<dd><b>" . $event['name'] . "</b>";
		$content .= "<tr><th><span class='event-date'>$event_date</span></th>"
			. "<td><span class='event-title'>" . $event['name'] . "</span>";
		if( $event['description'] != '' ) {
			$content .= "<br/><span class='event-description'>" . $event['description'] . "</span>";
		}
		if( $event['url'] != '' ) {
			if( !preg_match('/^\s*http/', $event['url']) ) {
				$url = "http://" . $event['url'];
			} else {
				$url = $event['url'];
			}
			$content .= "<br/><a class='event-url' target='_blank' href='" . $url . "' title='" . $event['name'] . "'>" . $url . "</a>";
		}
		// $content .= "</dd>";
		$content .= "</td></tr>";
	}
	// $content .= "</dl>\n"
	$content .= "</table>\n"
		. "</div>\n"
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
