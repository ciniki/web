<?php
//
// Description
// -----------
// This function will generate the writing catalog page for the business.
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_generatePageWritings($ciniki, $settings) {

	//
	// Store the content created by the page
	// Make sure everything gets generated ok before returning the content
	//
	$content = '';
	$page_content = '';
	$page_title = 'Writings';
	$base_url = $ciniki['request']['base_url'] . '/writings';

	if( isset($settings['page-writings-name']) && $settings['page-writings-name'] != '' ) {
		$page_title = $settings['page-writings-name'];
	}


	//
	// Check if we are the display a sample
	//
	if( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] != '' 
		&& isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] == 'sample' 
		&& isset($ciniki['request']['uri_split'][2]) && $ciniki['request']['uri_split'][2] != '' 
		) {
		$writing_permalink = $ciniki['request']['uri_split'][0];
		$sample_permalink = $ciniki['request']['uri_split'][2];

		//
		// Get the writing information
		//
		ciniki_core_loadMethod($ciniki, 'ciniki', 'writingcatalog', 'web', 'writingSample');
		$rc = ciniki_writingcatalog_web_writingSample($ciniki, $settings, $ciniki['request']['business_id'], $writing_permalink, $sample_permalink);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$item = $rc['item'];

		$article_title = "<a href='$base_url'>" . $page_title . "</a> - <a href='$base_url/$writing_permalink'>" . $item['title'] . "</a> - " . $item['sample']['title'];
		$page_title .= ' - ' . $item['title'] . ' - ' . $item['sample']['title'];

		$page_content .= "<article class='page'>\n"
			. "<header class='entry-title'><h1 class='entry-title'>$article_title</h1>";
		if( isset($item['subtitle']) && $item['subtitle'] != '' ) {
			$page_content .= "<div class='entry-meta'>" . $item['subtitle'] . "</div>";
		}
		$page_content .= "</header>\n"
			. "<div class='entry-content'>\n"
			. "";
	
		if( isset($item['image_id']) && $item['image_id'] > 0 ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
			$rc = ciniki_web_getScaledImageURL($ciniki, $item['image_id'], 'original', '500', 0);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$ciniki['response']['head']['og']['image'] = $rc['domain_url'];
			$page_content .= "<aside><div class='image-wrap'><div class='image'>"
				. "<img title='' alt='" . $item['title'] . "' src='" . $rc['url'] . "' />"
				. "</div></div></aside>";
		}

		if( isset($item['synopsis']) && $item['synopsis'] != '' ) {
			$ciniki['response']['head']['og']['description'] = strip_tags($item['synopsis']);
		} elseif( isset($item['description']) && $item['description'] != '' ) {
			$ciniki['response']['head']['og']['description'] = strip_tags($item['description']);
		}

		//
		// Add the content
		//
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
		$rc = ciniki_web_processContent($ciniki, $item['sample']['content']);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$page_content .= $rc['content'];

		$page_content .= "</div>"
			. "</article>"
			. "";
	}

	//
	// Check if we are to display an items
	//
	elseif( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] != '' ) {
		$writing_permalink = $ciniki['request']['uri_split'][0];

		//
		// Get the writing information
		//
		ciniki_core_loadMethod($ciniki, 'ciniki', 'writingcatalog', 'web', 'writingDetails');
		$rc = ciniki_writingcatalog_web_writingDetails($ciniki, $settings, $ciniki['request']['business_id'], $writing_permalink);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$item = $rc['item'];

		$article_title = "<a href='$base_url'>" . $page_title . "</a> - " . $item['title'];
		$page_title .= ' - ' . $item['title'];

		$page_content .= "<article class='page'>\n"
			. "<header class='entry-title'><h1 class='entry-title'>$article_title</h1>";
		if( isset($item['subtitle']) && $item['subtitle'] != '' ) {
			$page_content .= "<div class='entry-meta'>" . $item['subtitle'] . "</div>";
		}
		$page_content .= "</header>\n"
			. "<div class='entry-content'>\n"
			. "";
	
		if( isset($item['image_id']) && $item['image_id'] > 0 ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
			$rc = ciniki_web_getScaledImageURL($ciniki, $item['image_id'], 'original', '500', 0);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$ciniki['response']['head']['og']['image'] = $rc['domain_url'];
			$page_content .= "<aside><div class='image-wrap'><div class='image'>"
				. "<img title='' alt='" . $item['title'] . "' src='" . $rc['url'] . "' />"
				. "</div></div></aside>";
		}

		if( isset($item['synopsis']) && $item['synopsis'] != '' ) {
			$ciniki['response']['head']['og']['description'] = strip_tags($item['synopsis']);
		} elseif( isset($item['description']) && $item['description'] != '' ) {
			$ciniki['response']['head']['og']['description'] = strip_tags($item['description']);
		}

		//
		// Add the content
		//
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
		$rc = ciniki_web_processContent($ciniki, (isset($item['description'])&&$item['description']!='')?$item['description']:$item['synopsis']);	
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$page_content .= $rc['content'];

		//
		// Check if there are reviews
		//
		if( isset($item['reviews']) && count($item['reviews']) > 0 ) {
			$page_content .= "<h2>Reviews</h2>";
			foreach($item['reviews'] as $review) {
				$page_content .= "<blockquote class='quote-text'>";
				$rc = ciniki_web_processContent($ciniki, $review['content']);
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				$page_content .= $rc['content'];
				if( isset($review['title']) && $review['title'] != '' ) {
					$page_content .= "<cite class='quote-author alignright'>" . $review['title'] . "</cite>";
				}
				$page_content .= "</blockquote>";
			}
		}

		//
		// Check if there are Samples
		//
		if( isset($item['samples']) && count($item['samples']) > 0 ) {
			$page_content .= "<h2>Samples</h2>";
			$page_content .= "<p>";
			foreach($item['samples'] as $sample) {
				$page_content .= "<a href='$base_url/$writing_permalink/sample/" . $sample['permalink'] . "'>" . $sample['title'] . "</a><br/>";
			}
			$page_content .= "</p>";
		}

		//
		// Check if there are purchasing options
		//
		if( isset($item['orderinfo']) && count($item['orderinfo']) > 0 ) {
			$page_content .= "<h2>Purchasing Options</h2>";
			foreach($item['orderinfo'] as $orderinfo) {
				if( isset($orderinfo['title']) && $orderinfo['title'] != '' ) {
					$page_content .= "<b>" . $orderinfo['title'] . "</b>";
				}
				$rc = ciniki_web_processContent($ciniki, $orderinfo['content']);
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				$page_content .= $rc['content'];
			}
		}

		$page_content .= "</div>"
			. "</article>"
			. "";
	} 
	
	//
	// Display the list of writings
	//
	else {
		//
		// Get the list of categories
		//
		ciniki_core_loadMethod($ciniki, 'ciniki', 'writingcatalog', 'web', 'writingList');
		$rc = ciniki_writingcatalog_web_writingList($ciniki, $settings, $ciniki['request']['business_id'], array());
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$categories = $rc['categories'];

		$article_title = $page_title;

		$page_content .= "<article class='page'>\n"
			. "<header class='entry-title'><h1 class='entry-title'>$article_title</h1></header>\n"
			. "<div class='entry-content'>\n"
			. "";

		if( count($categories) > 0 ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processCIList');
			$base_url = $ciniki['request']['base_url'] . "/writings";
			$rc = ciniki_web_processCIList($ciniki, $settings, $base_url, $categories, array('image_version'=>'original', 'image_width'=>'150'));
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= $rc['content'];
		} else {
			$page_content .= "<p>We're sorry, but there doesn't appear to be any writings.</p>";
		}

		$page_content .= "</div>"
			. "</article>"
			. "";
	}

	//
	// Generate the sub menu
	//
	$submenu = array();

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
