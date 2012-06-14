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
	// Add the header
	//
	require_once($ciniki['config']['core']['modules_dir'] . '/web/private/generatePageHeader.php');
	$rc = ciniki_web_generatePageHeader($ciniki, $settings, 'Downloads');
	if( $rc['stat'] != 'ok' ) {	
		return $rc;
	}
	$content .= $rc['content'];

	//
	// Check if a form was submitted
	//
	$err_msg = '';
	if( isset($_POST['action']) ) {
		if( $_POST['action'] == 'signin' ) {
			// Verify the customer and create a session

		}
		elseif( $_POST['action'] == 'forgot' ) {
			// Set the forgot password notification
		}
	}

	//
	// Check if user submitted a new password
	//
	if( $ciniki['request']['uri_split'][0] == 'newpassword' ) {
		// Require old password, and new password
		$content .= 'set new password';
	}


	//
	// Check if this page was directed to from the recovery password email link
	// The second argument should be the customer uuid
	// The third argument should be the temp_password
	//
	if( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] == 'forgotpassword' 
		&& isset($ciniki['request']['uri_split'][1])  ) {
		$content .= 'Forgot password';
		$rc = ciniki_web_recoveryPassword($ciniki);
	}

	//
	// Check if the customer is logged in or not
	//
	elseif( isset($ciniki['session']['customer']['id']) && $ciniki['session']['customer']['id'] > 0 ) {
		
		//
		// Change password form
		//
		if( $ciniki['request']['uri_split'][0] == 'changepassword' ) {
			$content .= "change password";
		} 

		//
		//
		
		//
		// Account mainpage
		//
		else {
			$content .= "Account information";
		}
	}

	//
	// Display login form
	//
	else {
		$post_email = '';
		if( isset($_POST['email']) ) {
			$post_email = $_POST['email'];
		}
		$content .= "<div id='content'>\n"
			. "<article class='page'>\n"
			. "<header class='entry-title'><h1 class='entry-title'>Account</h1></header>\n";
		$content .= "<div class='entry-content'>"
			. "<div id='signin-form' style='display:block;'>\n"
			. "<form method='POST' action=''>"
			. "<input type='hidden' name='action' value='signin'>\n"
			. "<label for='email'>Email</label><input id='email' type='email' maxlength='250' name='email' value='$post_email' />\n" 
			. "<label for='password'>Password</label><input id='password' type='password' maxlength='100' name='password' value='' />\n"
			. "<input type='submit' value='Sign In' class='button'>\n"
			. "</form>"
			. "</div>\n"
			. "<div id='forgotpassword-form' style='display:none;'>\n"
			. "<p>Please enter your email address and you will receive a link to create a new password.</p>"
			. "<form method='POST' action=''>"
			. "<input type='hidden' name='action' value='forgot'>\n"
			. "<label for='email'>Email </label><input id='email' type='email' maxlength='250' name='email' value='$post_email' />\n" 
			. "<input type='submit' value='Get New Password' class='button'>\n"
			. "</form>"
			. "</div>\n"
			. "</div>";

		// Include forgot password form, and use javascript to swap forms.
	}






	//
	// Add the footer
	//
	require_once($ciniki['config']['core']['modules_dir'] . '/web/private/generatePageFooter.php');
	$rc = ciniki_web_generatePageFooter($ciniki, $settings);
	if( $rc['stat'] != 'ok' ) {	
		return $rc;
	}
	$content .= $rc['content'];

	return array('stat'=>'ok', 'content'=>$content);
}
?>
