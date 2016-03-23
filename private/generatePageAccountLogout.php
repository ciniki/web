<?php
//
// Description
// -----------
// This function will destroy the session and log the customer out.
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_generatePageAccountLogout(&$ciniki, $settings, $business_id, $timeout) {

    //
    // Clear all the session information
    //
    $ciniki['session']['customer'] = array();
    $ciniki['session']['cart'] = array();
    $ciniki['session']['user'] = array();
    $ciniki['session']['change_log_id'] = '';
    unset($_SESSION['customer']);
    unset($_SESSION['cart']);
    if( isset($_SESSION['login_referer']) ) {
        unset($_SESSION['login_referer']);
    }

    foreach($ciniki['business']['modules'] as $module => $m) {
        if( isset($ciniki['session'][$module]) ) {
            unset($ciniki['session'][$module]);
        }
        if( isset($_SESSION[$module]) ) {
            unset($_SESSION[$module]);
        }
    }

    //
    // Redirect them back to the home page
    //
    if( !isset($timeout) || $timeout != 'yes' ) {
        header('Location: ' . ($ciniki['request']['ssl_domain_base_url']!=''?$ciniki['request']['ssl_domain_base_url']:'/'));
        return array('stat'=>'ok');
    }

    //
    // Remove the timeout so regular logout will work
    //
    if( isset($_SESSION['account_timeout']) ) {
        unset($_SESSION['account_timeout']);
    }

    $base_url = $ciniki['request']['base_url'];
    $page = array(
        'title'=>'Timeout',
        );

    $breadcrumbs = array(
        'account'=>array('name'=>'Account', 'url'=>$base_url . '/account'),
        'timeout'=>array('name'=>'Timeout', 'url'=>$base_url . '/account/timeout'),
        );

	//
	// Add the header
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageHeader');
	$rc = ciniki_web_generatePageHeader($ciniki, $settings, 'Account', array());
	if( $rc['stat'] != 'ok' ) {	
		return $rc;
	}
	$page_content = $rc['content'];

	//
	// Check if article title and breadcrumbs should be displayed above content
	//
	if( (isset($settings['theme']['header-article-title']) && $settings['theme']['header-article-title'] == 'yes')
		|| (isset($settings['theme']['header-breadcrumbs']) && $settings['theme']['header-breadcrumbs'] == 'yes')
		) {
		$page_content .= "<div class='page-header'>";
		if( isset($settings['theme']['header-article-title']) && $settings['theme']['header-article-title'] == 'yes' ) {
			$page_content .= "<h1 class='page-header-title'>" . $page['title'] . "</h1>";
		}
		if( isset($settings['theme']['header-breadcrumbs']) && $settings['theme']['header-breadcrumbs'] == 'yes' && isset($breadcrumbs) ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processBreadcrumbs');
			$rc = ciniki_web_processBreadcrumbs($ciniki, $settings, $ciniki['request']['business_id'], $breadcrumbs);
			if( $rc['stat'] == 'ok' ) {
				$page_content .= $rc['content'];
			}
		}
		$page_content .= "</div>";
	}

    $page_content .= "<div id='content'>";
    $page_content .= "<article class='page'>\n";
    $page_content .= "<header class='entry-title'><h1 id='entry-title' class='entry-title'>Timeout</h1></header>";

    $page_content .= "<div class='form-message-content'><div class='form-result-message form-error-message'><div class='form-message-wrapper'>"
        . "<p>Your session has timed out.</p>"
        . "</div></div></div>";
    
    $page_content .= "</article>";
    $page_content .= "</div>";

	//
	// Add the footer
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageFooter');
	$rc = ciniki_web_generatePageFooter($ciniki, $settings);
	if( $rc['stat'] != 'ok' ) {	
		return $rc;
	}
	$page_content .= $rc['content'];
	
    //
    // Script is done.
    //
    return array('stat'=>'ok', 'content'=>$page_content);
}
?>
