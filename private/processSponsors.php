<?php
//
// Description
// -----------
// This function will process a list of sponsors to produce a list for a website
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
function ciniki_web_processSponsors($ciniki, $settings, $level, $categories) {

    $content = '';

    if( count($categories) > 0 ) {
        $content .= "<table class='sponsors-list'><tbody>\n";
        $prev_category = NULL;
        foreach($categories as $cnum => $c) {
            if( $prev_category != NULL ) {
                $content .= "</td></tr>\n";
            }
            if( isset($c['category']['name']) && $c['category']['name'] != '' ) {
                $content .= "<tr><th>"
                    . "<span class='sponsors-category'>" . $c['category']['name'] . "</span></th>"
                    . "<td>";
            } else {
                $content .= "<tr>"
                    . "<td>";
//              $content .= "<tr><th>"
//                  . "<span class='sponsors-category'></span></th>"
//                  . "<td>";
            }
            $content .= "<table class='sponsors-category-list'><tbody>\n";
            foreach($c['category']['sponsors'] as $pnum => $sponsor) {
                $sponsor = $sponsor['sponsor'];
                if( isset($sponsor['url']) ) {
                    $rc = ciniki_web_processURL($ciniki, $sponsor['url']);
                    if( $rc['stat'] != 'ok' ) {
                        return $rc;
                    }
                    $url = $rc['url'];
                    $display_url = $rc['display'];
                } else {
                    $url = '';
                }

                // Setup the sponsor image
                $content .= "<tr class='sponsor-start'>";
                if( isset($sponsor['description']) && $sponsor['description'] != '' ) {
                    $content .= "<td class='sponsors-image'>";
                } else {
                    $content .= "<td class='sponsors-image' rowspan='3'>";
                }
                if( isset($sponsor['image_id']) && $sponsor['image_id'] > 0 ) {
                    if( $level == 50 ) {
                        $rc = ciniki_web_getScaledImageURL($ciniki, $sponsor['image_id'], 'original', 400, 0);
                    } elseif( $level == 40 ) {
                        $rc = ciniki_web_getScaledImageURL($ciniki, $sponsor['image_id'], 'original', 300, 0);
                    } elseif( $level == 30 ) {
                        $rc = ciniki_web_getScaledImageURL($ciniki, $sponsor['image_id'], 'original', 250, 0);
                    } elseif( $level == 20 ) {
                        $rc = ciniki_web_getScaledImageURL($ciniki, $sponsor['image_id'], 'original', 200, 0);
                    } else {
                        $rc = ciniki_web_getScaledImageURL($ciniki, $sponsor['image_id'], 'original', 150, 0);
                    }
                    if( $rc['stat'] != 'ok' ) {
                        return $rc;
                    }
                    $content .= "<div class='image-sponsors-thumbnail'>"
                        . ($url!=''?"<a target='_blank' href='$url' title='" . $sponsor['name'] . "'>":'')
                        . "<img title='' alt='" . $sponsor['name'] . "' src='" . $rc['url'] . "' />"
                        . ($url!=''?'</a>':'')
                        . "</div>";
                }
                $content .= "</td>";

                // Setup the details
                $content .= "<td class='sponsors-details'>";
                $content .= "<span class='sponsors-title'>";
                if( $url != '' ) {
                    $content .= "<a target='_blank' href='$url' title='" . $sponsor['name'] . "'>" . $sponsor['name'] . "</a>";
                } else {
                    $content .= $sponsor['name'];
                }
                $content .= "</span>";
                if( isset($settings['site-layout']) && $settings['site-layout'] == 'twentyone' ) {
                    $content .= "<div class='sponsors-description'>";
                } else {
                    $content .= "</td></tr>";
                    $content .= "<tr><td class='sponsors-description'>";
                }
                if( isset($sponsor['description']) && $sponsor['description'] != '' ) {
                    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
                    $rc = ciniki_web_processContent($ciniki, $settings, $sponsor['description']);   
                    if( $rc['stat'] != 'ok' ) {
                        return $rc;
                    }
                    $content .= $rc['content'];
                }
                if( isset($settings['site-layout']) && $settings['site-layout'] == 'twentyone' ) {
                    $content .= "</div><div class='sponsors-more'>";
                } else {
                    $content .= "</td></tr>";
                    $content .= "<tr><td class='sponsors-more'>";
                }
                if( $url != '' ) {
                    $content .= "<a target='_blank' class='external-link' href='$url'>$display_url</a>";
                }
                if( isset($settings['site-layout']) && $settings['site-layout'] == 'twentyone' ) {
                    $content .= "</div></td></tr>";
                } else {
                    $content .= "</td></tr>";
                }
            }
            $content .= "</tbody></table>";
        }

        $content .= "</td></tr>\n</tbody></table>\n";
    } else {
        $content .= "<p>Currently no sponsors.</p>";
    }

    return array('stat'=>'ok', 'content'=>$content);
}
?>
