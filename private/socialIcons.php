<?php
//
// Description
// -----------
// This function will put together the HTML to display the social icons for the business website.
//
// Arguments
// ---------
// ciniki:
// settings:
// location:		The location where the icons will go, header or footer.
//
// Returns
// -------
//
function ciniki_web_socialIcons($ciniki, $settings, $location) {

	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processURL');
	// Check for social media icons
	$social = '';
	// Facebook
	if( (!isset($settings["site-social-facebook-$location-active"]) || $settings["site-social-facebook-$location-active"] == 'yes' )
		&& isset($ciniki['business']['social']['social-facebook-url']) && $ciniki['business']['social']['social-facebook-url'] != ''
		) {
		$rc = ciniki_web_processURL($ciniki, $ciniki['business']['social']['social-facebook-url']);
		$social .= "<a href='" . $rc['url'] . "' target='_blank' class='socialsymbol'>"
			. "<span title='Facebook' class='socialsymbol social-facebook'>&#xe227;</span></a>";
	}
	// Twitter
	if( (!isset($settings["site-social-twitter-$location-active"]) || $settings["site-social-twitter-$location-active"] == 'yes' )
		&& isset($ciniki['business']['social']['social-twitter-username']) && $ciniki['business']['social']['social-twitter-username'] != ''
		) {
		$social .= "<a href='http://twitter.com/" . $ciniki['business']['social']['social-twitter-username'] . "' target='_blank' class='socialsymbol'>"
			. "<span title='Twitter' class='socialsymbol social-twitter'>&#xe286;</span></a>";
	}
	// Etsy
	if( (!isset($settings["site-social-etsy-$location-active"]) || $settings["site-social-etsy-$location-active"] == 'yes' )
		&& isset($ciniki['business']['social']['social-etsy-url']) && $ciniki['business']['social']['social-etsy-url'] != ''
		) {
		$rc = ciniki_web_processURL($ciniki, $ciniki['business']['social']['social-etsy-url']);
		$social .= "<a href='" . $rc['url'] . "' target='_blank' class='socialsymbol'>"
			. "<span title='Etsy' class='socialsymbol social-etsy'>&#xe226;</span></a>";
	}
	// Pinterest
	if( (!isset($settings["site-social-pinterest-$location-active"]) || $settings["site-social-pinterest-$location-active"] == 'yes' )
		&& isset($ciniki['business']['social']['social-pinterest-username']) && $ciniki['business']['social']['social-pinterest-username'] != ''
		) {
		$social .= "<a href='http://pinterest.com/" . $ciniki['business']['social']['social-pinterest-username'] . "' target='_blank' class='socialsymbol'>"
			. "<span title='Pinterest' class='socialsymbol social-pinterest'>&#xe264;</span></a>";
	}
	// Tumblr
	if( (!isset($settings["site-social-tumblr-$location-active"]) || $settings["site-social-tumblr-$location-active"] == 'yes' )
		&& isset($ciniki['business']['social']['social-tumblr-username']) && $ciniki['business']['social']['social-tumblr-username'] != ''
		) {
		$social .= "<a href='http://" . $ciniki['business']['social']['social-tumblr-username'] . ".tumblr.com/' target='_blank' class='socialsymbol'>"
			. "<span title='Tumblr' class='socialsymbol social-tumblr'>&#xe285;</span></a>";
	}
	// Flickr
	if( (!isset($settings["site-social-flickr-$location-active"]) || $settings["site-social-flickr-$location-active"] == 'yes' )
		&& isset($ciniki['business']['social']['social-flickr-url']) && $ciniki['business']['social']['social-flickr-url'] != ''
		) {
		$rc = ciniki_web_processURL($ciniki, $ciniki['business']['social']['social-flickr-url']);
		$social .= "<a href='" . $rc['url'] . "' target='_blank' class='socialsymbol'>"
			. "<span title='Flickr' class='socialsymbol social-flickr'>&#xe229;</span></a>";
	}
	// YouTube
	if( (!isset($settings["site-social-youtube-$location-active"]) || $settings["site-social-youtube-$location-active"] == 'yes' )
		&& isset($ciniki['business']['social']['social-youtube-username']) && $ciniki['business']['social']['social-youtube-username'] != ''
		) {
		$social .= "<a href='http://youtube.com/user/" . $ciniki['business']['social']['social-youtube-username'] . "' target='_blank' class='socialsymbol'>"
			. "<span title='YouTube' class='socialsymbol social-youtube'>&#xe299;</span></a>";
	}
	// Vimeo
	if( (!isset($settings["site-social-vimeo-$location-active"]) || $settings["site-social-vimeo-$location-active"] == 'yes' )
		&& isset($ciniki['business']['social']['social-vimeo-url']) && $ciniki['business']['social']['social-vimeo-url'] != ''
		) {
		$rc = ciniki_web_processURL($ciniki, $ciniki['business']['social']['social-vimeo-url']);
		$social .= "<a href='" . $rc['url'] . "' target='_blank' class='socialsymbol'>"
			. "<span title='Vimeo' class='socialsymbol social-vimeo'>&#xe289;</span></a>";
	}
	// Instagram
	if( (!isset($settings["site-social-instagram-$location-active"]) || $settings["site-social-instagram-$location-active"] == 'yes' )
		&& isset($ciniki['business']['social']['social-instagram-username']) && $ciniki['business']['social']['social-instagram-username'] != ''
		) {
		$social .= "<a href='http://instagram.com/" . $ciniki['business']['social']['social-instagram-username'] . "' target='_blank' class='socialsymbol'>"
			. "<span title='Instagram' class='socialsymbol social-instagram'>&#xe300;</span></a>";
	}
	// Email
	if( (!isset($settings["site-social-email-$location-active"]) || $settings["site-social-email-$location-active"] == 'yes' )
		&& isset($ciniki['business']['social']['social-email-username']) && $ciniki['business']['social']['social-email-username'] != ''
		) {
//		$social .= "<a href='mailto:" . $ciniki['business']['social']['social-email-username'] . "' target='_blank' class='socialsymbol'>"
//	. "<span title='Email' class='socialsymbol social-email'>&#xe224;</span></a>";
	}

	return array('stat'=>'ok', 'social'=>$social);
}
?>
