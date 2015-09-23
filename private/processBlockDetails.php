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
function ciniki_web_processBlockDetails(&$ciniki, $settings, $business_id, $block) {

	$content = '';

	//
	// Make sure there is content to edit
	//
	if( $block['details'] != '' ) {
		foreach($block['details'] as $detail) {
			if( $detail['name'] != '' ) {
				$content .= "<b>" . $detail['name'] . "</b>: " . (isset($detail['value'])?$detail['value']:'') . "<br/>";
			}
		}
		if( $content != '' ) {
			$content = "<p>" . $content . "</p>";
		}
	}
	
	return array('stat'=>'ok', 'content'=>$content);
}
?>
