<?php
//
// Description
// -----------
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:			The ID of the business to add the image to.
// name:				The name of the slider.  
//
// Returns
// -------
// <rsp stat='ok' />
//
function ciniki_web_sliderUpdate(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'slider_id'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Slider'), 
        'name'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Name'), 
        'size'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Size'), 
        'effect'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Effect'), 
        'speed'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Speed'), 
        'resize'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Resize'), 
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
    $rc = ciniki_web_checkAccess($ciniki, $args['business_id'], 'ciniki.web.sliderUpdate'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }

	//
	// Update the slider in the database
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
	return ciniki_core_objectUpdate($ciniki, $args['business_id'], 'ciniki.web.slider', $args['slider_id'], $args);
}
?>
