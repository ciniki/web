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
function ciniki_web_processBlockOrderSeason(&$ciniki, $settings, $tnid, $block) {

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
    $api_order_skip = (isset($block['api_order_skip']) ? $block['api_order_skip'] : '');

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
    if( isset($block['orders']) ) {
        $content .= "<div class='order-season" . ((isset($block['size'])&&$block['size']!='') ? ' ' . $block['size'] : '') . "'>";
        $content .= "<table class='order-season'>";
        $content .= "<thead>";   
        $content .= "<tr><th>Date</th><th>Status</th><th>Items</th><th></th></tr>";   
        $content .= "</thead>";   
        $content .= "<tbody>";   
        foreach($block['orders'] as $rid => $item) {
            $content .= "<tr class='order-repeats-item'>";
            $content .= "<td class='date'>" . $item['order_date'] . "</td>";
            if( $item['date_status'] == 5 ) {
                $content .= "<td class='date'>Pending</td>";
            } elseif( $item['date_status'] <= 30 && $item['status'] <= 30 ) {
                // Order date still open, and order not locked
                $content .= "<td class='date'>Open</td>";
            } else {
                $content .= "<td class='date'>Closed</td>";
            }
            $content .= "<td class='products'>" . $item['products'] . "</td>";
            if( $item['date_status'] <= 30 && $item['status'] <= 30 && $block['skip_available'] == 'yes' ) {
                $content .= "<td class='action'><a onclick='return confirm(\"Are you sure you want to skip " . $item['order_date'] . "?\");' href='" . $block['base_url'] . "/csa/skip/" . $item['id'] . "'>Skip</a></td>";
            } else {
                $content .= "<td class='action'></td>";
            }
            $content .= "</tr>";
        }
        $content .= "</tbody>";
        $content .= "</table>";
        $content .= "</div>";

/*        //
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
            . "</script>"; */
    }
    
    return array('stat'=>'ok', 'content'=>$content);
}
?>
