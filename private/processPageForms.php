<?php
//
// Description
// -----------
// Process any forms submitted by pages from ciniki_web_pages.
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_processPageForms(&$ciniki, $settings, $tnid) {

    $success_message = '';
    $error_message = '';

    if( isset($ciniki['tenant']['modules']['ciniki.web']['flags']) 
        && ($ciniki['tenant']['modules']['ciniki.web']['flags']&0x04) > 0 
        && isset($_POST['contact-form-name'])
        ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContactForm');
        return ciniki_web_processContactForm($ciniki, $settings, $tnid);
    }

    return array('stat'=>'ok', 'error_message'=>$error_message, 'success_message'=>$success_message);
}
?>
