<?php
//
// Description
// -----------
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_generatePage($ciniki, $settings) {

	$request_pages = array_merge(array($ciniki['request']['page']), $ciniki['request']['uri_split']);

//	print "<pre>";
//	print_r($ciniki['request']);
//	print_r($request_pages);

	$prev_parent_id = 0;
	$uri_depth = 0;
	$prev_page = NULL;
	$top_page = NULL;
	$page = NULL;
	$article_title = '';
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'pageLoad');
	$depth = count($request_pages);
	$base_url = $ciniki['request']['base_url'];
	$sponsors = array();
	for($i=0;$i<$depth;$i++) {
		$uri_depth = $i-1;
		if( $i == ($depth-1) ) {
			// Last Page
			$rc = ciniki_web_pageLoad($ciniki, $settings, $ciniki['request']['business_id'], 
				array('permalink'=>$request_pages[$i], 'parent_id'=>$prev_parent_id));
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page = $rc['page'];
//			$base_url .= '/' . $rc['page']['permalink'];
			if( $top_page == NULL ) { $top_page = $rc['page']; }
			if( isset($rc['page']['sponsors']) && count($rc['page']['sponsors']) > 0 ) {
				$sponsors = $rc['page']['sponsors'];
			}
		} else {
			// Intermediate page, need title and id only
			$rc = ciniki_web_pageLoad($ciniki, $settings, $ciniki['request']['business_id'], 
				array('intermediate_permalink'=>$request_pages[$i], 'parent_id'=>$prev_parent_id));
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			if( $top_page == NULL ) { $top_page = $rc['page']; }
			if( isset($rc['page']['sponsors']) && count($rc['page']['sponsors']) > 0 ) {
				$sponsors = $rc['page']['sponsors'];
			}

			//
			// Check if next item is a child, otherwise this is the parent
			//
			if( !isset($rc['page']['children'])
				|| !isset($rc['page']['children'][$request_pages[$i+1]]) ) {
				// Load full page details
				$rc = ciniki_web_pageLoad($ciniki, $settings, $ciniki['request']['business_id'], 
					array('permalink'=>$request_pages[$i], 'parent_id'=>$prev_parent_id));
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				$page = $rc['page'];
				break;
			} else {
				$prev_parent_id = $rc['page']['id'];
				$prev_page = $rc['page'];
				$base_url .= '/' . $rc['page']['permalink'];
				$article_title .= ($article_title!=''?' - ':'') . "<a href='$base_url'>" . $rc['page']['title'] . "</a>";
			}
		}
	}

//	print "Showing page: \n";
//	print_r($page);
//	print "</pre>";

	//
	// The member is logged in, proceed to show the membersonly content
	//
	$page_content = '';
	$submenu = array();

	//
	// Check if children should be submenu
	//
	if( ($top_page['flags']&0x20) == 0x20 && isset($top_page['children']) ) {
		foreach($top_page['children'] as $child) {
			$submenu[$child['permalink']] = array('name'=>$child['name'],
				'url'=>$ciniki['request']['base_url'] . '/' . $top_page['permalink'] . '/' . $child['permalink']);
		}
		if( $top_page['id'] == $page['id'] ) {
			unset($page['children']);
		}
	}

	//
	// Check if a file was specified to be downloaded
	//
	$download_err = '';
	if( isset($ciniki['request']['uri_split'][$uri_depth+1]) 
		&& $ciniki['request']['uri_split'][$uri_depth+1] == 'download' 
		&& isset($ciniki['request']['uri_split'][$uri_depth+2]) 
		&& $ciniki['request']['uri_split'][$uri_depth+2] != '' 
		&& isset($page['files'])
		) {
		$file_permalink = $ciniki['request']['uri_split'][$uri_depth+2];

		//
		// Get the file details
		//
		$strsql = "SELECT ciniki_web_page_files.id, "
			. "ciniki_web_page_files.name, "
			. "ciniki_web_page_files.permalink, "
			. "ciniki_web_page_files.extension, "
			. "ciniki_web_page_files.binary_content "
			. "FROM ciniki_web_pages, ciniki_web_page_files "
			. "WHERE ciniki_web_pages.business_id = '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['business_id']) . "' "
			. "AND ciniki_web_pages.permalink = '" . ciniki_core_dbQuote($ciniki, $page['permalink']) . "' "
			. "AND ciniki_web_pages.id = ciniki_web_page_files.page_id "
			. "AND ciniki_web_page_files.business_id = '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['business_id']) . "' "
			. "AND CONCAT_WS('.', ciniki_web_page_files.permalink, ciniki_web_page_files.extension) = '" . ciniki_core_dbQuote($ciniki, $file_permalink) . "' "
			. "";
		$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.web', 'file');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( !isset($rc['file']) ) {
			return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'2201', 'msg'=>"I'm sorry, but the file you requested does not exist."));
		}
		$filename = $rc['file']['name'] . '.' . $rc['file']['extension'];

		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
		header('Cache-Control: no-cache, must-revalidate');
		header('Pragma: no-cache');
		$file = $rc['file'];
		if( $file['extension'] == 'pdf' ) {
			header('Content-Type: application/pdf');
		}
//		header('Content-Disposition: attachment;filename="' . $filename . '"');
		header('Content-Length: ' . strlen($rc['file']['binary_content']));
		header('Cache-Control: max-age=0');

		print $rc['file']['binary_content'];
		exit;
	}

	if( isset($ciniki['request']['uri_split'][$uri_depth+1]) 
		&& $ciniki['request']['uri_split'][$uri_depth+1] == 'gallery' 
		&& isset($ciniki['request']['uri_split'][$uri_depth+2]) 
		&& $ciniki['request']['uri_split'][$uri_depth+2] != '' 
		&& isset($page['images'])
		) {
		$image_permalink = $ciniki['request']['uri_split'][$uri_depth+2];

		$base_url .= '/' . $page['permalink'];
		$article_title .= ($article_title!=''?' - ':'') . "<a href='$base_url'>" . $page['title'] . "</a>";
		
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processGalleryImage');
		$rc = ciniki_web_processGalleryImage($ciniki, $settings, $ciniki['request']['business_id'], array(
			'item'=>$page,
			'gallery_url'=>$base_url . '/gallery',
			'article_title'=>$article_title,
			'image_permalink'=>$image_permalink
			));
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$page_content .= $rc['content'];

	} else {
		if( isset($sponsors) && is_array($sponsors) && count($sponsors) > 0 ) {
			$page['sponsors'] = $sponsors;
		}
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processPage');
		$rc =  ciniki_web_processPage($ciniki, 0, $base_url, $page, array('article_title'=>$article_title));
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$page_content .= $rc['content'];
	}

	//
	// Add the header
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageHeader');
	$rc = ciniki_web_generatePageHeader($ciniki, $settings, $top_page['title'], $submenu);
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
