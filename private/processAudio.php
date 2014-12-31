<?php
//
// Description
// -----------
// This function will prepare a list of audio files for the web page.
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_processAudio(&$ciniki, $settings, $business_id, $audio, $args) {

	$content = '';

	//
	// Display any audio sample
	//
	foreach($audio as $aid => $track) {
//		print "<pre>" . print_r($track, true) . "</pre>";
		$sources = '';
		$formats = array('mp3'=>'audio/mpeg', 'wav'=>'audio/wav', 'ogg'=>'audio/ogg');
		if( isset($track['formats']) ) {
			//
			// Go through the formats and find ones suitable for the web
			//
			foreach($track['formats'] as $fid => $format) {
				$cache_filename = '/' . sprintf('%02d', ($ciniki['request']['business_id']%100)) . '/'
					. sprintf('%07d', $ciniki['request']['business_id'])
					. '/ciniki.audio/' . $format['uuid'] . '.' . $format['extension'];
				$storage_filename = $ciniki['config']['ciniki.core']['storage_dir'] . '/'
					. $ciniki['business']['uuid'][0] . '/' . $ciniki['business']['uuid']
					. '/ciniki.audio/'
					. $format['uuid'][0] . '/' . $format['uuid'];
				$cache_full_filename = $ciniki['request']['cache_dir'] . $cache_filename;
				//
				// Copy the audio to the web-cache
				//
				if( !file_exists(dirname($cache_full_filename)) ) {
					mkdir(dirname($cache_full_filename), 0755, true);
				}
				if( !file_exists($cache_full_filename) ) {
					copy($storage_filename, $cache_full_filename);
				}
				$audio_url = $ciniki['request']['cache_url'] . $cache_filename;
				$audio_domain_url = 'http://' . $ciniki['request']['domain'] . $ciniki['request']['cache_url'] . $cache_filename;
				if( $format['type'] == '20' ) {
					$sources .= '<source src="' . $audio_url . '" type="audio/ogg" />';
				} elseif( $format['type'] == '30' ) {
					$sources .= '<source src="' . $audio_url . '" type="audio/wav" />';
				} elseif( $format['type'] == '40' ) {
					$sources .= '<source src="' . $audio_url . '" type="audio/mpeg" />';
				}
			}
		}

		if( $sources != '' ) {
			$content .= ($content!=''?'<br/>':'');
			if( isset($track['name']) && $track['name'] != '' ) {
				$content .= $track['name'] . ' ';
			}
			$content .= '<audio controls>' . $sources . '</audio>';
		}
	}

	return array('stat'=>'ok', 'content'=>$content);
}
?>
