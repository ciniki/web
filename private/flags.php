<?php
//
// Description
// -----------
// The module flags
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_web_flags($ciniki, $modules) {
	$flags = array(
		// 0x01
		array('flag'=>array('bit'=>'1', 'name'=>'Custom Pages')),
		array('flag'=>array('bit'=>'2', 'name'=>'Sliders')),
		array('flag'=>array('bit'=>'3', 'name'=>'Contact Form')),
		array('flag'=>array('bit'=>'4', 'name'=>'Collections')),
		// 0x10
//		array('flag'=>array('bit'=>'5', 'name'=>'')),
//		array('flag'=>array('bit'=>'6', 'name'=>'')),
//		array('flag'=>array('bit'=>'7', 'name'=>'')),
//		array('flag'=>array('bit'=>'8', 'name'=>'')),
		);

	return array('stat'=>'ok', 'flags'=>$flags);
}
?>
