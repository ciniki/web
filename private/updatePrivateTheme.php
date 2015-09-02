<?php
//
// Description
// -----------
// This function updates the theme files in the cache for the business. It will also
// update any settings if required.
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_web_updatePrivateTheme(&$ciniki, $business_id, &$settings) {

	//
	// Lookup the current theme id based on the permalink
	//
	$theme_id = 0;
	if( isset($settings['site-privatetheme-active']) ) {
		$strsql = "SELECT id "
			. "FROM ciniki_web_themes "
			. "WHERE ciniki_web_themes.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
			. "AND ciniki_web_themes.permalink = '" . ciniki_core_dbQuote($ciniki, $settings['site-privatetheme-active']) . "' "
			. "AND ciniki_web_themes.status = 10 "
			. "ORDER BY date_added DESC "
			. "LIMIT 1 "
			. "";
		$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.web', 'theme');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( isset($rc['theme']['id']) ) {
			$theme_id = $rc['theme']['id'];
		}
	}

	//
	// Get the current theme
	//
	if( $theme_id == 0 ) {
		//
		// If the current theme is not specified, get the last active theme added
		//
		$strsql = "SELECT id, permalink "
			. "FROM ciniki_web_themes "
			. "WHERE ciniki_web_themes.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
			. "AND ciniki_web_themes.status = 10 "
			. "ORDER BY date_added DESC "
			. "LIMIT 1 "
			. "";
		$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.web', 'theme');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( isset($rc['theme']['id']) ) {
			$theme_id = $rc['theme']['id'];
			$settings['site-privatetheme-active'] = $rc['theme']['permalink'];
		} else {
			//
			// No private themes active
			//
			return array('stat'=>'ok');
		}
	}

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbUpdate');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');

	//
	// Setup the cache directory
	//
	$theme_cache_dir = $ciniki['business']['web_cache_dir'] . '/' . $settings['site-privatetheme-active'];

	//
	// Load the list of javascript and css content from the database
	//
	$strsql = "SELECT id, type, media, content "
		. "FROM ciniki_web_theme_content "
		. "WHERE ciniki_web_theme_content.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
		. "AND ciniki_web_theme_content.theme_id = '" . ciniki_core_dbQuote($ciniki, $theme_id) . "' "
		. "AND ciniki_web_theme_content.status = 10 "
		. "ORDER BY media, sequence "
		. "";
	$rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.web', array(
		array('container'=>'type', 'fname'=>'type',
			'fields'=>array('type')),
		array('container'=>'media', 'fname'=>'media',
			'fields'=>array('media')),
		array('container'=>'content', 'fname'=>'id',
			'fields'=>array('id', 'media', 'content')),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	//
	// Join the content into files
	//
	$allcontent = array(
		'site-privatetheme-css-all'=>array('filename'=>'style.css', 'content'=>''),
		'site-privatetheme-css-print'=>array('filename'=>'print.css', 'content'=>''),
		'site-privatetheme-js'=>array('filename'=>'code.js', 'content'=>''),
		);
	if( isset($rc['types']) ) {
		foreach($rc['types'] as $type) {
			if( isset($type['media']) ) {
				foreach($type['media'] as $media) {
					if( $type['type'] == 'css' && $media['media'] == 'all' ) {
						$setting = 'site-privatetheme-css-all';
					} elseif( $type['type'] == 'css' && $media['media'] == 'print' ) {
						$setting = 'site-privatetheme-css-print';
					} elseif( $type['type'] == 'js' ) {
						$setting = 'site-privatetheme-js';
					} else {
						// Ignore unknown content
						continue;
					}
					if( isset($media['content']) ) {
						foreach($media['content'] as $type_media_content) {
							$allcontent[$setting]['content'] .= $type_media_content;
						}
					}
				}
			}
		}
	}
	
	//
	// Save the content to the cache directory
	//
	foreach($allcontent as $setting => $content) {
		if( $content['content'] != '' ) {
			// 
			// Write the content to the cache directory
			//
			if( !file_put_contents($theme_cache_dir . '/' . $content['filename'], $content['content']) ) {
				error_log('WEB-ERR: Unable to write cache theme file: ' . $theme_cache_dir . '/' . $content['filename']);
			} else {
				//
				// Save the setting
				//
/*				if( !isset($settings[$setting]) || $settings[$setting] != $content['filename'] ) {
					$strsql = "UPDATE ciniki_web_settings "
						. "SET detail_value = '', last_updated = UTC_TIMESTAMP() "
						. "WHERE ciniki_web_settings.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
						. "AND ciniki_web_settings.detail_key = '" . ciniki_core_dbQuote($ciniki, $setting) . "' "
						. "";
					$rc = ciniki_core_dbUpdate($ciniki, $strsql, 'ciniki.web');
					if( $rc['stat'] != 'ok' ) {
						error_log('WEB-ERR: Unable to remove web setting: ' . $setting);
					} else {
						ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.web', 'ciniki_web_history', $business_id,
							2, 'ciniki_web_settings', $setting, 'detail_value', );
					}
				} */
			}
		} else {
			//
			// Remove the file if it exists
			//
			if( file_exists($theme_cache_dir . '/' . $setting['filename']) ) {
				if( !unlink($theme_cache_dir . '/' . $setting['filename']) ) {
					error_log('WEB-ERR: Unable to remove cache theme file: ' . $theme_cache_dir . '/' . $settings['filename']);
				}
			}
			//
			// Remove the setting if it exists, not using settings after all
			//
/*			if( isset($settings[$setting]) && $settings[$setting] != '' ) {
				$strsql = "UPDATE ciniki_web_settings "
					. "SET detail_value = '', last_updated = UTC_TIMESTAMP() "
					. "WHERE ciniki_web_settings.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
					. "AND ciniki_web_settings.detail_key = '" . ciniki_core_dbQuote($ciniki, $setting) . "' "
					. "";
				$rc = ciniki_core_dbUpdate($ciniki, $strsql, 'ciniki.web');
				if( $rc['stat'] != 'ok' ) {
					error_log('WEB-ERR: Unable to remove web setting: ' . $setting);
				} else {
					ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.web', 'ciniki_web_history', $business_id,
						2, 'ciniki_web_settings', $setting, 'detail_value', '');
				}
			} */
		}
	}

	//
	// Load the list of images from the database
	//
	$strsql = "SELECT ciniki_images.id, ciniki_images.original_filename, ciniki_images.type, "
		. "ciniki_images.image, "
		. "UNIX_TIMESTAMP(ciniki_images.last_updated) AS last_updated "
		. "FROM ciniki_web_theme_images, ciniki_images "
		. "WHERE ciniki_web_theme_images.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
		. "AND ciniki_web_theme_images.theme_id = '" . ciniki_core_dbQuote($ciniki, $theme_id) . "' "
		. "AND ciniki_web_theme_images.image_id = ciniki_images.id "
		. "AND ciniki_images.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
		. "";
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.web', 'image');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( isset($rc['rows']) ) {
		foreach($rc['rows'] as $img) {
			$img_file = $theme_cache_dir . '/' . $img['original_filename'];
			if( !file_exists($img_file)
				|| filemtime($img_file) < $img['last_updated']
				) {
				//
				// Load the image from the database
				//
				ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'private', 'loadImage');
				$rc = ciniki_images_loadImage($ciniki, $ciniki['request']['business_id'], $img['id'], $version);
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				$image = $rc['image'];

				//
				// Write the file
				//
				$h = fopen($img_file, 'w');
				if( $h ) {
					fwrite($h, $image->getImageBlob());
					fclose($h);
				} else {
					error_log('WEB-ERR: Unable to load image: $img_file');
				}
			}
		}
	}

	//
	// Update the settings variable and settings in the database for this business
	// with the cache file names if required
	//

	//
	// Update the directory timestamp
	//
	touch($theme_cache_dir);

	return array('stat'=>'ok');
}
?>
