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
		return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'1054', 'msg'=>'The file you requested does not exist.  Please check your link and try again.'));
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

	//
	// Get the pages with content
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'info', 'web', 'pages');
	$rc = ciniki_info_web_pages($ciniki, $settings, $ciniki['request']['business_id']);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$info_pages = $rc['pages'];

	if( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] != '' ) {
		$page_permalink = $ciniki['request']['uri_split'][0];
		$page_settings_name = $ciniki['request']['uri_split'][0];
		foreach($info_pages as $page) {
			if( $page['permalink'] == $page_permalink ) {
				$content_type = $page['content_type'];
			}
		}
		// This allows for permalink and title renaming
		if( isset($content_type) ) {
			switch($content_type) {
				case 2: $page_settings_name = 'artiststatement'; break;
				case 3: $page_settings_name = 'cv'; break;
				case 4: $page_settings_name = 'awards'; break;
				case 5: $page_settings_name = 'history'; break;
				case 6: $page_settings_name = 'donations'; break;
				case 7: $page_settings_name = 'membership'; break;
				case 8: $page_settings_name = 'boardofdirectors'; break;
				case 9: $page_settings_name = 'facilities'; break;
				case 10: $page_settings_name = 'exhibitionapplication'; break;
				case 11: $page_settings_name = 'warranty'; break;
				case 12: $page_settings_name = 'testimonials'; break;
				case 13: $page_settings_name = 'reviews'; break;
				case 14: $page_settings_name = 'greenpolicy'; break;
				case 15: $page_settings_name = 'whyus'; break;
				case 16: $page_settings_name = 'privacypolicy'; break;
			}
		}
	}

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
	// Generate the testimonials page
	//
	elseif(	isset($ciniki['request']['uri_split'][0])
		&& $ciniki['request']['uri_split'][0] == 'testimonials'
		&& isset($settings['page-about-testimonials-active'])
		&& $settings['page-about-testimonials-active'] == 'yes' ) {

		ciniki_core_loadMethod($ciniki, 'ciniki', 'info', 'web', 'testimonials');
		$rc = ciniki_info_web_testimonials($ciniki, $settings, $ciniki['request']['business_id']);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$testimonials = $rc['testimonials'];

		$page_content .= "<article class='page'>\n"
			. "<header class='entry-title'><h1 class='entry-title'>Testimonials</h1></header>\n"
			. "<div class='entry-content'>";

		foreach($testimonials as $tid => $testimonial) {
			$page_content .= "<div class='quote wide'>";
			$page_content .= "<blockquote class='quote-text wide'><p class='wide'>" . $testimonial['quote'] . "</p>\n";
			if( isset($testimonial['who']) && $testimonial['who'] != '' ) {
				$page_content .= "<footer><cite class='quote-author wide alignright'>-- " . $testimonial['who'] . "</cite></footer>";
			}
			$page_content .= "</blockquote></div>";
		}
		$page_content .= "</div></article>";
	}

	//
	// Generate the content details
	//
	elseif(	isset($ciniki['request']['uri_split'][0])
		&& isset($settings['page-about-' . $page_settings_name . '-active'])
		&& $settings['page-about-' . $page_settings_name . '-active'] == 'yes' ) {

		$permalink = $ciniki['request']['uri_split'][0];
		ciniki_core_loadMethod($ciniki, 'ciniki', 'info', 'web', 'pageDetails');
		$rc = ciniki_info_web_pageDetails($ciniki, $settings, $ciniki['request']['business_id'], 
			array('permalink'=>$permalink));
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$info = $rc['content'];
		
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processPage');
		$rc = ciniki_web_processPage($ciniki, $settings, $ciniki['request']['base_url'] . '/about', $info, 
			array());
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$page_content .= $rc['content'];
	}
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
	if( isset($settings['page-about-whyus-active']) 
		&& $settings['page-about-whyus-active'] == 'yes' 
		&& isset($info_pages['15'])
		) {
		$submenu['whyus'] = array('name'=>$info_pages['15']['title'], 
			'url'=>$ciniki['request']['base_url'] . '/about/' . $info_pages['15']['permalink']);
	}
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
	if( isset($settings['page-about-boardofdirectors-active']) 
		&& $settings['page-about-boardofdirectors-active'] == 'yes' 
		&& isset($info_pages['8'])
		) {
		$submenu['boardofdirectors'] = array('name'=>$info_pages['8']['title'], 
			'url'=>$ciniki['request']['base_url'] . '/about/' . $info_pages['8']['permalink']);
	}
	if( isset($settings['page-about-reviews-active']) 
		&& $settings['page-about-reviews-active'] == 'yes' ) {
		$submenu['reviews'] = array('name'=>'Reviews', 
			'url'=>$ciniki['request']['base_url'] . '/about/reviews');
	}
	if( isset($settings['page-about-testimonials-active']) 
		&& $settings['page-about-testimonials-active'] == 'yes' ) {
		$submenu['testimonials'] = array('name'=>'Testimonials', 
			'url'=>$ciniki['request']['base_url'] . '/about/testimonials');
	}
	if( isset($settings['page-about-greenpolicy-active']) 
		&& $settings['page-about-greenpolicy-active'] == 'yes' 
		&& isset($info_pages['14'])
		) {
		$submenu['greenpolicy'] = array('name'=>$info_pages['14']['title'], 
			'url'=>$ciniki['request']['base_url'] . '/about/' . $info_pages['14']['permalink']);
	}
	if( isset($settings['page-about-warranty-active']) 
		&& $settings['page-about-warranty-active'] == 'yes' 
		&& isset($info_pages['11'])
		) {
		$submenu['warranty'] = array('name'=>$info_pages['11']['title'], 
			'url'=>$ciniki['request']['base_url'] . '/about/' . $info_pages['11']['permalink']);
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
	$content .= "<br style='clear: both;' />\n";
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
