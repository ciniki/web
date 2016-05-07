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
function ciniki_web_getPaddedImageURL($ciniki, $image_id, $version, $maxwidth, $maxheight, $padding_color, $quality='60') {
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
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'3135', 'msg'=>'Unable to load image', 'err'=>$rc['err']));
	}
	if( !isset($rc['image']) ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'3136', 'msg'=>'Unable to load image'));
	}
	$img = $rc['image'];

	//
	// Build working path, and final url
	//
	if( $img['type'] == 2 ) {
		$extension = 'png';
	} else {
		$extension = 'jpg';
	}
	if( $maxwidth == 0 && $maxheight == 0 ) {
//		$filename = '/' . sprintf('%02d', ($ciniki['request']['business_id']%100)) . '/'
//			. sprintf('%07d', $ciniki['request']['business_id'])
//			. '/po/' . sprintf('%010d', $img['id']) . '.' . $extension;
		$filename = '/po/' . sprintf('%010d', $img['id']) . '.' . $extension;
	} elseif( $maxwidth == 0 ) {
//		$filename = '/' . sprintf('%02d', ($ciniki['request']['business_id']%100)) . '/'
//			. sprintf('%07d', $ciniki['request']['business_id'])
//			. '/ph' . $maxheight . '/' . sprintf('%010d', $img['id']) . '.' . $extension;
		$filename = '/ph' . $maxheight . '/' . sprintf('%010d', $img['id']) . '.' . $extension;
	} else {
//		$filename = '/' . sprintf('%02d', ($ciniki['request']['business_id']%100)) . '/'
//			. sprintf('%07d', $ciniki['request']['business_id'])
//			. '/pw' . $maxwidth . '/' . sprintf('%010d', $img['id']) . '.' . $extension;
		$filename = '/pw' . $maxwidth . '/' . sprintf('%010d', $img['id']) . '.' . $extension;
	}
	$img_filename = $ciniki['business']['web_cache_dir'] . $filename;
	$img_url = $ciniki['business']['web_cache_url'] . $filename;
	$img_domain_url = 'http://' . $ciniki['request']['domain'] . $ciniki['business']['web_cache_url'] . $filename;

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
        // Padded to square image
        //
        if( $image->getImageWidth() > $image->getImageHeight() ) {
            $image->borderImage($padding_color, 0, ($image->getImageWidth() - $image->getImageHeight())/2);
        } elseif( $image->getImageHeight() > $image->getImageWidth() ) {
            $image->borderImage($padding_color, ($image->getImageHeight() - $image->getImageWidth())/2, 0);
        }

		//
		// Scale image
		//
		if( $maxwidth > 0 || $maxheight > 0 ) {
			$image->scaleImage($maxwidth, $maxheight);
		}

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
			if( $img['type'] == 2 ) {
				$image->setImageFormat('png');
			} else {
				$image->setImageCompressionQuality($quality);
			}
			fwrite($h, $image->getImageBlob());
			fclose($h);
		} else {
			return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'3134', 'msg'=>'Unable to load image'));
		}
	}

	return array('stat'=>'ok', 'url'=>$img_url, 'domain_url'=>$img_domain_url);
}
?>
