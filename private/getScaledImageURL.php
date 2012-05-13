<?php
//
// Description
// -----------
// This function will return the cache-url to an image, and generate the image cache if it does not exist.
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_web_getScaledImageURL($ciniki, $image_id, $version, $maxwidth, $maxheight, $quality='60') {

	//
	// Load last_updated date to check against the cache
	//
	$strsql = "SELECT id, UNIX_TIMESTAMP(ciniki_images.last_updated) AS last_updated "
		. "FROM ciniki_images "
		. "WHERE id = '" . ciniki_core_dbQuote($ciniki, $image_id) . "' "
		. "AND business_id = '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['business_id']) . "' "
		. "";
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'images', 'image');
	if( $rc['stat'] != 'ok' ) {	
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'644', 'msg'=>'Unable to load image', 'err'=>$rc['err']));
	}
	if( !isset($rc['image']) ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'645', 'msg'=>'Unable to load image'));
	}
	$img = $rc['image'];

	//
	// Build working path, and final url
	//
	if( $maxwidth == 0 ) {
		$filename = '/' . sprintf('%02d', ($ciniki['request']['business_id']%100)) . '/'
			. sprintf('%07d', $ciniki['request']['business_id'])
			. '/h' . $maxheight . '/' . sprintf('%010d', $img['id']) . '.jpg';
	} else {
		$filename = '/' . sprintf('%02d', ($ciniki['request']['business_id']%100)) . '/'
			. sprintf('%07d', $ciniki['request']['business_id'])
			. '/w' . $maxwidth . '/' . sprintf('%010d', $img['id']) . '.jpg';
	}
	$img_filename = $ciniki['request']['cache_dir'] . $filename;
	$img_url = $ciniki['request']['cache_url'] . $filename;

	//
	// Check last_updated against the file timestamp, if the file exists
	//
	$utc_offset = date_offset_get(new DateTime);
	if( !file_exists($img_filename) 
		|| (filemtime($img_filename) - $utc_offset) < $img['last_updated'] ) {
		//
		// Load the image from the database
		//
		require_once($ciniki['config']['core']['modules_dir'] . '/images/private/loadImage.php');
		$rc = ciniki_images_loadImage($ciniki, $img['id'], $version);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$image = $rc['image'];

		//
		// Scale image
		//
		$image->scaleImage($maxwidth, $maxheight);

		//
		// Apply a border
		//
		// $image->borderImage("rgb(255,255,255)", 10, 10);

		//
		// Check if directory exists
		//
		if( !file_exists(dirname($img_filename)) ) {
			mkdir(dirname($img_filename), 0755, true);
		}

		//
		// Write the file
		//
		$h = fopen($img_filename, 'w');
		if( $h ) {
			$image->setImageCompressionQuality($quality);
			fwrite($h, $image->getImageBlob());
			fclose($h);
		} else {
			return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'643', 'msg'=>'Unable to load image'));
		}
	}

	return array('stat'=>'ok', 'url'=>$img_url, );
}
?>
