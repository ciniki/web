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
function ciniki_web_processBreadcrumbs(&$ciniki, $settings, $tnid, $breadcrumbs) {

    $content = '';

    //
    // Make sure there is content to edit
    //
    if( $breadcrumbs != NULL && count($breadcrumbs) > 0 ) {
        $content .= "<div class='breadcrumbs'>";
        $content .= "<ul class='breadcrumbs'>";
        $content .= "<li><a href='" . $ciniki['request']['base_url'] . "/'>Home</a></li>";
        $i = 1;
        foreach($breadcrumbs as $breadcrumb) {
            if( $i < count($breadcrumbs) ) {
                $content .= "<li><a href='" . $breadcrumb['url'] . "'>" . $breadcrumb['name'] . "</a></li>";
            } else {
                $content .= "<li>" . $breadcrumb['name'] . "</li>";
            }
            $i++;
        }
        $content .= "</ul>";
        $content .= "</div>";
    }
    
    return array('stat'=>'ok', 'content'=>$content);
}
?>
