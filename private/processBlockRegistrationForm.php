<?php
//
// Description
// -----------
// This function will process a registration form for a module. This was originally developed
// for the musicfestival module.
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_processBlockRegistrationForm(&$ciniki, $settings, $tnid, $block) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');

    if( !isset($block['sections']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.196', 'msg'=>'No form specified'));
    }

    if( isset($block['id']) && $block['id'] != '' ) {
        $id = $block['id'];
    } else {
        $id = 'rf-' . chr(rand(65,90)) . chr(rand(65,90));
    }

    $content = '';

    //
    // Check for block title
    //
    if( isset($block['title']) && $block['title'] != '' ) {
        $content .= "<h2" . ((isset($block['size'])&&$block['size']!='') ? " class='" . $block['size'] . "'" : '') . ">" . $block['title'] . "</h2>";
    }

    //
    // Start the form
    //
    $content .= "<form id='{$id}' class='registration-form"
        . (isset($block['class']) ? " " . $block['class'] : '') . "'"
        . " action='" . (isset($block['action']) ? $block['action'] : '') . "'"
        . " method='" . (isset($block['method']) ? $block['method'] : 'POST') . "'"
        . ">";

    $onchangefn = '';
    if( isset($block['onchange']) ) {
        $onchangefn = " onchange='{$block['onchange']}'";
    }

    //
    // Process the form sections
    //
    foreach($block['sections'] as $sid => $section) {
        $content .= "<div id='$sid' class='section-wrap"
            . (!isset($section['visible']) || $section['visible'] == 'yes' ? '' : ' hidden')
            . "'>"
            . "<div class='section-title'>" . $section['label'] . "</div>"
            . "<div class='section-content'>";
       
        foreach($section['fields'] as $fid => $field) {
            //
            // Select field
            //
            $content .= "<div id='{$fid}-wrap' class='input section-field"
                . (!isset($field['visible']) || $field['visible'] == 'yes' ? '' : ' hidden')
                . (isset($field['size']) ? ' ' . $field['size'] : '')
                . "'>";
            if( isset($field['label']['title']) ) {
                $content .= "<label"
                    . (isset($field['label']['class']) ? " class='" . $field['label']['class'] . "'" : '')
                    . " for='{$fid}'>" . $field['label']['title'] . "</label>";
            }
            $selected_option_value = '';
            if( $field['type'] == 'select' ) {
                $content .= "<select id='{$fid}' name='{$fid}'{$onchangefn}>";
                foreach($field['options'] as $oid => $option) {
                    if( isset($option['selected']) && $option['selected'] == 'yes' ) {
                        $selected_option_value = $option['value'];
                    }
                    $content .= "<option value='" . $option['value'] . "'"
                        . (isset($option['selected']) && $option['selected'] == 'yes' ? ' selected' : '')
                        . ">"
                        . $option['label']
                        . "</option>";
                }
                $content .= "</select>";
            }
            //
            // Text input field
            //
            elseif( $field['type'] == 'text' ) {
                $content .= "<input class='text' id='{$fid}' name='{$fid}' value='{$field['value']}'/>";
            }
            //
            // Hidden input field
            //
            elseif( $field['type'] == 'hidden' ) {
                $content .= "<input class='hidden' id='{$fid}' name='{$fid}' value='{$field['value']}'/>";
            }

            $content .= "</div>";

            //
            // Check if more details for the field
            //
            if( isset($field['details']) && count($field['details']) > 0 ) {
                foreach($field['details'] as $did => $details ) {
                    $content .= "<div id='{$fid}-details-{$did}' class='input-details"
                        . ($selected_option_value == $did ? '' : ' hidden')
                        . "'>";
                    foreach($details as $detail) {
                        if( isset($detail['type']) && $detail['type'] == 'button' ) {
                            $content .= "<div class='input-details-button'>"
                                . "<a href='" . $detail['url'] . "'>"
                                    . "<span class='label'>" . $detail['label'] . "</span><span class='fa-icon'>&#xf105;</span>"
                                    . "</a>"
                                . "</div>";
                        } else {
                            $content .= "<div class='input-details-item"
                                . (isset($detail['size']) ? ' ' . $detail['size'] : '')
                                . "'>"
                                . "<span class='label'>" . $detail['label'] . "</span>"
                                . "<span class='value'>" . ($detail['value'] != '' ? $detail['value'] : '&nbsp;') . "</span>"
                                . "</div>";
                        }
                    }
                    $content .= "</div>";
                }
            }
        }

        $content .= "</div></div>";
    }

    //
    // Check for form buttons
    //
    if( isset($block['buttons']) ) {
        $content .= "<div class='buttons'>";
        foreach($block['buttons'] as $bid => $button) {
            $content .= "<input class='submit"
                . (isset($button['class']) && $button['class'] != '' ? ' ' . $button['class'] : '')
                . "' type='submit' name='{$bid}' value='{$button['label']}'/>";
        }
        $content .= "</div>";
    }

    //
    // End the form
    //
    $content .= "</form>";

    //
    // Check for javascript 
    //
    $js = '';
    if( isset($block['javascript']) && $block['javascript'] != '' ) {
        $js .= $block['javascript'];
    }

    //
    // Add the custom styles for folding
    //
    $css = ""
        . "@media (min-width: 50em) {"
            . "#content #{$id}.registration-form div.small {"
                . "max-width: 25%;"
                . "width: 25%;"
            . "}"
            . "#content #{$id}.registration-form div.medium {"
                . "max-width: 50%;"
                . "width: 50%;"
            . "}"
            . "#content #{$id}.registration-form div.large {"
                . "max-width: 75%;"
                . "width: 75%;"
            . "}"
/*            . "#content #ft-{$id}.folding-table tr td.odd, "
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
            . "}" */
        . "}";
    
    return array('stat'=>'ok', 'content'=>$content, 'css'=>$css, 'js'=>$js);
}
?>
