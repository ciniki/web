<?php
//
// Description
// -----------
// This block displays the account login form and registration form is enabled, and allows for signup.
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_processBlockLoginAccount(&$ciniki, $settings, $tnid, $block) {

    $content = '';
    $js = '';
    $signinerrors = '';
    $signinmsg = '';
    $display_register_form = 'no';

    $post_email = isset($_POST['email']) ? $_POST['email'] : '';

    if( isset($block['title']) && $block['title'] != '' ) {
        $content .= "<h2>" . $block['title'] . "</h2>";
    }

    $required_account_fields = array(
        'first'=>'First Name', 
        'last'=>'Last Name', 
        'primary_email'=>'Email Address', 
        'password'=>'Password',
        'mailing_address1'=>'Mailing Address', 
        'mailing_city'=>'City', 
        'mailing_province'=>'State/Province', 
        'mailing_postal'=>'ZIP/Postal Code', 
        'mailing_province'=>'Province',
        'mailing_country'=>'Country',
        );

    //
    // Check if a signup occured
    //
    if( isset($_POST['action']) && $_POST['action'] == 'createaccount' ) {
        $signinerrors = '';
        $display_register_form = 'yes';

        //
        // Check for required fields
        //
        $args = $_POST;
        if( isset($args['mailing_province_code_' . $args['mailing_country']]) && $args['mailing_province_code_' . $args['mailing_country']] != '' ) {
            $args['mailing_province'] = $args['mailing_province_code_' . $args['mailing_country']];
        }
        if( isset($args['billing_province_code_' . $args['billing_country']]) && $args['billing_province_code_' . $args['billing_country']] != '' ) {
            $args['billing_province'] = $args['billing_province_code_' . $args['billing_country']];
        }
        if( isset($args['billingflag']) && $args['billingflag'] == 'no' ) {
            $args['mailing_flags'] = 0x04;
        } else {
            $args['mailing_flags'] = 0x06;
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
        if( ciniki_core_checkModuleFlags($ciniki, 'ciniki.customers', 0x8000) ) {
            if( isset($_POST['birthday']) && $_POST['birthday'] != '' ) {
                $ts = strtotime($_POST['birthday']);
                if( $ts === FALSE ) {
                    $signinerrors = "Invalid birthdate, please enter in the format 'month day, year'.";
                    $errors = 'yes';
                } else {
                    $args['birthdate'] = strftime("%Y-%m-%d", $ts);
                }
            }
        }
        if( ciniki_core_checkModuleFlags($ciniki, 'ciniki.customers', 0x4000) ) {
            if( isset($_POST['connection']) && $_POST['connection'] != '' ) {
                $args['connection'] = $_POST['connection'];
            }
        }
        if( $signinerrors == '' ) {
            //
            // Check if email address already exists
            //
            ciniki_core_loadMethod($ciniki, 'ciniki', 'customers', 'hooks', 'customerLookup');
            $rc = ciniki_customers_hooks_customerLookup($ciniki, $ciniki['request']['tnid'], array('email'=>$_POST['primary_email']));
            if( $rc['stat'] != 'noexist' ) {
                $signinerrors = "There is already an account for that email address, please use the Forgot Password link to recover your password.";
            }
        }
        $args['primary_email_flags'] = 0x01;
        if( $signinerrors == '' ) {
            //
            // Setup the customer defaults
            //
            ciniki_core_loadMethod($ciniki, 'ciniki', 'customers', 'web', 'accountAdd');
            $rc = ciniki_customers_web_accountAdd($ciniki, $tnid, $args);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $customer_id = $rc['id'];

            //
            // Once the account is created, authenticate
            //
            ciniki_core_loadMethod($ciniki, 'ciniki', 'customers', 'web', 'auth');
            $rc = ciniki_customers_web_auth($ciniki, $settings, $ciniki['request']['tnid'], $args['primary_email'], $args['password']);
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
    elseif( isset($_POST['action']) && $_POST['action'] == 'selecttype' && isset($_POST['type']) ) {
        $display_register_form = 'yes';
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
        $url = $ciniki['request']['ssl_domain_base_url'] . $block['redirect'];
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
    if( $display_register_form == 'no' ) {
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
    }

    //
    // Display the signup for a new account form
    //
    if( isset($block['register']) && $block['register'] == 'yes' ) {
        if( $display_register_form == 'yes' ) {
            $customer = array(
                'parent_address1' => (isset($_POST['parent_address1']) ? $_POST['parent_address1'] : ''),
                'parent_address2' => (isset($_POST['parent_address2']) ? $_POST['parent_address2'] : ''),
                'parent_city' => (isset($_POST['parent_city']) ? $_POST['parent_city'] : ''),
                'parent_province' => (isset($_POST['parent_province']) ? $_POST['parent_province'] : ''),
                'parent_postal' => (isset($_POST['parent_postal']) ? $_POST['parent_postal'] : ''),
                'parent_country' => (isset($_POST['parent_country']) ? $_POST['parent_country'] : ''),
                'mailing_address1' => (isset($_POST['mailing_address1']) ? $_POST['mailing_address1'] : ''),
                'mailing_address2' => (isset($_POST['mailing_address2']) ? $_POST['mailing_address2'] : ''),
                'mailing_city' => (isset($_POST['mailing_city']) ? $_POST['mailing_city'] : ''),
                'mailing_province' => (isset($_POST['mailing_province']) ? $_POST['mailing_province'] : ''),
                'mailing_postal' => (isset($_POST['mailing_postal']) ? $_POST['mailing_postal'] : ''),
                'mailing_country' => (isset($_POST['mailing_country']) ? $_POST['mailing_country'] : ''),
                'billing_address1' => (isset($_POST['billing_address1']) ? $_POST['billing_address1'] : ''),
                'billing_address2' => (isset($_POST['billing_address2']) ? $_POST['billing_address2'] : ''),
                'billing_city' => (isset($_POST['billing_city']) ? $_POST['billing_city'] : ''),
                'billing_province' => (isset($_POST['billing_province']) ? $_POST['billing_province'] : ''),
                'billing_postal' => (isset($_POST['billing_postal']) ? $_POST['billing_postal'] : ''),
                'billing_country' => (isset($_POST['billing_country']) ? $_POST['billing_country'] : ''),
                'mailing_flags' => (isset($_POST['mailing_flags']) ? $_POST['mailing_flags'] : 0x06),
                'connection' => (isset($_POST['connection']) ? $_POST['connection'] : ''),
                );
            $content .= "<h2 class='wide'>Create a new account</h2>";
            $content .= "<form class='wide' action='' method='POST'>";
            $content .= "<input type='hidden' name='action' value='createaccount'>";
            $content .= "<input type='hidden' name='type' value='" . $_POST['type'] . "'>";

            ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'countryCodes');
            $rc = ciniki_core_countryCodes($ciniki);
            $country_codes = $rc['countries'];
            $province_codes = $rc['provinces'];
            if( $_POST['type'] == 20 ) {
                $content .= "<div class='contact-details-section'>"
                    . "<div class='input'>"
                    . "<label for='parent_name'>Family Name*</label>"
                    . "<input type='text' class='text' name='parent_name' value='" . (isset($_POST['parent_name'])?$_POST['parent_name']:'') . "'>"
                    . "</div>"
                    . "</div>";
                $content .= "<h2 class='wide'>Parent/Guardian</h2>";
            } elseif( $_POST['type'] == 30 ) {
                $content .= "<div class='contact-details-sect'>"
                    . "<div class='input'>"
                    . "<label for='parent_name'>Business Name*</label>"
                    . "<input type='text' class='text' name='parent_name' value='" . (isset($_POST['parent_name'])?$_POST['parent_name']:'') . "'>"
                    . "</div>"
                    . "<div class='input'>"
                    . "<label for='parent_email'>Business Email</label>"
                    . "<input type='text' class='text' name='parent_email' value='" . (isset($_POST['parent_email'])?$_POST['parent_email']:'') . "'>"
                    . "</div>"
                    . "<div class='input'>"
                    . "<label for='parent_work'>Business Phone</label>"
                    . "<input type='text' class='text' name='parent_work' value='" . (isset($_POST['parent_work'])?$_POST['parent_work']:'') . "'>"
                    . "</div>"
                    . "<div class='input'>"
                    . "<label for='parent_fax'>Business Fax</label>"
                    . "<input type='text' class='text' name='parent_fax' value='" . (isset($_POST['parent_fax'])?$_POST['parent_fax']:'') . "'>"
                    . "</div>"
                    . "</div>";
                //
                // Setup the address fields
                //
                $content .= "<h2 class='wide'>Business Address</h2>";
                $content .= "<div class='contact-details-section contact-details-form-mailing'>";
                $content .= "<div class='input parent_address1'>"
                    . "<label for='parent_address1'>Mailing Address Line 1*</label>"
                    . "<input type='text' class='text' name='parent_address1' value='" . $customer['parent_address1'] . "'>"
                    . "</div>";
                $content .= "<div class='input parent_address2'>"
                    . "<label for='parent_address2'>Line 2</label>"
                    . "<input type='text' class='text' name='parent_address2' value='" . $customer['parent_address2'] . "'>"
                    . "</div>";
                $content .= "<div class='input parent_city'>"
                    . "<label for='parent_city'>City*</label>"
                    . "<input type='text' class='text' name='parent_city' value='" . $customer['parent_city'] . "'>"
                    . "</div>";
                $content .= "<div class='input parent_country'>"
                    . "<label for='parent_country'>Country*</label>"
                    . "<select id='parent_country_code' type='select' class='select' name='parent_country' onchange='updateMailingProvince()'>"
                    . "<option value=''></option>";
                if( $customer['parent_country'] == '' ) {
                    $customer['parent_country'] = 'Canada';
                }
                foreach($country_codes as $country_code => $country_name) {
                    $content .= "<option value='" . $country_code . "' " 
                        . (($country_code == $customer['parent_country'] || $country_name == $customer['parent_country'])?' selected':'')
                        . ">" . $country_name . "</option>";
                    if( $country_code == $customer['parent_country'] || $country_name == $customer['parent_country'] ) {
                        $selected_country = $country_code;
                    }
                }
                $content .= "</select></div>";
                $content .= "<div class='input parent_province'>"
                    . "<label for='parent_province'>State/Province*</label>"
                    . "<input id='parent_province_text' type='text' class='text' name='parent_province' "
                        . (isset($province_codes[$selected_country])?" style='display:none;'":"")
                        . "value='" . $customer['parent_province'] . "'>";
                $js = '';
                if( $customer['parent_province'] == '' ) {
                    $customer['parent_province'] = 'ON';
                }
                foreach($province_codes as $country_code => $provinces) {
                    $content .= "<select id='parent_province_code_{$country_code}' type='select' class='select' "
                        . (($country_code != $selected_country)?" style='display:none;'":"")
                        . "name='parent_province_code_{$country_code}' >"
                        . "<option value=''></option>";
                    $js .= "document.getElementById('parent_province_code_" . $country_code . "').style.display='none';";
                    foreach($provinces as $province_code => $province_name) {
                        $content .= "<option value='" . $province_code . "'" 
                            . (($province_code == $customer['parent_province'] || $province_name == $customer['parent_province'])?' selected':'')
                            . ">" . $province_name . "</option>";
                    }
                    $content .= "</select>";
                }
                $content .= "</div>";
                $content .= "<div class='input parent_postal'>"
                    . "<label for='parent_postal'>ZIP/Postal Code</label>"
                    . "<input type='text' class='text' name='parent_postal' value='" . $customer['parent_postal'] . "'>"
                    . "</div>";
                $content .= "<script type='text/javascript'>"
                    . "function updateMailingProvince() {"
                        . "var cc = document.getElementById('parent_country_code');"
                        . "var pr = document.getElementById('parent_province_text');"
                        . "var pc = document.getElementById('parent_province_code_'+cc.value);"
                        . $js
                        . "if( pc != null ) {"
                            . "pc.style.display='';"
                            . "pr.style.display='none';"
                        . "}else{"
                            . "pr.style.display='';"
                        . "}"
                    . "}"
                    . "</script>";
                $content .= "</div>";
                $content .= "<h2 class='wide'>Administrator</h2>";
            }

            $content .= "<div class='contact-details-section'>"
                . "<div class='input'>"
                . "<label for='first'>First Name*</label>"
                . "<input type='text' class='text' name='first' value='" . (isset($_POST['first'])?$_POST['first']:'') . "'>"
                . "</div>"
                . "<div class='input'>"
                . "<label for='last'>Last Name*</label>"
                . "<input type='text' class='text' name='last' value='" . (isset($_POST['last'])?$_POST['last']:'') . "'>"
                . "</div>";
            if( ciniki_core_checkModuleFlags($ciniki, 'ciniki.customers', 0x0800) ) {
                // Specified as birthday, and converted to birthdate when parsed
                $content .= "<div class='input'>"
                    . "<label for='birthday'>Birthday</label>"
                    . "<input type='text' class='text' name='birthday' value='" . (isset($_POST['birthday'])?$_POST['birthday']:'') . "'>"
                    . "</div>";
            }
            $content .= "</div>";
            $content .= "<div class='contact-details-section'>"
                . "<div class='input'>"
                . "<label for='primary_email'>Email Address*</label>"
                . "<input type='text' class='text' name='primary_email' value='" . (isset($_POST['primary_email'])?$_POST['primary_email']:'') . "'>"
                . "</div>"
                . "<div class='input'>"
                . "<label for='password'>Password*</label>"
                . "<input type='password' class='text' name='password' value='" . (isset($_POST['password'])?$_POST['password']:'') . "'>"
                . "</div>"
                . "</div>";
            $content .= "<div class='contact-details-section'>"
                . "<div class='input'>"
                . "<label for='phone_cell'>Cell Phone</label>"
                . "<input type='phone_cell' class='text' name='phone_cell' value='" . (isset($_POST['phone_cell'])?$_POST['phone_cell']:'') . "'>"
                . "</div>"
                . "<div class='input'>"
                . "<label for='phone_home'>Home Phone</label>"
                . "<input type='phone_home' class='text' name='phone_home' value='" . (isset($_POST['phone_home'])?$_POST['phone_home']:'') . "'>"
                . "</div>"
                . "<div class='input'>"
                . "<label for='phone_work'>Work Phone</label>"
                . "<input type='phone_work' class='text' name='phone_work' value='" . (isset($_POST['phone_work'])?$_POST['phone_work']:'') . "'>"
                . "</div>"
                . "<div class='input'>"
                . "<label for='phone_fax'>Fax</label>"
                . "<input type='phone_fax' class='text' name='phone_fax' value='" . (isset($_POST['phone_fax'])?$_POST['phone_fax']:'') . "'>"
                . "</div>"
                . "</div>";

            //
            // Setup the address fields
            //
            $content .= "<div class='contact-details-section contact-details-form-mailing'>";
            $content .= "<div class='input mailing_address1'>"
                . "<label for='mailing_address1'>Mailing Address Line 1*</label>"
                . "<input type='text' class='text' name='mailing_address1' value='" . $customer['mailing_address1'] . "'>"
                . "</div>";
            $content .= "<div class='input mailing_address2'>"
                . "<label for='mailing_address2'>Line 2</label>"
                . "<input type='text' class='text' name='mailing_address2' value='" . $customer['mailing_address2'] . "'>"
                . "</div>";
            $content .= "<div class='input mailing_city'>"
                . "<label for='mailing_city'>City*</label>"
                . "<input type='text' class='text' name='mailing_city' value='" . $customer['mailing_city'] . "'>"
                . "</div>";
            $content .= "<div class='input mailing_country'>"
                . "<label for='mailing_country'>Country*</label>"
                . "<select id='mailing_country_code' type='select' class='select' name='mailing_country' onchange='updateMailingProvince()'>"
                . "<option value=''></option>";
            if( $customer['mailing_country'] == '' ) {
                $customer['mailing_country'] = 'Canada';
            }
            foreach($country_codes as $country_code => $country_name) {
                $content .= "<option value='" . $country_code . "' " 
                    . (($country_code == $customer['mailing_country'] || $country_name == $customer['mailing_country'])?' selected':'')
                    . ">" . $country_name . "</option>";
                if( $country_code == $customer['mailing_country'] || $country_name == $customer['mailing_country'] ) {
                    $selected_country = $country_code;
                }
            }
            $content .= "</select></div>";
            $content .= "<div class='input mailing_province'>"
                . "<label for='mailing_province'>State/Province*</label>"
                . "<input id='mailing_province_text' type='text' class='text' name='mailing_province' "
                    . (isset($province_codes[$selected_country])?" style='display:none;'":"")
                    . "value='" . $customer['mailing_province'] . "'>";
            $js = '';
            if( $customer['mailing_province'] == '' ) {
                $customer['mailing_province'] = 'ON';
            }
            foreach($province_codes as $country_code => $provinces) {
                $content .= "<select id='mailing_province_code_{$country_code}' type='select' class='select' "
                    . (($country_code != $selected_country)?" style='display:none;'":"")
                    . "name='mailing_province_code_{$country_code}' >"
                    . "<option value=''></option>";
                $js .= "document.getElementById('mailing_province_code_" . $country_code . "').style.display='none';";
                foreach($provinces as $province_code => $province_name) {
                    $content .= "<option value='" . $province_code . "'" 
                        . (($province_code == $customer['mailing_province'] || $province_name == $customer['mailing_province'])?' selected':'')
                        . ">" . $province_name . "</option>";
                }
                $content .= "</select>";
            }
            $content .= "</div>";
            $content .= "<div class='input mailing_postal'>"
                . "<label for='mailing_postal'>ZIP/Postal Code*</label>"
                . "<input type='text' class='text' name='mailing_postal' value='" . $customer['mailing_postal'] . "'>"
                . "</div>";
            $content .= "<script type='text/javascript'>"
                . "function updateMailingProvince() {"
                    . "var cc = document.getElementById('mailing_country_code');"
                    . "var pr = document.getElementById('mailing_province_text');"
                    . "var pc = document.getElementById('mailing_province_code_'+cc.value);"
                    . $js
                    . "if( pc != null ) {"
                        . "pc.style.display='';"
                        . "pr.style.display='none';"
                    . "}else{"
                        . "pr.style.display='';"
                    . "}"
                . "}"
                . "</script>";
            $content .= "</div>";

            // Billing Address
            $content .= "<div class='contact-details-section contact-details-form-billing'>";
            $content .= "<div class='input'>";
            $content .= "<label for='billingflag'>Same billing address</label>"
                . "<select id='billingflag' name='billingflag' type='select' class='select' onchange='updateBillingForm();'>";
            if( ($customer['mailing_flags']&0x02) == 0x02 ) {
                $content .= "<option value='yes' selected>Yes</option>"
                    . "<option value='no'>No</option>";
            } else {
                $content .= "<option value='yes'>Yes</option>"
                    . "<option value='no' selected>No</option>";
            }
            $content .= "</select>"
                . "</div>";
            if( ($customer['mailing_flags']&0x02) == 0x02 ) {
                $content .= "<div id='billingform' style='display:none;'>";
            } else {
                $content .= "<div id='billingform'>";
            }
            $content .= "<div class='input billing_address1'>"
                . "<label for='billing_address1'>Billing Address Line 1</label>"
                . "<input type='text' class='text' name='billing_address1' value='" . $customer['billing_address1'] . "'>"
                . "</div>";
            $content .= "<div class='input billing_address2'>"
                . "<label for='billing_address2'>Line 2</label>"
                . "<input type='text' class='text' name='billing_address2' value='" . $customer['billing_address2'] . "'>"
                . "</div>";
            $content .= "<div class='input billing_city'>"
                . "<label for='billing_city'>City</label>"
                . "<input type='text' class='text' name='billing_city' value='" . $customer['billing_city'] . "'>"
                . "</div>";
            $content .= "<div class='input billing_country'>"
                . "<label for='billing_country'>Country</label>"
                . "<select id='billing_country_code' type='select' class='select' name='billing_country' onchange='updateBillingProvince()'>"
                . "<option value=''></option>";
            if( $customer['billing_country'] == '' ) {
                $customer['billing_country'] = 'Canada';
            }
            foreach($country_codes as $country_code => $country_name) {
                $content .= "<option value='" . $country_code . "' " 
                    . (($country_code == $customer['billing_country'] || $country_name == $customer['billing_country'])?' selected':'')
                    . ">" . $country_name . "</option>";
                if( $country_code == $customer['billing_country'] || $country_name == $customer['billing_country'] ) {
                    $selected_country = $country_code;
                }
            }
            $content .= "</select></div>";
            $content .= "<div class='input billing_province'>"
                . "<label for='billing_province'>State/Province</label>"
                . "<input id='billing_province_text' type='text' class='text' name='billing_province' "
                    . (isset($province_codes[$selected_country])?" style='display:none;'":"")
                    . "value='" . $customer['billing_province'] . "'>";
            $js = '';
            foreach($province_codes as $country_code => $provinces) {
                $content .= "<select id='billing_province_code_{$country_code}' type='select' class='select' "
                    . (($country_code != $selected_country)?" style='display:none;'":"")
                    . "name='billing_province_code_{$country_code}' >"
                    . "<option value=''></option>";
                $js .= "document.getElementById('billing_province_code_" . $country_code . "').style.display='none';";
                foreach($provinces as $province_code => $province_name) {
                    $content .= "<option value='" . $province_code . "'" 
                        . (($province_code == $customer['billing_province'] || $province_name == $customer['billing_province'])?' selected':'')
                        . ">" . $province_name . "</option>";
                }
                $content .= "</select>";
            }
            $content .= "</div>";
            $content .= "<div class='input billing_postal'>"
                . "<label for='billing_postal'>ZIP/Postal Code</label>"
                . "<input type='text' class='text' name='billing_postal' value='" . $customer['billing_postal'] . "'>"
                . "</div>";
            $content .= "</div>"; // End wrapper div id billingform 
            // How did you hear about us
            if( ciniki_core_checkModuleFlags($ciniki, 'ciniki.customers', 0x4000) ) {
                $content .= "<div class='contact-details-section contact-details-form-connection'>";
                $content .= "<div class='input connection wide'>"
                    . "<label for='connection'>How did you hear about us?</label>"
                    . "<input type='text' class='text' name='connection' value='" . $customer['connection'] . "'>"
                    . "</div>";
                $content .= "</div>"; 
            }
            $content .= "<script type='text/javascript'>"
                . "function updateBillingForm() {"
                    . "var f = document.getElementById('billingflag').value;"
                    . "if(f=='yes'){"
                        . "document.getElementById('billingform').style.display = 'none';"
                    . "}else{"
                        . "document.getElementById('billingform').style.display = 'block';"
                    . "}"
                . "}"
                . "function updateBillingProvince() {"
                    . "var cc = document.getElementById('billing_country_code');"
                    . "var pr = document.getElementById('billing_province_text');"
                    . "var pc = document.getElementById('billing_province_code_'+cc.value);"
                    . $js
                    . "if( pc != null ) {"
                        . "pc.style.display='';"
                        . "pr.style.display='none';"
                    . "}else{"
                        . "pr.style.display='';"
                    . "}"
                . "}"
                . "</script>";
            $content .= "</div>";

            $content .= "<div class='submit'><input type='submit' name='continue' class='submit' value='Next' /></div>\n";
            $content .= "</form>";
        } else {
            $content .= "<h2>Create a new account</h2>";
            $content .= "<p>What type of account do you want to create?</p>";
            $content .= "<form action='' method='POST'>";
            $content .= "<input type='hidden' name='action' value='selecttype'>";
            $content .= "<input type='hidden' name='type' value='30'>";
            $content .= "<div class='big-button aligncenter'><input type='submit' name='continue' class='submit' value='Business with Employees' /></div>\n";
            $content .= "</form>";
/*            $content .= "<form action='' method='POST'>";
            $content .= "<input type='hidden' name='action' value='selecttype'>";
            $content .= "<input type='hidden' name='type' value='20'>";
            $content .= "<div class='big-button aligncenter'><input type='submit' name='continue' class='submit' value='Family with Children' /></div>\n";
            $content .= "</form>"; */
            $content .= "<form action='' method='POST'>";
            $content .= "<input type='hidden' name='action' value='selecttype'>";
            $content .= "<input type='hidden' name='type' value='10'>";
            $content .= "<div class='big-button aligncenter'><input type='submit' name='continue' class='submit' value='Individual' /></div>\n";
            $content .= "</form>";
        }
    }

    return array('stat'=>'ok', 'content'=>$content, 'js'=>$js);
}
?>
