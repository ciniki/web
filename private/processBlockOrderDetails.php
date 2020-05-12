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
function ciniki_web_processBlockOrderDetails(&$ciniki, $settings, $tnid, $block) {

    $heart_off = '<span class="fa-icon order-icon order-options-fav-off">&#xf08a;</span>';
    $heart_on = '<span class="fa-icon order-icon order-options-fav-on">&#xf004;</span>';
    $order_off = '<span class="fa-icon order-icon order-options-order-on">&#xf217;</span>';
    $order_on = '<span class="fa-icon order-icon order-options-order-on">&#xf217;</span>';

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
    $intl_currency_fmt = numfmt_create($rc['settings']['intl-default-locale'], NumberFormatter::CURRENCY);
    $intl_currency = $rc['settings']['intl-default-currency'];

    //
    // Setup the api endpoints and submit urls
    //
    $api_item_update = (isset($block['api_item_update']) ? $block['api_item_update'] : '');

    //
    // Check for block title
    //
    if( isset($block['title']) && $block['title'] != '' ) {
        $content .= "<h2" . ((isset($block['size'])&&$block['size']!='') ? " class='" . $block['size'] . "'" : '') . ">" . $block['title'] . "</h2>";
    }

    $js_variables = array();

    //
    // Make sure there is content to edit
    //
    if( isset($block['order']) ) {
        $content .= "<div class='order-details" . ((isset($block['size'])&&$block['size']!='') ? ' ' . $block['size'] : '') . "'>";
        $content .= "<table class='order-details'>";
        $content .= "<thead><tr><th>Item</th><th>Qty</th><th></th><th>Price</th><th>Total</th></tr></thead>";
        if( isset($block['order']['items']) && count($block['order']['items']) > 0 ) {
            $content .= "<tbody>";   
            foreach($block['order']['items'] as $item) {
                $subitem_text = '';
                if( isset($item['subitems']) ) {
                    ciniki_core_loadMethod($ciniki, 'ciniki', 'poma', 'private', 'formatItems');
                    $rc = ciniki_poma_formatItems($ciniki, $tnid, $item['subitems']);
                    if( $rc['stat'] != 'ok' ) {
                        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.184', 'msg'=>'Unable to load order', 'err'=>$rc['err']));
                    }
                    $subitems = isset($rc['items']) ? $rc['items'] : array();
                    foreach($subitems as $subitem) {
                        if( $subitem['quantity'] > 0 ) {
                            if( $subitem['quantity'] > 1 ) {
                                $subitem_text .= $subitem['quantity'] 
                                    . ($subitem['quantity_plural'] != '' ? ' ' . $subitem['quantity_plural'] : '');
                            } else {
                                $subitem_text .= $subitem['quantity']
                                    . ($subitem['quantity_single'] != '' ? ' ' . $subitem['quantity_single'] : '');
                            }
                            $subitem_text .= ' - ' . $subitem['description'] . '<br/>';
                        }
                    }
                }
                if( $subitem_text != '' ) {
                    $subitem_text = '<div class="subitems">' . $subitem_text . '</div>';
                }
                $sub_button = '';
                if( isset($item['substitutions']) && $item['substitutions'] == 'yes' ) {
                    $sub_button = "<span class='order-details-substitutions'><a href='" . $block['base_url'] . "/substitutions/" . $item['id'] . "'>Customize</a></span>";
                }
                $content .= "<tr id='order_item_" . $item['id'] . "'>";
                if( isset($item['code']) && $item['code'] != '' && isset($item['description']) && $item['description'] != '' ) {
                    $content .= "<td>" . $item['code'] . ' - ' . $item['description'] . " $sub_button$subitem_text</td>";
                } elseif( $item['code'] != '' ) {
                    $content .= "<td>" . $item['code'] . " $sub_button$subitem_text</td>";
                } elseif( $item['description'] != '' ) {
                    $content .= "<td>" . $item['description'] . " $sub_button$subitem_text</td>";
                } else {
                    $content .= "<td>$sub_button$subitem_text</td>";
                }
                if( isset($block['order']['editable']) && $block['order']['editable'] == 'yes' 
                    && isset($item['modifications']) && $item['modifications'] == 'yes'
                    ) {
                    $content .= "<td>"
                        . "<span class='order-qty'>"
                        . "<span class='order-qty-down' onclick='orderQtyDown(" . $item['id'] . ");'>-</span>"
                        . "<input id='order_item_quantity_" . $item['id'] . "' name='order_item_quantity_" . $item['id'] . "' "
                            . "value='" . $item['quantity'] . "' "
                            . "old_value='" . $item['quantity'] . "' "
                            . "onkeyup='orderQtyChange(" . $item['id'] . ");' "
                            . "onchange='orderQtyChange(" . $item['id'] . ");' "
                            . "/>"
                        . "<span class='order-qty-up' onclick='orderQtyUp(" . $item['id'] . ");'>+</span>"
                        . "</span>"
                        . "</td>"
                        . "<td>"
                        . ($item['quantity'] > 1 ? ' ' . $item['quantity_plural'] : ' ' . $item['quantity_single'])
                        . "</td>";
                } else {
                    $content .= "<td>" . (float)$item['quantity']
                        . "</td><td>"
                        . ($item['quantity'] > 1 ? ' ' . $item['quantity_plural'] : ' ' . $item['quantity_single'])
                        . "</td>";
                }
                $js_variables['order_item_quantity_' . $item['id']] = (float)$item['quantity'];
                if( isset($item['flags']) && ($item['flags']&0x0200) == 0x0200 ) {
                    //
                    // Prepaid item, hide the price and total
                    //
                    $content .= "<td><span id='order_item_price_" . $item['id'] . "' style='display: none;'></span></td>"
                        . "<td><span id='order_item_total_" . $item['id'] . "' style='display: none;'></span></td>";
                } else {
                    $content .= "<td id='order_item_price_" . $item['id'] . "'>" . $item['price_text'] 
                        . ((isset($item['discount_text']) && $item['discount_text'] != '') ? '<span class="discount-text">' . $item['discount_text'] . '</span>' : '')
                        . ((isset($item['deposit_text']) && $item['deposit_text'] != '') ? '<span class="deposit-text">' . $item['deposit_text'] . '</span>' : '')
                        . "</td>";
                    $content .= "<td><span id='order_item_total_" . $item['id'] . "'>" . $item['total_text'] . "</span>"
                        . "<span class='tax-text'>" . $item['tax_text'] . "</span></td>";
                }
                $content .= "</tr>";
            }
            $content .= "</tbody>";
        }
        $content .= "<tfoot>";
//        if( isset($block['order']['taxes_amount']) && $block['order']['taxes_amount'] > 0 ) {
//        }
        $content .= "<tr" . ($block['order']['taxes_amount'] > 0 ? '': " style='display:none;'") . "><td colspan='3'></td><td>Taxes</td>"
            . "<td id='order_taxes'>$" . number_format($block['order']['taxes_amount'], 2, '.', ',') . "</td>"
            . "</tr>";
        $content .= "<tr><td colspan='3'></td><td>Total</td>"
            . "<td id='order_total'>$" . number_format($block['order']['total_amount'], 2, '.', ',') . "</td>"
            . "</tr>";

        $content .= "</tfoot>";
        $content .= "</table>";
    }

//    $content .= "<pre class='wide'>" . print_r($block['order'], true) . "</pre>";

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
    $ciniki['request']['inline_javascript'] .= "};"
        . "function orderQtyUp(id){"
            . "var e=C.gE('order_item_quantity_' + id);"
            . "if(e.value==''){"
                . "return true;"
                . "e.value=1;"
            . "}else{"
                . "e.value=parseInt(e.value)+1;"
            . "}"
            . "orderQtyChange(id);"
        . "}"
        . "function orderQtyDown(id){"
            . "var e=C.gE('order_item_quantity_' + id);"
            . "if(parseInt(e.value)<1){"
                . "e.value=0;"
            . "}else if(e.value==''){"
                . "return true;"
                . "e.value=1;"
            . "}else{"
                . "e.value=parseInt(e.value)-1;"
            . "}"
            . "orderQtyChange(id);"
        . "}"
        . "function orderQtyChange(id){"
            . "var e=C.gE('order_item_quantity_' + id);"
            . "var a=document.activeElement;"
            . "if(e.value==''){"
                . "if(a==e){"
                    . "return true;"
                . "}else{"
                    . "e.value=0;"
                . "}"
            . "}"
            . "if(e.value!=org_val['order_item_quantity_'+id]){"
                . "C.getBg('" . $api_item_update . "'+id,{'quantity':e.value},function(r){"
                    . "if(r.stat=='noavail'){"
                        . "e.value=org_val['order_item_quantity_' + id];"
                        . "alert(\"We're sorry, but there are no more available.\");"
                        . "return false;"
                    . "} else if(r.stat!='ok'){"
                        . "e.value=org_val['order_item_quantity_' + id];"
                        . "alert('We had a problem updating your order. Please try again or contact us for help.');"
                        . "return false;"
                    . "}"
                    . "org_val['order_item_quantity_'+id]=e.value;"
                    . "if(r.order!=null&&r.order.items!=null&&r.order.items[id]!=null){"
                        . "var i=r.order.items[id];"
                        . "C.gE('order_item_price_'+id).innerHTML=i.price_html;"
                        . "C.gE('order_item_total_'+id).innerHTML=i.total_text;"
                        . "C.gE('order_taxes').innerHTML=r.order.taxes_amount;"
                        . "C.gE('order_taxes').parentNode.style.display=(r.order.taxes_amount>0?'':'none');"
                        . "C.gE('order_total').innerHTML=r.order.total_text;"
                    . "}else if(parseInt(e.value)==0){"
                        . "var f=C.gE('order_item_'+id);"
                        . "f.parentNode.removeChild(f);"
                        . "if(r.order!=null){"
                            . "C.gE('order_taxes').innerHTML=r.order.taxes_amount;"
                            . "C.gE('order_taxes').parentNode.style.display=(r.order.taxes_amount>0?'':'none');"
                            . "C.gE('order_total').innerHTML=r.order.total_text;"
                        . "}"
                    . "}"
                . "});"
            . "}"
        . "}"
        . "</script>";
    
    return array('stat'=>'ok', 'content'=>$content);
}
?>
