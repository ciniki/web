<?php
//
// Description
// -----------
// This function will process a URL to make sure it has the http:// at the beginning, and 
// return a display only version with the http:// stripped.
//
// Arguments
// ---------
// ciniki:
// url:             The url to be processed.
//
// Returns
// -------
//
function ciniki_web_processURL($ciniki, $url) {

    if( $url != '' && !preg_match('/^\s*http/i', $url) ) {
        $display_url = $url;
        $url = "http://" . $url;
    } else {
        $display_url = preg_replace('/^\s*http:\/\//i', '', $url);
        $display_url = preg_replace('/\/$/i', '', $display_url);
    }

    return array('stat'=>'ok', 'url'=>$url, 'display'=>$display_url);
}
?>
