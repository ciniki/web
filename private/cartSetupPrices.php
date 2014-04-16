<?php
//
// Description
// -----------
// This function will setup the price list for an item to be sold online.
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure, similar to ciniki variable but only web specific information.
// events:			The array of events as returned by ciniki_events_web_list.
// limit:			The number of events to show.  Only 2 events are shown on the homepage.
//
// Returns
// -------
//
function ciniki_web_cartSetupPrices($ciniki, $settings, $business_id, $prices) {

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
	// Process the price list
	//
	if( count($prices) > 1 ) {
		$content .= "<h2>Price</h2>";
	}
	foreach($prices as $pid => $price) {
		$content = "<div class='cart-pricelist'>"
			. "<div class='price'>";
		if( isset($price['name']) && $price['name'] != '' ) {
			$content .= "<span class='cart-pricelabel'>" . $price['name'] . ": </span>";
		}

		$final_price = $price['unit_amount'];
		$discount = '';
		if( $price['unit_discount_amount'] > 0 ) {
			$discount .= " - " . numfmt_format_currency($intl_currency_fmt,
				$price['unit_discount_amount'], $intl_currency);
			$final_price = bcsub($price['unit_amount'], $price['unit_discount_amount'], 4);
		}
		if( $price['unit_discount_percentage'] > 0 ) {
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

		// Check if sold out
		$sold_out = '';
		if( $price['limited_units'] == 'yes' && $price['units_available'] < 1 ) {
			$content .= ' Sold Out';
		}

		//
		// If quantity is limited, and not sold out
		//
		elseif( $price['cart'] == 'yes' ) {
			$content .= "<form action='" .  $ciniki['request']['base_url'] . "/cart' method='POST'>";
			$content .= "<input type='hidden' name='action' value='add'/>";
			$content .= "<input type='hidden' name='object' value='" . $price['object'] . "'/>";
			$content .= "<input type='hidden' name='object_id' value='" . $price['object_id'] . "'/>";
			$content .= "<input type='hidden' name='final_price' value='" . $final_price . "'/>";
			// Check what time of field the quantity should be based on how many are available
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

		$content .= "</div></div>";
	}

	return array('stat'=>'ok', 'content'=>$content);
}
?>
