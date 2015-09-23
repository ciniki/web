<?php
//
// Description
// -----------
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_processBlockAsideImage($ciniki, $settings, $business_id, $block) {

	if( !isset($block['image_id']) ) {
		return array('stat'=>'ok', 'content'=>'');
	}

	//
	// Generate the image url
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
	$rc = ciniki_web_getScaledImageURL($ciniki, $block['image_id'], 'original', '500', 0);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$content = "<aside><div class='image-wrap'><div class='image'>"
		. "<img title='' alt='" . (isset($block['title'])?$block['title']:'') . "' src='" . $rc['url'] . "' />"
		. "</div></div></aside>";

	//
	// Check if this image should be used as the primary when linked from other sites eg: facebook
	//
	if( isset($block['primary']) && $block['primary'] == 'yes' ) {
		$ciniki['response']['head']['og']['image'] = $rc['domain_url'];
	}

	return array('stat'=>'ok', 'content'=>$content);
}
?>
