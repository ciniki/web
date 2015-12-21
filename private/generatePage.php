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
function ciniki_web_generatePage(&$ciniki, $settings) {

	//
	// Check if module has generatePage.php override
	//
	if( isset($ciniki['business']['modules']['ciniki.landingpages']) && $ciniki['request']['page'] == 'landingpage' ) {
		$rc = ciniki_core_loadMethod($ciniki, 'ciniki', 'landingpages', 'web', 'generatePage');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( $rc['stat'] == 'ok' ) {
			return ciniki_landingpages_web_generatePage($ciniki, $settings);
		}
	}

	$request_pages = array_merge(array($ciniki['request']['page']), $ciniki['request']['uri_split']);

//	print "<pre>";
//	print_r($ciniki['request']);
//	print_r($request_pages);

	$breadcrumbs = array();

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
			$breadcrumbs[] = array('name'=>$rc['page']['title'], 'url'=>$base_url . '/' . $rc['page']['permalink']);
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
			$breadcrumbs[] = array('name'=>$rc['page']['title'], 'url'=>$base_url . '/' . $rc['page']['permalink']);

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

	$page_content = '';
	$submenu = array();

	//
	// Process a module page
	//
	if( $page['page_type'] == '30' ) {
		$base_url .= '/' . $rc['page']['permalink'];
		$domain_base_url = $ciniki['request']['domain_base_url'] . '/' . $ciniki['request']['page'];
		$ciniki['request']['page-container-class'] = str_replace('.', '-', $page['page_module']);

//		$breadcrumbs[] = array('name'=>$page['title'], 'url'=>$base_url . '/' . $ciniki['request']['page']);

		//
		// Process the module request
		//
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processModuleRequest');
		$rc = ciniki_web_processModuleRequest($ciniki, $settings, $ciniki['request']['business_id'], $page['page_module'],
			array(
				'uri_split'=>$ciniki['request']['uri_split'],
				'base_url'=>$base_url,
				'domain_base_url'=>$domain_base_url,
				'page_title'=>$top_page['title'],
				'breadcrumbs'=>$breadcrumbs,
			));
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( isset($rc['content']) ) {
			$page_content .= $rc['content'];
		}
		$breadcrumbs = $rc['breadcrumbs'];
		if( isset($rc['page_title']) ) {
			$page_title = $rc['page_title'];
			$article_title = $rc['page_title'];
		}
		if( isset($rc['submenu']) ) {
			$submenu = $rc['submenu'];
		}

	} 

	//
	// Process a manual page, no processing of content, output raw HTML
	//
	elseif( $page['page_type'] == '11' ) {
		//
		// Set the page class
		//
		$ciniki['request']['page-container-class'] = 'page-' . $ciniki['request']['page'];

		$page_content .= "<article class='page'>\n";
		if( isset($page['title']) ) {
			$article_title = $page['title'];
			$page_content .= "<header class='entry-title'><h1 class='entry-title'>" . $page['title'] . "</h1>";
			if( isset($breadcrumbs) ) {
				ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processBreadcrumbs');
				$rc = ciniki_web_processBreadcrumbs($ciniki, $settings, $ciniki['request']['business_id'], $breadcrumbs);
				if( $rc['stat'] == 'ok' ) {
					$page_content .= $rc['content'];
				}
			}
			$page_content .= "</header>";
		}
		
		//
		// Process any form submissions
		//
		$result_content = '';
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processPageForms');
		$rc = ciniki_web_processPageForms($ciniki, $settings, $ciniki['request']['business_id']);
		if( $rc['stat'] != 'ok' ) {
			$result_content .= "<div class='form-result-message form-error-message'><div class='form-message-wrapper'><p>Error processing request</p></div></div>";
		}
		if( isset($rc['error_message']) && $rc['error_message'] != '' ) {
			$result_content .= "<div class='form-result-message form-error-message'><div class='form-message-wrapper'><p>" . $rc['error_message'] . "</p></div></div>";
		}
		if( isset($rc['success_message']) && $rc['success_message'] != '' ) {
			$result_content .= "<div class='form-result-message form-success-message'><div class='form-message-wrapper'><p>" . $rc['success_message'] . "</p></div></div>";
		}

		if( $result_content != '' ) {
			$page_content .= "<div class='form-message-content'>"
				. $result_content
				. "</div>";
		}

		$page_content .= "<div class='entry-content'>";
		$page_content .= $page['content'];
		
		$page_content .= "</div>";
		$page_content .= "</article>";

	} 
	
	//
	// Process a custom page
	//
	else {		// $page['page_type'] == '10'
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
		// Set the page class
		//
		$ciniki['request']['page-container-class'] = 'page-' . $ciniki['request']['page'];

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
	}

	//
	// Add the header
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageHeader');
    error_log(print_r($submenu, true));
	$rc = ciniki_web_generatePageHeader($ciniki, $settings, $top_page['title'], $submenu);
	if( $rc['stat'] != 'ok' ) {	
		return $rc;
	}
	$content = $rc['content'];

	//
	// Check if article title and breadcrumbs should be displayed above content
	//
	if( (isset($settings['theme']['header-article-title']) && $settings['theme']['header-article-title'] == 'yes')
		|| (isset($settings['theme']['header-breadcrumbs']) && $settings['theme']['header-breadcrumbs'] == 'yes')
		) {
		$content .= "<div class='page-header'>";
		if( isset($settings['theme']['header-article-title']) && $settings['theme']['header-article-title'] == 'yes' ) {
			$content .= "<h1 class='page-header-title'>" . $article_title . "</h1>";
		}
		if( isset($settings['theme']['header-breadcrumbs']) && $settings['theme']['header-breadcrumbs'] == 'yes' && isset($breadcrumbs) ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processBreadcrumbs');
			$rc = ciniki_web_processBreadcrumbs($ciniki, $settings, $ciniki['request']['business_id'], $breadcrumbs);
			if( $rc['stat'] == 'ok' ) {
				$content .= $rc['content'];
			}
		}
		$content .= "</div>";
	}

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
