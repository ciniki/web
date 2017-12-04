<?php
//
// Description
// -----------
// This method will return the list of Home Page Links for a tenant.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:        The ID of the tenant to get Home Page Link for.
//
// Returns
// -------
//
function ciniki_web_hplinkList($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'parent_id'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Parent'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to tnid as owner, or sys admin.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'checkAccess');
    $rc = ciniki_web_checkAccess($ciniki, $args['tnid'], 'ciniki.web.hplinkList');
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
        . "WHERE ciniki_web_hplinks.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . (isset($args['parent_id']) && $args['parent_id'] != '' ? . " AND parent_id = '" . ciniki_core_dbQuote($ciniki, $args['parent_id']) . "' " : "")
        . "";
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
