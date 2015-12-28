<?php
//
// Description
// -----------
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_processBlockChartOverlay(&$ciniki, $settings, $business_id, $block) {

    if( !isset($block['labels']) || !isset($block['datasets']) ) {
        return array('stat'=>'ok', 'content'=>'');
    }

    if( isset($block['options']) ) {
        $options = $block['options'];
    } else {
        $options = array();
    }

	$content = '<div class="chart chart-overlay"><div class="chart-wrapper"><canvas id="canvas"></canvas></div></div>';

    //
    // Setup colours FIXME: This should be set via the business settings
    //
    $colours = array(
        '1'=>''
            . 'fillColor: "rgba(228,108,10,0.5)",'
            . 'pointColor: "rgba(228,108,10,0.5)",'
            . 'strokeColor: "rgba(233,94,0,0.5)",'
            . 'pointStrokeColor: "rgba(233,94,0,0.5)",'
            . 'highlightFill: "rgba(228,108,10,1.0)",'
            . 'highlightStroke: "rgba(233,94,0,1.0)",'
            . 'pointHighlightFill: "rgba(228,108,10,1.0)",'
            . 'pointHighlightStroke: "rgba(233,94,0,1.0)",'
            . '',
        '2'=>''
            . 'fillColor: "rgba(85,123,126,0.5)",'
            . 'pointColor: "rgba(85,123,126,0.5)",'
            . 'strokeColor: "rgba(73,91,103,0.5)",'
            . 'pointStrokeColor: "rgba(73,91,103,0.5)",'
            . 'highlightFill: "rgba(85,123,126,1.0)",'
            . 'highlightStroke: "rgba(73,91,103,1.0)",'
            . 'pointHighlightFill: "rgba(85,123,126,1.0)",'
            . 'pointHighlightStroke: "rgba(73,91,103,1.0)",'
            . '',
        );
    //
    // Build the javascript to display the graph
    //
    $js = 'var overlayData = {'
        . 'labels: [';
    foreach($block['labels'] as $label) {
        $js .= '"' . $label . '",';
    }
    $js .= '],'
        . 'datasets: [';
    foreach($block['datasets'] as $dataset) {
        $js .= '{'
            . 'label: "' . $dataset['label'] . '",'
            . 'type: "' . $dataset['type'] . '",'
            . '';
        $js .= $colours[$dataset['colour']];
        $js .= 'data: [';
        foreach($dataset['data'] as $data_point) {
            $js .= $data_point . ',';
        }
        $js .= '], '
            . '},';
    }
    $js .= ']};';
    $js .= "\n";
    $js .= 'var myOverlayChart = new Chart(document.getElementById("canvas").getContext("2d")).Overlay(overlayData, {'  
        . 'scaleBeginAtZero: ' . (isset($options['scaleBeginAtZero'])?$options['scaleBeginAtZero']:'true') . ','
        . 'populateSparseData: true,'
        . 'scaleLabel: "<%=value%>%",'
        . 'tooltipTemplate: "<%=value%>%",'
        . 'multiTooltipTemplate: "<%=value%>%",'
        . 'responsive: true,'
        . 'datasetFill : false,'
        . '});';
   
    $content .= '<script type="text/javascript">' . $js . '</script>';

    if( !isset($ciniki['response']['head']['scripts']) ) {
        $ciniki['response']['head']['scripts'] = array();
    }
    $ciniki['response']['head']['scripts'][] = array('src'=>'/ciniki-web-layouts/default/libs/Chart.min.js', 'type'=>'text/javascript');
	
	return array('stat'=>'ok', 'content'=>$content);
}
?>
