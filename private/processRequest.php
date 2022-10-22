<?php
//
// Description
// -----------
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_web_processRequest(&$ciniki) {

    $themes_root = "/ciniki-mods/web/themes";
    $themes_root_url = "/ciniki-web-themes";
    $preview = 'no';

    //
    // Setup the defaults
    //
    $ciniki['request']['tnid'] = 0;
    $ciniki['request']['page'] = '';
    $ciniki['request']['cache_url'] = '/ciniki-web-cache';
    $ciniki['request']['cache_dir'] = $ciniki['config']['ciniki.core']['modules_dir'] . '/web/cache';
    $ciniki['request']['layout_dir'] = $ciniki['config']['ciniki.core']['modules_dir'] . '/web/layouts';
    $ciniki['request']['layout_url'] = '/ciniki-web-layouts';
    $ciniki['request']['theme_dir'] = $ciniki['config']['ciniki.core']['modules_dir'] . '/web/themes';
    $ciniki['request']['theme_url'] = '/ciniki-web-themes';
    $ciniki['request']['inline_javascript'] = '';

    $ciniki['response'] = array('head'=>array(
        'links'=>array(),
        'scripts'=>array(),
        'og'=>array(
            'url'=>'',
            'title'=>'',
            'site_name'=>'',
            'image'=>'',
            'description'=>'',
            'type'=>'',
            ),
        ));

    $ciniki['tenant'] = array('modules'=>array());
    $ciniki['syncqueue'] = array();
    $ciniki['emailqueue'] = array();

    //
    // Required variable in this script
    //
    $uri = preg_replace('/^\//', '', $_SERVER['REQUEST_URI']);

    // 
    // Check if this is a preview request
    //
    if( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] == 'preview'  ) {
        $preview = 'yes';
        $uris = $ciniki['request']['uri_split'];
        array_shift($uris);
        $ciniki['request']['uri_split'] = $uris;
    }

    //
    // Setup the cache dir for the master tenant, incase no other tenant is found
    //
    $strsql = "SELECT uuid "
        . "FROM ciniki_tenants "
        . "WHERE id = '" . ciniki_core_dbQuote($ciniki, $ciniki['config']['ciniki.core']['master_tnid']) . "' ";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.tenants', 'tenant');
    if( $rc['stat'] == 'ok' && isset($rc['tenant']['uuid']) ) {
        $uuid = $rc['tenant']['uuid'];
        $ciniki['tenant']['cache_dir'] = $ciniki['config']['ciniki.core']['cache_dir'] . '/' . $uuid[0] . '/' . $uuid;
        $ciniki['tenant']['web_cache_dir'] = $ciniki['config']['ciniki.core']['modules_dir'] . '/web/cache/' . $uuid[0] . '/' . $uuid;
        $ciniki['tenant']['web_cache_url'] = $ciniki['request']['cache_url'] . '/' . $uuid[0] . '/' . $uuid;
    }

    //
    // Determine which site and page should be displayed
    // FIXME: Check for redirects from sitename or domain names to primary domain name.
    //
    if( isset($_SERVER['HTTP_HOST']) && $ciniki['config']['ciniki.web']['master.domain'] != $_SERVER['HTTP_HOST'] 
        && (!isset($ciniki['config']['ciniki.web']['shop.domain']) || $ciniki['config']['ciniki.web']['shop.domain'] != $_SERVER['HTTP_HOST'])
        ) {
        //
        // Lookup client domain in database
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'lookupClientDomain');
        $rc = ciniki_web_lookupClientDomain($ciniki, $_SERVER['HTTP_HOST'], 'domain');
        if( $rc['stat'] != 'ok' ) { 
            // Assume master tenant
    //      print_error($rc, 'unknown tenant ' . $ciniki['request']['uri_split'][0]);
    //      exit;
        }
        //
        // If a tenant if found, then setup the details
        //
        if( $rc['stat'] == 'ok' ) {
            $ciniki['request']['tnid'] = $rc['tnid'];
            if( isset($rc['domain']) && $rc['domain'] != '' ) {
                $ciniki['tenant']['domain'] = $rc['domain'];
            }
            if( isset($rc['sitename']) && $rc['sitename'] != '' ) {
                $ciniki['tenant']['sitename'] = $rc['sitename'];
            }
            $ciniki['tenant']['uuid'] = $rc['tenant_uuid'];
            $ciniki['tenant']['modules'] = $rc['modules'];
            $ciniki['tenant']['pages'] = $rc['pages'];
            $ciniki['tenant']['module_pages'] = $rc['module_pages'];
            if( isset($rc['redirect']) && $rc['redirect'] != '' && $preview == 'no' ) {
                Header('HTTP/1.1 301 Moved Permanently'); 
                Header('Location: http' . ($rc['forcessl']=='yes'?'s':'') . '://' . $rc['redirect'] . $_SERVER['REQUEST_URI']);
                exit;
            }
            if( isset($rc['forcessl']) && $rc['forcessl'] == 'yes' && $ciniki['request']['ssl'] != 'yes' ) {
                Header('HTTP/1.1 301 Moved Permanently'); 
                Header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
                exit;
            }
            //
            // Check if this tenant is a reseller
            //
            $ciniki['request']['reseller'] = 'no';
            if( isset($rc['reseller']) && $rc['reseller'] == 'yes' ) {
                $ciniki['request']['reseller'] = 'yes';
                $ciniki['request']['base_url'] = '';
            } else {
                //
                // Only setup page and shift uris if this is a master domain
                //
                $ciniki['request']['page'] = $ciniki['request']['uri_split'][0];
                if( $ciniki['request']['page'] != '' ) {
                    $uris = $ciniki['request']['uri_split'];
                    array_shift($uris);
                    $ciniki['request']['uri_split'] = $uris;
                }
                $ciniki['request']['base_url'] = '';
            }
            $ciniki['request']['domain'] = $_SERVER['HTTP_HOST'];
            $ciniki['request']['domain_base_url'] = 'http://' . $_SERVER['HTTP_HOST'];
            $ciniki['request']['ssl_domain_base_url'] = 'http://' . $_SERVER['HTTP_HOST'];
        }
    }

    //
    // Start the session here so we have access to _SESSION when checking if redirect
    //
    session_start();

    // 
    // If nothing was found, assume the master tenant
    //
    if( $ciniki['request']['tnid'] == 0 || $ciniki['request']['reseller'] == 'yes' ) {
        //
        // Check if should be redirected to secure website
        //
        if( $ciniki['request']['tnid'] == 0 ) {
            if( isset($ciniki['config']['ciniki.web']['master.domain.forcessl']) 
                && $ciniki['config']['ciniki.web']['master.domain.forcessl'] == 'on' 
                && $ciniki['request']['ssl'] != 'yes'
                ) {
                Header('HTTP/1.1 301 Moved Permanently'); 
                Header('Location: https://' . $ciniki['config']['ciniki.web']['master.domain'] . $_SERVER['REQUEST_URI']);
            }
            $ciniki['request']['domain'] = $ciniki['config']['ciniki.web']['master.domain'];
            $ciniki['request']['domain_base_url'] = 'http://' . $ciniki['config']['ciniki.web']['master.domain'];
            $ciniki['request']['ssl_domain_base_url'] = 'https://' . $ciniki['config']['ciniki.web']['master.domain'];
        }
        //
        // Check which page, or if they requested a clients website
        //
        if( $ciniki['request']['tnid'] == 0 && ($uri == '' || (isset($uri[0]) && $uri[0] == '?')) ) {
            if( isset($ciniki['config']['ciniki.web']['shop.domain']) && isset($_SERVER['HTTP_HOST']) 
                && $_SERVER['HTTP_HOST'] == $ciniki['config']['ciniki.web']['shop.domain'] && isset($ciniki['config']['ciniki.core']['shop_tnid']) 
                ) {
                $ciniki['request']['page'] = 'home';
                $ciniki['request']['tnid'] = $ciniki['config']['ciniki.core']['shop_tnid'];
                $ciniki['request']['base_url'] = '';
            } else {
    //            $ciniki['request']['page'] = 'masterindex';
                $ciniki['request']['page'] = 'home';
                $ciniki['request']['tnid'] = $ciniki['config']['ciniki.core']['master_tnid'];
                $ciniki['request']['base_url'] = '';
            }
    //
    // Removed on Apr 15, 2018 -- This allows for any pages on master tenant
    //
    /*    } elseif( $ciniki['request']['tnid'] == 0 && 
            ($ciniki['request']['uri_split'][0] == 'about' 
                || $ciniki['request']['uri_split'][0] == 'contact'
                || $ciniki['request']['uri_split'][0] == 'features'
                || $ciniki['request']['uri_split'][0] == 'signup'
                || $ciniki['request']['uri_split'][0] == 'documentation'
                || $ciniki['request']['uri_split'][0] == 'support'
                || $ciniki['request']['uri_split'][0] == 'products'
                || $ciniki['request']['uri_split'][0] == 'recipes'
                || $ciniki['request']['uri_split'][0] == 'blog'
                || $ciniki['request']['uri_split'][0] == 'gallery'
                || $ciniki['request']['uri_split'][0] == 'writings'
                || $ciniki['request']['uri_split'][0] == 'downloads'
                || $ciniki['request']['uri_split'][0] == 'faq'
                || $ciniki['request']['uri_split'][0] == 'directory'
                || $ciniki['request']['uri_split'][0] == 'collection'
                || $ciniki['request']['uri_split'][0] == 'tutorials'
    //          || $ciniki['request']['uri_split'][0] == 'plans'
                )
            ) {
            $ciniki['request']['page'] = $ciniki['request']['uri_split'][0];
            $ciniki['request']['tnid'] = $ciniki['config']['ciniki.core']['master_tnid'];
            $ciniki['request']['base_url'] = '';
            $uris = $ciniki['request']['uri_split'];
            array_shift($uris);
            $ciniki['request']['uri_split'] = $uris;
            //
            // Lookup tenant modules in database
            //
            ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'getActiveModules');
            $rc = ciniki_tenants_getActiveModules($ciniki, $ciniki['request']['tnid']);
            if( $rc['stat'] != 'ok' ) {
                // Generate the master tenant 404 page
                ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generateMaster404');
                $rc = ciniki_web_generateMaster404($ciniki, $rc);
                if( isset($rc['content']) ) {
                    print $rc['content'];
                } else {
                    print_error($rc, 'Unknown tenant ' . $ciniki['request']['uri_split'][0]);
                }
                exit;
            }
            $ciniki['tenant']['uuid'] = '';
            $ciniki['tenant']['modules'] = $rc['modules'];
    */
        } elseif( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] != '' ) {
            //
            // If client is reseller, or master domain check for a sitename below domain,
            //
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'lookupClientDomain');
            if( isset($ciniki['request']['reseller']) && $ciniki['request']['reseller'] == 'yes' ) {
                $rc = ciniki_web_lookupClientDomain($ciniki, $ciniki['request']['uri_split'][0], 'sitename', $ciniki['request']['tnid']);
            } else {
                $rc = ciniki_web_lookupClientDomain($ciniki, $ciniki['request']['uri_split'][0], 'sitename', $ciniki['config']['ciniki.core']['master_tnid']);
            }
            //
            // Did not find a resold tenant
            //
            if( $rc['stat'] != 'ok' ) {
                //
                // Only generate 404 if a master domain, all others that are resellers let continue to normal processing
                //
                if( $ciniki['request']['uri_split'][0] == 'robots.txt' ) {
                    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageRobots');
                    $rc = ciniki_web_generatePageRobots($ciniki, array());
                } elseif( $ciniki['request']['tnid'] == 0 ) {
                    $ciniki['request']['page'] = $ciniki['request']['uri_split'][0];
                    $ciniki['request']['tnid'] = $ciniki['config']['ciniki.core']['master_tnid'];
                    $ciniki['request']['base_url'] = '';
                    $uris = $ciniki['request']['uri_split'];
                    array_shift($uris);
                    $ciniki['request']['uri_split'] = $uris;
                } else {
                    //
                    // Only setup page and shift uris if this is a reseller and did not find sitename
                    //
                    $ciniki['request']['page'] = $ciniki['request']['uri_split'][0];
                    if( $ciniki['request']['page'] != '' ) {
                        $uris = $ciniki['request']['uri_split'];
                        array_shift($uris);
                        $ciniki['request']['uri_split'] = $uris;
                    }
                }
            } 
            //
            // Found a resold tenant based on sitename
            //
            elseif( $rc['stat'] == 'ok' ) {
                $ciniki['request']['tnid'] = $rc['tnid'];
                $ciniki['tenant']['uuid'] = $rc['tenant_uuid'];
                $ciniki['tenant']['modules'] = $rc['modules'];
                $ciniki['tenant']['pages'] = $rc['pages'];
                $ciniki['tenant']['module_pages'] = $rc['module_pages'];
                if( isset($rc['domain']) ) {
                    $ciniki['tenant']['domain'] = $rc['domain'];
                }
                $ciniki['request']['base_url'] = ($preview=='yes'?'/preview/':'/') . $ciniki['request']['uri_split'][0];
                if( $ciniki['request']['base_url'] != '' ) {
                    foreach($ciniki['tenant']['module_pages'] as $pid => $page) {
                        $ciniki['tenant']['module_pages'][$pid]['base_url'] = $ciniki['request']['base_url'] . $page['base_url'];
                    }
                }
                $ciniki['request']['domain'] = $ciniki['config']['ciniki.web']['master.domain'];
                $ciniki['request']['domain_base_url'] = 'http://' . $ciniki['config']['ciniki.web']['master.domain'] . '/' . $ciniki['request']['uri_split'][0];
                $ciniki['request']['ssl_domain_base_url'] = 'http://' . $ciniki['config']['ciniki.web']['master.domain'] . '/' . $ciniki['request']['uri_split'][0];
                //
                // If the customer has a primary domain, then make sure the request is redirected to the primary domain
                //
                if( isset($rc['redirect']) && $rc['redirect'] != '' && $preview == 'no' 
                    && (!isset($ciniki['config']['ciniki.web']['redirects']) || $ciniki['config']['ciniki.web']['redirects'] != 'off')
                    && (!isset($ciniki['config']['ciniki.web']['shop.domain']) || $ciniki['config']['ciniki.web']['shop.domain'] != $_SERVER['HTTP_HOST'])
                    ) {
                    // 
                    // If going to shop domain, only redirect if not logged in or going to account page. Otherwise make sure they
                    // get redirected back to main website so search engines don't see site twice
                    //
                    Header('HTTP/1.1 301 Moved Permanently'); 
                    Header('Location: http' . ($rc['forcessl']=='yes'?'s':'') . '://' . $rc['redirect'] . preg_replace('/^\/[^\/]+/', '', $_SERVER['REQUEST_URI']));
                    exit;
                }
                elseif( isset($rc['domain']) && $rc['domain'] != '' && $preview == 'no'
                    && (isset($ciniki['config']['ciniki.web']['redirects']) && $ciniki['config']['ciniki.web']['redirects'] != 'off')
                    && (!isset($ciniki['config']['ciniki.web']['shop.domain']) || $ciniki['config']['ciniki.web']['shop.domain'] != $_SERVER['HTTP_HOST'])
                    ) {
                    Header('HTTP/1.1 301 Moved Permanently'); 
                    Header('Location: http' . ($rc['forcessl']=='yes'?'s':'') . '://' . $rc['domain'] . preg_replace('/^\/[^\/]+/', '', $_SERVER['REQUEST_URI']));
                    exit;
                }
                //
                // If they have requested the shop domain, check to make sure they are logged in or going to account page, otherwise they should be redirected back to main site.
                //
                if( isset($ciniki['config']['ciniki.web']['shop.domain']) 
                    && $ciniki['config']['ciniki.web']['shop.domain'] == $_SERVER['HTTP_HOST']                          // Going to shop domain
                    && (!isset($_SESSION['customer']['id']) || $_SESSION['customer']['id'] == 0)      // Not logged in
                    && !preg_match("/^\/[^\/]+\/account/", $_SERVER['REQUEST_URI'])                                     // Not going to account page
                    ) {
                    //
                    // Check if a redirect is specified
                    //
                    if( isset($rc['redirect']) && $rc['redirect'] != '' && $preview == 'no' ) {
                        Header('HTTP/1.1 301 Moved Permanently'); 
                        Header('Location: http' . ($rc['forcessl']=='yes'?'s':'') . '://' . $rc['redirect'] . preg_replace('/^\/[^\/]+/', '', $_SERVER['REQUEST_URI']));
                        exit;
                    } 
                    elseif( isset($rc['domain']) && $rc['domain'] != '' && $preview == 'no' ) {
                        Header('HTTP/1.1 301 Moved Permanently'); 
                        Header('Location: http' . ($rc['forcessl']=='yes'?'s':'') . '://' . $rc['domain'] . preg_replace('/^\/[^\/]+/', '', $_SERVER['REQUEST_URI']));
                        exit;
                    } 
                    //
                    // No domain, redirect
                    else {
                        Header('HTTP/1.1 301 Moved Permanently'); 
                        Header('Location: http://' . $ciniki['config']['ciniki.web']['master.domain'] . $_SERVER['REQUEST_URI']);
                        exit;
                    }
                    if( $rc['forcessl'] == 'yes' ) {
                        Header('HTTP/1.1 301 Moved Permanently'); 
                        Header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
                        exit;
                    }
                }

                //
                // Remove the client name from the URI list
                //
                if( count($ciniki['request']['uri_split']) > 1 ) {
                    $uris = $ciniki['request']['uri_split'];
                    array_shift($uris);
                    $ciniki['request']['page'] = $uris[0];
                    array_shift($uris);
                    $ciniki['request']['uri_split'] = $uris;
                } else {
                    $ciniki['request']['url_split'] = array();
                    $ciniki['request']['page'] = '';
                }
            }
        }
    }

    //
    // Lookup master tenant modules in database
    //
    if( $ciniki['request']['tnid'] == $ciniki['config']['ciniki.core']['master_tnid'] ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'getActiveModules');
        $rc = ciniki_tenants_getActiveModules($ciniki, $ciniki['request']['tnid']);
        if( $rc['stat'] != 'ok' ) {
            // Generate the master tenant 404 page
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generateMaster404');
            $rc = ciniki_web_generateMaster404($ciniki, $rc);
            if( isset($rc['content']) ) {
                print $rc['content'];
            } else {
                print_error($rc, 'Unknown tenant ' . $ciniki['request']['uri_split'][0]);
            }
            exit;
        }
        $ciniki['tenant']['uuid'] = '';
        $ciniki['tenant']['modules'] = $rc['modules'];
    }

    //
    // Make sure shop URLs are forced to SSL
    //
    if( isset($ciniki['config']['ciniki.web']['shop.domain']) && isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] == $ciniki['config']['ciniki.web']['shop.domain'] 
        && ((isset($_SERVER['HTTP_CLUSTER_HTTPS']) && $_SERVER['HTTP_CLUSTER_HTTPS'] != 'on') 
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
            || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != '443')
            )
        ) {
        //
        // Force redirect to SSL
        //
        Header('HTTP/1.1 301 Moved Permanently'); 
        Header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        exit;
    }

    //
    // Setup the session
    //
    $ciniki['session'] = array();
    $ciniki['session']['change_log_id'] = 'web.' . date('Ymd.His');
    $ciniki['session']['user'] = array('id'=>'-2');
    // If the session is for the current tenant
    if( isset($_SESSION['tnid']) && $_SESSION['tnid'] == $ciniki['request']['tnid'] ) {
        if( isset($_SESSION['login']) ) {
            $ciniki['session']['login'] = $_SESSION['login'];
        }
        if( isset($_SESSION['customer']) ) {
            $ciniki['session']['customer'] = $_SESSION['customer'];
        }
        if( isset($_SESSION['account']) ) {
            $ciniki['session']['account'] = $_SESSION['account'];
        }
        if( isset($_SESSION['customers']) ) {
            $ciniki['session']['customers'] = $_SESSION['customers'];
        }
        if( isset($_SESSION['children']) ) {
            $ciniki['session']['children'] = $_SESSION['children'];
        }
        if( isset($_SESSION['cart']) ) {
            $ciniki['session']['cart'] = $_SESSION['cart'];
        }

        //
        // Load each modules session information
        //
        foreach($ciniki['tenant']['modules'] as $module => $m) {
            if( isset($_SESSION[$module]) ) {
                $ciniki['session'][$module] = $_SESSION[$module];
            }
        }
    } else {
        if( isset($_SESSION['login']) ) { unset($_SESSION['login']); };
        if( isset($_SESSION['customer']) ) { unset($_SESSION['customer']); };
        if( isset($_SESSION['account']) ) { unset($_SESSION['account']); };
        if( isset($_SESSION['customers']) ) { unset($_SESSION['customers']); };
        if( isset($_SESSION['cart']) ) { unset($_SESSION['cart']); };
        if( isset($ciniki['session']['login']) ) { unset($ciniki['session']['login']); };
        if( isset($ciniki['session']['customer']) ) { unset($ciniki['session']['customer']); };
        if( isset($ciniki['session']['customers']) ) { unset($ciniki['session']['customers']); };
        if( isset($ciniki['session']['cart']) ) { unset($ciniki['session']['cart']); };

        //
        // Unload each sessions information
        //
        foreach($ciniki['tenant']['modules'] as $module => $m) {
            if( isset($ciniki['session'][$module]) ) {
                unset($ciniki['session'][$module]);
            }
            if( isset($_SESSION[$module]) ) {
                unset($_SESSION[$module]);
            }
        }
    }
    $_SESSION['tnid'] = $ciniki['request']['tnid'];
    $ciniki['session']['tnid'] = $ciniki['request']['tnid'];

    //
    // Setup the cache dir for the tenant
    //
    if( isset($ciniki['tenant']['uuid']) && $ciniki['tenant']['uuid'] != '' ) {
        $ciniki['tenant']['cache_dir'] = $ciniki['config']['ciniki.core']['cache_dir'] . '/'
            . $ciniki['tenant']['uuid'][0] . '/' . $ciniki['tenant']['uuid'];
        $ciniki['tenant']['web_cache_dir'] = $ciniki['config']['ciniki.core']['modules_dir'] . '/web/cache/'
            . $ciniki['tenant']['uuid'][0] . '/' . $ciniki['tenant']['uuid'];
        $ciniki['tenant']['web_cache_url'] = $ciniki['request']['cache_url'] . '/'
            . $ciniki['tenant']['uuid'][0] . '/' . $ciniki['tenant']['uuid'];
    }

    //
    // Get the details for the tenant
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'web', 'details');
    $rc = ciniki_tenants_web_details($ciniki, $ciniki['request']['tnid']);
    if( $rc['stat'] != 'ok' ) {
        // Generate the master tenant 404 page
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generateMaster404');
        $rc = ciniki_web_generateMaster404($ciniki, $rc);
        if( isset($rc['content']) ) {
            print $rc['content'];
        } else {
            print_error($rc, 'Website not configured');
        }
        exit;
    }
    $ciniki['tenant']['details'] = $rc['details'];
    if( isset($rc['details']) ) {
        $ciniki['tenant']['social'] = $rc['social'];
    }

    //
    // Get the web settings for the tenant
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'settings');
    $rc = ciniki_web_settings($ciniki, $ciniki['request']['tnid']);
    if( $rc['stat'] != 'ok' ) {
        // Generate the master tenant 404 page
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generateMaster404');
        $rc = ciniki_web_generateMaster404($ciniki, $rc);
        if( isset($rc['content']) ) {
            print $rc['content'];
        } else {
            print_error($rc, 'Website not configured');
        }
        exit;
    }
    $settings = $rc['settings'];

    if( isset($settings['site-theme']) && $settings['site-theme'] == 'twentyone' ) {
        $settings['default-image-width'] = 1200;
        $settings['default-image-height'] = 1800;
        $settings['default-image-thumbnail-version'] = 'thumbnail';
        $settings['default-image-thumbnail-width'] = 800;
        // Force layout to be modern layout
        $settings['site-layout'] = 'twentyone';
    }

    if( isset($settings['site-header-og-image']) && $settings['site-header-og-image'] > 0 ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
        $rc = ciniki_web_getScaledImageURL($ciniki, $settings['site-header-og-image'], 'original', '0', '300', '85');
        if( $rc['stat'] == 'ok' ) {
            $ciniki['response']['head']['og']['image'] = $rc['domain_url'];
        }
    } elseif( isset($settings['site-header-image']) && $settings['site-header-image'] > 0 ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
        $rc = ciniki_web_getScaledImageURL($ciniki, $settings['site-header-image'], 'original', '0', '300', '85');
        if( $rc['stat'] == 'ok' ) {
            $ciniki['response']['head']['og']['image'] = $rc['domain_url'];
        }
    }

    //
    // Check if ssl is enabled on domain
    //
    if( (isset($settings['site-ssl-active']) && $settings['site-ssl-active'] == 'yes')
        || (isset($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] == 'https') 
        ) {
        $ciniki['request']['ssl_domain_base_url'] = preg_replace('/^http:/', 'https:', $ciniki['request']['ssl_domain_base_url']);
    } 

    //
    // Check for the SSL Shop configuration
    //
    if( isset($settings['site-ssl-shop']) && $settings['site-ssl-shop'] == 'yes' && isset($ciniki['config']['ciniki.web']['shop.domain']) && $ciniki['config']['ciniki.web']['shop.domain'] != '' ) {
        $ciniki['request']['ssl_domain_base_url'] = 'https://' . $ciniki['config']['ciniki.web']['shop.domain'] . '/' . $ciniki['tenant']['details']['sitename'];
    }

    // print "<pre>"; print_r($ciniki); print "</pre>";

    // Theme, pages, settings

    //
    // Check if no page specified, which means home page
    //
    if( $ciniki['request']['page'] == '' ) {
        $ciniki['request']['page'] = 'home';
    }

    //
    // Check if home page is a redirect to another page
    //
    if( isset($ciniki['request']['page']) && $ciniki['request']['page'] == 'home' 
        && isset($settings['page-home-active']) && $settings['page-home-active'] == 'yes' 
        && isset($settings['page-home-redirect']) && $settings['page-home-redirect'] != '' ) {
        $ciniki['request']['page'] = $settings['page-home-redirect'];
    }
    //
    // If home page is not active, search for the next page to call home
    //
    if( isset($ciniki['request']['page']) && $ciniki['request']['page'] == 'home' 
        && (!isset($settings['page-home-active']) || $settings['page-home-active'] != 'yes') 
        && (!isset($ciniki['tenant']['modules']['ciniki.web']['flags']) || ($ciniki['tenant']['modules']['ciniki.web']['flags']&0x0240) == 0)
        ) {
        if( isset($settings['page-about-active']) && $settings['page-about-active'] == 'yes' ) {
            $ciniki['request']['page'] = 'about';
        } elseif( isset($settings['page-features-active']) && $settings['page-features-active'] == 'yes' ) {
            $ciniki['request']['page'] = 'features';
        } elseif( isset($settings['page-blog-active']) && $settings['page-blog-active'] == 'yes' ) {
            $ciniki['request']['page'] = 'blog';
        } elseif( isset($settings['page-gallery-active']) && $settings['page-gallery-active'] == 'yes' ) {
            $ciniki['request']['page'] = 'gallery';
        } elseif( isset($settings['page-writings-active']) && $settings['page-writings-active'] == 'yes' ) {
            $ciniki['request']['page'] = 'writings';
        } elseif( isset($settings['page-contact-active']) && $settings['page-contact-active'] == 'yes' ) {
            $ciniki['request']['page'] = 'contact';
        } elseif( isset($settings['page-events-active']) && $settings['page-events-active'] == 'yes' ) {
            $ciniki['request']['page'] = 'events';
        } elseif( isset($settings['page-members-active']) && $settings['page-members-active'] == 'yes' ) {
            $ciniki['request']['page'] = 'members';
        } elseif( isset($settings['page-workshops-active']) && $settings['page-workshops-active'] == 'yes' ) {
            $ciniki['request']['page'] = 'workshops';
//        } elseif( isset($settings['page-directory-active']) && $settings['page-directory-active'] == 'yes' ) {
//            $ciniki['request']['page'] = 'directory';
        } elseif( isset($settings['page-links-active']) && $settings['page-links-active'] == 'yes' ) {
            $ciniki['request']['page'] = 'links';
        } else {
            // Generate the master tenant 404 page
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generateMaster404');
            $rc = ciniki_web_generateMaster404($ciniki, null);
            if( isset($rc['content']) ) {
                print $rc['content'];
            } else {
                print_error($rc, 'Website not configured');
            }
            exit;
        }
    }

    //
    // Load other packages pages, these will be used by generatePageHeader
    //
    $ciniki['tenant']['pages'] = array();
    if( isset($ciniki['config']['ciniki.core']['packages']) 
        && $ciniki['config']['ciniki.core']['packages'] != 'ciniki' 
        ) {
        $packages = explode(',', $ciniki['config']['ciniki.core']['packages']);
        $page = '';
        foreach($packages as $pkg) {
            if( $pkg != 'ciniki' ) {
                $rc = ciniki_core_loadMethod($ciniki, $pkg, 'web', 'private', 'pages');
                if( $rc['stat'] == 'ok' ) {
                    $fn = $pkg . '_web_pages';
                    $rc = $fn($ciniki);
                    if( isset($rc['pages']) ) {
                        foreach($rc['pages'] as $permalink => $page) {
                            $ciniki['tenant']['pages'][$permalink] = array('pkg'=>$pkg, 
                                'fn'=>$page['fn'],
                                'active'=>$page['active'],
                                'title'=>$page['title'],
                                'permalink'=>$permalink,
                                );
                        }
                    }
                }
            }
        }
    }

    //
    // Check for exact redirects
    //
    if( ciniki_core_checkModuleFlags($ciniki, 'ciniki.web', 0x04000000) ) {
        $url = $_SERVER['REQUEST_URI'];
        $u = preg_split('/\?/', $url);
        $url = $u[0];
        if( $ciniki['request']['base_url'] != '' ) {
            $url = preg_replace('#' . $ciniki['request']['base_url'] . '#i', '', $url, 1);
        }
        $strsql = "SELECT newurl "
            . "FROM ciniki_web_redirects "
            . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['tnid']) . "' "
            . "AND oldurl = '" . ciniki_core_dbQuote($ciniki, $url) . "' "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.web', 'redirect');
        if( $rc['stat'] == 'ok' && isset($rc['redirect']['newurl']) ) {
            Header('HTTP/1.1 301 Moved Permanently'); 
            if( preg_match("/^http/", $rc['redirect']['newurl']) ) {
                Header('Location: ' . $rc['redirect']['newurl']);
            } else {
                Header('Location: ' . $ciniki['request']['domain_base_url'] . $rc['redirect']['newurl']);
            }
            exit;
        }
    }

    //
    // Check if website has been configured
    //

    //
    // Check if website menu is set by pages menu
    //
    $pages_menu = 'no';
    if( isset($ciniki['tenant']['modules']['ciniki.web']['flags']) && ($ciniki['tenant']['modules']['ciniki.web']['flags']&0x0200) > 0 ) {
        $pages_menu = 'yes';
    }

    // FIRST check if this is a defined page, otherwise check modules
    // 
    // If Page is enabled and pages menu is used, then search the pages first before checking module pages.
    // This is done so control is handled to generatePage by default
    //
    $found = 'no';
    if( $pages_menu == 'yes' && $ciniki['request']['page'] != '' 
        && isset($ciniki['tenant']['modules']['ciniki.web']['flags'])
        && ($ciniki['tenant']['modules']['ciniki.web']['flags']&0x40) > 0 ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePage');
        $rc = ciniki_web_generatePage($ciniki, $settings);
        if( $rc['stat'] == 'ok' || $rc['stat'] == 'exit' ) {
            $found = 'yes';
        }
    }

    //
    // If custom pages not found, then check other modules
    //
    if( $found == 'no' ) {
        //
        // Process the request
        //
        // Master Home page
        if( $ciniki['request']['page'] == 'masterindex' && isset($settings['page-home-active']) && $settings['page-home-active'] == 'yes' ) {
            require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generateMasterIndex.php');
            $rc = ciniki_web_generateMasterIndex($ciniki, $settings);
        } 
        // Shop Home page
        elseif( $ciniki['request']['page'] == 'shopindex' && isset($settings['page-shop-active']) && $settings['page-shop-active'] == 'yes' ) {
            require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generateShopIndex.php');
            $rc = ciniki_web_generateShopIndex($ciniki, $settings);
        } 
        // Signup Page
        elseif( $ciniki['request']['page'] == 'signup' && isset($settings['page-signup-active']) && $settings['page-signup-active'] == 'yes' ) {
            require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageSignup.php');
            $rc = ciniki_web_generatePageSignup($ciniki, $settings);
        } 
        // API Page
        elseif( $ciniki['request']['page'] == 'api' ) {
            require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageAPI.php');
            $ciniki['response']['format'] = 'json';
            $rc = ciniki_web_generatePageAPI($ciniki, $settings);
        } 
        // Home Page
        elseif( $ciniki['request']['page'] == 'home' 
            && isset($settings['page-home-active']) && $settings['page-home-active'] == 'yes' ) {
            require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageHome.php');
            $rc = ciniki_web_generatePageHome($ciniki, $settings);
        } 
        // Search
        elseif( $ciniki['request']['page'] == 'search' 
            && isset($settings['page-search-active']) && $settings['page-search-active'] == 'yes' 
            ) {
            require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageSearch.php');
            $rc = ciniki_web_generatePageSearch($ciniki, $settings);
        } 
        // Contact
        elseif( $ciniki['request']['page'] == 'contact' 
            && isset($settings['page-contact-active']) && $settings['page-contact-active'] == 'yes' ) {
            require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageContact.php');
            $rc = ciniki_web_generatePageContact($ciniki, $settings);
        } 
        // Account
        elseif( $ciniki['request']['page'] == 'account' 
            && isset($settings['page-account-active']) && $settings['page-account-active'] == 'yes' ) {
            require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageAccount.php');
            $rc = ciniki_web_generatePageAccount($ciniki, $settings);
        } 
        // Cart
        elseif( $ciniki['request']['page'] == 'cart' 
            && isset($settings['page-cart-active']) && $settings['page-cart-active'] == 'yes' ) {
            require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageCart.php');
            $rc = ciniki_web_generatePageCart($ciniki, $settings);
        } 
        // Process links embedded in emails
        elseif( $ciniki['request']['page'] == 'mail' && isset($ciniki['tenant']['modules']['ciniki.mail']) ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generateModulePage');
            $rc = ciniki_web_generateModulePage($ciniki, $settings, $ciniki['request']['tnid'], 'ciniki.mail');
        } 

        // Process trade alerts pages
        elseif( $ciniki['request']['page'] == 'tradealerts' && isset($ciniki['tenant']['modules']['ciniki.tradealerts']) ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generateModulePage');
            $rc = ciniki_web_generateModulePage($ciniki, $settings, $ciniki['request']['tnid'], 'ciniki.tradealerts');
        } 

        // Process trade alerts pages
        elseif( $ciniki['request']['page'] == 'subscriptions' && isset($ciniki['tenant']['modules']['ciniki.subscriptions']) ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generateModulePage');
            $rc = ciniki_web_generateModulePage($ciniki, $settings, $ciniki['request']['tnid'], 'ciniki.subscriptions');
        } 

        // 
        // If Page is enabled and pages menu is used, then search the pages first before checking module pages.
        // This is done so control is handled to generatePage by default
        //
        //elseif( $pages_menu == 'yes' && $ciniki['request']['page'] != '' 
        //  && isset($ciniki['tenant']['modules']['ciniki.web']['flags'])
        //  && ($ciniki['tenant']['modules']['ciniki.web']['flags']&0x40) > 0 ) {
        //  ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePage');
        //  $rc = ciniki_web_generatePage($ciniki, $settings);
        //}

        // About
        elseif( $ciniki['request']['page'] == 'about' 
            && (isset($settings['page-about-active']) && $settings['page-about-active'] == 'yes')
                || ( isset($ciniki['request']['uri_split'][0])
                    && isset($settings['page-about-' . $ciniki['request']['uri_split'][0] . '-active'])
                    && $settings['page-about-' . $ciniki['request']['uri_split'][0] . '-active'] == 'yes'
                )) {
        //  require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageAbout.php');
        //  $rc = ciniki_web_generatePageAbout($ciniki, $settings);
            require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageInfo.php');
            $rc = ciniki_web_generatePageInfo($ciniki, $settings, 'about');
        } 
        // Features
        elseif( $ciniki['request']['page'] == 'features' 
            && isset($settings['page-features-active']) && $settings['page-features-active'] == 'yes' ) {
            require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageFeatures.php');
            $rc = ciniki_web_generatePageFeatures($ciniki, $settings);
        } 
        // Exhibitions
        elseif( $ciniki['request']['page'] == 'exhibitions' 
            && isset($settings['page-artgalleryexhibitions-active']) && $settings['page-artgalleryexhibitions-active'] == 'yes' ) {
            //require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageExhibitions.php');
            //$rc = ciniki_web_generatePageExhibitions($ciniki, $settings);
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generateModulePage');
            $rc = ciniki_web_generateModulePage($ciniki, $settings, $ciniki['request']['tnid'], 'ciniki.artgallery');
        }
        // Exhibitors
        elseif( $ciniki['request']['page'] == 'exhibitors' 
            && isset($settings['page-exhibitions-exhibitors-active']) && $settings['page-exhibitions-exhibitors-active'] == 'yes' ) {
            require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageExhibitors.php');
            $rc = ciniki_web_generatePageExhibitors($ciniki, $settings);
        }
        // Sponsors
        elseif( $ciniki['request']['page'] == 'sponsors' 
            && ( 
                (isset($settings['page-exhibitions-sponsors-active']) && $settings['page-exhibitions-sponsors-active'] == 'yes') 
                || (isset($settings['page-sponsors-active']) && $settings['page-sponsors-active'] == 'yes') 
            ) ) {
            require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageSponsors.php');
            $rc = ciniki_web_generatePageSponsors($ciniki, $settings);
        }
        // Sponsors
        elseif( $ciniki['request']['page'] == 'tour' 
            && isset($settings['page-exhibitions-tourexhibitors-active']) && $settings['page-exhibitions-tourexhibitors-active'] == 'yes' 
            ) {
            require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageTourExhibitors.php');
            $rc = ciniki_web_generatePageTourExhibitors($ciniki, $settings);
        }
        // First Aid
        elseif( $ciniki['request']['page'] == 'firstaid' 
            && isset($settings['page-fatt-active']) && $settings['page-fatt-active'] == 'yes' ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generateModulePage');
            $rc = ciniki_web_generateModulePage($ciniki, $settings, $ciniki['request']['tnid'], 'ciniki.fatt');
        }
        // Courses
        elseif( $ciniki['request']['page'] == 'courses' 
            && isset($settings['page-courses-active']) && $settings['page-courses-active'] == 'yes' ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generateModulePage');
            $rc = ciniki_web_generateModulePage($ciniki, $settings, $ciniki['request']['tnid'], 'ciniki.courses');
            //require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageCourses.php');
            //$rc = ciniki_web_generatePageCourses($ciniki, $settings);
        }
        // Classes
        elseif( $ciniki['request']['page'] == 'classes' 
            && isset($settings['page-classes-active']) && $settings['page-classes-active'] == 'yes' ) {
            require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageClasses.php');
            $rc = ciniki_web_generatePageClasses($ciniki, $settings);
        }
        // Members
        elseif( $ciniki['request']['page'] == 'members' 
            && isset($settings['page-members-active']) && $settings['page-members-active'] == 'yes' ) {
            //require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageMembers.php');
            //$rc = ciniki_web_generatePageMembers($ciniki, $settings);
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generateModulePage');
            $rc = ciniki_web_generateModulePage($ciniki, $settings, $ciniki['request']['tnid'], 'ciniki.customers.members');
        }
        // Dealers
        elseif( $ciniki['request']['page'] == 'dealers' 
            && isset($settings['page-dealers-active']) && $settings['page-dealers-active'] == 'yes' ) {
            require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageDealers.php');
            $rc = ciniki_web_generatePageDealers($ciniki, $settings);
        }
        // Distributors
        elseif( $ciniki['request']['page'] == 'distributors' 
            && isset($settings['page-distributors-active']) && $settings['page-distributors-active'] == 'yes' ) {
            require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageDistributors.php');
            $rc = ciniki_web_generatePageDistributors($ciniki, $settings);
        }
        // Products
        elseif( $ciniki['request']['page'] == 'products' 
            && isset($settings['page-products-active']) && $settings['page-products-active'] == 'yes' 
            ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generateModulePage');
            $rc = ciniki_web_generateModulePage($ciniki, $settings, $ciniki['request']['tnid'], 'ciniki.products');
        }
        // Herbalist
        elseif( $ciniki['request']['page'] == 'products' 
            && isset($settings['page-herbalist-active']) && $settings['page-herbalist-active'] == 'yes' 
            ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generateModulePage');
            $rc = ciniki_web_generateModulePage($ciniki, $settings, $ciniki['request']['tnid'], 'ciniki.herbalist');
        }
        // PDF Catalogs
        elseif( $ciniki['request']['page'] == 'pdfcatalogs' 
            && isset($settings['page-pdfcatalogs-active']) && $settings['page-pdfcatalogs-active'] == 'yes' ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generateModulePage');
            $rc = ciniki_web_generateModulePage($ciniki, $settings, $ciniki['request']['tnid'], 'ciniki.products.pdfcatalogs');
        }
        // Recipes
        elseif( $ciniki['request']['page'] == 'recipes' 
            && isset($settings['page-recipes-active']) && $settings['page-recipes-active'] == 'yes' ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generateModulePage');
            $rc = ciniki_web_generateModulePage($ciniki, $settings, $ciniki['request']['tnid'], 'ciniki.recipes');
        //  require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageRecipes.php');
        //  $rc = ciniki_web_generatePageRecipes($ciniki, $settings);
        }
        // Gallery
        elseif( $ciniki['request']['page'] == 'gallery' 
            && isset($settings['page-gallery-active']) && $settings['page-gallery-active'] == 'yes' ) {
            require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageGallery.php');
            $rc = ciniki_web_generatePageGallery($ciniki, $settings);
            // generateModulePage does not work for split artcatalog into categories
            //ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generateModulePage');
            //$rc = ciniki_web_generateModulePage($ciniki, $settings, $ciniki['request']['tnid'], 'ciniki.gallery');
        }
        // Writings
        elseif( $ciniki['request']['page'] == 'writings' 
            && isset($settings['page-writings-active']) && $settings['page-writings-active'] == 'yes' ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generateModulePage');
            $rc = ciniki_web_generateModulePage($ciniki, $settings, $ciniki['request']['tnid'], 'ciniki.writingcatalog');
    //        require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageWritings.php');
    //        $rc = ciniki_web_generatePageWritings($ciniki, $settings);
        }
        // Events
        elseif( $ciniki['request']['page'] == 'events' 
            && isset($settings['page-events-active']) && $settings['page-events-active'] == 'yes' ) {
    //        require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageEvents.php');
    //        $rc = ciniki_web_generatePageEvents($ciniki, $settings);
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generateModulePage');
            $rc = ciniki_web_generateModulePage($ciniki, $settings, $ciniki['request']['tnid'], 'ciniki.events');
        } 
        // Film Schedule
        elseif( $ciniki['request']['page'] == 'schedule' 
            && isset($settings['page-filmschedule-active']) && $settings['page-filmschedule-active'] == 'yes' ) {
            require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageFilmSchedule.php');
            $rc = ciniki_web_generatePageFilmSchedule($ciniki, $settings);
        } 
        // Workshops
        elseif( $ciniki['request']['page'] == 'workshops' 
            && isset($settings['page-workshops-active']) && $settings['page-workshops-active'] == 'yes' ) {
            require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageWorkshops.php');
            $rc = ciniki_web_generatePageWorkshops($ciniki, $settings);
        } 
        // Patents
        elseif( $ciniki['request']['page'] == 'patents' 
            && isset($settings['page-patents-active']) && $settings['page-patents-active'] == 'yes' ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generateModulePage');
            $rc = ciniki_web_generateModulePage($ciniki, $settings, $ciniki['request']['tnid'], 'ciniki.patents');
        } 
        // fielddaylog
        elseif( $ciniki['request']['page'] == 'fieldday' 
            && isset($settings['page-fielddaylog-active']) && $settings['page-fielddaylog-active'] == 'yes' ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generateModulePage');
            $rc = ciniki_web_generateModulePage($ciniki, $settings, $ciniki['request']['tnid'], 'qruqsp.fielddaylog');
        } 
        // winterfielddaylog
        elseif( $ciniki['request']['page'] == 'winterfieldday' 
            && isset($settings['page-winterfielddaylog-active']) && $settings['page-winterfielddaylog-active'] == 'yes' ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generateModulePage');
            $rc = ciniki_web_generateModulePage($ciniki, $settings, $ciniki['request']['tnid'], 'qruqsp.winterfielddaylog');
        } 
        // jiji
        elseif( $ciniki['request']['page'] == 'buysell' 
            && isset($settings['page-jiji-active']) && $settings['page-jiji-active'] == 'yes' ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generateModulePage');
            $rc = ciniki_web_generateModulePage($ciniki, $settings, $ciniki['request']['tnid'], 'ciniki.jiji');
        } 
        // Blog
        elseif( $ciniki['request']['page'] == 'blog' 
            && isset($settings['page-blog-active']) && $settings['page-blog-active'] == 'yes' ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generateModulePage');
            $rc = ciniki_web_generateModulePage($ciniki, $settings, $ciniki['request']['tnid'], 'ciniki.blog');
        } 
        // Member Blog
        elseif( $ciniki['request']['page'] == 'memberblog' 
            && isset($settings['page-memberblog-active']) && $settings['page-memberblog-active'] == 'yes' ) {
            require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageMemberBlog.php');
            $rc = ciniki_web_generatePageMemberBlog($ciniki, $settings);
        } 
        // Membersonly
        elseif( $ciniki['request']['page'] == 'membersonly' 
            && isset($settings['page-membersonly-active']) && $settings['page-membersonly-active'] == 'yes' ) {
            require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageMembersonly.php');
            $rc = ciniki_web_generatePageMembersonly($ciniki, $settings);
        } 
        // Tutorials
        elseif( $ciniki['request']['page'] == 'tutorials' 
            && isset($settings['page-tutorials-active']) && $settings['page-tutorials-active'] == 'yes' ) {
            require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageTutorials.php');
            $rc = ciniki_web_generatePageTutorials($ciniki, $settings);
        } 
        // FAQ
        elseif( $ciniki['request']['page'] == 'faq' 
            && isset($settings['page-faq-active']) && $settings['page-faq-active'] == 'yes' ) {
            require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageFAQ.php');
            $rc = ciniki_web_generatePageFAQ($ciniki, $settings);
        } 
        // Links
        elseif( $ciniki['request']['page'] == 'links' 
            && isset($settings['page-links-active']) && $settings['page-links-active'] == 'yes' ) {
            require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageLinks.php');
            $rc = ciniki_web_generatePageLinks($ciniki, $settings);
        } 
        // Newsletters
        elseif( $ciniki['request']['page'] == 'newsletters' 
            && isset($settings['page-newsletters-active']) && $settings['page-newsletters-active'] == 'yes' ) {
            require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageNewsletters.php');
            $rc = ciniki_web_generatePageNewsletters($ciniki, $settings);
        } 
        // Downloads
        elseif( $ciniki['request']['page'] == 'downloads'
        //  && isset($settings['page-downloads-active']) && $settings['page-downloads-active'] == 'yes' 
            ) {
            require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageDownloads.php');
            $rc = ciniki_web_generatePageDownloads($ciniki, $settings);
        } 
        // Surveys
        elseif( $ciniki['request']['page'] == 'surveys' 
            && isset($settings['page-surveys-active']) && $settings['page-surveys-active'] == 'yes' ) {
            require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageSurveys.php');
            $rc = ciniki_web_generatePageSurveys($ciniki, $settings);
        } 
        // Plans
        elseif( $ciniki['request']['page'] == 'plans' 
            && isset($settings['page-plans-active']) && $settings['page-plans-active'] == 'yes' ) {
            require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePagePlans.php');
            $rc = ciniki_web_generatePagePlans($ciniki, $settings);
        } 
        // Directory
//        elseif( $ciniki['request']['page'] == 'directory' 
//            && isset($settings['page-directory-active']) && $settings['page-directory-active'] == 'yes' ) {
//            require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageDirectory.php');
//            $rc = ciniki_web_generatePageDirectory($ciniki, $settings);
//        } 
        // Web Collection
        elseif( $ciniki['request']['page'] == 'collection' 
            && isset($ciniki['tenant']['modules']['ciniki.web']['flags'])
            && ($ciniki['tenant']['modules']['ciniki.web']['flags']&0x08) > 0 ) {
            require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageWebCollections.php');
            $rc = ciniki_web_generatePageWebCollections($ciniki, $settings);
        } 
        // Property Rentals
        elseif( $ciniki['request']['page'] == 'properties' 
            && isset($settings['page-propertyrentals-active']) && $settings['page-propertyrentals-active'] == 'yes' ) {
            require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePagePropertyRentals.php');
            $rc = ciniki_web_generatePagePropertyRentals($ciniki, $settings, 'info');
        } 
        // Music Festival
        elseif( $ciniki['request']['page'] == 'musicfestivals' 
            && isset($settings['page-musicfestivals-active']) && $settings['page-musicfestivals-active'] == 'yes' ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generateModulePage');
            $rc = ciniki_web_generateModulePage($ciniki, $settings, $ciniki['request']['tnid'], 'ciniki.musicfestivals');
        } 
        // Writing Festival
        elseif( $ciniki['request']['page'] == 'writingfestivals' 
            && isset($settings['page-writingfestivals-active']) && $settings['page-writingfestivals-active'] == 'yes' ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generateModulePage');
            $rc = ciniki_web_generateModulePage($ciniki, $settings, $ciniki['request']['tnid'], 'ciniki.writingfestivals');
        } 
        // Merchandise
        elseif( $ciniki['request']['page'] == 'merchandise' 
            && isset($settings['page-merchandise-active']) && $settings['page-merchandise-active'] == 'yes' ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generateModulePage');
            $rc = ciniki_web_generateModulePage($ciniki, $settings, $ciniki['request']['tnid'], 'ciniki.merchandise');
        } 
        // Info
        elseif( $ciniki['request']['page'] == 'info' 
            && isset($settings['page-info-active']) && $settings['page-info-active'] == 'yes' ) {
            require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageInfo.php');
            $rc = ciniki_web_generatePageInfo($ciniki, $settings, 'info');
        } 
        //
        // If pages is enabled but pages menu is not, then check for matching pages. Page menu option is above
        //
        elseif( $ciniki['request']['page'] != '' 
            && isset($ciniki['tenant']['modules']['ciniki.web']['flags'])
            && ($ciniki['tenant']['modules']['ciniki.web']['flags']&0x40) > 0 ) {
            require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePage.php');
            $rc = ciniki_web_generatePage($ciniki, $settings);
        }
        // FIXME: Need to make accessible for all custom pages, not just 001.
        // Custom pages
        //elseif( isset($settings['page-custom-001-permalink']) && $settings['page-custom-001-permalink'] == $ciniki['request']['page'] ) {
        //  require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageCustom.php');
        //  $rc = ciniki_web_generatePageCustom($ciniki, $settings);
        //}
        // Unknown page
        else {
            //
            // Check the custom pages
            //
            $found = 'no';  
            for($i=1;$i<6;$i++) {
                $pname = 'page-custom-' . sprintf("%03d", $i);
                if( isset($settings[$pname . '-permalink']) 
                    && $settings[$pname . '-permalink'] == $ciniki['request']['page'] 
                    ) {
                    require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageCustom.php');
                    $rc = ciniki_web_generatePageCustom($ciniki, $settings, $i);
                    $found = 'yes';
                    break;
                }
            }

            //
            // Check for pages from other packages
            //
            if( count($ciniki['tenant']['pages']) > 0 ) {
                foreach($ciniki['tenant']['pages'] as $permalink => $page) {
                    if( $ciniki['request']['page'] == $permalink && $page['active'] == 'yes' ) {
                        $rc = ciniki_core_loadMethod($ciniki, $page['pkg'], 'web', 'private', $page['fn']);
                        if( $rc['stat'] != 'noexist' ) {
                            $fn = $page['pkg'] . '_web_' . $page['fn'];
                            $found = 'yes';
                            $rc = $fn($ciniki, $settings);
                            break;
                        }
                    }
                }
            }

            if( $found == 'no' ) {
                if( $_SERVER['REQUEST_URI'] == '/robots.txt' ) {
                    require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageRobots.php');
                    $rc = ciniki_web_generatePageRobots($ciniki, $settings);
                } else { 
                    require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePage404.php');
                    $rc = ciniki_web_generatePage404($ciniki, $settings, null); 
                }
            }

        //  print_error($rc, 'Unknown page ' . $ciniki['request']['page']);
        //  exit;
        }
    }

    if( $rc['stat'] == '503' ) {
        require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePage503.php');
        $rc = ciniki_web_generatePage503($ciniki, $settings, $rc);
    }
    elseif( $rc['stat'] == '404' ) {
        //
        // If no page was found, check up the chain of redirects to see if theres a general match
        //
        if( ciniki_core_checkModuleFlags($ciniki, 'ciniki.web', 0x04000000) ) {
            $url = $_SERVER['REQUEST_URI'];
            $u = preg_split('/\?/', $url);
            $url = $u[0];
            if( $ciniki['request']['base_url'] != '' ) {
                $url = preg_replace('#' . $ciniki['request']['base_url'] . '#i', '', $url, 1);
            }
            while( $url != '' ) {   
                $strsql = "SELECT newurl "
                    . "FROM ciniki_web_redirects "
                    . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['tnid']) . "' "
                    . "AND oldurl = '" . ciniki_core_dbQuote($ciniki, $url) . "' "
                    . "";
                $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.web', 'redirect');
                if( $rc['stat'] == 'ok' && isset($rc['redirect']['newurl']) ) {
                    Header('HTTP/1.1 301 Moved Permanently'); 
                    if( preg_match("/^http/", $rc['redirect']['newurl']) ) {
                        Header('Location: ' . $rc['redirect']['newurl']);
                    } else {
                        Header('Location: ' . $ciniki['request']['domain_base_url'] . $rc['redirect']['newurl']);
                    }
                    exit;
                }
                $url = preg_replace("/\/[^\/]*$/", '', $url);
            }
        }

        if( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] == 'robots.txt' ) {
            require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePageRobots.php');
            $rc = ciniki_web_generatePageRobots($ciniki, $settings);
        } else {
            require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePage404.php');
            $rc = ciniki_web_generatePage404($ciniki, $settings, $rc);
        }
    } 

    if( isset($ciniki['response']['format']) && $ciniki['response']['format'] == 'json' ) {
        header("Content-Type: text/plain; charset=utf-8");
        header("Cache-Control: no-cache, must-revalidate");
        $rc['content'] = json_encode($rc);   
    /*    //
        // Only output if no queues to process
        //
        if( (!isset($ciniki['emailqueue']) || count($ciniki['emailqueue']) == 0) 
            && (isset($ciniki['smsqueue']) || count($ciniki['smsqueue']) == 0) 
            ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'printHashToJSON');
            ciniki_core_printHashToJSON($rc);
        } else {
            
        } */
    }

    elseif( $rc['stat'] != 'ok' && $rc['stat'] != 'exit' ) {
        require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/generatePage500.php');
        $rc = ciniki_web_generatePage500($ciniki, $settings, $rc);
    //  print_error($rc, 'Unable to generate page.');
    //  exit;
    }

    //
    // Save module session information
    //
    foreach($ciniki['tenant']['modules'] as $module => $m) {
        if( isset($ciniki['session'][$module]) ) {
            $_SESSION[$module] = $ciniki['session'][$module];
        }
    }


    //
    // Check for emailqueue
    //
    if( (isset($ciniki['emailqueue']) && count($ciniki['emailqueue']) > 0) || (isset($ciniki['smsqueue']) && count($ciniki['smsqueue']) > 0) ) {
        ob_start();
        if( isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false ) {
            ob_start("ob_gzhandler");
            print $rc['content'];
            ob_end_flush();
        } elseif( isset($rc['content']) && $rc['content'] != '' ) {
            print $rc['content'];
        }
        header("Connection: close");
        $contentlength = ob_get_length();
        header("Content-Length: $contentlength");
        ob_end_flush();
        ob_end_flush();
        flush();
        session_write_close();
        while(ob_get_level() > 0) {
            ob_end_clean();
        }

        if( isset($ciniki['emailqueue']) && count($ciniki['emailqueue']) > 0 ) {
            require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/core/private/emailQueueProcess.php');
            ciniki_core_emailQueueProcess($ciniki);
        }
        if( isset($ciniki['smsqueue']) && count($ciniki['smsqueue']) > 0 ) {
            require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/core/private/smsQueueProcess.php');
            ciniki_core_smsQueueProcess($ciniki);
        }
    } 

    elseif( isset($rc['content']) && $rc['content'] != '' ) {
        //
        // Output the page contents
        // FIXME: Add caching in here
        //
        print $rc['content'];
    }

    return array('stat'=>'ok');
}
?>
