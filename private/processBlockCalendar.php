<?php
//
// Description
// -----------
// Calendar block was developed for the ciniki.calendars module to display a reponsive calendar on a web page.
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_processBlockCalendar($ciniki, $settings, $business_id, $block) {

    //
    // Check to make sure the start and end date are set. This allows the calendar to 
    // display more than one month.
    //
    if( !isset($block['start']) || !is_a($block['start'], 'DateTime') ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.194', 'msg'=>'Invalid date'));
    }
    $cdt = $block['start'];
    if( !isset($block['end']) || !is_a($block['end'], 'DateTime') ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.195', 'msg'=>'Invalid date'));
    }
    $edt = $block['end'];

    //
    // Setup the settings for the calendar display
    //
    $weekday_start = isset($block['weekday_start']) ? $block['weekday_start'] : 0;
    $start_year = isset($block['start_year']) ? $block['start_year'] : 0;

    //
    // Check for end year
    //
    if( isset($block['end_year']) ) {
        $end_year = $block['end_year'];
    } else {
        $dt = new DateTime('now', new DateTimezone('UTC'));
        $dt->add(new DateInterval('P2Y'));
        $end_year = $dt->format('Y');
    }

    //
    // Add the month, year and forward back controls
    //
    $content = "<div class='calendar-controls'>";
    $content .= "<div class='calendar-controls-arrow'>";
    $content .= "<a class='calendar-controls-prev'"
        . " title='" . (isset($block['prev_title']) ? $block['prev_title'] : 'Previous') . "' "
        . " href='" . $block['prev_url'] . "'>"
        . "<span class='fa-icon'>" . (isset($block['prev_title']) ? $block['prev_title'] : '&#xf0d9;') . "</span>"
        . "</a>";
    $content .= "</div>";
    $content .= "<div class='calendar-controls-date'>" . $block['calendar_label'] . "</div>";
    $content .= "<div class='calendar-controls-arrow'>";
    $content .= "<a class='calendar-controls-next'"
        . " title='" . (isset($block['next_title']) ? $block['next_title'] : 'Next') . "' "
        . " href='" . $block['next_url'] . "'>"
        . "<span class='fa-icon'>" . (isset($block['next_title']) ? $block['next_title'] : '&#xf0da;') . "</span>"
        . "</a>";
    $content .= "</div>";
    $content .= "</div>";

    //
    // Start the calendar
    //
    if( isset($block['display']) && $block['display'] == 'grid' ) {
        $content .= "<table class='calendar calendar-grid'>";
    } else {    
        $content .= "<table class='calendar calendar-list'>";
    }

    //
    // Add the header
    //
    $days = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
    $content .= "<thead><tr>";
    //
    // Add the days of the week starting at the specified start day.
    //
    for($i=$weekday_start;$i<7;$i++) {
        $content .= "<th>" . $days[$i] . "</th>";
    }
    //
    // If weekday start is not a sunday, then fill in the remaining days
    //
    for($i = 0; $i < $weekday_start; $i++) {
        $content .= "<th>" . $days[$i] . "</th>";
    }
    $content .= "</tr>";

    $content .= "<tbody><tr>";
    //
    // Decide if there are blank spaces
    //
    if( $cdt->format('w') > $weekday_start ) {
        for($i = 0; $i < $cdt->format('w'); $i++ ) {
            $content .= "<td class='calendar-day calendar-day-blank'></td>";
        }
    }
    //
    // Add the days between the start and end dates
    //
    $cur_date_text = $cdt->format('Y-m-d');
    $oneday = new DateInterval('P1D');
    $edt->add($oneday);                 // Set to the next day so we know to stop at this date
    $end_before_text = $edt->format('Y-m-d');
    while( $cur_date_text != $end_before_text ) {
        if( $cdt->format('w') == 0 ) {
            if( $cdt->format('j') > 1 ) {
                $content .= "</tr>";
                $content .= "<tr>";
            }
        }
        $content .= "<td class='calendar-day'>";
        $content .= "<div class='calendar-date'>";
        $content .= "<span class='calendar-day-date'>" . $cdt->format('j') . "</span>";
        $content .= "<span class='calendar-day-weekday'>" . $cdt->format('D') . "</span>";
        $content .= "</div>";
        $content .= "<div class='calendar-day-items'>";

        //
        // Add the items to the day
        //
        if( isset($block['items'][$cur_date_text]['items']) ) {
            foreach($block['items'][$cur_date_text]['items'] as $item) {
                if( isset($item['url']) && $item['url'] != '' ) {
                    $content .= "<a href='" . $item['url'] . "'>";
                }
                $content .= "<div class='calendar-item";
                if( isset($item['classes']) ) {
                    foreach($item['classes'] as $class) {
                        if( $class != '' ) {
                            $content .= ' calendar-item-' . preg_replace("/[^a-zA-Z\-]/", '', $class);
                        }
                    }
                }
                $content .= "'";
                if( isset($item['style']) && $item['style'] != '' ) {
                    $content .= ' style="' . $item['style'] . '"';
                }
                $content .= ">";
                $content .= "<div class='calendar-item-title'>" . $item['title'] . "</div>";
                $content .= "<div class='calendar-item-time'>" . $item['time_text'] . "</div>";
                $content .= "</div>";
                if( isset($item['url']) && $item['url'] != '' ) {
                    $content .= "</a>";
                }
            }
        } else {
            $content .= "<div class='calendar-item-blank'></div>";
        }
        $content .= "</div>";
        $content .= "</td>";
        
        //
        // Advance to the next day
        //
        $cdt->add($oneday);
        $cur_date_text = $cdt->format('Y-m-d');
    }

    //
    // Add blank spaces
    //
    if( $block['end']->format('w') > 0 && $block['end']->format('w') < 6 ) {
        for($i = $block['end']->format('w'); $i < 7; $i++) {
            $content .= "<td class='calendar-day calendar-day-blank'></td>";
        }
    }

    $content .= "</tbody></table>";

    return array('stat'=>'ok', 'content'=>$content);
}
?>
