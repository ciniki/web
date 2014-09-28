<?php
//
// Description
// -----------
// This function will generate the events page for the business.
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_generatePageEvents($ciniki, $settings) {

	//
	// Check if a file was specified to be downloaded
	//
	$download_err = '';
	if( isset($ciniki['business']['modules']['ciniki.events'])
		&& isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] != ''
		&& isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] == 'download'
		&& isset($ciniki['request']['uri_split'][2]) && $ciniki['request']['uri_split'][2] != '' ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'events', 'web', 'fileDownload');
		$rc = ciniki_events_web_fileDownload($ciniki, $ciniki['request']['business_id'], $ciniki['request']['uri_split'][0], $ciniki['request']['uri_split'][2]);
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
		return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'1348', 'msg'=>'The file you requested does not exist.'));
	}

	//
	// Store the content created by the page
	// Make sure everything gets generated ok before returning the content
	//
	$content = '';
	$page_content = '';
	$page_title = 'Events';
	$ciniki['response']['head']['og']['url'] = $ciniki['request']['domain_base_url'] . '/events';

	//
	// FIXME: Check if anything has changed, and if not load from cache
	//

	//
	// Check if we are to display the gallery image for an events
	//
	//
	// Check if we are to display an image, from the gallery, or latest images
	//
	if( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] != '' 
		&& isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] == 'gallery' 
		&& isset($ciniki['request']['uri_split'][2]) && $ciniki['request']['uri_split'][2] != '' 
		) {
		$event_permalink = $ciniki['request']['uri_split'][0];
		$image_permalink = $ciniki['request']['uri_split'][2];

		//
		// Load the event to get all the details, and the list of images.
		// It's one query, and we can find the requested image, and figure out next
		// and prev from the list of images returned
		//
		ciniki_core_loadMethod($ciniki, 'ciniki', 'events', 'web', 'eventDetails');
		$rc = ciniki_events_web_eventDetails($ciniki, $settings, 
			$ciniki['request']['business_id'], $event_permalink);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$event = $rc['event'];

		//
		// Setup sharing information
		//
		$ciniki['response']['head']['og']['url'] .= '/' . $event_permalink;
		if( isset($event['image_id']) && $event['image_id'] > 0 ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
			$rc = ciniki_web_getScaledImageURL($ciniki, $event['image_id'], 'original', '500', 0);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$ciniki['response']['head']['og']['image'] = $rc['domain_url'];
		}
		if( isset($event['short_description']) && $event['short_description'] != '' ) {
			$ciniki['response']['head']['og']['description'] = strip_tags($event['short_description']);
		} elseif( isset($event['description']) && $event['description'] != '' ) {
			$ciniki['response']['head']['og']['description'] = strip_tags($event['description']);
		}
		
		if( !isset($event['images']) || count($event['images']) < 1 ) {
			return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1287', 'msg'=>'Unable to find image'));
		}

		$first = NULL;
		$last = NULL;
		$img = NULL;
		$next = NULL;
		$prev = NULL;
		foreach($event['images'] as $iid => $image) {
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

		if( count($event['images']) == 1 ) {
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
			$page_title = $event['name'] . ' - ' . $img['title'];
		} else {
			$page_title = $event['name'];
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
	// Check if we are to display an event
	//
	elseif( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] != '' ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'events', 'web', 'eventDetails');
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processURL');
		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'processDateRange');

		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'shortenURL');
		$surl = ciniki_web_shortenURL($ciniki, $ciniki['request']['business_id'], 
			$ciniki['response']['head']['og']['url']);

		//
		// Get the event information
		//
		$event_permalink = $ciniki['request']['uri_split'][0];
		$ciniki['response']['head']['og']['url'] .= '/' . $event_permalink;
		$rc = ciniki_events_web_eventDetails($ciniki, $settings, 
			$ciniki['request']['business_id'], $event_permalink);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$event = $rc['event'];
		$page_title = $event['name'];
		$page_content .= "<article class='page'>\n"
			. "<header class='entry-title'><h1 class='entry-title'>" . $event['name'] . "</h1>";
		$meta_content = '';
		$rc = ciniki_core_processDateRange($ciniki, $event);
		$meta_content .= $rc['dates'];
		if( $meta_content != '' ) {
			$page_content .= "<div class='entry-meta'>" . $meta_content;
			if( isset($event['times']) && $event['times'] != '' ) {
				$page_content .= "<br/>" . $event['times'];
			}
			$page_content .= "</div>";
		}
		$page_content .= "</header>\n";

		//
		// Add primary image
		//
		if( isset($event['image_id']) && $event['image_id'] > 0 ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
			$rc = ciniki_web_getScaledImageURL($ciniki, $event['image_id'], 'original', '500', 0);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$ciniki['response']['head']['og']['image'] = $rc['domain_url'];
			$page_content .= "<aside><div class='image-wrap'><div class='image'>"
				. "<img title='' alt='" . $event['name'] . "' src='" . $rc['url'] . "' />"
				. "</div></div></aside>";
		}

		if( isset($event['short_description']) && $event['short_description'] != '' ) {
			$ciniki['response']['head']['og']['description'] = strip_tags($event['short_description']);
		} elseif( isset($event['description']) && $event['description'] != '' ) {
			$ciniki['response']['head']['og']['description'] = strip_tags($event['description']);
		}
		
		//
		// Add description
		//
		if( isset($event['description']) && $event['description'] != '' ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
			$rc = ciniki_web_processContent($ciniki, $event['description']);	
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= $rc['content'];
		} elseif( isset($event['short_description']) ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
			$rc = ciniki_web_processContent($ciniki, $event['short_description']);	
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= $rc['content'];
		}

		if( isset($event['url']) ) {
			$rc = ciniki_web_processURL($ciniki, $event['url']);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$url = $rc['url'];
			$display_url = $rc['display'];
		} else {
			$url = '';
		}

		if( $url != '' ) {
			$page_content .= "<p>Website: <a class='cilist-url' target='_blank' href='" . $url . "' title='" . $event['name'] . "'>" . $display_url . "</a></p>";
		}

		//
		// List the prices for the course
		//
		if( isset($event['prices']) && count($event['prices']) > 0 ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'cartSetupPrices');
			$rc = ciniki_web_cartSetupPrices($ciniki, $settings, $ciniki['request']['business_id'], 
				$event['prices']);
			if( $rc['stat'] != 'ok' ) {
				error_log("Error in formatting prices.");
			} else {
				$page_content .= $rc['content'];
			}
		}
//		if( isset($event['prices']) && count($event['prices']) > 0 ) {
//			$page_content .= "<h2>Price</h2><p>";
//			foreach($event['prices'] as $pid => $price) {
//				if( $price['name'] != '' ) {
//					$page_content .= $price['name'] . " - " . $price['unit_amount_display'] . "<br/>";
//				} else {
//					$page_content .= $price['unit_amount_display'] . "<br/>";
//				}
//			}
//			$page_content .= "</p>";
//		}

		//
		// Display the files for the events
		//
		if( isset($event['files']) && count($event['files']) > 0 ) {
			$page_content .= "<p>";
			foreach($event['files'] as $file) {
				$url = $ciniki['request']['base_url'] . '/events/' . $ciniki['request']['uri_split'][0] . '/download/' . $file['permalink'] . '.' . $file['extension'];
//				$page_content .= "<span class='downloads-title'>";
				if( $url != '' ) {
					$page_content .= "<a target='_blank' href='" . $url . "' title='" . $file['name'] . "'>" . $file['name'] . "</a>";
				} else {
					$page_content .= $file['name'];
				}
//				$page_content .= "</span>";
				if( isset($file['description']) && $file['description'] != '' ) {
					$page_content .= "<br/><span class='downloads-description'>" . $file['description'] . "</span>";
				}
				$page_content .= "<br/>";
			}
			$page_content .= "</p>";
		}


		//
		// Display the additional images for the event
		//
		if( isset($event['images']) && count($event['images']) > 0 ) {
//			$page_content .= "<article class='page'>"	
//				. "<header class='entry-title'><h1 class='entry-title'>Gallery</h1></header>\n"
//				. "";
			$page_content .= "<br style='clear: right;'/>";
			$page_content .= "<h2>Gallery</h2>";
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageGalleryThumbnails');
			$img_base_url = $ciniki['request']['base_url'] . "/events/" . $event['permalink'] . "/gallery";
			$rc = ciniki_web_generatePageGalleryThumbnails($ciniki, $settings, $img_base_url, $event['images'], 125);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= "<div class='image-gallery'>" . $rc['content'] . "</div>";
//			$page_content .= "</article>";
		}

		//
		// Display any sponsors for the event
		//
		if( isset($event['sponsors']) && count($event['sponsors']) > 0 ) {
			$page_content .= "<h2>Sponsors</h2>";
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processSponsorImages');
			$img_base_url = $ciniki['request']['base_url'] . "/sponsors/";
			$rc = ciniki_web_processSponsorImages($ciniki, $settings, $img_base_url, $event['sponsors'], 125);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= "<div class='sponsor-gallery'>" . $rc['content'] . "</div>";
		}

		$page_content .= "</article>";
	}

	//
	// Display the list of events if a specific one isn't selected
	//
	else {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'events', 'web', 'eventList');
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processURL');
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processEvents');
		$ciniki['response']['head']['og']['description'] = strip_tags('Upcoming Events');

		$rc = ciniki_events_web_eventList($ciniki, $settings, $ciniki['request']['business_id'], 'upcoming', 0);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$events = $rc['events'];

		$page_content .= "<article class='page'>\n"
			. "<header class='entry-title'><h1 class='entry-title'>Upcoming Events</h1></header>\n"
			. "<div class='entry-content'>\n"
			. "";

		if( count($events) > 0 ) {
			//
			// Check events to find an image if there isn't a logo
			//
			if( $ciniki['response']['head']['og']['image'] == '' ) {
				foreach($events as $eid => $event) {
					if( isset($event['image_id']) && $event['image_id'] != '' && $event['image_id'] ) {
						ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
						$rc = ciniki_web_getScaledImageURL($ciniki, $event['image_id'], 'original', '500', 0);
						if( $rc['stat'] != 'ok' ) {
							return $rc;
						}
						$ciniki['response']['head']['og']['image'] = $rc['domain_url'];
					}
				}
			}

			$rc = ciniki_web_processEvents($ciniki, $settings, $events, 0);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= $rc['content'];
		} else {
			$page_content .= "<p>Currently no events.</p>";
		}

		$page_content .= "</div>\n"
			. "</article>\n"
			. "";
		//
		// Include past events if the user wants
		//
		if( isset($settings['page-events-past']) && $settings['page-events-past'] == 'yes' ) {
			//
			// Generate the content of the page
			//
			ciniki_core_loadMethod($ciniki, 'ciniki', 'events', 'web', 'eventList');
			$rc = ciniki_events_web_eventList($ciniki, $settings, $ciniki['request']['business_id'], 'past', 10);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$events = $rc['events'];

			$page_content .= "<article class='page'>\n"
				. "<header class='entry-title'><h1 class='entry-title'>Past Events</h1></header>\n"
				. "<div class='entry-content'>\n"
				. "";

			if( count($events) > 0 ) {
				$rc = ciniki_web_processEvents($ciniki, $settings, $events, 0);
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				$page_content .= $rc['content'];
			} else {
				$page_content .= "<p>No past events.</p>";
			}

			$page_content .= "</div>\n"
				. "</article>\n"
				. "";
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
		. "<br style='clear:both;' />\n"
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
