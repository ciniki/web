<?php
//
// Description
// -----------
// This function will lookup the client domain or sitename in the database, and return the tenant id.
// The request for the tenant website can be in the form of tenantdomain.com or ciniki.com/tenantsitename.
//
// Arguments
// ---------
// ciniki:
// domain:      The domain or sitename to lookup.
// type:        The type of lookup: domain or sitename.
//
// Returns
// -------
//
function ciniki_web_lookupClientDomain(&$ciniki, $domain, $type, $reseller_id=0) {

    //
    // Strip the www from the domain before looking up
    //
    $domain = preg_replace('/^www\./', '', $domain);

    //
    // Query the database for the domain
    //
    $sitename = '';
    if( $type == 'sitename' ) {
        $sitename = $domain;
        $strsql = "SELECT id AS tnid, uuid, 'no' AS isprimary, 'no' as forcessl, 'no' as reseller "
            . "FROM ciniki_tenants "
            . "WHERE sitename = '" . ciniki_core_dbQuote($ciniki, $domain) . "' "
            . "AND status = 1 "
            . "AND reseller_id = '" . ciniki_core_dbQuote($ciniki, $reseller_id) . "' ";
    } else {
        $strsql = "SELECT ciniki_tenant_domains.tnid, ciniki_tenants.uuid, "
            . "IF((ciniki_tenant_domains.flags&0x01)=0x01, 'yes', 'no') AS isprimary, "
            . "IF((ciniki_tenant_domains.flags&0x10)=0x10, 'yes', 'no') AS forcessl, "
            . "IF((ciniki_tenants.flags&0x01)=0x01, 'yes', 'no') AS reseller "
            . "FROM ciniki_tenant_domains, ciniki_tenants "
            . "WHERE ciniki_tenant_domains.domain = '" . ciniki_core_dbQuote($ciniki, $domain) . "' "
            . "AND ciniki_tenant_domains.status < 50 "
            . "AND ciniki_tenant_domains.tnid = ciniki_tenants.id "
            . "";
    }
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.tenants', 'tenant');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Make sure only one row was returned, otherwise there's a config error 
    // and we don't know which tenant the domain actually belongs to
    //
    if( !isset($rc['tenant']) || !isset($rc['tenant']['tnid']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.110', 'msg'=>'Configuration error'));
    }
    $isprimary = $rc['tenant']['isprimary'];
    $forcessl = $rc['tenant']['forcessl'];
    $reseller = $rc['tenant']['reseller'];
    $tnid = $rc['tenant']['tnid'];
    $tenant_uuid = $rc['tenant']['uuid'];

    //
    // Get the sitename
    //
    if( $type == 'domain' ) {
        $strsql = "SELECT sitename "
            . "FROM ciniki_tenants "
            . "WHERE id = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . "AND status = 1 ";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.tenants', 'tenant');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['tenant']['sitename']) ) {
            $sitename = $rc['tenant']['sitename'];
        }
    }

    //
    // Get primary domain if not primary
    //
    $redirect = '';
    $domain = '';
    if( $isprimary == 'no' ) {
        $strsql = "SELECT domain, flags "
            . "FROM ciniki_tenant_domains "
            . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
//            . "AND (flags&0x01) = 0x01 "  // Converted to sort by flags, so if there is a domain it will redirect
            . "AND status < 50 "
            . "ORDER BY (flags&0x01) DESC "
            . "LIMIT 1 "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.tenants', 'tenant');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['tenant']) && $rc['tenant']['domain'] != '' ) {
            if( ($rc['tenant']['flags']&0x10) == 0x10 ) {
                $forcessl = 'yes';
            }
            if( ($rc['tenant']['flags']&0x01) == 0x01 ) {
                $redirect = $rc['tenant']['domain'];
            } else {
                $domain = $rc['tenant']['domain'];
            }
        } 
    }

    //
    // Get the list of active modules for the tenant
    //
    $strsql = "SELECT ruleset "
        . ", CONCAT_WS('.', ciniki_tenant_modules.package, ciniki_tenant_modules.module) AS module_id, "
        . "ciniki_tenant_modules.flags, UNIX_TIMESTAMP(ciniki_tenant_modules.last_change) AS last_change "
        . "FROM ciniki_tenants, ciniki_tenant_modules "
        . "WHERE ciniki_tenants.id = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND ciniki_tenants.status = 1 "                                                       // Tenant is active
        . "AND ciniki_tenants.id = ciniki_tenant_modules.tnid "
        . "AND (ciniki_tenant_modules.status = 1 OR ciniki_tenant_modules.status = 2) "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashIDQuery');
    $rc = ciniki_core_dbHashIDQuery($ciniki, $strsql, 'ciniki.tenants', 'modules', 'module_id');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['modules']) || !isset($rc['modules']['ciniki.web']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.111', 'msg'=>'Website not activated.'));
    }
    $modules = $rc['modules'];

    //
    // Get the list of module pages and build their Base URL's
    //
    $strsql = "SELECT id, parent_id, permalink, title, flags, page_type, page_module "
        . "FROM ciniki_web_pages "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "";
    $rc = ciniki_core_dbHashIDQuery($ciniki, $strsql, 'ciniki.web', 'pages', 'id');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['pages']) ) {
        $pages = array();
        $module_pages = array();
    } else {
        $pages = $rc['pages'];
        $module_pages = array();
        //
        // Find the module pages and setup their base URL's
        //
        foreach($pages as $pid => $page) {
            if( $page['page_type'] == 30 ) {
                $module_pages[$page['page_module']] = $page;
                $module_pages[$page['page_module']]['base_url'] = '/' . $page['permalink'];
                if( $page['parent_id'] > 0 ) {
                    $parent_id = $page['parent_id'];
                    while($parent_id > 0 && isset($pages[$parent_id])) {
                        $module_pages[$page['page_module']]['base_url'] = '/' . $pages[$parent_id]['permalink'] . $module_pages[$page['page_module']]['base_url'];
                        $parent_id = $pages[$parent_id]['parent_id'];
                    }
                }
            }
        }
    }

    return array('stat'=>'ok', 'tnid'=>$tnid, 'tenant_uuid'=>$tenant_uuid, 'modules'=>$modules, 'pages'=>$pages, 'module_pages'=>$module_pages, 'redirect'=>$redirect, 'domain'=>$domain, 'sitename'=>$sitename, 'forcessl'=>$forcessl, 'reseller'=>$reseller);
}
?>
