<?php
//
// Description
// -----------
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_processBlockShareButtons(&$ciniki, $settings, $business_id, $block) {

    //
    // Store the content created by the page
    //
    $content = '';

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
        'googleplus'=>'&#xe239;',
        'email'=>'&#xe224;',
        );
    //
    // FontAwesome settings
    //
    if( isset($settings['theme']['share-social-icons']) && $settings['theme']['share-social-icons'] == 'FontAwesome' ) {
        $social_icons['facebook'] = '&#xf09a;';
        $social_icons['twitter'] = '&#xf099;';
//      $social_icons['etsy'] = '&#xe026;';     // Missing etsy logo
        $social_icons['pinterest'] = '&#xf231;';
        $social_icons['tumblr'] = '&#xf173;';
        $social_icons['flickr'] = '&#xf16e;';
        $social_icons['youtube'] = '&#xf167;';
        $social_icons['vimeo'] = '&#xf27d;';
        $social_icons['instagram'] = '&#xf16d;';
        $social_icons['googleplus'] = '&#xf0d5;';
        $social_icons['email'] = '&#xf0e0;';
    }


    $url = $ciniki['response']['head']['og']['url'];

    $content .= "<div class='share-buttons-wrap'><span class='share-buttons'>"
        . "<span class='socialtext'>Share</span>";

    //
    // Setup email button
    //
    $content .= "<a href='mailto:?subject=" . rawurlencode($block['pagetitle']) . "&body=" . urlencode($ciniki['response']['head']['og']['url']) . "'>"
        . "<span title='Share via Email' class='socialsymbol social-email'>" . $social_icons['email'] . "</span>"
        . "</a>";

    //
    // Setup facebook button
    //
    $content .= "<a href='https://www.facebook.com/sharer.php?u=" . urlencode($ciniki['response']['head']['og']['url']) . "' onclick='window.open(this.href, \"_blank\", \"height=430,width=640\"); return false;' target='_blank'>"
        . "<span title='Share on Facebook' class='socialsymbol social-facebook'>" . $social_icons['facebook'] . "</span>"
        . "</a>";

    //
    // Setup twitter button
    //
    if( isset($ciniki['business']['social']['social-twitter-business-name']) 
        && $ciniki['business']['social']['social-twitter-business-name'] != '' ) {
        $msg = $ciniki['business']['social']['social-twitter-business-name'] . ' - ' . strip_tags((isset($block['pagetitle'])?$block['pagetitle']:''));
    } else {
        $msg = $ciniki['business']['details']['name'] . ' - ' . strip_tags((isset($block['pagetitle'])?$block['pagetitle']:''));
    }
    if( isset($ciniki['business']['social']['social-twitter-username']) 
        && $ciniki['business']['social']['social-twitter-username'] != '' ) {
        $msg .= ' @' . $ciniki['business']['social']['social-twitter-username'];
    }
    if( isset($block['tags']) ) {
        $tags = array_unique($block['tags']);
        foreach($tags as $tag) {
            if( $tag == '' ) { continue; }
            $tag = preg_replace('/ /', '', $tag);
            
            //  if( (strlen($surl) + 1 + strlen($msg) + 2 + strlen($tag)) < 140 ) {
            //  URLs only count as 22 characters in twitter, plus 1 for space.
            if( (23 + strlen($msg) + 2 + strlen($tag)) < 140 ) {
                $msg .= ' #' . $tag;
            }
        }
    }
    $content .= "<a href='https://twitter.com/share?url=" . urlencode($url) . "&text=" . urlencode($msg) . "' onclick='window.open(this.href, \"_blank\", \"height=430,width=640\"); return false;' target='_blank'>"
        . "<span title='Share on Twitter' class='socialsymbol social-twitter'>" . $social_icons['twitter'] . "</span>"
        . "</a>";

    //
    // Setup pinterest button
    //
    $content .= "<a href='http://www.pinterest.com/pin/create/button?url=" . urlencode($ciniki['response']['head']['og']['url']) . "&media=" . urlencode($ciniki['response']['head']['og']['image']) . "&description=" . urlencode($ciniki['business']['details']['name'] . (isset($block['pagetitle'])?' - ' . $block['pagetitle']:'')) . "' onclick='window.open(this.href, \"_blank\", \"height=430,width=640\"); return false;' target='_blank'>"
        . "<span title='Share on Pinterest' class='socialsymbol social-pinterest'>" . $social_icons['pinterest'] . "</span>"
        . "</a>";

    //
    // Setup google+ button
    //
    $content .= "<a href='https://plus.google.com/share?url=" . urlencode($ciniki['response']['head']['og']['url']) . "' onclick='window.open(this.href, \"_blank\", \"height=430,width=640\"); return false;' target='_blank'>"
        . "<span title='Share on Google+' class='socialsymbol social-googleplus'>" . $social_icons['googleplus'] . "</span>"
        . "</a>";

    //
    // Done
    //
    $content .= "</span></div>";
    
    return array('stat'=>'ok', 'content'=>$content);
}
?>
