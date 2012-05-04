<?php
//
// Description
// -----------
// This function will generate the gallery page for the website
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_web_generatePageGalleryThumbnails($ciniki, $settings, $images, $maxlength) {

	//
	// Store the content created by the page
	//
	$content = '';

	foreach($images as $inum => $img) {
		//
		// Check for cached file, if not generate
		//
		$img_filename = $ciniki['request']['cache_dir'] . '/' . ($ciniki['request']['business_id']%100) . '/' 
			. sprintf('%07d', $ciniki['request']['business_id'])
			. '/t' . $maxlength . '/' . sprintf('%010d', $img['image_id']) . '.jpg';
		$img_url = $ciniki['request']['cache_url'] . '/' . ($ciniki['request']['business_id']%100) . '/' 
			. sprintf('%07d', $ciniki['request']['business_id']) 
			. '/t' . $maxlength . '/' . sprintf('%010d', $img['image_id']) . '.jpg';

		//
		// If the image file doesn't exist on disk, create it, or if it's been updated in the database since creation
		//
		if( !file_exists($img_filename) 
			|| filemtime($img_filename) < $img['last_updated'] ) {
			//
			// Load the image from the database
			//
			require_once($ciniki['config']['core']['modules_dir'] . '/images/private/loadImage.php');
			$rc = ciniki_images_loadImage($ciniki, $img['image_id'], 'thumbnail');
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$image = $rc['image'];
			
			$image->thumbnailImage($maxlength, 0);

			//
			// Check directory exists
			//
			if( !file_exists(dirname($img_filename)) ) {
				mkdir(dirname($img_filename), 0755, true);
			}

			//
			// Write the image to the cache file
			//
			$h = fopen($img_filename, 'w');
			if( $h ) {
				$image->setImageCompressionQuality(60);
				fwrite($h, $image->getImageBlob());
				fclose($h);
			}
		}

		$content .= "<div class='image-gallery-thumbnail'><img title='" . $img['title'] . "' "
			. "alt='" . $img['title'] . "' src='$img_url' /></div>";
			// width='" . $maxlength . "px' height='" . $maxlength . "px' /></div>";
	}


	return array('stat'=>'ok', 'content'=>$content);
}
?>
