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
function ciniki_web_generatePageLinks($ciniki, $settings) {

	//
	// Store the content created by the page
	// Make sure everything gets generated ok before returning the content
	//
	$content = '';

	//
	// FIXME: Check if anything has changed, and if not load from cache
	//
	

	//
	// Add the header
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageHeader');
	$rc = ciniki_web_generatePageHeader($ciniki, $settings, 'Links', array());
	if( $rc['stat'] != 'ok' ) {	
		return $rc;
	}
	$content .= $rc['content'];

	//
	// Generate the content of the page
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash');
	$rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_web_content', 'business_id', $ciniki['request']['business_id'], 'ciniki.web', 'content', 'page-links');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	$page_content = '';
	if( isset($rc['content']) && isset($rc['content']['page-links-content']) ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
		$rc = ciniki_web_processContent($ciniki, $rc['content']['page-links-content']);	
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$page_content = $rc['content'];
	}
	
	$content .= "<div id='content'>\n"
		. "<article class='page'>\n"
		. "<header class='entry-title'><h1 class='entry-title'>Links</h1></header>\n"
		. "<div class='entry-content'>\n"
		. "";
	if( $page_content != '' ) {
		$content .= $page_content;
	}

	//
	// Get the list of links to be displayed
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'links', 'web', 'list');
	$rc = ciniki_links_web_list($ciniki, $ciniki['request']['business_id']);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( isset($rc['categories']) ) {
		$categories = $rc['categories'];
	} else {
		$categories = array();
	}

	$content .= "<table class='links-list'>\n"
		. "";
	$prev_category = NULL;
	foreach($categories as $cnum => $c) {
		if( $prev_category != NULL ) {
			$content .= "</td></tr>\n";
		}
		if( isset($c['category']['cname']) && $c['category']['cname'] != '' ) {
			$content .= "<tr><th>"
				. "<span class='links-category'>" . $c['category']['cname'] . "</span></th>"
				. "<td>";
			// $content .= "<h2>" . $c['category']['cname'] . "</h2>";
		} else {
			$content .= "<tr><th>"
				. "<span class='links-category'></span></th>"
				. "<td>";
		}
		foreach($c['category']['links'] as $fnum => $link) {
			//$content .= "<p>";
			if( isset($link['link']['url']) ) {
				$url = $link['link']['url'];
			} else {
				$url = '';
			}
			if( $url != '' && !preg_match('/^\s*http/i', $url) ) {
				$display_url = $url;
				$url = "http://" . $url;
			} else {
				$display_url = preg_replace('/^\s*http:\/\//i', '', $url);
				$display_url = preg_replace('/\/$/i', '', $display_url);
			}
			$content .= "<span class='links-title'>";
			if( $url != '' ) {
				$content .= "<a target='_blank' href='" . $url . "' title='" . $link['link']['name'] . "'>" . $link['link']['name'] . "</a>";
			} else {
				$content .= $link['link']['name'];
			}
			$content .= "</span>";
			if( isset($link['link']['description']) && $link['link']['description'] != '' ) {
				$content .= "<br/><span class='links-description'>" . $link['link']['description'] . "</span>";
			}
			if( $url != '' ) {
				$content .= "<br/><a class='links-url' target='_blank' href='" . $url . "' title='" . $link['link']['name'] . "'>" . $display_url . "</a>";
			}
			$content .= "<br/><br/>";
			// $content .= "</p>";
		}
	}

	$content .= "</td></tr>\n</table>\n";

	$content .= "</div>"
		. "</article>"
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
