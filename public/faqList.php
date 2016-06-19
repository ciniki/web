<?php
//
// Description
// -----------
// This method will return the list of website faqs for a business.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:     The ID of the business to get faq list for.
//
// Returns
// -------
//
function ciniki_web_faqList($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //  
    // Check access to business_id as owner, or sys admin. 
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'checkAccess');
    $ac = ciniki_web_checkAccess($ciniki, $args['business_id'], 'ciniki.web.faqList');
    if( $ac['stat'] != 'ok' ) { 
        return $ac;
    }   

//  ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
//  $date_format = ciniki_users_dateFormat($ciniki);

    //
    // Query for the faqs
    //
    $strsql = "SELECT id, category, flags, question "
        . "FROM ciniki_web_faqs "
        . "WHERE ciniki_web_faqs.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "ORDER BY ciniki_web_faqs.category "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
    $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.web', array(
        array('container'=>'categories', 'fname'=>'category', 'name'=>'category',
            'fields'=>array('name'=>'category')),
        array('container'=>'faqs', 'fname'=>'id', 'name'=>'faq',
            'fields'=>array('id', 'category', 'flags', 'question')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    return $rc;
}
?>
