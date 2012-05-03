<?php
//
// Description
// -----------
// This function will generate the friends page for the website
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_web_generatePageFriends($ciniki, $settings) {

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
	$rc = ciniki_web_generatePageHeader($ciniki, $settings, 'Friends');
	if( $rc['stat'] != 'ok' ) {	
		return $rc;
	}
	$content .= $rc['content'];

	//
	// Generate the content of the page
	//
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbDetailsQueryDash.php');
	$rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_web_content', 'business_id', $ciniki['request']['business_id'], 'web', 'content', 'page-friends');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	$page_content = '';
	if( isset($rc['content']) && isset($rc['content']['page-friends-content']) ) {
		require_once($ciniki['config']['core']['modules_dir'] . '/web/private/processContent.php');
		$rc = ciniki_web_processContent($ciniki, $rc['content']['page-friends-content']);	
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$page_content = $rc['content'];
	}
	
	$content .= "<div id='content'>\n"
		. "<article class='page'>\n"
		. "<header class='entry-title'><h1 class='entry-title'>Friends</h1></header>\n"
		. "<div class='entry-content'>\n"
		. "";
	if( $page_content != '' ) {
		$content .= $page_content;
	}

	//
	// Get the list of friends to be displayed
	//
	require_once($ciniki['config']['core']['modules_dir'] . '/friends/web/friends.php');
	$rc = ciniki_friends_webFriends($ciniki, $ciniki['request']['business_id']);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( isset($rc['categories']) ) {
		$categories = $rc['categories'];
	} else {
		$categories = array();
	}

	foreach($categories as $cnum => $c) {
		if( $c['category']['cname'] != '' ) {
			$content .= "<h2>" . $c['category']['cname'] . "</h2>";
		}
		foreach($c['category']['friends'] as $fnum => $friend) {
			$content .= "<p>";
			if( isset($friend['friend']['url']) ) {
				$url = $friend['friend']['url'];
			} else {
				$url = '';
			}
			if( $url != '' && !preg_match('/^\s*http/', $url) ) {
				$url = "http://" . $url;
			}
			if( $url != '' ) {
				$content .= "<a target='_blank' href='" . $url . "' title='" . $friend['friend']['name'] . "'>" . $friend['friend']['name'] . "</a>";
			} else {
				$content .= $friend['friend']['name'];
			}
			if( isset($friend['friend']['description']) && $friend['friend']['description'] != '' ) {
				$content .= "<br/>" . $friend['friend']['description'];
			}
			if( $url != '' ) {
				$content .= "<br/><a target='_blank' href='" . $url . "' title='" . $friend['friend']['name'] . "'>" . $url . "</a>";
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
