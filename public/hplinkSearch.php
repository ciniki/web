<?php
//
// Description
// -----------
// This method searchs for a Home Page Links for a business.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:        The ID of the business to get Home Page Link for.
// start_needle:       The search string to search for.
// limit:              The maximum number of entries to return.
//
// Returns
// -------
//
function ciniki_web_hplinkSearch($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'),
        'start_needle'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Search String'),
        'limit'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Limit'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to business_id as owner, or sys admin.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'checkAccess');
    $rc = ciniki_web_checkAccess($ciniki, $args['business_id'], 'ciniki.web.hplinkSearch');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the list of hplinks
    //
    $strsql = "SELECT ciniki_web_hplinks.id, "
        . "ciniki_web_hplinks.parent_id, "
        . "ciniki_web_hplinks.title, "
        . "ciniki_web_hplinks.url, "
        . "ciniki_web_hplinks.sequence "
        . "FROM ciniki_web_hplinks "
        . "WHERE ciniki_web_hplinks.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "AND ("
            . "name LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR name LIKE '% " . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
        . ") "
        . "";
    if( isset($args['limit']) && is_numeric($args['limit']) && $args['limit'] > 0 ) {
        $strsql .= "LIMIT " . ciniki_core_dbQuote($ciniki, $args['limit']) . " ";
    } else {
        $strsql .= "LIMIT 25 ";
    }
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.web', array(
        array('container'=>'hplinks', 'fname'=>'id', 
            'fields'=>array('id', 'parent_id', 'title', 'url', 'sequence')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['hplinks']) ) {
        $hplinks = $rc['hplinks'];
        $hplink_ids = array();
        foreach($hplinks as $iid => $hplink) {
            $hplink_ids[] = $hplink['id'];
        }
    } else {
        $hplinks = array();
        $hplink_ids = array();
    }

    return array('stat'=>'ok', 'hplinks'=>$hplinks, 'nplist'=>$hplink_ids);
}
?>
