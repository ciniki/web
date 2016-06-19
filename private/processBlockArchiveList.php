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
function ciniki_web_processBlockArchiveList(&$ciniki, $settings, $business_id, $block) {

    $content = '';

    //
    // Make sure there is content to edit
    //
    $months = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
    if( isset($block['archive']) ) {
        $prev_year = '';
        $years = '';
        foreach($block['archive'] as $year_month) {
            $year = $year_month['year'];
            $month_txt = $months[$year_month['month']-1];
            $month = sprintf("%02d", $year_month['month']);
            if( $year != $prev_year ) {
                if( $prev_year != '' ) { $years .= "</dd>"; }
                $years .= "<dt>$year</dt><dd>";
                $cm = '';
            }
            $years .= $cm . "<a href='" . $block['base_url'] . "/$year/$month'>" . "$month_txt</a>&nbsp;(" . $year_month['num_posts'] . ")";
            $cm = ', ';
            $prev_year = $year;
        }

        if( $years != '' ) {
            $content .= "<dl class='wide'>$years</dl>";
        } else {
            $content .= "<p>Currently no posts.</p>";
        }
    }
    
    return array('stat'=>'ok', 'content'=>$content);
}
?>
