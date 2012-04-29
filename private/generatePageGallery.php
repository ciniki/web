<?php
//
// Description
// -----------
// This function will generate the gallery page for the website
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_web_generatePageGallery($ciniki, $settings) {

	//
	// Store the content created by the page
	//
	$page_content = '';

	//
	// FIXME: Check if anything has changed, and if not load from cache
	//
	

	$page_title = "Gallery";

	//
	// Check if we are at the main page or a category or year gallery
	//
	if( isset($ciniki['request']['uri_split'][0]) 
		&& $ciniki['request']['uri_split'][0] != '' && $ciniki['request']['uri_split'][0] == 'category' 
		&& $ciniki['request']['uri_split'][1] != '' ) {
		$page_title = urldecode($ciniki['request']['uri_split'][1]);

		//
		// Get the gallery for the specified category
		//
		$page_content .= 'gallery for category ' . $page_title;

	} elseif( isset($ciniki['request']['uri_split'][0]) 
		&&$ciniki['request']['uri_split'][0] != '' && $ciniki['request']['uri_split'][0] == 'year' 
		&& $ciniki['request']['uri_split'][1] != '' ) {
		$page_title = urldecode($ciniki['request']['uri_split'][1]);

		//
		// Get the gallery for the specified year
		//
		$page_content .= 'gallery for year ' . $page_title;

	} else {
		//
		// Generate the content of the page
		//
		require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbDetailsQuery.php');
		$rc = ciniki_core_dbDetailsQuery($ciniki, 'ciniki_web_content', 'business_id', $ciniki['request']['business_id'], 'web', 'content', 'page.gallery');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}

		if( isset($rc['content']['page.gallery.content']) ) {
			require_once($ciniki['config']['core']['modules_dir'] . '/web/private/processContent.php');
			$rc = ciniki_web_processContent($ciniki, $rc['content']['page.gallery.content']);	
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content = $rc['content'];
		}

		//
		// Get the list of categories
		//
		require_once($ciniki['config']['core']['modules_dir'] . '/artcatalog/web/categories.php');
		$rc = ciniki_artcatalog_web_categories($ciniki, $settings, $ciniki['request']['business_id']);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( !isset($rc['categories']) ) {
			// FIXME: load photo list with category = ''
			require_once($ciniki['config']['core']['modules_dir'] . '/artcatalog/web/categoryImages.php');
			$rc = ciniki_artcatalog_web_categoryImages($ciniki, $settings, $ciniki['request']['business_id'], '');
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= 'all thumbnail, no categories specified';
			$page_content .= '<pre>' . print_r($rc['images'], true) . '</pre>';
		} else {
			// FIXME: load photo list with specified category
			$page_content .= 'category list ';
			$page_content .= '<pre>' . print_r($rc['categories'], true) . '</pre>';

		}
	}



	$content = '';

	//
	// Add the header
	//
	require_once($ciniki['config']['core']['modules_dir'] . '/web/private/generatePageHeader.php');
	$rc = ciniki_web_generatePageHeader($ciniki, $settings, $page_title);
	if( $rc['stat'] != 'ok' ) {	
		return $rc;
	}
	$content .= $rc['content'];

	//
	// Build the page content
	//
	$content .= "<div id='content'>\n"
		. "<article class='page'>\n"
		. "<header class='entry-title'><h1 class='entry-title'>Gallery</h1></header>\n"
		. "<div class='entry-content'>\n"
		. "";
	if( $page_content != '' ) {
		$content .= $page_content;
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
