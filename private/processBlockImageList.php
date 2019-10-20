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
function ciniki_web_processBlockImageList(&$ciniki, $settings, $tnid, $block) {

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

//  print "<pre>";
//  print_r($block);
//  print "</pre>";

//  $content = "<table class='cilist'><tbody>";
    $count = 0;
//  $content .= "<tr><th><span class='cilist-category'></span></th><td>";

    $content = '';
    // Start the inner table for the item list
    if( isset($block['title']) && $block['title'] != '' ) {
        $content .= "<h2><span>" . $block['title'] . "</span></h2>";
    }
    $content .= "<div class='image-list'>\n";

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
        if( isset($item['is_details']) && $item['is_details'] == 'yes' 
            && isset($item['permalink']) && $item['permalink'] != '' ) {
            $url = $block['base_url'] . "/" . $item['permalink'];
        } elseif( isset($item['url']) && $item['url'] != '' ) {
            $rc = ciniki_web_processURL($ciniki, $item['url']);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $url = $rc['url'];
            $url_target = '_blank';
            $url_display = $rc['display'];
        }

        $content .= "<div class='image-list-entry-wrap'><div class='image-list-entry'>";

        //
        // Setup the image
        //
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
                . ($url!=''?"<a href='$url' target='$url_target' title='" . $item['title'] . "'>":'')
                . "<img title='' alt='" . $item['title'] . "' src='" . $rc['url'] . "' />"
                . ($url!=''?'</a>':'')
                . "</div>";
        } elseif( isset($block['noimage']) && $block['noimage'] == 'yes' ) {
            $content .= "<div class='image-list-wrap image-list-thumbnail'>"
                . ($url!=''?"<a href='$url' target='$url_target' title='" . $item['title'] . "'>":'')
                . "<img title='' alt='" . $item['title'] . "' src='/ciniki-web-layouts/default/img/noimage_240.png' />"
                . ($url!=''?'</a>':'')
                . "</div>";
        } elseif( isset($block['noimage']) && $block['noimage'] != '' ) {
            $content .= "<div class='image-list-wrap image-list-thumbnail'>"
                . ($url!=''?"<a href='$url' target='$url_target' title='" . $item['title'] . "'>":'')
                . "<img title='' alt='" . $item['title'] . "' src='" . $block['noimage'] . "' />"
                . ($url!=''?'</a>':'')
                . "</div>";
        }
        $content .= "</div>";
    
        //
        // Setup the details
        //
        $content .= "<div class='image-list-details'>";
        $content .= "<div class='image-list-title'><h2>" 
            . ($url!=''?"<a href='$url' target='$url_target' title='" . $item['title'] . "'>":'')
            . $item['title'];
        if( isset($block['title-prices']) && $block['title-prices'] == 'yes' 
            && isset($item['title_price']) && $item['title_price'] != '' 
            ) {
            $content .= "<span class='image-list-price'>" . $item['title_price'] . "</span>";
        }
        $content .= ($url!=''?'</a>':'')
            . "</h2>";
        if( isset($item['subtitle']) && $item['subtitle'] != '' ) {
            $content .= "<h3>" 
                . ($url!=''?"<a href='$url' target='$url_target' title='" . $item['subtitle'] . "'>":'')
                . $item['subtitle'] 
                . ($url!=''?'</a>':'')
                . "</h3>";
        }
        $content .= "</div>";

        //
        // Setup the meta information
        //
        if( isset($item['meta']) && count($item['meta']) > 0 ) {
            $rc = ciniki_web_processMeta($ciniki, $settings, $item);
            if( isset($rc['content']) && $rc['content'] != '' ) {
                $content .= "<div class='image-list-meta'>" . $rc['content'] . "</div>";
            }
        }

        $content .= "<div class='image-list-content'>";
        if( isset($item['synopsis']) && $item['synopsis'] != '' ) {
            $rc = ciniki_web_processContent($ciniki, $settings, $item['synopsis'], 'image-list-description');
            if( $rc['stat'] == 'ok' ) {
                $content .= $rc['content'];
            }
            //
            // Check for files
            //
            if( isset($block['child_files'][$iid]['files']) ) {
                foreach($block['child_files'][$iid]['files'] as $file_id => $file) {
                    $file_url = $block['base_url'] . '/' . $item['permalink'] . '/download/' . $file['permalink'] . '.' . $file['extension'];
                    $content .= "<p><a target='_blank' href='" . $file_url . "' title='" . $file['name'] . "'>" . $file['name'] . "</a></p>";
                }
            }
        } elseif( isset($item['description']) && $item['description'] != '' ) {
            $rc = ciniki_web_processContent($ciniki, $settings, $item['description'], 'image-list-description');
            if( $rc['stat'] == 'ok' ) {
                $content .= $rc['content'];
            }
            //
            // Check for files
            //
            if( isset($block['child_files'][$iid]['files']) ) {
                foreach($block['child_files'][$iid]['files'] as $file_id => $file) {
                    $file_url = $block['base_url'] . '/' . $item['permalink'] . '/download/' . $file['permalink'] . '.' . $file['extension'];
                    $content .= "<p><a target='_blank' href='" . $file_url . "' title='" . $file['name'] . "'>" . $file['name'] . "</a></p>";
                }
            }
        } elseif( isset($item['short_description']) && $item['short_description'] != '' ) {
            $rc = ciniki_web_processContent($ciniki, $settings, $item['short_description'], 'image-list-description');
            if( $rc['stat'] == 'ok' ) {
                $content .= $rc['content'];
            }
    
        } else {
            $content .= "<br/>";
        }
        if( isset($item['urls']) && count($item['urls']) > 0 ) {
            $urls = '';
            foreach($item['urls'] as $url) {
                $rc = ciniki_web_processURL($ciniki, $url);
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
                if( $rc['url'] != '' ) {
                    $urls .= ($urls!='')?'<br/>':'';
                    if( isset($url['title']) && $url['title'] != '' ) {
                        $urls .= "<a href='" . $rc['url'] . "' target='_blank'>" . $url['title'] . "</a>";
                    } else {
                        $urls .= "<a href='" . $rc['url'] . "' target='_blank'>" . $rc['display'] . "</a>";
                    }
                }
                $url = $rc['url'];
                $url_display = $rc['display'];
            }
            if( $urls != '' ) {
                $content .= "<div class='image-list-urls'>" . $urls . "</div>";
            }
        }

        if( isset($block['prices']) && $block['prices'] == 'yes' && isset($item['prices']) && count($item['prices']) > 0 ) {
            $rc = ciniki_web_cartSetupPrices($ciniki, $settings, $tnid, $item['prices']);
            if( $rc['stat'] == 'ok' && $rc['content'] != '' ) {
                $content .= "<div class='image-list-item-prices'>" . $rc['content'] . "</div>";
            }
        }

        $content .= "</div>";
        if( $url != '' ) {
            $content .= "<div class='image-list-more'>";
            $content .= "<a href='$url' target='$url_target'>$url_display</a>";
            $content .= "</div>";
        } 
        $content .= "</div>";
        
        $content .= "</div>";
        $content .= "</div>";
        $count++;
    }

    $content .= "</div>";

    return array('stat'=>'ok', 'content'=>$content);
}
?>
