<?php
//
// Description
// -----------
// This function will generate the exhibition sponsors page for the business.
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_generatePageSponsors($ciniki, $settings) {

	//
	// Store the content created by the page
	// Make sure everything gets generated ok before returning the content
	//
	$content = '';
	$page_content = '';

	//
	// FIXME: Check if anything has changed, and if not load from cache
	//

	$page_title = "Sponsors";
	if( isset($ciniki['business']['modules']['ciniki.exhibitions']) ) {
		$pkg = 'ciniki';
		$mod = 'exhibitions';
	} elseif( isset($ciniki['business']['modules']['ciniki.artclub']) ) {
		$pkg = 'ciniki';
		$mod = 'artclub';
	} else {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'946', 'msg'=>'No sponsor module enabled'));
	}

	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processURL');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processSponsors');

	ciniki_core_loadMethod($ciniki, $pkg, $mod, 'web', 'sponsorList');
	$sponsorList = $pkg . '_' . $mod . '_web_sponsorList';
	$rc = $sponsorList($ciniki, $settings, $ciniki['request']['business_id']);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( isset($rc['levels']) ) {
		$sponsors = $rc['levels'];
		foreach($sponsors as $lnum => $level) {
			$page_content .= "<article class='page'>\n"
				. "<header class='entry-title'><h1 class='entry-title'>";
			if( isset($level['level']['name']) ) {
				$page_content .= $level['level']['name'] . ' ';
			}
			$page_content .= "</h1></header>\n"
				. "<div class='entry-content'>\n"
				. "";
			$rc = ciniki_web_processSponsors($ciniki, $settings, $level['level']['number'], $level['level']['categories']);
			if( $rc['stat'] == 'ok' ) {
				$page_content .= $rc['content'];
			}
			$page_content .= "</div>\n"
				. "</article>\n"
				. "";
		}
	} else {
		$sponsors = $rc['categories'];
		$page_content .= "<article class='page'>\n"
			. "<header class='entry-title'><h1 class='entry-title'>Sponsors</h1></header>\n"
			. "<div class='entry-content'>\n"
			. "";
		$rc = ciniki_web_processSponsors($ciniki, $settings, 30, $sponsors);
		if( $rc['stat'] == 'ok' ) {
			$page_content .= $rc['content'];
		}
		$page_content .= "</div>\n"
			. "</article>\n"
			. "";
	}

	//
	// Generate the complete page
	//

	//
	// Add the header
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageHeader');
	$rc = ciniki_web_generatePageHeader($ciniki, $settings, 'Sponsors', array());
	if( $rc['stat'] != 'ok' ) {	
		return $rc;
	}
	$content .= $rc['content'];

	$content .= "<div id='content'>\n"
		. $page_content
		. "</div>"
		. "";

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
