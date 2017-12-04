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
function ciniki_web_collectionObjSettingsUpdate($ciniki, $tnid, $collection_id, $args) {

    //
    // Get the current list of objects
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'collectionObjSettingsGet');
    $rc = ciniki_web_collectionObjSettingsGet($ciniki, $tnid, $collection_id);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $cur_settings = $rc['settings'];    
    $cur_objects = $rc['objects'];

    //
    // Build the objects that need updating
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
    $objs = array();        // Build an array of objects that need updating or adding
    foreach($args as $arg_name => $arg_value) {
        if( preg_match("/^(.*\..*\..*)-(.*)$/", $arg_name, $matches) ) {
            list($pkg, $mod, $obj) = explode('.', $matches[1]);
            if( isset($ciniki['tenant']['modules'][$pkg . '.' . $mod]) ) {
                if( !isset($cur_settings[$arg_name]) || $cur_settings[$arg_name] != $arg_value ) {
                    if( !isset($objs[$pkg . '.' . $mod . '.' . $obj]) ) {
                        $objs[$pkg . '.' . $mod . '.' . $obj] = array();
                    }
                    $objs[$pkg . '.' . $mod . '.' . $obj][$matches[2]] = $arg_value;
                }
            }
        }
    }

    //
    // Update the objects
    //
    foreach($objs as $obj_name => $object) {
        //
        // If the object doesn't current exist in the table, add it
        //
        if( isset($cur_objects[$obj_name]) ) {
            // Update the object
            $rc = ciniki_core_objectUpdate($ciniki, $tnid, 'ciniki.web.collection_obj', 
                $cur_objects[$obj_name]['id'], $object, 0x04);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
        } else {
            // Add the object
            $object['collection_id'] = $collection_id;
            $object['object'] = $obj_name;
            if( !isset($object['title']) ) { $object['title'] = ''; }
            if( !isset($object['sequence']) ) { $object['sequence'] = ''; }
            if( !isset($object['num_items']) ) { $object['num_items'] = ''; }
            if( !isset($object['more']) ) { $object['more'] = ''; }
            $rc = ciniki_core_objectAdd($ciniki, $tnid, 'ciniki.web.collection_obj', $object, 0x04);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
        }
    }

    return array('stat'=>'ok');
}
?>
