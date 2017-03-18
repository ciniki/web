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
function ciniki_web_processBlockOrderQueue(&$ciniki, $settings, $business_id, $block) {

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

    //
    // Setup the api endpoints and submit urls
    //
    $api_queue_update = (isset($block['api_queue_update']) ? $block['api_queue_update'] : '');

    $js_variables = array();

    //
    // Check for block title
    //
    if( isset($block['title']) && $block['title'] != '' ) {
        $content .= "<h2" . ((isset($block['size'])&&$block['size']!='') ? " class='" . $block['size'] . "'" : '') . ">" . $block['title'] . "</h2>";
    }

    if( isset($block['intro']) && $block['intro'] != '' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
        $rc = ciniki_web_processContent($ciniki, $settings, $block['intro'], (isset($block['size']) ? $block['size'] : ''));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( $rc['content'] != '' ) {
            $content .= $rc['content'];
        }
    }

    //
    // Make sure there is content to edit
    //
    if( isset($block['queue']) ) {
        $content .= "<div class='order-repeats" . ((isset($block['size'])&&$block['size']!='') ? ' ' . $block['size'] : '') . "'>";
        $content .= "<table class='order-repeats'>";
        $content .= "<tbody>";   
        foreach($block['queue'] as $rid => $item) {
            $content .= "<tr class='order-repeats-item'>";
            $content .= "<td class='name'>" . $item['name'] . "</td>";
            //
            // Add the hidden row for adding to queue orders
            //
            $content .= "<td class='options'>";
            $content .=  "<div class='repeat-option'>";
            if( isset($block['ordered']) && $block['ordered'] == 'yes' ) {
                $content .= "There " . ($item['queue_quantity'] > 1 ? 'are ' : 'is ') . '<b>' . (float)$item['queue_quantity'] . '</b> on order for you';
            } else {
                $content .= "You have "
                    . "<span class='order-qty'>"
                    . "<span class='order-qty-down' onclick='queueQtyDown(" . $item['id'] . ");'>-</span>"
                    . "<input id='queue_quantity_" . $item['id'] . "' name='queue_quantity_" . $item['id'] . "' "
                        . "value='" . (float)$item['queue_quantity'] . "' "
                        . "onkeyup='queueQtyChange(" . $item['id'] . ");' "
                        . "onchange='queueQtyChange(" . $item['id'] . ");' "
                        . "/>"
                    . "<span class='order-qty-up' onclick='queueQtyUp(" . $item['id'] . ");'>+</span>"
                    . "</span>"
                    . " in your queue";
            }
            $content .= "</div>";
            $js_variables['object_ref_' . $item['id']] = $item['object'] . '/' . $item['object_id'];
            $js_variables['queue_quantity_' . $item['id']] = $item['queue_quantity'];
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
            . "function queueQtyUp(id){"
                . "var e=C.gE('queue_quantity_' + id);"
                . "if(e.value==''){"
                    . "return true;"
                    . "e.value=1;"
                . "}else{"
                    . "e.value=parseInt(e.value)+1;"
                . "}"
                . "queueQtyChange(id);"
            . "}"
            . "function queueQtyDown(id){"
                . "var e=C.gE('queue_quantity_' + id);"
                . "if(parseInt(e.value)<1){"
                    . "e.value=0;"
                . "}else if(e.value==''){"
                    . "return true;"
                    . "e.value=1;"
                . "}else{"
                    . "e.value=parseInt(e.value)-1;"
                . "}"
                . "queueQtyChange(id);"
            . "}"
            . "function queueQtyChange(id){"
                . "var e=C.gE('queue_quantity_' + id);"
                . "if(e.value!=org_val['queue_quantity_'+id]){"
                    . "C.getBg('" . $api_queue_update . "'+org_val['object_ref_'+id],{'quantity':e.value},function(r){"
                        . "if(r.stat!='ok'){"
                            . "e.value=org_val['queue_quantity_'+id];"
                            . "alert('We had a problem updating your queue. Please try again or contact us for help.');"
                            . "return false;"
                        . "}"
                        . "org_val['queue_quantity_'+id]=e.value;"
                    . "});"
                . "}"
            . "}"
            . "</script>";
    }
    
    return array('stat'=>'ok', 'content'=>$content);
}
?>
