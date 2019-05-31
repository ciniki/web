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
        }
        $js_tickets .= "};";

        $ticketmap .= "</svg>";
        $ticketmap .= "</div>";
        $ticketmap .= "<form method='POST' action='" . $ciniki['request']['ssl_domain_base_url'] . "/cart' class='wide' onsubmit='return submitTickets(event);'>";
        $ticketmap .= "<input type='hidden' name='action' value='addprices'>";
        $ticketmap .= "<input type='hidden' name='object' value='" . $block['object'] . "'>";
        $ticketmap .= "<input type='hidden' name='object_id' value='" . $block['object_id'] . "'>";
        $ticketmap .= "<input type='hidden' id='price_ids' name='price_ids' value=''>";
        $ticketmap .= "<input type='hidden' name='quantity' value='1'>";
        $ticketmap .= "<div class='ticketmap-tickets'>";
        $ticketmap .= "<table class='ticketmap-tickets'>";
        $ticketmap .= "<tbody id='ticketmap1-tickets'>";
        $ticketmap .= "<tr><td colspan=3>" . (isset($block['empty-text']) && $block['empty-text'] != '' ? $block['empty-text'] : 'No tickets selected') . "</td></tr>";
        $ticketmap .= "</tbody>";
        $ticketmap .= "</table>";
        $ticketmap .= "</div>";
        $ticketmap .= "<br/>";
        $ticketmap .= "<div class='ticketmap-buttons'>";
        $ticketmap .= "<div class='cart-buttons wide aligncenter'>";
        $ticketmap .= "<button class='cart-submit button' onclick='closeMap(event);'>Cancel</button>&nbsp;";
        $ticketmap .= "<input id='ticketmap-submit' class='cart-submit' type='submit' value='Add to Cart' />";
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

            $js = ""
                . $js_tickets
                . "var selectedTickets = [];"
                . "function openMap() {"
                    . "var e=document.getElementById('ticketmap1-background');"
                    . "e.style.display='block';" 
                    . "var e=document.getElementById('ticketmap1-wrap');"
                    . "e.style.display='block';" 
                    . "updateTickets();"
                    . "window.scrollTo(0, 0);"
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
                . "};"
                . "function selectTicket(tid) {"
                    . "var e=document.getElementById('ticket_' + tid);"
                    . "if(selectedTickets.indexOf(tid)==-1){"
                        . "e.setAttribute('fill', 'green');"
                        . "selectedTickets.push(tid);"
                    . "}else{" 
                        . "selectedTickets.splice(selectedTickets.indexOf(tid), 1);"
                        . "e.setAttribute('fill', 'blue');"
                    . "}"
                    . "updateTickets();"
                . "};"
                . "function updateTickets() {"
                    . "var e=document.getElementById('ticketmap1-tickets');"
                    . "var h='';"
                    . "var p=document.getElementById('price_ids');"
                    . "p.value='';"
                    . "for(var i in selectedTickets) {"
                        . "var t=tickets[selectedTickets[i]];"
                        . "h+='<tr><td>'+t.name+'</td><td>'+t.amount_display+'</td><td><a href=\"javascript:selectTicket('+t.price_id+');\">Remove</a></td></tr>';"
                        . "p.value+=selectedTickets[i]+',';"
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
