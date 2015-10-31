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
function ciniki_web_processBlockMessage($ciniki, $settings, $business_id, $block) {

	if( !isset($block['content']) ) {
		return array('stat'=>'ok', 'content'=>'');
	}
	$content = '';

	if( isset($block['title']) && $block['title'] != '' ) {
		$content .= '<h2>' . $block['title'] . "</h2>";
	}

	$content .= '<p class="wide">' . $block['content'] . '</p>';

	return array('stat'=>'ok', 'content'=>$content);
}
?>
