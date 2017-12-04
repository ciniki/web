<?php
//
// Description
// -----------
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_generatePageCustom($ciniki, $settings, $pnum) {

    //
    // Store the content created by the page
    // Make sure everything gets generated ok before returning the content
    //
    $content = '';
    $page_content = '';

    //
    // FIXME: Check if anything has changed, and if not load from cache
    //

    $pname = 'page-custom-' . sprintf("%03d", $pnum);
    $page_title = $settings[$pname . '-name'];
    $article_title = '';
    if( isset($settings[$pname . '-title']) ) {
        $article_title = $settings[$pname . '-title'];
    }
    $page_content .= "<article class='page'>\n"
        . "<header class='entry-title'><h1 class='entry-title'>$article_title</h1></header>\n"
        . "<div class='entry-content'>";
    if( isset($settings[$pname . '-image']) && $settings[$pname . '-image'] != '' 
        && $settings[$pname . '-image'] > 0 ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
        $rc = ciniki_web_getScaledImageURL($ciniki, $settings[$pname . '-image'], 'original', '500', 0);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $page_content .= "<aside><div class='image-wrap'>"
            . "<div class='image'><img title='' alt='" . $ciniki['tenant']['details']['name'] . "' src='" . $rc['url'] . "' /></div>";
        if( isset($settings[$pname .'-image-caption']) && $settings[$pname .'-image-caption'] != '' ) {
            $page_content .= "<div class='image-caption'>" . $settings[$pname .'-image-caption'] . "</div>";
        }
        $page_content .= "</div></aside>";
    }

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash');
    $rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_web_content', 'tnid', $ciniki['request']['tnid'], 'ciniki.web', 'content', $pname);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    if( isset($rc['content'][$pname . '-content']) ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
        $rc = ciniki_web_processContent($ciniki, $settings, $rc['content'][$pname . '-content']);   
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $page_content .= $rc['content'];
    }
    
    $page_content .= "<br style='clear:both;'/>";
    $page_content .= "</div>\n"
        . "</article>\n";

    //
    // Add the header
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageHeader');
    $rc = ciniki_web_generatePageHeader($ciniki, $settings, $page_title, array());
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }
    $content .= $rc['content'];

    $content .= "<div id='content'>\n";
    $content .= $page_content;
    $content .= "</div>\n";

    //
    // Add the footer
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageFooter');
    $rc = ciniki_web_generatePageFooter($ciniki, $settings);
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }
    $content .= $rc['content'];

    return array('stat'=>'ok', 'content'=>$content);
}
?>
