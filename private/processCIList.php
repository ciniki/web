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
function ciniki_web_processCIList(&$ciniki, $settings, $base_url, $categories, $pagination) {

	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processURL');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');

	$page_limit = 0;
	if( isset($pagination['limit']) ) {
		$page_limit = $pagination['limit'];
	}

	$content = "<table class='cilist'><tbody>";
	$count = 0;
	foreach($categories as $cid => $category) {
		if( $page_limit > 0 && $count >= $page_limit ) { $count++; break; }

		$content .= "<tr><th>" . (isset($category['name'])?$category['name']:'') . "</th><td>\n";
		$content .= "<table class='cilist-categories'><tbody>\n";

		foreach($category['list'] as $iid => $item) {
			if( $page_limit > 0 && $count >= $page_limit ) { $count++; break; }
			$url = '';
			$url_display = '... more';
			$javascript_onclick = '';
			if( isset($item['is_details']) && $item['is_details'] == 'yes' 
				&& isset($item['permalink']) && $item['permalink'] != '' ) {
				$url = $base_url . "/" . $item['permalink'];
				$javascript_onclick = " onclick='javascript:location.href=\"$url\";' ";
			} elseif( isset($item['url']) && $item['url'] != '' ) {
				$rc = ciniki_web_processURL($ciniki, $item['url']);
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				$url = $rc['url'];
				$url_display = $rc['display'];
			}

			// Setup the item image
			$content .= "<tr><td class='cilist-image' rowspan='3'>";
			if( isset($item['image_id']) && $item['image_id'] > 0 ) {
				$rc = ciniki_web_getScaledImageURL($ciniki, $item['image_id'], 'thumbnail', '150', 0);
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				if( $url != '' ) {
					$content .= "<div class='image-cilist-thumbnail'>"
						. "<a href='$url' title='" . $item['title'] . "'>"
						. "<img title='' alt='" . $item['title'] . "' src='" . $rc['url'] . "' /></a>"
						. "</div></aside>";
				} else {
					$content .= "<div class='image-cilist-thumbnail'>"
						. "<img title='' alt='" . $item['title'] . "' src='" . $rc['url'] . "' />"
						. "</div></aside>";
				}
			} elseif( isset($category['noimage']) && $category['noimage'] != '' ) {
				if( $url != '' ) {
					$content .= "<div class='image-cilist-thumbnail'>"
						. "<a href='$url' title='" . $item['title'] . "'>"
						. "<img title='' alt='" . $item['title'] . "' src='" . $category['noimage'] . "' /></a>"
						. "</div></aside>";
				} else {
					$content .= "<div class='image-cilist-thumbnail'>"
						. "<img title='' alt='" . $item['title'] . "' src='" . $category['noimage'] . "' />"
						. "</div></aside>";
				}
			}
			$content .= "</td>";
			
			// Setup the details
			$content .= "<td class='cilist-title'>";
			$content .= "<p class='cilist-title'>";
			if( $url != '' ) {
				$content .= "<a href='$url' title='" . $item['title'] . "'>" . $item['title'] . "</a>";
			} else {
				$content .= $item['title'];
			}
			$content .= "</p>";
			$content .= "</td></tr>";
			$content .= "<tr><td $javascript_onclick class='cilist-details'>";

			if( isset($item['description']) && $item['description'] != '' ) {
				$rc = ciniki_web_processContent($ciniki, $item['description'], 'cilist-description');
				if( $rc['stat'] == 'ok' ) {
					$content .= $rc['content'];
				}
			} elseif( isset($item['short_description']) && $item['short_description'] != '' ) {
				$rc = ciniki_web_processContent($ciniki, $item['short_description'], 'cilist-description');
				if( $rc['stat'] == 'ok' ) {
					$content .= $rc['content'];
				}
			}
		
			if( $url != '' ) {
				$content .= "<tr><td class='cilist-more'><a href='$url'>$url_display</a></td></tr>";
			}
			$count++;
		}
		$content .= "</tbody></table>";
		$content .= "</td></tr>";
	}
	$content .= "</tbody></table>\n";

	//
	// Check if we need prev and next buttons
	//
	$nav_content = '';
	if( $page_limit > 0 && isset($pagination['base_url']) && $pagination['base_url'] != '' ) {
		$prev = '';
		if( isset($pagination['page']) && $pagination['page'] > 1 ) {
			if( isset($pagination['base_url']) ) {
				$prev .= "<a href='" . $pagination['base_url'] . "?page=" . ($pagination['page']-1) . "'>";
				array_push($ciniki['response']['head']['links'], array('rel'=>'prev', 'href'=>$pagination['base_url'] . "?page=" . ($pagination['page']-1)));
				if( isset($pagination['prev']) && $pagination['prev'] != '' ) {
					$prev .= $pagination['prev'];
				} else {
					$prev .= 'Prev';
				}
				$prev .= "</a>";
			}
		}
		$next = '';
		if( $count > $page_limit ) {
			if( isset($pagination['base_url']) ) {
				$next .= "<a href='" . $pagination['base_url'] . "?page=" . ($pagination['page']+1) . "'>";
				array_push($ciniki['response']['head']['links'], array('rel'=>'next', 'href'=>$pagination['base_url'] . "?page=" . ($pagination['page']+1)));
				if( isset($pagination['prev']) && $pagination['prev'] != '' ) {
					$next .= $pagination['next'];
				} else {
					$next .= 'Next';
				}
				$next .= "</a>";
			}
		}
		if( $next != '' || $prev != '' ) {
			$nav_content = "<nav class='content-nav'>"
				. "<span class='prev'>$prev</span>"
				. "<span class='next'>$next</span>"
				. "</nav>"
				. "";
		}
	}

	return array('stat'=>'ok', 'content'=>$content, 'nav'=>$nav_content);
}
?>

