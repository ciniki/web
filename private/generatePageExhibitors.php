<?php
//
// Description
// -----------
// This function will generate the exhibitors page for the tenant.
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_generatePageExhibitors($ciniki, $settings) {

    //
    // Store the content created by the page
    // Make sure everything gets generated ok before returning the content
    //
    $content = '';
    $page_content = '';
    $page_title = 'Exhibitors';
    if( isset($settings['page-exhibitions-exhibitors-name']) && $settings['page-exhibitions-exhibitors-name'] != '' ) {
        $page_title = $settings['page-exhibitions-exhibitors-name'];
    }

    //
    // FIXME: Check if anything has changed, and if not load from cache
    //

    //
    // Check if we are to display the gallery image for an exhibitor
    //
    //
    // Check if we are to display an image, from the gallery, or latest images
    //
    if( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] != '' 
        && isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] == 'gallery' 
        && isset($ciniki['request']['uri_split'][2]) && $ciniki['request']['uri_split'][2] != '' 
        ) {
        $exhibitor_permalink = $ciniki['request']['uri_split'][0];
        $image_permalink = $ciniki['request']['uri_split'][2];
        $gallery_url = $ciniki['request']['base_url'] . "/exhibitors/" . $exhibitor_permalink . "/gallery";

        //
        // Load the participant to get all the details, and the list of images.
        // It's one query, and we can find the requested image, and figure out next
        // and prev from the list of images returned
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'exhibitions', 'web', 'participantDetails');
        $rc = ciniki_exhibitions_web_participantDetails($ciniki, $settings, 
            $ciniki['request']['tnid'], 
            $settings['page-exhibitions-exhibition'], $exhibitor_permalink);
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'404', 'err'=>array('code'=>'ciniki.web.49', 'msg'=>"I'm sorry, but we can't seem to find the image your requested.", $rc['err']));
        }
        $participant = $rc['participant'];

        if( !isset($participant['images']) || count($participant['images']) < 1 ) {
            return array('stat'=>'404', 'err'=>array('code'=>'ciniki.web.50', 'msg'=>"I'm sorry, but we can't seem to find the image your requested."));
        }

        $first = NULL;
        $last = NULL;
        $img = NULL;
        $next = NULL;
        $prev = NULL;
        foreach($participant['images'] as $iid => $image) {
            if( $first == NULL ) {
                $first = $image;
            }
            if( $image['permalink'] == $image_permalink ) {
                $img = $image;
            } elseif( $next == NULL && $img != NULL ) {
                $next = $image;
            } elseif( $img == NULL ) {
                $prev = $image;
            }
            $last = $image;
        }

        if( count($participant['images']) == 1 ) {
            $prev = NULL;
            $next = NULL;
        } elseif( $prev == NULL ) {
            // The requested image was the first in the list, set previous to last
            $prev = $last;
        } elseif( $next == NULL ) {
            // The requested image was the last in the list, set previous to last
            $next = $first;
        }

        if( $img['title'] != '' ) {
            $page_title = $participant['name'] . ' - ' . $img['title'];
        } else {
            $page_title = $participant['name'];
        }

        if( $img == NULL ) {
            return array('stat'=>'404', 'err'=>array('code'=>'ciniki.web.51', 'msg'=>"I'm sorry, but we can't seem to find the image your requested."));
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

        //
        // Set the page to wide if possible
        //
        $ciniki['request']['page-container-class'] = 'page-container-wide';

        $svg_prev = '';
        $svg_next = '';
        if( isset($settings['site-layout']) && $settings['site-layout'] == 'twentyone' ) {
            $ciniki['request']['inline_javascript'] = '';
            $ciniki['request']['onresize'] = "";
            $ciniki['request']['onload'] = "scrollto_header();";
            $svg_prev = '<svg viewbox="0 0 80 80" stroke="#fff" fill="none"><polyline stroke-width="5" stroke-linecap="round" stroke-linejoin="round" points="50,70 20,40 50,10"></polyline></svg>';
            $svg_next = '<svg viewbox="0 0 80 80" stroke="#fff" fill="none"><polyline stroke-width="5" stroke-linecap="round" stroke-linejoin="round" points="30,70 60,40 30,10"></polyline></svg>';

        } else {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generateGalleryJavascript');
            $rc = ciniki_web_generateGalleryJavascript($ciniki, $next, $prev);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $ciniki['request']['inline_javascript'] = $rc['javascript'];
            $ciniki['request']['onresize'] = "gallery_resize_arrows();";
            $ciniki['request']['onload'] = "scrollto_header();";
        }

        $page_content .= "<article class='page'>\n"
            . "<header class='entry-title'><h1 id='entry-title' class='entry-title'>$page_title</h1></header>\n"
            . "<div class='entry-content'>\n"
            . "";
        $page_content .= "<div id='gallery-image' class='gallery-image'>";
        $page_content .= "<div id='gallery-image-wrap' class='gallery-image-wrap'>";
        if( $prev != null ) {
            $page_content .= "<a id='gallery-image-prev' class='gallery-image-prev' href='$gallery_url/" . $prev['permalink'] . "'><div id='gallery-image-prev-img'>{$svg_prev}</div></a>";
        }
        if( $next != null ) {
            $page_content .= "<a id='gallery-image-next' class='gallery-image-next' href='$gallery_url/" . $next['permalink'] . "'><div id='gallery-image-next-img'>{$svg_next}</div></a>";
        }
        $page_content .= "<img id='gallery-image-img' title='" . $img['title'] . "' alt='" . $img['title'] . "' src='" . $img_url . "' onload='javascript: gallery_resize_arrows();' />";
        $page_content .= "</div><br/>"
            . "<div id='gallery-image-details' class='gallery-image-details'>"
            . "<span class='image-title'>" . $img['title'] . '</span>'
            . "<span class='image-details'></span>";
        if( $img['description'] != '' ) {
            $page_content .= "<span class='image-description'>" . preg_replace('/\n/', '<br/>', $img['description']) . "</span>";
        }
        $page_content .= "</div></div>";
        $page_content .= "</div></article>";
    }

    //
    // Check if we are to display an exhibitor
    //
    elseif( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] != '' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'exhibitions', 'web', 'participantDetails');
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processURL');

        //
        // Get the exhibitor information
        //
        $exhibitor_permalink = $ciniki['request']['uri_split'][0];
        $rc = ciniki_exhibitions_web_participantDetails($ciniki, $settings, 
            $ciniki['request']['tnid'], 
            $settings['page-exhibitions-exhibition'], $exhibitor_permalink);
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'404', 'err'=>array('code'=>'ciniki.web.52', 'msg'=>"I'm sorry, but we don't have any record of that exhibitor.", 'err'=>$rc['err']));;
        }
        $participant = $rc['participant'];
        $page_title = $participant['name'];
        $page_content .= "<article class='page'>\n"
            . "<header class='entry-title'><h1 class='entry-title'>" . $participant['name'] . "</h1></header>\n"
            . "";

        //
        // Add primary image
        //
        if( isset($participant['image_id']) && $participant['image_id'] > 0 ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
            $rc = ciniki_web_getScaledImageURL($ciniki, $participant['image_id'], 'original', '500', 0);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $page_content .= "<aside><div class='block-primary-image'><div class='image-wrap'><div class='image'>"
                . "<img title='' alt='" . $participant['name'] . "' src='" . $rc['url'] . "' />"
                . "</div></div></div></aside>";
        }
        
        //
        // Add description
        //
        $page_content .= "<div class='block-content'>";
        if( isset($participant['description']) ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
            $rc = ciniki_web_processContent($ciniki, $settings, $participant['description']);   
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $page_content .= $rc['content'];
        }

        if( isset($participant['url']) ) {
            $rc = ciniki_web_processURL($ciniki, $participant['url']);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $url = $rc['url'];
            $display_url = $rc['display'];
        } else {
            $url = '';
        }

        if( $url != '' ) {
            $page_content .= "<br/>Website: <a class='exhibitors-url' target='_blank' href='" . $url . "' title='" . $participant['name'] . "'>" . $display_url . "</a>";
        }
        $page_content .= "</article>";

        if( isset($participant['images']) && count($participant['images']) > 0 ) {
            $page_content .= "<article class='page'>"   
                . "<header class='entry-title'><h1 class='entry-title'>Gallery</h1></header>\n"
                . "";
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageGalleryThumbnails');
            $img_base_url = $ciniki['request']['base_url'] . "/exhibitors/" . $participant['permalink'] . "/gallery";
            $rc = ciniki_web_generatePageGalleryThumbnails($ciniki, $settings, $img_base_url, $participant['images'], 125);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $page_content .= "<div class='image-gallery'>" . $rc['content'] . "</div>";
            $page_content .= "</article>";
        }
    }

    //
    // Display the list of exhibitors if a specific one isn't selected
    //
    else {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'exhibitions', 'web', 'participantList');
        $rc = ciniki_exhibitions_web_participantList($ciniki, $settings, $ciniki['request']['tnid'], $settings['page-exhibitions-exhibition'], 'exhibitor');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $participants = $rc['categories'];

        $page_content .= "<article class='page'>\n"
            . "<header class='entry-title'><h1 class='entry-title'>$page_title</h1></header>\n"
            . "<div class='entry-content'>\n"
            . "";

        if( count($participants) > 0 ) {
            $base_url = $ciniki['request']['base_url'] . '/exhibitors';
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processCIList');
            $rc = ciniki_web_processCIList($ciniki, $settings, $base_url, $participants, array());
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $page_content .= $rc['content'];
        } else {
            $page_content .= "<p>Currently no exhibitors for this event.</p>";
        }

        $page_content .= "</div>\n"
            . "</article>\n"
            . "";
    }

    //
    // Generate the complete page
    //

    //
    // Add the header
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageHeader');
    $rc = ciniki_web_generatePageHeader($ciniki, $settings, $page_title, array());
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }
    $content .= $rc['content'];

    $content .= "<div id='content'>\n"
        . $page_content
        . "</div>"
        . "";

    //
    // Add the footer
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageFooter');
    $rc = ciniki_web_generatePageFooter($ciniki, $settings);
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }
    $content .= $rc['content'];

    return array('stat'=>'ok', 'content'=>$content);
}
?>
