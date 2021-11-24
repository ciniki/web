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
function ciniki_web_processContactForm(&$ciniki, $settings, $tnid, $args=array()) {

    $success_message = '';
    $error_message = '';

    if( !isset($ciniki['tenant']['modules']['ciniki.web']['flags'])
        || ($ciniki['tenant']['modules']['ciniki.web']['flags']&0x04) == 0 ) {
        $error_message = "This feature is not enabled.";
        return array('stat'=>'ok', 'error_message'=>$error_message, 'success_message'=>'');
    }

    if( !isset($_POST['contact-form-name']) || $_POST['contact-form-name'] == '' ) {
        $error_message = "You must enter your name.<br/>";
        return array('stat'=>'ok', 'error_message'=>$error_message, 'success_message'=>'');
    }
    if( !isset($_POST['contact-form-email']) || $_POST['contact-form-email'] == '' ) {
        $error_message = "You must enter your email address to get a response.<br/>";
        return array('stat'=>'ok', 'error_message'=>$error_message, 'success_message'=>'');
    }
    if( !preg_match('/^[^ ]+\@[^ ]+\.[^ ]+$/', trim($_POST['contact-form-email'])) ) {
        $error_message = "You must enter a valid email address to get a response.<br/>";
        return array('stat'=>'ok', 'error_message'=>$error_message, 'success_message'=>'');
    }
    if( !isset($_POST['contact-form-subject']) || $_POST['contact-form-subject'] == '' ) {
        $error_message = "Please add a subject.<br/>";
        return array('stat'=>'ok', 'error_message'=>$error_message, 'success_message'=>'');
    } else {
        $subject = $_POST['contact-form-subject'];
    }
    if( !isset($_POST['contact-form-message']) || trim($_POST['contact-form-message']) == '' ) {
        $error_message = "Please enter a message.<br/>";
        return array('stat'=>'ok', 'error_message'=>$error_message, 'success_message'=>'');
    } else {
        $msg = $_POST['contact-form-message'];
    }

    //
    // Check for hidden email filled out and ignore
    //
    if( isset($_POST['contact-form-email-again']) && $_POST['contact-form-email-again'] != '' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'logFileMsg');
        ciniki_core_logFileMsg($ciniki, $tnid, 'spam', 
            'BLOCKED FROM ' . $_POST['contact-form-email'] . ' - ' 
                . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'NO REFERER'));
        return array('stat'=>'ok', 'error_message'=>$error_message, 'success_message'=>"Your message was sent");
    }
    if( !isset($_SERVER['HTTP_REFERER']) ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'logFileMsg');
        ciniki_core_logFileMsg($ciniki, $tnid, 'spam', 
            'BLOCKED FROM ' . $_POST['contact-form-email'] . ' - NO REFERER');
        return array('stat'=>'ok', 'error_message'=>$error_message, 'success_message'=>"Your message was sent");
    }
    if( preg_match("/^[0-9]+$/", trim($subject)) ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'logFileMsg');
        ciniki_core_logFileMsg($ciniki, $tnid, 'spam', 
            'BLOCKED FROM ' . $_POST['contact-form-email'] . ' - NUMERIC SUBJECT');
        return array('stat'=>'ok', 'error_message'=>$error_message, 'success_message'=>"Your message was sent");
    }
    if( preg_match("/^[0-9]+$/", trim($msg)) ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'logFileMsg');
        ciniki_core_logFileMsg($ciniki, $tnid, 'spam', 
            'BLOCKED FROM ' . $_POST['contact-form-email'] . ' - NUMERIC MESSAGE');
        return array('stat'=>'ok', 'error_message'=>$error_message, 'success_message'=>"Your message was sent");
    }
/*    if( preg_match("/domainregistercorp.com/", $_POST['contact-form-email']) ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'logFileMsg');
        ciniki_core_logFileMsg($ciniki, $tnid, 'spam', 
            'BLOCKED FROM ' . $_POST['contact-form-email'] . ' - domainworld.com');
        return array('stat'=>'ok', 'error_message'=>$error_message, 'success_message'=>"Your message was sent");
    } */
    if( preg_match("/domainreg[a-z]*.com/", $_POST['contact-form-email']) ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'logFileMsg');
        ciniki_core_logFileMsg($ciniki, $tnid, 'spam', 
            'BLOCKED FROM ' . $_POST['contact-form-email'] . ' - domainworld.com');
        return array('stat'=>'ok', 'error_message'=>$error_message, 'success_message'=>"Your message was sent");
    }
    if( preg_match("/EXPIR.*DOMAIN/", $subject) ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'logFileMsg');
        ciniki_core_logFileMsg($ciniki, $tnid, 'spam', 
            'BLOCKED SUBJECT ' . $subject . ' - ' . $_POST['contact-form-email']);
        return array('stat'=>'ok', 'error_message'=>$error_message, 'success_message'=>"Your message was sent");
    }
    if( preg_match("/domainworld.com/", $_POST['contact-form-email']) ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'logFileMsg');
        ciniki_core_logFileMsg($ciniki, $tnid, 'spam', 
            'BLOCKED FROM ' . $_POST['contact-form-email'] . ' - domainworld.com');
        return array('stat'=>'ok', 'error_message'=>$error_message, 'success_message'=>"Your message was sent");
    }
    if( preg_match("/firebasestorage.googleapis.com.*DMCA/i", $msg) ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'logFileMsg');
        ciniki_core_logFileMsg($ciniki, $tnid, 'spam', 
            'BLOCKED FROM ' . $_POST['contact-form-email'] . ' - DMCA');
        return array('stat'=>'ok', 'error_message'=>$error_message, 'success_message'=>"Your message was sent");
    }
    if( preg_match("/googleapis.com.*appspot/i", $msg) ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'logFileMsg');
        ciniki_core_logFileMsg($ciniki, $tnid, 'spam', 
            'BLOCKED FROM ' . $_POST['contact-form-email'] . ' - Critical errors');
        return array('stat'=>'ok', 'error_message'=>$error_message, 'success_message'=>"Your message was sent");
    }
    if( preg_match("/(SpellPerfect|SpellPros).com/i", $msg) ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'logFileMsg');
        ciniki_core_logFileMsg($ciniki, $tnid, 'spam', 
            'BLOCKED FROM ' . $_POST['contact-form-email'] . ' - spellperfect.com');
        return array('stat'=>'ok', 'error_message'=>$error_message, 'success_message'=>"Your message was sent");
    }
    if( preg_match("/(DeadLinkCheck|URLInspect|DeadLinkDetector).com/i", $msg) ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'logFileMsg');
        ciniki_core_logFileMsg($ciniki, $tnid, 'spam', 
            'BLOCKED FROM ' . $_POST['contact-form-email'] . ' - deadlinkcheck.com');
        return array('stat'=>'ok', 'error_message'=>$error_message, 'success_message'=>"Your message was sent");
    }
    if( preg_match("/SpellAce.com/i", $msg) ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'logFileMsg');
        ciniki_core_logFileMsg($ciniki, $tnid, 'spam', 
            'BLOCKED FROM ' . $_POST['contact-form-email'] . ' - spellace.com');
        return array('stat'=>'ok', 'error_message'=>$error_message, 'success_message'=>"Your message was sent");
    }
    if( preg_match("/HelloSpell.com/i", $msg) ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'logFileMsg');
        ciniki_core_logFileMsg($ciniki, $tnid, 'spam', 
            'BLOCKED FROM ' . $_POST['contact-form-email'] . ' - hellospell.com');
        return array('stat'=>'ok', 'error_message'=>$error_message, 'success_message'=>"Your message was sent");
    }
/*        if( isset($ciniki['config']['ciniki.core']['log_dir']) && $ciniki['config']['ciniki.core']['log_dir'] != '' ) {
//            file_put_contents($ciniki['config']['ciniki.core']['log_dir'] . '/spam.log', 
//                'WEB: SPAM BLOCKED FROM ' . $_POST['contact-form-email'] . ' - ' . $_SERVER['HTTP_REFERER'] . "\n",
//                FILE_APPEND);
        } else {
            error_log('WEB: SPAM BLOCKED FROM ' . $_POST['contact-form-email'] . ' - ' . $_SERVER['HTTP_REFERER']);
        }
        return array('stat'=>'ok', 'error_message'=>$error_message, 'success_message'=>"Your message was sent");
    } */
    //
    // Log the details of the submitter
    //
    if( isset($settings['page-contact-debug-log']) && $settings['page-contact-debug-log'] == 'yes' ) {
        error_log('WEB: Contact form from ' . $_POST['contact-form-email'] . ' - ' . $_SERVER['HTTP_REFERER']);
    }

    //
    // No error, send the message
    //
    $phone = '';
    if( isset($settings['page-contact-form-phone']) && $settings['page-contact-form-phone'] == 'yes' 
        && isset($_POST['contact-form-phone']) && $_POST['contact-form-phone'] != ''
        ) {
        $phone .= $_POST['contact-form-phone'];
    }
    $dateofstay = '';
    if( isset($_POST['contact-form-dateofstay']) && $_POST['contact-form-dateofstay'] != '') {
        $dateofstay .= $_POST['contact-form-dateofstay'];
    }
    if( $error_message == '' ) {
        //
        // If the mail inbox flag has been sent, put the message into the inbox
        //
        if( isset($ciniki['tenant']['modules']['ciniki.mail']['flags']) && ($ciniki['tenant']['modules']['ciniki.mail']['flags']&0x10) > 0 ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'mail', 'hooks', 'inboxAddMessage');
            $rc = ciniki_mail_hooks_inboxAddMessage($ciniki, $tnid, array(
                'from_name'=>$_POST['contact-form-name'],
                'from_email'=>$_POST['contact-form-email'],
                'subject'=>$subject,
                'text_content'=>$msg . ($phone!=''?"\n\n" . $phone:''),
                'notification'=>'yes',
                'notification_emails'=>(isset($settings['page-contact-form-emails'])?$settings['page-contact-form-emails']:''),
                ));
            if( $rc['stat'] != 'ok' ) {
                $error_message = "I'm sorry, we had a problem delivering your message. Please try again, or contact us by phone.";
                error_log('WEB [' . $ciniki['tenant']['details']['name'] . ']: Error with form submit (2606)');
            }
        } 
        
        //
        // No inbox, email the message to specified email addresses or the tenant owners
        //
        else {
            $msg = "New message from " . $_POST['contact-form-name'] . " (" . $_POST['contact-form-email'] . ")"
                . ($phone!=''?" - " . $phone:'')
                . ($dateofstay != '' ? "\n\nDate of Stay: " . $dateofstay . "":'')
                . "\n\n"
                . "Message: \n\n"
                . $msg
                . "";
            $htmlmsg = "New message from " . $_POST['contact-form-name'] . " (" . $_POST['contact-form-email'] . ")"
                . ($phone!=''?" - " . $phone:'')
                . ($dateofstay != '' ? "<br/><br/>Date of Stay: " . $dateofstay . "":'')
                . "<br/><br/>"
                . preg_replace("/\n/", '<br/>', $_POST['contact-form-message'])
                . "";
            if( isset($settings['page-contact-form-emails']) && $settings['page-contact-form-emails'] != '' ) {
                $send_to_emails = explode(',', $settings['page-contact-form-emails']);
                foreach($send_to_emails as $email) {
                    $ciniki['emailqueue'][] = array('to'=>trim($email),
                        'tnid'=>$tnid,
                        'replyto_email'=>$_POST['contact-form-email'],
                        'replyto_name'=>$_POST['contact-form-name'],
                        'subject'=>$subject,
                        'textmsg'=>$msg,
                        'htmlmsg'=>$htmlmsg,
                        );
                }
                //
                // Check if send copy to submitter
                //
                if( isset($send_to_emails[0]) 
                    && isset($settings['page-contact-form-copy-submitter']) 
                    && $settings['page-contact-form-copy-submitter'] == 'yes' 
                    ) {
                    $msg = "We have sent your request, here is a copy."
                        . "\n\n"
                        . "From: " . $_POST['contact-form-email'] . "\n"
                        . ($phone != '' ? "Phone: " . $phone . "\n" : '')
                        . ($dateofstay != '' ? "Date of Stay: " . $dateofstay . "\n" : '')
                        . "\n"
                        . "Message: \n\n"
                        . $msg
                        . "";
                    $htmlmsg = "We have send your request, here is a copy."
                        . "<br/><br/>"
                        . "From: " . $_POST['contact-form-email'] . "<br/>"
                        . ($phone != '' ? "Phone: " . $phone . "<br/>" : '')
                        . ($dateofstay != '' ? "Date of Stay: " . $dateofstay . "<br/>" : '')
                        . "<br/>"
                        . preg_replace("/\n/", '<br/>', $_POST['contact-form-message'])
                        . "";
                    $ciniki['emailqueue'][] = array(
                        'to'=>trim($_POST['contact-form-email']),
                        'tnid'=>$tnid,
                        'replyto_email'=>$send_to_emails[0],
//                        'replyto_name'=>$_POST['contact-form-name'],
                        'subject'=>$subject,
                        'textmsg'=>$msg,
                        'htmlmsg'=>$htmlmsg,
                        );
                }
            } else {
                ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'hooks', 'tenantOwners');
                $rc = ciniki_tenants_hooks_tenantOwners($ciniki, $tnid, array());
                if( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.120', 'msg'=>'Unable to get tenant owners', 'err'=>$rc['err']));
                }
                $owners = $rc['users'];
                foreach($owners as $user_id => $owner) {
                    $ciniki['emailqueue'][] = array('user_id'=>$user_id,
                        'tnid'=>$tnid,
                        'replyto_email'=>$_POST['contact-form-email'],
                        'replyto_name'=>$_POST['contact-form-name'],
                        'subject'=>$subject,
                        'textmsg'=>$msg,
                        'htmlmsg'=>$htmlmsg,
                        );
                }
            }
        }
    }

    if( $error_message == '' ) {
        //
        // Success message
        //
        if( isset($settings['page-contact-form-submitted-message']) && $settings['page-contact-form-submitted-message'] != '' ) {
            $success_message .= $settings['page-contact-form-submitted-message'];
        } else {
            $success_message .= "Your message has been sent.";
        }
    }

    return array('stat'=>'ok', 'error_message'=>$error_message, 'success_message'=>$success_message);
}
?>
