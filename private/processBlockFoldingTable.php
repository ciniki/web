<?php
//
// Description
// -----------
// This block was designed for the ciniki.musicfestivals registrations list. The format 
// is based on the first column being a "header" style when in narrow screen, with 
// details below, whereas on large screen it will unfold to be a table.
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_processBlockFoldingTable(&$ciniki, $settings, $tnid, $block) {

    $content = '';

    if( isset($block['title']) && $block['title'] != '' ) {
        $content .= "<h2>" . $block['title'] . "</h2>";
    }

    if( isset($block['id']) && $block['id'] != '' ) {
        $id = $block['id'];
    } else {
        $id = chr(rand(65,90)) . chr(rand(65,90));
    }

    $content .= "<div id='ft-$id' class='folding-table"
        . (isset($block['class']) && $block['class'] != '' ? ' folding-table-' . $block['class'] : '')
        . "'>";
    if( isset($block['add']['url']) ) {
        $content .= "<div class='folding-table-add'>"
            . "<a href='" . $block['add']['url'] . "'>"
            . (isset($block['add']['label']) ? $block['add']['label'] : '+ Add')
            . "</a></div>";
    }
    $content .= "<table>";
    if( !isset($block['headers']) || $block['headers'] == 'yes' ) {
        $content .= "<thead><tr>";
        foreach($block['columns'] as $column) {
            $content .= "<th" . (isset($column['class']) && $column['class'] != '' ? " class='" . $column['class'] . "'" : "") . ">"
                . $column['label']
                . "</th>";
        }
        if( $block['editable'] == 'yes' ) {
            $content .= "<th></th>";
        }
        $content .= "</tr></thead>";
    }
    $content .= "<tbody>";
    $rowcount = 1;
    foreach($block['rows'] as $row) {
        //
        // Check if row is editable
        //
        $row_url = '';
        $a_start = '';
        $a_end = '';
        if( isset($block['editable']) && $block['editable'] == 'yes' 
            && isset($row['edit-url']) && $row['edit-url'] != '' 
            ) {
            $row_url = $row['edit-url'];
            $a_start = "<a href='" . $row['edit-url'] . "'>";
            $a_end = "</a>";
        } else {
            $a_start = "<span class='no-a'>";
            $a_end = "</span>";
        }
        $content .= "<tr class='" . (($rowcount%2) ? 'odd' : 'even') 
            . ($row_url != '' ? ' editable' : '')
            . "'>";
        $colcount = 1;
        foreach($block['columns'] as $column) {
            //
            // Check if a folded label column should be added, it will be hidden on wide view
            //
            if( isset($block['folded-labels']) && $block['folded-labels'] == 'yes' 
                && isset($column['fold']) && $column['fold'] == 'yes' 
                && $column['label'] != '' 
                ) {
                $content .= "<td class='label folded-label " . (($colcount%2) ? 'odd' : 'even') . "'>" 
                    . $a_start . $column['label'] 
                    //. "<span class='fa-icon cell-button'>&#xf105;</span>" 
                    . $a_end
                    . "</td>";
            }
            //
            // The main column content
            //
            $content .= "<td class='value " . (($colcount%2) ? 'odd' : 'even') 
                . (isset($column['class']) && $column['class'] != '' ? " " . $column['class'] : "") . "'>"
                . $a_start;
            if( isset($row[$column['field']]) ) {
                $content .= $row[$column['field']];
            } 
            if( !isset($column['fold']) || $column['fold'] != 'yes' ) {
                $content .= "<span class='fa-icon cell-button'>&#xf105;</span>";
            }
            $content .= $a_end . "</td>";
            $colcount++;
        }
        if( isset($block['editable']) && $block['editable'] == 'yes' ) {
            if( $row_url != '' ) {
                $content .= "<td class='row-button'>{$a_start}<span class='fa-icon'>&#xf105;</span>{$a_end}</td>";
            } else {
                $content .= "<td class='row-button'></td>";
            }
        }
        $content .= "</tr>";
        $rowcount++;
    }
    $content .= "</table>";
    $content .= "</div>";

    //
    // Add the custom styles for folding
    //
    $css = ""
        . "#content #ft-{$id}.folding-table tr td.label {"
            . "width: 25%;"
        . "}"
        . "#content #ft-{$id}.folding-table tr td.value {"
            . "width: 75%;"
        . "}"
        . "#content #ft-{$id}.folding-table tr td.value:first-child {"
            . "width: 100%;"
        . "}"
        . "@media (min-width: 50em) {"
            . "#content #ft-{$id}.folding-table tr td.odd, "
            . "#content #ft-{$id}.folding-table tr td.even {"
                . "background: inherit;"
            . "}"
            . "#content #ft-{$id}.folding-table thead {"
                . "display: table-header-group;"
            . "}"
            . "#content #ft-{$id}.folding-table tr .cell-button, "
            . "#content #ft-{$id}.folding-table tr td.label {"
                . "display: none;"
            . "}"
            . "#content #ft-{$id}.folding-table tr th, "
            . "#content #ft-{$id}.folding-table tr td, "
            . "#content #ft-{$id}.folding-table tr td.value, "
            . "#content #ft-{$id}.folding-table tr td.value:first-child, "
            . "#content #ft-{$id}.folding-table tr td.row-button {"
                . "display: table-cell;"
                . "width: auto;"
            . "}"
            . "#content #ft-{$id}.folding-table tr td.value:first-child {"
                . "font-weight: normal;"
            . "}"
        . "}";
    
    return array('stat'=>'ok', 'content'=>$content, 'css'=>$css);
}
?>
