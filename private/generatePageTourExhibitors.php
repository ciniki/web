<?php
//
// Description
// -----------
// This function will generate the exhibitors page for the business.
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_generatePageTourExhibitors($ciniki, $settings) {

	//
	// Store the content created by the page
	// Make sure everything gets generated ok before returning the content
	//
	$content = '';
	$page_content = '';
	$page_title = 'Exhibitors';

	//
	// FIXME: Check if anything has changed, and if not load from cache
	//

	//
	// Check if we are to display the gallery image for an exhibitor
	//
	//
	// Check if we are to display an image, from the gallery, or latest images
	//
	if( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] != '' 
		&& isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] == 'gallery' 
		&& isset($ciniki['request']['uri_split'][2]) && $ciniki['request']['uri_split'][2] != '' 
		) {
		$exhibitor_permalink = $ciniki['request']['uri_split'][0];
		$image_permalink = $ciniki['request']['uri_split'][2];

		//
		// Load the participant to get all the details, and the list of images.
		// It's one query, and we can find the requested image, and figure out next
		// and prev from the list of images returned
		//
		ciniki_core_loadMethod($ciniki, 'ciniki', 'exhibitions', 'web', 'participantDetails');
		$rc = ciniki_exhibitions_web_participantDetails($ciniki, $settings, 
			$ciniki['request']['business_id'], 
			$settings['page-exhibitions-exhibition'], $exhibitor_permalink);
		if( $rc['stat'] != 'ok' ) {
			return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'1307', 'msg'=>"I'm sorry, but we can't seem to find the image your requested.", $rc['err']));
		}
		$participant = $rc['participant'];

		if( !isset($participant['images']) || count($participant['images']) < 1 ) {
			return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'1102', 'msg'=>"I'm sorry, but we can't seem to find the image your requested."));
		}

		$first = NULL;
		$last = NULL;
		$img = NULL;
		$next = NULL;
		$prev = NULL;
		foreach($participant['images'] as $iid => $image) {
			if( $first == NULL ) {
				$first = $image;
			}
			if( $image['permalink'] == $image_permalink ) {
				$img = $image;
			} elseif( $next == NULL && $img != NULL ) {
				$next = $image;
			} elseif( $img == NULL ) {
				$prev = $image;
			}
			$last = $image;
		}

		if( count($participant['images']) == 1 ) {
			$prev = NULL;
			$next = NULL;
		} elseif( $prev == NULL ) {
			// The requested image was the first in the list, set previous to last
			$prev = $last;
		} elseif( $next == NULL ) {
			// The requested image was the last in the list, set previous to last
			$next = $first;
		}
		
		$page_title = $participant['name'] . ' - ' . $img['title'];
	
		if( $img == NULL ) {
			return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'1301', 'msg'=>"I'm sorry, but we can't seem to find the image your requested."));
		}
	
		//
		// Load the image
		//
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
		$rc = ciniki_web_getScaledImageURL($ciniki, $img['image_id'], 'original', 0, 600);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$img_url = $rc['url'];

		//
		// Set the page to wide if possible
		//
		$ciniki['request']['page-container-class'] = 'page-container-wide';

		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generateGalleryJavascript');
		$rc = ciniki_web_generateGalleryJavascript($ciniki, $next, $prev);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$ciniki['request']['inline_javascript'] = $rc['javascript'];

		$ciniki['request']['onresize'] = "gallery_resize_arrows();";
		$ciniki['request']['onload'] = "scrollto_header();";
		$page_content .= "<article class='page'>\n"
			. "<header class='entry-title'><h1 id='entry-title' class='entry-title'>$page_title</h1></header>\n"
			. "<div class='entry-content'>\n"
			. "";
		$page_content .= "<div id='gallery-image' class='gallery-image'>";
		$page_content .= "<div id='gallery-image-wrap' class='gallery-image-wrap'>";
		if( $prev != null ) {
			$page_content .= "<a id='gallery-image-prev' class='gallery-image-prev' href='" . $prev['permalink'] . "'><div id='gallery-image-prev-img'></div></a>";
		}
		if( $next != null ) {
			$page_content .= "<a id='gallery-image-next' class='gallery-image-next' href='" . $next['permalink'] . "'><div id='gallery-image-next-img'></div></a>";
		}
		$page_content .= "<img id='gallery-image-img' title='" . $img['title'] . "' alt='" . $img['title'] . "' src='" . $img_url . "' onload='javascript: gallery_resize_arrows();' />";
		$page_content .= "</div><br/>"
			. "<div id='gallery-image-details' class='gallery-image-details'>"
			. "<span class='image-title'>" . $img['title'] . '</span>'
			. "<span class='image-details'></span>";
		if( $img['description'] != '' ) {
			$page_content .= "<span class='image-description'>" . preg_replace('/\n/', '<br/>', $img['description']) . "</span>";
		}
		$page_content .= "</div></div>";
		$page_content .= "</div></article>";
	}

	//
	// Check if we are to display an exhibitor
	//
	elseif( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] != '' ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'exhibitions', 'web', 'participantDetails');
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processURL');

		//
		// Get the exhibitor information
		//
		$exhibitor_permalink = $ciniki['request']['uri_split'][0];
		$rc = ciniki_exhibitions_web_participantDetails($ciniki, $settings, 
			$ciniki['request']['business_id'], $settings['page-exhibitions-exhibition'], $exhibitor_permalink);
		if( $rc['stat'] != 'ok' ) {
			return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'1308', 'msg'=>"I'm sorry, but we can't find the exhibitor your requested.", $rc['err']));
		}
		$participant = $rc['participant'];
		$page_title = $participant['name'];
		$page_content .= "<article class='page'>\n"
			. "<header class='entry-title'><h1 class='entry-title'>" . $participant['name'] . "</h1></header>\n"
			. "";

		//
		// Check if map is to be displayed
		//
		if( isset($participant['latitude']) && $participant['latitude'] != '' 
			&& isset($participant['longitude']) && $participant['longitude'] != '' ) {
			if( !isset($ciniki['request']['inline_javascript']) ) {
				$ciniki['request']['inline_javascript'] = '';
			}
			$ciniki['request']['inline_javascript'] .= ''
				. '<script type="text/javascript">'
				. 'function gmap_initialize() {'
					. 'var myLatlng = new google.maps.LatLng(' . $participant['latitude'] . ',' . $participant['longitude'] . ');'
					. 'var mapOptions = {'
						. 'zoom: 12,'
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
					. 'var script = document.createElement("script");'
					. 'script.type = "text/javascript";'
					. 'script.src = "http://maps.googleapis.com/maps/api/js?key=' . $ciniki['config']['ciniki.web']['google.maps.api.key'] . '&sensor=false&callback=gmap_initialize";'
					. 'document.body.appendChild(script);'
				. '};'
				. 'window.onload = loadMap;'
				. '</script>';
			$page_content .= '<aside><div class="googlemap" id="googlemap"></div></aside>';
		}

		//
		// Add primary image
		//
		if( isset($participant['image_id']) && $participant['image_id'] > 0 ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
			$rc = ciniki_web_getScaledImageURL($ciniki, $participant['image_id'], 'original', '500', 0);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= "<aside><div class='image-wrap'><div class='image'>"
				. "<img title='' alt='" . $participant['name'] . "' src='" . $rc['url'] . "' />"
				. "</div></div></aside>";
		}
		
		//
		// Add description
		//
		if( isset($participant['description']) ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
			$rc = ciniki_web_processContent($ciniki, $participant['description']);	
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= $rc['content'];
		}

		$address = $participant['address1'] . "<br/>";
		if( isset($participant['address2']) && $participant['address2'] != '' ) {
			$address .= $participant['address2'] . "<br/>";
		}
		if( isset($participant['city']) && $participant['city'] != ''
			&& isset($participant['province']) && $participant['province'] != '' ) {
			$address .= $participant['city'] . ", " . $participant['province'];
		} elseif( isset($participant['city']) && $participant['city'] != '' ) {
			$address .= $participant['city'];
		} elseif( isset($participant['province']) && $participant['province'] != '' ) {
			$address .= $participant['province'];
		}
		if( isset($participant['postal']) && $participant['postal'] != '' ) {
			$address .= '  ' . $participant['postal'];
		}
		if( $address != '' ) {
			$page_content .= "<h2>Address</h2><p><address>" . $address . "</address></p>";
		}

		if( isset($participant['url']) ) {
			$rc = ciniki_web_processURL($ciniki, $participant['url']);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$url = $rc['url'];
			$display_url = $rc['display'];
		} else {
			$url = '';
		}

		if( $url != '' ) {
			$page_content .= "<br/>Website: <a class='exhibitors-url' target='_blank' href='" . $url . "' title='" . $participant['name'] . "'>" . $display_url . "</a>";
		}
		$page_content .= "</article>";

		if( isset($participant['images']) && count($participant['images']) > 0 ) {
			$page_content .= "<article class='page'>"	
				. "<header class='entry-title'><h1 class='entry-title'>Gallery</h1></header>\n"
				. "";
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageGalleryThumbnails');
			$img_base_url = $ciniki['request']['base_url'] . "/tour/" . $participant['permalink'] . "/gallery";
			$rc = ciniki_web_generatePageGalleryThumbnails($ciniki, $settings, $img_base_url, $participant['images'], 125);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= "<div class='image-gallery'>" . $rc['content'] . "</div>";
			$page_content .= "</article>";
		}
	}

	//
	// Display the list of exhibitors if a specific one isn't selected
	//
	else {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'exhibitions', 'web', 'participantList');
		$rc = ciniki_exhibitions_web_participantList($ciniki, $settings, $ciniki['request']['business_id'], $settings['page-exhibitions-exhibition'], 'tourexhibitor');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$participants = $rc['categories'];

		//
		// Load google maps api
		//
		if( !isset($ciniki['request']['inline_javascript']) ) {
			$ciniki['request']['inline_javascript'] = '';
		}
		$map_participant_javascript = '';
		
		//
		// Build the page
		//
		$page_content .= "<article class='page'>\n"
			. "<header class='entry-title'><h1 class='entry-title'>Exhibitors</h1></header>\n"
			. "<div class='entry-content'>\n"
			. "";

		$page_content .= '<div class="googlemap" id="googlemap"></div>';

		if( count($participants) > 0 ) {
			$page_content .= "<table class='exhibitors-list'><tbody>\n"
				. "";
			$prev_category = NULL;
			$count = 1;
			foreach($participants as $cnum => $c) {
				if( $prev_category != NULL ) {
					$page_content .= "</td></tr>\n";
				}
				if( isset($c['category']['name']) && $c['category']['name'] != '' ) {
					$page_content .= "<tr><th>"
						. "<span class='exhibitors-category'>" . $c['category']['name'] . "</span></th>"
						. "<td>";
				} else {
					$page_content .= "<tr><th>"
						. "<span class='exhibitors-category'></span></th>"
						. "<td>";
				}
				$page_content .= "<table class='exhibitors-category-list'><tbody>\n";
				foreach($c['category']['participants'] as $pnum => $participant) {
					$participant = $participant['participant'];
					$participant_url = $ciniki['request']['base_url'] . "/tour/" . $participant['permalink'];
					
					$marker_content = "<p><b>" . $participant['name'] . "</b></p>";
					$marker_content .= "<p>" . $participant['address1'] . "<br/>";
					if( isset($participant['address2']) && $participant['address2'] != '' ) {
						$marker_content .= $participant['address2'] . "<br/>";
					}
					if( isset($participant['city']) && $participant['city'] != ''
						&& isset($participant['province']) && $participant['province'] != '' ) {
						$marker_content .= $participant['city'] . ", " . $participant['province'];
					} elseif( isset($participant['city']) && $participant['city'] != '' ) {
						$marker_content .= $participant['city'];
					} elseif( isset($participant['province']) && $participant['province'] != '' ) {
						$marker_content .= $participant['province'];
					}
					if( isset($participant['postal']) && $participant['postal'] != '' ) {
						$marker_content .= '  ' . $participant['postal'];
					}
					$marker_content .= "</p>";
					$marker_content .= "<p class=\"exhibitors-more\"><a href=\"$participant_url\">... more</a></p>";

					if( isset($participant['latitude']) && $participant['latitude'] != ''
						&& isset($participant['longitude']) && $participant['longitude'] != '' ) {
						$map_participant_javascript .= "gmap_showParticipant(" . $participant['latitude'] . ',' . $participant['longitude'] . ",'" . $count . "','" . preg_replace("/'/", "\\'", $marker_content) . "');";
					}

					// Setup the exhibitor image
					$page_content .= "<tr><td class='exhibitors-image' rowspan='3'>";
					if( isset($participant['image_id']) && $participant['image_id'] > 0 ) {
						ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
						$rc = ciniki_web_getScaledImageURL($ciniki, $participant['image_id'], 'thumbnail', '150', 0);
						if( $rc['stat'] != 'ok' ) {
							return $rc;
						}
						$page_content .= "<div class='image-exhibitors-thumbnail'>"
							. "<a href='$participant_url' title='" . $participant['name'] . "'><img title='' alt='" . $participant['name'] . "' src='" . $rc['url'] . "' /></a>"
							. "</div>";
					}
					$page_content .= "</td>";

					// Setup the details
					$page_content .= "<td class='exhibitors-details'>";
					$page_content .= "<span class='exhibitors-title'>";
					$page_content .= "<a href='$participant_url' title='" . $participant['name'] . "'>" . $count . ".  " . $participant['name'] . "</a>";
					$page_content .= "</span>";
					$page_content .= "</td></tr>";
					$page_content .= "<tr><td class='exhibitors-description'>";
					if( isset($participant['description']) && $participant['description'] != '' ) {
						$page_content .= "<span class='exhibitors-description'>" . $participant['description'] . "</span>";
					}
					$page_content .= "</td></tr>";
					$page_content .= "<tr><td class='exhibitors-more'><a href='$participant_url'>... more</a></td></tr>";

					$count++;
				}
				$page_content .= "</tbody></table>";
			}

			$page_content .= "</td></tr>\n</tbody></table>\n";
		} else {
			$page_content .= "<p>Currently no exhibitors for this event.</p>";
		}

		$page_content .= "</div>\n"
			. "</article>\n"
			. "";
		
		//
		// Check which parts of the business contact information to display automatically
		//
		ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'web', 'contact');
		$rc = ciniki_businesses_web_contact($ciniki, $settings, $ciniki['request']['business_id']);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$contact_details = $rc['details'];
		
		$business_address = '';
		if( isset($contact_details['contact.address.street1']) && $contact_details['contact.address.street1'] != '' ) {
			$business_address .= $contact_details['contact.address.street1'] . "<br/>";
		}
		if( isset($contact_details['contact.address.street2']) && $contact_details['contact.address.street2'] != '' ) {
			$business_address .= $contact_details['contact.address.street2'] . "<br/>";
		}
		if( isset($contact_details['contact.address.city']) && $contact_details['contact.address.city'] != '' ) {
			$business_address .= $contact_details['contact.address.city'];
		}
		if( isset($contact_details['contact.address.city']) && $contact_details['contact.address.city'] != ''
			&& isset($contact_details['contact.address.province']) && $contact_details['contact.address.province'] != '' ) {
			$business_address .= ", " . $contact_details['contact.address.province'] . "";
		}
		if( isset($contact_details['contact.address.postal']) && $contact_details['contact.address.postal'] != '' ) {
			$business_address .= "  " . $contact_details['contact.address.postal'] . "<br/>";
		} else {
			$business_address .= "<br/>";
		}
		// 
		// Setup the javascript to display the map
		//
		$ciniki['request']['inline_javascript'] .= ''
			. '<script type="text/javascript">'
			. 'var map;'
//			. 'var infowindow;'
			. 'function gmap_initialize() {'
				. 'var myLatlng = new google.maps.LatLng(' . $settings['page-contact-map-latitude'] . ',' . $settings['page-contact-map-longitude'] . ');'
				. 'var mapOptions = {'
					. 'zoom: 11,'
					. 'center: myLatlng,'
					. 'panControl: false,'
					. 'zoomControl: true,'
					. 'scaleControl: true,'
					. 'mapTypeId: google.maps.MapTypeId.ROADMAP'
				. '};'
				. 'map = new google.maps.Map(document.getElementById("googlemap"), mapOptions);'
				. 'gmap_showParticipant(' . $settings['page-contact-map-latitude'] . ',' . $settings['page-contact-map-longitude'] . ',"","<p><b>' . $ciniki['business']['details']['name'] . '</b></p><p>' . $business_address . '</p>");'
//				. 'var marker = new google.maps.Marker({'
//					. 'position: myLatlng,'
//					. 'map: map,'
//					. 'title:"",'
//					. '});'
				. $map_participant_javascript
			. '};'
			. 'function gmap_showParticipant(lat,lng,num,content) {'
				. 'var myLatlng = new google.maps.LatLng(lat,lng);'
//				. 'var symbol = new google.maps.Symbol({fillColor:"#ff0000",path:CIRCLE});'
				. 'var icon="";'
				. 'if(num!="") {icon=\'https://chart.googleapis.com/chart?chst=d_map_pin_letter&chld=\'+num+\'|FF776B|000000\'};'
				. 'var marker = new google.maps.Marker({'
					. 'position: myLatlng,'
					. 'map: map,'
					. 'title:name,'
					. 'icon:icon,'
//					. 'icon:{path:google.maps.SymbolPath.CIRCLE,fillColor:"#ff0000"},'
					. '});'
				. 'var infowindow = new google.maps.InfoWindow({'
					. 'content:content});'
				. 'google.maps.event.addListener(marker, "click", function() { infowindow.open(map, marker);});'
			. '};'
			. 'function loadMap() {'
				. 'var script = document.createElement("script");'
				. 'script.type = "text/javascript";'
				. 'script.src = "http://maps.googleapis.com/maps/api/js?key=' . $ciniki['config']['ciniki.web']['google.maps.api.key'] . '&sensor=false&callback=gmap_initialize";'
				. 'document.body.appendChild(script);'
			. '};'
			. 'window.onload = loadMap;'
			. '</script>';
	}

	//
	// Generate the complete page
	//

	//
	// Add the header
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageHeader');
	$rc = ciniki_web_generatePageHeader($ciniki, $settings, $page_title, array());
	if( $rc['stat'] != 'ok' ) {	
		return $rc;
	}
	$content .= $rc['content'];

	$content .= "<div id='content'>\n"
		. $page_content
		. "</div>"
		. "";

	//
	// Add the footer
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageFooter');
	$rc = ciniki_web_generatePageFooter($ciniki, $settings);
	if( $rc['stat'] != 'ok' ) {	
		return $rc;
	}
	$content .= $rc['content'];

	return array('stat'=>'ok', 'content'=>$content);
}
?>
