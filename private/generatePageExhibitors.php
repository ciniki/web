<?php
//
// Description
// -----------
// This function will generate the exhibitors page for the business.
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_generatePageExhibitors($ciniki, $settings) {

	//
	// Store the content created by the page
	// Make sure everything gets generated ok before returning the content
	//
	$content = '';
	$page_content = '';

	//
	// FIXME: Check if anything has changed, and if not load from cache
	//


	//
	// Check if we are to display an exhibitor
	//
	if( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] != '' ) {
		//
		// Get the exhibitor information
		//
		$exhibitor_permalink = $ciniki['request']['uri_split'][0];
		ciniki_core_loadMethod($ciniki, 'ciniki', 'exhibitions', 'web', 'participantDetails');
		$rc = ciniki_exhibitions_web_participantDetails($ciniki, $settings, 
			$ciniki['request']['business_id'], 
			$settings['page-exhibitions-exhibition'], $exhibitor_permalink);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$participant = $rc['participant'];
		$page_content .= "<article class='page'>\n"
			. "<header class='entry-title'><h1 class='entry-title'>" . $participant['name'] . "</h1></header>\n"
			. "";

		//
		// Add primary image
		//
		if( isset($participant['image_id']) && $participant['image_id'] > 0 ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
			$rc = ciniki_web_getScaledImageURL($ciniki, $participant['image_id'], 'original', '500', 0);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= "<aside><div class='image'>"
				. "<img title='' alt='" . $participant['name'] . "' src='" . $rc['url'] . "' />"
				. "</div></aside>";
		}
		
		//
		// Add description
		//
		if( isset($participant['description']) ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
			$rc = ciniki_web_processContent($ciniki, $participant['description']);	
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= $rc['content'];
		}
		
		if( isset($participant['url']) ) {
			$url = $participant['url'];
		} else {
			$url = '';
		}
		if( $url != '' && !preg_match('/^\s*http/i', $url) ) {
			$display_url = $url;
			$url = "http://" . $url;
		} else {
			$display_url = preg_replace('/^\s*http:\/\//i', '', $url);
			$display_url = preg_replace('/\/$/i', '', $display_url);
		}

		if( $url != '' ) {
			$content .= "<br/><a class='participant-url' target='_blank' href='" . $url . "' title='" . $participant['name'] . "'>" . $display_url . "</a>";
		}

		//
		// FIXME: Add thumbnails for additional images
		//

		$page_content .= "</article>";
	}

	//
	// Display the list of exhibitors if a specific one isn't selected
	//
	else {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'exhibitions', 'web', 'participantList');
		$rc = ciniki_exhibitions_web_participantList($ciniki, $settings, $ciniki['request']['business_id'], $settings['page-exhibitions-exhibition'], 'exhibitor');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$participants = $rc['categories'];

		$page_content .= "<article class='page'>\n"
			. "<header class='entry-title'><h1 class='entry-title'>Exhibitors</h1></header>\n"
			. "<div class='entry-content'>\n"
			. "";

		if( count($participants) > 0 ) {
			$page_content = "<table class='exhibitors-list'><tbody>\n"
				. "";
			$prev_category = NULL;
			foreach($participants as $cnum => $c) {
				if( $prev_category != NULL ) {
					$page_content .= "</td></tr>\n";
				}
				if( isset($c['category']['name']) && $c['category']['name'] != '' ) {
					$page_content .= "<tr><th>"
						. "<span class='exhibitors-category'>" . $c['category']['name'] . "</span></th>"
						. "<td>";
				} else {
					$page_content .= "<tr><th>"
						. "<span class='exhibitors-category'></span></th>"
						. "<td>";
				}
				$page_content .= "<table class='exhibitors-category-list'><tbody>\n";
				foreach($c['category']['participants'] as $pnum => $participant) {
					$participant = $participant['participant'];

					// Setup the exhibitor image
					$page_content .= "<tr><td class='exhibitors-image' rowspan='3'>";
					if( isset($participant['image_id']) && $participant['image_id'] > 0 ) {
						ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
						$rc = ciniki_web_getScaledImageURL($ciniki, $participant['image_id'], 'thumbnail', '150', 0);
						if( $rc['stat'] != 'ok' ) {
							return $rc;
						}
						$page_content .= "<div class='image-exhibitors-thumbnail'>"
							. "<img title='' alt='" . $participant['name'] . "' src='" . $rc['url'] . "' />"
							. "</div></aside>";
					}
					$page_content .= "</td>";

					// Setup the details
					$page_content .= "<td class='exhibitors-details'>";
					$page_content .= "<span class='exhibitors-title'>";
					$page_content .= "<a target='_blank' href='" . $ciniki['request']['base_url'] . "/exhibitors/" . $participant['permalink'] . "' title='" . $participant['name'] . "'>" . $participant['name'] . "</a>";
					$page_content .= "</span>";
					$page_content .= "</td></tr>";
					$page_content .= "<tr><td class='exhibitors-description'>";
					if( isset($participant['description']) && $participant['description'] != '' ) {
						$page_content .= "<span class='exhibitors-description'>" . $participant['description'] . "</span>";
					}
					$page_content .= "</td></tr>";
					$page_content .= "<tr><td class='exhibitors-more'><a href='" . $ciniki['request']['base_url'] . "/exhibitors/" . $participant['permalink'] . "'>... more</a></td></tr>";
				}
				$page_content .= "</tbody></table>";
			}

			$page_content .= "</td></tr>\n</tbody></table>\n";
		} else {
			$page_content .= "<p>Currently no exhibitors for this event.</p>";
		}

		$page_content .= "</div>\n"
			. "</article>\n"
			. "";
	}

	//
	// Generate the complete page
	//

	//
	// Add the header
	//
	require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageHeader.php');
	$rc = ciniki_web_generatePageHeader($ciniki, $settings, 'About');
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
	require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageFooter.php');
	$rc = ciniki_web_generatePageFooter($ciniki, $settings);
	if( $rc['stat'] != 'ok' ) {	
		return $rc;
	}
	$content .= $rc['content'];

	return array('stat'=>'ok', 'content'=>$content);
}
?>
