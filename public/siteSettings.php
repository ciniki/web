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
	require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/core/private/prepareArgs.php');
	$rc = ciniki_core_prepareArgs($ciniki, 'no', array(
		'business_id'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No business specified'),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$args = $rc['args'];

	//
	// Check access to business_id as owner, and load module list
	//
	require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/checkAccess.php');
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
	$pages['contact'] = array('display_name'=>'Contact', 'active'=>'no');
	if( isset($modules['ciniki.events']) ) {
		$pages['events'] = array('display_name'=>'Events', 'active'=>'no');
	}
	if( isset($modules['ciniki.exhibitions']) ) {
		$pages['exhibitions'] = array('display_name'=>'Exhibitions', 'active'=>'no');
	}
	if( isset($modules['ciniki.artcatalog']) || isset($modules['ciniki.gallery']) ) {
		$pages['gallery'] = array('display_name'=>'Gallery', 'active'=>'no');
	}
	if( isset($modules['ciniki.links']) ) {
		$pages['links'] = array('display_name'=>'Links', 'active'=>'no');
	}
	if( isset($modules['ciniki.filedepot']) ) {
		$pages['downloads'] = array('display_name'=>'Downloads', 'active'=>'no');
		$pages['account'] = array('display_name'=>'Account', 'active'=>'no');
	}

	// 
	// If this is the master business, allow extra options
	//
	if( $ciniki['config']['ciniki.core']['master_business_id'] == $args['business_id'] ) {
		$pages['signup'] = array('display_name'=>'Signup', 'active'=>'no');
		$pages['api'] = array('display_name'=>'API', 'active'=>'no');
	}

	//
	// Load current settings
	//
	require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/core/private/dbDetailsQuery.php');
	$rc = ciniki_core_dbDetailsQuery($ciniki, 'ciniki_web_settings', 'business_id', $args['business_id'], 'ciniki.web', 'settings', '');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['settings']) ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'623', 'msg'=>'No settings found, site not configured.'));
	}
	$settings = $rc['settings'];

	//
	// Set which pages are active from the settings
	//
	if( isset($settings['page-home-active']) && $settings['page-home-active'] == 'yes' ) {
		$pages['home']['active'] = 'yes';
	}
	if( isset($settings['page-about-active']) && $settings['page-about-active'] == 'yes' ) {
		$pages['about']['active'] = 'yes';
	}
	if( isset($settings['page-contact-active']) && $settings['page-contact-active'] == 'yes' ) {
		$pages['contact']['active'] = 'yes';
	}
	if( isset($settings['page-events-active']) && $settings['page-events-active'] == 'yes' ) {
		$pages['events']['active'] = 'yes';
	}
	if( isset($settings['page-links-active']) && $settings['page-links-active'] == 'yes' ) {
		$pages['links']['active'] = 'yes';
	}
	if( isset($settings['page-gallery-active']) && $settings['page-gallery-active'] == 'yes' ) {
		$pages['gallery']['active'] = 'yes';
	}
	if( (isset($settings['page-exhibitions-exhibitors-active']) && $settings['page-exhibitions-exhibitors-active'] == 'yes')
		|| (isset($settings['page-exhibitions-sponsors-active']) && $settings['page-exhibitions-sponsors-active'] == 'yes') ) {
		$pages['exhibitions']['active'] = 'yes';
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

	//
	// Setup other settings
	//
	$rc_settings = array();
	$rc_advanced = array();
	if( isset($settings['site-theme']) && $settings['site-theme'] != '' ) {
		array_push($rc_settings, array('setting'=>array('name'=>'theme', 'display_name'=>'Theme', 'value'=>$settings['site-theme'])));
	} else {
		array_push($rc_settings, array('setting'=>array('name'=>'theme', 'display_name'=>'Theme', 'value'=>'default')));
	}
	if( isset($settings['site-header-image']) && $settings['site-header-image'] > 0 ) {
		array_push($rc_advanced, array('setting'=>array('name'=>'site-header-image', 'display_name'=>'Header Image', 'value'=>$settings['site-header-image'])));
	} else {
		array_push($rc_advanced, array('setting'=>array('name'=>'site-header-image', 'display_name'=>'Header Image', 'value'=>'0')));
	}
	if( isset($settings['site-logo-display']) && $settings['site-logo-display'] != '' ) {
		array_push($rc_advanced, array('setting'=>array('name'=>'site-logo-display', 'display_name'=>'Header Logo', 'value'=>$settings['site-logo-display'])));
	} else {
		array_push($rc_advanced, array('setting'=>array('name'=>'site-logo-display', 'display_name'=>'Header Logo', 'value'=>'no')));
	}
	if( isset($settings['site-featured']) && $settings['site-featured'] == 'yes' ) {
		$featured = 'yes';
	} else {
		$featured = 'no';
	}


	$rc_pages = array();
	foreach($pages as $page => $pagedetails) {
		array_push($rc_pages, array('page'=>array('name'=>$page, 'display_name'=>$pagedetails['display_name'], 'active'=>$pagedetails['active'])));
	}

	return array('stat'=>'ok', 'featured'=>$featured, 'pages'=>$rc_pages, 'settings'=>$rc_settings, 'advanced'=>$rc_advanced);
}
?>
