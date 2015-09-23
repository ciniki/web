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
function ciniki_web_processBlockPrintOptions(&$ciniki, $settings, $business_id, $block) {

	$content = '';

	//
	// Make sure there is content to edit
	//
	if( isset($block['options']) ) {
		foreach($block['options'] as $option) {
			$content .= "<a target='_blank' href='" . $option['url'] . "'>" . $option['name'] . "</a><br/>";
		}
		if( $content != '' ) {
			$content = "<p>" . $content . "</p>";
		}
	}
	
	return array('stat'=>'ok', 'content'=>$content);
}
?>
