<?php
//
// Description
// -----------
// This function will process a list of workshops, and format the html.
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
// workshops:           The array of workshops as returned by ciniki_workshops_web_list.
// limit:           The number of workshops to show.  Only 2 workshops are shown on the homepage.
//
// Returns
// -------
//
function ciniki_web_processWorkshops($ciniki, $settings, $workshops, $limit) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processURL');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');

    $page_limit = 0;
    if( isset($args['limit']) ) {
        $page_limit = $args['limit'];
    }

    if( isset($settings['site-layout']) && $settings['site-layout'] == 'twentyone' ) {
        $content = "<div class='image-list'>";
        $count = 0;
        foreach($workshops as $wid => $workshop) {
            if( $page_limit > 0 && $count >= $page_limit ) { $count++; break; }
            $workshop_date = $workshop['start_month'];
            $workshop_date .= " " . $workshop['start_day'];
            if( $workshop['end_day'] != '' && ($workshop['start_day'] != $workshop['end_day'] || $workshop['start_month'] != $workshop['end_month']) ) {
                if( $workshop['end_month'] != '' && $workshop['end_month'] == $workshop['start_month'] ) {
                    $workshop_date .= " - " . $workshop['end_day'];
                } else {
                    $workshop_date .= " - " . $workshop['end_month'] . " " . $workshop['end_day'];
                }
            }
            $workshop_date .= ", " . $workshop['start_year'];
            if( $workshop['end_year'] != '' && $workshop['start_year'] != $workshop['end_year'] ) {
                $workshop_date .= "/" . $workshop['end_year'];
            }

            $url = $ciniki['request']['base_url'] . "/workshops/" . $workshop['permalink'];
            $url_target = '';

            //
            // Start the image list item
            //
            $content .= "<div class='image-list-entry-wrap'>"
                . "<div class='image-list-entry'>";

            // Start image
            $content .= "<div class='image-list-image'>";
            if( $workshop['image_id'] > 0 ) {
                $rc = ciniki_web_getScaledImageURL($ciniki, $workshop['image_id'], 'original', 1200, 0, 70);
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
                $content .= "<div class='image-list-wrap image-list-original'>"
                    . ($url!=''?"<a href='$url' target='$url_target' title='" . $workshop['name'] . "'>":'')
                    . "<img title='' alt='" . $workshop['name'] . "' src='" . $rc['url'] . "' />"
                    . ($url!=''?'</a>':'')
                    . "</div>";
            } else {
                $content .= "<div class='image-list-wrap image-list-original no-image'>"
                    . ($url!=''?"<a href='$url' target='$url_target' title='" . $workshop['name'] . "'>":'')
                    . "<img title='' alt='" . $workshop['name'] . "' src='/ciniki-web-layouts/default/img/noimage_240.png' />"
                    . ($url!=''?'</a>':'')
                    . "</div>";
            }
            $content .= "</div>";
            
            $content .= "<div class='image-list-details'>";
            $content .= "<div class='image-list-title'><h2>" . $workshop['name'] . "</h2></div>";
            $content .= "<div class='image-list-subtitle'><h3>" . $workshop_date . "</h3></div>";
            $content .= "<div class='image-list-subtitle'><h3>" . $workshop['times'] . "</h3></div>";
            if( isset($workshop['description']) && $workshop['description'] != '' ) {
                $rc = ciniki_web_processContent($ciniki, $settings, $workshop['description'], 'image-list-description');
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
                $content .= "<div class='image-list-content'>" . $rc['content'] . "</div>";
            }

            if( $url != '' ) {
                $content .= "<div class='image-list-more'>";
                $content .= "<a href='$url' target='$url_target'>... more</a>";
                $content .= "</div>";
            } 
            $content .= "</div>";

            $content .= "</div></div>";
            $count++;
        }
        $content .= "</div>";

        return array('stat'=>'ok', 'content'=>$content);
    }


    $content = "<table class='cilist'><tbody>";
    $count = 0;
    foreach($workshops as $workshop_num => $workshop) {
        if( $limit > 0 && $count >= $limit ) { break; }
        $workshop_date = $workshop['start_month'];
        $workshop_date .= " " . $workshop['start_day'];
        if( $workshop['end_day'] != '' && ($workshop['start_day'] != $workshop['end_day'] || $workshop['start_month'] != $workshop['end_month']) ) {
            if( $workshop['end_month'] != '' && $workshop['end_month'] == $workshop['start_month'] ) {
                $workshop_date .= " - " . $workshop['end_day'];
            } else {
                $workshop_date .= " - " . $workshop['end_month'] . " " . $workshop['end_day'];
            }
        }
        $workshop_date .= ", " . $workshop['start_year'];
        if( $workshop['end_year'] != '' && $workshop['start_year'] != $workshop['end_year'] ) {
            $workshop_date .= "/" . $workshop['end_year'];
        }

        $javascript_onclick = '';
        if( $workshop['isdetails'] == 'yes' || (isset($workshop['num_images']) && $workshop['num_images'] > 0)
            || (isset($workshop['num_files']) & $workshop['num_files'] > 0) ) {
            $workshop_url = $ciniki['request']['base_url'] . "/workshops/" . $workshop['permalink'];
            $javascript_onclick = " onclick='javascript:location.href=\"$workshop_url\";' ";
        } else {
            if( $workshop['url'] != '' ) {
                $rc = ciniki_web_processURL($ciniki, $workshop['url']);
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
                $workshop_url = $rc['url'];
                $workshop_display_url = $rc['display'];
            } else {
                $workshop_url = '';
            }
        }

        $content .= "<tr><th><span class='cilist-category'>$workshop_date</span>";
        if( isset($workshop['times']) && $workshop['times'] != '' ) {
            $content .= "<span class='cilist-subcategory'>" . $workshop['times'] . "</span>";
        }
        $content .= "</th><td>\n";
        $content .= "<table class='cilist-categories'><tbody>\n";

        // Setup the workshop image
        $content .= "<tr><td class='cilist-image' rowspan='3'>";
        if( isset($workshop['image_id']) && $workshop['image_id'] > 0 ) {
            $rc = ciniki_web_getScaledImageURL($ciniki, $workshop['image_id'], 'thumbnail', '150', 0);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            if( $workshop_url != '' ) {
                $content .= "<div class='image-cilist-thumbnail'>"
                    . "<a href='$workshop_url' title='" . $workshop['name'] . "'><img title='' alt='" . $workshop['name'] . "' src='" . $rc['url'] . "' /></a>"
                    . "</div></aside>";
            } else {
                $content .= "<div class='image-cilist-thumbnail'>"
                    . "<img title='' alt='" . $workshop['name'] . "' src='" . $rc['url'] . "' />"
                    . "</div></aside>";
            }
        }
        $content .= "</td>";

        // Setup the details
        $content .= "<td class='cilist-title'>";
        $content .= "<p class='cilist-title'>";
        if( $workshop_url != '' ) {
            $content .= "<a href='$workshop_url' title='" . $workshop['name'] . "'>" . $workshop['name'] . "</a>";
        } else {
            $content .= $workshop['name'];
        }
        $content .= "</p>";
        $content .= "</td></tr>";
        $content .= "<tr><td $javascript_onclick class='cilist-details'>";
        if( isset($workshop['description']) && $workshop['description'] != '' ) {
            $rc = ciniki_web_processContent($ciniki, $settings, $workshop['description'], 'cilist-description');
            if( $rc['stat'] == 'ok' ) {
                $content .= $rc['content'];
            }
            // $content .= "<p class='cilist-description'>" . $workshop['description'] . "</p>";
        } elseif( isset($workshop['short_description']) && $workshop['short_description'] != '' ) {
            $rc = ciniki_web_processContent($ciniki, $settings, $workshop['short_description'], 'cilist-description');
            if( $rc['stat'] == 'ok' ) {
                $content .= $rc['content'];
            }
        }
        if( $workshop['isdetails'] == 'yes' || (isset($workshop['num_images']) && $workshop['num_images'] > 0)
            || (isset($workshop['num_files']) & $workshop['num_files'] > 0) ) {
            $content .= "<tr><td class='cilist-more'><a href='$workshop_url'>... more</a></td></tr>";
        } elseif( $workshop_url != '' ) {
            $content .= "<tr><td class='cilist-more'><a href='$workshop_url'>$workshop_display_url</a></td></tr>";
        }
        $count++;
        $content .= "</tbody></table>";
        $content .= "</td></tr>";
    }
    $content .= "</tbody></table>\n";

    return array('stat'=>'ok', 'content'=>$content);
}
?>
