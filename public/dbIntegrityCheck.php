<?php
//
// Description
// -----------
// This function will go through the history of the ciniki.artcatalog module and 
// add missing history elements.
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_web_dbIntegrityCheck(&$ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'fix'=>array('required'=>'no', 'default'=>'no', 'name'=>'Fix Problems'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];
    
    //
    // Check access to tnid as owner, or sys admin
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'checkAccess');
    $rc = ciniki_web_checkAccess($ciniki, $args['tnid'], 'ciniki.web.dbIntegrityCheck', 0);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbUpdate');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDelete');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashIDQuery');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbFixTableHistory');
//  ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'private', 'refAdd');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectRefFix');

    if( $args['fix'] == 'yes' ) {
        //
        // Remove any image definitions of undefined
        //
        $strsql = "UPDATE ciniki_web_settings SET detail_value = 0 "
            . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND detail_key in ('page-home-image', 'page-about-image', 'site-header-image') "
            . "AND detail_value = 'undefined' "
            . "";
        $rc = ciniki_core_dbUpdate($ciniki, $strsql, 'ciniki.images');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }

        $strsql = "UPDATE ciniki_web_history SET new_value = '0' "
            . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND table_name = 'ciniki_web_settings' "
            . "AND table_key in ('page-home-image', 'page-about-image', 'site-header-image') "
            . "AND table_field = 'detail_value' "
            . "AND new_value = 'undefined' "
            . "";
        $rc = ciniki_core_dbUpdate($ciniki, $strsql, 'ciniki.images');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }

        //
        // Load objects file
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'objects');
        $rc = ciniki_web_objects($ciniki);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $objects = $rc['objects'];

        //
        // Check any references for the objects
        //
        foreach($objects as $o => $obj) {
            $rc = ciniki_core_objectRefFix($ciniki, $args['tnid'], 'ciniki.web.'.$o, 0x04);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
        }

/*
        //
        // Load existing image refs
        //
        $strsql = "SELECT CONCAT_WS('-', object_id, ref_id) AS refid "
            . "FROM ciniki_image_refs "
            . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND object = 'ciniki.web.setting' "
            . "";
        $rc = ciniki_core_dbHashIDQuery($ciniki, $strsql, 'ciniki.images', 'refs', 'refid');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['refs']) ) {
            $refs = $rc['refs'];
        } else {
            $refs = array();
        }
        //
        // Add image refs
        //
        $strsql = "SELECT detail_key, detail_value "
            . "FROM ciniki_web_settings "
            . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND detail_key in ('page-home-image', 'page-about-image', 'site-header-image') "
            . "AND detail_value > 0 "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.web', 'item');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['rows']) ) {
            $items = $rc['rows'];
            foreach($items as $iid => $item) {
                if( !isset($refs[$item['detail_key'] . '-' . $item['detail_value']]) ) {
                    $rc = ciniki_images_refAdd($ciniki, $args['tnid'], array(
                        'image_id'=>$item['detail_value'],
                        'object'=>'ciniki.web.setting',
                        'object_id'=>$item['detail_key'],
                        'object_field'=>'detail_value'));
                    if( $rc['stat'] != 'ok' ) {
                        return $rc;
                    }
                }
            }
        }
*/
    }
    return array('stat'=>'ok');
}
?>
