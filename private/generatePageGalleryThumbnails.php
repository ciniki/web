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
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
// base_url:        The base URL to prepend to all images.  This should be the domain or master domain plus sitename.
//                  It should also include anything that will prepend the image permalink.  eg http://ciniki.com/sitename/gallery
//
//                  It should not contain a trailing slash.
//
// images:          The array of images to use for the gallery.  Each element of the array must contain 'image_id' element
//                  as a reference to an image ID in the ciniki images module.
//
// maxlength:       The maxlength of the thumbnail image, as it will be square.
//
// Returns
// -------
//
function ciniki_web_generatePageGalleryThumbnails($ciniki, $settings, $base_url, $images, $maxlength) {

    //
    // Store the content created by the page
    //
    $content = '';

    foreach($images as $inum => $img) {
        // 
        // Check if image is not specified
        //
        if( $img['image_id'] == 0 ) {
            $img_url = "/ciniki-web-layouts/default/img/noimage_240.png";
        }
        else {
            //
            // Check for cached file, if not generate
            //
//          $img_filename = $ciniki['request']['cache_dir'] . '/' . sprintf('%02d', ($ciniki['request']['tnid']%100)) . '/' 
//              . sprintf('%07d', $ciniki['request']['tnid'])
//              . '/t' . $maxlength . '/' . sprintf('%010d', $img['image_id']) . '.jpg';
//          $img_url = $ciniki['request']['cache_url'] . '/' . sprintf('%02d', ($ciniki['request']['tnid']%100)) . '/' 
//              . sprintf('%07d', $ciniki['request']['tnid']) 
//              . '/t' . $maxlength . '/' . sprintf('%010d', $img['image_id']) . '.jpg';
            $img_filename = $ciniki['tenant']['web_cache_dir'] . '/t' . $maxlength . '/' . sprintf('%010d', $img['image_id']) . '.jpg';
            $img_url = $ciniki['tenant']['web_cache_url'] . '/t' . $maxlength . '/' . sprintf('%010d', $img['image_id']) . '.jpg';

            //
            // If the image file doesn't exist on disk, create it, or if it's been updated in the database since creation
            //
//          $utc_offset = date_offset_get(new DateTime);
            if( !file_exists($img_filename) 
                || filemtime($img_filename) < $img['last_updated'] ) {
                //
                // Load the image from the database
                //
                ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'private', 'loadImage');
                $rc = ciniki_images_loadImage($ciniki, $ciniki['request']['tnid'], $img['image_id'], 'thumbnail');
                if( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.63', 'msg'=>'Unable to generate image: ' . $img['image_id'], 'err'=>$rc['err']));
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
        }

        $content .= "<div class='image-gallery-thumbnail'>"
            . "<a href='" . $base_url . "/" . $img['permalink'] . "'>"
            . '<img title="' . htmlspecialchars(strip_tags($img['title'])) . '" '
            . "alt='" . htmlspecialchars(strip_tags($img['title'])) . "' src='$img_url' /></a></div>";
            // width='" . $maxlength . "px' height='" . $maxlength . "px' /></div>";
    }


    return array('stat'=>'ok', 'content'=>$content);
}
?>
