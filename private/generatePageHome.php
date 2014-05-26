<?php
//
// Description
// -----------
// This function will generate the home page for the website.
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_generatePageHome(&$ciniki, $settings) {

	//
	// Store the content created by the page
	// Make sure everything gets generated ok before returning the content
	//
	$content = '';
	$content1 = '';
	$page_content = '';
	
	//
	// Setup facebook content defaults
	//
	$ciniki['response']['head']['facebook']['og:title'] = 'Home';
	$ciniki['response']['head']['facebook']['og:url'] = $ciniki['request']['domain_base_url'];

//	$content = "<pre>" . print_r($ciniki, true) . "</pre>";

	//
	// FIXME: Check if anything has changed, and if not load from cache
	//

	if( isset($settings['page-home-image']) && $settings['page-home-image'] != '' && $settings['page-home-image'] > 0 ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
		$rc = ciniki_web_getScaledImageURL($ciniki, $settings['page-home-image'], 'original', '500', 0);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$href = '';
		$_href = '';
		if( isset($settings['page-home-image-url']) && $settings['page-home-image-url'] != '' ) {
			$href = "<a href='" . $settings['page-home-image-url'] . "'>";
			$_href = "</a>";
		}
		$content1 .= "<aside><div class='image-wrap'>"
			. "<div class='image'>$href<img title='' alt='" . $ciniki['business']['details']['name'] . "' src='" . $rc['url'] . "' />$_href</div>";
		$ciniki['response']['head']['facebook']['og:image'] = $rc['domain_url'];
		if( isset($settings['page-home-image-caption']) && $settings['page-home-image-caption'] != '' ) {
			$content1 .= "<div class='image-caption'>$href" . $settings['page-home-image-caption'] . "$_href</div>";
		}
		$content1 .= "</div></aside>";
	}

	//
	// Generate the content of the page
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash');
	$rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_web_content', 'business_id', $ciniki['request']['business_id'], 'ciniki.web', 'content', 'page-home');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	if( isset($rc['content']['page-home-og:description']) && $rc['content']['page-home-og:description'] != '' ) {
		$ciniki['response']['head']['facebook']['og:description'] = strip_tags($rc['content']['page-home-og:description']);
	} elseif( isset($rc['content']['page-home-content']) ) {
		$ciniki['response']['head']['facebook']['og:description'] = strip_tags($rc['content']['page-home-content']);
	}


	if( isset($rc['content']['page-home-content']) ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
		$rc = ciniki_web_processContent($ciniki, $rc['content']['page-home-content']);	
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$content1 .= $rc['content'];
	}

	$page_content .= "<div id='content'>\n"
		. "";
	if( $page_content != '' ) {
		$page_content .= "<article class='page'>\n"
			. "<div class='entry-content'>\n"
			. $content1
			. "</div>"
			. "</article>"
			. "";
	}

	//
	// List the latest work
	//
	if( isset($ciniki['business']['modules']['ciniki.artcatalog']) 
		&& isset($settings['page-gallery-active']) && $settings['page-gallery-active'] == 'yes' 
		&& (!isset($settings['page-home-gallery-latest']) || $settings['page-home-gallery-latest'] == 'yes') 
		) {

		ciniki_core_loadMethod($ciniki, 'ciniki', 'artcatalog', 'web', 'latestImages');
		$rc = ciniki_artcatalog_web_latestImages($ciniki, $settings, $ciniki['request']['business_id'], 6);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$images = $rc['images'];

		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageGalleryThumbnails');
		$img_base_url = $ciniki['request']['base_url'] . "/gallery/latest";
		$rc = ciniki_web_generatePageGalleryThumbnails($ciniki, $settings, $img_base_url, $rc['images'], 150);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$page_content .= "<article class='page'>\n"
			. "<header class='entry-title'><h1 class='entry-title'>";
		if( isset($settings['page-home-gallery-latest-title']) && $settings['page-home-gallery-latest-title'] != '' ) {
			$page_content .= $settings['page-home-gallery-latest-title'];
		} else {
			$page_content .= "Latest Work";
		}
		$page_content .= "</h1></header>\n"
			. "<div class='image-gallery'>" . $rc['content'] . "</div>"
			. "</article>\n"
			. "";
	}

	//
	// List the random gallery images
	//
	if( isset($ciniki['business']['modules']['ciniki.artcatalog']) 
		&& isset($settings['page-gallery-active']) && $settings['page-gallery-active'] == 'yes' 
		&& isset($settings['page-home-gallery-random']) && $settings['page-home-gallery-random'] == 'yes' 
		) {

		ciniki_core_loadMethod($ciniki, 'ciniki', 'artcatalog', 'web', 'randomImages');
		$rc = ciniki_artcatalog_web_randomImages($ciniki, $settings, $ciniki['request']['business_id'], 6);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$images = $rc['images'];

		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageGalleryThumbnails');
		$img_base_url = $ciniki['request']['base_url'] . "/gallery/image";
		$rc = ciniki_web_generatePageGalleryThumbnails($ciniki, $settings, $img_base_url, $rc['images'], 150);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$page_content .= "<article class='page'>\n"
			. "<header class='entry-title'><h1 class='entry-title'>";
		if( isset($settings['page-home-gallery-random-title']) && $settings['page-home-gallery-random-title'] != '' ) {
			$page_content .= $settings['page-home-gallery-random-title'];
		} else {
			$page_content .= 'Example Work';
		}
		$page_content .= "</h1></header>\n"
			. "<div class='image-gallery'>" . $rc['content'] . "</div>"
			. "</article>\n"
			. "";
	}

	//
	// List any upcoming exhibitions
	//
	if( isset($ciniki['business']['modules']['ciniki.artgallery']) 
		&& isset($settings['page-artgalleryexhibitions-active']) && $settings['page-artgalleryexhibitions-active'] == 'yes' 
		&& (!isset($settings['page-home-upcoming-artgalleryexhibitions']) || $settings['page-home-upcoming-artgalleryexhibitions'] == 'yes') 
		) {
		//
		// Load and parse the events
		//
		ciniki_core_loadMethod($ciniki, 'ciniki', 'artgallery', 'web', 'exhibitionList');
		$rc = ciniki_artgallery_web_exhibitionList($ciniki, $settings, $ciniki['request']['business_id'], 'upcoming', 3);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$number_of_exhibitions = count($rc['exhibitions']);
		if( isset($rc['exhibitions']) && $number_of_exhibitions > 0 ) {
			$exhibitions = $rc['exhibitions'];
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processExhibitions');
			$rc = ciniki_web_processExhibitions($ciniki, $settings, $exhibitions, 2);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= "<article class='page'>\n"
				. "<header class='entry-title'><h1 class='entry-title'>Upcoming Exhibitions</h1></header>\n"
				. $rc['content']
				. "";
			if( $number_of_exhibitions > 2 ) {
				$page_content .= "<div class='events-more'><a href='" . $ciniki['request']['base_url'] . "/exhibitions'>... more exhibitions</a></div>";
			}
			$page_content .= "</article>\n"
				. "";
		}
	}

	//
	// List the latest recipes
	//
	if( isset($ciniki['business']['modules']['ciniki.recipes']) 
		&& isset($settings['page-recipes-active']) && $settings['page-recipes-active'] == 'yes' 
		&& (!isset($settings['page-home-latest-recipes']) || $settings['page-home-latest-recipes'] == 'yes') 
		) {
		//
		// Load and parse the recipes
		//
		ciniki_core_loadMethod($ciniki, 'ciniki', 'recipes', 'web', 'latest');
		$rc = ciniki_recipes_web_latest($ciniki, $settings, $ciniki['request']['business_id'], 3);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$number_of_recipes = count($rc['recipes']);
		if( isset($rc['recipes']) && $number_of_recipes > 0 ) {
			$recipes = $rc['recipes'];
			$base_url = $ciniki['request']['base_url'] . "/recipes";
			$rc = ciniki_web_processCIList($ciniki, $settings, $base_url, array('0'=>array(
				'name'=>'', 'noimage'=>'/ciniki-web-layouts/default/img/noimage_240.png',
				'list'=>$recipes)), array('limit'=>2));
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= "<article class='page'>\n"
				. "<header class='entry-title'><h1 class='entry-title'>Latest Recipes</h1></header>\n"
				. $rc['content']
				. "";
			if( $number_of_recipes > 2 ) {
				$page_content .= "<div class='events-more'><a href='" . $ciniki['request']['base_url'] . "/recipes'>... more recipes</a></div>";
			}
			$page_content .= "</article>\n"
				. "";
		}
	}


	//
	// List any upcoming workshops
	//
	if( isset($ciniki['business']['modules']['ciniki.workshops']) 
		&& isset($settings['page-workshops-active']) && $settings['page-workshops-active'] == 'yes' 
		&& (!isset($settings['page-home-upcoming-workshops']) || $settings['page-home-upcoming-workshops'] == 'yes') 
		) {
		//
		// Load and parse the workshops
		//
		ciniki_core_loadMethod($ciniki, 'ciniki', 'workshops', 'web', 'workshopList');
		$rc = ciniki_workshops_web_workshopList($ciniki, $settings, $ciniki['request']['business_id'], 'upcoming', 3);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$number_of_workshops = count($rc['workshops']);
		if( isset($rc['workshops']) && $number_of_workshops > 0 ) {
			$workshops = $rc['workshops'];
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processWorkshops');
			$rc = ciniki_web_processWorkshops($ciniki, $settings, $workshops, 2);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= "<article class='page'>\n"
				. "<header class='entry-title'><h1 class='entry-title'>Upcoming Workshops</h1></header>\n"
				. $rc['content']
				. "";
			if( $number_of_workshops > 2 ) {
				$page_content .= "<div class='events-more'><a href='" . $ciniki['request']['base_url'] . "/workshops'>... more workshops</a></div>";
			}
			$page_content .= "</article>\n"
				. "";
		}
	}

	//
	// List any upcoming events
	//
	if( isset($ciniki['business']['modules']['ciniki.events']) 
		&& isset($settings['page-events-active']) && $settings['page-events-active'] == 'yes' 
		&& (!isset($settings['page-home-upcoming-events']) || $settings['page-home-upcoming-events'] == 'yes') 
		) {
		//
		// Load and parse the events
		//
		ciniki_core_loadMethod($ciniki, 'ciniki', 'events', 'web', 'eventList');
		$rc = ciniki_events_web_eventList($ciniki, $settings, $ciniki['request']['business_id'], 'upcoming', 3);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$number_of_events = count($rc['events']);
		if( isset($rc['events']) && $number_of_events > 0 ) {
			$events = $rc['events'];
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processEvents');
			$rc = ciniki_web_processEvents($ciniki, $settings, $events, 2);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= "<article class='page'>\n"
				. "<header class='entry-title'><h1 class='entry-title'>Upcoming Events</h1></header>\n"
				. $rc['content']
				. "";
			if( $number_of_events > 2 ) {
				$page_content .= "<div class='events-more'><a href='" . $ciniki['request']['base_url'] . "/events'>... more events</a></div>";
			}
			$page_content .= "</article>\n"
				. "";
		}
	}


	$page_content .= "</div>"
		. "";

	//
	// Add the header
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageHeader');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processCIList');
	$rc = ciniki_web_generatePageHeader($ciniki, $settings, 'Home', array());
	if( $rc['stat'] != 'ok' ) {	
		return $rc;
	}
	$content .= $rc['content'];

	$content .= $page_content;

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
