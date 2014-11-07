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
function ciniki_web_collectionList($ciniki, $business_id) {

	//
	// Query for the collections
	//
	$strsql = "SELECT ciniki_web_collections.id, "
		. "ciniki_web_collections.name, "
		. "ciniki_web_collections.permalink, "
		. "ciniki_web_collections.status, "
		. "ciniki_web_collections.sequence, "
		. "ciniki_web_collections.image_id, "
		. "COUNT(ciniki_web_collection_objrefs.id) AS num_items "
		. "FROM ciniki_web_collections "
		. "LEFT JOIN ciniki_web_collection_objrefs ON ( "
			. "ciniki_web_collections.id = ciniki_web_collection_objrefs.collection_id "
			. "AND ciniki_web_collections.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
			. ") "
		. "WHERE ciniki_web_collections.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
		. "AND ciniki_web_collections.status = 10 " // Active and visible on website
		. "GROUP BY ciniki_web_collections.id "
		. "ORDER BY ciniki_web_collections.sequence, ciniki_web_collections.name "
		. "";
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
	$rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.web', array(
		array('container'=>'collections', 'fname'=>'id', 
			'fields'=>array('id', 'name', 'permalink', 'status', 'sequence', 'image_id')),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	return array('stat'=>'ok', 'collections'=>$rc['collections']);
}
?>
