<?php
//
// Description
// -----------
// This function will process a list of events, and format the html.
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
// events:          The array of events as returned by ciniki_events_web_list.
//
// Returns
// -------
//
function ciniki_web_processExhibitions($ciniki, $settings, $exhibitions, $args) {

    $page_limit = 0;
    if( isset($args['limit']) ) {
        $page_limit = $args['limit'];
    }

    $content = "<table class='cilist'>\n"
        . "";
    $total = count($exhibitions);
    $count = 0;
    foreach($exhibitions as $eid => $e) {
        if( $page_limit > 0 && $count >= $page_limit ) { $count++; break; }
        $exhibition = $e['exhibition'];
        // Display the date
        $exhibition_date = $exhibition['start_month'];
        $exhibition_date .= " " . $exhibition['start_day'];
        if( $exhibition['end_day'] != '' && ($exhibition['start_day'] != $exhibition['end_day'] || $exhibition['start_month'] != $exhibition['end_month']) ) {
            if( $exhibition['end_month'] != '' && $exhibition['end_month'] == $exhibition['start_month'] ) {
                $exhibition_date .= " - " . $exhibition['end_day'];
            } else {
                $exhibition_date .= " - " . $exhibition['end_month'] . " " . $exhibition['end_day'];
            }
        }
        $exhibition_date .= ", " . $exhibition['start_year'];
        if( $exhibition['end_year'] != '' && $exhibition['start_year'] != $exhibition['end_year'] ) {
            $exhibition_date .= "/" . $exhibition['end_year'];
        }
        $content .= "<tr><th><span class='cilist-category'>$exhibition_date</span>";
        if( $exhibition['location'] != '' ) {
            $content .= " <span class='cilist-subcategory'>" . $exhibition['location'] . "</span>";
        }
        $content .= "</th>"
            . "<td>";
        // Display the brief details
        $content .= "<table class='cilist-categories'><tbody>\n";
        if( $exhibition['num_images'] > 0 || $exhibition['long_description'] != '' ) {
            $exhibition_url = $ciniki['request']['base_url'] . "/exhibitions/" . $exhibition['permalink'];
        } else {
            $exhibition_url = '';
        }

        // Setup the exhibitor image
        $content .= "<tr><td class='cilist-image' rowspan='3'>";
        if( isset($exhibition['image_id']) && $exhibition['image_id'] > 0 ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
            $rc = ciniki_web_getScaledImageURL($ciniki, $exhibition['image_id'], 'thumbnail', '150', 0);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $content .= "<div class='image-cilist-thumbnail'>";
            if( $exhibition_url != '' ) {
                $content .= "<a href='$exhibition_url' title=\"" . htmlspecialchars(strip_tags($exhibition['name'])) . "\"><img title='' alt=\"" . htmlspecialchars(strip_tags($exhibition['name'])) . "\" src='" . $rc['url'] . "' /></a>";
            } else {
                $content .= "<img title='' alt='" . htmlspecialchars(strip_tags($exhibition['name'])) . "' src='" . $rc['url'] . "' />";
            }
            $content .= "</div></aside>";
        }
        $content .= "</td>";

        // Setup the details
        $content .= "<td class='cilist-details'>";
        $content .= "<p class='cilist-title'>";
        if( $exhibition_url != '' ) {
            $content .= "<a href='$exhibition_url' title=\"" . htmlspecialchars(strip_tags($exhibition['name'])) . "\">" . $exhibition['name'] . "</a>";
        } else {
            $content .= $exhibition['name'];
        }
        $content .= "</p>";
        $content .= "</td></tr>";
        $content .= "<tr><td class='cilist-description'>";
        if( isset($exhibition['description']) && $exhibition['description'] != '' ) {
            $content .= "<span class='cilist-description'>" . $exhibition['description'] . "</span>";
        }
        $content .= "</td></tr>";
        if( $exhibition_url != '' ) {
            $content .= "<tr><td class='cilist-more'><a href='$exhibition_url'>... more</a></td></tr>";
        } elseif( ($count+1) == $total || ($page_limit > 0 && ($count+1) >= $page_limit) ) {
            // Display a more for extra padding between lists
            $content .= "<tr><td class='cilist-more'></td></tr>";
        }
        $content .= "</tbody></table>";
        $content .= "</td></tr>";
        $count++;
    }
    $content .= "</table>\n"
        . "";

    //
    // Check to see if we need prev and next buttons
    //
    $nav_content = '';
    if( $page_limit > 0 && isset($args['base_url']) && $args['base_url'] != '' ) {
        $prev = '';
        if( isset($args['page']) && $args['page'] > 1 ) {
            if( isset($args['base_url']) ) {
                $prev .= "<a href='" . $args['base_url'] . "?page=" . ($args['page']-1) . "'>";
                array_push($ciniki['response']['head']['links'], array('rel'=>'prev', 'href'=>$args['base_url'] . "?page=" . ($args['page']-1)));
                if( isset($args['prev']) && $args['prev'] != '' ) {
                    $prev .= $args['prev'];
                } else {
                    $prev .= 'Prev';
                }
                $prev .= "</a>";
            }
        }
        $next = '';
        if( isset($args['page']) && $count > $page_limit ) {
            if( isset($args['base_url']) ) {
                $next .= "<a href='" . $args['base_url'] . "?page=" . ($args['page']+1) . "'>";
                array_push($ciniki['response']['head']['links'], array('rel'=>'next', 'href'=>$args['base_url'] . "?page=" . ($args['page']+1)));
                if( isset($args['prev']) && $args['prev'] != '' ) {
                    $next .= $args['next'];
                } else {
                    $next .= 'Next';
                }
                $next .= "</a>";
            }
        }
        if( $next != '' || $prev != '' ) {
            $nav_content = "<nav class='content-nav'>"
                . "<span class='prev'>$next</span>"
                . "<span class='next'>$prev</span>"
                . "</nav>"
                . "";
        }
    }

    return array('stat'=>'ok', 'content'=>$content, 'nav'=>$nav_content);
}
?>
