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
function ciniki_web_processBlockAudioPriceList(&$ciniki, $settings, $business_id, $block) {

	$content = '';

	//
	// Display any audio sample
	//
    if( !isset($block['list']) ) {
        return array('stat'=>'ok', 'content'=>$content);
    }

    //
    // Check for a title
    //
    if( isset($block['title']) && $block['title'] != '' ) {
        $content .= "<h2>" . $block['title'] . "</h2>";
    }

	foreach($block['list'] as $aid => $track) {
		$sources = array();
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
					$sources['ogg'] = '<source src="' . $audio_url . '" type="audio/ogg" />';
				} elseif( $format['type'] == '30' ) {
					$sources['wav'] = '<source src="' . $audio_url . '" type="audio/wav" />';
				} elseif( $format['type'] == '40' ) {
					$sources['mp3'] = '<source src="' . $audio_url . '" type="audio/mpeg" />';
				}
			}
		}

		if( count($sources) > 0 ) {
            $content .= "<div class='audio'>";
			if( isset($track['name']) && $track['name'] != '' ) {
				$content .= '<span class="audiolabel">' . $track['name'] . '</span>';
			}
			$content .= '<audio preload="none" controls>';
            if( isset($sources['wav']) ) {
                $content .= $sources['wav'];
            }
            if( isset($sources['mp3']) ) {
                $content .= $sources['mp3'];
            }
            if( isset($sources['ogg']) ) {
                $content .= $sources['ogg'];
            }
            $content .= '</audio>';
            $content .= "</div>";
		}
	}

	return array('stat'=>'ok', 'content'=>$content);
}
?>
