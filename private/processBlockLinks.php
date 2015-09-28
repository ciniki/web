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
function ciniki_web_processBlockLinks(&$ciniki, $settings, $business_id, $block) {

	$content = '';

	//
	// Make sure there is content to edit
	//
	if( $block['links'] != '' ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processURL');
		foreach($block['links'] as $link) {
			$rc = ciniki_web_processURL($ciniki, $link['url']);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			if( $rc['url'] != '' ) {
				$content .= "<a target='_blank' href='" . $rc['url'] . "' title='" 
					. ($link['name']!=''?$link['name']:$rc['display']) . "'>" 
					. ($link['name']!=''?$link['name']:$rc['display'])
					. "</a>";
			} else {
				$content .= $link['name'];
			}
			if( isset($link['description']) && $link['description'] != '' ) {
				$content .= "<br/><span class='downloads-description'>" . $link['description'] . "</span>";
			}
			$content .= "<br/>";
		}

		if( $content != '' ) {
			$content = "<p>" . $content . "</p>";
		}
	}
	
	return array('stat'=>'ok', 'content'=>$content);
}
?>
