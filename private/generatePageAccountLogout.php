<?php
//
// Description
// -----------
// This function will destroy the session and log the customer out.
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_generatePageAccountLogout(&$ciniki, $settings) {

    //
    // Clear all the session information
    //
    $ciniki['session']['customer'] = array();
    $ciniki['session']['cart'] = array();
    $ciniki['session']['user'] = array();
    $ciniki['session']['change_log_id'] = '';
    unset($_SESSION['customer']);
    unset($_SESSION['cart']);

    //
    // Redirect them back to the home page
    //
    header('Location: ' . ($ciniki['request']['ssl_domain_base_url']!=''?$ciniki['request']['ssl_domain_base_url']:'/'));

    //
    // Script is done.
    //
    return array('stat'=>'ok');
}
?>
