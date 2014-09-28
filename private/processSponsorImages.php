<?php
//
// Description
// -----------
// This function will generate the html content for the list of images to be displayed.
// The returned content will be HTML with links to the cached images for the browser to retrieve.
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
function ciniki_web_processSponsorImages($ciniki, $settings, $base_url, $sponsors, $maxlength) {

	//
	// Store the content created by the page
	//
	$content = '';

	foreach($sponsors as $snum => $sponsor) {
		// 
		// Check if image is not specified
		//
		if( $sponsor['image_id'] == 0 ) {
			$img_url = "/ciniki-web-layouts/default/img/noimage_240.png";
		}
		else {
			//
			// Check for cached file, if not generate
			//
			$img_filename = $ciniki['request']['cache_dir'] . '/' . sprintf('%02d', ($ciniki['request']['business_id']%100)) . '/' 
				. sprintf('%07d', $ciniki['request']['business_id'])
				. '/o' . $maxlength . '/' . sprintf('%010d', $sponsor['image_id']) . '.jpg';
			$img_url = $ciniki['request']['cache_url'] . '/' . sprintf('%02d', ($ciniki['request']['business_id']%100)) . '/' 
				. sprintf('%07d', $ciniki['request']['business_id']) 
				. '/o' . $maxlength . '/' . sprintf('%010d', $sponsor['image_id']) . '.jpg';

			//
			// If the image file doesn't exist on disk, create it, or if it's been updated in the database since creation
			//
//			$utc_offset = date_offset_get(new DateTime);
			if( !file_exists($img_filename) 
				|| filemtime($img_filename) < $sponsor['last_updated'] ) {
				//
				// Load the image from the database
				//
				ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'private', 'loadImage');
				$rc = ciniki_images_loadImage($ciniki, $ciniki['request']['business_id'], $sponsor['image_id'], 'original');
				if( $rc['stat'] != 'ok' ) {
					return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'2052', 'msg'=>'Unable to generate image: ' . $sponsor['image_id'], 'err'=>$rc['err']));
				}
				$image = $rc['image'];
				
				$image->thumbnailImage($maxlength, $maxlength, true);

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
		}

		$content .= "<div class='sponsor-gallery-thumbnail'>";
		if( isset($sponsor['url']) && $sponsor['url'] != '' ) {
			$content .= "<a target='_blank' href='" . $sponsor['url'] . "'>";
		}
		$content .= "<img title='" . htmlspecialchars(strip_tags($sponsor['title'])) . "' "
			. "alt='" . htmlspecialchars(strip_tags($sponsor['title'])) . "' src='$img_url' />";
		if( isset($sponsor['url']) && $sponsor['url'] != '' ) {
			$content .= "</a>";
		}
		$content .= "</div>";
	}

	return array('stat'=>'ok', 'content'=>$content);
}
?>
