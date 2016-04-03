<?php
//
// Description
// -----------
// This function will process raw text content into HTML.
//
// Arguments
// ---------
// ciniki:
// unprocessed_content:		The unprocessed text content that needs to be turned into html.
//
// Returns
// -------
//
function ciniki_web_processContent($ciniki, $settings, $unprocessed_content, $pclass='') {

	if( $unprocessed_content == '' ) { 
		return array('stat'=>'ok', 'content'=>'');
	}

	$processed_content = $unprocessed_content;

//	$processed_content = "<p class='$pclass'>" . preg_replace('/\n\s*\n/m', "</p><p class='$pclass'>", $unprocessed_content) . '</p>';
	// Remove empty paragraphs that are followed by a <h tag
//	$processed_content = preg_replace('/<p class=\'[A-Za-z\- ]*\'>(<h[1-6][^\>]*>[^<]+<\/h[1-6]>)<\/p>/', '$1', $processed_content);
	
//	$processed_content = preg_replace('/\r/m', '', $processed_content);
//	$processed_content = preg_replace('/h2><br\/>/m', 'h2>', $processed_content);

	// Create active links for urls specified without a href= infront, or '>' in front of www
//	$pattern = '#\b([^\"\'])((https?://?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))#';
//	$pattern = '#\b(((?<!(href=(\"|\')|.....>))https?://?|(?<!(://|..>))www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))#m';
//	$pattern1 = '#(www\.[a-zA-Z0-9\-\.]+)#m';
//	$pattern1 = '#\n([^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))#m';
//	$pattern = '#\b(((?<!(href=(\"|\')|.....>))https?://?|(?<!(://|..>))www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))#';

	//
	// 	Similar code to mail/private/emailProcessContent
	//
	$pattern = '#\b(((?<!(=(\"|\')|.>))https?://?|(?<!(//|.>))www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))#';
	$callback = create_function('$matches', '
		$display_url = $matches[1];
		$url = $display_url;
		if( isset($matches[2]) && ($matches[2] == "http://" || $matches[2] == "https://") ) {
			$display_url = substr($display_url, strlen($matches[2]));
			$display_url = preg_replace("/\\\\/$/", "", $display_url);
		} elseif( isset($matches[2]) && $matches[2] == "www." )  {
			$url = "http://" . $display_url;
		}
//		$url = preg_replace("/www/", "http://www", $display_url);
		return sprintf(\'<a onclick="event.stopPropagation();" href="%s" target="_blank">%s</a>\', $url, $display_url);
	');
//	$processed_content = preg_replace_callback($pattern1, $callback, $processed_content);
	$processed_content = preg_replace_callback($pattern, $callback, $processed_content);

	$processed_content = preg_replace('/((?<!mailto:|=|[a-zA-Z0-9._%+-])([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,64})(?![a-zA-Z]|<\/[aA]>))/', '<a href="mailto:$1">$1</a>', $processed_content);

	// Do the simple processing
	$processed_content = "<p class='$pclass'>" . preg_replace('/\n\s*\n/m', "</p><p class='$pclass'>", $processed_content) . '</p>';
	// Remove empty paragraphs that are followed by a <h tag
	$processed_content = preg_replace('/<p class=\'[A-Za-z\- ]*\'>(<h[1-6][^\>]*>[^<]+<\/h[1-6]>)<\/p>/', '$1', $processed_content);
	$processed_content = preg_replace('/\n/m', "<br/>", $processed_content);

	//
	// Check for iframe embeded videos
	//
	$youtube_callback = create_function('$matches', '
		$content = preg_replace("/ (width|height)=(\'|\")[0-9]+(\'|\")/", "", $matches[1]);
		return "<div class=\'embed-video\'><div class=\'embed-video-wrap\'>" . $content . "</div></div>";
		');
	$processed_content = preg_replace_callback('/(<iframe[^>]+youtube.com[^>]+><\/iframe>)/', $youtube_callback, $processed_content);

    //
    // Check for callbacks to find content to substitute
    //
    if( preg_match_all('/(\<ciniki\s+[^\>]+\>)/', $processed_content, $matches) ) {
        foreach($matches[0] as $match) {
            $module = '';
            $args = array();
            if( preg_match_all('/\s([a-zA-Z]+)=(\'|\")([^\'\"]+)(\'|\")/', $match, $match_args, PREG_SET_ORDER) ) {
                foreach($match_args as $arg) {    
                    $args[$arg[1]] = $arg[3];
                }
            }
            if( isset($args['module']) && strstr($args['module'], '.') ) {
                list($pkg, $mod) = explode('.', $args['module']);
                $rc = ciniki_core_loadMethod($ciniki, $pkg, $mod, 'web', 'processEmbed');
                if( $rc['stat'] == 'ok' ) {
                    //
                    // If the function exists, call function to get embed content
                    //
                    $fn = $rc['function_call'];
                    $rc = $fn($ciniki, $settings, $ciniki['request']['business_id'], $args);
                    if( $rc['stat'] == 'ok' ) {
                        //
                        // Content is plain and can be substituded
                        //
                        if( isset($rc['content']) ) {
                            $processed_content = str_replace($match, $rc['content'], $processed_content);
                        }
                        //
                        // Content is list of blocks that need to be processed and included
                        //
                        elseif( isset($rc['blocks']) ) {
                            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processBlocks');
                            $rc = ciniki_web_processBlocks($ciniki, $settings, $ciniki['request']['business_id'], $rc['blocks']);
                            if( $rc['stat'] == 'ok' ) {
                                $processed_content = str_replace($match, $rc['content'], $processed_content);
                            }
                        }
                    }
                }
            }
        }
    }

//	<iframe width="420" height="250" src="https://www.youtube.com/embed/u5kiz7GhJt0?list=PLKRqAE_6bwWsOl0ev4mr2SpqFtxSEhR1M" frameborder="0" allowfullscreen></iframe>

	return array('stat'=>'ok', 'content'=>$processed_content);
}
?>
