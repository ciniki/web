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
//			<setting name="site-header-image-size" display_name="Header Image Size" value="0" />
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
	// Get the website URL
	// 
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'lookupBusinessURL');
	$rc = ciniki_web_lookupBusinessURL($ciniki, $args['business_id']);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$url = $rc['url'];
	
	//
	// Build list of available pages from modules enabled
	//
	$pages = array();
	$pages['home'] = array('display_name'=>'Home', 'active'=>'no');
	if( isset($modules['ciniki.info']) ) {
		$pages['about'] = array('display_name'=>'About', 'active'=>'no');
	}

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
	if( isset($modules['ciniki.propertyrentals']) ) {
		$pages['propertyrentals'] = array('display_name'=>'Properties', 'active'=>'no');
	}
	if( isset($modules['ciniki.products']) ) {
		$pages['products'] = array('display_name'=>'Products', 'active'=>'no');
	}
	if( isset($modules['ciniki.filmschedule']) ) {
		$pages['filmschedule'] = array('display_name'=>'Schedule', 'active'=>'no');
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
	if( isset($modules['ciniki.fatt']) ) {
		$pages['fatt'] = array('display_name'=>'First Aid', 'active'=>'no');
	}
	if( isset($modules['ciniki.courses']) ) {
		$pages['courses'] = array('display_name'=>'Courses', 'active'=>'no');
	}
	if( isset($modules['ciniki.classes']) ) {
		$pages['classes'] = array('display_name'=>'Classes', 'active'=>'no');
	}
	if( isset($modules['ciniki.artcatalog']) || isset($modules['ciniki.gallery']) ) {
		$pages['gallery'] = array('display_name'=>'Gallery', 'active'=>'no');
	}
	if( isset($modules['ciniki.writingcatalog']) ) {
		$pages['writings'] = array('display_name'=>'Writings', 'active'=>'no');
	}
	if( isset($modules['ciniki.customers']) && ($modules['ciniki.customers']['flags']&0x02) == 0x02 ) {
		$pages['members'] = array('display_name'=>'Members', 'active'=>'no');
	}
	if( isset($modules['ciniki.customers']) && ($modules['ciniki.customers']['flags']&0x10) == 0x10 ) {
		$pages['dealers'] = array('display_name'=>'Dealers', 'active'=>'no');
	}
	if( isset($modules['ciniki.customers']) && ($modules['ciniki.customers']['flags']&0x0100) == 0x0100 ) {
		$pages['distributors'] = array('display_name'=>'Distributors', 'active'=>'no');
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
	if( isset($modules['ciniki.filedepot']) || isset($modules['ciniki.products']) ) {
		$pages['account'] = array('display_name'=>'Account', 'active'=>'no');
	}
	if( isset($modules['ciniki.membersonly']) ) {
		$pages['membersonly'] = array('display_name'=>'Members Only', 'active'=>'no');
	}

	//
	// Pages
	//
	if( isset($modules['ciniki.web']) && ($modules['ciniki.web']['flags']&0x0240) == 0x40) {
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
	if( isset($modules['ciniki.tutorials']) ) {
		$pages['tutorials'] = array('display_name'=>'Tutorials', 'active'=>'no');
	}
	if( isset($modules['ciniki.web']) && ($modules['ciniki.web']['flags']&0x80) == 0x80) {
		$pages['faq'] = array('display_name'=>'FAQ', 'active'=>'no');
	}
	if( isset($modules['ciniki.info']) && ($modules['ciniki.web']['flags']&0x20) > 0 ) {
		$pages['info'] = array('display_name'=>'Information', 'active'=>'no');
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
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'623', 'msg'=>'No settings found, site not configured.'));
	}
	$settings = $rc['settings'];

//	if( isset($settings['page-custom-001-name']) && $settings['page-custom-001-name'] != '' ) {
//		$pages['custom']['display_name'] = $settings['page-custom-001-name'];
//	}

	if( isset($modules['ciniki.web']) && ($modules['ciniki.web']['flags']&0x01) == 0x01) {
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
	}

	//
	// Set which pages are active from the settings
	//
	if( isset($settings['page-home-active']) && $settings['page-home-active'] == 'yes' ) {
		$pages['home']['active'] = 'yes';
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
	if( isset($settings['page-products-active']) && $settings['page-products-active'] == 'yes' ) {
		$pages['products']['active'] = 'yes';
	}
	if( isset($settings['page-recipes-active']) && $settings['page-recipes-active'] == 'yes' ) {
		$pages['recipes']['active'] = 'yes';
	}
	if( isset($settings['page-blog-active']) && $settings['page-blog-active'] == 'yes' ) {
		$pages['blog']['active'] = 'yes';
	}
//	if( isset($settings['page-custom-001-active']) && $settings['page-custom-001-active'] == 'yes' && ($modules['ciniki.web']['flags']&0x01) == 1 ) {
//		$pages['custom']['active'] = 'yes';
//	}
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

	//
	// Setup other settings
	//
	$rc_settings = array();
	$rc_header = array();
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
	// Header settings
	if( isset($settings['site-header-image']) && $settings['site-header-image'] > 0 ) {
		array_push($rc_header, array('setting'=>array('name'=>'site-header-image', 'display_name'=>'Header Image', 'value'=>$settings['site-header-image'])));
//		array_push($rc_advanced, array('setting'=>array('name'=>'site-header-image', 'display_name'=>'Header Image', 'value'=>$settings['site-header-image'])));
	} else {
		array_push($rc_header, array('setting'=>array('name'=>'site-header-image', 'display_name'=>'Header Image', 'value'=>'0')));
//		array_push($rc_advanced, array('setting'=>array('name'=>'site-header-image', 'display_name'=>'Header Image', 'value'=>'0')));
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
	// Footer settings
	if( isset($settings['site-footer-copyright-name']) && $settings['site-footer-copyright-name'] != '' ) {
		array_push($rc_footer, array('setting'=>array('name'=>'site-footer-copyright-name', 'display_name'=>'Copyright Title', 'value'=>$settings['site-footer-copyright-name'])));
	}
	if( isset($settings['site-footer-copyright-message']) && $settings['site-footer-copyright-message'] != '' ) {
		array_push($rc_footer, array('setting'=>array('name'=>'site-footer-copyright-message', 'display_name'=>'Copyright Title', 'value'=>$settings['site-footer-copyright-message'])));
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
	if( isset($modules['ciniki.web']) && ($modules['ciniki.web']['flags']&0x0240) == 0x0240) {
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
		return array('stat'=>'ok', 'featured'=>$featured, 'pages'=>$pages, 'module_pages'=>$ciniki_pages, 'settings'=>$rc_settings, 'header'=>$rc_header, 'footer'=>$rc_footer, 'advanced'=>$rc_advanced, 'url'=>$url);
	}

	return array('stat'=>'ok', 'featured'=>$featured, 'pages'=>$ciniki_pages, 'settings'=>$rc_settings, 'header'=>$rc_header, 'footer'=>$rc_footer, 'advanced'=>$rc_advanced, 'url'=>$url);
}
?>
