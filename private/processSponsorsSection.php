<?php
//
// Description
// -----------
// This function will generate the html content for the list of images to be displayed.
// The returned content will be HTML with links to the cached images for the browser to retrieve.
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
// base_url:        The base URL to prepend to all images.  This should be the domain or master domain plus sitename.
//                  It should also include anything that will prepend the image permalink.  eg http://ciniki.com/sitename/gallery
//
//                  It should not contain a trailing slash.
//
// images:          The array of images to use for the gallery.  Each element of the array must contain 'image_id' element
//                  as a reference to an image ID in the ciniki images module.
//
// maxlength:       The maxlength of the thumbnail image, as it will be square.
//
// Returns
// -------
//
function ciniki_web_processSponsorsSection($ciniki, $settings, $sponsors) {

    //
    // Store the content created by the page
    //
    $content = '';

    if( !isset($sponsors['sponsors']) || count($sponsors['sponsors']) < 1 ) {
        return array('stat'=>'ok', 'content'=>'');
    }

    $content .= "<h2 style='clear:right;'>" 
        . (isset($sponsors['title'])&&$sponsors['title']!=''?$sponsors['title']:'Sponsors') 
        . "</h2>";
    if( isset($sponsors['content']) && $sponsors['content'] != '' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
        $rc = ciniki_web_processContent($ciniki, $settings, $sponsors['content'], 'wide');  
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $content .= $rc['content'];
    }

    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processSponsorImages');
    $img_base_url = $ciniki['request']['base_url'] . '/sponsors';
    if( isset($sponsors['sponsors']) ) {
        $rc = ciniki_web_processSponsorImages($ciniki, $settings, $img_base_url, $sponsors['sponsors'], $sponsors['size']);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $content .= "<div class='sponsor-gallery'>" . $rc['content'] . "</div>";
    }

    return array('stat'=>'ok', 'content'=>$content);
}
?>
