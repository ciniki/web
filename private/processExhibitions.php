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
function ciniki_web_processExhibitions($ciniki, $settings, $exhibitions, $limit) {

//	$content = "<table class='exhibitors-list'>\n"
	$content = "<table class='cilist'>\n"
		. "";
	$count = 0;
	foreach($exhibitions as $eid => $e) {
		if( $limit > 0 && $count >= $limit ) { break; }
		$exhibition = $e['exhibition'];
		// Display the date
		$exhibition_date = $exhibition['start_month'];
		$exhibition_date .= " " . $exhibition['start_day'];
		if( $exhibition['end_day'] != '' && ($exhibition['start_day'] != $exhibition['end_day'] || $exhibition['start_month'] != $exhibition['end_month']) ) {
			if( $exhibition['end_month'] != '' && $exhibition['end_month'] == $exhibition['start_month'] ) {
				$exhibition_date .= " - " . $exhibition['end_day'];
			} else {
				$exhibition_date .= " - " . $exhibition['end_month'] . " " . $exhibition['end_day'];
			}
		}
		$exhibition_date .= ", " . $exhibition['start_year'];
		if( $exhibition['end_year'] != '' && $exhibition['start_year'] != $exhibition['end_year'] ) {
			$exhibition_date .= "/" . $exhibition['end_year'];
		}
		$content .= "<tr><th><span class='cilist-category'>$exhibition_date</span>";
		if( $exhibition['location'] != '' ) {
			$content .= " <span class='cilist-subcategory'>" . $exhibition['location'] . "</span>";
		}
		$content .= "</th>"
			. "<td>";
		// Display the brief details
		$content .= "<table class='cilist-categories'><tbody>\n";
		$exhibition_url = $ciniki['request']['base_url'] . "/exhibitions/" . $exhibition['permalink'];

		// Setup the exhibitor image
		$content .= "<tr><td class='cilist-image' rowspan='3'>";
		if( isset($exhibition['image_id']) && $exhibition['image_id'] > 0 ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
			$rc = ciniki_web_getScaledImageURL($ciniki, $exhibition['image_id'], 'thumbnail', '150', 0);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$content .= "<div class='image-cilist-thumbnail'>"
				. "<a href='$exhibition_url' title='" . $exhibition['name'] . "'><img title='' alt='" . $exhibition['name'] . "' src='" . $rc['url'] . "' /></a>"
				. "</div></aside>";
		}
		$content .= "</td>";

		// Setup the details
		$content .= "<td class='cilist-details'>";
		$content .= "<p class='cilist-title'>";
		$content .= "<a href='$exhibition_url' title='" . $exhibition['name'] . "'>" . $exhibition['name'] . "</a>";
		$content .= "</p>";
		$content .= "</td></tr>";
		$content .= "<tr><td class='cilist-description'>";
		if( isset($exhibition['description']) && $exhibition['description'] != '' ) {
			$content .= "<span class='cilist-description'>" . $exhibition['description'] . "</span>";
		}
		$content .= "</td></tr>";
		$content .= "<tr><td class='cilist-more'><a href='$exhibition_url'>... more</a></td></tr>";
		$content .= "</tbody></table>";
		$content .= "</td></tr>";
		$count++;
	}
	$content .= "</table>\n"
		. "";

	return array('stat'=>'ok', 'content'=>$content);
}
?>
