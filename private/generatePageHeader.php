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
		. "<link rel='icon' href='/ciniki-mods/core/ui/themes/default/img/favicon.png' type='image/png' />\n"
		. "";

	if( isset($ciniki['business']['modules']['ciniki.web']['flags']) && ($ciniki['business']['modules']['ciniki.web']['flags']&0x0100) > 0 
        && isset($settings['site-privatetheme-id']) && $settings['site-privatetheme-id'] > 0 
        ) {
		//
		// Check if theme files in directory are up to date
		//
		if( !isset($settings['site-privatetheme-permalink'])
			|| $settings['site-privatetheme-permalink'] == ''
			|| !file_exists($ciniki['business']['web_cache_dir'] . '/' . $settings['site-privatetheme-permalink']) 
			|| !isset($settings['site-privatetheme-last-updated']) 
			|| filemtime($ciniki['business']['web_cache_dir'] . '/' . $settings['site-privatetheme-permalink']) < $settings['site-privatetheme-last-updated']
			) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'updatePrivateTheme');
			$rc = ciniki_web_updatePrivateTheme($ciniki, $ciniki['request']['business_id'], $settings, (isset($settings['site-privatetheme-id'])?$settings['site-privatetheme-id']:0));
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
		}

        //
        // Check for remote CSS files FIXME: Move into theme_settings
        //
        $strsql = "SELECT ciniki_web_theme_content.id, "
            . "ciniki_web_theme_content.content_type, "
            . "ciniki_web_theme_content.media, "
            . "ciniki_web_theme_content.content "
            . "FROM ciniki_web_theme_content "
            . "WHERE ciniki_web_theme_content.theme_id = '" . ciniki_core_dbQuote($ciniki, $settings['site-privatetheme-id']) . "' "
            . "AND ciniki_web_theme_content.business_id = '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['business_id']) . "' "
            . "AND ciniki_web_theme_content.content_type = 'csshref' "
            . "AND ciniki_web_theme_content.status = 10 "
            . "ORDER BY ciniki_web_theme_content.media, ciniki_web_theme_content.sequence "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
        $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.web', array(
            array('container'=>'links', 'fname'=>'id', 
                'fields'=>array('id', 'media', 'content')),
            ));
        if( $rc['stat'] == 'ok' && isset($rc['links']) ) {
            foreach($rc['links'] as $link_id => $link) {
                $content .= "<link type='text/css' rel='stylesheet' href='" . $link['content'] . "' media='" . $link['media'] . "' />\n";
            }
        }
	}
	//
	// Add required layout css files
	//
	if( file_exists($ciniki['request']['layout_dir'] . '/' . $settings['site-layout'] . '/global.css') ) {
		$content .= "<link rel='stylesheet' type='text/css' media='all' href='" . $ciniki['request']['layout_url'] 
			. '/' . $settings['site-layout'] . "/global.css' />\n";
	} else if( $settings['site-layout'] != 'private' && file_exists($ciniki['request']['layout_dir'] . '/default/global.css') ) {
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
				. "document.createElement('hgroup');\n"
				. "document.createElement('header');\n"
				. "document.createElement('nav');\n"
				. "document.createElement('section');\n"
				. "document.createElement('article');\n"
				. "document.createElement('aside');\n"
				. "document.createElement('footer');\n"
			. "</script>\n"
		  	. "<![endif]-->\n"
			. "";
	} else if( $settings['site-layout'] != 'private' && file_exists($ciniki['request']['layout_dir'] . '/default/layout.css') ) {
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
	} else if( $settings['site-layout'] != 'private' && file_exists($ciniki['request']['layout_dir'] . '/default/ie8.css') ) {
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
		if( file_exists($ciniki['request']['theme_dir'] . '/' . $settings['site-theme'] . '/extras.css') ) {
			$content .= "<link rel='stylesheet' type='text/css' media='all' href='" . $ciniki['request']['theme_url'] 
				. '/' . $settings['site-theme'] . "/extras.css' />\n";
		}
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
//		if( file_exists($ciniki['request']['theme_dir'] . '/' . $settings['site-theme'] . '/print.css') ) {
//			$content .= "<link rel='stylesheet' type='text/css' media='print' href='" . $ciniki['request']['theme_url'] . '/' . $settings['site-theme'] . "/print.css' />\n";
//		}
	} else if( $settings['site-theme'] != 'private' && file_exists($ciniki['request']['theme_dir'] . '/default/style.css') ) {
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
//		if( file_exists($ciniki['request']['theme_dir'] . '/default/print.css') ) {
//			$content .= "<link rel='stylesheet' type='text/css' media='print' href='" . $ciniki['request']['theme_url'] . "/default/print.css' />\n";
//		}
	}

	//
	// Check for private theme files
	//
	if( isset($ciniki['business']['modules']['ciniki.web']['flags']) && ($ciniki['business']['modules']['ciniki.web']['flags']&0x0100) > 0 
		&& isset($settings['site-privatetheme-permalink']) && $settings['site-privatetheme-permalink'] != '' 
		) {
		$theme_cache_dir = $ciniki['business']['web_cache_dir'] . '/' . $settings['site-privatetheme-permalink'];
		$theme_cache_url = $ciniki['business']['web_cache_url'] . '/' . $settings['site-privatetheme-permalink'];
		//
		// Include the private theme files
		//
		if( file_exists($theme_cache_dir . '/style.css') ) {
			$content .= "<link rel='stylesheet' type='text/css' media='all' href='$theme_cache_url/style.css' />\n";
		}
		if( file_exists($theme_cache_dir . '/print.css') ) {
			$content .= "<link rel='stylesheet' type='text/css' media='print' href='$theme_cache_url/print.css' />\n";
		}
		if( file_exists($theme_cache_dir . '/code.js') ) {
			$content .= "<script async type='text/javascript' src='$theme_cache_url/code.js'></script>\n";
		}
	}

	//
	// Check head links
	//
	if( isset($ciniki['response']['head']['links']) ) {
		foreach($ciniki['response']['head']['links'] as $link) {
			$content .= "<link rel='" . $link['rel'] . "'" . (isset($link['title'])?" title='" . $link['title'] . "'":'') . " href='" . $link['href'] . "'/>\n";
		}
	}

	//
	// Check for head scripts
	//
	if( isset($ciniki['response']['head']['scripts']) ) {
		foreach($ciniki['response']['head']['scripts'] as $script) {
			$content .= "<script src='" . $script['src'] . "' type='" . $script['type'] . "'></script>\n";
		}
	}
	
	//
	// Header to support mobile device resize
	//
	$content .= '<meta name="viewport" content="width=device-width, initial-scale=1.0">' . "\n";
	$content .= '<meta charset="UTF-8">' . "\n";

	if( isset($settings['site-google-site-verification']) 
		&& $settings['site-google-site-verification'] != '' 
		) {
		$content .= '<meta name="google-site-verification" content="' . $settings['site-google-site-verification'] . '"/>' . "\n";
	}
	if( isset($settings['site-pinterest-site-verification']) 
		&& $settings['site-pinterest-site-verification'] != '' 
		) {
		$content .= '<meta name="p:domain_verify" content="' . $settings['site-pinterest-site-verification'] . '"/>' . "\n";
	}

	if( isset($settings['site-meta-robots']) 
		&& $settings['site-meta-robots'] != '' 
		) {
		$content .= '<meta name="robots" content="' . $settings['site-meta-robots'] . '"/>' . "\n";
	}

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
	if( isset($ciniki['request']['ciniki_api']) && $ciniki['request']['ciniki_api'] == 'yes' ) {
		// FIXME: This might be moved to static file if no need to be loaded with page
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generateCinikiJavascript');
		$rc = ciniki_web_generateCinikiJavascript($ciniki);
		if( $rc['stat'] == 'ok' ) {
			$content .= $rc['javascript'];
		}
	}
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
		&& ( 
			(isset($ciniki['session']['customer']['id']) && $ciniki['session']['customer']['id'] > 0)
			|| (isset($ciniki['session']['cart']['num_items']) && $ciniki['session']['cart']['num_items'] > 0)
			)
		) {
		$shopping_cart .= "<span><a rel='nofollow' href='" . $ciniki['request']['ssl_domain_base_url'] . "/cart'>"
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
	if( !isset($settings['page-account-header-buttons']) 
		|| $settings['page-account-header-buttons'] == 'yes' 
		) {
		if( $ciniki['request']['business_id'] == $ciniki['config']['ciniki.core']['master_business_id'] 
			&& isset($ciniki['config']['ciniki.core']['manage.url']) 
			&& $ciniki['config']['ciniki.core']['manage.url'] != '' 
			) {
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
				|| (isset($settings['page-cart-active']) && $settings['page-cart-active'] == 'yes')
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
				$signin_content .= "<span><a rel='nofollow' href='" . $ciniki['request']['ssl_domain_base_url'] . "/account'>Account</a></span>";
				$signin_content .= " | <span><a rel='nofollow' href='" . $ciniki['request']['ssl_domain_base_url'] . "/account/logout'>Logout</a></span>";
			} else {
				$signin_content .= "<span><a rel='nofollow' href='" . $ciniki['request']['ssl_domain_base_url'] . "/account'>Sign In</a></span>";
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
	} 
	//
	// Logout button for membersonly page with password
	//
	elseif( isset($_SESSION['membersonly']['authenticated']) && $_SESSION['membersonly']['authenticated'] == 'yes' ) {
		$signin_content .= "<div class='signin'><div class='signin-wrapper'>";
		$signin_content .= "<span><a rel='nofollow' href='" . $ciniki['request']['domain_base_url'] . "/membersonly?logout'>Logout</a></span>";
		$signin_content .= "</div></div>\n";
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
	$content .= "<div class='header-wrapper'>\n";

	// Add signin button if any.
	$content .= $signin_content;

//	if( isset($settings['site-header-image']) && $settings['site-header-image'] > 0 ) {
//		$content .= "<hgroup>\n";
//	} else {
//	}

	//
	// Setup the header image
	//
	$site_header_image = '';
	if( isset($settings['site-header-image']) && $settings['site-header-image'] > 0 ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
		if( !isset($settings['site-header-image-size']) || $settings['site-header-image-size'] == 'medium' ) {
//			$page_home_image = ciniki_web_getScaledImageURL($ciniki, $settings['site-header-image'], 'original', 0, '125', '85');
			$page_home_image = ciniki_web_getScaledImageURL($ciniki, $settings['site-header-image'], 'original', 0, 125, 90);
		} elseif( $settings['site-header-image-size'] == 'small' ) {
//			$page_home_image = ciniki_web_getScaledImageURL($ciniki, $settings['site-header-image'], 'original', 0, '100', '75');
			$page_home_image = ciniki_web_getScaledImageURL($ciniki, $settings['site-header-image'], 'original', 0, 100, 90);
		} elseif( $settings['site-header-image-size'] == 'large' ) {
//			$page_home_image = ciniki_web_getScaledImageURL($ciniki, $settings['site-header-image'], 'original', 0, '150', '100');
			$page_home_image = ciniki_web_getScaledImageURL($ciniki, $settings['site-header-image'], 'original', 0, 150, 90);
		} elseif( $settings['site-header-image-size'] == 'xlarge' ) {
//			$page_home_image = ciniki_web_getScaledImageURL($ciniki, $settings['site-header-image'], 'original', 0, '200', '150');
			$page_home_image = ciniki_web_getScaledImageURL($ciniki, $settings['site-header-image'], 'original', 0, 200, 90);
		} elseif( $settings['site-header-image-size'] == 'xxlarge' ) {
//			$page_home_image = ciniki_web_getScaledImageURL($ciniki, $settings['site-header-image'], 'original', 0, '300', '225');
			$page_home_image = ciniki_web_getScaledImageURL($ciniki, $settings['site-header-image'], 'original', 0, 300, 90);
		} elseif( $settings['site-header-image-size'] == 'original' ) {
			$page_home_image = ciniki_web_getScaledImageURL($ciniki, $settings['site-header-image'], 'original', 0, 0, 90);
		}
	}

	//
	// Decide if there is a header image to be displayed, or display an h1 title
	//
    $hgroup = '';
    $class = 'hgroup-wrapper logo-nav-wrapper';
	if( !isset($settings['site-header-title']) || $settings['site-header-title'] == 'yes' ) {
		$hgroup .= "<hgroup>\n";
		if( isset($page_home_image) && $page_home_image['stat'] == 'ok' ) {
			$hgroup .= "<div class='title-logo'>"
				. "<a href='" . $page_home_url . "' title='" . $ciniki['business']['details']['name'] 
				. "' rel='home'><img alt='Home' src='" . $page_home_image['url'] . "' /></a>"
				. "</div>";
		}
		if( isset($ciniki['business']['details']['tagline']) && $ciniki['business']['details']['tagline'] != '' ) {
			$hgroup .= "<div class='title-block'>";
		} else {
			$hgroup .= "<div class='title-block no-tagline'>";
		}
		$hgroup .= "<h1 id='site-title'>";
		$hgroup .= "<span class='title'><a href='" . $page_home_url . "' title='" . $ciniki['business']['details']['name'] . "' rel='home'>" . $ciniki['business']['details']['name'] . "</a></span></h1>\n";
		if( isset($ciniki['business']['details']['tagline']) && $ciniki['business']['details']['tagline'] != '' ) {
			$hgroup .= "<h2 id='site-description'>" . $ciniki['business']['details']['tagline'] . "</h2>\n";
		}
		$hgroup .= "</div>";

        //
        // Check for header content, eg address
        //
        if( isset($settings['site-header-address']) && $settings['site-header-address'] != '' ) {
            $class = 'hgroup-wrapper logo-title-address-nav-wrapper';
            $hgroup .= "<div class='title-address-single-line'>"
                . preg_replace("/\n/", ", ", $settings['site-header-address'])
                . "</div>";
            $hgroup .= "<div class='title-address-multi-line'>"
                . preg_replace("/\n/", "<br/>", $settings['site-header-address'])
                . "</div>";
        }

		$hgroup .= "</hgroup>\n";
	} else {
		$hgroup .= "<hgroup class='header-image'>\n";
		$hgroup .= "<span><a href='" . $page_home_url . "' title='" . $ciniki['business']['details']['name'] . "' rel='home'>";
			if( isset($page_home_image) && $page_home_image['stat'] == 'ok' ) {
				$hgroup .= "<img alt='Home' src='" . $page_home_image['url'] . "' />";
			}
		$hgroup .= "</a></span>\n";
		$hgroup .= "</hgroup>";
	}
	$content .= "<div class='$class'>";
    $content .= $hgroup;
    $class = '';

	//
	// Get any pages if enables
	//
	if( isset($ciniki['business']['modules']['ciniki.web']) && ($ciniki['business']['modules']['ciniki.web']['flags']&0x40) == 0x40) {
		$strsql = "SELECT id, title, permalink, page_type, page_redirect_url, page_module "
			. "FROM ciniki_web_pages "
			. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['business_id']) . "' "
			. "AND parent_id = 0 "
			. "AND (flags&0x01) = 1 "	// Active pages
			. "ORDER BY sequence, title "
			. "";
		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
		$rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.web', array(
			array('container'=>'pages', 'fname'=>'id', 
				'fields'=>array('id', 'title', 'permalink', 'page_type', 'page_redirect_url', 'page_module')),
			));
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( isset($rc['pages']) ) {
			$pages = $rc['pages'];
			//
			// Get the subpages for menu
			//
			if( count($pages) > 0 ) {
				ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuoteIDs');
				$strsql = "SELECT id, parent_id, title, permalink, page_type, page_redirect_url, page_module "
					. "FROM ciniki_web_pages "
					. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['business_id']) . "' "
					. "AND parent_id IN (" . ciniki_core_dbQuoteIDs($ciniki, array_keys($pages)) . ") "
					. "AND (flags&0x01) = 1 "	// Active pages
					. "ORDER BY sequence, title "
					. "";
				$rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.web', array(
					array('container'=>'parents', 'fname'=>'parent_id', 
						'fields'=>array('parent_id')),
					array('container'=>'subpages', 'fname'=>'id', 
						'fields'=>array('id', 'title', 'permalink', 'page_type', 'page_redirect_url', 'page_module')),
					));
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				if( isset($rc['parents']) ) {
					foreach($rc['parents'] as $parent_id => $page) {
						if( isset($pages[$parent_id]) ) {
							$pages[$parent_id]['subpages'] = $page['subpages'];
						}
					}
				}
				//
				// Check for module submenus
				//
				foreach($pages as $page_id => $page) {
					if( $page['page_type'] == '30' && $page['page_module'] != '' ) {
						list($pkg, $mod) = explode('.', $page['page_module']);	
						$rc = ciniki_core_loadMethod($ciniki, $pkg, $mod, 'web', 'subMenuItems');
						if( $rc['stat'] == 'ok' ) {
							$fn = $rc['function_call'];
							$rc = $fn($ciniki, $settings, $ciniki['request']['business_id'], array());
							if( $rc['stat'] == 'ok' ) {
								$pages[$page_id]['subpages'] = $rc['submenu'];
							}
						}
					}
				}
			}
		} else {
			$pages = array();
		}
	}

	//
	// Generate menu
	//
	$content .= "<button type='button' id='main-menu-toggle' class='menu-toggle'><i class='fa fa-bars'></i></button>";
	$content .= "<nav id='access' role='navigation'>\n"
		. "<h3 class='assistive-text'>Main menu</h3>\n"
		. "";
	$content .= "<div id='main-menu-container'>"
		. "<ul id='main-menu' class='menu'>\n"
		. "";
	$hide_menu_class = '';
	if( $ciniki['request']['page'] != 'home' && $ciniki['request']['page'] != 'masterindex' ) {
		$hide_menu_class = ' compact-hidden';
	}

	//
	// Check if pages flags and pages menu flag is NOT set for web module
	//
	if( !isset($ciniki['business']['modules']['ciniki.web']['flags']) || ($ciniki['business']['modules']['ciniki.web']['flags']&0x0200) == 0 ) {
		$content .= "<li class='menu-item" . ($ciniki['request']['page']=='home'?' menu-item-selected':'') . "'><a href='" . $page_home_url . "'>Home</a></li>";
	//	print "<pre>" .  print_r($ciniki['request'], true) . "</pre>";
	//	print "<pre>" .  print_r($settings, true) . "</pre>";
		if( isset($settings['page-about-active']) && $settings['page-about-active'] == 'yes' ) {
			$content .= "<li class='menu-item$hide_menu_class" . ($ciniki['request']['page']=='about'?' menu-item-selected':'') . "'><a href='" . $ciniki['request']['base_url'] . "/about'>";
			if( isset($settings['page-about-title']) && $settings['page-about-title'] != '' ) {
				$content .= $settings['page-about-title'];
			} else {
				$content .= "About";
			}
			$content .= "</a></li>";
		} elseif( isset($settings['page-about-artiststatement-active']) && $settings['page-about-artiststatement-active'] == 'yes' ) {
			$content .= "<li class='menu-item$hide_menu_class" . ($ciniki['request']['page']=='about'?' menu-item-selected':'') . "'><a href='" . $ciniki['request']['base_url'] . "/about/artiststatement'>";
			$content .= "Artist Statement";
			$content .= "</a></li>";
		} elseif( isset($settings['page-about-cv-active']) && $settings['page-about-cv-active'] == 'yes' ) {
			$content .= "<li class='menu-item$hide_menu_class" . ($ciniki['request']['page']=='about'?' menu-item-selected':'') . "'><a href='" . $ciniki['request']['base_url'] . "/about/cv'>";
			$content .= "CV";
			$content .= "</a></li>";
		} elseif( isset($settings['page-about-awards-active']) && $settings['page-about-awards-active'] == 'yes' ) {
			$content .= "<li class='menu-item$hide_menu_class" . ($ciniki['request']['page']=='about'?' menu-item-selected':'') . "'><a href='" . $ciniki['request']['base_url'] . "/about/awards'>";
			$content .= "Awards";
			$content .= "</a></li>";
		}

		//
		// Check for other package pages
		//
		if( isset($ciniki['business']['pages']) && count($ciniki['business']['pages']) > 0 ) {
			foreach($ciniki['business']['pages'] as $permalink => $page) {
				if( $page['active'] == 'yes' ) {
					$content .= "<li class='menu-item$hide_menu_class" . ($ciniki['request']['page']==$page['permalink']?' menu-item-selected':'') . "'><a href='" . $ciniki['request']['base_url'] . "/" . $page['permalink'] . "'>" . $page['title'] . "</a>";
					$content .= "</li>";
				}
			}
		}

		if( isset($settings['page-features-active']) && $settings['page-features-active'] == 'yes' ) {
			$content .= "<li class='menu-item$hide_menu_class" . ($ciniki['request']['page']=='features'?' menu-item-selected':'') . "'><a href='" . $ciniki['request']['base_url'] . "/features'>Features</a></li>";
		}
		if( isset($settings['page-products-active']) && $settings['page-products-active'] == 'yes' ) {
			$content .= "<li class='menu-item$hide_menu_class" . ($ciniki['request']['page']=='products'?' menu-item-selected':'') . "'><a href='" . $ciniki['request']['base_url'] . "/products'>Products</a></li>";
		}
		for($i=1;$i<6;$i++) {
			$pname = 'page-custom-' . sprintf("%03d", $i);
			if( isset($settings[$pname . '-active']) && $settings[$pname . '-active'] == 'yes' ) {
				$content .= "<li class='menu-item$hide_menu_class" . ($ciniki['request']['page']==$settings[$pname . '-permalink']?' menu-item-selected':'') . "'><a href='" . $ciniki['request']['base_url'] . "/" . $settings[$pname . '-permalink'] . "'>" . $settings[$pname . '-name'] . "</a></li>";
			}
		}
		if( isset($settings['page-signup-active']) && $settings['page-signup-active'] == 'yes' 
			&& (!isset($settings['page-signup-menu']) || $settings['page-signup-menu'] == 'yes') 
			) {
			$content .= "<li class='menu-item$hide_menu_class" . ($ciniki['request']['page']=='signup'?' menu-item-selected':'') . "'><a rel='nofollow' href='" . $ciniki['request']['base_url'] . "/signup'>Sign Up</a></li>";
		}
		if( isset($settings['page-exhibitions-exhibition']) && $settings['page-exhibitions-exhibition'] > 0
			&& isset($settings['page-exhibitions-exhibitors-active']) && $settings['page-exhibitions-exhibitors-active'] == 'yes' ) {
			$content .= "<li class='menu-item$hide_menu_class" . ($ciniki['request']['page']=='exhibitors'?' menu-item-selected':'') . "'><a href='" . $ciniki['request']['base_url'] . "/exhibitors'>";
			if( isset($settings['page-exhibitions-exhibitors-name']) && $settings['page-exhibitions-exhibitors-name'] != '' ) {
				$content .= $settings['page-exhibitions-exhibitors-name']; 
			} else {
				$content .= "Exhibitors";
			}
			$content .= "</a></li>";
		}
		if( isset($settings['page-propertyrentals-active']) && $settings['page-propertyrentals-active'] == 'yes' ) {
			$content .= "<li class='menu-item$hide_menu_class" . ($ciniki['request']['page']=='properties'?' menu-item-selected':'') . "'><a href='" . $ciniki['request']['base_url'] . "/properties'>";
			if( isset($settings['page-propertyrentals-name']) && $settings['page-propertyrentals-name'] != '' ) {
				$content .= $settings['page-propertyrentals-name'];
			} else {
				$content .= "Properties";
			}
			$content .= "</a></li>";
		}
		if( isset($settings['page-exhibitions-exhibition']) && $settings['page-exhibitions-exhibition'] > 0
			&& isset($settings['page-exhibitions-tourexhibitors-active']) && $settings['page-exhibitions-tourexhibitors-active'] == 'yes' ) {
			$content .= "<li class='menu-item$hide_menu_class" . ($ciniki['request']['page']=='exhibitions'?' menu-item-selected':'') . "'><a href='" . $ciniki['request']['base_url'] . "/tour'>";
			if( isset($settings['page-exhibitions-tourexhibitors-name']) && $settings['page-exhibitions-tourexhibitors-name'] != '' ) {
				$content .= $settings['page-exhibitions-tourexhibitors-name'];
			} else {
				$content .= "Tour";
			}
			$content .= "</a></li>";
		}
		if( isset($settings['page-artgalleryexhibitions-active']) && $settings['page-artgalleryexhibitions-active'] == 'yes' ) {
			$content .= "<li class='menu-item$hide_menu_class" . ($ciniki['request']['page']=='exhibitions'?' menu-item-selected':'') . "'><a href='" . $ciniki['request']['base_url'] . "/exhibitions'>Exhibitions</a></li>";
		}
		if( isset($settings['page-members-active']) && $settings['page-members-active'] == 'yes' ) {
			if( isset($settings['page-members-name']) && $settings['page-members-name'] != '' ) {
				$content .= "<li class='menu-item$hide_menu_class" . ($ciniki['request']['page']=='members'?' menu-item-selected':'') . "'><a href='" . $ciniki['request']['base_url'] . "/members'>" . $settings['page-members-name'] . "</a></li>";
			} else {
				$content .= "<li class='menu-item$hide_menu_class" . ($ciniki['request']['page']=='members'?' menu-item-selected':'') . "'><a href='" . $ciniki['request']['base_url'] . "/members'>Members</a></li>";
			}
		}
		if( isset($settings['page-gallery-active']) && $settings['page-gallery-active'] == 'yes' ) {
			if( isset($settings['page-gallery-artcatalog-split']) && $settings['page-gallery-artcatalog-split'] == 'yes' ) {
				if( isset($settings['page-gallery-artcatalog-paintings']) 
					&& $settings['page-gallery-artcatalog-paintings'] == 'yes' ) {
					$content .= "<li class='menu-item$hide_menu_class" . ($ciniki['request']['page']=='gallery'?' menu-item-selected':'') . "'>"
						. "<a href='" . $ciniki['request']['base_url'] . "/gallery/paintings'>Paintings</a></li>";
				} 
				if( isset($settings['page-gallery-artcatalog-photographs']) 
					&& $settings['page-gallery-artcatalog-photographs'] == 'yes' ) {
					$content .= "<li class='menu-item$hide_menu_class" . ($ciniki['request']['page']=='gallery'?' menu-item-selected':'') . "'>"
						. "<a href='" . $ciniki['request']['base_url'] . "/gallery/photographs'>Photographs</a></li>";
				} 
				if( isset($settings['page-gallery-artcatalog-jewelry']) 
					&& $settings['page-gallery-artcatalog-jewelry'] == 'yes' ) {
					$content .= "<li class='menu-item$hide_menu_class" . ($ciniki['request']['page']=='gallery'?' menu-item-selected':'') . "'>"
						. "<a href='" . $ciniki['request']['base_url'] . "/gallery/jewelry'>Jewelry</a></li>";
				} 
				if( isset($settings['page-gallery-artcatalog-sculptures']) 
					&& $settings['page-gallery-artcatalog-sculptures'] == 'yes' ) {
					$content .= "<li class='menu-item$hide_menu_class" . ($ciniki['request']['page']=='gallery'?' menu-item-selected':'') . "'>"
						. "<a href='" . $ciniki['request']['base_url'] . "/gallery/sculptures'>Sculptures</a></li>";
				} 
				if( isset($settings['page-gallery-artcatalog-fibrearts']) 
					&& $settings['page-gallery-artcatalog-fibrearts'] == 'yes' ) {
					$content .= "<li class='menu-item$hide_menu_class" . ($ciniki['request']['page']=='gallery'?' menu-item-selected':'') . "'>"
						. "<a href='" . $ciniki['request']['base_url'] . "/gallery/fibrearts'>Fibre Arts</a></li>";
				} 
				if( isset($settings['page-gallery-artcatalog-pottery']) 
					&& $settings['page-gallery-artcatalog-pottery'] == 'yes' ) {
					$content .= "<li class='menu-item$hide_menu_class" . ($ciniki['request']['page']=='gallery'?' menu-item-selected':'') . "'>"
						. "<a href='" . $ciniki['request']['base_url'] . "/gallery/pottery'>Pottery</a></li>";
				} 
			} else {
				$content .= "<li class='menu-item$hide_menu_class" . ($ciniki['request']['page']=='gallery'?' menu-item-selected':'') . "'><a href='" . $ciniki['request']['base_url'] . "/gallery'>";
				if( isset($settings['page-gallery-name']) && $settings['page-gallery-name'] != '' ) {
					$content .= $settings['page-gallery-name'];
				} else {
					$content .= "Gallery";
				}
				$content .= "</a></li>";
			}
		}
		if( isset($settings['page-writings-active']) && $settings['page-writings-active'] == 'yes' ) {
			if( isset($settings['page-writings-catalog-split']) && $settings['page-writings-catalog-split'] == 'yes' ) {
				if( isset($settings['page-writings-catalog-books']) 
					&& $settings['page-writings-catalog-books'] == 'yes' ) {
					$content .= "<li class='menu-item$hide_menu_class" . ($ciniki['request']['page']=='writings'?' menu-item-selected':'') . "'>"
						. "<a href='" . $ciniki['request']['base_url'] . "/writings/books'>Books</a></li>";
				} 
				if( isset($settings['page-writings-catalog-shortstories']) 
					&& $settings['page-writings-catalog-shortstories'] == 'yes' ) {
					$content .= "<li class='menu-item$hide_menu_class" . ($ciniki['request']['page']=='writings'?' menu-item-selected':'') . "'>"
						. "<a href='" . $ciniki['request']['base_url'] . "/writings/shortstories'>Short Stories</a></li>";
				} 
				if( isset($settings['page-writings-catalog-articles']) 
					&& $settings['page-writings-catalog-articles'] == 'yes' ) {
					$content .= "<li class='menu-item$hide_menu_class" . ($ciniki['request']['page']=='writings'?' menu-item-selected':'') . "'>"
						. "<a href='" . $ciniki['request']['base_url'] . "/writings/shortstories'>Articles</a></li>";
				} 
			} else {
				$content .= "<li class='menu-item$hide_menu_class" . ($ciniki['request']['page']=='writings'?' menu-item-selected':'') . "'><a href='" . $ciniki['request']['base_url'] . "/writings'>";
				if( isset($settings['page-writings-name']) && $settings['page-writings-name'] != '' ) {
					$content .= $settings['page-writings-name'];
				} else {
					$content .= "Writings";
				}
				$content .= "</a></li>";
			}
		}
		if( isset($settings['page-fatt-active']) && $settings['page-fatt-active'] == 'yes' ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'fatt', 'web', 'menuItems');
			$rc = ciniki_fatt_web_menuItems($ciniki, $settings, $ciniki['request']['business_id'], array());
			if( $rc['stat'] == 'ok' ) {
				foreach($rc['menu'] as $item) {
					$content .= "<li class='menu-item$hide_menu_class" . ((isset($item['selected'])&&$item['selected']=='yes')?' menu-item-selected':'') . "'>"
						. "<a href='" . $ciniki['request']['base_url'] . '/' . $item['permalink'] . "'>"
						. $item['title']
						. "</a></li>";
				}
			}
		}
		if( isset($settings['page-courses-active']) && $settings['page-courses-active'] == 'yes' ) {
			$content .= "<li class='menu-item$hide_menu_class" . ($ciniki['request']['page']=='courses'?' menu-item-selected':'') . "'><a href='" . $ciniki['request']['base_url'] . "/courses'>";
			if( isset($settings['page-courses-name']) && $settings['page-courses-name'] != '' ) {
				$content .= $settings['page-courses-name'];
			} else {
				$content .= "Courses";
			}
			$content .= "</a></li>";
		}
		if( isset($settings['page-classes-active']) && $settings['page-classes-active'] == 'yes' ) {
			$content .= "<li class='menu-item$hide_menu_class" . ($ciniki['request']['page']=='classes'?' menu-item-selected':'') . "'><a href='" . $ciniki['request']['base_url'] . "/classes'>";
			if( isset($settings['page-classes-name']) && $settings['page-classes-name'] != '' ) {
				$content .= $settings['page-classes-name'];
			} else {
				$content .= "Classes";
			}
			$content .= "</a></li>";
		}
		if( isset($settings['page-workshops-active']) && $settings['page-workshops-active'] == 'yes' ) {
			$content .= "<li class='menu-item$hide_menu_class" . ($ciniki['request']['page']=='workshops'?' menu-item-selected':'') . "'><a href='" . $ciniki['request']['base_url'] . "/workshops'>Workshops</a></li>";
		}
		if( isset($settings['page-recipes-active']) && $settings['page-recipes-active'] == 'yes' ) {
			$content .= "<li class='menu-item$hide_menu_class" . ($ciniki['request']['page']=='events'?' menu-item-selected':'') . "'><a href='" . $ciniki['request']['base_url'] . "/recipes'>Recipes</a></li>";
		}
		if( isset($settings['page-filmschedule-active']) && $settings['page-filmschedule-active'] == 'yes' ) {
			if( isset($settings['page-filmschedule-title']) && $settings['page-filmschedule-title'] != '' ) {
				$content .= "<li class='menu-item$hide_menu_class" . ($ciniki['request']['page']=='filmschedule'?' menu-item-selected':'') . "'><a href='" . $ciniki['request']['base_url'] . "/schedule'>" . $settings['page-filmschedule-title'] . "</a></li>";
			} else {
				$content .= "<li class='menu-item$hide_menu_class" . ($ciniki['request']['page']=='filmschedule'?' menu-item-selected':'') . "'><a href='" . $ciniki['request']['base_url'] . "/schedule'>Schedule</a></li>";
			}
		}
		if( isset($settings['page-events-active']) && $settings['page-events-active'] == 'yes' ) {
			if( isset($settings['page-events-title']) && $settings['page-events-title'] != '' ) {
				$content .= "<li class='menu-item$hide_menu_class" . ($ciniki['request']['page']=='events'?' menu-item-selected':'') . "'><a href='" . $ciniki['request']['base_url'] . "/events'>" . $settings['page-events-title'] . "</a></li>";
			} else {
				$content .= "<li class='menu-item$hide_menu_class" . ($ciniki['request']['page']=='events'?' menu-item-selected':'') . "'><a href='" . $ciniki['request']['base_url'] . "/events'>Events</a></li>";
			}
		}
		if( isset($settings['page-directory-active']) && $settings['page-directory-active'] == 'yes' ) {
			if( isset($settings['page-directory-title']) && $settings['page-directory-title'] != '' ) {
				$content .= "<li class='menu-item$hide_menu_class" . ($ciniki['request']['page']=='directory'?' menu-item-selected':'') . "'><a href='" . $ciniki['request']['base_url'] . "/directory'>" . $settings['page-directory-title'] . "</a></li>";
			} else {
				$content .= "<li class='menu-item$hide_menu_class" . ($ciniki['request']['page']=='directory'?' menu-item-selected':'') . "'><a href='" . $ciniki['request']['base_url'] . "/directory'>Directory</a></li>";
			}
		}
		if( isset($settings['page-dealers-active']) && $settings['page-dealers-active'] == 'yes' ) {
			if( isset($settings['page-dealers-name']) && $settings['page-dealers-name'] != '' ) {
				$content .= "<li class='menu-item$hide_menu_class" . ($ciniki['request']['page']=='dealers'?' menu-item-selected':'') . "'><a href='" . $ciniki['request']['base_url'] . "/dealers'>" . $settings['page-dealers-name'] . "</a></li>";
			} else {
				$content .= "<li class='menu-item$hide_menu_class" . ($ciniki['request']['page']=='dealers'?' menu-item-selected':'') . "'><a href='" . $ciniki['request']['base_url'] . "/dealers'>Dealers</a></li>";
			}
		}
		if( isset($settings['page-distributors-active']) && $settings['page-distributors-active'] == 'yes' ) {
			if( isset($settings['page-distributors-name']) && $settings['page-distributors-name'] != '' ) {
				$content .= "<li class='menu-item$hide_menu_class" . ($ciniki['request']['page']=='distributors'?' menu-item-selected':'') . "'><a href='" . $ciniki['request']['base_url'] . "/distributors'>" . $settings['page-distributors-name'] . "</a></li>";
			} else {
				$content .= "<li class='menu-item$hide_menu_class" . ($ciniki['request']['page']=='distributors'?' menu-item-selected':'') . "'><a href='" . $ciniki['request']['base_url'] . "/distributors'>Distributors</a></li>";
			}
		}
		if( isset($settings['page-links-active']) && $settings['page-links-active'] == 'yes' ) {
			if( isset($settings['page-links-title']) && $settings['page-links-title'] != '' ) {
				$content .= "<li class='menu-item$hide_menu_class" . ($ciniki['request']['page']=='links'?' menu-item-selected':'') . "'><a href='" . $ciniki['request']['base_url'] . "/links'>" . $settings['page-links-title'] . "</a></li>";
			} else {
				$content .= "<li class='menu-item$hide_menu_class" . ($ciniki['request']['page']=='links'?' menu-item-selected':'') . "'><a href='" . $ciniki['request']['base_url'] . "/links'>Links</a></li>";
			}
		}
		if( isset($settings['page-newsletters-active']) && $settings['page-newsletters-active'] == 'yes' ) {
			$content .= "<li class='menu-item$hide_menu_class" . ($ciniki['request']['page']=='newsletters'?' menu-item-selected':'') . "'><a href='" . $ciniki['request']['base_url'] . "/newsletters'>Newsletters</a></li>";
		}
		if( isset($settings['page-downloads-active']) && $settings['page-downloads-active'] == 'yes' 
			&& ( 
				(isset($settings['page-downloads-public']) && $settings['page-downloads-public'] == 'yes')
				|| 
				(isset($settings['page-downloads-customers']) && $settings['page-downloads-customers'] == 'yes' 
					&& isset($ciniki['session']['customer']['id']) && $ciniki['session']['customer']['id'] > 0 )
				)
			) {
			$content .= "<li class='menu-item$hide_menu_class" . ($ciniki['request']['page']=='downloads'?' menu-item-selected':'') . "'><a href='" . $ciniki['request']['base_url'] . "/downloads'>";
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
			// Customer is logged in, or menu item should always be displayed
			&& (isset($ciniki['session']['customer']['id']) && $ciniki['session']['customer']['id'] > 0
				|| (isset($settings['page-memberblog-menu-active']) && $settings['page-memberblog-menu-active'] == 'yes')
				)
			) {
			$content .= "<li class='menu-item$hide_menu_class" . ($ciniki['request']['page']=='memberblog'?' menu-item-selected':'') . "'><a href='" . $ciniki['request']['base_url'] . "/memberblog'>";
			if( isset($settings['page-memberblog-name']) && $settings['page-memberblog-name'] != '' ) {
				$content .= $settings['page-memberblog-name'];
			} else {
				$content .= "Member News";
			}
			$content .= "</a></li>";
		}

	}

	//
	// Check for any pages
	//
	if( isset($ciniki['business']['modules']['ciniki.web']) 
		&& ($ciniki['business']['modules']['ciniki.web']['flags']&0x40) == 0x40 && isset($pages) ) {
		$i = 0;
	
		if( isset($settings['page-home-active']) && $settings['page-home-active'] == 'yes' 
			&& ($ciniki['business']['modules']['ciniki.web']['flags']&0x0200) > 0 
			) {
			$content .= "<li class='menu-item" . ($ciniki['request']['page']=='home'?' menu-item-selected':'') . "'><a href='" . $page_home_url . "'>Home</a></li>";
		}
	//	print "<pre>" .  print_r($ciniki['request'], true) . "</pre>";
	//	print "<pre>" .  print_r($settings, true) . "</pre>";

		foreach($pages as $page) {
			if( $page['page_type'] == '20' ) {
				//
				// Redirect to another url
				//
				$content .= "<li class='menu-item$hide_menu_class" . ($ciniki['request']['page']==$page['permalink']?' menu-item-select':'') . "'><a href='" . $page['page_redirect_url'] . "'>" . $page['title'] . "</a></li>";
			} 
			elseif( $page['page_type'] == '30' || ($page['title'] != '' && $page['permalink'] != '') ) {
				//
				// Module page
				//
				$content .= "<li id='menu-item-$i' class='menu-item$hide_menu_class" . ($ciniki['request']['page']==$page['permalink']?' menu-item-selected':'') 
					. ((isset($page['subpages'])&&count($page['subpages'])>0)?' menu-item-dropdown':'') 
					. "'><a href='" . $ciniki['request']['base_url'] . "/" . $page['permalink'] . "'>" . $page['title'] . "</a>";
				if( isset($page['subpages']) && count($page['subpages']) > 0 ) {
					$content .= "<ul id='menu-item-$i-sub' class='sub-menu sub-menu-hidden'>";
					foreach($page['subpages'] as $subpage ) {
						$content .= "<li class='sub-menu-item'>";
						if( isset($subpage['page_type']) && $subpage['page_type'] == '20' ) {
							$content .= "<a href='" . $subpage['page_redirect_url'] . "'>" . $subpage['title'] . "</a>";
						} else {
							$content .= "<a href='" . $ciniki['request']['base_url'] . "/" . $page['permalink'] . "/" . $subpage['permalink'] . "'>" . $subpage['title'] . "</a>";
						}
						$content .= "</li>";
					}
					$content .= "</ul>";
					$content .= "<span class='dropdown-button'><i class='navicon'></i></span>";
				}
				$content .= "</li>";
/*			} elseif( $page['title'] != '' && $page['permalink'] != '' ) {
				$content .= "<li id='menu-item-$i' class='menu-item$hide_menu_class" . ($ciniki['request']['page']==$page['permalink']?' menu-item-select':'') 
					. ((isset($page['subpages'])&&count($page['subpages'])>0)?' menu-item-dropdown':'') 
					. "'><a href='" . $ciniki['request']['base_url'] . "/" . $page['permalink'] . "'>" . $page['title'] . "</a>";
				if( isset($page['subpages']) && count($page['subpages']) > 0 ) {
					$content .= "<ul id='menu-item-$i-sub' class='sub-menu sub-menu-hidden'>";
					foreach($page['subpages'] as $subpage ) {
						$content .= "<li class='sub-menu-item" 
							// . ($ciniki['request']['page']==$page['permalink']?' menu-item-select':'') 
							. "'><a href='" . $ciniki['request']['base_url'] . "/" . $page['permalink'] . "/" . $subpage['permalink'] . "'>" . $subpage['title'] . "</a>";
						$content .= "</li>";
					}
					$content .= "</ul>";
					$content .= "<span class='dropdown-button'><i class='navicon'></i></span>";
				}
				$content .= "</li>"; */
			}
			$i++;
		}
	}

	if( !isset($ciniki['business']['modules']['ciniki.web']['flags']) || ($ciniki['business']['modules']['ciniki.web']['flags']&0x0200) == 0 ) {
		if( isset($settings['page-blog-active']) && $settings['page-blog-active'] == 'yes' ) {
			$content .= "<li class='menu-item$hide_menu_class" . ($ciniki['request']['page']=='blog'?' menu-item-selected':'') . "'><a href='" . $ciniki['request']['base_url'] . "/blog'>";
			if( isset($settings['page-blog-name']) && $settings['page-blog-name'] != '' ) {
				$content .= $settings['page-blog-name'];
			} else {
				$content .= "Blog";
			}
			$content .= "</a></li>";
		}
		//
		// Check if membersonly area is enabled, and the member has logged in
		//
		if( isset($settings['page-membersonly-active']) && $settings['page-membersonly-active'] == 'yes' 
			&& isset($ciniki['business']['modules']['ciniki.membersonly'])
			// Customer is logged in, or menu item should always be displayed
			&& (isset($ciniki['session']['customer']['id']) && $ciniki['session']['customer']['id'] > 0
				|| (isset($settings['page-membersonly-menu-active']) && $settings['page-membersonly-menu-active'] == 'yes')
				)
			) {
			$content .= "<li class='menu-item$hide_menu_class" . ($ciniki['request']['page']=='membersonly'?' menu-item-selected':'') . "'><a href='" . $ciniki['request']['base_url'] . "/membersonly'>";
			if( isset($settings['page-membersonly-name']) && $settings['page-membersonly-name'] != '' ) {
				$content .= $settings['page-membersonly-name'];
			} else {
				$content .= "Members Only";
			}
			$content .= "</a></li>";
		}

		if( isset($settings['page-tutorials-active']) && $settings['page-tutorials-active'] == 'yes' ) {
			$content .= "<li class='menu-item$hide_menu_class" . ($ciniki['request']['page']=='tutorials'?' menu-item-selected':'') . "'><a href='" . $ciniki['request']['base_url'] . "/tutorials'>Tutorials</a></li>";
		}
		if( isset($settings['page-faq-active']) && $settings['page-faq-active'] == 'yes' ) {
			$content .= "<li class='menu-item$hide_menu_class" . ($ciniki['request']['page']=='faq'?' menu-item-selected':'') . "'><a href='" . $ciniki['request']['base_url'] . "/faq'>FAQ</a></li>";
		}
		if( (isset($settings['page-exhibitions-exhibition']) && $settings['page-exhibitions-exhibition'] > 0
			&& isset($settings['page-exhibitions-sponsors-active']) && $settings['page-exhibitions-sponsors-active'] == 'yes')
			|| isset($settings['page-sponsors-active']) && $settings['page-sponsors-active'] == 'yes' ) {
			$content .= "<li class='menu-item$hide_menu_class" . ($ciniki['request']['page']=='sponsors'?' menu-item-selected':'') . "'><a href='" . $ciniki['request']['base_url'] . "/sponsors'>Sponsors</a></li>";
		}
	//	if( isset($settings['page-sponsors-active']) && $settings['page-sponsors-active'] == 'yes' ) {
	//		$content .= "<li class='menu-item$hide_menu_class" . ($ciniki['request']['page']=='sponsors'?' menu-item-selected':'') . "'><a href='" . $ciniki['request']['base_url'] . "/sponsors'>Sponsors</a></li>";
	//	}
		if( isset($settings['page-info-active']) && $settings['page-info-active'] == 'yes' ) {
			$content .= "<li class='menu-item$hide_menu_class" . ($ciniki['request']['page']=='info'?' menu-item-selected':'') . "'><a href='" . $ciniki['request']['base_url'] . "/info'>";
			if( isset($settings['page-info-title']) && $settings['page-info-title'] != '' ) {
				$content .= $settings['page-info-title'];
			} else {
				$content .= "Info";
			}
			$content .= "</a></li>";
		}
	}
	if( isset($settings['page-contact-active']) && $settings['page-contact-active'] == 'yes' ) {
		$content .= "<li class='menu-item$hide_menu_class" . ($ciniki['request']['page']=='contact'?' menu-item-selected':'') . "'><a href='" . $ciniki['request']['base_url'] . "/contact'>Contact</a></li>";
	}
	$content .= "</ul>\n"
		. "</div>\n";

	//
	// Check if there is a submenu to display
	//
	if( is_array($submenu) && count($submenu) > 0 ) {
		$content .= "<div class='menu-divider'></div>";
		$content .= "<h3 class='assistive-text'>Sub menu</h3>\n"
			. "";
		$content .= "<div id='sub-menu-container'>"
			. "<ul id='sub-menu' class='menu'>\n"
			. "";
		$cur_url = '';
		if( isset($ciniki['request']['uri_split'][0]) ) {
			$cur_url = $ciniki['request']['base_url'] . '/' . $ciniki['request']['page'] . '/' . $ciniki['request']['uri_split'][0];
		}
		foreach($submenu as $sid => $item) {
			$content .= "<li class='menu-item" . ($cur_url!=''&&strncmp($item['url'], $cur_url, strlen($cur_url))==0?' menu-item-selected':'') . "'><a href='" . $item['url'] . "'>" . $item['name'] . "</a></li>";
		}
		$content .= "</ul>\n"
			. "</div>\n";
	}

	$content .= "</nav>\n";
	$content .= "</div>\n";
	$content .= "</div>\n";
	$content .= "</header>\n"
		. "";
	$content .= "<hr class='section-divider header-section-divider' />\n";

	return array('stat'=>'ok', 'content'=>$content);
}
?>
