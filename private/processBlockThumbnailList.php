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
function ciniki_web_processBlockThumbnailList(&$ciniki, $settings, $business_id, $block) {

    if( !isset($block['list']) ) {
        return array('stat'=>'ok', 'content'=>'');
    }

    if( !isset($block['base_url']) ) {
        return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'3046', 'msg'=>'Unable to process request'));
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
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
        $rc = ciniki_web_getScaledImageURL($ciniki, $item['image_id'], 'thumbnail', '240', 0);
        if( $rc['stat'] != 'ok' ) {
            $img_url = '/ciniki-web-layouts/default/img/noimage_240.png';
        } else {
            $img_url = $rc['url'];
        }
        $content .= "<div class='thumbnail-list-item-wrap'>"
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
