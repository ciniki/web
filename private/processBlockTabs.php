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
function ciniki_web_processBlockTabs(&$ciniki, $settings, $business_id, $block) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processMeta');

    $content = '';

    if( isset($block['tabs']) && isset($block['tabs']) ) {
        $content .= "<div class='page-tabs-wrap'>";
        $content .= "<div class='page-tabs'>";
        foreach($block['tabs'] as $tab) {
            $selected = (isset($tab['selected']) && $tab['selected'] == 'yes' ? ' page-tabs-tab-selected' : '');
            $content .= "<span class='page-tabs-tab $selected'><a href='" . $tab['url'] . "' title='" . $tab['title'] . "'>" . $tab['title'] . "</a></span>";    
        }
        $content .= "</div>";
        $content .= "</div>";
    }

    return array('stat'=>'ok', 'content'=>$content);
}
?>
