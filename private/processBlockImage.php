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
	$rc = ciniki_web_getScaledImageURL($ciniki, $block['image']['image_id'], 'original', 0, 600);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$img_url = $rc['url'];

	// Setup the og image
	if( isset($block['primary']) && $block['primary'] == 'yes' ) {
		$ciniki['response']['head']['og']['image'] = $rc['domain_url'];
	}

	//
	// Set the page to wide if possible
	//
	$ciniki['request']['page-container-class'] = 'page-container-wide';

	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generateGalleryJavascript');
	$rc = ciniki_web_generateGalleryJavascript($ciniki, isset($block['next'])?$block['next']:NULL, isset($block['prev'])?$block['prev']:NULL);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$ciniki['request']['inline_javascript'] = $rc['javascript'];

	$ciniki['request']['onresize'] = "gallery_resize_arrows();";
	$ciniki['request']['onload'] = "scrollto_header();";

	$content .= "<div id='gallery-image' class='gallery-image'>";
	$content .= "<div id='gallery-image-wrap' class='gallery-image-wrap'>";
	if( isset($block['prev']) ) {
		$content .= "<a id='gallery-image-prev' class='gallery-image-prev' href='" . $block['prev']['url'] . "'><div id='gallery-image-prev-img'></div></a>";
	}
	if( isset($block['next']) ) {
		$content .= "<a id='gallery-image-next' class='gallery-image-next' href='" . $block['next']['url'] . "'><div id='gallery-image-next-img'></div></a>";
	}
	$content .= "<img id='gallery-image-img' title='" . $block['image']['title'] . "' alt='" . $block['image']['title'] . "' src='" . $img_url . "' onload='javascript: gallery_resize_arrows();' />";
	$content .= "</div><br/>"
		. "<div id='gallery-image-details' class='gallery-image-details'>"
		. "<span class='image-title'>" . $block['image']['title'] . '</span>'
		. "<span class='image-details'></span>";
	if( $block['image']['description'] != '' ) {
		$content .= "<span class='image-description'>" . preg_replace('/\n/', '<br/>', $block['image']['description']) . "</span>";
	}
	$content .= "</div></div>";

	return array('stat'=>'ok', 'content'=>$content);
}
?>
