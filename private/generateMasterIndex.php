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
function ciniki_web_generateMasterIndex($ciniki, $settings) {

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
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageHeader');
	$rc = ciniki_web_generatePageHeader($ciniki, $settings, 'Home');
	if( $rc['stat'] != 'ok' ) {	
		return $rc;
	}
	$content .= $rc['content'];

	
	//
	// Generate the content of the page
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash');
	$rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_web_content', 'business_id', $ciniki['request']['business_id'], 'ciniki.web', 'content', 'page-about');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	if( isset($rc['content']['page-about-content']) ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
		$rc = ciniki_web_processContent($ciniki, $rc['content']['page-about-content']);	
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$page_content = $rc['content'];
	}

	$content .= "<div id='content'>\n"
		. "<article class='page'>\n"
		. "";
	if( isset($settings['page-about-image']) && $settings['page-about-image'] != '' && $settings['page-about-image'] > 0 ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
		$rc = ciniki_web_getScaledImageURL($ciniki, $settings['page-about-image'], 'original', '500', 0);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$content .= "<aside><div class='image borderless'><img title='' alt='About' src='" . $rc['url'] . "' /></div></aside>";
	}

	$content .= "<div class='entry-content'>\n"
		. $page_content
		. "</div>"
		. "</article>"
		. "";

	//
	// Grab the list of businesses from the database and display
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'web', 'featured');
	$rc = ciniki_businesses_web_featured($ciniki, $settings);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$businesses = $rc['businesses'];
	if( count($businesses) > 0 ) {
		$content .= "<article class='page'>\n"
			. "<header class='entry-title'><h1 class='entry-title'>Featured Businesses</h1></header>\n"
			. "<div class='button-list'>"
			. "";
		foreach($businesses as $bnum => $b) {
			$business = $b['business'];
			if( isset($business['domain']) && $business['domain'] != '' ) {
				$url = "http://" . $business['domain'] . "";
			} else {
				$url = "http://" . $ciniki['config']['ciniki.web']['master.domain'] . "/" . $business['sitename'];
			}
			$content .= "<div class='button-list-wrap'><div class='button-list-button'>"
				. "<a title='" . $business['name'] . "' alt='" . $business['name'] . "' href='$url'><span>" . $business['name'] . "</span></a>"
				. "</div></div>"
				. "\n";
		}
		$content .= "</div>"
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
		ciniki_core_loadMethod($ciniki, 'ciniki', 'events', 'web', 'list');
		$rc = ciniki_events_web_list($ciniki, $ciniki['request']['business_id'], 'upcoming', 3);
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
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageFooter');
	$rc = ciniki_web_generatePageFooter($ciniki, $settings);
	if( $rc['stat'] != 'ok' ) {	
		return $rc;
	}
	$content .= $rc['content'];

	return array('stat'=>'ok', 'content'=>$content);
}
?>
