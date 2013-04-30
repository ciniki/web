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

	$page_title = "Sponsors";
	if( isset($ciniki['business']['modules']['ciniki.exhibitions']) ) {
		$pkg = 'ciniki';
		$mod = 'exhibitions';
	} elseif( isset($ciniki['business']['modules']['ciniki.artclub']) ) {
		$pkg = 'ciniki';
		$mod = 'artclub';
	} else {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'946', 'msg'=>'No sponsor module enabled'));
	}

	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processURL');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');

	ciniki_core_loadMethod($ciniki, $pkg, $mod, 'web', 'sponsorList');
	$sponsorList = $pkg . '_' . $mod . '_web_sponsorList';
	$rc = $sponsorList($ciniki, $settings, $ciniki['request']['business_id']);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$sponsors = $rc['categories'];

	$page_content .= "<article class='page'>\n"
		. "<header class='entry-title'><h1 class='entry-title'>Sponsors</h1></header>\n"
		. "<div class='entry-content'>\n"
		. "";

	if( count($sponsors) > 0 ) {
		$page_content .= "<table class='sponsors-list'><tbody>\n";
		$prev_category = NULL;
		foreach($sponsors as $cnum => $c) {
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
			$page_content .= "<table class='sponsors-category-list'><tbody>\n";
			foreach($c['category']['sponsors'] as $pnum => $sponsor) {
				$sponsor = $sponsor['sponsor'];
				if( isset($sponsor['url']) ) {
					$rc = ciniki_web_processURL($ciniki, $sponsor['url']);
					if( $rc['stat'] != 'ok' ) {
						return $rc;
					}
					$url = $rc['url'];
					$display_url = $rc['display'];
				} else {
					$url = '';
				}

				// Setup the exhibitor image
				$page_content .= "<tr><td class='sponsors-image' rowspan='3'>";
				if( isset($sponsor['image_id']) && $sponsor['image_id'] > 0 ) {
					$rc = ciniki_web_getScaledImageURL($ciniki, $sponsor['image_id'], 'original', 0, 150);
					if( $rc['stat'] != 'ok' ) {
						return $rc;
					}
					$page_content .= "<div class='image-sponsors-thumbnail'>"
						. "<a target='_blank' href='$url' title='" . $sponsor['name'] . "'><img title='' alt='" . $sponsor['name'] . "' src='" . $rc['url'] . "' /></a>"
						. "</div></aside>";
				}
				$page_content .= "</td>";

				// Setup the details
				$page_content .= "<td class='sponsors-details'>";
				$page_content .= "<span class='sponsors-title'>";
				$page_content .= "<a target='_blank' href='$url' title='" . $sponsor['name'] . "'>" . $sponsor['name'] . "</a>";
				$page_content .= "</span>";
				$page_content .= "</td></tr>";
				$page_content .= "<tr><td class='sponsors-description'>";
				if( isset($sponsor['description']) && $sponsor['description'] != '' ) {
					$page_content .= "<span class='sponsors-description'>" . $sponsor['description'] . "</span>";
				}
				$page_content .= "</td></tr>";
				$page_content .= "<tr><td class='sponsors-more'><a target='_blank' class='external-link' href='$url'>$display_url</a></td></tr>";
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
	$rc = ciniki_web_generatePageHeader($ciniki, $settings, 'Sponsors', array());
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
