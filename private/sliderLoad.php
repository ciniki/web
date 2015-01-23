<?php
//
// Description
// -----------
// This function will process a list of events, and format the html.
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure, similar to ciniki variable but only web specific information.
// events:			The array of events as returned by ciniki_events_web_list.
// limit:			The number of events to show.  Only 2 events are shown on the homepage.
//
// Returns
// -------
//
function ciniki_web_sliderLoad(&$ciniki, $settings, $business_id, $slider_id) {

	$strsql = "SELECT ciniki_web_sliders.id, "
		. "ciniki_web_sliders.size, "
		. "ciniki_web_sliders.effect, "
		. "ciniki_web_sliders.speed, "
		. "ciniki_web_sliders.resize, "
		. "ciniki_web_slider_images.id AS slider_image_id, "
		. "ciniki_web_slider_images.image_id, "
		. "ciniki_web_slider_images.caption, "
		. "ciniki_web_slider_images.url, "
		. "ciniki_web_slider_images.image_offset, "
		. "UNIX_TIMESTAMP(ciniki_web_slider_images.last_updated) AS last_updated "
		. "FROM ciniki_web_sliders "
		. "LEFT JOIN ciniki_web_slider_images ON ("
			. "ciniki_web_sliders.id = ciniki_web_slider_images.slider_id "
			. "AND ciniki_web_slider_images.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
			. "AND ciniki_web_slider_images.start_date <= UTC_TIMESTAMP() "
			. "AND (ciniki_web_slider_images.end_date = '0000-00-00 00:00:00' "
				. "OR ciniki_web_slider_images.end_date > UTC_TIMESTAMP() "
				. ") "
			. ") "
		. "WHERE ciniki_web_sliders.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
		. "AND ciniki_web_sliders.id = '" . ciniki_core_dbQuote($ciniki, $slider_id) . "' "
		. "ORDER BY ciniki_web_slider_images.sequence "
		. "";
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
	$rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.web', array(
		array('container'=>'sliders', 'fname'=>'id',
			'fields'=>array('size', 'effect', 'speed', 'resize')),
		array('container'=>'images', 'fname'=>'slider_image_id',
			'fields'=>array('image_id', 'caption', 'url', 'image_offset', 'last_updated')),
		));
	if( $rc['stat'] != 'ok' ) {	
		return $rc;
	}

	if( !isset($rc['sliders'][$slider_id]) ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1763', 'msg'=>'Slider not found'));
	}

	return array('stat'=>'ok', 'slider'=>$rc['sliders'][$slider_id]);
}
?>
