<?php
//
// Description
// -----------
// This function will generate the contact page for the website.
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_generatePageContact(&$ciniki, $settings) {

    //
    // Store the content created by the page
    // Make sure everything gets generated ok before returning the content
    //
    $content = '';
    $ciniki['request']['page-container-class'] = 'page-contact';

    //
    // FIXME: Check if anything has changed, and if not load from cache
    //
    $contact_form_submitted = 'no';
    $contact_form_errors = '';
    $contact_form_success = '';

    if( isset($ciniki['tenant']['modules']['ciniki.web']['flags'])
        && ($ciniki['tenant']['modules']['ciniki.web']['flags']&0x04) > 0 
        && isset($_POST['contact-form-name']) 
        ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContactForm');
        $rc = ciniki_web_processContactForm($ciniki, $settings, $ciniki['request']['tnid']);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['error_message']) && $rc['error_message'] != '' ) {
            $contact_form_errors = $rc['error_message'];
        } else {
            $contact_form_submitted = 'yes';
            $contact_form_success = $rc['success_message'];
        }
        
/*      if( !isset($_POST['contact-form-name']) || $_POST['contact-form-name'] == '' ) {
            $contact_form_errors = "You must enter your name.<br/>";
        }
        if( !isset($_POST['contact-form-email']) || $_POST['contact-form-email'] == '' ) {
            $contact_form_errors = "You must enter your email address to get a response.<br/>";
        }
        if( !isset($_POST['contact-form-subject']) || $_POST['contact-form-subject'] == '' ) {
            $contact_form_errors = "Please add a subject.<br/>";
        } else {
            $subject = $_POST['contact-form-subject'];
        }
        $msg = "New message from " . $_POST['contact-form-name'] . " (" . $_POST['contact-form-email'] . ")\n"
            . "\n"
            . "Message: \n";
        if( !isset($_POST['contact-form-message']) || $_POST['contact-form-message'] == '' ) {
            $msg .= 'No message added';
        } else {
            $msg .= $_POST['contact-form-message'];
        }

        if( $contact_form_errors == '' ) {
            if( isset($settings['page-contact-form-emails']) && $settings['page-contact-form-emails'] != '' ) {
                $send_to_emails = explode(',', $settings['page-contact-form-emails']);
                foreach($send_to_emails as $email) {
                    $ciniki['emailqueue'][] = array('to'=>trim($email),
                        'replyto_email'=>$_POST['contact-form-email'],
                        'replyto-name'=>$_POST['contact-form-name'],
                        'subject'=>$subject,
                        'textmsg'=>$msg,
                        );
                }
            } else {
                //
                //  Email the owners a bug was added to the system.
                //
                $strsql = "SELECT user_id "
                    . "FROM ciniki_tenant_users "
                    . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['tnid']) . "' "
                    . "AND package = 'ciniki' "
                    . "AND (permission_group = 'owners') "
                    . "";
                ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQueryList');
                $rc = ciniki_core_dbQueryList($ciniki, $strsql, 'ciniki.bugs', 'user_ids', 'user_id');
                if( $rc['stat'] != 'ok' || !isset($rc['user_ids']) || !is_array($rc['user_ids']) ) {
                    $contact_form_errors = "Oops, we're sorry but we seem to have hit a bit of a snag.  We've already the geeks and they'll get the problem fixed for us.  Please try again later.";
                    error_log('WEB: Error with form submit');
                } else {
                    foreach($rc['user_ids'] as $user_id) {
                        $ciniki['emailqueue'][] = array('user_id'=>$user_id,
                            'replyto_email'=>$_POST['contact-form-email'],
                            'replyto_name'=>$_POST['contact-form-name'],
                            'subjext'=>$subject,
                            'textmsg'=>$msg,
                            );
                    }
                }
            }
            $contact_form_submitted = 'yes';
        }
        */
    }

    //
    // Check which parts of the tenant contact information to display automatically
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'web', 'contact');
    $rc = ciniki_tenants_web_contact($ciniki, $settings, $ciniki['request']['tnid']);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $contact_details = $rc['details'];
    $contact_users = $rc['users'];

    $contact_content = '';
    if( isset($settings['page-contact-tenant-name-display']) && $settings['page-contact-tenant-name-display'] == 'yes' 
        && isset($contact_details['contact.tenant.name']) && $contact_details['contact.tenant.name'] != '' ) {
        $contact_content .= "<span class='contact-title'>" . $contact_details['contact.tenant.name'] . "</span><br/>\n";
    }
    if( isset($settings['page-contact-person-name-display']) && $settings['page-contact-person-name-display'] == 'yes' 
        && isset($contact_details['contact.person.name']) && $contact_details['contact.person.name'] != '' ) {
        if( !isset($settings['page-contact-tenant-name-display']) || $settings['page-contact-tenant-name-display'] != 'yes' ) {
            $contact_content .= "<span class='contact-title'>" . $contact_details['contact.person.name'] . "</span><br/>\n";
        } else {
            $contact_content .= $contact_details['contact.person.name'] . "<br/>\n";
        }
    }
    if( isset($settings['page-contact-address-display']) && $settings['page-contact-address-display'] == 'yes' ) {
        if( isset($contact_details['contact.address.street1']) && $contact_details['contact.address.street1'] != '' ) {
            $contact_content .= $contact_details['contact.address.street1'] . "<br/>\n";
        }
        if( isset($contact_details['contact.address.street2']) && $contact_details['contact.address.street2'] != '' ) {
            $contact_content .= $contact_details['contact.address.street2'] . "<br/>\n";
        }
        if( isset($contact_details['contact.address.city']) && $contact_details['contact.address.city'] != '' ) {
            $contact_content .= $contact_details['contact.address.city'] . "\n";
        }
        if( isset($contact_details['contact.address.city']) && $contact_details['contact.address.city'] != ''
            && isset($contact_details['contact.address.province']) && $contact_details['contact.address.province'] != '' ) {
            $contact_content .= ", " . $contact_details['contact.address.province'] . "";
        }
        if( isset($contact_details['contact.address.postal']) && $contact_details['contact.address.postal'] != '' ) {
            $contact_content .= "  " . $contact_details['contact.address.postal'] . "<br/>\n";
        } else {
            $contact_content .= "<br/>\n";
        }
        if( isset($contact_details['contact.address.country']) && $contact_details['contact.address.country'] != '' ) {
            $contact_content .= $contact_details['contact.address.country'] . "<br/>\n";
        }
    }
    if( isset($settings['page-contact-phone-display']) && $settings['page-contact-phone-display'] == 'yes' 
        && isset($contact_details['contact.phone.number']) && $contact_details['contact.phone.number'] != '' ) {
        $contact_content .= "phone: " . $contact_details['contact.phone.number'] . "<br/>\n";
    }
    if( isset($settings['page-contact-fax-display']) && $settings['page-contact-fax-display'] == 'yes' 
        && isset($contact_details['contact.fax.number']) && $contact_details['contact.fax.number'] != '' ) {
        $contact_content .= "fax: " . $contact_details['contact.fax.number'] . "<br/>\n";
    }
    if( isset($settings['page-contact-email-display']) && $settings['page-contact-email-display'] == 'yes' 
        && isset($contact_details['contact.email.address']) && $contact_details['contact.email.address'] != '' ) {
        $contact_content .= "<a class='contact-email' href='mailto:" . $contact_details['contact.email.address'] . "' />" . $contact_details['contact.email.address'] . "</a><br/>\n";
    }

    //
    // Generate the list of employee's who are to be shown on the website
    //
    if( isset($settings['page-contact-user-display']) && $settings['page-contact-user-display'] == 'yes' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processEmployeeBios');
        $rc = ciniki_web_processEmployeeBios($ciniki, $settings, 'contact', $contact_users);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['content']) && $rc['content'] != '' ) {
            $contact_content .= $rc['content'];
        }
    }

    //
    // Generate the content of the page
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash');
    $rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_web_content', 'tnid', $ciniki['request']['tnid'], 'ciniki.web', 'content', 'page-contact');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    if( isset($rc['content']['page-contact-content']) ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
        $rc = ciniki_web_processContent($ciniki, $settings, $rc['content']['page-contact-content']);    
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $page_content = $rc['content'];
    }

    //
    // Check if map is to be displayed
    //
    if( isset($settings['page-contact-google-map']) && $settings['page-contact-google-map'] == 'yes' 
        && isset($settings['page-contact-map-latitude']) && $settings['page-contact-map-latitude'] != '' 
        && isset($settings['page-contact-map-longitude']) && $settings['page-contact-map-longitude'] != '' 
        ) {
        if( !isset($ciniki['request']['inline_javascript']) ) {
            $ciniki['request']['inline_javascript'] = '';
        }
        $ciniki['request']['inline_javascript'] .= ''
            . '<script type="text/javascript">'
            . 'function gmap_initialize() {'
                . 'var myLatlng = new google.maps.LatLng(' . $settings['page-contact-map-latitude'] . ',' . $settings['page-contact-map-longitude'] . ');'
                . 'var mapOptions = {'
                    . 'zoom: 13,'
                    . 'center: myLatlng,'
                    . 'panControl: false,'
                    . 'zoomControl: true,'
                    . 'scaleControl: true,'
                    . 'mapTypeId: google.maps.MapTypeId.ROADMAP'
                . '};'
                . 'var map = new google.maps.Map(document.getElementById("googlemap"), mapOptions);'
                . 'var marker = new google.maps.Marker({'
                    . 'position: myLatlng,'
                    . 'map: map,'
                    . 'title:"",'
                    . '});'
            . '};'
            . 'function loadMap() {'
                . 'var script = document.createElement("script");'
                . 'script.type = "text/javascript";'
                . 'script.src = "' . ($ciniki['request']['ssl']=='yes'?'https':'http') . '://maps.googleapis.com/maps/api/js?key=' . $ciniki['config']['ciniki.web']['google.maps.api.key'] . '&sensor=false&callback=gmap_initialize";'
                . 'document.body.appendChild(script);'
            . '};'
            . 'window.onload = loadMap;'
            . '</script>';
        $map_content = '<aside><div class="googlemap" id="googlemap"></div></aside>';
    }

    //
    // Add the header
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageHeader');
    $rc = ciniki_web_generatePageHeader($ciniki, $settings, 'Contact', array());
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }
    $content .= $rc['content'];

    //
    // Put together all the contact content
    //
    $content .= "<div id='content'>\n"
        . "<article class='page'>\n"
        . "<header class='entry-title'><h1 class='entry-title'>Contact</h1></header>\n";
    if( isset($map_content) && $map_content != '' ) {
        $content .= $map_content;
    }
    $content .= "<div class='entry-content'>\n";
    if( isset($page_content) && $page_content != '' ) {
        $content .= $page_content;
    }
    if( $contact_content != '' ) {
//      $content .= "<p>" . $contact_content . "</p>";
        $content .= $contact_content;
    }

    //
    // Check if contact form should be displayed
    //
    if( ciniki_core_checkModuleFlags($ciniki, 'ciniki.web', 0x04) ) {
        if( isset($settings['page-contact-form-display']) 
            && $settings['page-contact-form-display'] == 'yes' 
            && $contact_form_submitted == 'no' 
            ) {
            $content .= "<br>";
            if( isset($settings['page-contact-form-intro-message']) 
                && $settings['page-contact-form-intro-message'] != '' ) {
                $content .= "<p>" . $settings['page-contact-form-intro-message'] . "</p>";
            }
            if( isset($contact_form_errors) && $contact_form_errors != '' ) {
                $content .= "<p>" . $contact_form_errors . "</p>";
            }
            $content .= "<form action='' method='post' id='contact-form'>";
            $content .= "<div class='input'>"
                . "<label for='contact-form-name'>Name</label>"
                . "<input type='text' class='text' value='' name='contact-form-name' id='contact-form-name'/>"
                . "</div>";
            $content .= "<div class='input hidden'>"
                . "<label for='contact-form-name-again'>Email Again</label>"
                . "<input type='email' class='text' value='' name='contact-form-email-again' id='contact-form-email-again'/>"
                . "</div>";
            $content .= "<div class='input'>"
                . "<label for='contact-form-email'>Email</label>"
                . "<input type='email' class='text' value='' name='contact-form-email' id='contact-form-email'/>"
                . "</div>";
            if( isset($settings['page-contact-form-phone']) && $settings['page-contact-form-phone'] == 'yes' ) {
                $content .= "<div class='input'>"
                    . "<label for='contact-form-phone'>Phone Number</label>"
                    . "<input type='text' class='text' value='' name='contact-form-phone' id='contact-form-phone'/>"
                    . "</div>";
            }
            $content .= "<div class='input'>"
                . "<label for='contact-form-subject'>Subject</label>"
                . "<input type='text' class='text' value='' name='contact-form-subject' id='contact-form-subject'/>"
                . "</div>";
            $content .= "<div class='textarea'>"
                . "<label for='contact-form-message'>Message</label>"
                . "<textarea name='contact-form-message' class='medium' id='contact-form-message'></textarea>"
                . "</div>";
            $content .= "<div class='submit'>"
                . "<input type='submit' value='Submit' name='submit' id='contact-form-submit' class='submit'>"
                . "</div>";
            $content .= "</form>";
        } elseif( $contact_form_submitted == 'yes' ) {
            $content .= "<p>" . $contact_form_success . "</p>";
/*          if( isset($settings['page-contact-form-submitted-message']) 
                && $settings['page-contact-form-submitted-message'] != '' ) {
                $content .= "<p>" . $settings['page-contact-form-submitted-message'] . "</p>";
            } else {
                $content .= "<p>Your message has been sent.</p>";
            } */
        }
    }

//  $content .= "<br style='clear: both;'/>";

    //
    // Check if mailchimp subscribe form should be displayed
    //
    if( isset($settings['page-contact-mailchimp-signup']) 
        && $settings['page-contact-mailchimp-signup'] == 'yes' 
        && isset($settings['page-contact-mailchimp-submit-url'])
        && $settings['page-contact-mailchimp-submit-url'] != ''
        ) {
        $content .= "<article class='page'>\n"
            . "<header class='entry-title'><h1 class='entry-title'>Subscribe to our e-newsletter</h1></header>\n";
        $content .= '<div id="mc_embed_signup" class="entry-content">'
            . '<form action="' . $settings['page-contact-mailchimp-submit-url'] . '" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>'
            . '<div class="indicates-required">'
            . '<span class="asterisk">*</span> indicates required</div>'
            . '<div class="input mc-field-group">'
                . '<label for="mce-EMAIL">Email Address  <span class="asterisk">*</span></label>'
                    . '<input type="email" value="" name="EMAIL" class="text required email" id="mce-EMAIL"></div>'
            . '<div class="input mc-field-group">'
                . '<label for="mce-FNAME">First Name </label>'
                . '<input type="text" value="" name="FNAME" class="text" id="mce-FNAME">'
            . '</div>'
            . '<div class="input mc-field-group">'
                . '<label for="mce-LNAME">Last Name </label>'
                . '<input type="text" value="" name="LNAME" class="text" id="mce-LNAME">'
            . '</div> '
            . '<div id="mce-responses" class="clear"> '
                . '<div class="response" id="mce-error-response" style="display:none"></div>'
                . '<div class="response" id="mce-success-response" style="display:none"></div>'
            . '</div>'
            . '<div style="position: absolute; left: -5000px;">'
                . '<input type="text" name="b_bc80f6925fec8d5f6c52e96f5_a3f3456bcd" tabindex="-1" value="">'
            . '</div>'
            . '<div class="submit">'
                . '<input type="submit" value="Subscribe" name="subscribe" id="mc-embedded-subscribe" class="submit">'
            . '</div>'
            . '</form>'
            . '</div>'
            . '</article>'
            . '';
        // Add the javascript to submit to mailchimp
        $content .= "<script type='text/javascript' src='//s3.amazonaws.com/downloads.mailchimp.com/js/mc-validate.js'></script>\n";
        $content .= '<script type=\'text/javascript\'>(function($) {window.fnames = new Array(); window.ftypes = new Array();fnames[0]=\'EMAIL\';ftypes[0]=\'email\';fnames[1]=\'FNAME\';ftypes[1]=\'text\';fnames[2]=\'LNAME\';ftypes[2]=\'text\';}(jQuery));var $mcj = jQuery.noConflict(true);</script>' . "\n";
    }

    //
    // Check if any public subscription lists which could be signed up for
    //
    if( isset($ciniki['tenant']['modules']['ciniki.subscriptions']) 
        && isset($settings['page-contact-subscriptions-signup']) && $settings['page-contact-subscriptions-signup'] == 'yes'
        ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'subscriptions', 'web', 'subscriptionManager');
        $rc = ciniki_subscriptions_web_subscriptionManager($ciniki, $settings, $ciniki['request']['tnid']);
        if( $rc['stat'] == 'ok' && isset($rc['blocks']) ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processBlocks');
            $rc = ciniki_web_processBlocks($ciniki, $settings, $ciniki['request']['tnid'], $rc['blocks']);
            if( $rc['stat'] == 'ok' && isset($rc['content']) ) {
                $content .= $rc['content'];
            }
        }
    }
    $content .= "<br style='clear: both;'/>";

    $content .= "</div>"
        . "</article>"
        . "";
    $content .= "</div>";

    //
    // Add the footer
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageFooter');
    $rc = ciniki_web_generatePageFooter($ciniki, $settings);
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }
    $content .= $rc['content'];

    return array('stat'=>'ok', 'content'=>$content);
}
?>
