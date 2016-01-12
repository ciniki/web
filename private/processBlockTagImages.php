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
function ciniki_web_processBlockTagImages($ciniki, $settings, $business_id, $block) {

	if( !isset($block['tags']) ) {
		return array('stat'=>'ok', 'content'=>'');
	}

	if( !isset($block['base_url']) ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'2576', 'msg'=>'Unable to process request'));
	}

	$content = "";

	$content .= "<div class='image-categories'>";
	foreach($block['tags'] as $tid => $tag) {
        if( isset($tag['name']) ) {
            $name = $tag['name'];
        } elseif( isset($tag['title']) ) {
            $name = $tag['title'];
        } else {
            $name = '';
        }
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
		$rc = ciniki_web_getScaledImageURL($ciniki, $tag['image_id'], 'thumbnail', '240', 0);
		if( $rc['stat'] != 'ok' ) {
			$img_url = '/ciniki-web-layouts/default/img/noimage_240.png';
		} else {
			$img_url = $rc['url'];
		}
		$content .= "<div class='image-categories-thumbnail-wrap'>"
			. "<a href='" . $block['base_url'] . '/' . $tag['permalink'] . "' " . "title='$name'>"
			. "<div class='image-categories-thumbnail'>"
			. "<img title='$name' alt='$name' src='$img_url' />"
			. "</div>"
			. "<span class='image-categories-name'>$name</span>"
			. "</a></div>";
	}
	$content .= "</div>";

	return array('stat'=>'ok', 'content'=>$content);
}
?>
