<?php
//
// Description
// -----------
// This function will return a list of user interface settings for the module.
//
// Arguments
// ---------
// ciniki:
// tnid:     The ID of the tenant to get events for.
//
// Returns
// -------
//
function ciniki_web_hooks_privateThemes($ciniki, $tnid, $args) {

    if( !isset($ciniki['tenant']['modules']['ciniki.web']['flags']) || ($ciniki['tenant']['modules']['ciniki.web']['flags']&0x0100) == 0 ) {
        return array('stat'=>'ok'); 
    }

    $strsql = "SELECT ciniki_web_themes.id, "
        . "ciniki_web_themes.name "
        . "FROM ciniki_web_themes "
        . "WHERE ciniki_web_themes.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.web', array(
        array('container'=>'themes', 'fname'=>'id', 'fields'=>array('id', 'name')),
        ));
    return $rc;
}
?>
