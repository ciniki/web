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
function ciniki_web_processSlider(&$ciniki, $settings, $slider_id) {

	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processURL');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getCroppedImageURL');


	//
	// Lookup the slider details
	//
	$rc = ciniki_web_sliderLoad($ciniki, $settings, $slider_id);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$slider = $rc['slider'];

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

	$slider_pause_time = 3000;
	if( isset($slider['pause']) ) {
		if( $slider['pause'] == 'xslow' ) {
			$slider_pause_time = 4000;
		} elseif( $slider['pause'] == 'slow' ) {
			$slider_pause_time = 3000;
		} elseif( $slider['pause'] == 'fast' ) {
			$slider_pause_time = 2000;
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
	$javascript = "function ciniki_web_runSlider('" . $slider['id'] . "') {"
		. "};";
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
		if( $count > 0 ) {	
			$style = "style='display: none;' ";
		}
		if( $image['url'] != '' ) {
			$image_list .= "<li>"
				. "<a href='" . $image['url'] . "' target='$url_target' title='" . $item['title'] . "'>"
				. "<img title='' alt='" . $item['title'] . "' src='" . $rc['url'] . "' /></a>"
				. "</li>";
		} else {
			$image_list .= "<li>"
				. "<img title='' alt='" . $item['title'] . "' src='" . $rc['url'] . "' />"
				. "</li>";
		}
		$image_list .= "/>";

		$pager_list .= "<a rel='$count' class=''>" . $count + 1 . "</a>";

		$count++;
	}

	$javascript = "var Slider = function() { this.initialize.apply(this, arguments) }\n";
	$javascript .= "	Slider.prototype = {\n";

	$javascript .= "	initialize: function(slider) {\n";
	$javascript .= "		this.ul = slider.children[0]\n";
	$javascript .= "		this.li = this.ul.children\n";

	// make <ul> as large as all <li>’s
	$javascript .= "		this.ul.style.width = (this.li[0].clientWidth * this.li.length) + 'px'\n";
	$javascript .= "		this.currentIndex = 0\n";
	$javascript .= "	},\n";

	$javascript .= "	goTo: function(index) {\n";
	// filter invalid indices
	$javascript .= "		if (index < 0 || index > this.li.length - 1)\n";
	$javascript .= "		return\n";

	// move <ul> left
	$javascript .= "		this.ul.style.left = '-' + (100 * index) + '%'\n";

	$javascript .= "		this.currentIndex = index\n";
	$javascript .= "	},\n";

	$javascript .= "	goToPrev: function() {\n";
	$javascript .= "		this.goTo(this.currentIndex - 1)\n";
	$javascript .= "	},\n";

	$javascript .= "	goToNext: function() {\n";
	$javascript .= "		this.goTo(this.currentIndex + 1)\n";
	$javascript .= "	}\n";
	$javascript .= "}\n";


	if( $image_list != '' ) {
		if( !isset($ciniki['request']['inline_javascript']) ) {
			$ciniki['request']['inline_javascript'] = '';
		}
		$ciniki['request']['inline_javascript'] += $javascript;
		
		$content = "<div class='slider'>";
		$content .= "<div class='slider-image-wrap'>";
		$content .= "<div class='slider-image'>";
		$content .= "<ul>";
		$content .= $image_list;
		$content .= "</ul>\n";
		$content .= "</div>\n";
		$content .= "</div>\n";
		$content .= "<div class='slider-pager-wrap'>"
		$content .= "<div class='slider-pager'>"
		$content .= "<ul>" . $pager_list . "</ul>";
		$content .= "</div>\n";
		$content .= "</div>\n";
		$content .= "</div>\n";
	}

	return array('stat'=>'ok', 'content'=>$content);
}
?>

