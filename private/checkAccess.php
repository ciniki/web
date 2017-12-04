<?php
//
// Description
// ===========
// This function will check the user has access to the web module public methods, and 
// return a list of other modules enabled for the tenant.
//
// Arguments
// =========
// ciniki:
// tnid:         The ID of the tenant the request is for.
// method:              The method being requested.
// 
// Returns
// =======
//
function ciniki_web_checkAccess(&$ciniki, $tnid, $method) {
    //
    // Check if the tenant is active and the module is enabled
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'checkModuleAccess');
    $rc = ciniki_tenants_checkModuleAccess($ciniki, $tnid, 'ciniki', 'web');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    if( !isset($rc['ruleset']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.8', 'msg'=>'No permissions granted'));
    }
    $modules = $rc['modules'];

    // Sysadmins are allowed full permissions
    if( ($ciniki['session']['user']['perms'] & 0x01) == 0x01 ) {
        return array('stat'=>'ok', 'modules'=>$modules);
    }

    //
    // Not all methods are available to tenant owners
    //

    //
    // Users who are an owner or employee of a tenant can see the tenant 
    // FIXME: Add proper methods here
    if( $method == 'ciniki.web.siteSettings' 
        || $method == 'ciniki.web.pageSettingsGet'
        || $method == 'ciniki.web.pageSettingsHistory'
        || $method == 'ciniki.web.tenantUsers'
        || $method == 'ciniki.web.siteSettingsGet'
        || $method == 'ciniki.web.siteSettingsUpdate'
        || $method == 'ciniki.web.pageAdd'
        || $method == 'ciniki.web.pageDelete'
        || $method == 'ciniki.web.pageFileAdd'
        || $method == 'ciniki.web.pageFileDelete'
        || $method == 'ciniki.web.pageFileDownload'
        || $method == 'ciniki.web.pageFileGet'
        || $method == 'ciniki.web.pageFileHistory'
        || $method == 'ciniki.web.pageFileUpdate'
        || $method == 'ciniki.web.pageGet'
        || $method == 'ciniki.web.pageHistory'
        || $method == 'ciniki.web.pageImageAdd'
        || $method == 'ciniki.web.pageImageDelete'
        || $method == 'ciniki.web.pageImageGet'
        || $method == 'ciniki.web.pageImageHistory'
        || $method == 'ciniki.web.pageImageUpdate'
        || $method == 'ciniki.web.pageList'
        || $method == 'ciniki.web.pageUpdate'
        || $method == 'ciniki.web.privateThemeAdd'
        || $method == 'ciniki.web.privateThemeContentAdd'
        || $method == 'ciniki.web.privateThemeContentGet'
        || $method == 'ciniki.web.privateThemeContentHistory'
        || $method == 'ciniki.web.privateThemeContentUpdate'
        || $method == 'ciniki.web.privateThemeGet'
        || $method == 'ciniki.web.privateThemeHistory'
        || $method == 'ciniki.web.privateThemeImageAdd'
        || $method == 'ciniki.web.privateThemeImageDelete'
        || $method == 'ciniki.web.privateThemeImageGet'
        || $method == 'ciniki.web.privateThemeImageHistory'
        || $method == 'ciniki.web.privateThemeImageUpdate'
        || $method == 'ciniki.web.privateThemeImages'
        || $method == 'ciniki.web.privateThemeList'
        || $method == 'ciniki.web.privateThemeUpdate'
        || $method == 'ciniki.web.faqAdd'
        || $method == 'ciniki.web.faqDelete'
        || $method == 'ciniki.web.faqGet'
        || $method == 'ciniki.web.faqHistory'
        || $method == 'ciniki.web.faqList'
        || $method == 'ciniki.web.faqSearchCategory'
        || $method == 'ciniki.web.faqUpdate'
        || $method == 'ciniki.web.redirectAdd'
        || $method == 'ciniki.web.redirectDelete'
        || $method == 'ciniki.web.redirectGet'
        || $method == 'ciniki.web.redirectHistory'
        || $method == 'ciniki.web.redirectList'
        || $method == 'ciniki.web.redirectUpdate'
        || $method == 'ciniki.web.sliderAdd'
        || $method == 'ciniki.web.sliderDelete'
        || $method == 'ciniki.web.sliderGet'
        || $method == 'ciniki.web.sliderHistory'
        || $method == 'ciniki.web.sliderImageAdd'
        || $method == 'ciniki.web.sliderImageDelete'
        || $method == 'ciniki.web.sliderImageGet'
        || $method == 'ciniki.web.sliderImageHistory'
        || $method == 'ciniki.web.sliderImageUpdate'
        || $method == 'ciniki.web.sliderImages'
        || $method == 'ciniki.web.sliderList'
        || $method == 'ciniki.web.sliderUpdate'
        || $method == 'ciniki.web.collectionAdd'
        || $method == 'ciniki.web.collectionDelete'
        || $method == 'ciniki.web.collectionGet'
        || $method == 'ciniki.web.collectionHistory'
        || $method == 'ciniki.web.collectionList'
        || $method == 'ciniki.web.collectionUpdate'
        ) {
        $strsql = "SELECT tnid, user_id FROM ciniki_tenant_users "
            . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . "AND user_id = '" . ciniki_core_dbQuote($ciniki, $ciniki['session']['user']['id']) . "' "
            . "AND package = 'ciniki' "
            . "AND status = 10 "
            . "AND (permission_group = 'owners' OR permission_group = 'employees' OR permission_group = 'resellers' ) "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.tenants', 'user');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        //
        // If the user has permission, return ok
        //
        if( isset($rc['rows']) && isset($rc['rows'][0]) 
            && $rc['rows'][0]['user_id'] > 0 && $rc['rows'][0]['user_id'] == $ciniki['session']['user']['id'] ) {
            return array('stat'=>'ok', 'modules'=>$modules);
        }
    }

    //
    // Some methods are available to resellers by not owners
    // 
    if( $method == 'ciniki.web.clearContentCache' 
        || $method == 'ciniki.web.clearImageCache'
        ) {
        $strsql = "SELECT tnid, user_id FROM ciniki_tenant_users "
            . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . "AND user_id = '" . ciniki_core_dbQuote($ciniki, $ciniki['session']['user']['id']) . "' "
            . "AND package = 'ciniki' "
            . "AND status = 10 "
            . "AND (permission_group = 'resellers' ) "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.tenants', 'user');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        //
        // If the user has permission, return ok
        //
        if( isset($rc['rows']) && isset($rc['rows'][0]) 
            && $rc['rows'][0]['user_id'] > 0 && $rc['rows'][0]['user_id'] == $ciniki['session']['user']['id'] ) {
            return array('stat'=>'ok', 'modules'=>$modules);
        }
    }
    //
    // By default, fail
    //
    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.9', 'msg'=>'Access denied.'));
}
?>
