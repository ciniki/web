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
	// Check if we are to display an image, from the gallery, or latest images
	//
	if( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] != '' 
		&& ((($ciniki['request']['uri_split'][0] == 'category' || $ciniki['request']['uri_split'][0] == 'year')
			&& isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] != '' 
			&& isset($ciniki['request']['uri_split'][2]) && $ciniki['request']['uri_split'][2] != '' 
			)
			|| ($ciniki['request']['uri_split'][0] == 'latest' 
			&& isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] != '' 
			)
			|| ($ciniki['request']['uri_split'][0] == 'image' 
			&& isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] != '' 
			)
			)
		) {
		
		//
		// Get the permalink for the image requested
		//
		if( $ciniki['request']['uri_split'][0] == 'latest' ) {
			$image_permalink = $ciniki['request']['uri_split'][1];
		} elseif( $ciniki['request']['uri_split'][0] == 'image' ) {
			$image_permalink = $ciniki['request']['uri_split'][1];
		} else {
			$image_permalink = $ciniki['request']['uri_split'][2];
		}
		// 
		// Get the image details
		//
		$strsql = "SELECT ciniki_artcatalog.id, name, permalink, image_id, type, catalog_number, category, year, flags, webflags, "
			. "IF((ciniki_artcatalog.flags&0x01)=1, 'yes', 'no') AS forsale, "
			. "IF((ciniki_artcatalog.flags&0x02)=2, 'yes', 'no') AS sold, "
			. "IF((ciniki_artcatalog.webflags&0x01)=1, 'yes', 'no') AS hidden, "
			. "media, size, framed_size, price, location, awards, notes, "
			. "date_added, last_updated "
			. "FROM ciniki_artcatalog "
			. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['business_id']) . "' "
			. "AND permalink = '" . ciniki_core_dbQuote($ciniki, $image_permalink) . "' "
			. "";
		$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'artcatalog', 'piece');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( !isset($rc['piece']) ) {
			return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'651', 'msg'=>'Unable to find artwork'));
		}
		$img = $rc['piece'];
		$page_title = $img['name'];
		$prev = NULL;
		$next = NULL;

		//
		// Requested photo from within a gallery, which may be a category or year or latest
		// Latest category is special, and doesn't contain the keyword category, is also shortened url
		//
		if( $ciniki['request']['uri_split'][0] == 'latest' ) {
			$image_permalink = $ciniki['request']['uri_split'][1];
			//
			// Get the position of the image in the gallery.
			// Count the number of items before the specified image, then use
			// that number to LIMIT a query
			//
			$strsql = "SELECT COUNT(*) AS pos_num FROM ciniki_artcatalog "
				. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['business_id']) . "' "
				. "AND (webflags&0x01) = 0 "
				. "AND date_added > '" . ciniki_core_dbQuote($ciniki, $img['date_added']) . "' "
				. "";
			$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'artcatalog', 'position');
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			if( !isset($rc['position']['pos_num']) ) {
				return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'652', 'msg'=>'Unable to load image'));
			}
			$offset = $rc['position']['pos_num'];
			//
			// Get the previous and next photos
			//
			$strsql = "SELECT id, name, permalink "
				. "FROM ciniki_artcatalog "
				. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['business_id']) . "' "
				. "AND (webflags&0x01) = 0 "
				. "ORDER BY ciniki_artcatalog.date_added DESC "
				. "";
			if( $offset == 0 ) {
				$strsql .= "LIMIT 3 ";
			} elseif( $offset > 0 ) {
				$strsql .= "LIMIT " . ($offset-1) . ", 3";
			} else {
				return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'653', 'msg'=>'Unable to load image'));
			}
			$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'artcatalog', 'next');
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$prev = NULL;
			if( $offset > 0 && isset($rc['rows'][0]) ) {
				$prev = $rc['rows'][0];
			} elseif( $offset == 0 ) {
				//
				// Get the last image in the series
				//
			}
			$next = NULL;
			if( $offset > 0 && isset($rc['rows'][2]) ) {
				$next = $rc['rows'][2];
			} elseif( $offset == 0 && isset($rc['rows'][1]) ) {
				$next = $rc['rows'][1];
			}

			//
			// If the image requested is at the end of the gallery, then
			// get the first image
			//
			if( $rc['num_rows'] < 3 ) {
				$strsql = "SELECT id, name, permalink "
					. "FROM ciniki_artcatalog "
					. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['business_id']) . "' "
					. "AND (webflags&0x01) = 0 "
					. "ORDER BY ciniki_artcatalog.date_added DESC " 	// SORT to get the newest image first
					. "LIMIT 1"
					. "";
				$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'artcatalog', 'next');
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				if( isset($rc['next']) 
					&& $rc['next']['permalink'] != $image_permalink	// Make sure it's not the same image
					) {
					$next = $rc['next'];
				}
				
			}
			//
			// If the image is at begining of the gallery, then get the last image
			//
			if( $offset == 0 ) {
				$strsql = "SELECT id, name, permalink "
					. "FROM ciniki_artcatalog "
					. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['business_id']) . "' "
					. "AND (webflags&0x01) = 0 "
					. "ORDER BY ciniki_artcatalog.date_added ASC " 	// SORT to get the oldest image first
					. "LIMIT 1"
					. "";
				$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'artcatalog', 'prev');
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				if( isset($rc['prev']) 
					&& $rc['prev']['permalink'] != $image_permalink		// Check not a single image, and going to loop
					) {
					$prev = $rc['prev'];
				}
			}

		} elseif( $ciniki['request']['uri_split'][0] == 'image' ) {
			$image_permalink = $ciniki['request']['uri_split'][1];
			//
			// There is no next and previous images if request is direct to the image
			//
			$next = NULL;
			$prev = NULL;
		} else {
			$image_permalink = $ciniki['request']['uri_split'][2];
			//
			// Get the position of the image in the gallery.
			// Count the number of items before the specified image, then use
			// that number to LIMIT a query
			//
			$strsql = "SELECT COUNT(*) AS pos_num FROM ciniki_artcatalog "
				. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['business_id']) . "' "
				. "AND (webflags&0x01) = 0 "
				. "AND category = '" . ciniki_core_dbQuote($ciniki, $img['category']) . "' "
				. "AND date_added > '" . ciniki_core_dbQuote($ciniki, $img['date_added']) . "' "
				. "";
			$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'artcatalog', 'position');
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			if( !isset($rc['position']['pos_num']) ) {
				return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'652', 'msg'=>'Unable to load image'));
			}
			$offset = $rc['position']['pos_num'];
			//
			// Get the previous and next photos
			//
			$strsql = "SELECT id, name, permalink "
				. "FROM ciniki_artcatalog "
				. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['business_id']) . "' "
				. "AND (webflags&0x01) = 0 "
				. "AND category = '" . ciniki_core_dbQuote($ciniki, $img['category']) . "' "
				. "ORDER BY ciniki_artcatalog.date_added DESC "
				. "";
			if( $offset == 0 ) {
				$strsql .= "LIMIT 3 ";
			} elseif( $offset > 0 ) {
				$strsql .= "LIMIT " . ($offset-1) . ", 3";
			} else {
				return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'653', 'msg'=>'Unable to load image'));
			}
			$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'artcatalog', 'next');
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$prev = NULL;
			if( $offset > 0 && isset($rc['rows'][0]) && $rc['rows'][0]['permalink'] != $image_permalink ) {
				$prev = $rc['rows'][0];
			}
			$next = NULL;
			if( $offset > 0 && isset($rc['rows'][2]) ) {
				$next = $rc['rows'][2];
			} elseif( $offset == 0 && isset($rc['rows'][1]) ) {
				$next = $rc['rows'][1];
			}

			//
			// If the image requested is at the end of the gallery, then
			// get the first image
			//
			if( $rc['num_rows'] < 3 ) {
				$strsql = "SELECT id, name, permalink "
					. "FROM ciniki_artcatalog "
					. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['business_id']) . "' "
					. "AND (webflags&0x01) = 0 "
					. "AND category = '" . ciniki_core_dbQuote($ciniki, $img['category']) . "' "
					. "ORDER BY ciniki_artcatalog.date_added DESC " 	// SORT to get the newest image first
					. "LIMIT 1"
					. "";
				$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'artcatalog', 'next');
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				if( isset($rc['next']) 
					&& $rc['next']['permalink'] != $image_permalink	// Make sure it's not the same image
					) {
					$next = $rc['next'];
				}
			}
			//
			// If the image is at begining of the gallery, then get the last image
			//
			if( $offset == 0 ) {
				$strsql = "SELECT id, name, permalink "
					. "FROM ciniki_artcatalog "
					. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['business_id']) . "' "
					. "AND (webflags&0x01) = 0 "
					. "AND category = '" . ciniki_core_dbQuote($ciniki, $img['category']) . "' "
					. "ORDER BY ciniki_artcatalog.date_added ASC " 	// SORT to get the oldest image first
					. "LIMIT 1"
					. "";
				$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'artcatalog', 'prev');
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				if( isset($rc['prev']) && $next != NULL 
					&& $next['permalink'] != $rc['prev']['permalink']   // Check more than 2 images, and going to loop
					&& $rc['prev']['permalink'] != $image_permalink		// Check not a single image, and going to loop
					) {
					$prev = $rc['prev'];
				}
			}
		}

		//
		// Load the image
		//
		require_once($ciniki['config']['core']['modules_dir'] . '/web/private/getScaledImageURL.php');
		$rc = ciniki_web_getScaledImageURL($ciniki, $img['image_id'], 'original', 0, 600);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		//
		// Set the page to wide if possible
		//
		$ciniki['request']['page-container-class'] = 'page-container-wide';
		//
		// Javascript to resize the image, and arrow overlays once the image is loaded.
		// This is done so the image can be properly fit to the size of the screen.
		//
		$ciniki['request']['inline_javascript'] = "<script type='text/javascript'>\n"
			. "function gallery_resize_arrows() {"
				. "var i = document.getElementById('gallery-image-img');"
				. "var t = document.getElementById('entry-title');"
				. "var d = document.getElementById('gallery-image-details');"
				. "var w = document.getElementById('gallery-image-wrap');"
				// Detect IE
				. "try {"
					. "var bwidth = parseInt(getComputedStyle(w, null).getPropertyValue('border-right-width'), 10);"
					. "var mheight = parseInt(getComputedStyle(t, null).getPropertyValue('margin-bottom'), 10);"
				. "} catch(e) {"
					. "var bwidth = parseInt(w.currentStyle.borderWidth, 10);"
					. "var mheight = 20;"
				. "}"
				. "var cheight = (t.offsetHeight + i.offsetHeight);"
				. "var wheight = window.innerHeight;"
				. "if( cheight > wheight ) {"
					. "i.style.maxHeight = (wheight - t.offsetHeight - mheight - (bwidth*2)-20) + 'px';"
					. "i.style.width = 'auto';"
					. "}"
				. "var cwidth = i.offsetWidth;"
				. "var wwidth = document.getElementById('main-menu-container').offsetWidth;"
				. "if( cwidth > wwidth ) {"
					. "if( navigator.appName == 'Microsoft Internet Explorer') {"
						. "var ua = navigator.userAgent;"
						. "var re = new RegExp('MSIE ([0-9]{1,}[\.0-9]{0,})');"
						. "if (re.exec(ua) != null) {"
							. "rv = parseFloat(RegExp.$1); }"
						. "if( rv <= 8 ) {"
						. "w.style.maxWidth = (wwidth - (bwidth*2)) + 'px';"
						. "i.style.maxWidth = '100%';"
						. "i.style.height = 'auto';"
						. "}"
					. "} else {"
						. "i.style.maxWidth = (wwidth - (bwidth*2)) + 'px';"
						. "i.style.height = 'auto';"
					. "}"
				. "}"
				. "document.getElementById('gallery-image-prev').style.height = i.height + 'px';"
				. "document.getElementById('gallery-image-next').style.height = i.height + 'px';"
				. "document.getElementById('gallery-image-prev').style.width = (i.offsetLeft + (i.offsetWidth/2)) + 'px';"
				. "document.getElementById('gallery-image-next').style.width = ((i.offsetLeft-2)+100) + 'px';"
				. "document.getElementById('gallery-image-prev').style.left = '0px';"
				. "document.getElementById('gallery-image-next').style.left = (i.offsetLeft+i.width) + 'px';"
				. "var p = document.getElementById('gallery-image-prev-img');"
				. "p.style.left = (i.offsetLeft-21) + 'px';"
				. "p.style.top = ((i.height/2)-(p.offsetHeight/2)) + 'px';"
				. "var n = document.getElementById('gallery-image-next-img');"
				. "n.style.left = '1px';"
				. "n.style.top = ((i.height/2)-(p.offsetHeight/2)) + 'px';"
				. "var w = document.getElementById('gallery-image-wrap');"
				. "d.style.width = w.offsetWidth + 'px';"
				. "window.scrollTo(0, t.offsetTop - 10);"
			. "}\n"
			. "function scrollto_header() {"
				. "var e = document.getElementById('entry-title');"
				. "window.scrollTo(0, e.offsetTop - 10);"
			. "}\n"
			. "</script>\n";
		$ciniki['request']['onresize'] = "gallery_resize_arrows();";
		$ciniki['request']['onload'] = "scrollto_header();";
		$page_content .= "<div id='gallery-image' class='gallery-image'>";
		$page_content .= "<div id='gallery-image-wrap' class='gallery-image-wrap'>";
		if( $prev != null ) {
			$page_content .= "<a id='gallery-image-prev' class='gallery-image-prev' href='" . $prev['permalink'] . "'><div id='gallery-image-prev-img'></div></a>";
		}
		if( $next != null ) {
			$page_content .= "<a id='gallery-image-next' class='gallery-image-next' href='" . $next['permalink'] . "'><div id='gallery-image-next-img'></div></a>";
		}
		$page_content .= "<img id='gallery-image-img' title='" . $img['name'] . "' alt='" . $img['name'] . "' src='" . $rc['url'] . "' onload='javascript: gallery_resize_arrows();' />";
		$page_content .= "</div><br/>"
			. "<div id='gallery-image-details' class='gallery-image-details'>"
			. "<span class='image-title'>" . $img['name'] . "</span>"
			. "<span class='image-details'>";
		$comma = "";
		if( $img['size'] != '' ) { 
			$page_content .= $img['size'];
			$comma = ", ";
		}
		if( $img['framed_size'] != '' ) { 
			$page_content .= " (framed: " . $img['framed_size'] . ")";
			$comma = ", ";
		}
		if( $img['price'] != '' && $img['forsale'] == 'yes' ) { 
			$page_content .= $comma . preg_replace('/^\s*([^$])/', '\$$1', $img['price']);
			$comma = ", ";
		}
		if( $img['sold'] == 'yes' ) {
			$page_content .= " <b> SOLD</b>";
			$comma = ", ";
		}
		$page_content .= "</span>\n"
			. "";
		if( $img['awards'] != '' ) {
			require_once($ciniki['config']['core']['modules_dir'] . '/web/private/processContent.php');
			$rc = ciniki_web_processContent($ciniki, $img['awards']);	
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= "<span class='image-awards-title'>Awards</span>"
				. "<span class='image-awards'>" . $rc['content'] . "</span>"
				. "";
		}
		$page_content .= "</div></div>";
	
	} elseif( isset($ciniki['request']['uri_split'][0]) 
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
		$img_base_url = $ciniki['request']['base_url'] . "/gallery/category/" . $ciniki['request']['uri_split'][1];
		$rc = ciniki_web_generatePageGalleryThumbnails($ciniki, $settings, $img_base_url, $rc['images'], 125);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$page_content .= "<div class='image-gallery'>" . $rc['content'] . "</div>";

	} else {
		//
		// Get any user specified content for the gallery page
		//
		require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbDetailsQueryDash.php');
		$rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_web_content', 'business_id', $ciniki['request']['business_id'], 'web', 'content', 'page-gallery');
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
		// List the categories the user has created in the artcatalog, 
		// OR just show all the thumbnails if they haven't created any categories
		//
		require_once($ciniki['config']['core']['modules_dir'] . '/artcatalog/web/categories.php');
		$rc = ciniki_artcatalog_web_categories($ciniki, $settings, $ciniki['request']['business_id']);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( !isset($rc['categories']) ) {
			//
			// No categories specified, just show thumbnails of all artwork
			//
			$page_title = 'Gallery';
			require_once($ciniki['config']['core']['modules_dir'] . '/artcatalog/web/categoryImages.php');
			$rc = ciniki_artcatalog_web_categoryImages($ciniki, $settings, $ciniki['request']['business_id'], 'category', '');
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$images = $rc['images'];

			require_once($ciniki['config']['core']['modules_dir'] . '/web/private/generatePageGalleryThumbnails.php');
			$img_base_url = $ciniki['request']['base_url'] . "/gallery/image";
			$rc = ciniki_web_generatePageGalleryThumbnails($ciniki, $settings, $img_base_url, $rc['images'], 150, 0);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= "<div class='image-gallery'>" . $rc['content'] . "</div>";
		} else {
			$page_title = 'Galleries';
			$page_content .= "<div class='image-categories'>";
			foreach($rc['categories'] AS $cnum => $category) {
				$name = $category['category']['name'];
				require_once($ciniki['config']['core']['modules_dir'] . '/web/private/getScaledImageURL.php');
				$rc = ciniki_web_getScaledImageURL($ciniki, $category['category']['image_id'], 'thumbnail', '240', 0);
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				$page_content .= "<div class='image-categories-thumbnail'>"
					. "<a href='" . $ciniki['request']['base_url'] . "/gallery/category/" . urlencode($name) . "' "
						. "title='" . $name . "'>"
					. "<img title='$name' alt='$name' src='" . $rc['url'] . "' />"
					. "<span class='image-categories-name'>$name</span>"
					. "</a></div>";
			}
			$page_content .= "</div>";
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
		. "<header class='entry-title'><h1 id='entry-title' class='entry-title'>$page_title</h1></header>\n"
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
