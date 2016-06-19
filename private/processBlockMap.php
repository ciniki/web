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
function ciniki_web_processBlockMap(&$ciniki, $settings, $business_id, $block) {

    
    $content = '';
    if( !isset($ciniki['request']['inline_javascript']) ) {
        $ciniki['request']['inline_javascript'] = '';
    }
    $ciniki['request']['inline_javascript'] = ''
        . '<script type="text/javascript">'
        . 'var gmap_loaded=0;'
        . 'function gmap_initialize() {'
            . 'var myLatlng = new google.maps.LatLng(' . $block['latitude'] . ',' . $block['longitude'] . ');'
            . 'var mapOptions = {'
                . 'zoom: 13,'
                . 'center: myLatlng,'
                . 'panControl: false,'
                . 'zoomControl: true,'
                . 'scaleControl: true,'
                . 'mapTypeId: google.maps.MapTypeId.ROADMAP'
            . '};'
            . 'var map = new google.maps.Map(document.getElementById("googlemap"), mapOptions);'
            . 'var marker = new google.maps.Marker({'
                . 'position: myLatlng,'
                . 'map: map,'
                . 'title:"",'
                . '});'
        . '};'
        . 'function loadMap() {'
            . 'if(gmap_loaded==1) {return;}'
            . 'var script = document.createElement("script");'
            . 'script.type = "text/javascript";'
            . 'script.src = "' . ($ciniki['request']['ssl']=='yes'?'https':'http') . '://maps.googleapis.com/maps/api/js?key=' . $ciniki['config']['ciniki.web']['google.maps.api.key'] . '&sensor=false&callback=gmap_initialize";'
            . 'document.body.appendChild(script);'
            . 'gmap_loaded=1;'
        . '};'
        . '</script>'
        . $ciniki['request']['inline_javascript'];
    if( isset($block['aside']) && $block['aside'] == 'yes' ) {
        $content .= "<aside><div class='googlemap' id='googlemap'></div></aside>";
    } else {
        $content .= "<div class='googlemap' id='googlemap'></div>";
    }

    return array('stat'=>'ok', 'content'=>$content);
}
?>
