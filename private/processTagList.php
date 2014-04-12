<?php
//
// Description
// -----------
// This function will process a list of tags along with sizes to return a word size cloud
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_processTagList($ciniki, $settings, $base_url, $tags, $args) {

	if( isset($args['delimiter']) ) {	
		$content = '';
		foreach($tags as $tag) {
			$content .= ($content!=''?$args['delimiter']:'');
			$content .= "<a href='$base_url/" . $tag['permalink'] . "'>"
				. $tag['name'] . "</a>";
		}
	} else {
		$content = "<div class='largebutton-list'>";

		foreach($tags as $tag) {
			$content .= "<div class='button-list-wrap'>";
			$content .= "<div class='button-list-button'><a href='$base_url/" . $tag['permalink'] . "'>" 	
				. $tag['name'] . "</a></div>";
			$content .= "</div> ";
		}

		$content .= "</div>";
	}

	return array('stat'=>'ok', 'content'=>$content);
}
?>

