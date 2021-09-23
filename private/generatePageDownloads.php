<?php
//
// Description
// -----------
// This function will generate the downloads page for the website.  The downloads
// are from the filedepot module, but may be from other modules as well in the future.
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_generatePageDownloads($ciniki, $settings) {

    //
    // Check if a file was specified to be downloaded
    //
    $download_err = '';
    if( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] != '' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'filedepot', 'web', 'download');
        $rc = ciniki_filedepot_web_download($ciniki, $ciniki['request']['tnid'], $ciniki['request']['uri_split'][0]);
        if( $rc['stat'] == 'ok' ) {
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            header("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: no-cache');
            $finfo = finfo_open(FILEINFO_MIME);
            if( $finfo ) {
                header('Content-Type: ' . finfo_file($finfo, $rc['storage_filename']));
            }
//          header('Content-Disposition: attachment;filename="' . $rc['filename'] . '"');
            header('Content-Length: ' . filesize($rc['storage_filename']));
            header('Cache-Control: max-age=0');

            $fp = fopen($rc['storage_filename'], 'rb');
            fpassthru($fp);
            exit;
        }
        
        //
        // If there was an error locating the files, display generic error
        //
        return array('stat'=>'404', 'err'=>array('code'=>'ciniki.web.38', 'msg'=>'We are sorry but the file you requested doesn\'t exist.'));
    }


    //
    // Store the content created by the page
    // Make sure everything gets generated ok before returning the content
    //
    $content = '';

    //
    // FIXME: Check if anything has changed, and if not load from cache
    //
    

    //
    // Add the header
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageHeader');
    $page_title = 'Downloads';
    if( isset($settings['page-downloads-name']) && $settings['page-downloads-name'] != '' ) {
        $page_title = $settings['page-downloads-name'];
    }
    $rc = ciniki_web_generatePageHeader($ciniki, $settings, $page_title, array());
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }
    $content .= $rc['content'];

    //
    // Generate the content of the page
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash');
    $rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_web_content', 'tnid', $ciniki['request']['tnid'], 'ciniki.web', 'content', 'page-downloads');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    $page_content = '';
    if( isset($rc['content']) && isset($rc['content']['page-downloads-content']) ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
        $rc = ciniki_web_processContent($ciniki, $settings, $rc['content']['page-downloads-content']);  
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $page_content = $rc['content'];
    }
    
    $content .= "<div id='content'>\n"
        . "<article class='page'>\n"
        . "<header class='entry-title'><h1 class='entry-title'>$page_title</h1></header>\n"
        . "<div class='entry-content'>\n"
        . "";
    if( $page_content != '' ) {
        $content .= $page_content;
    }

    //
    // Get the list of downloads to be displayed
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'filedepot', 'web', 'list');
    $rc = ciniki_filedepot_web_list($ciniki, $ciniki['request']['tnid']);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['categories']) ) {
        $categories = $rc['categories'];
    } else {
        $categories = array();
    }

    $content .= "<table class='downloads-list'>\n"
        . "";
    $prev_category = NULL;
    foreach($categories as $cnum => $c) {
        if( $prev_category != NULL ) {
            $content .= "</td></tr>\n";
        }
        if( isset($c['category']['name']) && $c['category']['name'] != '' ) {
            $content .= "<tr><th>"
                . "<span class='downloads-category'>" . $c['category']['name'] . "</span></th>"
                . "<td>";
            // $content .= "<h2>" . $c['category']['name'] . "</h2>";
        } else {
            $content .= "<tr><th class='downloads-category-empty'>"
                . "<span class='downloads-category'></span></th>"
                . "<td>";
        }
        foreach($c['category']['files'] as $fnum => $download) {
            //$content .= "<p>";
            $url = $ciniki['request']['base_url'] . '/downloads/' . $download['file']['permalink'] . '.' . $download['file']['extension'];
            $content .= "<span class='downloads-title'>";
            if( $url != '' ) {
                $content .= "<a target='_blank' href='" . $url . "' title='" . $download['file']['name'] . "'>" . $download['file']['name'] . "</a>";
            } else {
                $content .= $download['file']['name'];
            }
            $content .= "</span>";
            if( isset($download['file']['description']) && $download['file']['description'] != '' ) {
                $content .= "<br/><span class='downloads-description'>" . $download['file']['description'] . "</span>";
            }
            if( isset($settings['site-layout']) && $settings['site-layout'] == 'twentyone' ) {
                $content .= "<div class='downloads-more'>"
                    . "<a class='downloads-url' target='_blank' href='" . $url . "' title='" . $download['file']['name'] . "'>Download</a>"
                    . "</div>";
            }
//          if( $url != '' ) {
//              $content .= "<br/><a class='downloads-url' target='_blank' href='" . $url . "' title='" . $download['file']['name'] . "'>" . $display_url . "</a>";
//          }
            $content .= "<br/><br/>";
            // $content .= "</p>";
        }
    }

    $content .= "</td></tr>\n</table>\n";

    $content .= "</div>"
        . "</article>"
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
