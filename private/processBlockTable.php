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
    if( isset($block['intro']) && $block['intro'] != '' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
        $rc = ciniki_web_processContent($ciniki, $settings, $block['intro'], ((isset($block['wide'])&&$block['wide']=='yes')?'wide':''));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( $rc['content'] != '' ) {
            $content .= $rc['content'];
        }
    }

    $content .= "<table>";
    $num_cols = 0;
    if( !isset($block['headers']) || $block['headers'] == 'yes' ) {
        $content .= "<thead><tr>";
        foreach($block['columns'] as $column) {
            $content .= "<th" . (isset($column['class']) && $column['class'] != '' ? " class='" . $column['class'] . "'" : "") . ">"
                . $column['label']
                . "</th>";
            $num_cols++;
        }
        $content .= "</tr></thead>";
    }
    $content .= "<tbody>";
    $count = 0;
    foreach($block['rows'] as $row) {
        $content .= "<tr>";
        foreach($block['columns'] as $column) {
            $content .= "<td" . (isset($column['class']) && $column['class'] != '' ? " class='" . $column['class'] . "'" : "") . ">";
            if( isset($column['strsub']) && $column['strsub'] != '' ) {
                $value = $column['strsub'];
                if( preg_match('/{_([a-zA-Z0-9_]+)_}/', $column['strsub'], $m) ) {
                    foreach($m as $field) {
                        if( isset($row[$field]) ) {
                            $value = str_replace("{_{$field}_}", $row[$field], $value);
                        } 
                    }
                }
                $content .= $value;
            }
            if( isset($column['field']) && isset($row[$column['field']]) ) {
                $content .= $row[$column['field']];
            } 
            $content .= "</td>";
        }
        $content .= "</tr>";
        $count++;
    }
    if( $count == 0 && isset($block['empty']) ) {
        $content .= "<tr><td class='empty' colspan='" . $num_cols . "'>" . $block['empty'] . "</td></tr>";
    }
    $content .= "</table>";
    $content .= "</div>";
    
    return array('stat'=>'ok', 'content'=>$content);
}
?>
