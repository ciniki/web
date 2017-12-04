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
function ciniki_web_processBlockPriceTable(&$ciniki, $settings, $tnid, $block) {

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
    if( $block['prices'] != '' ) {
        //
        // Process the price list
        //
        if( isset($block['title']) && $block['title'] != '' ) {
            $content .= "<h2 class='wide'>" . $block['title'] . "</h2>";
        }
        $content .= "<div class='cart-pricetable" . ((isset($block['size'])&&$block['size']!='')?' cart-pricetable-'.$block['size']:'') . "'>";
        $content .= "<table class='cart-pricetable'>";
        if( isset($block['headers']) && count($block['headers']) > 0 ) {
            $content .= "<thead><tr>";
            foreach($block['headers'] as $header) {
                $content .= "<th>" . $header . "</th>";
            }
            $content .= "</tr></thead>";
        }
        $content .= "<body>";   
        foreach($block['prices'] as $rid => $row) {
            //
            // Calculate final price
            //
            $final_price = $row['unit_amount'];
            $discount = '';
            if( isset($row['unit_discount_amount']) && $row['unit_discount_amount'] > 0 ) {
                $discount .= " - " . numfmt_format_currency($intl_currency_fmt,
                    $row['unit_discount_amount'], $intl_currency);
                $final_price = bcsub($row['unit_amount'], $row['unit_discount_amount'], 4);
            }
            if( isset($row['unit_discount_percentage']) && $row['unit_discount_percentage'] > 0 ) {
                $percentage = bcdiv($row['unit_discount_percentage'], 100, 4);
                $discount .= " - " .  $row['unit_discount_amount'] . "%";
                $final_price = bcsub($final_price, bcmul($final_price, $percentage, 4), 4);
            }

            $content .= "<tr>";
            foreach($block['fields'] as $field) {
                if( $field == 'price' ) {
                    $content .= "<td>"
                        . numfmt_format_currency($intl_currency_fmt, $final_price, $intl_currency)
                        . "</td>";
                } else {
                    $content .= "<td>"
                        . $row[$field]
                        . "</td>";
                }
            }
            $content .= "</tr>";
        }
        $content .= "</tbody>";
        $content .= "</table>";
        $content .= "</div>";
    }
    
    return array('stat'=>'ok', 'content'=>$content);
}
?>
