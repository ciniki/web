<?php
//
// Description
// -----------
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:         The ID of the tenant.
// faq_id:              The ID of the faq to get.
//
// Returns
// -------
//
function ciniki_web_faqGet($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'faq_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'FAQ'),
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
    $rc = ciniki_web_checkAccess($ciniki, $args['tnid'], 'ciniki.web.faqGet'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
    $date_format = ciniki_users_dateFormat($ciniki);
    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'timeFormat');
    $time_format = ciniki_users_timeFormat($ciniki);

    //
    // Get the main information
    //
    $strsql = "SELECT ciniki_web_faqs.id, "
        . "ciniki_web_faqs.category, "
        . "ciniki_web_faqs.flags, "
        . "ciniki_web_faqs.question, "
        . "ciniki_web_faqs.answer "
        . "FROM ciniki_web_faqs "
        . "WHERE ciniki_web_faqs.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND ciniki_web_faqs.id = '" . ciniki_core_dbQuote($ciniki, $args['faq_id']) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
    $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.web', array(
        array('container'=>'faqs', 'fname'=>'id', 'name'=>'faq',
            'fields'=>array('id', 'category', 'flags', 'question', 'answer')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['faqs']) ) {
        return array('stat'=>'ok', 'err'=>array('code'=>'ciniki.web.133', 'msg'=>'Unable to find question'));
    }
    $faq = $rc['faqs'][0]['faq'];

    return array('stat'=>'ok', 'faq'=>$faq);
}
?>
