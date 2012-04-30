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
	if( file_exists($ciniki['request']['layout_dir'] . '/' . $settings['site.layout'] . '/style.css') ) {
		$content .= "<link rel='stylesheet' type='text/css' media='all' href='" . $ciniki['request']['layout_url'] 
			. '/' . $settings['site.layout'] . "/style.css' />\n";
	} else if( file_exists($ciniki['request']['layout_dir'] . '/default/style.css') ) {
		$content .= "<link rel='stylesheet' type='text/css' media='all' href='" . $ciniki['request']['layout_url'] 
			. "/default/style.css' />\n";
	}
	if( file_exists($ciniki['request']['theme_dir'] . '/' . $settings['site.theme'] . '/style.css') ) {
		$content .= "<link rel='stylesheet' type='text/css' media='all' href='" . $ciniki['request']['theme_url'] 
			. '/' . $settings['site.theme'] . "/style.css' />\n";
	} else if( file_exists($ciniki['request']['theme_dir'] . '/default/style.css') ) {
		$content .= "<link rel='stylesheet' type='text/css' media='all' href='" . $ciniki['request']['theme_url'] 
			. "/default/style.cssl' />\n";
	}
	
	//
	// Header to support mobile device resize
	//
	$content .= '<meta name="viewport" content="width=device-width, initial-scale=1.0">' . "\n";

	$content .= "</head>\n";

	// Generate header of the page
	$content .= "<body>\n"
		. "<div id='page-container'>\n"
		. "<header>\n"
		. "<hgroup>\n"
		. "<h1 id='site-title'><span><a href='" . $ciniki['request']['base_url'] . "/' title='" . $ciniki['business']['details']['name'] . "' rel='home'>" . $ciniki['business']['details']['name'] . "</a></span></h1>\n";

	
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
	if( isset($settings['page.about.active']) && $settings['page.about.active'] == 'yes' ) {
		$content .= "<li class='menu-item'><a href='" . $ciniki['request']['base_url'] . "/about'>About</a></li>";
	}
	if( isset($settings['page.gallery.active']) && $settings['page.gallery.active'] == 'yes' ) {
		$content .= "<li class='menu-item'><a href='" . $ciniki['request']['base_url'] . "/gallery'>Gallery</a></li>";
	}
	if( isset($settings['page.events.active']) && $settings['page.events.active'] == 'yes' ) {
		$content .= "<li class='menu-item'><a href='" . $ciniki['request']['base_url'] . "/events'>Events</a></li>";
	}
	if( isset($settings['page.links.active']) && $settings['page.links.active'] == 'yes' ) {
		$content .= "<li class='menu-item'><a href='" . $ciniki['request']['base_url'] . "/links'>Links</a></li>";
	}
	if( isset($settings['page.contact.active']) && $settings['page.contact.active'] == 'yes' ) {
		$content .= "<li class='menu-item'><a href='" . $ciniki['request']['base_url'] . "/contact'>Contact</a></li>";
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
