<?php
//
// Description
// -----------
// This function will generate the classes page for the business.
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_generatePageClasses($ciniki, $settings) {

	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');

	//
	// Store the content created by the page
	// Make sure everything gets generated ok before returning the content
	//
	$content = '';
	$page_content = '';
	if( isset($settings['page-classes-title']) && $settings['page-classes-title'] != '' ) {
		$page_title = $settings['page-classes-title'];
	} elseif( isset($settings['page-classes-name']) && $settings['page-classes-name'] != '' ) {
		$page_title = $settings['page-classes-name'];
	} else {
		$page_title = 'Classes';
	}


	//
	// FIXME: Check if anything has changed, and if not load from cache
	//

	//
	// Check if we are to display the gallery image for an class
	//
	//
	// Check if we are to display an image, from the gallery, or latest images
	//
	if( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] == 'class' 
		&& isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] != '' 
		&& isset($ciniki['request']['uri_split'][2]) && $ciniki['request']['uri_split'][2] == 'gallery' 
		&& isset($ciniki['request']['uri_split'][3]) && $ciniki['request']['uri_split'][3] != '' 
		) {
		$class_permalink = $ciniki['request']['uri_split'][1];
		$image_permalink = $ciniki['request']['uri_split'][3];

		//
		// Load the class details.
		//
		ciniki_core_loadMethod($ciniki, 'ciniki', 'classes', 'web', 'classDetails');
		$rc = ciniki_classes_web_classDetails($ciniki, $settings, 
			$ciniki['request']['business_id'], $class_permalink);
		if( $rc['stat'] != 'ok' ) {
			return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'1810', 'msg'=>"I'm sorry, but we can't seem to find the image you requested.", $rc['err']));
		}
		$class = $rc['class'];

		if( !isset($class['images']) || count($class['images']) < 1 ) {
			return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'1811', 'msg'=>"I'm sorry, but we can't seem to find the image you requested."));
		}

		$first = NULL;
		$last = NULL;
		$img = NULL;
		$next = NULL;
		$prev = NULL;
		foreach($class['images'] as $iid => $image) {
			if( $first == NULL ) {
				$first = $image;
			}
			if( $image['permalink'] == $image_permalink ) {
				$img = $image;
			} elseif( $next == NULL && $img != NULL ) {
				$next = $image;
			} elseif( $img == NULL ) {
				$prev = $image;
			}
			$last = $image;
		}

		if( count($class['images']) == 1 ) {
			$prev = NULL;
			$next = NULL;
		} elseif( $prev == NULL ) {
			// The requested image was the first in the list, set previous to last
			$prev = $last;
		} elseif( $next == NULL ) {
			// The requested image was the last in the list, set previous to last
			$next = $first;
		}
	
		$img_base_url = 
		$article_title = "<a href='" .  $ciniki['request']['base_url'] . "/classes/class/" . $class['permalink'] . "'>" . $class['name'] . "</a>";
		if( $img['title'] != '' ) {
			$page_title = $class['name'] . ' - ' . $img['title'];
			$article_title .= ' - ' . $img['title'];
		} else {
			$page_title = $class['name'];
		}

		if( $img == NULL ) {
			return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'1008', 'msg'=>"I'm sorry, but we can't seem to find the image you requested."));
		}
		//
		// Load the image
		//
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
		$rc = ciniki_web_getScaledImageURL($ciniki, $img['image_id'], 'original', 0, 600);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$img_url = $rc['url'];

		//
		// Set the page to wide if possible
		//
		$ciniki['request']['page-container-class'] = 'page-container-wide';

		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generateGalleryJavascript');
		$rc = ciniki_web_generateGalleryJavascript($ciniki, $next, $prev);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$ciniki['request']['inline_javascript'] = $rc['javascript'];

		$ciniki['request']['onresize'] = "gallery_resize_arrows();";
		$ciniki['request']['onload'] = "scrollto_header();";
		$page_content .= "<article class='page'>\n"
			. "<header class='entry-title'><h1 id='entry-title' class='entry-title'>$article_title</h1></header>\n"
			. "<div class='entry-content'>\n"
			. "";
		$page_content .= "<div id='gallery-image' class='gallery-image'>";
		$page_content .= "<div id='gallery-image-wrap' class='gallery-image-wrap'>";
		if( $prev != null ) {
			$page_content .= "<a id='gallery-image-prev' class='gallery-image-prev' href='" . $prev['permalink'] . "'><div id='gallery-image-prev-img'></div></a>";
		}
		if( $next != null ) {
			$page_content .= "<a id='gallery-image-next' class='gallery-image-next' href='" . $next['permalink'] . "'><div id='gallery-image-next-img'></div></a>";
		}
		$page_content .= "<img id='gallery-image-img' title='" . $img['title'] . "' alt='" . $img['title'] . "' src='" . $img_url . "' onload='javascript: gallery_resize_arrows();' />";
		$page_content .= "</div><br/>"
			. "<div id='gallery-image-details' class='gallery-image-details'>"
			. "<span class='image-title'>" . $img['title'] . '</span>'
			. "<span class='image-details'></span>";
		if( $img['description'] != '' ) {
			$page_content .= "<span class='image-description'>" . preg_replace('/\n/', '<br/>', $img['description']) . "</span>";
		}
		$page_content .= "</div></div>";
		$page_content .= "</div></article>";
	}

	//
	// Check if we are to display a class 
	//
	elseif( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] == 'class' 
		&& isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] != '' 
		) {
		$permalink = $ciniki['request']['uri_split'][1];
		
		//
		// Check if this is a page or category
		//
		ciniki_core_loadMethod($ciniki, 'ciniki', 'classes', 'web', 'classDetails');
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processURL');

		//
		// Get the class information
		//
		$rc = ciniki_classes_web_classDetails($ciniki, $settings, 
			$ciniki['request']['business_id'], $permalink);
		if( $rc['stat'] != 'ok' ) {
			return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'1812', 'msg'=>"I'm sorry, but we can't find the class you requested.", $rc['err']));
		}
		$class = $rc['class'];
		$page_title = $class['name'];
		$page_content .= "<article class='page'>\n"
			. "<header class='entry-title'><h1 class='entry-title'>" . $class['name'] . "</h1></header>\n"
			. "";
		//
		// Add primary image
		//
		if( isset($class['image_id']) && $class['image_id'] > 0 ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
			$rc = ciniki_web_getScaledImageURL($ciniki, $class['image_id'], 'original', '500', 0);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= "<aside><div class='image-wrap'><div class='image'>"
				. "<img title='' alt='" . $class['name'] . "' src='" . $rc['url'] . "' />"
				. "</div></div></aside>";
		}
		
		//
		// Add description
		//
		$page_content .= "<div class='entry-content'>";
		if( isset($class['description']) ) {
			$rc = ciniki_web_processContent($ciniki, $class['description']);	
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= $rc['content'];
		}

		$page_content .= "</div>";
		$page_content .= "</article>";

		if( isset($class['images']) && count($class['images']) > 0 ) {
			$page_content .= "<article class='page'>"	
				. "<header class='entry-title'><h1 class='entry-title'>Gallery</h1></header>\n"
				. "";
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageGalleryThumbnails');
			$img_base_url = $ciniki['request']['base_url'] . "/classes/class/" . $class['permalink'] . "/gallery";
			$rc = ciniki_web_generatePageGalleryThumbnails($ciniki, $settings, $img_base_url, $class['images'], 125);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= "<div class='image-gallery'>" . $rc['content'] . "</div>";
			$page_content .= "</article>";
		}
	}

	//
	// Check if we are to display a category 
	//
	elseif( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] == 'category' 
		&& isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] != '' 
		) {
		$category_permalink = $ciniki['request']['uri_split'][1];

		//
		// Load any content for this page
		//
		ciniki_core_loadMethod($ciniki, 'ciniki', 'classes', 'web', 'pageInfo');
		$rc = ciniki_classes_web_pageInfo($ciniki, $settings, 
			$ciniki['request']['business_id'], 'category-' . $category_permalink);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$info = $rc['info'];

		$page_content .= "<article class='page'>\n"
			. "<header class='entry-title'><h1 class='entry-title'>" . $info['title'] . "</h1></header>\n"
			. "";

		if( isset($info['image_id']) && $info['image_id'] != '' && $info['image_id'] != 0 ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
			$rc = ciniki_web_getScaledImageURL($ciniki, $info['image_id'], 'original', '500', 0);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= "<aside><div class='image-wrap'>"
				. "<div class='image'><img title='' src='" . $rc['url'] . "' /></div>";
			if( isset($info['image_caption']) && $info['image_caption'] != '' ) {
				$page_content .= "<div class='image-caption'>" . $info['image_caption'] . "</div>";
			}
			$page_content .= "</div></aside>";
		}

		$page_content .= "<div class='entry-content'>";
		if( isset($info['content']) ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
			$rc = ciniki_web_processContent($ciniki, $info['content']);	
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= $rc['content'];
		}
		if( isset($info['image_id']) && $info['image_id'] != '' && $info['image_id'] != 0 ) {
			$page_content .= "<br style='clear:both;' />\n";
		}

		$base_url = $ciniki['request']['base_url'] . "/classes";
		//
		// If only categories defined
		//
		if( ($ciniki['business']['modules']['ciniki.classes']['flags']&0x02) > 0 ) {
			//
			// Get the list of classes
			//
			ciniki_core_loadMethod($ciniki, 'ciniki', 'classes', 'web', 'classList');
			$rc = ciniki_classes_web_classList($ciniki, $settings, 
				$ciniki['request']['business_id'], array('category'=>$category_permalink));
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$categories = $rc['categories'];

			if( count($categories) > 0 ) {
				ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processCIList');
				$rc = ciniki_web_processCIList($ciniki, $settings, $base_url . '/class', $categories, array());
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				$page_content .= $rc['content'];
			} else {
				$page_content .= "<p>I'm sorry, but we don't currently offer any classes.</p>";
			}
		}

		//
		// Otherwise display the list of classes with no categories
		//
		else {
			//
			// Get the list of classes
			//
			ciniki_core_loadMethod($ciniki, 'ciniki', 'classes', 'web', 'classList');
			$rc = ciniki_classes_web_classList($ciniki, $settings, $ciniki['request']['business_id'], array());
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$classes = $rc['classes'];

			if( count($classes) > 0 ) {
				ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processCIList');
				$rc = ciniki_web_processCIList($ciniki, $settings, $base_url . '/class', $classes, 
					array('notitle'=>'yes'));
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
			} else {
				$page_content .= "<p>I'm sorry, but we don't currently offer any classes.</p>";
			}
		}
		$page_content .= "</div></article>";
	}
		
	//
	// Generate the main page for the classes.  If there are no subcat specified, then
	// list all the classes here.
	//
	else {
		//
		// Load any content for this page
		//
		ciniki_core_loadMethod($ciniki, 'ciniki', 'classes', 'web', 'pageInfo');
		$rc = ciniki_classes_web_pageInfo($ciniki, $settings, 
			$ciniki['request']['business_id'], 'introduction');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$info = $rc['info'];

		$page_content .= "<article class='page'>\n"
			. "<header class='entry-title'><h1 class='entry-title'>" . $page_title . "</h1></header>\n"
			. "";
		if( isset($info['image_id']) && $info['image_id'] != '' && $info['image_id'] != 0 ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
			$rc = ciniki_web_getScaledImageURL($ciniki, $info['image_id'], 'original', '500', 0);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= "<aside><div class='image-wrap'>"
				. "<div class='image'><img title='' src='" . $rc['url'] . "' /></div>";
			if( isset($info['image_caption']) && $info['image_caption'] != '' ) {
				$page_content .= "<div class='image-caption'>" . $info['image_caption'] . "</div>";
			}
			$page_content .= "</div></aside>";
		}

		$page_content .= "<div class='entry-content'>";
		if( isset($info['content']) && $info['content'] != '' ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
			$rc = ciniki_web_processContent($ciniki, $info['content']);	
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= $rc['content'];
		}
		if( isset($info['image_id']) && $info['image_id'] != '' && $info['image_id'] != 0 ) {
			$page_content .= "<br style='clear:both;' />\n";
		}

		$base_url = $ciniki['request']['base_url'] . "/classes";

		//
		// If categories and sub-categories are enabled, then list the categories
		//
		$page_content .= '';
		if( ($ciniki['business']['modules']['ciniki.classes']['flags']&0x02) > 0 ) {
			$page_content .= "<h2>Categories</h2>";
			//
			// Get the list of categories
			//
			ciniki_core_loadMethod($ciniki, 'ciniki', 'classes', 'web', 'categoryList');
			$rc = ciniki_classes_web_categoryList($ciniki, $settings, $ciniki['request']['business_id']);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			if( isset($rc['categories']) && count($rc['categories']) > 0 ) {
				$categories = $rc['categories'];
				ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processTagList');
				$rc = ciniki_web_processTagList($ciniki, $settings, $base_url . '/category', $categories, array());
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				$page_content .= $rc['content'];
			}
		} 
		
		//
		// If only categories defined
		//
		elseif( ($ciniki['business']['modules']['ciniki.classes']['flags']&0x01) > 0 ) {
			//
			// Get the list of classes
			//
			ciniki_core_loadMethod($ciniki, 'ciniki', 'classes', 'web', 'classList');
			$rc = ciniki_classes_web_classList($ciniki, $settings, $ciniki['request']['business_id'], array());
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}

			if( isset($rc['categories']) && count($rc['categories']) > 0 ) {
				$categories = $rc['categories'];
				ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processCIList');
				$rc = ciniki_web_processCIList($ciniki, $settings, $base_url . '/class', $categories, array());
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				$page_content .= $rc['content'];
			} else {
				$page_content .= "<p>I'm sorry, but we don't currently offer any classes.</p>";
			}
		}

		//
		// Otherwise display the list of classes with no categories
		//
		else {
			//
			// Get the list of classes
			//
			ciniki_core_loadMethod($ciniki, 'ciniki', 'classes', 'web', 'classList');
			$rc = ciniki_classes_web_classList($ciniki, $settings, $ciniki['request']['business_id'], array());
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			
			$classes = $rc['list'];

			if( count($classes) > 0 ) {
				ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processCIList');
				$rc = ciniki_web_processCIList($ciniki, $settings, $base_url . '/class', $classes,
					array('notitle'=>'yes'));
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				$page_content .= $rc['content'];
			} else {
				$page_content .= "<p>I'm sorry, but are no classes currently.</p>";
			}
		}

		$page_content .= "</div>\n"
			. "</article>\n"
			. "";
	}



	//
	// Generate the complete page
	//

	//
	// Add the header
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageHeader');
	$rc = ciniki_web_generatePageHeader($ciniki, $settings, $page_title, array());
	if( $rc['stat'] != 'ok' ) {	
		return $rc;
	}
	$content .= $rc['content'];

	$content .= "<div id='content'>\n"
		. $page_content
		. "</div>"
		. "";

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
