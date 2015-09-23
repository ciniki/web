<?php
//
// Description
// -----------
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_processBlockShareButtons(&$ciniki, $settings, $business_id, $block) {

	//
	// Store the content created by the page
	//
	$content = '';

	$url = $ciniki['response']['head']['og']['url'];

	$content .= "<p class='share-buttons-wrap'><span class='share-buttons'>"
		. "<span class='socialtext'>Share on: </span>";

	//
	// Setup facebook button
	//
	$content .= "<a href='https://www.facebook.com/sharer.php?u=" . urlencode($ciniki['response']['head']['og']['url']) . "' onclick='window.open(this.href, \"_blank\", \"height=430,width=640\"); return false;' target='_blank'>"
		. "<span title='Share on Facebook' class='socialsymbol social-facebook'>&#xe227;</span>"
		. "</a>";

	//
	// Setup twitter button
	//
	if( isset($ciniki['business']['social']['social-twitter-business-name']) 
		&& $ciniki['business']['social']['social-twitter-business-name'] != '' ) {
		$msg = $ciniki['business']['social']['social-twitter-business-name'] . ' - ' . strip_tags((isset($block['title'])?$block['title']:''));
	} else {
		$msg = $ciniki['business']['details']['name'] . ' - ' . strip_tags((isset($block['title'])?$block['title']:''));
	}
	if( isset($ciniki['business']['social']['social-twitter-username']) 
		&& $ciniki['business']['social']['social-twitter-username'] != '' ) {
		$msg .= ' @' . $ciniki['business']['social']['social-twitter-username'];
	}
	if( isset($block['tags']) ) {
		$tags = array_unique($block['tags']);
		foreach($tags as $tag) {
			if( $tag == '' ) { continue; }
			$tag = preg_replace('/ /', '', $tag);
			
			//	if( (strlen($surl) + 1 + strlen($msg) + 2 + strlen($tag)) < 140 ) {
			//	URLs only count as 22 characters in twitter, plus 1 for space.
			if( (23 + strlen($msg) + 2 + strlen($tag)) < 140 ) {
				$msg .= ' #' . $tag;
			}
		}
	}
	$content .= "<a href='https://twitter.com/share?url=" . urlencode($url) . "&text=" . urlencode($msg) . "' onclick='window.open(this.href, \"_blank\", \"height=430,width=640\"); return false;' target='_blank'>"
		. "<span title='Share on Twitter' class='socialsymbol social-twitter'>&#xe286;</span>"
		. "</a>";

	//
	// Setup pinterest button
	//
	$content .= "<a href='http://www.pinterest.com/pin/create/button?url=" . urlencode($ciniki['response']['head']['og']['url']) . "&media=" . urlencode($ciniki['response']['head']['og']['image']) . "&description=" . urlencode($ciniki['business']['details']['name'] . (isset($block['title'])?' - ' . $block['title']:'')) . "' onclick='window.open(this.href, \"_blank\", \"height=430,width=640\"); return false;' target='_blank'>"
		. "<span title='Share on Pinterest' class='socialsymbol social-pinterest'>&#xe264;</span>"
		. "</a>";

	//
	// Setup google+ button
	//
	$content .= "<a href='https://plus.google.com/share?url=" . urlencode($ciniki['response']['head']['og']['url']) . "' onclick='window.open(this.href, \"_blank\", \"height=430,width=640\"); return false;' target='_blank'>"
		. "<span title='Share on Google+' class='socialsymbol social-googleplus'>&#xe239;</span>"
		. "</a>";

	//
	// Done
	//
	$content .= "</span></p>";
	
	return array('stat'=>'ok', 'content'=>$content);
}
?>
