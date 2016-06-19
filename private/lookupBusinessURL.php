<?php
//
// Description
// -----------
// This function will lookup the client domain or sitename in the database, and return the business id.
// The request for the business website can be in the form of businessdomain.com or ciniki.com/businesssitename.
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
function ciniki_web_lookupBusinessURL(&$ciniki, $business_id) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');

    //
    // Check if they have SSL turned on
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash');
    $rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_web_settings', 'business_id', $business_id, 'ciniki.web', 'settings', 'site-ssl');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $ssl_settings = $rc['settings'];

    $strsql = "SELECT domain, "
        . "(flags&0x01) AS isprimary "
        . "FROM ciniki_business_domains "
        . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
        . "AND status < 50 "
        . "ORDER BY isprimary DESC "
        . "LIMIT 1"
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.businesses', 'business');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['business']) ) {
        if( isset($ssl_settings['site-ssl-active']) && $ssl_settings['site-ssl-active'] == 'yes' ) {
            return array('stat'=>'ok', 'url'=>"http://" . $rc['business']['domain'], 'secure_url'=>"https://" . $rc['business']['domain']);
        } else {
            return array('stat'=>'ok', 'url'=>"http://" . $rc['business']['domain'], 'secure_url'=>"http://" . $rc['business']['domain']);
        }
    }

    //
    // Get the sitename if no domain is specified
    //
    $strsql = "SELECT sitename FROM ciniki_businesses "
        . "WHERE id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.businesses', 'business');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['business']) ) {
        if( isset($ssl_settings['site-ssl-active']) && $ssl_settings['site-ssl-active'] == 'yes' ) {
            return array('stat'=>'ok', 
                'url'=>"http://" . $ciniki['config']['ciniki.web']['master.domain'] . '/' . $rc['business']['sitename'],
                'secure_url'=>"https://" . $ciniki['config']['ciniki.web']['master.domain'] . '/' . $rc['business']['sitename'],
                );
        } else {
            return array('stat'=>'ok', 
                'url'=>"http://" . $ciniki['config']['ciniki.web']['master.domain'] . '/' . $rc['business']['sitename'],
                'secure_url'=>"http://" . $ciniki['config']['ciniki.web']['master.domain'] . '/' . $rc['business']['sitename'],
                );
        }
    }

    return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1052', 'msg'=>'Unable to find business URL'));
}
?>
