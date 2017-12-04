<?php
//
// Description
// -----------
// This function will add/modify/delete an object in the web index.
//
// Arguments
// ---------
// ciniki:
//
// Returns
// -------
//
function ciniki_web_indexUpdateObject(&$ciniki, $tnid, $args) {
    
    $common_words = array(
        'a', 'i',
        'an', 'on', 'in',
        'and', 'the', 'for', 'any', 'are', 'but', 'not', 'was', 'our', 
        'all', 'has', 'use', 'too', 'put', 'let', 'its', "it's", 
        'they', "they're", 'there', 'their');

    list($pkg, $mod) = explode('.', $args['object']);

    //
    // Check if pages menu enabled, then
    //
    if( ciniki_core_checkModuleFlags($ciniki, 'ciniki.web', 0x0200) && !isset($args['base_url']) ) {
        $rc = ciniki_web_indexModuleBaseURL($ciniki, $tnid, $pkg . '.' . $mod);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['base_url']) ) {
            $args['base_url'] = $rc['base_url'];
        } else {
            $rc = ciniki_web_indexObjectBaseURL($ciniki, $tnid, $args['object']);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            if( isset($rc['base_url']) ) {
                $args['base_url'] = $rc['base_url'];
            }
        }
    }

    //
    // Get the current index data
    //
    $strsql = "SELECT id, uuid, label, title, subtitle, meta, "
        . "primary_image_id, synopsis, "
        . "object, object_id, "
        . "primary_words, secondary_words, tertiary_words, weight, url "
        . "FROM ciniki_web_index "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND object = '" . ciniki_core_dbQuote($ciniki, $args['object']) . "' "
        . "AND object_id = '" . ciniki_core_dbQuote($ciniki, $args['object_id']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.web', 'object');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['object']) ) {
        $index_object = $rc['object'];
    }

    //
    // Get the modules index
    //
    $rc = ciniki_core_loadMethod($ciniki, $pkg, $mod, 'hooks', 'webIndexObject');
    if( $rc['stat'] == 'ok' ) {
        $fn = $rc['function_call'];
        $rc = $fn($ciniki, $tnid, $args);
        if( $rc['stat'] != 'ok' && $rc['stat'] != 'noexist' ) {
            return $rc;
        }
        if( isset($rc['object']) ) {
            $module_object = $rc['object'];
        }
    }

    //
    // Get the tenant uuid
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'cacheDir');
    $rc = ciniki_web_cacheDir($ciniki, $tnid);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $cache_dir = $rc['cache_dir'];

    //
    // Check if index should be removed
    //
    if( isset($index_object) && !isset($module_object) ) {
        if( $index_object['primary_image_id'] > 0 ) {
            $filename = $cache_dir . '/search/' . sprintf("%012d", $index_object['primary_image_id']) . '.jpg';
            if( file_exists($filename) ) {
                unlink($filename);
            }
        }
        $rc = ciniki_core_objectDelete($ciniki, $tnid, 'ciniki.web.index', $index_object['id'], $index_object['uuid'], 0x07);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
    }

    if( isset($module_object) ) {
        //
        // Clean up each of the three word fields
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makeKeywords');
        foreach(array('primary_words', 'secondary_words', 'tertiary_words') as $field) {
            $module_object[$field] = ciniki_core_makeKeywords($ciniki, $module_object[$field]);
        }

        //
        // Update the index
        //
        if( isset($index_object) ) {
            //
            // Check if anything is different
            //
            $update_args = array();
            $fields = array('label', 'title', 'subtitle', 'meta', 'primary_image_id', 'synopsis', 'primary_words', 'secondary_words', 'tertiary_words', 'weight', 'url');
            foreach($fields as $field) {
                if( $index_object[$field] != $module_object[$field] ) {
                    $update_args[$field] = $module_object[$field];
                }
            }

//            if( isset($update_args['primary_image_id']) ) {
                $rc = ciniki_web_indexUpdateObjectImage($ciniki, $tnid, $module_object['primary_image_id'], $index_object['id']);
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
//            }
            
            if( count($update_args) > 0 ) {
                $rc = ciniki_core_objectUpdate($ciniki, $tnid, 'ciniki.web.index', $index_object['id'], $update_args, 0x07);
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
            }
        } else {
            $rc = ciniki_core_objectAdd($ciniki, $tnid, 'ciniki.web.index', $module_object, 0x07);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $index_id = $rc['id'];
            $rc = ciniki_web_indexUpdateObjectImage($ciniki, $tnid, $module_object['primary_image_id'], $index_id);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
        }
    }

    return array('stat'=>'ok');
}
?>
