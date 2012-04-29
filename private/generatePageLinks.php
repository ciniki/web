<?php
//
// Description
// -----------
// This function will generate the links page for the website
//
// Arguments
// ---------
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
	require_once($ciniki['config']['core']['modules_dir'] . '/web/private/generatePageHeader.php');
	$rc = ciniki_web_generatePageHeader($ciniki, $settings, 'Links');
	if( $rc['stat'] != 'ok' ) {	
		return $rc;
	}
	$content .= $rc['content'];

	//
	// Generate the content of the page
	//
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbDetailsQuery.php');
	$rc = ciniki_core_dbDetailsQuery($ciniki, 'ciniki_web_content', 'business_id', $ciniki['request']['business_id'], 'web', 'content', 'page.links');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	$page_content = '';
	if( isset($rc['content']) && isset($rc['content']['page.links.content']) ) {
		require_once($ciniki['config']['core']['modules_dir'] . '/web/private/processContent.php');
		$rc = ciniki_web_processContent($ciniki, $rc['content']['page.links.content']);	
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
	require_once($ciniki['config']['core']['modules_dir'] . '/links/web/list.php');
	$rc = ciniki_links_web_list($ciniki, $ciniki['request']['business_id']);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( isset($rc['categories']) ) {
		$categories = $rc['categories'];
	} else {
		$categories = array();
	}

	foreach($categories as $cnum => $c) {
		if( isset($c['category']['cname']) && $c['category']['cname'] != '' ) {
			$content .= "<h2>" . $c['category']['cname'] . "</h2>";
		}
		foreach($c['category']['links'] as $fnum => $link) {
			$content .= "<p>";
			if( isset($link['link']['url']) ) {
				$url = $link['link']['url'];
			} else {
				$url = '';
			}
			if( $url != '' && !preg_match('/^\s*http/', $url) ) {
				$url = "http://" . $url;
			}
			if( $url != '' ) {
				$content .= "<a target='_blank' href='" . $url . "' title='" . $link['link']['name'] . "'>" . $link['link']['name'] . "</a>";
			} else {
				$content .= $link['link']['name'];
			}
			if( isset($link['link']['description']) && $link['link']['description'] != '' ) {
				$content .= "<br/>" . $link['link']['description'];
			}
			if( $url != '' ) {
				$content .= "<br/><a target='_blank' href='" . $url . "' title='" . $link['link']['name'] . "'>" . $url . "</a>";
			}
			$content .= "</p>";
		}
	}

	$content .= "</div>"
		. "</article>"
		. "</div>"
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
