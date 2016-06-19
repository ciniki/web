<?php
//
// Description
// -----------
// This method will return the list of website private theme for a business.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:     The ID of the business to get private theme list for.
//
// Returns
// -------
//
function ciniki_web_privateThemeList($ciniki) {
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
    $ac = ciniki_web_checkAccess($ciniki, $args['business_id'], 'ciniki.web.privateThemeList');
    if( $ac['stat'] != 'ok' ) { 
        return $ac;
    }   

    //
    // Load event maps
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'maps');
    $rc = ciniki_web_maps($ciniki);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $maps = $rc['maps'];

    //
    // Query for the private theme
    //
    $strsql = "SELECT id, name, status, status AS status_text "
        . "FROM ciniki_web_themes "
        . "WHERE ciniki_web_themes.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "ORDER BY ciniki_web_themes.name "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
    $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.web', array(
        array('container'=>'themes', 'fname'=>'id', 'name'=>'theme',
            'fields'=>array('id', 'name', 'status', 'status_text'),
            'maps'=>array('status_text'=>$maps['theme']['status']),
            ),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    return $rc;
}
?>
