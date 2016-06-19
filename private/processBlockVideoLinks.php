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
function ciniki_web_processBlockVideoLinks(&$ciniki, $settings, $business_id, $block) {

    $content = '';

    if( isset($block['title']) && $block['title'] != '' ) {
        $content .= "<h2>" . $block['title'] . "</h2>";
    }

    //
    // Make sure there is content to edit
    //
    if( $block['videos'] != '' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processURL');
        foreach($block['videos'] as $link) {
            //
            // Setup youtube or vimeo video embed
            //
            if( $link['link_type'] == '2000' ) {
                $url = preg_replace('/watch\?v=/', 'embed/', $link['url']);
                $url = preg_replace('/&amp;.*$/', '', $url);
                $content .= "<div class='embed-video'>"
                    . "<div class='embed-video-wrap'>"
                        . "<iframe src='" . $url . "' frameborder='0' allowfullscreen></iframe>"
                    . "</div>"
                    . "</div>";
            }
            //
            // Setup vimeo videos
            //
            elseif( $link['link_type'] == '2001' ) {
                $content .= "<div class='embed-video'>"
                    . "<div class='embed-video-wrap'>"
                        . "<iframe src='" . $link['url'] . "' frameborder='0' allowfullscreen></iframe>"
                    . "</div>"
                    . "</div>";
            }
        }
    }
    
    return array('stat'=>'ok', 'content'=>$content);
}
?>
