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
//			header('Content-Disposition: attachment;filename="' . $file['filename'] . '"');
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
	$submenu = array();
	$content = '';
	$page_content = '';
	$page_title = 'Events';
	$page_name = 'Events';	// Used in listings, tags etc, no always the same as page_title
	if( isset($settings['page-events-title']) && $settings['page-events-title'] != '' ) {
		$page_title = $settings['page-events-title'];
		$page_name = $settings['page-events-title'];
	}
	$ciniki['response']['head']['og']['url'] = $ciniki['request']['domain_base_url'] . '/events';

	//
	// FIXME: Check if anything has changed, and if not load from cache
	//

	$display_event_list = 'yes';
	$tag_type = 10;
	$tag_permalink = '';

	//
	// Check if we are to display a category
	//
	if( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] == 'category' 
		&& isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] != '' 
		) {
		$tag_type = 10;
		$tag_permalink = $ciniki['request']['uri_split'][1];
	}

	//
	// Check if we are to display an image, from the gallery, or latest images
	//
	elseif( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] != '' 
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
		ciniki_core_loadMethod($ciniki, 'ciniki', 'events', 'web', 'eventDetails');
		$rc = ciniki_events_web_eventDetails($ciniki, $settings, 
			$ciniki['request']['business_id'], $event_permalink);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$event = $rc['event'];

		if( !isset($event['images']) || count($event['images']) < 1 ) {
			return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'2146', 'msg'=>"We're sorry, but we could not find the image you requested."));
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

		if( isset($event['short_description']) && $event['short_description'] != '' ) {
			$ciniki['response']['head']['og']['description'] = strip_tags($event['short_description']);
		} elseif( isset($event['description']) && $event['description'] != '' ) {
			$ciniki['response']['head']['og']['description'] = strip_tags($event['description']);
		}
		
		if( !isset($event['images']) || count($event['images']) < 1 ) {
			return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'1287', 'msg'=>"I'm sorry, but we don't seem to have the photo you requested."));
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

		$article_title = '<a href="' . $ciniki['request']['base_url'] . "/events/$event_permalink\">" . $event['name'] . "</a>";
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
			return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'2150', 'msg'=>"We're sorry, but the image you requested does not exist."));
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
		$gallery_url = $ciniki['request']['base_url'] . "/events/" . $event_permalink . "/gallery";
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
		ciniki_core_loadMethod($ciniki, 'ciniki', 'events', 'web', 'eventDetails');
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processURL');
		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'processDateRange');

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
		// Check if share buttons should be shown
		//
		if( !isset($settings['page-events-share-buttons']) 
			|| $settings['page-events-share-buttons'] == 'yes' ) {
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
			$img_base_url = $ciniki['request']['base_url'] . "/events/" . $event['permalink'] . "/gallery";
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
		ciniki_core_loadMethod($ciniki, 'ciniki', 'events', 'web', 'eventList');
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processURL');
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processEvents');
		//
		// If categories are enabled, and no category specified
		//
		if( ($ciniki['business']['modules']['ciniki.events']['flags']&0x10) > 0 
			&& isset($settings['page-events-categories-display'])
			&& $settings['page-events-categories-display'] != 'off'
			&& $tag_permalink == ''
			) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'events', 'web', 'tags');
			$rc = ciniki_events_web_tags($ciniki, $settings, $ciniki['request']['business_id'], '10');
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$categories = $rc['tags'];
			
			//
			// Get the first category to display
			//
			if( count($categories) > 0 ) {
				$cat = reset($categories);
				$tag_type = 10;

				if( !isset($settings['page-events-content']) || $settings['page-events-content'] == '' ) {
					$tag_permalink = $cat['permalink'];
				}
				$ciniki['response']['head']['links'][] = array('rel'=>'canonical',
					'href'=>$ciniki['request']['domain_base_url'] . '/events/category/' . $cat['permalink']);
			}
		}

		//
		// Check if there is content for the landing page
		//
		if( ($ciniki['business']['modules']['ciniki.events']['flags']&0x10) > 0 
			&& isset($settings['page-events-categories-display'])
			&& $settings['page-events-categories-display'] != 'off'
			&& isset($settings['page-events-content'])
			&& $settings['page-events-content'] != ''
			&& $tag_permalink == ''
			) {
			$page_content .= "<article class='page'>\n"
				. "<header class='entry-title'><h1 class='entry-title'>$page_name</h1></header>\n"
				. "<div class='entry-content'>\n"
				. "";
			if( isset($settings['page-events-image']) && $settings['page-events-image'] != '' && $settings['page-events-image'] != 0 ) {
				ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
				$rc = ciniki_web_getScaledImageURL($ciniki, $settings['page-events-image'], 'original', '500', 0);
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				$page_content .= "<aside><div class='image-wrap'>"
					. "<div class='image'><img title='' src='" . $rc['url'] . "' /></div>";
				if( isset($settings['page-events-image-caption']) && $settings['page-events-image-caption'] != '' ) {
					$page_content .= "<div class='image-caption'>" . $settings['page-events-image-caption'] . "</div>";
				}
				$page_content .= "</div></aside>";
			}

			$page_content .= "<div class='entry-content'>";
			if( isset($settings['page-events-content']) ) {
				ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
				$rc = ciniki_web_processContent($ciniki, $settings['page-events-content']);	
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				$page_content .= $rc['content'];
			}

			$page_content .= "</div></article>";
		} else {
			$upcoming_title = 'Upcoming ' . $page_name;
			$past_title = 'Past '. $page_name;
			//
			// Get the events
			//
			$rc = ciniki_events_web_eventList($ciniki, $settings, $ciniki['request']['business_id'], 
				array('type'=>'upcoming', 'tag_type'=>$tag_type, 'tag_permalink'=>$tag_permalink));
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$events = $rc['events'];
			//
			// Check if the upcoming event list should be hidden
			//
			$hide_upcoming = 'no';	
			if( count($events) == 0 && isset($settings['page-events-upcoming-empty-hide']) && $settings['page-events-upcoming-empty-hide'] == 'yes' 
				 && isset($settings['page-events-past']) && $settings['page-events-past'] == 'yes' ) {
				 $hide_upcoming = 'yes';
			}

			if( $tag_permalink != '' ) {
				ciniki_core_loadMethod($ciniki, 'ciniki', 'events', 'web', 'tagDetails');
				$rc = ciniki_events_web_tagDetails($ciniki, $settings, $ciniki['request']['business_id'], 
					$tag_type, $tag_permalink);
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				$tag = $rc['tag'];

				if( isset($tag['title-upcoming']) && $tag['title-upcoming'] != '' ) {
					$upcoming_title = $tag['title-upcoming'];
				}
				if( isset($tag['title-past']) && $tag['title-past'] != '' ) {
					$past_title = $tag['title-past'];
				}
			
				//
				// Check if there is content and/or an image to display for the category
				//
				if( isset($tag['synopsis']) && $tag['synopsis'] != '' ) {
					$ciniki['response']['head']['og']['description'] = $tag['synopsis'];
				} else {
					$ciniki['response']['head']['og']['description'] = $tag['title'] . ' - ' . $upcoming_title;
				}
				if( (isset($tag['content']) && $tag['content'] != '') 
					|| (isset($tag['image-id']) && $tag['image-id'] != '' && $tag['image-id'] > 0) 
					) {
					$page_content .= "<article class='page'>\n"
						. "<header class='entry-title'><h1 class='entry-title'>" . $tag['title'] . "</h1></header>\n"
						. "<div class='entry-content'>\n"
						. "";
					
					//
					// Add image
					//
					if( isset($tag['image-id']) && $tag['image-id'] != '' && $tag['image-id'] > 0 ) {
						ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
						$rc = ciniki_web_getScaledImageURL($ciniki, $tag['image-id'], 'original', '500', 0);
						if( $rc['stat'] != 'ok' ) {
							return $rc;
						}
						$ciniki['response']['head']['og']['image'] = $rc['domain_url'];
						$page_content .= "<aside><div class='image-wrap'>"
							. "<div class='image'><img title='' src='" . $rc['url'] . "' /></div>";
						if( isset($tag['image-caption']) && $tag['image-caption'] != '' ) {
							$page_content .= "<div class='image-caption'>" . $tag['image-caption'] . "</div>";
						}
						$page_content .= "</div></aside>";
					}

					//
					// Add content
					//
					if( isset($tag['content']) && $tag['content'] != '' ) {
						ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
						$rc = ciniki_web_processContent($ciniki, $tag['content']);	
						if( $rc['stat'] != 'ok' ) {
							return $rc;
						}
						$page_content .= $rc['content'];
						$page_content .= "<br style='clear: both;'/>";
					}
				} else {
					$page_content .= "<article class='page'>\n"
						. "<header class='entry-title'><h1 class='entry-title'>" . $tag['title'] . ' - ' . $upcoming_title . "</h1></header>\n"
						. "<div class='entry-content'>\n"
						. "";
					$upcoming_title = '';
					$past_title = $tag['title'] . ' - ' . $past_title;
				}
			} else {
				$ciniki['response']['head']['og']['description'] = strip_tags($upcoming_title);
				$page_content .= "<article class='page'>\n";
				//
				// Check if upcoming should be hidden when past visible
				//
				if( $hide_upcoming == 'no' ) {
					$page_content .= "<header class='entry-title'><h1 class='entry-title'>" . $upcoming_title . "</h1></header>\n";
				}
				$upcoming_title = '';
				$page_content .= "<div class='entry-content'>\n";
			}

			if( count($events) > 0 ) {
				if( isset($upcoming_title) && $upcoming_title != '' ) {
					$page_content .= "<h2>" . $upcoming_title . "</h2>";
				}

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
			} elseif( !isset($hide_upcoming) || $hide_upcoming != 'yes' ) {
//			!isset($settings['page-events-upcoming-empty-hide']) 
//				|| ($settings['page-events-upcoming-empty-hide'] != 'yes' && isset($settings['page-events-past']) && $settings['page-events-past'] == 'yes') 
				if( isset($upcoming_title) && $upcoming_title != '' ) {
					$page_content .= "<h2>" . $upcoming_title . "</h2>";
				}
				$page_content .= "<p>Currently no events.</p>";
			}

			//
			// Include past events if the user wants
			//
			if( isset($settings['page-events-past']) && $settings['page-events-past'] == 'yes' ) {
				//
				// Generate the content of the page
				//
				ciniki_core_loadMethod($ciniki, 'ciniki', 'events', 'web', 'eventList');
				$rc = ciniki_events_web_eventList($ciniki, $settings, $ciniki['request']['business_id'], 
					array('type'=>'past', 'limit'=>'10', 'tag_type'=>$tag_type, 'tag_permalink'=>$tag_permalink));
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				$events = $rc['events'];

				if( isset($past_title) && $past_title != '' ) {
					if( isset($hide_upcoming) && $hide_upcoming == 'yes' ) {
						$page_content .= "<header class='entry-title'><h1 class='entry-title'>" . $past_title . "</h1></header>\n";
					} else {
						$page_content .= "<br style='clear:both;'/>\n";
						$page_content .= "<h2>" . $past_title . "</h2>\n";
					}
				}

				if( count($events) > 0 ) {
					$rc = ciniki_web_processEvents($ciniki, $settings, $events, 0);
					if( $rc['stat'] != 'ok' ) {
						return $rc;
					}
					$page_content .= $rc['content'];
				} else {
					$page_content .= "<p>No past events.</p>";
				}
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
	// Decide what items should be in the submenu
	//
	if( ($ciniki['business']['modules']['ciniki.events']['flags']&0x10) > 0 
		&& isset($settings['page-events-categories-display'])
		&& $settings['page-events-categories-display'] == 'submenu'
		) {
		if( !isset($categories) ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'events', 'web', 'tags');
			$rc = ciniki_events_web_tags($ciniki, $settings, $ciniki['request']['business_id'], '10');
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$categories = $rc['tags'];
		}
		if( count($categories) > 1 ) {
			foreach($categories as $cid => $cat) {
				$submenu[$cid] = array('name'=>$cat['tag_name'], 
					'url'=>$ciniki['request']['base_url'] . "/events/category/" . $cat['permalink']);
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
