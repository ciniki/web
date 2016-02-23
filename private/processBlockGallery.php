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
function ciniki_web_processBlockGallery(&$ciniki, $settings, $business_id, $block) {

	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processURL');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getPaddedImageURL');

	//
	// Start content and clear to both sides of the page
	//
	$content = '<br clear="both"/>';
    $images = '';

	if( isset($block['title']) && $block['title'] != '' ) {
		$content .= "<h2 class='entry-title wide'>" . $block['title'] . "</h2>\n";
	}

	foreach($block['images'] as $inum => $img) {
        
		// 
		// Check if image is not specified
		//
		if( $img['image_id'] == 0 ) {
            continue;
//			$img_url = "/ciniki-web-layouts/default/img/noimage_240.png";
		} else {
            if( isset($block['thumbnail_format']) && $block['thumbnail_format'] == 'square-padded' ) {
                $version = ((isset($block['image_version'])&&$block['image_version']!='')?$block['image_version']:'thumbnail');
                $rc = ciniki_web_getPaddedImageURL($ciniki, $img['image_id'], 'original', 
                    ((isset($block['image_width'])&&$block['image_width']!='')?$block['image_width']:'150'), 
                    ((isset($block['image_height'])&&$block['image_height']!='')?$block['image_height']:'0'),
                    ((isset($block['thumbnail_padding_color'])&&$block['thumbnail_padding_color']!='')?$block['thumbnail_padding_color']:'#ffffff') 
                    );
            } else {
                $version = ((isset($block['image_version'])&&$block['image_version']!='')?$block['image_version']:'thumbnail');
                $rc = ciniki_web_getScaledImageURL($ciniki, $img['image_id'], $version, 
                    ((isset($block['image_width'])&&$block['image_width']!='')?$block['image_width']:'150'), 
                    ((isset($block['image_height'])&&$block['image_height']!='')?$block['image_height']:'0') 
                    );
            }
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $images .= "<div class='image-gallery-thumbnail'>"
                . "<a href='" . $block['base_url'] . "/" . $img['permalink'] . "'>"
                . "<img title='" . htmlspecialchars(strip_tags($img['title'])) . "' alt='" . htmlspecialchars(strip_tags($img['title'])) . "' src='" . $rc['url'] . "' /></a>"
                . "</div>";
        }
	}

    if( $images != '' ) {
        $content .= "<div class='image-gallery'>" . $images . "</div>";
    }

	return array('stat'=>'ok', 'content'=>$content);
}
?>
