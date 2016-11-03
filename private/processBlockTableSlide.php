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
function ciniki_web_processBlockTableSlide(&$ciniki, $settings, $business_id, $block) {

    $content = '';

    if( isset($block['direction']) && $block['direction'] == 'horizontal' ) {
        if( isset($block['title']) && $block['title'] != '' ) {
            $content .= "<h2>" . $block['title'] . "</h2>";
        }

        $content .= "<div class='table-slide-horizontal'>";
        $content .= "<div class='table-slide-rowlabels'>";
        foreach($block['rows'] as $row) {
            $content .= "<div class='table-slide-rowlabel'>" 
                . ((isset($row['label']) && $row['label'] != '' ) ? $row['label'] : '&nbsp;') 
                . "</div>";
        }
        $content .= "</div>";

        $content .= "<div class='table-slide-data'>";
        foreach($block['data'] as $col_id => $col) {
            $content .= "<div class='table-slide-column'>";
            foreach($block['rows'] as $row) {
                $content .= "<div class='table-slide-coldata" 
                    . ((isset($row['class']) && $row['class'] != '') ? " table-slide-" . $row['class'] : '&nbsp;')
                    . "'>"
                    . (isset($col[$row['field']]) ? $col[$row['field']] : '&nbsp;')
                    . "</div>";
            }
            $content .= "</div>";
        }
        $content .= "</div>";
        $content .= "</div>";
    }
    
    return array('stat'=>'ok', 'content'=>$content);
}
?>
