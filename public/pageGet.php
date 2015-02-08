<?php
//
// Description
// -----------
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:			The ID of the business.
//
// Returns
// -------
//
function ciniki_web_pageGet($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
		'page_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Page'),
		'parent_id'=>array('required'=>'no', 'default'=>'0', 'blank'=>'no', 'name'=>'Parent'),
		'images'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Images'),
		'files'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Files'),
		'parentlist'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Parent List'),
		'children'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Children'),
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];

    //  
    // Make sure this module is activated, and
    // check permission to run this function for this business
    //  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'checkAccess');
    $rc = ciniki_web_checkAccess($ciniki, $args['business_id'], 'ciniki.web.pageGet'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
	$date_format = ciniki_users_dateFormat($ciniki);

	if( $args['page_id'] > 0 ) {
		//
		// Get the main webrmation
		//
		$strsql = "SELECT ciniki_web_pages.id, "
			. "ciniki_web_pages.parent_id, "
			. "ciniki_web_pages.title, "
			. "ciniki_web_pages.permalink, "
			. "ciniki_web_pages.category, "
			. "ciniki_web_pages.sequence, "
			. "ciniki_web_pages.flags, "
			. "ciniki_web_pages.primary_image_id, "
			. "ciniki_web_pages.primary_image_caption, "
			. "ciniki_web_pages.primary_image_url, "
			. "ciniki_web_pages.child_title, "
			. "ciniki_web_pages.synopsis, "
			. "ciniki_web_pages.content "
			. "FROM ciniki_web_pages "
			. "WHERE ciniki_web_pages.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "AND ciniki_web_pages.id = '" . ciniki_core_dbQuote($ciniki, $args['page_id']) . "' "
			. "";
		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
		$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.web', array(
			array('container'=>'pages', 'fname'=>'id', 'name'=>'page',
				'fields'=>array('id', 'parent_id',
					'title', 'permalink', 'category', 'sequence', 'flags', 
					'primary_image_id', 'primary_image_caption', 'primary_image_url', 
					'child_title', 'synopsis', 'content')),
			));
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$page = $rc['pages'][0]['page'];

		//
		// Get the images
		//
		if( isset($args['images']) && $args['images'] == 'yes' ) {
			$strsql = "SELECT id, name, image_id, webflags "
				. "FROM ciniki_web_page_images "
				. "WHERE page_id = '" . ciniki_core_dbQuote($ciniki, $args['page_id']) . "' "
				. "AND business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
				. "";
			$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.web', array(
				array('container'=>'images', 'fname'=>'id', 'name'=>'image',
					'fields'=>array('id', 'name', 'image_id', 'webflags')),
				));
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			if( isset($rc['images']) ) {
				$page['images'] = $rc['images'];
				ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'private', 'loadCacheThumbnail');
				foreach($page['images'] as $inum => $img) {
					if( isset($img['image']['image_id']) && $img['image']['image_id'] > 0 ) {
						$rc = ciniki_images_loadCacheThumbnail($ciniki, $args['business_id'], 
							$img['image']['image_id'], 75);
						if( $rc['stat'] != 'ok' ) {
							return $rc;
						}
						$page['images'][$inum]['image']['image_data'] = 'data:image/jpg;base64,' . base64_encode($rc['image']);
					}
				}
			}
		}

		//
		// Get the files
		//
		if( isset($args['files']) && $args['files'] == 'yes' ) {
			$strsql = "SELECT id, name, extension, permalink "
				. "FROM ciniki_web_page_files "
				. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
				. "AND ciniki_web_page_files.page_id = '" . ciniki_core_dbQuote($ciniki, $args['page_id']) . "' "
				. "";
			$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.web', array(
				array('container'=>'files', 'fname'=>'id', 'name'=>'file',
					'fields'=>array('id', 'name', 'extension', 'permalink')),
			));
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			if( isset($rc['files']) ) {
				$page['files'] = $rc['files'];
			} else {
				$page['files'] = array();
			} 
		}

		//
		// Get the child items
		//
		if( isset($args['children']) && $args['children'] == 'yes' ) {
			$strsql = "SELECT id, title "
				. "FROM ciniki_web_pages "
				. "WHERE parent_id = '" . ciniki_core_dbQuote($ciniki, $args['page_id']) . "' "
				. "AND business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
				. "ORDER BY sequence, title "
				. "";
			$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.web', array(
				array('container'=>'pages', 'fname'=>'id', 'name'=>'page',
					'fields'=>array('id', 'title')),
					));
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			if( isset($rc['pages']) ) {
				$page['pages'] = $rc['pages'];
			} else {
				$page['pages'] = array();
			}
		}
	} else {
		$strsql = "SELECT MAX(sequence) AS sequence "
			. "FROM ciniki_web_pages "
			. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "AND parent_id = '" . ciniki_core_dbQuote($ciniki, $args['parent_id']) . "' "
			. "";
		$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.web', 'max');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( isset($rc['max']['sequence']) ) {
			$sequence = $rc['max']['sequence'] + 1;
		} else {
			$sequence = 1;
		}
		$page = array('id'=>'0', 
			'title'=>'',
			'permalink'=>'',
			'parent_id'=>$args['parent_id'],
			'category'=>'',
			'sequence'=>$sequence,
			'flags'=>'17',
			'primary_image_id'=>'0',
			'primary_image_caption'=>'',
			'primary_image_url'=>'',
			'synopsis'=>'',
			'content'=>'',
			'child_title'=>'',
			);
	}

	//
	// Get the complete list of pages for the parent list
	//
	$parentlist = array();
	if( isset($args['parentlist']) && $args['parentlist'] == 'yes' ) {
		$strsql = "SELECT id, parent_id, title "
			. "FROM ciniki_web_pages "
			. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "ORDER BY parent_id, sequence, title ";
		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
		$rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.web', array(
			array('container'=>'pages', 'fname'=>'parent_id',
				'fields'=>array('parent_id', 'title')),
			array('container'=>'pages', 'fname'=>'id',
				'fields'=>array('id', 'parent_id', 'title')),
			));
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		//
		// Check if there are pages with no parent
		//
		if( isset($rc['pages'][0]['pages']) ) {
			function buildParentList($parentlist, $depth, $cpage, $pages) {
				// Check for children
				if( $depth > 0 ) {
					$indent = '';
					for($i=1;$i<$depth;$i++) {
						$indent .= ' - ';
					}
					$parentlist[] = array('page'=>array(
						'id'=>$cpage['id'], 
						'depth'=>$depth,
						'title'=>$indent . $cpage['title'],
						));
				}
				if( isset($pages[$cpage['id']]['pages']) ) {
					$child_pages = $pages[$cpage['id']]['pages'];
					foreach($child_pages as $child_id => $child_page) {
						$parentlist = buildParentList($parentlist, $depth+1, $child_page, $pages);
					}
				}
				return $parentlist;
			}
			$pages = $rc['pages'];
			$parentlist = buildParentList(array(), 0, array('id'=>'0', 'title'=>''), $pages);
		}
	}

	return array('stat'=>'ok', 'page'=>$page, 'parentlist'=>$parentlist);
}
?>
