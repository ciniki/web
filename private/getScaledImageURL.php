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
// image_id:        The ID of the image in the images module to prepare for the website.
// version:         The version of the image, original or thumbnail.  Thumbnail down not
//                  refer to the size, but the square cropped version of the original.
// maxwidth:        The maximum width the rendered photo should be.
// maxheight:       The maximum height the rendered photo should be.
// quality:         The quality setting for jpeg output.  The default if unspecified is 60.
//
// Returns
// -------
//
function ciniki_web_getScaledImageURL($ciniki, $image_id, $version, $maxwidth, $maxheight, $quality='60') {

    if( $maxwidth == 0 && $maxheight == 0 ) {
        $size = 'o';
    } elseif( $maxwidth == 0 ) {
        $size = 'h' . $maxheight;
    } else {
        $size = 'w' . $maxwidth;
    }

    //
    // NOTE: The cache.db was an attempt to speed up EFS(nfs) on Amazon AWS. It did not appear
    //       help performance at all and should not be enabled. The code remains in here incase
    //       it can be useful in the future.
    //

    //
    // Load last_updated date to check against the cache
    //
    $reload_image = 'no';
/*    if( isset($ciniki['config']['ciniki.web']['cache.db']) && $ciniki['config']['ciniki.web']['cache.db'] == 'on' ) {
        $strsql = "SELECT ciniki_images.id, "
            . "ciniki_images.type, "
            . "UNIX_TIMESTAMP(ciniki_images.last_updated) AS last_updated,  "
            . "UNIX_TIMESTAMP(ciniki_web_image_cache.last_updated) AS cache_last_updated "
            . "FROM ciniki_images "
            . "LEFT JOIN ciniki_web_image_cache ON ("
                . "ciniki_images.id = ciniki_web_image_cache.image_id "
                . "AND ciniki_web_image_cache.tnid = '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['tnid']) . "' "
                . "AND ciniki_web_image_cache.size = '" . ciniki_core_dbQuote($ciniki, $size) . "' "
                . ") "
            . "WHERE ciniki_images.id = '" . ciniki_core_dbQuote($ciniki, $image_id) . "' "
            . "AND ciniki_images.tnid = '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['tnid']) . "' "
            . "";
        $reload_image = 'yes';
    } else { */
        $strsql = "SELECT id, type, UNIX_TIMESTAMP(ciniki_images.last_updated) AS last_updated "
            . "FROM ciniki_images "
            . "WHERE id = '" . ciniki_core_dbQuote($ciniki, $image_id) . "' "
            . "AND tnid = '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['tnid']) . "' "
            . "";
/*    } */
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.images', 'image');
    if( $rc['stat'] != 'ok' ) { 
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.103', 'msg'=>'Unable to load image', 'err'=>$rc['err']));
    }
    if( !isset($rc['image']) ) {
        return array('stat'=>'404', 'err'=>array('code'=>'ciniki.web.104', 'msg'=>'The image you requested does not exist.'));
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
//      $filename = '/' . sprintf('%02d', ($ciniki['request']['tnid']%100)) . '/'
//          . sprintf('%07d', $ciniki['request']['tnid'])
//          . '/o/' . sprintf('%010d', $img['id']) . '.' . $extension;
        $filename = '/o/' . sprintf('%010d', $img['id']) . '.' . $extension;
        $size = 'o';
    } elseif( $maxwidth == 0 ) {
//      $filename = '/' . sprintf('%02d', ($ciniki['request']['tnid']%100)) . '/'
//          . sprintf('%07d', $ciniki['request']['tnid'])
//          . '/h' . $maxheight . '/' . sprintf('%010d', $img['id']) . '.' . $extension;
        $filename = '/h' . $maxheight . '/' . sprintf('%010d', $img['id']) . '.' . $extension;
        $size = 'h' . $maxheight;
    } else {
//      $filename = '/' . sprintf('%02d', ($ciniki['request']['tnid']%100)) . '/'
//          . sprintf('%07d', $ciniki['request']['tnid'])
//          . '/w' . $maxwidth . '/' . sprintf('%010d', $img['id']) . '.' . $extension;
        $filename = '/w' . $maxwidth . '/' . sprintf('%010d', $img['id']) . '.' . $extension;
        $size = 'w' . $maxwidth;
    }
    $img_filename = $ciniki['tenant']['web_cache_dir'] . $filename;
    $img_url = $ciniki['tenant']['web_cache_url'] . $filename;
    $img_domain_url = 'http://' . $ciniki['request']['domain'] . $ciniki['tenant']['web_cache_url'] . $filename;

    //
    // Check db for cache details
    //
    if( isset($ciniki['config']['ciniki.web']['cache.db']) && $ciniki['config']['ciniki.web']['cache.db'] == 'on'
        && isset($img['cache_last_updated']) && $img['cache_last_updated'] >= $img['last_updated']
        ) {
        return array('stat'=>'ok', 'url'=>$img_url, 'domain_url'=>$img_domain_url);
    }

    //
    // Check last_updated against the file timestamp, if the file exists
    //
    if( $reload_image == 'yes' || !file_exists($img_filename) || filemtime($img_filename) < $img['last_updated'] ) {

        //
        // Load the image from the database
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'private', 'loadImage');
        $rc = ciniki_images_loadImage($ciniki, $ciniki['request']['tnid'], $img['id'], $version);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $image = $rc['image'];

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
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.105', 'msg'=>'Unable to load image'));
        }

        //
        // Update database
        //
/*        if( isset($ciniki['config']['ciniki.web']['cache.db']) && $ciniki['config']['ciniki.web']['cache.db'] == 'on' ) {
            $strsql = "INSERT INTO ciniki_web_image_cache (tnid, image_id, size, last_updated) "    
                . "VALUES('" . ciniki_core_dbQuote($ciniki, $ciniki['request']['tnid']) . "'"
                . ", '" . ciniki_core_dbQuote($ciniki, $img['id']) . "'"
                . ", '" . ciniki_core_dbQuote($ciniki, $size) . "'"
                . ", UTC_TIMESTAMP()) "
                . "ON DUPLICATE KEY UPDATE last_updated = UTC_TIMESTAMP() "
                . "";
            ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbInsert');
            $rc = ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.tenants');
            if( $rc['stat'] != 'ok' ) {
                error_log('CACHE: Unable to save ' . $img_filename . ' to ciniki_web_cache');
                return $rc;
            }
        } */
    }

    return array('stat'=>'ok', 'url'=>$img_url, 'domain_url'=>$img_domain_url, 'filename'=>$img_filename);
}
?>
