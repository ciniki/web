<?php
//
// Description
// -----------
// This function will generate the members page for the business.
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_generatePageMembers($ciniki, $settings) {

	//
	// Check if a file was specified to be downloaded
	//
	$download_err = '';
	if( isset($ciniki['business']['modules']['ciniki.artclub'])
		&& isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] == 'download'
		&& isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] != '' ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'artclub', 'web', 'fileDownload');
		$rc = ciniki_artclub_web_fileDownload($ciniki, $ciniki['request']['business_id'], $ciniki['request']['uri_split'][1]);
		if( $rc['stat'] == 'ok' ) {
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			header("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
			header('Cache-Control: no-cache, must-revalidate');
			header('Pragma: no-cache');
			$file = $rc['file'];
			if( $file['extension'] == 'pdf' ) {
				header('Content-Type: application/pdf');
			}
			header('Content-Disposition: attachment;filename="' . $file['filename'] . '"');
			header('Content-Length: ' . strlen($file['binary_content']));
			header('Cache-Control: max-age=0');

			print $file['binary_content'];
			exit;
		}
		
		//
		// If there was an error locating the files, display generic error
		//
		return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'1055', 'msg'=>'The file you requested does not exist.'));
	}

	//
	// Store the content created by the page
	// Make sure everything gets generated ok before returning the content
	//
	$content = '';
	$page_content = '';
	$page_title = 'Exhibitors';

	//
	// FIXME: Check if anything has changed, and if not load from cache
	//

	//
	// Check if we are to display the gallery image for an members
	//
	//
	// Check if we are to display an image, from the gallery, or latest images
	//
	if( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] != '' 
		&& isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] == 'gallery' 
		&& isset($ciniki['request']['uri_split'][2]) && $ciniki['request']['uri_split'][2] != '' 
		) {
		$member_permalink = $ciniki['request']['uri_split'][0];
		$image_permalink = $ciniki['request']['uri_split'][2];

		//
		// Load the member to get all the details, and the list of images.
		// It's one query, and we can find the requested image, and figure out next
		// and prev from the list of images returned
		//
		ciniki_core_loadMethod($ciniki, 'ciniki', 'artclub', 'web', 'memberDetails');
		$rc = ciniki_artclub_web_memberDetails($ciniki, $settings, 
			$ciniki['request']['business_id'], $member_permalink);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$member = $rc['member'];

		if( !isset($member['images']) || count($member['images']) < 1 ) {
			return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'967', 'msg'=>'Unable to find image'));
		}

		$first = NULL;
		$last = NULL;
		$img = NULL;
		$next = NULL;
		$prev = NULL;
		foreach($member['images'] as $iid => $image) {
			if( $first == NULL ) {
				$first = $image;
			}
			if( $image['permalink'] == $image_permalink ) {
				$img = $image;
			} elseif( $next == NULL && $img != NULL ) {
				$next = $image;
			} elseif( $img == NULL ) {
				$prev = $image;
			}
			$last = $image;
		}

		if( count($member['images']) == 1 ) {
			$prev = NULL;
			$next = NULL;
		} elseif( $prev == NULL ) {
			// The requested image was the first in the list, set previous to last
			$prev = $last;
		} elseif( $next == NULL ) {
			// The requested image was the last in the list, set previous to last
			$next = $first;
		}
		
		$page_title = $member['name'] . ' - ' . $img['title'];
	
		//
		// Load the image
		//
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
		$rc = ciniki_web_getScaledImageURL($ciniki, $img['image_id'], 'original', 0, 600);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$img_url = $rc['url'];

		//
		// Set the page to wide if possible
		//
		$ciniki['request']['page-container-class'] = 'page-container-wide';

		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generateGalleryJavascript');
		$rc = ciniki_web_generateGalleryJavascript($ciniki, $next, $prev);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$ciniki['request']['inline_javascript'] = $rc['javascript'];

		$ciniki['request']['onresize'] = "gallery_resize_arrows();";
		$ciniki['request']['onload'] = "scrollto_header();";
		$page_content .= "<article class='page'>\n"
			. "<header class='entry-title'><h1 id='entry-title' class='entry-title'>$page_title</h1></header>\n"
			. "<div class='entry-content'>\n"
			. "";
		$page_content .= "<div id='gallery-image' class='gallery-image'>";
		$page_content .= "<div id='gallery-image-wrap' class='gallery-image-wrap'>";
		if( $prev != null ) {
			$page_content .= "<a id='gallery-image-prev' class='gallery-image-prev' href='" . $prev['permalink'] . "'><div id='gallery-image-prev-img'></div></a>";
		}
		if( $next != null ) {
			$page_content .= "<a id='gallery-image-next' class='gallery-image-next' href='" . $next['permalink'] . "'><div id='gallery-image-next-img'></div></a>";
		}
		$page_content .= "<img id='gallery-image-img' title='" . $img['title'] . "' alt='" . $img['title'] . "' src='" . $img_url . "' onload='javascript: gallery_resize_arrows();' />";
		$page_content .= "</div><br/>"
			. "<div id='gallery-image-details' class='gallery-image-details'>"
			. "<span class='image-title'>" . $img['title'] . '</span>'
			. "<span class='image-details'></span>";
		if( $img['description'] != '' ) {
			$page_content .= "<span class='image-description'>" . preg_replace('/\n/', '<br/>', $img['description']) . "</span>";
		}
		$page_content .= "</div></div>";
		$page_content .= "</div></article>";
	}

	//
	// Check if we are to display an member
	//
	elseif( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] != '' ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'artclub', 'web', 'memberDetails');
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processURL');

		//
		// Get the member information
		//
		$member_permalink = $ciniki['request']['uri_split'][0];
		$rc = ciniki_artclub_web_memberDetails($ciniki, $settings, 
			$ciniki['request']['business_id'], $member_permalink);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$member = $rc['member'];
		$page_title = $member['name'];
		$page_content .= "<article class='page'>\n"
			. "<header class='entry-title'><h1 class='entry-title'>" . $member['name'] . "</h1></header>\n"
			. "";

		//
		// Add primary image
		//
		if( isset($member['image_id']) && $member['image_id'] > 0 ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
			$rc = ciniki_web_getScaledImageURL($ciniki, $member['image_id'], 'original', '500', 0);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= "<aside><div class='image-wrap'><div class='image'>"
				. "<img title='' alt='" . $member['name'] . "' src='" . $rc['url'] . "' />"
				. "</div></div></aside>";
		}
		
		//
		// Add description
		//
		if( isset($member['description']) ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
			$rc = ciniki_web_processContent($ciniki, $member['description']);	
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= $rc['content'];
		}

		if( isset($member['url']) ) {
			$rc = ciniki_web_processURL($ciniki, $member['url']);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$url = $rc['url'];
			$display_url = $rc['display'];
		} else {
			$url = '';
		}

		if( $url != '' ) {
			$page_content .= "<br/>Website: <a class='members-url' target='_blank' href='" . $url . "' title='" . $member['name'] . "'>" . $display_url . "</a>";
		}
		$page_content .= "</article>";

		if( isset($member['images']) && count($member['images']) > 0 ) {
			$page_content .= "<article class='page'>"	
				. "<header class='entry-title'><h1 class='entry-title'>Gallery</h1></header>\n"
				. "";
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageGalleryThumbnails');
			$img_base_url = $ciniki['request']['base_url'] . "/members/" . $member['permalink'] . "/gallery";
			$rc = ciniki_web_generatePageGalleryThumbnails($ciniki, $settings, $img_base_url, $member['images'], 125);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= "<div class='image-gallery'>" . $rc['content'] . "</div>";
			$page_content .= "</article>";
		}
	}

	//
	// Display the list of members if a specific one isn't selected
	//
	else {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
		ciniki_core_loadMethod($ciniki, 'ciniki', 'artclub', 'web', 'memberList');
		$rc = ciniki_artclub_web_memberList($ciniki, $settings, $ciniki['request']['business_id']);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$members = $rc['members'];

		$page_content .= "<article class='page'>\n"
			. "<header class='entry-title'><h1 class='entry-title'>Members</h1></header>\n"
			. "<div class='entry-content'>\n"
			. "";

		if( count($members) > 0 ) {
			$page_content .= "<table class='members-list'><tbody><tr><th></th><td>\n"
				. "";
			$prev_category = NULL;
			$page_content .= "<table class='members-category-list'><tbody>\n";
			foreach($members as $mnum => $member) {
				$member = $member['member'];
				$member_url = $ciniki['request']['base_url'] . "/members/" . $member['permalink'];

				// Setup the member image
				$page_content .= "<tr><td class='members-image' rowspan='3'>";
				if( isset($member['image_id']) && $member['image_id'] > 0 ) {
					ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
					$rc = ciniki_web_getScaledImageURL($ciniki, $member['image_id'], 'thumbnail', '150', 0);
					if( $rc['stat'] != 'ok' ) {
						return $rc;
					}
					$page_content .= "<div class='image-members-thumbnail'>"
						. "<a href='$member_url' title='" . $member['name'] . "'><img title='' alt='" . $member['name'] . "' src='" . $rc['url'] . "' /></a>"
						. "</div></aside>";
				}
				$page_content .= "</td>";

				// Setup the details
				$page_content .= "<td class='members-details'>";
				$page_content .= "<span class='members-title'>";
				$page_content .= "<a href='$member_url' title='" . $member['name'] . "'>" . $member['name'] . "</a>";
				$page_content .= "</span>";
				$page_content .= "</td></tr>";
				$page_content .= "<tr><td class='members-description'>";
				if( isset($member['description']) && $member['description'] != '' ) {
					$rc = ciniki_web_processContent($ciniki, $member['description'], 'members-description');
					if( $rc['stat'] == 'ok' ) {
						$page_content .= $rc['content'];
					}
					// $page_content .= "<span class='members-description'>" . $member['description'] . "</span>";
				}
				$page_content .= "</td></tr>";
				$page_content .= "<tr><td class='members-more'><a href='$member_url'>... more</a></td></tr>";
			}
			$page_content .= "</tbody></table>";

			$page_content .= "</td></tr>\n</tbody></table>\n";
		} else {
			$page_content .= "<p>Currently no members.</p>";
		}

		$page_content .= "</div>\n"
			. "</article>\n"
			. "";
		
		//
		// Check if membership info should be displayed here
		//
		if( isset($settings['page-members-membership-details']) && $settings['page-members-membership-details'] == 'yes' ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'artclub', 'web', 'membershipDetails');
			$rc = ciniki_artclub_web_membershipDetails($ciniki, $settings, $ciniki['request']['business_id']);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$membership = $rc['membership'];
			if( $membership['details'] != '' ) {
				$page_content .= "<article class='page'>\n"
					. "<header class='entry-title'><h1 class='entry-title'>Membership</h1></header>\n"
					. "<div class='entry-content'>\n"
					. "";
				$rc = ciniki_web_processContent($ciniki, $membership['details']);	
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				$page_content .= $rc['content'];

				foreach($membership['files'] as $fid => $file) {
					$file = $file['file'];
					$url = $ciniki['request']['base_url'] . '/members/download/' . $file['permalink'] . '.' . $file['extension'];
					$page_content .= "<p><a target='_blank' href='" . $url . "' title='" . $file['name'] . "'>" . $file['name'] . "</a></p>";
				}

				$page_content .= "</div>\n"
					. "</article>";
			}
		}
	}

	//
	// Generate the complete page
	//

	//
	// Add the header
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageHeader');
	$rc = ciniki_web_generatePageHeader($ciniki, $settings, $page_title, array());
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
