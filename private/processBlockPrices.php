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
function ciniki_web_processBlockPrices(&$ciniki, $settings, $tnid, $block) {

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
            $content .= "<h2>" . $block['title'] . "</h2>";
        }
        $content .= "<div class='cart-pricelist'>";
        foreach($block['prices'] as $pid => $price) {
            $content .= "<div class='price'>";
            if( isset($price['name']) && $price['name'] != '' ) {
                $content .= "<span class='cart-pricelabel'>" . $price['name'] . ": </span>";
            }

            $final_price = $price['unit_amount'];
            $discount = '';
            if( isset($price['unit_discount_amount']) && $price['unit_discount_amount'] > 0 ) {
                $discount .= " - " . numfmt_format_currency($intl_currency_fmt,
                    $price['unit_discount_amount'], $intl_currency);
                $final_price = bcsub($price['unit_amount'], $price['unit_discount_amount'], 4);
            }
            if( isset($price['unit_discount_percentage']) && $price['unit_discount_percentage'] > 0 ) {
                $percentage = bcdiv($price['unit_discount_percentage'], 100, 4);
                $discount .= " - " .  $price['unit_discount_amount'] . "%";
                $final_price = bcsub($final_price, bcmul($final_price, $percentage, 4), 4);
            }

            // Apply the discounts
            if( $final_price != $price['unit_amount'] ) {
                $content .= '<del>' . $price['unit_amount'] . '</del>' . $discount . ' ';
                $content .= numfmt_format_currency($intl_currency_fmt, $final_price, $intl_currency);
                $content .= ' ' . $intl_currency;
            } else {
                $content .= numfmt_format_currency($intl_currency_fmt, $price['unit_amount'], $intl_currency);
                $content .= ' ' . $intl_currency;
            }

            // Check if display stock level
            if( isset($price['units_inventory']) ) {
                $inv = 'no';
                if( isset($settings['page-cart-inventory-customers-display']) 
                    && $settings['page-cart-inventory-customers-display'] == 'yes' 
                    ) {
                    $inv = 'yes';
                }
                if( isset($settings['page-cart-inventory-members-display']) 
                    && $settings['page-cart-inventory-members-display'] == 'yes' 
                    && isset($ciniki['session']['customer']['member_status'])
                    && $ciniki['session']['customer']['member_status'] == 10
                    ) {
                    $inv = 'yes';
                }
                if( isset($settings['page-cart-inventory-dealers-display']) 
                    && $settings['page-cart-inventory-dealers-display'] == 'yes' 
                    && isset($ciniki['session']['customer']['dealer_status'])
                    && $ciniki['session']['customer']['dealer_status'] == 10
                    ) {
                    $inv = 'yes';
                }
                if( isset($settings['page-cart-inventory-distributor-display']) 
                    && $settings['page-cart-inventory-distributor-display'] == 'yes' 
                    && isset($ciniki['session']['customer']['distributor_status'])
                    && $ciniki['session']['customer']['distributor_status'] == 10
                    ) {
                    $inv = 'yes';
                }
                if( $inv == 'yes' ) {
                    if( $price['units_available'] > 0 ) {
                        $content .= ' (' . $price['units_available'] . ' in stock)';
                    } else {
                        $content .= ' (backordered)';
                    }
                }
            }

            // Check if sold out
            $sold_out = '';
            if( isset($price['limited_units']) && isset($price['units_available']) 
                && $price['limited_units'] == 'yes' && $price['units_available'] < 1 
                ) {
                if( isset($price['individual_ticket']) && $price['individual_ticket'] == 'yes' ) {
                    $content .= ' Sold';
                } else {
                    $content .= ' Sold Out';
                }
            }

            //
            // If quantity is limited, and not sold out
            //
            elseif( isset($price['cart']) && $price['cart'] == 'yes' 
                && isset($settings['page-cart-active']) && $settings['page-cart-active'] == 'yes'
                && isset($ciniki['tenant']['modules']['ciniki.sapos']) 
                && ($ciniki['tenant']['modules']['ciniki.sapos']['flags']&0x08) > 0 
                ) {
                $content .= "<form action='" .  $ciniki['request']['ssl_domain_base_url'] . "/cart' method='POST'>";
                $content .= "<input type='hidden' name='action' value='add'/>";
                $content .= "<input type='hidden' name='object' value='" . $price['object'] . "'/>";
                $content .= "<input type='hidden' name='object_id' value='" . $price['object_id'] . "'/>";
                if( isset($price['price_id']) && $price['price_id'] != '' ) {
                    $content .= "<input type='hidden' name='price_id' value='" . $price['price_id'] . "'/>";
                }
                $content .= "<input type='hidden' name='final_price' value='" . $final_price . "'/>";
                // Check what type of field the quantity should be based on how many are available
                if( isset($price['limited_units']) && $price['limited_units'] == 'yes' 
                    && isset($price['units_available']) && $price['units_available'] > 1 
                    && $price['units_available'] <= 30 ) {
                    $content .= "<span class='cart-quantity'>"
                        . "<select name='quantity'>";
                    for($i=1;$i<=$price['units_available'];$i++) {
                        $content .= "<option value='$i'>$i</option>";
                    }
                    $content .= "</select></span>";
                }
                elseif( isset($price['limited_units']) && $price['limited_units'] == 'yes' 
                    && isset($price['limited_units']) && $price['units_available'] == 1 ) {
                    $content .= "<input type='hidden' name='quantity' value='1'/>"; 
                }
                elseif( isset($price['limited_units']) && $price['limited_units'] == 'yes' 
                    && isset($price['limited_units']) && $price['units_available'] > 1 ) {
                    $content .= "<span class='cart-quantity'><input class='quantity' name='quantity' type='text' value='1' size='2'/></span>";
                }
                elseif( !isset($price['limited_units']) || $price['limited_units'] == 'no' ) {
                    $content .= "<span class='cart-quantity'><input class='quantity' name='quantity' type='text' value='1' size='2'/></span>";
                }
                    
                $content .= "<span class='cart-submit'>"
                    . "<input class='cart-submit' type='submit' name='add' value='";
                if( isset($price['add_text']) && $price['add_text'] != '' ) {
                    $content .= $price['add_text'];
                } else {
                    $content .= 'Add to Cart';
                }
                $content .= "'/></span>";
                $content .= "</form>";
            }

            $content .= "</div>";
        }
        $content .= "</div>";
    }
    
    return array('stat'=>'ok', 'content'=>$content);
}
?>
