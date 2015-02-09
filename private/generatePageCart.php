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
	$display_signup = 'no';
	$cart_err_msg = '';
	$signup_err_msg = '';
	$cart = NULL;
	$cart_edit = 'yes';
	$errors = array();
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

	//
	// Check if no customer, and create dummy information
	//
	if( !isset($ciniki['session']['customer']) ) {
		$_SESSION['customer'] = array(
			'price_flags'=>0x01,
			'pricepoint_id'=>0,
			'first'=>'',
			'last'=>'',
			'display_name'=>'',
			'email'=>'',
			);
		$ciniki['session']['customer'] = $_SESSION['customer'];
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
	elseif( (isset($_POST['update']) && $_POST['update'] != '' 
			&& isset($_POST['action']) && $_POST['action'] == 'update')
		|| (isset($_POST['submitorder']) && $_POST['submitorder'] != '') 
		) {
		$update_args = array();
		if( isset($_POST['po_number']) ) {
			$update_args['po_number'] = $_POST['po_number'];
		}
		if( isset($_POST['customer_notes']) ) {
			$update_args['customer_notes'] = $_POST['customer_notes'];
		}
		if( count($update_args) > 0 ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'web', 'cartUpdate');
			$rc = ciniki_sapos_web_cartUpdate($ciniki, $settings, 
				$ciniki['request']['business_id'], $update_args);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
		}
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
		if( !isset($_POST['submitorder']) ) {
			header("Location: " . $ciniki['request']['ssl_domain_base_url'] . "/cart");
			exit;
		}

		//
		// Incase redirect fails, or submiting an order, Load the updated cart
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
	if( isset($_POST['submitorder']) && $_POST['submitorder'] != ''
		&& isset($ciniki['session']['customer']['dealer_status']) 
		&& $ciniki['session']['customer']['dealer_status'] > 0 
		&& $ciniki['session']['customer']['dealer_status'] < 60 
		) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'web', 'checkOrder');
		$rc = ciniki_sapos_web_checkOrder($ciniki, $settings, $ciniki['request']['business_id'], $cart);
		if( $rc['stat'] == 'warn' ) {
			$cart_err_msg .= "<p class='wide cart-error'>" . $rc['err']['msg'] . "</p>";
			$display_cart = 'yes';
		} elseif( $rc['stat'] != 'ok' ) {
			return $rc;
		} else {
			$display_cart = 'confirm';
			$cart_edit = 'no';
		}
	}
	//
	// Check if dealer has confirmed the order
	//
	elseif( isset($_POST['confirmorder']) && $_POST['confirmorder'] != ''
		&& isset($ciniki['session']['customer']['dealer_status']) 
		&& $ciniki['session']['customer']['dealer_status'] > 0 
		&& $ciniki['session']['customer']['dealer_status'] < 60 
		) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'web', 'submitOrder');
		$rc = ciniki_sapos_web_submitOrder($ciniki, $settings, $ciniki['request']['business_id'], $cart);
		if( $rc['stat'] == 'warn' ) {
			$cart_err_msg .= "<p class='wide cart-error'>" . $rc['err']['msg'] . "</p>";
			$display_cart = 'yes';
		} elseif( $rc['stat'] != 'ok' ) {
			return $rc;
		} else {
			$content .= "<p>Your order has been submitted.</p>";
			//
			// Email the receipt to the dealer
			//
			if( isset($settings['page-cart-dealersubmit-email-template']) 
				&& $settings['page-cart-dealersubmit-email-template'] != '' 
				&& isset($cart['customer']['emails'][0]['email']['address'])
				) {
				//
				// Load business details
				//
				ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'businessDetails');
				$rc = ciniki_businesses_businessDetails($ciniki, $ciniki['request']['business_id']);
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				$business_details = array();
				if( isset($rc['details']) && is_array($rc['details']) ) {	
					$business_details = $rc['details'];
				}

				//
				// Load the invoice settings
				//
				ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash');
				$rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_sapos_settings', 'business_id', $ciniki['request']['business_id'],
					'ciniki.sapos', 'settings', 'invoice');
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				$sapos_settings = array();
				if( isset($rc['settings']) ) {
					$sapos_settings = $rc['settings'];
				}
				
				//
				// Create the pdf
				//
				$rc = ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'templates', $settings['page-cart-dealersubmit-email-template']);
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				$fn = $rc['function_call'];
				$rc = $fn($ciniki, $ciniki['request']['business_id'], $cart['id'], $business_details, $sapos_settings, 'email');
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}

				//
				// Email the pdf to the customer
				//
				$filename = $rc['filename'];
				$invoice = $rc['invoice'];
				$pdf = $rc['pdf'];


				$subject = "Order #" . $invoice['invoice_number'];
				$textmsg = "Thank you for your order, please find the order summary attached.";
				if( isset($settings['page-cart-dealersubmit-email-textmsg']) 
					&& $settings['page-cart-dealersubmit-email-textmsg'] != '' 
					) {
					$textmsg = $settings['page-cart-dealersubmit-email-textmsg'];
				}	
				$ciniki['emailqueue'][] = array('to'=>$invoice['customer']['emails'][0]['email']['address'],
					'to_name'=>(isset($invoice['customer']['display_name'])?$invoice['customer']['display_name']:''),
					'business_id'=>$ciniki['request']['business_id'],
					'subject'=>$subject,
					'textmsg'=>$textmsg,
					'attachments'=>array(array('string'=>$pdf->Output('invoice', 'S'), 'filename'=>$filename)),
					);
			}

			$display_cart = 'no';
			$cart = NULL;
			unset($_SESSION['cart']);
			unset($ciniki['session']['cart']);
		}
	}

	//
	// Check if checkout
	//
	elseif( isset($_POST['checkout']) && $_POST['checkout'] != '' && $cart != NULL ) {
		if( isset($cart['customer_id']) && $cart['customer_id'] > 0 ) {
			$content .= "ERROR - Unable to Process checkout";
			$display_cart = 'review';
			$cart_edit = 'no';
		} else {
			$display_signup = 'yes';
			$display_cart = 'no';
		}
	}

	//
	// Check if returned from Paypal
	//


	if( $display_signup == 'yes' || $display_signup == 'forgot' ) {
		$content .= "<article class='page cart'>\n";
		$post_email = '';
		if( isset($_POST['email']) ) {
			$post_email = $_POST['email'];
		}
		$content .= "<aside>";
		// Javascript to switch forms	
		$ciniki['request']['inline_javascript'] = "<script type='text/javascript'>\n"
			. "	function swapLoginForm(l) {\n"
			. "		if( l == 'forgotpassword' ) {\n"
			. "			document.getElementById('signin-form').style.display = 'none';\n"
			. "			document.getElementById('forgotpassword-form').style.display = 'block';\n"
			. "			document.getElementById('forgotemail').value = document.getElementById('email').value;\n"
			. "		} else {\n"
			. "			document.getElementById('signin-form').style.display = 'block';\n"
			. "			document.getElementById('forgotpassword-form').style.display = 'none';\n"
			. "		}\n"
			. "		return true;\n"
			. "	}\n"
			. "</script>"
			. "";
//			$content .= "<div class='entry-content'>";
		$content .= "<div id='signin-form' style='display:" . ($display_signup=='yes'?'block':'none') . ";'>\n";
		$content .= "<h2>Existing Account</h2>";
		$content .= "<p>Bought something here before? Please sign in to your account:</p>";
		$content .= "<form action='" .  $ciniki['request']['ssl_domain_base_url'] . "/account' method='POST'>";
		if( $signup_err_msg != '' ) {
			$content .= "<p class='formerror'>$signup_err_msg</p>\n";
		}
		$content .="<input type='hidden' name='action' value='signin'>\n"
			. "<div class='input'><label for='email'>Email</label><input id='email' type='email' class='text' maxlength='250' name='email' value='$post_email' /></div>\n" 
			. "<div class='input'><label for='password'>Password</label><input id='password' type='password' class='text' maxlength='100' name='password' value='' /></div>\n"
			. "<div class='submit'><input type='submit' class='submit' value='Sign In' /></div>\n"
			. "</form>"
			. "<br/>";
		if( !isset($settings['page-account-password-change']) 
			|| $settings['page-account-password-change'] == 'yes' ) {
			$content .= "<div id='forgot-link'><p>"
				. "<a class='color' href='javscript:void(0);' onclick='swapLoginForm(\"forgotpassword\");return false;'>Forgot your password?</a></p></div>\n";
		}
		$content .= "</div>\n";

		// Forgot password form
		$content .= "<div id='forgotpassword-form' style='display:" . ($display_signup=='forgot'?'block':'none') . ";'>\n";
		$content .= "<h2>Forgot Password</h2>";
		$content .= "<p>Please enter your email address and you will receive a link to create a new password.</p>";
		$content .= "<form action='" .  $ciniki['request']['ssl_domain_base_url'] . "/account' method='POST'>";
		if( $signup_err_msg != '' ) {
			$content .= "<p class='formerror'>$signup_err_msg</p>\n";
		}
		$content .= "<input type='hidden' name='action' value='forgot'>\n"
			. "<div class='input'><label for='forgotemail'>Email </label><input id='forgotemail' type='email' class='text' maxlength='250' name='email' value='$post_email' /></div>\n" 
			. "<div class='submit'><input type='submit' class='submit' value='Get New Password' /></div>\n"
			. "</form>"
			. "<br/>"
			. "<div id='forgot-link'><p><a class='color' href='javascript:void();' onclick='swapLoginForm(\"signin\"); return false;'>Sign In</a></p></div>\n"
			. "</div>\n";

		$content .= "</aside>";

		//
		// Signup for a new account form
		//
		$content .= "<h2>Create a new account</h2>";
		$content .= "<form action='" .  $ciniki['request']['ssl_domain_base_url'] . "/cart' method='POST'>";
		$fields = array(
			'first'=>array('name'=>'First Name', 'type'=>'text', 'class'=>'text', 'value'=>(isset($_POST['first'])?$_POST['first']:'')),
			'last'=>array('name'=>'Last Name', 'type'=>'text', 'class'=>'text', 'value'=>(isset($_POST['last'])?$_POST['last']:'')),
			'phone'=>array('name'=>'Phone Number', 'type'=>'text', 'class'=>'text', 'value'=>(isset($_POST['phone'])?$_POST['phone']:'')),
			'email'=>array('name'=>'Email Address', 'type'=>'email', 'class'=>'text', 'value'=>(isset($_POST['email'])?$_POST['email']:'')),
			);
		foreach($fields as $fid => $field) {
			$content .= "<div class='input'><label for='$fid'>" . $field['name'] . "</label>"
				. "<input type='" . $field['type'] . "' class='" . $field['class'] . "' name='$fid' value='" . $field['value'] . "'>";
			if( isset($errors[$fid]) && $errors[$fid] != '' ) {
				$content .= "<p class='formerror'>" . $errors[$fid] . "</p>";
			}
			$content .= "</div>";
		}

		$content .= "<div class='submit'><input type='submit' name='continue' class='submit' value='Continue Shopping' />";
		$content .= " <input type='submit' name='next' class='submit' value='Next' /></div>\n";
//		$content .= "<div class='cart-buttons'>";
//		$content .= "<span class='cart-submit'>"
//			. "<input class='cart-submit' type='submit' name='continue' value='Continue Shopping'/>"
//			. "</span>";
//		$content .= "</div>";
		$content .= "</form>";
		$content .= "</article>\n";
	}

	//
	// Display the contents of the shopping cart
	//
	if( $display_cart == 'yes' || $display_cart == 'confirm' || $display_cart == 'review' ) {
		$content .= "<article class='page cart'>\n"
//			. "<form action='" .  $ciniki['request']['ssl_domain_base_url'] . "/cart' method='POST'>"
			. "<header class='entry-title'>"
			. "<h1 id='entry-title' class='entry-title'>$page_title</h1>";
		if( isset($settings['page-cart-product-search']) 
			&& $settings['page-cart-product-search'] == 'yes' 
			) {
			$content .= "<div class='cart-search-input'>";
		
			if( $cart_edit == 'yes' ) {
				$content .= "<form id='cart-search-form' action='" .  $ciniki['request']['ssl_domain_base_url'] . "/cart' method='POST'>"
					. "<input type='hidden' name='action' value='add'/>"
					. "<input id='cart-search-form-object' type='hidden' name='object' value='' />"
					. "<input id='cart-search-form-object_id' type='hidden' name='object_id' value='' />"
					. "<input id='cart-search-form-price_id' type='hidden' name='price_id' value='' />"
					. "<input id='cart-search-form-final_price' type='hidden' name='final_price' value='' />"
					. "<input id='cart-search-form-quantity' type='hidden' name='quantity' value='1' />"
					. "<label for='search_str'></label><input id='cart-search-str' class='input' type='text' autofocus placeholder='Search' name='search_str' value='" . (isset($_POST['search_str'])?$_POST['search_str']:'') . "' onkeyup='return update_cart_search();' onsearch='return update_cart_search();' onsubmit='return false;' autocomplete='off' />"
					. "</form>";
			}
			$content .= "</div>";
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
			&& $ciniki['session']['customer']['dealer_status'] > 0 
			&& $ciniki['session']['customer']['dealer_status'] < 60 
			) {
			$inv = 'yes';
		}

		//
		// Check if we should display the search box
		//
		if( isset($settings['page-cart-product-search']) 
			&& $settings['page-cart-product-search'] == 'yes' 
			&& $cart_edit == 'yes' 
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
				. "<th class='aligncenter'>Item</th>"
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
		//
		// Check if there is a review message
		//
		elseif( $display_cart == 'review' ) {
			$content .= "<p>Please review your order.</p>";
		}


		if( $inv == 'yes' ) {
			$item_objects = array();
			ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'private', 'getReservedQuantities');
			if( isset($cart['items']) ) {
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
			}
			foreach($item_objects as $o => $oids) {
				//
				// Get current inventory
				//
				list($pkg, $mod, $obj) = explode('.', $o);
				$object_ids = array_keys($oids);
				$rc = ciniki_core_loadMethod($ciniki, $pkg, $mod, 'sapos', 'cartItemsDetails');
				if( $rc['stat'] == 'ok') {
					$fn = $pkg . '_' . $mod . '_sapos_cartItemsDetails';
					$rc = $fn($ciniki, $ciniki['request']['business_id'], array(
						'object'=>$o, 'object_ids'=>$object_ids));
					if( isset($rc['details']) ) {
						foreach($rc['details'] as $detail) {
							$item_id = $item_objects[$o][$detail['object_id']];
							$cart['items'][$item_id]['item']['quantity_inventory'] = $detail['quantity_inventory'];
							$cart['items'][$item_id]['item']['permalink'] = $detail['permalink'];
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
			$content .= "<form action='" .  $ciniki['request']['ssl_domain_base_url'] . "/cart' class='wide' method='POST'>";
			$content .= "<input type='hidden' name='action' value='update'/>";
			if( $cart_err_msg != '' ) {
				$content .= $cart_err_msg;
			}
			$content .= "<div class='cart-items'>";
			$content .= "<table class='cart-items'>";
			$content .= "<thead><tr>"
				. "<th class='aligncenter'>Item</th>"
				. "<th class='alignright'>Quantity</th>"
				. ($inv=='yes'?"<th class='alignright'>Inventory</th>":"")
				. "<th class='alignright'>Price</th>"
				. "<th class='alignright'>Total</th>"
				. ($cart_edit=='yes'?"<th>Actions</th>":"")
				. "</tr></thead>";
			$content .= "<tbody>";
			$count=0;
			foreach($cart['items'] as $item_id => $item) {
				$item = $item['item'];
				$content .= "<tr class='" . (($count%2)==0?'item-even':'item-odd') . "'>"
					. "<td>";
				if( isset($item['object']) && isset($item['permalink']) ) {
					switch($item['object']) {
						case 'ciniki.products.product': 
							$item['url'] = $ciniki['request']['base_url'] . '/products/product/' . $item['permalink'];
							break;
					}
				}
				if( isset($item['url']) && $item['url'] != '' ) {
					$content .= "<a href='" . $item['url'] . "'>" . $item['description'] . "</a>";
				} else {
					$content .= $item['description'];
				}
				$content .= "</td>";
				$content .= "<td class='alignright'>";
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
				if( $cart_edit == 'yes' ) {
					$content .= "<td class='aligncenter'>"
						. "<span class='cart-submit'>"
	//					. "<input class='cart-submit' onclick='alert(document.getElementById(\"quantity_" . $item['id'] . "\").value);return true;' type='submit' name='update_delete_" . $item['id'] . "' value='Delete'/>"
						. "<input class='cart-submit' onclick='document.getElementById(\"quantity_" . $item['id'] . "\").value=0;return true;' type='submit' name='update' value='Delete'/>"
						. "</span>"
						. "</td>";
				}
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
					. "</td>"
					. ($cart_edit=='yes'?'<td></td>':'') . "</tr>";
				$count++;
			}
			if( isset($cart['shipping_amount']) && $cart['shipping_amount'] > 0 ) {
				$content .= "<tr class='" . (($count%2)==0?'item-even':'item-odd') . "'>";
				$content .= "<td colspan='$num_cols' class='alignright'>Shipping:</td>"
					. "<td class='alignright'>"
					. numfmt_format_currency($intl_currency_fmt, $cart['shipping_amount'], $intl_currency)
					. "</td>"
					. ($cart_edit=='yes'?'<td></td>':'') . "</tr>";
				$count++;
			}
			if( isset($cart['taxes']) ) {
				foreach($cart['taxes'] as $tax) {
					$tax = $tax['tax'];
					$content .= "<tr class='" . (($count%2)==0?'item-even':'item-odd') . "'>";
					$content .= "<td colspan='$num_cols' class='alignright'>" . $tax['description'] . ":</td>"
						. "<td class='alignright'>"
						. numfmt_format_currency($intl_currency_fmt, $tax['amount'], $intl_currency)
						. "</td>"
						. ($cart_edit=='yes'?'<td></td>':'') . "</tr>";
					$count++;
				}
			}
			$content .= "<tr class='" . (($count%2)==0?'item-even':'item-odd') . "'>";
			$content .= "<td colspan='$num_cols' class='alignright'><b>Total:</b></td>"
				. "<td class='alignright'>"
				. numfmt_format_currency($intl_currency_fmt, $cart['total_amount'], $intl_currency)
				. "</td>"
				. ($cart_edit=='yes'?'<td></td>':'') . "</tr>";
			$count++;
			$content .= "</foot>";
			$content .= "</table>";
			$content .= "</div>";
			$content .= "<br/>";

			//
			// Display the bill to and ship to information
			//
			$count = 1;
			$cart_details = '';
			if( isset($settings['page-cart-po-number']) && $settings['page-cart-po-number'] != 'no' ) {
				$cart_details .= "<tr class='" . (($count%2)==0?'item-even':'item-odd') . "'>";
				$cart_details .= "<th>PO Number:</td>" . "<td>";
				if( $cart_edit == 'yes' ) {
					$cart_details .= "<input id='po_number' class='text' type='text' placeholder='PO Number' "
							. "name='po_number' value='" . $cart['po_number'] . "' />";
				} else {
					$cart_details .= $cart['po_number'];
				}
				$cart_details .= "</td></tr>";
				$count++;
			}
			$baddr = '';
			if( isset($cart['billing_name']) && $cart['billing_name'] != '' ) {
				$baddr .= ($baddr!=''?'<br/>':'') . $cart['billing_name'];
			}
			if( isset($cart['billing_address1']) && $cart['billing_address1'] != '' ) {
				$baddr .= ($baddr!=''?'<br/>':'') . $cart['billing_address1'];
			}
			if( isset($cart['billing_address2']) && $cart['billing_address2'] != '' ) {
				$baddr .= ($baddr!=''?'<br/>':'') . $cart['billing_address2'];
			}
			$city = '';
			if( isset($cart['billing_city']) && $cart['billing_city'] != '' ) {
				$city .= ($city!=''?'':'') . $cart['billing_city'];
			}
			if( isset($cart['billing_province']) && $cart['billing_province'] != '' ) {
				$city .= ($city!=''?', ':'') . $cart['billing_province'];
			}
			if( isset($cart['billing_postal']) && $cart['billing_postal'] != '' ) {
				$city .= ($city!=''?'  ':'') . $cart['billing_postal'];
			}
			if( $city != '' ) { 
				$baddr .= ($baddr!=''?'<br/>':'') . $city;
			}
			if( isset($cart['billing_country']) && $cart['billing_country'] != '' ) {
				$baddr .= ($baddr!=''?'<br/>':'') . $cart['billing_country'];
			}
			if( $baddr != '' ) {
				$cart_details .= "<tr class='" . (($count%2)==0?'item-even':'item-odd') . "'>";
				$cart_details .= "<th>Bill To:</th><td>";
				$cart_details .= $baddr;
				$cart_details .= "</td></tr>";
				$count++;
			}
			$saddr = '';
			if( isset($cart['shipping_name']) && $cart['shipping_name'] != '' ) {
				$saddr .= ($saddr!=''?'<br/>':'') . $cart['shipping_name'];
			}
			if( isset($cart['shipping_address1']) && $cart['shipping_address1'] != '' ) {
				$saddr .= ($saddr!=''?'<br/>':'') . $cart['shipping_address1'];
			}
			if( isset($cart['shipping_address2']) && $cart['shipping_address2'] != '' ) {
				$saddr .= ($saddr!=''?'<br/>':'') . $cart['shipping_address2'];
			}
			$city = '';
			if( isset($cart['shipping_city']) && $cart['shipping_city'] != '' ) {
				$city .= ($city!=''?'':'') . $cart['shipping_city'];
			}
			if( isset($cart['shipping_province']) && $cart['shipping_province'] != '' ) {
				$city .= ($city!=''?', ':'') . $cart['shipping_province'];
			}
			if( isset($cart['shipping_postal']) && $cart['shipping_postal'] != '' ) {
				$city .= ($city!=''?'  ':'') . $cart['shipping_postal'];
			}
			if( $city != '' ) { 
				$saddr .= ($saddr!=''?'<br/>':'') . $city;
			}
			if( isset($cart['shipping_country']) && $cart['shipping_country'] != '' ) {
				$saddr .= ($saddr!=''?'<br/>':'') . $cart['shipping_country'];
			}
			if( $saddr != '' ) {
				$cart_details .= "<tr class='" . (($count%2)==0?'item-even':'item-odd') . "'>";
				$cart_details .= "<th>Ship To:</th><td>";
				$cart_details .= $saddr;
				$cart_details .= "</td></tr>";
				$count++;
			}

			if( $cart_details != '' ) {
				$content .= "<div class='cart-details'>";
				$content .= "<table class='cart-details'>";
				$content .= "<tbody>";
				$content .= $cart_details;
				$content .= "</tbody>";
				$content .= "</table>";
				$content .= "</div>";
			}

			if( isset($settings['page-cart-customer-notes']) && $settings['page-cart-customer-notes'] == 'yes' ) {
				if( $cart_edit == 'yes' ) {
				$content .= "<label for='customer_notes'>Notes</label>"
					. "<textarea class='' class='text' id='customer_notes' name='customer_notes'>" 
					. $cart['customer_notes'] 
					. "</textarea>"
					. "";
				} else {
					$content .= "<label for='customer_notes'>Notes</label>"
						. "<p>" . $cart['customer_notes'] . "</p>";
				}
			}
					
			// cart buttons
//			$content .= "<table class='cart-buttons'>"
//				. "<tfoot>";
//			$content .= "<tr><td class='aligncenter'>";
			$content .= "<div class='cart-buttons'>";
			$content .= "<span class='cart-submit'>"
				. "<input class='cart-submit' type='submit' name='continue' value='Continue Shopping'/>"
				. "</span>";
			if( $cart_edit == 'yes' ) {
				$content .= "<span class='cart-submit'>"
					. "<input class='cart-submit' type='submit' name='update' value='Update'/>"
					. "</span>";
			}
			if( isset($ciniki['session']['customer']['dealer_status']) 
				&& $ciniki['session']['customer']['dealer_status'] > 0 
				&& $ciniki['session']['customer']['dealer_status'] < 60 
				) {
				if( $display_cart == 'confirm' ) {
					$content .= "<span class='cart-submit'>"
						. "<input class='cart-submit' type='submit' name='confirmorder' value='Confirm Order'/>"
						. "</span>";
				} else {
					$content .= "<span class='cart-submit'>"
						. "<input class='cart-submit' type='submit' name='submitorder' value='Submit Order'/>"
						. "</span>";
				}
			} else {
				if( $display_cart == 'review' ) {
					$content .= "<span class='cart-submit'>"
						. "<input class='cart-submit' type='submit' name='confirmorder' value='Checkout via Paypal'/>"
						. "</span>";
				} else {
					$content .= "<span class='cart-submit'>"
						. "<input class='cart-submit' type='submit' name='checkout' value='Checkout'/>"
						. "</span>";
				}
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
