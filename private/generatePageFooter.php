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
	$content .= "<hr class='section-divider' />\n";
	$content .= "<footer>"
		. "All content &copy; Copyright " . date('Y') . " by " . $ciniki['business']['details']['name']. "."
		. "</footer>"
		. "";

	$content .= "</body>"
		. "</html>"
		. "";

	return array('stat'=>'ok', 'content'=>$content);
}
?>
