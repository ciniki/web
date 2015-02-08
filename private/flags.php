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
		array('flag'=>array('bit'=>'5', 'name'=>'Quick Links')),
		array('flag'=>array('bit'=>'6', 'name'=>'Info Pages')),	// Single page for now before contact
		array('flag'=>array('bit'=>'7', 'name'=>'Pages')),		// Customizable pages, good for website only customers
		array('flag'=>array('bit'=>'8', 'name'=>'FAQ')),		// Enable FAQ on website
		);

	return array('stat'=>'ok', 'flags'=>$flags);
}
?>
