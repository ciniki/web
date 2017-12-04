<?php
//
// Description
// ===========
//
// Arguments
// ---------
// 
// Returns
// -------
// <rsp stat='ok' id='34' />
//
function ciniki_web_privateThemeImageAdd(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'theme_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Theme'), 
        'image_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Image'), 
        'name'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Filename'),
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
    $rc = ciniki_web_checkAccess($ciniki, $args['tnid'], 'ciniki.web.privateThemeImageAdd'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    } 

    //
    // Check if name not specified or blank
    //
    if( !isset($args['name']) || $args['name'] == '' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'hooks', 'imageDetails');
        $rc = ciniki_images_hooks_imageDetails($ciniki, $args['tnid'], array('image_id'=>$args['image_id']));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $image = $rc['image'];
        $args['name'] = $image['original_filename'];
    }

    //
    // Make sure the name is a permalink
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');
    $args['name'] = ciniki_core_makePermalink($ciniki, $args['name'], 'filename');

    //
    // Check the permalink doesn't already exist
    //
    $strsql = "SELECT id, name "
        . "FROM ciniki_web_theme_images "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND name = '" . ciniki_core_dbQuote($ciniki, $args['name']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.web', 'image');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( $rc['num_rows'] > 0 ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.160', 'msg'=>'You already have an image with this name, please choose another name'));
    }

    //
    // Add the image to the database
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
    $rc = ciniki_core_objectAdd($ciniki, $args['tnid'], 'ciniki.web.theme_image', $args, 0x07);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $rsp = $rc;

    //
    // Update theme last_updated
    //
    $strsql = "UPDATE ciniki_web_themes SET last_updated = UTC_TIMESTAMP() "
        . "WHERE id = '" . ciniki_core_dbQuote($ciniki, $args['theme_id']) . "' "
        . "AND tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbUpdate');
    $rc = ciniki_core_dbUpdate($ciniki, $strsql, 'ciniki.web');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    return $rsp;
}
?>
