<?php
//
// Description
// -----------
// This function will return the web cache dir for a tenant.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:         The ID of the tenant to get the details for.
// keys:                The comma delimited list of keys to lookup values for.
//
// Returns
// -------
// <details>
//      <tenant name='' tagline='' />
// </details>
//
function ciniki_web_cacheDir(&$ciniki, $tnid) {
    $rsp = array('stat'=>'ok', 'details'=>array());

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');

    if( isset($ciniki['config']['ciniki.web']['cache_dir']) && $ciniki['config']['ciniki.web']['cache_dir'] != '' ) {
        $base_cache_dir = $ciniki['config']['ciniki.web']['cache_dir'];
    } else {
        $base_cache_dir = $ciniki['config']['ciniki.core']['root_dir'] . '/ciniki-mods/web/cache/';
    }

    //
    // Determine the tnid
    //
    if( $tnid == 0 ) {
        $cache_dir = $base_cache_dir . '/0/0' ;
    }

    //
    // If previously requested, use from settings
    //
    elseif( isset($ciniki['tenant']['settings']['web_cache_dir']) ) {
        return array('stat'=>'ok', 'cache_dir'=>$ciniki['tenant']['settings']['web_cache_dir']);
    }

    //
    // Nothing requested, setup cache dir
    //
    elseif( $tnid > 0 ) {
        $strsql = "SELECT uuid "
            . "FROM ciniki_tenants "
            . "WHERE id = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' ";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.tenants', 'tenant');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( !isset($rc['tenant']) ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.6', 'msg'=>'Unable to get tenant details'));
        }

        $tenant_uuid = $rc['tenant']['uuid'];

        $cache_dir = $base_cache_dir . '/' . $tenant_uuid[0] . '/' . $tenant_uuid;

        //
        // Save settings in $ciniki cache for faster access
        //
        if( !isset($ciniki['tenant']) ) {
            $ciniki['tenant'] = array('settings'=>array('web_cache_dir'=>$cache_dir));
        } 
        elseif( !isset($ciniki['tenant']['settings']) ) {
            $ciniki['tenant']['settings'] = array('web_cache_dir'=>$cache_dir);
        } 
        else {
            $ciniki['tenant']['settings']['web_cache_dir'] = $cache_dir;
        }
    } else {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.7', 'msg'=>'Unable to get tenant cache directory'));
    }

    return array('stat'=>'ok', 'cache_dir'=>$cache_dir);
}
?>
