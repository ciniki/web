<?php
//
// Description
// -----------
// This block displays a login form, registration form(if enabled) and processes
// a signup.
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_processBlockLogin(&$ciniki, $settings, $tnid, $block) {

    $content = '';
    $js = '';
    $signinerrors = '';
    $signinmsg = '';

    $post_email = isset($_POST['email_address']) ? $_POST['email_address'] : '';

    if( isset($block['title']) && $block['title'] != '' ) {
        $content .= "<h2>" . $block['title'] . "</h2>";
    }

    $required_account_fields = array(
        'first'=>'First Name', 
        'last'=>'Last Name', 
        'email_address'=>'Email Address', 
        'home_phone'=>'Home Phone', 
        'password'=>'Password',
        'address1'=>'Address', 
        'city'=>'City', 
        'province'=>'State/Province', 
        'postal'=>'ZIP/Postal Code', 
        'country'=>'Country',
        );

    //
    // Check if a signup occured
    //
    if( isset($_POST['action']) && $_POST['action'] == 'createaccount' ) {
        $signinerrors = '';

        //
        // Check for required fields
        //
        $args = $_POST;
        if( isset($args['province_code_' . $args['country']]) && $args['province_code_' . $args['country']] != '' ) {
            $args['province'] = $args['province_code_' . $args['country']];
        }
        $missing_fields = array();
        foreach($required_account_fields as $fid => $fname) {
            if( !isset($args[$fid]) || trim($args[$fid]) == '' ) {
                $missing_fields[] = $fname;
            }
        }
        if( count($missing_fields) > 1 ) {
            $signinerrors = "You must enter " . implode(', ', $missing_fields) . " to create your account.";
        } elseif( count($missing_fields) > 0 ) {
            $signinerrors = "You must enter " . implode(', ', $missing_fields) . " to create your account.";
        }
        if( $signinerrors == '' ) {
            //
            // Check if email address already exists
            //
            ciniki_core_loadMethod($ciniki, 'ciniki', 'customers', 'hooks', 'customerLookup');
            $rc = ciniki_customers_hooks_customerLookup($ciniki, $ciniki['request']['tnid'], array('email'=>$_POST['email_address']));
            if( $rc['stat'] != 'noexist' ) {
                $signinerrors = "There is already an account for that email address, please use the Forgot Password link to recover your password.";
            }
        }
        if( $signinerrors == '' ) {
            //
            // Setup the customer defaults
            //
            $args['phone_label_1'] = 'Home';
            $args['phone_number_1'] = trim($args['home_phone']);
            $args['phone_label_2'] = 'Cell';
            $args['phone_number_2'] = trim($args['cell_phone']);
            unset($args['home_phone']);
            unset($args['cell_phone']);

            ciniki_core_loadMethod($ciniki, 'ciniki', 'customers', 'web', 'customerAdd');
            $rc = ciniki_customers_web_customerAdd($ciniki, $ciniki['request']['tnid'], $args);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $customer_id = $rc['id'];

            //
            // Once the account is created, authenticate
            //
            ciniki_core_loadMethod($ciniki, 'ciniki', 'customers', 'web', 'auth');
            $rc = ciniki_customers_web_auth($ciniki, $settings, $ciniki['request']['tnid'], $args['email_address'], $args['password']);
            error_log('RETURN');
            if( $rc['stat'] != 'ok' ) {
                $signinerrors = "Unable to authenticate, please try again or click Forgot your password to get a new one.";
            } else {
                //
                // Check for any module information that should be loaded into the session
                //
                foreach($ciniki['tenant']['modules'] as $module => $m) {
                    list($pkg, $mod) = explode('.', $module);
                    $rc = ciniki_core_loadMethod($ciniki, $pkg, $mod, 'web', 'accountSessionLoad');
                    if( $rc['stat'] == 'ok' ) {
                        $fn = $rc['function_call'];
                        $rc = $fn($ciniki, $settings, $ciniki['request']['tnid']);
                        if( $rc['stat'] != 'ok' ) {
                            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.24', 'msg'=>'Unable to load account information', 'err'=>$rc['err']));
                        }
                    }
                }

                if( isset($block['redirect']) && $block['redirect'] != '' ) {
                    header("Location: " . $block['redirect']);
                    exit;
                }
            }
        }
    }

    //
    // Check if a login occured
    //
    if( isset($_POST['action']) && $_POST['action'] == 'signin' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'customers', 'web', 'auth');
        $rc = ciniki_customers_web_auth($ciniki, $settings, $ciniki['request']['tnid'], $_POST['email'], $_POST['password']);
        if( $rc['stat'] != 'ok' ) {
            $signinerrors = "Unable to authenticate, please try again or click Forgot your password to get a new one.";
        } else {
            //
            // Check for any module information that should be loaded into the session
            //
            foreach($ciniki['tenant']['modules'] as $module => $m) {
                list($pkg, $mod) = explode('.', $module);
                $rc = ciniki_core_loadMethod($ciniki, $pkg, $mod, 'web', 'accountSessionLoad');
                if( $rc['stat'] == 'ok' ) {
                    $fn = $rc['function_call'];
                    $rc = $fn($ciniki, $settings, $ciniki['request']['tnid']);
                    if( $rc['stat'] != 'ok' ) {
                        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.24', 'msg'=>'Unable to load account information', 'err'=>$rc['err']));
                    }
                }
            }

            if( isset($block['redirect']) && $block['redirect'] != '' ) {
                header("Location: " . $block['redirect']);
                exit;
            }
        }
    }

    //
    // Check if forgot password was used
    //
    if( isset($_POST['action']) && $_POST['action'] == 'forgot' ) {
        $url = $block['redirect'];
        if( strstr($block['redirect'], '?') !== false ) {
            $url .= '&pwreset=1';
        } else {
            $url .= '?pwreset=1';
        }
        ciniki_core_loadMethod($ciniki, 'ciniki', 'customers', 'web', 'passwordRequestReset');
        $rc = ciniki_customers_web_passwordRequestReset($ciniki, $ciniki['request']['tnid'], $_POST['email'], $url);
        if( $rc['stat'] != 'ok' ) {
            $signinerrors = "You must enter a valid email address to get a new password.";
        } else {
            //
            // Display the signin message and return
            //
            $content .= "<div class='form-message-content'><div class='form-result-message form-success-message'>"
                . "<div class='form-message-wrapper'>"
                . "<p>A link has been sent to your email to get a new password. Please check your email and click on the link.</p>"
                . "</div></div></div>";
            if( isset($block['redirect']) && $block['redirect'] != '' ) {
                $_SESSION['passwordreset_referer'] = $block['redirect'];
            }
            return array('stat'=>'ok', 'content'=>$content);
        }
    }

    //
    // Check if password reset form submitted
    //
    $display_passwordreset = (isset($_GET['pwreset']) && $_GET['pwreset'] == 1 ? 'yes' : 'no');
    $passwordreseterrors = '';
    if( isset($_POST['action']) && $_POST['action'] == 'passwordreset' ) {
        if( !isset($_POST['newpassword']) || strlen($_POST['newpassword']) < 8 ) {
            $passwordreseterrors = "Your new password must be at least 8 characters long.";
            $display_passwordreset = 'yes';
        } elseif( !isset($_POST['email']) || $_POST['email'] == '' ) {
            $passwordreseterrors = "You need to enter an email address to reset your password.";
            $display_passwordreset = 'yes';
        } elseif( !isset($_POST['temppassword']) || $_POST['temppassword'] == '' ) {
            $signuperrors = "Sorry, but the link was invalid.  Please try again.";
        } else {
            $display_passwordreset = 'no';
            ciniki_core_loadMethod($ciniki, 'ciniki', 'customers', 'web', 'changeTempPassword');
            $rc = ciniki_customers_web_changeTempPassword($ciniki, $ciniki['request']['tnid'], $_POST['email'], $_POST['temppassword'], $_POST['newpassword']);
            if( $rc['stat'] != 'ok' ) {
                $signinerrors = "Sorry, we were unable to set your new password.  Please try again or call us for help.";
            } else {
                $signinmsg = "Your password has been reset, please login to continue.";
            }
        }
    }

    //
    // Check if password reset form should be displayed
    //
    if( $display_passwordreset == 'yes' ) {
        if( isset($passwordreseterrors) && $passwordreseterrors != '' ) {
            $content .= "<div class='form-message-content'><div class='form-result-message form-error-message'><div class='form-message-wrapper'>";
            $content .= "<p class='formerror'>" . $passwordreseterrors . "</p>";
            $content .= "</div></div></div>";
        }
        $content .= "<div id='reset-form' class='reset-form'>\n"
            . "<p>Please enter a new password.  It must be at least 8 characters long.</p>"
            . "<form method='POST' action=''>";
        $content .="<input type='hidden' name='action' value='passwordreset'>\n";
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
        $content .= "<div class='input'><label for='password'>New Password</label>"
            . "<input id='password' type='password' class='text' maxlength='100' name='newpassword' value='' />"
            . "</div>\n"
            . "<div class='submit'><input type='submit' class='submit' value='Set Password' /></div>\n"
            . "</form>"
            . "</div>\n";
        return array('stat'=>'ok', 'content'=>$content);
    }

    //
    // Check for any messages that should be displayed
    //
    if( isset($signinmsg) && $signinmsg != '' ) {
        $content .= "<div class='form-message-content'><div class='form-result-message form-success-message'><div class='form-message-wrapper'>";
        $content .= "<p class='formerror'>" . $signinmsg . "</p>";
        $content .= "</div></div></div>";
    }
    if( isset($signinerrors) && $signinerrors != '' ) {
        $content .= "<div class='form-message-content'><div class='form-result-message form-error-message'><div class='form-message-wrapper'>";
        $content .= "<p class='formerror'>" . $signinerrors . "</p>";
        $content .= "</div></div></div>";
    }

    //
    // Display the registration/login form
    //
    $content .= "<aside>";
    $content .= "<div id='signin-form' style='display: block;'>\n";
    $content .= "<h2>Sign In</h2>";
    $content .= "<p>Already have an account? Please sign in:</p>";
    $content .= "<form action='' method='POST'>";
//    if( $display_signup == 'createaccount' ) {
//        $content .= "<input type='hidden' name='next' value='edit'>";
//    }
//    if( $signup_err_msg != '' ) {
//        $content .= "<p class='formerror'>$signup_err_msg</p>\n";
//    }
    $content .="<input type='hidden' name='action' value='signin'>\n"
        . "<div class='input'><label for='email'>Email</label><input id='email' type='email' class='text' maxlength='250' name='email' value='$post_email' /></div>\n" 
        . "<div class='input'><label for='password'>Password</label><input id='password' type='password' class='text' maxlength='100' name='password' value='' /></div>\n"
        . "<div class='submit'><input type='submit' class='submit' value='Sign In' /></div>\n"
        . "</form>"
        . "<br/>";
    if( !isset($settings['page-account-password-change']) || $settings['page-account-password-change'] == 'yes' ) {
        $content .= "<div id='forgot-link'><p>"
            . "<a class='color' href='javscript:void(0);' onclick='swapLoginForm(\"forgotpassword\");return false;'>Forgot your password?</a></p></div>\n";
    }
    $content .= "</div>\n";

    // Forgot password form
    $content .= "<div id='forgotpassword-form' style='display:none;'>\n";
    $content .= "<h2>Forgot Password</h2>";
    $content .= "<p>Please enter your email address and you will receive a link to create a new password.</p>";
    $content .= "<form action='' method='POST'>";
//    if( $signup_err_msg != '' ) {
//        $content .= "<p class='formerror'>$signup_err_msg</p>\n";
//    }
    $content .= "<input type='hidden' name='action' value='forgot'>\n"
        . "<input type='hidden' name='redirect' value='' />"
        . "<div class='input'><label for='forgotemail'>Email </label><input id='forgotemail' type='email' class='text' maxlength='250' name='email' value='$post_email' /></div>\n" 
        . "<div class='submit'><input type='submit' class='submit' value='Get New Password' /></div>\n"
        . "</form>"
        . "<br/>"
        . "<div id='forgot-link'><p><a class='color' href='javascript:void();' onclick='swapLoginForm(\"signin\"); return false;'>Sign In</a></p></div>\n"
        . "</div>\n";

    $content .= "</aside>";

    //
    // Javascript to switch sign in and forgot password forms
    //
    $js .= "function swapLoginForm(l) {\n"
        . "if( l == 'forgotpassword' ) {\n"
            . "document.getElementById('signin-form').style.display = 'none';\n"
            . "document.getElementById('forgotpassword-form').style.display = 'block';\n"
            . "document.getElementById('forgotemail').value = document.getElementById('email').value;\n"
        . "} else {\n"
            . "document.getElementById('signin-form').style.display = 'block';\n"
            . "document.getElementById('forgotpassword-form').style.display = 'none';\n"
        . "}\n"
        . "return true;\n"
        . "};\n";
    //
    // Display the signup for a new account form
    //
    if( isset($block['register']) && $block['register'] == 'yes' ) {
        $content .= "<h2>Create a new account</h2>";
        $content .= "<form action='' method='POST'>";
        $content .= "<input type='hidden' name='action' value='createaccount'>";
//        if( $display_signup == 'createaccount' ) {
//            $content .= "<input type='hidden' name='next' value='edit'>";
//        }
        $fields = array(
            'first'=>array('name'=>'First Name', 'type'=>'text', 'class'=>'text', 'value'=>(isset($_POST['first'])?$_POST['first']:'')),
            'last'=>array('name'=>'Last Name', 'type'=>'text', 'class'=>'text', 'value'=>(isset($_POST['last'])?$_POST['last']:'')),
            'email_address'=>array('name'=>'Email Address', 'type'=>'email', 'class'=>'text', 'value'=>(isset($_POST['email_address'])?$_POST['email_address']:'')),
            'password'=>array('name'=>'Password', 'type'=>'password', 'class'=>'text', 'value'=>(isset($_POST['password'])?$_POST['password']:'')),
            'home_phone'=>array('name'=>'Home Phone', 'type'=>'text', 'class'=>'text', 'value'=>(isset($_POST['home_phone'])?$_POST['home_phone']:'')),
            'cell_phone'=>array('name'=>'Cell Phone', 'type'=>'text', 'class'=>'text', 'value'=>(isset($_POST['cell_phone'])?$_POST['cell_phone']:'')),
            );
        foreach($fields as $fid => $field) {
            $content .= "<div class='input'><label for='$fid'>" . $field['name'] . (array_key_exists($fid, $required_account_fields)?' *':'') . "</label>"
                . "<input type='" . $field['type'] . "' class='" . $field['class'] . "' name='$fid' value='" . $field['value'] . "'>";
            if( isset($errors[$fid]) && $errors[$fid] != '' ) {
                $content .= "<p class='formerror'>" . $errors[$fid] . "</p>";
            }
            $content .= "</div>";
        }

        //
        // Setup the address fields
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'countryCodes');
        $rc = ciniki_core_countryCodes($ciniki);
        $country_codes = $rc['countries'];
        $province_codes = $rc['provinces'];
        $address = array(
            'address1'=>(isset($_POST['address1'])?$_POST['address1']:''),
            'address2'=>(isset($_POST['address2'])?$_POST['address2']:''),
            'city'=>(isset($_POST['city'])?$_POST['city']:''),
            'province'=>(isset($args['province'])?$args['province']:''),
            'postal'=>(isset($_POST['postal'])?$_POST['postal']:''),
            'country'=>(isset($_POST['country'])?$_POST['country']:'Canada'),
            );
        $form = '';
        $form .= "<h2>Billing Address</h2>";
        $form .= "<div class='input country'>"
            . "<label for='country'>Country" . (array_key_exists('country', $required_account_fields)?' *':'') . "</label>"
            . "<select id='country_code' type='select' class='select' name='country' onchange='updateProvince()'>"
            . "<option value=''></option>";
        $selected_country = '';
        foreach($country_codes as $country_code => $country_name) {
            $form .= "<option value='" . $country_code . "' " 
                . (($country_code == $address['country'] || $country_name == $address['country'])?' selected':'')
                . ">" . $country_name . "</option>";
            if( $country_code == $address['country'] || $country_name == $address['country'] ) {
                $selected_country = $country_code;
            }
        }
        $form .= "</select></div>";
        $form .= "<div class='input address1'>"
            . "<label for='address1'>Address" . (array_key_exists('address1', $required_account_fields)?' *':'') . "</label>"
            . "<input type='text' class='text' name='address1' value='" . $address['address1'] . "'>"
            . "</div>";
        $form .= "<div class='input address2'>"
            . "<label for='address2'>" . (array_key_exists('address2', $required_account_fields)?' *':'') . "</label>"
            . "<input type='text' class='text' name='address2' value='" . $address['address2'] . "'>"
            . "</div>";
        $form .= "<div class='input city'>"
            . "<label for='city'>City" . (array_key_exists('city', $required_account_fields)?' *':'') . "</label>"
            . "<input type='text' class='text' name='city' value='" . $address['city'] . "'>"
            . "</div>";
        $form .= "<div class='input province'>"
            . "<label for='province'>State/Province" . (array_key_exists('province', $required_account_fields)?' *':'') . "</label>"
            . "<input id='province_text' type='text' class='text' name='province' "
                . ((isset($province_codes[$selected_country]) && $province_codes[$selected_country])?" style='display:none;'":"")
                . "value='" . $address['province'] . "'>";
        $pc_js = '';
        foreach($province_codes as $country_code => $provinces) {
            $form .= "<select id='province_code_{$country_code}' type='select' class='select' "
                . (($country_code != $selected_country)?" style='display:none;'":"")
                . " name='province_code_{$country_code}' >"
                . "<option value=''></option>";
            $pc_js .= "document.getElementById('province_code_" . $country_code . "').style.display='none';";
            foreach($provinces as $province_code => $province_name) {
                $form .= "<option value='" . $province_code . "'" 
                    . (($province_code == (isset($_POST["province_code_{$country_code}"])?$_POST["province_code_{$country_code}"]:'') || $province_name == (isset($_POST["province_code_{$country_code}"])?$_POST["province_code_{$country_code}"]:''))?' selected':'')
                    . ">" . $province_name . "</option>";
            }
            $form .= "</select>";
        }
        $form .= "</div>";
        $form .= "<div class='input postal'>"
            . "<label for='postal'>ZIP/Postal Code" . (array_key_exists('postal', $required_account_fields)?' *':'') . "</label>"
            . "<input type='text' class='text' name='postal' value='" . $address['postal'] . "'>"
            . "</div>";
        $js .= "function updateProvince() {"
                . "var cc = document.getElementById('country_code');"
                . "var pr = document.getElementById('province_text');"
                . "var pc = document.getElementById('province_code_'+cc.value);"
                . $pc_js
                . "if( pc != null ) {"
                    . "pc.style.display='';"
                    . "pr.style.display='none';"
                . "}else{"
                    . "pr.style.display='';"
                . "}"
            . "}";
        $content .= $form;

        $content .= "<div class='submit'><input type='submit' name='continue' class='submit' value='Next' /></div>\n";
        $content .= "</form>";
    }




    return array('stat'=>'ok', 'content'=>$content, 'js'=>$js);
}
?>
