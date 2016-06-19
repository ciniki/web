<?php
//
// Description
// -----------
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:         The ID of the business to add the image to.
// name:                The name of the slider.  
//
// Returns
// -------
// <rsp stat='ok' />
//
function ciniki_web_sliderImageUpdate(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'slider_image_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Slider Image'), 
        'slider_id'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Slider'), 
        'image_id'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Image'),
        'sequence'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Sequence'), 
        'object'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Object'), 
        'object_id'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Object ID'), 
        'caption'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Caption'), 
        'url'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'URL'), 
        'image_offset'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Offset'), 
        'overlay'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Overlay'), 
        'overlay_position'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Overlay Position'), 
        'start_date'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'datetimetoutc', 'name'=>'Start Date'),
        'end_date'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'datetimetoutc', 'name'=>'End Date'),
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
    $rc = ciniki_web_checkAccess($ciniki, $args['business_id'], 'ciniki.web.sliderImageUpdate'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }
    
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
    $rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.web');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Grab the old sequence
    //
    if( isset($args['sequence']) ) {
        $strsql = "SELECT id, slider_id, sequence "
            . "FROM ciniki_web_slider_images "
            . "WHERE id = '" . ciniki_core_dbQuote($ciniki, $args['slider_image_id']) . "' "
            . "AND business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.web', 'item');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( !isset($rc['item']) ) {
            return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1770', 'msg'=>'Unable to find image'));
        }
        $old_sequence = $rc['item']['sequence'];
        $slider_id = $rc['item']['slider_id'];
    }

    //
    // Update the slider image
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
    $rc = ciniki_core_objectUpdate($ciniki, $args['business_id'], 'ciniki.web.slider_image', $args['slider_image_id'], $args, 0x04);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Update any sequences
    //
    if( isset($args['sequence']) ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'sliderUpdateSequences');
        $rc = ciniki_web_sliderUpdateSequences($ciniki, $args['business_id'], 
            $slider_id, $args['sequence'], $old_sequence);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
    }

    //
    // Commit the changes to the database
    //
    $rc = ciniki_core_dbTransactionCommit($ciniki, 'ciniki.web');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Update the last_change date in the business modules
    // Ignore the result, as we don't want to stop user updates if this fails.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'updateModuleChangeDate');
    ciniki_businesses_updateModuleChangeDate($ciniki, $args['business_id'], 'ciniki', 'web');

    return array('stat'=>'ok');
}
?>
