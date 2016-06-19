<?php
//
// Description
// -----------
// This function updates the theme files in the cache for the business. It will also
// update any settings if required.
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_web_galleryFindNextPrev(&$ciniki, $images, $image_permalink) {

    $rsp = array('stat'=>'ok',
        'first'=>NULL,
        'last'=>NULL,
        'img'=>NULL,
        'next'=>NULL,
        'prev'=>NULL,
        );

    foreach($images as $iid => $image) {
        if( $rsp['first'] == NULL ) {
            $rsp['first'] = $image;
        }
        if( $image['permalink'] == $image_permalink ) {
            $rsp['img'] = $image;
        } elseif( $rsp['next'] == NULL && $rsp['img'] != NULL ) {
            $rsp['next'] = $image;
        } elseif( $rsp['img'] == NULL ) {
            $rsp['prev'] = $image;
        }
        $rsp['last'] = $image;
    }

    if( count($images) == 1 ) {
        $rsp['prev'] = NULL;
        $rsp['next'] = NULL;
    } elseif( $rsp['prev'] == NULL ) {
        // The requested image was the first in the list, set previous to last
        $rsp['prev'] = $rsp['last'];
    } elseif( $rsp['next'] == NULL ) {
        // The requested image was the last in the list, set previous to last
        $rsp['next'] = $rsp['first'];
    }

    return $rsp;
}
?>
