<?php
//
// Description
// -----------
// This function will generate the footer to be displayed at the bottom
// of every web page.
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_generatePageFooter($ciniki, $settings) {

	//
	// Store the content
	//
	$content = '';

	// Generate the footer content
	$content .= "<hr class='section-divider footer-section-divider' />\n";
	$content .= "<footer>"
		. "<span class='copyright'>All content &copy; Copyright " . date('Y') . " by " . $ciniki['business']['details']['name'] . ".</span>"
		. "<br/>";
	if( $ciniki['config']['ciniki.web']['poweredby.url'] != '' && $ciniki['config']['ciniki.core']['master_business_id'] != $ciniki['request']['business_id'] ) {
		$content .= "<span class='poweredby'>Powered by <a href='" . $ciniki['config']['ciniki.web']['poweredby.url'] . "'>" . $ciniki['config']['ciniki.web']['poweredby.name'] . "</a></span>"
			. "";
	}
	$content .= "</footer>"
		. "";

	// Close page-container
	$content .= "</div>\n";

	$content .= "</body>"
		. "</html>"
		. "";

	return array('stat'=>'ok', 'content'=>$content);
}
?>
