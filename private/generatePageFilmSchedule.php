<?php
//
// Description
// -----------
// This function will generate the film schedule page for the business.
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_generatePageFilmSchedule($ciniki, $settings) {

	//
	// Check if a file was specified to be downloaded
	//
	$download_err = '';
	if( isset($ciniki['business']['modules']['ciniki.filmschedule'])
		&& isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] != ''
		&& isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] == 'download'
		&& isset($ciniki['request']['uri_split'][2]) && $ciniki['request']['uri_split'][2] != '' ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'filmschedule', 'web', 'fileDownload');
		$rc = ciniki_filmschedule_web_fileDownload($ciniki, $ciniki['request']['business_id'], $ciniki['request']['uri_split'][0], $ciniki['request']['uri_split'][2]);
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
		return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'2377', 'msg'=>'The file you requested does not exist.'));
	}

	//
	// Store the content created by the page
	// Make sure everything gets generated ok before returning the content
	//
	$submenu = array();
	$content = '';
	$page_content = '';
	$page_title = 'Schedule';
	$page_name = 'Schedule';	// Used in listings, tags etc, no always the same as page_title
	if( isset($settings['page-filmschedule-title']) && $settings['page-filmschedule-title'] != '' ) {
		$page_title = $settings['page-filmschedule-title'];
		$page_name = $settings['page-filmschedule-title'];
	}
	$ciniki['response']['head']['og']['url'] = $ciniki['request']['domain_base_url'] . '/schedule';

	//
	// FIXME: Check if anything has changed, and if not load from cache
	//

	$display_event_list = 'yes';

	//
	// Check if we are to display an image, from the gallery, or latest images
	//
	if( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] != '' 
		&& isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] == 'gallery' 
		&& isset($ciniki['request']['uri_split'][2]) && $ciniki['request']['uri_split'][2] != '' 
		) {
		$display_event_list = 'no';
		$event_permalink = $ciniki['request']['uri_split'][0];
		$image_permalink = $ciniki['request']['uri_split'][2];

		//
		// Load the event to get all the details, and the list of images.
		// It's one query, and we can find the requested image, and figure out next
		// and prev from the list of images returned
		//
		ciniki_core_loadMethod($ciniki, 'ciniki', 'filmschedule', 'web', 'eventDetails');
		$rc = ciniki_filmschedule_web_eventDetails($ciniki, $settings, $ciniki['request']['business_id'], $event_permalink);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$event = $rc['event'];

		if( !isset($event['images']) || count($event['images']) < 1 ) {
			return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'2483', 'msg'=>"We're sorry, but we could not find the image you requested."));
		}

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

		if( isset($event['synopsis']) && $event['synopsis'] != '' ) {
			$ciniki['response']['head']['og']['description'] = strip_tags($event['synopsis']);
		} elseif( isset($event['description']) && $event['description'] != '' ) {
			$ciniki['response']['head']['og']['description'] = strip_tags($event['description']);
		}
		
		if( !isset($event['images']) || count($event['images']) < 1 ) {
			return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'2493', 'msg'=>"I'm sorry, but we don't seem to have the photo you requested."));
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

		$article_title = '<a href="' . $ciniki['request']['base_url'] . "/schedule/$event_permalink\">" . $event['name'] . "</a>";
		if( $img['title'] != '' ) {
			$page_title = $event['name'] . ' - ' . $img['title'];
			$article_title .= ' - ' . $img['title'];
		} else {
			$page_title = $event['name'];
		}
	
		//
		// Load the image
		//
		if( isset($img['image_id']) && $img['image_id'] > 0 ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
			$rc = ciniki_web_getScaledImageURL($ciniki, $img['image_id'], 'original', 0, 600);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$img_url = $rc['url'];
		} else {
			return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'2494', 'msg'=>"We're sorry, but the image you requested does not exist."));
		}

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
		$gallery_url = $ciniki['request']['base_url'] . "/schedule/" . $event_permalink . "/gallery";
		if( $prev != null ) {
			$page_content .= "<a id='gallery-image-prev' class='gallery-image-prev' href='$gallery_url/" . $prev['permalink'] . "'><div id='gallery-image-prev-img'></div></a>";
		}
		if( $next != null ) {
			$page_content .= "<a id='gallery-image-next' class='gallery-image-next' href='$gallery_url/" . $next['permalink'] . "'><div id='gallery-image-next-img'></div></a>";
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
		$display_event_list = 'no';
		ciniki_core_loadMethod($ciniki, 'ciniki', 'filmschedule', 'web', 'eventDetails');
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processURL');
		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'processDateRange');

		//
		// Get the event information
		//
		$event_permalink = $ciniki['request']['uri_split'][0];
		$ciniki['response']['head']['og']['url'] .= '/' . $event_permalink;
		$rc = ciniki_filmschedule_web_eventDetails($ciniki, $settings, $ciniki['request']['business_id'], $event_permalink);
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
			if( isset($event['start_time']) && $event['start_time'] != '' ) {
				$page_content .= "<br/>" . $event['start_time'];
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

		if( isset($event['synopsis']) && $event['synopsis'] != '' ) {
			$ciniki['response']['head']['og']['description'] = strip_tags($event['synopsis']);
		} elseif( isset($event['description']) && $event['description'] != '' ) {
			$ciniki['response']['head']['og']['description'] = strip_tags($event['description']);
		}
		
		//
		// Add description
		//
		if( isset($event['description']) && $event['description'] != '' ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
			$rc = ciniki_web_processContent($ciniki, $settings, $event['description']);	
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= $rc['content'];
		} elseif( isset($event['synopsis']) ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
			$rc = ciniki_web_processContent($ciniki, $settings, $event['synopsis']);	
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= $rc['content'];
		}

		//
		// Display the links for the event
		//
		if( isset($event['links']) && count($event['links']) > 0 ) {
			$page_content .= "<p>";
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processURL');
			foreach($event['links'] as $link) {
				$rc = ciniki_web_processURL($ciniki, $link['url']);
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				if( $rc['url'] != '' ) {
					$page_content .= "<a target='_blank' href='" . $rc['url'] . "' title='" 
						. ($link['name']!=''?$link['name']:$rc['display']) . "'>" 
						. ($link['name']!=''?$link['name']:$rc['display'])
						. "</a>";
				} else {
					$page_content .= $link['name'];
				}
				if( isset($link['description']) && $link['description'] != '' ) {
					$page_content .= "<br/><span class='downloads-description'>" . $link['description'] . "</span>";
				}
				$page_content .= "<br/>";
			}
			$page_content .= "</p>";
		}

		//
		// Check if share buttons should be shown
		//
		if( !isset($settings['page-filmschedule-share-buttons']) 
			|| $settings['page-filmschedule-share-buttons'] == 'yes' ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processShareButtons');
			$rc = ciniki_web_processShareButtons($ciniki, $settings, array(
				'title'=>$page_title,
				'tags'=>array($page_name),
				));
			if( $rc['stat'] == 'ok' ) {
				$page_content .= $rc['content'];
			}
		}

		//
		// Display the additional images for the event
		//
		if( isset($event['images']) && count($event['images']) > 0 ) {
			$page_content .= "<br style='clear: right;'/>";
			$page_content .= "<h2>Gallery</h2>";
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageGalleryThumbnails');
			$img_base_url = $ciniki['request']['base_url'] . "/schedule/" . $event['permalink'] . "/gallery";
			$rc = ciniki_web_generatePageGalleryThumbnails($ciniki, $settings, $img_base_url, $event['images'], 125);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= "<div class='image-gallery'>" . $rc['content'] . "</div>";
		}

		//
		// Display any sponsors for the event
		//
		if( isset($event['sponsors']['sponsors']) && count($event['sponsors']['sponsors']) > 0 ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processSponsorsSection');
			$rc = ciniki_web_processSponsorsSection($ciniki, $settings, $event['sponsors']);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= $rc['content'];
		}

		$page_content .= "<br style='clear: right;'/>";
		$page_content .= "</article>";
	}

	//
	// Check if the event list is to be displayed
	//
	if( $display_event_list == 'yes' ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'filmschedule', 'web', 'eventList');
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processURL');
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processFilmScheduleEvents');

		//
		// Check if there is content for the landing page
		//
		$upcoming_title = 'Upcoming ' . $page_name;
		$past_title = 'Past '. $page_name;

		$rc = ciniki_filmschedule_web_eventList($ciniki, $settings, $ciniki['request']['business_id'], 
			array('type'=>'upcoming'));
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$events = $rc['events'];

		if( isset($upcoming_title) && $upcoming_title != '' ) {
			$page_content .= "<h2>" . $upcoming_title . "</h2>";
		}

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

			$rc = ciniki_web_processFilmScheduleEvents($ciniki, $settings, $events, 0);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= $rc['content'];
		} else {
			$page_content .= "<p>Currently no upcoming films.</p>";
		}

		//
		// Include past events if the user wants
		//
		if( isset($settings['page-filmschedule-past']) && $settings['page-filmschedule-past'] == 'yes' ) {
			//
			// Generate the content of the page
			//
			ciniki_core_loadMethod($ciniki, 'ciniki', 'filmschedule', 'web', 'eventList');
			$rc = ciniki_filmschedule_web_eventList($ciniki, $settings, $ciniki['request']['business_id'], 
				array('type'=>'past', 'limit'=>'10'));
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$events = $rc['events'];

			if( isset($past_title) && $past_title != '' ) {
				$page_content .= "<br style='clear:both;'/>\n";
				$page_content .= "<h2>" . $past_title . "</h2>\n";
			}

			if( count($events) > 0 ) {
				$rc = ciniki_web_processFilmScheduleEvents($ciniki, $settings, $events, 0);
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				$page_content .= $rc['content'];
			} else {
				$page_content .= "<p>No past events.</p>";
			}
			
			//
			// Close the content and article
			//
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
	$rc = ciniki_web_generatePageHeader($ciniki, $settings, $page_title, $submenu);
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
