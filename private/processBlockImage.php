<?php
//
// Description
// -----------
// This function will prepare a single image page for the website.
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_processBlockImage(&$ciniki, $settings, $business_id, $block) {

	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processURL');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');

	$content = '';

	//
	// Load the image
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
	$rc = ciniki_web_getScaledImageURL($ciniki, $block['image_id'], 'original', 0, 600);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$img_url = $rc['url'];

	// Setup the og image
	if( isset($block['primary']) && $block['primary'] == 'yes' ) {
		$ciniki['response']['head']['og']['image'] = $rc['domain_url'];
	}

	$content .= "<div id='image-wrap' class='image-wrap'>";
	$content .= "<div id='image' class='image'>";
	$img_content = "<img title='" . $block['title'] . "' alt='" . $block['title'] . "' src='" . $img_url . "' />";
    if( isset($block['base_url']) && isset($block['permalink']) && $block['permalink'] != '' ) {
        $content .= "<a href='" . $block['base_url'] . '/' . $block['permalink'] . "'>" . $img_content . "</a>";
    } else {
        $content .= $img_content;
    }
	$content .= "</div>";
	$content .= "</div>";

	return array('stat'=>'ok', 'content'=>$content);
}
?>
