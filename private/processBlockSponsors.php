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
function ciniki_web_processBlockSponsors(&$ciniki, $settings, $business_id, $block) {

	$content = '';

	//
	// Make sure there is content to edit
	//
	if( $block['sponsors'] != '' ) {
		$content .= "<h2 style='clear:right;'>" 
			. (isset($block['sponsors']['title'])&&$block['sponsors']['title']!=''?$block['sponsors']['title']:'Sponsors') 
			. "</h2>";
		if( isset($block['sponsors']['content']) && $block['sponsors']['content'] != '' ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
			$rc = ciniki_web_processContent($ciniki, $settings, $block['sponsors']['content'], 'wide');	
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$content .= $rc['content'];
		}

		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processSponsorImages');
		$img_base_url = $ciniki['request']['base_url'] . '/sponsors';
		if( isset($block['sponsors']['sponsors']) ) {
			$rc = ciniki_web_processSponsorImages($ciniki, $settings, $img_base_url, $block['sponsors']['sponsors'], $block['sponsors']['size']);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$content .= "<div class='sponsor-gallery'>" . $rc['content'] . "</div>";
		}

		if( $content != '' ) {
			$content = "<p>" . $content . "</p>";
		}
	}
	
	return array('stat'=>'ok', 'content'=>$content);
}
?>
