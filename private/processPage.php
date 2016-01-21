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
function ciniki_web_processPage(&$ciniki, $settings, $base_url, $page, $args) {

	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processURL');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');

	$content = '';

	$content .= "<article class='page'>\n"
		. "<header class='entry-title'><h1 class='entry-title'>" 
		. (isset($args['article_title'])&&$args['article_title']!=''?$args['article_title'] . ' - ':'')
		. $page['title'] . "</h1>"
		. "";
    if( isset($args['page_menu']) && count($args['page_menu']) > 0 ) {
        $content .= "<div class='page-menu-container'><ul class='page-menu'>";
        foreach($args['page_menu'] as $item) {  
            $content .= "<li class='page-menu-item'><a href='" . $item['url'] . "'>" . $item['name'] . "</a></li>";
        }
        $content .= "</ul></div>";
    }
    $content .= "</header>";

	$content .= "<div class='entry-content'>";
	if( isset($page['image_id']) && $page['image_id'] != '' && $page['image_id'] != 0 ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
		$rc = ciniki_web_getScaledImageURL($ciniki, $page['image_id'], 'original', '500', 0);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$content .= "<aside>"
            . "<div class='block block-primary-image'>"
            . "<div class='image-wrap'>"
			. "<div class='image'><img title='' src='" . $rc['url'] . "' /></div>";
		if( isset($page['image_caption']) && $page['image_caption'] != '' ) {
			$content .= "<div class='image-caption'>" . $page['image_caption'] . "</div>";
		}
		$content .= "</div></div></aside>";
	}

	if( isset($page['content']) ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
		$rc = ciniki_web_processContent($ciniki, $page['content']);	
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$content .= "<div class='block block-content'>" . $rc['content'] . "</div>";
	}
	if( isset($page['files']) ) {
        $files = '';
		foreach($page['files'] as $fid => $file) {
			$url = $base_url . ($page['permalink']!=''?'/' . $page['permalink']:'') . '/download/' . $file['permalink'] . '.' . $file['extension'];
			$files .= "<p><a target='_blank' href='" . $url . "' title='" . $file['name'] . "'>" . $file['name'] . "</a></p>";
		}
        if( $files != '' ) {
            $content .= "<div class='block block-files'>" . $files . "</div>";
        }
	}
	$content .= "</div>";
	$content .= "<br style='clear:both;'/>";

	//
	// Display the additional images for the content
	//
	if( isset($page['images']) && count($page['images']) > 0 ) {
        $content .= "<div class='block block-gallery'>";
		$content .= "<h2 style='clear:right;'>Gallery</h2>\n";
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageGalleryThumbnails');
		if( $page['permalink'] != '' ) {
			$img_base_url = $base_url . '/' . $page['permalink'] . '/gallery';
		} else {
			$img_base_url = $base_url . '/gallery';
		}
		$rc = ciniki_web_generatePageGalleryThumbnails($ciniki, $settings, $img_base_url, $page['images'], 125);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$content .= "<div class='image-gallery'>" . $rc['content'] . "</div>";
        $content .= "</div>";
	}

	//
	// Display the list of children
	//
	if( isset($page['children']) && count($page['children']) > 0 ) {
        $content .= "<div class='block block-children'>";
		$content .= "<br/>";
		if( isset($page['child_title']) && $page['child_title'] != '' ) {
			$content .= "<h2>" . $page['child_title'] . "</h2>";
		}
		if( count($page['children']) > 0 ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processCIList');
			$child_base_url = $base_url;
			if( $page['permalink'] != '' ) {
				$child_base_url .= '/' . $page['permalink'];
			}
			$list_args = array('notitle'=>'yes');
			if( isset($page['child_files']) ) {
				$list_args['child_files'] = $page['child_files'];
			}
			$rc = ciniki_web_processCIList($ciniki, $settings, $child_base_url, $page['children'], $list_args);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$content .= $rc['content'];
		} else {
			$content .= "";
		}
        $content .= "</div>";
	}

	//
	// Display the list of children with categories
	//
	if( isset($page['child_categories']) && count($page['child_categories']) > 0 ) {
        $content .= "<div class='block block-child-categories'>";
		$content .= "<br/>";
		if( isset($page['child_title']) && $page['child_title'] != '' ) {
			$content .= "<h2>" . $page['child_title'] . "</h2>";
		}
		if( count($page['child_categories']) > 0 ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processCIList');
			$child_base_url = $base_url . '/' . $page['permalink'];
			$list_args = array();
			if( isset($page['child_files']) ) {
				$list_args['child_files'] = $page['child_files'];
			}
			$rc = ciniki_web_processCIList($ciniki, $settings, $child_base_url, $page['child_categories'], $list_args);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$content .= $rc['content'];
		} else {
			$content .= "";
		}
        $content .= "</div>";

//		$content .= "</div>"
//			. "</article>"
//			. "";
	}

	//
	// Display any sponsors for the page
	//
	if( isset($page['sponsors']['sponsors']) && count($page['sponsors']['sponsors']) > 0 ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processSponsorsSection');
		$rc = ciniki_web_processSponsorsSection($ciniki, $settings, $page['sponsors']);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
        if( $rc['content'] != '' ) {
            $content .= "<div class='block block-sponsors'>" . $rc['content'] . "</div>";
        }
	}

	$content .= "</article>\n";

	return array('stat'=>'ok', 'content'=>$content);
}
?>
