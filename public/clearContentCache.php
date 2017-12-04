<?php
//
// Description
// -----------
// This method will clear the web cache for a tenant.  All files in the cache will
// get recreated the next time they are required.  This will slow down page loads,
// and should be done sparingly, if at all.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:     The ID of the tenant to clear the cache for.
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
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];
    
    //
    // Check access to tnid as owner
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'checkAccess');
    $ac = ciniki_web_checkAccess($ciniki, $args['tnid'], 'ciniki.web.clearContentCache');
    if( $ac['stat'] != 'ok' ) {
        return $ac;
    }

    //
    // Get the UUID for the tenant
    //
    $strsql = "SELECT uuid FROM ciniki_tenants "
        . "WHERE id = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.tenants', 'tenant');
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }
    if( !isset($rc['tenant']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.130', 'msg'=>'Tenant does not exist'));
    }
    $uuid = $rc['tenant']['uuid'];

    //
    // Remove the tenant cache directory
    //
    $tenant_cache_dir = $ciniki['config']['ciniki.core']['cache_dir'] 
        . '/' . $uuid[0] . '/' . $uuid . '/ciniki.web';
    if( file_exists($tenant_cache_dir) ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'recursiveRmdir');
        $rc = ciniki_core_recursiveRmdir($ciniki, $tenant_cache_dir);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
    }

    return array('stat'=>'ok');
}
?>
