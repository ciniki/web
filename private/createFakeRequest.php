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
function ciniki_web_createFakeRequest($ciniki, $business_id) {

    //
    // Get the web settings for the business
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'settings');
    $rc = ciniki_web_settings($ciniki, $business_id);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $settings = $rc['settings'];

    $web_ciniki = array('config'=>$ciniki['config'], 'databases'=>$ciniki['databases']);

    //
    // Get the business uuid
    //
    $strsql = "SELECT uuid, sitename "
        . "FROM ciniki_businesses "
        . "WHERE id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.businesses', 'business');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['business']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.11', 'msg'=>'Business not found'));
    }
    $business = $rc['business'];
    $web_ciniki['business'] = array('uuid'=>$rc['business']['uuid']);
    $web_ciniki['business']['cache_dir'] = $ciniki['config']['ciniki.core']['cache_dir'] . '/'
        . $business['uuid'][0] . '/' . $business['uuid'];
    $web_ciniki['business']['modules'] = $ciniki['business']['modules'];

    $web_ciniki['request'] = array(
        'business_id'=>$business_id,
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
    // Get the primary domain for the business
    //
    $strsql = "SELECT domain "
        . "FROM ciniki_business_domains "
        . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
        . "AND status = 1 "
        . "ORDER BY flags DESC "
        . "LIMIT 1 "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.businesses', 'domain');
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
        $web_ciniki['request']['domain_base_url'] = 'http://' . $master_domain . '/' . $business['sitename'];
        $web_ciniki['request']['ssl_domain_base_url'] = 'http://' . $master_domain . '/' . $business['sitename'];
        $web_ciniki['request']['base_url'] = '/' . $business['sitename'];
    }
    $web_ciniki['request']['cache_url'] = 'http://' . $web_ciniki['request']['domain'] . '/ciniki-web-cache';
    $web_ciniki['request']['layout_url'] = 'http://' . $web_ciniki['request']['domain'] . '/ciniki-web-layouts';
    $web_ciniki['request']['theme_url'] = 'http://' . $web_ciniki['request']['domain'] . '/ciniki-web-themes';

    return array('stat'=>'ok', 'web_ciniki'=>$web_ciniki, 'settings'=>$settings);
}
?>
