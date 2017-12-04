<?php
//
// Description
// -----------
// This function will create a fake web request hash that can be used as $ciniki['request'] when
// calling web page generators to fake a web request and provide required variables.
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_createFakeRequest($ciniki, $tnid) {

    //
    // Get the web settings for the tenant
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'settings');
    $rc = ciniki_web_settings($ciniki, $tnid);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $settings = $rc['settings'];

    $web_ciniki = array('config'=>$ciniki['config'], 'databases'=>$ciniki['databases']);

    //
    // Get the tenant uuid
    //
    $strsql = "SELECT uuid, sitename "
        . "FROM ciniki_tenants "
        . "WHERE id = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.tenants', 'tenant');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['tenant']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.11', 'msg'=>'Tenant not found'));
    }
    $tenant = $rc['tenant'];
    $web_ciniki['tenant'] = array('uuid'=>$rc['tenant']['uuid']);
    $web_ciniki['tenant']['cache_dir'] = $ciniki['config']['ciniki.core']['cache_dir'] . '/'
        . $tenant['uuid'][0] . '/' . $tenant['uuid'];
    $web_ciniki['tenant']['modules'] = $ciniki['tenant']['modules'];

    $web_ciniki['request'] = array(
        'tnid'=>$tnid,
        'page'=>'', 
        'args'=>array(),
        'cache_dir'=>$ciniki['config']['ciniki.core']['modules_dir'] . '/web/cache',
        'layout_dir'=>$ciniki['config']['ciniki.core']['modules_dir'] . '/web/layouts',
        'theme_dir'=>$ciniki['config']['ciniki.core']['modules_dir'] . '/web/themes',
        'inline_javascript'=>'',
        'ssl'=>'no',
        'uri_split'=>array(),
        );

    //
    // Get the primary domain for the tenant
    //
    $strsql = "SELECT domain "
        . "FROM ciniki_tenant_domains "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND status = 1 "
        . "ORDER BY flags DESC "
        . "LIMIT 1 "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.tenants', 'domain');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['domain']) ) {
        $web_ciniki['request']['domain'] = $rc['domain']['domain'];
        $web_ciniki['request']['domain_base_url'] = 'http://' . $rc['domain']['domain'];
        $web_ciniki['request']['ssl_domain_base_url'] = 'http://' . $rc['domain']['domain'];
        $web_ciniki['request']['base_url'] = '';
    } else {
        //
        // No domain, lookup as subdomain
        //
        $master_domain = $ciniki['config']['ciniki.web']['master.domain'];
        $web_ciniki['request']['domain'] = $master_domain;
        $web_ciniki['request']['domain_base_url'] = 'http://' . $master_domain . '/' . $tenant['sitename'];
        $web_ciniki['request']['ssl_domain_base_url'] = 'http://' . $master_domain . '/' . $tenant['sitename'];
        $web_ciniki['request']['base_url'] = '/' . $tenant['sitename'];
    }
    $web_ciniki['request']['cache_url'] = 'http://' . $web_ciniki['request']['domain'] . '/ciniki-web-cache';
    $web_ciniki['request']['layout_url'] = 'http://' . $web_ciniki['request']['domain'] . '/ciniki-web-layouts';
    $web_ciniki['request']['theme_url'] = 'http://' . $web_ciniki['request']['domain'] . '/ciniki-web-themes';

    return array('stat'=>'ok', 'web_ciniki'=>$web_ciniki, 'settings'=>$settings);
}
?>
