<?php
//
// Description
// -----------
//
// Arguments
// ---------
// ciniki:
// business_id: 			The ID of the business to get the users for.
// args:					The arguments for the function.
//
// Returns
// -------
//
function ciniki_web_hooks_webCollectionUpdate($ciniki, $business_id, $args) {

	if( isset($args['object']) 
		&& isset($args['object_id']) && $args['object_id'] != '' 
		&& isset($args['collection_ids'])
		) {
		//
		// Check if the new collection ids are in a list or array
		//
		if( is_array($args['collection_ids']) ) {
			$new_collection_ids = $args['collection_ids'];
		} else {
			$new_collection_ids = explode($args['collection_ids']);
		}

		// 
		// Get the list of current collections for the object
		//
		$strsql = "SELECT id, uuid, collection_id "
			. "FROM ciniki_web_collection_objrefs "
			. "WHERE object = '" . ciniki_core_dbQuote($ciniki, $args['object']) . "' "
			. "AND object_id = '" . ciniki_core_dbQuote($ciniki, $args['object_id']) . "' "
			. "AND business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
			. "";
		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
		$rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.web', array(
			array('container'=>'collections', 'fname'=>'collection_id',
				'fields'=>array('id', 'uuid', 'collection_id')),
			));
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$cur_collections = array();
		if( isset($rc['collections']) ) {
			$cur_collections = $rc['collections'];
		}

		//
		// Check which should be deleted
		//
		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectDelete');
		foreach($cur_collections as $cur_col_id => $cur_col) {
			if( !in_array($cur_col_id, $new_collection_ids) ) {
				$rc = ciniki_core_objectDelete($ciniki, $business_id, 'ciniki.web.collection_objref', 
					$cur_col['id'], $cur_col['uuid'], 0x04);
				if( $rc['stat'] != 'ok' ) {
					return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'2055', 'msg'=>'Unable to remove from the collection', 'err'=>$rc['err']));
				}
			}
		}

		//
		// Check for additions
		//
		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
		foreach($new_collection_ids as $new_col_id) {
			if( !isset($cur_collections[$new_col_id]) ) {
				$rc = ciniki_core_objectAdd($ciniki, $business_id, 'ciniki.web.collection_objref', 
					array('object'=>$args['object'],
						'object_id'=>$args['object_id'],
						'collection_id'=>$new_col_id), 0x04);
				if( $rc['stat'] != 'ok' ) {
					return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'2054', 'msg'=>'Unable to add the collection', 'err'=>$rc['err']));
				}
			}
		}
	}

	return array('stat'=>'ok');
}
?>
