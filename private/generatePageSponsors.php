<?php
//
// Description
// -----------
// This function will generate the exhibition sponsors page for the business.
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_generatePageSponsors($ciniki, $settings) {

	//
	// Store the content created by the page
	// Make sure everything gets generated ok before returning the content
	//
	$content = '';
	$page_content = '';

	//
	// FIXME: Check if anything has changed, and if not load from cache
	//

	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processURL');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');

	ciniki_core_loadMethod($ciniki, 'ciniki', 'exhibitions', 'web', 'participantList');
	$rc = ciniki_exhibitions_web_participantList($ciniki, $settings, $ciniki['request']['business_id'], $settings['page-exhibitions-exhibition'], 'sponsor');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$participants = $rc['categories'];

	$page_content .= "<article class='page'>\n"
		. "<header class='entry-title'><h1 class='entry-title'>Sponsors</h1></header>\n"
		. "<div class='entry-content'>\n"
		. "";

	if( count($participants) > 0 ) {
		$page_content .= "<table class='exhibitors-list'><tbody><tr><td>\n";
		foreach($participants as $cnum => $c) {
			$page_content .= "<table class='exhibitors-category-list'><tbody>\n";
			foreach($c['category']['participants'] as $pnum => $participant) {
				$participant = $participant['participant'];
				if( isset($participant['url']) ) {
					$rc = ciniki_web_processURL($ciniki, $participant['url']);
					if( $rc['stat'] != 'ok' ) {
						return $rc;
					}
					$url = $rc['url'];
					$display_url = $rc['display'];
				} else {
					$url = '';
				}

				// Setup the exhibitor image
				$page_content .= "<tr><td class='exhibitors-image' rowspan='3'>";
				if( isset($participant['image_id']) && $participant['image_id'] > 0 ) {
					$rc = ciniki_web_getScaledImageURL($ciniki, $participant['image_id'], 'original', 0, 150);
					if( $rc['stat'] != 'ok' ) {
						return $rc;
					}
					$page_content .= "<div class='image-exhibitors-thumbnail'>"
						. "<a target='_blank' href='$url' title='" . $participant['name'] . "'><img title='' alt='" . $participant['name'] . "' src='" . $rc['url'] . "' /></a>"
						. "</div></aside>";
				}
				$page_content .= "</td>";

				// Setup the details
				$page_content .= "<td class='exhibitors-details'>";
				$page_content .= "<span class='exhibitors-title'>";
				$page_content .= "<a target='_blank' href='$url' title='" . $participant['name'] . "'>" . $participant['name'] . "</a>";
				$page_content .= "</span>";
				$page_content .= "</td></tr>";
				$page_content .= "<tr><td class='exhibitors-description'>";
				if( isset($participant['description']) && $participant['description'] != '' ) {
					$page_content .= "<span class='exhibitors-description'>" . $participant['description'] . "</span>";
				}
				$page_content .= "</td></tr>";
				$page_content .= "<tr><td class='exhibitors-more'><a target='_blank' class='external-link' href='$url'>$display_url</a></td></tr>";
			}
			$page_content .= "</tbody></table>";
		}

		$page_content .= "</td></tr>\n</tbody></table>\n";
	} else {
		$page_content .= "<p>Currently no sponsors for this event.</p>";
	}

	$page_content .= "</div>\n"
		. "</article>\n"
		. "";

	//
	// Generate the complete page
	//

	//
	// Add the header
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageHeader');
	$rc = ciniki_web_generatePageHeader($ciniki, $settings, 'Sponsors');
	if( $rc['stat'] != 'ok' ) {	
		return $rc;
	}
	$content .= $rc['content'];

	$content .= "<div id='content'>\n"
		. $page_content
		. "</div>"
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
