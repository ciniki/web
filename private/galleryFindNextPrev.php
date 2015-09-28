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
		if( $first == NULL ) {
			$first = $image;
		}
		if( $image['permalink'] == $image_permalink ) {
			$img = $image;
		} elseif( $next == NULL && $img != NULL ) {
			$next = $image;
		} elseif( $img == NULL ) {
			$prev = $image;
		}
		$last = $image;
	}

	if( count($images) == 1 ) {
		$prev = NULL;
		$next = NULL;
	} elseif( $prev == NULL ) {
		// The requested image was the first in the list, set previous to last
		$prev = $last;
	} elseif( $next == NULL ) {
		// The requested image was the last in the list, set previous to last
		$next = $first;
	}

	return $rsp;
}
?>
