<?php
//
// Description
// -----------
// This function will generate the home page for the website.
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_generatePage404($ciniki, $settings, $errors) {

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
//  header("HTTP/1.0 404 Not Found");
    header("Status: 404 Not Found", true, 404);
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageHeader');
    $rc = ciniki_web_generatePageHeader($ciniki, $settings, 'Page not found', array());
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }
    $content .= $rc['content'];

    //
    // Check if article title and breadcrumbs should be displayed above content
    //
    if( (isset($settings['theme']['header-article-title']) && $settings['theme']['header-article-title'] == 'yes')
        || (isset($settings['theme']['header-breadcrumbs']) && $settings['theme']['header-breadcrumbs'] == 'yes')
        ) {
        $content .= "<div class='page-header'>";
        if( isset($settings['theme']['header-article-title']) && $settings['theme']['header-article-title'] == 'yes' ) {
            $content .= "<h1 class='page-header-title'>404</h1>";
        }
        if( isset($settings['theme']['header-breadcrumbs']) && $settings['theme']['header-breadcrumbs'] == 'yes' && isset($breadcrumbs) ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processBreadcrumbs');
            $rc = ciniki_web_processBreadcrumbs($ciniki, $settings, $ciniki['request']['business_id'], $breadcrumbs);
            if( $rc['stat'] == 'ok' ) {
                $content .= $rc['content'];
            }
        }
        $content .= "</div>";
    }

    //
    // Generate page content
    //
    $content .= "<div id='content'>\n";
    $content .= "<article class='page'>\n";
    $content .= "<div class='entry-content'>\n";
    $content .= "<header class='entry-title'><h1 class='entry-title'>Unable to find page</h1></header>";
    if( $errors != null && isset($errors['err']['msg']) ) {
        $content .= "<p>" . $errors['err']['msg'] . "</p>";
        $ciniki['request']['error_codes_msg'] = 'err:' . $errors['err']['code'];
        // Check for nested errors
        if( isset($errors['err']['err']) ) {
            $err = $errors['err'];
            while( isset($err['err']) ) {
                $ciniki['request']['error_codes_msg'] .= ',' . $err['err']['code'];
                $err = $err['err'];
            }
        }
    } else {
        $content .= "<p>I'm sorry, but we are unable to find the page you requested.</p>";
    }
    $content .= "</div>";
    $content .= "</article>";
    $content .= "</div>";

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
