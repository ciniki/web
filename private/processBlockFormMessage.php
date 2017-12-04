<?php
//
// Description
// -----------
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_processBlockFormMessage($ciniki, $settings, $tnid, $block) {

    if( !isset($block['message']) ) {
        return array('stat'=>'ok', 'content'=>'');
    }
    $content = '';

    if( isset($block['title']) && $block['title'] != '' ) {
        $content .= "<h2" . ((isset($block['size'])&&$block['size']!='') ? " class='" . $block['size'] . "'" : '') . ">" . $block['title'] . "</h2>";
    }

    $content .= "<div class='form-message-content'>";
    if( $block['level'] == 'error' ) {
        $content .= "<div class='form-result-message form-error-message'><div class='form-message-wrapper'><p>" . $block['message'] . "</p></div></div>";
    } elseif( $block['level'] == 'warning' ) {
        $content .= "<div class='form-result-message form-warning-message'><div class='form-message-wrapper'><p>" . $block['message'] . "</p></div></div>";
    } else {
        $content .= "<div class='form-result-message form-success-message'><div class='form-message-wrapper'><p>" . $block['message'] . "</p></div></div>";
    }
    $content .= "</div>";

    return array('stat'=>'ok', 'content'=>$content);
}
?>
