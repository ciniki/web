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
function ciniki_web_sync_objects($ciniki, &$sync, $tnid, $args) {
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'objects');
    return ciniki_web_objects($ciniki);
}
?>
