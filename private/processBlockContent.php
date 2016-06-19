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
function ciniki_web_processBlockContent(&$ciniki, $settings, $business_id, $block) {

    $content = '';

    //
    // Check for a title
    //

    //
    // Make sure there is content to edit
    //
    if( isset($block['content']) && $block['content'] != '' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
        $rc = ciniki_web_processContent($ciniki, $settings, $block['content'], ((isset($block['wide'])&&$block['wide']=='yes')?'wide':''));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( $rc['content'] != '' ) {
            if( isset($block['title']) && $block['title'] != '' ) {
                $content .= "<h2" . ((isset($block['wide'])&&$block['wide']=='yes')?" class='wide'":'') . ">" . $block['title'] . "</h2>";
            }
            $content .= $rc['content'];
        }
    }
    elseif( isset($block['html']) && $block['html'] != '' ) {
        if( isset($block['title']) && $block['title'] != '' ) {
            $content .= "<h2>" . $block['title'] . "</h2>";
        }
        $content .= $block['html'];
    }

    
    return array('stat'=>'ok', 'content'=>$content);
}
?>
