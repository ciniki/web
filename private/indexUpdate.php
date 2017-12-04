<?php
//
// Description
// -----------
// This function will setup the javascript for image resize and positioning in gallery view.
//
// Arguments
// ---------
// ciniki:
//
// Returns
// -------
//
function ciniki_web_indexUpdate(&$ciniki, $tnid) {

    //
    // Load INTL settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'intlSettings');
    $rc = ciniki_tenants_intlSettings($ciniki, $tnid);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the list of modules currently in the index
    //
    $strsql = "SELECT DISTINCT(object) "
        . "FROM ciniki_web_index "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.web', 'object');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $index_modules = array();
    if( isset($rc['rows']) ) {
        //
        // Build a list of dot notated modules
        //
        foreach($rc['rows'] as $row) {
            $index_modules[] = preg_replace("/^([^\.]+\.[^\.]+)\..*$/", "$1", $row['object']);
        }
    }
    $index_modules = array_unique($index_modules);

    //
    // Get the list of modules enabled for the tenant
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'hooks', 'getActiveModules');
    $rc = ciniki_tenants_hooks_getActiveModules($ciniki, $tnid, array());
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $tenant_modules = array_keys($rc['modules']);

    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'indexDeleteModule');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'indexUpdateModule');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'indexUpdateObject');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'indexUpdateObjectImage');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'indexModuleBaseURL');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'indexObjectBaseURL');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectDelete');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'checkModuleFlags');

    //
    // Remove any objects where the module is no longer in the tenant
    //
    foreach($index_modules as $module) {
        if( !in_array($module, $tenant_modules) ) {
            $rc = ciniki_web_indexDeleteModule($ciniki, $tnid, $module);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
        }
    }

    //
    // Update the modules indexes
    //
    foreach($tenant_modules as $module) {
        $rc = ciniki_web_indexUpdateModule($ciniki, $tnid, $module);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
    }
    
    return array('stat'=>'ok');
}
?>
