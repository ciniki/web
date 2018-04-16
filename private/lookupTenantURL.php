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
function ciniki_web_lookupTenantURL(&$ciniki, $tnid) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');

    //
    // Check if they have SSL turned on
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash');
    $rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_web_settings', 'tnid', $tnid, 'ciniki.web', 'settings', 'site-ssl');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $ssl_settings = $rc['settings'];

    $strsql = "SELECT domain, "
        . "(flags&0x01) AS isprimary "
        . "FROM ciniki_tenant_domains "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND status < 50 "
        . "ORDER BY isprimary DESC "
        . "LIMIT 1"
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.tenants', 'tenant');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['tenant']) ) {
        if( isset($ssl_settings['site-ssl-active']) && $ssl_settings['site-ssl-active'] == 'yes' ) {
            return array('stat'=>'ok', 'url'=>"http://" . $rc['tenant']['domain'], 'secure_url'=>"https://" . $rc['tenant']['domain']);
        } else {
            return array('stat'=>'ok', 'url'=>"http://" . $rc['tenant']['domain'], 'secure_url'=>"http://" . $rc['tenant']['domain']);
        }
    }

    //
    // Get the sitename if no domain is specified
    //
    $strsql = "SELECT sitename, reseller_id "
        . "FROM ciniki_tenants "
        . "WHERE id = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.tenants', 'tenant');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['tenant']) ) {
        $sitename = $rc['tenant']['sitename'];
        $reseller_id = $rc['tenant']['reseller_id'];
        //
        // Get the reseller domain
        //
        $strsql = "SELECT domain, "
            . "(flags&0x01) AS isprimary "
            . "FROM ciniki_tenant_domains "
            . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $reseller_id) . "' "
            . "AND status < 50 "
            . "ORDER BY isprimary DESC "
            . "LIMIT 1"
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.tenants', 'tenant');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['tenant']['domain']) ) {
            return array('stat'=>'ok', 
                'url'=>"http://" . $rc['tenant']['domain'] . '/' . $sitename, 
                'secure_url'=>"https://" . $rc['tenant']['domain'] . '/' . $sitename,
                );
        }

        if( isset($ssl_settings['site-ssl-active']) && $ssl_settings['site-ssl-active'] == 'yes' ) {
            return array('stat'=>'ok', 
                'url'=>"http://" . $ciniki['config']['ciniki.web']['master.domain'] . '/' . $sitename,
                'secure_url'=>"https://" . $ciniki['config']['ciniki.web']['master.domain'] . '/' . $sitename,
                );
        } else {
            return array('stat'=>'ok', 
                'url'=>"http://" . $ciniki['config']['ciniki.web']['master.domain'] . '/' . $sitename,
                'secure_url'=>"http://" . $ciniki['config']['ciniki.web']['master.domain'] . '/' . $sitename,
                );
        }
    }

    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.109', 'msg'=>'Unable to find tenant URL'));
}
?>
