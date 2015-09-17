<?php
//
// Description
// -----------
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:			The ID of the business.
//
// Returns
// -------
//
function ciniki_web_privateThemeContentGet($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
		'content_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Content'),
		'theme_id'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Theme'),
		'content_type'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Type'),
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];

    //  
    // Make sure this module is activated, and
    // check permission to run this function for this business
    //  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'checkAccess');
    $rc = ciniki_web_checkAccess($ciniki, $args['business_id'], 'ciniki.web.privateThemeContentGet'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
	$date_format = ciniki_users_dateFormat($ciniki);

	if( $args['content_id'] == 0 ) {
		//
		// Get the next sequence
		//
		$strsql = "SELECT MAX(sequence) AS sequence "
			. "FROM ciniki_web_theme_content "
			. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "AND theme_id = '" . ciniki_core_dbQuote($ciniki, $args['theme_id']) . "' "
			. "";
		if( isset($args['content_type']) && $args['content_type'] != '' ) {
			$strsql .= "AND content_type = '" . ciniki_core_dbQuote($ciniki, $args['content_type']) . "' ";
		}
		$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.web', 'max');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( isset($rc['max']['sequence']) ) {
			$sequence = $rc['max']['sequence'] + 1;
		} else {
			$sequence = 1;
		}
		$content = array('id'=>'0', 
			'theme_id'=>$args['theme_id'],
			'name'=>'',
			'status'=>'10',
			'sequence'=>$sequence,
			'content_type'=>(isset($args['content_type'])?$args['content_type']:$args['content_type']),
			'media'=>'all',
			'content'=>'',
			);
	} else {
		//
		// Get the content
		//
		$strsql = "SELECT ciniki_web_theme_content.id, "
			. "ciniki_web_theme_content.theme_id, "
			. "ciniki_web_theme_content.name, "
			. "ciniki_web_theme_content.status, "
			. "ciniki_web_theme_content.sequence, "
			. "ciniki_web_theme_content.content_type, "
			. "ciniki_web_theme_content.media, "
			. "ciniki_web_theme_content.content "
			. "FROM ciniki_web_theme_content "
			. "WHERE ciniki_web_theme_content.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "AND ciniki_web_theme_content.id = '" . ciniki_core_dbQuote($ciniki, $args['content_id']) . "' "
			. "";
		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
		$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.web', array(
			array('container'=>'content', 'fname'=>'id', 'name'=>'content',
				'fields'=>array('id', 'theme_id', 'name', 'status', 'sequence', 'content_type', 'media', 'content')),
			));
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$content = $rc['content'][0]['content'];
	}

	return array('stat'=>'ok', 'content'=>$content);
}
?>
