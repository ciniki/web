<?php
//
// Description
// -----------
// Price cards were developed for donations to display the packages available for donations.
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_processBlockPriceCards($ciniki, $settings, $business_id, $block) {

    if( !isset($block['cards']) ) {
        return array('stat'=>'ok', 'content'=>'');
    }

//    if( !isset($block['base_url']) ) {
//        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.193', 'msg'=>'Unable to process request'));
//    }

    $content = "";

    $content .= "<div class='price-cards'>";
    foreach($block['cards'] as $cid => $card) {
        if( isset($card['name']) ) {
            $name = $card['name'];
        } elseif( isset($card['title']) ) {
            $name = $card['title'];
        } else {
            $name = '';
        }
/*        if( isset($block['thumbnail_format']) && $block['thumbnail_format'] == 'square-padded' ) {
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
        } */
//        $content .= "<a class='price-card-a' href='" . $block['base_url'] . '/' . $card['permalink'] . "' " . "title='$name'>";
        $content .= "<div class='price-card-wrap'>"
            . "<div class='price-card'>"
//            . "<div class='price-card-thumbnail'>"
//            . "<img title='$name' alt='$name' src='$img_url' />"
//            . "</div>"
            . "<div class='price-card-text'>"
            . "<div class='price-card-title'>$name</div>";
        if( isset($card['subname']) && $card['subname'] != '' ) {
            $content .= "<div class='price-card-subtitle'>" . $card['subname'] . "</div>";
        }
        if( isset($card['synopsis']) && $card['synopsis'] != '' ) {
            $content .= "<div class='price-card-synopsis'>" . $card['synopsis'] . "</div>";
        }
        $content .= "</div>";
        $content .= "<div class='price-card-form'>"
            . "<form action='" . $ciniki['request']['ssl_domain_base_url'] . "/cart' method='POST'>"
            . "<input type='hidden' name='action' value='add'/>"
            . "<input type='hidden' name='object' value='ciniki.sapos.donationpackage'/>"
            . "<input type='hidden' name='object_id' value='" . $card['id'] . "'/>"
            . "<input type='hidden' name='quantity' value='1'/>";
        if( $card['amount'] > 0 ) {
            $content .= "<input type='hidden' name='final_amount' value='" . $card['amount'] . "' />";
        } else {
            $content .= "<div class='price-card-amount'>"
                . "Amount: <input class='text' type='text' name='user_amount'/>"
                . "</div>";
        }
        $content .= "<div class='price-card-submit'><input class='cart-submit' type='submit' name='add' value='Donate Now'/></div>";
        $content .= "</form>";
        $content .= "</div></div></div>";
    }
    $content .= "</div>";

    return array('stat'=>'ok', 'content'=>$content);
}
?>
