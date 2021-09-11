<?php
//
// Description
// -----------
// This function will generate an error page when there was a problem processing the request.  It should
// appear in the customers website with header/footer.
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_generatePage500(&$ciniki, $settings, $errors) {

    //
    // Store the content created by the page
    // Make sure everything gets generated ok before returning the content
    //
    $content = '';
    $page_content = '';

    //
    // FIXME: Check if anything has changed, and if not load from cache
    //
    

    //
    // Add the header
    //
//  header("HTTP/1.0 404 Not Found");
    header("Status: 500 Internal Server Error", true, 500);
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageHeader');
    $rc = ciniki_web_generatePageHeader($ciniki, $settings, 'Internal Server Error', array());
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }
    $content .= $rc['content'];

    $content .= "<div id='content'>\n";
    $content .= "<article class='page'>\n";
    $content .= "<div class='entry-content'>\n";
    $content .= "<header class='entry-title'><h1 class='entry-title'>We seem to have hit a snag</h1></header>";
    $content .= "<div class='block-content'>";
    $content .= "<p>I'm sorry, but we seem to be having trouble processing your request.  You can continue browsing the site while we fix the problem.</p>";
    $content .= "</div>";
    $content .= "</div>";
    $content .= "</article>";
    $content .= "</div>";

    $err_msg = "Web ERR [500]: " . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . ' [' . $_SERVER['HTTP_USER_AGENT'] . '] ';

    if( isset($errors['err']['code']) ) {
        $ciniki['request']['error_codes_msg'] = 'err:' . $errors['err']['code'];
        $err_msg .= '[' . $errors['err']['code'] . ':' . $errors['err']['msg'] . ']';
    } else {
        $ciniki['request']['error_codes_msg'] = "I'm sorry, we seem to have run into a spot of trouble.";
        $err_msg .= "I'm sorry, we seem to have run into a spot of trouble.";
    }
    // Check for nested errors
    if( isset($errors['err']['err']) ) {
        $err = $errors['err'];
        while( isset($err['err']) ) {
            $ciniki['request']['error_codes_msg'] .= ',' . $err['err']['code'];
            $err_msg .= '[' . $err['err']['code'] . ':' . $err['err']['msg'] . ']';
            $err = $err['err'];
        }
    }

    error_log($err_msg);

    //
    // Email sysadmins there was a problem with a web request
    //
    if( !isset($ciniki['config']['ciniki.web']['email.500.errors']) || $ciniki['config']['ciniki.web']['email.500.errors'] == 'yes' ) {
        $ciniki['emailqueue'][] = array('to'=>$ciniki['config']['ciniki.core']['alerts.notify'],
            'subject'=>'Web ERR 500',
            'textmsg'=>$_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "\n"
                . print_r($errors, true),
            );
    }

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
