<?php
//
// Description
// -----------
// This function will generate the robots.txt for a website.
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_generatePageRobots($ciniki, $settings) {

    //
    // Store the content created by the page
    // Make sure everything gets generated ok before returning the content
    //
    $content = '';

    $content = "User-agent: *\n"
        . "Disallow:\n";

    return array('stat'=>'ok', 'content'=>$content);
}
?>
