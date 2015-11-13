<?php
//
// Description
// -----------
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:			The ID of the business to update the theme for.
//
// Returns
// -------
// <rsp stat='ok' />
//
function ciniki_web_privateThemeUpdate(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'theme_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Theme'), 
        'name'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Name'), 
        'permalink'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Permalink'), 
		'status'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Status'),
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
    $rc = ciniki_web_checkAccess($ciniki, $args['business_id'], 'ciniki.web.privateThemeUpdate'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }

	//
	// Check for an updated name
	//
	if( isset($args['name']) ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');
		$args['permalink'] = ciniki_core_makePermalink($ciniki, $args['name']);
		//
		// Make sure the permalink is unique
		//
		$strsql = "SELECT id, name, permalink "
			. "FROM ciniki_web_themes "
			. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "AND permalink = '" . ciniki_core_dbQuote($ciniki, $args['permalink']) . "' "
			. "AND id <> '" . ciniki_core_dbQuote($ciniki, $args['theme_id']) . "' "
			. "";
		$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.web', 'theme');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( $rc['num_rows'] > 0 ) {
			return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'2542', 'msg'=>'You already have a theme with this name, please choose another name.'));
		}
	}

	//
	// Get the list of current settings
	//
	$strsql = "SELECT id, uuid, detail_key, detail_value "
		. "FROM ciniki_web_theme_settings "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND theme_id = '" . ciniki_core_dbQuote($ciniki, $args['theme_id']) . "' "
		. "";
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
	$rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.web', array(
		array('container'=>'settings', 'fname'=>'detail_key',
			'fields'=>array('id', 'uuid', 'detail_key', 'detail_value')),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$settings = array();
	if( isset($rc['settings']) ) {
		$settings = $rc['settings'];
	}

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
	$rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.web');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	//
	// Update the theme in the database
	//
	$rc = ciniki_core_objectUpdate($ciniki, $args['business_id'], 'ciniki.web.theme', $args['theme_id'], $args);
	if( $rc['stat'] != 'ok' ) {
		ciniki_core_dbTransactionRollback($ciniki, 'ciniki.web');
		return $rc;
	}

	//
	// Check for any settings and add/update
	//
	$valid_settings = array(
		'header-social-icons',
		'header-article-title',
		'header-breadcrumbs',
		'share-social-icons',
		'footer-layout',
		'footer-social-icons',
		'footer-copyright-message',
		'footer-subscription-agreement',
		'footer-privacy-policy',
		);
	foreach($valid_settings as $field) {
		if( isset($ciniki['request']['args'][$field]) ) {
			if( isset($settings[$field]['detail_value']) && $settings[$field]['detail_value'] != $ciniki['request']['args'][$field] ) {
				//
				// Update the setting
				//
				$rc = ciniki_core_objectUpdate($ciniki, $args['business_id'], 'ciniki.web.theme_setting', $settings[$field]['id'],
					array('detail_value'=>$ciniki['request']['args'][$field]), 0x04);
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
			} else if( !isset($settings[$field]) ) {
				//
				// Add the setting
				//
				$rc = ciniki_core_objectAdd($ciniki, $args['business_id'], 'ciniki.web.theme_setting', 
					array('theme_id'=>$args['theme_id'], 'detail_key'=>$field, 'detail_value'=>$ciniki['request']['args'][$field]), 0x04);
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
			}
		}
	}

    //
    // Update theme last_updated
    //
    $strsql = "UPDATE ciniki_web_themes SET last_updated = UTC_TIMESTAMP() "
        . "WHERE id = '" . ciniki_core_dbQuote($ciniki, $args['theme_id']) . "' "
        . "AND business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbUpdate');
    $rc = ciniki_core_dbUpdate($ciniki, $strsql, 'ciniki.web');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

	//
	// Commit the changes to the database
	//
	$rc = ciniki_core_dbTransactionCommit($ciniki, 'ciniki.web');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	//
	// Update the last_change date in the business modules
	// Ignore the result, as we don't want to stop user updates if this fails.
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'updateModuleChangeDate');
	ciniki_businesses_updateModuleChangeDate($ciniki, $args['business_id'], 'ciniki', 'web');

	return array('stat'=>'ok');
}
?>
