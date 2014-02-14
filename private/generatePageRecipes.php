<?php
//
// Description
// -----------
// This function will generate the gallery page for the website
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_generatePageRecipes($ciniki, $settings) {

	//
	// Check if a file was specified to be downloaded
	//
	$download_err = '';
	if( isset($ciniki['business']['modules']['ciniki.recipes'])
		&& isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] == 'p'
		&& isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] != ''
		&& isset($ciniki['request']['uri_split'][2]) && $ciniki['request']['uri_split'][2] == 'download'
		&& isset($ciniki['request']['uri_split'][3]) && $ciniki['request']['uri_split'][3] != '' ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'recipes', 'web', 'fileDownload');
		$rc = ciniki_recipes_web_fileDownload($ciniki, $ciniki['request']['business_id'], 
			$ciniki['request']['uri_split'][1], $ciniki['request']['uri_split'][3]);
		if( $rc['stat'] == 'ok' ) {
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			header("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
			header('Cache-Control: no-cache, must-revalidate');
			header('Pragma: no-cache');
			$file = $rc['file'];
			if( $file['extension'] == 'pdf' ) {
				header('Content-Type: application/pdf');
			}
			header('Content-Disposition: attachment;filename="' . $file['filename'] . '"');
			header('Content-Length: ' . strlen($file['binary_content']));
			header('Cache-Control: max-age=0');

			print $file['binary_content'];
			exit;
		}
		
		//
		// If there was an error locating the files, display generic error
		//
		return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'1551', 'msg'=>'The file you requested does not exist.'));
	}

	//
	// Store the content created by the page
	//
	$page_content = '';

	//
	// FIXME: Check if anything has changed, and if not load from cache
	//
		

	$page_title = "Recipes";
	if( isset($ciniki['business']['modules']['ciniki.recipes']) ) {
		$pkg = 'ciniki';
		$mod = 'recipes';
		$category_uri_component = 'recipes';
	} else {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1552', 'msg'=>'No recipe module enabled'));
	}

	//
	// Check if we are to display an image, from the gallery, or latest images
	//
	if( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] == 'r' 
		&& isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] != '' 
		&& isset($ciniki['request']['uri_split'][2]) && $ciniki['request']['uri_split'][2] == 'gallery' 
		&& isset($ciniki['request']['uri_split'][3]) && $ciniki['request']['uri_split'][3] != '' 
		) {
		$recipe_permalink = $ciniki['request']['uri_split'][1];
		$image_permalink = $ciniki['request']['uri_split'][3];

		//
		// Load the recipe to get all the details, and the list of images.
		// It's one query, and we can find the requested image, and figure out next
		// and prev from the list of images returned
		//
		ciniki_core_loadMethod($ciniki, 'ciniki', 'recipes', 'web', 'recipeDetails');
		$rc = ciniki_recipes_web_recipeDetails($ciniki, $settings, 
			$ciniki['request']['business_id'], $recipe_permalink);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$recipe = $rc['recipe'];

		if( !isset($recipe['images']) || count($recipe['images']) < 1 ) {
			return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1553', 'msg'=>'Unable to find image'));
		}

		$first = NULL;
		$last = NULL;
		$img = NULL;
		$next = NULL;
		$prev = NULL;
		foreach($recipe['images'] as $iid => $image) {
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

		if( count($recipe['images']) == 1 ) {
			$prev = NULL;
			$next = NULL;
		} elseif( $prev == NULL ) {
			// The requested image was the first in the list, set previous to last
			$prev = $last;
		} elseif( $next == NULL ) {
			// The requested image was the last in the list, set previous to last
			$next = $first;
		}
	
		if( $img['title'] != '' ) {
			$page_title = $recipe['name'] . ' - ' . $img['title'];
		} else {
			$page_title = $recipe['name'];
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
			. "<header class='entry-title'><h1 id='entry-title' class='entry-title'>$page_title</h1></header>\n"
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
	// Display the page of the recipe details
	//
	elseif( isset($ciniki['request']['uri_split'][0]) 
		&& $ciniki['request']['uri_split'][0] == 'r'
		&& $ciniki['request']['uri_split'][1] != '' ) {

		ciniki_core_loadMethod($ciniki, 'ciniki', 'recipes', 'web', 'recipeDetails');
		//
		// Get the recipe information
		//
		$recipe_permalink = $ciniki['request']['uri_split'][1];
		$rc = ciniki_recipes_web_recipeDetails($ciniki, $settings, 
			$ciniki['request']['business_id'], $recipe_permalink);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$recipe = $rc['recipe'];
		$page_title = $recipe['name'];
		$page_content .= "<article class='page'>\n"
			. "<header class='entry-title'><h1 class='entry-title'>" . $recipe['name'] . "</h1></header>\n"
			. "";

		//
		// Add primary image
		//
		if( isset($recipe['image_id']) && $recipe['image_id'] > 0 ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
			$rc = ciniki_web_getScaledImageURL($ciniki, $recipe['image_id'], 'original', '500', 0);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= "<aside><div class='image-wrap'><div class='image'>"
				. "<img title='' alt='" . $recipe['name'] . "' src='" . $rc['url'] . "' />"
				. "</div></div></aside>";
		}
		
		//
		// Add description
		//
		if( isset($recipe['description']) && $recipe['description'] != '' ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
			$rc = ciniki_web_processContent($ciniki, $recipe['description']);	
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= $rc['content'];
		}

		//
		// Add technical details for recipe
		//
		$additional_info = '';
		if( isset($recipe['num_servings']) && $recipe['num_servings'] != '' ) {
			$additional_info .= "<b>Servings</b>: " . $recipe['num_servings'] . "<br/>";
		}
		if( isset($recipe['prep_time']) && $recipe['prep_time'] != '' ) {
			$additional_info .= "<b>Prep Time</b>: " . $recipe['prep_time'] . " minutes<br/>";
		}
		if( isset($recipe['cook_time']) && $recipe['cook_time'] != '' ) {
			$additional_info .= "<b>Cook Time</b>: " . $recipe['cook_time'] . " minutes<br/>";
		}
		if( $additional_info != '' ) {
			$page_content .= "<p>$additional_info</p>";
		}

		//
		// Add ingredients list
		//
		if( isset($recipe['ingredients']) && $recipe['ingredients'] != '' ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
			$rc = ciniki_web_processContent($ciniki, $recipe['ingredients']);	
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= "<h2>Ingredients</h2>";
			$page_content .= $rc['content'];
		}

		//
		// Add instructions
		//
		if( isset($recipe['instructions']) && $recipe['instructions'] != '' ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
			$rc = ciniki_web_processContent($ciniki, $recipe['instructions']);	
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= "<h2>Instructions</h2>";
			$page_content .= $rc['content'];
		}

		//
		// Display the additional images for the recipe
		//
		if( isset($recipe['images']) && count($recipe['images']) > 0 ) {
			$page_content .= "<article class='page'>"	
				. "<header class='entry-title'><h2 class='entry-title'>Gallery</h2></header>\n"
				. "";
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageGalleryThumbnails');
			$img_base_url = $ciniki['request']['base_url'] . "/recipes/r/" . $recipe['permalink'] . "/gallery";
			$rc = ciniki_web_generatePageGalleryThumbnails($ciniki, $settings, $img_base_url, $recipe['images'], 125);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= "<div class='image-gallery'>" . $rc['content'] . "</div>";
			$page_content .= "</article>";
		}

		//
		// Display the similar recipes
		//
		if( isset($recipe['similar']) && count($recipe['similar']) > 0 ) {
			$page_content .= "<article class='page'>"
				. "<header class='entry-title'><h2 class='entry-title'>Similar Recipes</h2></header>\n"
				. "";
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processCIList');
			$base_url = $ciniki['request']['base_url'] . "/recipes/r";
			$rc = ciniki_web_processCIList($ciniki, $settings, $base_url, array('0'=>array(
				'name'=>'', 'noimage'=>'/ciniki-web-layouts/default/img/noimage_240.png',
				'list'=>$recipe['similar'])), array());
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= "<div class='entry-content'>" . $rc['content'] . "</div>";
			$page_content .= "</article>";
		}
	}

	//
	// Generate the category/cuisine listing page
	//
	elseif( isset($ciniki['request']['uri_split'][0]) 
		&& ($ciniki['request']['uri_split'][0] == 'category' 
			|| $ciniki['request']['uri_split'][0] == 'cuisine' )
		&& $ciniki['request']['uri_split'][1] != '' ) {
		$page_title = urldecode($ciniki['request']['uri_split'][1]);

		ciniki_core_loadMethod($ciniki, $pkg, $mod, 'web', 'categoryRecipes');
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');

		$page_content .= "<article class='page'>\n"
			. "<header class='entry-title'><h1 id='entry-title' class='entry-title'>$page_title</h1></header>\n"
			. "<div class='entry-content'>\n"
			. "";

		//
		// Get the items for the specified category
		//
		$categoryRecipes = $pkg . '_' . $mod . '_web_categoryRecipes';
		$rc = $categoryRecipes($ciniki, $settings, $ciniki['request']['business_id'], 
			$ciniki['request']['uri_split'][0], urldecode($ciniki['request']['uri_split'][1]));
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$recipes = $rc['recipes'];

		//
		// Generate list of recipes
		//
		$base_url = $ciniki['request']['base_url'] . "/recipes/r";
		if( count($recipes) > 0 ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processCIList');
			$rc = ciniki_web_processCIList($ciniki, $settings, $base_url, array('0'=>array(
				'name'=>'', 'noimage'=>'/ciniki-web-layouts/default/img/noimage_240.png',
				'list'=>$recipes)), array());
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= $rc['content'];
		} else {
			$page_content .= "<p>Currently no recipes.</p>";
		}
		$page_content .= "</article>"
			. "</div>"
			. "";
	}

	//
	// Generate the main recipes page, showing the main categories
	//
	else {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash');
		$rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_web_content', 'business_id', $ciniki['request']['business_id'], 'ciniki.web', 'content', 'page-recipes');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}

		$page_content .= "<article class='page'>\n"
			. "<header class='entry-title'><h1 id='entry-title' class='entry-title'>$page_title</h1></header>\n"
			. "<div class='entry-content'>\n"
			. "";

		if( isset($rc['content']['page-recipes-content']) ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
			$rc = ciniki_web_processContent($ciniki, $rc['content']['page-recipes-content']);	
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= $rc['content'];
		}

		//
		// List the categories the user has created in the artcatalog, 
		// OR just show all the thumbnails if they haven't created any categories
		//
		ciniki_core_loadMethod($ciniki, $pkg, $mod, 'web', 'categories');
		$categories = $pkg . '_' . $mod . '_web_categories';
		$rc = $categories($ciniki, $settings, $ciniki['request']['business_id']); 
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( isset($settings['page-recipes-name']) && $settings['page-recipes-name'] != '' ) {
			$page_title = $settings['page-recipes-name'];
		} else {
			$page_title = 'Recipes';
		}
		if( !isset($rc['categories']) ) {
			return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1554', 'msg'=>'Internal error'));
		} else {
			$page_content .= "<div class='image-categories'>";
			foreach($rc['categories'] AS $cnum => $category) {
				$name = $category['category']['name'];
				ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
				$rc = ciniki_web_getScaledImageURL($ciniki, $category['category']['image_id'], 'thumbnail', '240', 0);
				if( $rc['stat'] != 'ok' ) {
					$img_url = '/ciniki-web-layouts/default/img/noimage_240.png';
				} else {
					$img_url = $rc['url'];
				}
				$page_content .= "<div class='image-categories-thumbnail-wrap'>"
					. "<a href='" . $ciniki['request']['base_url'] . "/$category_uri_component/category/" . urlencode($name) . "' "
						. "title='" . $name . "'>"
					. "<div class='image-categories-thumbnail'>"
					. "<img title='$name' alt='$name' src='$img_url' />"
					. "</div>"
					. "<span class='image-categories-name'>$name</span>"
					. "</a></div>";
			}
			$page_content .= "</div>";
		}
		$page_content .= "</article>"
			. "</div>"
			. "";
	}

	$content = '';

	//
	// Add the header
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageHeader');
	$rc = ciniki_web_generatePageHeader($ciniki, $settings, $page_title, array());
	if( $rc['stat'] != 'ok' ) {	
		return $rc;
	}
	$content .= $rc['content'];

	//
	// Build the page content
	//
	$content .= "<div id='content'>\n";

	if( $page_content != '' ) {
		$content .= $page_content;
	}

	$content .= "</div>";

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
