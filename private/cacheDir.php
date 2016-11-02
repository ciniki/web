<?php
//
// Description
// -----------
// This function will return the web cache dir for a business.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:         The ID of the business to get the details for.
// keys:                The comma delimited list of keys to lookup values for.
//
// Returns
// -------
// <details>
//      <business name='' tagline='' />
// </details>
//
function ciniki_web_cacheDir(&$ciniki, $business_id) {
    $rsp = array('stat'=>'ok', 'details'=>array());

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');

    if( isset($ciniki['config']['ciniki.web']['cache_dir']) && $ciniki['config']['ciniki.web']['cache_dir'] != '' ) {
        $base_cache_dir = $ciniki['config']['ciniki.web']['cache_dir'];
    } else {
        $base_cache_dir = $ciniki['config']['ciniki.core']['root_dir'] . '/ciniki-mods/web/cache/';
    }

    //
    // Determine the business_id
    //
    if( $business_id == 0 ) {
        $cache_dir = $base_cache_dir . '/0/0' ;
    }

    //
    // If previously requested, use from settings
    //
    elseif( isset($ciniki['business']['settings']['web_cache_dir']) ) {
        return array('stat'=>'ok', 'cache_dir'=>$ciniki['business']['settings']['web_cache_dir']);
    }

    //
    // Nothing requested, setup cache dir
    //
    elseif( $business_id > 0 ) {
        $strsql = "SELECT uuid "
            . "FROM ciniki_businesses "
            . "WHERE id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' ";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.businesses', 'business');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( !isset($rc['business']) ) {
            return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'3389', 'msg'=>'Unable to get business details'));
        }

        $business_uuid = $rc['business']['uuid'];

        $cache_dir = $base_cache_dir . '/' . $business_uuid[0] . '/' . $business_uuid;

        //
        // Save settings in $ciniki cache for faster access
        //
        if( !isset($ciniki['business']) ) {
            $ciniki['business'] = array('settings'=>array('web_cache_dir'=>$cache_dir));
        } 
        elseif( !isset($ciniki['business']['settings']) ) {
            $ciniki['business']['settings'] = array('web_cache_dir'=>$cache_dir);
        } 
        else {
            $ciniki['business']['settings']['web_cache_dir'] = $cache_dir;
        }
    } else {
        return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'3388', 'msg'=>'Unable to get business cache directory'));
    }

    return array('stat'=>'ok', 'cache_dir'=>$cache_dir);
}
?>
