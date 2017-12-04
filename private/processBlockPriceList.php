<?php
//
// Description
// -----------
// This function will prepare a list of audio files for the web page.
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_processBlockPriceList(&$ciniki, $settings, $tnid, $block) {

    $content = '';

    //
    // Display any audio sample
    //
    if( !isset($block['list']) ) {
        return array('stat'=>'ok', 'content'=>$content);
    }

    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'cartSetupPrices');

    //
    // Check for a title
    //
    if( isset($block['title']) && $block['title'] != '' ) {
        $content .= "<h2>" . $block['title'] . "</h2>";
    }
   
    $audiosamples = '';
    $list_content = '';
    $found_prices = 'no';
    foreach($block['list'] as $iid => $item) {
        if( isset($block['codes']) && $block['codes'] == 'yes' && isset($item['code']) && $item['code'] != '' 
            && !preg_match('/' . preg_replace('/\//', "\\\/", $item['code']) . '/', $item['title']) 
            ) {
            $item['title'] = $item['code'] . ' - ' . $item['title'];
        }
        $list_content .= "<div class='price-list-item'>";
        $list_content .= "<div class='item-name'>";
        $list_content .= "<span class='item-name'>" . $item['title'] . "</span>";
        $list_content .= "</div>";

        if( isset($item['prices']) ) {
            $rc = ciniki_web_cartSetupPrices($ciniki, $settings, $tnid, $item['prices']);
            if( $rc['stat'] == 'ok' && $rc['content'] != '' ) {
                $found_prices = 'yes';
                $list_content .= $rc['content'];
            }
        }
        $list_content .= "</div>";
    }
    if( $list_content != '' ) {
        $content .= "<div class='price-list" . ($found_prices=='yes'?' price-list-prices':'') . "'>" . $list_content . "</div>";
    }

    return array('stat'=>'ok', 'content'=>$content);
}
?>
