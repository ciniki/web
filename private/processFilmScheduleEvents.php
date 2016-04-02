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
function ciniki_web_processFilmScheduleEvents($ciniki, $settings, $events, $limit) {

	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processURL');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');

	$content = "<table class='cilist'><tbody>";
	$count = 0;
	foreach($events as $event_num => $event) {
		if( $limit > 0 && $count >= $limit ) { break; }
		$event_date = $event['start_month'];
		$event_date .= " " . $event['start_day'];
		$event_date .= ", " . $event['start_year'];

		$javascript_onclick = '';
		$url_target = '';
		$event_url = $ciniki['request']['base_url'] . "/schedule/" . $event['permalink'];
		$javascript_onclick = " onclick='javascript:location.href=\"$event_url\";' ";

		$content .= "<tr><th><span class='cilist-category'>$event_date</span>";
		if( isset($event['start_time']) && $event['start_time'] != '' ) {
			$content .= "<span class='cilist-subcategory'>" . $event['start_time'] . "</span>";
		}
		$content .= "</th><td>\n";
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
					. "<a href='$event_url' " . ($url_target!=''?'target="$url_target" ':'') . "title='" . $event['name'] . "'><img title='' alt='" . $event['name'] . "' src='" . $rc['url'] . "' /></a>"
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
		if( $event_url != '' ) {
			$content .= "<a href='$event_url' " . ($url_target!=''?'target="$url_target" ':'') . "title='" . $event['name'] . "'>" . $event['name'] . "</a>";
		} else {
			$content .= $event['name'];
		}
		$content .= "</p>";
		$content .= "</td></tr>";
		$content .= "<tr><td $javascript_onclick class='cilist-details" . ($javascript_onclick!=''?' clickable':'') . "'>";
		if( isset($event['synopsis']) && $event['synopsis'] != '' ) {
			$rc = ciniki_web_processContent($ciniki, $settings, $event['synopsis'], 'cilist-description');
			if( $rc['stat'] == 'ok' ) {
				$content .= $rc['content'];
			}
		} elseif( isset($event['description']) && $event['description'] != '' ) {
			$rc = ciniki_web_processContent($ciniki, $settings, $event['description'], 'cilist-description');
			if( $rc['stat'] == 'ok' ) {
				$content .= $rc['content'];
			}
			// $content .= "<p class='cilist-description'>" . $event['description'] . "</p>";
		}
		$content .= "<tr><td class='cilist-more'><a href='$event_url'>... more</a></td></tr>";
		$count++;
		$content .= "</tbody></table>";
		$content .= "</td></tr>";
	}
	$content .= "</tbody></table>\n";

	return array('stat'=>'ok', 'content'=>$content);
}
?>
