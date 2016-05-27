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
function ciniki_web_processBlockCIList(&$ciniki, $settings, $business_id, $block) {

	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processURL');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getPaddedImageURL');

	$page_limit = 0;
	if( isset($args['limit']) ) {
		$page_limit = $args['limit'];
	}

	$content = '';

	if( isset($block['title']) && $block['title'] != '' ) {
		$content .= "<h2 class='wide'>" . $block['title'] . "</h2>";
	}

//	print "<pre>";
//	print_r($categories);
//	print "</pre>";

	$content .= "<table class='cilist'><tbody>";
	$count = 0;
	foreach($block['categories'] as $cid => $category) {
		if( $page_limit > 0 && $count >= $page_limit ) { $count++; break; }
		// If no titles, then highlight the title in the category
		if( isset($block['notitle']) && $block['notitle'] == 'yes' ) {
			$title_url = '';
			if( count($category['list']) == 1 ) {
				// Check if category should be linked
				$item = array_slice($category['list'], 0, 1);
				$item = array_pop($item);
				if( isset($item['is_details']) && $item['is_details'] == 'yes' ) {
					$title_url = $block['base_url'] . '/' . $item['permalink'];
				}
			}
			$content .= "\n<tr><th><span class='cilist-title'>" 
				. ($title_url!=''?"<a href='$title_url' title='" . $item['title'] . "'>":'')
				. (isset($category['name'])?$category['name']:'') 
				. ($title_url!=''?'</a>':'')
				. "</span></th><td>\n";
		} else {
			$content .= "\n<tr><th><span class='cilist-category'>" . (isset($category['name'])?$category['name']:'') . "</span>";
			if( isset($category['subname']) && $category['subname'] != '' ) {
				$content .= "<span class='cilist-subcategory'>" . $category['subname'] . "</span>";
			}
			$content .= "</th><td>\n";
		}
		// Start the inner table for the item list
		$content .= "<table class='cilist-categories'><tbody>\n";

		foreach($category['list'] as $iid => $item) {
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
			if( isset($block['notitle']) 
				&& ($block['notitle'] == 'yes' || $block['notitle'] == 'hide')
				) {
				$content .= "<tr><td class='cilist-image' rowspan='2'>";
			} else {
				$content .= "<tr><td class='cilist-image' rowspan='3'>";
			}
			if( isset($item['image_id']) && $item['image_id'] > 0 ) {
                if( isset($block['thumbnail_format']) && $block['thumbnail_format'] == 'square-padded' ) {
                    $version = ((isset($block['image_version'])&&$block['image_version']!='')?$block['image_version']:'thumbnail');
                    $rc = ciniki_web_getPaddedImageURL($ciniki, $item['image_id'], 'original', 
                        ((isset($block['image_width'])&&$block['image_width']!='')?$block['image_width']:'150'), 
                        ((isset($block['image_height'])&&$block['image_height']!='')?$block['image_height']:'0'),
                        ((isset($block['thumbnail_padding_color'])&&$block['thumbnail_padding_color']!='')?$block['thumbnail_padding_color']:'#ffffff') 
                        );
                } else {
                    $version = ((isset($block['image_version'])&&$block['image_version']!='')?$block['image_version']:'thumbnail');
                    $rc = ciniki_web_getScaledImageURL($ciniki, $item['image_id'], $version, 
                        ((isset($block['image_width'])&&$block['image_width']!='')?$block['image_width']:'150'), 
                        ((isset($block['image_height'])&&$block['image_height']!='')?$block['image_height']:'0') 
                        );
                }
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
						. "</div>";
				} else {
					$content .= "<div class='image-cilist-thumbnail'>"
						. "<img title='' alt='" . $item['title'] . "' src='/ciniki-web-layouts/default/img/noimage_240.png' />"
						. "</div>";
				}
			} elseif( isset($block['noimage']) && $block['noimage'] != '' ) {
				if( $url != '' ) {
					$content .= "<div class='image-cilist-thumbnail'>"
						. "<a href='$url' target='$url_target' title='" . $item['title'] . "'>"
						. "<img title='' alt='" . $item['title'] . "' src='" . $block['noimage'] . "' /></a>"
						. "</div>";
				} else {
					$content .= "<div class='image-cilist-thumbnail'>"
						. "<img title='' alt='" . $item['title'] . "' src='" . $block['noimage'] . "' />"
						. "</div>";
				}
			}
			$content .= "</td>";
			
			// Setup the details
			if( isset($block['notitle']) && $block['notitle'] == 'yes' ) {
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
				$rc = ciniki_web_processContent($ciniki, $settings, $item['synopsis'], 'cilist-description');
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
				$rc = ciniki_web_processContent($ciniki, $settings, $item['description'], 'cilist-description');
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
				$rc = ciniki_web_processContent($ciniki, $settings, $item['short_description'], 'cilist-description');
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
	}
	$content .= "</tbody></table>\n";

//	$content .= "<pre>" . print_r($block, true) . "</pre>";

	return array('stat'=>'ok', 'content'=>$content);
}
?>
