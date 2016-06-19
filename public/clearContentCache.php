<?php
//
// Description
// -----------
// This method will clear the web cache for a business.  All files in the cache will
// get recreated the next time they are required.  This will slow down page loads,
// and should be done sparingly, if at all.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:     The ID of the business to clear the cache for.
//
// Returns
// -------
// <rsp stat="ok" />
//
function ciniki_web_clearContentCache($ciniki) {
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
    // Check access to business_id as owner
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'checkAccess');
    $ac = ciniki_web_checkAccess($ciniki, $args['business_id'], 'ciniki.web.clearContentCache');
    if( $ac['stat'] != 'ok' ) {
        return $ac;
    }

    //
    // Get the UUID for the business
    //
    $strsql = "SELECT uuid FROM ciniki_businesses "
        . "WHERE id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.businesses', 'business');
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }
    if( !isset($rc['business']) ) {
        return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1603', 'msg'=>'Business does not exist'));
    }
    $uuid = $rc['business']['uuid'];

    //
    // Remove the business cache directory
    //
    $business_cache_dir = $ciniki['config']['ciniki.core']['cache_dir'] 
        . '/' . $uuid[0] . '/' . $uuid . '/ciniki.web';
    if( file_exists($business_cache_dir) ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'recursiveRmdir');
        $rc = ciniki_core_recursiveRmdir($ciniki, $business_cache_dir);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
    }

    return array('stat'=>'ok');
}
?>
