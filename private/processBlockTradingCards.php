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
function ciniki_web_processBlockTradingCards($ciniki, $settings, $tnid, $block) {

    if( !isset($block['cards']) ) {
        return array('stat'=>'ok', 'content'=>'');
    }

    if( !isset($block['base_url']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.119', 'msg'=>'Unable to process request'));
    }

    $content = "";

    if( isset($block['title']) && $block['title'] != '' ) {
        $content .= "<h2>" . $block['title'] . "</h2>";
    }

    $content .= "<div class='trading-cards'>";
    foreach($block['cards'] as $cid => $card) {
        if( isset($card['name']) ) {
            $name = $card['name'];
        } elseif( isset($card['title']) ) {
            $name = $card['title'];
        } else {
            $name = '';
        }
        if( isset($block['thumbnail_format']) && $block['thumbnail_format'] == 'square-padded' ) {
            $version = ((isset($block['image_version'])&&$block['image_version']!='')?$block['image_version']:'thumbnail');
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getPaddedImageURL');
            $rc = ciniki_web_getPaddedImageURL($ciniki, $card['image_id'], 'original', 
                ((isset($block['image_width'])&&$block['image_width']!='')?$block['image_width']:'400'), 
                ((isset($block['image_height'])&&$block['image_height']!='')?$block['image_height']:'0'),
                ((isset($block['thumbnail_padding_color'])&&$block['thumbnail_padding_color']!='')?$block['thumbnail_padding_color']:'#ffffff') 
                );
        } else {
            $version = ((isset($block['image_version'])&&$block['image_version']!='')?$block['image_version']:'thumbnail');
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
            $rc = ciniki_web_getScaledImageURL($ciniki, $card['image_id'], $version,
                ((isset($block['image_width'])&&$block['image_width']!='')?$block['image_width']:'400'),
                ((isset($block['image_height'])&&$block['image_height']!='')?$block['image_height']:'0')
                );
        }
//        $rc = ciniki_web_getScaledImageURL($ciniki, $card['image_id'], 'thumbnail', '240', 0);
        if( $rc['stat'] != 'ok' ) {
            $img_url = '/ciniki-web-layouts/default/img/noimage_240.png';
        } else {
            $img_url = $rc['url'];
        }
        $start_url = '';
        $end_url = '';
        if( isset($card['permalink']) && $card['permalink'] != '' ) {
            $start_url = "<a href='" . $block['base_url'] . '/' . $card['permalink'] . "' " . "title='$name'>";
            $end_url = "</a>";
        }
        $content .= "<div class='trading-card-wrap'>"
            . "<div class='trading-card'>"
            . $start_url
            . "<div class='trading-card-thumbnail'>"
            . "<img title='$name' alt='$name' src='$img_url' />"
            . "</div>"
            . $end_url
            . "<div class='trading-card-text'>"
            . $start_url
            . "<span class='trading-card-title'>$name</span>";
        if( isset($card['subname']) && $card['subname'] != '' ) {
            $content .= "<span class='trading-card-subtitle'>" . $card['subname'] . "</span>";
        }
        if( isset($card['display_price']) && $card['display_price'] != '' ) {
            $content .= "<span class='trading-card-price'>" . $card['display_price'] . "</span>";
        }
        if( isset($card['synopsis']) && $card['synopsis'] != '' ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
            $rc = ciniki_web_processContent($ciniki, $settings, $card['synopsis'], '');
            if( $rc['stat'] == 'ok' ) {
                $content .= "<div class='trading-card-synopsis'>" . $rc['content'] . "</div>";
            }
        }
        $content .= $end_url . "</div>";
        if( isset($block['more-button']) && $block['more-button'] == 'yes' && $start_url != '' ) {
            $content .= "<div class='trading-card-more'>"
                . $start_url
                . "<span>"
                . (isset($block['more-button-text']) ? $block['more-button-text'] : '... more')
                . "</span>"
                . $end_url
                . "</div>";
        }
        $content .= "</div></div>";
    }
    $content .= "</div>";

    return array('stat'=>'ok', 'content'=>$content);
}
?>
