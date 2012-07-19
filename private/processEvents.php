<?php
//
// Description
// -----------
// This function will process a list of events, and format the html.
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure, similar to ciniki variable but only web specific information.
// events:			The array of events as returned by ciniki_events_web_list.
// limit:			The number of events to show.  Only 2 events are shown on the homepage.
//
// Returns
// -------
//
function ciniki_web_processEvents($ciniki, $settings, $events, $limit) {

	$content = "<table class='event-list'>\n"
		. "";
	$count = 0;
	foreach($events as $event_num => $e) {
		if( $limit > 0 && $count >= $limit ) { break; }
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
		$content .= "<tr><th><span class='event-date'>$event_date</span></th>"
			. "<td><span class='event-title'>" . $event['name'] . "</span>";
		if( $event['description'] != '' ) {
			$content .= "<br/><span class='event-description'>" . $event['description'] . "</span>";
		}
		if( $event['url'] != '' ) {
			$url = $event['url'];
			if( $url != '' && !preg_match('/^\s*http/i', $url) ) {
				$display_url = $url;
				$url = "http://" . $url;
			} else {
				$display_url = preg_replace('/^\s*http:\/\//i', '', $url);
				$display_url = preg_replace('/\/$/i', '', $display_url);
			}
			$content .= "<br/><a class='event-url' target='_blank' href='" . $url . "' title='" . $event['name'] . "'>" . $display_url . "</a>";
		}
		$content .= "</td></tr>";
		$count++;
	}
	$content .= "</table>\n"
		. "";

	return array('stat'=>'ok', 'content'=>$content);
}
?>

