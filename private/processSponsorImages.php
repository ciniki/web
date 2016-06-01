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
function ciniki_web_processSponsorImages($ciniki, $settings, $base_url, $sponsors, $size) {

	//
	// Store the content created by the page
	//
	$content = '';

	$maxlength = 125;
	$size_class = 'small';
	switch($size) {
		case '10': $maxlength = 100; $size_class = 'tiny'; break;
		case '20': $maxlength = 150; $size_class = 'small'; break;
		case '30': $maxlength = 200; $size_class = 'medium'; break;
		case '40': $maxlength = 250; $size_class = 'large'; break;
		case '50': $maxlength = 300; $size_class = 'xlarge'; break;
	}

	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processURL');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'private', 'loadImage');

	foreach($sponsors as $snum => $sponsor) {
		if( isset($sponsor['sponsor']) ) { $sponsor = $sponsor['sponsor']; }
		// 
		// Check if image is not specified
		//
		if( $sponsor['image_id'] == 0 ) {
			continue;
		}
		else {
			//
			// Check for cached file, if not generate
			//
//			$img_filename = $ciniki['request']['cache_dir'] . '/' . sprintf('%02d', ($ciniki['request']['business_id']%100)) . '/' 
//				. sprintf('%07d', $ciniki['request']['business_id'])
//				. '/o' . $maxlength . '/' . sprintf('%010d', $sponsor['image_id']) . '.jpg';
			$img_filename = $ciniki['business']['web_cache_dir'] . '/o' . $maxlength . '/' . sprintf('%010d', $sponsor['image_id']) . '.jpg';
//			$img_url = $ciniki['request']['cache_url'] . '/' . sprintf('%02d', ($ciniki['request']['business_id']%100)) . '/' 
//				. sprintf('%07d', $ciniki['request']['business_id']) 
//				. '/o' . $maxlength . '/' . sprintf('%010d', $sponsor['image_id']) . '.jpg';
			$img_url = $ciniki['business']['web_cache_url'] . '/o' . $maxlength . '/' . sprintf('%010d', $sponsor['image_id']) . '.jpg';

			//
			// If the image file doesn't exist on disk, create it, or if it's been updated in the database since creation
			//
//			$utc_offset = date_offset_get(new DateTime);
			if( !file_exists($img_filename) 
				|| filemtime($img_filename) < $sponsor['last_updated'] ) {
				//
				// Load the image from the database
				//
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

		$content .= "<div class='sponsor-gallery-thumbnail sponsor-gallery-thumbnail-$size_class'>";
		if( isset($sponsor['url']) ) {
			$rc = ciniki_web_processURL($ciniki, $sponsor['url']);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$url = $rc['url'];
			$display_url = $rc['display'];
		} else if( isset($sponsor['permalink']) ) {
			$url = $base_url . '/' . $sponsor['permalink'];
		} else {
			$url = '';
		}

		if( isset($url) && $url != '' ) {
			if( isset($display_url) ) {
				$content .= "<a target='_blank' href='" . $url . "'>";
			} else {
				$content .= "<a href='" . $url . "'>";
			}
		}
		$content .= "<img title='" . htmlspecialchars(strip_tags($sponsor['title'])) . "' "
			. "alt='" . htmlspecialchars(strip_tags($sponsor['title'])) . "' src='$img_url' />";
		if( isset($url) && $url != '' ) {
			$content .= "</a>";
		}
		$content .= "</div>";
	}

	return array('stat'=>'ok', 'content'=>$content);
}
?>
