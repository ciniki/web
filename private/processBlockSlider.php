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
function ciniki_web_processBlockSlider(&$ciniki, $settings, $tnid, $block) {

    //
    // Store the content created by the page
    //
    $content = '';

    if( isset($block['slider-id']) && $block['slider-id'] > 0 ) {
        //
        // Load the slider
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'sliderLoad');
        $rc = ciniki_web_sliderLoad($ciniki, $settings, $tnid, $block['slider-id']);
        if( $rc['stat'] == 'ok' ) {
            $slider = $rc['slider'];
            //
            // Process the slider content
            //
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processSlider');
            $rc = ciniki_web_processSlider($ciniki, $settings, $slider);
            if( $rc['stat'] == 'ok' ) {
                $content = $rc['content'];
            }
        }
    }

    return array('stat'=>'ok', 'content'=>$content);
}
?>
