<?php
//
// Description
// -----------
// This function will generate the classes page for the tenant.
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_generatePagePropertyRentals($ciniki, $settings) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');

    //
    // Store the content created by the page
    // Make sure everything gets generated ok before returning the content
    //
    $content = '';
    $page_content = '';
    $base_url = $ciniki['request']['base_url'] . "/properties";
    if( isset($settings['page-propertyrentals-title']) && $settings['page-propertyrentals-title'] != '' ) {
        $page_title = $settings['page-propertyrentals-title'];
    } elseif( isset($settings['page-propertyrentals-name']) && $settings['page-propertyrentals-name'] != '' ) {
        $page_title = $settings['page-propertyrentals-name'];
    } else {
        $page_title = 'Properties';
    }


    //
    // FIXME: Check if anything has changed, and if not load from cache
    //

    //
    // Check if we are to display the gallery image for an class
    //
    //
    // Check if we are to display an image, from the gallery, or latest images
    //
    if( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] != '' 
        && $ciniki['request']['uri_split'][0] != 'available' 
        && $ciniki['request']['uri_split'][0] != 'rented' 
        && isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] == 'gallery' 
        && isset($ciniki['request']['uri_split'][2]) && $ciniki['request']['uri_split'][2] != '' 
        ) {
        $property_permalink = $ciniki['request']['uri_split'][0];
        $image_permalink = $ciniki['request']['uri_split'][2];
        $gallery_url = $ciniki['request']['base_url'] . "/properties/$property_permalink/gallery";

        //
        // Load the property details.
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'propertyrentals', 'web', 'propertyDetails');
        $rc = ciniki_propertyrentals_web_propertyDetails($ciniki, $settings, 
            $ciniki['request']['tnid'], $property_permalink);
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'404', 'err'=>array('code'=>'ciniki.web.78', 'msg'=>"I'm sorry, but we can't seem to find the image you requested.", $rc['err']));
        }
        $property = $rc['property'];

        if( !isset($property['images']) || count($property['images']) < 1 ) {
            return array('stat'=>'404', 'err'=>array('code'=>'ciniki.web.79', 'msg'=>"I'm sorry, but we can't seem to find the image you requested."));
        }

        $first = NULL;
        $last = NULL;
        $img = NULL;
        $next = NULL;
        $prev = NULL;
        foreach($property['images'] as $iid => $image) {
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

        if( count($property['images']) == 1 ) {
            $prev = NULL;
            $next = NULL;
        } elseif( $prev == NULL ) {
            // The requested image was the first in the list, set previous to last
            $prev = $last;
        } elseif( $next == NULL ) {
            // The requested image was the last in the list, set previous to last
            $next = $first;
        }
    
        $img_base_url = 
        $article_title = "<a href='" .  $ciniki['request']['base_url'] . "/properties/" . $property['permalink'] . "'>" . $property['title'] . "</a>";
        if( $img['title'] != '' ) {
            $page_title = $property['title'] . ' - ' . $img['title'];
            $article_title .= ' - ' . $img['title'];
        } else {
            $page_title = $property['title'];
        }

        if( $img == NULL ) {
            return array('stat'=>'404', 'err'=>array('code'=>'ciniki.web.80', 'msg'=>"I'm sorry, but we can't seem to find the image you requested."));
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

        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generateGalleryJavascript');
        $rc = ciniki_web_generateGalleryJavascript($ciniki, $next, $prev);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $ciniki['request']['inline_javascript'] = $rc['javascript'];

        $ciniki['request']['onresize'] = "gallery_resize_arrows();";
        $ciniki['request']['onload'] = "scrollto_header();";
        $page_content .= "<article class='page'>\n"
            . "<header class='entry-title'><h1 id='entry-title' class='entry-title'>$article_title</h1></header>\n"
            . "<div class='entry-content'>\n"
            . "";
        $page_content .= "<div id='gallery-image' class='gallery-image'>";
        $page_content .= "<div id='gallery-image-wrap' class='gallery-image-wrap'>";
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
    // Check if we are to display a property 
    //
    elseif( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] != '' 
        && $ciniki['request']['uri_split'][0] != 'available' 
        && $ciniki['request']['uri_split'][0] != 'rented' 
        ) {
        $permalink = $ciniki['request']['uri_split'][0];
        
        //
        // Check if this is a page or category
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'propertyrentals', 'web', 'propertyDetails');
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processPage');

        //
        // Get the property information
        //
        $rc = ciniki_propertyrentals_web_propertyDetails($ciniki, $settings, $ciniki['request']['tnid'], $permalink);
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'404', 'err'=>array('code'=>'ciniki.web.81', 'msg'=>"I'm sorry, but we can't find the property you requested.", $rc['err']));
        }
        $property = $rc['property'];

        $page_content .= "<article class='page'>\n"
            . "<header class='entry-title'><h1 class='entry-title'>" . $property['title'] . "</h1></header>\n"
            . "";

        if( isset($property['image_id']) && $property['image_id'] != '' && $property['image_id'] != 0 ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
            $rc = ciniki_web_getScaledImageURL($ciniki, $property['image_id'], 'original', '500', 0);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $page_content .= "<aside><div class='image-wrap'>"
                . "<div class='image'><img title='' src='" . $rc['url'] . "' /></div>";
            if( isset($property['image_caption']) && $property['image_caption'] != '' ) {
                $page_content .= "<div class='image-caption'>" . $property['image_caption'] . "</div>";
            }
            $page_content .= "</div></aside>";
        }
        $page_content .= "<div class='entry-content'>";

        $details = '';
        if( isset($property['sqft']) && $property['sqft'] > 0 ) {
            $details .= "<b>Size</b>: " . $property['sqft'] . ' sqft<br/>';
        }
        if( isset($property['owner']) && $property['owner'] != '' ) {
            $details .= "<b>Owner</b>: " . $property['owner'] . '<br/>';
        }
        if( $details != '' ) {
            $page_content .= '<p>' . $details . '</p>';
        }

        if( isset($property['description']) ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
            $rc = ciniki_web_processContent($ciniki, $settings, $property['description']);  
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $page_content .= $rc['content'];
        }
        if( isset($info['image_id']) && $info['image_id'] != '' && $info['image_id'] != 0 ) {
            $page_content .= "<br style='clear:both;' />\n";
        }
        $page_content .= "</div>";  

        //
        // Display the additional images for the content
        //
        if( isset($property['images']) && count($property['images']) > 0 ) {
            $page_content .= "<h2 style='clear:right;'>Gallery</h2>\n";
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageGalleryThumbnails');
            if( $property['permalink'] != '' ) {
                $img_base_url = $base_url . '/' . $property['permalink'] . '/gallery';
            } else {
                $img_base_url = $base_url . '/gallery';
            }
            $rc = ciniki_web_generatePageGalleryThumbnails($ciniki, $settings, $img_base_url, $property['images'], 125);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $page_content .= "<div class='image-gallery'>" . $rc['content'] . "</div>";
        }

        $page_content .= "</article>";
    }

    //
    // Check if we are to display a category 
    //
    elseif( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] == 'category' 
        && isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] != '' 
        ) {
/*      $category_permalink = $ciniki['request']['uri_split'][1];

        //
        // Load any content for this page
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'classes', 'web', 'pageInfo');
        $rc = ciniki_classes_web_pageInfo($ciniki, $settings, 
            $ciniki['request']['tnid'], 'category-' . $category_permalink);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $info = $rc['info'];

        $page_content .= "<article class='page'>\n"
            . "<header class='entry-title'><h1 class='entry-title'>" . $info['title'] . "</h1></header>\n"
            . "";

        if( isset($info['image_id']) && $info['image_id'] != '' && $info['image_id'] != 0 ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
            $rc = ciniki_web_getScaledImageURL($ciniki, $info['image_id'], 'original', '500', 0);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $page_content .= "<aside><div class='image-wrap'>"
                . "<div class='image'><img title='' src='" . $rc['url'] . "' /></div>";
            if( isset($info['image_caption']) && $info['image_caption'] != '' ) {
                $page_content .= "<div class='image-caption'>" . $info['image_caption'] . "</div>";
            }
            $page_content .= "</div></aside>";
        }

        $page_content .= "<div class='entry-content'>";
        if( isset($info['content']) ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
            $rc = ciniki_web_processContent($ciniki, $settings, $info['content']);  
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $page_content .= $rc['content'];
        }
        if( isset($info['image_id']) && $info['image_id'] != '' && $info['image_id'] != 0 ) {
            $page_content .= "<br style='clear:both;' />\n";
        }

        $base_url = $ciniki['request']['base_url'] . "/classes";
        //
        // If only categories defined
        //
        if( ($ciniki['tenant']['modules']['ciniki.classes']['flags']&0x02) > 0 ) {
            //
            // Get the list of classes
            //
            ciniki_core_loadMethod($ciniki, 'ciniki', 'classes', 'web', 'classList');
            $rc = ciniki_classes_web_classList($ciniki, $settings, 
                $ciniki['request']['tnid'], array('category'=>$category_permalink));
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $categories = $rc['categories'];

            if( count($categories) > 0 ) {
                ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processCIList');
                $rc = ciniki_web_processCIList($ciniki, $settings, $base_url . '/class', $categories, array());
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
                $page_content .= $rc['content'];
            } else {
                $page_content .= "<p>I'm sorry, but we don't currently offer any classes.</p>";
            }
        }

        //
        // Otherwise display the list of classes with no categories
        //
        else {
            //
            // Get the list of classes
            //
            ciniki_core_loadMethod($ciniki, 'ciniki', 'classes', 'web', 'classList');
            $rc = ciniki_classes_web_classList($ciniki, $settings, $ciniki['request']['tnid'], array());
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $classes = $rc['classes'];

            if( count($classes) > 0 ) {
                ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processCIList');
                $rc = ciniki_web_processCIList($ciniki, $settings, $base_url . '/class', $classes, 
                    array('notitle'=>'yes'));
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
            } else {
                $page_content .= "<p>I'm sorry, but we don't currently offer any classes.</p>";
            }
        }
        $page_content .= "</div></article>"; */
    }
        
    //
    // Generate the main page for the classes.  If there are no subcat specified, then
    // list all the classes here.
    //
    else {
        //
        // Load any content for this page
        //
        $status = 10;
        if( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] == 'rented' ) {
            $status = 20;
            $page_title = "Rented Properties";
        } else {
            $page_title = "Available Properties";
        }
/*      ciniki_core_loadMethod($ciniki, 'ciniki', 'propertyrentals', 'web', 'propertyList');
        $rc = ciniki_classes_web_pageInfo($ciniki, $settings, 
            $ciniki['request']['tnid'], 'introduction');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $info = $rc['info'];
*/
        $page_content .= "<article class='page'>\n"
            . "<header class='entry-title'><h1 class='entry-title'>" . $page_title . "</h1></header>\n"
            . "";
/*      if( isset($info['image_id']) && $info['image_id'] != '' && $info['image_id'] != 0 ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
            $rc = ciniki_web_getScaledImageURL($ciniki, $info['image_id'], 'original', '500', 0);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $page_content .= "<aside><div class='image-wrap'>"
                . "<div class='image'><img title='' src='" . $rc['url'] . "' /></div>";
            if( isset($info['image_caption']) && $info['image_caption'] != '' ) {
                $page_content .= "<div class='image-caption'>" . $info['image_caption'] . "</div>";
            }
            $page_content .= "</div></aside>";
        }

        $page_content .= "<div class='entry-content'>";
        if( isset($info['content']) && $info['content'] != '' ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
            $rc = ciniki_web_processContent($ciniki, $settings, $info['content']);  
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $page_content .= $rc['content'];
        }
        if( isset($info['image_id']) && $info['image_id'] != '' && $info['image_id'] != 0 ) {
            $page_content .= "<br style='clear:both;' />\n";
        }
*/

        //
        // Get the list of classes
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'propertyrentals', 'web', 'properties');
        $rc = ciniki_propertyrentals_web_properties($ciniki, $settings, $ciniki['request']['tnid'], array('status'=>$status));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $properties = $rc['list'];

        $page_content .= "<div class='entry-content'>";

        if( count($properties) > 0 ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processCIList');
            $rc = ciniki_web_processCIList($ciniki, $settings, $base_url, array('0'=>array('list'=>$properties)),
                array('notitle'=>'no'));
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $page_content .= $rc['content'];
        } elseif( $status == 20 ) {
            $page_content .= "<p>I'm sorry, but there are currently no rented properties.</p>";
        } else {
            $page_content .= "<p>I'm sorry, but there are currently no available rentals.</p>";
        }

        $page_content .= "</div>\n"
            . "</article>\n"
            . "";
    }


    //
    // If categories and sub-categories are enabled, then create the submenu of categories
    //
    $submenu = array();
    if( ($ciniki['tenant']['modules']['ciniki.propertyrentals']['flags']&0x01) > 0 ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'propertyrentals', 'web', 'categories');
        $rc = ciniki_propertyrentals_web_categories($ciniki, $settings, $ciniki['request']['tnid']);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['categories']) && count($rc['categories']) > 1 ) {
            foreach($rc['categories'] as $category) {
                $submenu[$category['permalink']] = array('name'=>$category['name'],
                    'url'=>$base_url . '/category/' . $category['permalink']);
            }
        }
    } elseif( $settings['page-propertyrentals-rented'] == 'yes' ) {
        $submenu['available'] = array('name'=>'Available', 'url'=>$base_url);
        $submenu['rented'] = array('name'=>'Rented', 'url'=>$base_url . '/rented');
    }
        

    //
    // Generate the complete page
    //

    //
    // Add the header
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageHeader');
    $rc = ciniki_web_generatePageHeader($ciniki, $settings, $page_title, $submenu);
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
