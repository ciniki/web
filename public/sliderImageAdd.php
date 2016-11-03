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
function ciniki_web_sliderImageAdd(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'slider_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Slider'), 
        'image_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Image'),
        'sequence'=>array('required'=>'no', 'default'=>'0', 'blank'=>'yes', 'name'=>'Sequence'), 
        'object'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'name'=>'Object'), 
        'object_id'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'name'=>'Object ID'), 
        'caption'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Caption'), 
        'url'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'URL'), 
        'image_offset'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'middle-center', 'name'=>'Offset'), 
        'overlay'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'name'=>'Overlay'), 
        'overlay_position'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'name'=>'Overlay Position'), 
        'start_date'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'type'=>'datetimetoutc', 'name'=>'Start Date'),
        'end_date'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'type'=>'datetimetoutc', 'name'=>'End Date'),
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];

    //  
    // Make sure this module is activated, and
    // check permission to run this function for this business
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'checkAccess');
    $rc = ciniki_web_checkAccess($ciniki, $args['business_id'], 'ciniki.web.sliderImageAdd'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    } 

    if( $args['slider_id'] <= 0 ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.172', 'msg'=>'No slider specified'));
    }

    if( $args['sequence'] == 0 ) {
        $strsql = "SELECT MAX(sequence) AS sequence "
            . "FROM ciniki_web_slider_images "
            . "WHERE slider_id = '" . ciniki_core_dbQuote($ciniki, $args['slider_id']) . "' "
            . "AND business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.web', 'max');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['max']['sequence']) && $rc['max']['sequence'] > 0 ) {
            $args['sequence'] = $rc['max']['sequence'] + 1;
        }
    }

    //
    // Add the slider image to the database
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
    return ciniki_core_objectAdd($ciniki, $args['business_id'], 'ciniki.web.slider_image', $args, 0x07);
}
?>
