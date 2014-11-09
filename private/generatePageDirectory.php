<?php
//
// Description
// -----------
// This function will generate the links page for the website
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_generatePageDirectory($ciniki, $settings) {

	//
	// Store the content created by the page
	// Make sure everything gets generated ok before returning the content
	//
	$content = '';
	$page_content = '';
	$page_title = 'Directory';
	if( isset($settings['page-directory-title']) && $settings['page-directory-title'] != '' ) {
		$page_title = $settings['page-directory-title'];
	}
	$base_url = $ciniki['request']['base_url'] . '/directory';

	//
	// FIXME: Check if anything has changed, and if not load from cache
	//
	
	//
	// Generate the content for an item
	//
	if( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] != '' 
		&& isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] == 'gallery' 
		&& isset($ciniki['request']['uri_split'][2]) && $ciniki['request']['uri_split'][2] != '' 
		) {
		$entry_permalink = $ciniki['request']['uri_split'][0];
		$image_permalink = $ciniki['request']['uri_split'][2];
	
		// 
		// Check if this is an entry
		//
		ciniki_core_loadMethod($ciniki, 'ciniki', 'directory', 'web', 'entryDetails');
		$rc = ciniki_directory_web_entryDetails($ciniki, $settings, $ciniki['request']['business_id'], $entry_permalink);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$entry = $rc['entry'];
		$article_title = "<a href='" . $ciniki['request']['base_url'] . "/directory'>$page_title</a>";
		$article_title .= " - <a href='$base_url/$entry_permalink'>" . $entry['title'] . "</a>";
		$page_title .= ' - ' . $entry['title'];

		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processGalleryImage');
		$rc = ciniki_web_processGalleryImage($ciniki, $settings, $ciniki['request']['business_id'], array(
			'item'=>$entry,
			'article_title'=>$article_title,
			'image_permalink'=>$image_permalink,
			));
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$page_content .= $rc['content'];
	}
	
	//
	// Generate the content for a category or an item
	//
	elseif( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] != '' ) {
		$category = $ciniki['request']['uri_split'][0];

		// 
		// Check if this is an entry
		//
		ciniki_core_loadMethod($ciniki, 'ciniki', 'directory', 'web', 'entryDetails');
		$rc = ciniki_directory_web_entryDetails($ciniki, $settings, $ciniki['request']['business_id'], $category);
		if( $rc['stat'] != 'ok' && $rc['stat'] != '404' ) {
			return $rc;
		}
		if( $rc['stat'] != '404' && isset($rc['entry']) ) {
			$page = $rc['entry'];
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processPage');
			$article_title = "<a href='" . $ciniki['request']['base_url'] . "/directory'>$page_title</a>";
//			$article_title .= ' - ' . $page['title'];
			$page_title .= ' - ' . $page['title'];

			$rc = ciniki_web_processPage($ciniki, $settings, $base_url, $page, array(
				'article_title'=>$article_title,
				));
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= $rc['content'];
		} else {
			//
			// Get the list of links to be displayed
			//
			ciniki_core_loadMethod($ciniki, 'ciniki', 'directory', 'web', 'list');
			$rc = ciniki_directory_web_list($ciniki, $ciniki['request']['business_id'], $category);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$article_title = "<a href='" . $ciniki['request']['base_url'] . "/directory'>$page_title</a>";
			$page_content .= "<article class='page'>\n"
				. "<header class='entry-title'><h1 class='entry-title'>"
				. $article_title
				. "</h1></header>\n"
				. "<div class='entry-content'>\n"
				. "";

			if( isset($rc['categories']) ) {
				$base_url = $ciniki['request']['base_url'] . '/directory';
				ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processCIList');
				$rc = ciniki_web_processCIList($ciniki, $settings, $base_url, $rc['categories'], array());
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				$page_content .= $rc['content'];
			}
			$page_content .= "</div>"
				. "<br style='clear:both;'/>"
				. "</article>"
				. "";
		}
	}

	//
	// Display the complete list
	//
	elseif( isset($settings['page-directory-layout']) && $settings['page-directory-layout'] == 'list' ) {
		$page_content .= "<article class='page'>\n"
			. "<header class='entry-title'><h1 class='entry-title'>"
			. $page_title
			. "</h1></header>\n"
			. "<div class='entry-content'>\n"
			. "";

		//
		// Get the list of entries to be displayed
		//
		ciniki_core_loadMethod($ciniki, 'ciniki', 'directory', 'web', 'list');
		$rc = ciniki_directory_web_list($ciniki, $ciniki['request']['business_id'], '');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( isset($rc['categories']) ) {
			$base_url = $ciniki['request']['base_url'] . '/directory';
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processCIList');
			$rc = ciniki_web_processCIList($ciniki, $settings, $base_url, $rc['categories'], array());
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= $rc['content'];
		}

		$page_content .= "</div>"
			. "</article>"
			. "";
		
	}

	//
	// Display the categories
	//
	else {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'directory', 'web', 'tagCloud');
		$base_url = $ciniki['request']['base_url'] . '/directory';
		$rc = ciniki_directory_web_tagCloud($ciniki, $settings, $ciniki['request']['business_id']);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}

		$page_content .= "<article class='page'>\n"
			. "<header class='entry-title'><h1 class='entry-title'>"
			. $page_title
			. "</h1></header>\n"
			. "<div class='entry-content'>\n"
			. "";

		//
		// Process the tags
		//
		if( isset($settings['page-directory-categories-display']) 
			&& $settings['page-members-categories-display'] == 'wordcloud' ) {
			if( isset($rc['tags']) && count($rc['tags']) > 0 ) {
				ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processTagCloud');
				$rc = ciniki_web_processTagCloud($ciniki, $settings, $base_url, $rc['tags']);
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				$page_content .= $rc['content'];
			} else {
				$page_content = "<p>I'm sorry, there are no categories for this blog</p>";
			}
		} else {
			if( isset($rc['tags']) && count($rc['tags']) > 0 ) {
				ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processTagList');
				$rc = ciniki_web_processTagList($ciniki, $settings, $base_url, $rc['tags'], array());
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				$page_content .= $rc['content'];
			} else {
				$page_content = "<p>I'm sorry, there are no categories for this blog</p>";
			}
		} 
		$page_content .= "</div>\n"
			. "</article>\n"
			. "";
	}

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
