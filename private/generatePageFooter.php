<?php
//
// Description
// -----------
// This function will generate the about page for the website
//
// Arguments
// ---------
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
	if( $ciniki['config']['web']['poweredby.url'] != '' && $ciniki['config']['core']['master_business_id'] != $ciniki['request']['business_id'] ) {
		$content .= "<span class='poweredby'>Powered by <a href='" . $ciniki['config']['web']['poweredby.url'] . "'>" . $ciniki['config']['web']['poweredby.name'] . "</a></span>"
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
