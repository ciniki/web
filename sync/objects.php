<?php
//
// Description
// -----------
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_web_sync_objects($ciniki, &$sync, $business_id, $args) {
	
	$objects = array();
	$objects['content'] = array(
		'name'=>'Web Content',
		'table'=>'ciniki_web_content',
		'type'=>'settings',
		'history_table'=>'ciniki_web_history',
		);
	$objects['setting'] = array(
		'type'=>'settings',
		'name'=>'Web Setting',
		'table'=>'ciniki_web_settings',
		'refs'=>array(
			'page-home-image'=>array('ref'=>'ciniki.images.image'),
			'page-about-image'=>array('ref'=>'ciniki.images.image'),
			'page-exhibitions-exhibition'=>array('ref'=>'ciniki.exhibitions.exhibition'),
			'site-header-image'=>array('ref'=>'ciniki.images.image'),
			),
		'history_table'=>'ciniki_web_history',
		);
	$objects['faq'] = array(
		'name'=>'Web FAQ',
		'table'=>'ciniki_web_faqs',
		'fields'=>array(
			'flags'=>array(),
			'category'=>array(),
			'question'=>array(),
			'answer'=>array(),
			),
		'history_table'=>'ciniki_web_history',
		);
	
	return array('stat'=>'ok', 'objects'=>$objects);
}
?>
