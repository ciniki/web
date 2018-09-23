<?php
//
// Description
// ===========
// This method will return the file in it's binary form.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:     The ID of the tenant the requested file belongs to.
// file_id:         The ID of the file to be downloaded.
//
// Returns
// -------
// Binary file.
//
function ciniki_web_pageFileDownload($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'file_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'File'), 
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];
    
    //  
    // Make sure this module is activated, and
    // check permission to run this function for this tenant
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'checkAccess');
    $rc = ciniki_web_checkAccess($ciniki, $args['tnid'], 'ciniki.web.pageFileDownload'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    //
    // Get the uuid for the file
    //
    $strsql = "SELECT ciniki_web_page_files.id, "
        . "ciniki_web_page_files.uuid, "
        . "ciniki_web_page_files.name, "
        . "ciniki_web_page_files.extension, "
        . "ciniki_web_page_files.binary_content "
        . "FROM ciniki_web_page_files "
        . "WHERE id = '" . ciniki_core_dbQuote($ciniki, $args['file_id']) . "' "
        . "AND tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.web', 'file');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['file']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.143', 'msg'=>'Unable to find file'));
    }
    $file = $rc['file'];
    $filename = $rc['file']['name'] . '.' . $rc['file']['extension'];

    //
    // Load the file contents
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'storageFileLoad');
    $rc = ciniki_core_storageFileLoad($ciniki, $args['tnid'], 'ciniki.web.page_file', array('subdir'=>'pagefiles', 'uuid'=>$file['uuid']));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $binary_content = $rc['binary_content'];

    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 
    header("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT"); 
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');

    if( $file['extension'] == 'pdf' ) {
        header('Content-Type: application/pdf');
    } elseif( $file['extension'] == 'mp3' ) {
        header('Content-Type: audio/mpeg');
    } else {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.144', 'msg'=>'Unsupported file type'));
    }
    // Specify Filename
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Content-Length: ' . strlen($binary_content));
    header('Cache-Control: max-age=0');

    print $binary_content;
    
    return array('stat'=>'exit');
}
?>
