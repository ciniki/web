<?php
//
// Description
// -----------
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_generatePageWebCollections(&$ciniki, $settings) {

	//
	// Store the content created by the page
	// Make sure everything gets generated ok before returning the content
	//
	$content = '';
	$page_title = '';
	$page_content = '';
	
	//
	// Setup facebook content defaults
	//
	$ciniki['response']['head']['og']['title'] = $ciniki['business']['details']['name'] . '';
	$ciniki['response']['head']['og']['url'] = $ciniki['request']['domain_base_url'];

//	$content = "<pre>" . print_r($ciniki, true) . "</pre>";

	//
	// FIXME: Check if anything has changed, and if not load from cache
	//


	//
	// Check if requested a specific collection of one object
	//
	if( isset($ciniki['request']['uri_split'][0])
		&& $ciniki['request']['uri_split'][0] != '' 
		&& isset($ciniki['request']['uri_split'][1])
		&& $ciniki['request']['uri_split'][1] != '' 
		) {
		$collection_permalink = $ciniki['request']['uri_split'][0];
		$collection_mod = $ciniki['request']['uri_split'][1];

		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'collectionDetails');
		$rc = ciniki_web_collectionDetails($ciniki, $ciniki['request']['business_id'], $collection_permalink);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$collection = $rc['collection'];
		$page_title = $collection['name'];

		if( isset($ciniki['business']['modules']['ciniki.blog']) && $collection_mod == 'blog' ) {
			if( isset($collection['objects']['ciniki.blog.post']) ) {
				$cobj = $collection['objects']['ciniki.blog.post'];
			}
			$page_post_limit = 10;
			if( isset($ciniki['request']['args']['page']) && $ciniki['request']['args']['page'] != '' ) {
				$page_post_cur = $ciniki['request']['args']['page'];
			} else {
				$page_post_cur = 1;
			}
			// Load the list entries
			ciniki_core_loadMethod($ciniki, 'ciniki', 'blog', 'web', 'webCollectionList');
			$rc = ciniki_blog_web_webCollectionList($ciniki, $settings, $ciniki['request']['business_id'], 
				array('collection_id'=>$collection['id'],
					'offset'=>(($page_post_cur-1)*$page_post_limit), 'limit'=>$page_post_limit+1), '');
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$block_content = '';
			if( isset($rc['posts']) && count($rc['posts']) > 0 ) {
				$posts = $rc['posts'];
				$nav_base_url = $ciniki['request']['base_url'] . '/collection/' . $collection_permalink . '/' . $collection_mod;
				ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processCIList');
				$rc = ciniki_web_processCIList($ciniki, $settings, $ciniki['request']['base_url'], $posts, 
					array('page'=>$page_post_cur, 'limit'=>$page_post_limit,
						'prev'=>'Newer Posts &rarr;',
						'next'=>'&larr; Older Posts',
						'base_url'=>$nav_base_url));
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				$num_posts = $rc['count'];
				$block_content .= "<article class='page'>\n"
					. "<header class='entry-title'><h1 class='entry-title'>"
					. ((isset($cobj['title']) && $cobj['title'] != '')?$cobj['title']:'Latest Blog Posts')
					. "</h1></header>\n"
					. $rc['content']
					. "";
				if( isset($rc['nav']) && $rc['nav'] != '' ) {
					$block_content .= $rc['nav'];
				}
				$block_content .= "</article>\n";
			}
			$page_content .= $block_content;
		} 
		
		elseif( isset($ciniki['business']['modules']['ciniki.events']) && $collection_mod == 'events' ) {
			if( isset($collection['objects']['ciniki.events.event']) ) {
				$cobj = $collection['objects']['ciniki.events.event'];
			}
			//
			// Load and parse the upcoming events
			//
			ciniki_core_loadMethod($ciniki, 'ciniki', 'events', 'web', 'webCollectionList');
			$rc = ciniki_events_web_webCollectionList($ciniki, $settings, $ciniki['request']['business_id'], 
				array('type'=>'upcoming', 'collection_id'=>$collection['id']));
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$block_content = '';
			if( isset($rc['events']) && count($rc['events']) > 0 ) {
				$events = $rc['events'];
				ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processEvents');
				$rc = ciniki_web_processEvents($ciniki, $settings, $events, 0);
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				$block_content .= "<article class='page page-home'>\n"
					. "<header class='entry-title'><h1 class='entry-title'>Upcoming "
					. ((isset($cobj['title']) && $cobj['title'] != '')?$cobj['title']:'Events')
					. "</h1></header>\n"
					. "<div class='entry-content'>"
					. $rc['content']
					. "</div>"
					. "";
				$block_content .= "</article>\n";
			}

			if( isset($settings['page-events-past']) && $settings['page-events-past'] == 'yes' ) {
				//
				// Load and parse the past events
				//
				ciniki_core_loadMethod($ciniki, 'ciniki', 'events', 'web', 'webCollectionList');
				$rc = ciniki_events_web_webCollectionList($ciniki, $settings, $ciniki['request']['business_id'], 
					array('type'=>'past', 'collection_id'=>$collection['id'], 'limit'=>25));
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				if( isset($rc['events']) && count($rc['events']) > 0 ) {
					$events = $rc['events'];
					ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processEvents');
					$rc = ciniki_web_processEvents($ciniki, $settings, $events, 0);
					if( $rc['stat'] != 'ok' ) {
						return $rc;
					}
					$block_content .= "<article class='page page-home'>\n"
						. "<header class='entry-title'><h1 class='entry-title'>Past "
						. ((isset($cobj['title']) && $cobj['title'] != '')?$cobj['title2']:'Events')
						. "</h1></header>\n"
						. "<div class='entry-content'>"
						. $rc['content']
						. "</div>"
						. "";
					$block_content .= "</article>\n";
				}
			}
			$page_content .= $block_content;
		} 
		
		elseif( isset($ciniki['business']['modules']['ciniki.workshops']) && $collection_mod == 'workshops' ) {
			if( isset($collection['objects']['ciniki.workshops.workshop']) ) {
				$cobj = $collection['objects']['ciniki.workshops.workshop'];
			}
			//
			// Load and parse the upcoming workshops
			//
			ciniki_core_loadMethod($ciniki, 'ciniki', 'workshops', 'web', 'webCollectionList');
			$rc = ciniki_workshops_web_webCollectionList($ciniki, $settings, $ciniki['request']['business_id'], 
				array('type'=>'upcoming', 'collection_id'=>$collection['id']));
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$block_content = '';
			if( isset($rc['workshops']) && count($rc['workshops']) > 0 ) {
				$workshops = $rc['workshops'];
				ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processWorkshops');
				$rc = ciniki_web_processWorkshops($ciniki, $settings, $workshops, 0);
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				$block_content .= "<article class='page page-home'>\n"
					. "<header class='entry-title'><h1 class='entry-title'>Upcoming "
					. ((isset($cobj['title']) && $cobj['title'] != '')?$cobj['title']:'Workshops')
					. "</h1></header>\n"
					. "<div class='entry-content'>"
					. $rc['content']
					. "</div>"
					. "";
				$block_content .= "</article>\n";
			}

			if( isset($settings['page-workshops-past']) && $settings['page-workshops-past'] == 'yes' ) {
				//
				// Load and parse the past workshops
				//
				ciniki_core_loadMethod($ciniki, 'ciniki', 'workshops', 'web', 'webCollectionList');
				$rc = ciniki_workshops_web_webCollectionList($ciniki, $settings, $ciniki['request']['business_id'], 
					array('type'=>'past', 'collection_id'=>$collection['id'], 'limit'=>25));
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				if( isset($rc['workshops']) && count($rc['workshops']) > 0 ) {
					$workshops = $rc['workshops'];
					ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processWorkshops');
					$rc = ciniki_web_processWorkshops($ciniki, $settings, $workshops, 0);
					if( $rc['stat'] != 'ok' ) {
						return $rc;
					}
					$block_content .= "<article class='page page-home'>\n"
						. "<header class='entry-title'><h1 class='entry-title'>Past "
						. ((isset($cobj['title']) && $cobj['title'] != '')?$cobj['title2']:'Workshops')
						. "</h1></header>\n"
						. "<div class='entry-content'>"
						. $rc['content']
						. "</div>"
						. "";
					$block_content .= "</article>\n";
				}
			}

			$page_content .= $block_content;
		} 
		
		elseif( isset($ciniki['business']['modules']['ciniki.artgallery']) && $collection_mod == 'exhibitions' ) {
			if( isset($collection['objects']['ciniki.artgallery.exhibition']) ) {
				$cobj = $collection['objects']['ciniki.artgallery.exhibition'];
			}
			//
			// Load and parse the upcoming exhibitions
			//
			ciniki_core_loadMethod($ciniki, 'ciniki', 'artgallery', 'web', 'webCollectionList');
			$rc = ciniki_artgallery_web_webCollectionList($ciniki, $settings, $ciniki['request']['business_id'], 
				array('type'=>'upcoming', 'collection_id'=>$collection['id']));
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$block_content = '';
			if( isset($rc['exhibitions']) && count($rc['exhibitions']) > 0 ) {
				$exhibitions = $rc['exhibitions'];
				ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processExhibitions');
				$rc = ciniki_web_processExhibitions($ciniki, $settings, $exhibitions, array('limit'=>0));
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				$block_content .= "<article class='page page-home'>\n"
					. "<header class='entry-title'><h1 class='entry-title'>Upcoming "
					. ((isset($cobj['title']) && $cobj['title'] != '')?$cobj['title']:'Exhibitions')
					. "</h1></header>\n"
					. "<div class='entry-content'>"
					. $rc['content']
					. "</div>"
					. "";
				$block_content .= "</article>\n";
			}

			if( isset($settings['page-artgalleryexhibitions-past']) && $settings['page-artgalleryexhibitions-past'] == 'yes' ) {
				//
				// Load and parse the past exhibitions
				//
				ciniki_core_loadMethod($ciniki, 'ciniki', 'exhibitions', 'web', 'webCollectionList');
				$rc = ciniki_artgallery_web_webCollectionList($ciniki, $settings, $ciniki['request']['business_id'], 
					array('type'=>'past', 'collection_id'=>$collection['id'], 'limit'=>25));
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				if( isset($rc['exhibitions']) && count($rc['exhibitions']) > 0 ) {
					$exhibitions = $rc['exhibitions'];
					ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processExhibitions');
					$rc = ciniki_web_processExhibitions($ciniki, $settings, $exhibitions, array('limit'=>0));
					if( $rc['stat'] != 'ok' ) {
						return $rc;
					}
					$block_content .= "<article class='page page-home'>\n"
						. "<header class='entry-title'><h1 class='entry-title'>Past "
						. ((isset($cobj['title']) && $cobj['title'] != '')?$cobj['title2']:'Exhibitions')
						. "</h1></header>\n"
						. "<div class='entry-content'>"
						. $rc['content']
						. "</div>"
						. "";
					$block_content .= "</article>\n";
				}
			}

			$page_content .= $block_content;
		} 
		else {
			return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'2066', 'msg'=>"There are no items for this collection."));
		}
	}

	//
	// Show the collection for all objects
	//
	elseif( isset($ciniki['request']['uri_split'][0])
		&& $ciniki['request']['uri_split'][0] != '' ) {
		$collection_permalink = $ciniki['request']['uri_split'][0];

		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'collectionDetails');
		$rc = ciniki_web_collectionDetails($ciniki, $ciniki['request']['business_id'], $collection_permalink);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$collection = $rc['collection'];
		$more_base_url = $ciniki['request']['base_url'] . '/collection/' . $collection_permalink;
		$page_title = $collection['name'];

		// Setup description for facebook
		if( isset($collection['synopsis']) && $collection['synopsis'] != '' ) {
			$ciniki['response']['head']['og']['description'] = strip_tags($collection['synopsis']);
		} elseif( isset($collection['description']) ) {
			$ciniki['response']['head']['og']['description'] = strip_tags($collection['description']);
		}

		$description = '';
		if( isset($collection['description']) && $collection['description'] != '' ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
			$rc = ciniki_web_processContent($ciniki, $collection['description']);	
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$description = $rc['content'];
		}
		$image = '';
		if( isset($collection['image_id']) && $collection['image_id'] > 0 ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
			$rc = ciniki_web_getScaledImageURL($ciniki, $collection['image_id'], 'original', '500', 0);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			if( $description == '' ) {
				//
				// If no content or collection list, then center the image
				//
				$image .= "<div class='wide aligncenter'>";
				$image .= "<div class='image'><img title='' alt='" . $collection['name'] . "' src='" . $rc['url'] . "' /></div>";
				$image .= "</div>";
				if( isset($collection['image_caption']) && $collection['image_caption'] != '' ) {
					$image .= "<div class='image-caption aligncenter'>" . $collection['image_caption'] . "</div>";
				}
			} else {
				$image .= "<aside><div class='image-wrap'>"
					. "<div class='image'><img title='' alt='" . $collection['name'] . "' src='" . $rc['url'] . "' /></div>";
				if( $ciniki['response']['head']['og']['image'] == '' ) {
					$ciniki['response']['head']['og']['image'] = $rc['domain_url'];
				}
				if( isset($collection['image_caption']) && $collection['image_caption'] != '' ) {
					$image .= "<div class='image-caption'>" . $collection['image_caption'] . "</div>";
				}
				$image .= "</div></aside>";
			}
		}
		if( $image != '' || $description != '' ) {
			$page_content .= "<article class='page page-home'>\n"
				. "<header><h1>" . $collection['name'] . "</h1></header>"
				. "<div class='entry-content'>\n"
				. $image . $description
				. "</div>"
				. "<br style='clear:both;'/>"
				. "</article>"
				. "";
		}

		//
		// Build the different blocks that will appear on the site, but don't decide
		// how they will be arranged until done building them all
		//
		$blocks = array();

		//
		// Load the blog entries, if there are any attached to this collection.
		//
		if( isset($ciniki['business']['modules']['ciniki.blog']) 
			&& isset($collection['objects']['ciniki.blog.post'])
			) {
			// Determine number to display
			$cobj = $collection['objects']['ciniki.blog.post'];
			$list_size = 2;
			if( isset($cobj['num_display_items']) && $cobj['num_display_items'] > 0 ) {
				$list_size = $cobj['num_display_items'];
			}
			// Load the list entries
			ciniki_core_loadMethod($ciniki, 'ciniki', 'blog', 'web', 'webCollectionList');
			$rc = ciniki_blog_web_webCollectionList($ciniki, $settings, $ciniki['request']['business_id'], 
				array('collection_id'=>$collection['id'], 'limit'=>$list_size+1), '');
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$block_content = '';
			if( isset($rc['posts']) && count($rc['posts']) > 0 ) {
				$posts = $rc['posts'];
				$base_url = $ciniki['request']['base_url'] . "/blog";
				ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processCIList');
				$rc = ciniki_web_processCIList($ciniki, $settings, $base_url, $posts, 
					array('limit'=>$list_size, 'base_url'=>$ciniki['request']['base_url'] . "/blog"));
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				$num_posts = $rc['count'];
				$block_content .= "<article class='page'>\n"
					. "<header class='entry-title'><h1 class='entry-title'>"
					. ((isset($cobj['title']) && $cobj['title'] != '')?$cobj['title']:'Latest Blog Posts')
					. "</h1></header>\n"
					. $rc['content']
					. "";
				if( $num_posts > $list_size ) {
					$block_content .= "<div class='cilist-more'>"
						. "<a href='" . $more_base_url . "/blog'>"
						. ((isset($cobj['more']) && $cobj['more'] != '')?$cobj['more']:'... more blog posts')
						. "</a></div>";
				}
				$block_content .= "</article>\n";
			}
			if( $block_content != '' ) {
				$blocks['ciniki.blog.post'] = array('content'=>$block_content, 'display'=>'no');
			}
		}

		//
		// Load the events
		//
		if( isset($ciniki['business']['modules']['ciniki.events']) 
			&& isset($collection['objects']['ciniki.events.event'])
			) {
			$cobj = $collection['objects']['ciniki.events.event'];
			$list_size = 2;
			if( isset($cobj['num_display_items']) && $cobj['num_display_items'] > 0 ) {
				$list_size = $cobj['num_display_items'];
			}
			//
			// Load and parse the events
			//
			ciniki_core_loadMethod($ciniki, 'ciniki', 'events', 'web', 'webCollectionList');
			$rc = ciniki_events_web_webCollectionList($ciniki, $settings, $ciniki['request']['business_id'], 
				array('type'=>'upcoming', 'collection_id'=>$collection['id'], 'limit'=>$list_size+1));
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$block_content = '';
			if( isset($rc['events']) && count($rc['events']) > 0 ) {
				$events = $rc['events'];
				ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processEvents');
				$rc = ciniki_web_processEvents($ciniki, $settings, $events, $list_size);
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				$block_content .= "<article class='page page-home'>\n"
					. "<header class='entry-title'><h1 class='entry-title'>Upcoming "
					. ((isset($cobj['title']) && $cobj['title'] != '')?$cobj['title']:'Events')
					. "</h1></header>\n"
					. "<div class='entry-content'>"
					. $rc['content']
					. "</div>"
					. "";
				if( count($events) > $list_size 
					|| (isset($settings['page-events-past']) && $settings['page-events-past'] == 'yes'
						&& $cobj['num_items'] > count($events))
					) {
					$block_content .= "<div class='cilist-more'>"
						. "<a href='" . $more_base_url . "/events'>"
						. ((isset($cobj['more']) && $cobj['more'] != '')?$cobj['more']:'... more events')
						. "</a></div>";
				}
				$block_content .= "</article>\n";
			}
			if( $block_content != '' ) {
				$blocks['ciniki.events.event'] = array('content'=>$block_content, 'display'=>'no');
			}
		}

		//
		// Load the exhibitions
		//
		if( isset($ciniki['business']['modules']['ciniki.artgallery']) 
			&& isset($collection['objects']['ciniki.artgallery.exhibition'])
			) {
			$cobj = $collection['objects']['ciniki.artgallery.exhibition'];
			$list_size = 2;
			if( isset($cobj['num_display_items']) && $cobj['num_display_items'] > 0 ) {
				$list_size = $cobj['num_display_items'];
			}
			//
			// Load and parse the exhibitions
			//
			ciniki_core_loadMethod($ciniki, 'ciniki', 'artgallery', 'web', 'webCollectionList');
			$rc = ciniki_artgallery_web_webCollectionList($ciniki, $settings, $ciniki['request']['business_id'], 
				array('collection_id'=>$collection['id'], 'type'=>'upcoming', 'limit'=>$list_size+1));
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$block_content = '';
			if( isset($rc['exhibitions']) && count($rc['exhibitions']) > 0 ) {
				$exhibitions = $rc['exhibitions'];
				ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processExhibitions');
				$rc = ciniki_web_processExhibitions($ciniki, $settings, $exhibitions, array('limit'=>$list_size));
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				$block_content .= "<article class='page page-home'>\n"
					. "<header class='entry-title'><h1 class='entry-title'>Upcoming "
					. ((isset($cobj['title']) && $cobj['title'] != '')?$cobj['title']:'Exhibitions')
					. "</h1></header>\n"
					. "<div class='entry-content'>"
					. $rc['content']
					. "</div>"
					. "";
				if( count($exhibitions) > $list_size 
					|| (isset($settings['page-artgalleryexhibitions-past']) && $settings['page-artgalleryexhibitions-past'] == 'yes'
						&& $cobj['num_items'] > count($exhibitions))
					) {
					$block_content .= "<div class='cilist-more'>"
						. "<a href='" . $more_base_url . "/exhibitions'>"
						. ((isset($cobj['more']) && $cobj['more'] != '')?$cobj['more']:'... more exhibitions')
						. "</a></div>";
				}
				$block_content .= "</article>\n";
			}
			if( $block_content != '' ) {
				$blocks['ciniki.artgallery.exhibition'] = array('content'=>$block_content, 'display'=>'no');
			}
		}

		//
		// Load the workshops
		//
		if( isset($ciniki['business']['modules']['ciniki.workshops']) 
			&& isset($collection['objects']['ciniki.workshops.workshop'])
			) {
			$cobj = $collection['objects']['ciniki.workshops.workshop'];
			$list_size = 2;
			if( isset($cobj['num_display_items']) && $cobj['num_display_items'] > 0 ) {
				$list_size = $cobj['num_display_items'];
			}
			//
			// Load and parse the workshops
			//
			ciniki_core_loadMethod($ciniki, 'ciniki', 'workshops', 'web', 'webCollectionList');
			$rc = ciniki_workshops_web_webCollectionList($ciniki, $settings, $ciniki['request']['business_id'], 
				array('collection_id'=>$collection['id'], 'type'=>'upcoming', 'limit'=>$list_size+1));
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$block_content = '';
			if( isset($rc['workshops']) && count($rc['workshops']) > 0 ) {
				$workshops = $rc['workshops'];
				ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processWorkshops');
				$rc = ciniki_web_processWorkshops($ciniki, $settings, $workshops, $list_size);
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				$block_content .= "<article class='page page-home'>\n"
					. "<header class='entry-title'><h1 class='entry-title'>Upcoming "
					. ((isset($cobj['title']) && $cobj['title'] != '')?$cobj['title']:'Workshops')
					. "</h1></header>\n"
					. "<div class='entry-content'>"
					. $rc['content']
					. "</div>"
					. "";
				if( count($workshops) > $list_size 
					|| (isset($settings['page-workshops-past']) && $settings['page-workshops-past'] == 'yes'
						&& $cobj['num_items'] > count($workshops))
					) {
					$block_content .= "<div class='cilist-more'>"
						. "<a href='" . $more_base_url . "/workshops'>"
						. ((isset($cobj['more']) && $cobj['more'] != '')?$cobj['more']:'... more workshops')
						. "</a></div>";
				}
				$block_content .= "</article>\n";
			}
			if( $block_content != '' ) {
				$blocks['ciniki.workshops.workshop'] = array('content'=>$block_content, 'display'=>'no');
			}
		}

		//
		// Look for any blocks that have a sequence set
		//
		if( isset($collection['objects']) ) {
			foreach($collection['objects'] as $oid => $object) {
				if( isset($blocks[$oid]['content']) ) {
					$page_content .= $blocks[$oid]['content'];
					$blocks[$oid]['displayed'] = 'yes';
				}
			}
		}
		foreach($blocks as $object => $block) {
			if( !isset($block['displayed']) || $block['displayed'] != 'yes' ) {
				$page_content .= $block['content'];
			}
		}
	}

	//
	// Add the header
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageHeader');
	$rc = ciniki_web_generatePageHeader($ciniki, $settings, $page_title, array());
	if( $rc['stat'] != 'ok' ) {	
		return $rc;
	}
	$content .= $rc['content'];

	$content .= "<div id='content'>"
		. $page_content
		. "</div>";

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
