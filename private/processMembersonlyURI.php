<?php
//
// Description
// -----------
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
// events:          The array of events as returned by ciniki_events_web_list.
// limit:           The number of events to show.  Only 2 events are shown on the homepage.
//
// Returns
// -------
//
function ciniki_web_processMembersonlyURI(&$ciniki, $settings, $depth, $base_url, $parent_id) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'membersonly', 'web', 'pageDetails');

    //
    // Check if at the current depth there is a page
    //
    if( isset($ciniki['request']['uri_split'][$depth]) && $ciniki['request']['uri_split'][$depth] != '' ) {
        $page_permalink = $ciniki['request']['uri_split'][$depth];
        $rc = ciniki_membersonly_web_pageDetails($ciniki, $settings, $ciniki['request']['business_id'], 
            array('permalink'=>$page_permalink, 'parent_id'=>$parent_id));
        if( $rc['stat'] != 'ok' && $rc['stat'] != '404' ) {
            return $rc;
        }
        if( $rc['stat'] == '404' ) {
            if( $depth == 0 ) {
                // Get the root page
                $rc = ciniki_membersonly_web_pageDetails($ciniki, $settings, $ciniki['request']['business_id'], 
                    array('page_id'=>$parent_id, 'parent_id'=>0));
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
                $page = $rc['page'];
                $page['permalink'] = '';
                $depth--;
            } else {
                return $rc;
            }
        } else {
            $page = $rc['page'];
        }

        //
        // Check if subpage is specified
        //
        if( isset($ciniki['request']['uri_split'][($depth+1)]) 
            && $ciniki['request']['uri_split'][($depth+1)] != '' 
            && isset($page['children'][$ciniki['request']['uri_split'][($depth+1)]]) ) {
            return ciniki_web_processMembersonlyURI($ciniki, $settings, ($depth+1), $base_url . ($page['permalink']!=''?'/'.$page['permalink']:''), $page['id']);
        }

        //
        // Check if gallery was requested
        //
        elseif( isset($ciniki['request']['uri_split'][($depth+1)]) 
            && $ciniki['request']['uri_split'][($depth+1)] == 'gallery' 
            && isset($ciniki['request']['uri_split'][($depth+2)]) 
            && $ciniki['request']['uri_split'][($depth+2)] != '' 
            ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processGalleryImage');
            return ciniki_web_processGalleryImage($ciniki, $settings, $ciniki['request']['business_id'],
                array('item'=>$page, 
                    'article_title'=>$page['title'],
                    'image_permalink'=>$ciniki['request']['uri_split'][($depth+2)]));
        }

        //
        // Check if a file was specified to be downloaded
        //
        elseif( isset($ciniki['request']['uri_split'][($depth+1)]) 
            && $ciniki['request']['uri_split'][($depth+1)] == 'download' 
            && isset($ciniki['request']['uri_split'][($depth+2)]) 
            && $ciniki['request']['uri_split'][($depth+2)] != '' 
            ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'membersonly', 'web', 'fileDownload');
            $rc = ciniki_membersonly_web_fileDownload($ciniki, $ciniki['request']['business_id'], 
                $page['id'], $ciniki['request']['uri_split'][($depth+2)]);
            if( $rc['stat'] == 'ok' ) {
                header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
                header("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
                header('Cache-Control: no-cache, must-revalidate');
                header('Pragma: no-cache');
                $file = $rc['file'];
                if( $file['extension'] == 'pdf' ) {
                    header('Content-Type: application/pdf');
                }
//              header('Content-Disposition: attachment;filename="' . $file['filename'] . '"');
                header('Content-Length: ' . strlen($file['binary_content']));
                header('Cache-Control: max-age=0');

                print $file['binary_content'];
                exit;
            }
            
            //
            // If there was an error locating the files, display generic error
            //
            return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'2199', 'msg'=>'The file you requested does not exist.'));
        }

        //
        // Otherwise process the page
        //
        else {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processPage');
            return ciniki_web_processPage($ciniki, $settings, $base_url, $page, array());
        }
    }

    return array('stat'=>'ok', 'content'=>$content);
}
?>
