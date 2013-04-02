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
function ciniki_web_processContent($ciniki, $unprocessed_content) {

	if( $unprocessed_content == '' ) { 
		return array('stat'=>'ok', 'content'=>'');
	}

	
	$processed_content = "<p class='intro'>" . preg_replace('/\n\s*\n/m', '</p><p>', $unprocessed_content) . '</p>';
//	$processed_content = preg_replace('/\r/m', '', $processed_content);
	$processed_content = preg_replace('/\n/m', '<br/>', $processed_content);
//	$processed_content = preg_replace('/h2><br\/>/m', 'h2>', $processed_content);

	return array('stat'=>'ok', 'content'=>$processed_content);
}
?>

