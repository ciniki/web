<?php
//
// Description
// -----------
// Process any forms submitted by pages from ciniki_web_pages.
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_processPageForms(&$ciniki, $settings, $business_id) {

	$success_message = '';
	$error_message = '';

	error_log('processing');
	error_log(print_r($_POST, true));
	if( isset($ciniki['business']['modules']['ciniki.web']['flags']) 
		&& ($ciniki['business']['modules']['ciniki.web']['flags']&0x04) > 0 
		&& isset($_POST['contact-form-name'])
		) {
		error_log('proc');
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContactForm');
		return ciniki_web_processContactForm($ciniki, $settings, $business_id);
	}

	return array('stat'=>'ok', 'error_message'=>$error_message, 'success_message'=>$success_message);
}
?>
