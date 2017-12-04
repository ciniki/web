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
function ciniki_web_processBlockMeta(&$ciniki, $settings, $tnid, $block) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processMeta');

    $content = '';

    //
    // Setup the meta information
    //
    if( isset($block['meta']['categories']) && count($block['meta']['categories']) > 0 ) {
        $meta_categories = '';
        foreach($block['meta']['categories'] as $category) {
            if( isset($category['name']) && $category['name'] != '' ) {
                if( $category['permalink'] != '' ) {
                    $meta_categories .= ($meta_categories!=''?', ':'')
                        . "<a href='" . (isset($block['meta']['category_base_url'])?$block['meta']['category_base_url']:$block['base_url']) . '/' . $category['permalink']
                        . "'>" . $category['name'] . "</a>";
                } else {
                    $meta_categories .= ($meta_categories!=''?', ':'') . $category['name'];
                }
            }
        }
        if( $meta_categories != '' ) {
            if( isset($block['meta']['divider']) && $block['meta']['divider'] != '' && $content != '' ) {
                $content .= $block['meta']['divider'];
            }
            $content .= "<div class='meta-categories'><span class='meta-label'>";
            if( count($block['meta']['categories']) > 1 ) {
                if( isset($block['meta']['categories_prefix']) && $block['meta']['categories_prefix'] != '' ) {
                    $content .= $block['meta']['categories_prefix'] . ' ';
                } else {
                    $content .= 'Filed under: ';
                }
            } else {
                if( isset($block['meta']['category_prefix']) && $block['meta']['category_prefix'] != '' ) {
                    $content .= $block['meta']['category_prefix'] . ' ';
                } else {
                    $content .= 'Filed under: ';
                }
            }
            $content .= "</span>";
            $content .= $meta_categories;
            $content .= "</div>";
        }
    }
    if( isset($block['meta']['tags']) && count($block['meta']['tags']) > 0 ) {
        $meta_tags = '';
        foreach($block['meta']['tags'] as $tag) {
            if( isset($tag['name']) && $tag['name'] != '' ) {
                if( $tag['permalink'] != '' ) {
                    $meta_tags .= ($meta_tags!=''?', ':'')
                        . "<a href='" . (isset($block['meta']['tag_base_url'])?$block['meta']['tag_base_url']:$block['base_url']) . '/' . $tag['permalink']
                        . "'>" . $tag['name'] . "</a>";
                } else {
                    $meta_tags .= ($meta_tags!=''?', ':'') . $tag['name'];
                }
            }
        }
        if( $meta_tags != '' ) {
            if( isset($block['meta']['divider']) && $block['meta']['divider'] != '' && $content != '' ) {
                $content .= $block['meta']['divider'];
            }
            $content .= "<div class='meta-tags'><span class='meta-label'>";
            if( count($block['meta']['tags']) > 1 ) {
                if( isset($block['meta']['tags_prefix']) && $block['meta']['tags_prefix'] != '' ) {
                    $content .= $block['meta']['tags_prefix'] . ' ';
                } else {
                    $content .= 'Keywords: ';
                }
            } else {
                if( isset($block['meta']['tag_prefix']) && $block['meta']['tag_prefix'] != '' ) {
                    $content .= $block['meta']['tag_prefix'] . ' ';
                } else {
                    $content .= 'Keywords: ';
                }
            }
            $content .= "</span>";
            $content .= $meta_tags;
            $content .= "</div>";
        }
    }

    if( $content != '' ) {
        $content = "<div class='entry-meta'>" . $content . "</div>";
    }


    return array('stat'=>'ok', 'content'=>$content);
}
?>
