<?php
//
// Description
// -----------
// This function will generate the about page for the business.
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_generatePageAbout($ciniki, $settings) {

	//
	// Check if a file was specified to be downloaded
	//
	$download_err = '';
//	if( (isset($ciniki['business']['modules']['ciniki.artclub'])
//			|| isset($ciniki['business']['modules']['ciniki.artgallery']))
	if( isset($ciniki['business']['modules']['ciniki.info']) 
		&& isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] == 'download'
		&& isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] != '' ) {
//		if( isset($ciniki['business']['modules']['ciniki.artgallery']) ) {
//			ciniki_core_loadMethod($ciniki, 'ciniki', 'artgallery', 'web', 'fileDownload');
//			$rc = ciniki_artgallery_web_fileDownload($ciniki, $ciniki['request']['business_id'], $ciniki['request']['uri_split'][1]);
//		} else {
//			ciniki_core_loadMethod($ciniki, 'ciniki', 'artclub', 'web', 'fileDownload');
//			$rc = ciniki_artclub_web_fileDownload($ciniki, $ciniki['request']['business_id'], $ciniki['request']['uri_split'][1]);
//		}
		ciniki_core_loadMethod($ciniki, 'ciniki', 'info', 'web', 'fileDownload');
		$rc = ciniki_info_web_fileDownload($ciniki, $ciniki['request']['business_id'], $ciniki['request']['uri_split'][1]);
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
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1054', 'msg'=>'Unable to locate file'));
	}

	//
	// Store the content created by the page
	// Make sure everything gets generated ok before returning the content
	//
	$content = '';
	$page_content = '';

	//
	// FIXME: Check if anything has changed, and if not load from cache
	//

//	$subpages = array(
//		'artiststatement'=>array('title'=>'Artist Statement'), 
//		'cv'=>array('title'=>'CV'), 
//		'awards'=>array('title'=>'Awards'), 
//		);
	if( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] != '' 
		&& isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] == 'gallery' 
		&& isset($ciniki['request']['uri_split'][2]) && $ciniki['request']['uri_split'][2] != '' 
		) {
		$content_permalink = $ciniki['request']['uri_split'][0];
		$image_permalink = $ciniki['request']['uri_split'][2];

		//
		// Load the event to get all the details, and the list of images.
		// It's one query, and we can find the requested image, and figure out next
		// and prev from the list of images returned
		//
		ciniki_core_loadMethod($ciniki, 'ciniki', 'info', 'web', 'pageDetails');
		$rc = ciniki_info_web_pageDetails($ciniki, $settings, $ciniki['request']['business_id'], 
			array('permalink'=>$content_permalink));
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$info = $rc['content'];

		if( !isset($info['images']) || count($info['images']) < 1 ) {
			return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1681', 'msg'=>'Unable to find image'));
		}

		$first = NULL;
		$last = NULL;
		$img = NULL;
		$next = NULL;
		$prev = NULL;
		foreach($info['images'] as $iid => $image) {
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

		if( count($info['images']) == 1 ) {
			$prev = NULL;
			$next = NULL;
		} elseif( $prev == NULL ) {
			// The requested image was the first in the list, set previous to last
			$prev = $last;
		} elseif( $next == NULL ) {
			// The requested image was the last in the list, set previous to last
			$next = $first;
		}

		if( $img['title'] != '' ) {
			$page_title = $info['title'] . ' - ' . $img['title'];
		} else {
			$page_title = $info['title'];
		}
	
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
	// Generate the content details
	//
	elseif(	isset($ciniki['request']['uri_split'][0])
		&& isset($settings['page-about-' . $ciniki['request']['uri_split']['0'] . '-active'])
		&& $settings['page-about-' . $ciniki['request']['uri_split']['0'] . '-active'] == 'yes' ) {

		$permalink = $ciniki['request']['uri_split'][0];
		ciniki_core_loadMethod($ciniki, 'ciniki', 'info', 'web', 'pageDetails');
		$rc = ciniki_info_web_pageDetails($ciniki, $settings, $ciniki['request']['business_id'], 
			array('permalink'=>$permalink));
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$info = $rc['content'];

		$page_content .= "<article class='page'>\n"
			. "<header class='entry-title'><h1 class='entry-title'>" . $info['title'] . "</h1></header>\n"
			. "";
		if( isset($info['image_id']) && $info['image_id'] != '' && $info['image_id'] != 0 ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
			$rc = ciniki_web_getScaledImageURL($ciniki, $info['image_id'], 'original', '500', 0);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= "<aside><div class='image-wrap'>"
				. "<div class='image'><img title='' src='" . $rc['url'] . "' /></div>";
			if( isset($info['image_caption']) && $info['image_caption'] != '' ) {
				$page_content .= "<div class='image-caption'>" . $info['image_caption'] . "</div>";
			}
			$page_content .= "</div></aside>";
		}

		$page_content .= "<div class='entry-content'>";
		if( isset($info['content']) ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
			$rc = ciniki_web_processContent($ciniki, $info['content']);	
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= $rc['content'];
		}
		if( isset($info['files']) ) {
			foreach($info['files'] as $fid => $file) {
				$url = $ciniki['request']['base_url'] . '/about/download/' . $file['permalink'] . '.' . $file['extension'];
				$page_content .= "<p><a target='_blank' href='" . $url . "' title='" . $file['name'] . "'>" . $file['name'] . "</a></p>";
			}
		}
		$page_content .= "</div>";
		$page_content .= "</article>\n";

		//
		// Display the additional images for the content
		//
		if( isset($info['images']) && count($info['images']) > 0 ) {
			$page_content .= "<article class='page'>"	
				. "<header class='entry-title'><h1 class='entry-title'>Gallery</h1></header>\n"
				. "";
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageGalleryThumbnails');
			$img_base_url = $ciniki['request']['base_url'] . "/about/" . $info['permalink'] . "/gallery";
			$rc = ciniki_web_generatePageGalleryThumbnails($ciniki, $settings, $img_base_url, $info['images'], 125);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= "<div class='image-gallery'>" . $rc['content'] . "</div>";
			$page_content .= "</article>";
		}
	}
//
//
// FIXME: Remove old code
//
//
/*
	elseif( isset($ciniki['request']['uri_split'][0]) 
		&& array_key_exists($ciniki['request']['uri_split']['0'], $subpages)
		&& isset($settings['page-about' . $ciniki['request']['uri_split']['0'] . '-active'])
		&& $settings['page-about' . $ciniki['request']['uri_split']['0'] . '-active'] == 'yes'
		) {

		$page = $ciniki['request']['uri_split'][0];

		

		$page_content .= "<article class='page'>\n";
		$page_content .= "<header class='entry-title'><h1 class='entry-title'>" 
			. $subpages[$page]['title'] . "</h1></header>\n";
		if( isset($settings["page-about$page-image"]) 
			&& $settings["page-about$page-image"] != '' && $settings["page-about$page-image"] > 0 
			) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
			$rc = ciniki_web_getScaledImageURL($ciniki, $settings["page-about$page-image"], 'original', '500', 0);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= "<aside><div class='image-wrap'>"
				. "<div class='image'><img title='' alt='" . $ciniki['business']['details']['name'] . "' src='" . $rc['url'] . "' /></div>";
			if( isset($settings["page-about$page-image-caption"]) && $settings["page-about$page-image-caption"] != '' ) {
				$page_content .= "<div class='image-caption'>" . $settings["page-about$page-image-caption"] . "</div>";
			}
			$page_content .= "</div></aside>";
		}

		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash');
		$rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_web_content', 'business_id', $ciniki['request']['business_id'], 'ciniki.web', 'content', "page-about$page");
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}

		if( isset($rc['content']["page-about$page-content"]) ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
			$rc = ciniki_web_processContent($ciniki, $rc['content']["page-about$page-content"]);	
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= "<div class='entry-content'>"
				. $rc['content']
				. "</div>";
		}

		$page_content .= "</div>\n"
			. "</article>\n";
	}
	
	//
	// Check if history
	//
	elseif( isset($ciniki['request']['uri_split'][0]) 
		&& ($ciniki['request']['uri_split'][0] == 'history' || $ciniki['request']['uri_split'][0] == 'donations')
		&& isset($settings['page-abouthistory-active']) && $settings['page-abouthistory-active'] == 'yes' 
		) {
		$page = $ciniki['request']['uri_split'][0];
		$page_content .= "<article class='page'>\n";
		if( $page == 'history' ) {
			$page_content .= "<header class='entry-title'><h1 class='entry-title'>History</h1></header>\n";
		} elseif( $page == 'donations' ) {
			$page_content .= "<header class='entry-title'><h1 class='entry-title'>Donations</h1></header>\n";
		} else {
			$page_content .= "<header class='entry-title'><h1 class='entry-title'>About</h1></header>\n";
		}
		if( isset($settings["page-about$page-image"]) && $settings["page-about$page-image"] != '' && $settings["page-about$page-image"] > 0 ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
			$rc = ciniki_web_getScaledImageURL($ciniki, $settings["page-about$page-image"], 'original', '500', 0);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= "<aside><div class='image-wrap'>"
				. "<div class='image'><img title='' alt='" . $ciniki['business']['details']['name'] . "' src='" . $rc['url'] . "' /></div>";
			if( isset($settings["page-about$page-image-caption"]) && $settings["page-about$page-image-caption"] != '' ) {
				$page_content .= "<div class='image-caption'>" . $settings["page-about$page-image-caption"] . "</div>";
			}
			$page_content .= "</div></aside>";
		}

		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash');
		$rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_web_content', 'business_id', $ciniki['request']['business_id'], 'ciniki.web', 'content', "page-about$page");
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}

		if( isset($rc['content']["page-about$page-content"]) ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
			$rc = ciniki_web_processContent($ciniki, $rc['content']["page-about$page-content"]);	
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= "<div class='entry-content'>"
				. $rc['content']
				. "</div>";
		}

		$page_content .= "</div>\n"
			. "</article>\n";
	}

	elseif( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] == 'boardofdirectors'
		&& isset($settings['page-aboutboardofdirectors-active']) && $settings['page-aboutboardofdirectors-active'] == 'yes' 
		) {
		$page = 'boardofdirectors';
		$page_content .= "<article class='page'>\n"
			. "<header class='entry-title'><h1 class='entry-title'>Board of Directors</h1></header>\n"
			. "<div class='entry-content'>\n"
			. "";
		if( isset($settings["page-about$page-image"]) && $settings["page-about$page-image"] != '' && $settings["page-about$page-image"] > 0 ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
			$rc = ciniki_web_getScaledImageURL($ciniki, $settings["page-about$page-image"], 'original', '500', 0);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= "<aside><div class='image-wrap'>"
				. "<div class='image'><img title='' alt='" . $ciniki['business']['details']['name'] . "' src='" . $rc['url'] . "' /></div>";
			if( isset($settings["page-about$page-image-caption"]) && $settings["page-about$page-image-caption"] != '' ) {
				$page_content .= "<div class='image-caption'>" . $settings["page-about$page-image-caption"] . "</div>";
			}
			$page_content .= "</div></aside>";
		}

		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash');
		$rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_web_content', 'business_id', $ciniki['request']['business_id'], 'ciniki.web', 'content', "page-about$page");
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}

		if( isset($rc['content']["page-about$page-content"]) ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
			$rc = ciniki_web_processContent($ciniki, $rc['content']["page-about$page-content"]);	
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= "<div class='entry-content'>"
				. $rc['content']
				. "</div>";
		}

		$page_content .= "</div>\n"
			. "</article>";
	}

	//
	// Check if membership info should be displayed here
	//
	elseif( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] == 'membership'
		&& (isset($ciniki['business']['modules']['ciniki.artclub']) 
			|| isset($ciniki['business']['modules']['ciniki.artgallery']))
		&& isset($settings['page-aboutmembership-active']) && $settings['page-aboutmembership-active'] == 'yes' 
		) {
		$page = 'membership';
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
		if( isset($ciniki['business']['modules']['ciniki.artgallery']) ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'artgallery', 'web', 'membershipDetails');
			$rc = ciniki_artgallery_web_membershipDetails($ciniki, $settings, $ciniki['request']['business_id']);
		} else {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'artclub', 'web', 'membershipDetails');
			$rc = ciniki_artclub_web_membershipDetails($ciniki, $settings, $ciniki['request']['business_id']);
		}
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$membership = $rc['membership'];
		if( $membership['details'] != '' ) {
			$page_content .= "<article class='page'>\n"
				. "<header class='entry-title'><h1 class='entry-title'>Membership</h1></header>\n"
				. "<div class='entry-content'>\n"
				. "";
			if( isset($settings["page-about$page-image"]) && $settings["page-about$page-image"] != '' && $settings["page-about$page-image"] > 0 ) {
				ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
				$rc = ciniki_web_getScaledImageURL($ciniki, $settings["page-about$page-image"], 'original', '500', 0);
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				$page_content .= "<aside><div class='image-wrap'>"
					. "<div class='image'><img title='' alt='" . $ciniki['business']['details']['name'] . "' src='" . $rc['url'] . "' /></div>";
				if( isset($settings["page-about$page-image-caption"]) && $settings["page-about$page-image-caption"] != '' ) {
					$page_content .= "<div class='image-caption'>" . $settings["page-about$page-image-caption"] . "</div>";
				}
				$page_content .= "</div></aside>";
			}
			$rc = ciniki_web_processContent($ciniki, $membership['details']);	
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= $rc['content'];

			foreach($membership['files'] as $fid => $file) {
				$file = $file['file'];
				$url = $ciniki['request']['base_url'] . '/about/download/' . $file['permalink'] . '.' . $file['extension'];
				$page_content .= "<p><a target='_blank' href='" . $url . "' title='" . $file['name'] . "'>" . $file['name'] . "</a></p>";
			}

			$page_content .= "</div>\n"
				. "</article>";
		}
	}
*/
	//
	// Generate the content of the page
	//
	else {
		$page_content .= "<article class='page'>\n"
			. "<header class='entry-title'><h1 class='entry-title'>About</h1></header>\n"
			. "";
		ciniki_core_loadMethod($ciniki, 'ciniki', 'info', 'web', 'pageDetails');
		$rc = ciniki_info_web_pageDetails($ciniki, $settings, $ciniki['request']['business_id'], 
			array('content_type'=>1));
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$info = $rc['content'];
		if( isset($info['image_id']) && $info['image_id'] != '' && $info['image_id'] != 0 ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
			$rc = ciniki_web_getScaledImageURL($ciniki, $info['image_id'], 'original', '500', 0);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= "<aside><div class='image-wrap'>"
				. "<div class='image'><img title='' alt='" . $ciniki['business']['details']['name'] . "' src='" . $rc['url'] . "' /></div>";
			if( isset($info['image_caption']) && $info['image_caption'] != '' ) {
				$page_content .= "<div class='image-caption'>" . $info['image_caption'] . "</div>";
			}
			$page_content .= "</div></aside>";
		}

//		if( isset($settings['page-about-image']) && $settings['page-about-image'] != '' && $settings['page-about-image'] > 0 ) {
//			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
//			$rc = ciniki_web_getScaledImageURL($ciniki, $settings['page-about-image'], 'original', '500', 0);
//			if( $rc['stat'] != 'ok' ) {
//				return $rc;
//			}
//			$page_content .= "<aside><div class='image-wrap'>"
//				. "<div class='image'><img title='' alt='" . $ciniki['business']['details']['name'] . "' src='" . $rc['url'] . "' /></div>";
//			if( isset($settings['page-about-image-caption']) && $settings['page-about-image-caption'] != '' ) {
//				$page_content .= "<div class='image-caption'>" . $settings['page-about-image-caption'] . "</div>";
//			}
//			$page_content .= "</div></aside>";
//		}

//		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash');
//		$rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_web_content', 'business_id', $ciniki['request']['business_id'], 'ciniki.web', 'content', 'page-about');
//		if( $rc['stat'] != 'ok' ) {
//			return $rc;
//		}

		if( isset($info['content']) ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
			$rc = ciniki_web_processContent($ciniki, $info['content']);	
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= "<div class='entry-content'>"
				. $rc['content']
				. "</div>";
		}
//		if( isset($rc['content']['page-about-content']) ) {
//			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
//			$rc = ciniki_web_processContent($ciniki, $rc['content']['page-about-content']);	
//			if( $rc['stat'] != 'ok' ) {
//				return $rc;
//			}
//			$page_content .= "<div class='entry-content'>"
//				. $rc['content']
//				. "</div>";
//		}

		$page_content .= "\n"
			. "</article>\n";

		//
		// Generate the list of employee's who are to be shown on the website
		//
		if( isset($settings['page-about-user-display']) && $settings['page-about-user-display'] == 'yes' ) {
			//
			// Check which parts of the business contact information to display automatically
			//
			ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'web', 'bios');
			$rc = ciniki_businesses_web_bios($ciniki, $settings, $ciniki['request']['business_id'], 'about');
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$users = $rc['users'];
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processEmployeeBios');
			$rc = ciniki_web_processEmployeeBios($ciniki, $settings, 'about', $users);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			if( isset($rc['content']) && $rc['content'] != '' ) {
				$page_content .= "<article class='page'>\n";
				if( isset($settings['page-about-bios-title']) && $settings['page-about-bios-title'] != '' ) {
					$page_content .= "<header class='entry-title'><h1 class='entry-title'>"
						. $settings['page-about-bios-title'] . "</h1></header>\n";
				}
				$page_content .= "<div class='entry-content'>"
					. $rc['content']
					. "</div>";
//				$page_content .= $rc['content'];
				$page_content .= "</article>\n";
			}
		}
	}	

	//
	// Check if we are to display a submenu
	//
	$submenu = array();
//	$submenu['about'] = array('name'=>'About', 'url'=>$ciniki['request']['base_url'] . '/about');
	if( isset($settings['page-about-artiststatement-active']) 
		&& $settings['page-about-artiststatement-active'] == 'yes' ) {
		$submenu['artiststatement'] = array('name'=>'Artist Statement', 
			'url'=>$ciniki['request']['base_url'] . '/about/artiststatement');
	}
	if( isset($settings['page-about-cv-active']) 
		&& $settings['page-about-cv-active'] == 'yes' ) {
		$submenu['cv'] = array('name'=>'CV', 
			'url'=>$ciniki['request']['base_url'] . '/about/cv');
	}
	if( isset($settings['page-about-awards-active']) 
		&& $settings['page-about-awards-active'] == 'yes' ) {
		$submenu['awards'] = array('name'=>'Awards', 
			'url'=>$ciniki['request']['base_url'] . '/about/awards');
	}
//	if( isset($settings['page-about-history-active']) && $settings['page-about-history-active'] == 'yes' ) {
//		$submenu['history'] = array('name'=>'History', 
//			'url'=>$ciniki['request']['base_url'] . '/about/history');
//	}
	if( isset($settings['page-about-history-active']) && $settings['page-about-history-active'] == 'yes' ) {
		$submenu['history'] = array('name'=>'History', 
			'url'=>$ciniki['request']['base_url'] . '/about/history');
	}
	if( isset($settings['page-about-donations-active']) && $settings['page-about-donations-active'] == 'yes' ) {
		$submenu['donations'] = array('name'=>'Donations', 
			'url'=>$ciniki['request']['base_url'] . '/about/donations');
	}
	if( isset($settings['page-about-facilities-active']) && $settings['page-about-facilities-active'] == 'yes' ) {
		$submenu['facilities'] = array('name'=>'Facilities', 
			'url'=>$ciniki['request']['base_url'] . '/about/facilities');
	}
	if( isset($settings['page-about-boardofdirectors-active']) && $settings['page-about-boardofdirectors-active'] == 'yes' ) {
		$submenu['boardofdirectors'] = array('name'=>'Board of Directors', 
			'url'=>$ciniki['request']['base_url'] . '/about/boardofdirectors');
	}
	if( isset($settings['page-about-membership-active']) && $settings['page-about-membership-active'] == 'yes' ) {
		$submenu['membership'] = array('name'=>'Membership', 
			'url'=>$ciniki['request']['base_url'] . '/about/membership');
	}

	//
	// Add the header
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageHeader');
	$rc = ciniki_web_generatePageHeader($ciniki, $settings, 'About', $submenu);
	if( $rc['stat'] != 'ok' ) {	
		return $rc;
	}
	$content .= $rc['content'];

	$content .= "<div id='content'>\n";
	$content .= $page_content;
	$content .= "</div>\n";

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
