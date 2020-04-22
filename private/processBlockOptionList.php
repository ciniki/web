<?php
//
// Description
// -----------
// This block was developed to display a list of times available for online order pickups.
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_processBlockOptionList(&$ciniki, $settings, $tnid, $block) {

    $content = '';

    if( isset($block['title']) && $block['title'] != '' ) {
        $content .= "<h2" . ((isset($block['wide'])&&$block['wide']=='yes')?" class='wide'":'') . ">" . $block['title'] . "</h2>";
    }

    $content .= "<div class='option-list'>";
    foreach($block['options'] as $option) {
        $content .= "<div class='option-list-item'>"
            . "<span class='option-list-label'>" . $option['label'] 
            . "</span>";
        if( isset($option['available']) && $option['available'] == 'yes' ) {
            $content .= "<span class='option-list-button'>" 
                . "<a href='" . $option['url'] . "'>" . $block['button-label'] . "</a>"
                . "</span>";
        } elseif( isset($option['message']) ) {
            $content .= "<span class='option-list-message'>" . $option['message'] . "</span>";
        }
        $content .= "</div>";
    }
    $content .= "</div>";
    
    return array('stat'=>'ok', 'content'=>$content);
}
?>
