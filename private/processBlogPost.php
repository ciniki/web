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
function ciniki_web_processBlogPost(&$ciniki, $settings, $post, $args) {

	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processURL');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');

	$content = '';
	$plaintxt = '';

	//
	// Setup the base_url based on
	$base_url = ((isset($args['output']) && $args['output']=='email')?$ciniki['request']['domain_base_url']:$ciniki['request']['base_url']);

	if( isset($args['output']) && $args['output'] == 'web' ) {
		$content .= "<article class='page'>\n"
			. "<header class='entry-title'><h1 class='entry-title'>" . $post['title'] . "</h1>"
			. "";
		$meta_content = '';
		$meta_content .= 'Published: <time datetime="' . $post['publish_datetime'] . '" pubdate="pubdate">' . $post['publish_date'] . '</time>';
		if( $meta_content != '' ) {
			$content .= "<div class='entry-meta'>" . $meta_content . "</div>";
		}
		$content .= "</header>\n"
			. "";
	}

	//
	// Add primary image
	//
	if( isset($post['image_id']) && $post['image_id'] > 0 ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
		$rc = ciniki_web_getScaledImageURL($ciniki, $post['image_id'], 'original', '500', 0);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$ciniki['response']['head']['og']['image'] = $rc['domain_url'];
		$content .= "<aside><div class='image-wrap'><div class='image'>"
			. "<img title='' alt='" . $post['title'] . "' src='" . $rc['url'] . "' />"
			. "</div></div></aside>";
	}

	if( isset($post['excerpt']) && $post['excerpt'] != '' ) {
		$ciniki['response']['head']['og']['description'] = strip_tags($post['excerpt']);
	} elseif( isset($post['content']) && $post['content'] != '' ) {
		$ciniki['response']['head']['og']['description'] = strip_tags($post['content']);
	}
	
	//
	// Add description
	//
	if( isset($post['content']) && $post['content'] != '' ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
		$rc = ciniki_web_processContent($ciniki, $post['content']);	
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$content .= $rc['content'];
		if( $args['output'] == 'email' ) {
			$plaintxt .= strip_tags($post['content']);
		}
	}

	//
	// Display the files for the posts
	//
	if( isset($post['files']) && count($post['files']) > 0 ) {
		$content .= "<p>";
		foreach($post['files'] as $file) {
			$url = $base_url . "/" . $args['blogtype'] . "/" . $post['permalink'] . '/download/' . $file['permalink'] . '.' . $file['extension'];
//				$content .= "<span class='downloads-title'>";
			if( $url != '' ) {
				$content .= "<a target='_blank' href='" . $url . "' title='" . $file['name'] . "'>" . $file['name'] . "</a>";
			} else {
				$content .= $file['name'];
			}
//				$content .= "</span>";
			if( isset($file['description']) && $file['description'] != '' ) {
				$content .= "<br/><span class='downloads-description'>" . $file['description'] . "</span>";
			}
			$content .= "<br/>";
		}
		$content .= "</p>";
	}

	//
	// Display the links for the posts
	//
	if( isset($post['links']) && count($post['links']) > 0 ) {
		$content .= "<p>";
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processURL');
		foreach($post['links'] as $link) {
			$rc = ciniki_web_processURL($ciniki, $link['url']);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			if( $rc['url'] != '' ) {
				$content .= "<a target='_blank' href='" . $rc['url'] . "' title='" 
					. ($link['name']!=''?$link['name']:$rc['display']) . "'>" 
					. ($link['name']!=''?$link['name']:$rc['display'])
					. "</a>";
			} else {
				$content .= $link['name'];
			}
			if( isset($link['description']) && $link['description'] != '' ) {
				$content .= "<br/><span class='downloads-description'>" . $link['description'] . "</span>";
			}
			$content .= "<br/>";
		}
		$content .= "</p>";
	}

	//
	// Display the categories and tags for the blog post
	//
	$meta_content = '';
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processTagList');
	if( isset($post['categories']) && count($post['categories']) > 0 ) {
		$rc = ciniki_web_processTagList($ciniki, $settings, 
			$base_url . "/" . $args['blogtype'] . "/category", $post['categories'], array('delimiter'=>', '));
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( isset($rc['content']) && $rc['content'] != '' ) {
			$meta_content .= 'Filed under: ' . $rc['content'];
		}
	}
	if( isset($post['tags']) && count($post['tags']) > 0 ) {
		$rc = ciniki_web_processTagList($ciniki, $settings,
			$base_url . "/" . $args['blogtype'] . "/tag", $post['tags'], array('delimiter'=>', '));
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( isset($rc['content']) && $rc['content'] != '' ) {
			$meta_content .= ($meta_content!=''?'<br/>':'') . 'Tags: ' . $rc['content'];
		}
	}
	if( $meta_content != '' ) {
		$content .= '<p class="entry-meta">' . $meta_content . '</p>';
	}

	//
	// Check if share buttons should be shown
	//
	if( (!isset($settings['page-blog-share-buttons']) || $settings['page-blog-share-buttons'] == 'yes') 
		&& $args['blogtype'] == 'blog'
		&& $args['output'] == 'web'
		) {
		$tags = array();
		if( isset($post['categories']) ) {
			foreach($post['categories'] as $cat) {
				$tags[] = $cat['name'];
			}
		}
		if( isset($post['tags']) ) {
			foreach($post['tags'] as $tag) {
				$tags[] = $tag['name'];
			}
		}
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processShareButtons');
		$rc = ciniki_web_processShareButtons($ciniki, $settings, array(
			'title'=>$post['title'],
			'tags'=>$tags,
			));
		if( $rc['stat'] == 'ok' ) {
			$content .= $rc['content'];
		}
	}


	//
	// End of the main article content
	//
	$content .= "<br style='clear:both'/>";
//	$content .= "</article>";

	//
	// Display the additional images for the post
	//
	if( isset($post['images']) && count($post['images']) > 0 ) {
//		$content .= "<article class='page'>"	
//			. "<header class='entry-title'><h2 class='entry-title'>Gallery</h2></header>\n"
//			. "";
		$content .= "<h2 class='entry-title'>Gallery</h2>";
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageGalleryThumbnails');
		$img_base_url = $base_url . "/" . $args['blogtype'] . "/" . $post['permalink'] . "/gallery";
		$rc = ciniki_web_generatePageGalleryThumbnails($ciniki, $settings, $img_base_url, $post['images'], 125);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$content .= "<div class='image-gallery'>" . $rc['content'] . "</div>";
//		$content .= "</article>";
	}

	//
	// Display the products linked to this blog post
	//
	if( isset($post['products']) && count($post['products']) > 0 ) {
//		$content .= "<article class='page'>"
//			. "<header class='entry-title'><h2 class='entry-title'>Products</h2></header>\n"
//			. "";
		$content .= "<h2 class='entry-title'>Products</h2>";
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processCIList');
		$rc = ciniki_web_processCIList($ciniki, $settings, $base_url . "/products/p", array('0'=>array(
			'name'=>'', 'noimage'=>'/ciniki-web-layouts/default/img/noimage_240.png',
			'list'=>$post['products'])), array());
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
//		$content .= "<div class='entry-content'>" . $rc['content'] . "</div>";
		$content .= $rc['content'];
//		$content .= "</article>";
	}

	//
	// Display the recipes
	//
	if( isset($post['recipes']) && count($post['recipes']) > 0 ) {
//		$content .= "<article class='page'>"
//			. "<header class='entry-title'><h2 class='entry-title'>Recipes</h2></header>\n"
//			. "";
		$content .= "<h2 class='entry-title'>Recipes</h2>";
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processCIList');
		$rc = ciniki_web_processCIList($ciniki, $settings, $base_url . "/recipes/i", array('0'=>array(
			'name'=>'', 'noimage'=>'/ciniki-web-layouts/default/img/noimage_240.png',
			'list'=>$post['recipes'])), array());
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$content .= "<div class='entry-content'>" . $rc['content'] . "</div>";
//		$content .= "</article>";
	}

	$content .= "</article>";

	if( $args['output'] == 'email' ) {
		return array('stat'=>'ok', 'content'=>$content, 'text_content'=>$plaintxt);
	}

	return array('stat'=>'ok', 'content'=>$content);
}
?>
