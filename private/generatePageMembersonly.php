<?php
//
// Description
// -----------
// This function will generate the gallery page for the website
//
// The blog URL's can consist of
// 		/blog/ - Display the latest blog entries
//		/blog/archive - Display the archive for the blog
// 		/blog/category/categoryname - Display the entries for the category
// 		/blog/tag/tagname - Display the entries for a tag
//		/blog/permalink - Display a blog entry
//		/blog/permalink/gallery/imagepermalink - Display a blog entry image gallery
//		/blog/permalink/download/filepermalink - Download a blog entry file
//
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_generatePageMembersonly($ciniki, $settings) {

	//
	// Check if the membersonly area is active, and the customer is signed in.  Otherwise, redirect
	// to the signin page
	//
	if( !isset($ciniki['business']['modules']['ciniki.membersonly']) ) {
		return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'2195', 'msg'=>'Page does not exist.'));
	}
	if( !isset($settings['page-membersonly-active']) || $settings['page-membersonly-active'] != 'yes' ) {
		return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'2196', 'msg'=>'Page does not active.'));
	}
	if( !isset($ciniki['session']['customer']['member_status']) 
		|| $ciniki['session']['customer']['member_status'] != '10' ) {
		
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageAccount');
		return ciniki_web_generatePageAccount($ciniki, $settings);
	}

	//
	// The member is logged in, proceed to show the membersonly content
	//
	$page_content = '';
	$submenu = array();

	//
	// Get the list of pages, decide if we need a submenu
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'membersonly', 'web', 'pages');
	$rc = ciniki_membersonly_web_pages($ciniki, $settings, $ciniki['request']['business_id'], array());
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$num_pages = 0;
	if( isset($rc['categories']) ) {
		$categories = $rc['categories'];
		$pages = array();
		foreach($categories as $cat) {
			$pages = array_merge($pages, $cat['list']);
		}
	} else {
		$pages = array();
	}
	$num_pages = count($pages);	
	if( $num_pages == 1 ) {
		$root_page = array_pop($pages);
	}
//		print "<pre>"; print_r($rc['categories']); print "</pre>"; 

	//
	// Check if there is at least one page specified
	//
	if( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] != '' ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processMembersonlyURI');
		$rc = ciniki_web_processMembersonlyURI($ciniki, $settings, 0, $ciniki['request']['base_url'] . '/membersonly', $root_page['id']);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$page_content .= $rc['content'];
	} 

	//
	// Nothing here yet
	//
	elseif( $num_pages == 0 ) {
		$page_content = "<p>Nothing here yet</p>";
	}
	
	//
	// Show the default page
	//
	elseif( $num_pages == 1 ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'membersonly', 'web', 'pageDetails');
		$rc = ciniki_membersonly_web_pageDetails($ciniki, $settings, $ciniki['request']['business_id'],
			array('permalink'=>$root_page['permalink']));
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$page = $rc['page'];
		$page['permalink'] = '';
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processPage');
		$rc =  ciniki_web_processPage($ciniki, 0, $ciniki['request']['base_url'] . '/membersonly', $page, array());
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$page_content .= $rc['content'];
	} 

	//
	// Show all the pages
	//
	else {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processCIList');
		$child_base_url = $ciniki['request']['base_url'] . '/membersonly';
		$list_args = array('notitle'=>'yes');
		$rc = ciniki_web_processCIList($ciniki, $settings, $child_base_url, $categories, $list_args);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$page_content .= $rc['content'];
	}

	//
	// Add the header
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageHeader');
	$rc = ciniki_web_generatePageHeader($ciniki, $settings, 'About', $submenu);
	if( $rc['stat'] != 'ok' ) {	
		return $rc;
	}
	$content = $rc['content'];

	$content .= "<div id='content'>\n";
	$content .= $page_content;
	$content .= "<br style='clear: both;' />\n";
	$content .= "</div>\n";

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
