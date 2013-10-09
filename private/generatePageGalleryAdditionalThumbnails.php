<?php
//
// Description
// -----------
// This function will generate the html content for the thumbnails of additional images
// for an artcatalog item.  These will be swapped out by javascript.
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure, similar to ciniki variable but only web specific information.
// base_url:		The base URL to prepend to all images.  This should be the domain or master domain plus sitename.
//					It should also include anything that will prepend the image permalink.  eg http://ciniki.com/sitename/gallery
//
//					It should not contain a trailing slash.
//
// images:			The array of images to use for the gallery.  Each element of the array must contain 'image_id' element
//					as a reference to an image ID in the ciniki images module.
//
// maxlength:		The maxlength of the thumbnail image, as it will be square.
//
// Returns
// -------
//
function ciniki_web_generatePageGalleryAdditionalThumbnails($ciniki, $settings, $base_url, $images, $maxlength) {

	//
	// Store the content created by the page
	//
	$content = '';

	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
	foreach($images as $inum => $img) {
		//
		// Check for cached file, if not generate
		//
		$img_filename = $ciniki['request']['cache_dir'] . '/' . sprintf('%02d', ($ciniki['request']['business_id']%100)) . '/' 
			. sprintf('%07d', $ciniki['request']['business_id'])
			. '/t' . $maxlength . '/' . sprintf('%010d', $img['image_id']) . '.jpg';
		$img_url = $ciniki['request']['cache_url'] . '/' . sprintf('%02d', ($ciniki['request']['business_id']%100)) . '/' 
			. sprintf('%07d', $ciniki['request']['business_id']) 
			. '/t' . $maxlength . '/' . sprintf('%010d', $img['image_id']) . '.jpg';

		//
		// If the image file doesn't exist on disk, create it, or if it's been updated in the database since creation
		//
		$utc_offset = date_offset_get(new DateTime);
		if( $img['id'] > 0 && !file_exists($img_filename) 
			|| (filemtime($img_filename) - $utc_offset) < $img['last_updated'] ) {
			//
			// Load the image from the database
			//
			ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'private', 'loadImage');
			$rc = ciniki_images_loadImage($ciniki, $img['image_id'], 'thumbnail');
			if( $rc['stat'] != 'ok' ) {
				return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1337', 'msg'=>'Unable to generate image: ' . $img['image_id'], 'err'=>$rc['err']));
			}
			$image = $rc['image'];
			
			$image->thumbnailImage($maxlength, 0);

			//
			// Check if they image is marked as sold, and add red dot
			//
			if( isset($img['sold']) && $img['sold'] == 'yes' ) {
				$draw = new ImagickDraw();
				$draw->setFillColor('red');
				$draw->setStrokeColor(new ImagickPixel('white') );
				$size = $maxlength/20;
				$draw->circle($maxlength-($size*2), $maxlength-($size*2), $maxlength-$size, $maxlength-$size);
				$image->drawImage($draw);
			}

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

		//
		// Make sure the images have been generated on disk
		//
		$rc = ciniki_web_getScaledImageURL($ciniki, $img['image_id'], 'original', 0, 600);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$full_img_url = $rc['url'];

		$content .= "<div class='image-gallery-thumbnail'>"
			. "<a onclick='gallery_swap_image(\"$full_img_url\");return false;' href='#'>"
			. "<img title='" . $img['title'] . "' "
			. "alt='" . $img['title'] . "' src='$img_url' /></a></div>";
	}

	return array('stat'=>'ok', 'content'=>$content);
}
?>
