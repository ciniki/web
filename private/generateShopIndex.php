<?php
//
// Description
// -----------
// This function will generate the shop page for the website.
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_generateShopIndex(&$ciniki, $settings) {

    //
    // Store the content created by the page
    // Make sure everything gets generated ok before returning the content
    //
    $content = '';
    $page_content = '';

    //
    // FIXME: Check if anything has changed, and if not load from cache
    //
    

    //
    // Add the header
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageHeader');
    $rc = ciniki_web_generatePageHeader($ciniki, $settings, 'Home', array());
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }
    $content .= $rc['content'];

    

    $aside_content = '';
    if( isset($settings['page-shop-image']) && $settings['page-shop-image'] != '' && $settings['page-shop-image'] > 0 ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
        $rc = ciniki_web_getScaledImageURL($ciniki, $settings['page-shop-image'], 'original', '500', 0);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $aside_content .= "<div class='image borderless'><img title='' alt='About' src='" . $rc['url'] . "' /></div>";
    }

    //
    // Generate the content of the page
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash');
    $rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_web_content', 'business_id', $ciniki['request']['business_id'], 'ciniki.web', 'content', 'page-shop');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    print_r($rc);
    
    $page_content = '';
    if( isset($rc['content']['page-shop-content']) ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
        if( $aside_content == '' ) {
            $rc = ciniki_web_processContent($ciniki, $settings, $rc['content']['page-shop-content'], 'wide');   
        } else {
            $rc = ciniki_web_processContent($ciniki, $settings, $rc['content']['page-shop-content'], 'wide');   
        }
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $page_content = $rc['content'];
    }

    $content .= "<div id='content' class='evensplit'>\n"
        . "<article class='page'>\n";
    if( $aside_content != '' ) {
        $content .= "<aside>" . $page_content . "</aside>";
    }
    $content .= "<div class='entry-content'>\n"
        . $page_content
        . "</div>"
        . "";
        
    $content .= "</article>"
        . "</div>"
        . "";

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
