<?php
//
// Description
// ===========
// This function will initialize a the website for a tenant who just activated the module.
// This function is used by the web signup process.
//
// Arguments
// =========
// ciniki:
// tnid:         The ID of the tenant the request is for.
// 
// Returns
// =======
//
function ciniki_web_moduleInitialize($ciniki, $tnid) {

    //
    // Get the list of modules activated for this tenant
    //
    $strsql = "SELECT ruleset, CONCAT_WS('.', ciniki_tenant_modules.package, ciniki_tenant_modules.module) AS module_id "
        . "FROM ciniki_tenant_modules "
        . "WHERE ciniki_tenant_modules.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND ciniki_tenant_modules.status = 1 "                                                     // Tenant is active
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashIDQuery');
    $rc = ciniki_core_dbHashIDQuery($ciniki, $strsql, 'ciniki.tenants', 'modules', 'module_id');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['modules']) || !isset($rc['modules']['ciniki.web']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.112', 'msg'=>'Access denied.'));
    }
    $modules = $rc['modules'];

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbInsert');

    //
    // Active the home page
    //
    $strsql = "INSERT INTO ciniki_web_settings (tnid, detail_key, detail_value, date_added, last_updated) VALUES ("
        . "'" . ciniki_core_dbQuote($ciniki, $tnid) . "', "
        . "'page-home-active', 'yes', UTC_TIMESTAMP(), UTC_TIMESTAMP())";
    ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.web');

    //
    // Active about page
    //
    $strsql = "INSERT INTO ciniki_web_settings (tnid, detail_key, detail_value, date_added, last_updated) VALUES ("
        . "'" . ciniki_core_dbQuote($ciniki, $tnid) . "', "
        . "'page-about-active', 'yes', UTC_TIMESTAMP(), UTC_TIMESTAMP())";
    ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.web');
    $strsql = "INSERT INTO ciniki_web_content (tnid, detail_key, detail_value, date_added, last_updated) VALUES ("
        . "'" . ciniki_core_dbQuote($ciniki, $tnid) . "', "
        . "'page-about-content', 'Sample about page', UTC_TIMESTAMP(), UTC_TIMESTAMP())";
    ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.web');

    //
    // Active contact page 
    //
    $strsql = "INSERT INTO ciniki_web_settings (tnid, detail_key, detail_value, date_added, last_updated) VALUES ("
        . "'" . ciniki_core_dbQuote($ciniki, $tnid) . "', "
        . "'page-contact-active', 'yes', UTC_TIMESTAMP(), UTC_TIMESTAMP())";
    ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.web');
    $strsql = "INSERT INTO ciniki_web_settings (tnid, detail_key, detail_value, date_added, last_updated) VALUES ("
        . "'" . ciniki_core_dbQuote($ciniki, $tnid) . "', "
        . "'page-contact-name-display', 'yes', UTC_TIMESTAMP(), UTC_TIMESTAMP())";
    ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.web');
    $strsql = "INSERT INTO ciniki_web_settings (tnid, detail_key, detail_value, date_added, last_updated) VALUES ("
        . "'" . ciniki_core_dbQuote($ciniki, $tnid) . "', "
        . "'page-contact-email-display', 'yes', UTC_TIMESTAMP(), UTC_TIMESTAMP())";
    ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.web');

    //
    // Active artcatalog gallery
    //
    if( isset($modules['ciniki.artcatalog']) ) {
        $strsql = "INSERT INTO ciniki_web_settings (tnid, detail_key, detail_value, date_added, last_updated) VALUES ("
            . "'" . ciniki_core_dbQuote($ciniki, $tnid) . "', "
            . "'page-gallery-active', 'yes', UTC_TIMESTAMP(), UTC_TIMESTAMP())";
        ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.web');
        //
        // Set the theme to blue on black by default if artcatalog specified
        //
        $strsql = "INSERT INTO ciniki_web_settings (tnid, detail_key, detail_value, date_added, last_updated) VALUES ("
            . "'" . ciniki_core_dbQuote($ciniki, $tnid) . "', "
            . "'site-theme', 'black', UTC_TIMESTAMP(), UTC_TIMESTAMP())";
        ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.web');
    }

    //
    // Active events
    //
    if( isset($modules['ciniki.events']) ) {
        $strsql = "INSERT INTO ciniki_web_settings (tnid, detail_key, detail_value, date_added, last_updated) VALUES ("
            . "'" . ciniki_core_dbQuote($ciniki, $tnid) . "', "
            . "'page-events-active', 'yes', UTC_TIMESTAMP(), UTC_TIMESTAMP())";
        ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.web');
        $strsql = "INSERT INTO ciniki_web_settings (tnid, detail_key, detail_value, date_added, last_updated) VALUES ("
            . "'" . ciniki_core_dbQuote($ciniki, $tnid) . "', "
            . "'page-events-past', 'yes', UTC_TIMESTAMP(), UTC_TIMESTAMP())";
        ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.web');
    }

    //
    // Active links
    //
    if( isset($modules['ciniki.links']) ) {
        $strsql = "INSERT INTO ciniki_web_settings (tnid, detail_key, detail_value, date_added, last_updated) VALUES ("
            . "'" . ciniki_core_dbQuote($ciniki, $tnid) . "', "
            . "'page-links-active', 'yes', UTC_TIMESTAMP(), UTC_TIMESTAMP())";
        ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.web');
    }

    //
    // Update the last_change date in the tenant modules
    // Ignore the result, as we don't want to stop user updates if this fails.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'updateModuleChangeDate');
    ciniki_tenants_updateModuleChangeDate($ciniki, $tnid, 'ciniki', 'web');

    return array('stat'=>'ok');
}
?>
