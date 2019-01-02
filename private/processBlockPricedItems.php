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
function ciniki_web_processBlockPricedItems(&$ciniki, $settings, $tnid, $block) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processURL');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getPaddedImageURL');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processMeta');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'cartSetupPrices');

    $page_limit = 0;
    if( isset($block['limit']) ) {
        $page_limit = $block['limit'];
    }

    $count = 0;

    $content = '';
    if( isset($block['title']) && $block['title'] != '' ) {
        $content .= "<h2" . ((isset($block['wide'])&&$block['wide']=='yes')?" class='wide'":'') . ">" . $block['title'] . "</h2>";
    }
    $content .= "<div class='image-list priced-items'>\n";

    foreach($block['list'] as $iid => $item) {
        if( !isset($item['title']) && isset($item['name']) ) {
            $item['title'] = $item['name'];
        }
        if( isset($block['codes']) && $block['codes'] == 'yes' && isset($item['code']) && $item['code'] != '' 
            && !preg_match('/' . preg_replace('/\//', "\\\/", $item['code']) . '/', $item['title']) 
            ) {
            $item['title'] = $item['code'] . ' - ' . $item['title'];
        }
        if( $page_limit > 0 && $count >= $page_limit ) { $count++; break; }
        $url = '';
        if( isset($block['more_button_text']) && $block['more_button_text'] != '' ) {
            $url_display = $block['more_button_text'];
        } else {
            $url_display = '... more';
        }

        $url_target = '';
//      $javascript_onclick = '';
        if( isset($item['is_details']) && $item['is_details'] == 'yes' 
            && isset($item['permalink']) && $item['permalink'] != '' ) {
            $url = $block['base_url'] . "/" . $item['permalink'];
//          $javascript_onclick = " onclick='javascript:location.href=\"$url\";' ";
        } elseif( isset($item['url']) && $item['url'] != '' ) {
            $rc = ciniki_web_processURL($ciniki, $item['url']);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $url = $rc['url'];
            $url_target = '_blank';
            $url_display = $rc['display'];
        }

        $content .= "<div class='image-list-entry-wrap priced-items-wrap'>"
            . ($url!=''?"<a href='$url' target='$url_target' title='" . $item['title'] . "'>":'')
            . "<div class='image-list-entry priced-items-entry'>";

        //
        // Setup the image
        //
        if( isset($block['images']) && $block['images'] == 'yes' ) {
            $content .= "<div class='image-list-image'>";
            if( isset($item['image_id']) && $item['image_id'] > 0 ) {
                if( isset($block['thumbnail_format']) && $block['thumbnail_format'] == 'square-padded' ) {
                    $version = ((isset($block['image_version'])&&$block['image_version']!='')?$block['image_version']:'thumbnail');
                    $rc = ciniki_web_getPaddedImageURL($ciniki, $item['image_id'], 'original', 
                        ((isset($block['image_width'])&&$block['image_width']!='')?$block['image_width']:'400'), 
                        ((isset($block['image_height'])&&$block['image_height']!='')?$block['image_height']:'0'),
                        ((isset($block['thumbnail_padding_color'])&&$block['thumbnail_padding_color']!='')?$block['thumbnail_padding_color']:'#ffffff') 
                        );
                } else {
                    $version = ((isset($block['image_version'])&&$block['image_version']!='')?$block['image_version']:'thumbnail');
                    $rc = ciniki_web_getScaledImageURL($ciniki, $item['image_id'], $version, 
                        ((isset($block['image_width'])&&$block['image_width']!='')?$block['image_width']:'400'), 
                        ((isset($block['image_height'])&&$block['image_height']!='')?$block['image_height']:'0') 
                        );
                }
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
                $content .= "<div class='image-list-wrap image-list-$version'>"
                    . "<img title='' alt='" . $item['title'] . "' src='" . $rc['url'] . "' />"
                    . "</div>";
            } elseif( isset($block['noimage']) && $block['noimage'] == 'yes' ) {
                $content .= "<div class='image-list-wrap image-list-thumbnail'>"
                    . "<img title='' alt='" . $item['title'] . "' src='/ciniki-web-layouts/default/img/noimage_240.png' />"
                    . "</div>";
            } elseif( isset($block['noimage']) && $block['noimage'] != '' ) {
                $content .= "<div class='image-list-wrap image-list-thumbnail'>"
                    . "<img title='' alt='" . $item['title'] . "' src='" . $block['noimage'] . "' />"
                    . "</div>";
            }
            $content .= "</div>";
        }
    
        //
        // Setup the details
        //
        $content .= "<div class='image-list-details'>";
        $content .= "<div class='image-list-title'>" 
            . $item['title'] 
            . "";
        $content .= "</div>";
        $content .= "<div class='image-list-price'>" 
            . number_format($item['price'], 2) 
            . "";
        $content .= "</div>";

        //
        // Setup the meta information
        //
/*        if( isset($item['meta']) && count($item['meta']) > 0 ) {
            $rc = ciniki_web_processMeta($ciniki, $settings, $item);
            if( isset($rc['content']) && $rc['content'] != '' ) {
                $content .= "<div class='image-list-meta'>" . $rc['content'] . "</div>";
            }
        } */
        if( isset($item['synopsis']) && $item['synopsis'] != '' ) {
            $content .= "<div class='image-list-content'>";
            $rc = ciniki_web_processContent($ciniki, $settings, $item['synopsis'], 'image-list-description');
            if( $rc['stat'] == 'ok' ) {
                $content .= $rc['content'];
            }
            $content .= "</div>";
        }

/*        if( $url != '' && $block['more_text'] != '' ) {
            $content .= "<div class='image-list-more'>";
            $content .= "<a href='$url' target='$url_target'>{$block['more_text']}</a>";
            $content .= "</div>";
        }  */
        $content .= "</div>";
        
        $content .= "</div>";
        $content .= ($url!=''?"</a>":'');
        $content .= "</div>";
        $count++;
    }

    $content .= "</div>";

    return array('stat'=>'ok', 'content'=>$content);
}
?>
