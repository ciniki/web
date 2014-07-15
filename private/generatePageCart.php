<?php
//
// Description
// -----------
// This function will generate a customers cart, add/update/delete items and process checkout
// through paypal.
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_generatePageCart(&$ciniki, $settings) {

	//
	// Store the content created by the page
	// Make sure everything gets generated ok before returning the content
	//
	$content = '';
	$display_cart = 'yes';
	$cart_err_msg = '';
	$cart = NULL;
	$cart_edit = 'yes';
	$page_title = "Shopping Cart";

	//
	// Required methods
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'web', 'cartLoad');

	//
	// Get business/user settings
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'intlSettings');
	$rc = ciniki_businesses_intlSettings($ciniki, $ciniki['request']['business_id']);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$intl_timezone = $rc['settings']['intl-default-timezone'];
	$intl_currency_fmt = numfmt_create($rc['settings']['intl-default-locale'], NumberFormatter::CURRENCY);
	$intl_currency = $rc['settings']['intl-default-currency'];

	//
	// Load the business modules
	//
	$modules = array();
	ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'getActiveModules');
	$rc = ciniki_businesses_getActiveModules($ciniki, $ciniki['request']['business_id']);
	if( $rc['stat'] == 'ok' ) {
		$modules = $rc['modules'];
	}

	//
	// Check if a cart already exists
	//
	$rc = ciniki_sapos_web_cartLoad($ciniki, $settings, $ciniki['request']['business_id']);
	if( $rc['stat'] == 'noexist' ) {
		$cart = NULL;
		$_SESSION['cart']['sapos_id'] = 0;
		$_SESSION['cart']['num_items'] = 0;
		$ciniki['session']['cart']['sapos_id'] = 0;
		$ciniki['session']['cart']['num_items'] = 0;

	} elseif( $rc['stat'] != 'ok' ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1693', 'msg'=>'Error processing shopping cart, please try again.'));
	} else {
		$cart = $rc['cart'];
		$_SESSION['cart']['num_items'] = count($cart['items']);
		$ciniki['session']['cart']['num_items'] = count($cart['items']);
	}

	// $ct = print_r($rc, true);

	//
	// FIXME: Add check for cookies
	//

	//
	// Check if a item is being added to the cart
	//
	if( isset($_POST['action']) && $_POST['action'] == 'add' ) {
		$item_exists = 'no';
		if( $cart == NULL ) {
			// Create a shopping cart
			ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'web', 'cartCreate');
			$rc = ciniki_sapos_web_cartCreate($ciniki, $settings, $ciniki['request']['business_id'], array());
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$sapos_id = $rc['sapos_id'];
			$_SESSION['cart']['sapos_id'] = $sapos_id;
			$_SESSION['cart']['num_items'] = 0;
			$ciniki['session']['cart'] = array();
			$ciniki['session']['cart']['sapos_id'] = $sapos_id;
			$ciniki['session']['cart']['num_items'] = 0;
		} else {
			//
			// Check if item already exists in the cart
			//
			if( isset($cart['items']) ) {
				foreach($cart['items'] as $item) {
					$item = $item['item'];
					if( $item['object'] == $_POST['object']
						&& $item['object_id'] == $_POST['object_id'] ) {
						$item_exists = 'yes';
						//
						// Update the quantity
						//
//						if( $item['quantity'] != $_POST['quantity'] ) {
						ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'web', 'cartItemUpdate');
						$rc = ciniki_sapos_web_cartItemUpdate($ciniki, $settings, 
							$ciniki['request']['business_id'],
							array('item_id'=>$item['id'],
								'quantity'=>$item['quantity'] + $_POST['quantity']));
						if( $rc['stat'] != 'ok' ) {
							return $rc;
						}
//						}
						break;
					}
				}
			}
		}

		//
		// Add the item to the cart, if they don't already exist
		//
		if( $item_exists == 'no' ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'web', 'cartItemAdd');
			$rc = ciniki_sapos_web_cartItemAdd($ciniki, $settings, $ciniki['request']['business_id'],
				array('object'=>$_POST['object'],
					'object_id'=>$_POST['object_id'],
					'price_id'=>(isset($_POST['price_id'])?$_POST['price_id']:0),
					'quantity'=>$_POST['quantity']));
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
		}

		//
		// Redirect to avoid form duplicate submission
		//
		header("Location: " . $ciniki['request']['base_url'] . "/cart");
		exit;

		//
		// Incase redirect fails, Load the updated cart
		//
		$rc = ciniki_sapos_web_cartLoad($ciniki, $settings, $ciniki['request']['business_id']);
		if( $rc['stat'] != 'ok' ) {	
			return $rc;
		}
		$cart = $rc['cart'];
		$_SESSION['cart']['num_items'] = count($cart['items']);
		$ciniki['session']['cart']['num_items'] = count($cart['items']);
	}
	
	//
	// Check if cart quantities were updated
	//
	elseif( isset($_POST['update']) && $_POST['update'] != '' 
		&& isset($_POST['action']) && $_POST['action'] == 'update' ) {
		if( isset($cart['items']) ) {
			foreach($cart['items'] as $item) {
				$item = $item['item'];
				if( isset($_POST['quantity_' . $item['id']]) 
					&& $_POST['quantity_' . $item['id']] != $item['quantity'] ) {
					$new_quantity = intval($_POST['quantity_' . $item['id']]);
					if( $new_quantity <= 0 ) {
						ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'web', 'cartItemDelete');
						$rc = ciniki_sapos_web_cartItemDelete($ciniki, $settings, 
							$ciniki['request']['business_id'],
							array('item_id'=>$item['id']));
						if( $rc['stat'] != 'ok' ) {
							return $rc;
						}
					} else {
						ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'web', 'cartItemUpdate');
						$rc = ciniki_sapos_web_cartItemUpdate($ciniki, $settings, 
							$ciniki['request']['business_id'],
							array('item_id'=>$item['id'],
								'quantity'=>$new_quantity));
						if( $rc['stat'] != 'ok' ) {
							return $rc;
						}
					}
				}
			}
		}

		//
		// Redirect to avoid form duplicate submission
		//
//		$content .= print_r($_POST, true);
		header("Location: " . $ciniki['request']['base_url'] . "/cart");
		exit;

		//
		// Incase redirect fails, Load the updated cart
		//
		$rc = ciniki_sapos_web_cartLoad($ciniki, $settings, $ciniki['request']['business_id']);
		if( $rc['stat'] != 'ok' ) {	
			return $rc;
		}
		$cart = $rc['cart'];
		$_SESSION['cart']['num_items'] = count($cart['items']);
		$ciniki['session']['cart']['num_items'] = count($cart['items']);
	}

	//
	// Check if checkout
	//
	elseif( isset($_POST['checkout']) && $_POST['checkout'] != '' ) {
		$content .= "Process checkout";
	}

	//
	// Check if returned from Paypal
	//

	//
	// Display the contents of the shopping cart
	//
	if( $display_cart == 'yes' ) {
		//
		// Display cart items
		//
		$content .= "<article class='page'>\n"
			. "<header class='entry-title'>"
			. "<h1 id='entry-title' class='entry-title'>$page_title</h1></header>\n"
			. "<div class='cart'>\n"
			. "";
		if( $cart != NULL && isset($cart['items']) && count($cart['items']) > 0 ) {
			$content .= "<form action='" .  $ciniki['request']['base_url'] . "/cart' method='POST'>";
			$content .= "<input type='hidden' name='action' value='update'/>";
			$content .= "<div class='cart-items'>";
			$content .= "<table class='cart-items'>";
			$content .= "<thead><tr>"
				. "<th class='alignleft'>Item</th>"
				. "<th class='alignright'>Quantity</th>"
				. "<th class='alignright'>Price</th>"
				. "<th class='alignright'>Total</th>"
				. "<th>Actions</th>"
				. "</tr></thead>";
			$content .= "<tbody>";
			$count=0;
			foreach($cart['items'] as $item_id => $item) {
				$item = $item['item'];
				$content .= "<tr class='" . (($count%2)==0?'item-even':'item-odd') . "'>"
					. "<td>" . $item['description'] . "</td>"
					. "<td class='alignright'>";
				if( $cart_edit == 'yes' ) {
					$content .= "<span class='cart-quantity'>"
						. "<input class='quantity' id='quantity_" . $item['id'] . "' name='quantity_" . $item['id'] . "' type='text' value='" 
							. $item['quantity'] . "' size='2'/>"
						. "</span>";
				} else {
					$content .= $item['quantity'];
				}
				$content .= "</td>";
				$discount_text = '';
				if( $item['unit_discount_amount'] > 0 ) {
					$discount_text .= '-' . numfmt_format_currency($intl_currency_fmt, 
						$item['unit_discount_amount'], $intl_currency)
						. (($item['quantity']>1)?'x'.$item['quantity']:'');
				}
				if( $item['unit_discount_percentage'] > 0 ) {
					$discount_text .= ($discount_text!=''?', ':'') . '-' . $item['unit_discount_percentage'] . '%';
				}
				$content .= "<td class='alignright'>" 
						. numfmt_format_currency($intl_currency_fmt, $item['unit_amount'], $intl_currency)
						. ($discount_text!=''?('<br/>' . $discount_text . ' ('
							. numfmt_format_currency($intl_currency_fmt, $item['discount_amount'], $intl_currency)) . ')':'')
						. "</td>";
				$content .= "<td class='alignright'>" 
						. numfmt_format_currency($intl_currency_fmt, $item['total_amount'], $intl_currency)
						. "</td>";
				$content .= "<td class='aligncenter'>"
					. "<span class='cart-submit'>"
//					. "<input class='cart-submit' onclick='alert(document.getElementById(\"quantity_" . $item['id'] . "\").value);return true;' type='submit' name='update_delete_" . $item['id'] . "' value='Delete'/>"
					. "<input class='cart-submit' onclick='document.getElementById(\"quantity_" . $item['id'] . "\").value=0;return true;' type='submit' name='update' value='Delete'/>"
					. "</span>"
					. "</td>";
				$content .= "</tr>";
				$count++;
			}
			$content .= "</tbody>";
			$content .= "<tfoot>";
			// cart totals
			if( $cart['shipping_amount'] > 0 || (isset($cart['taxes']) && count($cart['taxes']) > 0) ) {
				$content .= "<tr class='" . (($count%2)==0?'item-even':'item-odd') . "'>";
				$content .= "<td colspan='3' class='alignright'>Sub-Total:</td>"
					. "<td class='alignright'>"
					. numfmt_format_currency($intl_currency_fmt, $cart['subtotal_amount'], $intl_currency)
					. "</td><td></td></tr>";
				$count++;
			}
			if( isset($cart['shipping_amount']) && $cart['shipping_amount'] > 0 ) {
				$content .= "<tr class='" . (($count%2)==0?'item-even':'item-odd') . "'>";
				$content .= "<td colspan='3' class='alignright'>Shipping:</td>"
					. "<td class='alignright'>"
					. numfmt_format_currency($intl_currency_fmt, $cart['shipping_amount'], $intl_currency)
					. "</td><td></td></tr>";
				$count++;
			}
			if( isset($cart['taxes']) ) {
				foreach($cart['taxes'] as $tax) {
					$tax = $tax['tax'];
					$content .= "<tr class='" . (($count%2)==0?'item-even':'item-odd') . "'>";
					$content .= "<tr><td colspan='3' class='alignright'>" . $tax['description'] . "<td>"
						. "<td class='alignright'>"
						. numfmt_format_currency($intl_currency_fmt, $tax['amount'], $intl_currency)
						. "</td><td></td></tr>";
					$count++;
				}
			}
			$content .= "<tr class='" . (($count%2)==0?'item-even':'item-odd') . "'>";
			$content .= "<td colspan='3' class='alignright'><b>Total:</b></td>"
				. "<td class='alignright'>"
				. numfmt_format_currency($intl_currency_fmt, $cart['total_amount'], $intl_currency)
				. "</td><td></td></tr>";
			$count++;
			$content .= "</foot>";
			$content .= "</table>";
			$content .= "</div>";
				
			// cart buttons
//			$content .= "<table class='cart-buttons'>"
//				. "<tfoot>";
//			$content .= "<tr><td class='aligncenter'>";
			$content .= "<div class='cart-buttons'>";
			$content .= "<span class='cart-submit'>"
				. "<input class='cart-submit' type='submit' name='continue' value='Continue Shopping'/>"
				. "</span>";
			$content .= "<span class='cart-submit'>"
				. "<input class='cart-submit' type='submit' name='update' value='Update'/>"
				. "</span>";
			$content .= "<span class='cart-submit'>"
				. "<input class='cart-submit' type='submit' name='checkout' value='Checkout'/>"
				. "</span>";
//			$content .= "</td></tr>";
//			$content .= "</tfoot>";
//			$content .= "</table>";
			$content .= "</div>";
			$content .= "</form>";
		} else {
			$content .= "<p>Your shopping cart is empty.</p>";
		}

		//
		// Display checkout button
		//

		$content .= "</div></article>\n";
	}


	//
	// Add the header
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageHeader');
	$rc = ciniki_web_generatePageHeader($ciniki, $settings, $page_title, array());
	if( $rc['stat'] != 'ok' ) {	
		return $rc;
	}
	$page_content = $rc['content'];
	
	if( $content != '' ) {
		$page_content .= "<div id='content'>";
		$page_content .= $content;
		$page_content .= "</div>\n";
	}

//	$page_content .= print_r($_SESSION, true) . "<br/>";
//	$page_content .= print_r($cart, true) . "<br/>";
//	$page_content .= print_r($ct, true) . "<br/>";

	//
	// Add the footer
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageFooter');
	$rc = ciniki_web_generatePageFooter($ciniki, $settings);
	if( $rc['stat'] != 'ok' ) {	
		return $rc;
	}
	$page_content .= $rc['content'];

	return array('stat'=>'ok', 'content'=>$page_content);
}
?>
