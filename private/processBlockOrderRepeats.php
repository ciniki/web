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
function ciniki_web_processBlockOrderRepeats(&$ciniki, $settings, $tnid, $block) {

    $content = '';

    //
    // Get tenant/user settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'intlSettings');
    $rc = ciniki_tenants_intlSettings($ciniki, $tnid);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $intl_timezone = $rc['settings']['intl-default-timezone'];

    //
    // Setup the api endpoints and submit urls
    //
    $api_repeat_update = (isset($block['api_repeat_update']) ? $block['api_repeat_update'] : '');

    $js_variables = array();

    //
    // Check for block title
    //
    if( isset($block['title']) && $block['title'] != '' ) {
        $content .= "<h2" . ((isset($block['size'])&&$block['size']!='') ? " class='" . $block['size'] . "'" : '') . ">" . $block['title'] . "</h2>";
    }

    //
    // Make sure there is content to edit
    //
    if( isset($block['repeats']) ) {
        $content .= "<div class='order-repeats" . ((isset($block['size'])&&$block['size']!='') ? ' ' . $block['size'] : '') . "'>";
        $content .= "<table class='order-repeats'>";
        $content .= "<tbody>";   
        foreach($block['repeats'] as $rid => $item) {
            $content .= "<tr class='order-repeats-item'>";
            $content .= "<td class='name'>" . $item['name'] . "</td>";
            //
            // Add the hidden row for adding to standing orders
            //
            $content .= "<td class='options'>";
            $content .=  "<div class='repeat-option'>Repeat "
                . "<span class='order-qty'>"
                . "<span class='order-qty-down' onclick='repeatQtyDown(" . $item['id'] . ");'>-</span>"
                . "<input id='repeat_quantity_" . $item['id'] . "' name='repeat_quantity_" . $item['id'] . "' "
                    . "value='" . (float)$item['repeat_quantity'] . "' "
                    . "onkeyup='repeatQtyChange(" . $item['id'] . ");' "
                    . "onchange='repeatQtyChange(" . $item['id'] . ");' "
                    . "/>"
                . "<span class='order-qty-up' onclick='repeatQtyUp(" . $item['id'] . ");'>+</span>"
                . "</span>"
                . " every "
                . "<select id='repeat_days_" . $item['id'] . "' onchange='repeatChange(" . $item['id'] . ");'>"
                    . "<option value='7'" . (isset($item['repeat_days'])&&$item['repeat_days']==7?' selected':'') . ">week</option>"
                    . "<option value='14'" . (isset($item['repeat_days'])&&$item['repeat_days']==14?' selected':'') . ">2 weeks</option>"
//                            . "<option value='21'" . (isset($item['repeat_days'])&&$item['repeat_days']==21?' selected':'') . ">3 weeks</option>"
//                            . "<option value='28'" . (isset($item['repeat_days'])&&$item['repeat_days']==28?' selected':'') . ">4 weeks</option>"
                . "</select>"
                . "</div>";
            $js_variables['object_ref_' . $item['id']] = $item['object'] . '/' . $item['object_id'];
            $js_variables['repeat_quantity_' . $item['id']] = $item['repeat_quantity'];
            $js_variables['repeat_days_' . $item['id']] = isset($item['repeat_days']) ? $item['repeat_days'] : 0;
//                        $content .= "<pre>" . print_r($item, true) . "</pre>";
//            $content .= "</td><td>";
            $content .=  "<div id='repeat_option_next_" . $item['id'] . "' class='repeat-option " . ($item['repeat_quantity']>0?'':"repeat-next-hide") . "'>"
                . "Next order on <span id='repeat_date_next_" . $item['id'] . "'>" . $item['repeat_next_date'] . "</span>"
                . " <button onclick='repeatSkip(" . $item['id'] . ");'>Skip</button>"
                . "</div>";
            $content .= "</td></tr>";
        }
        $content .= "</tbody>";
        $content .= "</table>";
        $content .= "</div>";

        //
        // Add javascript
        //
        if( !isset($ciniki['request']['inline_javascript']) ) {
            $ciniki['request']['inline_javascript'] = '';
        }
        $ciniki['request']['ciniki_api'] = 'yes';
        $ciniki['request']['inline_javascript'] .= "<script type='text/javascript'>"
            . "var org_val={"
            . "";
        foreach($js_variables as $k => $v) {
            $ciniki['request']['inline_javascript'] .= "'$k':'$v',";
        }
        $ciniki['request']['inline_javascript'] .= ""
            . "};"
            . "function repeatQtyUp(id){"
                . "var e=C.gE('repeat_quantity_' + id);"
                . "if(e.value==''){"
                    . "return true;"
                    . "e.value=1;"
                . "}else{"
                    . "e.value=parseInt(e.value)+1;"
                . "}"
                . "repeatChange(id);"
            . "}"
            . "function repeatQtyDown(id){"
                . "var e=C.gE('repeat_quantity_' + id);"
                . "if(parseInt(e.value)<1){"
                    . "e.value=0;"
                . "}else if(e.value==''){"
                    . "return true;"
                    . "e.value=1;"
                . "}else{"
                    . "e.value=parseInt(e.value)-1;"
                . "}"
                . "repeatChange(id);"
            . "}"
            . "function repeatSkip(id){"
                . "repeatChange(id,'yes');"
            . "}"
            . "function repeatChange(id,skip){"
                . "var e=C.gE('repeat_quantity_' + id);"
                . "var d=C.gE('repeat_days_' + id);"
                . "var e3=C.gE('repeat_option_next_' + id);"
                . "var args={};"
                . "if(e.value!=org_val['repeat_quantity_'+id]){"
                    . "args['quantity']=e.value;"
                . "}"
                . "if(d.value!=org_val['repeat_days_'+id]){"
                    . "args['repeat_days']=d.value;"
                . "}"
                . "if(skip!=null&&skip=='yes'){"
                    . "args['skip']='yes';"
                . "}"
                . "C.getBg('" . $api_repeat_update . "'+org_val['object_ref_'+id],args,function(r){"
                    . "if(r.stat=='noavail'){"
                        . "e.value=org_val['repeat_quantity_'+id];"
                        . "alert(\"We're sorry, but there are no more available.\");"
                        . "return false;"
                    . "}else if(r.stat!='ok'){"
                        . "e.value=org_val['repeat_quantity_'+id];"
                        . "alert('We had a problem updating your standing order. Please try again or contact us for help.');"
                        . "return false;"
                    . "}"
                    . "if(r.item.next_order_date_text!=null){"
                        . "C.gE('repeat_date_next_'+id).innerHTML=r.item.next_order_date_text;"
                    . "}"
                    . "if(r.item.quantity!=null){"
                        . "org_val['repeat_quantity_'+id]=r.item.quantity;"
                    . "}"
                    . "if(r.item.repeat_days!=null){"
                        . "org_val['repeat_days_'+id]=r.item.repeat_days;"
                    . "}"
                    . "if(parseFloat(e.value)>0){"
                        . "if(e3.classList.contains('repeat-next-hide')){"
                            . "e3.classList.remove('repeat-next-hide');"
                        . "}"
                    . "}else if(!e3.classList.contains('repeat-next-hide')){"
                        . "e3.classList.add('repeat-next-hide');"
                    . "}"
                . "});"
            . "}"
            . "</script>";
    }
    
    return array('stat'=>'ok', 'content'=>$content);
}
?>
