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
function ciniki_web_processModuleRequest(&$ciniki, $settings, $business_id, $module, $args) {


	//
	// Check the module is enabled for the business
	//
	if( !isset($ciniki['business']['modules'][$module]) ) {
		return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'2571', 'msg'=>"I'm sorry, but the page you requested does not exist."));
	}

	//
	// call the modules processRequest to get page content
	//
	list($pkg, $mod) = explode('.', $module);
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

	$rsp = array('stat'=>'ok', 'page_title'=>$page['title'], 'content'=>'');

	//
	// Check if submenu returned
	//
	if( isset($page['submenu']) ) {
		$rsp['submenu'] = $page['submenu'];
	}

	//
	// Setup the article
	//
	$article_title = '';
	if( isset($page['breadcrumbs']) && count($page['breadcrumbs']) > 0 ) {
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
	$rsp['content'] .= "<article class='page'>\n"
		. "<header class='entry-title'><h1 id='entry-title' class='entry-title'>$article_title</h1>";
	if( isset($page['article_meta']) && count($page['article_meta']) > 0 ) {
		$rsp['content'] .= "<div class='entry-meta'>";
		$count = 0;
		foreach($page['article_meta'] as $meta) {
			$rsp['content'] .= ($count>0?'<br/>':'') . $meta;
			$count++;
		}
		$rsp['content'] .= "</div>";
	}
	$rsp['content'] .= "</header>\n"
		. "<div class='entry-content'>\n"
		. "";

	//
	// Process the blocks of content
	//
	if( isset($page['blocks']) ) {
		foreach($page['blocks'] as $block) {
			$processor = '';
			switch($block['type']) {
				case 'cilist': $processor = 'processBlockCIList'; break;
				case 'clist': $processor = 'processBlockCList'; break;
				case 'imagelist': $processor = 'processBlockImageList'; break;
				case 'gallery': $processor = 'processBlockGallery'; break;
				case 'primaryimage': $processor = 'processBlockImage'; break;
				case 'image': $processor = 'processBlockImage'; break;
				case 'tagcloud': $processor = 'processBlockTagCloud'; break;
				case 'tagimages': $processor = 'processBlockTagImages'; break;
				case 'asideimage': $processor = 'processBlockAsideImage'; break;
				case 'details': $processor = 'processBlockDetails'; break;
				case 'content': $processor = 'processBlockContent'; break;
				case 'message': $processor = 'processBlockMessage'; break;
				case 'printoptions': $processor = 'processBlockPrintOptions'; break;
				case 'sharebuttons': $processor = 'processBlockShareButtons'; break;
				case 'prices': $processor = 'processBlockPrices'; break;
				case 'links': $processor = 'processBlockLinks'; break;
				case 'files': $processor = 'processBlockFiles'; break;
				case 'sponsors': $processor = 'processBlockSponsors'; break;
			}
			if( $processor != '' ) {
				$rc = ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', $processor);
				if( $rc['stat'] == 'ok' ) {
					$fn = "ciniki_web_$processor";
					$rc = $fn($ciniki, $settings, $business_id, $block);
					if( $rc['stat'] != 'ok' ) {
						return $rc;
					}
					if( isset($rc['content']) ) {
						$rsp['content'] .= $rc['content'];
					}
				}
			}
		}
	}

	//
	// Check if we need previous next buttons for long lists
	//
	if( isset($page['pagination']) ) {
		
	}

	//
	// close the article
	//
	$rsp['content'] .= "</div></article>";
	
	//
	// Return the content
	//
	return $rsp;
}
?>
