<?php
//
// Description
// -----------
// This function will generate a 404 error page for the master tenant.  This is for all
// errors when we don't know what the tenant is they were requesting.
//
// Arguments
// ---------
// ciniki:
//
// Returns
// -------
//
function ciniki_web_generateMaster404($ciniki, $errors) {

    //
    // Store the content created by the page
    // Make sure everything gets generated ok before returning the content
    //
    $content = '';
    $page_content = '';


    //
    // Get the details for the master tenant
    //
    $ciniki['request']['tnid'] = $ciniki['config']['ciniki.core']['master_tnid'];
    $ciniki['request']['base_url'] = '';
    require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/tenants/web/details.php');
    $rc = ciniki_tenants_web_details($ciniki, $ciniki['config']['ciniki.core']['master_tnid']);
    if( $rc['stat'] != 'ok' ) {
        print_error($rc, 'Website not configured.');
        exit;
    }
    $ciniki['tenant']['details'] = $rc['details'];
    if( isset($rc['details']) ) {
        $ciniki['tenant']['social'] = $rc['social'];
    }

    //
    // Get the master tenant settings
    //
    require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/web/private/settings.php');
    $rc = ciniki_web_settings($ciniki, $ciniki['config']['ciniki.core']['master_tnid']);
    if( $rc['stat'] != 'ok' ) {
        print_error($rc, 'Website not configured.');
        exit;
    }
    $settings = $rc['settings'];

    //
    // Add the header
    //
    header("Status: 404 Not Found", true, 404);
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageHeader');
    $rc = ciniki_web_generatePageHeader($ciniki, $settings, 'Page not found', array());
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }
    $content .= $rc['content'];

    $content .= "<div id='content'>\n";
    $content .= "<article class='page'>\n";
    $content .= "<div class='entry-content'>\n";
    $content .= "<header class='entry-title'><h1 class='entry-title'>Unable to find page</h1></header>";
    $content .= "<p>I'm sorry, but we are unable to find the page you requested.</p>";
    $content .= "<p><br/></p>";
    if( $errors != null && isset($errors['err']['msg']) ) {
        $ciniki['request']['error_codes_msg'] = "err:" . $errors['err']['code'];
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
