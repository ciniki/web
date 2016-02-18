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
    $name = preg_replace('/[^a-zA-Z0-9]/', '', $block['canvas']);

    $content = '';

	if( isset($block['title']) && $block['title'] != '' ) {
		$content .= '<h2 class="wide">' . $block['title'] . "</h2>";
	}

	$content .= '<div class="chart chart-overlay"><div class="chart-wrapper"><canvas id="' . $block['canvas'] . '"></canvas></div></div>';

    //
    // Setup colours FIXME: This should be set via the business settings
    //
    $colours = array(
        '1'=>''
            . 'fillColor: "rgba(85,123,126,0.5)",'
            . 'pointColor: "rgba(85,123,126,0.5)",'
            . 'strokeColor: "rgba(73,91,103,0.5)",'
            . 'pointStrokeColor: "rgba(73,91,103,0.5)",'
            . 'highlightFill: "rgba(85,123,126,1.0)",'
            . 'highlightStroke: "rgba(73,91,103,1.0)",'
            . 'pointHighlightFill: "rgba(85,123,126,1.0)",'
            . 'pointHighlightStroke: "rgba(73,91,103,1.0)",'
            . '',
        '2'=>''
            . 'fillColor: "rgba(228,108,10,0.5)",'
            . 'pointColor: "rgba(228,108,10,0.5)",'
            . 'strokeColor: "rgba(233,94,0,0.5)",'
            . 'pointStrokeColor: "rgba(233,94,0,0.5)",'
            . 'highlightFill: "rgba(228,108,10,1.0)",'
            . 'highlightStroke: "rgba(233,94,0,1.0)",'
            . 'pointHighlightFill: "rgba(228,108,10,1.0)",'
            . 'pointHighlightStroke: "rgba(233,94,0,1.0)",'
            . '',
        );
    //
    // Build the javascript to display the graph when it should be displayed multiple graphs
    //
    if( isset($block['page_limit']) && count($block['labels']) > $block['page_limit'] ) {
        $js_labels = array();
        $i = 0;
        $num_labels = count($block['labels']);
        foreach($block['labels'] as $label) {
            if( isset($block['start']) && $block['start'] == 'end' ) {
                $k = floor(($num_labels-1)/$block['page_limit']) - floor(($num_labels-$i-1)/$block['page_limit']);
            } else {
                $k = floor($i/$block['page_limit']);
            }
            if( !isset($js_labels[$k]) ) {
                $js_labels[$k] = '';
            }
            $js_labels[$k] .= '"' . $label . '",';
            $i++;
        }
//        print "<pre>" . print_r($js_labels, true) . "</pre>";
        $i = 0;
        $js_datasets = array();
        foreach($block['datasets'] as $dataset) {
            $i = 0;
            $num_data = count($dataset['data']);
            $last_k = 0;
            foreach($dataset['data'] as $data_point) {
                if( isset($block['start']) && $block['start'] == 'end' ) {
                    $k = floor(($num_data-1)/$block['page_limit']) - floor(($num_data-$i-1)/$block['page_limit']);
                } else {
                    $k = floor($i/$block['page_limit']);
                }
                // Check if new page dataset starting
                if( $i == 0 || $k != $last_k ) {
                    // Check if old page dataset needs closing
                    if( $i > 0 ) {
                        $js_datasets[$last_k] .= '], '
                            . '},';
                    }
                    if( !isset($js_datasets[$k]) ) {
                        $js_datasets[$k] = '';
                    }
                    $js_datasets[$k] .= '{'
                        . 'label: "' . $dataset['label'] . '",'
                        . 'type: "' . $dataset['type'] . '",'
                        . '';
                    $js_datasets[$k] .= $colours[$dataset['colour']];
                    $js_datasets[$k] .= 'data: [';
                }
                $js_datasets[$k] .= $data_point . ',';
                $last_k = $k;
                $i++;
            }
            $js_datasets[$k] .= '], '
                . '},';
        }
//        print "<pre>" . print_r($js_datasets, true) . "</pre>";
        $js = "var overlayData_$name = [];";
        for($i=0;$i<ceil(count($block['labels'])/$block['page_limit']);$i++) {
            $js .= "overlayData_" . $name . "[$i] = {"
                . 'labels: [';
            $js .= $js_labels[$i];
            $js .= '],'
                . 'datasets: [';
            $js .= $js_datasets[$i];
            $js .= ']};';
            $js .= "\n";
        }
        //
        // Include multipage nav
        //
        $content .= "<div class='multipage-nav'><div class='multipage-nav-content'>";
        $content .= "<span class='multipage-nav-button multipage-nav-button-first'>"
            . "<a onclick='switchOverlayChart_$name(0);'><span class='multipage-nav-button-text'>First</span></a>"
            . "</span>";
        $content .= "<span class='multipage-nav-button multipage-nav-button-prev'>"
            . "<a onclick='switchOverlayChart_$name(0);'><span class='multipage-nav-button-text'>Prev</span></a>"
            . "</span>";
        for($i=0;$i<ceil(count($block['labels'])/$block['page_limit']);$i++) {
            $content .= "<span id='multipage-nav-button-$name-$i' class='multipage-nav-button" . ($i==$k?' multipage-nav-button-selected':'') . "'><a onclick='switchOverlayChart_$name($i);'>"
                . "<span class='multipage-nav-button-text'>" . ($i+1) . "</span></a></span>";
        }
        $content .= "<span class='multipage-nav-button multipage-nav-button-next'>"
            . "<a onclick='switchOverlayChart_$name($k);'><span class='multipage-nav-button-text'>Next</span></a>"
            . "</span>";
        $content .= "<span class='multipage-nav-button multipage-nav-button-last'>"
            . "<a onclick='switchOverlayChart_$name($k);'><span class='multipage-nav-button-text'>Last</span></a>"
            . "</span>";
        $content .= "</div></div>";

        $js .= 'var myOverlayChart_' . $name . ' = new Chart(document.getElementById("' . $block['canvas'] . '").getContext("2d")).Overlay(overlayData_' . $name . '[' . $k . '], {'  
            . 'scaleBeginAtZero: ' . (isset($options['scaleBeginAtZero'])?$options['scaleBeginAtZero']:'true') . ','
            . 'populateSparseData: true,'
            . 'scaleLabel: "<%=value%>%",'
            . 'tooltipTemplate: "<%=value%>%",'
            . 'multiTooltipTemplate: "<%=value%>%",'
            . 'responsive: true,'
            . 'datasetFill : false,'
            . '});';
        $js .= 'function switchOverlayChart_' . $name . '(n){'
            . 'myOverlayChart_' . $name . '.destroy();'
            // Setup the new chart with new dataset
            . 'myOverlayChart_' . $name . ' = new Chart(document.getElementById("' . $block['canvas'] . '").getContext("2d")).Overlay(overlayData_' . $name . '[n], {'  
            . 'scaleBeginAtZero: ' . (isset($options['scaleBeginAtZero'])?$options['scaleBeginAtZero']:'true') . ','
            . 'populateSparseData: true,'
            . 'scaleLabel: "<%=value%>%",'
            . 'tooltipTemplate: "<%=value%>%",'
            . 'multiTooltipTemplate: "<%=value%>%",'
            . 'responsive: true,'
            . 'datasetFill : false,'
            . '});'
            // Set the highlight button
            . 'for(var i=0;i<' . ceil(count($block['labels'])/$block['page_limit']) . ';i++){'
                . "var e=document.getElementById('multipage-nav-button-$name-' + i);"
                . "if(i==n){"
                    . "if(!e.classList.contains('multipage-nav-button-selected')){e.classList.add('multipage-nav-button-selected');}"
                . "}else{"
                    . "if(e.classList.contains('multipage-nav-button-selected')){e.classList.remove('multipage-nav-button-selected');}"
                . "}"
            . '}'
            . '};';
       
        $content .= '<script type="text/javascript">' . $js . '</script>';
    } 
    
    //
    // Display a single graph with no paging
    //
    else {
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
        $js .= 'var myOverlayChart = new Chart(document.getElementById("' . $block['canvas'] . '").getContext("2d")).Overlay(overlayData, {'  
            . 'scaleBeginAtZero: ' . (isset($options['scaleBeginAtZero'])?$options['scaleBeginAtZero']:'true') . ','
            . 'populateSparseData: true,'
            . 'scaleLabel: "<%=value%>%",'
            . 'tooltipTemplate: "<%=value%>%",'
            . 'multiTooltipTemplate: "<%=value%>%",'
            . 'responsive: true,'
            . 'datasetFill : false,'
            . '});';
       
        $content .= '<script type="text/javascript">' . $js . '</script>';
    }

    if( !isset($ciniki['response']['head']['scripts']) ) {
        $ciniki['response']['head']['scripts'] = array();
    }
    $ciniki['response']['head']['scripts'][] = array('src'=>'/ciniki-web-layouts/default/libs/Chart.min.js', 'type'=>'text/javascript');
	
	return array('stat'=>'ok', 'content'=>$content);
}
?>
