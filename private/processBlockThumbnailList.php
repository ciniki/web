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
function ciniki_web_processBlockThumbnailList(&$ciniki, $settings, $tnid, $block) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getPaddedImageURL');

    if( !isset($block['list']) ) {
        return array('stat'=>'ok', 'content'=>'');
    }

    if( !isset($block['base_url']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.118', 'msg'=>'Unable to process request'));
    }

    $content = "";

    $content .= "<div class='thumbnail-list'>";
    foreach($block['list'] as $cid => $item) {
        if( isset($item['name']) ) {
            $name = $item['name'];
        } elseif( isset($item['title']) ) {
            $name = $item['title'];
        } else {
            $name = '';
        }
        if( isset($block['thumbnail_format']) && $block['thumbnail_format'] == 'square-padded' ) {
            $version = ((isset($block['image_version'])&&$block['image_version']!='')?$block['image_version']:'original');
            $rc = ciniki_web_getPaddedImageURL($ciniki, $item['image_id'], 'original', 
                ((isset($block['image_width'])&&$block['image_width']!='')?$block['image_width']:'240'), 
                ((isset($block['image_height'])&&$block['image_height']!='')?$block['image_height']:'0'),
                ((isset($block['thumbnail_padding_color'])&&$block['thumbnail_padding_color']!='')?$block['thumbnail_padding_color']:'#ffffff') 
                );
        } else {
            $version = ((isset($block['image_version'])&&$block['image_version']!='')?$block['image_version']:'thumbnail');
            $rc = ciniki_web_getScaledImageURL($ciniki, $item['image_id'], $version, 
                ((isset($block['image_width'])&&$block['image_width']!='')?$block['image_width']:'240'), 
                ((isset($block['image_height'])&&$block['image_height']!='')?$block['image_height']:'0') 
                );
        }
//        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
//        $rc = ciniki_web_getScaledImageURL($ciniki, $item['image_id'], 'thumbnail', '240', 0);
        if( $rc['stat'] != 'ok' ) {
            $img_url = '/ciniki-web-layouts/default/img/noimage_240.png';
        } else {
            $img_url = $rc['url'];
        }
        $anchor = '';
        if( isset($block['anchors']) && $block['anchors'] == 'permalink' ) {
            $anchor = " id='" . $item['permalink'] . "'";
        }
        $content .= "<div{$anchor} class='thumbnail-list-item-wrap'>"
            . "<div class='thumbnail-list-item'>"
            . "<a href='" . $block['base_url'] . '/' . $item['permalink'] . "' " . "title='$name'>"
            . "<div class='thumbnail-list-item-image'>"
            . "<img title='$name' alt='$name' src='$img_url' />"
            . "</div>"
            . "<div class='thumbnail-list-item-text'>"
            . "<span class='thumbnail-list-item-title'>$name</span>";
        if( isset($item['subname']) && $item['subname'] != '' ) {
            $content .= "<span class='thumbnail-list-item-subtitle'>" . $item['subname'] . "</span>";
        }
        $content .= "</div></a></div></div>";
    }
    $content .= "</div>";

    return array('stat'=>'ok', 'content'=>$content);
}
?>
