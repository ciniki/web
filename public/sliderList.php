<?php
//
// Description
// -----------
// This method will return the list of website sliders for a business.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:     The ID of the business to get slider list for.
//
// Returns
// -------
//
function ciniki_web_sliderList($ciniki) {
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
    $ac = ciniki_web_checkAccess($ciniki, $args['business_id'], 'ciniki.web.sliderList');
    if( $ac['stat'] != 'ok' ) { 
        return $ac;
    }   

    //
    // Query for the sliders
    //
    $strsql = "SELECT id, name, size, effect "
        . "FROM ciniki_web_sliders "
        . "WHERE ciniki_web_sliders.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "ORDER BY ciniki_web_sliders.name "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
    $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.web', array(
        array('container'=>'sliders', 'fname'=>'id', 'name'=>'slider',
            'fields'=>array('id', 'name', 'size', 'effect')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    return $rc;
}
?>
