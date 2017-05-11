<?php
//
// Description
// -----------
// This method will return the list of available pages for the business,
// and which ones have been activated.  In addition, the theme and header
// information will be returned.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:     The ID of the business to get the site settings for.
//
// Returns
// -------
// <rsp stat="ok" featured="no">
//      <pages>
//          <page name="home" display_name="Home" active="yes" />
//          <page name="about" display_name="About" active="yes" />
//          <page name="contact" display_name="Contact" active="yes" />
//          <page name="events" display_name="Events" active="no" />
//          <page name="gallery" display_name="Gallery" active="yes" />
//          <page name="links" display_name="Links" active="yes" />
//      </pages>
//      <settings>
//          <setting name="theme" display_name="Theme" value="black" />
//      </settings>
//      <header>
//          <setting name="site-header-image" display_name="Header Image" value="0" />
//          <setting name="site-header-image-size" display_name="Header Image Size" value="0" />
//          <setting name="site-header-title" display_name="Header Logo" value="no" />
//      </header>
//      <advanced>
//          <setting name="site-header-image" display_name="Header Image" value="0" />
//          <setting name="site-logo-display" display_name="Header Logo" value="no" />
//      </advanced>
// </rsp>
//
function ciniki_web_siteSettings($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to business_id as owner, and load module list
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'checkAccess');
    $ac = ciniki_web_checkAccess($ciniki, $args['business_id'], 'ciniki.web.siteSettings');
    if( $ac['stat'] != 'ok' ) {
        return $ac;
    }
    $ciniki['business']['modules'] = $ac['modules'];

    //
    // Get the website URL
    // 
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'lookupBusinessURL');
    $rc = ciniki_web_lookupBusinessURL($ciniki, $args['business_id']);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $url = $rc['url'];

    $rsp = array('stat'=>'ok');
    
    //
    // Build list of available pages from modules enabled
    //
    $pages = array();
    $pages['home'] = array('display_name'=>'Home', 'active'=>'no');
    if( $args['business_id'] == $ciniki['config']['ciniki.core']['master_business_id'] 
        && isset($ciniki['config']['ciniki.web']['shop.domain']) && $ciniki['config']['ciniki.web']['shop.domain'] != '' 
        ) {
        $pages['shop'] = array('display_name'=>'Shop', 'active'=>'no');
    }
    if( isset($ciniki['business']['modules']['ciniki.info']) ) {
        $pages['about'] = array('display_name'=>'About', 'active'=>'no');
    }

    if( isset($ciniki['business']['modules']['ciniki.marketing']) && ($ciniki['business']['modules']['ciniki.marketing']['flags']&0x01) == 0x01 ) {
        $pages['features'] = array('display_name'=>'Features', 'active'=>'no');
    }
    if( isset($ciniki['business']['modules']['ciniki.web']) && ($ciniki['business']['modules']['ciniki.web']['flags']&0x01) == 1) {
        $pages['custom-001'] = array('display_name'=>'Custom Page', 'active'=>'no');
        $pages['custom-002'] = array('display_name'=>'Custom Page', 'active'=>'no');
        $pages['custom-003'] = array('display_name'=>'Custom Page', 'active'=>'no');
        $pages['custom-004'] = array('display_name'=>'Custom Page', 'active'=>'no');
        $pages['custom-005'] = array('display_name'=>'Custom Page', 'active'=>'no');
    }
    if( isset($ciniki['business']['modules']['ciniki.propertyrentals']) ) {
        $pages['propertyrentals'] = array('display_name'=>'Properties', 'active'=>'no');
    }
    if( isset($ciniki['business']['modules']['ciniki.products']) 
        && ($ciniki['business']['modules']['ciniki.products']['flags']&0x80) > 0 
        ) {
        $pages['pdfcatalogs'] = array('display_name'=>'Catalogs', 'active'=>'no');
    }
    if( isset($ciniki['business']['modules']['ciniki.products']) ) {
        $pages['products'] = array('display_name'=>'Products', 'active'=>'no');
    }
    if( isset($ciniki['business']['modules']['ciniki.herbalist']) ) {
        $pages['herbalist'] = array('display_name'=>'Products', 'active'=>'no');
    }
    if( isset($ciniki['business']['modules']['ciniki.filmschedule']) ) {
        $pages['filmschedule'] = array('display_name'=>'Schedule', 'active'=>'no');
    }
    if( isset($ciniki['business']['modules']['ciniki.workshops']) ) {
        $pages['workshops'] = array('display_name'=>'Workshops', 'active'=>'no');
    }
    if( isset($ciniki['business']['modules']['ciniki.events']) ) {
        $pages['events'] = array('display_name'=>'Events', 'active'=>'no');
    }
    if( isset($ciniki['business']['modules']['ciniki.musicfestivals']) ) {
        $pages['musicfestivals'] = array('display_name'=>'Music Festival', 'active'=>'no');
    }
    if( isset($ciniki['business']['modules']['ciniki.exhibitions']) ) {
        $pages['exhibitions'] = array('display_name'=>'Exhibitions', 'active'=>'no');
    }
    if( isset($ciniki['business']['modules']['ciniki.fatt']) ) {
        $pages['fatt'] = array('display_name'=>'First Aid', 'active'=>'no');
    }
    if( isset($ciniki['business']['modules']['ciniki.courses']) ) {
        $pages['courses'] = array('display_name'=>'Courses', 'active'=>'no');
    }
    if( isset($ciniki['business']['modules']['ciniki.classes']) ) {
        $pages['classes'] = array('display_name'=>'Classes', 'active'=>'no');
    }
    if( isset($ciniki['business']['modules']['ciniki.artcatalog']) || isset($ciniki['business']['modules']['ciniki.gallery']) ) {
        $pages['gallery'] = array('display_name'=>'Gallery', 'active'=>'no');
    }
    if( isset($ciniki['business']['modules']['ciniki.writingcatalog']) ) {
        $pages['writings'] = array('display_name'=>'Writings', 'active'=>'no');
    }
    if( isset($ciniki['business']['modules']['ciniki.customers']) && ($ciniki['business']['modules']['ciniki.customers']['flags']&0x02) == 0x02 ) {
        $pages['members'] = array('display_name'=>'Members', 'active'=>'no');
    }
    if( isset($ciniki['business']['modules']['ciniki.customers']) && ($ciniki['business']['modules']['ciniki.customers']['flags']&0x10) == 0x10 ) {
        $pages['dealers'] = array('display_name'=>'Dealers', 'active'=>'no');
    }
    if( isset($ciniki['business']['modules']['ciniki.customers']) && ($ciniki['business']['modules']['ciniki.customers']['flags']&0x0100) == 0x0100 ) {
        $pages['distributors'] = array('display_name'=>'Distributors', 'active'=>'no');
    }
    if( isset($ciniki['business']['modules']['ciniki.artclub']) ) {
        $pages['members'] = array('display_name'=>'Members', 'active'=>'no');
    }
    if( isset($ciniki['business']['modules']['ciniki.sponsors']) ) {
        $pages['sponsors'] = array('display_name'=>'Sponsors', 'active'=>'no');
    }
    if( isset($ciniki['business']['modules']['ciniki.artgallery']) ) {
        $pages['artgalleryexhibitions'] = array('display_name'=>'Exhibitions', 'active'=>'no');
    }
    if( isset($ciniki['business']['modules']['ciniki.directory']) ) {
        $pages['directory'] = array('display_name'=>'Directory', 'active'=>'no');
    }
    if( isset($ciniki['business']['modules']['ciniki.links']) ) {
        $pages['links'] = array('display_name'=>'Links', 'active'=>'no');
    }
    if( isset($ciniki['business']['modules']['ciniki.newsletters']) ) {
        $pages['newsletters'] = array('display_name'=>'Newsletters', 'active'=>'no');
    }
    if( isset($ciniki['business']['modules']['ciniki.filedepot']) ) {
        $pages['downloads'] = array('display_name'=>'Downloads', 'active'=>'no');
    }
    if( isset($ciniki['business']['modules']['ciniki.blog']) ) {
        if( ($ciniki['business']['modules']['ciniki.blog']['flags']&0x01) > 0 ) {
            $pages['blog'] = array('display_name'=>'Blog', 'active'=>'no');
        }
        if( ($ciniki['business']['modules']['ciniki.blog']['flags']&0x0100) > 0 ) {
            $pages['account'] = array('display_name'=>'Account', 'active'=>'no');
            $pages['memberblog'] = array('display_name'=>'Member News', 'active'=>'no');
        }
    }
    if( isset($ciniki['business']['modules']['ciniki.jiji']) ) {
        $pages['jiji'] = array('display_name'=>'Buy/Sell', 'active'=>'no');
    }
    if( isset($ciniki['business']['modules']['ciniki.sapos']) && ($ciniki['business']['modules']['ciniki.sapos']['flags']&0x08) > 0 ) {
        $pages['cart'] = array('display_name'=>'Shopping Cart', 'active'=>'no');
    }
    if( isset($ciniki['business']['modules']['ciniki.recipes']) ) {
        $pages['recipes'] = array('display_name'=>'Recipes', 'active'=>'no');
    }
    if( isset($ciniki['business']['modules']['ciniki.patents']) ) {
        $pages['patents'] = array('display_name'=>'Patents', 'active'=>'no');
    }
    if( isset($ciniki['business']['modules']['ciniki.surveys']) ) {
        $pages['surveys'] = array('display_name'=>'Surveys', 'active'=>'no');
    }
    if( isset($ciniki['business']['modules']['ciniki.filedepot']) 
        || isset($ciniki['business']['modules']['ciniki.products']) 
        || isset($ciniki['business']['modules']['ciniki.subscriptions']) 
        || isset($ciniki['business']['modules']['ciniki.merchandise']) 
        ) {
        $pages['account'] = array('display_name'=>'Account', 'active'=>'no');
    }
    if( isset($ciniki['business']['modules']['ciniki.membersonly']) ) {
        $pages['membersonly'] = array('display_name'=>'Members Only', 'active'=>'no');
    }
    if( isset($ciniki['business']['modules']['ciniki.web']) && ($ciniki['business']['modules']['ciniki.web']['flags']&0x4000) > 0 ) {
        $pages['search'] = array('display_name'=>'Search', 'active'=>'no');
    }

    //
    // Pages
    //
    if( isset($ciniki['business']['modules']['ciniki.web']) && ($ciniki['business']['modules']['ciniki.web']['flags']&0x0240) == 0x40) {
        $strsql = "SELECT id, title, permalink, "
            . "IF((flags&0x01)=1,'yes','no') AS active "
            . "FROM ciniki_web_pages "
            . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
            . "AND parent_id = 0 "
            . "ORDER BY sequence, title "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.web', 'page');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['rows']) ) {
            foreach($rc['rows'] as $row) {
                $pages[$row['permalink']] = array('id'=>$row['id'], 'display_name'=>$row['title'], 'active'=>$row['active']);
            }
        }
    }

    // 
    // If this is the master business, allow extra options
    //
    if( $ciniki['config']['ciniki.core']['master_business_id'] == $args['business_id'] ) {
        $pages['signup'] = array('display_name'=>'Signup', 'active'=>'no');
        $pages['api'] = array('display_name'=>'API', 'active'=>'no');
    }
    if( isset($ciniki['business']['modules']['ciniki.tutorials']) ) {
        $pages['tutorials'] = array('display_name'=>'Tutorials', 'active'=>'no');
    }
    if( isset($ciniki['business']['modules']['ciniki.web']) && ($ciniki['business']['modules']['ciniki.web']['flags']&0x80) == 0x80) {
        $pages['faq'] = array('display_name'=>'FAQ', 'active'=>'no');
    }
    if( isset($ciniki['business']['modules']['ciniki.info']) && ($ciniki['business']['modules']['ciniki.web']['flags']&0x20) > 0 ) {
        $pages['info'] = array('display_name'=>'Information', 'active'=>'no');
    }
    if( ciniki_core_checkModuleFlags($ciniki, 'ciniki.merchandise', 0x0100) ) {
        $pages['merchandise'] = array('display_name'=>'Shop', 'active'=>'no');
    }
    $pages['contact'] = array('display_name'=>'Contact', 'active'=>'no');


    //
    // Load current settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQuery');
    $rc = ciniki_core_dbDetailsQuery($ciniki, 'ciniki_web_settings', 'business_id', $args['business_id'], 'ciniki.web', 'settings', '');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['settings']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.169', 'msg'=>'No settings found, site not configured.'));
    }
    $settings = $rc['settings'];

//  if( isset($settings['page-custom-001-name']) && $settings['page-custom-001-name'] != '' ) {
//      $pages['custom']['display_name'] = $settings['page-custom-001-name'];
//  }

    if( isset($ciniki['business']['modules']['ciniki.web']) && ($ciniki['business']['modules']['ciniki.web']['flags']&0x01) == 0x01) {
        for($i=1;$i<6;$i++) {
            $pname = 'page-custom-' . sprintf("%03d", $i);
            $cname = 'custom-' . sprintf("%03d", $i);
            if( isset($settings[$pname . '-name']) && $settings[$pname . '-name'] != '' ) {
                $pages['custom-00' . $i]['display_name'] = $settings[$pname . '-name'];
            }
            if( isset($settings[$pname . '-active']) 
                && $settings[$pname . '-active'] == 'yes' && ($ciniki['business']['modules']['ciniki.web']['flags']&0x01) == 1 ) {
                $pages[$cname]['active'] = 'yes';
            }
        }
    }

    //
    // Set which pages are active from the settings
    //
    if( isset($settings['page-home-active']) && $settings['page-home-active'] == 'yes' ) {
        $pages['home']['active'] = 'yes';
    }
    if( isset($settings['page-shop-active']) && $settings['page-shop-active'] == 'yes' ) {
        $pages['shop']['active'] = 'yes';
    }
    //
    // Allow any about page to trigger it to be active in website menu
    //
    if( isset($settings['page-about-active']) && $settings['page-about-active'] == 'yes' ) {
        $pages['about']['active'] = 'yes';
    } elseif( isset($settings['page-about-artiststatement-active']) && $settings['page-about-artiststatement-active'] == 'yes' ) {
        $pages['about']['active'] = 'yes';
    } elseif( isset($settings['page-about-cv-active']) && $settings['page-about-cv-active'] == 'yes' ) {
        $pages['about']['active'] = 'yes';
    } elseif( isset($settings['page-about-awards-active']) && $settings['page-about-awards-active'] == 'yes' ) {
        $pages['about']['active'] = 'yes';
    }
    if( isset($settings['page-about-active']) && $settings['page-about-active'] == 'yes' ) {
        $pages['about']['active'] = 'yes';
    }
    if( isset($settings['page-features-active']) && $settings['page-features-active'] == 'yes' ) {
        $pages['features']['active'] = 'yes';
    }
    if( isset($settings['page-pdfcatalogs-active']) && $settings['page-pdfcatalogs-active'] == 'yes' ) {
        $pages['pdfcatalogs']['active'] = 'yes';
    }
    if( isset($settings['page-products-active']) && $settings['page-products-active'] == 'yes' ) {
        $pages['products']['active'] = 'yes';
    }
    if( isset($settings['page-herbalist-active']) && $settings['page-herbalist-active'] == 'yes' ) {
        $pages['herbalist']['active'] = 'yes';
    }
    if( isset($settings['page-recipes-active']) && $settings['page-recipes-active'] == 'yes' ) {
        $pages['recipes']['active'] = 'yes';
    }
    if( isset($settings['page-patents-active']) && $settings['page-patents-active'] == 'yes' ) {
        $pages['patents']['active'] = 'yes';
    }
    if( isset($settings['page-jiji-active']) && $settings['page-jiji-active'] == 'yes' ) {
        $pages['jiji']['active'] = 'yes';
    }
    if( isset($settings['page-blog-active']) && $settings['page-blog-active'] == 'yes' ) {
        $pages['blog']['active'] = 'yes';
    }
//  if( isset($settings['page-custom-001-active']) && $settings['page-custom-001-active'] == 'yes' && ($ciniki['business']['modules']['ciniki.web']['flags']&0x01) == 1 ) {
//      $pages['custom']['active'] = 'yes';
//  }
    if( isset($settings['page-contact-active']) && $settings['page-contact-active'] == 'yes' ) {
        $pages['contact']['active'] = 'yes';
    }
    if( isset($settings['page-propertyrentals-active']) && $settings['page-propertyrentals-active'] == 'yes' ) {
        $pages['propertyrentals']['active'] = 'yes';
    }
    if( isset($settings['page-tutorials-active']) && $settings['page-tutorials-active'] == 'yes' ) {
        $pages['tutorials']['active'] = 'yes';
    }
    if( isset($settings['page-faq-active']) && $settings['page-faq-active'] == 'yes' ) {
        $pages['faq']['active'] = 'yes';
    }
    if( isset($settings['page-events-active']) && $settings['page-events-active'] == 'yes' ) {
        $pages['events']['active'] = 'yes';
    }
    if( isset($settings['page-musicfestivals-active']) && $settings['page-musicfestivals-active'] == 'yes' ) {
        $pages['musicfestivals']['active'] = 'yes';
    }
    if( isset($settings['page-filmschedule-active']) && $settings['page-filmschedule-active'] == 'yes' ) {
        $pages['filmschedule']['active'] = 'yes';
    }
    if( isset($settings['page-workshops-active']) && $settings['page-workshops-active'] == 'yes' ) {
        $pages['workshops']['active'] = 'yes';
    }
    if( isset($settings['page-directory-active']) && $settings['page-directory-active'] == 'yes' ) {
        $pages['directory']['active'] = 'yes';
    }
    if( isset($settings['page-links-active']) && $settings['page-links-active'] == 'yes' ) {
        $pages['links']['active'] = 'yes';
    }
    if( isset($settings['page-gallery-active']) && $settings['page-gallery-active'] == 'yes' ) {
        $pages['gallery']['active'] = 'yes';
    }
    if( isset($settings['page-writings-active']) && $settings['page-writings-active'] == 'yes' ) {
        $pages['writings']['active'] = 'yes';
    }
    if( isset($settings['page-members-active']) && $settings['page-members-active'] == 'yes' ) {
        $pages['members']['active'] = 'yes';
    }
    if( isset($settings['page-dealers-active']) && $settings['page-dealers-active'] == 'yes' ) {
        $pages['dealers']['active'] = 'yes';
    }
    if( isset($settings['page-distributors-active']) && $settings['page-distributors-active'] == 'yes' ) {
        $pages['distributors']['active'] = 'yes';
    }
    if( isset($settings['page-sponsors-active']) && $settings['page-sponsors-active'] == 'yes' ) {
        $pages['sponsors']['active'] = 'yes';
    }
    if( isset($settings['page-newsletters-active']) && $settings['page-newsletters-active'] == 'yes' ) {
        $pages['newsletters']['active'] = 'yes';
    }
    if( isset($settings['page-surveys-active']) && $settings['page-surveys-active'] == 'yes' ) {
        $pages['surveys']['active'] = 'yes';
    }
    if( isset($settings['page-fatt-active']) && $settings['page-fatt-active'] == 'yes' ) {
        $pages['fatt']['active'] = 'yes';
    }
    if( isset($settings['page-courses-active']) && $settings['page-courses-active'] == 'yes' ) {
        $pages['courses']['active'] = 'yes';
    }
    if( isset($settings['page-classes-active']) && $settings['page-classes-active'] == 'yes' ) {
        $pages['classes']['active'] = 'yes';
    }
    if( (isset($settings['page-exhibitions-exhibitors-active']) && $settings['page-exhibitions-exhibitors-active'] == 'yes')
        || (isset($settings['page-exhibitions-sponsors-active']) && $settings['page-exhibitions-sponsors-active'] == 'yes') ) {
        $pages['exhibitions']['active'] = 'yes';
    }
    if( isset($settings['page-artgalleryexhibitions-active']) && $settings['page-artgalleryexhibitions-active'] == 'yes' ) {
        $pages['artgalleryexhibitions']['active'] = 'yes';
    }
    if( isset($settings['page-signup-active']) && $settings['page-signup-active'] == 'yes' ) {
        $pages['signup']['active'] = 'yes';
    }
    if( isset($settings['page-api-active']) && $settings['page-api-active'] == 'yes' ) {
        $pages['api']['active'] = 'yes';
    }
    if( isset($settings['page-downloads-active']) && $settings['page-downloads-active'] == 'yes' ) {
        $pages['downloads']['active'] = 'yes';
    }
    if( isset($settings['page-account-active']) && $settings['page-account-active'] == 'yes' ) {
        $pages['account']['active'] = 'yes';
    }
    if( isset($settings['page-cart-active']) && $settings['page-cart-active'] == 'yes' 
        && isset($pages['cart']) ) {
        $pages['cart']['active'] = 'yes';
    }
    if( isset($settings['page-memberblog-active']) && $settings['page-memberblog-active'] == 'yes' ) {
        $pages['memberblog']['active'] = 'yes';
    }
    if( isset($settings['page-membersonly-active']) && $settings['page-membersonly-active'] == 'yes' ) {
        $pages['membersonly']['active'] = 'yes';
    }
    if( isset($settings['page-info-active']) && $settings['page-info-active'] == 'yes' ) {
        $pages['info']['active'] = 'yes';
    }
    if( isset($settings['page-merchandise-active']) && $settings['page-merchandise-active'] == 'yes' ) {
        $pages['merchandise']['active'] = 'yes';
    }
    if( isset($settings['page-search-active']) && $settings['page-search-active'] == 'yes' ) {
        $pages['search']['active'] = 'yes';
    }

    //
    // Setup other settings
    //
    $rc_settings = array();
    $rc_header = array();
    $rc_background = array();
    $rc_footer = array();
    $rc_advanced = array();
    if( isset($settings['site-theme']) && $settings['site-theme'] != '' ) {
        array_push($rc_settings, array('setting'=>array('name'=>'theme', 'display_name'=>'Theme', 'value'=>$settings['site-theme'])));
    } else {
        array_push($rc_settings, array('setting'=>array('name'=>'theme', 'display_name'=>'Theme', 'value'=>'default')));
    }
    if( isset($settings['site-layout']) && $settings['site-layout'] != '' ) {
        array_push($rc_settings, array('setting'=>array('name'=>'layout', 'display_name'=>'Theme', 'value'=>$settings['site-layout'])));
    } else {
        array_push($rc_settings, array('setting'=>array('name'=>'layout', 'display_name'=>'Theme', 'value'=>'default')));
    }
    if( isset($settings['site-background-image']) && $settings['site-background-image'] > 0 ) {
        array_push($rc_background, array('setting'=>array('name'=>'site-background-image', 'display_name'=>'Background Image', 'value'=>$settings['site-background-image'])));
    } else {
        array_push($rc_background, array('setting'=>array('name'=>'site-background-image', 'display_name'=>'Background Image', 'value'=>'0')));
    }
    $bg_keys = array(
        'site-background-overlay-colour',
        'site-background-overlay-percent',
        'site-background-position-x',
        'site-background-position-y',
        );
    foreach($bg_keys as $bg_key) {
        if( isset($settings[$bg_key]) && $settings[$bg_key] != '' ) {
            array_push($rc_background, array('setting'=>array('name'=>$bg_key, 'value'=>$settings[$bg_key])));
        } else {
            array_push($rc_background, array('setting'=>array('name'=>$bg_key, 'value'=>'')));
        }
    }
    if( isset($settings['site-header-image']) && $settings['site-header-image'] > 0 ) {
        array_push($rc_header, array('setting'=>array('name'=>'site-header-image', 'display_name'=>'Header Image', 'value'=>$settings['site-header-image'])));
    } else {
        array_push($rc_header, array('setting'=>array('name'=>'site-header-image', 'display_name'=>'Header Image', 'value'=>'0')));
    }
    if( isset($settings['site-header-image-size']) && $settings['site-header-image-size'] != '' ) {
        array_push($rc_header, array('setting'=>array('name'=>'site-header-image-size', 'display_name'=>'Header Title', 'value'=>$settings['site-header-image-size'])));
    } else {
        array_push($rc_header, array('setting'=>array('name'=>'site-header-image-size', 'display_name'=>'Header Title', 'value'=>'medium')));
    }
    if( isset($settings['site-header-title']) && $settings['site-header-title'] != '' ) {
        array_push($rc_header, array('setting'=>array('name'=>'site-header-title', 'display_name'=>'Header Title', 'value'=>$settings['site-header-title'])));
    } else {
        array_push($rc_header, array('setting'=>array('name'=>'site-header-title', 'display_name'=>'Header Title', 'value'=>'yes')));
    }
    $header_keys = array(
        'site-header-landingpage1-title',
        'site-header-landingpage1-permalink',
        'site-header-address',
        );
    foreach($header_keys as $header_key) {
        if( isset($settings[$header_key]) && $settings[$header_key] != '' ) {
            array_push($rc_header, array('setting'=>array('name'=>$header_key, 'value'=>$settings[$header_key])));
        } else {
            array_push($rc_header, array('setting'=>array('name'=>$header_key, 'value'=>'')));
        }
    }
    // Footer settings
    if( isset($settings['site-footer-copyright-name']) && $settings['site-footer-copyright-name'] != '' ) {
        array_push($rc_footer, array('setting'=>array('name'=>'site-footer-copyright-name', 'value'=>$settings['site-footer-copyright-name'])));
    }
    if( isset($settings['site-footer-copyright-message']) && $settings['site-footer-copyright-message'] != '' ) {
        array_push($rc_footer, array('setting'=>array('name'=>'site-footer-copyright-message', 'value'=>$settings['site-footer-copyright-message'])));
    }
    $footer_keys = array(
        'site-footer-landingpage1-title',
        'site-footer-landingpage1-permalink',
        'site-footer-message',
        );
    foreach($footer_keys as $footer_key) {
        if( isset($settings[$footer_key]) && $settings[$footer_key] != '' ) {
            array_push($rc_footer, array('setting'=>array('name'=>$footer_key, 'value'=>$settings[$footer_key])));
        } else {
            array_push($rc_footer, array('setting'=>array('name'=>$footer_key, 'value'=>'')));
        }
    }
//  if( isset($settings['site-logo-display']) && $settings['site-logo-display'] != '' ) {
//      array_push($rc_advanced, array('setting'=>array('name'=>'site-logo-display', 'display_name'=>'Header Logo', 'value'=>$settings['site-logo-display'])));
//  } else {
//      array_push($rc_advanced, array('setting'=>array('name'=>'site-logo-display', 'display_name'=>'Header Logo', 'value'=>'no')));
//  }

    //
    // Setup the response
    //
    $rsp['settings'] = $rc_settings;
    $rsp['header'] = $rc_header;
    $rsp['background'] = $rc_background;
    $rsp['footer'] = $rc_footer;
    $rsp['advanced'] = $rc_advanced;
    $rsp['url'] = $url;

    //
    // Check the featured
    //
    if( isset($settings['site-featured']) && $settings['site-featured'] == 'yes' ) {
        $rsp['featured'] = 'yes';
    } else {
        $rsp['featured'] = 'no';
    }

    $ciniki_pages = array();
    foreach($pages as $page => $pagedetails) {
        if( isset($pagedetails['display_name']) ) {
            $rc_page = array('page'=>array('name'=>$page, 'display_name'=>$pagedetails['display_name'], 'active'=>$pagedetails['active']));
            if( isset($pagedetails['id']) ) {
                $rc_page['page']['id'] = $pagedetails['id'];
            }
            array_push($ciniki_pages, $rc_page);
        }
    }

    //
    // Get the list of custom menu pages
    //
    if( isset($ciniki['business']['modules']['ciniki.web']) && ($ciniki['business']['modules']['ciniki.web']['flags']&0x0240) == 0x0240) {
        $pages = array();
        $strsql = "SELECT id, title, permalink, "
            . "IF((flags&0x01)=1,'yes','no') AS active "
            . "FROM ciniki_web_pages "
            . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
            . "AND parent_id = 0 "
            . "ORDER BY sequence, title "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.web', 'page');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['rows']) ) {
            foreach($rc['rows'] as $row) {
                $pages[] = array('page'=>array('id'=>$row['id'], 'display_name'=>$row['title'], 'active'=>$row['active']));
            }
        }
        $rsp['pages'] = $pages;
        $rsp['module_pages'] = $ciniki_pages;
//      $rsp = array('stat'=>'ok', 'featured'=>$featured, 'pages'=>$pages, 'module_pages'=>$ciniki_pages, 'settings'=>$rc_settings, 'header'=>$rc_header, 'footer'=>$rc_footer, 'advanced'=>$rc_advanced, 'url'=>$url);
    } else {
        $rsp['pages'] = $ciniki_pages;
 //       $rsp = array('stat'=>'ok', 'featured'=>$featured, 'pages'=>$ciniki_pages, 'settings'=>$rc_settings, 'header'=>$rc_header, 'footer'=>$rc_footer, 'advanced'=>$rc_advanced, 'url'=>$url);
    }

    //
    // Get the list of landing pages
    //
    if( isset($ciniki['business']['modules']['ciniki.landingpages']) ) {
        $strsql = "SELECT id, permalink, short_title "
            . "FROM ciniki_landingpages "
            . "WHERE ciniki_landingpages.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
            . "ORDER BY ciniki_landingpages.short_title "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
        $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.landingpages', array(
            array('container'=>'landingpages', 'fname'=>'id', 'fields'=>array('id', 'permalink', 'short_title')),
            ));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['landingpages']) ) {
            $rsp['landingpages'] = $rc['landingpages'];
        }
    }

    return $rsp;
}
?>
