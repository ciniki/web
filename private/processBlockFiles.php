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
function ciniki_web_processBlockFiles(&$ciniki, $settings, $business_id, $block) {

	$content = '';

	//
	// Make sure there is content to edit
	//
	if( $block['files'] != '' ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processURL');
		foreach($block['files'] as $file) {
			$url = $block['base_url'] . $file['permalink'] . '.' . $file['extension'];
			if( $url != '' ) {
				$content .= "<a target='_blank' href='" . $url . "' title='" . $file['name'] . "'>" . $file['name'] . "</a>";
			} else {
				$content .= $file['name'];
			}
			if( isset($file['description']) && $file['description'] != '' ) {
				$content .= "<br/><span class='downloads-description'>" . $file['description'] . "</span>";
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
