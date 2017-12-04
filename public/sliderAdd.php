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
//
function ciniki_web_sliderAdd(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'name'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Name'), 
        'size'=>array('required'=>'no', 'default'=>'medium', 'blank'=>'no', 'name'=>'Size'), 
        'effect'=>array('required'=>'no', 'default'=>'slide', 'blank'=>'yes', 'name'=>'Effect'), 
        'speed'=>array('required'=>'no', 'default'=>'medium', 'blank'=>'yes', 'name'=>'Speed'), 
        'resize'=>array('required'=>'no', 'default'=>'cropped', 'blank'=>'yes', 'name'=>'Resize'), 
        'image_offset'=>array('required'=>'no', 'default'=>'middle-center', 'blank'=>'yes', 'name'=>'Position'), 
        'images'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'type'=>'idlist', 'name'=>'Images'),
        'modules'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'name'=>'Modules'),
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
    $rc = ciniki_web_checkAccess($ciniki, $args['tnid'], 'ciniki.web.sliderAdd'); 
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
    // Add the slider to the database
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
    $rc = ciniki_core_objectAdd($ciniki, $args['tnid'], 'ciniki.web.slider', $args, 0x04);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $slider_id = $rc['id'];

    $rsp = array('stat'=>'ok', 'id'=>$slider_id);

    //
    // Add the images
    //
    if( isset($args['images']) && is_array($args['images']) ) {
        $rsp['images'] = array();
        $sequence = 1;
        foreach($args['images'] as $image_id) {
            $i_args = array(
                'slider_id'=>$slider_id,
                'image_id'=>$image_id,
                'sequence'=>$sequence++,
                'caption'=>'',
                'object'=>'',
                'object_id'=>'',
                'url'=>'',
                'image_offset'=>$args['image_offset'],
                'overlay'=>'',
                'overlay_position'=>'',
                'start_date'=>'',
                'end_date'=>'',
                );
            $rc = ciniki_core_objectAdd($ciniki, $args['tnid'], 'ciniki.web.slider_image', $i_args, 0x04);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $rsp['images'][] = array('image'=>array('id'=>$rc['id'], 'image_id'=>$image_id));
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
    // Update the last_change date in the tenant modules
    // Ignore the result, as we don't want to stop user updates if this fails.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'updateModuleChangeDate');
    ciniki_tenants_updateModuleChangeDate($ciniki, $args['tnid'], 'ciniki', 'web');

    return $rsp;
}
?>
