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
	$rc = ciniki_core_dbDetailsQuery($ciniki, 'ciniki_web_settings', 'business_id', $business_id, 'ciniki.web', 'settings', '');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['settings']) ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'2319', 'msg'=>'No settings found, site not configured.'));
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

	//
	// Check if private theme is enabled and load any settings for the private theme
	//
	if( isset($ciniki['business']['modules']['ciniki.web']['flags']) && ($ciniki['business']['modules']['ciniki.web']['flags']&0x0100) > 0 ) {
		//
		// Check if theme id set for private theme
		//
		$strsql = "SELECT id, permalink, last_updated "
			. "FROM ciniki_web_themes "
			. "WHERE ciniki_web_themes.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
			. "";
		if( isset($settings['site-privatetheme-id']) ) {
			$strsql .= "AND ciniki_web_themes.id = '" . ciniki_core_dbQuote($ciniki, $settings['site-privatetheme-id']) . "' ";
		} elseif( isset($settings['site-privatetheme-permalink']) ) {
			$strsql .= "AND ciniki_web_themes.permalink = '" . ciniki_core_dbQuote($ciniki, $settings['site-privatetheme-permalink']) . "' ";
		}
		// If id and permalink not set, then get the first active one added
		$strsql .= "AND ciniki_web_themes.status = 10 "
			. "ORDER BY date_added ASC "
			. "LIMIT 1 "
			. "";
		$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.web', 'theme');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( isset($rc['theme']['id']) ) {
			$settings['site-privatetheme-id'] = $rc['theme']['id'];
			$settings['site-privatetheme-permalink'] = $rc['theme']['permalink'];
			$dt = new DateTime($rc['theme']['last_updated'], new DateTimeZone('UTC'));
			$settings['site-privatetheme-last-updated'] = $dt->format('U');
		}

		//
		// If specified, load the private theme settings
		//
		if( isset($settings['site-privatetheme-id']) && $settings['site-privatetheme-id'] > 0 ) {
			$strsql = "SELECT detail_key, detail_value "
				. "FROM ciniki_web_theme_settings "
				. "WHERE ciniki_web_theme_settings.business_id = '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['business_id']) . "' "
				. "AND ciniki_web_theme_settings.theme_id = '" . ciniki_core_dbQuote($ciniki, $settings['site-privatetheme-id']) . "' "
				. "";
			ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQueryList2');
			$rc = ciniki_core_dbQueryList2($ciniki, $strsql, 'ciniki.web', 'settings');
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			if( isset($rc['settings']) ) {
				$settings['theme'] = $rc['settings'];
			}
		}
	}
	
	return array('stat'=>'ok', 'settings'=>$settings);
}
?>
