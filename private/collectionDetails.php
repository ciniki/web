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
function ciniki_web_collectionDetails($ciniki, $business_id, $collection_permalink) {

	//
	// Query for the collections
	//
	$strsql = "SELECT ciniki_web_collections.id, "
		. "ciniki_web_collections.name, "
		. "ciniki_web_collections.permalink, "
		. "ciniki_web_collections.status, "
		. "ciniki_web_collections.sequence, "
		. "ciniki_web_collections.image_id, "
		. "ciniki_web_collections.image_caption, "
		. "ciniki_web_collections.synopsis, "
		. "ciniki_web_collections.description, "
		. "ciniki_web_collection_objs.id AS obj_id, "
		. "ciniki_web_collection_objs.object AS object, "
		. "ciniki_web_collection_objs.sequence AS obj_sequence, "
		. "ciniki_web_collection_objs.num_items AS obj_num_items, "
		. "ciniki_web_collection_objs.title AS obj_title, "
		. "ciniki_web_collection_objs.more AS obj_more "
		. "FROM ciniki_web_collections "
		. "LEFT JOIN ciniki_web_collection_objs ON ("
			. "ciniki_web_collections.id = ciniki_web_collection_objs.collection_id "
			. "AND ciniki_web_collection_objs.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
			. ") "
		. "WHERE ciniki_web_collections.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
		. "AND ciniki_web_collections.permalink = '" . ciniki_core_dbQuote($ciniki, $collection_permalink) . "' "
		. "AND ciniki_web_collections.status = 10 " // Active and visible on website
		. "ORDER BY ciniki_web_collections.id, ciniki_web_collection_objs.sequence ASC"
		. "";
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
	$rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.web', array(
		array('container'=>'collections', 'fname'=>'id', 
			'fields'=>array('id', 'name', 'permalink', 'status', 'sequence', 'image_id',
				'image_caption', 'synopsis', 'description')),
		array('container'=>'objects', 'fname'=>'object',
			'fields'=>array('id'=>'obj_id', 'object', 'sequence'=>'obj_sequence', 	
				'num_display_items'=>'obj_num_items',
				'title'=>'obj_title', 'more'=>'obj_more')),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['collections']) ) {
		return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'2068', 'msg'=>'That collection does not exist.'));
	}
	$collection = array_pop($rc['collections']);

	if( !isset($collection['objects']) ) {
		$collection['objects'] = array();
	}

	//
	// Get the number of items available for each object
	//
	$strsql = "SELECT object, COUNT(id) AS num_items "
		. "FROM ciniki_web_collection_objrefs "
		. "WHERE ciniki_web_collection_objrefs.collection_id = '" . ciniki_core_dbQuote($ciniki, $collection['id']) . "' "
		. "AND ciniki_web_collection_objrefs.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
		. "GROUP BY object "
		. "";
	$rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.web', array(
		array('container'=>'objects', 'fname'=>'object', 
			'fields'=>array('num_items')),
			));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( isset($rc['objects']) ) {
		foreach($rc['objects'] as $oid => $obj) {
			if( !isset($collection['objects'][$oid]) ) {
				$collection['objects'][$oid] = array();
			}
			$collection['objects'][$oid]['num_items'] = $obj['num_items'];
		}
	}

	return array('stat'=>'ok', 'collection'=>$collection);
}
?>
