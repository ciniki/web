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
function ciniki_web_generatePageProducts($ciniki, $settings) {

	//
	// Check if a file was specified to be downloaded
	//
	$download_err = '';
	if( isset($ciniki['business']['modules']['ciniki.products'])
		&& isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] == 'p'
		&& isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] != ''
		&& isset($ciniki['request']['uri_split'][2]) && $ciniki['request']['uri_split'][2] == 'download'
		&& isset($ciniki['request']['uri_split'][3]) && $ciniki['request']['uri_split'][3] != '' ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'products', 'web', 'fileDownload');
		$rc = ciniki_products_web_fileDownload($ciniki, $ciniki['request']['business_id'], 
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
		return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'1504', 'msg'=>'The file you requested does not exist.'));
	}

	//
	// Store the content created by the page
	//
	$page_content = '';

	//
	// FIXME: Check if anything has changed, and if not load from cache
	//
		
	$page_title = "Products";
	$tags = array();
	$ciniki['response']['head']['og']['url'] = $ciniki['request']['domain_base_url'] . '/products';

	//
	// Generate the product page
	//
	if( isset($ciniki['request']['uri_split'][0]) 
		&& $ciniki['request']['uri_split'][0] == 'product'
		&& isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] != '' 
		) {
		$product_permalink = $ciniki['request']['uri_split'][1];
		ciniki_core_loadMethod($ciniki, 'ciniki', 'products', 'web', 'productDetails');
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processProduct');

		//
		// Get the product information
		//
		$rc = ciniki_products_web_productDetails($ciniki, $settings, $ciniki['request']['business_id'], 
			array('product_permalink'=>$product_permalink));
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$product = $rc['product'];
		$page_title = $product['name'];

//		$ciniki['response']['head']['links'][] = array('rel'=>'canonical', 
//			'href'=>$ciniki['request']['domain_base_url'] . '/products/product/' . $product_permalink
//			);
		$ciniki['response']['head']['og']['url'] .= '/product/' . $product_permalink;
		$ciniki['response']['head']['og']['description'] = strip_tags($product['short_description']);

		//
		// Check if image requested
		//
		if( isset($ciniki['request']['uri_split'][2]) && $ciniki['request']['uri_split'][2] == 'gallery'
			&& isset($ciniki['request']['uri_split'][3]) && $ciniki['request']['uri_split'][3] != '' 
			) {
			$image_permalink = $ciniki['request']['uri_split'][3];
//			$ciniki['response']['head']['links']['canonical']['href'] .= '/gallery/' . $image_permalink;
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processGalleryImage');
			$rc = ciniki_web_processGalleryImage($ciniki, $settings, $ciniki['request']['business_id'],
				array('item'=>$product,
					'article_title'=>"<a href='" . $ciniki['request']['base_url'] 
						. "/products/product/" . $product_permalink . "'>" . $product['name'] . "</a>",
					'image_permalink'=>$image_permalink,
				));
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= $rc['content'];
		} 
	
		//
		// Display the product
		//
		else {
			$article_title = $product['name'];
			$page_content .= "<article class='page'>\n"
				. "<header class='entry-title'><h1 class='entry-title'>" . $article_title . "</h1></header>\n"
				. "<div class='entry-content'>";

			$base_url = $ciniki['request']['base_url'] . "/products/product/" . $product_permalink;
			$rc = ciniki_web_processProduct($ciniki, $settings, $ciniki['request']['business_id'], 
				$base_url, $product, array('title'=>$page_title, 'tags'=>$product['social-tags']));
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= $rc['content'];
			$page_content .= "</div></article>";
		}
	}
	
	//
	// Generate the product in a category
	//
	elseif( isset($ciniki['request']['uri_split'][0]) 
		&& $ciniki['request']['uri_split'][0] == 'category'
		&& isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] != '' 
		&& isset($ciniki['request']['uri_split'][2]) && $ciniki['request']['uri_split'][2] == 'product'
		&& isset($ciniki['request']['uri_split'][3]) && $ciniki['request']['uri_split'][3] != '' 
		) {
		$category_permalink = $ciniki['request']['uri_split'][1];
		$product_permalink = $ciniki['request']['uri_split'][3];
		ciniki_core_loadMethod($ciniki, 'ciniki', 'products', 'web', 'productDetails');
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processProduct');
		
		//
		// Get the product information
		//
		$rc = ciniki_products_web_productDetails($ciniki, $settings, $ciniki['request']['business_id'], 
			array('product_permalink'=>$product_permalink, 
				'category_permalink'=>$category_permalink,
				));
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$product = $rc['product'];
		$page_title = $product['name'];
		$article_title = $product['name'];
		
		$ciniki['response']['head']['links'][] = array('rel'=>'canonical', 
			'href'=>$ciniki['request']['domain_base_url'] . '/products/product/' . $product_permalink
			);
		$ciniki['response']['head']['og']['url'] .= '/product/' . $product_permalink;
		$ciniki['response']['head']['og']['description'] = strip_tags($product['short_description']);

		if( isset($product['category_title']) && $product['category_title'] != '' ) {
			$article_title = "<a href='" . $ciniki['request']['base_url'] . "/products/category/" . $category_permalink
					. "'>" . $product['category_title'] . "</a>";
		} else {
			$article_title = $page_title;
		}

		//
		// Check if image requested
		//
		if( isset($ciniki['request']['uri_split'][4]) && $ciniki['request']['uri_split'][4] == 'gallery'
			&& isset($ciniki['request']['uri_split'][5]) && $ciniki['request']['uri_split'][5] != '' 
			) {
			$image_permalink = $ciniki['request']['uri_split'][5];
			$ciniki['response']['head']['links']['canonical']['href'] .= '/gallery/' . $image_permalink;
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processGalleryImage');
			$rc = ciniki_web_processGalleryImage($ciniki, $settings, $ciniki['request']['business_id'],
				array('item'=>$product,
					'article_title'=>$article_title .= " - <a href='" . $ciniki['request']['base_url'] 
						. "/products/category/$category_permalink/product/$product_permalink'>" . $product['name'] . "</a>",
					'image_permalink'=>$image_permalink,
				));
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= $rc['content'];
		} 
	
		//
		// Display the product
		//
		else {
			$article_title .= ' - ' . $product['name'];
			$page_content .= "<article class='page'>\n"
				. "<header class='entry-title'><h1 class='entry-title'>" . $article_title . "</h1></header>\n"
				. "<div class='entry-content'>";

			//
			// Display the product
			//
			$base_url = $ciniki['request']['base_url'] . "/products/category/$category_permalink"
				. "/product/" . $product_permalink;
			$rc = ciniki_web_processProduct($ciniki, $settings, $ciniki['request']['business_id'], 
				$base_url, $product, array('title'=>$page_title, 'tags'=>$product['social-tags']));
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= $rc['content'];

			$page_content .= "</div></article>";
		}
	}

	//
	// Generate the product in a sub category
	//
	elseif( isset($ciniki['request']['uri_split'][0]) 
		&& $ciniki['request']['uri_split'][0] == 'category'
		&& isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] != '' 
		&& isset($ciniki['request']['uri_split'][2]) && $ciniki['request']['uri_split'][2] != '' 
		&& isset($ciniki['request']['uri_split'][3]) && $ciniki['request']['uri_split'][3] == 'product'
		&& isset($ciniki['request']['uri_split'][4]) && $ciniki['request']['uri_split'][4] != '' 
		) {
		$category_permalink = $ciniki['request']['uri_split'][1];
		$subcategory_permalink = $ciniki['request']['uri_split'][2];
		$product_permalink = $ciniki['request']['uri_split'][4];
		ciniki_core_loadMethod($ciniki, 'ciniki', 'products', 'web', 'productDetails');
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processProduct');
		
		//
		// Get the product information
		//
		$rc = ciniki_products_web_productDetails($ciniki, $settings, $ciniki['request']['business_id'], 
			array('product_permalink'=>$product_permalink, 
				'category_permalink'=>$category_permalink,
				'subcategory_permalink'=>$subcategory_permalink,
				));
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$product = $rc['product'];
		$page_title = $product['name'];
		$article_title = $product['name'];

		$ciniki['response']['head']['links']['canonical'] = array('rel'=>'canonical', 
			'href'=>$ciniki['request']['domain_base_url'] . '/products/product/' . $product_permalink
			);
		$ciniki['response']['head']['og']['url'] .= '/product/' . $product_permalink;
		$ciniki['response']['head']['og']['description'] = strip_tags($product['short_description']);

		if( isset($product['category_title']) && $product['category_title'] != '' ) {
			$article_title = "<a href='" . $ciniki['request']['base_url'] . "/products/category/" . $category_permalink
					. "'>" . $product['category_title'] . "</a>";
		} else {
			$article_title = $page_title;
		}
		if( isset($product['subcategory_title']) && $product['subcategory_title'] != '' ) {
			$article_title .= ' - ' 
				. "<a href='" . $ciniki['request']['base_url'] . "/products/category/" . $category_permalink
					. "/" . $subcategory_permalink . "'>" . $product['subcategory_title'] . "</a>";
		}

		//
		// Check if image requested
		//
		if( isset($ciniki['request']['uri_split'][5]) && $ciniki['request']['uri_split'][5] == 'gallery'
			&& isset($ciniki['request']['uri_split'][6]) && $ciniki['request']['uri_split'][6] != '' 
			) {
			$image_permalink = $ciniki['request']['uri_split'][6];
			$ciniki['response']['head']['links']['canonical']['href'] .= '/gallery/' . $image_permalink;
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processGalleryImage');
			$rc = ciniki_web_processGalleryImage($ciniki, $settings, $ciniki['request']['business_id'],
				array('item'=>$product,
					'article_title'=>$article_title .= " - <a href='" . $ciniki['request']['base_url'] 
						. "/products/category/$category_permalink/$subcategory_permalink"
						. "/product/$product_permalink'>" . $product['name'] . "</a>",
					'image_permalink'=>$image_permalink,
				));
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= $rc['content'];
		} 
	
		//
		// Display the product
		//
		else {
			$article_title .= ' - ' . $product['name'];
			$page_content .= "<article class='page'>\n"
				. "<header class='entry-title'><h1 class='entry-title'>" . $article_title . "</h1></header>\n"
				. "<div class='entry-content'>";

			//
			// Display the product
			//
			$base_url = $ciniki['request']['base_url'] 
				. "/products/category/$category_permalink/$subcategory_permalink" 
				. "/product/" . $product_permalink;
			$rc = ciniki_web_processProduct($ciniki, $settings, $ciniki['request']['business_id'], 
				$base_url, $product, array('title'=>$page_title, 'tags'=>$product['social-tags']));
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= $rc['content'];
			$page_content .= "</div></article>";
		}
	}

	//
	// Generate the sub-category listing page
	//
	elseif( isset($ciniki['request']['uri_split'][0]) 
		&& $ciniki['request']['uri_split'][0] == 'category'
		&& isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] != '' 
		&& isset($ciniki['request']['uri_split'][2]) && $ciniki['request']['uri_split'][2] != '' 
		) {
		$category_permalink = urldecode($ciniki['request']['uri_split'][1]);
		$subcategory_permalink = urldecode($ciniki['request']['uri_split'][2]);

		ciniki_core_loadMethod($ciniki, 'ciniki', 'products', 'web', 'subcategoryDetails');
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');

		//
		// Get the details for a category
		//
		$rc = ciniki_products_web_subcategoryDetails($ciniki, $settings, $ciniki['request']['business_id'],
			array('category_permalink'=>$category_permalink, 'subcategory_permalink'=>$subcategory_permalink));
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$details = isset($rc['details'])?$rc['details']:array();
		$products = isset($rc['products'])?$rc['products']:array();

//		print "<pre>" . print_r($rc, true) . "</pre>";

		if( isset($details['category_title']) && $details['category_title'] != '' ) {
//			$article_title = "<a href='" . $ciniki['request']['base_url'] . "/products'>$page_title</a> - "
			$article_title = "<a href='" . $ciniki['request']['base_url'] . "/products/category/" . $category_permalink
					. "'>" . $details['category_title'] . "</a>";
			$page_title = $details['category_title'];
		} else {
			$article_title = $page_title;
		}
		if( isset($details['subcategory_title']) && $details['subcategory_title'] != '' ) {
			$article_title .= ' - ' . $details['subcategory_title'];
			$page_title .= ' - ' . $details['subcategory_title'];
		}

		$page_content .= "<article class='page'>\n"
			. "<header class='entry-title'><h1 id='entry-title' class='entry-title'>"
			. $article_title
			. "</h1></header>\n"
			. "<div class='entry-content'>\n"
			. "";

		if( isset($products) && count($products) > 0 ) {
			$base_url = $ciniki['request']['base_url'] . "/products/category/" . $category_permalink 
				. "/" . $subcategory_permalink
				. "/product";
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processCIList');
			$rc = ciniki_web_processCIList($ciniki, $settings, $base_url, array('0'=>array(
				'name'=>'', 'noimage'=>'/ciniki-web-layouts/default/img/noimage_240.png',
				'list'=>$products)), array());
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= $rc['content'];
		} else {
			$page_content .= "<p>I'm sorry, but there doesn't appear to be an products available in this category.</p>";
		}


	}

	//
	// Generate the category listing page
	//
	elseif( isset($ciniki['request']['uri_split'][0]) 
		&& $ciniki['request']['uri_split'][0] == 'category'
		&& $ciniki['request']['uri_split'][1] != '' 
		) {
		$category_permalink = urldecode($ciniki['request']['uri_split'][1]);

		ciniki_core_loadMethod($ciniki, 'ciniki', 'products', 'web', 'categoryDetails');
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');

		//
		// Get the details for a category
		//
		$rc = ciniki_products_web_categoryDetails($ciniki, $settings, $ciniki['request']['business_id'],
			array('category_permalink'=>$category_permalink));
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$details = isset($rc['details'])?$rc['details']:array();
		$subcategories = isset($rc['subcategories'])?$rc['subcategories']:array();
		$subcategorytypes = isset($rc['subcategorytypes'])?$rc['subcategorytypes']:array();
		$products = isset($rc['products'])?$rc['products']:array();

//		print "<pre>" . print_r($rc, true) . "</pre>";
		
		if( isset($details['category_title']) && $details['category_title'] != '' ) {
//			$article_title = "<a href='" . $ciniki['request']['base_url'] . "/products'>$page_title</a> - "
//				. $details['category_title'];
			$article_title = $details['category_title'];
			$page_title = $details['category_title'];
		} else {
			$article_title = $page_title;
		}

		$page_content .= "<article class='page'>\n"
			. "<header class='entry-title'><h1 id='entry-title' class='entry-title'>"
			. $article_title
			. "</h1></header>\n"
			. "<div class='entry-content'>\n"
			. "";

		//
		// if there's any content or an image for the category, display it
		//
		if( (isset($details['content']) && $details['content'] != '')
			|| isset($details['image_id']) && $details['image_id'] != 0 ) {
			// Image
			if( isset($details['image_id']) && $details['image_id'] > 0 ) {
				ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
				$rc = ciniki_web_getScaledImageURL($ciniki, $details['image_id'], 'original', '500', 0);
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				$page_content .= "<aside><div class='image-wrap'><div class='image'>"
					. "<img title='' alt='" . $product['name'] . "' src='" . $rc['url'] . "' />"
					. "</div></div></aside>";
			}
			
			// Content
			if( isset($rc['details']['content']) ) {
				ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
				$rc = ciniki_web_processContent($ciniki, $rc['details']['content']);	
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				$page_content .= $rc['content'];
			}
		}

		//
		// If there are sub categories, display them
		//
		if( isset($subcategories) && count($subcategories) > 0 ) {
			$base_url = $ciniki['request']['base_url'] . "/products/category/" . $category_permalink . "/category";
			$page_content .= "<div class='image-categories'>";
			foreach($subcategories AS $cnum => $category) {
				$name = $category['name'];
				$permalink = $category['permalink'];
				ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
				$rc = ciniki_web_getScaledImageURL($ciniki, $category['image_id'], 'thumbnail', '240', 0);
				if( $rc['stat'] != 'ok' ) {
					$img_url = '/ciniki-web-layouts/default/img/noimage_240.png';
				} else {
					$img_url = $rc['url'];
				}
				$page_content .= "<div class='image-categories-thumbnail-wrap'>"
					. "<a href='" . $ciniki['request']['base_url'] . "/products/category/" 
						. $category_permalink . "/" . $permalink . "' " . "title='" . $name . "'>"
					. "<div class='image-categories-thumbnail'>"
					. "<img title='$name' alt='$name' src='$img_url' />"
					. "</div>"
					. "<span class='image-categories-name'>$name</span>"
					. "</a></div>";
			}
			$page_content .= "</div>";
		}

		//
		// If there is more than one type of subcategory
		//
		if( isset($subcategorytypes) && count($subcategorytypes) > 0 ) {
			foreach($subcategorytypes as $tid => $type) {
				$subcategories = $type['categories'];
				$base_url = $ciniki['request']['base_url'] . "/products/category/" . $category_permalink . "/category";
				$page_content .= "<div class='image-categories'>";
				foreach($subcategories AS $cnum => $category) {
					$name = $category['name'];
					$permalink = $category['permalink'];
					ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
					$rc = ciniki_web_getScaledImageURL($ciniki, $category['image_id'], 'thumbnail', '240', 0);
					if( $rc['stat'] != 'ok' ) {
						$img_url = '/ciniki-web-layouts/default/img/noimage_240.png';
					} else {
						$img_url = $rc['url'];
					}
					$page_content .= "<div class='image-categories-thumbnail-wrap'>"
						. "<a href='" . $ciniki['request']['base_url'] . "/products/category/" 
							. $category_permalink . "/" . $permalink . "' " . "title='" . $name . "'>"
						. "<div class='image-categories-thumbnail'>"
						. "<img title='$name' alt='$name' src='$img_url' />"
						. "</div>"
						. "<span class='image-categories-name'>$name</span>"
						. "</a></div>";
				}
				$page_content .= "</div>";
			}
		}

		//
		// If there are products, display the list
		//
		if( isset($products) && count($products) > 0 ) {
			if( isset($subcategories) && count($subcategories) > 0 ) {
				$page_content .= "<br style='clear: both;' />";
			}
			$base_url = $ciniki['request']['base_url'] . "/products/category/" . $category_permalink 
				. "/product";
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processCIList');
			$rc = ciniki_web_processCIList($ciniki, $settings, $base_url, array('0'=>array(
				'name'=>'', 'noimage'=>'/ciniki-web-layouts/default/img/noimage_240.png',
				'list'=>$products)), array());
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= $rc['content'];
		}

		//
		// Generate list of products
		//
		$page_content .= "</div>";
		$page_content .= "</article>"
			. "";
	}

	//
	// Generate the main products page, showing the main categories
	//
	else {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash');
		$rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_web_content', 
			'business_id', $ciniki['request']['business_id'], 'ciniki.web', 'content', 'page-products');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}

		$page_content .= "<article class='page'>\n"
			. "<header class='entry-title'><h1 id='entry-title' class='entry-title'>$page_title</h1></header>\n"
			. "<div class='entry-content'>\n"
			. "";

		if( isset($rc['content']['page-products-content']) ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
			$rc = ciniki_web_processContent($ciniki, $rc['content']['page-products-content']);	
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= $rc['content'];
		}

		//
		// List the categories the user has created in the artcatalog, 
		// OR just show all the thumbnails if they haven't created any categories
		//
		ciniki_core_loadMethod($ciniki, 'ciniki', 'products', 'web', 'categories');
		$rc = ciniki_products_web_categories($ciniki, $settings, $ciniki['request']['business_id']); 
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( isset($settings['page-products-name']) && $settings['page-products-name'] != '' ) {
			$page_title = $settings['page-products-name'];
		} else {
			$page_title = 'Products';
		}
		if( !isset($rc['categories']) ) {
			return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1496', 'msg'=>'Internal error'));
		} else {
			$page_content .= "<div class='image-categories'>";
			foreach($rc['categories'] AS $cnum => $category) {
				$name = $category['name'];
				$permalink = $category['permalink'];
				ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
				$rc = ciniki_web_getScaledImageURL($ciniki, $category['image_id'], 'thumbnail', '240', 0);
				if( $rc['stat'] != 'ok' ) {
					$img_url = '/ciniki-web-layouts/default/img/noimage_240.png';
				} else {
					$img_url = $rc['url'];
				}
				$page_content .= "<div class='image-categories-thumbnail-wrap'>"
					. "<a href='" . $ciniki['request']['base_url'] . "/products/category/" . $permalink . "' "
						. "title='" . $name . "'>"
					. "<div class='image-categories-thumbnail'>"
					. "<img title='$name' alt='$name' src='$img_url' />"
					. "</div>"
					. "<span class='image-categories-name'>$name</span>"
					. "</a></div>";
			}
			$page_content .= "</div>";
		}
		$page_content .= "</div>";
		$page_content .= "</article>"
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
	if( $page_content != '' ) { $content .= $page_content; }
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
