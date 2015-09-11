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
function ciniki_web_maps($ciniki) {
	$maps = array();
	$maps['theme'] = array(
		'status'=>array(
			'10'=>'Active',
			'50'=>'Inactive',
		));
	$maps['theme_content'] = array(
		'status'=>array(
			'10'=>'Active',
			'50'=>'Inactive',
		));

	return array('stat'=>'ok', 'maps'=>$maps);
}
?>
