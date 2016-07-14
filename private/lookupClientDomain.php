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
function ciniki_web_lookupClientDomain(&$ciniki, $domain, $type) {

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
        $strsql = "SELECT id AS business_id, uuid, 'no' AS isprimary, 'no' as forcessl "
            . "FROM ciniki_businesses "
            . "WHERE sitename = '" . ciniki_core_dbQuote($ciniki, $domain) . "' "
            . "AND status = 1 "
            . "";
    } else {
        $strsql = "SELECT ciniki_business_domains.business_id, ciniki_businesses.uuid, "
            . "IF((ciniki_business_domains.flags&0x01)=0x01, 'yes', 'no') AS isprimary, "
            . "IF((ciniki_business_domains.flags&0x10)=0x10, 'yes', 'no') AS forcessl "
            . "FROM ciniki_business_domains, ciniki_businesses "
            . "WHERE ciniki_business_domains.domain = '" . ciniki_core_dbQuote($ciniki, $domain) . "' "
            . "AND ciniki_business_domains.status < 50 "
            . "AND ciniki_business_domains.business_id = ciniki_businesses.id "
            . "";
    }
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.businesses', 'business');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Make sure only one row was returned, otherwise there's a config error 
    // and we don't know which business the domain actually belongs to
    //
    if( !isset($rc['business']) || !isset($rc['business']['business_id']) ) {
        return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'610', 'msg'=>'Configuration error'));
    }
    $isprimary = $rc['business']['isprimary'];
    $forcessl = $rc['business']['forcessl'];
    $business_id = $rc['business']['business_id'];
    $business_uuid = $rc['business']['uuid'];

    //
    // Get the sitename
    //
    if( $type == 'domain' ) {
        $strsql = "SELECT sitename "
            . "FROM ciniki_businesses "
            . "WHERE id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
            . "AND status = 1 ";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.businesses', 'business');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['business']['sitename']) ) {
            $sitename = $rc['business']['sitename'];
        }
    }

    //
    // Get primary domain if not primary
    //
    $redirect = '';
    $domain = '';
    if( $isprimary == 'no' ) {
        $strsql = "SELECT domain, flags "
            . "FROM ciniki_business_domains "
            . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
//            . "AND (flags&0x01) = 0x01 "  // Converted to sort by flags, so if there is a domain it will redirect
            . "AND status < 50 "
            . "ORDER BY (flags&0x01) DESC "
            . "LIMIT 1 "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.businesses', 'business');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['business']) && $rc['business']['domain'] != '' ) {
            if( ($rc['business']['flags']&0x10) == 0x10 ) {
                $forcessl = 'yes';
            }
            if( ($rc['business']['flags']&0x01) == 0x01 ) {
                $redirect = $rc['business']['domain'];
            } else {
                $domain = $rc['business']['domain'];
            }
        } 
    }

    //
    // Get the list of active modules for the business
    //
    $strsql = "SELECT ruleset "
        . ", CONCAT_WS('.', ciniki_business_modules.package, ciniki_business_modules.module) AS module_id, "
        . "ciniki_business_modules.flags, UNIX_TIMESTAMP(ciniki_business_modules.last_change) AS last_change "
        . "FROM ciniki_businesses, ciniki_business_modules "
        . "WHERE ciniki_businesses.id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
        . "AND ciniki_businesses.status = 1 "                                                       // Business is active
        . "AND ciniki_businesses.id = ciniki_business_modules.business_id "
        . "AND (ciniki_business_modules.status = 1 OR ciniki_business_modules.status = 2) "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashIDQuery');
    $rc = ciniki_core_dbHashIDQuery($ciniki, $strsql, 'ciniki.businesses', 'modules', 'module_id');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['modules']) || !isset($rc['modules']['ciniki.web']) ) {
        return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'613', 'msg'=>'Website not activated.'));
    }
    $modules = $rc['modules'];

    return array('stat'=>'ok', 'business_id'=>$business_id, 'business_uuid'=>$business_uuid, 'modules'=>$modules, 'redirect'=>$redirect, 'domain'=>$domain, 'sitename'=>$sitename, 'forcessl'=>$forcessl);
}
?>
