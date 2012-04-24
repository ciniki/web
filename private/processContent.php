<?php
//
// Description
// -----------
// This function will process raw text content into HTML.
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_web_processContent($ciniki, $unprocessed_content) {

	if( $unprocessed_content == '' ) { 
		return array('stat'=>'ok', 'content'=>'');
	}

	$processed_content = '<p>' . preg_replace('/\n\s*\n/m', '</p><p>', $unprocessed_content) . '</p>';
	$processed_content = preg_replace('/\n/m', '<br/>', $processed_content);

	return array('stat'=>'ok', 'content'=>$processed_content);
}
?>

