<?php
//
// Description
// -----------
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_processBlockImageList(&$ciniki, $settings, $business_id, $block) {

	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processURL');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');

	$page_limit = 0;
	if( isset($args['limit']) ) {
		$page_limit = $args['limit'];
	}

//	print "<pre>";
//	print_r($categories);
//	print "</pre>";

	$content = "<table class='cilist'><tbody>";
	$count = 0;
	$content .= "<tr><th><span class='cilist-category'></span></th><td>";

	// Start the inner table for the item list
	$content .= "<table class='cilist-categories'><tbody>\n";

	foreach($block['list'] as $iid => $item) {
		if( $page_limit > 0 && $count >= $page_limit ) { $count++; break; }
		$url = '';
		$url_display = '... more';
		$url_target = '';
		$javascript_onclick = '';
		if( isset($item['is_details']) && $item['is_details'] == 'yes' 
			&& isset($item['permalink']) && $item['permalink'] != '' ) {
			$url = $block['base_url'] . "/" . $item['permalink'];
			$javascript_onclick = " onclick='javascript:location.href=\"$url\";' ";
		} elseif( isset($item['url']) && $item['url'] != '' ) {
			$rc = ciniki_web_processURL($ciniki, $item['url']);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$url = $rc['url'];
			$url_target = '_blank';
			$url_display = $rc['display'];
		}

		// Setup the item image
		if( isset($args['notitle']) 
			&& ($args['notitle'] == 'yes' || $args['notitle'] == 'hide')
			) {
			$content .= "<tr><td class='cilist-image' rowspan='2'>";
		} else {
			$content .= "<tr><td class='cilist-image' rowspan='3'>";
		}
		if( isset($item['image_id']) && $item['image_id'] > 0 ) {
			$version = ((isset($args['image_version'])&&$args['image_version']!='')?$args['image_version']:'thumbnail');
			$rc = ciniki_web_getScaledImageURL($ciniki, $item['image_id'], $version, 
				((isset($args['image_width'])&&$args['image_width']!='')?$args['image_width']:'150'), 
				((isset($args['image_height'])&&$args['image_height']!='')?$args['image_height']:'0') 
				);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			if( $url != '' ) {
				$content .= "<div class='image-cilist-$version'>"
					. "<a href='$url' target='$url_target' title='" . $item['title'] . "'>"
					. "<img title='' alt='" . $item['title'] . "' src='" . $rc['url'] . "' /></a>"
					. "</div>";
			} else {
				$content .= "<div class='image-cilist-$version'>"
					. "<img title='' alt='" . $item['title'] . "' src='" . $rc['url'] . "' />"
					. "</div>";
			}
		} elseif( isset($block['noimage']) && $block['noimage'] == 'yes' ) {
			if( $url != '' ) {
				$content .= "<div class='image-cilist-thumbnail'>"
					. "<a href='$url' target='$url_target' title='" . $item['title'] . "'>"
					. "<img title='' alt='" . $item['title'] . "' src='/ciniki-web-layouts/default/img/noimage_240.png' /></a>"
					. "</div></aside>";
			} else {
				$content .= "<div class='image-cilist-thumbnail'>"
					. "<img title='' alt='" . $item['title'] . "' src='/ciniki-web-layouts/default/img/noimage_240.png' />"
					. "</div></aside>";
			}
		} elseif( isset($block['noimage']) && $block['noimage'] != '' ) {
			if( $url != '' ) {
				$content .= "<div class='image-cilist-thumbnail'>"
					. "<a href='$url' target='$url_target' title='" . $item['title'] . "'>"
					. "<img title='' alt='" . $item['title'] . "' src='" . $block['noimage'] . "' /></a>"
					. "</div></aside>";
			} else {
				$content .= "<div class='image-cilist-thumbnail'>"
					. "<img title='' alt='" . $item['title'] . "' src='" . $block['noimage'] . "' />"
					. "</div></aside>";
			}
		}
		$content .= "</td>";
		
		// Setup the details
		if( isset($args['notitle']) && $args['notitle'] == 'yes' ) {
			$content .= "";
		} else {
			$content .= "<td class='cilist-title'>";
			$content .= "<p class='cilist-title'>";
			if( $url != '' ) {
				$content .= "<a href='$url' target='$url_target' title='" . $item['title'] . "'>" . $item['title'] . "</a>";
			} else {
				$content .= $item['title'];
			}
			$content .= "</p>";
			$content .= "</td></tr>";
			$content .= "<tr>";
		}
		$content .= "<td $javascript_onclick class='cilist-details" . ($javascript_onclick!=''?' clickable':'') . "'>";

		if( isset($item['synopsis']) && $item['synopsis'] != '' ) {
			$rc = ciniki_web_processContent($ciniki, $item['synopsis'], 'cilist-description');
			if( $rc['stat'] == 'ok' ) {
				$content .= $rc['content'];
			}
			//
			// Check for files
			//
			if( isset($args['child_files'][$iid]['files']) ) {
				foreach($args['child_files'][$iid]['files'] as $file_id => $file) {
					$file_url = $block['base_url'] . '/' . $item['permalink'] . '/download/' . $file['permalink'] . '.' . $file['extension'];
					$content .= "<p><a target='_blank' href='" . $file_url . "' title='" . $file['name'] . "'>" . $file['name'] . "</a></p>";
				}
			}
		} elseif( isset($item['description']) && $item['description'] != '' ) {
			$rc = ciniki_web_processContent($ciniki, $item['description'], 'cilist-description');
			if( $rc['stat'] == 'ok' ) {
				$content .= $rc['content'];
			}
			//
			// Check for files
			//
			if( isset($args['child_files'][$iid]['files']) ) {
				foreach($args['child_files'][$iid]['files'] as $file_id => $file) {
					$file_url = $block['base_url'] . '/' . $item['permalink'] . '/download/' . $file['permalink'] . '.' . $file['extension'];
					$content .= "<p><a target='_blank' href='" . $file_url . "' title='" . $file['name'] . "'>" . $file['name'] . "</a></p>";
				}
			}
		} elseif( isset($item['short_description']) && $item['short_description'] != '' ) {
			$rc = ciniki_web_processContent($ciniki, $item['short_description'], 'cilist-description');
			if( $rc['stat'] == 'ok' ) {
				$content .= $rc['content'];
			}
	
		} else {
			$content .= "<br/>";
		}
		$content .= "</tr>";

		if( $url != '' ) {
			$content .= "<tr><td class='cilist-more'><a href='$url' target='$url_target'>$url_display</a></td></tr>";
		} elseif( isset($item['urls']) && count($item['urls']) > 0 ) {
			$content .= "<tr><td class='cilist-more'>";
			$urls = '';
			foreach($item['urls'] as $url) {
				$rc = ciniki_web_processURL($ciniki, $url);
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				if( $rc['url'] != '' ) {
					$urls .= ($urls!='')?'<br/>':'';
					if( isset($url['title']) && $url['title'] != '' ) {
						$urls .= "<a href='" . $rc['url'] . "' target='_blank'>" . $url['title'] . "</a>";
					} else {
						$urls .= "<a href='" . $rc['url'] . "' target='_blank'>" . $rc['display'] . "</a>";
					}
				}
				$url = $rc['url'];
				$url_display = $rc['display'];
			}
			$content .= $urls . "</td></tr>";
		} else {
			$content .= "<tr><td class='cilist-more'></td></tr>";
			
		}
		$count++;
	}
	$content .= "</tbody></table>";
	$content .= "</td></tr>";
	$content .= "</tbody></table>\n";

	return array('stat'=>'ok', 'content'=>$content);
}
?>
