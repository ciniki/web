<?php
//
// Description
// -----------
// This hook will return the list of active/invisible web collections for use in
// other modules.  If the object/object_id is provided, then it will mark which
// ones are currently used.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id: 			The ID of the business to get the users for.
//
// Returns
// -------
//
function ciniki_web_hooks_webCollectionList($ciniki, $business_id, $args) {

	$strsql = "SELECT ciniki_web_collections.id, "
		. "ciniki_web_collections.name, "
		. "ciniki_web_collections.permalink, "
		. "ciniki_web_collections.sequence "
		. "";
	if( isset($args['object']) && isset($args['object_id']) && $args['object_id'] != '' ) {
		$strsql .= ", IF(ciniki_web_collection_objrefs.id > 0, 'yes', 'no') AS active "
			. "FROM ciniki_web_collections "
			. "LEFT JOIN ciniki_web_collection_objrefs ON ("
				. "ciniki_web_collections.id = ciniki_web_collection_objrefs.collection_id "
				. "AND ciniki_web_collections.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
				. "AND ciniki_web_collection_objrefs.object = '" . ciniki_core_dbQuote($ciniki, $args['object']) . "' "
				. "AND ciniki_web_collection_objrefs.object_id = '" . ciniki_core_dbQuote($ciniki, $args['object_id']) . "' "
				. ") ";
	} else {
		$strsql .= ", 'no' AS active "
			. "FROM ciniki_web_collections "
			. "";
	}
	$strsql .= "WHERE ciniki_web_collections.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
		. "AND ciniki_web_collections.status < 60 "
		. "ORDER BY ciniki_web_collections.sequence, ciniki_web_collections.name ";

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
	$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.businesses', array(
		array('container'=>'collections', 'fname'=>'id', 'name'=>'collection', 
			'fields'=>array('id', 'name', 'permalink', 'sequence', 'selected'=>'active'),
			),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['collections']) ) {
		return array('stat'=>'ok', 'collections'=>array());
	}
	$collections = $rc['collections'];
	$selected = '';
	$selected_text = '';
	foreach($collections as $cid => $collection) {
		if( $collection['collection']['selected'] == 'yes' ) {
			$selected .= ($selected!=''?',':'') . $collection['collection']['id'];
			$selected_text .= ($selected_text!=''?', ':'') . $collection['collection']['name'];
		}
	}

	return array('stat'=>'ok', 'collections'=>$collections, 'selected'=>$selected, 'selected_text'=>$selected_text);
}
?>
