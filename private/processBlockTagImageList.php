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
function ciniki_web_processBlockTagImageList($ciniki, $settings, $business_id, $block) {

	if( !isset($block['tags']) ) {
		return array('stat'=>'ok', 'content'=>'');
	}

	if( !isset($block['base_url']) ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'2591', 'msg'=>'Unable to process request'));
	}

	$content = "";

	$content .= "<div class='image-list image-list-tags'>";
	foreach($block['tags'] as $tid => $tag) {
		$url_target = '';
		$url = $block['base_url'] . '/' . $tag['permalink'];
		$content .= "<div class='image-list-entry'>";

		//
		// Setup the image
		//
		$content .= "<div class='image-list-image'>";
		if( isset($tag['image_id']) && $tag['image_id'] > 0 ) {
			$version = ((isset($block['image_version'])&&$block['image_version']!='')?$block['image_version']:'thumbnail');
			$rc = ciniki_web_getScaledImageURL($ciniki, $tag['image_id'], $version, 
				((isset($block['image_width'])&&$block['image_width']!='')?$block['image_width']:'400'), 
				((isset($block['image_height'])&&$block['image_height']!='')?$block['image_height']:'0') 
				);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$content .= "<div class='image-list-wrap image-list-$version'>"
				. ($url!=''?"<a href='$url' target='$url_target' title='" . $tag['name'] . "'>":'')
				. "<img title='' alt='" . $tag['name'] . "' src='" . $rc['url'] . "' />"
				. ($url!=''?'</a>':'')
				. "</div>";
		} elseif( isset($block['noimage']) && $block['noimage'] == 'yes' ) {
			$content .= "<div class='image-list-wrap image-list-thumbnail'>"
				. ($url!=''?"<a href='$url' target='$url_target' title='" . $tag['name'] . "'>":'')
				. "<img title='' alt='" . $tag['name'] . "' src='/ciniki-web-layouts/default/img/noimage_240.png' />"
				. ($url!=''?'</a>':'')
				. "</div>";
		} elseif( isset($block['noimage']) && $block['noimage'] != '' ) {
			$content .= "<div class='image-list-wrap image-list-thumbnail'>"
				. ($url!=''?"<a href='$url' target='$url_target' title='" . $tag['name'] . "'>":'')
				. "<img title='' alt='" . $tag['name'] . "' src='" . $block['noimage'] . "' />"
				. ($url!=''?'</a>':'')
				. "</div>";
		}
		$content .= "</div>";
	
		//
		// Setup the details
		//
		$content .= "<div class='image-list-details'>";
		$content .= "<div class='image-list-title'><h2>" 
			. ($url!=''?"<a href='$url' target='$url_target' title='" . $tag['name'] . "'>":'')
			. $tag['name'] 
			. ($url!=''?'</a>':'')
			. "</h2>";
		if( isset($tag['subtitle']) && $tag['subtitle'] != '' ) {
			$content .= "<h3>" 
				. ($url!=''?"<a href='$url' target='$url_target' title='" . $tag['subtitle'] . "'>":'')
				. $tag['subtitle'] 
				. ($url!=''?'</a>':'')
				. "</h3>";
		}
		$content .= "</div>";

		//
		// Setup the meta information
		//
		if( isset($tag['meta']) && count($tag['meta']) > 0 ) {
			$rc = ciniki_web_processMeta($ciniki, $settings, $tag);
			if( isset($rc['content']) && $rc['content'] != '' ) {
				$content .= "<div class='image-list-meta'>" . $rc['content'] . "</div>";
			}
		}

		$content .= "<div class='image-list-content'>";
		if( isset($tag['synopsis']) && $tag['synopsis'] != '' ) {
			$rc = ciniki_web_processContent($ciniki, $tag['synopsis'], 'image-list-description');
			if( $rc['stat'] == 'ok' ) {
				$content .= $rc['content'];
			}
			//
			// Check for files
			//
			if( isset($block['child_files'][$iid]['files']) ) {
				foreach($block['child_files'][$iid]['files'] as $file_id => $file) {
					$file_url = $block['base_url'] . '/' . $tag['permalink'] . '/download/' . $file['permalink'] . '.' . $file['extension'];
					$content .= "<p><a target='_blank' href='" . $file_url . "' title='" . $file['name'] . "'>" . $file['name'] . "</a></p>";
				}
			}
		} elseif( isset($tag['description']) && $tag['description'] != '' ) {
			$rc = ciniki_web_processContent($ciniki, $tag['description'], 'image-list-description');
			if( $rc['stat'] == 'ok' ) {
				$content .= $rc['content'];
			}
			//
			// Check for files
			//
			if( isset($block['child_files'][$iid]['files']) ) {
				foreach($block['child_files'][$iid]['files'] as $file_id => $file) {
					$file_url = $block['base_url'] . '/' . $tag['permalink'] . '/download/' . $file['permalink'] . '.' . $file['extension'];
					$content .= "<p><a target='_blank' href='" . $file_url . "' title='" . $file['name'] . "'>" . $file['name'] . "</a></p>";
				}
			}
		} elseif( isset($tag['short_description']) && $tag['short_description'] != '' ) {
			$rc = ciniki_web_processContent($ciniki, $tag['short_description'], 'image-list-description');
			if( $rc['stat'] == 'ok' ) {
				$content .= $rc['content'];
			}
	
		} else {
			$content .= "";
		}
		$content .= "</div>";

//		if( $url != '' ) {
//			$content .= "<div class='image-list-more'>";
//			$content .= "<a href='$url' target='$url_target'>$url_display</a>";
//			$content .= "</div>";
//		} 
		
		$content .= "</div>";
		$content .= "</div>";
	}

	$content .= "</div>";

	return array('stat'=>'ok', 'content'=>$content);
}
?>
