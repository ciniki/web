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
function ciniki_web_processBlockTable(&$ciniki, $settings, $tnid, $block) {

    $content = '';

    if( isset($block['title']) && $block['title'] != '' ) {
        $content .= "<h2>" . $block['title'] . "</h2>";
    }

    $content .= "<div class='table"
        . (isset($block['class']) && $block['class'] != '' ? ' table-' . $block['class'] : '')
        . "'>";
    $content .= "<table>";
    if( !isset($block['headers']) || $block['headers'] == 'yes' ) {
        $content .= "<thead><tr>";
        foreach($block['columns'] as $column) {
            $content .= "<th" . (isset($column['class']) && $column['class'] != '' ? " class='" . $column['class'] . "'" : "") . ">"
                . $column['label']
                . "</th>";
        }
        $content .= "</tr></thead>";
    }
    $content .= "<tbody>";
    foreach($block['rows'] as $row) {
        $content .= "<tr>";
        foreach($block['columns'] as $column) {
            $content .= "<td" . (isset($column['class']) && $column['class'] != '' ? " class='" . $column['class'] . "'" : "") . ">";
            if( isset($row[$column['field']]) ) {
                $content .= $row[$column['field']];
            } 
            $content .= "</td>";
        }
        $content .= "</tr>";
    }
    $content .= "</table>";
    $content .= "</div>";
    
    return array('stat'=>'ok', 'content'=>$content);
}
?>
