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
function ciniki_web_processBlockMenu(&$ciniki, $settings, $business_id, $block) {

    $content = '';

    //
    // Make sure there is content to edit
    //
    if( isset($block['menu']) ) {
        foreach($block['menu'] as $menu_item) {
            $content .= "<li class='menu-item"
                . ((isset($menu_item['selected']) && $menu_item['selected'] == 'yes')?' menu-item-selected':'')
                . ' menu-item-' . preg_replace('/[^a-z0-9]/', '', strtolower($menu_item['name']))
                . "'>"
                . "<a href='" . $menu_item['url'] . "'>"
                . $menu_item['name']
                . "</a></li>";
        }         
    }

    if( $content != '' ) {
        $content = "<ul class='menu'>" . $content . "</ul>";
    }

    
    return array('stat'=>'ok', 'content'=>$content);
}
?>
