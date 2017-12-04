<?php
//
// Description
// -----------
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_processBlockTagImages($ciniki, $settings, $tnid, $block) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getPaddedImageURL');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');

    if( !isset($block['tags']) ) {
        return array('stat'=>'ok', 'content'=>'');
    }

    if( !isset($block['base_url']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.117', 'msg'=>'Unable to process request'));
    }

    $content = "";

    if( isset($block['title']) && $block['title'] != '' ) {
        $content .= "<h2 class='wide'>" . $block['title'] . "</h2>";
    }

    $content .= "<div class='image-categories'>";
    foreach($block['tags'] as $tid => $tag) {
        if( isset($tag['name']) ) {
            $name = $tag['name'];
        } elseif( isset($tag['title']) ) {
            $name = $tag['title'];
        } else {
            $name = '';
        }
        if( isset($block['thumbnail_format']) && $block['thumbnail_format'] == 'square-padded' ) {
            $version = ((isset($block['image_version'])&&$block['image_version']!='')?$block['image_version']:'thumbnail');
            $rc = ciniki_web_getPaddedImageURL($ciniki, $tag['image_id'], 'original', 
                ((isset($block['image_width'])&&$block['image_width']!='')?$block['image_width']:'240'), 
                ((isset($block['image_height'])&&$block['image_height']!='')?$block['image_height']:'0'),
                ((isset($block['thumbnail_padding_color'])&&$block['thumbnail_padding_color']!='')?$block['thumbnail_padding_color']:'#ffffff') 
                );
        } else {
            $rc = ciniki_web_getScaledImageURL($ciniki, $tag['image_id'], 'thumbnail', '240', 0);
        }
        if( $rc['stat'] != 'ok' ) {
            $img_url = '/ciniki-web-layouts/default/img/noimage_240.png';
        } else {
            $img_url = $rc['url'];
        }
        $content .= "<div class='image-categories-thumbnail-wrap" . (isset($block['size']) && $block['size'] != '' ? ' image-categories-thumbnail-' . $block['size'] : '') . "'>";
        if( isset($tag['url']) ) {
            $content .= "<a href='" . $tag['url'] . "' " . "title='$name'>";
        } else {
            $content .= "<a href='" . $block['base_url'] . '/' . $tag['permalink'] . "' " . "title='$name'>";
        }
        $content .= "<div class='image-categories-thumbnail'>"
            . "<img title='$name' alt='$name' src='$img_url' />"
            . "</div>"
            . "<div class='image-categories-text'>"
            . "<span class='image-categories-name'>$name</span>";
        if( isset($tag['subname']) && $tag['subname'] != '' ) {
            $content .= "<span class='image-categories-subname'>" . $tag['subname'] . "</span>";
        }
        $content .= "</div>";
        $content .= "</a></div>";
    }
    $content .= "</div>";

    return array('stat'=>'ok', 'content'=>$content);
}
?>
