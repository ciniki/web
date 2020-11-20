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
function ciniki_web_processBlockAsideImage(&$ciniki, $settings, $tnid, $block) {

    if( !isset($block['image_id']) ) {
        return array('stat'=>'ok', 'content'=>'');
    }

    $quality = 60;
    $width = 500;

    if( isset($block['quality']) && $block['quality'] == 'high' ) {
        $quality = 90;
        $width = 1000;
    }

    //
    // Generate the image url
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
    $rc = ciniki_web_getScaledImageURL($ciniki, $block['image_id'], 'original', $width, 0, $quality);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $content = "<aside><div "
        . (isset($block['cssid']) && $block['cssid'] != '' ? " id='" . $block['cssid'] . "'" : '')
        . "class='image-wrap'><div class='image'>"
        . "<img title='' alt='" . (isset($block['title'])?$block['title']:'') . "' src='" . $rc['url'] . "' />"
        . "</div>";
    if( isset($block['caption']) && $block['caption'] != '' ) {
        $content .= "<div class='image-caption'>" . $block['caption'] . "</div>";
    }
    $content .= "</div></aside>";

    //
    // Check if this image should be used as the primary when linked from other sites eg: facebook
    //
    if( isset($block['primary']) && $block['primary'] == 'yes' ) {
        $ciniki['response']['head']['og']['image'] = $rc['domain_url'];
    }

    return array('stat'=>'ok', 'content'=>$content);
}
?>
