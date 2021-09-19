<?php
//
// Description
// -----------
// This function will generate the home page for the website.
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_generatePageHome(&$ciniki, $settings) {

    //
    // Store the content created by the page
    // Make sure everything gets generated ok before returning the content
    //
    $content = '';
    $content1 = '';
    $page_content = '';
    
    //
    // Setup facebook content defaults
    //
    $ciniki['response']['head']['og']['title'] = $ciniki['tenant']['details']['name'] . '';
    $ciniki['response']['head']['og']['url'] = $ciniki['request']['domain_base_url'];
    $ciniki['request']['page-container-class'] = 'page-home';

//  $content = "<pre>" . print_r($ciniki, true) . "</pre>";

    //
    // FIXME: Check if anything has changed, and if not load from cache
    //

    $thumbnail_width = 150;
    if( isset($settings['default-image-thumbnail-width']) && $settings['default-image-thumbnail-width'] > $thumbnail_width ) {
        $thumbnail_width = $settings['default-image-thumbnail-width'];
    }
    //
    // Check if there is a slider to display
    //
    $slider_content = '';
    if( isset($settings['page-home-slider']) 
        && $settings['page-home-slider'] != '' && $settings['page-home-slider'] > 0 
        ) {
        //
        // Load the slider
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'sliderLoad');
        $rc = ciniki_web_sliderLoad($ciniki, $settings, $ciniki['request']['tnid'], $settings['page-home-slider']);
        if( $rc['stat'] == 'ok' ) {
            $slider = $rc['slider'];
            //
            // Process the slider content
            //
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processSlider');
            $rc = ciniki_web_processSlider($ciniki, $settings, $slider);
            if( $rc['stat'] == 'ok' ) {
                $slider_content = $rc['content'];
            }
        }
    }

    //
    // Check if there is a slider to display based on artcatalog work
    //
    if( isset($ciniki['tenant']['modules']['ciniki.artcatalog']) 
        && isset($settings['page-gallery-active']) && $settings['page-gallery-active'] == 'yes' 
        && isset($settings['page-home-gallery-slider-type']) 
        && ($settings['page-home-gallery-slider-type'] == 'latest'
            || $settings['page-home-gallery-slider-type'] == 'random' 
            || $settings['page-home-gallery-slider-type'] == 'forsale') 
        ) {

        ciniki_core_loadMethod($ciniki, 'ciniki', 'artcatalog', 'web', 'sliderImages');
        $rc = ciniki_artcatalog_web_sliderImages($ciniki, $settings, $ciniki['request']['tnid'], 
            $settings['page-home-gallery-slider-type'], 15);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['images']) && count($rc['images']) > 0 ) {
            foreach($rc['images'] as $iid => $img) {
                $rc['images'][$iid]['url'] = $ciniki['request']['base_url'] . '/gallery/category/' . $img['category'] . '/' . $img['permalink'];
            }
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processSlider');
            $size = 'xlarge';
            if( isset($settings['page-home-gallery-slider-size']) && $settings['page-home-gallery-slider-size'] != '' ) {
                $size = $settings['page-home-gallery-slider-size'];
            }
            $rc = ciniki_web_processSlider($ciniki, $settings, array(
                'size'=>$size,
                'speed'=>'medium',
                'resize'=>'scaled',
                'images'=>$rc['images']));
            if( $rc['stat'] == 'ok' ) {
                $slider_content = '';
                if( isset($settings['page-home-gallery-slider-title']) && $settings['page-home-gallery-slider-title'] != '' ) { 
                    $slider_content = "<header class='entry-title'><h1 class='entry-title'>" . $settings['page-home-gallery-slider-title'] . "</h1></header>";
                }
                $slider_content .= $rc['content'];
            }
        }
    }

    //
    // Check if there is a slider to be displayed based on members work
    //
    if( isset($ciniki['tenant']['modules']['ciniki.customers']) 
        && ($ciniki['tenant']['modules']['ciniki.customers']['flags']&0x02) > 0 
        && isset($settings['page-members-active']) && $settings['page-members-active'] == 'yes' 
        && isset($settings['page-home-membergallery-slider-type']) 
        && ($settings['page-home-membergallery-slider-type'] == 'latest' || $settings['page-home-membergallery-slider-type'] == 'random') 
        ) {

        ciniki_core_loadMethod($ciniki, 'ciniki', 'customers', 'web', 'memberSliderImages');
        $rc = ciniki_customers_web_memberSliderImages($ciniki, $settings, $ciniki['request']['tnid'], 
            $settings['page-home-membergallery-slider-type'], 15);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['images']) && count($rc['images']) > 0 ) {
            foreach($rc['images'] as $iid => $img) {
                $rc['images'][$iid]['url'] = $ciniki['request']['base_url'] . '/members/' . $img['member_permalink'];
                if( isset($settings['page-home-membergallery-slider-labels']) 
                    && $settings['page-home-membergallery-slider-labels'] == 'yes' 
                    ) {
                    $rc['images'][$iid]['label'] = '';
                    if( isset($img['title']) && $img['title'] != '' ) {
                        $rc['images'][$iid]['label'] = '<i>' . $img['title'] . '</i>';
                    }
                    if( isset($img['display_name']) && $img['display_name'] != '' ) {
                        $rc['images'][$iid]['label'] .= ($rc['images'][$iid]['label'] != '' ? ' - ' : '') . $img['display_name'];
                    }
                }
            }
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processSlider');
            $size = 'xlarge';
            if( isset($settings['page-home-membergallery-slider-size']) && $settings['page-home-membergallery-slider-size'] != '' ) {
                $size = $settings['page-home-membergallery-slider-size'];
            }
            $rc = ciniki_web_processSlider($ciniki, $settings, array(
                'size'=>$size,
                'speed'=>'medium',
                'resize'=>'scaled',
                'images'=>$rc['images']));
            if( $rc['stat'] == 'ok' ) {
                $slider_content = $rc['content'];
            }
        }
    }

    //
    // Check if there is a slider to be displayed based on exhibitions
    //
    if( isset($ciniki['tenant']['modules']['ciniki.ags']) 
        && isset($settings['page-home-ags-slider-type']) 
        && ($settings['page-home-ags-slider-type'] == 'latest' || $settings['page-home-ags-slider-type'] == 'random') 
        ) {

        ciniki_core_loadMethod($ciniki, 'ciniki', 'ags', 'web', 'sliderImages');
        $rc = ciniki_ags_web_sliderImages($ciniki, $settings, $ciniki['request']['tnid'], 
            $settings['page-home-ags-slider-type'], 15);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['images']) && count($rc['images']) > 0 ) {
            foreach($rc['images'] as $iid => $img) {
                if( isset($settings['page-home-ags-slider-labels']) 
                    && $settings['page-home-ags-slider-labels'] == 'yes' 
                    ) {
                    $rc['images'][$iid]['label'] = '';
                    if( isset($img['title']) && $img['title'] != '' ) {
                        $rc['images'][$iid]['label'] = '<i>' . $img['title'] . '</i>';
                    }
                    if( isset($img['display_name']) && $img['display_name'] != '' ) {
                        $rc['images'][$iid]['label'] .= ($rc['images'][$iid]['label'] != '' ? ' - ' : '') . $img['display_name'];
                    }
                }
            }
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processSlider');
            $size = 'xlarge';
            if( isset($settings['page-home-ags-slider-size']) && $settings['page-home-ags-slider-size'] != '' ) {
                $size = $settings['page-home-ags-slider-size'];
            }
            $rc = ciniki_web_processSlider($ciniki, $settings, array(
                'size'=>$size,
                'speed'=>'medium',
                'resize'=>'scaled',
                'images'=>$rc['images']));
            if( $rc['stat'] == 'ok' ) {
                $slider_content = $rc['content'];
            }
        }
    }

    $page_content .= "<div id='content'>\n"
        . "";
    if( $slider_content != '' ) {
        $page_content .= "<article class='page page-home page-home-slider'>\n"
            . "<div class='entry-content'>\n"
            . $slider_content
            . "</div>"
            . "</article>"
            . "";
    }

    //
    // Generate the content of the page
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash');
    $rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_web_content', 'tnid', $ciniki['request']['tnid'], 'ciniki.web', 'content', 'page-home');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    
    if( !isset($settings['page-home-content-layout']) || $settings['page-home-content-layout'] != 'manual' ) {
        if( isset($rc['content']['page-home-og-description']) && $rc['content']['page-home-og-description'] != '' ) {
            $ciniki['response']['head']['og']['description'] = strip_tags($rc['content']['page-home-og-description']);
        } elseif( isset($rc['content']['page-home-content']) ) {
            $ciniki['response']['head']['og']['description'] = strip_tags($rc['content']['page-home-content']);
        }
    }
    if( isset($settings['page-home-seo-description']) && $settings['page-home-seo-description'] != '' ) {
        $ciniki['response']['head']['og']['description'] = strip_tags($settings['page-home-seo-description']);
    }


    if( isset($rc['content']['page-home-content']) && $rc['content']['page-home-content'] != '' ) {
        if( !isset($settings['page-home-content-layout']) || $settings['page-home-content-layout'] != 'manual' ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
            $rc = ciniki_web_processContent($ciniki, $settings, $rc['content']['page-home-content']);   
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $home_page_welcome_title = '';
            if( isset($settings['page-home-title']) && $settings['page-home-title'] != '' ) {
                $home_page_welcome_title = '<h1 class="entry-title">' . $settings['page-home-title'] . '</h1>';
            }
            $home_page_welcome = "<div class='block-content'>" . $rc['content'] . "</div>";
        } else {
            $home_page_welcome_title = '';
            if( isset($settings['page-home-title']) && $settings['page-home-title'] != '' ) {
                $home_page_welcome_title = '<h1 class="entry-title">' . $settings['page-home-title'] . '</h1>';
            }
            $home_page_welcome = "<div class='block-content'>" . $rc['content']['page-home-content'] . "</div>";
        }
    } else {
        $home_page_welcome_title = '';
        $home_page_welcome = '';
    }

    //
    // Determine if the collections should be displayed on the home page
    //
    $home_page_collections = '';
    $home_page_collections_title = '';
    if( isset($settings['page-home-collections-display']) 
        && $settings['page-home-collections-display'] == 'yes' ) {
        if( isset($settings['page-home-collections-title']) ) {
            $home_page_collections_title = $settings['page-home-collections-title'];
        } else {
            $home_page_collections_title = 'Collections';
        }
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'collectionList');
        $rc = ciniki_web_collectionList($ciniki, $ciniki['request']['tnid']);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['collections']) && count($rc['collections']) > 0 ) {
            foreach($rc['collections'] as $collection) {
                $home_page_collections .= '<div class="button-list-wrap">';
                $home_page_collections .= '<div class="button-list-button">';
                $home_page_collections .= '<a alt="' . $collection['name'] . '" title="' . $collection['name'] . '" href="' . $ciniki['request']['base_url'] . '/collection/' . $collection['permalink'] . '"><span>' . $collection['name'] . '</span></a>';
                $home_page_collections .= '</div>';
                $home_page_collections .= '</div>';
            }
        }
    }

    //
    // Determine if there are home page quick links
    //
    $home_page_quicklinks = '';
    $home_page_quicklinks_title = '';
    if( isset($ciniki['tenant']['modules']['ciniki.web']['flags'])
        && ($ciniki['tenant']['modules']['ciniki.web']['flags']&0x10) == 0x10 ) {
        if( isset($settings['page-home-quicklinks-title']) ) {
            $home_page_quicklinks_title = $settings['page-home-quicklinks-title'];
        }
        for($i=0;$i<10;$i++) {
            $name = sprintf('page-home-quicklinks-%03d-name', $i);
            $url = sprintf('page-home-quicklinks-%03d-url', $i);
            if( isset($settings[$name]) && $settings[$name] != ''
                && isset($settings[$url]) && $settings[$url] != ''
                ) {
                $home_page_quicklinks .= '<div class="button-list-wrap">';
                $home_page_quicklinks .= '<div class="button-list-button">';
                if( $settings[$url][0] == '/' ) {
                    $home_page_quicklinks .= '<a href="' . $ciniki['request']['base_url'] . $settings[$url] . '"><span>' . $settings[$name] . '</span></a>';
                } else {
                    $home_page_quicklinks .= '<a href="' . $settings[$url] . '"><span>' . $settings[$name] . '</span></a>';
                }
                $home_page_quicklinks .= '</div>';
                $home_page_quicklinks .= '</div>';
            }
        }
    }
    
    //
    // Check if there is an image to display
    //
    $home_page_wide_image = '';
    $home_page_aside_image = '';
    //
    // 2 images
    //
    if( isset($settings['page-home-image']) && $settings['page-home-image'] != '' && $settings['page-home-image'] > 0 
        && isset($settings['page-home-image2']) && $settings['page-home-image2'] != '' && $settings['page-home-image2'] > 0 
        ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
        $rc = ciniki_web_getScaledImageURL($ciniki, $settings['page-home-image'], 'original', '500', 0);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $url = $rc['url'];
        $href = '';
        $_href = '';
        if( isset($settings['page-home-image-url']) && $settings['page-home-image-url'] != '' ) {
            $href = "<a href='" . $settings['page-home-image-url'] . "'>";
            $_href = "</a>";
        }
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
        $rc = ciniki_web_getScaledImageURL($ciniki, $settings['page-home-image2'], 'original', '500', 0);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $url2 = $rc['url'];
        $href2 = '';
        $_href2 = '';
        if( isset($settings['page-home-image2-url']) && $settings['page-home-image2-url'] != '' ) {
            $href2 = "<a href='" . $settings['page-home-image2-url'] . "'>";
            $_href2 = "</a>";
        }
        //
        // Setup the image for wide format
        //
        $home_page_wide_image .= "<div class='wide aligncenter'>";
        $home_page_wide_image .= "<div class='image'>$href<img title='' alt='" . $ciniki['tenant']['details']['name'] . "' src='" . $rc['url'] . "' />$_href</div>";
        $home_page_wide_image .= "</div>";
        if( isset($settings['page-home-image-caption']) && $settings['page-home-image-caption'] != '' ) {
            $home_page_wide_image .= "<div class='image-caption aligncenter'>$href" . $settings['page-home-image-caption'] . "$_href</div>";
        }
        //
        // Setup the image for the aside
        //
        $home_page_aside_image .= "<aside>";
        // 1st image
        $home_page_aside_image .= "<div class='block block-primary-image'>";
        $home_page_aside_image .= "<div class='image-wrap'>"
            . "<div class='image'>$href<img title='' alt='" . $ciniki['tenant']['details']['name'] . "' src='" . $url . "' />$_href</div>";
        if( $ciniki['response']['head']['og']['image'] == '' ) {
            $ciniki['response']['head']['og']['image'] = $rc['domain_url'];
        }
        if( isset($settings['page-home-image-caption']) && $settings['page-home-image-caption'] != '' ) {
            $home_page_aside_image .= "<div class='image-caption'>$href" . $settings['page-home-image-caption'] . "$_href</div>";
        }
        $home_page_aside_image .= "</div>";
        // 2nd image
        $home_page_aside_image .= "<div class='image-wrap'>"
            . "<div class='image'>$href2<img title='' alt='" . $ciniki['tenant']['details']['name'] . "' src='" . $url2 . "' />$_href2</div>";
        if( isset($settings['page-home-image2-caption']) && $settings['page-home-image2-caption'] != '' ) {
            $home_page_aside_image .= "<div class='image-caption'>$href2" . $settings['page-home-image2-caption'] . "$_href2</div>";
        }
        $home_page_aside_image .= "</div>";
        $home_page_aside_image .= "</div>";
        $home_page_aside_image .= "</aside>";

    }
    //
    // 1 image
    //
    elseif( isset($settings['page-home-image']) 
        && $settings['page-home-image'] != '' && $settings['page-home-image'] > 0 
        ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
        $rc = ciniki_web_getScaledImageURL($ciniki, $settings['page-home-image'], 'original', '500', 0);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $href = '';
        $_href = '';
        if( isset($settings['page-home-image-url']) && $settings['page-home-image-url'] != '' ) {
            $href = "<a href='" . $settings['page-home-image-url'] . "'>";
            $_href = "</a>";
        }
        //
        // Setup the image for wine format
        //
        $home_page_wide_image .= "<div class='wide aligncenter'>";
        $home_page_wide_image .= "<div class='image'>$href<img title='' alt='" . $ciniki['tenant']['details']['name'] . "' src='" . $rc['url'] . "' />$_href</div>";
        $home_page_wide_image .= "</div>";
        if( isset($settings['page-home-image-caption']) && $settings['page-home-image-caption'] != '' ) {
            $home_page_wide_image .= "<div class='image-caption aligncenter'>$href" . $settings['page-home-image-caption'] . "$_href</div>";
        }
        //
        // Setup the image for the aside
        //
        $home_page_aside_image .= "<aside><div class='block block-primary-image'><div class='image-wrap'>"
            . "<div class='image'>$href<img title='' alt='" . $ciniki['tenant']['details']['name'] . "' src='" . $rc['url'] . "' />$_href</div>";
        if( $ciniki['response']['head']['og']['image'] == '' ) {
            $ciniki['response']['head']['og']['image'] = $rc['domain_url'];
        }
        if( isset($settings['page-home-image-caption']) && $settings['page-home-image-caption'] != '' ) {
            $home_page_aside_image .= "<div class='image-caption'>$href" . $settings['page-home-image-caption'] . "$_href</div>";
        }
        $home_page_aside_image .= "</div></div></aside>";
    } 
    
    //
    // Decide how the content should layout
    //
    if( $home_page_collections_title != '' ) {
        $home_page_collections_title = '<h1>' . $home_page_collections_title . '</h1>';
    }
    if( $home_page_quicklinks_title != '' ) {
        $home_page_quicklinks_title = '<h1>' . $home_page_quicklinks_title . '</h1>';
    }
    if( $home_page_welcome != '' && $home_page_collections != '' && $home_page_aside_image != '' ) {
        $content1 .= $home_page_collections_title;
        $content1 .= '<div class="button-list">' . $home_page_collections . '</div>';
        $content1 .= '<br style="clear: right;"/>';
        $content1 .= $home_page_welcome_title . $home_page_aside_image . $home_page_welcome;
    } elseif( $home_page_welcome != '' && $home_page_quicklinks != '' && $home_page_aside_image != '' ) {
        $content1 .= $home_page_quicklinks_title;
        $content1 .= '<div class="button-list">' . $home_page_quicklinks . '</div>';
        $content1 .= '<br style="clear: right;"/>';
        $content1 .= $home_page_welcome_title . $home_page_aside_image . $home_page_welcome;
    } elseif( $home_page_welcome != '' && $home_page_collections != '' ) {
        $content1 .= '<aside>' . $home_page_collections_title
            . '<div class="largebutton-list">' . $home_page_collections . '</div></aside>';
        $content1 .= $home_page_welcome_title . $home_page_welcome;
    } elseif( $home_page_welcome != '' && $home_page_quicklinks != '' ) {
        $content1 .= '<aside>' . $home_page_quicklinks_title
            . '<div class="largebutton-list">' . $home_page_quicklinks . '</div></aside>';
        $content1 .= $home_page_welcome_title . $home_page_welcome;
    } elseif( $home_page_welcome != '' && $home_page_aside_image != '' ) {
        $content1 .= $home_page_welcome_title . $home_page_aside_image . $home_page_welcome;
    } elseif( $home_page_collections != '' && $home_page_aside_image != '' ) {
        $content1 .= $home_page_aside_image;
        $content1 .= $home_page_collections_title;
        $content1 .= '<div class="largebutton-list">' . $home_page_collections . '</div>';
    } elseif( $home_page_quicklinks != '' && $home_page_aside_image != '' ) {
        $content1 .= $home_page_aside_image;
        $content1 .= $home_page_quicklinks_title;
        $content1 .= '<div class="largebutton-list">' . $home_page_quicklinks . '</div>';
    } elseif( $home_page_welcome != '' ) {
        $content1 .= $home_page_welcome_title . $home_page_welcome;
    } elseif( $home_page_collections != '' ) {
        $content1 .= $home_page_collections_title;
        $content1 .= '<div class="button-list">' . $home_page_collections . '</div>';
    } elseif( $home_page_quicklinks != '' ) {
        $content1 .= $home_page_quicklinks_title;
        $content1 .= '<div class="wide aligncenter"><div class="button-list">' . $home_page_quicklinks . '</div></div>';
    } elseif( $home_page_wide_image != '' ) {
        $content1 .= $home_page_wide_image;
    }
        
    if( $content1 != '' 
        && (!isset($settings['page-home-content-sequence']) || $settings['page-home-content-sequence'] == '' || $settings['page-home-content-sequence'] < 2)
        ) {
        $page_content .= "<article class='page page-home page-home-content'>\n"
//          . "<header><h1></h1></header>\n"
            . "<div class='entry-content'>\n"
            . $content1
            . "</div>"
            . "<br style='clear:both;'/>"
            . "</article>"
            . "";
    }

    //
    // List the latest work
    //
    if( isset($ciniki['tenant']['modules']['ciniki.artcatalog']) 
        && isset($settings['page-gallery-active']) && $settings['page-gallery-active'] == 'yes' 
        && (!isset($settings['page-home-gallery-latest']) || $settings['page-home-gallery-latest'] == 'yes') 
        ) {

        ciniki_core_loadMethod($ciniki, 'ciniki', 'artcatalog', 'web', 'latestImages');
        $rc = ciniki_artcatalog_web_latestImages($ciniki, $settings, $ciniki['request']['tnid'], 6);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $images = $rc['images'];

        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageGalleryThumbnails');
        $img_base_url = $ciniki['request']['base_url'] . "/gallery/latest";
        $rc = ciniki_web_generatePageGalleryThumbnails($ciniki, $settings, $img_base_url, $rc['images'], $thumbnail_width);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $page_content .= "<article class='page page-home-latestwork'>\n"
            . "<header class='entry-title'><h1 class='entry-title'>";
        if( isset($settings['page-home-gallery-latest-title']) && $settings['page-home-gallery-latest-title'] != '' ) {
            $page_content .= $settings['page-home-gallery-latest-title'];
        } else {
            $page_content .= "Latest Work";
        }
        $page_content .= "</h1></header>\n"
            . "<div class='image-gallery'>" . $rc['content'] . "</div>"
            . "</article>\n"
            . "";
    }

    //
    // List the random gallery images
    //
    if( isset($ciniki['tenant']['modules']['ciniki.artcatalog']) 
        && isset($settings['page-gallery-active']) && $settings['page-gallery-active'] == 'yes' 
        && isset($settings['page-home-gallery-random']) && $settings['page-home-gallery-random'] == 'yes' 
        ) {

        ciniki_core_loadMethod($ciniki, 'ciniki', 'artcatalog', 'web', 'randomImages');
        $rc = ciniki_artcatalog_web_randomImages($ciniki, $settings, $ciniki['request']['tnid'], 6);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $images = $rc['images'];

        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageGalleryThumbnails');
        $img_base_url = $ciniki['request']['base_url'] . "/gallery/image";
        $rc = ciniki_web_generatePageGalleryThumbnails($ciniki, $settings, $img_base_url, $rc['images'], $thumbnail_width);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $page_content .= "<article class='page'>\n"
            . "<header class='entry-title'><h1 class='entry-title'>";
        if( isset($settings['page-home-gallery-random-title']) && $settings['page-home-gallery-random-title'] != '' ) {
            $page_content .= $settings['page-home-gallery-random-title'];
        } else {
            $page_content .= 'Example Work';
        }
        $page_content .= "</h1></header>\n"
            . "<div class='image-gallery'>" . $rc['content'] . "</div>"
            . "</article>\n"
            . "";
    }

    //
    // List the blog entries
    //
    if( isset($ciniki['tenant']['modules']['ciniki.blog']) 
//        && isset($settings['page-blog-active']) && $settings['page-blog-active'] == 'yes' 
        && (!isset($settings['page-home-latest-blog']) || $settings['page-home-latest-blog'] == 'yes') 
        ) {
        $list_size = 2;
        if( isset($settings['page-home-latest-blog-number']) 
            && $settings['page-home-latest-blog-number'] > 0 ) {
            $list_size = $settings['page-home-latest-blog-number'];
        }
        //
        // Get the blog url
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'indexModuleBaseURL');
        $rc = ciniki_web_indexModuleBaseURL($ciniki, $ciniki['request']['tnid'], ['ciniki.blog.latest', 'ciniki.blog']);
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.183', 'msg'=>'Unable to find page', 'err'=>$rc['err']));
        }
        if( isset($rc['base_url']) && $rc['base_url'] != '' ) {
            $base_url = $ciniki['request']['base_url'] . $rc['base_url'];
        } else {
            $base_url = $ciniki['request']['base_url'] . "/blog";
        }

        if( isset($settings['site-theme']) && $settings['site-theme'] == 'twentyone' ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'blog', 'web', 'posts');
            $rc = ciniki_blog_web_posts($ciniki, $settings, $ciniki['request']['tnid'], array('latest'=>'yes', 'limit'=>$list_size+1), 'public');
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $posts = $rc['posts'];
            if( count($posts) > 0 ) {
                ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processBlocks');
                $rc = ciniki_web_processBlocks($ciniki, $settings, $ciniki['request']['tnid'], array(
                    array('type'=>'imagelist', 
                        'section'=>'blog',
                        'noimage'=>'yes',
                        'base_url'=>$base_url,
                        'list'=>$posts,
                        'limit'=>$list_size,
                        ),
                    ));
                if( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.186', 'msg'=>'', 'err'=>$rc['err']));
                }
                $page_content .= "<article class='page page-home page-home-blog'>\n"
                    . "<header class='entry-title'><h1 class='entry-title'>";
                if( isset($settings['page-home-latest-blog-title']) && $settings['page-home-latest-blog-title'] != '' ) {
                    $page_content .= $settings['page-home-latest-blog-title'];
                } else {
                    $page_content .= "Latest Blog Posts";
                }
                $page_content .= "</h1></header>\n"
                    . "<div class='entry-content'>"
                    . $rc['content']
                    . "";
                if( count($posts) > $list_size ) {
                    $page_content .= "<div class='entry-content'>";
                    $page_content .= "<div class='home-more'><a href='" . $base_url . "'>";
                    if( isset($settings['page-home-latest-blog-more']) 
                        && $settings['page-home-latest-blog-more'] != '' ) {
                        $page_content .= $settings['page-home-latest-blog-more'];
                    } else {
                        $page_content .= "... more blog posts";
                    }
                    $page_content .= "</a></div>";
                    $page_content .= "</div>";
                }
                $page_content .= "</div>";
                $page_content .= "</article>\n"
                    . "";
            }
        } else {
            //
            // Load and parse the blog entries
            //
            ciniki_core_loadMethod($ciniki, 'ciniki', 'blog', 'web', 'ciListPosts');
            $rc = ciniki_blog_web_ciListPosts($ciniki, $settings, $ciniki['request']['tnid'], 
                array('latest'=>'yes', 'limit'=>$list_size+1), '');
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            if( isset($rc['posts']) && count($rc['posts']) > 0 ) {
                $posts = $rc['posts'];

                ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processCIList');
                $rc = ciniki_web_processCIList($ciniki, $settings, $base_url, $posts, 
                    array('limit'=>$list_size,
                        'base_url'=>$base_url,
                        'thumbnail_format'=>(isset($settings['page-blog-thumbnail-format'])&&$settings['page-blog-thumbnail-format']!=''?$settings['page-blog-thumbnail-format']:'square-cropped'),
                        'thumbnail_padding_color'=>(isset($settings['page-blog-thumbnail-padding-color'])&&$settings['page-blog-thumbnail-padding-color']!=''?$settings['page-blog-thumbnail-padding-color']:'#ffffff'),
                    ));
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
                $num_posts = $rc['count'];
                $page_content .= "<article class='page'>\n"
                    . "<header class='entry-title'><h1 class='entry-title'>";
                if( isset($settings['page-home-latest-blog-title']) && $settings['page-home-latest-blog-title'] != '' ) {
                    $page_content .= $settings['page-home-latest-blog-title'];
                } else {
                    $page_content .= "Latest Blog Posts";
                }
                $page_content .= "</h1></header>\n"
                    . $rc['content']
                    . "";
                if( $num_posts > $list_size ) {
                    $page_content .= "<div class='entry-content'>";
                    $page_content .= "<div class='cilist-more'><a href='" . $base_url . "'>";
                    if( isset($settings['page-home-latest-blog-more']) 
                        && $settings['page-home-latest-blog-more'] != '' ) {
                        $page_content .= $settings['page-home-latest-blog-more'];
                    } else {
                        $page_content .= "... more blog posts";
                    }
                    $page_content .= "</a></div>";
                    $page_content .= "</div>";
                }
                $page_content .= "</article>\n"
                    . "";
            }
        }
    }

    //
    // List the new products
    //
    if( isset($ciniki['tenant']['modules']['ciniki.products']) 
        && isset($settings['page-products-active']) && $settings['page-products-active'] == 'yes' 
        && (!isset($settings['page-home-products-latest']) || $settings['page-home-products-latest'] == 'yes') 
        ) {
        $list_size = 2;
        if( isset($settings['page-home-products-latest-number']) 
            && $settings['page-home-products-latest-number'] > 0 ) {
            $list_size = $settings['page-home-products-latest-number'];
        }
        ciniki_core_loadMethod($ciniki, 'ciniki', 'products', 'web', 'newProducts');
        $rc = ciniki_products_web_newProducts($ciniki, $settings, $ciniki['request']['tnid'], $list_size);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }

        if( isset($rc['products']) && count($rc['products']) > 0 ) {
            $products = $rc['products'];
            $base_url = $ciniki['request']['base_url'] . "/products";
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processCIList');
            $rc = ciniki_web_processCIList($ciniki, $settings, $base_url, array('0'=>array(
                'name'=>'', 'noimage'=>'/ciniki-web-layouts/default/img/noimage_240.png',
                'list'=>$products)), array('limit'=>$list_size));
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $num_products = $rc['count'];
            $page_content .= "<article class='page'>\n"
                . "<header class='entry-title'><h1 class='entry-title'>";
            if( isset($settings['page-home-products-latest-title']) 
                && $settings['page-home-products-latest-title'] != '' 
                ) {
                $page_content .= $settings['page-home-products-latest-title'];
            } else {
                $page_content .= "New Products";
            }
            $page_content .= "</h1></header>\n"
                . $rc['content']
                . "";
            if( $num_products > $list_size ) {
                $page_content .= "<div class='cilist-more'><a href='" . $ciniki['request']['base_url'] . "/products'>";
                if( isset($settings['page-home-products-latest-more']) 
                    && $settings['page-home-products-latest-more'] != '' ) {
                    $page_content .= $settings['page-home-products-latest-more'];
                } else {
                    $page_content .= "... more products";
                }
                $page_content .= "</a></div>";
            }
            $page_content .= "</article>\n"
                . "";
        }
    }

    //
    // List any current exhibitions
    //
    $more_exhibitions = '';
    if( isset($ciniki['tenant']['modules']['ciniki.artgallery']) 
        && isset($settings['page-artgalleryexhibitions-active']) && $settings['page-artgalleryexhibitions-active'] == 'yes' 
        && (!isset($settings['page-home-current-artgalleryexhibitions']) || $settings['page-home-current-artgalleryexhibitions'] == 'yes') 
        ) {
        $list_size = 2;
        if( isset($settings['page-home-current-artgalleryexhibitions-number']) 
            && $settings['page-home-current-artgalleryexhibitions-number'] > 0 ) {
            $list_size = $settings['page-home-current-artgalleryexhibitions-number'];
        }
        //
        // Load and parse the events
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'artgallery', 'web', 'exhibitionList');
        $rc = ciniki_artgallery_web_exhibitionList($ciniki, $settings, $ciniki['request']['tnid'], 
            array('type'=>'current', 'limit'=>($list_size+1)));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $number_of_exhibitions = count($rc['exhibitions']);
        if( isset($rc['exhibitions']) && $number_of_exhibitions > 0 ) {
            $exhibitions = $rc['exhibitions'];
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processExhibitions');
            $rc = ciniki_web_processExhibitions($ciniki, $settings, $exhibitions, array('limit'=>$list_size));
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $page_content .= "<article class='page page-home-exhibitions'>\n"
                . "<header class='entry-title'><h1 class='entry-title'>";
            if( isset($settings['page-home-current-artgalleryexhibitions-title']) 
                && $settings['page-home-current-artgalleryexhibitions-title'] != '' ) {
                $page_content .= $settings['page-home-current-artgalleryexhibitions-title'];
            } else {
                if( $number_of_exhibitions > 1 ) {
                    $page_content .= "Current Exhibitions";
                } else {
                    $page_content .= "Current Exhibition";
                }
            }
            $page_content .= "</h1></header>\n"
                . $rc['content']
                . "";
            if( $number_of_exhibitions > $list_size ) {
                $more_exhibitions = "<div class='cilist-more'><a href='" . $ciniki['request']['base_url'] . "/exhibitions'>";
                if( isset($settings['page-home-current-artgalleryexhibitions-more']) 
                    && $settings['page-home-current-artgalleryexhibitions-more'] != '' ) {
                    $more_exhibitions .= $settings['page-home-current-artgalleryexhibitions-more'];
                } else {
                    $more_exhibitions .= "... more exhibitions";
                }
                $more_exhibitions .= "</a></div>";
//              $page_content .= "<div class='cilist-more'><a href='" . $ciniki['request']['base_url'] . "/exhibitions'>... more exhibitions</a></div>";
            }
            $page_content .= "</article>\n"
                . "";
        }
    }

    //
    // List any upcoming exhibitions
    //
    if( isset($ciniki['tenant']['modules']['ciniki.artgallery']) 
        && isset($settings['page-artgalleryexhibitions-active']) && $settings['page-artgalleryexhibitions-active'] == 'yes' 
        && (!isset($settings['page-home-upcoming-artgalleryexhibitions']) || $settings['page-home-upcoming-artgalleryexhibitions'] == 'yes') 
        ) {
        $list_size = 2;
        if( isset($settings['page-home-upcoming-artgalleryexhibitions-number']) 
            && $settings['page-home-upcoming-artgalleryexhibitions-number'] > 0 ) {
            $list_size = $settings['page-home-upcoming-artgalleryexhibitions-number'];
        }
        //
        // Load and parse the exhibitions
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'artgallery', 'web', 'exhibitionList');
        $rc = ciniki_artgallery_web_exhibitionList($ciniki, $settings, $ciniki['request']['tnid'], 
            array('type'=>'upcoming', 'limit'=>($list_size+1)));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $number_of_exhibitions = count($rc['exhibitions']);
        if( isset($rc['exhibitions']) && $number_of_exhibitions > 0 ) {
            $exhibitions = $rc['exhibitions'];
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processExhibitions');
            $rc = ciniki_web_processExhibitions($ciniki, $settings, $exhibitions, array('limit'=>$list_size));
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $page_content .= "<article class='page page-home-artgallery'>\n"
                . "<header class='entry-title'><h1 class='entry-title'>";
            if( isset($settings['page-home-upcoming-artgalleryexhibitions-title']) 
                && $settings['page-home-upcoming-artgalleryexhibitions-title'] != '' ) {
                $page_content .= $settings['page-home-upcoming-artgalleryexhibitions-title'];
            } else {
                $page_content .= "Upcoming Exhibitions";
            }
            $page_content .= "</h1></header>\n"
                . $rc['content']
                . "";
            if( $number_of_exhibitions > $list_size ) {
                $more_exhibitions = "<div class='cilist-more'><a href='" . $ciniki['request']['base_url'] . "/exhibitions'>";
                if( isset($settings['page-home-upcoming-artgalleryexhibitions-more']) 
                    && $settings['page-home-upcoming-artgalleryexhibitions-more'] != '' ) {
                    $more_exhibitions .= $settings['page-home-upcoming-artgalleryexhibitions-more'];
                } else {
                    $more_exhibitions .= "... more exhibitions";
                }
                $more_exhibitions .= "</a></div>";
//              $page_content .= "<div class='cilist-more'><a href='" . $ciniki['request']['base_url'] . "/exhibitions'>... more exhibitions</a></div>";
            }
            $page_content .= "</article>\n"
                . "";
        }
    }
    if( $more_exhibitions != '' ) {
        $page_content .= $more_exhibitions;
    }

    //
    // List the latest recipes
    //
    if( isset($ciniki['tenant']['modules']['ciniki.recipes']) 
        && isset($settings['page-recipes-active']) && $settings['page-recipes-active'] == 'yes' 
        && (!isset($settings['page-home-recipes-latest']) || $settings['page-home-recipes-latest'] == 'yes') 
        ) {
        $list_size = 2;
        if( isset($settings['page-home-recipes-latest-number']) 
            && $settings['page-home-recipes-latest-number'] > 0 ) {
            $list_size = $settings['page-home-recipes-latest-number'];
        }
        //
        // Load and parse the recipes
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'recipes', 'web', 'latest');
        $rc = ciniki_recipes_web_latest($ciniki, $settings, $ciniki['request']['tnid'], $list_size+1);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $number_of_recipes = count($rc['recipes']);
        if( isset($rc['recipes']) && $number_of_recipes > 0 ) {
            $recipes = $rc['recipes'];
            $base_url = $ciniki['request']['base_url'] . "/recipes";
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processCIList');
            $rc = ciniki_web_processCIList($ciniki, $settings, $base_url, array('0'=>array(
                'name'=>'', 'noimage'=>'/ciniki-web-layouts/default/img/noimage_240.png',
                'list'=>$recipes)), array('limit'=>$list_size));
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $page_content .= "<article class='page page-home-recipes'>\n"
                . "<header class='entry-title'><h1 class='entry-title'>";
            if( isset($settings['page-home-recipes-latest-title']) 
                && $settings['page-home-recipes-latest-title'] != '' ) {
                $page_content .= $settings['page-home-recipes-latest-title'];
            } else {
                $page_content .= "Latest Recipes";
            }
            $page_content .= "</h1></header>\n"
                . $rc['content']
                . "";
            if( $number_of_recipes > $list_size ) {
                $page_content .= "<div class='cilist-more'><a href='" . $ciniki['request']['base_url'] . "/recipes'>";
                if( isset($settings['page-home-recipes-latest-more']) 
                    && $settings['page-home-recipes-latest-more'] != '' ) {
                    $page_content .= $settings['page-home-recipes-latest-more'];
                } else {
                    $page_content .= "... more recipes";
                }
                $page_content .= "</a></div>";
//              $page_content .= "<div class='cilist-more'><a href='" . $ciniki['request']['base_url'] . "/recipes'>... more recipes</a></div>";
            }
            $page_content .= "</article>\n"
                . "";
        }
    }


    //
    // List any upcoming workshops
    //
    if( isset($ciniki['tenant']['modules']['ciniki.workshops']) 
        && isset($settings['page-workshops-active']) && $settings['page-workshops-active'] == 'yes' 
        && (!isset($settings['page-home-upcoming-workshops']) || $settings['page-home-upcoming-workshops'] == 'yes') 
        ) {
        $list_size = 2;
        if( isset($settings['page-home-upcoming-workshops-number']) 
            && $settings['page-home-upcoming-workshops-number'] > 0 ) {
            $list_size = $settings['page-home-upcoming-workshops-number'];
        }
        //
        // Load and parse the workshops
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'workshops', 'web', 'workshopList');
        $rc = ciniki_workshops_web_workshopList($ciniki, $settings, $ciniki['request']['tnid'], 'upcoming', $list_size+1);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $number_of_workshops = count($rc['workshops']);
        if( isset($rc['workshops']) && $number_of_workshops > 0 ) {
            $workshops = $rc['workshops'];
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processWorkshops');
            $rc = ciniki_web_processWorkshops($ciniki, $settings, $workshops, $list_size);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $page_content .= "<article class='page page-home-workshops'>\n"
                . "<header class='entry-title'><h1 class='entry-title'>";
            if( isset($settings['page-home-upcoming-workshops-title']) 
                && $settings['page-home-upcoming-workshops-title'] != '' ) {
                $page_content .= $settings['page-home-upcoming-workshops-title'];
            } else {
                $page_content .= "Upcoming Workshops";
            }
            $page_content .= "</h1></header>\n"
                . $rc['content']
                . "";
            if( $number_of_workshops > $list_size ) {
                $page_content .= "<div class='cilist-more'><a href='" . $ciniki['request']['base_url'] . "/workshops'>";
                if( isset($settings['page-home-latest-workshops-more']) 
                    && $settings['page-home-latest-workshops-more'] != '' ) {
                    $page_content .= $settings['page-home-latest-workshops-more'];
                } else {
                    $page_content .= "... more workshops";
                }
                $page_content .= "</a></div>";
//              $page_content .= "<div class='cilist-more'><a href='" . $ciniki['request']['base_url'] . "/workshops'>... more workshops</a></div>";
            }
            $page_content .= "</article>\n"
                . "";
        }
    }

    //
    // List any upcoming film schedule
    //
    if( isset($ciniki['tenant']['modules']['ciniki.filmschedule']) 
        && isset($settings['page-filmschedule-active']) && $settings['page-filmschedule-active'] == 'yes' 
        && (!isset($settings['page-home-upcoming-filmschedule']) || $settings['page-home-upcoming-filmschedule'] == 'yes') 
        ) {
        $list_size = 2;
        if( isset($settings['page-home-upcoming-filmschedule-number']) 
            && $settings['page-home-upcoming-filmschedule-number'] > 0 ) {
            $list_size = $settings['page-home-upcoming-filmschedule-number'];
        }
        //
        // Load and parse the filmschedule
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'filmschedule', 'web', 'eventList');
        $rc = ciniki_filmschedule_web_eventList($ciniki, $settings, $ciniki['request']['tnid'], array('type'=>'upcoming', 'limit'=>$list_size+1));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $number_of_events = count($rc['events']);
        if( isset($rc['events']) && $number_of_events > 0 ) {
            $events = $rc['events'];
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processFilmScheduleEvents');
            $rc = ciniki_web_processFilmScheduleEvents($ciniki, $settings, $events, $list_size);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $page_content .= "<article class='page page-home page-home-filmschedule'>\n"
                . "<header class='entry-title'><h1 class='entry-title'>";
            if( isset($settings['page-home-upcoming-filmschedule-title']) && $settings['page-home-upcoming-filmschedule-title'] != '' ) {
                $page_content .= $settings['page-home-upcoming-filmschedule-title'];
            } else {
                $page_content .= "Upcoming Films";
            }
            $page_content .= "</h1></header>\n"
                . $rc['content']
                . "";
            if( $number_of_events > $list_size ) {
                $page_content .= "<div class='cilist-more'><a href='" . $ciniki['request']['base_url'] . "/schedule'>";
                if( isset($settings['page-home-upcoming-filmschedule-more']) && $settings['page-home-upcoming-filmschedule-more'] != '' ) {
                    $page_content .= $settings['page-home-upcoming-filmschedule-more'];
                } else {
                    $page_content .= "... more films";
                }
                $page_content .= "</a></div>";
            }
            $page_content .= "</article>\n"
                . "";
        }
    }

    //
    // List any current events
    //
    if( isset($ciniki['tenant']['modules']['ciniki.events']) 
//        && isset($settings['page-events-active']) && $settings['page-events-active'] == 'yes' 
        && isset($settings['page-home-current-events']) && $settings['page-home-current-events'] == 'yes'
        ) {
        $list_size = 2;
        if( isset($settings['page-home-current-events-number']) 
            && $settings['page-home-current-events-number'] > 0 ) {
            $list_size = $settings['page-home-current-events-number'];
        }
        // Load the base url
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'indexModuleBaseURL');
        $rc = ciniki_web_indexModuleBaseURL($ciniki, $ciniki['request']['tnid'], ['ciniki.events.upcoming', 'ciniki.events']);
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.183', 'msg'=>'Unable to find page', 'err'=>$rc['err']));
        }
        if( isset($rc['base_url']) && $rc['base_url'] != '' ) {
            $base_url = $ciniki['request']['base_url'] . $rc['base_url'];
        } else {
            $base_url = $ciniki['request']['base_url'] . "/events";
        }
        if( isset($settings['site-theme']) && $settings['site-theme'] == 'twentyone' ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'events', 'web', 'list');
            $rc = ciniki_events_web_list($ciniki, $settings, $ciniki['request']['tnid'], array('type'=>'current','limit'=>$list_size+1, 'format'=>'imagelist'));
            $events = isset($rc['events']) ? $rc['events'] : array();
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processBlocks');
            $rc = ciniki_web_processBlocks($ciniki, $settings, $ciniki['request']['tnid'], array(
                array('type'=>'imagelist', 
                    'section'=>'events',
                    'noimage'=>'yes',
                    'base_url'=>$base_url,
                    'list'=>$events,
                    'limit'=>$list_size,
                    ),
                ));
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.186', 'msg'=>'', 'err'=>$rc['err']));
            }
            if( count($events) > 0 ) {
                $page_content .= "<article class='page page-home page-home-events'>\n"
                    . "<header class='entry-title'><h1 class='entry-title'>";
                if( isset($settings['page-home-current-events-title']) && $settings['page-home-current-events-title'] != '' ) {
                    $page_content .= $settings['page-home-current-events-title'];
                } else {
                    $page_content .= "Current Events";
                }
                $page_content .= "</h1></header>\n"
                    . "<div class='entry-content'>"
                    . $rc['content']
                    . "";
                if( count($events) > $list_size ) {
                    $page_content .= "<div class='home-more'><a href='" . $base_url . "'>";
                    if( isset($settings['page-home-current-events-more']) && $settings['page-home-current-events-more'] != '' ) {
                        $page_content .= $settings['page-home-current-events-more'];
                    } else {
                        $page_content .= "... more events";
                    }
                    $page_content .= "</a></div>";
    //              $page_content .= "<div class='cilist-more'><a href='" . $ciniki['request']['base_url'] . "/events'>... more events</a></div>";
                }
                $page_content .= "</div>";
                $page_content .= "</article>\n"
                    . "";
            }
        } else {
            //
            // Load and parse the events
            //
            ciniki_core_loadMethod($ciniki, 'ciniki', 'events', 'web', 'eventList');
            $rc = ciniki_events_web_eventList($ciniki, $settings, $ciniki['request']['tnid'], array('type'=>'current', 'limit'=>$list_size+1));
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $number_of_events = count($rc['events']);
            if( isset($rc['events']) && $number_of_events > 0 ) {
                $events = $rc['events'];
                ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processEvents');
                $rc = ciniki_web_processEvents($ciniki, $settings, $events, $list_size, $base_url);
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
                $page_content .= "<article class='page page-home page-home-events'>\n"
                    . "<header class='entry-title'><h1 class='entry-title'>";
                if( isset($settings['page-home-current-events-title']) && $settings['page-home-current-events-title'] != '' ) {
                    $page_content .= $settings['page-home-current-events-title'];
                } else {
                    $page_content .= "Current Events";
                }
                $page_content .= "</h1></header>\n"
                    . "<div class='entry-content'>"
                    . $rc['content']
                    . "";
                if( $number_of_events > $list_size ) {
                    $page_content .= "<div class='cilist-more'><a href='" . $base_url . "'>";
                    if( isset($settings['page-home-current-events-more']) && $settings['page-home-current-events-more'] != '' ) {
                        $page_content .= $settings['page-home-current-events-more'];
                    } else {
                        $page_content .= "... more events";
                    }
                    $page_content .= "</a></div>";
    //              $page_content .= "<div class='cilist-more'><a href='" . $ciniki['request']['base_url'] . "/events'>... more events</a></div>";
                }
                $page_content .= "</div>";
                $page_content .= "</article>\n"
                    . "";
            }
        }
    }

    //
    // List any upcoming events
    //
    if( isset($ciniki['tenant']['modules']['ciniki.events']) 
//        && isset($settings['page-events-active']) && $settings['page-events-active'] == 'yes' 
        && (!isset($settings['page-home-upcoming-events']) || $settings['page-home-upcoming-events'] == 'yes') 
        ) {
        $list_size = 2;
        if( isset($settings['page-home-upcoming-events-number']) 
            && $settings['page-home-upcoming-events-number'] > 0 ) {
            $list_size = $settings['page-home-upcoming-events-number'];
        }
        // Load the base url
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'indexModuleBaseURL');
        $rc = ciniki_web_indexModuleBaseURL($ciniki, $ciniki['request']['tnid'], ['ciniki.events.upcoming', 'ciniki.events']);
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.183', 'msg'=>'Unable to find page', 'err'=>$rc['err']));
        }
        if( isset($rc['base_url']) && $rc['base_url'] != '' ) {
            $base_url = $ciniki['request']['base_url'] . $rc['base_url'];
        } else {
            $base_url = $ciniki['request']['base_url'] . "/events";
        }
        if( isset($settings['site-theme']) && $settings['site-theme'] == 'twentyone' ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'events', 'web', 'list');
            if( isset($settings['page-home-current-events']) && $settings['page-home-current-events'] == 'yes' ) {
                $rc = ciniki_events_web_list($ciniki, $settings, $ciniki['request']['tnid'], array('limit'=>$list_size+1, 'format'=>'imagelist'));
            } else {
                $rc = ciniki_events_web_list($ciniki, $settings, $ciniki['request']['tnid'], array('type'=>'future','limit'=>$list_size+1, 'format'=>'imagelist'));
            }
            $events = isset($rc['events']) ? $rc['events'] : array();
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processBlocks');
            $rc = ciniki_web_processBlocks($ciniki, $settings, $ciniki['request']['tnid'], array(
                array('type'=>'imagelist', 
                    'section'=>'events',
                    'noimage'=>'yes',
                    'base_url'=>$base_url,
                    'list'=>$events,
                    ),
                ));
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.186', 'msg'=>'', 'err'=>$rc['err']));
            }
            if( count($events) > 0 ) {
                $page_content .= "<article class='page page-home page-home-events'>\n"
                    . "<header class='entry-title'><h1 class='entry-title'>";
                if( isset($settings['page-home-upcoming-events-title']) && $settings['page-home-upcoming-events-title'] != '' ) {
                    $page_content .= $settings['page-home-upcoming-events-title'];
                } else {
                    $page_content .= "Upcoming Events";
                }
                $page_content .= "</h1></header>\n"
                    . "<div class='entry-content'>"
                    . $rc['content']
                    . "";
                if( count($events) > $list_size ) {
                    $page_content .= "<div class='home-more'><a href='" . $base_url . "'>";
                    if( isset($settings['page-home-upcoming-events-more']) && $settings['page-home-upcoming-events-more'] != '' ) {
                        $page_content .= $settings['page-home-upcoming-events-more'];
                    } else {
                        $page_content .= "... more events";
                    }
                    $page_content .= "</a></div>";
    //              $page_content .= "<div class='cilist-more'><a href='" . $ciniki['request']['base_url'] . "/events'>... more events</a></div>";
                }
                $page_content .= "</div>";
                $page_content .= "</article>\n"
                    . "";
            }
        } else {
            //
            // Load and parse the events
            //
            ciniki_core_loadMethod($ciniki, 'ciniki', 'events', 'web', 'eventList');
            if( isset($settings['page-home-current-events']) && $settings['page-home-current-events'] == 'yes' ) {
                $rc = ciniki_events_web_eventList($ciniki, $settings, $ciniki['request']['tnid'], array('type'=>'future', 'limit'=>$list_size+1));
            } else {
                $rc = ciniki_events_web_eventList($ciniki, $settings, $ciniki['request']['tnid'], array('type'=>'upcoming', 'limit'=>$list_size+1));
            }
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $number_of_events = count($rc['events']);
            if( isset($rc['events']) && $number_of_events > 0 ) {
                $events = $rc['events'];
                ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processEvents');
                $rc = ciniki_web_processEvents($ciniki, $settings, $events, $list_size, $base_url);
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
                $page_content .= "<article class='page page-home page-home-events'>\n"
                    . "<header class='entry-title'><h1 class='entry-title'>";
                if( isset($settings['page-home-upcoming-events-title']) && $settings['page-home-upcoming-events-title'] != '' ) {
                    $page_content .= $settings['page-home-upcoming-events-title'];
                } else {
                    $page_content .= "Upcoming Events";
                }
                $page_content .= "</h1></header>\n"
                    . "<div class='entry-content'>"
                    . $rc['content']
                    . "";
                if( $number_of_events > $list_size ) {
                    $page_content .= "<div class='cilist-more'><a href='" . $base_url . "'>";
                    if( isset($settings['page-home-upcoming-events-more']) && $settings['page-home-upcoming-events-more'] != '' ) {
                        $page_content .= $settings['page-home-upcoming-events-more'];
                    } else {
                        $page_content .= "... more events";
                    }
                    $page_content .= "</a></div>";
    //              $page_content .= "<div class='cilist-more'><a href='" . $ciniki['request']['base_url'] . "/events'>... more events</a></div>";
                }
                $page_content .= "</div>";
                $page_content .= "</article>\n"
                    . "";
            }
        }
    }

    //
    // Show Book Covers
    //
    if( isset($ciniki['tenant']['modules']['ciniki.writingcatalog']) 
        && isset($settings['page-writings-active']) && $settings['page-writings-active'] == 'yes' 
        && (!isset($settings['page-home-writings-covers']) || $settings['page-home-writings-covers'] == 'yes') 
        ) {
        //
        // Load and parse the writing catalog
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'writingcatalog', 'web', 'writingCovers');
        $rc = ciniki_writingcatalog_web_writingCovers($ciniki, $settings, $ciniki['request']['tnid'], array());
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['categories']) ) {
            $page_content .= "<article class='page page-home page-home-writings'>\n"
                . "<header class='entry-title'><h1 class='entry-title'>";
            if( isset($settings['page-home-writings-covers-title']) && $settings['page-home-writings-covers-title'] != '' ) {
                $page_content .= $settings['page-home-writings-covers-title'];
            } else {
                $page_content .= "Writings";
            }
            $page_content .= "</h1></header>";
            $page_content .= "<div class='sponsor-gallery'>";
            $categories = $rc['categories'];
            foreach($categories as $cat) {
                if( isset($cat['list']) ) {
                    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processSponsorImages');
                    $rc = ciniki_web_processSponsorImages($ciniki, $settings, $ciniki['request']['base_url'] . '/writings', $cat['list'], '40');
                    if( $rc['stat'] != 'ok' ) {
                        return $rc;
                    }
                    $page_content .= $rc['content'];
                }
            }
            $page_content .= "</div>";
            $page_content .= "</article>\n";
        }
    }

    //
    // Check if content should be moved after other elements
    //
    if( $content1 != '' 
        && (isset($settings['page-home-content-sequence']) && $settings['page-home-content-sequence'] != '' && $settings['page-home-content-sequence'] > 1)
        ) {
        $page_content .= "<article class='page page-home'>\n"
//          . "<header><h1></h1></header>\n"
            . "<div class='entry-content'>\n"
            . $content1
            . "</div>"
            . "<br style='clear:both;'/>"
            . "</article>"
            . "";
    }

    //
    // Check if there are any sponsors
    //
    if( isset($ciniki['tenant']['modules']['ciniki.sponsors']) 
        && ($ciniki['tenant']['modules']['ciniki.sponsors']['flags']&0x02) == 0x02
        ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'sponsors', 'web', 'sponsorRefList');
        $rc = ciniki_sponsors_web_sponsorRefList($ciniki, $settings, $ciniki['request']['tnid'], 
            'ciniki.web.page', 'home');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['sponsors']) ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processSponsorsSection');
            $rc = ciniki_web_processSponsorsSection($ciniki, $settings, $rc['sponsors']);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $page_content .= "<article class='page page-home'>\n";
            $page_content .= $rc['content'];
            $page_content .= "</article>";
        }
    }

    $page_content .= "</div>"
        . "";

    //
    // Add the header
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageHeader');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processCIList');
    $rc = ciniki_web_generatePageHeader($ciniki, $settings, (isset($settings['page-home-seo-title'])&&$settings['page-home-seo-title']!=''?$settings['page-home-seo-title']:'Home'), array());
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }
    $content .= $rc['content'];

    $content .= $page_content;

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
