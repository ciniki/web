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
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
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
    // check permission to run this function for this business
    //  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'checkAccess');
    $rc = ciniki_web_checkAccess($ciniki, $args['business_id'], 'ciniki.web.privateThemeImageAdd'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    } 

	//
	// Check if name not specified or blank
	//
	if( !isset($args['name']) || $args['name'] == '' ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'hooks', 'imageDetails');
		$rc = ciniki_images_hooks_imageDetails($ciniki, $args['business_id'], array('image_id'=>$args['image_id']));
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
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND name = '" . ciniki_core_dbQuote($ciniki, $args['name']) . "' "
		. "";
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.web', 'image');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( $rc['num_rows'] > 0 ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'2548', 'msg'=>'You already have an image with this name, please choose another name'));
	}

	//
	// Add the image to the database
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
	return ciniki_core_objectAdd($ciniki, $args['business_id'], 'ciniki.web.theme_image', $args, 0x07);
}
?>
