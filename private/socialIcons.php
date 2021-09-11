<?php
//
// Description
// -----------
// This function will put together the HTML to display the social icons for the tenant website.
//
// Arguments
// ---------
// ciniki:
// settings:
// location:        The location where the icons will go, header or footer.
//
// Returns
// -------
//
function ciniki_web_socialIcons($ciniki, $settings, $location) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processURL');
    // Check for social media icons
    $social = '';

    //
    // Default the social icons to the MonoSocial font
    //
    $social_icons = array(
        'facebook'=>'&#xe227;',
        'twitter'=>'&#xe286;',
        'etsy'=>'&#xe226;',
        'pinterest'=>'&#xe264;',
        'tumblr'=>'&#xe285;',
        'flickr'=>'&#xe229;',
        'youtube'=>'&#xe299;',
        'vimeo'=>'&#xe289;',
        'instagram'=>'&#xe300;',
        'linkedin'=>'&#xe252;',
        'email'=>'&#xe224;',
        );
//
// Settings for regular social font without circle
//
    // FontAwesome settings
//    if( (isset($settings['site-theme']) && $settings['site-theme'] == 'twentyone') 
//        || (isset($settings['theme'][$location . '-social-icons']) && $settings['theme'][$location . '-social-icons'] == 'FontAwesome')
//        ) {
    if( isset($settings['theme'][$location . '-social-icons']) && $settings['theme'][$location . '-social-icons'] == 'FontAwesome') {
        $social_icons['facebook'] = '&#xf09a;';
        $social_icons['twitter'] = '&#xf099;';
//      $social_icons['etsy'] = '&#xe026;';     // Missing etsy logo
        $social_icons['pinterest'] = '&#xf231;';
        $social_icons['tumblr'] = '&#xf173;';
        $social_icons['flickr'] = '&#xf16e;';
        $social_icons['youtube'] = '&#xf167;';
        $social_icons['vimeo'] = '&#xf27d;';
        $social_icons['instagram'] = '&#xf16d;';
        $social_icons['linkedin'] = '&#xf0e1;';
        $social_icons['email'] = '&#xf0e0;';
    }


    // Facebook
    if( (!isset($settings["site-social-facebook-$location-active"]) || $settings["site-social-facebook-$location-active"] == 'yes' )
        && isset($ciniki['tenant']['social']['social-facebook-url']) && $ciniki['tenant']['social']['social-facebook-url'] != ''
        ) {
        $rc = ciniki_web_processURL($ciniki, $ciniki['tenant']['social']['social-facebook-url']);
        $social .= "<a href='" . $rc['url'] . "' target='_blank' class='socialsymbol'>"
            . "<span title='Facebook' class='socialsymbol social-facebook'>" . $social_icons['facebook'] . "</span></a>";
    }
    // Twitter
    if( (!isset($settings["site-social-twitter-$location-active"]) || $settings["site-social-twitter-$location-active"] == 'yes' )
        && isset($ciniki['tenant']['social']['social-twitter-username']) && $ciniki['tenant']['social']['social-twitter-username'] != ''
        ) {
        $social .= "<a href='http://twitter.com/" . $ciniki['tenant']['social']['social-twitter-username'] . "' target='_blank' class='socialsymbol'>"
            . "<span title='Twitter' class='socialsymbol social-twitter'>" . $social_icons['twitter'] . "</span></a>";
    }
    // Linkedin
    if( (!isset($settings["site-social-linkedin-$location-active"]) || $settings["site-social-linkedin-$location-active"] == 'yes' )
        && isset($ciniki['tenant']['social']['social-linkedin-url']) && $ciniki['tenant']['social']['social-linkedin-url'] != ''
        ) {
        $social .= "<a href='" . $ciniki['tenant']['social']['social-linkedin-url'] . "' target='_blank' class='socialsymbol'>"
            . "<span title='Instagram' class='socialsymbol social-linkedin'>" . $social_icons['linkedin'] . "</span></a>";
    }
    // Etsy
    if( (!isset($settings["site-social-etsy-$location-active"]) || $settings["site-social-etsy-$location-active"] == 'yes' )
        && isset($ciniki['tenant']['social']['social-etsy-url']) && $ciniki['tenant']['social']['social-etsy-url'] != ''
        ) {
        $rc = ciniki_web_processURL($ciniki, $ciniki['tenant']['social']['social-etsy-url']);
        $social .= "<a href='" . $rc['url'] . "' target='_blank' class='socialsymbol'>"
            . "<span title='Etsy' class='socialsymbol social-etsy'>" . $social_icons['etsy'] . "</span></a>";
    }
    // Pinterest
    if( (!isset($settings["site-social-pinterest-$location-active"]) || $settings["site-social-pinterest-$location-active"] == 'yes' )
        && isset($ciniki['tenant']['social']['social-pinterest-username']) && $ciniki['tenant']['social']['social-pinterest-username'] != ''
        ) {
        $social .= "<a href='http://pinterest.com/" . $ciniki['tenant']['social']['social-pinterest-username'] . "' target='_blank' class='socialsymbol'>"
            . "<span title='Pinterest' class='socialsymbol social-pinterest'>" . $social_icons['pinterest'] . "</span></a>";
    }
    // Tumblr
    if( (!isset($settings["site-social-tumblr-$location-active"]) || $settings["site-social-tumblr-$location-active"] == 'yes' )
        && isset($ciniki['tenant']['social']['social-tumblr-username']) && $ciniki['tenant']['social']['social-tumblr-username'] != ''
        ) {
        $social .= "<a href='http://" . $ciniki['tenant']['social']['social-tumblr-username'] . ".tumblr.com/' target='_blank' class='socialsymbol'>"
            . "<span title='Tumblr' class='socialsymbol social-tumblr'>" . $social_icons['tumblr'] . "</span></a>";
    }
    // Flickr
    if( (!isset($settings["site-social-flickr-$location-active"]) || $settings["site-social-flickr-$location-active"] == 'yes' )
        && isset($ciniki['tenant']['social']['social-flickr-url']) && $ciniki['tenant']['social']['social-flickr-url'] != ''
        ) {
        $rc = ciniki_web_processURL($ciniki, $ciniki['tenant']['social']['social-flickr-url']);
        $social .= "<a href='" . $rc['url'] . "' target='_blank' class='socialsymbol'>"
            . "<span title='Flickr' class='socialsymbol social-flickr'>" . $social_icons['flickr'] . "</span></a>";
    }
    // YouTube
    if( (!isset($settings["site-social-youtube-$location-active"]) || $settings["site-social-youtube-$location-active"] == 'yes' )
        && isset($ciniki['tenant']['social']['social-youtube-url']) && $ciniki['tenant']['social']['social-youtube-url'] != ''
        ) {
        $social .= "<a href='" . $ciniki['tenant']['social']['social-youtube-url'] . "' target='_blank' class='socialsymbol'>"
            . "<span title='YouTube' class='socialsymbol social-youtube'>" . $social_icons['youtube'] . "</span></a>";
    }
    // Vimeo
    if( (!isset($settings["site-social-vimeo-$location-active"]) || $settings["site-social-vimeo-$location-active"] == 'yes' )
        && isset($ciniki['tenant']['social']['social-vimeo-url']) && $ciniki['tenant']['social']['social-vimeo-url'] != ''
        ) {
        $rc = ciniki_web_processURL($ciniki, $ciniki['tenant']['social']['social-vimeo-url']);
        $social .= "<a href='" . $rc['url'] . "' target='_blank' class='socialsymbol'>"
            . "<span title='Vimeo' class='socialsymbol social-vimeo'>" . $social_icons['vimeo'] . "</span></a>";
    }
    // Instagram
    if( (!isset($settings["site-social-instagram-$location-active"]) || $settings["site-social-instagram-$location-active"] == 'yes' )
        && isset($ciniki['tenant']['social']['social-instagram-username']) && $ciniki['tenant']['social']['social-instagram-username'] != ''
        ) {
        $social .= "<a href='http://instagram.com/" . $ciniki['tenant']['social']['social-instagram-username'] . "' target='_blank' class='socialsymbol'>"
            . "<span title='Instagram' class='socialsymbol social-instagram'>" . $social_icons['instagram'] . "</span></a>";
    }
    // Email
    if( (!isset($settings["site-social-email-$location-active"]) || $settings["site-social-email-$location-active"] == 'yes' )
        && isset($ciniki['tenant']['social']['social-email-username']) && $ciniki['tenant']['social']['social-email-username'] != ''
        ) {
//      $social .= "<a href='mailto:" . $ciniki['tenant']['social']['social-email-username'] . "' target='_blank' class='socialsymbol'>"
//  . "<span title='Email' class='socialsymbol social-email'>" . $social_icons['email'] . "</span></a>";
    }

    return array('stat'=>'ok', 'social'=>$social);
}
?>
