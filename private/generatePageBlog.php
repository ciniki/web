<?php
//
// Description
// -----------
// This function will generate the gallery page for the website
//
// The blog URL's can consist of
// 		/blog/ - Display the latest blog entries
//		/blog/archive - Display the archive for the blog
// 		/blog/category/categoryname - Display the entries for the category
// 		/blog/tag/tagname - Display the entries for a tag
//		/blog/permalink - Display a blog entry
//		/blog/permalink/gallery/imagepermalink - Display a blog entry image gallery
//		/blog/permalink/download/filepermalink - Download a blog entry file
//
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_generatePageBlog($ciniki, $settings, $blogtype='blog') {

	//
	// Check if a file was specified to be downloaded
	//
	$download_err = '';
	if( isset($ciniki['business']['modules']['ciniki.blog'])
		&& isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] != ''
		&& isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] == 'download'
		&& isset($ciniki['request']['uri_split'][2]) && $ciniki['request']['uri_split'][2] != '' ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'blog', 'web', 'fileDownload');
		$rc = ciniki_blog_web_fileDownload($ciniki, $ciniki['request']['business_id'], 
			$ciniki['request']['uri_split'][0], $ciniki['request']['uri_split'][2], $blogtype);
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
		return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'1588', 'msg'=>'The file you requested does not exist.'));
	}

	//
	// Store the content created by the page
	//
	$page_content = '';
	$ciniki['response']['head']['og']['url'] = $ciniki['request']['domain_base_url'] . '/blog';

	//
	// FIXME: Check if anything has changed, and if not load from cache
	//
		
	
	$page_post_limit = 10;
	if( isset($ciniki['request']['args']['page']) && $ciniki['request']['args']['page'] != '' ) {
		$page_post_cur = $ciniki['request']['args']['page'];
	} else {
		$page_post_cur = 1;
	}
	$base_url = $ciniki['request']['base_url'] . "/" . $blogtype;
	$page_title = "Blog";
	if( $blogtype == 'memberblog' ) {
		if( isset($settings['page-memberblog-name']) && $settings['page-memberblog-name'] != '' ) {
			$page_title = $settings['page-memberblog-name'];
		} else {
			$page_title = "Member News";
		}
	} elseif( isset($settings['page-blog-name']) && $settings['page-blog-name'] != '' ) {
		$page_title = $settings['page-blog-name'];
	}
	if( isset($ciniki['business']['modules']['ciniki.blog']) ) {
		$pkg = 'ciniki';
		$mod = 'blog';
		$category_uri_component = 'blog';
	} else {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1589', 'msg'=>'No blog module enabled'));
	}

	//
	// Check if we are to display an image, from the gallery, or latest images
	//
	if( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] != '' 
		&& isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] == 'gallery' 
		&& isset($ciniki['request']['uri_split'][2]) && $ciniki['request']['uri_split'][2] != '' 
		) {
		$post_permalink = $ciniki['request']['uri_split'][0];
		$image_permalink = $ciniki['request']['uri_split'][2];

		//
		// Load the post to get all the details, and the list of images.
		// It's one query, and we can find the requested image, and figure out next
		// and prev from the list of images returned
		//
		ciniki_core_loadMethod($ciniki, 'ciniki', 'blog', 'web', 'postDetails');
		$rc = ciniki_blog_web_postDetails($ciniki, $settings, 
			$ciniki['request']['business_id'], array('permalink'=>$post_permalink, 'blogtype'=>$blogtype));
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$post = $rc['post'];

		if( !isset($post['images']) || count($post['images']) < 1 ) {
			return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'1590', 'msg'=>"We're sorry, but we could not find the image you requested."));
		}

		//
		// Setup sharing information
		//
		$ciniki['response']['head']['og']['url'] .= '/' . $post_permalink;
		if( isset($post['image_id']) && $post['image_id'] > 0 ) {
			// Check for the primary image in the post
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
			$rc = ciniki_web_getScaledImageURL($ciniki, $post['image_id'], 'original', '500', 0);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$ciniki['response']['head']['og']['image'] = $rc['domain_url'];
		}
		if( isset($post['excerpt']) && $post['excerpt'] != '' ) {
			$ciniki['response']['head']['og']['description'] = strip_tags($post['excerpt']);
		} elseif( isset($post['content']) && $post['content'] != '' ) {
			$ciniki['response']['head']['og']['description'] = strip_tags($post['content']);
		}
		
		$first = NULL;
		$last = NULL;
		$img = NULL;
		$next = NULL;
		$prev = NULL;
		foreach($post['images'] as $iid => $image) {
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
		if( $img == NULL ) {
			return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'2120', 'msg'=>"We're sorry, but we could not find the image you requested."));
		}

		if( count($post['images']) == 1 ) {
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
			$page_title = $post['title'] . ' - ' . $img['title'];
		} else {
			$page_title = $post['title'];
		}

	
		//
		// Load the image
		//
		if( $img['image_id'] > 0 ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
			$rc = ciniki_web_getScaledImageURL($ciniki, $img['image_id'], 'original', 0, 600);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$img_url = $rc['url'];
		} else {
			return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'2117', 'msg'=>"We're sorry, but we could not find the image you requested."));
		}

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
			. "<header class='entry-title'><h1 id='entry-title' class='entry-title'>"
			. "<a href='" . $ciniki['request']['base_url'] . "/$blogtype/" . $post['permalink'] . "'>$page_title</a>"
			. "</h1></header>\n"
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
	// Generate the category and tag listing page
	//
	elseif( isset($ciniki['request']['uri_split'][0]) 
		&& ($ciniki['request']['uri_split'][0] == 'category' || $ciniki['request']['uri_split'][0] == 'tag')
		&& isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] != '' ) {

		ciniki_core_loadMethod($ciniki, $pkg, $mod, 'web', 'posts');
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');

		//
		// Get the items for the specified category
		//
		if( $ciniki['request']['uri_split'][0] == 'category' ) {
			$rc = ciniki_blog_web_posts($ciniki, $settings, $ciniki['request']['business_id'], 
				array('category'=>urldecode($ciniki['request']['uri_split'][1]), 
					'offset'=>(($page_post_cur-1)*$page_post_limit), 'limit'=>$page_post_limit+1), $blogtype);
		} else {
			$rc = ciniki_blog_web_posts($ciniki, $settings, $ciniki['request']['business_id'], 
				array('tag'=>urldecode($ciniki['request']['uri_split'][1]), 
					'offset'=>(($page_post_cur-1)*$page_post_limit), 'limit'=>$page_post_limit+1), $blogtype);
		}
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$posts = $rc['posts'];
	
		//
		// Get the tag name
		//
		$tag_name = $ciniki['request']['uri_split'][1];
		foreach($posts as $post) {
			$tag_name = $post['tag_name'];
			break;
		}
		$page_title .= ' - ' . $tag_name;

		$page_content .= "<article class='page'>\n"
			. "<header class='entry-title'><h1 id='entry-title' class='entry-title'>$page_title</h1></header>\n"
			. "<div class='entry-content'>\n"
			. "";

		//
		// Generate list of posts
		//
		$nav_base_url = $ciniki['request']['base_url'] . "/$blogtype/" . $ciniki['request']['uri_split'][0] 
			. '/' . $ciniki['request']['uri_split'][1];
		if( count($posts) > 0 ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processCIList');
			$rc = ciniki_web_processCIList($ciniki, $settings, $base_url, $posts, 
				array('page'=>$page_post_cur, 'limit'=>$page_post_limit,
					'prev'=>'Newer Posts &rarr;',
					'next'=>'&larr; Older Posts',
					'base_url'=>$nav_base_url,
				));
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= $rc['content'];
			$nav_content = $rc['nav'];
		} else {
			$page_content .= "<p>Currently no posts.</p>";
		}
		$page_content .= "</article>";
		if( $nav_content != '' ) {
			$page_content .= $nav_content;
		}
		$page_content .= "</div>"
			. "";
	}

	//
	// Display list of categories or tags
	//
	elseif( isset($ciniki['request']['uri_split'][0]) 
		&& ($ciniki['request']['uri_split'][0] == 'categories' || $ciniki['request']['uri_split'][0] == 'tags') 
		) {

		ciniki_core_loadMethod($ciniki, $pkg, $mod, 'web', 'tagCloud');
		if( $ciniki['request']['uri_split'][0] == 'categories' ) {
			$page_title .= ' - Categories';
			$base_url = $ciniki['request']['base_url'] . "/$blogtype/category";
			$rc = ciniki_blog_web_tagCloud($ciniki, $settings, $ciniki['request']['business_id'], 10, $blogtype);
		} elseif( $ciniki['request']['uri_split'][0] == 'tags' ) {
			$page_title .= ' - Tags';
			$base_url = $ciniki['request']['base_url'] . "/$blogtype/tag";
			$rc = ciniki_blog_web_tagCloud($ciniki, $settings, $ciniki['request']['business_id'], 20, $blogtype);
		}
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}

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
			if( $ciniki['request']['uri_split'][0] == 'categories' ) {
				$page_content = "<p>I'm sorry, there are no categories for this blog";
			} elseif( $ciniki['request']['uri_split'][0] == 'tags' ) {
				$page_content = "<p>I'm sorry, there are no tags for this blog";
			}
		}
	}

	//
	// Display the archive of month posts
	//
	elseif( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] == 'archive'
		&& isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] != '' ) {

		ciniki_core_loadMethod($ciniki, $pkg, $mod, 'web', 'posts');
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');

		$year = $ciniki['request']['uri_split'][1];
		if( isset($ciniki['request']['uri_split'][2]) && $ciniki['request']['uri_split'][2] != '' ) {
			$month = $ciniki['request']['uri_split'][2];
			$page_title .= ' - ' . date_format(date_create($year . '-' . $month . '-01'), 'M Y');
			$nav_base_url = $ciniki['request']['base_url'] . "/$blogtype/" . $ciniki['request']['uri_split'][0] 
				. '/' . $ciniki['request']['uri_split'][1] . '/' . $ciniki['request']['uri_split'][2];
		} else {
			$month = '';
			$page_title .= ' - ' . $year;
			$nav_base_url = $ciniki['request']['base_url'] . "/$blogtype/" . $ciniki['request']['uri_split'][0] 
				. '/' . $ciniki['request']['uri_split'][1];
		}

		$rc = ciniki_blog_web_posts($ciniki, $settings, $ciniki['request']['business_id'], 
			array('year'=>$year, 'month'=>$month, 
				'offset'=>(($page_post_cur-1)*$page_post_limit), 'limit'=>$page_post_limit+1), $blogtype);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$posts = $rc['posts'];
	
		$page_content .= "<article class='page'>\n"
			. "<header class='entry-title'><h1 id='entry-title' class='entry-title'>$page_title</h1></header>\n"
			. "<div class='entry-content'>\n"
			. "";

		//
		// Generate list of posts
		//
		if( count($posts) > 0 ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processCIList');
			$rc = ciniki_web_processCIList($ciniki, $settings, $base_url, $posts, 
				array('page'=>$page_post_cur, 'limit'=>$page_post_limit,
					'prev'=>'Newer Posts &rarr;',
					'next'=>'&larr; Older Posts',
					'base_url'=>$nav_base_url,
				));
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= $rc['content'];
			$nav_content = $rc['nav'];
		} else {
			$page_content .= "<p>Currently no posts.</p>";
		}
		$page_content .= "</article>";
		if( isset($nav_content) && $nav_content != '' ) {
			$page_content .= $nav_content;
		}
		$page_content .= "</div>"
			. "";
	}

	//
	// Display the archive of posts
	//
	elseif( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] == 'archive' ) {
		ciniki_core_loadMethod($ciniki, $pkg, $mod, 'web', 'archive');
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');

		$rc = ciniki_blog_web_archive($ciniki, $settings, $ciniki['request']['business_id'], $blogtype);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$prev_year = '';
		$years = '';
		$months = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
		foreach($rc['archive'] as $m) {
			$year = $m['year'];
			$month_txt = $months[$m['month']-1];
			$month = sprintf("%02d", $m['month']);
			if( $year != $prev_year ) {
				if( $prev_year != '' ) { $years .= "</dd>"; }
				$years .= "<dt>$year</dt><dd>";
				$cm = '';
			}
			$years .= $cm . "<a href='" . $ciniki['request']['base_url'] . "/$blogtype/archive/$year/$month'>"
				. "$month_txt</a>&nbsp;(" . $m['num_posts'] . ")";
			$cm = ', ';
			$prev_year = $year;
		}

		$page_title .= ' - Archive';

		$page_content .= "<article class='page'>\n"
			. "<header class='entry-title'><h1 id='entry-title' class='entry-title'>$page_title</h1></header>\n"
			. "<div class='entry-content'>\n"
			. "";

		if( $years != '' ) {
			$page_content .= "<dl class='wide'>$years</dl>";
		} else {
			$page_content .= "<p>Currently no posts.</p>";
		}

		$page_content .= "</article>"
			. "</div>"
			. "";
	}

	//
	// Display the page of the post details
	//
	elseif( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] != '' ) {

		ciniki_core_loadMethod($ciniki, 'ciniki', 'blog', 'web', 'postDetails');
		//
		// Get the post information
		//
		$post_permalink = $ciniki['request']['uri_split'][0];
		$ciniki['response']['head']['og']['url'] .= '/' . $post_permalink;
		$rc = ciniki_blog_web_postDetails($ciniki, $settings, 
			$ciniki['request']['business_id'], array('permalink'=>$post_permalink, 'blogtype'=>$blogtype));
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$post = $rc['post'];
		$page_title = $post['title'];

		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processBlogPost');
		$rc = ciniki_web_processBlogPost($ciniki, $settings, $post, 
			array('blogtype'=>$blogtype, 'output'=>'web'));
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$page_content .= $rc['content'];
	}

	//
	// Generate the main posts page, showing the main categories
	//
	else {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash');
		$rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_web_content', 'business_id', $ciniki['request']['business_id'], 'ciniki.web', 'content', 'page-blog');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}

		$page_content .= "<article class='page'>\n"
			. "<header class='entry-title'><h1 id='entry-title' class='entry-title'>$page_title</h1></header>\n"
			. "<div class='entry-content'>\n"
			. "";

		if( isset($rc['content']['page-blog-content']) ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
			$rc = ciniki_web_processContent($ciniki, $rc['content']['page-blog-content']);	
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= $rc['content'];
		}

		//
		// List the categories the user has created in the artcatalog, 
		// OR just show all the thumbnails if they haven't created any categories
		//
		ciniki_core_loadMethod($ciniki, $pkg, $mod, 'web', 'posts');
		$rc = ciniki_blog_web_posts($ciniki, $settings, $ciniki['request']['business_id'], 
			array('offset'=>(($page_post_cur-1)*$page_post_limit), 'limit'=>$page_post_limit+1), $blogtype);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( isset($settings['page-blog-name']) && $settings['page-blog-name'] != '' ) {
			$page_title = $settings['page-blog-name'];
		} else {
			$page_title = 'Blog';
		}
		if( !isset($rc['posts']) || count($rc['posts']) < 1 ) {
			$page_content .= "<p>Currently no posts.</p>";
		} else {
			$posts = $rc['posts'];
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processCIList');
			$base_url = $ciniki['request']['base_url'] . "/$blogtype";
			$rc = ciniki_web_processCIList($ciniki, $settings, $base_url, $posts, 
				array('page'=>$page_post_cur, 'limit'=>$page_post_limit,
					'prev'=>'Newer Posts &rarr;',
					'next'=>'&larr; Older Posts',
					'base_url'=>$ciniki['request']['base_url'] . "/$blogtype",
				));
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= $rc['content'];
			$nav_content = $rc['nav'];
		}
		$page_content .= "</article>";
		if( isset($nav_content) && $nav_content != '' ) {
			$page_content .= $nav_content;
		}
	}

	$content = '';

	//
	// The submenu 
	//
	$submenu = array();
	$submenu['latest'] = array('name'=>'Latest', 'url'=>$ciniki['request']['base_url'] . "/$blogtype");
	$submenu['archive'] = array('name'=>'Archive', 'url'=>$ciniki['request']['base_url'] . "/$blogtype/archive");
	if( ($ciniki['business']['modules']['ciniki.blog']['flags']&0x02) > 0 ) {
		$submenu['category'] = array('name'=>'Categories', 'url'=>$ciniki['request']['base_url'] . "/$blogtype/categories");
	}
	if( ($ciniki['business']['modules']['ciniki.blog']['flags']&0x04) > 0 ) {
		$submenu['tag'] = array('name'=>'Tags', 'url'=>$ciniki['request']['base_url'] . "/$blogtype/tags");
	}

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
