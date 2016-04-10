<?php
//
// Description
// -----------
// This function updates the index for a module.
//
// Arguments
// ---------
// ciniki:
//
// Returns
// -------
//
function ciniki_web_indexUpdateModule($ciniki, $business_id, $module) {
   
    list($pkg, $mod) = explode('.', $module);

    //
    // Get the base_url for this module, as it may be inside a custom page.
    // There can also be base_urls for objects as page may link to object instead of module.
    // For example, ciniki.customers.dealers
    //
    if( ciniki_core_checkModuleFlags($ciniki, 'ciniki.web', 0x0200) ) {
        $rc = ciniki_web_indexModuleBaseURL($ciniki, $business_id, $module);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['base_url']) ) {
            error_log($module . '->' . $rc['base_url']);
            $base_url = $rc['base_url'];
        }
    }
    
    $object_base_urls = array();
    $rc = ciniki_core_loadMethod($ciniki, $pkg, $mod, 'hooks', 'webOptions');
    if( $rc['stat'] == 'ok' ) {
        $fn = $rc['function_call'];
        $rc = $fn($ciniki, $business_id, array());
        if( $rc['stat'] == 'ok' && isset($rc['pages']) ) {
            $pages = $rc['pages'];
            foreach($pages as $object => $page) {
                //
                // Check for a base_url for the object
                //
                $rc = ciniki_web_indexObjectBaseURL($ciniki, $business_id, $object);
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
                if( isset($rc['base_url']) ) {
                    error_log($object . '->' . $rc['base_url']);
                    $object_base_urls[$object] = $rc['base_url'];
                }
            }
        }
    }


    //
    // Get the list of objects from the index
    //
    $index_objects = array();
    $strsql = "SELECT id, uuid, object, object_id, UNIX_TIMESTAMP(last_updated) AS last_updated "
        . "FROM ciniki_web_index "
        . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
        . "AND object LIKE '" . ciniki_core_dbQuote($ciniki, $module) . ".%' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.web', 'object');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['rows']) ) {
        $index_objects = $rc['rows'];
    }

    //
    // Get the list of objects from the module, the object.object_id must be returned as array index
    //
    $module_objects = array();
    $rc = ciniki_core_loadMethod($ciniki, $pkg, $mod, 'hooks', 'webIndexList');
    if( $rc['stat'] == 'ok' ) {
        $fn = $rc['function_call'];
        $rc = $fn($ciniki, $business_id, array());
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['objects']) ) {
            $module_objects = $rc['objects'];
        }
    } else {
        //
        // No webIndexList for module, delete anything that exists
        //
        $rc = ciniki_web_indexDeleteModule($ciniki, $business_id, $module);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
    }

    //
    // Check for index_objects to delete
    //
    foreach($index_objects as $oid => $object) {
        if( !isset($module_objects[$object['object'] . '.' . $object['object_id']]) ) {
            error_log('Delete');
            $rc = ciniki_core_objectDelete($ciniki, $business_id, 'ciniki.web.index', $object['id'], $object['uuid'], 0x07);
        }
    }

    //
    // Update module objects
    //
    foreach($module_objects as $oid => $object) {
        $args = array('object'=>$object['object'], 'object_id'=>$object['object_id']);
        if( isset($base_url) ) {
            $args['base_url'] = $base_url;
        } elseif( isset($object_base_urls[$object['object']]) ) {
            $args['base_url'] = $object_base_urls[$object['object']];
        }
        $rc = ciniki_web_indexUpdateObject($ciniki, $business_id, $args);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
    }

    return array('stat'=>'ok');
}
?>
