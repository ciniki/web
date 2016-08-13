<?php
//
// Description
// -----------
// This function will generate the footer to be displayed at the bottom
// of every web page.
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_generatePageFooter(&$ciniki, $settings) {
    global $start_time;

    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
    //
    // Store the content
    //
    $content = '';
    $popup_box_content = '';
    $javascript = '';

    // Generate the footer content
    $content .= "<hr class='section-divider footer-section-divider' />\n";
    $content .= "<footer>";
    $content .= "<div class='footer-wrapper'>";

    //
    // Check if there are any sponsors
    //
    if( isset($ciniki['business']['modules']['ciniki.sponsors']) 
        && ($ciniki['business']['modules']['ciniki.sponsors']['flags']&0x02) == 0x02
        ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'sponsors', 'web', 'sponsorRefList');
        $rc = ciniki_sponsors_web_sponsorRefList($ciniki, $settings, $ciniki['request']['business_id'], 'ciniki.web.page', 'footer');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['sponsors']) ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processSponsorsSection');
            $rc = ciniki_web_processSponsorsSection($ciniki, $settings, $rc['sponsors']);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            if( isset($rc['content']) && $rc['content'] != '' ) {
                $content .= $rc['content'];
            }
        }
    }

    //
    // Check if there is a pre footer message
    //
    if( isset($ciniki['business']['modules']['ciniki.web']['flags']) && ($ciniki['business']['modules']['ciniki.web']['flags']&0x100000) > 0 
        && isset($settings['site-footer-message']) && $settings['site-footer-message'] != '' 
        ) {
        $rc = ciniki_web_processContent($ciniki, $settings, $settings['site-footer-message']);  
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $content .= "<div class='footer-message-wrapper'><div class='footer-message'><p>" . $rc['content'] . "</p></div></div>";
    }

    //
    // Check if there is a footer menu
    //
    if( ciniki_core_checkModuleFlags($ciniki, 'ciniki.web', 0x0200) 
        && isset($settings['theme']['footer-menu']) && $settings['theme']['footer-menu'] == 'yes' 
        && isset($ciniki['response']['pages'])
        ) {
        //
        // Get the pages for the footer
        //
        $content .= "<nav id='footer-menu'>";
        $content .= "<div class='footer-menu-container'>";
        $content .= "<h3 class='assistive-text'>Footer Menu</h3>";
        $content .= "<ul class='menu'>";
        foreach($ciniki['response']['pages'] as $page) {
            if( ($page['menu_flags']&0x02) == 0 ) {
                continue;   // Skip non footer pages
            }
            $content .= "<li class='menu-item" . ($ciniki['request']['page']==$page['permalink']?' menu-item-selected':'') 
                . ((isset($page['subpages'])&&count($page['subpages'])>0)?' menu-item-dropdown':'') 
                . " menu-item-" . $page['permalink']
                . (($page['menu_flags']&0x01)==0x01?' menu-item-header':'')
                . (($page['menu_flags']&0x02)==0x02?' menu-item-footer':'')
                . "'><a href='" . $ciniki['request']['base_url'] . "/" . $page['permalink'] . "'>" . $page['title'] . "</a>";
            if( isset($page['subpages']) && count($page['subpages']) > 0 ) {
                $content .= "<ul class='sub-menu sub-menu-hidden'>";
                foreach($page['subpages'] as $subpage ) {
                    $content .= "<li class='sub-menu-item'>";
                    if( isset($subpage['page_type']) && $subpage['page_type'] == '20' ) {
                        $content .= "<a href='" . $subpage['page_redirect_url'] . "'>" . $subpage['title'] . "</a>";
                    } elseif( isset($subpage['url']) && $subpage['url'] != '' ) {
                        $content .= "<a href='" . $subpage['url'] . "'>" . (isset($subpage['name'])?$subpage['name']:$subpage['title']) . "</a>";
                    } else {
                        $content .= "<a href='" . $ciniki['request']['base_url'] . "/" . $page['permalink'] . "/" . $subpage['permalink'] . "'>" . $subpage['title'] . "</a>";
                    }
                    $content .= "</li>";
                }
                $content .= "</ul>";
            }
            //
            // If the footer menu item is the account page and the customer is not logged in, display the login form
            //
            if( isset($page['id']) && $page['id'] == 'account' 
                && (!isset($ciniki['session']['customer']['id']) || $ciniki['session']['customer']['id'] < 1) 
                ) {
                if( isset($settings['page-account-signin-redirect']) 
                    && $settings['page-account-signin-redirect'] == 'back' 
                    ) {
                    if( (!isset($_SESSION['login_referer']) || $_SESSION['login_referer'] == '') 
                        && isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] != '' 
                        ) {
                            $_SESSION['login_referer'] = $_SERVER['HTTP_REFERER'];
                    }
                }
                $_SESSION['loginform'] = 'yes';
                $content .= "<div id='footer-signin' class='signin-form'>"
                    . "<form action='" . $ciniki['request']['ssl_domain_base_url'] . "/account' method='post'>"
                    . "<input type='hidden' name='action' value='signin'>"
                    . "<div class='input'>"
                        . "<label for='footeremail'>Email</label>"
                        . "<input id='footeremail' type='email' class='text' maxlength='250' name='email' />"
                    . "</div>"
                    . "<div class='input'>"
                        . "<label for='password'>Password</label>"
                        . "<input id='password' type='password' class='text' maxlength='100' name='password' />"
                    . "</div>"
                    . "<div class='submit'>"
                        . "<input type='submit' class='submit button' value='Sign In'/>"
                    . "</div>"
                    . "</form>"
                    . "<a class='color' href='javscript:void(0);' onclick='swapFooterLoginForm(\"forgotpassword\");return false;'>Forgot your password?</a>"
                    . "</div>";
                $content .= "<div id='footer-forgot' class='signin-form' style='display: none;'>"
                    . "<form action='" . $ciniki['request']['ssl_domain_base_url'] . "/account' method='post'>"
                    . "<input type='hidden' name='action' value='forgot'>"
                    . "<div class='input'>"
                        . "<label for='footerforgotemail'>Email</label>"
                        . "<input id='footerforgotemail' type='email' class='text' maxlength='250' name='email' />"
                    . "</div>"
                    . "<div class='submit'>"
                        . "<input type='submit' class='submit button' value='Get New Password'/>"
                    . "</div>"
                    . "<a class='color' href='javascript:void();' onclick='swapFooterLoginForm(\"signin\"); return false;'>Sign In</a>"
                    . "</form>"
                    . "</div>";

                //
                // Javascript to switch login/forgot password forms
                //
                $javascript .= ""
                    . "function swapFooterLoginForm(l) {"
                        . "if( l == 'forgotpassword' ) {"
                            . "document.getElementById('footer-signin').style.display = 'none';"
                            . "document.getElementById('footer-forgot').style.display = 'block';"
                            . "document.getElementById('footerforgotemail').value = document.getElementById('footeremail').value;"
                        . "} else {"
                            . "document.getElementById('footer-signin').style.display = 'block';"
                            . "document.getElementById('footer-forgot').style.display = 'none';"
                        . "}\n"
                        . "return true;\n"
                    . "}\n"
                    . "";
            }
            $content .= "</li>";
        }
        $content .= "</ul>";
        $content .= "</div>";
        $content .= "</nav>";
    }

    //
    // Check for social media icons
    //
    $social = '';
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'socialIcons');
    $rc = ciniki_web_socialIcons($ciniki, $settings, 'footer');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['social']) && $rc['social'] != '' ) {
        $social = $rc['social'];
    }

    //
    // Check for copyright information
    //
    $copyright = '';
    if( isset($settings['theme']['footer-copyright-message']) && $settings['theme']['footer-copyright-message'] != '' ) {
        $copyright .= "<span class='copyright'>" . preg_replace('/{_year_}/', date('Y'), $settings['theme']['footer-copyright-message']) . "</span><br/>";
    } else {
        $copyright .= "<span class='copyright'>All content &copy; Copyright " . date('Y') . " by " . ((isset($settings['site-footer-copyright-name']) && $settings['site-footer-copyright-name'] != '')?$settings['site-footer-copyright-name']:$ciniki['business']['details']['name']) . ".</span><br/>";
    }
    if( isset($settings['site-footer-copyright-message']) && $settings['site-footer-copyright-message'] != '' ) {
        $rc = ciniki_web_processContent($ciniki, $settings, $settings['site-footer-copyright-message'], 'copyright');   
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $copyright .= $rc['content'];
    }

    //
    // Check for theme copyrights
    //
    if( file_exists($ciniki['request']['theme_dir'] . '/' . $settings['site-theme'] . '/copyright.html') ) {
        $copyright .= "<span class='copyright'>" . file_get_contents($ciniki['request']['theme_dir'] . '/' . $settings['site-theme'] . '/copyright.html') . "</span><br/>";
    }

    if( isset($ciniki['config']['ciniki.web']['poweredby.url']) && $ciniki['config']['ciniki.web']['poweredby.url'] != '' && $ciniki['config']['ciniki.core']['master_business_id'] != $ciniki['request']['business_id'] ) {
        $copyright .= "<span class='poweredby'>Powered by <a href='" . $ciniki['config']['ciniki.web']['poweredby.url'] . "'>" . $ciniki['config']['ciniki.web']['poweredby.name'] . "</a></span>";
    }

    //
    // Check for footer landingpages
    //
    $footer_buttons = '';
    if( isset($settings['site-footer-landingpage1-permalink']) && $settings['site-footer-landingpage1-permalink'] != '' 
        && isset($settings['site-footer-landingpage1-title']) && $settings['site-footer-landingpage1-title'] != '' 
        && (!isset($ciniki['session']['customer']['id']) || isset($ciniki['session']['customer']['id']) == 0)
        ) {
        $social = "<span class='button'>"
            . "<a href='" . $ciniki['request']['domain_base_url'] . '/landingpage/' . $settings['site-footer-landingpage1-permalink'] . "'>"
            . $settings['site-footer-landingpage1-title']
            . "</a>"
            . "</span>"
            . $social;
    }
    //
    // Check if any links should be added to the footer
    //
    $links = '';
    $content_types = array();
    if( isset($settings['theme']['footer-subscription-agreement']) && $settings['theme']['footer-subscription-agreement'] == 'popup' 
        && isset($ciniki['business']['modules']['ciniki.info']['flags']) && ($ciniki['business']['modules']['ciniki.info']['flags']&0x02000000) > 0
        ) {
        $content_types[] = '26';
    }
    if( isset($settings['theme']['footer-privacy-policy']) && $settings['theme']['footer-privacy-policy'] == 'popup' 
        && isset($ciniki['business']['modules']['ciniki.info']['flags']) && ($ciniki['business']['modules']['ciniki.info']['flags']&0x8000) > 0 
        ) {
        $content_types[] = '16';
    }

    //
    // Get the information for the links
    //
    if( count($content_types) > 0 ) {
        //
        // Setup the javascript for the popups
        //
        $javascript = ""
            . "var curPopup = '';"
            . "function popupShow(p) {"
            . "var e = document.getElementById(p);"
            . "e.style.display='block';"
            . "curPopup = p;"
            . "popupResize();"
            . "window.addEventListener('resize', popupResize);"
            . "};"
            . "function popupHide(p) {"
            . "var e = document.getElementById(p);"
            . "e.style.display='none';"
            . "curPopup = '';"
            . "window.removeEventListener('resize', popupResize);"
            . "};"
            . "function popupResize() {"
            . "var e = document.getElementById(curPopup);"
            . "var h = document.getElementById(curPopup+'-header');"
            . "var c = document.getElementById(curPopup+'-content');"
            . "var f = document.getElementById(curPopup+'-footer');"
            . "if(h!=null&&c!=null&&f!=null){"
                . "var s=h.parentNode.parentNode.currentStyle||window.getComputedStyle(h.parentNode.parentNode);"
                . "c.style.height=(window.innerHeight-h.clientHeight-f.clientHeight-(parseInt(s.marginTop)*2))+'px';"
            . "}"
            . "};"
            . "";
        //
        // Load the content to be setup for popups
        //
        $strsql = "SELECT content_type, title, permalink, content "
            . "FROM ciniki_info_content "
            . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['business_id']) . "' "
            . "AND content_type IN (" . ciniki_core_dbQuoteIDs($ciniki, $content_types) . ") "
            . "ORDER BY content_type DESC "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.info', 'info');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['rows']) ) {
            foreach($rc['rows'] as $row) {
                if( $row['content'] == '' ) {
                    continue;
                }
                $links .= ($links!=''?' | ':'') . "<a href='javascript: popupShow(\"" . $row['permalink'] . "\");'>" . $row['title'] . "</a>";
                $popup_box_content .= "<div id='" . $row['permalink'] . "' class='popup-container' style='display:none;'>\n"
                    . "<div class='popup-wrapper'>\n"
                        . "<div class='popup-body'>"
                        . "<div id='" . $row['permalink'] . "-header' class='popup-header'>"
                            . "<button type='button' class='popup-button' onclick='popupHide(\"" . $row['permalink'] . "\");'>&times;</button>"
                            . "<h4 class='popup-title'>" . $row['title'] . "</h4>"
                        . "</div>"
                        . "<div id='" . $row['permalink'] . "-content' class='popup-content'>"
                        . $row['content']
                        . "</div>"
                        . "<div id='" . $row['permalink'] . "-footer' class='popup-footer'>"
                            . "<button type='button' class='popup-button' onclick='popupHide(\"" . $row['permalink'] . "\");'>Close</button>"
                        . "</div>"
                        . "</div>"
                    . "</div>"
                    . "</div>";
            }
        }
    }

//  if( isset($settings['site-footer-subscription-agreement']) && $settings['site-footer-subscription-agreement'] == 'yes' ) {
//      $links .= "<a href='/'>Subscription Agreement</a>";
//  }
//  if( isset($settings['site-footer-privacy-policy']) && $settings['site-footer-privacy-policy'] == 'yes' ) {
//      $links .= ($links!=''?' | ':'') . "<a href='/'>Privacy Policy</a>";
//  }

    //
    // Decide how the footer should be laid out
    //
    if( isset($settings['theme']['footer-layout']) && $settings['theme']['footer-layout'] == 'copyright-links-social' ) {
        $content .= "<div class='copyright'>" . $copyright . "</div>";

        if( $links != '' ) {
            $content .= "<div class='links'>" . $links . "</div>";
        }

        if( $social != '' ) {
            $content .= "<div class='social-icons'>" . $social . "</div>";
        }

    } else {
        if( $social != '' ) {
            $content .= "<div class='social-icons'>" . $social . "</div>";
        }

        if( $links != '' ) {
            $content .= "<div class='links'>" . $links . "</div>";
        }

        $content .= "<div class='copyright'>";
        $content .= $copyright;
        $content .= "</div>";
    }

    //
    // Extra information for the bottom of the page, error messages, debug info, etc
    //
    $content .= "<div id='x-info' class='x-info'>";
    // If there was an error page generated, see if we should put the error code in the footer for debug purposes.
    // This keeps it out of the way, but easy to tell people what to look for.
    if( isset($ciniki['request']['error_codes_msg']) && $ciniki['request']['error_codes_msg'] != '' ) {
        $content .= "<br/><span class='error_msg'>" . $ciniki['request']['error_codes_msg'] . "</span>";
    }
    $content .= "<span id='x-stats' class='x-stats' style='display:none;'>Execution: " . sprintf("%.4f", ((microtime(true)-$start_time)/60)) . "seconds</span>";
    $content .= "</div>";
    $content .= "</div>";

    $content .= "</footer>"
        . "";

    // Close page-container
    $content .= "</div>\n";

    //
    // Include any modal boxes
    //
    if( $popup_box_content != '' ) {
        $content .= $popup_box_content;
    }
    //
    // Check if My Live Chat is enabled
    //
    if( isset($settings['site-mylivechat-enable']) && $settings['site-mylivechat-enable'] == 'yes' 
        && isset($settings['site-mylivechat-userid']) && $settings['site-mylivechat-userid'] != '' 
        ) {
        $javascript .= ""
            . "function setupMyLiveChat() {"
                . "var e=document.createElement('script');"
                . "e.src='https://mylivechat.com/chatwidget.aspx?hccid=" . $settings['site-mylivechat-userid'] . "';"
                . "document.body.appendChild(e);"
            . "}"
            . "if(window.addEventListener){"
                . "window.addEventListener('load', setupMyLiveChat, false);"
            . "}else{"
                . "window.attachEvent('onload',setupMyLiveChat);"
            . "};";
    }

    if( $javascript != '' ) {
        $content .= "<script type='text/javascript'>$javascript</script>";
    }

    //
    // Check if a timeout is specified and the user is logged in
    //
/*    if( isset($settings['page-account-timeout']) && $settings['page-account-timeout'] > 0 
        && isset($ciniki['session']['customer']['id']) && $ciniki['session']['customer']['id'] > 0 
        ) {   
        $ciniki['request']['inline_javascript'] .= '<script type="text/javascript">setInterval(function(){window.location.href="' . $ciniki['request']['ssl_domain_base_url'] . '/account/logout/timeout";},' . ($settings['page-account-timeout']*60000) . ');</script>';
    } */

    $content .= "</body>"
        . "</html>"
        . "";

    return array('stat'=>'ok', 'content'=>$content);
}
?>
