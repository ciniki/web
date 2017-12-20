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
function ciniki_web_processBlockDecisionButtons(&$ciniki, $settings, $tnid, $block) {

    $content = '';

    if( isset($block['title']) && $block['title'] != '' ) {
        $content .= "<h2>" . $block['title'] . "</h2>";
    }

    $content .= "<div class='decision-buttons'>";
    foreach($block['buttons'] as $button) {
        $content .= "<div class='decision-button'>"
            . "<a href='" . $button['url'] . "'>" . $button['label'] . "</a>"
            . "</div>"
            . "";
    }
    $content .= "</div>";
    
    return array('stat'=>'ok', 'content'=>$content);
}
?>
