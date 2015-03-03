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
	global $start_time;

	//
	// Store the content
	//
	$content = '';

	// Generate the footer content
	$content .= "<hr class='section-divider footer-section-divider' />\n";
	$content .= "<footer>";

	// Check for social media icons
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'socialIcons');
	$rc = ciniki_web_socialIcons($ciniki, $settings, 'footer');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( isset($rc['social']) && $rc['social'] != '' ) {
		$content .= "<div class='social-icons'>" . $rc['social'] . "</div>";
	}
	
	$content .= "<span class='copyright'>All content &copy; Copyright " . date('Y') . " by " . ((isset($settings['site-footer-copyright-name']) && $settings['site-footer-copyright-name'] != '')?$settings['site-footer-copyright-name']:$ciniki['business']['details']['name']) . ".</span>"
		. "<br/>";
	if( isset($settings['site-footer-copyright-message']) && $settings['site-footer-copyright-message'] != '' ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
		$rc = ciniki_web_processContent($ciniki, $settings['site-footer-copyright-message'], 'copyright');	
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$content .= $rc['content'];
	}

	if( $ciniki['config']['ciniki.web']['poweredby.url'] != '' && $ciniki['config']['ciniki.core']['master_business_id'] != $ciniki['request']['business_id'] ) {
		$content .= "<span class='poweredby'>Powered by <a href='" . $ciniki['config']['ciniki.web']['poweredby.url'] . "'>" . $ciniki['config']['ciniki.web']['poweredby.name'] . "</a></span>"
			. "";
	}

	// If there was an error page generated, see if we should put the error code in the footer for debug purposes.
	// This keeps it out of the way, but easy to tell people what to look for.
	if( isset($ciniki['request']['error_codes_msg']) && $ciniki['request']['error_codes_msg'] != '' ) {
		$content .= "<br/><span class='poweredby'>" . $ciniki['request']['error_codes_msg'] . "</span>";
	}
	$content .= "<span class='x-stats' style='display:none;'>Execution: " . sprintf("%.4f", ((microtime(true)-$start_time)/60)) . "seconds</span>";
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
