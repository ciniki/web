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
function ciniki_web_generatePageExhibitions($ciniki, $settings) {

	//
	// Check if a file was specified to be downloaded
	//
	$download_err = '';
	if( isset($ciniki['business']['modules']['ciniki.artgallery'])
		&& isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] != ''
		&& isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] == 'download'
		&& isset($ciniki['request']['uri_split'][2]) && $ciniki['request']['uri_split'][2] != '' ) {
//		ciniki_core_loadMethod($ciniki, 'ciniki', 'artgallery', 'web', 'fileDownload');
//		$rc = ciniki_artgallery_web_fileDownload($ciniki, $ciniki['request']['business_id'], $ciniki['request']['uri_split'][1]);
		ciniki_core_loadMethod($ciniki, 'ciniki', 'info', 'web', 'fileDownload');
		$rc = ciniki_info_web_fileDownload($ciniki, $ciniki['request']['business_id'], 
			$ciniki['request']['uri_split'][0], '',
			$ciniki['request']['uri_split'][2]);
		if( $rc['stat'] == 'ok' ) {
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			header("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
			header('Cache-Control: no-cache, must-revalidate');
			header('Pragma: no-cache');
			$file = $rc['file'];
			if( $file['extension'] == 'pdf' ) {
				header('Content-Type: application/pdf');
			}
//			header('Content-Disposition: attachment;filename="' . $file['filename'] . '"');
			header('Content-Length: ' . strlen($file['binary_content']));
			header('Cache-Control: max-age=0');

			print $file['binary_content'];
			exit;
		}
		
		//
		// If there was an error locating the files, display generic error
		//
		return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'1116', 'msg'=>'Unable to locate file'));
	}

	//
	// Store the content created by the page
	// Make sure everything gets generated ok before returning the content
	//
	$content = '';
	$page_content = '';
	$page_title = 'Exhibitors';
	$ciniki['response']['head']['og']['url'] = $ciniki['request']['domain_base_url'] . '/exhibitions';

	//
	// The initial limit is how many to show on the exhibitions page after current and upcoming.  
	// This allows a shorter list in the initial page, and longer lists for the archive
	//
	$page_past_initial_limit = 2;
	$page_past_limit = 10;
	if( isset($settings['page-artgalleryexhibitions-initial-number']) 
		&& $settings['page-artgalleryexhibitions-initial-number'] != ''
		&& is_numeric($settings['page-artgalleryexhibitions-initial-number'])
		&& $settings['page-artgalleryexhibitions-initial-number'] > 0 ) {
		$page_past_initial_limit = intval($settings['page-artgalleryexhibitions-initial-number']);
	}
	if( isset($settings['page-artgalleryexhibitions-archive-number']) 
		&& $settings['page-artgalleryexhibitions-archive-number'] != ''
		&& is_numeric($settings['page-artgalleryexhibitions-archive-number'])
		&& $settings['page-artgalleryexhibitions-archive-number'] > 0 ) {
		$page_past_limit = intval($settings['page-artgalleryexhibitions-archive-number']);
	}
	if( isset($ciniki['request']['args']['page']) && $ciniki['request']['args']['page'] != '' ) {
		$page_past_cur = $ciniki['request']['args']['page'];
	} else {
		$page_past_cur = 1;
	}

	//
	// FIXME: Check if anything has changed, and if not load from cache
	//

	//
	// Check if we are to display the application
	//
	if( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] == 'exhibitionapplication' 
//		&& isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] == 'application'  
		&& isset($settings['page-artgalleryexhibitions-application-details']) && $settings['page-artgalleryexhibitions-application-details'] == 'yes'
		) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
		ciniki_core_loadMethod($ciniki, 'ciniki', 'info', 'web', 'pageDetails');
//		$rc = ciniki_artgallery_web_exhibitionApplicationDetails($ciniki, $settings, $ciniki['request']['business_id']);
		$rc = ciniki_info_web_pageDetails($ciniki, $settings, $ciniki['request']['business_id'],
			array('content_type'=>10));
		if( $rc['stat'] != 'ok' ) {
			return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'1302', 'msg'=>"I'm sorry, but we can't find any information about the requestion application.", 'err'=>$rc['err']));;
		}
		$info = $rc['content'];
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processPage');
		$rc = ciniki_web_processPage($ciniki, $settings, $ciniki['request']['base_url'] . '/exhibitions', 
			$info, array());
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$page_content .= $rc['content'];
//		if( $application['details'] != '' ) {
//			$page_content .= "<article class='page'>\n"
//				. "<header class='entry-title'><h1 class='entry-title'>Exhibitor Application</h1></header>\n"
//				. "<div class='entry-content'>\n"
//				. "";
//			$rc = ciniki_web_processContent($ciniki, $application['details']);	
//			if( $rc['stat'] != 'ok' ) {
//				return $rc;
//			}
//			$page_content .= $rc['content'];
//
//			foreach($application['files'] as $fid => $file) {
//				$file = $file['file'];
//				$url = $ciniki['request']['base_url'] . '/exhibitions/download/' . $file['permalink'] . '.' . $file['extension'];
//				$page_content .= "<p><a target='_blank' href='" . $url . "' title='" . $file['name'] . "'>" . $file['name'] . "</a></p>";
//			}
//
//			$page_content .= "</div>\n"
//				. "</article>";
//		}
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
			return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'1304', 'msg'=>"I'm sorry, but we can't seem to find the image your requested.", $rc['err']));
		}
		$exhibition = $rc['exhibition'];

		$ciniki['response']['head']['og']['url'] .= '/' . $exhibition_permalink;
		if( isset($exhibition['image_id']) && $exhibition['image_id'] > 0 ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
			$rc = ciniki_web_getScaledImageURL($ciniki, $exhibition['image_id'], 'original', '500', 0);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$ciniki['response']['head']['og']['image'] = $rc['domain_url'];
		}
		if( isset($exhibition['short_description']) && $exhibition['short_description'] != '' ) {
			$ciniki['response']['head']['og']['description'] = strip_tags($exhibition['short_description']);
		} elseif( isset($exhibition['description']) && $exhibition['description'] != '' ) {
			$ciniki['response']['head']['og']['description'] = strip_tags($exhibition['description']);
		}
		
		if( !isset($exhibition['images']) || count($exhibition['images']) < 1 ) {
			return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'1132', 'msg'=>"I'm sorry, but we can't seem to find the image your requested."));
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
		$article_title = "<a href='" . $ciniki['request']['base_url'] . "/exhibitions/" . $exhibition['permalink'] . "'>" . $exhibition['name'] . "</a>";
		if( $img['title'] != '' ) {
			$page_title .= ' - ' . $img['title'];
			$article_title .= ' - ' . $img['title'];
		}
	
		if( $img == NULL || $img['image_id'] <= 0 ) {
			return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'1305', 'msg'=>"I'm sorry, but we can't seem to find the image your requested."));
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
			. "<header class='entry-title'><h1 id='entry-title' class='entry-title'>$article_title</h1></header>\n"
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
		$page_content .= "<img id='gallery-image-img' title='" . htmlspecialchars(strip_tags($img['title'])) . "' alt=\"" . htmlspecialchars(strip_tags($img['title'])) . "\" src='" . $img_url . "' onload='javascript: gallery_resize_arrows();' />";
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
	elseif( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] != '' 
		&& $ciniki['request']['uri_split'][0] != 'category' 
		) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'artgallery', 'web', 'exhibitionDetails');
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processURL');

		//
		// Get the exhibitor information
		//
		$exhibition_permalink = $ciniki['request']['uri_split'][0];
		$ciniki['response']['head']['og']['url'] .= '/' . $exhibition_permalink;
		$rc = ciniki_artgallery_web_exhibitionDetails($ciniki, $settings, 
			$ciniki['request']['business_id'], $exhibition_permalink);
		if( $rc['stat'] != 'ok' ) {
			return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'1306', 'msg'=>"I'm sorry, but we can't seem to find the exhibition you requested."));
		}
		$exhibition = $rc['exhibition'];
		// Format the date
		$exhibition_date = $exhibition['start_month'];
		$exhibition_date .= " " . $exhibition['start_day'];
		if( $exhibition['end_day'] != '' && ($exhibition['start_day'] != $exhibition['end_day'] || $exhibition['start_month'] != $exhibition['end_month']) ) {
			if( $exhibition['end_month'] != '' && $exhibition['end_month'] == $exhibition['start_month'] ) {
				$exhibition_date .= " - " . $exhibition['end_day'];
			} else {
				$exhibition_date .= " - " . $exhibition['end_month'] . " " . $exhibition['end_day'];
			}
		}
		$exhibition_date .= ", " . $exhibition['start_year'];
		$page_title = $exhibition['name'];
		$page_content .= "<article class='page'>\n"
			. "<header class='entry-title'><h1 class='entry-title'>" . $exhibition['name'] . "</h1>"
			. "<div class='entry-meta'>" . $exhibition_date . "</div>"
			. "</header>\n"
			. "";

		//
		// Add primary image
		//
		$aside_display = 'block';
		if( isset($exhibition['image_id']) && $exhibition['image_id'] > 0 ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
			$rc = ciniki_web_getScaledImageURL($ciniki, $exhibition['image_id'], 'original', '500', 0);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$ciniki['response']['head']['og']['image'] = $rc['domain_url'];
			$page_content .= "<aside id='aside-image'><div class='image-wrap'><div class='image'>"
				. "<img title='' alt='" . htmlspecialchars(strip_tags($exhibition['name'])) . "' src='" . $rc['url'] . "' />"
				. "</div></div></aside>";
			$aside_display = 'none';
		} 
		//
		// No primary image, display a map to the location
		//
		if( isset($exhibition['location_details']['latitude']) && $exhibition['location_details']['latitude'] != 0
			&& isset($exhibition['location_details']['longitude']) && $exhibition['location_details']['longitude'] != 0
			) {
			if( !isset($ciniki['request']['inline_javascript']) ) {
				$ciniki['request']['inline_javascript'] = '';
			}
			$ciniki['request']['inline_javascript'] .= ''
				. '<script type="text/javascript">'
				. 'var gmap_loaded=0;'
				. 'function gmap_initialize() {'
					. 'var myLatlng = new google.maps.LatLng(' . $exhibition['location_details']['latitude'] . ',' . $exhibition['location_details']['longitude'] . ');'
					. 'var mapOptions = {'
						. 'zoom: 13,'
						. 'center: myLatlng,'
						. 'panControl: false,'
						. 'zoomControl: true,'
						. 'scaleControl: true,'
						. 'mapTypeId: google.maps.MapTypeId.ROADMAP'
					. '};'
					. 'var map = new google.maps.Map(document.getElementById("googlemap"), mapOptions);'
					. 'var marker = new google.maps.Marker({'
						. 'position: myLatlng,'
						. 'map: map,'
						. 'title:"",'
						. '});'
				. '};'
				. 'function loadMap() {'
					. 'if(gmap_loaded==1) {return;}'
					. 'var script = document.createElement("script");'
					. 'script.type = "text/javascript";'
					. 'script.src = "' . ($ciniki['request']['ssl']=='yes'?'https':'http') . '://maps.googleapis.com/maps/api/js?key=' . $ciniki['config']['ciniki.web']['google.maps.api.key'] . '&sensor=false&callback=gmap_initialize";'
					. 'document.body.appendChild(script);'
					. 'gmap_loaded=1;'
				. '};'
				. 'function toggleMap() {'
					. "var i = document.getElementById('aside-image');\n"
					. "var m = document.getElementById('aside-map');\n"
					. "if(i!=null){"
						. "if(i.style.display!='none') {i.style.display='none';m.style.display='block'; loadMap();"
						. "} else {i.style.display='block';m.style.display='none'; "
						. "}\n"
					. "}"
				. '};'
				. ((!isset($exhibition['image_id']) || $exhibition['image_id'] == 0)?'window.onload=loadMap;':'')
				. '</script>';
			$page_content .= "<aside id='aside-map' style='display:${aside_display};'><div class='googlemap' id='googlemap'></div></aside>";
		}

		if( isset($exhibition['short_description']) && $exhibition['short_description'] != '' ) {
			$ciniki['response']['head']['og']['description'] = strip_tags($exhibition['short_description']);
		} elseif( isset($exhibition['description']) && $exhibition['description'] != '' ) {
			$ciniki['response']['head']['og']['description'] = strip_tags($exhibition['description']);
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
		} elseif( isset($exhibition['short_description']) ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
			$rc = ciniki_web_processContent($ciniki, $exhibition['short_description']);	
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= $rc['content'];
		}

		if( isset($exhibition['location_address']) && $exhibition['location_address'] != '' ) {
			$page_content .= "<h2>Location</h2>";
			$toggle_map = '';
			if( isset($exhibition['image_id']) && $exhibition['image_id'] > 0 
				&& isset($exhibition['location_details']['latitude']) && $exhibition['location_details']['latitude'] != 0
				&& isset($exhibition['location_details']['longitude']) && $exhibition['location_details']['longitude'] != 0
				) {
				$toggle_map = "<a href='javascript: toggleMap();'>map</a>";
			}
			if( isset($exhibition['location_details']['url']) && $exhibition['location_details']['url'] != '' ) {
				$page_content .= "<p><a target='_blank' href='" . $exhibition['location_details']['url'] . "'>" . $exhibition['location_details']['name'] . '</a></br>';
				$toggle_map .= ($toggle_map!=''?', ':'') . "<a target='_blank' href='" . $exhibition['location_details']['url'] . "'>website</a>";
			} else {
				$page_content .= "<p>" . $exhibition['location_details']['name'] . '</br>';
			}
			$page_content .= $exhibition['location_address'] 
				. ($toggle_map!=''?"(" . $toggle_map . ")":'')
				. "</p>";
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

		//
		// Check if share buttons should be shown
		//
		if( !isset($settings['page-exhibitions-share-buttons']) 
			|| $settings['page-exhibitions-share-buttons'] == 'yes' ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processShareButtons');
			$rc = ciniki_web_processShareButtons($ciniki, $settings, array(
				'title'=>$page_title,
				'tags'=>array('Exhibitions'),
				));
			if( $rc['stat'] == 'ok' ) {
				$page_content .= $rc['content'];
			}
		}
		
		//
		// Add images if they exist
		//
		if( isset($exhibition['images']) && count($exhibition['images']) > 0 ) {
			$page_content .= "<br style='clear: right;'/>";
			$page_content .= "<h2>Gallery</h2>";
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageGalleryThumbnails');
			$img_base_url = $ciniki['request']['base_url'] . "/exhibitions/" . $exhibition['permalink'] . "/gallery";
			$rc = ciniki_web_generatePageGalleryThumbnails($ciniki, $settings, $img_base_url, $exhibition['images'], 125);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= "<div class='image-gallery'>" . $rc['content'] . "</div>";
		}

		$page_content .= "<br style='clear: right;'/>";
		$page_content .= "</article>";
	}

	//
	// Display the list of exhibitors if a specific one isn't selected
	//
	else {
		//
		// Check to see if there is an introduction message to display
		//
		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash');
		$rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_web_content', 'business_id', $ciniki['request']['business_id'], 'ciniki.web', 'content', 'page-artgalleryexhibitions');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$ciniki['response']['head']['og']['description'] = strip_tags('Upcoming Exhibitions');

		if( isset($rc['content']['page-artgalleryexhibitions-content']) && $rc['content']['page-artgalleryexhibitions-content'] != '' ) {
			$page_content .= "<article class='page'>\n"
				. "<header class='entry-title'><h1 class='entry-title'>Exhibitions</h1></header>\n"
				. "";
			$desc_content = $rc['content']['page-artgalleryexhibitions-content'];
			if( isset($settings['page-artgalleryexhibitions-image']) && $settings['page-artgalleryexhibitions-image'] != '' && $settings['page-artgalleryexhibitions-image'] > 0 ) {
				ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
				$rc = ciniki_web_getScaledImageURL($ciniki, $settings['page-artgalleryexhibitions-image'], 'original', '500', 0);
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				$page_content .= "<aside><div class='image-wrap'>"
					. "<div class='image'><img title='' alt='" . $ciniki['business']['details']['name'] . "' src='" . $rc['url'] . "' /></div>";
				if( isset($settings['page-artgalleryexhibitions-image-caption']) && $settings['page-artgalleryexhibitions-image-caption'] != '' ) {
					$page_content .= "<div class='image-caption'>" . $settings['page-artgalleryexhibitions-image-caption'] . "</div>";
				}
				$page_content .= "</div></aside>";
			}

			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
			$rc = ciniki_web_processContent($ciniki, $desc_content);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= "<div class='entry-content'>"
				. $rc['content']
				. "</div>";
			$page_content .= "</article>\n";
		}

		//
		// Display list of upcoming exhibitions
		//
		$category = '';
		if( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] == 'category' 
			&& isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] != '' 
			) {
			$category = $ciniki['request']['uri_split'][1];
		}
		if( $page_past_cur == 1 ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'artgallery', 'web', 'exhibitionList');
			$rc = ciniki_artgallery_web_exhibitionList($ciniki, $settings, $ciniki['request']['business_id'], 
				array('type'=>'current', 'limit'=>0, 'category'=>$category));
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			if( count($rc['exhibitions']) > 0 ) {
				$exhibitions = $rc['exhibitions'];
				$page_content .= "<article class='page'>\n"
					. "<header class='entry-title'><h1 class='entry-title'>"
					. (count($rc['exhibitions'])>1?'Current Exhibitions':'Current Exhibition') 
					. "</h1></header>\n"
					. "<div class='entry-content'>\n"
					. "";

				if( count($exhibitions) > 0 ) {
					ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processExhibitions');
					$rc = ciniki_web_processExhibitions($ciniki, $settings, $exhibitions, 0);
					if( $rc['stat'] != 'ok' ) {
						return $rc;
					}
					$page_content .= $rc['content'];
				}

				$page_content .= "</div>\n"
					. "</article>\n"
					. "";
			}

			ciniki_core_loadMethod($ciniki, 'ciniki', 'artgallery', 'web', 'exhibitionList');
			$rc = ciniki_artgallery_web_exhibitionList($ciniki, $settings, $ciniki['request']['business_id'], 
				array('type'=>'upcoming', 'limit'=>0, 'category'=>$category));
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
		}

		//
		// Include past exhibitions if the user wants
		//
		if( isset($settings['page-artgalleryexhibitions-past']) && $settings['page-artgalleryexhibitions-past'] == 'yes' ) {
			//
			// Generate the content of the page
			//
			ciniki_core_loadMethod($ciniki, 'ciniki', 'artgallery', 'web', 'exhibitionList');
			if( $page_past_cur == 1 ) {
				$offset = 0;
			} else {
				$offset = $page_past_initial_limit + ($page_past_cur-2)*$page_past_limit;
			}
			$rc = ciniki_artgallery_web_exhibitionList($ciniki, $settings, $ciniki['request']['business_id'], 
				array('type'=>'past', 
					'category'=>$category,
					'offset'=>$offset,
					'limit'=>($page_past_cur==1?($page_past_initial_limit+1):($page_past_limit+1))));
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
				$rc = ciniki_web_processExhibitions($ciniki, $settings, $exhibitions, 
					array('page'=>$page_past_cur,
						'limit'=>($page_past_cur==1?$page_past_initial_limit:$page_past_limit), 
						'prev'=>'Newer Exhibitions &rarr;',
						'next'=>'&larr; Older Exhibitions',
						'base_url'=>$ciniki['request']['base_url'] . "/exhibitions" . ($category!=''?'/category/'.$category:'')));
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				$page_content .= $rc['content'];
				$nav_content = $rc['nav'];
			} else {
				$page_content .= "<p>No past exhibitions</p>";
			}

			$page_content .= "</div>\n"
				. "</article>\n"
				. "";
			if( isset($nav_content) && $nav_content != '' ) {	
				$page_content .= $nav_content;
			}
		}

		//
		// Check if the exhibition application should be displayed
		//
		if( isset($settings['page-artgalleryexhibitions-application-details']) 
			&& $settings['page-artgalleryexhibitions-application-details'] == 'yes' 
			&& $page_past_cur == 1
			) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
			ciniki_core_loadMethod($ciniki, 'ciniki', 'info', 'web', 'pageDetails');
			$rc = ciniki_info_web_pageDetails($ciniki, $settings, $ciniki['request']['business_id'],
				array('content_type'=>10));
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$application = $rc['content'];
			if( $application['content'] != '' ) {
				$page_content .= "<article class='page'>\n"
//					. "<header class='entry-title'><h1 class='entry-title'>Exhibitor Application</h1></header>\n"
					. "<div class='entry-content'>\n"
					. "";
				$page_content .= "<p class='exhibitors-application'><a href='" . $ciniki['request']['base_url'] . "/exhibitions/exhibitionapplication'>Apply to be an exhibitor</a></p>";
				$page_content .= "</div>\n"
					. "</article>";
			}
		}
	}

	//
	// Check for categories
	//
	$submenu = array();
	if( ($ciniki['business']['modules']['ciniki.artgallery']['flags']&0x04) > 0 ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'artgallery', 'web', 'categories');
		$rc = ciniki_artgallery_web_categories($ciniki, $settings, $ciniki['request']['business_id'], array());
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( isset($rc['categories']) ) {
			foreach($rc['categories'] as $category) {
				$submenu[$category['permalink']] = array('name'=>$category['name'],
					'url'=>$ciniki['request']['base_url'] . '/exhibitions/category/' . $category['permalink']);
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
	$rc = ciniki_web_generatePageHeader($ciniki, $settings, $page_title, $submenu);
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
