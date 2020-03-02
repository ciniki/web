<?php
//
// Description
// -----------
// This function will process a list of tags along with sizes to return a word size cloud
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_processBlockButtonList($ciniki, $settings, $tnid, $block) {

    if( !isset($block['tags']) || count($block['tags']) == 0 ) {
        return array('stat'=>'ok', 'content'=>'');
    }

    $content = "<div class='tag-list-wrap" . ((isset($block['tag_type'])&&$block['tag_type']!='')?' tag-list-'.$block['tag_type']:'') . "'>";
    if( isset($block['title']) && $block['title'] != '' ) {
        $content .= "<h2>{$block['title']}</h2>";
    }

    $content = "<div class='largebutton-list'>";

    foreach($block['tags'] as $tag) {
        $content .= "<div class='button-list-wrap'>";
        $content .= "<div class='button-list-button'><a href='{$block['base_url']}/{$tag['permalink']}'>"    
            . $tag['name'] . "</a></div>";
        $content .= "</div> ";
    }

    $content .= "</div>";

    return array('stat'=>'ok', 'content'=>$content);
}
?>
