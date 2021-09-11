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
function ciniki_web_processBlockGalleryImage(&$ciniki, $settings, $tnid, $block) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processURL');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');

    $content = '';

    //
    // Check for quality setting
    //
    $quality = 60;
    if( isset($block['quality']) && $block['quality'] == 'high' ) {
        $quality = 90;
    }
    $height = 600;
    if( isset($block['size']) && $block['size'] == 'large' ) {
        $height = 1200;
    }

    //
    // Load the image
    //
    if( isset($block['url']) && $block['url'] != '' ) {
        //
        // Allow the override of the url, used when the image should not be put in the public cache directory
        //
        $img_url = $block['url'];
    } else {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
        $rc = ciniki_web_getScaledImageURL($ciniki, $block['image']['image_id'], 'original', 0, $height, $quality);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $img_url = $rc['url'];

        // Setup the og image, only for public images
        if( isset($block['primary']) && $block['primary'] == 'yes' ) {
            $ciniki['response']['head']['og']['image'] = $rc['domain_url'];
        }
    }

    //
    // Set the page to wide if possible
    //
    if( !isset($ciniki['request']['page-container-class']) ) {
        $ciniki['request']['page-container-class'] = 'page-container-wide';
    } else {
        $ciniki['request']['page-container-class'] .= ' page-container-wide';
    }

    $svg_prev = '';
    $svg_next = '';
    if( isset($settings['site-theme']) && $settings['site-theme'] == 'twentyone' ) {
        $ciniki['request']['inline_javascript'] = '';
        $ciniki['request']['onresize'] = "";
        $ciniki['request']['onload'] = "";
        $svg_prev = '<svg viewbox="0 0 80 80" stroke="#fff" fill="none"><polyline stroke-width="5" stroke-linecap="round" stroke-linejoin="round" points="50,70 20,40 50,10"></polyline></svg>';
        $svg_next = '<svg viewbox="0 0 80 80" stroke="#fff" fill="none"><polyline stroke-width="5" stroke-linecap="round" stroke-linejoin="round" points="30,70 60,40 30,10"></polyline></svg>';
    } else {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generateGalleryJavascript');
        $rc = ciniki_web_generateGalleryJavascript($ciniki, isset($block['next'])?$block['next']:NULL, isset($block['prev'])?$block['prev']:NULL);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $ciniki['request']['inline_javascript'] = $rc['javascript'];
        $ciniki['request']['onresize'] = "gallery_resize_arrows();";
        $ciniki['request']['onload'] = "scrollto_header();";
    }

    $content .= "<div id='gallery-image' class='gallery-image'>";
    $content .= "<div id='gallery-image-wrap' class='gallery-image-wrap'>";
    if( isset($block['prev']['url']) ) {
        $content .= "<a id='gallery-image-prev' class='gallery-image-prev' href='" . $block['prev']['url'] . "'><div id='gallery-image-prev-img'>{$svg_prev}</div></a>";
    }
    if( isset($block['next']['url']) ) {
        $content .= "<a id='gallery-image-next' class='gallery-image-next' href='" . $block['next']['url'] . "'><div id='gallery-image-next-img'>{$svg_next}</div></a>";
    }
    $content .= "<img id='gallery-image-img' title='" . $block['image']['title'] . "' alt='" . $block['image']['title'] . "' src='" . $img_url . "' onload='javascript: gallery_resize_arrows();' />";
    $content .= "</div><br/>"
        . "<div id='gallery-image-details' class='gallery-image-details'>"
        . "<span class='image-title'>" . $block['image']['title'] . '</span>'
        . "<span class='image-details'></span>";
    if( isset($block['image']['description']) && $block['image']['description'] != '' ) {
        $content .= "<span class='image-description'>" . preg_replace('/\n/', '<br/>', $block['image']['description']) . "</span>";
    }
    $content .= "</div></div>";

    return array('stat'=>'ok', 'content'=>$content);
}
?>
