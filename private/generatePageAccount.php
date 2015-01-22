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
	// Store the content created by the page
	// Make sure everything gets generated ok before returning the content
	//
	$content = '';
	$subscription_err_msg = '';
	$chgpwd_err_msg = '';
	$submenu = array();

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
	// Pull in subscription list
	//
	if( isset($modules['ciniki.subscriptions']) ) {
		$subscriptions = array();
		ciniki_core_loadMethod($ciniki, 'ciniki', 'subscriptions', 'web', 'list');
		$rc = ciniki_subscriptions_web_list($ciniki, $settings, $ciniki['request']['business_id']);
		if( $rc['stat'] == 'ok' && isset($rc['subscriptions']) ) {
			$subscriptions = $rc['subscriptions'];
		}
	}

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
	if( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] == 'switch'
		&& isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] != '' 
		&& isset($ciniki['session']['customers'])
		&& isset($ciniki['session']['customers'][$ciniki['request']['uri_split'][1]])
		) {
		$_SESSION['customer'] = $ciniki['session']['customers'][$ciniki['request']['uri_split'][1]];
		$_SESSION['customer']['email'] = $ciniki['session']['login']['email'];
		$ciniki['session']['customer'] = $_SESSION['customer'];
		unset($ciniki['session']['cart']);
		unset($_SESSION['cart']);
		ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'web', 'cartLoad');
		$rc = ciniki_sapos_web_cartLoad($ciniki, $settings, $ciniki['request']['business_id']);
		if( $rc['stat'] == 'ok' ) {
			$_SESSION['cart'] = $rc['cart'];
			$_SESSION['cart']['sapos_id'] = $rc['cart']['id'];
			$_SESSION['cart']['num_items'] = count($rc['cart']['items']);
		}
		if( $_SESSION['account_chooser_redirect'] != '' ) {
			$redirect = $_SESSION['account_chooser_redirect'];
			$_SESSION['account_chooser_redirect'] = '';
			if( $redirect == 'back' 
				&& isset($_SESSION['login_referer']) && $_SESSION['login_referer'] != '' ) {
				header('Location: ' . $_SESSION['login_referer']);
				$_SESSION['login_referer'] = '';
				exit;
			}
			if( $redirect != '' ) {
				header('Location: ' . $ciniki['request']['ssl_domain_base_url'] . $redirect);
				exit;
			}
		} 
		header('Location: ' . ($ciniki['request']['ssl_domain_base_url']!=''?$ciniki['request']['ssl_domain_base_url']:'') . '/account');
		exit;
	}
	elseif( (isset($_POST['action']) && $_POST['action'] == 'logout') 
		|| (isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] == 'logout') ) {
		$ciniki['session']['customer'] = array();
		$ciniki['session']['cart'] = array();
		$ciniki['session']['user'] = array();
		$ciniki['session']['change_log_id'] = '';
		unset($_SESSION['customer']);
		unset($_SESSION['cart']);
		header('Location: ' . ($ciniki['request']['ssl_domain_base_url']!=''?$ciniki['request']['ssl_domain_base_url']:'/'));
	}
	elseif( isset($_POST['action']) ) {
		if( $_POST['action'] == 'signin' ) {
			//
			// Check the referrer and that cookies are enabled
			//
			if( !isset($_SESSION['loginform']) ) {
				$err_msg = "It appears that you do not have cookies enabled in your browser.  They are "
					. "required for you to login.  Please check your browser settings and try again.  <br/><br/>Here is a link to help: "
					. "<a target='_blank' href='http://support.google.com/accounts/bin/answer.py?hl=en&answer=61416'>How to enable cookies</a>."
					. "";
				$display_form = 'login';
			}

			// Verify the customer and create a session
			elseif( isset($_POST['email']) && $_POST['email'] != '' 
				&& isset($_POST['password']) && $_POST['password'] != '' 
				) {
				ciniki_core_loadMethod($ciniki, 'ciniki', 'customers', 'web', 'auth');
				$rc = ciniki_customers_web_auth($ciniki, $ciniki['request']['business_id'], $_POST['email'], $_POST['password']);
				if( $rc['stat'] != 'ok' ) {
					$err_msg = "Unable to authenticate, please try again or click Forgot your password to get a new one";
					$display_form = 'login';
				} else {
					$display_form = 'no';
					ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'web', 'cartLoad');
					$rc = ciniki_sapos_web_cartLoad($ciniki, $settings, $ciniki['request']['business_id']);
					if( $rc['stat'] == 'ok' ) {
					//	print "Loaded cart";
						$_SESSION['cart'] = $rc['cart'];
						$_SESSION['cart']['sapos_id'] = $rc['cart']['id'];
						$_SESSION['cart']['num_items'] = count($rc['cart']['items']);
					}
					//
					// If multiple accounts, then display account chooser
					//
					if( isset($ciniki['session']['customers']) && count($ciniki['session']['customers']) > 1 ) {
						if( ($settings['page-account-signin-redirect']) 
							) {
							$_SESSION['account_chooser_redirect'] = $settings['page-account-signin-redirect'];
						} else {
							$_SESSION['account_chooser_redirect'] = '';
						}
					}
					//exit;
					elseif( isset($settings['page-account-signin-redirect']) ) {
						if( $settings['page-account-signin-redirect'] == 'back' 
							&& isset($_SESSION['login_referer']) && $_SESSION['login_referer'] != '' ) {
							header('Location: ' . $_SESSION['login_referer']);
							$_SESSION['login_referer'] = '';
							exit;
						}
						if( $settings['page-account-signin-redirect'] != '' ) {
							header('Location: ' . $ciniki['request']['ssl_domain_base_url'] . $settings['page-account-signin-redirect']);
							exit;
						}
					}
				}
			}
		}
		elseif( $_POST['action'] == 'forgot' ) {
			// Set the forgot password notification
			if( isset($_POST['email']) && $_POST['email'] != '' ) {
				$url = $ciniki['request']['ssl_domain_base_url'] . '/account/reset';
				ciniki_core_loadMethod($ciniki, 'ciniki', 'customers', 'web', 'passwordRequestReset');
				$rc = ciniki_customers_web_passwordRequestReset($ciniki, $ciniki['request']['business_id'], $_POST['email'], $url);
				if( $rc['stat'] != 'ok' ) {
					$err_msg = 'You must enter a valid email address to get a new password.';
					$display_form = 'forgot';
				} else {
					$display_form = 'no';

					$content .= "<div id='content'>\n"
						. "<article class='page'>\n"
						. "<header class='entry-title'><h1 class='entry-title'>Account</h1></header>\n";
					$content .= "<div class='entry-content'>"
						. "<p>A link has been sent to your email to get a new password.</p>\n"
						. "</div>";
					$content .= "</article>\n"
						. "</div>\n";
				}
			} else {
				$err_msg = 'You must enter a valid email address to get a new password.';
				$display_form = 'forgot';
			}
		}
		//
		// Set a new password after using forgot password form
		//
		elseif( $_POST['action'] == 'reset' ) {
			if( !isset($_POST['newpassword']) || strlen($_POST['newpassword']) < 8 ) {
				$err_msg = 'Your new password must be at least 8 characters long.';
				$display_form = 'reset';
			} elseif( !isset($_POST['email']) || $_POST['email'] == '' ) {
				$err_msg = 'Invalid email address.';
				$display_form = 'reset';
			} elseif( !isset($_POST['temppassword']) || $_POST['temppassword'] == '' ) {
				$err_msg = 'Invalid link.';
				$display_form = 'reset';
			} else {
				ciniki_core_loadMethod($ciniki, 'ciniki', 'customers', 'web', 'changeTempPassword');
				$rc = ciniki_customers_web_changeTempPassword($ciniki, $ciniki['request']['business_id'], 
					$_POST['email'], $_POST['temppassword'], $_POST['newpassword']);
				if( $rc['stat'] != 'ok' ) {
					$err_msg = "Unable to set your new password, please try again.";
					$display_form = 'reset';
				} else {
					$err_msg = "Your password has been set, you may now sign in.";
					$display_form = 'login';
				}
			}
		}
		//
		// Update subscriptions, or password
		//
		elseif( $_POST['action'] == 'accountupdate' 
			&& isset($ciniki['session']['customer']['id']) && $ciniki['session']['customer']['id'] > 0 ) {

			if( isset($modules['ciniki.subscriptions']) && isset($subscriptions) && count($subscriptions) > 0 ) {
				//
				// Pull in subscription list for user
				//
				ciniki_core_loadMethod($ciniki, 'ciniki', 'subscriptions', 'web', 'list');
				ciniki_core_loadMethod($ciniki, 'ciniki', 'subscriptions', 'web', 'subscribe');
				ciniki_core_loadMethod($ciniki, 'ciniki', 'subscriptions', 'web', 'unsubscribe');
				foreach($subscriptions as $snum => $subscription) {
					$sid = $subscription['subscription']['id'];
					// Check if the subscribed to the subscription
					if( isset($_POST["subscription-$sid"]) && $_POST["subscription-$sid"] == $sid ) {
						if( $subscription['subscription']['subscribed'] == 'no' ) {
							ciniki_subscriptions_web_subscribe($ciniki, $settings, $ciniki['request']['business_id'], 
								$sid, $ciniki['session']['customer']['id']);
							$subscription_err_msg = 'Your subscriptions have been updated.';
						}
					} else {
						if( $subscription['subscription']['subscribed'] == 'yes' ) {
							ciniki_subscriptions_web_unsubscribe($ciniki, $settings, $ciniki['request']['business_id'], 
								$sid, $ciniki['session']['customer']['id']);
							$subscription_err_msg = 'Your subscriptions have been updated.';
						}
					}
				}
			}

			//
			// Check if customer wants to change their password
			//
			if( isset($_POST['oldpassword']) && $_POST['oldpassword'] != '' 
				&& isset($_POST['newpassword']) && $_POST['newpassword'] != '' 
				&& (!isset($settings['page-account-password-change']) 
					|| $settings['page-account-password-change'] == 'yes')
				) {
				ciniki_core_loadMethod($ciniki, 'ciniki', 'customers', 'web', 'changePassword');
				$rc = ciniki_customers_web_changePassword($ciniki, $ciniki['request']['business_id'], 
					$_POST['oldpassword'], $_POST['newpassword']);
				if( $rc['stat'] != 'ok' ) {
					$chgpwd_err_msg = "Unable to set your new password, please try again.";
				} else {
					$chgpwd_err_msg = "Your password has been updated.";
				}
			}
		}
	
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
	// Check if user submitted a new password
	//
//	if( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] == 'newpassword' ) {
//		// Require old password, and new password
//		$content .= 'set new password';
//	}

	//
	// Check if this page was opened from an unsubscribe link
	//
	if( isset($ciniki['request']['uri_split'][0]) 
		&& $ciniki['request']['uri_split'][0] == 'unsubscribe' 
		&& isset($_GET['e']) && $_GET['e'] != '' 
		&& isset($_GET['s']) && $_GET['s'] != ''
		&& isset($_GET['k']) && $_GET['k'] != ''
		) {

		//
		// Get the information about the customer, from the link provided in the email.  The
		// email must be less than 30 days since it was sent for the link to still be active
		//
		$strsql = "SELECT ciniki_subscription_customers.subscription_id AS id, ciniki_subscription_customers.customer_id, "
			. "ciniki_subscriptions.name "
			. "FROM ciniki_mail, ciniki_subscription_customers, ciniki_subscriptions, ciniki_customer_emails "
			. "WHERE ciniki_mail.unsubscribe_key = '" . ciniki_core_dbQuote($ciniki, $_GET['k']) . "' "
			. "AND ciniki_mail.business_id = '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['business_id']) . "' "
			. "AND ciniki_mail.customer_id = ciniki_subscription_customers.customer_id "
			. "AND (UNIX_TIMESTAMP(UTC_TIMESTAMP())-UNIX_TIMESTAMP(ciniki_mail.date_sent)) < 2592000 " // Mail was sent within 30 days
			. "AND ciniki_subscription_customers.business_id = '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['business_id']) . "' "
			. "AND ciniki_subscription_customers.subscription_id = ciniki_subscriptions.id "
			. "AND ciniki_subscriptions.business_id = '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['business_id']) . "' "
			. "AND ciniki_subscriptions.uuid = '" . ciniki_core_dbQuote($ciniki, $_GET['s']) . "' "
			. "AND ciniki_customer_emails.business_id = '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['business_id']) . "' "
			. "AND ciniki_subscription_customers.customer_id = ciniki_customer_emails.customer_id "
			. "AND ciniki_customer_emails.email = '" . ciniki_core_dbQuote($ciniki, $_GET['e']) . "' "
			. "";
		$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.mail', 'subscription');
		if( isset($rc['subscription']) && isset($rc['subscription']['id']) ) {
			if( !isset($ciniki['session']['change_log_id']) ) {
				$ciniki['session']['change_log_id'] = 'mail.' . date('ymd.His');
				$ciniki['session']['user'] = array('id'=>'-3');
			}
			$subscription_name = $rc['subscription']['name'];
			ciniki_core_loadMethod($ciniki, 'ciniki', 'subscriptions', 'web', 'unsubscribe');
			ciniki_subscriptions_web_unsubscribe($ciniki, $settings, $ciniki['request']['business_id'], 
				$rc['subscription']['id'], $rc['subscription']['customer_id']);
		}

		$content .= "<div id='content'>\n"
			. "<article class='page'>\n"
			. "<header class='entry-title'><h1 class='entry-title'>Unsubscribe</h1></header>\n"
			. "<div class='entry-content'>\n";

		$content .= "You have been unsubscribed from the Mailing List.";
		
		$content .= "</div>\n"
			. "</article>\n"
			. "</div>\n";
		$display_form = 'yes';
	}

	//
	// Check if this page was directed to from the recovery password email link
	// The second argument should be the customer uuid
	// The third argument should be the temp_password
	//
	if( (isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] == 'reset' 
		&& isset($_GET['email']) && $_GET['email'] != ''
		&& isset($_GET['pwd']) && $_GET['pwd'] != '' )
		|| $display_form == 'reset'
		) {

		$content .= "<div id='content'>\n"
			. "<article class='page account'>\n"
			. "<header class='entry-title'><h1 class='entry-title'>Account</h1></header>\n";
		$content .= "<div class='entry-content'>";
		$content .= "<p>Please enter a new password.  It must be at least 8 characters long.</p>";
		$content .= "<div id='reset-form'>\n"
			. "<form method='POST' action='" . $ciniki['request']['ssl_domain_base_url'] . "/account'>";
		if( $err_msg != '' ) {
			$content .= "<p class='formerror'>$err_msg</p>\n";
		}
		$content .="<input type='hidden' name='action' value='reset'>\n";
		if( isset($_GET['email']) ) {
			$content .= "<input type='hidden' name='email' value='" . $_GET['email'] . "'>\n";
		} else {
			$content .= "<input type='hidden' name='email' value='" . $_POST['email'] . "'>\n";
		}
		if( isset($_GET['email']) ) {
			$content .= "<input type='hidden' name='temppassword' value='" . $_GET['pwd'] . "'>\n";
		} else {
			$content .= "<input type='hidden' name='temppassword' value='" . $_POST['temppassword'] . "'>\n";
		}
		$content .= "<div class='input'><label for='password'>New Password</label><input id='password' type='password' class='text' maxlength='100' name='newpassword' value='' /></div>\n"
			. "<div class='submit'><input type='submit' class='submit' value='Set Password' /></div>\n"
			. "</form>"
			. "</div>\n"
			. "</div>";
		$content .= "</article>\n"
			. "</div>\n";
		$display_form = 'no';
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
		
		if( $subpage == 'accounts' 
			&& isset($ciniki['session']['customers']) 
			&& count($ciniki['session']['customers']) > 0
			) {
			$content .= "<aside>";
//			$content .= "<p><b class='entry-title'>Current Account</b></p>";
			$content .= "<h1>Account</h1>";
			$content .= "<p>Name: " . $ciniki['session']['customer']['display_name'] . "</p>";
			if( isset($customer['addresses']) ) {
				foreach($customer['addresses'] as $addr) {
					$addr = $addr['address'];
					if( ($addr['flags']&0x02) ) {
						$content .= "<p><b>Billing Address</b><br/>"
							. preg_replace('/\n/', '<br/>', $addr['joined'])
							. "</p>";
					}
					if( ($addr['flags']&0x01) ) {
						$content .= "<p><b>Shipping Address</b><br/>"
							. preg_replace('/\n/', '<br/>', $addr['joined'])
							. "</p>";
					}
				}
			}
			$content .= "</aside>";
			$content .= "<header class='entry-title'><h1 class='entry-title'>Other Accounts</h1></header>";
			$content .= "<div class='entry-content'>\n";
//			$content .= "<p>Select an account to manage</p>";
			
			$content .= "<div class='largebutton-list'>";
//			$content .= "<form action='' method='POST'>";
//			$content .= "<input type='hidden' name='action' value='switch'/>";
			foreach($ciniki['session']['customers'] as $cust) {
				$content .= "<div class='button-list-wrap'><div class='button-list-button'>";
				$content .= "<a href='" . $ciniki['request']['base_url'] . '/account/switch/' . $cust['id'] . "'>" . $cust['display_name'] . "</a>";
				$content .= "</div></div><br/>";
			}
			$content .= "</div>";
//			$content .= "</form>";
			$content .= "</div>";
		}

		elseif( $subpage == 'orders' ) {
			$content .= "<div class='entry-content'>\n";
			$content .= "<h1 class='entry-title'>Orders</h1>";
			ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'web', 'customerOrders');
			$rc = ciniki_sapos_web_customerOrders($ciniki, $settings, $ciniki['request']['business_id'], $ciniki['session']['customer']['id'], array());
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			if( isset($rc['invoices']) ) {
				$content .= "<div class='cart cart-items'>";
				$content .= "<form target='_blank' method='POST' action='" . $ciniki['request']['ssl_domain_base_url'] . "/account/orders'>";
				$content .= "<input type='hidden' name='action' value='downloadorder'/>";
				$content .= "<input type='hidden' id='invoice_id' name='invoice_id' value=''/>";
				$content .= "<table class='cart-items'>";
				$content .= "<thead><tr>"
					. "<th>Invoice #</th>"
					. "<th>Date</th>"
					. "<th>Status</th>";
				if( isset($settings['page-account-invoices-view-pdf']) 
					&& $settings['page-account-invoices-view-pdf'] == 'yes' ) {
					$content .= "<th>Action</th>";
				}
				$content .= "</tr></thead>";
				$content .= "<tbody>";
				$count = 0;
				foreach($rc['invoices'] as $invoice) {
					$content .= "<tr class='" . (($count%2)==0?'item-even':'item-odd') . "'>"
						. "<td>" . $invoice['invoice_number'] . "</td>"
						. "<td>" . $invoice['invoice_date'] . "</td>"
						. "<td class='aligncenter'>" . $invoice['status'] . "</td>";
					if( isset($settings['page-account-invoices-view-pdf']) 
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
			$content .= "</div>";
		}

		if( isset($modules['ciniki.subscriptions']) && $subpage == 'subscriptions' && isset($subscriptions) && count($subscriptions) > 0 ) {
			//
			// reload subscriptions to get the customer subscriptions
			//
			if( isset($modules['ciniki.subscriptions']) && count($subscriptions) > 0 ) {
				ciniki_core_loadMethod($ciniki, 'ciniki', 'subscriptions', 'web', 'list');
				$rc = ciniki_subscriptions_web_list($ciniki, $settings, $ciniki['request']['business_id']);
				if( $rc['stat'] == 'ok' && isset($rc['subscriptions']) ) {
					$subscriptions = $rc['subscriptions'];
				}
			}
			$content .= "<div class='entry-content'>\n";
			$content .= "<form action='' method='POST'>";
			$content .= "<input type='hidden' name='action' value='accountupdate'/>";
			$content .= "<h1 class='entry-title'>Subscriptions</h1>";
			// Check for any content the business provided
			if( isset($content_details['page-account-content-subscriptions']) ) {
				ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
				$rc = ciniki_web_processContent($ciniki, $content_details['page-account-content-subscriptions']);	
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				$content .= $rc['content'];
			}

			if( $subscription_err_msg != '' ) {
				$content .= "<p class='formerror'>$subscription_err_msg</p>";
			}

			foreach($subscriptions as $snum => $subscription) {
				$sid = $subscription['subscription']['id'];
				$content .= "<input id='subscription-$sid' type='checkbox' class='checkbox' name='subscription-$sid' value='$sid' ";
				if( $subscription['subscription']['subscribed'] == 'yes' ) {
					$content .= " checked";
				}
				$content .= "/>";
				$content .= " <label class='checkbox' for='subscription-$sid'>" . $subscription['subscription']['name'] . "</label><br/>";
			}
			$content .= "<div class='submit'><input type='submit' class='submit' value='Save Changes'></div>\n";
			$content .= "<br/><br/>";
			$content .= "</div>\n";
		}

		//
		// Allow user to change password
		//
		if( $subpage == 'changepassword' ) {
			if( isset($content_details['page-account-content']) ) {
				ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
				$rc = ciniki_web_processContent($ciniki, $content_details['page-account-content']);	
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				$content .= "<aside>" . $rc['content'];
				$content .= "</aside>";
			}

			$content .= "<div class='entry-content'>\n";
			$content .= "<form action='' method='POST'>";
			$content .= "<input type='hidden' name='action' value='accountupdate'/>";
			$content .= "<h1 class='entry-title'>Change Password</h1>"
				. "<p>If you would like to change your password, enter your old password followed by a new one.</p>"
				. "";

			if( $chgpwd_err_msg != '' ) {
				$content .= "<p class='formerror'>$chgpwd_err_msg</p>";
			}

			$content .= "<label for='oldpassword'>Old Password:</label><input class='text' id='oldpassword' type='password' name='oldpassword' />";
			$content .= "<label for='newpassword'>New Password:</label><input class='text' id='newpassword' type='password' name='newpassword' />";

			$content .= "<div class='submit'><input type='submit' class='submit' value='Save Changes'></div>\n";
			$content .= "</form>";

//			$content .= "<form action='' method='POST'>\n"
//				. "<input type='hidden' name='action' value='logout'>\n"
//				. "<div class='bigsubmit'><input type='submit' class='bigsubmit' value='Logout'></div>\n"
//				. "</form>"
//				. "";
			$content .= "</div>\n";
		}

		$content .= "</article>\n"
			. "</div>\n";

		$display_form = 'no';
	}

	//
	// Display login form
	//
	if( $display_form == 'login' || $display_form == 'forgot' ) {
		//
		// Set a session variable, to test for cookies being turned on
		//
		if( isset($settings['page-account-signin-redirect']) 
			&& $settings['page-account-signin-redirect'] == 'back' 
			) {
			if( (!isset($_SESSION['login_referer']) || $_SESSION['login_referer'] == '') 
				&& isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] != '' 
				) {
					$_SESSION['login_referer'] = $_SERVER['HTTP_REFERER'];
			}
		}
		$_SESSION['loginform'] = 'yes';
		$post_email = '';
		if( isset($_POST['email']) ) {
			$post_email = $_POST['email'];
		}
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
		$content .= "<div id='content'>\n"
			. "<article class='page'>\n"
			. "<header class='entry-title'><h1 class='entry-title'>Account</h1></header>\n";
		$content .= "<div class='entry-content'>";
		$content .= "<div id='signin-form' style='display:";
		if( $display_form == 'login' ) { $content .= "block;"; } else { $content .= "none;"; }
		$content .= "'>\n"
			. "<form method='POST' action=''>";
		if( $err_msg != '' ) {
			$content .= "<p class='formerror'>$err_msg</p>\n";
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
				. "<a class='color' href='javscript:void(0)' onclick='swapLoginForm(\"forgotpassword\");return false;'>Forgot your password?</a></p></div>\n";
		}
		$content .= "</div>\n"
			. "<div id='forgotpassword-form' style='display:";
		if( $display_form == 'forgot' ) { $content .= "block;"; } else { $content .= "none;"; }
		$content .= "'>\n"
			. "<p>Please enter your email address and you will receive a link to create a new password.</p>"
			. "<form method='POST' action=''>";
		if( $err_msg != '' ) {
			$content .= "<p class='formerror'>$err_msg</p>\n";
		}
		$content .= "<input type='hidden' name='action' value='forgot'>\n"
			. "<div class='input'><label for='forgotemail'>Email </label><input id='forgotemail' type='email' class='text' maxlength='250' name='email' value='$post_email' /></div>\n" 
			. "<div class='submit'><input type='submit' class='submit' value='Get New Password' /></div>\n"
			. "</form>"
			. "<br/>"
			. "<div id='forgot-link'><p><a class='color' href='javascript: swapLoginForm(\"signin\"); return false;'>Sign In</a></p></div>\n"
			. "</div>\n"
			. "</div>";
		$content .= "</article>\n"
			. "</div>\n";
		// Include forgot password form, and use javascript to swap forms.
	}

	//
	// Add the header
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageHeader');
	$rc = ciniki_web_generatePageHeader($ciniki, $settings, 'Account', $submenu);
	if( $rc['stat'] != 'ok' ) {	
		return $rc;
	}
	$page_content = $rc['content'];
	
	if( $content != '' ) {
		$page_content .= $content;
	}

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
