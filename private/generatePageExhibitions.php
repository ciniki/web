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
function ciniki_web_generatePageExhibitions($ciniki, $settings) {

	//
	// Check if a file was specified to be downloaded
	//
	$download_err = '';
	if( isset($ciniki['business']['modules']['ciniki.artgallery'])
		&& isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] == 'download'
		&& isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] != '' ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'artgallery', 'web', 'fileDownload');
		$rc = ciniki_artgallery_web_fileDownload($ciniki, $ciniki['request']['business_id'], $ciniki['request']['uri_split'][1]);
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
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1116', 'msg'=>'Unable to locate file'));
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
	// Check if we are to display the application
	//
	if( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] == 'application' 
//		&& isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] == 'application'  
		&& isset($settings['page-artgalleryexhibitions-application-details']) && $settings['page-artgalleryexhibitions-application-details'] == 'yes'
		) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
		ciniki_core_loadMethod($ciniki, 'ciniki', 'artgallery', 'web', 'exhibitionApplicationDetails');
		$rc = ciniki_artgallery_web_exhibitionApplicationDetails($ciniki, $settings, $ciniki['request']['business_id']);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$application = $rc['application'];
		if( $application['details'] != '' ) {
			$page_content .= "<article class='page'>\n"
				. "<header class='entry-title'><h1 class='entry-title'>Exhibitor Application</h1></header>\n"
				. "<div class='entry-content'>\n"
				. "";
			$rc = ciniki_web_processContent($ciniki, $application['details']);	
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= $rc['content'];

			foreach($application['files'] as $fid => $file) {
				$file = $file['file'];
				$url = $ciniki['request']['base_url'] . '/exhibitions/download/' . $file['permalink'] . '.' . $file['extension'];
				$page_content .= "<p><a target='_blank' href='" . $url . "' title='" . $file['name'] . "'>" . $file['name'] . "</a></p>";
			}

			$page_content .= "</div>\n"
				. "</article>";
		}
	}
	//
	// Check if we are to display an image, from the gallery, or latest images
	//
	elseif( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] != '' 
		&& isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] == 'gallery' 
		&& isset($ciniki['request']['uri_split'][2]) && $ciniki['request']['uri_split'][2] != '' 
		) {
		$exhibition_permalink = $ciniki['request']['uri_split'][0];
		$image_permalink = $ciniki['request']['uri_split'][2];

		//
		// Load the exhibition to get all the details, and the list of images.
		// It's one query, and we can find the requested image, and figure out next
		// and prev from the list of images returned
		//
		ciniki_core_loadMethod($ciniki, 'ciniki', 'artgallery', 'web', 'exhibitionDetails');
		$rc = ciniki_artgallery_web_exhibitionDetails($ciniki, $settings, 
			$ciniki['request']['business_id'], $exhibition_permalink);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$exhibition = $rc['exhibition'];

		if( !isset($exhibition['images']) || count($exhibition['images']) < 1 ) {
			return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1132', 'msg'=>'Unable to find image'));
		}

		$first = NULL;
		$last = NULL;
		$img = NULL;
		$next = NULL;
		$prev = NULL;
		foreach($exhibition['images'] as $iid => $image) {
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

		if( count($exhibition['images']) == 1 ) {
			$prev = NULL;
			$next = NULL;
		} elseif( $prev == NULL ) {
			// The requested image was the first in the list, set previous to last
			$prev = $last;
		} elseif( $next == NULL ) {
			// The requested image was the last in the list, set previous to last
			$next = $first;
		}
		
		$page_title = $exhibition['name'] . ' - ' . $img['title'];
	
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
	// Check if we are to display an exhibition
	//
	elseif( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] != '' ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'artgallery', 'web', 'exhibitionDetails');
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processURL');

		//
		// Get the exhibitor information
		//
		$exhibitor_permalink = $ciniki['request']['uri_split'][0];
		$rc = ciniki_artgallery_web_exhibitionDetails($ciniki, $settings, 
			$ciniki['request']['business_id'], $exhibitor_permalink);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$exhibition = $rc['exhibition'];
		$page_title = $exhibition['name'];
		$page_content .= "<article class='page'>\n"
			. "<header class='entry-title'><h1 class='entry-title'>" . $exhibition['name'] . "</h1></header>\n"
			. "";

		//
		// Add primary image
		//
		if( isset($exhibition['image_id']) && $exhibition['image_id'] > 0 ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
			$rc = ciniki_web_getScaledImageURL($ciniki, $exhibition['image_id'], 'original', '500', 0);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= "<aside><div class='image-wrap'><div class='image'>"
				. "<img title='' alt='" . $exhibition['name'] . "' src='" . $rc['url'] . "' />"
				. "</div></div></aside>";
		}
		
		//
		// Add description
		//
		if( isset($exhibition['description']) ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
			$rc = ciniki_web_processContent($ciniki, $exhibition['description']);	
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= $rc['content'];
		}

		//
		// Add the links if they exist
		//
		if( isset($exhibition['links']) && count($exhibition['links']) > 0 ) {
			$page_content .= "<p>";
			foreach($exhibition['links'] as $lid => $link) {
				$rc = ciniki_web_processURL($ciniki, $link['url']);
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				$url = $rc['url'];
				$display_url = $rc['display'];
				$page_content .= "<br/>" . $link['name'] . ": <a class='exhibitors-url' target='_blank' href='" . $url . "' title='" . $link['name'] . "'>" . $display_url . "</a>";
			}
			$page_content .= "</p>";
		} else {
			$url = '';
		}

		$page_content .= "</article>";
		
		//
		// Add images if they exist
		//
		if( isset($exhibition['images']) && count($exhibition['images']) > 0 ) {
			$page_content .= "<article class='page'>"	
				. "<header class='entry-title'><h1 class='entry-title'>Gallery</h1></header>\n"
				. "";
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageGalleryThumbnails');
			$img_base_url = $ciniki['request']['base_url'] . "/exhibitions/" . $exhibition['permalink'] . "/gallery";
			$rc = ciniki_web_generatePageGalleryThumbnails($ciniki, $settings, $img_base_url, $exhibition['images'], 125);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= "<div class='image-gallery'>" . $rc['content'] . "</div>";
			$page_content .= "</article>";
		}
	}

	//
	// Display the list of exhibitors if a specific one isn't selected
	//
	else {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'artgallery', 'web', 'exhibitionList');
		$rc = ciniki_artgallery_web_exhibitionList($ciniki, $settings, $ciniki['request']['business_id'], 'upcoming', 0);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$exhibitions = $rc['exhibitions'];
		$page_content .= "<article class='page'>\n"
			. "<header class='entry-title'><h1 class='entry-title'>Upcoming Exhibitions</h1></header>\n"
			. "<div class='entry-content'>\n"
			. "";

		if( count($exhibitions) > 0 ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processExhibitions');
			$rc = ciniki_web_processExhibitions($ciniki, $settings, $exhibitions, 0);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= $rc['content'];
		} else {
			$page_content .= "<p>No upcoming exhibitions</p>";
		}

		$page_content .= "</div>\n"
			. "</article>\n"
			. "";

		//
		// Include past exhibitions if the user wants
		//
		if( isset($settings['page-artgalleryexhibitions-past']) && $settings['page-artgalleryexhibitions-past'] == 'yes' ) {
			//
			// Generate the content of the page
			//
			ciniki_core_loadMethod($ciniki, 'ciniki', 'artgallery', 'web', 'exhibitionList');
			$rc = ciniki_artgallery_web_exhibitionList($ciniki, $settings, $ciniki['request']['business_id'], 'past', 10);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$exhibitions = $rc['exhibitions'];

			$page_content .= "<article class='page'>\n"
				. "<header class='entry-title'><h1 class='entry-title'>Past Exhibitions</h1></header>\n"
				. "<div class='entry-content'>\n"
				. "";
			if( count($exhibitions) > 0 ) {
				ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processExhibitions');
				$rc = ciniki_web_processExhibitions($ciniki, $settings, $exhibitions, 0);
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				$page_content .= $rc['content'];
			} else {
				$page_content .= "<p>No past exhibitions</p>";
			}

			$page_content .= "</div>\n"
				. "</article>\n"
				. "";
		}

		//
		// Check if the exhibition application should be displayed
		//
		if( isset($settings['page-artgalleryexhibitions-application-details']) && $settings['page-artgalleryexhibitions-application-details'] == 'yes' ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
			ciniki_core_loadMethod($ciniki, 'ciniki', 'artgallery', 'web', 'exhibitionApplicationDetails');
			$rc = ciniki_artgallery_web_exhibitionApplicationDetails($ciniki, $settings, $ciniki['request']['business_id']);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$application = $rc['application'];
			if( $application['details'] != '' ) {
				$page_content .= "<article class='page'>\n"
//					. "<header class='entry-title'><h1 class='entry-title'>Exhibitor Application</h1></header>\n"
					. "<div class='entry-content'>\n"
					. "";
				$page_content .= "<p class='exhibitors-application'><a href='" . $ciniki['request']['base_url'] . "/exhibitions/application'>Apply to be an exhibitor</a></p>";
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
	$rc = ciniki_web_generatePageHeader($ciniki, $settings, $page_title);
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
