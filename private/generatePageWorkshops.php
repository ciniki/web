<?php
//
// Description
// -----------
// This function will generate the workshops page for the business.
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_generatePageWorkshops($ciniki, $settings) {

    //
    // Check if a file was specified to be downloaded
    //
    $download_err = '';
    if( isset($ciniki['business']['modules']['ciniki.workshops'])
        && isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] != ''
        && isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] == 'download'
        && isset($ciniki['request']['uri_split'][2]) && $ciniki['request']['uri_split'][2] != '' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'workshops', 'web', 'fileDownload');
        $rc = ciniki_workshops_web_fileDownload($ciniki, $ciniki['request']['business_id'], $ciniki['request']['uri_split'][0], $ciniki['request']['uri_split'][2]);
        if( $rc['stat'] == 'ok' ) {
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            header("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: no-cache');
            $file = $rc['file'];
            if( $file['extension'] == 'pdf' ) {
                header('Content-Type: application/pdf');
            }
//          header('Content-Disposition: attachment;filename="' . $file['filename'] . '"');
            header('Content-Length: ' . strlen($file['binary_content']));
            header('Cache-Control: max-age=0');

            print $file['binary_content'];
            exit;
        }
        
        //
        // If there was an error locating the files, display generic error
        //
        return array('stat'=>'404', 'err'=>array('code'=>'ciniki.web.94', 'msg'=>'The file you requested does not exist.'));
    }

    //
    // Store the content created by the page
    // Make sure everything gets generated ok before returning the content
    //
    $content = '';
    $page_content = '';
    $page_title = 'Exhibitors';
    $ciniki['response']['head']['og']['url'] = $ciniki['request']['domain_base_url'] . '/workshops';

    //
    // FIXME: Check if anything has changed, and if not load from cache
    //

    //
    // Check if we are to display the gallery image for an workshops
    //
    //
    // Check if we are to display an image, from the gallery, or latest images
    //
    if( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] != '' 
        && isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] == 'gallery' 
        && isset($ciniki['request']['uri_split'][2]) && $ciniki['request']['uri_split'][2] != '' 
        ) {
        $workshop_permalink = $ciniki['request']['uri_split'][0];
        $image_permalink = $ciniki['request']['uri_split'][2];

        //
        // Load the workshop to get all the details, and the list of images.
        // It's one query, and we can find the requested image, and figure out next
        // and prev from the list of images returned
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'workshops', 'web', 'workshopDetails');
        $rc = ciniki_workshops_web_workshopDetails($ciniki, $settings, $ciniki['request']['business_id'], $workshop_permalink);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $workshop = $rc['workshop'];

        $ciniki['response']['head']['og']['url'] .= '/' . $workshop_permalink;

        if( !isset($workshop['images']) || count($workshop['images']) < 1 ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.95', 'msg'=>'Unable to find image'));
        }

        $first = NULL;
        $last = NULL;
        $img = NULL;
        $next = NULL;
        $prev = NULL;
        foreach($workshop['images'] as $iid => $image) {
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

        if( count($workshop['images']) == 1 ) {
            $prev = NULL;
            $next = NULL;
        } elseif( $prev == NULL ) {
            // The requested image was the first in the list, set previous to last
            $prev = $last;
        } elseif( $next == NULL ) {
            // The requested image was the last in the list, set previous to last
            $next = $first;
        }
        
        $page_title = $workshop['name'] . ' - ' . $img['title'];

        if( $img == NULL ) {
            return array('stat'=>'404', 'err'=>array('code'=>'ciniki.web.96', 'msg'=>'The image you requested does not exist.'));
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
        $ciniki['response']['head']['og']['image'] = $rc['domain_url'];
        if( isset($workshop['short_description']) && $workshop['short_description'] != '' ) {
            $ciniki['response']['head']['og']['description'] = strip_tags($workshop['short_description']);
        } elseif( isset($workshop['description']) && $workshop['description'] != '' ) {
            $ciniki['response']['head']['og']['description'] = strip_tags($workshop['description']);
        }

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
        $page_content .= "<article class='page'>\n"
            . "<header class='entry-title'><h1 id='entry-title' class='entry-title'>$page_title</h1></header>\n"
            . "<div class='entry-content'>\n"
            . "";
        $page_content .= "<div id='gallery-image' class='gallery-image'>";
        $page_content .= "<div id='gallery-image-wrap' class='gallery-image-wrap'>";
        $gallery_url = $ciniki['request']['base_url'] . "/workshops/" . $workshop_permalink . "/gallery";
        if( $prev != null ) {
            $page_content .= "<a id='gallery-image-prev' class='gallery-image-prev' href='$gallery_url/" . $prev['permalink'] . "'><div id='gallery-image-prev-img'></div></a>";
        }
        if( $next != null ) {
            $page_content .= "<a id='gallery-image-next' class='gallery-image-next' href='$gallery_url/" . $next['permalink'] . "'><div id='gallery-image-next-img'></div></a>";
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
    // Check if we are to display an workshop
    //
    elseif( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] != '' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'workshops', 'web', 'workshopDetails');
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processURL');
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'processDateRange');

        //
        // Get the workshop information
        //
        $workshop_permalink = $ciniki['request']['uri_split'][0];
        $ciniki['response']['head']['og']['url'] .= '/' . $workshop_permalink;
        $rc = ciniki_workshops_web_workshopDetails($ciniki, $settings, 
            $ciniki['request']['business_id'], $workshop_permalink);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $workshop = $rc['workshop'];
        $page_title = $workshop['name'];
        $page_content .= "<article class='page'>\n"
            . "<header class='entry-title'><h1 class='entry-title'>" . $workshop['name'] . "</h1>";
        $meta_content = '';
        $rc = ciniki_core_processDateRange($ciniki, $workshop);
        $meta_content .= $rc['dates'];
        if( $meta_content != '' ) {
            $page_content .= "<div class='entry-meta'>" . $meta_content;
            if( isset($workshop['times']) && $workshop['times'] != '' ) {
                $page_content .= "<br/>" . $workshop['times'];
            }
            $page_content .= "</div>";
        }
        $page_content .= "</header>\n";

        //
        // Add primary image
        //
        if( isset($workshop['image_id']) && $workshop['image_id'] > 0 ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
            $rc = ciniki_web_getScaledImageURL($ciniki, $workshop['image_id'], 'original', '500', 0);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $ciniki['response']['head']['og']['image'] = $rc['domain_url'];
            $page_content .= "<aside><div class='image-wrap'><div class='image'>"
                . "<img title='' alt='" . $workshop['name'] . "' src='" . $rc['url'] . "' />"
                . "</div></div></aside>";
        }
        
        if( isset($workshop['short_description']) && $workshop['short_description'] != '' ) {
            $ciniki['response']['head']['og']['description'] = strip_tags($workshop['short_description']);
        } elseif( isset($workshop['description']) && $workshop['description'] != '' ) {
            $ciniki['response']['head']['og']['description'] = strip_tags($workshop['description']);
        }

        //
        // Add description
        //
        if( isset($workshop['description']) && $workshop['description'] != '' ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
            $rc = ciniki_web_processContent($ciniki, $settings, $workshop['description']);  
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $page_content .= $rc['content'];
        } elseif( isset($workshop['short_description']) ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
            $rc = ciniki_web_processContent($ciniki, $settings, $workshop['short_description']);    
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $page_content .= $rc['content'];
        }

        if( isset($workshop['url']) ) {
            $rc = ciniki_web_processURL($ciniki, $workshop['url']);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $url = $rc['url'];
            $display_url = $rc['display'];
        } else {
            $url = '';
        }

        if( $url != '' ) {
            $page_content .= "<p>Website: <a class='cilist-url' target='_blank' href='" . $url . "' title='" . $workshop['name'] . "'>" . $display_url . "</a></p>";
        }

        //
        // Display the files for the workshops
        //
        if( isset($workshop['files']) && count($workshop['files']) > 0 ) {
            $page_content .= "<p>";
            foreach($workshop['files'] as $file) {
                $url = $ciniki['request']['base_url'] . '/workshops/' . $ciniki['request']['uri_split'][0] . '/download/' . $file['permalink'] . '.' . $file['extension'];
//              $page_content .= "<span class='downloads-title'>";
                if( $url != '' ) {
                    $page_content .= "<a target='_blank' href='" . $url . "' title='" . $file['name'] . "'>" . $file['name'] . "</a>";
                } else {
                    $page_content .= $file['name'];
                }
//              $page_content .= "</span>";
                if( isset($file['description']) && $file['description'] != '' ) {
                    $page_content .= "<br/><span class='downloads-description'>" . $file['description'] . "</span>";
                }
                $page_content .= "<br/>";
            }
            $page_content .= "</p>";
        }

        //
        // Check if share buttons should be shown
        //
        if( !isset($settings['page-workshops-share-buttons']) 
            || $settings['page-workshops-share-buttons'] == 'yes' ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processShareButtons');
            $rc = ciniki_web_processShareButtons($ciniki, $settings, array(
                'title'=>$page_title,
                'tags'=>array('Workshops'),
                ));
            if( $rc['stat'] == 'ok' ) {
                $page_content .= $rc['content'];
            }
        }


        $page_content .= "</article>";

        //
        // Display the additional images for the workshop
        //
        if( isset($workshop['images']) && count($workshop['images']) > 0 ) {
            $page_content .= "<article class='page'>"   
                . "<header class='entry-title'><h1 class='entry-title'>Gallery</h1></header>\n"
                . "";
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageGalleryThumbnails');
            $img_base_url = $ciniki['request']['base_url'] . "/workshops/" . $workshop['permalink'] . "/gallery";
            $rc = ciniki_web_generatePageGalleryThumbnails($ciniki, $settings, $img_base_url, $workshop['images'], 125);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $page_content .= "<div class='image-gallery'>" . $rc['content'] . "</div>";
            $page_content .= "</article>";
        }
    }

    //
    // Display the list of workshops if a specific one isn't selected
    //
    else {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'workshops', 'web', 'workshopList');
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processURL');
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processWorkshops');
        $rc = ciniki_workshops_web_workshopList($ciniki, $settings, $ciniki['request']['business_id'], 'upcoming', 0);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $workshops = $rc['workshops'];

        $ciniki['response']['head']['og']['description'] = strip_tags('Upcoming Workshops');

        $page_content .= "<article class='page'>\n"
            . "<header class='entry-title'><h1 class='entry-title'>Upcoming Workshops</h1></header>\n"
            . "<div class='entry-content'>\n"
            . "";

        if( count($workshops) > 0 ) {
            $rc = ciniki_web_processWorkshops($ciniki, $settings, $workshops, 0);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $page_content .= $rc['content'];
        } else {
            $page_content .= "<p>Currently no workshops.</p>";
        }

        $page_content .= "</div>\n"
            . "</article>\n"
            . "";
        //
        // Include past workshops if the user wants
        //
        if( isset($settings['page-workshops-past']) && $settings['page-workshops-past'] == 'yes' ) {
            //
            // Generate the content of the page
            //
            ciniki_core_loadMethod($ciniki, 'ciniki', 'workshops', 'web', 'workshopList');
            $rc = ciniki_workshops_web_workshopList($ciniki, $settings, $ciniki['request']['business_id'], 'past', 10);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $workshops = $rc['workshops'];

            $page_content .= "<article class='page'>\n"
                . "<header class='entry-title'><h1 class='entry-title'>Past Workshops</h1></header>\n"
                . "<div class='entry-content'>\n"
                . "";

            if( count($workshops) > 0 ) {
                $rc = ciniki_web_processWorkshops($ciniki, $settings, $workshops, 0);
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
                $page_content .= $rc['content'];
            } else {
                $page_content .= "<p>No past workshops.</p>";
            }

            $page_content .= "</div>\n"
                . "</article>\n"
                . "";
        }
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
