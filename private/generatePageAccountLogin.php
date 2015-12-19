<?php
//
// Description
// -----------
// This function will verify the account login of a customer or present the login form. If the customer submitted
// a forgot password request, this function also handles those requests.
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_generatePageAccountLogin(&$ciniki, $settings, $business_id, $breadcrumbs) {


    //
    // Check if the customer is logged in
    //
    if( isset($ciniki['session']['customer']['id']) && $ciniki['session']['customer']['id'] > 0 ) {
        return array('stat'=>'ok');
    }

    //
    // Check if the login form was submitted
    //
    $display_form = 'login';
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

        //
        // Verify the customer and create a session
        //
        elseif( isset($_POST['email']) && $_POST['email'] != '' 
            && isset($_POST['password']) && $_POST['password'] != '' 
            ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'customers', 'web', 'auth');
            $rc = ciniki_customers_web_auth($ciniki, $business_id, $_POST['email'], $_POST['password']);
            if( $rc['stat'] != 'ok' ) {
                $err_msg = "Unable to authenticate, please try again or click Forgot your password to get a new one";
                $display_form = 'login';
            } else {
                $display_form = 'no';

                //
                // Check for any module information that should be loaded into the session
                //
                foreach($ciniki['business']['modules'] as $module => $m) {
                    list($pkg, $mod) = explode('.', $module);
                    $rc = ciniki_core_loadMethod($ciniki, $pkg, $mod, 'web', 'accountSessionLoad');
                    if( $rc['stat'] == 'ok' ) {
                        $fn = $rc['function_call'];
                        $rc = $fn($ciniki, $settings, $business_id);
                        if( $rc['stat'] != 'ok' ) {
                            return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'2900', 'msg'=>'Unable to load account information', 'err'=>$rc['err']));
                        }
                    }
                }

                //
                // If multiple accounts, setup the redirect upon choosing an account
                //
                if( isset($ciniki['session']['customers']) && count($ciniki['session']['customers']) > 1 ) {
                    if( ($settings['page-account-signin-redirect']) 
                        ) {
                        $_SESSION['account_chooser_redirect'] = $settings['page-account-signin-redirect'];
                    } else {
                        $_SESSION['account_chooser_redirect'] = '';
                    }
                }

                //
                // Check for a redirect
                //
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

    //
    // Check for a forgot password form submit
    //
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
    // Check if a reset password was submitted, from a forgot password link
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

        //
        // Javascript to switch login/forgot password forms
        //
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

        //
        // Signin form
        //
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
                . "<a class='color' href='javscript:void(0);' onclick='swapLoginForm(\"forgotpassword\");return false;'>Forgot your password?</a></p></div>\n";
        }
        $content .= "</div>\n";
        
        //
        // Forgot password form
        //
        $content .= "<div id='forgotpassword-form' style='display:";
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
            . "<div id='forgot-link'><p><a class='color' href='javascript:void();' onclick='swapLoginForm(\"signin\"); return false;'>Sign In</a></p></div>\n"
            . "</div>\n";

        $content .= "</div>"
            . "</article>"
            . "</div>\n";
    }

	//
	// Check if this page was directed to from the recovery password email link
	// The second argument should be the customer uuid
	// The third argument should be the temp_password
	//
	elseif( (isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] == 'reset' 
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
    // Add the header
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageHeader');
    $rc = ciniki_web_generatePageHeader($ciniki, $settings, 'Account', array());
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
            $page_content .= "<h1 class='page-header-title'>" . $article_title . "</h1>";
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
    
    return array('stat'=>'ok', 'content'=>$page_content);
}
?>
