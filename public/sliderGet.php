<?php
//
// Description
// -----------
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:			The ID of the business to add the slider image to.
// slider_image_id:		The ID of the slider image to get.
//
// Returns
// -------
//
function ciniki_web_sliderGet($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
		'slider_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Slider'),
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
    $rc = ciniki_web_checkAccess($ciniki, $args['business_id'], 'ciniki.web.sliderGet'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

	//
	// Load the object
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectGet');
	$rc = ciniki_core_objectGet($ciniki, $args['business_id'], 'ciniki.web.slider', $args['slider_id']);
	if( $rc['stat'] != 'ok' ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1761', 'msg'=>'Unable to find the slider image you requested.', 'err'=>$rc['err']));
	}
	$slider = $rc['slider'];

	//
	// Load the images
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'private', 'loadCacheThumbnail');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectGetSubs');
	$rc = ciniki_core_objectGetSubs($ciniki, $args['business_id'], 'ciniki.web.slider', $args['slider_id'], 'ciniki.web.slider_image');
	if( $rc['stat'] != 'ok' ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1761', 'msg'=>'Unable to find the slider image you requested.', 'err'=>$rc['err']));
	}
	if( isset($rc['images']) ) {
		$images = $rc['images'];
		$slider['images'] = array();
		foreach($images as $sid => $image) {
			$image = $image['image'];
			$rc = ciniki_images_loadCacheThumbnail($ciniki, $args['business_id'], $image['image_id'], 75);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			//
			// Attach the image data
			//
			$slider['images'][] = array('image'=>array('id'=>$image['id'], 'image_id'=>$image['image_id'],
				'title'=>'',
				'image_data'=>'data:image/jpg;base64,' . base64_encode($rc['image'])));
			
		}
	} else {
		$slider['images'] = array();
	}

	return array('stat'=>'ok', 'slider'=>$slider);
}
?>
