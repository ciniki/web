<?php
//
// Description
// -----------
// This function takes a list of tags or categories and returns a delimited list
//
// Arguments
// ---------
// ciniki:
// url:				The url to be processed.
//
// Returns
// -------
//
function ciniki_web_processTagList($ciniki, $settings, $base_url, $delimiter, $list) {

	$content = '';

	if( is_array($list) && count($list) > 0 ) {
		foreach($list as $tag) {
			$content .= ($content!=''?$delimiter:'') 
				. "<a href='$base_url/" . rawurlencode((isset($tag['permalink'])&&$tag['permalink']!='')?$tag['permalink']:$tag['name']) . "'>" . $tag['name'] . "</a>";
		}
	}

	return array('stat'=>'ok', 'content'=>$content);
}
?>
