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

	//
	// FIXME: Check if anything has changed, and if not load from cache
	//
	

	//
	// Generate the content of the page
	//
	if( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] != '' ) {
		$category = $ciniki['request']['uri_split'][0];

		$page_content .= "<div id='content'>\n"
			. "<article class='page'>\n"
			. "<header class='entry-title'><h1 class='entry-title'><a href='" . $ciniki['request']['base_url'] . "/directory'>Directory</a></h1></header>\n"
			. "<div class='entry-content'>\n"
			. "";

		//
		// Get the list of links to be displayed
		//
		ciniki_core_loadMethod($ciniki, 'ciniki', 'directory', 'web', 'list');
		$rc = ciniki_directory_web_list($ciniki, $ciniki['request']['business_id'], $category);
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
			. "</div>"
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
			. "<header class='entry-title'><h1 class='entry-title'>Directory</h1></header>\n"
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
