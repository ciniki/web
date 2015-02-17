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
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');

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

	$slider_pause_time = 4500;
	if( isset($slider['speed']) ) {
		if( $slider['speed'] == 'xslow' ) {
			$slider_pause_time = 8000;
		} elseif( $slider['speed'] == 'slow' ) {
			$slider_pause_time = 6000;
		} elseif( $slider['speed'] == 'medium' ) {
			$slider_pause_time = 4500;
		} elseif( $slider['speed'] == 'fast' ) {
			$slider_pause_time = 2500;
		} elseif( $slider['speed'] == 'xfast' ) {
			$slider_pause_time = 1000;
		}
	}

	$slider_effect = 'slide';
	if( isset($slider['effect']) && $slider['effect'] != '' ) {
		$slider_effect = $slider['effect'];
	}

	if( !isset($slider['resize']) ) {
		$slider_format = 'cropped';
	} elseif( $slider['resize'] == 'scaled' ) {
		$slider_format = 'scaled';
	} else {
		$slider_format = 'cropped';
	}

	$image_list = '';
	$pager_list = '';
	$count = 0;
	$style = 'width:1024px;';
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
		if( $slider_format == 'scaled' ) {
			$rc = ciniki_web_getScaledImageURL($ciniki, $image['image_id'], 'original', 0, $slider_height);
		} else {
			$rc = ciniki_web_getCroppedImageURL($ciniki, $image['image_id'], 'original', 
				array('height'=>$slider_height, 'width'=>$slider_width, 'position'=>$image['image_offset'], 
					'last_updated'=>$image['last_updated']));
		}
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( $image['url'] != '' ) {
			$url_target = '';
			if( preg_match("/^http/", $image['url']) ) {
				$url_target = '_blank';
			}
			$image_list .= "<li style='${style}height:${slider_height}px;max-height:${slider_height}px;'>"
				. "<a href='" . $image['url'] . "' target='$url_target' title='" . $image['caption'] . "'>"
				. "<img title='' alt='" . $image['caption'] . "' src='" . $rc['url'] . "' /></a>"
				. "</li>";
		} else {
			$image_list .= "<li style='${style}height:${slider_height}px;max-height:${slider_height}px;'>"
				. "<img title='' alt='" . $image['caption'] . "' src='" . $rc['url'] . "' />"
				. "</li>";
		}

		$pager_list .= "<a rel='$count' class='" . ($count==0?'active':'') . "' onclick='javascript: sliders[0].goTo($count);'>" . ($count + 1) . "</a>";

		$count++;
		$style = 'display:none;';
	}

	$javascript = "var Slider = function() { this.initialize.apply(this, arguments) }\n";
	$javascript .= "Slider.prototype = {\n";

	$javascript .= "	initialize: function(slider, pager) {\n";
	$javascript .= "		this.ul = slider.childNodes[0];\n";
	$javascript .= "		this.li = this.ul.childNodes;\n";
	$javascript .= "		this.pager = pager;\n";
	$javascript .= "		this.resize();\n";

	// make <ul> as large as all <li>’s

	$javascript .= "		this.currentIndex = 0\n";
	$javascript .= "	},\n";

	if( $slider_format == 'scaled' ) {
		$javascript .= "	resize: function(index) {\n";
		$javascript .= "		var height_spacing = 0;\n";
		$javascript .= "		var ulwidth = 0;\n";
		$javascript .= "		for(i in this.li) {\n";
		$javascript .= "			if( this.li[i].style != null ) { \n";
		$javascript .= "				var li_e = this.li[i].childNodes[0];\n";
		$javascript .= "				if( this.li[i].childNodes[0].nodeName == 'A' ) {";
		$javascript .= "					li_e = this.li[i].childNodes[0].childNodes[0];\n";
		$javascript .= "				}\n";
		$javascript .= "				if( height_spacing == 0 ) {";
		$javascript .= "					if( window.getComputedStyle ) {";
		$javascript .= "						var style = window.getComputedStyle(li_e);";
		$javascript .= "						height_spacing += parseInt(style.getPropertyValue('padding-left').replace(/px/,''))*2;";
		$javascript .= "						height_spacing += parseInt(style.getPropertyValue('border-left-width').replace(/px/,''))*2;";
		$javascript .= "					} else if( li_e.currentStyle ) {";
		$javascript .= "						height_spacing += parseInt(li_e.currentStyle['padding'].replace(/px/,''))*2;";
		$javascript .= "						height_spacing += parseInt(li_e.currentStyle['borderLeftWidth'].replace(/px/,''))*2;";
		$javascript .= "					}";
		$javascript .= "					height_spacing += 4;";
//		$javascript .= "console.log(height_spacing);\n";
		$javascript .= "				}\n";
		$javascript .= "				this.li[i].style.width = (this.ul.parentElement.clientWidth - 0) + 'px';";
		$javascript .= "				this.li[i].style.maxHeight = $slider_height + height_spacing + 'px';";
		$javascript .= "				this.li[i].style.lineHeight = $slider_height + 'px';";
		$javascript .= "				li_e.style.maxWidth = (this.ul.parentElement.clientWidth - height_spacing) + 'px';";
		$javascript .= "				li_e.style.maxHeight = ($slider_height - height_spacing) + 'px';";
		$javascript .= "				this.li[i].style.display = 'inline-block';\n";
		$javascript .= "				ulwidth += this.ul.parentElement.clientWidth;\n";
		$javascript .= "			}\n";
		$javascript .= "		}\n";
		$javascript .= "		this.ul.style.width = ulwidth + 'px'\n";
		$javascript .= "		this.ul.style.maxWidth = ulwidth + 'px'\n";
		$javascript .= "	},\n";
	} else {
		$javascript .= "	resize: function(index) {\n";
		$javascript .= "		for(i in this.li) {\n";
		$javascript .= "			if( this.li[i].style != null ) { \n";
		$javascript .= "				this.li[i].style.display = 'inline-block';\n";
		$javascript .= "				this.li[i].style.width = this.ul.parentElement.clientWidth + 'px';\n";
	//	$javascript .= "				this.li[i].style.height = ((this.li[i].children[0].clientHeight*this.ul.parentElement.clientWidth)/this.li[i].children[0].clientWidth) + 'px';\n";
		$javascript .= "			}\n";
		$javascript .= "		}\n";
		$javascript .= "		this.ul.style.width = (this.ul.parentElement.clientWidth * this.li.length) + 'px'\n";
		$javascript .= "		this.ul.style.maxWidth = (this.ul.parentElement.clientWidth * this.li.length) + 'px'\n";
	//	$javascript .= "console.log(this.li[0].children[0].children[0].clientHeight);";
		$javascript .= "		if( this.li[0].children[0].nodeName == 'A' ) {";
		$javascript .= "			this.ul.style.height = this.li[0].children[0].children[0].clientHeight + 'px'\n";
		$javascript .= "			this.ul.parentElement.style.height = this.li[0].children[0].children[0].clientHeight + 'px'\n";
		$javascript .= "		} else {";
		$javascript .= "			this.ul.style.height = this.li[0].children[0].clientHeight + 'px'\n";
		$javascript .= "			this.ul.parentElement.style.height = this.li[0].children[0].clientHeight + 'px'\n";
		$javascript .= "		}";
	//	$javascript .= "		this.ul.style.width = (this.li[0].clientWidth * this.li.length) + 'px'\n";
	//	$javascript .= "		this.ul.style.height = (this.li[0].clientHeight) + 'px'\n";
	//	$javascript .= "		this.ul.parentElement.style.height = (this.li[0].clientHeight) + 'px'\n";
	//	$javascript .= "		this.ul.style.maxHeight = this.li[0].clientHeight + 'px'\n";
		$javascript .= "	},\n";
	}
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
	$javascript .= "function slider_resize(i) {\n";
	$javascript .= "	if( sliders != null && sliders[i] != null ) { sliders[i].resize(); };\n";
	$javascript .= "}\n";


	$content = '';
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
//		$ciniki['request']['onresize'] .= 'if( sliders != null ) { sliders[0].resize(); }';
		$ciniki['request']['onresize'] .= 'slider_resize(0);';

		
		$content = "<div class='slider slider-" . $slider_format . "'>";
		$content .= "<div class='slider-image-wrap'>";
		$content .= "<div id='slider-ctl' class='slider-image'";
		if( $slider_format == 'scaled' ) {
//			$content .= " style='height:" . ($slider_height+22) . "px;'";
		}
		$content .=	">";
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
