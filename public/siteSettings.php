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
// business_id:		The ID of the business to get the site settings for.
//
// Returns
// -------
// <rsp stat="ok" featured="no">
//		<pages>
//			<page name="home" display_name="Home" active="yes" />
//			<page name="about" display_name="About" active="yes" />
//			<page name="contact" display_name="Contact" active="yes" />
//			<page name="events" display_name="Events" active="no" />
//			<page name="gallery" display_name="Gallery" active="yes" />
//			<page name="links" display_name="Links" active="yes" />
//		</pages>
//		<settings>
//			<setting name="theme" display_name="Theme" value="black" />
//		</settings>
//		<header>
//			<setting name="site-header-image" display_name="Header Image" value="0" />
//			<setting name="site-header-title" display_name="Header Logo" value="no" />
//		</header>
//		<advanced>
//			<setting name="site-header-image" display_name="Header Image" value="0" />
//			<setting name="site-logo-display" display_name="Header Logo" value="no" />
//		</advanced>
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
	$modules = $ac['modules'];
	
	//
	// Build list of available pages from modules enabled
	//
	$pages = array();
	$pages['home'] = array('display_name'=>'Home', 'active'=>'no');
	$pages['about'] = array('display_name'=>'About', 'active'=>'no');
	if( isset($modules['ciniki.marketing']) && ($modules['ciniki.marketing']['flags']&0x01) == 0x01 ) {
		$pages['features'] = array('display_name'=>'Features', 'active'=>'no');
	}
	if( isset($modules['ciniki.web']) && ($modules['ciniki.web']['flags']&0x01) == 1) {
		$pages['custom-001'] = array('display_name'=>'Custom Page', 'active'=>'no');
		$pages['custom-002'] = array('display_name'=>'Custom Page', 'active'=>'no');
		$pages['custom-003'] = array('display_name'=>'Custom Page', 'active'=>'no');
		$pages['custom-004'] = array('display_name'=>'Custom Page', 'active'=>'no');
		$pages['custom-005'] = array('display_name'=>'Custom Page', 'active'=>'no');
	}
	if( isset($modules['ciniki.products']) ) {
		$pages['products'] = array('display_name'=>'Products', 'active'=>'no');
	}
	if( isset($modules['ciniki.workshops']) ) {
		$pages['workshops'] = array('display_name'=>'Workshops', 'active'=>'no');
	}
	if( isset($modules['ciniki.events']) ) {
		$pages['events'] = array('display_name'=>'Events', 'active'=>'no');
	}
	if( isset($modules['ciniki.exhibitions']) ) {
		$pages['exhibitions'] = array('display_name'=>'Exhibitions', 'active'=>'no');
	}
	if( isset($modules['ciniki.courses']) ) {
		$pages['courses'] = array('display_name'=>'Courses', 'active'=>'no');
	}
	if( isset($modules['ciniki.artcatalog']) || isset($modules['ciniki.gallery']) ) {
		$pages['gallery'] = array('display_name'=>'Gallery', 'active'=>'no');
	}
	if( isset($modules['ciniki.customers']) && ($modules['ciniki.customers']['flags']&0x02) == 0x02 ) {
		$pages['members'] = array('display_name'=>'Members', 'active'=>'no');
	}
	if( isset($modules['ciniki.artclub']) ) {
		$pages['members'] = array('display_name'=>'Members', 'active'=>'no');
	}
	if( isset($modules['ciniki.sponsors']) ) {
		$pages['sponsors'] = array('display_name'=>'Sponsors', 'active'=>'no');
	}
	if( isset($modules['ciniki.artgallery']) ) {
		$pages['artgalleryexhibitions'] = array('display_name'=>'Exhibitions', 'active'=>'no');
	}
	if( isset($modules['ciniki.directory']) ) {
		$pages['directory'] = array('display_name'=>'Directory', 'active'=>'no');
	}
	if( isset($modules['ciniki.links']) ) {
		$pages['links'] = array('display_name'=>'Links', 'active'=>'no');
	}
	if( isset($modules['ciniki.newsletters']) ) {
		$pages['newsletters'] = array('display_name'=>'Newsletters', 'active'=>'no');
	}
	if( isset($modules['ciniki.filedepot']) ) {
		$pages['downloads'] = array('display_name'=>'Downloads', 'active'=>'no');
		$pages['account'] = array('display_name'=>'Account', 'active'=>'no');
	}
	if( isset($modules['ciniki.blog']) ) {
		if( ($modules['ciniki.blog']['flags']&0x01) > 0 ) {
			$pages['blog'] = array('display_name'=>'Blog', 'active'=>'no');
		}
		if( ($modules['ciniki.blog']['flags']&0x0100) > 0 ) {
			$pages['account'] = array('display_name'=>'Account', 'active'=>'no');
			$pages['memberblog'] = array('display_name'=>'Member News', 'active'=>'no');
		}
	}
	if( isset($modules['ciniki.sapos']) && ($modules['ciniki.sapos']['flags']&0x08) > 0 ) {
		$pages['cart'] = array('display_name'=>'Shopping Cart', 'active'=>'no');
	}
	if( isset($modules['ciniki.recipes']) ) {
		$pages['recipes'] = array('display_name'=>'Recipes', 'active'=>'no');
	}
	if( isset($modules['ciniki.surveys']) ) {
		$pages['surveys'] = array('display_name'=>'Surveys', 'active'=>'no');
	}

	// 
	// If this is the master business, allow extra options
	//
	if( $ciniki['config']['ciniki.core']['master_business_id'] == $args['business_id'] ) {
		$pages['signup'] = array('display_name'=>'Signup', 'active'=>'no');
		$pages['api'] = array('display_name'=>'API', 'active'=>'no');
	}
	$pages['faq'] = array('display_name'=>'FAQ', 'active'=>'no');
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
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'623', 'msg'=>'No settings found, site not configured.'));
	}
	$settings = $rc['settings'];

	if( isset($settings['page-custom-001-name']) && $settings['page-custom-001-name'] != '' ) {
		$pages['custom']['display_name'] = $settings['page-custom-001-name'];
	}

	for($i=1;$i<6;$i++) {
		$pname = 'page-custom-' . sprintf("%03d", $i);
		$cname = 'custom-' . sprintf("%03d", $i);
		if( isset($settings[$pname . '-name']) && $settings[$pname . '-name'] != '' ) {
			$pages['custom-00' . $i]['display_name'] = $settings[$pname . '-name'];
		}
		if( isset($settings[$pname . '-active']) 
			&& $settings[$pname . '-active'] == 'yes' && ($modules['ciniki.web']['flags']&0x01) == 1 ) {
			$pages[$cname]['active'] = 'yes';
		}
	}

	//
	// Set which pages are active from the settings
	//
	if( isset($settings['page-home-active']) && $settings['page-home-active'] == 'yes' ) {
		$pages['home']['active'] = 'yes';
	}
	if( isset($settings['page-about-active']) && $settings['page-about-active'] == 'yes' ) {
		$pages['about']['active'] = 'yes';
	}
	if( isset($settings['page-features-active']) && $settings['page-features-active'] == 'yes' ) {
		$pages['features']['active'] = 'yes';
	}
	if( isset($settings['page-products-active']) && $settings['page-products-active'] == 'yes' ) {
		$pages['products']['active'] = 'yes';
	}
	if( isset($settings['page-recipes-active']) && $settings['page-recipes-active'] == 'yes' ) {
		$pages['recipes']['active'] = 'yes';
	}
	if( isset($settings['page-blog-active']) && $settings['page-blog-active'] == 'yes' ) {
		$pages['blog']['active'] = 'yes';
	}
	if( isset($settings['page-custom-001-active']) && $settings['page-custom-001-active'] == 'yes' && ($modules['ciniki.web']['flags']&0x01) == 1 ) {
		$pages['custom']['active'] = 'yes';
	}
	if( isset($settings['page-contact-active']) && $settings['page-contact-active'] == 'yes' ) {
		$pages['contact']['active'] = 'yes';
	}
	if( isset($settings['page-faq-active']) && $settings['page-faq-active'] == 'yes' ) {
		$pages['faq']['active'] = 'yes';
	}
	if( isset($settings['page-events-active']) && $settings['page-events-active'] == 'yes' ) {
		$pages['events']['active'] = 'yes';
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
	if( isset($settings['page-members-active']) && $settings['page-members-active'] == 'yes' ) {
		$pages['members']['active'] = 'yes';
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
	if( isset($settings['page-courses-active']) && $settings['page-courses-active'] == 'yes' ) {
		$pages['courses']['active'] = 'yes';
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

	//
	// Setup other settings
	//
	$rc_settings = array();
	$rc_header = array();
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
	if( isset($settings['site-header-image']) && $settings['site-header-image'] > 0 ) {
		array_push($rc_header, array('setting'=>array('name'=>'site-header-image', 'display_name'=>'Header Image', 'value'=>$settings['site-header-image'])));
//		array_push($rc_advanced, array('setting'=>array('name'=>'site-header-image', 'display_name'=>'Header Image', 'value'=>$settings['site-header-image'])));
	} else {
		array_push($rc_header, array('setting'=>array('name'=>'site-header-image', 'display_name'=>'Header Image', 'value'=>'0')));
//		array_push($rc_advanced, array('setting'=>array('name'=>'site-header-image', 'display_name'=>'Header Image', 'value'=>'0')));
	}
	if( isset($settings['site-header-title']) && $settings['site-header-title'] != '' ) {
		array_push($rc_header, array('setting'=>array('name'=>'site-header-title', 'display_name'=>'Header Title', 'value'=>$settings['site-header-title'])));
	} else {
		array_push($rc_header, array('setting'=>array('name'=>'site-header-title', 'display_name'=>'Header Title', 'value'=>'yes')));
	}
//	if( isset($settings['site-logo-display']) && $settings['site-logo-display'] != '' ) {
//		array_push($rc_advanced, array('setting'=>array('name'=>'site-logo-display', 'display_name'=>'Header Logo', 'value'=>$settings['site-logo-display'])));
//	} else {
//		array_push($rc_advanced, array('setting'=>array('name'=>'site-logo-display', 'display_name'=>'Header Logo', 'value'=>'no')));
//	}
	if( isset($settings['site-featured']) && $settings['site-featured'] == 'yes' ) {
		$featured = 'yes';
	} else {
		$featured = 'no';
	}


	$rc_pages = array();
	foreach($pages as $page => $pagedetails) {
		if( isset($pagedetails['display_name']) ) {
			array_push($rc_pages, array('page'=>array('name'=>$page, 'display_name'=>$pagedetails['display_name'], 'active'=>$pagedetails['active'])));
		}
	}

	return array('stat'=>'ok', 'featured'=>$featured, 'pages'=>$rc_pages, 'settings'=>$rc_settings, 'header'=>$rc_header, 'advanced'=>$rc_advanced);
}
?>
