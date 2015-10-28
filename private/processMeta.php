<?php
//
// Description
// -----------
// This function will prepare the share buttons to display on a page.
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_web_processMeta(&$ciniki, $settings, $args) {

	//
	// Store the content created by the page
	//
	$content = '';

	if( isset($args['meta']['date']) && $args['meta']['date'] != '' ) {
		$content .= "<span class='meta-date'>";
		$content .= $args['meta']['date'];
		$content .= "</span>";
	}
	if( isset($args['meta']['categories']) && count($args['meta']['categories']) > 0 ) {
		$meta_categories = '';
		foreach($args['meta']['categories'] as $category) {
			if( isset($category['name']) && $category['name'] != '' ) {
				if( $category['permalink'] != '' ) {
					$meta_categories .= ($meta_categories!=''?', ':'')
						. "<a href='" . (isset($args['meta']['category_base_url'])?$args['meta']['category_base_url']:$args['base_url']) . '/' . $category['permalink']
						. "'>" . $category['name'] . "</a>";
				} else {
					$meta_categories .= ($meta_categories!=''?', ':'') . $category['name'];
				}
			}
		}
		if( $meta_categories != '' ) {
			if( isset($args['meta']['divider']) && $args['meta']['divider'] != '' && $content != '' ) {
				$content .= $args['meta']['divider'];
			}
			$content .= "<span class='meta-categories'>";
			if( isset($args['meta']['category_prefix']) && $args['meta']['category_prefix'] != '' ) {
				if( count($args['meta']['categories']) > 1 && isset($args['meta']['categories_prefix']) && $args['meta']['categories_prefix'] != '' ) {
					$content .= $args['meta']['categories_prefix'] . ' ';
				} else {
					$content .= $args['meta']['category_prefix'] . ' ';
				}
			}
			$content .= $meta_categories;
			$content .= "</span>";
		}
	}

	return array('stat'=>'ok', 'content'=>$content);
}
?>
