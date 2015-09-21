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

	$tag_types = array(
		'meals'=>array('name'=>'Meals & Courses', 'tag_type'=>'10', 'visible'=>($ciniki['business']['modules']['ciniki.recipes']['flags']&0x01)>0?'yes':'no'),
		'ingredients'=>array('name'=>'Main Ingredients', 'tag_type'=>'20', 'visible'=>($ciniki['business']['modules']['ciniki.recipes']['flags']&0x02)>0?'yes':'no'),
		'cuisines'=>array('name'=>'Cuisines', 'tag_type'=>'30', 'visible'=>($ciniki['business']['modules']['ciniki.recipes']['flags']&0x04)>0?'yes':'no'),
		'methods'=>array('name'=>'Methods', 'tag_type'=>'40', 'visible'=>($ciniki['business']['modules']['ciniki.recipes']['flags']&0x08)>0?'yes':'no'),
		'occasions'=>array('name'=>'Occasions', 'tag_type'=>'50', 'visible'=>($ciniki['business']['modules']['ciniki.recipes']['flags']&0x10)>0?'yes':'no'),
		'diets'=>array('name'=>'Diets', 'tag_type'=>'60', 'visible'=>($ciniki['business']['modules']['ciniki.recipes']['flags']&0x20)>0?'yes':'no'),
		'seasons'=>array('name'=>'Seasons', 'tag_type'=>'70', 'visible'=>($ciniki['business']['modules']['ciniki.recipes']['flags']&0x40)>0?'yes':'no'),
		'collections'=>array('name'=>'Collections', 'tag_type'=>'80', 'visible'=>($ciniki['business']['modules']['ciniki.recipes']['flags']&0x80)>0?'yes':'no'),
		'products'=>array('name'=>'Products', 'tag_type'=>'90', 'visible'=>($ciniki['business']['modules']['ciniki.recipes']['flags']&0x0100)>0?'yes':'no'),
		);

	//
	// Check if a file was specified to be downloaded
	//
	$download_err = '';
	if( isset($ciniki['business']['modules']['ciniki.recipes'])
		&& isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] == 'download'
		&& isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] != ''
		&& isset($ciniki['request']['uri_split'][2]) && $ciniki['request']['uri_split'][2] != '' 
		&& preg_match("/^(.*)\.pdf$/", $ciniki['request']['uri_split'][2], $matches)
		) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'recipes', 'web', 'downloadPDF');
		$rc = ciniki_recipes_web_downloadPDF($ciniki, $settings, $ciniki['request']['business_id'], 
			$matches[1], array('layout'=>$ciniki['request']['uri_split'][1]));
		if( $rc['stat'] == 'ok' ) {
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			header("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
			header('Cache-Control: no-cache, must-revalidate');
			header('Pragma: no-cache');
			$file = $rc['file'];
			if( $file['extension'] == 'pdf' ) {
				header('Content-Type: application/pdf');
			}
//			header('Content-Disposition: attachment;filename="' . $file['filename'] . '"');
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
	// Check the module is active
	//
	if( !isset($ciniki['business']['modules']['ciniki.recipes']) ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1552', 'msg'=>'No recipe module enabled'));
	}

	//
	// Store the content created by the page
	//
	$page_content = '';

	$page_title = "Recipes";
	$article_title = "Recipes";
	$display = '';
	$ciniki['response']['head']['og']['url'] = $ciniki['request']['domain_base_url'] . '/recipes';
	$base_url = $ciniki['request']['base_url'] . '/recipes';

	
	//
	// Parse the request arguments
	// ---------------------------
	//

	//
	// Check if tag type specified
	//
	if( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] != '' 
		&& isset($tag_types[$ciniki['request']['uri_split'][0]]) && $tag_types[$ciniki['request']['uri_split'][0]]['visible'] == 'yes'
		) {
		$type_permalink = $ciniki['request']['uri_split'][0];
		$tag_type = $tag_types[$type_permalink]['tag_type'];
		$tag_title = $tag_types[$type_permalink]['name'];
		$display = 'type';
		$ciniki['response']['head']['og']['url'] .= '/' . $type_permalink;
		$base_url .= '/' . $type_permalink;

		//
		// Check if recipe was specified
		//
		if( isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] != '' 
			&& isset($ciniki['request']['uri_split'][2]) && $ciniki['request']['uri_split'][2] != '' ) {
			$tag_permalink = $ciniki['request']['uri_split']['1'];
			$recipe_permalink = $ciniki['request']['uri_split']['2'];
			$display = 'recipe';
			$ciniki['response']['head']['links'][] = array('rel'=>'canonical', 
				'href'=>$ciniki['request']['domain_base_url'] . '/recipes/' . $recipe_permalink);
			$ciniki['response']['head']['og']['url'] .= '/' . $tag_permalink . '/' . $recipe_permalink;
			$base_url .= '/' . $tag_permalink . '/' . $recipe_permalink;
			
			//
			// Check for gallery pic request
			//
			if( isset($ciniki['request']['uri_split'][3]) && $ciniki['request']['uri_split'][3] == 'gallery' 
				&& isset($ciniki['request']['uri_split'][4]) && $ciniki['request']['uri_split'][4] != '' 
				) {
				$image_permalink = $ciniki['request']['uri_split'][4];
				$display = 'recipepic';
			}
		} 

		//
		// Check if tag name was specified
		//
		elseif( isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] != '' ) {
			$tag_type = $tag_types[$ciniki['request']['uri_split'][0]]['tag_type'];
			$tag_title = $tag_types[$ciniki['request']['uri_split'][0]]['name'];
			$tag_permalink = $ciniki['request']['uri_split']['1'];
			$display = 'tag';
			$ciniki['response']['head']['og']['url'] .= '/' . $tag_permalink;
			$base_url .= '/' . $tag_permalink;
		}
	}

	//
	// Check if recipe url request without tag path
	//
	elseif( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] != '' ) {
		$recipe_permalink = $ciniki['request']['uri_split'][0];
		$display = 'recipe';
		//
		// Check for gallery pic request
		//
		if( isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] == 'gallery'
			&& isset($ciniki['request']['uri_split'][2]) && $ciniki['request']['uri_split'][2] != '' 
			) {
			$image_permalink = $ciniki['request']['uri_split'][2];
			$display = 'recipepic';
		}
		$ciniki['response']['head']['og']['url'] .= '/' . $recipe_permalink;
		$base_url .= '/' . $recipe_permalink;
	}

	//
	// Nothing selected, default to first tag type
	//
	else {
		$display = 'type';
		foreach($tag_types as $permalink => $type) {
			if( $type['visible'] == 'yes' ) {
				$tag_type = $type['tag_type'];
				$type_permalink = $permalink;
				$tag_title = $type['name'];
				$ciniki['response']['head']['og']['url'] .= '/' . $permalink;
				break;
			}
		}
	}

	//
	// Build the page content
	// ----------------------
	//
	
	//
	// Display the tag type page
	//
	if( $display == 'type' ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'recipes', 'web', 'tags');
		$rc = ciniki_recipes_web_tags($ciniki, $settings, $ciniki['request']['business_id'], $tag_type);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( isset($rc['tags']) ) {
			$tags = $rc['tags'];
		} else {
			$tags = array();
		}
	
		$page_title = 'Recipes - ' . $tag_types[$type_permalink]['name'];
		$article_title = '<a href="' . $ciniki['request']['base_url'] . '/recipes">Recipes</a> - ' . $tag_types[$type_permalink]['name'];

		$page_content .= "<article class='page'>\n"
			. "<header class='entry-title'><h1 id='entry-title' class='entry-title'>$article_title</h1></header>\n"
			. "<div class='entry-content'>\n"
			. "";

		if( count($tags) > 25 || $tag_type == '20' ) {
			//
			// Process the tags
			//
			if( isset($rc['tags']) && count($rc['tags']) > 0 ) {
				ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processTagCloud');
				$rc = ciniki_web_processTagCloud($ciniki, $settings, $base_url, $rc['tags']);
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				$page_content .= $rc['content'];
			} else {
				$page_content = "<p>I'm sorry, there are no tags.";
			}

		} elseif( count($tags) > 0 ) {
			$page_content .= "<div class='image-categories'>";
			foreach($rc['tags'] as $tid => $tag) {
				$name = $tag['name'];
				ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
				$rc = ciniki_web_getScaledImageURL($ciniki, $tag['image_id'], 'thumbnail', '240', 0);
				if( $rc['stat'] != 'ok' ) {
					$img_url = '/ciniki-web-layouts/default/img/noimage_240.png';
				} else {
					$img_url = $rc['url'];
				}
				$page_content .= "<div class='image-categories-thumbnail-wrap'>"
					. "<a href='" . $ciniki['request']['base_url'] . "/recipes/$type_permalink/" . $tag['permalink'] . "' " . "title='$name'>"
					. "<div class='image-categories-thumbnail'>"
					. "<img title='$name' alt='$name' src='$img_url' />"
					. "</div>"
					. "<span class='image-categories-name'>$name</span>"
					. "</a></div>";
			}
			$page_content .= "</div>";
		} else {
			$page_content .= "<p>I'm sorry, but we don't have any recipes for that category.</p>";	
		}
	}


	elseif( $display == 'tag' ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'recipes', 'web', 'recipes');
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');

		//
		// Get the items for the specified category
		//
		ciniki_core_loadMethod($ciniki, 'ciniki', 'recipes', 'web', 'recipes');
		$rc = ciniki_recipes_web_recipes($ciniki, $settings, $ciniki['request']['business_id'],
			array('tag_type'=>$tag_type, 'tag_permalink'=>$tag_permalink));
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$recipes = $rc['recipes'];

		$tag_name = $ciniki['request']['uri_split'][1];
		foreach($recipes as $recipe) {
			$tag_name = $recipe['tag_name'];
			break;
		}

		$page_title = 'Recipes - ' . $tag_types[$type_permalink]['name'] . ' - ' . $tag_name;
		$article_title = '<a href="' . $ciniki['request']['base_url'] . '/recipes">Recipes</a> - ' 
			. '<a href="' . $ciniki['request']['base_url'] . '/recipes/' . $type_permalink . '">' . $tag_types[$type_permalink]['name'] . '</a>'
			. ' - ' . $tag_name;

		$page_content .= "<article class='page'>\n"
			. "<header class='entry-title'><h1 id='entry-title' class='entry-title'>$article_title</h1></header>\n"
			. "<div class='entry-content'>\n"
			. "";

		//
		// Generate list of recipes
		//
		$base_url = $ciniki['request']['base_url'] . "/recipes";
		if( count($recipes) > 0 ) {
			$ci_base_url = $base_url;
			if( isset($type_permalink) && isset($tag_permalink) && $tag_permalink != '' ) {
				$ci_base_url .= "/$type_permalink/$tag_permalink";
			}
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processCIList');
			$rc = ciniki_web_processCIList($ciniki, $settings, $ci_base_url, array('0'=>array(
				'name'=>'', 'noimage'=>'/ciniki-web-layouts/default/img/noimage_240.png',
				'list'=>$recipes)), array());
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= $rc['content'];
		} else {
			$page_content .= "<p>Currently no recipes.</p>";
		}

		$page_content .= "</div>";
		$page_content .= "</article>";
	}

	//
	// Display the recipe or an image from the recipes additional images
	//
	elseif( $display == 'recipe' || $display == 'recipepic' ) {
		//
		// Load the recipe to get all the details, and the list of images.
		// It's one query, and we can find the requested image, and figure out next
		// and prev from the list of images returned
		//
		ciniki_core_loadMethod($ciniki, 'ciniki', 'recipes', 'web', 'recipeDetails');
		$rc = ciniki_recipes_web_recipeDetails($ciniki, $settings, 
			$ciniki['request']['business_id'], $recipe_permalink, (isset($tag_permalink)?$tag_permalink:''));
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$recipe = $rc['recipe'];
		if( isset($rc['tag_name']) ) {
			$tag_name = $rc['tag_name'];
		}
		$ciniki['response']['head']['og']['description'] = strip_tags($recipe['description']);

		if( isset($tag_permalink) && $tag_permalink != '' ) {
			$page_title = 'Recipes - ' . $tag_types[$type_permalink]['name'] . ' - ' . $tag_name . ' - ' . $recipe['name'];
			$article_title = '<a href="' . $ciniki['request']['base_url'] . '/recipes">Recipes</a>' 
				. ' - <a href="' . $ciniki['request']['base_url'] . '/recipes/' . $type_permalink . '">' . $tag_types[$type_permalink]['name'] . '</a>'
				. ' - <a href="' . $ciniki['request']['base_url'] . '/recipes/' . $type_permalink . '/' . $tag_permalink . '">' . $tag_name . '</a>'
				. '';
		} else {
			$page_title = 'Recipes - ' . $recipe['name'];
			$article_title = '<a href="' . $ciniki['request']['base_url'] . '/recipes">Recipes</a>';
		}
		
		if( $display == 'recipepic' ) {
			if( !isset($recipe['images']) || count($recipe['images']) < 1 ) {
				return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1553', 'msg'=>'Unable to find image'));
			}

			$article_title .= ' - <a href="' . $ciniki['request']['base_url'] . '/recipes/' . $recipe_permalink . '">' . $recipe['name'] . '</a>';

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
				$page_title .= ' - ' . $img['title'];
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
			$ciniki['response']['head']['og']['image'] = $rc['domain_url'];

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
				$page_content .= "<a id='gallery-image-prev' class='gallery-image-prev' href='$base_url/gallery/" . $prev['permalink'] . "'><div id='gallery-image-prev-img'></div></a>";
			}
			if( $next != null ) {
				$page_content .= "<a id='gallery-image-next' class='gallery-image-next' href='$base_url/gallery/" . $next['permalink'] . "'><div id='gallery-image-next-img'></div></a>";
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
		// Display recipe
		//
		else {
			$article_title .= ' - ' . $recipe['name'];
			$page_content .= "<article class='page'>\n"
				. "<header class='entry-title'><h1 class='entry-title'>" . $article_title . "</h1></header>\n"
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
				$ciniki['response']['head']['og']['image'] = $rc['domain_url'];
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
			if( isset($recipe['roast_time']) && $recipe['roast_time'] != '' ) {
				$additional_info .= "<b>Roast Time</b>: " . $recipe['roast_time'] . " minutes<br/>";
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
			// Add print options
			//
//			$page_content .= "<p><b>Printing Options</b><br/>"
//				. "You can download and print a PDF version of this recipe, formatted for 8.5\" x 11\" paper. "
//				. "</p><p>"
//				. "<a target='_blank' href='" . $ciniki['request']['base_url'] . '/recipes/download/single/' . $recipe['permalink'] . ".pdf'>Large</a><br/>"
//				. "</p>";
			$page_content .= "<p>"
				. "<a target='_blank' href='" . $ciniki['request']['base_url'] . '/recipes/download/single/' . $recipe['permalink'] . ".pdf'>Print this recipe</a><br/>"
				. "</p>";

			//
			// Display the categories and tags for the blog post
			//
			$meta_content = '';
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processTagList');
	//		if( isset($recipe['categories']) && count($recipe['categories']) > 0 ) {
	//			$rc = ciniki_web_processTagList($ciniki, $settings, 
	//				$ciniki['request']['base_url'] . '/recipe/category', ', ', $recipe['categories']);
	//			if( $rc['stat'] != 'ok' ) {
	//				return $rc;
	//			}
	//			if( isset($rc['content']) && $rc['content'] != '' ) {
	//				$meta_content .= 'Filed under: ' . $rc['content'];
	//			}
	//		}
/*			if( isset($recipe['tags']) && count($recipe['tags']) > 0 ) {
				$rc = ciniki_web_processTagList($ciniki, $settings,
					$ciniki['request']['base_url'] . '/recipes/tag', $recipe['tags'], array('delimiter'=>', '));
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				if( isset($rc['content']) && $rc['content'] != '' ) {
					$meta_content .= ($meta_content!=''?'<br/>':'') . 'Tags: ' . $rc['content'];
				}
			}
			if( $meta_content != '' ) {
				$page_content .= '<p class="entry-meta">' . $meta_content . '</p>';
			}
*/
			if( !isset($settings['page-recipes-share-buttons']) 
				|| $settings['page-recipes-share-buttons'] == 'yes' ) {
				if( isset($recipe['category']) && $recipe['category'] != '' ) {
					$tags[] = $recipe['category'];
				}
				if( isset($recipe['categories']) ) {
					foreach($recipe['categories'] as $cat) {
						$tags[] = $cat['name'];
					}
				}
				if( isset($recipe['tags']) ) {
					foreach($recipe['tags'] as $tag) {
						$tags[] = $tag['name'];
					}
				}
				ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processShareButtons');
				$rc = ciniki_web_processShareButtons($ciniki, $settings, array(
					'title'=>$page_title,
					'tags'=>$tags,
					));
				if( $rc['stat'] == 'ok' ) {
					$page_content .= $rc['content'];
				}
			}

			//
			// Display the additional images for the recipe
			//
			if( isset($recipe['images']) && count($recipe['images']) > 0 ) {
				$page_content .= "<br clear='both'/>";
				$page_content .= "<h2 class='entry-title'>Gallery</h2>\n";
				ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageGalleryThumbnails');
				$rc = ciniki_web_generatePageGalleryThumbnails($ciniki, $settings, $base_url . '/gallery', $recipe['images'], 125);
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
				$page_content .= "<br clear='both'/>";
				$page_content .= "<h2 class='entry-title'>Similar Recipes</h2>\n"
					. "";
				ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processCIList');
				$similar_base_url = $ciniki['request']['base_url'] . "/recipes/";
				$rc = ciniki_web_processCIList($ciniki, $settings, $similar_base_url, array('0'=>array(
					'name'=>'', 'noimage'=>'/ciniki-web-layouts/default/img/noimage_240.png',
					'list'=>$recipe['similar'])), array());
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				$page_content .= "<div class='entry-content'>" . $rc['content'] . "</div>";
				$page_content .= "</article>";
			}
		}
	}


	//
	// The submenu 
	//
	$submenu = array();
	foreach($tag_types as $tag_permalink => $tag) {
		if( $tag['visible'] == 'yes' ) {
			$submenu[$tag_permalink] = array('name'=>$tag['name'], 'url'=>$ciniki['request']['base_url'] . '/recipes/' . $tag_permalink);
		}
	}

	
	//
	// Add the header and footer content
	// ---------------------------------
	//
	$content = '';

	//
	// Add the header
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageHeader');
	$rc = ciniki_web_generatePageHeader($ciniki, $settings, $page_title, $submenu);
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
