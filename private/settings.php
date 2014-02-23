<?php
//
// Description
// -----------
// This function will return the list of web settings for a business.
//
// Arguments
// ---------
// ciniki:
// business_id:		The ID of the business to get the web settings for.
//
// Returns
// -------
//
function ciniki_web_settings($ciniki, $business_id) {
	//
	// Load settings from the database
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQuery');
	$rc = ciniki_core_dbDetailsQuery($ciniki, 'ciniki_web_settings', 
		'business_id', $business_id, 'ciniki.web', 'settings', '');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['settings']) ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'622', 'msg'=>'No settings found, site not configured.'));
	}
	$settings = $rc['settings'];

	//
	// Make sure the required defaults have been set
	//
	if( !isset($settings['site-layout']) || $settings['site-layout'] == '' ) {
		if( isset($ciniki['config']['ciniki.web']['default-layout']) && $ciniki['config']['ciniki.web']['default-layout'] != '' ) {
			$settings['site-layout'] = $ciniki['config']['ciniki.web']['default-layout'];
		} else {
			$settings['site-layout'] = 'default';
		}
	}
	if( !isset($settings['site-theme']) || $settings['site-theme'] == '' ) {
		if( isset($ciniki['config']['ciniki.web']['default-theme']) && $ciniki['config']['ciniki.web']['default-theme'] != '' ) {
			$settings['site-theme'] = $ciniki['config']['ciniki.web']['default-theme'];
		} else {
			$settings['site-theme'] = 'default';
		}
	}
	
	return array('stat'=>'ok', 'settings'=>$settings);
}
?>
