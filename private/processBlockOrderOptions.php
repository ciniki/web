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
function ciniki_web_processBlockOrderOptions(&$ciniki, $settings, $tnid, $block) {

    $heart_off = '<span class="fa-icon order-icon order-options-fav-off">&#xf08a;</span>';
    $heart_on = '<span class="fa-icon order-icon order-options-fav-on">&#xf004;</span>';
    $order_off = '<span class="fa-icon order-icon order-options-order-off">&#xf217;</span>';
    $order_on = '<span class="fa-icon order-icon order-options-order-on">&#xf217;</span>';
    $order_repeat = '<span class="fa-icon order-icon order-options-order-repeat">&#xf217;</span>';
    $order_queue = '<span class="fa-icon order-icon order-options-order-queue">&#xf217;</span>';
    $queue_slot_open = '<span class="fa-icon order-icon order-options-queue-slot-open">&#xf096;</span>';
    $queue_slot_filled = '<span class="fa-icon order-icon order-options-queue-slot-filled">&#xf14a;</span>';

    $content = '';
    // Generate unique ID for each org_val when using same block multiple times on page
    $blkid = sprintf("%02d", rand(0,99));

    //
    // Get tenant/user settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'intlSettings');
    $rc = ciniki_tenants_intlSettings($ciniki, $tnid);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $intl_timezone = $rc['settings']['intl-default-timezone'];
    $intl_currency_fmt = numfmt_create($rc['settings']['intl-default-locale'], NumberFormatter::CURRENCY);
    $intl_currency = $rc['settings']['intl-default-currency'];

    //
    // Setup the api endpoints and submit urls
    //
    $api_fav_on = (isset($block['api_fav_on']) ? $block['api_fav_on'] : '');
    $api_fav_off = (isset($block['api_fav_off']) ? $block['api_fav_off'] : '');
    $api_order_update = (isset($block['api_order_update']) ? $block['api_order_update'] : '');
    $api_repeat_update = (isset($block['api_repeat_update']) ? $block['api_repeat_update'] : '');
    $api_queue_update = (isset($block['api_queue_update']) ? $block['api_queue_update'] : '');

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
    if( $block['options'] != '' ) {
        $content .= "<div class='order-options" . ((isset($block['size'])&&$block['size']!='') ? ' ' . $block['size'] : '') . "'>";
        $content .= "<table class='order-options'>";
        $content .= "<tbody>";   
        $group_name = '';
        foreach($block['options'] as $oid => $option) {
            if( isset($block['groupings']) && $block['groupings'] == 'tables' 
                && isset($option['group_name']) && $option['group_name'] != $group_name 
                ) {    
                if( $group_name != '' ) {
                    $content .= "</tbody>";
                    $content .= "</table>";
                    $content .= "<table class='order-options'>";
                    $content .= "<tbody>";   
                }
                $content .= "<tr class='order-options-item order-options-group-start'>";
                $group_name = $option['group_name'];
            } else {
                $content .= "<tr class='order-options-item'>";
            }
            if( isset($block['clickable']) && $block['clickable'] == 'yes' 
                && isset($option['permalink']) && $option['permalink'] != '' 
                ) {
                $content .= "<td class='name'><a href='" . $block['base_url'] . "/" . $option['permalink'] . "'>" . $option['name'] . "</a></td>";
            } else {
                $content .= "<td class='name'>" . $option['name'] . "</td>";
            }
            if( isset($ciniki['session']['customer']['id']) && $ciniki['session']['customer']['id'] > 0 
                && (!isset($ciniki['config']['ciniki.core']['maintenance']) || $ciniki['config']['ciniki.core']['maintenance'] != 'on') 
                ) {
                if( isset($option['queue_slots_total']) && $option['queue_slots_total'] > 0 
                    && isset($option['queue_slots_filled']) 
                    ) {
                    $js_variables['queue_size_other_' . $option['id']] = ($option['queue_size'] - $option['queue_quantity']);
                    $js_variables['queue_slots_total_' . $option['id']] = $option['queue_slots_total'];
                    $js_variables['queue_slots_filled_' . $option['id']] = $option['queue_slots_filled'];
                    $content .= "<td id='q_" . $option['id'] . "'>";
                    for($i = 1; $i <= $option['queue_slots_total']; $i++) {
                        if( $i <= $option['queue_slots_filled'] ) {
                            $content .= $queue_slot_filled;
                        } else {
                            $content .= $queue_slot_open;
                        }
                    }
                    $content .= "</td>";
                } else {
                    $content .= "<td>&nbsp;</td>";
                }
            }
            if( isset($option['sale_price_text']) && $option['sale_price_text'] != '' ) {
                $content .= "<td class='price alignright'><s>" . $option['price_text'] . '</s> ' . $option['sale_price_text'] . "</td>";
            } else {
                $content .= "<td class='price alignright'>" . $option['price_text'] . "</td>";
            }
            if( isset($ciniki['session']['customer']['id']) && $ciniki['session']['customer']['id'] > 0 
                && (!isset($ciniki['config']['ciniki.core']['maintenance']) || $ciniki['config']['ciniki.core']['maintenance'] != 'on') 
                ) {
                if( isset($option['favourite']) && $option['favourite'] == 'yes' ) {
                    if( isset($option['favourite_value']) && $option['favourite_value'] == 'on' ) {
                        $content .= "<td id='fav_" . $option['id'] . "' class='clickable aligncenter fav-on' onclick='favToggle_{$blkid}(" . $option['id'] . ");'>" . $heart_on . "</td>";
                    } else {
                        $content .= "<td id='fav_" . $option['id'] . "' class='clickable aligncenter fav-off' onclick='favToggle_{$blkid}(" . $option['id'] . ");'>" . $heart_off . "</td>";
                    }
                } else {
                    $content .= "<td></td>";
                }
                if( isset($option['available']) && $option['available'] == 'yes' && isset($option['order_quantity']) && $option['order_quantity'] > 0 ) {
                    $content .= "<td id='option_" . $option['id'] . "' class='clickable aligncenter' onclick='orderToggle_{$blkid}(" . $option['id'] . ");'>" . $order_on . "</td>";
                } 
                elseif( isset($option['repeat']) && $option['repeat'] == 'yes' && isset($option['repeat_quantity']) && $option['repeat_quantity'] > 0 ) {
                    $content .= "<td id='option_" . $option['id'] . "' class='clickable aligncenter' onclick='orderToggle_{$blkid}(" . $option['id'] . ");'>" . $order_repeat . "</td>";
                }
                elseif( isset($option['queue']) && $option['queue'] == 'yes' && isset($option['queue_quantity']) && $option['queue_quantity'] > 0 ) {
                    $content .= "<td id='option_" . $option['id'] . "' class='clickable aligncenter' onclick='orderToggle_{$blkid}(" . $option['id'] . ");'>" . $order_queue . "</td>";
                }
                elseif( (isset($option['available']) && $option['available'] == 'yes') 
                    || (isset($option['repeat']) && $option['repeat'] == 'yes') 
                    || (isset($option['queue']) && $option['queue'] == 'yes') 
                    ) {
                    $content .= "<td id='option_" . $option['id'] . "' class='clickable aligncenter' onclick='orderToggle_{$blkid}(" . $option['id'] . ");'>" . $order_off . "</td>";
                } else {
                    $content .= "<td></td>";
                }
            }
            $content .= "</tr>";

            //
            // Check if cart should be shown
            //
            if( isset($ciniki['session']['customer']['id']) && $ciniki['session']['customer']['id'] > 0 
                && (!isset($ciniki['config']['ciniki.core']['maintenance']) || $ciniki['config']['ciniki.core']['maintenance'] != 'on') 
                ) {
                //
                // Add the hidden row for adding to current order
                //
                if( isset($option['available']) && $option['available'] == 'yes' && isset($ciniki['session']['ciniki.poma']['date']['order_date_text']) ) {
                    $content .= "<tr id='order_option_" . $option['id'] . "' class='order-options-order order-hide'><td colspan='5'>";
                    $content .=  "<div class='order-option'>Order "
                        . "<span class='order-qty'>"
                        . "<span class='order-qty-down' onclick='orderQtyDown_{$blkid}(" . $option['id'] . ");'>-</span>"
//                        . "<input type='number' pattern='[0-9]' min='0' step='1' id='order_quantity_" . $option['id'] . "' name='order_quantity_" . $option['id'] . "' value='" . $option['order_quantity'] . "' editable=false/>"
                        . "<input id='order_quantity_" . $option['id'] . "' name='order_quantity_" . $option['id'] . "' "
                            . "value='" . $option['order_quantity'] . "' "
//                            . "old_value='" . $option['order_quantity'] . "' "
                            . "onkeyup='orderQtyChange_{$blkid}(" . $option['id'] . ");' "
                            . "onchange='orderQtyChange_{$blkid}(" . $option['id'] . ");' "
                            . "/>"
                        . "<span class='order-qty-up' onclick='orderQtyUp_{$blkid}(" . $option['id'] . ");'>+</span>"
                        . "</span>"
                        . " on " . $ciniki['session']['ciniki.poma']['date']['order_date_text']
                        . "</div>";
                    $js_variables['order_quantity_' . $option['id']] = $option['order_quantity'];
                    $content .= "</td></tr>";
                }
                //
                // Add the hidden row for adding to standing orders
                //
                if( isset($option['repeat']) && $option['repeat'] == 'yes' ) {
                    $content .= "<tr id='repeat_option_" . $option['id'] . "' class='order-options-order order-hide'><td colspan='5'>";
                    $content .=  "<div class='repeat-option'>Repeat "
                        . "<span class='order-qty'>"
                        . "<span class='order-qty-down' onclick='repeatQtyDown_{$blkid}(" . $option['id'] . ");'>-</span>"
                        . "<input id='repeat_quantity_" . $option['id'] . "' name='repeat_quantity_" . $option['id'] . "' "
                            . "value='" . $option['repeat_quantity'] . "' "
//                            . "old_value='" . $option['repeat_quantity'] . "' "
                            . "onkeyup='repeatQtyChange_{$blkid}(" . $option['id'] . ");' "
                            . "onchange='repeatQtyChange_{$blkid}(" . $option['id'] . ");' "
                            . "/>"
                        . "<span class='order-qty-up' onclick='repeatQtyUp_{$blkid}(" . $option['id'] . ");'>+</span>"
                        . "</span>"
                        . " every "
                        . "<select id='repeat_days_" . $option['id'] . "' onchange='repeatChange_{$blkid}(" . $option['id'] . ");'>"
                            . "<option value='7'" . (isset($option['repeat_days'])&&$option['repeat_days']==7?' selected':'') . ">week</option>"
                            . "<option value='14'" . (isset($option['repeat_days'])&&$option['repeat_days']==14?' selected':'') . ">2 weeks</option>"
//                            . "<option value='21'" . (isset($option['repeat_days'])&&$option['repeat_days']==21?' selected':'') . ">3 weeks</option>"
//                            . "<option value='28'" . (isset($option['repeat_days'])&&$option['repeat_days']==28?' selected':'') . ">4 weeks</option>"
                        . "</select>"
                        . "</div>";
                    $js_variables['repeat_quantity_' . $option['id']] = $option['repeat_quantity'];
                    $js_variables['repeat_days_' . $option['id']] = isset($option['repeat_days']) ? $option['repeat_days'] : 0;
                    $content .=  "<div id='repeat_option_next_" . $option['id'] . "' class='repeat-option " . ($option['repeat_quantity']>0?'':"repeat-next-hide") . "'>"
                        . "Next order on <span id='repeat_date_next_" . $option['id'] . "'>" . $option['repeat_next_date'] . "</span>"
                        . " <button onclick='repeatSkip_{$blkid}(" . $option['id'] . ");'>Skip</button>"
                        . "</div>";
                    $content .= "</td></tr>";
                }
                //
                // Add the hidden row for managing item in a queue
                //
                if( isset($option['queue']) && $option['queue'] == 'yes' ) {
                    $content .= "<tr id='order_option_" . $option['id'] . "' class='order-options-order order-hide'><td colspan='5'>";
                    $content .=  "<div class='order-option'>"
                        . "You have "
                        . "<span class='order-qty'>"
                        . "<span class='order-qty-down' onclick='queueQtyDown_{$blkid}(" . $option['id'] . ");'>-</span>"
                        . "<input id='queue_quantity_" . $option['id'] . "' name='order_quantity_" . $option['id'] . "' "
                            . "value='" . $option['queue_quantity'] . "' "
                            . "onkeyup='queueQtyChange_{$blkid}(" . $option['id'] . ");' "
                            . "onchange='queueQtyChange_{$blkid}(" . $option['id'] . ");' "
                            . "/>"
                        . "<span class='order-qty-up' onclick='queueQtyUp_{$blkid}(" . $option['id'] . ");'>+</span>"
                        . "</span>"
                        . " in your queue"
                        . "</div>";
                    $js_variables['queue_quantity_' . $option['id']] = $option['queue_quantity'];
                    $content .= "</td></tr>";
                }
            }
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
            . "var org_val_{$blkid}={"
            . "";
        foreach($js_variables as $k => $v) {
            $ciniki['request']['inline_javascript'] .= "'$k':'$v',";
        }
        $ciniki['request']['inline_javascript'] .= ""
            . "};"
            . "function favToggle_{$blkid}(id){"
                . "var e=C.gE('fav_' + id);"
                . "if(e.classList.contains('fav-on')){"
                    . "C.getBg('" . $api_fav_off . "' + id,null,function(r){"
                        . "if(r.stat!='ok'){"
                            . "alert('Oops, we had a problem removing the favourite. Please try again or contact us for help.');"
                        . "}else{"
                            . "e.classList.remove('fav-on');"
                            . "e.classList.add('fav-off');"
                            . "e.innerHTML = '" . $heart_off . "';"
                        . "}"
                    . "});"
                . "}else{"
                    . "C.getBg('" . $api_fav_on . "' + id,null,function(r){"
                        . "if(r.stat!='ok'){"
                            . "alert('Oops, we had a problem adding the favourite. Please try again or contact us for help.');"
                        . "}else{"
                            . "e.classList.remove('fav-off');"
                            . "e.classList.add('fav-on');"
                            . "e.innerHTML = '" . $heart_on . "';"
                        . "}"
                    . "});"
                . "}"
            . "}" 
            . "function orderToggle_{$blkid}(id){"
                . "var e=C.gE('order_option_' + id);"
                . "if(e.classList.contains('order-show')){"
                    . "e.classList.remove('order-show');"
                    . "e.classList.add('order-hide');"
                . "}else{"
                    . "e.classList.remove('order-hide');"
                    . "e.classList.add('order-show');"
                . "}"
                . "var e=C.gE('repeat_option_' + id);"
                . "if(e!=null){"
                    . "if(e.classList.contains('order-show')){"
                        . "e.classList.remove('order-show');"
                        . "e.classList.add('order-hide');"
                    . "}else{"
                        . "e.classList.remove('order-hide');"
                        . "e.classList.add('order-show');"
                    . "}"
                . "}"
            . "}"
            . "function orderQtyUp_{$blkid}(id){"
                . "var e=C.gE('order_quantity_' + id);"
                . "if(e.value==''){"
                    . "return true;"
//                    . "e.value=1;"
                . "}else{"
                    . "e.value=parseInt(e.value)+1;"
                . "}"
                . "orderQtyChange_{$blkid}(id);"
            . "}"
            . "function orderQtyDown_{$blkid}(id){"
                . "var e=C.gE('order_quantity_' + id);"
                . "if(parseInt(e.value)<1){"
                    . "e.value=0;"
                . "}else if(e.value==''){"
                    . "return true;"
                    . "e.value=1;"
                . "}else{"
                    . "e.value=parseInt(e.value)-1;"
                . "}"
                . "orderQtyChange_{$blkid}(id);"
            . "}"
            . "function orderQtyChange_{$blkid}(id){"
                . "var e=C.gE('order_quantity_' + id);"
                . "var e2=C.gE('repeat_quantity_' + id);"
                . "var icn=C.gE('option_' + id).firstElementChild;"
                . "if(e.value!=org_val_{$blkid}['order_quantity_'+id]){"
                    . "C.getBg('" . $api_order_update . "'+id,{'quantity':e.value},function(r){"
                        . "if(r.stat=='noavail'){"
                            . "e.value=org_val_{$blkid}['order_quantity_'+id];"
                            . "alert(\"We're sorry, but there are no more available.\");"
                            . "return false;"
                        . "}else if(r.stat!='ok'){"
                            . "e.value=org_val_{$blkid}['order_quantity_'+id];"
                            . "alert('We had a problem updating your order. Please try again or contact us for help.');"
                            . "return false;"
                        . "}"
                        . "org_val_{$blkid}['order_quantity_'+id]=e.value;"
                        . "if(e.value>0&&!icn.classList.contains('order-options-order-on')){"
                            . "icn.classList.add('order-options-order-on');"
                        . "}else if(e.value==0&&icn.classList.contains('order-options-order-on')){"
                            . "icn.classList.remove('order-options-order-on');"
                        . "}"
                    . "});"
                . "}"
            . "}"
            . "function repeatQtyUp_{$blkid}(id){"
                . "var e=C.gE('repeat_quantity_' + id);"
                . "if(e.value==''){"
                    . "return true;"
//                    . "e.value=1;"
                . "}else{"
                    . "e.value=parseInt(e.value)+1;"
                . "}"
                . "repeatChange_{$blkid}(id);"
            . "}"
            . "function repeatQtyDown(id){"
                . "var e=C.gE('repeat_quantity_' + id);"
                . "if(parseInt(e.value)<1){"
                    . "e.value=0;"
                . "}else if(e.value==''){"
                    . "return true;"
//                    . "e.value=1;"
                . "}else{"
                    . "e.value=parseInt(e.value)-1;"
                . "}"
                . "repeatChange_{$blkid}(id);"
            . "}"
            . "function repeatSkip_{$blkid}(id){"
                . "repeatChange_{$blkid}(id,'yes');"
            . "}"
            . "function repeatChange_{$blkid}(id,skip){"
                . "var e=C.gE('repeat_quantity_' + id);"
                . "var d=C.gE('repeat_days_' + id);"
                . "var e3=C.gE('repeat_option_next_' + id);"
                . "var icn=C.gE('option_' + id).firstElementChild;"
                . "var args={};"
                . "if(e.value!=org_val_{$blkid}['repeat_quantity_'+id]){"
                    . "args['quantity']=e.value;"
                . "}"
                . "if(d.value!=org_val_{$blkid}['repeat_days_'+id]){"
                    . "args['repeat_days']=d.value;"
                . "}"
                . "if(skip!=null&&skip=='yes'){"
                    . "args['skip']='yes';"
                . "}"
                . "C.getBg('" . $api_repeat_update . "'+id,args,function(r){"
                    . "if(r.stat=='noavail'){"
                        . "e.value=org_val_{$blkid}['order_quantity_'+id];"
                        . "alert(\"We're sorry, but there are no more available.\");"
                        . "return false;"
                    . "}else if(r.stat!='ok'){"
                        . "e.value=org_val_{$blkid}['repeat_quantity_'+id];"
                        . "alert('We had a problem updating your standing order. Please try again or contact us for help.');"
                        . "return false;"
                    . "}"
                    . "if(r.item.next_order_date_text!=null){"
                        . "C.gE('repeat_date_next_'+id).innerHTML=r.item.next_order_date_text;"
                    . "}"
                    . "if(r.item.quantity!=null){"
                        . "org_val_{$blkid}['repeat_quantity_'+id]=r.item.quantity;"
                    . "}"
                    . "if(r.item.repeat_days!=null){"
                        . "org_val_{$blkid}['repeat_days_'+id]=r.item.repeat_days;"
                    . "}"
                    . "if(parseFloat(e.value)>0&&!icn.classList.contains('order-options-order-repeat')){"
                        . "icn.classList.add('order-options-order-repeat');"
                    . "}else if(e.value==0&&icn.classList.contains('order-options-order-repeat')){"
                        . "icn.classList.remove('order-options-order-repeat');"
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
            . "function queueQtyUp_{$blkid}(id){"
                . "var e=C.gE('queue_quantity_' + id);"
                . "if(e.value==''){"
                    . "return true;"
//                    . "e.value=1;"
                . "}else{"
                    . "e.value=parseInt(e.value)+1;"
                . "}"
                . "queueQtyChange_{$blkid}(id);"
            . "}"
            . "function queueQtyDown_{$blkid}(id){"
                . "var e=C.gE('queue_quantity_' + id);"
                . "if(parseInt(e.value)<1){"
                    . "e.value=0;"
                . "}else if(e.value==''){"
                    . "return true;"
                    . "e.value=1;"
                . "}else{"
                    . "e.value=parseInt(e.value)-1;"
                . "}"
                . "queueQtyChange_{$blkid}(id);"
            . "}"
            . "function queueQtyChange_{$blkid}(id){"
                . "var e=C.gE('queue_quantity_' + id);"
                . "var icn=C.gE('option_' + id).firstElementChild;"
                . "if(e.value!=org_val_{$blkid}['queue_quantity_'+id]){"
                    . "C.getBg('" . $api_queue_update . "'+id,{'quantity':e.value},function(r){"
                        . "var diff=(e.value-org_val_{$blkid}['queue_quantity_'+id]);"
                        . "var t=parseInt(org_val_{$blkid}['queue_slots_total_'+id]);"
                        . "var f=parseInt(org_val_{$blkid}['queue_slots_filled_'+id]);"
                        . "f+=parseInt(diff);"
                        . "if(f<=0) {"
                            . "while(f<0){f+=t;}"
                            . "if(f<=0&&(e.value>0||parseInt(org_val_{$blkid}['queue_size_other_'+id])>0)){f=t;}"
                        . "}else if(f>t){"
                            . "f=f%t;"
                            . "if(f==0){f=t;}"
                        . "}"
                        . "org_val_{$blkid}['queue_slots_filled_'+id]=f;"
                        . "var h='';"
//                        . "console.log(org_val_{$blkid}['queue_slots_total_'+id]);"
                        . "for(var i=1;i<=t;i++){"
                            . "if(i<=f){"
                                . "h+='$queue_slot_filled';"
                            . "}else{"
                                . "h+='$queue_slot_open';"
                            . "}"
                        . "}"
                        . "C.gE('q_'+id).innerHTML = h;"
                        . "if(r.stat=='noavail'){"
                            . "e.value=org_val_{$blkid}['order_quantity_'+id];"
                            . "alert(\"We're sorry, but there are no more available.\");"
                            . "return false;"
                        . "}else if(r.stat!='ok'){"
                            . "e.value=org_val_{$blkid}['queue_quantity_'+id];"
                            . "alert('We had a problem updating your queue. Please try again or contact us for help.');"
                            . "return false;"
                        . "}"
                        . "org_val_{$blkid}['queue_quantity_'+id]=e.value;"
                        . "if(e.value>0&&!icn.classList.contains('order-options-order-queue')){"
                            . "icn.classList.add('order-options-order-queue');"
                        . "}else if(e.value==0&&icn.classList.contains('order-options-order-queue')){"
                            . "icn.classList.remove('order-options-order-queue');"
                        . "}"
                    . "});"
                . "}"
            . "}"
            . "</script>";
    }
    
    return array('stat'=>'ok', 'content'=>$content);
}
?>
