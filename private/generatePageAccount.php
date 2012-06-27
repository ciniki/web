<?php
//
// Description
// -----------
// This function will generate the downloads page for the website
//
// Returns
// -------
//
function ciniki_web_generatePageAccount($ciniki, $settings) {

	//
	// Store the content created by the page
	// Make sure everything gets generated ok before returning the content
	//
	$content = '';

	//
	// Check if a form was submitted
	//
	$err_msg = '';
	$display_form = 'login';
	if( isset($_POST['action']) ) {
		if( $_POST['action'] == 'logout' ) {
			$ciniki['session']['customer'] = array();
			unset($_SESSION['customer']);
		}
		elseif( $_POST['action'] == 'signin' ) {
			// Verify the customer and create a session
			if( isset($_POST['email']) && $_POST['email'] != '' 
				&& isset($_POST['password']) && $_POST['password'] != '' 
				) {
				ciniki_core_loadMethod($ciniki, 'ciniki', 'customers', 'web', 'auth');
				$rc = ciniki_customers_auth($ciniki, $ciniki['request']['business_id'], $_POST['email'], $_POST['password']);
				if( $rc['stat'] != 'ok' ) {
					$err_msg = "Unable to authenticate, please try again or click Forgot your password to get a new one";
					$display_form = 'login';
				} else {
					$display_form = 'no';
				}
			}
		}
		elseif( $_POST['action'] == 'forgot' ) {
			// Set the forgot password notification
			if( isset($_POST['email']) && $_POST['email'] != '' ) {
				$url = 'http://' . $_SERVER['HTTP_HOST'] . $ciniki['request']['base_url'] . '/account/reset';
				ciniki_core_loadMethod($ciniki, 'ciniki', 'customers', 'web', 'passwordRequestReset');
				$rc = ciniki_customers_passwordRequestReset($ciniki, $ciniki['request']['business_id'], $_POST['email'], $url);
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
				$rc = ciniki_customers_changeTempPassword($ciniki, $ciniki['request']['business_id'], 
					$_POST['email'], $_POST['temppassword'], $_POST['newpassword']);
				if( $rc['stat'] != 'ok' ) {
					error_log(print_r($rc, true));
					$err_msg = "Unable to set your new password, please try again.";
					$display_form = 'reset';
				} else {
					$err_msg = "Your password has been set, you may now sign in.";
					$display_form = 'login';
				}
			}
		}
	}

	//
	// Check if user submitted a new password
	//
	if( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] == 'newpassword' ) {
		// Require old password, and new password
		$content .= 'set new password';
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
			. "<article class='page'>\n"
			. "<header class='entry-title'><h1 class='entry-title'>Account</h1></header>\n";
		$content .= "<div class='entry-content'>";
		$content .= "<p>Please enter a new password.  It must be at least 8 characters long.</p>";
		$content .= "<div id='reset-form'>\n"
			. "<form method='POST' action='http://" . $_SERVER['HTTP_HOST'] . $ciniki['request']['base_url'] . "/account'>";
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
	
		$content .= "<div id='content'>\n"
			. "<article class='page'>\n"
			. "<header class='entry-title'><h1 class='entry-title'>Account</h1></header>\n";
		
		//
		// Change password form
		//
		if( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] == 'changepassword' ) {
			$content .= "change password";
		} 

		//
		// FIXME: manage subscriptions
		// FIXME: view wine orders
		// FIXME: change email address
		//


		//
		// Account mainpage
		//
		else {
			$content .= "Account information";
		}

		$content .= "<form action='' method='POST'>\n"
			. "<input type='hidden' name='action' value='logout'>\n"
			. "<div class='submit'><input type='submit' class='submit' value='Logout'></div>\n"
			. "</form>"
			. "";

		$content .= "</article>\n"
			. "</div>\n";

		$display_form = 'no';
	}

	//
	// Display login form
	//
	if( $display_form == 'login' || $display_form == 'forgot' ) {
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
			. "<br/>"
			. "<div id='forgot-link'><p><a class='color' href='javascript: swapLoginForm(\"forgotpassword\");'>Forgot your password?</a></p></div>\n"
			. "</div>\n"
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
	$rc = ciniki_web_generatePageHeader($ciniki, $settings, 'Account');
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
