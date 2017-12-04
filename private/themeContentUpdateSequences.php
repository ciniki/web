<?php
//
// Description
// ===========
// This function will update the sequences for theme content.
//
// Arguments
// =========
// ciniki:
// 
// Returns
// =======
// <rsp stat="ok" />
//
function ciniki_web_themeContentUpdateSequences($ciniki, $tnid, $theme_id, $type, $new_seq, $old_seq) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbUpdate');

    //
    // Get the sequences
    //
    $strsql = "SELECT id, sequence AS number "
        . "FROM ciniki_web_theme_content "
        . "WHERE theme_id = '" . ciniki_core_dbQuote($ciniki, $theme_id) . "' "
        . "AND content_type = '" . ciniki_core_dbQuote($ciniki, $type) . "' "
        . "AND tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "";
    // Use the last_updated to determine which is in the proper position for duplicate numbers
    if( $new_seq < $old_seq || $old_seq == -1) {
        $strsql .= "ORDER BY sequence, last_updated DESC";
    } else {
        $strsql .= "ORDER BY sequence, last_updated ";
    }
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.web', 'sequence');
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'ciniki.web');
        return $rc;
    }
    $cur_number = 1;
    if( isset($rc['rows']) ) {
        $sequences = $rc['rows'];
        foreach($sequences as $sid => $seq) {
            //
            // If the number is not where it's suppose to be, change
            //
            if( $cur_number != $seq['number'] ) {
                $strsql = "UPDATE ciniki_web_theme_content SET "
                    . "sequence = '" . ciniki_core_dbQuote($ciniki, $cur_number) . "' "
                    . ", last_updated = UTC_TIMESTAMP() "
                    . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
                    . "AND id = '" . ciniki_core_dbQuote($ciniki, $seq['id']) . "' "
                    . "";
                $rc = ciniki_core_dbUpdate($ciniki, $strsql, 'ciniki.web');
                if( $rc['stat'] != 'ok' ) {
                    ciniki_core_dbTransactionRollback($ciniki, 'ciniki.web');
                }
                ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.web', 'ciniki_web_history', $tnid, 
                    2, 'ciniki_web_theme_content', $seq['id'], 'sequence', $cur_number);
                $ciniki['syncqueue'][] = array('push'=>'ciniki.web.theme_content', 'args'=>array('id'=>$seq['id']));
                
            }
            $cur_number++;
        }
    }
    
    return array('stat'=>'ok');
}
?>
