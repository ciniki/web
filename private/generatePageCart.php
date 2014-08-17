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
	// Check if should be forced to SSL
	//
	if( isset($settings['site-ssl-force-cart']) 
		&& $settings['site-ssl-force-cart'] == 'yes' 
		) {
		if( isset($settings['site-ssl-active'])
			&& $settings['site-ssl-active'] == 'yes'
			&& (!isset($_SERVER['HTTP_CLUSTER_HTTPS']) || $_SERVER['HTTP_CLUSTER_HTTPS'] != 'on')
			&& (!isset($_SERVER['SERVER_PORT']) || $_SERVER['SERVER_PORT'] != '443' ) )  {
			header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
			exit;
		}
	}

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
//	print "<pre>" . print_r($_POST, true) . "</pre>";
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
		header("Location: " . $ciniki['request']['ssl_domain_base_url'] . "/cart");
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
		header("Location: " . $ciniki['request']['ssl_domain_base_url'] . "/cart");
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
	// Check if dealer is submitting an order
	//
	elseif( isset($_POST['submitorder']) && $_POST['submitorder'] != ''
		&& isset($ciniki['session']['customer']['dealer_status']) 
		&& $ciniki['session']['customer']['dealer_status'] == 10 
		) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'web', 'submitOrder');
		$rc = ciniki_sapos_web_submitOrder($ciniki, $settings, $ciniki['request']['business_id']);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		} else {
			$content .= "<p>Your order has been submitted.</p>";
		}
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
		$content .= "<article class='page cart'>\n"
//			. "<form action='" .  $ciniki['request']['ssl_domain_base_url'] . "/cart' method='POST'>"
			. "<header class='entry-title'>"
			. "<h1 id='entry-title' class='entry-title'>$page_title</h1>";
		if( isset($settings['page-cart-product-search']) 
			&& $settings['page-cart-product-search'] == 'yes' 
			) {
			$content .= "<div class='cart-search-input'>"
				. "<form id='cart-search-form' action='" .  $ciniki['request']['ssl_domain_base_url'] . "/cart' method='POST'>"
				. "<input type='hidden' name='action' value='add'/>"
				. "<input id='cart-search-form-object' type='hidden' name='object' value='' />"
				. "<input id='cart-search-form-object_id' type='hidden' name='object_id' value='' />"
				. "<input id='cart-search-form-price_id' type='hidden' name='price_id' value='' />"
				. "<input id='cart-search-form-final_price' type='hidden' name='final_price' value='' />"
				. "<input id='cart-search-form-quantity' type='hidden' name='quantity' value='1' />"
				. "<label for='search_str'></label><input id='cart-search-str' class='input' type='text' autofocus placeholder='Search' name='search_str' value='" . (isset($_POST['search_str'])?$_POST['search_str']:'') . "' onkeyup='return update_cart_search();' onsearch='return update_cart_search();' onsubmit='return false;' autocomplete='off' />"
				. "</form></div>";
		}
		$content .= "</header>\n"
			. "<div class='cart'>\n"
			. "";
		//
		// Check if we should display inventory
		//
		$inv = 'no';
		if( isset($settings['page-cart-inventory-customersj-display']) 
			&& $settings['page-cart-inventory-customersj-display'] == 'yes' 
			) {
			$inv = 'yes';
		}
		elseif( isset($settings['page-cart-inventory-dealers-display']) 
			&& $settings['page-cart-inventory-dealers-display'] == 'yes' 
			&& isset($ciniki['session']['customer']['dealer_status'])
			&& $ciniki['session']['customer']['dealer_status'] == 10 
			) {
			$inv = 'yes';
		}

		//
		// Check if we should display the search box
		//
		if( isset($settings['page-cart-product-search']) 
			&& $settings['page-cart-product-search'] == 'yes' 
			) {
			$limit = 11;
			$ciniki['request']['ciniki_api'] = 'yes';
			$ciniki['request']['inline_javascript'] .= "<script type='text/javascript'>\n"
				. "var prev_cart_search_str = '';\n"
				. "function update_cart_search() {\n"
					. "var str = document.getElementById('cart-search-str').value;\n"
					. "if( prev_cart_search_str != str ) {\n"
						. "var t = document.getElementById('cart-search-result');\n"
						. "if( str == '' ) { t.style.display = 'none'; }\n"
						. "else if( str != prev_cart_search_str ) {\n"
							. "C.getBg('cart/search/'+encodeURIComponent(str),{'limit':$limit},update_search_results);\n"
							. "t.style.display = 'block';\n"
						. "}\n"
						. "prev_cart_search_str = str;\n"
					. "}\n"
					. "return false;"
				. "};"
				. "function cart_add_search_result(o,i,p,f,q) {"
					. "C.gE('cart-search-form-object').value=o;"
					. "C.gE('cart-search-form-object_id').value=i;"
					. "C.gE('cart-search-form-price_id').value=(p!=null&&p!=''?p:0);"
					. "C.gE('cart-search-form-final_price').value=f;"
					. "C.gE('cart-search-form-quantity').value=q;"
					. "C.gE('cart-search-form').submit();"
				. "};"
				. "function update_search_results(rsp) {"
					. "var d = document.getElementById('cart-search-results');"
					. "C.clr(d);"
					. "if(rsp.products!=null&&rsp.products.length>0) {"
						. "var ct=0;"
						. "for(i in rsp.products) {"
							. "var p=rsp.products[i].product;"
							. "ct++;"
							. "var tr=C.aE('tr',null,(i%2==0?'item-even':'item-odd'));"
							. "if(ct>=$limit){"
								. "var c=C.aE('td',null,'aligncenter','. . .');"
								. "c.colSpan=" . ($inv=='yes'?5:4) . ";"
								. "tr.appendChild(c);"
							. "}else{"
								. "tr.appendChild(C.aE('td',null,null,p.name));"
								. "if(p.cart!=null&&p.cart=='yes'"
									// Check if inventory available or backorder available
									. "&&(p.inventory_available>0||(p.inventory_flags&0x02)>0)){"
									. "tr.appendChild(C.aE('td',null,'alignright','<span class=\"cart-quantity\"><input "
									. "class=\"quantity\" id=\"quantity_'+i+'\" name=\"quantity_'+i+'\" "
									. "value=\"1\" size=\"2\"/></span>'));"
								. "}else{"
									. "tr.appendChild(C.aE('td'));"
								. "}"
								// Check if inventory is being tracked,
								// and if the item is backordered, decide if sold out or backorder should display
								. ($inv=='yes'?"tr.appendChild(C.aE('td',null,'alignright',("
								. "(p.inventory_flags&0x01)==1?("
									. "(p.inventory_available>0?p.inventory_available:"
										. "((p.inventory_flags&0x02)==2?'Backordered':'Sold out'))"
									. "):''))"
								. ");":"")
								. "tr.appendChild(C.aE('td',null,'alignright',p.price));"
								. "if(p.cart!=null&&p.cart=='yes'"
									// Check if inventory available or backorder available
									. "&&(p.inventory_available>0||(p.inventory_flags&0x02)>0)){"
									. "var e = C.aE('td',null,'aligncenter');"
									. "var b = C.aE('input',null,'cart-submit');"
									. "b.type='submit';"
									. "b.value='Add';"
									. "b.setAttribute('onclick', 'cart_add_search_result(\"ciniki.products.product\","
										. "\"'+p.id+'\","
										. "\"'+(p.price_id!=null?p.price_id:0)+'\","
										. "\"'+p.unit_amount+'\","
										. "C.gE(\"quantity_'+i+'\").value);return false;');"
									. "e.appendChild(b);"
									. "tr.appendChild(e);"
								. "}else{"
									. "tr.appendChild(C.aE('td',null,null,''));"
								. "}"
							. "}"
							. "d.appendChild(tr);"
						. "}"
					. "}else{"
						. "d.innerHTML='<tr class=\"item-even\"><td class=\"aligncenter\" colspan=\"" . ($inv=='yes'?5:4) . "\">No products found</td></tr>';"
					. "}"
				. "};"
				. "</script>\n";
			$content .= "<div class='cart-search-items' id='cart-search-result' style='display:none;'>"
//				. "<form action='" .  $ciniki['request']['ssl_domain_base_url'] . "/cart' method='POST'>"
				. "<table class='cart-items'>\n"
				. "<thead><tr>"
				. "<th class='alignleft'>Item</th>"
				. "<th class='alignright'>Quantity</th>"
				. ($inv=='yes'?"<th class='alignright'>Inventory</th>":"")
				. "<th class='alignright'>Price</th>"
				. "<th>Actions</th>"
				. "</tr></thead>"
				. "<tbody id='cart-search-results'>"
				. "</tbody>\n"
				. "</table>"
//				. "</form>"
				. "</div>\n";
		}


		if( $inv == 'yes' ) {
			$item_objects = array();
			ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'private', 'getReservedQuantities');
			foreach($cart['items'] as $item_id => $item) {
				// Create the object
				if( !isset($item_objects[$item['item']['object']]) ) {
					$item_objects[$item['item']['object']] = array();
				}
				// Add the item
				$item_objects[$item['item']['object']][$item['item']['object_id']] = $item_id;
				$cart['items'][$item_id]['item']['quantity_inventory'] = 0;
				$cart['items'][$item_id]['item']['quantity_reserved'] = 0;
			}
			foreach($item_objects as $o => $oids) {
				//
				// Get current inventory
				//
				list($pkg, $mod, $obj) = explode('.', $o);
				$object_ids = array_keys($oids);
				$rc = ciniki_core_loadMethod($ciniki, $pkg, $mod, 'sapos', 'cartItemsInventory');
				if( $rc['stat'] == 'ok') {
					$fn = $pkg . '_' . $mod . '_sapos_cartItemsInventory';
					$rc = $fn($ciniki, $ciniki['request']['business_id'], array(
						'object'=>$o, 'object_ids'=>$object_ids));
					if( isset($rc['quantities']) ) {
						foreach($rc['quantities'] as $quantity) {
							$item_id = $item_objects[$o][$quantity['object_id']];
							$cart['items'][$item_id]['item']['quantity_inventory'] = $quantity['quantity_inventory'];
						}
					}
				}

				//
				// Get the number reserved
				//
				$rc = ciniki_sapos_getReservedQuantities($ciniki, $ciniki['request']['business_id'], 
					$o, $object_ids, $cart['id']);
				if( isset($rc['quantities']) ) {
					foreach($rc['quantities'] as $quantity) {
						$item_id = $item_objects[$o][$quantity['object_id']];
						$cart['items'][$item_id]['item']['quantity_reserved'] = $quantity['quantity_reserved'];
					}
				}
			}
		}
		//
		// Display cart items
		//
		if( $cart != NULL && isset($cart['items']) && count($cart['items']) > 0 ) {
			$content .= "<form action='" .  $ciniki['request']['ssl_domain_base_url'] . "/cart' method='POST'>";
			$content .= "<input type='hidden' name='action' value='update'/>";
			$content .= "<div class='cart-items'>";
			$content .= "<table class='cart-items'>";
			$content .= "<thead><tr>"
				. "<th class='alignleft'>Item</th>"
				. "<th class='alignright'>Quantity</th>"
				. ($inv=='yes'?"<th class='alignright'>Inventory</th>":"")
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
				if( $inv == 'yes' ) {
					$content .= "<td class='alignright'>";
					$quantity_available = $item['quantity_inventory'] - $item['quantity_reserved'];
					if( $quantity_available > 0 ) {
						$content .= $quantity_available;
					} else {
						$content .= (($item['flags']&0x04)>0?'Backordered':'Sold out');
					}
					$content .= "</td>";
				}
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
			$num_cols = 3;
			if( $inv == 'yes' ) { $num_cols++; }
			if( $cart['shipping_amount'] > 0 || (isset($cart['taxes']) && count($cart['taxes']) > 0) ) {
				$content .= "<tr class='" . (($count%2)==0?'item-even':'item-odd') . "'>";
				$content .= "<td colspan='$num_cols' class='alignright'>Sub-Total:</td>"
					. "<td class='alignright'>"
					. numfmt_format_currency($intl_currency_fmt, $cart['subtotal_amount'], $intl_currency)
					. "</td><td></td></tr>";
				$count++;
			}
			if( isset($cart['shipping_amount']) && $cart['shipping_amount'] > 0 ) {
				$content .= "<tr class='" . (($count%2)==0?'item-even':'item-odd') . "'>";
				$content .= "<td colspan='$num_cols' class='alignright'>Shipping:</td>"
					. "<td class='alignright'>"
					. numfmt_format_currency($intl_currency_fmt, $cart['shipping_amount'], $intl_currency)
					. "</td><td></td></tr>";
				$count++;
			}
			if( isset($cart['taxes']) ) {
				foreach($cart['taxes'] as $tax) {
					$tax = $tax['tax'];
					$content .= "<tr class='" . (($count%2)==0?'item-even':'item-odd') . "'>";
					$content .= "<td colspan='$num_cols' class='alignright'>" . $tax['description'] . ":</td>"
						. "<td class='alignright'>"
						. numfmt_format_currency($intl_currency_fmt, $tax['amount'], $intl_currency)
						. "</td><td></td></tr>";
					$count++;
				}
			}
			$content .= "<tr class='" . (($count%2)==0?'item-even':'item-odd') . "'>";
			$content .= "<td colspan='$num_cols' class='alignright'><b>Total:</b></td>"
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
			if( isset($ciniki['session']['customer']['dealer_status']) 
				&& $ciniki['session']['customer']['dealer_status'] == 10 ) {
				$content .= "<span class='cart-submit'>"
					. "<input class='cart-submit' type='submit' name='submitorder' value='Submit Order'/>"
					. "</span>";

			} else {
				$content .= "<span class='cart-submit'>"
					. "<input class='cart-submit' type='submit' name='checkout' value='Checkout'/>"
					. "</span>";
			}
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
