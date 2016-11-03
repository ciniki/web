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
function ciniki_web_processContactForm(&$ciniki, $settings, $business_id) {

    $success_message = '';
    $error_message = '';

    if( !isset($ciniki['business']['modules']['ciniki.web']['flags'])
        || ($ciniki['business']['modules']['ciniki.web']['flags']&0x04) == 0 ) {
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
    if( !isset($_POST['contact-form-subject']) || $_POST['contact-form-subject'] == '' ) {
        $error_message = "Please add a subject.<br/>";
        return array('stat'=>'ok', 'error_message'=>$error_message, 'success_message'=>'');
    } else {
        $subject = $_POST['contact-form-subject'];
    }
    if( !isset($_POST['contact-form-message']) || $_POST['contact-form-message'] == '' ) {
        $msg = 'No message added';
    } else {
        $msg = $_POST['contact-form-message'];
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
    if( $error_message == '' ) {
        //
        // If the mail inbox flag has been sent, put the message into the inbox
        //
        if( isset($ciniki['business']['modules']['ciniki.mail']['flags']) && ($ciniki['business']['modules']['ciniki.mail']['flags']&0x10) > 0 ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'mail', 'hooks', 'inboxAddMessage');
            $rc = ciniki_mail_hooks_inboxAddMessage($ciniki, $business_id, array(
                'from_name'=>$_POST['contact-form-name'],
                'from_email'=>$_POST['contact-form-email'],
                'subject'=>$subject,
                'text_content'=>$msg . ($phone!=''?"\n\n" . $phone:''),
                'notification'=>'yes',
                'notification_emails'=>(isset($settings['page-contact-form-emails'])?$settings['page-contact-form-emails']:''),
                ));
            if( $rc['stat'] != 'ok' ) {
                $error_message = "I'm sorry, we had a problem delivering your message. Please try again, or contact us by phone.";
                error_log('WEB [' . $ciniki['business']['details']['name'] . ']: Error with form submit (2606)');
            }
        } 
        
        //
        // No inbox, email the message to specified email addresses or the business owners
        //
        else {
            $msg = "New message from " . $_POST['contact-form-name'] . " (" . $_POST['contact-form-email'] . ")"
                . ($phone!=''?" - " . $phone:'')
                . "\n\n"
                . "Message: \n\n"
                . $msg
                . "";
            if( isset($settings['page-contact-form-emails']) && $settings['page-contact-form-emails'] != '' ) {
                $send_to_emails = explode(',', $settings['page-contact-form-emails']);
                foreach($send_to_emails as $email) {
                    $ciniki['emailqueue'][] = array('to'=>trim($email),
                        'replyto_email'=>$_POST['contact-form-email'],
                        'replyto_name'=>$_POST['contact-form-name'],
                        'subject'=>$subject,
                        'textmsg'=>$msg,
                        );
                }
            } else {
                ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'hooks', 'businessOwners');
                $rc = ciniki_businesses_hooks_businessOwners($ciniki, $business_id, array());
                if( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.120', 'msg'=>'Unable to get business owners', 'err'=>$rc['err']));
                }
                $owners = $rc['users'];
                foreach($owners as $user_id => $owner) {
                    $ciniki['emailqueue'][] = array('user_id'=>$user_id,
                        'replyto_email'=>$_POST['contact-form-email'],
                        'replyto_name'=>$_POST['contact-form-name'],
                        'subject'=>$subject,
                        'textmsg'=>$msg,
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
