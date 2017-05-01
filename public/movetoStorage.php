<?php
//
// Description
// ===========
// This method will move all the newsletters for a business to ciniki-storage.
//
// Arguments
// ---------
// 
// Returns
// -------
//
function ciniki_web_movetoStorage(&$ciniki) {

    //
    // Sysadmins are allowed full access
    //
    if( ($ciniki['session']['user']['perms'] & 0x01) != 0x01 ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.179', 'msg'=>'Permission Denied'));
    }

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');

    $strsql = "SELECT ciniki_web_page_files.id, "
        . "ciniki_businesses.id AS business_id, "
        . "ciniki_businesses.uuid AS business_uuid, "
        . "ciniki_web_page_files.uuid, "
        . "ciniki_web_page_files.binary_content "
        . "FROM ciniki_web_page_files, ciniki_businesses "
        . "WHERE ciniki_web_page_files.business_id = ciniki_businesses.id "
        . "ORDER BY ciniki_web_page_files.business_id "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.newsletters', 'newsletter');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    $files = $rc['rows'];
    foreach($files as $file) {
        if( $file['binary_content'] != '' ) {
            $args = array();
//            $args['checksum'] = crc32($file['binary_content']);
            $storage_dirname = $ciniki['config']['ciniki.core']['storage_dir'] . '/'
                . $file['business_uuid'][0] . '/' . $file['business_uuid']
                . "/ciniki.web/pagefiles/"
                . $file['uuid'][0];
            $storage_filename = $storage_dirname . '/' . $file['uuid'];
            if( !is_dir($storage_dirname) ) {
                if( !mkdir($storage_dirname, 0700, true) ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.179', 'msg'=>'Unable to add file'));
                }
            }
            if( file_exists($storage_filename) ) {
                error_log('FILE[' . $file['id'] . ']: file already exists');
            } elseif( file_put_contents($storage_filename, $file['binary_content']) === FALSE ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.180', 'msg'=>'Unable to add file'));
            }
//            $rc = ciniki_core_objectUpdate($ciniki, $file['business_id'], 'ciniki.web.page_file', $file['id'], $args, 0x07);
        } else {
            error_log('FILE[' . $file['id'] . ']: binary_content is empty');
        }
    }

    return array('stat'=>'ok');
}
?>
