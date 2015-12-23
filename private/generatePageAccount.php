<?php
//
// Description
// -----------
// This function will generate a customers account page.  This page allows the customer
// to login to their account, change their password and subscribe/unsubscribe to public
// subscriptions (newsletters).
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_generatePageAccount(&$ciniki, $settings) {

    $breadcrumbs = array();
    $breadcrumbs[] = array('name'=>'Account', 'url'=>$ciniki['request']['domain_base_url'] . '/account');

	//
	// Check if should be forced to SSL
	//
	if( isset($settings['site-ssl-force-account']) 
		&& $settings['site-ssl-force-account'] == 'yes' 
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
    // Check if logout was requested
    //
    if( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] == 'logout' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageAccountLogout');
        return ciniki_web_generatePageAccountLogout($ciniki, $settings, $ciniki['request']['business_id']);
    }

    //
    // Check if customer is logged in, or display the login form/forgot password form.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageAccountLogin');
    $rc = ciniki_web_generatePageAccountLogin($ciniki, $settings, $ciniki['request']['business_id'], $breadcrumbs);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( $rc['stat'] == 'ok' && isset($rc['content']) ) {
        // Login form display or welcome page
        return $rc;
    }

    //
    // NOTE: At this point the customer is considered logged in
    //

    //
    // Check if there was a switch of customer (parent switching between child accounts)
    // This is a special case because of the redirects
    //
	if( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] == 'switch'
		&& isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] != '' 
		&& isset($ciniki['session']['customers'])
		&& isset($ciniki['session']['customers'][$ciniki['request']['uri_split'][1]])
		) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageAccountSwitch', $breadcrumbs);
        return ciniki_web_generatePageAccountSwitch($ciniki, $settings, $ciniki['request']['business_id'], $ciniki['request']['uri_split'][1]);
    }

//    print "<pre>" . print_r($ciniki['request'], true) . "</pre>";
//    print "<pre>" . print_r($ciniki['business'], true) . "</pre>";
//    exit;

    //
    // Gather the submodule menu items
    //
    $submenu = array();
    foreach($ciniki['business']['modules'] as $module => $m) {
        list($pkg, $mod) = explode('.', $module);
        $rc = ciniki_core_loadMethod($ciniki, $pkg, $mod, 'web', 'accountSubMenuItems');
        if( $rc['stat'] == 'ok' ) {
            $fn = $rc['function_call'];
            $rc = $fn($ciniki, $settings, $ciniki['request']['business_id']);
            if( $rc['stat'] == 'ok' && isset($rc['submenu']) ) {
                $submenu = array_merge($submenu, $rc['submenu']);
            }
        }
    }

    //
    // Sort the menu items by priority
    //
    usort($submenu, function($a, $b) {
        if( $a['priority'] == $b['priority'] ) {
            return 0;
        }
        // Sort so largest priority is top of list or first menu item
        return ($a['priority'] < $b['priority'])?1:-1;
    });

    //
    // Check for a module to process the request
    //
    $requested_item = null;
    $base_url = $ciniki['request']['base_url'] . '/account';
    if( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] != '' ) {
        $requested_page_url = $base_url . '/' . $ciniki['request']['uri_split'][0];
        foreach($submenu as $item) {
            if( strncmp($requested_page_url, $item['url'], strlen($requested_page_url)) == 0 ) {
                $requested_item = $item;
                break;
            }
        }
    } 
    //
    // Nothing requested, default to the first item in the submenu
    //
    elseif( isset($submenu[0]) ) {
        $requested_item = $submenu[0];
        if( !isset($ciniki['request']['uri_split'][0]) ) {
            $ciniki['request']['uri_split'] = explode('/', preg_replace('#' . $base_url . '#', '', $requested_item['url'], 1));
            if( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] == '' ) {
                array_shift($ciniki['request']['uri_split']);
            }
        }
    } 

    if( $requested_item == null ) {
        return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'2907', 'msg'=>'Requested page not found.'));
    }

    //
    // Process the request
    //
    $article_title = '';
    $content = '';
    $rc = ciniki_core_loadMethod($ciniki, $requested_item['package'], $requested_item['module'], 'web', 'accountProcessRequest');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'2908', 'msg'=>'Requested page not found.', 'err'=>$rc['err']));
    }
    $fn = $rc['function_call'];
    $rc = $fn($ciniki, $settings, $ciniki['request']['business_id'], array(
        'page_title'=>'Account', 
        'breadcrumbs'=>$breadcrumbs,
        'base_url'=>$base_url,
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $page = $rc['page'];
    if( isset($page['breadcrumbs']) ) {
        $breadcrumbs = $page['breadcrumbs'];
    }
    $article_title = $page['title'];

    if( isset($settings['page-account-sidebar']) && $settings['page-account-sidebar'] != 'no' ) {
        $sidebar_menu = $submenu;
        $submenu = array();
    }

	//
	// Add the header
	//
    $ciniki['request']['page-container-class'] = 'page-account';
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageHeader');
	$rc = ciniki_web_generatePageHeader($ciniki, $settings, 'Account', $submenu);
	if( $rc['stat'] != 'ok' ) {	
		return $rc;
	}
	$page_content = $rc['content'];
	
	//
	// Check if article title and breadcrumbs should be displayed above content
	//
	if( (isset($settings['theme']['header-article-title']) && $settings['theme']['header-article-title'] == 'yes')
		|| (isset($settings['theme']['header-breadcrumbs']) && $settings['theme']['header-breadcrumbs'] == 'yes')
		) {
		$page_content .= "<div class='page-header'>";
		if( isset($settings['theme']['header-article-title']) && $settings['theme']['header-article-title'] == 'yes' ) {
			$page_content .= "<h1 class='page-header-title'>" . $page['title'] . "</h1>";
		}
		if( isset($settings['theme']['header-breadcrumbs']) && $settings['theme']['header-breadcrumbs'] == 'yes' && isset($breadcrumbs) ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processBreadcrumbs');
			$rc = ciniki_web_processBreadcrumbs($ciniki, $settings, $ciniki['request']['business_id'], $breadcrumbs);
			if( $rc['stat'] == 'ok' ) {
				$page_content .= $rc['content'];
			}
		}
		$page_content .= "</div>";
	}

    $page_content .= "<div id='content'>";

    if( isset($settings['page-account-sidebar']) && $settings['page-account-sidebar'] == 'left' ) {
        //
        // Add the sidebar content
        //
        $page_content .= "<aside class='col-left-narrow'>";
        $page_content .= "<div class='aside-content'>";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processBlockMenu');
        $rc = ciniki_web_processBlockMenu($ciniki, $settings, $ciniki['request']['business_id'], array('title'=>'', 'menu'=>$sidebar_menu));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $page_content .= $rc['content'];
        $page_content .= "</div>";
        $page_content .= "</aside>";

        $page_content .= "<article class='page col-right-wide'>\n";
    } elseif( isset($settings['page-account-sidebar']) && $settings['page-account-sidebar'] == 'right' ) {
        $page_content .= "<article class='page col-left-wide'>\n";
    } else {
        $page_content .= "<article class='page'>\n";
    }

    $page_content .= "<header class='entry-title'><h1 id='entry-title' class='entry-title'>$article_title</h1></header>";

	//
	// Process the blocks of content
	//
    $page_content .= "<div class='entry-content'>\n";
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processBlocks');
	if( isset($page['blocks']) ) {
		$rc = ciniki_web_processBlocks($ciniki, $settings, $ciniki['request']['business_id'], $page['blocks']);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$page_content .= $rc['content'];
	}
    $page_content .= "</div>";
    $page_content .= "</article>";

    if( isset($settings['page-account-sidebar']) && $settings['page-account-sidebar'] == 'right' ) {
        //
        // Add the sidebar content
        //
        $page_content .= "<aside class='col-right-narrow'>";
        $page_content .= "<div class='aside-content'>";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processBlockMenu');
        $rc = ciniki_web_processBlockMenu($ciniki, $settings, $ciniki['request']['business_id'], array('title'=>'', 'menu'=>$sidebar_menu));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $page_content .= $rc['content'];
        $page_content .= "</div>";
        $page_content .= "</aside>";
    }

    $page_content .= "</div>";

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
/*
	//
	// Store the content created by the page
	// Make sure everything gets generated ok before returning the content
	//
	$content = '';
	$subscription_err_msg = '';
	$chgpwd_err_msg = '';
	$submenu = array();
    $article_title = 'Account';

	//
	// Pull in order stats
	//
	if( isset($modules['ciniki.sapos']) && isset($ciniki['session']['customer']['id']) ) {
		$open_orders = -1;
		$past_orders = -1;
		ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'web', 'customerStats');
		$rc = ciniki_sapos_web_customerStats($ciniki, $settings, $ciniki['request']['business_id'], $ciniki['session']['customer']['id']);
		if( $rc['stat'] == 'ok' && isset($rc['stats']) ) {
			if( isset($rc['stats']['invoices']['typestatus']['40.15']) ) {
				$open_orders += $rc['stats']['invoices']['typestatus']['40.15'];
			}
			if( isset($rc['stats']['invoices']['typestatus']['40.30']) ) {
				$open_orders += $rc['stats']['invoices']['typestatus']['40.30'];
			}
			if( isset($rc['stats']['invoices']['typestatus']['40.50']) ) {
				$open_orders += $rc['stats']['invoices']['typestatus']['40.50'];
			}
		}
	}

	//
	// Check if a form was submitted
	//
	$err_msg = '';
	$display_form = 'login';
		//
		// FIXME: Download the invoice PDF
		//
		elseif( $_POST['action'] == 'downloadorder' 
			&& isset($_POST['invoice_id']) && $_POST['invoice_id'] != '' 
			&& isset($ciniki['session']['customer']['id'])
			&& isset($settings['page-account-invoices-view-pdf']) 
			&& $settings['page-account-invoices-view-pdf'] == 'yes'
			) {
			//
			// Load business details
			//
			ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'businessDetails');
			$rc = ciniki_businesses_businessDetails($ciniki, $ciniki['request']['business_id']);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			if( isset($rc['details']) && is_array($rc['details']) ) {	
				$business_details = $rc['details'];
			} else {
				$business_details = array();
			}

			//
			// Load the invoice settings
			//
			ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash');
			$rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_sapos_settings', 'business_id', 
				$ciniki['request']['business_id'], 'ciniki.sapos', 'settings', 'invoice');
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			if( isset($rc['settings']) ) {
				$sapos_settings = $rc['settings'];
			} else {
				$sapos_settings = array();
			}
			
			//
			// check for invoice-default-template
			//
			if( isset($args['type']) && $args['type'] == 'picklist' ) {
				$invoice_template = 'picklist';
			} else {
				if( !isset($sapos_settings['invoice-default-template']) 
					|| $sapos_settings['invoice-default-template'] == '' ) {
					$invoice_template = 'default';
				} else {
					$invoice_template = $sapos_settings['invoice-default-template'];
				}
			}
	
			//
			// Check the invoice belongs to the customer
			//
			$strsql = "SELECT id, customer_id "
				. "FROM ciniki_sapos_invoices "
				. "WHERE id = '" . ciniki_core_dbQuote($ciniki, $_POST['invoice_id']) . "' "
				. "AND business_id = '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['business_id']) . "' "
				. "";
			$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.sapos', 'invoice');
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			if( !isset($rc['invoice']) ) {
				return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'2062', 'msg'=>'Invalid invoice'));
			}

			$rc = ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'templates', 'order');
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$fn = $rc['function_call'];

			return $fn($ciniki, $ciniki['request']['business_id'], $_POST['invoice_id'], 
				$business_details, $sapos_settings);
			exit;
		}
	}

	//
	// Check if the customer is logged in or not
	//
	elseif( isset($ciniki['session']['customer']['id']) && $ciniki['session']['customer']['id'] > 0 ) {
		$submenu = array();
		$subpage = '';
		if( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] != '' 
			&& in_array($ciniki['request']['uri_split'][0], array('accounts', 'orders', 'subscriptions', 'changepassword')) 
			) {
			$subpage = $ciniki['request']['uri_split'][0];
		}
		if( isset($ciniki['session']['customers']) && count($ciniki['session']['customers']) > 1 ) {
			$submenu['accounts'] = array('name'=>'Accounts',
				'url'=>$ciniki['request']['base_url'] . '/account/accounts');
			if( $subpage == '' ) { 
				$subpage = 'accounts';
			}
		}
//		if( isset($open_orders) && $open_orders > 0 ) {
//			$submenu['openorders'] = array('name'=>'Orders',
//				'url'=>$ciniki['request']['base_url'] . '/account/openorders');
//			if( $subpage == '' ) { 
//				$subpage = 'openorders';
//			}
//		}
		if( ((isset($open_orders) && $open_orders > 0) || (isset($past_orders) && $past_orders > 0))
			&& isset($settings['page-account-invoices-list']) 
			&& $settings['page-account-invoices-list'] == 'yes'
			) {
			$submenu['orders'] = array('name'=>'Orders',
				'url'=>$ciniki['request']['base_url'] . '/account/orders');
			if( $subpage == '' ) { 
				$subpage = 'orders';
			}
		}
		if( isset($modules['ciniki.subscriptions']) && isset($subscriptions) && count($subscriptions) > 0 ) {
			$submenu['subscriptions'] = array('name'=>'Subscriptions',
				'url'=>$ciniki['request']['base_url'] . '/account/subscriptions');
			if( $subpage == '' ) { 
				$subpage = 'subscriptions';
			}
		}
		$submenu['changepassword'] = array('name'=>'Password',
			'url'=>$ciniki['request']['base_url'] . '/account/changepassword');
		if( $subpage == '' ) { 
			$subpage = 'changepassword';
		}

		if( count($submenu) < 2 ) {
			$submenu = array();
		}

		//
		// Get any content for the account page
		//
		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash');
		$rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_web_content', 'business_id', $ciniki['request']['business_id'], 'ciniki.web', 'content', 'page-account');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}

		$content_details = array();
		if( isset($rc['content']) ) {
			$content_details = $rc['content'];
		}

		//
		// Get the details about the customer
		//
		ciniki_core_loadMethod($ciniki, 'ciniki', 'customers', 'web', 'customerDetails');
		$rc = ciniki_customers_web_customerDetails($ciniki, $settings, $ciniki['request']['business_id'], $ciniki['session']['customer']['id']);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( isset($rc['customer']) ) {
			$customer = $rc['customer'];
			$customer_details = $rc['details'];
		} else {
			$customer = array();
			$customer_details = array();
		}

		//
		// Start building the html output
		//
		$content .= "<div id='content'>\n"
			. "<article class='page account'>\n"
//			. "<header class='entry-title'><h1 class='entry-title'>Account</h1></header>\n"
			. "";
		
		elseif( $subpage == 'orders' ) {
			$content .= "<div class='entry-content'>\n";
			ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'web', 'customerOrders');
			$rc = ciniki_sapos_web_customerOrders($ciniki, $settings, $ciniki['request']['business_id'], $ciniki['session']['customer']['id'], array());
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			if( isset($rc['invoices']) ) {
				$invoices = $rc['invoices'];
				$invoice_displayed = 'no';
				//
				// Check if user requested to view the order details
				//
				if( isset($_POST['action']) && $_POST['action'] == 'orderdetails' 
					&& isset($_POST['invoice_id']) && $_POST['invoice_id'] != '' 
					&& isset($ciniki['session']['customer']['id'])
					&& isset($settings['page-account-invoices-view-details']) && $settings['page-account-invoices-view-details'] == 'yes'
					) {
					$invoice_id = $_POST['invoice_id'];
					foreach($invoices as $invoice) {
						if( $invoice['id'] == $invoice_id ) {
							ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'web', 'customerOrder');
							$rc = ciniki_sapos_web_customerOrder($ciniki, $settings, $ciniki['request']['business_id'], 
								$ciniki['session']['customer']['id'], array('invoice_id'=>$invoice['id']));
							if( $rc['stat'] != 'ok' ) {
								$invoice_details_err_msg = "We seem to be having problems locating that invoice, please contact us for assistance.";
								break;
							}
							if( isset($rc['invoice']) ) {
								$customer_invoice = $rc['invoice'];
							}
						}
					}
					//
					// Display the customer invoice
					//
					if( isset($customer_invoice) ) {
						$content .= "<h1 class='entry-title'>Order #" . $customer_invoice['invoice_number'] . "</h1>";
//						$content .= "<pre>" . print_r($customer_invoice, true) . "</pre>";

						//
						// Note: Taken from generatePageCart
						//
						$content .= "<div class='cart cart-items'>";
						$content .= "<table class='cart-items'>";
						$content .= "<thead><tr>"
							. "<th class='aligncenter'>Item</th>"
							. "<th class='alignright'>Qty Ordered</th>"
							. "<th class='alignright'>Qty Shipped</th>"
							. "<th class='alignright'>Price</th>"
							. "<th class='alignright'>Total</th>"
							. "</tr></thead>";
						$content .= "<tbody>";
						$count=0;
						foreach($customer_invoice['items'] as $item_id => $item) {
							$item = $item['item'];
							$content .= "<tr class='" . (($count%2)==0?'item-even':'item-odd') . "'>"
								. "<td>";
//							if( isset($item['object']) && isset($item['permalink']) ) {
//								switch($item['object']) {
//									case 'ciniki.products.product': 
//										$item['url'] = $ciniki['request']['base_url'] . '/products/product/' . $item['permalink'];
//										break;
//								}
//							}
							if( isset($item['url']) && $item['url'] != '' ) {
								$content .= "<a href='" . $item['url'] . "'>" . $item['description'] . "</a>";
							} else {
								$content .= $item['description'];
							}
							$content .= "</td>";
							$content .= "<td class='alignright'>" . $item['quantity'] . "</td>";
							$content .= "<td class='alignright'>" . $item['shipped_quantity'] . "</td>";
							$discount_text = '';
							if( $item['unit_discount_amount'] > 0 ) {
								$discount_text .= '-' . $item['unit_discount_amount_display']
									. (($item['quantity']>1)?'x'.$item['quantity']:'');
							}
							if( $item['unit_discount_percentage'] > 0 ) {
								$discount_text .= ($discount_text!=''?', ':'') . '-' . $item['unit_discount_percentage'] . '%';
							}
							$content .= "<td class='alignright'>" . $item['unit_amount_display']
									. ($discount_text!=''?('<br/>' . $discount_text . ' (' . $item['discount_amount_display'] .')'):'')
									. "</td>";
							$content .= "<td class='alignright'>" 
									. $item['total_amount_display']
									. "</td>";
							$content .= "</tr>";
							$count++;
						}
						$content .= "</tbody>";
						$content .= "<tfoot>";
						// cart totals
						$num_cols = 4;
						if( $customer_invoice['shipping_amount'] > 0 || (isset($customer_invoice['taxes']) && count($customer_invoice['taxes']) > 0) ) {
							$content .= "<tr class='" . (($count%2)==0?'item-even':'item-odd') . "'>";
							$content .= "<td colspan='$num_cols' class='alignright'>Sub-Total:</td>"
								. "<td class='alignright'>"
								. numfmt_format_currency($intl_currency_fmt, $customer_invoice['subtotal_amount'], $intl_currency)
								. "</td>"
								. "</tr>";
							$count++;
						}
						if( isset($customer_invoice['shipping_amount']) && $customer_invoice['shipping_amount'] > 0 ) {
							$content .= "<tr class='" . (($count%2)==0?'item-even':'item-odd') . "'>";
							$content .= "<td colspan='$num_cols' class='alignright'>Shipping:</td>"
								. "<td class='alignright'>"
								. numfmt_format_currency($intl_currency_fmt, $customer_invoice['shipping_amount'], $intl_currency)
								. "</td>"
								. "</tr>";
							$count++;
						}
						if( isset($customer_invoice['taxes']) ) {
							foreach($customer_invoice['taxes'] as $tax) {
								$tax = $tax['tax'];
								$content .= "<tr class='" . (($count%2)==0?'item-even':'item-odd') . "'>";
								$content .= "<td colspan='$num_cols' class='alignright'>" . $tax['description'] . ":</td>"
									. "<td class='alignright'>"
									. numfmt_format_currency($intl_currency_fmt, $tax['amount'], $intl_currency)
									. "</td>"
									. "</tr>";
								$count++;
							}
						}
						$content .= "<tr class='" . (($count%2)==0?'item-even':'item-odd') . "'>";
						$content .= "<td colspan='$num_cols' class='alignright'><b>Total:</b></td>"
							. "<td class='alignright'>" . $customer_invoice['total_amount_display'] . "</td>"
							. "</tr>";
						$count++;
						$content .= "</foot>";
						$content .= "</table>";
						$content .= "</div>";
						$content .= "<br/>";


						$invoice_displayed = 'yes';
					}
					//
					// Display the shipments
					//
					if( isset($customer_invoice['shipments']) ) {
						foreach($customer_invoice['shipments'] as $shipment) {
//						$content .= "<pre>" . print_r($shipment, true) . "</pre>";
							$shipment = $shipment['shipment'];
							$content .= "<div class='cart'>";
							$content .= "<h2 class='entry-title'>Shipment" . (count($customer_invoice['shipments'])>1?" #" . $shipment['shipment_number']:'') . "</h2>";

							$saddr = '';
							if( isset($shipment['shipping_name']) && $shipment['shipping_name'] != '' ) { $saddr .= ($saddr!=''?'<br/>':'') . $shipment['shipping_name']; }
							if( isset($shipment['shipping_address1']) && $shipment['shipping_address1'] != '' ) { $saddr .= ($saddr!=''?'<br/>':'') . $shipment['shipping_address1']; }
							if( isset($shipment['shipping_address2']) && $shipment['shipping_address2'] != '' ) { $saddr .= ($saddr!=''?'<br/>':'') . $shipment['shipping_address2']; }
							$city = '';
							if( isset($shipment['shipping_city']) && $shipment['shipping_city'] != '' ) { $city .= ($city!=''?'':'') . $shipment['shipping_city']; }
							if( isset($shipment['shipping_province']) && $shipment['shipping_province'] != '' ) { $city .= ($city!=''?', ':'') . $shipment['shipping_province']; }
							if( isset($shipment['shipping_postal']) && $shipment['shipping_postal'] != '' ) { $city .= ($city!=''?'  ':'') . $shipment['shipping_postal']; }
							if( $city != '' ) { $saddr .= ($saddr!=''?'<br/>':'') . $city; } 
							if( isset($shipment['shipping_country']) && $shipment['shipping_country'] != '' ) { $saddr .= ($saddr!=''?'<br/>':'') . $shipment['shipping_country']; }

							$content .= "<div class='cart-details'><table class='cart-details'><tbody>";
							$count = 1;
							$content .= "<tr class='" . (($count%2)==0?'item-even':'item-odd') . "'><th>Status</th><td>" . $shipment['status_text'] . "</td></tr>";
							$count++;
							$content .= "<tr class='" . (($count%2)==0?'item-even':'item-odd') . "'><th>Date Shipped</th><td>" . $shipment['ship_date'] . "</td></tr>";
							$count++;
							$content .= "<tr class='" . (($count%2)==0?'item-even':'item-odd') . "'><th>Address</th><td>" . $saddr . "</td></tr>";
							$count++;
							if( preg_match('/fedex/i', $shipment['shipping_company']) && $shipment['tracking_number'] != '' ) {
								$content .= "<tr class='" . (($count%2)==0?'item-even':'item-odd') . "'><th>Tracking Number</th><td><a target='_blank' href='https://www.fedex.com/apps/fedextrack/?action=track&trackingnumber=" . $shipment['tracking_number'] . "&cntry_code=us'>" . $shipment['tracking_number'] . "</a></td></tr>";
								$count++;
							}
							$content .= "</tbody></table></div>";
							$content .= "<br/>";
							$content .= "<div class='cart-items'>";
							$content .= "<table class='cart-items'>";
							$content .= "<thead><tr>"
								. "<th class='aligncenter'>Item</th>"
								. "<th class='alignright'>Quantity</th>"
								. "</tr></thead>";
							$content .= "<tbody>";
							$count=0;
							foreach($shipment['items'] as $item_id => $item) {
								$item = $item['item'];
								$content .= "<tr class='" . (($count%2)==0?'item-even':'item-odd') . "'>"
									. "<td>";
	//							if( isset($item['object']) && isset($item['permalink']) ) {
	//								switch($item['object']) {
	//									case 'ciniki.products.product': 
	//										$item['url'] = $ciniki['request']['base_url'] . '/products/product/' . $item['permalink'];
	//										break;
	//								}
	//							}
								if( isset($item['url']) && $item['url'] != '' ) {
									$content .= "<a href='" . $item['url'] . "'>" . $item['description'] . "</a>";
								} else {
									$content .= $item['description'];
								}
								$content .= "</td>";
								$content .= "<td class='alignright'>" . $item['quantity'] . "</td>";
								$content .= "</tr>";
								$count++;
							}
							$content .= "</tbody>";
							$content .= "</table>";
							$content .= "</div>";
							$content .= "</div>";
						}
					}


				}
				//
				// If the invoice could not be found, was locked, etc, then revert display to the list of orders
				//
				if( $invoice_displayed == 'no' ) {
					$content .= "<h1 class='entry-title'>Orders</h1>";
					if( isset($invoice_details_err_msg) && $invoice_details_err_msg != '' ) {
						$content .= "<p class='formerror'>$invoice_details_err_msg</p>";
					}
					$content .= "<div class='cart cart-items'>";
					if( isset($settings['page-account-invoices-view-details']) && $settings['page-account-invoices-view-details'] == 'yes' ) {
						$content .= "<form method='POST' action='" . $ciniki['request']['ssl_domain_base_url'] . "/account/orders'>";
						$content .= "<input type='hidden' name='action' value='orderdetails'/>";
					} else {
						$content .= "<form target='_blank' method='POST' action='" . $ciniki['request']['ssl_domain_base_url'] . "/account/orders'>";
						$content .= "<input type='hidden' name='action' value='downloadorder'/>";
					}
					$content .= "<input type='hidden' id='invoice_id' name='invoice_id' value=''/>";
					$content .= "<table class='cart-items'>";
					$content .= "<thead><tr>"
						. "<th>Invoice #</th>"
						. ((isset($settings['page-cart-po-number']) && $settings['page-cart-po-number'] == 'required')?"<th>PO Number</th>":"")
						. "<th>Date</th>"
						. "<th>Status</th>";
					if( (isset($settings['page-account-invoices-view-pdf']) && $settings['page-account-invoices-view-pdf'] == 'yes')
						|| (isset($settings['page-account-invoices-view-details']) && $settings['page-account-invoices-view-details'] == 'yes')
						) {
						$content .= "<th>Action</th>";
					}
					$content .= "</tr></thead>";
					$content .= "<tbody>";
					$count = 0;
					foreach($invoices as $invoice) {
						$content .= "<tr class='" . (($count%2)==0?'item-even':'item-odd') . "'>"
							. "<td>" . $invoice['invoice_number'] . "</td>"
							. ((isset($settings['page-cart-po-number']) && $settings['page-cart-po-number'] == 'required')?"<td>".$invoice['po_number']."</td>":"")
							. "<td>" . $invoice['invoice_date'] . "</td>"
							. "<td class='aligncenter'>" . $invoice['status'] . "</td>"
							. "";
						if( isset($settings['page-account-invoices-view-details']) 
							&& $settings['page-account-invoices-view-details'] == 'yes' ) {
							$content .= "<td class='aligncenter'>"
								. "<input class='cart-submit' onclick='document.getElementById(\"invoice_id\").value=" . $invoice['id'] . ";return true;' type='submit' name='details' value='View'/>"
								. "</td>";
						}
						elseif( isset($settings['page-account-invoices-view-pdf']) 
							&& $settings['page-account-invoices-view-pdf'] == 'yes' ) {
							$content .= "<td class='aligncenter'>"
								. "<input class='cart-submit' onclick='document.getElementById(\"invoice_id\").value=" . $invoice['id'] . ";return true;' type='submit' name='pdf' value='View'/>"
								. "</td>";
						}
						$content .= "</tr>";
						$count++;
					}
					$content .= "</tbody></table>";
					$content .= "</form>";
					$content .= "</div>";
				}
			}
			$content .= "</div>";
		}

		$content .= "</article>\n"
			. "</div>\n";

		$display_form = 'no';
	}

*/
}
?>
