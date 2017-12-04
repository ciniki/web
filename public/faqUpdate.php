<?php
//
// Description
// -----------
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:         The ID of the tenant to add the faq to.
//
// Returns
// -------
// <rsp stat='ok' />
//
function ciniki_web_faqUpdate(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'faq_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'FAQ'), 
        'category'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Category'), 
        'flags'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Flags'), 
        'question'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Question'),
        'answer'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Answer'),
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];

    //  
    // Make sure this module is activated, and
    // check permission to run this function for this tenant
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'checkAccess');
    $rc = ciniki_web_checkAccess($ciniki, $args['tnid'], 'ciniki.web.faqUpdate'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }

    //
    // Update the faq
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
    return ciniki_core_objectUpdate($ciniki, $args['tnid'], 'ciniki.web.faq', $args['faq_id'], $args, 0x07);
}
?>
