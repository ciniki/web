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
function ciniki_web_processBlockMappedTickets(&$ciniki, $settings, $tnid, $block) {

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
    // Make sure there is content to edit
    //
    if( $block['mappedtickets'] != '' ) {
        //
        // Process the price list
        //
        if( isset($block['title']) && $block['title'] != '' ) {
            $content .= "<h2>" . $block['title'] . "</h2>";
        }
        $ticketmap = "<div id='ticketmap1-background' class='ticketmap-background' style='display: none;' >";
        $ticketmap .= "</div>";
        $ticketmap .= "<div id='ticketmap1-wrap' class='ticketmap-wrap' style='display: none;' >";
        $ticketmap .= "<div class='ticketmap'>";
        $ticketmap .= "<div class='ticketmap-image'>";
        
        //
        // Process the background image
        //
        if( isset($block['image_id']) && $block['image_id'] > 0 ) {
            //
            // Get the size of the image
            //
            $rc = ciniki_web_getScaledImageURL($ciniki, $block['image_id'], 'original', 0, 0);
            if( $rc['stat'] == 'ok' ) {
                list($width, $height, $type, $attr) = getimagesize($rc['filename']);
                $ticketmap .= "<svg id='ticketmap1' viewbox='0 0 $width $height'>";
                $ticketmap .= "<image x='0' y='0' xlink:href='" . $rc['domain_url'] . "' width='$width' height='$height'/>";
            } else {
                $ticketmap .= "<svg id='ticketmap1' viewbox='0 0 1024 600'>";
            }
        } else {
            $ticketmap .= "<svg id='ticketmap1' viewbox='0 0 1024 600'>";
        }

        $font_size = 20;
        if( isset($block['mappedtickets'][0]['diameter']) ) {
            $font_size = $block['mappedtickets'][0]['diameter'];
        }
        $ticketmap .= "<style>.pricelabel {font: bold {$font_size}px sans-serif; color: #fff; text-align: center;}</style>"; 
        $num_available = 0;
        $js_tickets = "var tickets={";
        foreach($block['mappedtickets'] as $pid => $price) {
            if( ($price['webflags']&0x01) == 0x01 ) {
                continue;
            }
            $price['amount_display'] = '$' . number_format($price['unit_amount'], 2);
            if( ($price['webflags']&0x04) == 0 ) {
                $num_available++;
            }
            $js_tickets .= "{$price['price_id']}:" . json_encode($price) . ",";
            $ticketmap .= "<circle id='ticket_" . $price['price_id'] . "' "
                . "cx='" . $price['position_x'] . "' "
                . "cy='" . $price['position_y'] . "' "
                . "r='" . $price['diameter'] . "' "
                . "stroke='green' stroke-width='0' "
                . (($price['webflags']&0x04) == 0 ? "onclick='selectTicket({$price['price_id']});' " : "")
                . "fill='" . (($price['webflags']&0x04) == 0 ? 'blue' : 'red') . "' />";
            if( isset($price['position_num']) && $price['position_num'] != '' ) {
                $ticketmap .= "<text "
                    . "x='" . $price['position_x'] . "' "
                    . "y='" . $price['position_y'] . "' "
                    . "width='{$font_size}px' "
                    . "height='{$font_size}px' "
                    . "fill='#fff' "
                    . "dominant-baseline='middle' "
                    . "text-anchor='middle' "
                    . "class='pricelabel'>" 
                    . $price['position_num'] 
                    . "</text>";
                
            }
        }
        $js_tickets .= "};";
        $js_addons = "var addons={";
        foreach($block['addons'] as $pid => $price) {
            $js_addons .= "{$price['price_id']}:" . json_encode($price) . ",";
        }
        $js_addons .= "};";

        $ticketmap .= "</svg>";
        $ticketmap .= "</div>";
        $ticketmap .= "<form method='POST' action='" . $ciniki['request']['ssl_domain_base_url'] . "/cart' class='wide' onsubmit='return submitTickets(event);'>";
//        $ticketmap .= "<input type='hidden' name='action' value='addprices'>";
//        $ticketmap .= "<input type='hidden' name='object' value='" . $block['object'] . "'>";
//        $ticketmap .= "<input type='hidden' name='object_id' value='" . $block['object_id'] . "'>";
//        $ticketmap .= "<input type='hidden' id='price_ids' name='price_ids' value=''>";
//        $ticketmap .= "<input type='hidden' name='quantity' value='1'>";
        $ticketmap .= "<div class='ticketmap-tickets'>";
        $ticketmap .= "<h2>Cart</h2>";
        $ticketmap .= "<table class='ticketmap-tickets'>";
        $ticketmap .= "<tbody id='ticketmap1-tickets'>";
        $ticketmap .= "<tr><td colspan=3>" . (isset($block['empty-text']) && $block['empty-text'] != '' ? $block['empty-text'] : 'No tickets selected') . "</td></tr>";
        $ticketmap .= "</tbody>";
        $ticketmap .= "</table>";
        $ticketmap .= "</div>";
        if( isset($block['addons']) && count($block['addons']) > 0 ) {
            $ticketmap .= "<div class='ticketmap-addons'>";
            $ticketmap .= "<h2>Additional Options</h2>";
            $ticketmap .= "<table class='ticketmap-tickets'>";
            $ticketmap .= "<tbody>";
            foreach($block['addons'] as $addon) {
                $ticketmap .= "<tr><td>" . $addon['name'] . "</td>"
                    . "<td>$" . number_format($addon['unit_amount'], 2) . "</td>"
                    . "<td><a href='javascript:addItem({$addon['price_id']});'>Add</a></td>"
                    . "</tr>";
            }
            $ticketmap .= "</tbody>";
            $ticketmap .= "</table>";
            $ticketmap .= "</div>";
        }
        $ticketmap .= "<br/>";
        $ticketmap .= "<div class='ticketmap-buttons'>";
        $ticketmap .= "<div class='cart-buttons wide aligncenter'>";
        $ticketmap .= "<button class='cart-submit button' onclick='closeMap(event);'>Close</button>&nbsp;";
        $ticketmap .= "<input id='ticketmap-submit' class='cart-submit' type='submit' name='checkout' value='Checkout' />";
        $ticketmap .= "</div>";
        $ticketmap .= "</form>";
        $ticketmap .= "</div>";
        $ticketmap .= "</div>";

        if( $num_available > 0 ) {
            $content .= "<div class='order-options'>";
            $content .= (isset($block['intro-text']) && $block['intro-text'] != '' ? $block['intro-text'] : 'Purchase a ticket');
            $content .= "<button class='cart-submit' name='add' onclick='openMap();'>"
                . (isset($block['button-label']) && $block['button-label'] != '' ? $block['button-label'] : 'Select Ticket')
                . "</button>";
            $content .= "</div>";
            $content .= "<br/>";
            $content .= $ticketmap;

            // Enable the API
            $ciniki['request']['ciniki_api'] = 'yes';
            $api_cart_load = (isset($block['api_cart_load']) ? $block['api_cart_load'] : '/ciniki/sapos/cartLoad');
            $js = ""
                . $js_tickets
                . $js_addons
                . "var selectedTickets = [];"
                . "var selectedAddons = {};"
                . "function openMap() {"
                    // Load cart
                    . "C.getBg('ciniki/sapos/cartLoad',{},function(r){"
                        . "if(r.stat!='ok'&&r.stat!='noexist'){"
                            . "alert(\"We're sorry, but we were unable to load your cart.  Please try again or contact us for help.\");"
                            . "return false;"
                        . "}"
                        . "if(r.cart!=null&&r.cart.items!=null){"
                            . "for(var i in r.cart.items){"
                                . "if(r.cart.items[i].item.object=='" . $block['object'] . "'"
                                    . "&&r.cart.items[i].item.object_id=='" . $block['object_id'] . "'"
                                    . "){"
                                        . "if(tickets[r.cart.items[i].item.price_id]!=null){"
                                            . "tickets[r.cart.items[i].item.price_id].item_id=r.cart.items[i].item.id;"
                                            . "selectedTickets.push(parseInt(r.cart.items[i].item.price_id));"
                                            . "var e=document.getElementById('ticket_' + r.cart.items[i].item.price_id);"
                                            . "e.setAttribute('fill', 'green');"
                                        . "} else if(addons[r.cart.items[i].item.price_id]!=null){"
                                            . "addons[r.cart.items[i].item.price_id].item_id=r.cart.items[i].item.id;"
                                            . "selectedAddons[r.cart.items[i].item.price_id] = {'quantity':parseInt(r.cart.items[i].item.quantity)};"
                                        . "}"
                                . "}"
                            . "}"
                        . "}"
                        . "var e=document.getElementById('ticketmap1-background');"
                        . "e.style.display='block';" 
                        . "var e=document.getElementById('ticketmap1-wrap');"
                        . "e.style.display='block';" 
                        . "updateTickets();"
                        . "window.scrollTo(0, 0);"
                    . "});"
                . "};"
                . "function closeMap(evt) {"
                    . "evt.preventDefault();"
                    . "for(var i in selectedTickets) {"
                        . "document.getElementById('ticket_' + selectedTickets[i]).setAttribute('fill', 'blue');"
                    . "}"
                    . "selectedTickets = [];"
                    . "var e=document.getElementById('ticketmap1-background');"
                    . "e.style.display='none';" 
                    . "var e=document.getElementById('ticketmap1-wrap');"
                    . "e.style.display='none';" 
                    . "location.reload();"
                . "};"
                . "function addItem(tid) {"
                    . "if(addons[tid] != null){"
                        . "if(selectedAddons[tid]!=null){"
                            . "var t=addons[tid];"
                            . "C.getBg('ciniki/sapos/cartItemUpdate',{"
                                . "'item_id':t.item_id,"
                                . "'quantity':(selectedAddons[tid].quantity+1),"
                                . "},function(r){"
                                    . "if(r.stat!='ok'){"
                                        . "alert(\"We're sorry, we ran into a problem.  Please try again or contact us for help.\");"
                                        . "return false;"
                                    . "}"
                                    . "selectedAddons[tid].quantity++;"
                                    . "updateTickets();"
                                . "});"
                        . "}else{"
                            . "C.getBg('ciniki/sapos/cartItemAdd',{"
                                . "'object':'" . $block['object'] . "',"
                                . "'object_id':'" . $block['object_id'] . "',"
                                . "'quantity':'1',"
                                . "'price_id':tid,"
                                . "},function(r){"
                                    . "if(r.stat!='ok'){"
                                        . "alert(\"We're sorry, we ran into a problem.  Please try again or contact us for help.\");"
                                        . "return false;"
                                    . "}"
                                    . "selectedAddons[tid] = {'quantity':1};"
                                    . "addons[tid].item_id=r.id;"
                                    . "updateTickets();"
                                . "});"
                        . "}"
                    . "}"
                . "};"
                . "function removeItem(tid) {"
                    . "if(addons[tid] != null){"
                        . "if(selectedAddons[tid]!=null){"
                            . "var t=addons[tid];"
                            . "C.getBg('ciniki/sapos/cartItemDelete',{"
                                . "'item_id':t.item_id,"
                                . "},function(r){"
                                    . "if(r.stat!='ok'){"
                                        . "alert(\"We're sorry, we ran into a problem.  Please try again or contact us for help.\");"
                                        . "return false;"
                                    . "}"
                                    . "delete selectedAddons[tid];"
                                    . "addons[tid].item_id=0;"
                                    . "updateTickets();"
                                . "});"
                        . "}"
                    . "}"
                . "};"
                . "function selectTicket(tid) {"
                    . "var e=document.getElementById('ticket_' + tid);"
                    . "if(selectedTickets.indexOf(tid)==-1){"
                        // Add to cart
                        . "C.getBg('ciniki/sapos/cartItemAdd',{"
                            . "'object':'" . $block['object'] . "',"
                            . "'object_id':'" . $block['object_id'] . "',"
                            . "'quantity':'1',"
                            . "'price_id':tid,"
                            . "},function(r){"
                                . "if(r.stat!='ok'){"
                                    . "alert(\"We're sorry, we ran into a problem.  Please try again or contact us for help.\");"
                                    . "return false;"
                                . "}"
                                . "e.setAttribute('fill', 'green');"
                                . "selectedTickets.push(tid);"
                                . "tickets[tid].item_id=r.id;"
                                . "updateTickets();"
                            . "});"
                    . "}else{" 
                        // Remove from cart
                        . "C.getBg('ciniki/sapos/cartItemDelete',{"
                            . "'item_id':tickets[tid].item_id,"
                            . "},function(r){"
                                . "if(r.stat!='ok'){"
                                    . "alert(\"We're sorry, we ran into a problem.  Please try again or contact us for help.\");"
                                    . "return false;"
                                . "}"
                                . "selectedTickets.splice(selectedTickets.indexOf(tid), 1);"
                                . "e.setAttribute('fill', 'blue');"
                                . "tickets[tid].item_id=0;"
                                . "updateTickets();"
                            . "});"
                    . "}"
                . "};"
                . "function updateTickets() {"
                    . "var e=document.getElementById('ticketmap1-tickets');"
                    . "var h='';"
                    . "for(var i in selectedTickets) {"
                        . "var t=tickets[selectedTickets[i]];"
                        . "h+='<tr><td>'+t.name+'</td><td></td><td>'+t.amount_display+'</td><td><a href=\"javascript:selectTicket('+t.price_id+');\">Remove</a></td></tr>';"
                    . "}"
                    // Update the addons
                    . "for(var i in selectedAddons) {"
                        . "var t=addons[i];"
                        . "h+='<tr><td>'+t.name+'</td><td>' + selectedAddons[i].quantity + '</td><td>'+t.unit_amount_display+'</td><td><a href=\"javascript:removeItem('+t.price_id+');\">Remove</a></td></tr>';"
                    . "}"
                    . "if(h!=''){"
                        . "e.innerHTML=h;"
                    . "}else{"
                        . "e.innerHTML='<tr><td>" . (isset($block['empty-text']) && $block['empty-text'] != '' ? $block['empty-text'] : 'No tickets selected') . "</td></tr>'"
                    . "}"
                . "};"
                . "function submitTickets(evt) {"
                    . "if( selectedTickets.length <= 0 ) {"
                        . "alert('Nothing selected');"
                        . "return false;"
                    . "}"
                    . "return true; "
                . "};"
                . "";
        } elseif( count($block['mappedtickets']) > 0 ) {
            $content .= "Sold Out";
        }
    }

    if( isset($js) ) {
        return array('stat'=>'ok', 'content'=>$content, 'js'=>$js);

    }
    
    return array('stat'=>'ok', 'content'=>$content);
}
?>
