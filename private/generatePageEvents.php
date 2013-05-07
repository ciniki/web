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
		
		$page_title = $event['name'] . ' - ' . $img['title'];
	
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

		//
		// Get the event information
		//
		$event_permalink = $ciniki['request']['uri_split'][0];
		$rc = ciniki_events_web_eventDetails($ciniki, $settings, 
			$ciniki['request']['business_id'], $event_permalink);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$event = $rc['event'];
		$page_title = $event['name'];
		$page_content .= "<article class='page'>\n"
			. "<header class='entry-title'><h1 class='entry-title'>" . $event['name'] . "</h1></header>\n"
			. "";

		//
		// Add primary image
		//
		if( isset($event['image_id']) && $event['image_id'] > 0 ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
			$rc = ciniki_web_getScaledImageURL($ciniki, $event['image_id'], 'original', '500', 0);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= "<aside><div class='image-wrap'><div class='image'>"
				. "<img title='' alt='" . $event['name'] . "' src='" . $rc['url'] . "' />"
				. "</div></div></aside>";
		}
		
		//
		// Add description
		//
		if( isset($event['description']) ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
			$rc = ciniki_web_processContent($ciniki, $event['description']);	
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
			$page_content .= "<br/>Website: <a class='cilist-url' target='_blank' href='" . $url . "' title='" . $event['name'] . "'>" . $display_url . "</a>";
		}
		$page_content .= "</article>";

		if( isset($event['images']) && count($event['images']) > 0 ) {
			$page_content .= "<article class='page'>"	
				. "<header class='entry-title'><h1 class='entry-title'>Gallery</h1></header>\n"
				. "";
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageGalleryThumbnails');
			$img_base_url = $ciniki['request']['base_url'] . "/events/" . $event['permalink'] . "/gallery";
			$rc = ciniki_web_generatePageGalleryThumbnails($ciniki, $settings, $img_base_url, $event['images'], 125);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= "<div class='image-gallery'>" . $rc['content'] . "</div>";
			$page_content .= "</article>";
		}
	}

	//
	// Display the list of events if a specific one isn't selected
	//
	else {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'events', 'web', 'eventList');
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processURL');
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processEvents');
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
