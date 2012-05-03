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
	

	$page_title = "Galleries";

	//
	// Check if we are at the main page or a category or year gallery
	//
	if( isset($ciniki['request']['uri_split'][0]) 
		&& $ciniki['request']['uri_split'][0] != '' 
		&& ($ciniki['request']['uri_split'][0] == 'category' || $ciniki['request']['uri_split'][0] == 'year')
		&& $ciniki['request']['uri_split'][1] != '' ) {
		$page_title = urldecode($ciniki['request']['uri_split'][1]);

		//
		// Get the gallery for the specified category
		//
		require_once($ciniki['config']['core']['modules_dir'] . '/artcatalog/web/categoryImages.php');
		$rc = ciniki_artcatalog_web_categoryImages($ciniki, $settings, $ciniki['request']['business_id'], 
			$ciniki['request']['uri_split'][0], urldecode($ciniki['request']['uri_split'][1]));
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$images = $rc['images'];

		require_once($ciniki['config']['core']['modules_dir'] . '/web/private/generatePageGalleryThumbnails.php');
		$rc = ciniki_web_generatePageGalleryThumbnails($ciniki, $settings, $rc['images'], 125);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$page_content .= "<div class='image-gallery'>" . $rc['content'] . "</div>";

		// $page_content .= '<pre>' . print_r($images, true) . '</pre>';

	} else {
		//
		// Generate the content of the page
		//
		require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbDetailsQuery.php');
		$rc = ciniki_core_dbDetailsQuery($ciniki, 'ciniki_web_content', 'business_id', $ciniki['request']['business_id'], 'web', 'content', 'page-gallery');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}

		if( isset($rc['content']['page-gallery-content']) ) {
			require_once($ciniki['config']['core']['modules_dir'] . '/web/private/processContent.php');
			$rc = ciniki_web_processContent($ciniki, $rc['content']['page-gallery-content']);	
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
			$page_title = 'Gallery';
			require_once($ciniki['config']['core']['modules_dir'] . '/artcatalog/web/categoryImages.php');
			$rc = ciniki_artcatalog_web_categoryImages($ciniki, $settings, $ciniki['request']['business_id'], 'category', '');
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$images = $rc['images'];


			require_once($ciniki['config']['core']['modules_dir'] . '/web/private/generatePageGalleryThumbnails.php');
			$rc = ciniki_web_generatePageGalleryThumbnails($ciniki, $settings, $rc['images'], 150);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= "<div class='image-gallery'>" . $rc['content'] . "</div>";

			//$page_content .= 'all thumbnail, no categories specified';
			//$page_content .= '<pre>' . print_r($rc['images'], true) . '</pre>';
		} else {
			$page_title = 'Galleries';
			$page_content .= "<ul>\n";
			foreach($rc['categories'] AS $cnum => $category) {
				$name = $category['category']['name'];
				$page_content .= "<li class='gallery-menu-item'><a href='" . $ciniki['request']['base_url'] . "/gallery/category/" . urlencode($name) . "' "
					. "title='" . $name . "'>" . $name . "</a></li>\n";
			}
			$page_content .= "</ul>\n";
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
		. "<header class='entry-title'><h1 class='entry-title'>$page_title</h1></header>\n"
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
