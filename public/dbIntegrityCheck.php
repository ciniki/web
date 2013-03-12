<?php
//
// Description
// -----------
// This function will go through the history of the ciniki.artcatalog module and 
// add missing history elements.
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_web_dbIntegrityCheck(&$ciniki) {
	//
	// Find all the required and optional arguments
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
	$rc = ciniki_core_prepareArgs($ciniki, 'no', array(
		'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
		'fix'=>array('required'=>'no', 'default'=>'no', 'name'=>'Fix Problems'),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$args = $rc['args'];
	
	//
	// Check access to business_id as owner, or sys admin
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'checkAccess');
	$rc = ciniki_web_checkAccess($ciniki, $args['business_id'], 'ciniki.web.dbIntegrityCheck', 0);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbUpdate');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDelete');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashIDQuery');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbFixTableHistory');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'private', 'refAdd');

	if( $args['fix'] == 'yes' ) {
		//
		// Load existing image refs
		//
		$strsql = "SELECT CONCAT_WS('-', object_id, image_id) AS refid "
			. "FROM ciniki_image_refs "
			. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "AND object = 'ciniki.web.setting' "
			. "";
		$rc = ciniki_core_dbHashIDQuery($ciniki, $strsql, 'ciniki.images', 'refs', 'refid');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( isset($rc['refs']) ) {
			$refs = $rc['refs'];
		} else {
			$refs = array();
		}
		//
		// Add image refs
		//
		$strsql = "SELECT detail_key, detail_value "
			. "FROM ciniki_web_settings "
			. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "AND detail_key in ('page-home-image', 'page-about-image', 'site-header-image') "
			. "AND detail_value > 0 "
			. "";
		$rc = ciniki_core_dbHashQuery($ciniki, $strsql, $module, 'item');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( isset($rc['rows']) ) {
			$items = $rc['rows'];
			foreach($items as $iid => $item) {
				if( !isset($refs[$item['detail_key'] . '-' . $item['detail_value']]) ) {
					$rc = ciniki_images_refAdd($ciniki, $args['business_id'], array(
						'image_id'=>$item['detail_value'],
						'object'=>'ciniki.web.setting',
						'object_id'=>$item['detail_key'],
						'object_field'=>'detail_value'));
					if( $rc['stat'] != 'ok' ) {
						return $rc;
					}
				}
			}
		}
	}

	return array('stat'=>'ok');
}
?>
