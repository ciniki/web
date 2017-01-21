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
function ciniki_web_processBlockOrderSubstitutions(&$ciniki, $settings, $business_id, $block) {

    $content = '';
    $show_prices = 'no';

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
    $api_substitution_add = (isset($block['api_substitution_add']) ? $block['api_substitution_add'] : '');
    $api_substitution_update = (isset($block['api_substitution_update']) ? $block['api_substitution_update'] : '');

    //
    // Check for block title
    //
    if( isset($block['title']) && $block['title'] != '' ) {
        $content .= "<h2" . ((isset($block['size'])&&$block['size']!='') ? " class='" . $block['size'] . "'" : '') . ">" . $block['title'] . "</h2>";
    }

    //
    // Make sure there is content to edit
    //
    $content .= "<div class='order-substitutions" . ((isset($block['size'])&&$block['size']!='') ? ' ' . $block['size'] : '') . "'>";
    $content .= "<table class='order-substitutions order-substitutions-items'>";
    $content .= "<thead><tr><th>Item</th><th>Qty</th><th></th>";
    if( $show_prices == 'yes' ) {
        $content .= "<th>Price</th><th>Total</th>";
    }
    $content .= "</tr></thead>";
    $content .= "<tbody id='order_items'>";   
    $json_item = "var item={'subitems':[";
    $total_amount = 0;
    if( isset($block['subitems']) && count($block['subitems']) > 0 ) {
        $count = 0;
        foreach($block['subitems'] as $item) {
            $json_item .= "{"
                . "'id':'" . $item['id'] . "',"
                . "'description':'" . $item['description'] . "',"
                . "'unit_amount':'" . $item['unit_amount'] . "',"
                . "'quantity':'" . $item['quantity'] . "',"
                . "'quantity_plural':'" . $item['quantity_plural'] . "',"
                . "'quantity_single':'" . $item['quantity_single'] . "',"
                . "},";
            $total_amount = bcadd($total_amount, bcmul($item['unit_amount'], $item['quantity'], 6), 2);
        }
    }
    $json_item .= "],";
    $content .= "</tbody>";
    $content .= "</table>";

    //
    // Add the list of substitutions
    //
    $json_item .= "'subs':[";
    if( isset($block['substitutions']) && count($block['substitutions']) > 0 ) {
        $content .= "<h2" . ((isset($block['size'])&&$block['size']!='') ? " class='" . $block['size'] . "'" : '') . ">Add Items</h2>";
        $content .= "<p>Choose items you would like to substitute.</p>";
        $content .= "<div class='order-substitutions" . ((isset($block['size'])&&$block['size']!='') ? ' ' . $block['size'] : '') . "'>";
        $content .= "<table class='order-substitutions order-substitutions-subs'>";
        $content .= "<thead><tr><th>Item</th><th></th></tr></thead>";
        $content .= "<tbody id='order_subs'>";   
        $count = 0;
        foreach($block['substitutions'] as $sub) {
            $json_item .= "{"
                . "'id':'" . $count++ . "',"
                . "'object':'" . $sub['object'] . "',"
                . "'object_id':'" . $sub['object_id'] . "',"
                . "'description':'" . $sub['description'] . "',"
                . "'unit_amount':'" . $sub['unit_amount'] . "',"
                . "'quantity_plural':'" . $sub['quantity_plural'] . "',"
                . "'quantity_single':'" . $sub['quantity_single'] . "',"
                . "},";
        }
        $content .= "</tbody>";
        $content .= "</table>";
    }
    $json_item .= "],"
        . "'curtotal':" . $total_amount . ","
        . "'limit':" . $block['limit_total'] . ","
        . "'available':" . bcsub($block['limit_total'], $total_amount, 2) . ","
        . "};"
        . "";

//    $content .= "<pre class='wide'>" . print_r($block['order'], true) . "</pre>";

    //
    // Add javascript
    //
    if( !isset($ciniki['request']['inline_javascript']) ) {
        $ciniki['request']['inline_javascript'] = '';
    }
    $ciniki['request']['ciniki_api'] = 'yes';
    $ciniki['request']['inline_javascript'] .= "<script type='text/javascript'>"
        . $json_item
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
            . "var old_value=item.subitems[id].quantity;"
            . "if(e.value!=old_value){"
                . "var nv=e.value;"
                . "e.value=old_value;"
                . "C.getBg('" . $api_substitution_update . "'+item.subitems[id].id,{'quantity':nv},function(r){"
                    . "if(r.stat!='ok'){"
                        . "alert(r.err.msg);"
                        . "return false;"
                    . "}"
                    . "if(r.item!=null){"
                        . "item=r.item;"
                    . "}"
                    . "orderSubsUpdate();"
                . "});"
            . "}" 
        . "}"
        . "function orderSubAdd(id){"
            . "if(item.subs[id]!=null){"
                . "C.getBg('" . $api_substitution_add . "'+item.subs[id].object+'/'+item.subs[id].object_id,{},function(r){"
                    . "if(r.stat!='ok'){"
                        . "alert(r.err.msg);"
                        . "return false;"
                    . "}"
                    . "if(r.item!=null){"
                        . "item=r.item;"
                    . "}"
                    . "orderSubsUpdate();"
                . "});"
            . "}"
        . "}"
        . "function orderSubsUpdate(){" 
            . "var t=C.gE('order_items');" // The tbody containing the order items
            . "t.innerHTML='';"
            . "var cheapest=999;"
            . "console.log(item);"
            . "for(var i in item.subitems) {"
                . "console.log(item);"
                . "if(item.subitems[i].unit_amount<cheapest){"
                    . "cheapest=item.subitems[i].unit_amount;"
                . "}"
                . "var qb = \"<span class='order-qty'>"
                    . "<span class='order-qty-down' onclick='orderQtyDown(\" + i + \");'>\"+(parseFloat(item.subitems[i].quantity)>0?'-':'')+\"</span>"
                    . "<input id='order_item_quantity_\" + i + \"' "
                        . "value='\" + item.subitems[i].quantity + \"' "
                        . "old_value='\" + item.subitems[i].quantity + \"' "
                        . "onkeyup='orderQtyChange(\" + i + \");' "
                        . "onchange='orderQtyChange(\" + i + \");' "
                        . "/>"
                    . "<span class='order-qty-up' onclick='orderQtyUp(\" + i + \");'>\"+(parseFloat(item.subitems[i].unit_amount)<parseFloat(item.available)?'+':'')+\"</span>"
                    . "</span>\";"
                . "var r=C.aE('tr');"
                . "r.appendChild(C.aE('td',null,null,item.subitems[i].description));"
                . "r.appendChild(C.aE('td',null,null,qb));"
                . "r.appendChild(C.aE('td',null,null,(item.subitems[i].quantity>1?item.subitems[i].quantity_plural:item.subitems[i].quantity_single)));"
                . "t.appendChild(r);"
            . "}"
            . "var t=C.gE('order_subs');"       // The tbody containing the subs
            . "if(t!=null){"
                . "t.innerHTML='';"
                . "for(var i in item.subs) {"
                    . "if(item.subs[i].unit_amount<cheapest){"
                        . "cheapest=item.subs[i].unit_amount;"
                    . "}"
                    . "var ab='';"
                    . "if(parseFloat(item.available)>parseFloat(item.subs[i].unit_amount)){"
                        . "ab=\"<span class='order-button'><button onclick='orderSubAdd(\" + i + \");'>Add</button></span>\";"
                    . "}"
                    . "var r=C.aE('tr');"
                    . "r.appendChild(C.aE('td',null,null,item.subs[i].description));"
                    . "r.appendChild(C.aE('td',null,null,ab));"
                    . "t.appendChild(r);"
                . "}"
            . "}"
        . "}"
        . "window.onload = function(){orderSubsUpdate();};"
        . "</script>";
    
    return array('stat'=>'ok', 'content'=>$content);
}
?>
