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
function ciniki_web_generatePageMembers($ciniki, $settings) {

    //
    // Check if a file was specified to be downloaded
    //
    $download_err = '';
    if( isset($ciniki['tenant']['modules']['ciniki.info'])
        && isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] == 'download'
        && isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] != '' 
        && isset($ciniki['request']['uri_split'][2]) && $ciniki['request']['uri_split'][2] != '' 
        ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'info', 'web', 'fileDownload');
        $rc = ciniki_info_web_fileDownload($ciniki, $ciniki['request']['tnid'], 
            $ciniki['request']['uri_split'][1], '',
            $ciniki['request']['uri_split'][2]);
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
        return array('stat'=>'404', 'err'=>array('code'=>'ciniki.web.71', 'msg'=>'We\'re sorry, but the file you requested does not exist.'));
    }

    //
    // Store the content created by the page
    // Make sure everything gets generated ok before returning the content
    //
    $content = '';
    $page_content = '';
    $add_membership_info = 'no';
    $page_title = 'Members';
    if( isset($settings['page-members-name']) && $settings['page-members-name'] != '' ) {
        $page_title = $settings['page-members-name'];
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
        if( isset($settings['page-members-list-format']) && $settings['page-members-list-format'] == 'thumbnail-list' ) {
            $rc = ciniki_customers_web_memberList($ciniki, $settings, $ciniki['request']['tnid'], array('category'=>$category_permalink, 'format'=>'list'));
        } else {
            $rc = ciniki_customers_web_memberList($ciniki, $settings, $ciniki['request']['tnid'], array('category'=>$category_permalink, 'format'=>'2dlist'));
        }
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $members = $rc['members'];

        $article_title = "<a href='" . $ciniki['request']['base_url'] . "/members'>$page_title</a>";
        if( $rc['tag_name'] != '' ) {
            $page_title .= ' - ' . $rc['tag_name'];
            $article_title .= ' - ' . $rc['tag_name'];
        }

        $page_content .= "<article class='page page-members'>\n"
            . "<header class='entry-title'><h1 class='entry-title'>$article_title</h1></header>\n"
            . "<div class='entry-content'>\n"
            . "";

        if( count($members) > 0 ) {
            $base_url = $ciniki['request']['base_url'] . "/members";
            if( isset($settings['page-members-list-format']) && $settings['page-members-list-format'] == 'thumbnail-list' ) {
                ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processBlockThumbnailList');
                $rc = ciniki_web_processBlockThumbnailList($ciniki, $settings, $ciniki['request']['tnid'], array(
                    'anchors'=>'permalink',
                    'base_url'=>$base_url,
                    'list'=>$members,
                    ));
            } else {
                ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processCIList');
                $rc = ciniki_web_processCIList($ciniki, $settings, $base_url, $members, array('notitle'=>'yes', 'anchors'=>'permalink'));
            }
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
        && isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] == 'gallery' 
        && isset($ciniki['request']['uri_split'][2]) && $ciniki['request']['uri_split'][2] != '' 
        ) {
        $member_permalink = $ciniki['request']['uri_split'][0];
        $image_permalink = $ciniki['request']['uri_split'][2];
        $gallery_url = $ciniki['request']['base_url'] . "/members/$member_permalink/gallery";

        //
        // Load the member to get all the details, and the list of images.
        // It's one query, and we can find the requested image, and figure out next
        // and prev from the list of images returned
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'customers', 'web', 'memberDetails');
        $rc = ciniki_customers_web_memberDetails($ciniki, $settings, 
            $ciniki['request']['tnid'], $member_permalink);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $member = $rc['member'];

        if( !isset($member['images']) || count($member['images']) < 1 ) {
            return array('stat'=>'404', 'err'=>array('code'=>'ciniki.web.72', 'msg'=>"I'm sorry, but the image you requested does not exist."));
        }

        $first = NULL;
        $last = NULL;
        $img = NULL;
        $next = NULL;
        $prev = NULL;
        foreach($member['images'] as $iid => $image) {
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

        if( count($member['images']) == 1 ) {
            $prev = NULL;
            $next = NULL;
        } elseif( $prev == NULL ) {
            // The requested image was the first in the list, set previous to last
            $prev = $last;
        } elseif( $next == NULL ) {
            // The requested image was the last in the list, set previous to last
            $next = $first;
        }
    
        $article_title = "<a href='" . $ciniki['request']['base_url'] . "/members/$member_permalink'>" . $member['name'] . "</a>";
        if( $img['title'] != '' ) {
            $page_title = $member['name'] . ' - ' . $img['title'];
            $article_title .= ' - ' . $img['title'];
        } else {
            $page_title = $member['name'];
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
            return array('stat'=>'404', 'err'=>array('code'=>'ciniki.web.73', 'msg'=>"We're sorry, but the image you requested does not exist."));
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
        $page_content .= "<img id='gallery-image-img' title='" . preg_replace("/\'/", "\\'", strip_tags($img['title'])) . "' alt='" . preg_replace("/\'/", "\\'", strip_tags($img['title'])) . "' src='" . $img_url . "' onload='javascript: gallery_resize_arrows();' />";
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
    // Check if we are to display an member
    //
    elseif( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] != '' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'customers', 'web', 'memberDetails');
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processURL');

        //
        // Get the member information
        //
        $member_permalink = $ciniki['request']['uri_split'][0];
        $rc = ciniki_customers_web_memberDetails($ciniki, $settings, 
            $ciniki['request']['tnid'], $member_permalink);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $member = $rc['member'];
        $page_title = $member['name'];
        $page_content .= "<article class='page'>\n"
            . "<header class='entry-title'><h1 class='entry-title'>" . $member['name'] . "</h1></header>\n"
            . "";

        //
        // Add primary image
        //
        if( isset($member['image_id']) && $member['image_id'] > 0 ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
            $rc = ciniki_web_getScaledImageURL($ciniki, $member['image_id'], 'original', '500', 0);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $page_content .= "<aside><div class='block block-primary-image'><div class='image-wrap'><div class='image'>"
                . "<img title='' alt='" . $member['name'] . "' src='" . $rc['url'] . "' />"
                . "</div>";
            if( isset($member['image_caption']) && $member['image_caption'] != '' ) {
                $page_content .= "<div class='image-caption'>" . $member['image_caption'] . "</div>";
            }
            $page_content .= "</div></aside></aside>";
        }
        
        //
        // Add description
        //
        if( isset($member['description']) ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
            $rc = ciniki_web_processContent($ciniki, $settings, $member['description']);    
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $page_content .= "<div class='block-content'>" . $rc['content'] . "</div>";
        }

        //
        // Add contact_info
        //
        $cinfo = '';
        if( isset($member['addresses']) ) {
            foreach($member['addresses'] as $address) {
                $addr = '';
                if( $address['address1'] != '' ) {
                    $addr .= ($addr!=''?'<br/>':'') . $address['address1'];
                }
                if( $address['address2'] != '' ) {
                    $addr .= ($addr!=''?'<br/>':'') . $address['address2'];
                }
                if( $address['city'] != '' ) {
                    $addr .= ($addr!=''?'<br/>':'') . $address['city'];
                }
                if( $address['province'] != '' ) {
                    $addr .= ($addr!=''?', ':'') . $address['province'];
                }
                if( $address['postal'] != '' ) {
                    $addr .= ($addr!=''?'  ':'') . $address['postal'];
                }
                if( $addr != '' ) {
                    $cinfo .= ($cinfo!=''?'<br/>':'') . "$addr";
                }
            }
        }
        if( isset($member['phones']) ) {
            foreach($member['phones'] as $phone) {
                if( $phone['phone_label'] != '' && $phone['phone_number'] != '' ) {
                    $cinfo .= ($cinfo!=''?'<br/>':'') . $phone['phone_label'] . ': ' . $phone['phone_number'];
                } elseif( $phone['phone_number'] != '' ) {
                    $cinfo .= ($cinfo!=''?'<br/>':'') . $phone['phone_number'];
                }
            }
        }
        if( isset($member['emails']) ) {
            foreach($member['emails'] as $email) {
                if( $email['email'] != '' ) {
                    $cinfo .= ($cinfo!=''?'<br/>':'') . '<a href="mailto:' . $email['email'] . '">' . $email['email'] . '</a>';
                }
            }
        }

        if( $cinfo != '' ) {
            $page_content .= "<h2>Contact Info</h2>\n";
            $page_content .= "<p>$cinfo</p>";
        }

        if( isset($member['links']) ) {
            $links = '';
            foreach($member['links'] as $link) {
                $rc = ciniki_web_processURL($ciniki, $link['url']);
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
                $url = $rc['url'];
                $display_url = $rc['display'];
                if( $link['name'] != '' ) {
                    $display_url = $link['name'];
                }
                $links .= ($links!=''?'<br/>':'') 
                    . "<a class='members-url' target='_blank' href='" . $url . "' "
                    . "title='" . $display_url . "'>" . $display_url . "</a>";
            }
            if( $links != '' ) {
                $page_content .= "<div class='block-content'>";
                $page_content .= "<h2>Links</h2>\n";
                $page_content .= "<p>" . $links . "</p></div>";
            }
        }

        $page_content .= "</article>";

        if( isset($member['images']) && count($member['images']) > 0 ) {
            $page_content .= "<article class='page page-gallery'>"   
                . "<header class='entry-title'><h1 class='entry-title'>Gallery</h1></header>\n"
                . "";
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageGalleryThumbnails');
            $img_base_url = $ciniki['request']['base_url'] . "/members/" . $member['permalink'] . "/gallery";
            $rc = ciniki_web_generatePageGalleryThumbnails($ciniki, $settings, $img_base_url, $member['images'], 125);
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
        if( isset($settings['page-members-membership-details']) && $settings['page-members-membership-details'] != 'no' ) {
            $add_membership_info = $settings['page-members-membership-details'];
        } elseif( isset($settings['page-members-application-details']) && $settings['page-members-application-details'] != 'no' ) {
            $add_membership_info = $settings['page-members-application-details'];
        }
        $membership_info = '';
        if( isset($add_membership_info) && $add_membership_info != '' ) {
            //
            // Pull the membership info from the ciniki.info module
            //
            ciniki_core_loadMethod($ciniki, 'ciniki', 'info', 'web', 'pageDetails');
            if( isset($add_membership_info) && $add_membership_info != 'no' ) {
                $rc = ciniki_info_web_pageDetails($ciniki, $settings, $ciniki['request']['tnid'], 
                    array('content_type'=>'7'));
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
                $info = $rc['content'];
            } elseif( isset($add_application_info) && $add_application_info == 'yes' ) {
                $rc = ciniki_info_web_pageDetails($ciniki, $settings, $ciniki['request']['tnid'], 
                    array('content_type'=>'17'));
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
                $info = $rc['content'];
            }
            if( isset($info) ) {
                $membership_info .= "<br /><article class='page'>\n"
                    . "<header class='entry-title'><h1 class='entry-title'>" . $info['title'] . "</h1></header>\n"
                    . "";
                if( isset($info['image_id']) && $info['image_id'] != '' && $info['image_id'] != 0 ) {
                    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
                    $rc = ciniki_web_getScaledImageURL($ciniki, $info['image_id'], 'original', '500', 0);
                    if( $rc['stat'] != 'ok' ) {
                        return $rc;
                    }
                    $membership_info .= "<aside><div class='image-wrap'>"
                        . "<div class='image'><img title='' src='" . $rc['url'] . "' /></div>";
                    if( isset($info['image_caption']) && $info['image_caption'] != '' ) {
                        $membership_info .= "<div class='image-caption'>" . $info['image_caption'] . "</div>";
                    }
                    $membership_info .= "</div></aside>";
                }

                $membership_info .= "<div class='entry-content'>";
                if( isset($info['content']) ) {
                    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
                    $rc = ciniki_web_processContent($ciniki, $settings, $info['content']);  
                    if( $rc['stat'] != 'ok' ) {
                        return $rc;
                    }
                    $membership_info .= $rc['content'];
                }
                if( isset($info['files']) ) {
                    foreach($info['files'] as $fid => $file) {
                        $url = $ciniki['request']['base_url'] . '/members/download/' . $info['permalink'] . '/' . $file['permalink'] . '.' . $file['extension'];
                        $membership_info .= "<p><a class='button' target='_blank' href='" . $url . "' title='" . $file['name'] . "'>" . $file['name'] . "</a></p>";
                    }
                }
                $membership_info .= "</div>";
                $membership_info .= "</article>\n";
            }
        }
        if( isset($add_membership_info) && $add_membership_info == 'top' ) {
            $page_content .= $membership_info;
        }

        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');

        if( isset($settings['page-members-categories-display']) 
            && ($settings['page-members-categories-display'] == 'wordlist'
                || $settings['page-members-categories-display'] == 'wordcloud' )
            && isset($ciniki['tenant']['modules']['ciniki.customers']['flags']) 
            && ($ciniki['tenant']['modules']['ciniki.customers']['flags']&0x04) > 0 ) {
            //
            // Display the list of categories
            //
//          ciniki_core_loadMethod($ciniki, 'ciniki', 'customers', 'web', 'memberCategories');
//          $rc = ciniki_customers_web_memberCategories($ciniki, $settings, $ciniki['request']['tnid']);
//          if( $rc['stat'] != 'ok' ) {
//              return $rc;
//          }
//          $categories = $rc['categories'];
            
            ciniki_core_loadMethod($ciniki, 'ciniki', 'customers', 'web', 'tagCloud');
            $base_url = $ciniki['request']['base_url'] . '/members/category';
            $rc = ciniki_customers_web_tagCloud($ciniki, $settings, $ciniki['request']['tnid'], 40);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }

            $page_content .= "<article class='page'>\n"
                . "<header class='entry-title'><h1 class='entry-title'>$page_title</h1></header>\n"
                . "<div class='entry-content'>\n"
                . "";

            //
            // Process the tags
            //
            if( $settings['page-members-categories-display'] == 'wordlist' ) {
                if( isset($rc['tags']) && count($rc['tags']) > 0 ) {
                    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processTagList');
                    $rc = ciniki_web_processTagList($ciniki, $settings, $base_url, $rc['tags'], array());
                    if( $rc['stat'] != 'ok' ) {
                        return $rc;
                    }
                    $page_content .= $rc['content'];
                } else {
                    $page_content = "<p>I'm sorry, there are no categories for this blog</p>";
                }
            } elseif( $settings['page-members-categories-display'] == 'wordcloud' ) {
                if( isset($rc['tags']) && count($rc['tags']) > 0 ) {
                    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processTagCloud');
                    $rc = ciniki_web_processTagCloud($ciniki, $settings, $base_url, $rc['tags']);
                    if( $rc['stat'] != 'ok' ) {
                        return $rc;
                    }
                    $page_content .= $rc['content'];
                } else {
                    $page_content = "<p>I'm sorry, there are no categories for this blog</p>";
                }
            }
            $page_content .= "</div>\n"
                . "</article>\n"
                . "";
        } else {
            //
            // Display the list of members
            //
            ciniki_core_loadMethod($ciniki, 'ciniki', 'customers', 'web', 'memberList');
            if( isset($settings['page-members-list-format']) && $settings['page-members-list-format'] == 'thumbnail-list' ) {
                $rc = ciniki_customers_web_memberList($ciniki, $settings, $ciniki['request']['tnid'], array('format'=>'list'));
            } else {
                $rc = ciniki_customers_web_memberList($ciniki, $settings, $ciniki['request']['tnid'], array('format'=>'2dlist'));
            }
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $members = $rc['members'];

            if( count($members) > 0 ) {
                $page_content .= "<article class='page'>\n"
                    . "<header class='entry-title'><h1 class='entry-title'>$page_title</h1></header>\n"
                    . "<div class='entry-content'>\n"
                    . "";

                $base_url = $ciniki['request']['base_url'] . "/members";
                if( isset($settings['page-members-list-format']) && $settings['page-members-list-format'] == 'thumbnail-list' ) {
                    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processBlockThumbnailList');
//                    print "<pre>" . print_r($members, true) . "</pre>";
                    $rc = ciniki_web_processBlockThumbnailList($ciniki, $settings, $ciniki['request']['tnid'], array(
                        'anchors'=>'permalink',
                        'base_url'=>$base_url,
                        'list'=>$members,
                        ));
                } else {
                    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processCIList');
                    $rc = ciniki_web_processCIList($ciniki, $settings, $base_url, $members, array('notitle'=>'yes', 'anchors'=>'permalink'));
                }
//                  array('0'=>array('name'=>'', 'list'=>$members)), array());
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
                $page_content .= $rc['content'];
                $page_content .= "</div>\n"
                    . "</article>\n"
                    . "";
            } else {
                if( !isset($settings['page-members-membership-details']) 
                    || $settings['page-members-membership-details'] != 'yes' 
                    ) {
                    $page_content .= "<article class='page page-membershipdetails'>\n"
                        . "<header class='entry-title'><h1 class='entry-title'>$page_title</h1></header>\n"
                        . "<div class='entry-content'>\n"
                        . "<p>Currently no members.</p>"
                        . "</div>\n"
                        . "</article>\n"
                        . "";
                }
            }
        }
        if( isset($add_membership_info) && ($add_membership_info == 'yes' || $add_membership_info == 'bottom') ) {
            $page_content .= $membership_info;
        }
    }


    //
    // Generate the complete page
    //
    $submenu = array();
    if( isset($settings['page-members-categories-display']) && $settings['page-members-categories-display'] == 'submenu' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'customers', 'web', 'tagCloud');
        $base_url = $ciniki['request']['base_url'] . '/members/category';
        $rc = ciniki_customers_web_tagCloud($ciniki, $settings, $ciniki['request']['tnid'], 40);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['tags']) && count($rc['tags']) > 0 ) {
            foreach($rc['tags'] as $tag) {
                $submenu[] = array(
                    'name' => $tag['name'],
                    'url' => $ciniki['request']['base_url'] . '/members/category/' . $tag['permalink'],
                    );
            }
        }
    }

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
        . "<br style='clear:both;' />\n"
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
