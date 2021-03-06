<?php
//
// Description
// -----------
// This function updates the index for a object.
//
// Arguments
// ---------
// ciniki:
//
// Returns
// -------
//
function ciniki_web_indexObjectBaseURL(&$ciniki, $tnid, $object) {
    
    //
    // Get the base_url for this module, as it may be inside a custom page.
    //
    if( ciniki_core_checkModuleFlags($ciniki, 'ciniki.web', 0x0200) ) {
        $strsql = "SELECT id, parent_id, title, permalink "
            . "FROM ciniki_web_pages "
            . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . "AND page_type = 30 "
            . "AND page_module = '" . ciniki_core_dbQuote($ciniki, $object) . "' "
            . "AND (flags&0x01) = 0x01 "
            . "LIMIT 1 "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.web', 'item');
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'ok');
        }
        if( !isset($rc['item']) ) {
            return array('stat'=>'ok');
        }
        $base_url = '/' . $rc['item']['permalink'];
        
        if( $rc['item']['parent_id'] > 0 ) {
            $parent_id = $rc['item']['parent_id'];
            while( $parent_id != 0 ) {
                $strsql = "SELECT id, parent_id, title, permalink "
                    . "FROM ciniki_web_pages "
                    . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
                    . "AND id = '" . ciniki_core_dbQuote($ciniki, $parent_id) . "' "
                    . "AND (flags&0x01) = 0x01 "
                    . "LIMIT 1 "
                    . "";
                $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.web', 'item');
                if( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'ok');
                }
                if( !isset($rc['item']) ) {
                    return array('stat'=>'ok');
                }
                $base_url = '/' . $rc['item']['permalink'] . $base_url;
                $parent_id = $rc['item']['parent_id'];
            }
        }

        return array('stat'=>'ok', 'base_url'=>$base_url);
    }

    return array('stat'=>'ok');
}
?>
