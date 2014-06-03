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
function ciniki_web_generatePageHeader($ciniki, $settings, $title, $submenu) {

	//
	// Store the header content
	//
	$content = '';

	// Used if there is a redirect to another site
	$page_home_url = $ciniki['request']['base_url'] . '/';
	if( isset($settings['page-home-url']) && $settings['page-home-url'] != '' ) {
		$page_home_url = $settings['page-home-url'];
	}

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

	//
	// Add required layout css files
	//
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
			. "<script>\n"
				. "document.createElement('header');\n"
				. "document.createElement('nav');\n"
				. "document.createElement('section');\n"
				. "document.createElement('article');\n"
				. "document.createElement('aside');\n"
				. "document.createElement('footer');\n"
			. "</script>\n"
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

	//
	// Check for ie8.css file in the layout directory
	//
	if( file_exists($ciniki['request']['layout_dir'] . '/' . $settings['site-layout'] . '/ie8.css') ) {
		$content .= "<!--[if IE 8]>\n"
			. "<link rel='stylesheet' type='text/css' media='all' href='" . $ciniki['request']['layout_url'] 
			. '/' . $settings['site-layout'] . "/ie8.css' />\n"
			. "<![endif]-->\n"
			. "";
	} else if( file_exists($ciniki['request']['layout_dir'] . '/default/ie8.css') ) {
		$content .= "<!--[if IE 8]>\n"
			. "<link rel='stylesheet' type='text/css' media='all' href='" . $ciniki['request']['layout_url'] 
			. "/default/ie8.css' />\n"
			. "<![endif]-->\n"
			. "";
	} 

	//
	// Add the theme files
	//
	if( file_exists($ciniki['request']['theme_dir'] . '/' . $settings['site-theme'] . '/style.css') ) {
		$content .= "<link rel='stylesheet' type='text/css' media='all' href='" . $ciniki['request']['theme_url'] 
			. '/' . $settings['site-theme'] . "/style.css' />\n";
		if( file_exists($ciniki['request']['theme_dir'] . '/' . $settings['site-theme'] . '/ie9.css') ) {
			$content .= "<!--[if (IE 9) & (!IEMobile)]>\n"
				. "<link rel='stylesheet' type='text/css' media='all' href='" . $ciniki['request']['theme_url'] 
				. '/' . $settings['site-theme'] . "/ie9.css' />\n"
				. "<![endif]-->\n";
		}
		if( file_exists($ciniki['request']['theme_dir'] . '/' . $settings['site-theme'] . '/ie8.css') ) {
			$content .= "<!--[if (IE 8) & (!IEMobile)]>\n"
				. "<link rel='stylesheet' type='text/css' media='all' href='" . $ciniki['request']['theme_url'] 
				. '/' . $settings['site-theme'] . "/ie8.css' />\n"
				. "<![endif]-->\n";
		}
		if( file_exists($ciniki['request']['theme_dir'] . '/' . $settings['site-theme'] . '/ie.css') ) {
			$content .= "<!--[if (lt IE 8)]>\n"
				. "<link rel='stylesheet' type='text/css' media='all' href='" . $ciniki['request']['theme_url'] 
				. '/' . $settings['site-theme'] . "/ie.css' />\n"
				. "<![endif]-->\n";
		}
		if( file_exists($ciniki['request']['theme_dir'] . '/' . $settings['site-theme'] . '/print.css') ) {
			$content .= "<link rel='stylesheet' type='text/css' media='print' href='" . $ciniki['request']['theme_url'] . '/' . $settings['site-theme'] . "/print.css' />\n";
		}
	} else if( file_exists($ciniki['request']['theme_dir'] . '/default/style.css') ) {
		$content .= "<link rel='stylesheet' type='text/css' media='all' href='" . $ciniki['request']['theme_url'] 
			. "/default/style.css' />\n";
		if( file_exists($ciniki['request']['theme_dir'] . '/default/ie9.css') ) {
			$content .= "<!--[if (IE 9) & (!IEMobile)]>\n"
				. "<link rel='stylesheet' type='text/css' media='all' href='" . $ciniki['request']['theme_url'] . "/default/ie9.css' />\n"
				. "<![endif]-->\n";
		}
		if( file_exists($ciniki['request']['theme_dir'] . '/default/ie8.css') ) {
			$content .= "<!--[if (IE 8) & (!IEMobile)]>\n"
				. "<link rel='stylesheet' type='text/css' media='all' href='" . $ciniki['request']['theme_url'] . "/default/ie8.css' />\n"
				. "<![endif]-->\n";
		}
		if( file_exists($ciniki['request']['theme_dir'] . '/default/ie.css') ) {
			$content .= "<!--[if (lt IE 8)]>\n"
				. "<link rel='stylesheet' type='text/css' media='all' href='" . $ciniki['request']['theme_url'] . "/default/ie.css' />\n"
				. "<![endif]-->\n";
		}
		if( file_exists($ciniki['request']['theme_dir'] . '/default/print.css') ) {
			$content .= "<link rel='stylesheet' type='text/css' media='print' href='" . $ciniki['request']['theme_url'] . "/default/print.css' />\n";
		}
	}

	//
	// Check head links
	//
	if( isset($ciniki['response']['head']['links']) ) {
		foreach($ciniki['response']['head']['links'] as $link) {
			$content .= "<link rel='" . $link['rel'] . "' href='" . $link['href'] . "'/>\n";
		}
	}
	
	//
	// Header to support mobile device resize
	//
	$content .= '<meta name="viewport" content="width=device-width, initial-scale=1.0">' . "\n";
	$content .= '<meta charset="UTF-8">' . "\n";

	//
	// Check for header Open Graph (Facebook) object information, for better linking into facebook
	//
	if( isset($ciniki['response']['head']['og']) ) {
		$og_site_name = $ciniki['business']['details']['name'];
		foreach($ciniki['response']['head']['og'] as $og_type => $og_value) {
//			if( $og_type != 'og:description' && $og_value != '' ) {
			if( $og_value != '' ) {
				if( $og_type == 'site_name' ) {
					$og_site_name = $og_value;
				}
				$content .= '<meta property="og:' . $og_type . '" content="' . preg_replace('/"/', "'", $og_value) . '"/>' . "\n";
			}
		}
		if( $og_site_name != '' ) {
			$content .= "<meta property=\"og:site_name\" content=\"" . preg_replace('/"/', "\'", $og_site_name) . "\"/>\n";
		}
		if( $ciniki['response']['head']['og']['title'] == '' ) {
			$content .= '<meta property="og:title" content="' . $ciniki['business']['details']['name'] . ' - ' . $title . '"/>' . "\n";
			
		}
	}

//	if( isset($ciniki['response']['head']['sharethis']['enable']) && $ciniki['response']['head']['sharethis']['enable'] == 'yes' ) {
//		$content .= '<script type="text/javascript">var switchTo5x=true;</script>' . "\n"
//			. '<script type="text/javascript" src="http://w.sharethis.com/button/buttons.js"></script>' . "\n"
//			. '<script type="text/javascript">stLight.options({publisher: "289f0396-66fc-44a7-ad22-5fc0d836da3f", doNotHash: false, doNotCopy: false, hashAddressBar: false});</script>' . "\n"
//			. '';
//	}

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

	//
	// Check if there is custom CSS to include
	//
	if( isset($settings['site-custom-css']) && $settings['site-custom-css'] ) {
		$content .= "<style>" . $settings['site-custom-css'] . "</style>";
	}

	//
	// Tried the html5shiv to correct ie8 Mono Social Icon Font problems, but did not work
	//
//	$content .= "<!--[if lt IE 9]>\n"
//		. '<script language="javascript">/*
//		 HTML5 Shiv v3.7.0 | @afarkas @jdalton @jon_neal @rem | MIT/GPL2 Licensed
//		 */
//		 (function(l,f){function m(){var a=e.elements;return"string"==typeof a?a.split(" "):a}function i(a){var b=n[a[o]];b||(b={},h++,a[o]=h,n[h]=b);return b}function p(a,b,c){b||(b=f);if(g)return b.createElement(a);c||(c=i(b));b=c.cache[a]?c.cache[a].cloneNode():r.test(a)?(c.cache[a]=c.createElem(a)).cloneNode():c.createElem(a);return b.canHaveChildren&&!s.test(a)?c.frag.appendChild(b):b}function t(a,b){if(!b.cache)b.cache={},b.createElem=a.createElement,b.createFrag=a.createDocumentFragment,b.frag=b.createFrag();
//		 a.createElement=function(c){return!e.shivMethods?b.createElem(c):p(c,a,b)};a.createDocumentFragment=Function("h,f","return function(){var n=f.cloneNode(),c=n.createElement;h.shivMethods&&("+m().join().replace(/[\w\-]+/g,function(a){b.createElem(a);b.frag.createElement(a);return\'c("\'+a+\'")\'})+");return n}")(e,b.frag)}function q(a){a||(a=f);var b=i(a);if(e.shivCSS&&!j&&!b.hasCSS){var c,d=a;c=d.createElement("p");d=d.getElementsByTagName("head")[0]||d.documentElement;c.innerHTML="x<style>article,aside,dialog,figcaption,figure,footer,header,hgroup,main,nav,section{display:block}mark{background:#FF0;color:#000}template{display:none}</style>";
//		 c=d.insertBefore(c.lastChild,d.firstChild);b.hasCSS=!!c}g||t(a,b);return a}var k=l.html5||{},s=/^<|^(?:button|map|select|textarea|object|iframe|option|optgroup)$/i,r=/^(?:a|b|code|div|fieldset|h1|h2|h3|h4|h5|h6|i|label|li|ol|p|q|span|strong|style|table|tbody|td|th|tr|ul)$/i,j,o="_html5shiv",h=0,n={},g;(function(){try{var a=f.createElement("a");a.innerHTML="<xyz></xyz>";j="hidden"in a;var b;if(!(b=1==a.childNodes.length)){f.createElement("a");var c=f.createDocumentFragment();b="undefined"==typeof c.cloneNode||
//		 "undefined"==typeof c.createDocumentFragment||"undefined"==typeof c.createElement}g=b}catch(d){g=j=!0}})();var e={elements:k.elements||"abbr article aside audio bdi canvas data datalist details dialog figcaption figure footer header hgroup main mark meter nav output progress section summary template time video",version:"3.7.0",shivCSS:!1!==k.shivCSS,supportsUnknownElements:g,shivMethods:!1!==k.shivMethods,type:"default",shivDocument:q,createElement:p,createDocumentFragment:function(a,b){a||(a=f);
//		 if(g)return a.createDocumentFragment();for(var b=b||i(a),c=b.frag.cloneNode(),d=0,e=m(),h=e.length;d<h;d++)c.createElement(e[d]);return c}};l.html5=e;q(f)})(this,document);
//		 </script>'
//		. "<![endif]-->\n";

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

	// Check for social media icons
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'socialIcons');
	$rc = ciniki_web_socialIcons($ciniki, $settings, 'header');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$social_icons = '';
	if( isset($rc['social']) && $rc['social'] != '' ) {
		$social_icons = $rc['social'];
	}

	//
	// Shopping Cart link
	//
	$shopping_cart = '';
	if( isset($settings['page-cart-active']) && $settings['page-cart-active'] == 'yes' 	
		&& isset($ciniki['session']['cart']['sapos_id']) && $ciniki['session']['cart']['sapos_id'] > 0 ) {
		$shopping_cart .= "<span><a rel='nofollow' href='" . $ciniki['request']['base_url'] . "/cart'>"
			. "Cart";
		if( isset($ciniki['session']['cart']['num_items']) && $ciniki['session']['cart']['num_items'] > 0 ) {
			$shopping_cart .= ' (' . $ciniki['session']['cart']['num_items'] . ')';
		}
		$shopping_cart .= "</a></span>";
	}

	//
	// Check if we are to display a sign in button
	//
	$signin_content = '';
	if( $ciniki['request']['business_id'] == $ciniki['config']['ciniki.core']['master_business_id'] 
		&& isset($ciniki['config']['ciniki.core']['manage.url']) && $ciniki['config']['ciniki.core']['manage.url'] != '' ) {
		$signin_content .= "<div class='signin'><div class='signin-wrapper'>";
		if( $social_icons != '' ) {
			$signin_content .= "<span class='social-icons hide-babybear'>$social_icons</span><span class='social-divider hide-babybear'>|</span>";
		}
		$signin_content .= "<span><a rel='nofollow' "
			. "href='" . $ciniki['config']['ciniki.core']['manage.url'] . "'>"
			. "Sign in</a></span>";
		$signin_content .= "</div></div>\n";
	} 
	// Display a cart and/or customer signin for regular businesses
	elseif( $ciniki['request']['business_id'] != $ciniki['config']['ciniki.core']['master_business_id']
		&& isset($settings['page-account-active']) && $settings['page-account-active'] == 'yes'
		&& ((isset($settings['page-downloads-customers']) && $settings['page-downloads-customers'] == 'yes')
			// Add check for members blog
			|| (isset($settings['page-subscriptions-public']) && $settings['page-subscriptions-public'] == 'yes')
			|| (isset($ciniki['business']['modules']['ciniki.blog']) && ($ciniki['business']['modules']['ciniki.blog']['flags']&0x0100) > 0) // Used if there are other pages that allow customer only content
		)) {
		$signin_content .= "<div class='signin'><div class='signin-wrapper'>";
		if( $social_icons != '' ) {
			$signin_content .= "<span class='social-icons hide-babybear'>$social_icons</span><span class='social-divider hide-babybear'>|</span>";
		}
		// Check for a cart
		if( $shopping_cart != '' ) {
			$signin_content .= $shopping_cart . " | ";
		}
		if( isset($ciniki['session']['customer']['id']) > 0 ) {
			$signin_content .= "<span><a rel='nofollow' href='" . $ciniki['request']['base_url'] . "/account'>Account</a></span>";
			$signin_content .= " | <span><a rel='nofollow' href='" . $ciniki['request']['base_url'] . "/account/logout'>Logout</a></span>";
		} else {
			$signin_content .= "<span><a rel='nofollow' href='" . $ciniki['request']['base_url'] . "/account'>Sign In</a></span>";
		}
		$signin_content .= "</div></div>\n";
	} elseif( $shopping_cart != '' ) {
		$signin_content .= "<div class='signin'><div class='signin-wrapper'>";
		if( $social_icons != '' ) {
			$signin_content .= "<span class='social-icons hide-babybear'>$social_icons</span><span class='social-divider hide-babybear'>|</span>";
		}
		$signin_content .= $shopping_cart;
		$signin_content .= "</div></div>\n";
	} elseif( $social_icons != '' ) {
		$signin_content .= "<div class='signin'><div class='signin-wrapper hide-babybear'><span class='social-icons'>$social_icons</span></div></div>";
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

//	if( isset($settings['site-header-image']) && $settings['site-header-image'] > 0 ) {
//		$content .= "<hgroup>\n";
//	} else {
//	}
	//
	// Decide if there is a header image to be displayed, or display an h1 title
	//
	if( !isset($settings['site-header-title']) || $settings['site-header-title'] == 'yes' ) {
		$content .= "<hgroup>\n";
//		if( $social_icons != '' ) {
//			$content .= "<div class='social-icons'>" . $social_icons . "</div>";
//		}
		if( isset($settings['site-header-image']) && $settings['site-header-image'] > 0 ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
			$rc = ciniki_web_getScaledImageURL($ciniki, $settings['site-header-image'], 'original', 0, '100', '85');
			$content .= "<div class='title-logo'><a href='" . $page_home_url . "' title='" . $ciniki['business']['details']['name'] 
				. "' rel='home'><img alt='Home' src='" . $rc['url'] . "' /></a></div>";
		}
		$content .= "<div class='title-block'><h1 id='site-title'>";
		$content .= "<span class='title'><a href='" . $page_home_url . "' title='" . $ciniki['business']['details']['name'] . "' rel='home'>" . $ciniki['business']['details']['name'] . "</a></span></h1>\n";
		if( isset($ciniki['business']['details']['tagline']) && $ciniki['business']['details']['tagline'] != '' ) {
			$content .= "<h2 id='site-description'>" . $ciniki['business']['details']['tagline'] . "</h2>\n";
		}
		$content .= "</div>";
//		if( $social_icons != '' ) {
//			$content .= "<span class='social-icons'>" . $social_icons . "</span>";
//		}
		$content .= "</hgroup>\n";
	} else {
		$content .= "<hgroup class='header-image'>\n";
//		if( $social_icons != '' ) {
//			$content .= "<div class='social-icons'>" . $social_icons . "</div>";
//		}
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
		$rc = ciniki_web_getScaledImageURL($ciniki, $settings['site-header-image'], 'original', 0, '125', '85');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$content .= "<span><a href='" . $page_home_url . "' title='" . $ciniki['business']['details']['name'] . "' rel='home'>"
			. "<img alt='Home' src='" . $rc['url'] . "' />"
			. "</a></span>\n";
//		if( $social_icons != '' ) {
//			$content .= "<span class='social-icons'>" . $social_icons . "</span>";
//		}
		$content .= "</hgroup>";
	}
//	if( isset($settings['site-header-image']) && $settings['site-header-image'] > 0 ) {
//		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
//		$rc = ciniki_web_getScaledImageURL($ciniki, $settings['site-header-image'], 'original', 0, '125', '85');
//		if( $rc['stat'] != 'ok' ) {
//			return $rc;
//		}
//		$content .= "<span><a href='" . $page_home_url . "' title='" . $ciniki['business']['details']['name'] . "' rel='home'>"
//			. "<img alt='Home' src='" . $rc['url'] . "' />"
//			. "</a></span>\n";
//	} else {
//		$content .= "<h1 id='site-title'>";
//		if( isset($settings['site-logo-display']) && $settings['site-logo-display'] == 'yes' 
//			&& isset($ciniki['business']['details']['logo_id']) && $ciniki['business']['details']['logo_id'] > 0 ) {
//			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
//			$rc = ciniki_web_getScaledImageURL($ciniki, $ciniki['business']['details']['logo_id'], 'original', 0, '100', '85');
//			$content .= "<span class='logo'><a href='" . $page_home_url . "' title='" . $ciniki['business']['details']['name'] 
//				. "' rel='home'><img alt='Home' src='" . $rc['url'] . "' /></a></span>";
//		}
//		$content .= "<span class='title'><a href='" . $page_home_url . "' title='" . $ciniki['business']['details']['name'] . "' rel='home'>" . $ciniki['business']['details']['name'] . "</a></span></h1>\n";
//	}

	//
	// Generate menu
	//
	$content .= "<nav id='access' role='navigation'>\n"
		. "<h3 class='assistive-text'>Main menu</h3>\n"
		. "";
	$content .= "<div id='main-menu-container'>"
		. "<ul id='main-menu' class='menu'>\n"
		. "<li class='menu-item'><a href='" . $page_home_url . "'>Home</a></li>"
		. "";
	$hide_menu_class = '';
	if( $ciniki['request']['page'] != 'home' && $ciniki['request']['page'] != 'masterindex' ) {
		$hide_menu_class = ' compact-hidden';
	}
	if( isset($settings['page-about-active']) && $settings['page-about-active'] == 'yes' ) {
		$content .= "<li class='menu-item$hide_menu_class'><a href='" . $ciniki['request']['base_url'] . "/about'>About</a></li>";
	}
	if( isset($settings['page-features-active']) && $settings['page-features-active'] == 'yes' ) {
		$content .= "<li class='menu-item$hide_menu_class'><a href='" . $ciniki['request']['base_url'] . "/features'>Features</a></li>";
	}
	if( isset($settings['page-products-active']) && $settings['page-products-active'] == 'yes' ) {
		$content .= "<li class='menu-item$hide_menu_class'><a href='" . $ciniki['request']['base_url'] . "/products'>Products</a></li>";
	}
	for($i=1;$i<6;$i++) {
		$pname = 'page-custom-' . sprintf("%03d", $i);
		if( isset($settings[$pname . '-active']) && $settings[$pname . '-active'] == 'yes' ) {
			$content .= "<li class='menu-item$hide_menu_class'><a href='" . $ciniki['request']['base_url'] . "/" . $settings[$pname . '-permalink'] . "'>" . $settings[$pname . '-name'] . "</a></li>";
		}
	}
	if( isset($settings['page-signup-active']) && $settings['page-signup-active'] == 'yes' 
		&& (!isset($settings['page-signup-menu']) || $settings['page-signup-menu'] == 'yes') 
		) {
		$content .= "<li class='menu-item$hide_menu_class'><a rel='nofollow' href='" . $ciniki['request']['base_url'] . "/signup'>Sign Up</a></li>";
	}
	if( isset($settings['page-exhibitions-exhibition']) && $settings['page-exhibitions-exhibition'] > 0
		&& isset($settings['page-exhibitions-exhibitors-active']) && $settings['page-exhibitions-exhibitors-active'] == 'yes' ) {
		$content .= "<li class='menu-item$hide_menu_class'><a href='" . $ciniki['request']['base_url'] . "/exhibitors'>Exhibitors</a></li>";
	}
	if( isset($settings['page-exhibitions-exhibition']) && $settings['page-exhibitions-exhibition'] > 0
		&& isset($settings['page-exhibitions-tourexhibitors-active']) && $settings['page-exhibitions-tourexhibitors-active'] == 'yes' ) {
		$content .= "<li class='menu-item$hide_menu_class'><a href='" . $ciniki['request']['base_url'] . "/tour'>";
		if( isset($settings['page-exhibitions-tourexhibitors-name']) && $settings['page-exhibitions-tourexhibitors-name'] != '' ) {
			$content .= $settings['page-exhibitions-tourexhibitors-name'];
		} else {
			$content .= "Tour";
		}
		$content .= "</a></li>";
	}
	if( isset($settings['page-artgalleryexhibitions-active']) && $settings['page-artgalleryexhibitions-active'] == 'yes' ) {
		$content .= "<li class='menu-item$hide_menu_class'><a href='" . $ciniki['request']['base_url'] . "/exhibitions'>Exhibitions</a></li>";
	}
	if( isset($settings['page-classes-active']) && $settings['page-classes-active'] == 'yes' ) {
		$content .= "<li class='menu-item$hide_menu_class'><a href='" . $ciniki['request']['base_url'] . "/classes'>Classes</a></li>";
	}
	if( isset($settings['page-members-active']) && $settings['page-members-active'] == 'yes' ) {
		$content .= "<li class='menu-item$hide_menu_class'><a href='" . $ciniki['request']['base_url'] . "/members'>Members</a></li>";
	}
	if( isset($settings['page-gallery-active']) && $settings['page-gallery-active'] == 'yes' ) {
		if( isset($settings['page-gallery-artcatalog-split']) && $settings['page-gallery-artcatalog-split'] == 'yes' ) {
			if( isset($settings['page-gallery-artcatalog-paintings']) 
				&& $settings['page-gallery-artcatalog-paintings'] == 'yes' ) {
				$content .= "<li class='menu-item$hide_menu_class'>"
					. "<a href='" . $ciniki['request']['base_url'] . "/gallery/paintings'>Paintings</a></li>";
			} 
			if( isset($settings['page-gallery-artcatalog-photographs']) 
				&& $settings['page-gallery-artcatalog-photographs'] == 'yes' ) {
				$content .= "<li class='menu-item$hide_menu_class'>"
					. "<a href='" . $ciniki['request']['base_url'] . "/gallery/photographs'>Photographs</a></li>";
			} 
			if( isset($settings['page-gallery-artcatalog-jewelry']) 
				&& $settings['page-gallery-artcatalog-jewelry'] == 'yes' ) {
				$content .= "<li class='menu-item$hide_menu_class'>"
					. "<a href='" . $ciniki['request']['base_url'] . "/gallery/jewelry'>Jewelry</a></li>";
			} 
			if( isset($settings['page-gallery-artcatalog-sculptures']) 
				&& $settings['page-gallery-artcatalog-sculptures'] == 'yes' ) {
				$content .= "<li class='menu-item$hide_menu_class'>"
					. "<a href='" . $ciniki['request']['base_url'] . "/gallery/sculptures'>Sculptures</a></li>";
			} 
			if( isset($settings['page-gallery-artcatalog-fibrearts']) 
				&& $settings['page-gallery-artcatalog-fibrearts'] == 'yes' ) {
				$content .= "<li class='menu-item$hide_menu_class'>"
					. "<a href='" . $ciniki['request']['base_url'] . "/gallery/fibrearts'>Fibre Arts</a></li>";
			} 
		} else {
			$content .= "<li class='menu-item$hide_menu_class'><a href='" . $ciniki['request']['base_url'] . "/gallery'>";
			if( isset($settings['page-gallery-name']) && $settings['page-gallery-name'] != '' ) {
				$content .= $settings['page-gallery-name'];
			} else {
				$content .= "Gallery";
			}
			$content .= "</a></li>";
		}
	}
	if( isset($settings['page-courses-active']) && $settings['page-courses-active'] == 'yes' ) {
		$content .= "<li class='menu-item$hide_menu_class'><a href='" . $ciniki['request']['base_url'] . "/courses'>";
		if( isset($settings['page-courses-name']) && $settings['page-courses-name'] != '' ) {
			$content .= $settings['page-courses-name'];
		} else {
			$content .= "Courses";
		}
		$content .= "</a></li>";
	}
	if( isset($settings['page-workshops-active']) && $settings['page-workshops-active'] == 'yes' ) {
		$content .= "<li class='menu-item$hide_menu_class'><a href='" . $ciniki['request']['base_url'] . "/workshops'>Workshops</a></li>";
	}
	if( isset($settings['page-recipes-active']) && $settings['page-recipes-active'] == 'yes' ) {
		$content .= "<li class='menu-item$hide_menu_class'><a href='" . $ciniki['request']['base_url'] . "/recipes'>Recipes</a></li>";
	}
	if( isset($settings['page-events-active']) && $settings['page-events-active'] == 'yes' ) {
		$content .= "<li class='menu-item$hide_menu_class'><a href='" . $ciniki['request']['base_url'] . "/events'>Events</a></li>";
	}
	if( isset($settings['page-directory-active']) && $settings['page-directory-active'] == 'yes' ) {
		$content .= "<li class='menu-item$hide_menu_class'><a href='" . $ciniki['request']['base_url'] . "/directory'>Directory</a></li>";
	}
	if( isset($settings['page-links-active']) && $settings['page-links-active'] == 'yes' ) {
		$content .= "<li class='menu-item$hide_menu_class'><a href='" . $ciniki['request']['base_url'] . "/links'>Links</a></li>";
	}
	if( isset($settings['page-newsletters-active']) && $settings['page-newsletters-active'] == 'yes' ) {
		$content .= "<li class='menu-item$hide_menu_class'><a href='" . $ciniki['request']['base_url'] . "/newsletters'>Newsletters</a></li>";
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

	//
	// Check if member news is enabled, and the member has logged in
	//
	if( isset($settings['page-memberblog-active']) && $settings['page-memberblog-active'] == 'yes' 
		&& isset($ciniki['business']['modules']['ciniki.blog'])
		&& ($ciniki['business']['modules']['ciniki.blog']['flags']&0x0100) > 0 
		&& isset($ciniki['session']['customer']['id']) && $ciniki['session']['customer']['id'] > 0
		) {
		$content .= "<li class='menu-item$hide_menu_class'><a href='" . $ciniki['request']['base_url'] . "/memberblog'>";
		if( isset($settings['page-memberblog-name']) && $settings['page-memberblog-name'] != '' ) {
			$content .= $settings['page-memberblog-name'];
		} else {
			$content .= "Member News";
		}
		$content .= "</a></li>";

	}

	if( isset($settings['page-blog-active']) && $settings['page-blog-active'] == 'yes' ) {
		$content .= "<li class='menu-item$hide_menu_class'><a href='" . $ciniki['request']['base_url'] . "/blog'>";
		if( isset($settings['page-blog-name']) && $settings['page-blog-name'] != '' ) {
			$content .= $settings['page-blog-name'];
		} else {
			$content .= "Blog";
		}
		$content .= "</a></li>";
	}
	if( isset($settings['page-faq-active']) && $settings['page-faq-active'] == 'yes' ) {
		$content .= "<li class='menu-item$hide_menu_class'><a href='" . $ciniki['request']['base_url'] . "/faq'>FAQ</a></li>";
	}
	if( (isset($settings['page-exhibitions-exhibition']) && $settings['page-exhibitions-exhibition'] > 0
		&& isset($settings['page-exhibitions-sponsors-active']) && $settings['page-exhibitions-sponsors-active'] == 'yes')
		|| isset($settings['page-sponsors-active']) && $settings['page-sponsors-active'] == 'yes' ) {
		$content .= "<li class='menu-item$hide_menu_class'><a href='" . $ciniki['request']['base_url'] . "/sponsors'>Sponsors</a></li>";
	}
//	if( isset($settings['page-sponsors-active']) && $settings['page-sponsors-active'] == 'yes' ) {
//		$content .= "<li class='menu-item$hide_menu_class'><a href='" . $ciniki['request']['base_url'] . "/sponsors'>Sponsors</a></li>";
//	}
	if( isset($settings['page-contact-active']) && $settings['page-contact-active'] == 'yes' ) {
		$content .= "<li class='menu-item$hide_menu_class'><a href='" . $ciniki['request']['base_url'] . "/contact'>Contact</a></li>";
	}
	$content .= "</ul>\n"
		. "</div>\n";

	//
	// Check if there is a submenu to display
	//
	if( is_array($submenu) && count($submenu) > 0 ) {
		$content .= "<h3 class='assistive-text'>Sub menu</h3>\n"
			. "";
		$content .= "<div id='sub-menu-container'>"
			. "<ul id='sub-menu' class='menu'>\n"
			. "";
		foreach($submenu as $sid => $item) {
			$content .= "<li class='menu-item'><a href='" . $item['url'] . "'>" . $item['name'] . "</a></li>";
		}
		$content .= "</ul>\n"
			. "</div>\n";
	}

	$content .= "</nav>\n";
	$content .= "</header>\n"
		. "";
	$content .= "<hr class='section-divider header-section-divider' />\n";

	return array('stat'=>'ok', 'content'=>$content);
}
?>
