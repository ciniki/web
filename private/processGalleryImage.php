<?php
//
// Description
// -----------
// This function will prepare a single image page for the website.
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_processGalleryImage(&$ciniki, $settings, $business_id, $args) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processURL');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');

    $content = '';

    if( !isset($args['item']['images']) || count($args['item']['images']) < 1 ) {
        return array('stat'=>'404', 'err'=>array('code'=>'ciniki.web.121', 'msg'=>"I'm sorry, but we don't seem to have the image your requested."));
    }

    $first = NULL;
    $last = NULL;
    $img = NULL;
    $next = NULL;
    $prev = NULL;
    foreach($args['item']['images'] as $iid => $image) {
        if( $first == NULL ) {
            $first = $image;
        }
        if( $image['permalink'] == $args['image_permalink'] ) {
            $img = $image;
        } elseif( $next == NULL && $img != NULL ) {
            $next = $image;
        } elseif( $img == NULL ) {
            $prev = $image;
        }
        $last = $image;
    }

    if( count($args['item']['images']) == 1 ) {
        $prev = NULL;
        $next = NULL;
    } elseif( $prev == NULL ) {
        // The requested image was the first in the list, set previous to last
        $prev = $last;
    } elseif( $next == NULL ) {
        // The requested image was the last in the list, set previous to last
        $next = $first;
    }

    if( $img == NULL ) {
        return array('stat'=>'404', 'err'=>array('code'=>'ciniki.web.122', 'msg'=>"I'm sorry, but we don't seem to have the image your requested."));
    }

    if( $img['title'] != '' ) {
        $args['article_title'] .= ' - ' . $img['title'];
    }

    //
    // Load the image
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
    $rc = ciniki_web_getScaledImageURL($ciniki, $img['image_id'], 'original', 0, 600);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $img_url = $rc['url'];

    // Setup the og image
    $ciniki['response']['head']['og']['image'] = $rc['domain_url'];

    //
    // Set the page to wide if possible
    //
    $ciniki['request']['page-container-class'] = 'page-container-wide';

    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generateGalleryJavascript');
    $rc = ciniki_web_generateGalleryJavascript($ciniki, $next, $prev);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $ciniki['request']['inline_javascript'] = $rc['javascript'];

    $ciniki['request']['onresize'] = "gallery_resize_arrows();";
    $ciniki['request']['onload'] = "scrollto_header();";
    $content .= "<article class='page'>\n"
        . "<header class='entry-title'><h1 id='entry-title' class='entry-title'>" 
            . $args['article_title'] . "</h1></header>\n"
        . "<div class='entry-content'>\n"
        . "";
    $content .= "<div id='gallery-image' class='gallery-image'>";
    $content .= "<div id='gallery-image-wrap' class='gallery-image-wrap'>";
    if( $prev != null ) {
        $content .= "<a id='gallery-image-prev' class='gallery-image-prev' href='" . (isset($args['gallery_url'])?$args['gallery_url'].'/':'') . $prev['permalink'] . "'><div id='gallery-image-prev-img'></div></a>";
    }
    if( $next != null ) {
        $content .= "<a id='gallery-image-next' class='gallery-image-next' href='" . (isset($args['gallery_url'])?$args['gallery_url'].'/':'') . $next['permalink'] . "'><div id='gallery-image-next-img'></div></a>";
    }
    $content .= "<img id='gallery-image-img' title='" . $img['title'] . "' alt='" . $img['title'] . "' src='" . $img_url . "' onload='javascript: gallery_resize_arrows();' />";
    $content .= "</div><br/>"
        . "<div id='gallery-image-details' class='gallery-image-details'>"
        . "<span class='image-title'>" . $img['title'] . '</span>'
        . "<span class='image-details'></span>";
    if( $img['description'] != '' ) {
        $content .= "<span class='image-description'>" . preg_replace('/\n/', '<br/>', $img['description']) . "</span>";
    }
    $content .= "</div></div>";
    $content .= "</div></article>";

    return array('stat'=>'ok', 'content'=>$content);
}
?>
