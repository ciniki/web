<?php
//
// Description
// -----------
// Product cards will display a list of "cards", typically about the shape of a vertical business card, containing the name and 1 or more price options.
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_processBlockProductCards($ciniki, $settings, $business_id, $block) {

    if( !isset($block['cards']) ) {
        return array('stat'=>'ok', 'content'=>'');
    }

    if( !isset($block['base_url']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.176', 'msg'=>'Unable to process request'));
    }

    $content = "";

    $content .= "<div class='product-cards'>";
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
                ((isset($block['image_width'])&&$block['image_width']!='')?$block['image_width']:'240'), 
                ((isset($block['image_height'])&&$block['image_height']!='')?$block['image_height']:'0'),
                ((isset($block['thumbnail_padding_color'])&&$block['thumbnail_padding_color']!='')?$block['thumbnail_padding_color']:'#ffffff') 
                );
        } else {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
            $rc = ciniki_web_getScaledImageURL($ciniki, $card['image_id'], 'thumbnail', '240', 0);
        }
        //$rc = ciniki_web_getScaledImageURL($ciniki, $card['image_id'], 'thumbnail', '240', 0);
        if( $rc['stat'] != 'ok' ) {
            $img_url = '/ciniki-web-layouts/default/img/noimage_240.png';
        } else {
            $img_url = $rc['url'];
        }
        $content .= "<a class='product-card-a' href='" . $block['base_url'] . '/' . $card['permalink'] . "' " . "title='$name'>";
        $content .= "<div class='product-card-wrap'>"
            . "<div class='product-card'>"
            . "<div class='product-card-thumbnail'>"
            . "<img title='$name' alt='$name' src='$img_url' />"
            . "</div>"
            . "<div class='product-card-text'>"
            . "<div class='product-card-title'>$name</div>";
        if( isset($card['subname']) && $card['subname'] != '' ) {
            $content .= "<div class='product-card-subtitle'>" . $card['subname'] . "</div>";
        }
        if( isset($card['options']) && count($card['options']) > 0 ) {
            $content .= "<div class='product-card-options'>";
            foreach($card['options'] as $option) {
                if( isset($option['sale_price_display']) && $option['sale_price_display'] != '' ) {
                    $option['price_display'] = '<s>' . $option['price_display'] . '</s> ' . $option['sale_price_display'];
                }
                $content .= "<div class='product-card-option'>"
                    . "<span class='product-card-option-name'>" . $option['name'] . "</span>"
                    . "<span class='product-card-option-price'>" . $option['price_display'] . "</span>"
                    . "</div>";
            }
            $content .= "</div>";
        }
        $content .= "</div>";
        $content .= "</div></div>";
        $content .= "</a>";
    }
    $content .= "</div>";

    return array('stat'=>'ok', 'content'=>$content);
}
?>
