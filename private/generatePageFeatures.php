<?php
//
// Description
// -----------
// This function will generate the members page for the tenant.
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_generatePageFeatures($ciniki, $settings) {

    //
    // Store the content created by the page
    // Make sure everything gets generated ok before returning the content
    //
    $content = '';
    $page_content = '';
    $page_title = 'Features';

    //
    // Get the list of categories
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'marketing', 'web', 'categoryList');
    $rc = ciniki_marketing_web_categoryList($ciniki, $settings, $ciniki['request']['tnid'], array());
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $categories = $rc['categories'];

    //
    // Check if category selected
    //
    $current_category = NULL;
    $default_category = NULL;
    foreach($categories as $cid => $category) {
        if( $default_category == NULL ) {
            $default_category = $category;
        }
        if( isset($ciniki['request']['uri_split'][0]) 
            && $ciniki['request']['uri_split'][0] != '' 
            && $category['permalink'] == $ciniki['request']['uri_split'][0] 
            ) {
            $current_category = $category;
            break;
        }
    }
    


    //
    // FIXME: Check if anything has changed, and if not load from cache
    //

    //
    // Check if we are to display a category
    //
    if( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] == 'category' 
        && isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] != '' 
        ) {
        $category_permalink = $ciniki['request']['uri_split'][1];

        ciniki_core_loadMethod($ciniki, 'ciniki', 'customers', 'web', 'memberList');
        $rc = ciniki_customers_web_memberList($ciniki, $settings, $ciniki['request']['tnid'],
            array('category'=>$category_permalink, 'format'=>'2dlist'));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $members = $rc['members'];

        $article_title = "<a href='" . $ciniki['request']['base_url'] . "/members'>Members</a>";
        if( $rc['tag_name'] != '' ) {
            $page_title .= ' - ' . $rc['tag_name'];
            $article_title .= ' - ' . $rc['tag_name'];
        }

        $page_content .= "<article class='page page-features'>\n"
            . "<header class='entry-title'><h1 class='entry-title'>$article_title</h1></header>\n"
            . "<div class='entry-content'>\n"
            . "";

        if( count($members) > 0 ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processCIList');
            $base_url = $ciniki['request']['base_url'] . "/members";
            $rc = ciniki_web_processCIList($ciniki, $settings, $base_url, $members, array('notitle'=>'hide'));
//              array('0'=>array('name'=>'', 'list'=>$members)), array());
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $page_content .= $rc['content'];
        } else {
            $page_content .= "<p>We're sorry, but there doesn't appear to be any members in this category.</p>";
        }

        $page_content .= "</div>"
            . "</article>"
            . "";
    }

    //
    // Check if we are to display an image, from the gallery, or latest images
    //
    elseif( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] != '' 
        && isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] != '' 
        && isset($ciniki['request']['uri_split'][2]) && $ciniki['request']['uri_split'][2] == 'gallery' 
        && isset($ciniki['request']['uri_split'][3]) && $ciniki['request']['uri_split'][3] != '' 
        ) {
        $category_permalink = $ciniki['request']['uri_split'][0];
        $feature_permalink = $ciniki['request']['uri_split'][1];
        $image_permalink = $ciniki['request']['uri_split'][3];
        $gallery_url = $ciniki['request']['base_url'] . "/features/$category_permalink/$feature_permalink/gallery";

        //
        // Load the member to get all the details, and the list of images.
        // It's one query, and we can find the requested image, and figure out next
        // and prev from the list of images returned
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'marketing', 'web', 'featureDetails');
        $rc = ciniki_marketing_web_featureDetails($ciniki, $settings, 
            $ciniki['request']['tnid'], $current_category['id'], $feature_permalink);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $feature = $rc['feature'];

        if( !isset($feature['images']) || count($feature['images']) < 1 ) {
            return array('stat'=>'404', 'err'=>array('code'=>'ciniki.web.53', 'msg'=>"We're sorry, but the image you requested does not exist."));
        }

        $first = NULL;
        $last = NULL;
        $img = NULL;
        $next = NULL;
        $prev = NULL;
        foreach($feature['images'] as $iid => $image) {
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

        if( count($feature['images']) == 1 ) {
            $prev = NULL;
            $next = NULL;
        } elseif( $prev == NULL ) {
            // The requested image was the first in the list, set previous to last
            $prev = $last;
        } elseif( $next == NULL ) {
            // The requested image was the last in the list, set previous to last
            $next = $first;
        }
    
        $article_title = "<a href='" . $ciniki['request']['base_url'] . "/features/$category_permalink/$feature_permalink'>" . $feature['title'] . "</a>";
        if( $img['title'] != '' ) {
            $page_title = $feature['title'] . ' - ' . $img['title'];
            $article_title .= ' - ' . $img['title'];
        } else {
            $page_title = $feature['title'];
        }
    
        //
        // Load the image
        //
        if( isset($img['image_id']) && $img['image_id'] > 0 ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
            $rc = ciniki_web_getScaledImageURL($ciniki, $img['image_id'], 'original', 0, 600);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
        $img_url = $rc['url'];
        } else {
            return array('stat'=>'404', 'err'=>array('code'=>'ciniki.web.54', 'msg'=>"We're sorry, but the image you requested does not exist."));
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
        $page_content .= "<article class='page page-features'>\n"
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
    // Check if we are to display an feature
    //
    elseif( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] != '' 
        && isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] != '' 
        ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'marketing', 'web', 'featureDetails');
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processURL');

        $category_permalink = $ciniki['request']['uri_split'][0];
        $feature_permalink = $ciniki['request']['uri_split'][1];
        //
        // Get the feature information
        //
        $rc = ciniki_marketing_web_featureDetails($ciniki, $settings, 
            $ciniki['request']['tnid'], $current_category['id'], $feature_permalink);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $feature = $rc['feature'];
        $page_title = $feature['title'];
        $page_content .= "<article class='page page-features'>\n"
            . "<header class='entry-title'><h1 class='entry-title'>" . $feature['title'] . "</h1></header>\n"
            . "";

        //
        // Add primary image
        //
        if( isset($feature['image_id']) && $feature['image_id'] > 0 ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
            $rc = ciniki_web_getScaledImageURL($ciniki, $feature['image_id'], 'original', '500', 0);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $page_content .= "<aside><div class='image-wrap'><div class='image'>"
                . "<img title='' alt='" . $feature['title'] . "' src='" . $rc['url'] . "' />"
                . "</div></div></aside>";
        }
        
        //
        // Add description
        //
        if( isset($feature['full_description']) ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
            $rc = ciniki_web_processContent($ciniki, $settings, $feature['full_description']);  
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $page_content .= $rc['content'];
        }
    
        $page_content .= "</article>";

        if( isset($feature['images']) && count($feature['images']) > 0 ) {
            $page_content .= "<article class='page page-features'>" 
                . "<header class='entry-title'><h1 class='entry-title'>More Examples</h1></header>\n"
                . "";
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageGalleryThumbnails');
            $img_base_url = $ciniki['request']['base_url'] . "/features/" . $category_permalink 
                . "/" . $feature_permalink . "/gallery";
            $rc = ciniki_web_generatePageGalleryThumbnails($ciniki, $settings, $img_base_url, $feature['images'], 125);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $page_content .= "<div class='image-gallery'>" . $rc['content'] . "</div>";
            $page_content .= "</article>";
        }
    }

    //
    // Display the list of members if a specific one isn't selected
    //
    else {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
        ciniki_core_loadMethod($ciniki, 'ciniki', 'marketing', 'web', 'featureList');
        $selected_category = NULL;
        if( $current_category != NULL ) {
            $selected_category = $current_category;
        } elseif( $default_category != NULL ) {
            $selected_category = $default_category;
        }
        $rc = ciniki_marketing_web_featureList($ciniki, $settings, $ciniki['request']['tnid'], 
            array('category_id'=>($selected_category!=NULL?$selected_category['id']:'')));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $sections = $rc['sections'];

        $page_title = 'Features';
        if( $selected_category != NULL && $selected_category['name'] != '' ) {
            $page_title .= ' for ' . $selected_category['name'];
        }

        $page_content .= "<article class='page page-features'>\n"
            . "<header class='entry-title'><h1 class='entry-title'>$page_title</h1></header>\n"
            . "<div class='entry-content'>\n"
            . "";

        if( $selected_category != NULL 
            && isset($selected_category['full_description']) 
            && $selected_category['full_description'] != '' 
            ) {
            $rc = ciniki_web_processContent($ciniki, $settings, $selected_category['full_description']);    
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $page_content .= $rc['content'];
        }

        foreach($sections as $sid => $section) {
            if( count($section['features']) > 0 ) {
                if( $sid == '30' ) {
                    $page_content .= "<h2>Additional Features</h2>";
                    if( isset($selected_category['addon_description']) 
                        && $selected_category['addon_description'] != '' 
                        ) {
                        $rc = ciniki_web_processContent($ciniki, $settings, $selected_category['addon_description']);   
                        if( $rc['stat'] != 'ok' ) {
                            return $rc;
                        }
                        $page_content .= $rc['content'];
                    }
                } elseif( $sid == '50' ) {
                    $page_content .= "<h2>Coming soon...</h2>";
                    if( isset($selected_category['future_description']) 
                        && $selected_category['future_description'] != '' 
                        ) {
                        $rc = ciniki_web_processContent($ciniki, $settings, $selected_category['future_description']);  
                        if( $rc['stat'] != 'ok' ) {
                            return $rc;
                        }
                        $page_content .= $rc['content'];
                    }
                }

                
                ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processCIList');
                $base_url = $ciniki['request']['base_url'] . "/features/" . $selected_category['permalink'];
                $rc = ciniki_web_processCIList($ciniki, $settings, $base_url, $section['features'], 
                    array('notitle'=>'yes'));
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
                $page_content .= $rc['content'];

                if( $sid == '10' ) {
                    if( isset($selected_category['base_notes']) 
                        && $selected_category['base_notes'] != '' 
                        ) {
                        $rc = ciniki_web_processContent($ciniki, $settings, $selected_category['base_notes']);  
                        if( $rc['stat'] != 'ok' ) {
                            return $rc;
                        }
                        $page_content .= $rc['content'];
                    }
                } elseif( $sid == '30' ) {
                    if( isset($selected_category['addon_notes']) 
                        && $selected_category['addon_notes'] != '' 
                        ) {
                        $rc = ciniki_web_processContent($ciniki, $settings, $selected_category['addon_notes']); 
                        if( $rc['stat'] != 'ok' ) {
                            return $rc;
                        }
                        $page_content .= $rc['content'];
                    }
                } elseif( $sid == '50' ) {
                    if( isset($selected_category['future_notes']) 
                        && $selected_category['future_notes'] != '' 
                        ) {
                        $rc = ciniki_web_processContent($ciniki, $settings, $selected_category['future_notes']);    
                        if( $rc['stat'] != 'ok' ) {
                            return $rc;
                        }
                        $page_content .= $rc['content'];
                    }
                }
            } elseif( $sid == '10' ) {
                $page_content .= "<p>Currently no features.</p>";
            }
        }

        if( isset($selected_category['signup_text']) && $selected_category['signup_text'] != '' ) {
            $url = '/signup';
            if( isset($selected_category['signup_url']) && $selected_category['signup_url'] != '' ) {
                $args = explode('?', $selected_category['signup_url']);
                if( count($args) > 1 ) {
                    $url = $args[0];
                    $url_args = $args[1];
                } else {
                    $url = $selected_category['signup_url'];
                }
            }
            $page_content .= "<form action='$url' method='GET' class='wide'>";
            if( isset($url_args) && $url_args != '' ) {
                $args = explode('&', $url_args);
                foreach($args as $arg) {
                    list($name, $val) = explode('=', $arg);
                    $page_content .= "<input type='hidden' name='$name' value='$val'/>";
                }
            }
            $page_content .= "<div class='bigsubmit'>"
                . "<input type='submit' class='bigsubmit' name='signup' value='" . $selected_category['signup_text'] . "' /></div>"
                . "</form>"
                . "";
        }

        $page_content .= "</div>\n"
            . "</article>\n"
            . "";
    }

    //
    // Generate the sub menu
    //
    $submenu = array();
    if( count($categories) > 1 ) {
        foreach($categories as $cid => $category) {
            $submenu[$category['permalink']] = array('name'=>$category['name'],
                'url'=>$ciniki['request']['base_url'] . '/features/' . $category['permalink']);
        }
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
