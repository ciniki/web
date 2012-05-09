<?php
//
// Description
// -----------
// This function will generate the about page for the website
//
// Arguments
// ---------
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
		$content .= "<div id='page-container'";
	if( isset($ciniki['request']['page-container-class']) && $ciniki['request']['page-container-class'] != '' ) {
		$content .= " class='" . $ciniki['request']['page-container-class'] . "'\n";
	}
	$content .= ">\n";

	$content .= "<header>\n";
	//
	// Display a sign in button, for the master business
	//
	if( $ciniki['request']['business_id'] == $ciniki['config']['core']['master_business_id'] 
		&& isset($ciniki['config']['core']['manage.url']) && $ciniki['config']['core']['manage.url'] != '' ) {
		$content .= "<div class='signin'><a href='" . $ciniki['config']['core']['manage.url'] . "'><span>Sign in</span></a></div>\n";
	}
	if( isset($settings['site-header-image']) && $settings['site-header-image'] > 0 ) {
		$content .= "<hgroup class='header-image'>\n";
	} else {
		$content .= "<hgroup>\n";
	}
	//
	// Decide if there is a header image to be displayed, or display an h1 title
	//
	if( isset($settings['site-header-image']) && $settings['site-header-image'] > 0 ) {
		require_once($ciniki['config']['core']['modules_dir'] . '/web/private/getScaledImageURL.php');
		$rc = ciniki_web_getScaledImageURL($ciniki, $settings['site-header-image'], 'original', 0, '125', '85');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$content .= "<span><a href='" . $ciniki['request']['base_url'] . "/' title='" . $ciniki['business']['details']['name'] . "' rel='home'>"
			. "<img alt='Home' src='" . $rc['url'] . "' />"
			. "</a></span>\n";
	} else {
		$content .= "<h1 id='site-title'><span><a href='" . $ciniki['request']['base_url'] . "/' title='" . $ciniki['business']['details']['name'] . "' rel='home'>" . $ciniki['business']['details']['name'] . "</a></span></h1>\n";
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
	if( isset($settings['page-gallery-active']) && $settings['page-gallery-active'] == 'yes' ) {
		$content .= "<li class='menu-item$hide_menu_class'><a href='" . $ciniki['request']['base_url'] . "/gallery'>Gallery</a></li>";
	}
	if( isset($settings['page-events-active']) && $settings['page-events-active'] == 'yes' ) {
		$content .= "<li class='menu-item$hide_menu_class'><a href='" . $ciniki['request']['base_url'] . "/events'>Events</a></li>";
	}
	if( isset($settings['page-links-active']) && $settings['page-links-active'] == 'yes' ) {
		$content .= "<li class='menu-item$hide_menu_class'><a href='" . $ciniki['request']['base_url'] . "/links'>Links</a></li>";
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
