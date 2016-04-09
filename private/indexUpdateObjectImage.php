<?php
//
// Description
// -----------
// This function will update the objects image in the web cache
//
// Arguments
// ---------
// ciniki:
//
// Returns
// -------
//
function ciniki_web_indexUpdateObjectImage($ciniki, $business_id, $image_id, $index_id) {

    if( $image_id <= 0 ) {
        return array('stat'=>'ok');
    }

	//
	// Load last_updated date to check against the cache
	//
	$strsql = "SELECT id, type, UNIX_TIMESTAMP(ciniki_images.last_updated) AS last_updated "
		. "FROM ciniki_images "
		. "WHERE id = '" . ciniki_core_dbQuote($ciniki, $image_id) . "' "
		. "AND business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
		. "";
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.images', 'image');
	if( $rc['stat'] != 'ok' ) {	
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'3277', 'msg'=>'Unable to load image', 'err'=>$rc['err']));
	}
	if( !isset($rc['image']) ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'3278', 'msg'=>'Unable to load image'));
	}
	$img = $rc['image'];

	if( $img['type'] == 2 ) {
		$extension = 'png';
	} else {
		$extension = 'jpg';
	}

    //
    // Get the cache directory
    //
    $filename = $ciniki['config']['ciniki.core']['root_dir'] . '/ciniki-mods/web/cache/'
        . '/' . sprintf("%02d/%07d", $business_id, $business_id) 
        . '/search/' . sprintf("%010d", $image_id) . '.' . $extension;

    if( !file_exists($filename) || filemtime($filename) < $img['last_updated'] ) {
		//
		// Load the image from the database
		//
		ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'private', 'loadImage');
		$rc = ciniki_images_loadImage($ciniki, $business_id, $img['id'], 'thumbnail');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$image = $rc['image'];

		//
		// Scale image
		//
        $image->scaleImage(240, 0);

		//
		// Apply a border
		//
		// $image->borderImage("rgb(255,255,255)", 10, 10);

		//
		// Check if directory exists
		//
		if( !file_exists(dirname($filename)) ) {
			mkdir(dirname($filename), 0755, true);
		}

		//
		// Write the file
		//
		$h = fopen($filename, 'w');
		if( $h ) {
			if( $img['type'] == 2 ) {
				$image->setImageFormat('jpg');
			} 
            $image->setImageCompressionQuality(60);
			fwrite($h, $image->getImageBlob());
			fclose($h);
		} else {
			return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'3279', 'msg'=>'Unable to load image'));
		}
    }

    return array('stat'=>'ok');
}
?>
