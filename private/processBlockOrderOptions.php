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
function ciniki_web_processBlockOrderOptions(&$ciniki, $settings, $business_id, $block) {

    $heart_off = '<span class="fa-icon order-icon order-options-fav-off">&#xf08a;</span>';
    $heart_on = '<span class="fa-icon order-icon order-options-fav-on">&#xf004;</span>';
    $order_off = '<span class="fa-icon order-icon order-options-order-on">&#xf217;</span>';
    $order_on = '<span class="fa-icon order-icon order-options-order-on">&#xf217;</span>';

    $content = '';

    //
    // Get business/user settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'intlSettings');
    $rc = ciniki_businesses_intlSettings($ciniki, $business_id);
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
        foreach($block['options'] as $oid => $option) {
            $content .= "<tr class='order-options-item'>";
            $content .= "<td class='name'>" . $option['name'] . "</td>";
            $content .= "<td class='price alignright'>" . $option['price_text'] . "</td>";
            if( isset($ciniki['session']['customer']['id']) && $ciniki['session']['customer']['id'] > 0 ) {
                if( isset($option['favourite']) && $option['favourite'] == 'yes' ) {
                    if( isset($option['favourite_value']) && $option['favourite_value'] == 'on' ) {
                        $content .= "<td id='fav_" . $option['id'] . "' class='clickable aligncenter fav-on' onclick='favToggle(" . $option['id'] . ");'>" . $heart_on . "</td>";
                    } else {
                        $content .= "<td id='fav_" . $option['id'] . "' class='clickable aligncenter fav-off' onclick='favToggle(" . $option['id'] . ");'>" . $heart_off . "</td>";
                    }
                } else {
                    $content .= "<td></td>";
                }
                if( (isset($option['available']) && $option['available'] == 'yes' && isset($option['order_quantity']) && $option['order_quantity'] > 0)
                    || (isset($option['repeat']) && $option['repeat'] == 'yes' && isset($option['repeat_quantity']) && $option['repeat_quantity'] > 0)
                    || (isset($option['queue']) && $option['queue'] == 'yes' && isset($option['queue_quantity']) && $option['queue_quantity'] > 0)
                    ) {
                    $content .= "<td id='option_" . $option['id'] . "' class='clickable aligncenter' onclick='orderToggle(" . $option['id'] . ");'>" . $order_on . "</td>";
                } 
                elseif( (isset($option['available']) && $option['available'] == 'yes') 
                    || (isset($option['repeat']) && $option['repeat'] == 'yes') 
                    || (isset($option['queue']) && $option['queue'] == 'yes') 
                    ) {
                    $content .= "<td id='option_" . $option['id'] . "' class='clickable aligncenter' onclick='orderToggle(" . $option['id'] . ");'>" . $order_off . "</td>";
                } else {
                    $content .= "<td></td>";
                }
            }
            $content .= "</tr>";

            //
            // Check if cart should be shown
            //
            if( isset($ciniki['session']['customer']['id']) && $ciniki['session']['customer']['id'] > 0 ) {
                if( (isset($option['available']) && $option['available'] == 'yes') 
                    || (isset($option['repeat']) && $option['repeat'] == 'yes') 
                    || (isset($option['queue']) && $option['queue'] == 'yes')
                    ) {
                error_log('testing');
                    $content .= "<tr id='order_option_" . $option['id'] . "' class='order-options-order order-hide'><td colspan='4'>";
                    if( isset($option['available']) && $option['available'] == 'yes' 
                        && isset($ciniki['session']['ciniki.poma']['date']['order_date_text'])
                        ) {
                        $content .=  "<div class='order-option'>Order "
                            . "<span class='order-qty'>"
                            . "<span class='order-qty-down' onclick='orderQtyDown(" . $option['id'] . ");'>-</span>"
    //                        . "<input type='number' pattern='[0-9]' min='0' step='1' id='order_quantity_" . $option['id'] . "' name='order_quantity_" . $option['id'] . "' value='" . $option['order_quantity'] . "' editable=false/>"
                            . "<input id='order_quantity_" . $option['id'] . "' name='order_quantity_" . $option['id'] . "' "
                                . "value='" . $option['order_quantity'] . "' "
                                . "old_value='" . $option['order_quantity'] . "' "
                                . "onkeyup='orderQtyChange(" . $option['id'] . ");' "
                                . "onchange='orderQtyChange(" . $option['id'] . ");' "
                                . "/>"
                            . "<span class='order-qty-up' onclick='orderQtyUp(" . $option['id'] . ");'>+</span>"
                            . "</span>"
                            . " on " . $ciniki['session']['ciniki.poma']['date']['order_date_text']
                            . "</div>";
                    }
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
            . "function favToggle(id){"
                . "var e=C.gE('fav_' + id);"
                . "if(e.classList.contains('fav-on')){"
                    . "C.getBg('" . $api_fav_off . "' + id,null,function(){"
                        . "e.classList.remove('fav-on');"
                        . "e.classList.add('fav-off');"
                        . "e.innerHTML = '" . $heart_off . "';"
                    . "});"
                . "}else{"
                    . "C.getBg('" . $api_fav_on . "' + id,null,function(){"
                        . "e.classList.remove('fav-off');"
                        . "e.classList.add('fav-on');"
                        . "e.innerHTML = '" . $heart_on . "';"
                    . "});"
                . "}"
            . "}" 
            . "function orderToggle(id){"
                . "var e=C.gE('order_option_' + id);"
                . "if(e.classList.contains('order-show')){"
                    . "e.classList.remove('order-show');"
                    . "e.classList.add('order-hide');"
                . "}else{"
                    . "e.classList.remove('order-hide');"
                    . "e.classList.add('order-show');"
                . "}"
            . "}"
            . "function orderQtyUp(id){"
                . "var e=C.gE('order_quantity_' + id);"
                . "if(e.value==''){"
                    . "e.value=1;"
                . "}else{"
                    . "e.value=parseInt(e.value)+1;"
                . "}"
                . "orderQtyChange(id);"
            . "}"
            . "function orderQtyDown(id){"
                . "var e=C.gE('order_quantity_' + id);"
                . "if(parseInt(e.value)<1){"
                    . "e.value=0;"
                . "}else if(e.value==''){"
                    . "e.value=1;"
                . "}else{"
                    . "e.value=parseInt(e.value)-1;"
                . "}"
                . "orderQtyChange(id);"
            . "}"
            . "function orderQtyChange(id){"
                . "var e=C.gE('order_quantity_' + id);"
                . "if(e.value!=e.old_value){"
                    . "C.getBg('" . $api_order_update . "'+id,{'quantity':e.value},function(){"
                        . "console.log('updated');"
                    . "});"
                . "}"
                . "console.log('change');"
            . "}"
            . "</script>";
    }
    
    return array('stat'=>'ok', 'content'=>$content);
}
?>
