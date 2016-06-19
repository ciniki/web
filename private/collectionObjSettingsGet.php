<?php
//
// Description
// -----------
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_web_collectionObjSettingsGet($ciniki, $business_id, $collection_id) {

    //
    // Get the list of objects
    //
    $strsql = "SELECT id, object, title, sequence, num_items, more "
        . "FROM ciniki_web_collection_objs "
        . "WHERE collection_id = '" . ciniki_core_dbQuote($ciniki, $collection_id) . "' "
        . "AND business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.web', array(
        array('container'=>'objects', 'fname'=>'object', 'name'=>'object',
            'fields'=>array('id', 'object', 'title', 'sequence', 'num_items', 'more')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $settings = array();
    $objects = array();
    if( isset($rc['objects']) ) {
        $objects = $rc['objects'];
        foreach($rc['objects'] as $obj) {
            $settings[$obj['object'] . '-title'] = $obj['title'];
            $settings[$obj['object'] . '-sequence'] = $obj['sequence'];
            $settings[$obj['object'] . '-num_items'] = $obj['num_items'];
            $settings[$obj['object'] . '-more'] = $obj['more'];
        }
    }
    return array('stat'=>'ok', 'settings'=>$settings, 'objects'=>$objects);
}
?>
