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
function ciniki_web_processBlockMonthlyAvailability(&$ciniki, $settings, $tnid, $block) {

    $content = '';

    if( isset($block['title']) && $block['title'] != '' ) {
        $content .= "<h2>" . $block['title'] . "</h2>";
    }

    $content .= "<div class='table monthly-availability"
        . (isset($block['class']) && $block['class'] != '' ? ' table-' . $block['class'] : '')
        . "'>";
    $content .= "<table>";
    if( !isset($block['headers']) || $block['headers'] == 'yes' ) {
        $content .= "<thead><tr>"
            . "<th>J</th>"
            . "<th>F</th>"
            . "<th>M</th>"
            . "<th>A</th>"
            . "<th>M</th>"
            . "<th>J</th>"
            . "<th>J</th>"
            . "<th>A</th>"
            . "<th>S</th>"
            . "<th>O</th>"
            . "<th>N</th>"
            . "<th>D</th>"
            . "</tr></thead>";
    }
    $content .= "<tbody><tr>"
        . "<td>" . (($block['months']&0x01) == 0x01 ? "<span class='fa-icon'>&#xf00c;</span>" : "") . "</td>"
        . "<td>" . (($block['months']&0x02) == 0x02 ? "<span class='fa-icon'>&#xf00c;</span>" : "") . "</td>"
        . "<td>" . (($block['months']&0x04) == 0x04 ? "<span class='fa-icon'>&#xf00c;</span>" : "") . "</td>"
        . "<td>" . (($block['months']&0x08) == 0x08 ? "<span class='fa-icon'>&#xf00c;</span>" : "") . "</td>"
        . "<td>" . (($block['months']&0x10) == 0x10 ? "<span class='fa-icon'>&#xf00c;</span>" : "") . "</td>"
        . "<td>" . (($block['months']&0x20) == 0x20 ? "<span class='fa-icon'>&#xf00c;</span>" : "") . "</td>"
        . "<td>" . (($block['months']&0x40) == 0x40 ? "<span class='fa-icon'>&#xf00c;</span>" : "") . "</td>"
        . "<td>" . (($block['months']&0x80) == 0x80 ? "<span class='fa-icon'>&#xf00c;</span>" : "") . "</td>"
        . "<td>" . (($block['months']&0x100) == 0x100 ? "<span class='fa-icon'>&#xf00c;</span>" : "") . "</td>"
        . "<td>" . (($block['months']&0x200) == 0x200 ? "<span class='fa-icon'>&#xf00c;</span>" : "") . "</td>"
        . "<td>" . (($block['months']&0x400) == 0x400 ? "<span class='fa-icon'>&#xf00c;</span>" : "") . "</td>"
        . "<td>" . (($block['months']&0x800) == 0x800 ? "<span class='fa-icon'>&#xf00c;</span>" : "") . "</td>"
        . "</tr></tbody>";
    $content .= "</table>";
    $content .= "</div>";
    
    return array('stat'=>'ok', 'content'=>$content);
}
?>

