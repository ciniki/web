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
function ciniki_web_processBlockFiles(&$ciniki, $settings, $tnid, $block) {

    $content = '';

    if( isset($block['title']) && $block['title'] != '' ) {
        $content .= "<h2>" . $block['title'] . "</h2>";
    }

    //
    // Make sure there is content to edit
    //
    if( $block['files'] != '' ) {
        $file_content = '';
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processURL');
        foreach($block['files'] as $file) {
            $url = $block['base_url'] . '/' . $file['permalink'] . '.' . $file['extension'];
            if( $url != '' ) {
                $file_content .= "<a target='_blank' href='" . $url . "' title='" . $file['name'] . "'>" . $file['name'] . "</a>";
            } else {
                $file_content .= $file['name'];
            }
            if( isset($file['description']) && $file['description'] != '' ) {
                $file_content .= "<br/><span class='downloads-description'>" . $file['description'] . "</span>";
            }
            $file_content .= "<br/>";
        }

        if( $file_content != '' ) {
            $content .= "<p>" . $file_content . "</p>";
        }
    }
    
    return array('stat'=>'ok', 'content'=>$content);
}
?>
