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
function ciniki_web_processTagCloud($ciniki, $settings, $base_url, $tags) {

	$min = 0;
	$max = 1;
	// Find the minimum and maximum
	foreach($tags as $tag) {
		if( $min == 0 ) { $min = $tag['num_tags']; }
		elseif( $tag['num_tags'] < $min ) { $min = $tag['num_tags']; }
		if( $tag['num_tags'] > $max ) { $max = $tag['num_tags']; }
	}
	
	$fmax = 9;
	$fmin = 0;
	$tag_content = '';
	$size = 0;
	foreach($tags as $tag) {
		if( $max > $fmax ) {
			$fontsize = round(($fmax * ($tag['num_tags']-$min))/($max-$min));
		} else {
			$fontsize = $tag['num_tags'];
		}
		if( !isset($tag['permalink']) || $tag['permalink'] == '' ) {
			$tag['permalink'] = rawurlencode($tag['name']);
		}
		$tag_content .= "<span class='size-$fontsize'><a href='$base_url/" . $tag['permalink'] . "'>" 	
			. $tag['name'] . "</a></span> ";
		$size += strlen($tag['name']);
	}

	$cloud_size = 'word-cloud-medium';
	if( $size > 150 ) {
		$cloud_size = 'word-cloud-large';
	}
	$content = "<div class='word-cloud-wrap'>";
	$content .= "<div class='word-cloud $cloud_size'>";
	$content .= $tag_content;
	$content .= "</div>";
	$content .= "</div>";

	return array('stat'=>'ok', 'content'=>$content);
}
?>
