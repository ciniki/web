<?php
//
// Description
// -----------
// This function will generate a customers account page.  This page allows the customer
// to login to their account, change their password and subscribe/unsubscribe to public
// subscriptions (newsletters).
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_generatePageAccount(&$ciniki, $settings) {

    $breadcrumbs = array();
    $breadcrumbs[] = array('name'=>'Account', 'url'=>$ciniki['request']['domain_base_url'] . '/account');

    //
    // Set no caching
    //
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
    header("Cache-Control: no-store, no-cache, must-revalidate"); 
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");

    //
    // Check if should be forced to SSL
    //
    if( isset($settings['site-ssl-force-account']) 
        && $settings['site-ssl-force-account'] == 'yes' 
        ) {
        if( isset($settings['site-ssl-active'])
            && $settings['site-ssl-active'] == 'yes'
            && (!isset($_SERVER['HTTP_CLUSTER_HTTPS']) || $_SERVER['HTTP_CLUSTER_HTTPS'] != 'on')
            && (!isset($_SERVER['SERVER_PORT']) || $_SERVER['SERVER_PORT'] != '443' ) )  {
            header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
            exit;
        }
    }

    //
    // Check if should force to shop.domain
    //
    if( isset($settings['site-ssl-shop']) && $settings['site-ssl-shop'] == 'yes' 
        && isset($ciniki['config']['ciniki.web']['shop.domain']) && $_SERVER['HTTP_HOST'] != $ciniki['config']['ciniki.web']['shop.domain'] && $_SERVER['HTTP_HOST'] != $ciniki['config']['ciniki.web']['master.domain']
        ) {
        header('Location: https://' . $ciniki['config']['ciniki.web']['shop.domain'] . '/' . $ciniki['business']['sitename'] . $_SERVER['REQUEST_URI']);
        exit;
    }

    //
    // Check if logout was requested
    //
    if( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] == 'logout' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageAccountLogout');
        if( isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] == 'timeout' ) {
            return ciniki_web_generatePageAccountLogout($ciniki, $settings, $ciniki['request']['business_id'], 'yes');
        }
        return ciniki_web_generatePageAccountLogout($ciniki, $settings, $ciniki['request']['business_id'], 'no');
    }

    //
    // Check if customer is logged in, or display the login form/forgot password form.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageAccountLogin');
    $rc = ciniki_web_generatePageAccountLogin($ciniki, $settings, $ciniki['request']['business_id'], $breadcrumbs);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( $rc['stat'] == 'ok' && isset($rc['content']) ) {
        // Login form display or welcome page
        return $rc;
    }

    //
    // NOTE: At this point the customer is considered logged in
    //
    $ciniki['request']['page-container-class'] = 'page-account';

    //
    // Check if a timeout is specified
    //
    if( isset($settings['page-account-timeout']) && $settings['page-account-timeout'] > 0 ) {
        $ciniki['request']['inline_javascript'] .= '<script type="text/javascript">setInterval(function(){window.location.href="' . $ciniki['request']['ssl_domain_base_url'] . '/account/logout/timeout";},' . ($settings['page-account-timeout']*60000) . ');</script>';
    }

    //
    // Check if there was a switch of customer (parent switching between child accounts)
    // This is a special case because of the redirects
    //
    if( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] == 'switch'
        && isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] != '' 
        && isset($ciniki['session']['customers'])
        && isset($ciniki['session']['customers'][$ciniki['request']['uri_split'][1]])
        ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageAccountSwitch', $breadcrumbs);
        return ciniki_web_generatePageAccountSwitch($ciniki, $settings, $ciniki['request']['business_id'], $ciniki['request']['uri_split'][1]);
    }

//    print "<pre>" . print_r($ciniki['request'], true) . "</pre>";
//    print "<pre>" . print_r($ciniki['business'], true) . "</pre>";
//    exit;

    //
    // Gather the submodule menu items
    //
    $submenu = array();
    foreach($ciniki['business']['modules'] as $module => $m) {
        list($pkg, $mod) = explode('.', $module);
        $rc = ciniki_core_loadMethod($ciniki, $pkg, $mod, 'web', 'accountSubMenuItems');
        if( $rc['stat'] == 'ok' ) {
            $fn = $rc['function_call'];
            $rc = $fn($ciniki, $settings, $ciniki['request']['business_id']);
            if( $rc['stat'] == 'ok' && isset($rc['submenu']) ) {
                $submenu = array_merge($submenu, $rc['submenu']);
            }
        }
    }

    //
    // Sort the menu items by priority
    //
    usort($submenu, function($a, $b) {
        if( $a['priority'] == $b['priority'] ) {
            return 0;
        }
        // Sort so largest priority is top of list or first menu item
        return ($a['priority'] < $b['priority'])?1:-1;
    });

    //
    // Check for a module to process the request
    //
    $requested_item = null;
    $base_url = $ciniki['request']['base_url'] . '/account';
    if( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] != '' ) {
        $requested_page_url = $base_url . '/' . $ciniki['request']['uri_split'][0];
        foreach($submenu as $item) {
            if( strncmp($requested_page_url, $item['url'], strlen($requested_page_url)) == 0 ) {
                $requested_item = $item;
                break;
            }
        }
    } 
    //
    // Nothing requested, default to the first item in the submenu
    //
    elseif( isset($submenu[0]) ) {
        $requested_item = $submenu[0];
        if( !isset($ciniki['request']['uri_split'][0]) ) {
            $ciniki['request']['uri_split'] = explode('/', preg_replace('#' . $base_url . '#', '', $requested_item['url'], 1));
            if( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] == '' ) {
                array_shift($ciniki['request']['uri_split']);
            }
        }
    } 

    if( $requested_item == null ) {
        return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'2907', 'msg'=>'Requested page not found.'));
    }

    //
    // Process the request
    //
    $article_title = '';
    $content = '';
    $rc = ciniki_core_loadMethod($ciniki, $requested_item['package'], $requested_item['module'], 'web', 'accountProcessRequest');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'2908', 'msg'=>'Requested page not found.', 'err'=>$rc['err']));
    }
    $fn = $rc['function_call'];
    $rc = $fn($ciniki, $settings, $ciniki['request']['business_id'], array(
        'page_title'=>'Account', 
        'breadcrumbs'=>$breadcrumbs,
        'base_url'=>$base_url,
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $page = $rc['page'];
    if( isset($page['breadcrumbs']) ) {
        $breadcrumbs = $page['breadcrumbs'];
    }
    $article_title = $page['title'];

    if( isset($settings['page-account-sidebar']) && $settings['page-account-sidebar'] != 'no' ) {
        $sidebar_menu = $submenu;
        $submenu = array();
    }

    //
    // Check if a container class was set
    //
    if( isset($page['container-class']) && $page['container-class'] != '' ) {
        if( !isset($ciniki['request']['page-container-class']) ) { 
            $ciniki['request']['page-container-class'] = $page['container-class'];
        } else {
            $ciniki['request']['page-container-class'] .= ' ' . $page['container-class'];
        }
    }

    //
    // Process the blocks of content before header incase require includes in header
    //
    $block_content = "<div class='entry-content'>\n";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processBlocks');
    if( isset($page['blocks']) ) {
        $rc = ciniki_web_processBlocks($ciniki, $settings, $ciniki['request']['business_id'], $page['blocks']);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $block_content .= $rc['content'];
    }
    $block_content .= "</div>";
    $block_content .= "</article>";

    //
    // Add the header
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageHeader');
    $rc = ciniki_web_generatePageHeader($ciniki, $settings, 'Account', $submenu);
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }
    $page_content = $rc['content'];
    
    //
    // Check if article title and breadcrumbs should be displayed above content
    //
    if( (isset($settings['theme']['header-article-title']) && $settings['theme']['header-article-title'] == 'yes')
        || (isset($settings['theme']['header-breadcrumbs']) && $settings['theme']['header-breadcrumbs'] == 'yes')
        ) {
        $page_content .= "<div class='page-header'>";
        if( isset($settings['theme']['header-article-title']) && $settings['theme']['header-article-title'] == 'yes' ) {
            $page_content .= "<h1 class='page-header-title'>" . $page['title'] . "</h1>";
        }
        if( isset($settings['theme']['header-breadcrumbs']) && $settings['theme']['header-breadcrumbs'] == 'yes' && isset($breadcrumbs) ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processBreadcrumbs');
            $rc = ciniki_web_processBreadcrumbs($ciniki, $settings, $ciniki['request']['business_id'], $breadcrumbs);
            if( $rc['stat'] == 'ok' ) {
                $page_content .= $rc['content'];
            }
        }
        $page_content .= "</div>";
    }

    $page_content .= "<div id='content'>";

    if( isset($settings['page-account-sidebar']) && $settings['page-account-sidebar'] == 'left' ) {
        //
        // Add the sidebar content
        //
        $page_content .= "<div class='sidebar-menu-toggle'>"
            . "<button type='button' id='sidebar-menu-toggle' class='sidebar-menu-toggle'><i class='fa fa-bars'></i></button>"
            . "</div>";
        $page_content .= "<aside id='sidebar-menu' class='col-left-narrow sidebar-menu'>";
        $page_content .= "<div class='aside-content sidebar-menu'>";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processBlockMenu');
        $rc = ciniki_web_processBlockMenu($ciniki, $settings, $ciniki['request']['business_id'], array('title'=>'', 'menu'=>$sidebar_menu));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $page_content .= $rc['content'];
        $page_content .= "</div>";
        $page_content .= "</aside>";

        $page_content .= "<article class='page col-right-wide'>\n";
    } elseif( isset($settings['page-account-sidebar']) && $settings['page-account-sidebar'] == 'right' ) {
        $page_content .= "<article class='page col-left-wide'>\n";
    } else {
        $page_content .= "<article class='page'>\n";
    }

    $page_content .= "<header class='entry-title'><h1 id='entry-title' class='entry-title'>$article_title</h1></header>";

    $page_content .= $block_content;

    if( isset($settings['page-account-sidebar']) && $settings['page-account-sidebar'] == 'right' ) {
        //
        // Add the sidebar content
        //
        $page_content .= "<div class='sidebar-menu-toggle'>"
            . "<button type='button' id='sidebar-menu-toggle' class='sidebar-menu-toggle'><i class='fa fa-bars'></i></button>"
            . "</div>";
        $page_content .= "<aside id='sidebar-menu' class='col-right-narrow sidebar-menu'>";
        $page_content .= "<div class='aside-content sidebar-menu'>";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processBlockMenu');
        $rc = ciniki_web_processBlockMenu($ciniki, $settings, $ciniki['request']['business_id'], array('title'=>'', 'menu'=>$sidebar_menu));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $page_content .= $rc['content'];
        $page_content .= "</div>";
        $page_content .= "</aside>";
    }

    $page_content .= "</div>";

    //
    // Add the footer
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageFooter');
    $rc = ciniki_web_generatePageFooter($ciniki, $settings);
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }
    $page_content .= $rc['content'];

    return array('stat'=>'ok', 'content'=>$page_content);
}
?>
