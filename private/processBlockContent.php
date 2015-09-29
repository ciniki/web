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
function ciniki_web_processBlockContent(&$ciniki, $settings, $business_id, $block) {

	$content = '';

	//
	// Make sure there is content to edit
	//
	if( $block['content'] != '' ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
		$rc = ciniki_web_processContent($ciniki, $block['content'], ((isset($block['wide'])&&$block['wide']=='yes')?'wide':''));
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( $rc['content'] != '' ) {
			if( isset($block['title']) && $block['title'] != '' ) {
				$content .= "<h2" . ((isset($block['wide'])&&$block['wide']=='yes')?" class='wide'":'') . ">" . $block['title'] . "</h2>";
			}
			$content .= $rc['content'];
		}
	}
	
	return array('stat'=>'ok', 'content'=>$content);
}
?>
