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
function ciniki_web_indexUpdateObject($ciniki, $business_id, $args) {

    $common_words = array('and', 'the', 'for', 'any', 'are', 'but', 'not', 'was', 'our', 
        'all', 'has', 'use', 'too', 'put', 'let', 'its', "it's", 
        'they', "they're", 'there', 'their');

    list($pkg, $mod) = explode('.', $args['object']);

    //
    // Check if pages menu enabled, then
    //
    if( ciniki_core_checkModuleFlags($ciniki, $pkg . '.' . $mod, 0x0200) && !isset($args['base_url']) ) {
        $rc = ciniki_web_indexModuleBaseURL($ciniki, $business_id, $module);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['base_url']) ) {
            $args['base_url'] = $rc['base_url'];
        }
    }

    //
    // Get the current index data
    //
    $strsql = "SELECT id, uuid, title, subtitle, meta, "
        . "primary_image_id, synopsis, "
        . "object, object_id, "
        . "primary_words, secondary_words, tertiary_words, weight, url "
        . "FROM ciniki_web_index "
        . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
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
        $rc = $fn($ciniki, $business_id, $args);
        if( $rc['stat'] != 'ok' && $rc['stat'] != 'noexist' ) {
            return $rc;
        }
        if( isset($rc['object']) ) {
            $module_object = $rc['object'];
        }
    }

    //
    // Check if index should be removed
    //
    if( isset($index_object) && !isset($module_object) ) {
        $rc = ciniki_core_objectDelete($ciniki, $business_id, 'ciniki.web.index', $index_object['id'], $index_object['uuid'], 0x07);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
    }

    if( isset($module_object) ) {
        //
        // Clean up each of the three word fields
        //
        foreach(array('primary_words', 'secondary_words', 'tertiary_words') as $field) {
            $str = preg_replace('/\.\s+/', '', $module_object[$field]);
            $str = strtolower($str);
            $words = explode(' ', $str);

            //
            // Remove 2 letter words, and common words
            //
            foreach($words as $wid => $word) {
//                if( strlen($word) < 3 ) {
//                    unset($words[$wid]);
//                }
                if( in_array($word, $common_words) ) {
                    unset($words[$wid]);
                }
            }

            //
            // Sort the words
            //
            sort($words);

            //
            // Remove duplicates, and join into single string
            //
            $module_object[$field] = implode(' ', array_unique($words));
        }

        //
        // Update the index
        //
        if( isset($index_object) ) {
            //
            // Check if anything is different
            //
            $update_args = array();
            $fields = array('title', 'subtitle', 'meta', 'primary_image_id', 'synopsis', 'primary_words', 'secondary_words', 'tertiary_words', 'weight', 'url');
            foreach($fields as $field) {
                if( $index_object[$field] != $module_object[$field] ) {
                    $update_args[$field] = $module_object[$field];
                }
            }

            if( isset($update_args['primary_image_id']) ) {
                $rc = ciniki_web_indexUpdateObjectImage($ciniki, $business_id, $module_object['primary_image_id'], $index_object['id']);
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
            }
            
            if( count($update_args) > 0 ) {
                $rc = ciniki_core_objectUpdate($ciniki, $business_id, 'ciniki.web.index', $index_object['id'], $update_args, 0x07);
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
            }
        } else {
            $rc = ciniki_core_objectAdd($ciniki, $business_id, 'ciniki.web.index', $module_object, 0x07);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $index_id = $rc['id'];
            $rc = ciniki_web_indexUpdateObjectImage($ciniki, $business_id, $module_object['primary_image_id'], $index_id);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
        }
    }

    return array('stat'=>'ok');
}
?>
