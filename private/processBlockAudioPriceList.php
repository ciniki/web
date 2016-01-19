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

    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'cartSetupPrices');

    //
    // Check for a title
    //
    if( isset($block['title']) && $block['title'] != '' ) {
        $content .= "<h2>" . $block['title'] . "</h2>";
    }
   
    $audiosamples = '';
    $list_content = '';
    $found_prices = 'no';
    foreach($block['list'] as $iid => $item) {
        $list_content .= "<div class='price-list-item'>";
        $list_content .= "<div class='item-name'>";
        if( isset($block['base_url']) && $block['base_url'] != '' && isset($item['permalink']) && $item['permalink'] != '' ) {
            $list_content .= "<span class='item-name'><a href='" . $block['base_url'] . '/' . $item['permalink'] . "'>" . $item['title'] . "</a></span>";
        } else {
            $list_content .= "<span class='item-name'>" . $item['title'] . "</span>";
        }

        if( isset($item['audio']) && count($item['audio']) > 0 ) {
            $list_content .= "<a id='item-{$iid}-play' class='play' onclick='cBAPLplay(\"item-$iid\");'></a>";
            $list_content .= "</div>";
            
            $track = array_shift($item['audio']);
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
                $audiosamples .= "<div id='item-{$iid}' class='audio hidden'>";
//                if( isset($track['name']) && $track['name'] != '' ) {
//                    $audiosamples .= '<span class="audiolabel">' . $track['name'] . '</span>';
//                }
                $audiosamples .= "<audio id='item-{$iid}-audio' preload='none' onplay='cBAPLstart(\"item-{$iid}\");' onpause='cBAPLstop(\"item-{$iid}\");' controls>";
                if( isset($sources['wav']) ) {
                    $audiosamples .= $sources['wav'];
                }
                if( isset($sources['mp3']) ) {
                    $audiosamples .= $sources['mp3'];
                }
                if( isset($sources['ogg']) ) {
                    $audiosamples .= $sources['ogg'];
                }
                $audiosamples .= '</audio>';
                $audiosamples .= "</div>";
            }
        } else {
            $list_content .= "</div>";
        }
        
        if( isset($item['prices']) ) {
            $rc = ciniki_web_cartSetupPrices($ciniki, $settings, $business_id, $item['prices']);
            if( $rc['stat'] == 'ok' && $rc['content'] != '' ) {
                $found_prices = 'yes';
                $list_content .= $rc['content'];
            }
        }
        $list_content .= "</div>";
    }
    if( $list_content != '' ) {
        $content .= "<div class='price-list" . ($found_prices=='yes'?' price-list-prices':'') . "'>" . $list_content . "</div>";
    }

    if( $audiosamples != '' ) {
        $content .= "<div id='cBAPLaudio' class='audiosamples'>" . $audiosamples . "</div>";
    }

    if( !isset($ciniki['request']['inline_javascript']) ) {
        $ciniki['request']['inline_javascript'] = '';
    }
    $ciniki['request']['inline_javascript'] .= "<script type='text/javascript'>"
        . "function cBAPLplay(id){"
            . "var e=document.getElementById('cBAPLaudio');"
            . "for(var i=0;i<e.children.length;i++){"
                . "var c=e.children[i];"
                . "if(!c.classList.contains('hidden')){"
                    . "c.classList.add('hidden');"
                    . "document.getElementById(c.id+'-audio').pause();"
                . "}else if(c.id==id&&c.classList.contains('hidden')){"
                    . "c.classList.remove('hidden');"
                    . "var a=document.getElementById(c.id+'-audio');"
                    . "if(a!=null){if(a.currentTime>0){a.currentTime=0;}a.play();}"
                . "}"
            . "}"
        . "}"
        . "function cBAPLstart(id){"
            . "document.getElementById(id+'-play').className='pause';"
        . "}"
        . "function cBAPLstop(id){"
            . "document.getElementById(id+'-play').className='play';"
        . "}"
        . "</script>";

	return array('stat'=>'ok', 'content'=>$content);
}
?>
