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
		array('flag'=>array('bit'=>'1', 'name'=>'Custom Pages')),
		array('flag'=>array('bit'=>'2', 'name'=>'Sliders')),
		);

	return array('stat'=>'ok', 'flags'=>$flags);
}
?>
