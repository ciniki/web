<?php
//
// Description
// -----------
// This function will return the cache-url to an image, and generate the image cache 
// if it does not exist.  This allows a normal url to be presented to the browser, and
// proper caching in the browser.
//
// Arguments
// ---------
// ciniki:
// image_id:		The ID of the image in the images module to prepare for the website.
// version:			The version of the image, original or thumbnail.  Thumbnail down not
//					refer to the size, but the square cropped version of the original.
// maxwidth:		The maximum width the rendered photo should be.
// maxheight:		The maximum height the rendered photo should be.
// quality:			The quality setting for jpeg output.  The default if unspecified is 60.
//
// Returns
// -------
//
function ciniki_web_getCroppedImageURL($ciniki, $image_id, $version, $args) {

	//
	// Load last_updated date to check against the cache
	//
	$strsql = "SELECT id, type, UNIX_TIMESTAMP(ciniki_images.last_updated) AS last_updated "
		. "FROM ciniki_images "
		. "WHERE id = '" . ciniki_core_dbQuote($ciniki, $image_id) . "' "
		. "AND business_id = '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['business_id']) . "' "
		. "";
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.images', 'image');
	if( $rc['stat'] != 'ok' ) {	
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1272', 'msg'=>'Unable to load image', 'err'=>$rc['err']));
	}
	if( !isset($rc['image']) ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1350', 'msg'=>'Unable to load image'));
	}
	$img = $rc['image'];

	$quality = 60;
	if( isset($args['quality']) && $args['quality'] != '' ) {
		$quality = $args['quality'];
	}

	//
	// Build working path, and final url
	//
	if( $img['type'] == 2 ) {
		$extension = 'png';
	} else {
		$extension = 'jpg';
	}
	$filename = '/' . sprintf('%02d', ($ciniki['request']['business_id']%100)) . '/'
		. sprintf('%07d', $ciniki['request']['business_id'])
		. '/c' . $args['width'] . 'x' . $args['height'] 
		. '/' . sprintf('%010d', $img['id']) . '.' . $extension;
	$img_filename = $ciniki['request']['cache_dir'] . $filename;
	$img_url = $ciniki['request']['cache_url'] . $filename;
	$img_domain_url = 'http://' . $ciniki['request']['domain'] . $ciniki['request']['cache_url'] . $filename;

	//
	// Check last_updated against the file timestamp, if the file exists
	//
//	$utc_offset = date_offset_get(new DateTime);
	if( !file_exists($img_filename) 
		|| filemtime($img_filename) < $img['last_updated'] ) {
		//
		// Load the image from the database
		//
		ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'private', 'loadImage');
		$rc = ciniki_images_loadImage($ciniki, $ciniki['request']['business_id'], $img['id'], $version);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$image = $rc['image'];

		//
		// Decide which way to scale the image, so it is small enough/large enough
		// and doesn't need padding.
		//
		$d = $image->getImageGeometry();
		if( ($d['height']/($d['width']/$args['width'])) < $args['height'] ) {
			$image->scaleImage(0, $args['height']);
		} else {
			$image->scaleImage($args['width'], 0);
		}

		//
		// Crop the image to the proper size and position
		//
		$d = $image->getImageGeometry();
		if( $d['height'] > $args['height'] ) {
			$x = 0;
			if( isset($args['position']) && strncmp('top-', $args['position'], 4) == 0 ) {
				$y = 0;
			} elseif( isset($args['position']) && strncmp('bottom-', $args['position'], 4) == 0 ) {
				$y = $d['height'] - $args['height'];
			} else {
				$y = floor(($d['height'] - $args['height'])/2);
			}
		} else {
			if( isset($args['position']) && strpos($args['position'], '-left') !== FALSE ) {
				$x = 0;
			} elseif( isset($args['position']) && strpos($args['position'], '-right') !== FALSE ) {
				$x = $d['width'] - $args['width'];
			} else {
				$x = floor(($d['width'] - $args['width'])/2);
			}
			$y = 0;
		}
		$image->cropImage($args['width'], $args['height'], $x, $y);

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
			if( $img['type'] == 2 ) {
				$image->setImageFormat('png');
			} else {
				$image->setImageCompressionQuality($quality);
			}
			fwrite($h, $image->getImageBlob());
			fclose($h);
		} else {
			return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1692', 'msg'=>'Unable to load image'));
		}
	}

	return array('stat'=>'ok', 'url'=>$img_url, 'domain_url'=>$img_domain_url);
}
?>
