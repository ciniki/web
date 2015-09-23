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
function ciniki_web_processBlockGallery(&$ciniki, $settings, $business_id, $block) {

	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processURL');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');

	//
	// Start content and clear to both sides of the page
	//
	$content = '<br clear="both"/>';

	if( isset($block['title']) && $block['title'] != '' ) {
		$content .= "<h2 class='entry-title'>" . $block['title'] . "</h2>\n";
	}
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageGalleryThumbnails');
	$rc = ciniki_web_generatePageGalleryThumbnails($ciniki, $settings, $block['base_url'], $block['images'], 125);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$content .= "<div class='image-gallery'>" . $rc['content'] . "</div>";

	return array('stat'=>'ok', 'content'=>$content);
}
?>
