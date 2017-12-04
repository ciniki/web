<?php
//
// Description
// -----------
// This function will generate the exhibition sponsors page for the tenant.
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_generatePageSponsors($ciniki, $settings) {

    //
    // Store the content created by the page
    // Make sure everything gets generated ok before returning the content
    //
    $content = '';
    $page_content = '';

    //
    // FIXME: Check if anything has changed, and if not load from cache
    //
    if( isset($settings['page-sponsors-sponsorship-active']) && $settings['page-sponsors-sponsorship-active'] == 'yes'
        && ciniki_core_checkModuleFlags($ciniki, 'ciniki.info', 0x400000)
        && isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] == 'sponsorship'
        && isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] == 'download'
        && isset($ciniki['request']['uri_split'][2]) && $ciniki['request']['uri_split'][2] != '' 
        ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'info', 'web', 'fileDownload');
        $rc = ciniki_info_web_fileDownload($ciniki, $ciniki['request']['tnid'], 'sponsorship', '', $ciniki['request']['uri_split'][2]);
        if( $rc['stat'] == 'ok' ) {
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            header("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: no-cache');
            $file = $rc['file'];
            if( $file['extension'] == 'pdf' ) {
                header('Content-Type: application/pdf');
            }
//          header('Content-Disposition: attachment;filename="' . $file['filename'] . '"');
            header('Content-Length: ' . strlen($file['binary_content']));
            header('Cache-Control: max-age=0');

            print $file['binary_content'];
            exit;
        }
        
        //
        // If there was an error locating the files, display generic error
        //
        return array('stat'=>'404', 'err'=>array('code'=>'ciniki.web.191', 'msg'=>'The file you requested does not exist.  Please check your link and try again.'));
    }

    $page_title = "Sponsors";
    if( isset($ciniki['tenant']['modules']['ciniki.sponsors']) ) {
        $pkg = 'ciniki';
        $mod = 'sponsors';
    } elseif( isset($ciniki['tenant']['modules']['ciniki.exhibitions']) ) {
        $pkg = 'ciniki';
        $mod = 'exhibitions';
    } else {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.82', 'msg'=>'No sponsor module enabled'));
    }

    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processURL');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processSponsors');

    if( isset($settings['page-sponsors-sponsorship-active']) && $settings['page-sponsors-sponsorship-active'] == 'yes'
        && ciniki_core_checkModuleFlags($ciniki, 'ciniki.info', 0x400000)
        && isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] == 'sponsorship'
        ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'info', 'web', 'pageDetails');
        $rc = ciniki_info_web_pageDetails($ciniki, $settings, $ciniki['request']['tnid'], array('permalink'=>'sponsorship'));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $info = $rc['content'];
        
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processPage');
        $rc = ciniki_web_processPage($ciniki, $settings, $ciniki['request']['base_url'] . "/sponsors", $info, 
            array());
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $page_content .= $rc['content'];
        
    } else {
        ciniki_core_loadMethod($ciniki, $pkg, $mod, 'web', 'sponsorList');
        $sponsorList = $pkg . '_' . $mod . '_web_sponsorList';
        $rc = $sponsorList($ciniki, $settings, $ciniki['request']['tnid']);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['levels']) ) {
            $sponsors = $rc['levels'];
            foreach($sponsors as $lnum => $level) {
                $page_content .= "<article class='page'>\n"
                    . "<header class='entry-title'><h1 class='entry-title'>";
                if( isset($level['level']['name']) ) {
                    $page_content .= $level['level']['name'] . ' ';
                }
                $page_content .= "</h1></header>\n"
                    . "<div class='entry-content'>\n"
                    . "";
                $rc = ciniki_web_processSponsors($ciniki, $settings, $level['level']['number'], $level['level']['categories']);
                if( $rc['stat'] == 'ok' ) {
                    $page_content .= $rc['content'];
                }
                $page_content .= "</div>\n"
                    . "</article>\n"
                    . "";
            }
        } else {
            $sponsors = $rc['categories'];
            $page_content .= "<article class='page'>\n"
                . "<header class='entry-title'><h1 class='entry-title'>Sponsors</h1></header>\n"
                . "<div class='entry-content'>\n"
                . "";
            $rc = ciniki_web_processSponsors($ciniki, $settings, 30, $sponsors);
            if( $rc['stat'] == 'ok' ) {
                $page_content .= $rc['content'];
            }
            $page_content .= "</div>\n"
                . "</article>\n"
                . "";
        }
    }

    //
    // Generate the complete page
    //

    //
    // Build the submenu if required
    //
    $submenu = array();
    if( isset($settings['page-sponsors-sponsorship-active']) && $settings['page-sponsors-sponsorship-active'] == 'yes'
        && ciniki_core_checkModuleFlags($ciniki, 'ciniki.info', 0x400000)
        ) {
        $submenu['sponsors'] = array('name'=>'Sponsors', 'url'=>$ciniki['request']['base_url'] . '/sponsors');
        $submenu['sponsorship'] = array('name'=>'Become a Sponsor', 'url'=>$ciniki['request']['base_url'] . '/sponsors/sponsorship');
    } 

    //
    // Add the header
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageHeader');
    $rc = ciniki_web_generatePageHeader($ciniki, $settings, 'Sponsors', $submenu);
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }
    $content .= $rc['content'];

    $content .= "<div id='content'>\n"
        . $page_content
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
