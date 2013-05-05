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

	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processURL');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');

	$content = "<table class='cilist'><tbody>";
	$count = 0;
	foreach($events as $event_num => $event) {
		if( $limit > 0 && $count >= $limit ) { break; }
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

		if( $event['isdetails'] == 'yes' ) {
			$event_url = $ciniki['request']['base_url'] . "/events/" . $event['permalink'];
		} else {
			if( $event['url'] != '' ) {
				$rc = ciniki_web_processURL($ciniki, $event['url']);
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				$event_url = $rc['url'];
				$event_display_url = $rc['display'];
			} else {
				$event_url = '';
			}
		}

		$content .= "<tr><th>$event_date</th><td>\n";
		$content .= "<table class='cilist-categories'><tbody>\n";

		// Setup the event image
		$content .= "<tr><td class='cilist-image' rowspan='3'>";
		if( isset($event['image_id']) && $event['image_id'] > 0 ) {
			$rc = ciniki_web_getScaledImageURL($ciniki, $event['image_id'], 'thumbnail', '150', 0);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			if( $event_url != '' ) {
				$content .= "<div class='image-cilist-thumbnail'>"
					. "<a href='$event_url' title='" . $event['name'] . "'><img title='' alt='" . $event['name'] . "' src='" . $rc['url'] . "' /></a>"
					. "</div></aside>";
			} else {
				$content .= "<div class='image-cilist-thumbnail'>"
					. "<img title='' alt='" . $event['name'] . "' src='" . $rc['url'] . "' />"
					. "</div></aside>";
			}
		}
		$content .= "</td>";

		// Setup the details
		$content .= "<td class='cilist-title'>";
		$content .= "<p class='cilist-title'>";
//		if( $event_url != '' ) {
//			$content .= "<a href='$event_url' title='" . $event['name'] . "'>" . $event['name'] . "</a>";
//		} else {
			$content .= $event['name'];
//		}
		$content .= "</p>";
		$content .= "</td></tr>";
		$content .= "<tr><td class='cilist-details'>";
		if( isset($event['description']) && $event['description'] != '' ) {
			$rc = ciniki_web_processContent($ciniki, $event['description'], 'cilist-description');
			if( $rc['stat'] == 'ok' ) {
				$content .= $rc['content'];
			}
			// $content .= "<p class='cilist-description'>" . $event['description'] . "</p>";
		}
		if( $event['isdetails'] == 'yes' ) {
			$content .= "<tr><td class='cilist-more'><a href='$event_url'>... more</a></td></tr>";
		} elseif( $event_url != '' ) {
			$content .= "<tr><td class='cilist-more'><a href='$event_url'>$event_display_url</a></td></tr>";
		}
		$count++;
		$content .= "</tbody></table>";
		$content .= "</td></tr>";
	}
	$content .= "</tbody></table>\n";

	return array('stat'=>'ok', 'content'=>$content);
}
?>

