<?php
//
// Description
// -----------
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure, similar to ciniki variable but only web specific information.
// events:			The array of events as returned by ciniki_events_web_list.
// limit:			The number of events to show.  Only 2 events are shown on the homepage.
//
// Returns
// -------
//
function ciniki_web_processModuleRequest(&$ciniki, $settings, $business_id, $module_page, $args) {

    //
    // Split the module into pieces
    //
    $m_pieces = explode('.', $module_page);
    $module = $m_pieces[0] . '.' . $m_pieces[1];
    $pkg = $m_pieces[0];
    $mod = $m_pieces[1];
    $args['module_page'] = $module_page;

	//
	// Check the module is enabled for the business
	//
	if( !isset($ciniki['business']['modules'][$module]) ) {
		return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'2571', 'msg'=>"I'm sorry, but the page you requested does not exist."));
	}

	//
	// call the modules processRequest to get page content
	//
	$rc = ciniki_core_loadMethod($ciniki, $pkg, $mod, 'web', 'processRequest');
	if( $rc['stat'] != 'ok' ) {
		return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'2577', 'msg'=>"I'm sorry, but the page you requested does not exist."));
	}
	$fn = $rc['function_call'];
	$rc = $fn($ciniki, $settings, $ciniki['request']['business_id'], $args);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['page']) ) {
		$page = array('title'=>$args['page_title']);
	} else {
		$page = $rc['page'];
	}

	//
	// Check if the response is a download
	//
	if( isset($rc['download']) ) {
		$file = $rc['download'];
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
		header('Cache-Control: no-cache, must-revalidate');
		header('Pragma: no-cache');
		if( $file['extension'] == 'pdf' ) {
			header('Content-Type: application/pdf');
		}
//		header('Content-Disposition: attachment;filename="' . $filename . '"');
		header('Content-Length: ' . strlen($file['binary_content']));
		header('Cache-Control: max-age=0');

		print $file['binary_content'];
		exit;
	}

	$rsp = array('stat'=>'ok', 'page_title'=>$page['title'], 'content'=>'', 'breadcrumbs'=>$page['breadcrumbs']);

	//
	// Check if submenu returned
	//
	if( isset($page['submenu']) ) {
		$rsp['submenu'] = $page['submenu'];
	}
	if( isset($page['container_class']) && $page['container_class'] != '' ) {
		if( !isset($ciniki['request']['page-container-class']) ) { 
			$ciniki['request']['page-container-class'] = $page['container_class'];
		} else {
			$ciniki['request']['page-container-class'] .= ' ' . $page['container_class'];
		}
	}
	if( isset($page['container-class']) && $page['container-class'] != '' ) {
		if( !isset($ciniki['request']['page-container-class']) ) { 
			$ciniki['request']['page-container-class'] = $page['container-class'];
		} else {
			$ciniki['request']['page-container-class'] .= ' ' . $page['container-class'];
		}
	}

    //
    // Make sure module class is set
    //
    $container_class = preg_replace("/\./", '-', $module_page);
    if( !isset($ciniki['request']['page-container-class']) ) {
        $ciniki['request']['page-container-class'] = $container_class;
    } elseif( !preg_match("/" . $container_class . "/", $ciniki['request']['page-container-class']) ) {
        $ciniki['request']['page-container-class'] .= ' ' . $container_class;
    }

	//
	// Setup the article
	//
	$article_title = '';
	if( (!isset($settings['site-layout']) || $settings['site-layout'] == 'default')
		&& isset($page['breadcrumbs']) && count($page['breadcrumbs']) > 0 
		) {
		$num_crumbs = count($page['breadcrumbs']);	
		$i = 1;
		foreach($page['breadcrumbs'] as $breadcrumb) {
			if( $breadcrumb['name'] == '' ) { 
				continue;
			}
			if( $i < $num_crumbs ) {
				$article_title .= ($article_title!=''?' - ':'') . '<a href="' . $breadcrumb['url'] . '">' . $breadcrumb['name'] . '</a>';
			} else {
				$article_title .= ($article_title!=''?' - ':'') . $breadcrumb['name'];
			}
			$i++;
		}
	} else {
		$article_title = $page['title'];
	}

//	if( isset($page['sidebar']) && count($page['sidebar']) > 0 ) {
//		$rsp['content'] .= "<div class='col-left-wide'>";
//	}
	if( isset($page['sidebar']) && count($page['sidebar']) > 0 ) {
		$rsp['content'] .= "<article class='page col-left-wide'>\n";
	} else {
		$rsp['content'] .= "<article class='page'>\n";
	}
	$rsp['content'] .= "<header class='entry-title'><h1 id='entry-title' class='entry-title'>$article_title</h1>";
	if( isset($page['subtitle']) && $page['subtitle'] != '' ) {
		$rsp['content'] .= "<h2>" . $page['subtitle'] . "</h2>";
	}
	if( isset($page['breadcrumbs']) ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processBreadcrumbs');
		$rc = ciniki_web_processBreadcrumbs($ciniki, $settings, $business_id, $page['breadcrumbs']);
		if( $rc['stat'] == 'ok' ) {
			$rsp['content'] .= $rc['content'];
		}
	}

	//
	// Setup the meta information
	//
	if( isset($page['meta']) && count($page['meta']) > 0 ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processMeta');
		$rc = ciniki_web_processMeta($ciniki, $settings, $page);
		if( isset($rc['content']) && $rc['content'] != '' ) {
			$rsp['content'] .= "<div class='entry-meta'>" . $rc['content'] . "</div>";
		}
	}

	elseif( isset($page['article_meta']) && count($page['article_meta']) > 0 ) {
		$rsp['content'] .= "<div class='entry-meta'>";
		$count = 0;
		foreach($page['article_meta'] as $meta) {
			$rsp['content'] .= ($count>0?'<br/>':'') . $meta;
			$count++;
		}
		$rsp['content'] .= "</div>";
	}
	
	//
	// Check if share buttons should be included in header
	//
	if( isset($page['article_header_share_buttons']) && $page['article_header_share_buttons'] == 'yes' ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processBlockShareButtons');
		$rc = ciniki_web_processBlockShareButtons($ciniki, $settings, $business_id, array('pagetitle'=>$page['title']));
		if( $rc['content'] != '' ) {
			$rsp['content'] .= $rc['content'];
		}
	}

    if( isset($args['page_menu']) && count($args['page_menu']) > 0 ) {
        $rsp['content'] .= "<div class='page-menu-container'><ul class='page-menu'>";
        foreach($args['page_menu'] as $item) {  
            $rsp['content'] .= "<li class='page-menu-item'><a href='" . $item['url'] . "'>" . $item['name'] . "</a></li>";
        }
        $rsp['content'] .= "</ul></div>";
    }

	$rsp['content'] .= "</header>\n"
		. "<div class='entry-content'>\n"
		. "";

	//
	// Process the blocks of content
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processBlocks');
	if( isset($page['blocks']) ) {
		$rc = ciniki_web_processBlocks($ciniki, $settings, $business_id, $page['blocks']);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$rsp['content'] .= $rc['content'];
	}

	//
	// close the article
	//
	$rsp['content'] .= "</div></article>";

	//
	// Add the sidebar content
	//
	if( isset($page['sidebar']) && count($page['sidebar']) > 0 ) {
		$rsp['content'] .= "<aside class='col-right-narrow'>";
		$rsp['content'] .= "<div class='aside-content'>";
		$rc = ciniki_web_processBlocks($ciniki, $settings, $business_id, $page['sidebar']);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$rsp['content'] .= $rc['content'];
		$rsp['content'] .= "</div>";
		$rsp['content'] .= "</aside>";
	}

	//
	// Return the content
	//
	return $rsp;
}
?>
