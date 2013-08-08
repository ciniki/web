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
function ciniki_web_generatePageCustom($ciniki, $settings) {

	//
	// Store the content created by the page
	// Make sure everything gets generated ok before returning the content
	//
	$content = '';
	$page_content = '';

	//
	// FIXME: Check if anything has changed, and if not load from cache
	//

	$page_content .= "<article class='page'>\n"
		. "<header class='entry-title'><h1 class='entry-title'>" . $settings['page-custom-001-name'] . "</h1></header>\n"
		. "";
	if( isset($settings['page-custom-001-image']) && $settings['page-custom-001-image'] != '' && $settings['page-custom-001-image'] > 0 ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
		$rc = ciniki_web_getScaledImageURL($ciniki, $settings['page-custom-001-image'], 'original', '500', 0);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$page_content .= "<aside><div class='image-wrap'>"
			. "<div class='image'><img title='' alt='" . $ciniki['business']['details']['name'] . "' src='" . $rc['url'] . "' /></div>";
		if( isset($settings['page-custom-001-image-caption']) && $settings['page-custom-001-image-caption'] != '' ) {
			$page_content .= "<div class='image-caption'>" . $settings['page-custom-001-image-caption'] . "</div>";
		}
		$page_content .= "</div></aside>";
	}

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash');
	$rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_web_content', 'business_id', $ciniki['request']['business_id'], 'ciniki.web', 'content', 'page-custom-001');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	if( isset($rc['content']['page-custom-001-content']) ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
		$rc = ciniki_web_processContent($ciniki, $rc['content']['page-custom-001-content']);	
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$page_content .= "<div class='entry-content'>"
			. $rc['content']
			. "</div>";
	}

	$page_content .= "</div>\n"
		. "</article>\n";

	//
	// Add the header
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageHeader');
	$rc = ciniki_web_generatePageHeader($ciniki, $settings, 'About', array());
	if( $rc['stat'] != 'ok' ) {	
		return $rc;
	}
	$content .= $rc['content'];

	$content .= "<div id='content'>\n";
	$content .= $page_content;
	$content .= "</div>\n";

	//
	// Add the footer
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageFooter');
	$rc = ciniki_web_generatePageFooter($ciniki, $settings);
	if( $rc['stat'] != 'ok' ) {	
		return $rc;
	}
	$content .= $rc['content'];

	return array('stat'=>'ok', 'content'=>$content);
}
?>
