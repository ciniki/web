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
function ciniki_web_processBlockTagList($ciniki, $settings, $tnid, $block) {

    if( !isset($block['tags']) || count($block['tags']) == 0 ) {
        return array('stat'=>'ok', 'content'=>'');
    }

    $content = "<div class='tag-list-wrap" . ((isset($block['tag_type'])&&$block['tag_type']!='')?' tag-list-'.$block['tag_type']:'') . "'>";
    if( isset($block['title']) && $block['title'] != '' ) {
        $content .= "<h2>" . $block['title'] . "</h2>";
    }

    $tag_content = '';
    $size = 0;
    $content .= "<ul class='tag-list'>";
    foreach($block['tags'] as $tag) {
        if( !isset($tag['permalink']) || $tag['permalink'] == '' ) {
            $tag['permalink'] = rawurlencode($tag['name']);
        }
        $content .= "<li><span class='tag-list-tag'><a href='" . $block['base_url'] . '/' . $tag['permalink'] . "'>"    
            . $tag['name'] . "</a></span>"
            . "<span class='tag-list-count'> (" . $tag['num_tags'] . ")</span>"
            . "</li>";
    }

    $content .= "</ul>";
    $content .= "</div>";

    return array('stat'=>'ok', 'content'=>$content);
}
?>
