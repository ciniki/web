<?php
//
// Description
// ===========
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:         The ID of the tenant to add the faq to.
//
// category:            (optional) The category of the faq.
// flags:               (optional)
//
//                      0x01 - Hidden, unavailable on the website
//
// question:            The question being asked.
// answer:              The answer to the question.
// 
// Returns
// -------
// <rsp stat='ok' id='34' />
//
function ciniki_web_faqAdd(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'category'=>array('required'=>'yes', 'blank'=>'yes', 'name'=>'Category'), 
        'flags'=>array('required'=>'no', 'default'=>'0', 'blank'=>'no', 'name'=>'Flags'), 
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
    $rc = ciniki_web_checkAccess($ciniki, $args['tnid'], 'ciniki.web.faqAdd'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $modules = $rc['modules'];

    //
    // Add the faq
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
    return ciniki_core_objectAdd($ciniki, $args['tnid'], 'ciniki.web.faq', $args, 0x07);
}
?>
