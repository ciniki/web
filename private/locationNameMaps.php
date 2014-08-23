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
function ciniki_web_locationNameMaps($ciniki) {
	
	$maps = array();
	$maps['canada'] = array('name'=>'Canada',
		'provinces'=>array(
			'ab'=>array('name'=>'Alberta'),
			'bc'=>array('name'=>'British Columbia'),
			'mb'=>array('name'=>'Manitoba'),
			'nb'=>array('name'=>'New Brunswick'),
			'nl'=>array('name'=>'Newfoundland & Labrador'),
			'nfl'=>array('name'=>'Newfoundland & Labrador'),
			'ns'=>array('name'=>'Nova Scotia'),
			'nt'=>array('name'=>'Northwest Territories'),
			'nu'=>array('name'=>'Nunavut'),
			'on'=>array('name'=>'Ontario'),
			'pe'=>array('name'=>'Prince Edward Island'),
			'pei'=>array('name'=>'Prince Edward Island'),
			'qc'=>array('name'=>'Quebec'),
			'sk'=>array('name'=>'Saskatchewan'),
			'yt'=>array('name'=>'Yukon'),
			),
		);
	$maps['usa'] = array('name'=>'United States',
		'provinces'=>array(
			'al'=>array('name'=>'Alabama'),
			'ak'=>array('name'=>'Alaska'),
			'az'=>array('name'=>'Arizona'),
			'ar'=>array('name'=>'Arkansas'),
			'ca'=>array('name'=>'California'),
			'co'=>array('name'=>'Colorado'),
			'ct'=>array('name'=>'Connecticut'),
			'de'=>array('name'=>'Delaware'),
			'dc'=>array('name'=>'District of Columbia'),
			'fl'=>array('name'=>'Florida'),
			'ga'=>array('name'=>'Georgia'),
			'hi'=>array('name'=>'Hawaii'),
			'id'=>array('name'=>'Idaho'),
			'il'=>array('name'=>'Illinois'),
			'in'=>array('name'=>'Indiana'),
			'ia'=>array('name'=>'Iowa'),
			'ks'=>array('name'=>'Kansas'),
			'ky'=>array('name'=>'Kentucky'),
			'la'=>array('name'=>'Louisiana'),
			'me'=>array('name'=>'Maine'),
			'md'=>array('name'=>'Maryland'),
			'ma'=>array('name'=>'Massachusetts'),
			'mi'=>array('name'=>'Michigan'),
			'mn'=>array('name'=>'Minnesota'),
			'ms'=>array('name'=>'Mississippi'),
			'mo'=>array('name'=>'Missouri'),
			'mt'=>array('name'=>'Montana'),
			'ne'=>array('name'=>'Nebraska'),
			'nv'=>array('name'=>'Nevada'),
			'nh'=>array('name'=>'New Hampshire'),
			'nj'=>array('name'=>'New Jersey'),
			'nm'=>array('name'=>'New Mexico'),
			'ny'=>array('name'=>'New York'),
			'nc'=>array('name'=>'North Carolina'),
			'nd'=>array('name'=>'North Dakota'),
			'oh'=>array('name'=>'Ohio'),
			'ok'=>array('name'=>'Oklahoma'),
			'or'=>array('name'=>'Oregon'),
			'pa'=>array('name'=>'Pennsylvania'),
			'ri'=>array('name'=>'Rhode Island'),
			'sc'=>array('name'=>'South Carolina'),
			'sd'=>array('name'=>'South Dakota'),
			'tn'=>array('name'=>'Tennessee'),
			'tx'=>array('name'=>'Texas'),
			'ut'=>array('name'=>'Utah'),
			'vt'=>array('name'=>'Vermont'),
			'va'=>array('name'=>'Virginia'),
			'wa'=>array('name'=>'Washington'),
			'wv'=>array('name'=>'West Virginia'),
			'wi'=>array('name'=>'Wisconsin'),
			'wy'=>array('name'=>'Wyoming'),
			),
		);
	$maps['mexico'] = array('name'=>'Mexico');
	
	return array('stat'=>'ok', 'maps'=>$maps);
}
?>
