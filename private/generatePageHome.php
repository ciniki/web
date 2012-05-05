<?php
//
// Description
// -----------
// This function will generate the home page for the website
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_web_generatePageHome($ciniki, $settings) {

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
	// Add the header
	//
	require_once($ciniki['config']['core']['modules_dir'] . '/web/private/generatePageHeader.php');
	$rc = ciniki_web_generatePageHeader($ciniki, $settings, 'Home');
	if( $rc['stat'] != 'ok' ) {	
		return $rc;
	}
	$content .= $rc['content'];

	
	//
	// Generate the content of the page
	//
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbDetailsQueryDash.php');
	$rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_web_content', 'business_id', $ciniki['request']['business_id'], 'web', 'content', 'page-home');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	if( isset($rc['content']['page-home-content']) ) {
		require_once($ciniki['config']['core']['modules_dir'] . '/web/private/processContent.php');
		$rc = ciniki_web_processContent($ciniki, $rc['content']['page-home-content']);	
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$page_content = $rc['content'];
	}

	$content .= "<div id='content'>\n"
		. "";
	if( $page_content != '' ) {
		$content .= "<article class='page'>\n"
			. "<div class='entry-content'>\n"
			. $page_content
			. "</div>"
			. "</article>"
			. "";
	}

	//
	// List the latest work
	//
	if( isset($ciniki['business']['modules']['ciniki.artcatalog']) 
		&& $settings['page-gallery-active'] == 'yes' ) {

		require_once($ciniki['config']['core']['modules_dir'] . '/artcatalog/web/latestImages.php');
		$rc = ciniki_artcatalog_web_latestImages($ciniki, $settings, $ciniki['request']['business_id'], 6);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$images = $rc['images'];

		require_once($ciniki['config']['core']['modules_dir'] . '/web/private/generatePageGalleryThumbnails.php');
		$rc = ciniki_web_generatePageGalleryThumbnails($ciniki, $settings, $rc['images'], 150);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$content .= "<article class='page'>\n"
			. "<header class='entry-title'><h1 class='entry-title'>Latest Work</h1></header>\n"
			. "<div class='image-gallery'>" . $rc['content'] . "</div>"
			. "</article>\n"
			. "";
	}

	//
	// List any upcoming events
	//
	if( isset($ciniki['business']['modules']['ciniki.events']) 
		&& $settings['page-events-active'] == 'yes' ) {
		//
		// Load and parse the events
		//
		require_once($ciniki['config']['core']['modules_dir'] . '/events/web/list.php');
		$rc = ciniki_events_web_list($ciniki, $ciniki['request']['business_id'], 'upcoming', 3);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$number_of_events = count($rc['events']);
		if( isset($rc['events']) && $number_of_events > 0 ) {
			$events = $rc['events'];
			require_once($ciniki['config']['core']['modules_dir'] . '/web/private/processEvents.php');
			$rc = ciniki_web_processEvents($ciniki, $settings, $events, 2);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$content .= "<article class='page'>\n"
				. "<header class='entry-title'><h1 class='entry-title'>Upcoming Events</h1></header>\n"
				. $rc['content']
				. "";
			if( $number_of_events > 2 ) {
				$content .= "<div class='events-more'><a href='" . $ciniki['request']['base_url'] . "/events'>... more events</a></div>";
			}
			$content .= "</article>\n"
				. "";
		}
	}

	$content .= "</div>"
		. "";

	//
	// Add the footer
	//
	require_once($ciniki['config']['core']['modules_dir'] . '/web/private/generatePageFooter.php');
	$rc = ciniki_web_generatePageFooter($ciniki, $settings);
	if( $rc['stat'] != 'ok' ) {	
		return $rc;
	}
	$content .= $rc['content'];

	return array('stat'=>'ok', 'content'=>$content);
}
?>
