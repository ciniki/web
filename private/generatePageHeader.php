<?php
//
// Description
// -----------
// This function will generate the header for the website, to be displayed 
// at the top of the all pages.
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure, similar to ciniki variable but only web specific information.
// title:			The title to use for the page.
//
// Returns
// -------
//
function ciniki_web_generatePageHeader($ciniki, $settings, $title) {

	//
	// Store the header content
	//
	$content = '';

	// Generate the head content
	$content .= "<!DOCTYPE html>\n"
		. "<html>\n"
		. "<head>\n"
		. "<title>" . $ciniki['business']['details']['name'];
	if( $title != '' ) {
		$content .= " - " . $title;
	}
	$content .= "</title>\n"
		. "";

	// Add required layout, theme and js files
	if( file_exists($ciniki['request']['layout_dir'] . '/' . $settings['site-layout'] . '/global.css') ) {
		$content .= "<link rel='stylesheet' type='text/css' media='all' href='" . $ciniki['request']['layout_url'] 
			. '/' . $settings['site-layout'] . "/global.css' />\n";
	} else if( file_exists($ciniki['request']['layout_dir'] . '/default/global.css') ) {
		$content .= "<link rel='stylesheet' type='text/css' media='all' href='" . $ciniki['request']['layout_url'] 
			. "/default/global.css' />\n";
	}
	if( file_exists($ciniki['request']['layout_dir'] . '/' . $settings['site-layout'] . '/layout.css') ) {
		$content .= "<link rel='stylesheet' type='text/css' media='all and (min-width: 33.236em)' href='" . $ciniki['request']['layout_url'] 
			. '/' . $settings['site-layout'] . "/layout.css' />\n"
			. "<!--[if (lt IE 9) & (!IEMobile)]>\n"
			. "<link rel='stylesheet' type='text/css' media='all' href='" . $ciniki['request']['layout_url'] 
			. '/' . $settings['site-layout'] . "/layout.css' />\n"
		  	. "<![endif]-->\n"
			. "<!--[if IE 8]>\n"
			. "<link rel='stylesheet' type='text/css' media='all' href='" . $ciniki['request']['layout_url'] 
			. '/' . $settings['site-layout'] . "/ie8.css' />\n"
			. "<![endif]-->\n"
			. "";
	} else if( file_exists($ciniki['request']['layout_dir'] . '/default/layout.css') ) {
		$content .= "<link rel='stylesheet' type='text/css' media='all and (min-width: 33.236em)' href='" . $ciniki['request']['layout_url'] 
			. "/default/layout.css' />\n"
			. "<!--[if (lt IE 9) & (!IEMobile)]>\n"
			. "<link rel='stylesheet' type='text/css' media='all' href='" . $ciniki['request']['layout_url'] . "/default/layout.css' />\n"
		  	. "<![endif]-->\n"
			. "";
	}

	if( file_exists($ciniki['request']['theme_dir'] . '/' . $settings['site-theme'] . '/style.css') ) {
		$content .= "<link rel='stylesheet' type='text/css' media='all' href='" . $ciniki['request']['theme_url'] 
			. '/' . $settings['site-theme'] . "/style.css' />\n";
	} else if( file_exists($ciniki['request']['theme_dir'] . '/default/style.css') ) {
		$content .= "<link rel='stylesheet' type='text/css' media='all' href='" . $ciniki['request']['theme_url'] 
			. "/default/style.css' />\n";
	}
	
	//
	// Header to support mobile device resize
	//
	$content .= '<meta name="viewport" content="width=device-width, initial-scale=1.0">' . "\n";

	//
	// Include any inline javascript
	//
	if( isset($ciniki['request']['inline_javascript']) && $ciniki['request']['inline_javascript'] != '' ) {
		$content .= $ciniki['request']['inline_javascript'];
	}

	//
	// Include google analytics
	//
	if( isset($settings['site-google-analytics-account']) && $settings['site-google-analytics-account'] != '' ) {
		$content .= "<script type='text/javascript'>\n"
			. "var _gaq = _gaq || [];\n"
			. "_gaq.push(['_setAccount', '" . $settings['site-google-analytics-account'] . "']);\n"
			. "_gaq.push(['_trackPageview']);\n"
			. "(function() {\n"
				. "var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;\n"
				. "ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';\n"
				. "var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);\n"
			. "})();\n"
			. "</script>\n"
			. "";
	}

	$content .= "</head>\n";

	// Generate header of the page
	$content .= "<body";
	if( isset($ciniki['request']['onresize']) && $ciniki['request']['onresize'] != '' ) {
		$content .= " onresize='" . $ciniki['request']['onresize'] . "'";
	}
	if( isset($ciniki['request']['onload']) && $ciniki['request']['onload'] != '' ) {
		$content .= " onload='" . $ciniki['request']['onload'] . "'";
	}
	$content .= ">\n";

	//
	// Check if we are to display a sign in button
	//
	$signin_content = '';
	if( $ciniki['request']['business_id'] == $ciniki['config']['ciniki.core']['master_business_id'] 
		&& isset($ciniki['config']['ciniki.core']['manage.url']) && $ciniki['config']['ciniki.core']['manage.url'] != '' ) {
		$signin_content .= "<div class='signin'><a href='" . $ciniki['config']['ciniki.core']['manage.url'] . "'><span>Sign in</span></a></div>\n";
	} 
	// Display a customer signin for regular businesses
	elseif( $ciniki['request']['business_id'] != $ciniki['config']['ciniki.core']['master_business_id']
		&& isset($settings['page-account-active']) && $settings['page-account-active'] == 'yes'
		&& ((isset($settings['page-downloads-customers']) && $settings['page-downloads-customers'] == 'yes')
		// || () // Used if there are other pages that allow customer only content
		)) {
		if( isset($ciniki['session']['customer']['id']) > 0 ) {
			$signin_content .= "<div class='signin'><a href='" . $ciniki['request']['base_url'] . "/account'><span>My Account</span></a></div>\n";
		} else {
			$signin_content .= "<div class='signin'><a href='" . $ciniki['request']['base_url'] . "/account'><span>Sign In</span></a></div>\n";
		}
	}

	//
	// Setup the page-container
	//
	$content .= "<div id='page-container'";
	$page_container_class = '';
	if( isset($ciniki['request']['page-container-class']) && $ciniki['request']['page-container-class'] != '' ) {
		$page_container_class = $ciniki['request']['page-container-class'];
	}
	if( $signin_content != '' ) {
		if( $page_container_class != '' ) { $page_container_class .= " "; }
		$page_container_class .= 'signin';
	}
	if( isset($settings['site-logo-display']) && $settings['site-logo-display'] == 'yes' 
		&& isset($ciniki['business']['details']['logo_id']) && $ciniki['business']['details']['logo_id'] > 0 ) {
		if( $page_container_class != '' ) { $page_container_class .= " "; }
		$page_container_class .= 'logo';
	}
	if( $page_container_class != '' ) {
		$content .= " class='$page_container_class'";
	}
	$content .= ">\n";

	$content .= "<header>\n";

	// Add signin button if any.
	$content .= $signin_content;

	if( isset($settings['site-header-image']) && $settings['site-header-image'] > 0 ) {
		$content .= "<hgroup class='header-image'>\n";
	} else {
		$content .= "<hgroup>\n";
	}
	//
	// Decide if there is a header image to be displayed, or display an h1 title
	//
	if( isset($settings['site-header-image']) && $settings['site-header-image'] > 0 ) {
		require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/getScaledImageURL.php');
		$rc = ciniki_web_getScaledImageURL($ciniki, $settings['site-header-image'], 'original', 0, '125', '85');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$content .= "<span><a href='" . $ciniki['request']['base_url'] . "/' title='" . $ciniki['business']['details']['name'] . "' rel='home'>"
			. "<img alt='Home' src='" . $rc['url'] . "' />"
			. "</a></span>\n";
	} else {
		$content .= "<h1 id='site-title'>";
		if( isset($settings['site-logo-display']) && $settings['site-logo-display'] == 'yes' 
			&& isset($ciniki['business']['details']['logo_id']) && $ciniki['business']['details']['logo_id'] > 0 ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
			$rc = ciniki_web_getScaledImageURL($ciniki, $ciniki['business']['details']['logo_id'], 'original', 0, '100', '85');
			$content .= "<span class='logo'><a href='" . $ciniki['request']['base_url'] . "/' title='" . $ciniki['business']['details']['name'] 
				. "' rel='home'><img alt='Home' src='" . $rc['url'] . "' /></a></span>";
		}
		$content .= "<span class='title'><a href='" . $ciniki['request']['base_url'] . "/' title='" . $ciniki['business']['details']['name'] . "' rel='home'>" . $ciniki['business']['details']['name'] . "</a></span></h1>\n";
	}


	
	if( isset($ciniki['business']['details']['tagline']) && $ciniki['business']['details']['tagline'] != '' ) {
		$content .= "<h2 id='site-description'>" . $ciniki['business']['details']['tagline'] . "</h2>\n";
	}
	$content .= "</hgroup>\n";

	//
	// Generate menu
	//
	$content .= "<nav id='access' role='navigation'>\n"
		. "<h3 class='assistive-text'>Main menu</h3>\n"
		. "";
	$content .= "<div id='main-menu-container'>"
		. "<ul id='main-menu' class='menu'>\n"
		. "<li class='menu-item'><a href='" . $ciniki['request']['base_url'] . "/'>Home</a></li>"
		. "";
	$hide_menu_class = '';
	if( $ciniki['request']['page'] != 'home' && $ciniki['request']['page'] != 'masterindex' ) {
		$hide_menu_class = ' compact-hidden';
	}
	if( isset($settings['page-about-active']) && $settings['page-about-active'] == 'yes' ) {
		$content .= "<li class='menu-item$hide_menu_class'><a href='" . $ciniki['request']['base_url'] . "/about'>About</a></li>";
	}
	if( isset($settings['page-signup-active']) && $settings['page-signup-active'] == 'yes' ) {
		$content .= "<li class='menu-item$hide_menu_class'><a href='" . $ciniki['request']['base_url'] . "/signup'>Sign Up</a></li>";
	}
	if( isset($settings['page-exhibitions-exhibition']) && $settings['page-exhibitions-exhibition'] > 0
		&& isset($settings['page-exhibitions-exhibitors-active']) && $settings['page-exhibitions-exhibitors-active'] == 'yes' ) {
		$content .= "<li class='menu-item$hide_menu_class'><a href='" . $ciniki['request']['base_url'] . "/exhibitors'>Exhibitors</a></li>";
	}
	if( isset($settings['page-exhibitions-exhibition']) && $settings['page-exhibitions-exhibition'] > 0
		&& isset($settings['page-exhibitions-sponsors-active']) && $settings['page-exhibitions-sponsors-active'] == 'yes' ) {
//		$content .= "<li class='menu-item$hide_menu_class'><a href='" . $ciniki['request']['base_url'] . "/sponsors'>Sponsors</a></li>";
	}
	if( isset($settings['page-gallery-active']) && $settings['page-gallery-active'] == 'yes' ) {
		$content .= "<li class='menu-item$hide_menu_class'><a href='" . $ciniki['request']['base_url'] . "/gallery'>Gallery</a></li>";
	}
	if( isset($settings['page-events-active']) && $settings['page-events-active'] == 'yes' ) {
		$content .= "<li class='menu-item$hide_menu_class'><a href='" . $ciniki['request']['base_url'] . "/events'>Events</a></li>";
	}
	if( isset($settings['page-links-active']) && $settings['page-links-active'] == 'yes' ) {
		$content .= "<li class='menu-item$hide_menu_class'><a href='" . $ciniki['request']['base_url'] . "/links'>Links</a></li>";
	}
	if( isset($settings['page-downloads-active']) && $settings['page-downloads-active'] == 'yes' 
		&& ( 
			(isset($settings['page-downloads-public']) && $settings['page-downloads-public'] == 'yes')
			|| 
			(isset($settings['page-downloads-customers']) && $settings['page-downloads-customers'] == 'yes' 
				&& isset($ciniki['session']['customer']['id']) && $ciniki['session']['customer']['id'] > 0 )
			)
		) {
		$content .= "<li class='menu-item$hide_menu_class'><a href='" . $ciniki['request']['base_url'] . "/downloads'>";
		if( isset($settings['page-downloads-name']) && $settings['page-downloads-name'] != '' ) {
			$content .= $settings['page-downloads-name'];
		} else {
			$content .= "Downloads";
		}
		$content .= "</a></li>";
	}

	if( isset($settings['page-contact-active']) && $settings['page-contact-active'] == 'yes' ) {
		$content .= "<li class='menu-item$hide_menu_class'><a href='" . $ciniki['request']['base_url'] . "/contact'>Contact</a></li>";
	}
	$content .= "</ul>\n"
		. "</div>\n";
		
	$content .= "</nav>\n"
		. "</header>\n"
		. "";
	$content .= "<hr class='section-divider header-section-divider' />\n";

	return array('stat'=>'ok', 'content'=>$content);
}
?>
