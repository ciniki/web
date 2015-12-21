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
function ciniki_web_processBlockFormMessage($ciniki, $settings, $business_id, $block) {

	if( !isset($block['message']) ) {
		return array('stat'=>'ok', 'content'=>'');
	}
	$content = '';

    if( $block['level'] == 'error' ) {
        $content .= "<div class='form-result-message form-error-message'><div class='form-message-wrapper'><p>" . $block['message'] . "</p></div></div>";
    } elseif( $block['level'] == 'warning' ) {
        $content .= "<div class='form-result-message form-warning-message'><div class='form-message-wrapper'><p>" . $block['message'] . "</p></div></div>";
    } else {
        $content .= "<div class='form-result-message form-success-message'><div class='form-message-wrapper'><p>" . $block['message'] . "</p></div></div>";
    }

	return array('stat'=>'ok', 'content'=>$content);
}
?>
