<?php
//
// Description
// -----------
// This hook will return the list of active/invisible web collections for use in
// other modules.  If the object/object_id is provided, then it will mark which
// ones are currently used.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:             The ID of the tenant to get the users for.
//
// Returns
// -------
//
function ciniki_web_hooks_webCollectionDeleteObjRef($ciniki, $tnid, $args) {

    if( isset($args['object']) && isset($args['object_id']) && $args['object_id'] != '' ) {
        $strsql = "SELECT id, uuid "
            . "FROM ciniki_web_collection_objrefs "
            . "WHERE object = '" . ciniki_core_dbQuote($ciniki, $args['object']) . "' "
            . "AND object_id = '" . ciniki_core_dbQuote($ciniki, $args['object_id']) . "' "
            . "AND tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.web', 'ref');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( !isset($rc['rows']) ) {
            return array('stat'=>'ok');
        }
        $refs = $rc['rows'];
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectDelete');
        foreach($refs as $ref) {
            $rc = ciniki_core_objectDelete($ciniki, $tnid, 'ciniki.web.collection_objref', 
                $ref['id'], $ref['uuid'], 0x04);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
        }
    }

    return array('stat'=>'ok');
}
?>
