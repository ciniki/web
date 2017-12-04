<?php
//
// Description
// -----------
// This function will process a page block with a form.
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_processBlockSectionedForm(&$ciniki, $settings, $tnid, $block) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');

    if( !isset($block['form']['sections']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.181', 'msg'=>'No form specified'));
    }

    $content = '';

    //
    // Check for block title
    //
    if( isset($block['title']) && $block['title'] != '' ) {
        $content .= "<h2" . ((isset($block['size'])&&$block['size']!='') ? " class='" . $block['size'] . "'" : '') . ">" . $block['title'] . "</h2>";
    }

    //
    // List the sections of the forms
    //
    $content .= "<ol class='sectioned-form-sections" . ((isset($block['size'])&&$block['size']!='') ? ' ' . $block['size'] : '') . "'>";
    $prev_section = null;
    $cur_section = null;
    $next_section = null;
    $hidden_fields = '';
    foreach($block['form']['sections'] as $sid => $section) {
        $selected = 'no';
        if( $next_section == null && $cur_section != null ) {
            $next_section = $section;
            $next_section['id'] = $sid;
        }
        if( $sid == $block['section'] ) {
            $cur_section = $section;
            $cur_section['id'] = $sid;
            $selected = 'yes';
            $content .= "<li class='active'>" . $section['name'] . "</li>";
        } else {
            $content .= "<li>" . $section['name'] . "</li>";
            foreach($section['fields'] as $fid => $field) {
                if( $field['type'] == 'checkboxes' ) {
                    $c = 0;
                    foreach($field['options'] as $o) {
                        if( isset($block['values'][$fid]) && in_array($o, $block['values'][$fid]) ) {
                            $hidden_fields .= "<input type='hidden' name='{$fid}_{$c}' value=\"$o\"/>";
                        }
                        $c++;
                    }
                } else {
                    $hidden_fields .= "<input type='hidden' name='$fid' value=\"" . (isset($block['values'][$fid]) ? $block['values'][$fid] : '') . "\"/>";
                }
            }
        }
        if( $cur_section == null ) {
            $prev_section = $section;
            $prev_section['id'] = $sid;
        }
    }
    $content .= "</ol>";

    //
    // Display the selected section
    //
    $javascript = '';
    if( $cur_section != null ) {
        $content .= "<div class='sectioned-form'>";

        $content .= "<form action='' method='POST'>";
        $content .= "<input type='hidden' name='cur_section' value='" . $cur_section['id'] . "'>";
        $content .= $hidden_fields;
        foreach($cur_section['fields'] as $fid => $field) {
            $content .= "<div id='input_" . $fid . "' class='sectioned-form-input sectioned-form-" . $field['type'] 
                . (isset($field['err_msg']) ? ' sectioned-form-error' : '')
                . (isset($field['required']) && $field['required'] == 'yes' ? ' sectioned-form-required' : '')
                . (isset($field['size']) && $field['size'] != '' ? ' sectioned-form-' . $field['size'] : '')
                . (isset($field['visible']) && $field['visible'] != 'yes' ? ' sectioned-form-hidden' : '')
                . "'>";
            if( isset($field['label']) && $field['label'] != '' ) {
                $content .= "<label for='$fid'>" . $field['label'] 
                    . (isset($field['required']) && $field['required'] == 'yes' ? ' *' : '') 
                    . "</label>";
            }
            $content .= "<div class='sectioned-form-content'>";
            if( isset($field['description']) && $field['description'] != '' ) {
                $rc = ciniki_web_processContent($ciniki, $settings, $field['description']);
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
                $content .= "<div class='sectioned-form-description'>" . $rc['content'] . "</div>";
            }
            if( $field['type'] == 'text' ) {
                $content .= "<input type='text' id='$fid' name='$fid' value=\"" . (isset($block['values'][$fid]) ? $block['values'][$fid] : '') . "\"/>";
            } elseif( $field['type'] == 'date' ) {
                $content .= "<input type='text' id='$fid' name='$fid' value=\"" . (isset($block['values'][$fid]) ? $block['values'][$fid] : '') . "\"/>";
            } elseif( $field['type'] == 'radio' ) {
                $content .= "<fieldset>";
                $c = 0;
                foreach( $field['options'] as $o) {
                    $content .= "<input onchange='updateForm();' type='radio' id='{$fid}_{$c}' name='$fid' value=\"$o\"" 
                        . (isset($block['values'][$fid]) && $block['values'][$fid] == $o ? ' checked': '') . " />";
                    $content .= "<label for='{$fid}_{$c}'>$o</label>";
                    $c++;
                }
                $content .= "</fieldset>";
            } elseif( $field['type'] == 'select' ) {
                $content .= "<select id='$fid' name='$fid'>";
                foreach( $field['options'] as $o) {
                    $content .= "<option value=\"$o\"" . (isset($block['values'][$fid]) && $block['values'][$fid] == $o ? ' selected': '') . ">$o</option>";
                }
                $content .= "</select>";

            } elseif( $field['type'] == 'checkboxes' ) {
                $c = 0;
                $content .= "<fieldset>";
                foreach( $field['options'] as $o) {
                    $content .= "<div class='checkbox'>";
                    $content .= "<input type='checkbox' id='{$fid}_{$c}' name='{$fid}_{$c}' value=\"$o\"" 
                        . (isset($block['values'][$fid]) && in_array($o, $block['values'][$fid]) ? ' checked': '') . " />"
                        . "<label for='{$fid}_{$c}'>$o</label>";
                    $content .= "</div>";
                    $c++;
                }
                $content .= "</fieldset>";
            } elseif( $field['type'] == 'textarea' ) {
                $content .= "<textarea name='$fid' class='" . (isset($field['size']) ? $field['size'] : '') . "'>" 
                    . (isset($block['values'][$fid]) ? $block['values'][$fid] : '')
                    . "</textarea>";
            }
            if( isset($field['err_msg']) && $field['err_msg'] != '' ) {
                $content .= "<span class='sectioned-form-err-msg'>" . $field['err_msg'] . "</span>";
            }
            $content .= "</div>";
            $content .= "</div>";

            //
            // Build the javascript to show/hide inputs based on conditions
            //
            if( isset($field['conditions']) ) {
                $javascript .= "var visible='yes';";
                foreach($field['conditions'] as $condition) {
                    //
                    // Check if conditions field exists
                    //
                    if( isset($block['form']['sections'][$condition['section']]['fields'][$condition['field']]) ) {
                        $c_field = $block['form']['sections'][$condition['section']]['fields'][$condition['field']];
                        //
                        // Find the value of the radio
                        //
                        if( $c_field['type'] == 'radio' ) {
                            $javascript .= "var e = document.getElementsByName('" . $condition['field'] . "');"
                                . "var v = '';"
                                . "for(i=0;i < e.length;i++) {"
                                    . "if(e[i].checked==true){"
                                        . "v=e[i].value;"
                                    . "}"
                                . "} ";
                            //
                            // Check if the value meets condition
                            //
                            $javascript .= "if(v!=\"" . $condition['value'] . "\"){"
                                    . "visible='no';"
                                . "}";
                        }
                    }
                }

                //
                // Check if the value meets condition
                //
                $javascript .= "var e = document.getElementById('input_$fid');"
                    . "if(visible=='no'){"
                        . "if(!e.classList.contains('sectioned-form-hidden')){"
                            . "e.classList.add('sectioned-form-hidden');"
                        . "}"
                    . "}else{"
                        . "if(e.classList.contains('sectioned-form-hidden')){"
                            . "e.classList.remove('sectioned-form-hidden');"
                        . "}"
                    . "}";
            }
        }

        $content .= "<div class='sectioned-form-nav'><div class='sectioned-form-nav-content'>";
        $content .= "<span class='sectioned-form-nav-button sectioned-form-nav-button-prev'>";
        if( $prev_section != null ) {
//            $content .= "<a href='" . $block['base_url'] . "?section=" . $prev_section['id'] . "'><span>Previous</span></a>";
            $content .= "<input type='hidden' name='previous' value='" . $prev_section['id'] . "'/>";
            $content .= "<input class='submit' type='submit' name='action' value='Previous'/>";
        }
        $content .= "</span>";
        $content .= "<span class='sectioned-form-nav-button sectioned-form-nav-button-next'>";
        if( $next_section != null ) {
            $content .= "<input type='hidden' name='next' value='" . $next_section['id'] . "'/>";
            $content .= "<input class='submit' type='submit' name='action' value='Next'/>";
            //$content .= "<a href='" . $block['base_url'] . "?section=" . $next_section['id'] . "'><span>Next</span></a>";
        } else {
            $content .= "<input class='submit' type='submit' name='action' value='Submit'/>";
            //$content .= "<a href='" . $block['base_url'] . "?section=" . $next_section['id'] . "'><span>Submit</span></a>";
        }
        $content .= "</span>";
        $content .= "</div></div>";
        $content .= "</form>";
        $content .= "</div>";

        //
        // Javascript to hide/show fields
        //
        $content .= "<script type='text/javascript'>"
            . "function updateForm() {"
            . "console.log('test');"
            . $javascript
            . "}"
            . "window.onLoad = updateForm;"
            . "</script>";

    } else {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.182', 'msg'=>'Error encountered.'));
    }

    return array('stat'=>'ok', 'content'=>$content);
}
?>
