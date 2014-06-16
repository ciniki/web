<?php
//
// Description
// -----------
// This function will process a list of events, and format the html.
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure, similar to ciniki variable but only web specific information.
// events:			The array of events as returned by ciniki_events_web_list.
// limit:			The number of events to show.  Only 2 events are shown on the homepage.
//
// Returns
// -------
//
function ciniki_web_processSlider(&$ciniki, $settings, $slider) {

	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processURL');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getCroppedImageURL');

	//
	// Make sure the slider is setup with at least one image
	//
	if( !isset($slider['images']) || count($slider['images']) < 1 ) {
		return array('stat'=>'ok', 'content'=>'');
	}

	//
	// Setup the size for the slider images
	//
	$slider_width = 1024;
	$slider_height = 300;
	if( isset($slider['size']) ) {
		if( $slider['size'] == 'tiny' ) {
			$slider_height = 100;
		} elseif( $slider['size'] == 'small' ) {
			$slider_height = 200;
		} elseif( $slider['size'] == 'medium' ) {
			$slider_height = 300;
		} elseif( $slider['size'] == 'large' ) {
			$slider_height = 400;
		} elseif( $slider['size'] == 'xlarge' ) {
			$slider_height = 500;
		} elseif( $slider['size'] == 'xxlarge' ) {
			$slider_height = 600;
		}
	} 

	$slider_pause_time = 4000;
	if( isset($slider['pause']) ) {
		if( $slider['pause'] == 'xslow' ) {
			$slider_pause_time = 7000;
		} elseif( $slider['pause'] == 'slow' ) {
			$slider_pause_time = 5500;
		} elseif( $slider['pause'] == 'medium' ) {
			$slider_pause_time = 4000;
		} elseif( $slider['pause'] == 'fast' ) {
			$slider_pause_time = 2500;
		} elseif( $slider['pause'] == 'xfast' ) {
			$slider_pause_time = 1000;
		}
	}

	$slider_effect = 'slide';
	if( isset($slider['effect']) && $slider['effect'] != '' ) {
		$slider_effect = $slider['effect'];
	}

	$image_list = '';
	$pager_list = '';
	$count = 0;
	foreach($slider['images'] as $image) {
		//
		// Check if the image_id is specified
		//
		if( !isset($image['image_id']) || $image['image_id'] == 0 ) {
			//
			// FIXME: Use the object:object_id to lookup the image
			//

			//
			// Skip this image if no image specified
			//
			continue;
		}

		//
		// Check for the URL for this image if not specified
		//
		if( !isset($image['url']) || $image['url'] == '' ) {
			//
			// FIXME: Use the object:object_id to lookup the url
			//
		}

		//
		// Generate the image for the slider, must be cropped to exact dimensions
		//
		$rc = ciniki_web_getCroppedImageURL($ciniki, $image['image_id'], 'original', 
			array('height'=>$slider_height, 'width'=>$slider_width, 'position'=>'middle-center'));
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$style = '';
		if( $image['url'] != '' ) {
			$url_target = '';
			if( preg_match("/^http/", $image['url']) ) {
				$url_target = '_blank';
			}
			$image_list .= "<li $style>"
				. "<a href='" . $image['url'] . "' target='$url_target' title='" . $image['caption'] . "'>"
				. "<img title='' alt='" . $image['caption'] . "' src='" . $rc['url'] . "' /></a>"
				. "</li>";
		} else {
			$image_list .= "<li $style>"
				. "<img title='' alt='" . $image['caption'] . "' src='" . $rc['url'] . "' />"
				. "</li>";
		}

		$pager_list .= "<a rel='$count' class='" . ($count==0?'active':'') . "' onclick='javascript: sliders[0].goTo($count);'>" . ($count + 1) . "</a>";

		$count++;
	}

	$javascript = "var Slider = function() { this.initialize.apply(this, arguments) }\n";
	$javascript .= "Slider.prototype = {\n";

	$javascript .= "	initialize: function(slider, pager) {\n";
	$javascript .= "		this.ul = slider.children[0];\n";
	$javascript .= "		this.li = this.ul.children;\n";
	$javascript .= "		this.pager = pager;\n";
	$javascript .= "		this.resize();\n";

	// make <ul> as large as all <li>’s

	$javascript .= "		this.currentIndex = 0\n";
	$javascript .= "	},\n";

	$javascript .= "	resize: function(index) {\n";
	$javascript .= "		for(i in this.li) {\n";
	$javascript .= "			if( this.li[i].style != null ) { \n";
	$javascript .= "				this.li[i].style.width = this.ul.parentElement.clientWidth + 'px';\n";
//	$javascript .= "				this.li[i].style.height = ((this.li[i].children[0].clientHeight*this.ul.parentElement.clientWidth)/this.li[i].children[0].clientWidth) + 'px';\n";
	$javascript .= "			}\n";
	$javascript .= "		}\n";
	$javascript .= "		this.ul.style.width = (this.ul.parentElement.clientWidth * this.li.length) + 'px'\n";
	$javascript .= "		this.ul.style.maxWidth = (this.ul.parentElement.clientWidth * this.li.length) + 'px'\n";
	$javascript .= "		this.ul.style.height = this.li[0].children[0].clientHeight + 'px'\n";
	$javascript .= "		this.ul.parentElement.style.height = this.li[0].children[0].clientHeight + 'px'\n";
//	$javascript .= "		this.ul.style.width = (this.li[0].clientWidth * this.li.length) + 'px'\n";
//	$javascript .= "		this.ul.style.height = (this.li[0].clientHeight) + 'px'\n";
//	$javascript .= "		this.ul.parentElement.style.height = (this.li[0].clientHeight) + 'px'\n";
//	$javascript .= "		this.ul.style.maxHeight = this.li[0].clientHeight + 'px'\n";

	$javascript .= "	},\n";
	$javascript .= "	goTo: function(index) {\n";
	// filter invalid indices
	$javascript .= "		if( index >= this.li.length ) { index = 0; }\n";
	$javascript .= "		if (index < 0 || index > this.li.length - 1)\n";
	$javascript .= "		return\n";

	// move <ul> left
	$javascript .= "		this.ul.style.left = '-' + (100 * index) + '%'\n";
	$javascript .= "		if( this.pager != null ) { \n";
	$javascript .= "			this.pager.children[this.currentIndex].className = '';\n";
	$javascript .= "			this.pager.children[index].className = 'active';\n";
	$javascript .= "		}\n";
	$javascript .= "		this.currentIndex = index\n";
	$javascript .= "	},\n";

	$javascript .= "	goToPrev: function() {\n";
	$javascript .= "		this.goTo(this.currentIndex - 1)\n";
	$javascript .= "	},\n";

	$javascript .= "	goToNext: function() {\n";
	$javascript .= "		this.goTo(this.currentIndex + 1)\n";
	$javascript .= "	},\n";

	$javascript .= "}\n";
	$javascript .= "var sliders = [];\n";
	$javascript .= "function slider_setup() {\n";
	$javascript .= "	sliders.push(new Slider(document.getElementById('slider-ctl'), document.getElementById('slider-pager')));\n";
	$javascript .= "	setInterval(function() {sliders[0].goToNext()}, $slider_pause_time);\n";
	$javascript .= "}\n";


	if( $image_list != '' ) {
		if( !isset($ciniki['request']['inline_javascript']) ) {
			$ciniki['request']['inline_javascript'] = '';
		}
		$ciniki['request']['inline_javascript'] .= "<script type='text/javascript'>" . $javascript . "</script>";
		if( !isset($ciniki['request']['onload']) ) {
			$ciniki['request']['onload'] = '';
		}
		$ciniki['request']['onload'] .= 'slider_setup();';
		// Setup the onresize to adjust the slider images when a window resize occurs
		if( !isset($ciniki['request']['onresize']) ) {
			$ciniki['request']['onresize'] = '';
		}
		$ciniki['request']['onresize'] .= 'sliders[0].resize();';

		
		$content = "<div class='slider'>";
		$content .= "<div class='slider-image-wrap'>";
		$content .= "<div id='slider-ctl' class='slider-image'>";
		$content .= "<ul>";
		$content .= $image_list;
		$content .= "</ul>\n";
		$content .= "</div>\n";
		$content .= "</div>\n";
		$content .= "<div class='slider-pager-wrap'>";
		$content .= "<div id='slider-pager' class='slider-pager'>";
		$content .= $pager_list;
		$content .= "</div>\n";
		$content .= "</div>\n";
		$content .= "</div>\n";
	}

	return array('stat'=>'ok', 'content'=>$content);
}
?>

