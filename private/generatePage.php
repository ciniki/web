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
function ciniki_web_generatePage(&$ciniki, $settings) {

    //
    // Check if there is a landing page override
    //
    if( isset($ciniki['tenant']['modules']['ciniki.landingpages']) && $ciniki['request']['page'] == 'landingpage' ) {
        $rc = ciniki_core_loadMethod($ciniki, 'ciniki', 'landingpages', 'web', 'generatePage');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( $rc['stat'] == 'ok' ) {
            return ciniki_landingpages_web_generatePage($ciniki, $settings);
        }
    }

    $request_pages = array_merge(array($ciniki['request']['page']), $ciniki['request']['uri_split']);

    $breadcrumbs = array();

    $prev_parent_id = 0;
    $uri_depth = 0;
    $prev_page = NULL;
    $top_page = NULL;
    $page = NULL;
    $article_title = '';
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'pageLoad');
    $depth = count($request_pages);
    $base_url = $ciniki['request']['base_url'];
    $sponsors = array();
    $pwdpage = array(
        'uuid' => '',           // Used as unique page key for storing session password, allows different passwords on different pages
        'permalink' => '',      // The permalink of the page with the password, could be intermediate page
        'password' => '',
        'page_title' => '',
        );
    for($i=0;$i<$depth;$i++) {
        $uri_depth = $i-1;
        //
        // The end of the URI string for loading pages
        //
        if( $i == ($depth-1) ) {
            // Last Page
            $rc = ciniki_web_pageLoad($ciniki, $settings, $ciniki['request']['tnid'], 
                array('permalink'=>$request_pages[$i], 'parent_id'=>$prev_parent_id));
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $page = $rc['page'];
            //
            // Check if a members only section
            //
            if( ($page['flags']&0x04) == 0x04 ) {
                if( isset($ciniki['session']['customer']['id']) && (!isset($ciniki['session']['customer']['member_status']) || $ciniki['session']['customer']['member_status'] != 10) ) {
                    // Logged in but not member
                    return array('stat'=>'404', 'err'=>array('code'=>'ciniki.web.185', 'msg'=>'You must be an active member to view this page'));
                } elseif( !isset($ciniki['session']['customer']['id']) || $ciniki['session']['customer']['id'] <= 0 ) {
                    // Redirect to login
                    if( isset($_SERVER['HTTPS']) ) {
                        $_SESSION['login_referer'] = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                    } else {
                        $_SESSION['login_referer'] = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                    }
                    header('Location: ' . $ciniki['request']['ssl_domain_base_url'] . '/account');
                    return array('stat'=>'exit');
                }
            }
            if( ($page['flags']&0x02) == 0x02 && (!isset($ciniki['session']['customer']['id']) || $ciniki['session']['customer']['id'] < 1) ) {
                return array('stat'=>'404', 'err'=>array('code'=>'ciniki.web.99', 'msg'=>'Page not found'));
            }
            $page['depth'] = $i;
//          $base_url .= '/' . $rc['page']['permalink'];
            if( $top_page == NULL ) { 
                $top_page = $rc['page']; 
                $top_page['base_url'] = $base_url . '/' . $rc['page']['permalink'];
            }
            $breadcrumbs[] = array('name'=>$rc['page']['title'], 'url'=>$base_url . '/' . $rc['page']['permalink']);
            if( isset($rc['page']['sponsors']) && count($rc['page']['sponsors']) > 0 ) {
                $sponsors = $rc['page']['sponsors'];
            }
            //
            // Check if last page, empty with children and page_menu
            // Only display child page automatically when children
            // are to be displayed as a submenu.
            //
            if( $i == 0 && $page['image_id'] == 0 && $page['content'] == '' && isset($page['children']) && count($page['children']) > 0 && ($page['flags']&0x0C90) == 0 ) {
                $depth++;
                $child = array_shift($page['children']);
                reset($page['children']);
                $uri_split[] = $child['permalink'];
                $request_pages[] = $child['permalink'];
                $prev_parent_id = $page['id'];
                $prev_page = $page;
                $base_url .= '/' . $rc['page']['permalink'];
            }
        } 
        //
        // Intermediate page
        //
        else {
            $rc = ciniki_web_pageLoad($ciniki, $settings, $ciniki['request']['tnid'], 
                array('intermediate_permalink'=>$request_pages[$i], 'parent_id'=>$prev_parent_id));
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            if( $top_page == NULL ) { 
                $top_page = $rc['page']; 
                $top_page['base_url'] = $base_url . '/' . $rc['page']['permalink'];
            }
            $breadcrumbs[] = array('name'=>$rc['page']['title'], 'url'=>$base_url . '/' . $rc['page']['permalink']);

            if( isset($rc['page']['sponsors']) && count($rc['page']['sponsors']) > 0 ) {
                $sponsors = $rc['page']['sponsors'];
            }

            //
            // Check if intermediate page is password protected
            //
            if( ($rc['page']['flags']&0x04) == 0x04 ) {
                if( isset($ciniki['session']['customer']['id']) && (!isset($ciniki['session']['customer']['member_status']) || $ciniki['session']['customer']['member_status'] != 10) ) {
                    // Logged in but not member
                    return array('stat'=>'404', 'err'=>array('code'=>'ciniki.web.185', 'msg'=>'You must be an active member to view this page'));
                } elseif( !isset($ciniki['session']['customer']['id']) || $ciniki['session']['customer']['id'] <= 0 ) {
                    // Redirect to login
                    if( isset($_SERVER['HTTPS']) ) {
                        $_SESSION['login_referer'] = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                    } else {
                        $_SESSION['login_referer'] = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                    }
                    header('Location: ' . $ciniki['request']['ssl_domain_base_url'] . '/account');
                    return array('stat'=>'exit');
                }
            }
            if( isset($rc['page']['flags']) && ($rc['page']['flags']&0x08) == 0x08 
                && $rc['page']['page_password'] != '' 
                ) {
                $pwdpage['password'] = $rc['page']['page_password'];
                $pwdpage['permalink'] = $rc['page']['permalink'];
                $pwdpage['title'] = $rc['page']['title'];
                $pwdpage['uuid'] = $rc['page']['uuid'];
            }

            //
            // Check if next item is a child, otherwise this is the parent
            //
            if( !isset($rc['page']['children'])
                || !isset($rc['page']['children'][$request_pages[$i+1]]) ) {
                // Load full page details
                $rc = ciniki_web_pageLoad($ciniki, $settings, $ciniki['request']['tnid'], 
                    array('permalink'=>$request_pages[$i], 'parent_id'=>$prev_parent_id));
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
                $page = $rc['page'];
                $page['depth'] = $i;
                break;
            } else {
                $prev_parent_id = $rc['page']['id'];
                $prev_page = $rc['page'];
                $base_url .= '/' . $rc['page']['permalink'];
                if( !isset($settings['theme']['header-breadcrumbs']) || $settings['theme']['header-breadcrumbs'] == 'no' ) {
                    $article_title .= ($article_title!=''?' - ':'') . "<a href='$base_url'>" . $rc['page']['title'] . "</a>";
                }
            }
        }
    }

    //
    // Check if last page is password protected
    //
    if( isset($page['flags']) && ($page['flags']&0x08) == 0x08 && $page['page_password'] != '' ) {
        $pwdpage['password'] = $page['page_password'];
        $pwdpage['permalink'] = $page['permalink'];
        $pwdpage['title'] = $page['title'];
        $pwdpage['uuid'] = $page['uuid'];
    }

    //
    // If a password protected page or a page below, make sure password is entered
    //
    if( $pwdpage['password'] != '' ) {
        $page['password_protected'] = 'yes';
        if( !isset($_SESSION['pwdpages'][$pwdpage['uuid']]['authenticated']) 
            || $_SESSION['pwdpages'][$pwdpage['uuid']]['authenticated'] != 'yes' 
            ) {
            $err_msg = '';  
            if( isset($_POST['password']) && $_POST['password'] == $pwdpage['password'] ) {
                if( !isset($_SESSION['pwdpages']) ) {
                    $_SESSION['pwdpages'] = array();
                }
                $_SESSION['pwdpages'][$pwdpage['uuid']] = array('authenticated' => 'yes');
                header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
                exit;
            } elseif( isset($_POST['password']) ) {
                $err_msg = 'Incorrect password';
            }
            //
            // Display the password form
            //
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageHeader');
            $rc = ciniki_web_generatePageHeader($ciniki, $settings, $page['title'], array());
            if( $rc['stat'] != 'ok' ) { 
                return $rc;
            }
            $content = $rc['content'];

            $content .= "<div id='content'><article class='page'>\n";
/*                if( isset($settings['page-membersonly-message']) && $settings['page-membersonly-message'] != '' ) {
                ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
                $rc = ciniki_web_processContent($ciniki, $settings, $settings['page-membersonly-message'], 'wide');   
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
                $content .= $rc['content'];
            } else { */
                $content .= "<p>This page is password protected.</p>";
//                }
            $content .= "<form method='POST' action=''>";
            if( $err_msg != '' ) {
                $content .= "<p class='formerror'>$err_msg</p>\n";
            }
            $content .= "<input type='hidden' name='action' value='signin'>\n"
                . "<div class='input'><label for='password'>Password</label><input id='password' type='password' class='text' maxlength='100' name='password' value='' /></div>\n"
                . "<div class='submit'><input type='submit' class='submit' value='Continue' /></div>\n"
                . "</form>"
                . "<br/>";
            $content .= "</form>";
            $content .= "<br style='clear: both;' />\n";
            $content .= "</article></div>\n";

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
    }

//  print "Showing page: \n";
//  print_r($page);
//  print "</pre>";

    $page_content = '';
    $submenu = array();
    
    $page_menu = array();
    if( $top_page != null && ($top_page['flags']&0x40) == 0x40 && isset($top_page['children']) && count($top_page['children']) > 0 ) {
        foreach($top_page['children'] as $child) {
            $page_menu[] = array('name'=>$child['name'], 'url'=>$top_page['base_url'] . '/' . $child['permalink']);
        }
    } 

    //
    // Process a module page
    //
    if( $page['page_type'] == '30' ) {
        $base_url .= '/' . $rc['page']['permalink'];
        $domain_base_url = $ciniki['request']['domain_base_url'] . '/' . $ciniki['request']['page'];
        $ssl_domain_base_url = $ciniki['request']['ssl_domain_base_url'] . '/' . $ciniki['request']['page'];
        $ciniki['request']['page-container-class'] = str_replace('.', '-', $page['page_module']);

//      $breadcrumbs[] = array('name'=>$page['title'], 'url'=>$base_url . '/' . $ciniki['request']['page']);

        if( ($top_page['flags']&0x20) == 0x20 && isset($top_page['children']) ) {
            foreach($top_page['children'] as $child) {
                $submenu[$child['permalink']] = array('name'=>$child['name'],
                    'url'=>$ciniki['request']['base_url'] . '/' . $top_page['permalink'] . '/' . $child['permalink']);
            }
            if( $top_page['id'] == $page['id'] ) {
                unset($page['children']);
            }
        }
        //
        // Process the module request
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processModuleRequest');
        $uri_split = $ciniki['request']['uri_split'];
        for($i = 0; $i < $page['depth']; $i++) {
            array_shift($uri_split);
        }
        $rc = ciniki_web_processModuleRequest($ciniki, $settings, $ciniki['request']['tnid'], $page['page_module'],
            array(
                'uri_split'=>$uri_split,
                'base_url'=>$base_url,
                'domain_base_url'=>$domain_base_url,
                'ssl_domain_base_url'=>$ssl_domain_base_url,
                'page_title'=>$page['title'],
                'page_menu'=>$page_menu,
                'breadcrumbs'=>$breadcrumbs,
                'password_protected' => (isset($page['password_protected']) ? $page['password_protected'] : 'no'),
            ));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['content']) ) {
            $page_content .= $rc['content'];
        }
        if( isset($rc['fullscreen-content']) ) {
            $page['fullscreen-content'] = $rc['fullscreen-content'];
        }
        $breadcrumbs = $rc['breadcrumbs'];
        if( isset($rc['page_title']) ) {
            $page_title = $rc['page_title'];
            $article_title = $rc['page_title'];
        }
        if( isset($rc['submenu']) ) {
            $submenu = $rc['submenu'];
        }
    } 

    //
    // Process a manual page, no processing of content, output raw HTML
    //
    elseif( $page['page_type'] == '11' ) {
        //
        // Set the page class
        //
        $ciniki['request']['page-container-class'] = 'page-' . $ciniki['request']['page'];

        $page_content .= "<article class='page'>\n";
        if( isset($page['title']) ) {
            $article_title = $page['title'];
            $page_content .= "<header class='entry-title'><h1 class='entry-title'>" . $page['title'] . "</h1>";
            if( isset($breadcrumbs) ) {
                ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processBreadcrumbs');
                $rc = ciniki_web_processBreadcrumbs($ciniki, $settings, $ciniki['request']['tnid'], $breadcrumbs);
                if( $rc['stat'] == 'ok' ) {
                    $page_content .= $rc['content'];
                }
            }
            if( isset($page_menu) && count($page_menu) > 0 ) {
                $page_content .= "<div class='page-menu-container'><ul class='page-menu'>";
                foreach($page_menu as $item) {  
                    $page_content .= "<li class='page-menu-item'><a href='" . $item['url'] . "'>" . $item['name'] . "</a></li>";
                }
                $page_content .= "</ul></div>";
            }
            $page_content .= "</header>";
        }
        
        //
        // Process any form submissions
        //
        $result_content = '';
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processPageForms');
        $rc = ciniki_web_processPageForms($ciniki, $settings, $ciniki['request']['tnid']);
        if( $rc['stat'] != 'ok' ) {
            $result_content .= "<div class='form-result-message form-error-message'><div class='form-message-wrapper'><p>Error processing request</p></div></div>";
        }
        if( isset($rc['error_message']) && $rc['error_message'] != '' ) {
            $result_content .= "<div class='form-result-message form-error-message'><div class='form-message-wrapper'><p>" . $rc['error_message'] . "</p></div></div>";
        }
        if( isset($rc['success_message']) && $rc['success_message'] != '' ) {
            $result_content .= "<div class='form-result-message form-success-message'><div class='form-message-wrapper'><p>" . $rc['success_message'] . "</p></div></div>";
        }

        if( $result_content != '' ) {
            $page_content .= "<div class='form-message-content'>"
                . $result_content
                . "</div>";
        }

        $page_content .= "<div class='entry-content'>";
        $page_content .= $page['content'];
        
        $page_content .= "</div>";
        $page_content .= "</article>";
    } 
    
    //
    // Process a custom page
    //
    else {      // $page['page_type'] == '10'
        //
        // Check if children should be submenu
        //
        if( ($top_page['flags']&0x20) == 0x20 && isset($top_page['children']) ) {
            foreach($top_page['children'] as $child) {
                $submenu[$child['permalink']] = array('name'=>$child['name'],
                    'url'=>$ciniki['request']['base_url'] . '/' . $top_page['permalink'] . '/' . $child['permalink']);
            }
            if( $top_page['id'] == $page['id'] ) {
                unset($page['children']);
            }
        }

        //
        // Set the page class
        //
        $ciniki['request']['page-container-class'] = 'page-' . $ciniki['request']['page'];
        if( $page['permalink'] != $ciniki['request']['page'] ) {
            $ciniki['request']['page-container-class'] .= ' page-' . $ciniki['request']['page'] . '-' . $page['permalink'];
        }

        //
        // Check if a file was specified to be downloaded
        //
        $download_err = '';
        if( isset($ciniki['request']['uri_split'][$uri_depth+1]) 
            && $ciniki['request']['uri_split'][$uri_depth+1] == 'download' 
            && isset($ciniki['request']['uri_split'][$uri_depth+2]) 
            && $ciniki['request']['uri_split'][$uri_depth+2] != '' 
            && isset($page['files'])
            ) {
            $file_permalink = $ciniki['request']['uri_split'][$uri_depth+2];

            //
            // Get the file details
            //
            $strsql = "SELECT ciniki_web_page_files.id, "
                . "ciniki_web_page_files.uuid, "
                . "ciniki_web_page_files.name, "
                . "ciniki_web_page_files.permalink, "
                . "ciniki_web_page_files.extension, "
                . "ciniki_web_page_files.description, "
                . "ciniki_web_page_files.binary_content "
                . "FROM ciniki_web_pages, ciniki_web_page_files "
                . "WHERE ciniki_web_pages.tnid = '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['tnid']) . "' "
                . "AND ciniki_web_pages.permalink = '" . ciniki_core_dbQuote($ciniki, $page['permalink']) . "' "
                . "AND ciniki_web_pages.id = ciniki_web_page_files.page_id "
                . "AND ciniki_web_page_files.tnid = '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['tnid']) . "' "
                . "AND CONCAT_WS('.', ciniki_web_page_files.permalink, ciniki_web_page_files.extension) = '" . ciniki_core_dbQuote($ciniki, $file_permalink) . "' "
                . "";
            $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.web', 'file');
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            if( !isset($rc['file']) ) {
                return array('stat'=>'404', 'err'=>array('code'=>'ciniki.web.12', 'msg'=>"I'm sorry, but the file you requested does not exist."));
            }
            $file = $rc['file'];
            $filename = $rc['file']['name'] . '.' . $rc['file']['extension'];

            //
            // Load the file contents
            //
            ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'storageFileLoad');
            $rc = ciniki_core_storageFileLoad($ciniki, $ciniki['request']['tnid'], 'ciniki.web.page_file', array('subdir'=>'pagefiles', 'uuid'=>$file['uuid']));
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $binary_content = $rc['binary_content'];

            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            header("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: no-cache');
            if( $file['extension'] == 'pdf' ) {
                header('Content-Type: application/pdf');
            } elseif( $file['extension'] == 'zip' ) {
                header('Content-Type: application/zip');
            }
    //      header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Content-Length: ' . strlen($binary_content));
            header('Cache-Control: max-age=0');

            print $binary_content;
            exit;
        }

        if( isset($ciniki['request']['uri_split'][$uri_depth+1]) 
            && $ciniki['request']['uri_split'][$uri_depth+1] == 'gallery' 
            && isset($ciniki['request']['uri_split'][$uri_depth+2]) 
            && $ciniki['request']['uri_split'][$uri_depth+2] != '' 
            && isset($page['images'])
            ) {
            $image_permalink = $ciniki['request']['uri_split'][$uri_depth+2];

            $base_url .= '/' . $page['permalink'];
            // $article_title = "<a href='$base_url'>" . $page['title'] . "</a>";
            $article_title .= ($article_title!=''?' - ':'') . "<a href='$base_url'>" . $page['title'] . "</a>";
            
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processGalleryImage');
            $rc = ciniki_web_processGalleryImage($ciniki, $settings, $ciniki['request']['tnid'], array(
                'item'=>$page,
                'gallery_url'=>$base_url . '/gallery',
                'article_title'=>$article_title,
                'image_permalink'=>$image_permalink
                ));
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $page_content .= $rc['content'];

        } else {
            if( isset($sponsors) && is_array($sponsors) && count($sponsors) > 0 ) {
                $page['sponsors'] = $sponsors;
            }
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processPage');
            $rc =  ciniki_web_processPage($ciniki, $settings, $base_url, $page, array('article_title'=>$article_title, 'page_menu'=>$page_menu));
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $page_content .= $rc['content'];
        }
    }

    if( isset($page['fullscreen-content']) && $page['fullscreen-content'] == 'yes' ) {
        $ciniki['response']['fullscreen-content'] = 'yes';
    } 

    //
    // Add the header
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageHeader');
    $rc = ciniki_web_generatePageHeader($ciniki, $settings, $top_page['title'], $submenu);
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }
    $content = $rc['content'];

    if( isset($page['fullscreen-content']) && $page['fullscreen-content'] == 'yes' ) {
        $content .= "<div id='content' class='fullscreen'>\n";
        $content .= $page_content;
        $content .= "</div>\n";
    } else {
        //
        // Check if article title and breadcrumbs should be displayed above content
        //
        if( (isset($settings['theme']['header-article-title']) && $settings['theme']['header-article-title'] == 'yes')
            || (isset($settings['theme']['header-breadcrumbs']) && $settings['theme']['header-breadcrumbs'] == 'yes')
            ) {
            $content .= "<div class='page-header'>";
            if( isset($settings['theme']['header-article-title']) && $settings['theme']['header-article-title'] == 'yes' ) {
                $content .= "<h1 class='page-header-title'>" . $article_title . "</h1>";
            }
            if( isset($settings['theme']['header-breadcrumbs']) && $settings['theme']['header-breadcrumbs'] == 'yes' && isset($breadcrumbs) ) {
                ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processBreadcrumbs');
                $rc = ciniki_web_processBreadcrumbs($ciniki, $settings, $ciniki['request']['tnid'], $breadcrumbs);
                if( $rc['stat'] == 'ok' ) {
                    $content .= $rc['content'];
                }
            }
            $content .= "</div>";
        }

        $content .= "<div id='content'>\n";
        $content .= $page_content;
        $content .= "<br style='clear: both;' />\n";
        $content .= "</div>\n";
    }

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
