<?php
//
// Description
// -----------
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:         The ID of the tenant to add the slider image to.
// slider_image_id:     The ID of the slider image to get.
//
// Returns
// -------
//
function ciniki_web_sliderImageGet($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'slider_image_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Slider Image'),
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
    $rc = ciniki_web_checkAccess($ciniki, $args['tnid'], 'ciniki.web.sliderImageGet'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    //
    // Get the settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'intlSettings');
    $rc = ciniki_tenants_intlSettings($ciniki, $args['tnid']);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $intl_timezone = $rc['settings']['intl-default-timezone'];
    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
    $date_format = ciniki_users_dateFormat($ciniki, 'php');

    //
    // Get the main information
    //
    $strsql = "SELECT id, "
        . "slider_id, "
        . "image_id, "
        . "sequence, "
        . "object, "
        . "object_id, "
        . "caption, "
        . "url, "
        . "image_offset, "
        . "overlay, "
        . "overlay_position, "
        . "start_date, "
        . "end_date "
        . "FROM ciniki_web_slider_images "
        . "WHERE ciniki_web_slider_images.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND ciniki_web_slider_images.id = '" . ciniki_core_dbQuote($ciniki, $args['slider_image_id']) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
    $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.web', array(
        array('container'=>'images', 'fname'=>'id', 'name'=>'image',
            'fields'=>array('id', 'slider_id', 'image_id', 'sequence', 'object', 'object_id',
                'caption', 'url', 'image_offset', 'overlay', 'overlay_position', 'start_date', 'end_date'),
                'utctotz'=>array('start_date'=>array('timezone'=>$intl_timezone, 'format'=>$date_format),
                    'end_date'=>array('timezone'=>$intl_timezone, 'format'=>$date_format))),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['images']) ) {
        return array('stat'=>'ok', 'err'=>array('code'=>'ciniki.web.173', 'msg'=>'Unable to find slider image'));
    }
    $image = $rc['images'][0]['image'];

    return array('stat'=>'ok', 'image'=>$image);
}
?>
