<?php
//
// Description
// -----------
// This function will generate the contact page for the website.
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_generatePageContact($ciniki, $settings) {

	//
	// Store the content created by the page
	// Make sure everything gets generated ok before returning the content
	//
	$content = '';

	//
	// FIXME: Check if anything has changed, and if not load from cache
	//
	

	//
	// Check which parts of the business contact information to display automatically
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'web', 'contact');
	$rc = ciniki_businesses_web_contact($ciniki, $settings, $ciniki['request']['business_id']);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$contact_details = $rc['details'];
	$contact_users = $rc['users'];

	$contact_content = '';
	if( isset($settings['page-contact-business-name-display']) && $settings['page-contact-business-name-display'] == 'yes' 
		&& isset($contact_details['contact.business.name']) && $contact_details['contact.business.name'] != '' ) {
		$contact_content .= "<span class='contact-title'>" . $contact_details['contact.business.name'] . "</span><br/>\n";
	}
	if( isset($settings['page-contact-person-name-display']) && $settings['page-contact-person-name-display'] == 'yes' 
		&& isset($contact_details['contact.person.name']) && $contact_details['contact.person.name'] != '' ) {
		if( !isset($settings['page-contact-business-name-display']) || $settings['page-contact-business-name-display'] != 'yes' ) {
			$contact_content .= "<span class='contact-title'>" . $contact_details['contact.person.name'] . "</span><br/>\n";
		} else {
			$contact_content .= $contact_details['contact.person.name'] . "<br/>\n";
		}
	}
	if( isset($settings['page-contact-address-display']) && $settings['page-contact-address-display'] == 'yes' ) {
		if( isset($contact_details['contact.address.street1']) && $contact_details['contact.address.street1'] != '' ) {
			$contact_content .= $contact_details['contact.address.street1'] . "<br/>\n";
		}
		if( isset($contact_details['contact.address.street2']) && $contact_details['contact.address.street2'] != '' ) {
			$contact_content .= $contact_details['contact.address.street2'] . "<br/>\n";
		}
		if( isset($contact_details['contact.address.city']) && $contact_details['contact.address.city'] != '' ) {
			$contact_content .= $contact_details['contact.address.city'] . "\n";
		}
		if( isset($contact_details['contact.address.city']) && $contact_details['contact.address.city'] != ''
			&& isset($contact_details['contact.address.province']) && $contact_details['contact.address.province'] != '' ) {
			$contact_content .= ", " . $contact_details['contact.address.province'] . "";
		}
		if( isset($contact_details['contact.address.postal']) && $contact_details['contact.address.postal'] != '' ) {
			$contact_content .= "  " . $contact_details['contact.address.postal'] . "<br/>\n";
		} else {
			$contact_content .= "<br/>\n";
		}
		if( isset($contact_details['contact.address.country']) && $contact_details['contact.address.country'] != '' ) {
			$contact_content .= $contact_details['contact.address.country'] . "<br/>\n";
		}
	}
	if( isset($settings['page-contact-phone-display']) && $settings['page-contact-phone-display'] == 'yes' 
		&& isset($contact_details['contact.phone.number']) && $contact_details['contact.phone.number'] != '' ) {
		$contact_content .= "phone: " . $contact_details['contact.phone.number'] . "<br/>\n";
	}
	if( isset($settings['page-contact-fax-display']) && $settings['page-contact-fax-display'] == 'yes' 
		&& isset($contact_details['contact.fax.number']) && $contact_details['contact.fax.number'] != '' ) {
		$contact_content .= "fax: " . $contact_details['contact.fax.number'] . "<br/>\n";
	}
	if( isset($settings['page-contact-email-display']) && $settings['page-contact-email-display'] == 'yes' 
		&& isset($contact_details['contact.email.address']) && $contact_details['contact.email.address'] != '' ) {
		$contact_content .= "<a class='contact-email' href='mailto:" . $contact_details['contact.email.address'] . "' />" . $contact_details['contact.email.address'] . "</a><br/>\n";
	}

	//
	// Generate the list of employee's who are to be shown on the website
	//
	if( $settings['page-contact-user-display'] == 'yes' ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processEmployeeBios');
		$rc = ciniki_web_processEmployeeBios($ciniki, $settings, 'contact', $contact_users);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( isset($rc['content']) && $rc['content'] != '' ) {
			$contact_content .= $rc['content'];
		}
	}

	//
	// Generate the content of the page
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash');
	$rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_web_content', 'business_id', $ciniki['request']['business_id'], 'ciniki.web', 'content', 'page-contact');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	if( isset($rc['content']['page-contact-content']) ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
		$rc = ciniki_web_processContent($ciniki, $rc['content']['page-contact-content']);	
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$page_content = $rc['content'];
	}

	//
	// Check if map is to be displayed
	//
	if( isset($settings['page-contact-google-map']) && $settings['page-contact-google-map'] == 'yes' 
		&& isset($settings['page-contact-map-latitude']) && $settings['page-contact-map-latitude'] != '' 
		&& isset($settings['page-contact-map-longitude']) && $settings['page-contact-map-longitude'] != '' 
		) {
		if( !isset($ciniki['request']['inline_javascript']) ) {
			$ciniki['request']['inline_javascript'] = '';
		}
		$ciniki['request']['inline_javascript'] .= ''
			. '<script type="text/javascript">'
			. 'function gmap_initialize() {'
				. 'var myLatlng = new google.maps.LatLng(' . $settings['page-contact-map-latitude'] . ',' . $settings['page-contact-map-longitude'] . ');'
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
				. 'var script = document.createElement("script");'
				. 'script.type = "text/javascript";'
				. 'script.src = "' . ($ciniki['request']['ssl']=='yes'?'https':'http') . '://maps.googleapis.com/maps/api/js?key=' . $ciniki['config']['ciniki.web']['google.maps.api.key'] . '&sensor=false&callback=gmap_initialize";'
				. 'document.body.appendChild(script);'
			. '};'
			. 'window.onload = loadMap;'
			. '</script>';
		$map_content = '<aside><div class="googlemap" id="googlemap"></div></aside>';
	}

	//
	// Add the header
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageHeader');
	$rc = ciniki_web_generatePageHeader($ciniki, $settings, 'Contact', array());
	if( $rc['stat'] != 'ok' ) {	
		return $rc;
	}
	$content .= $rc['content'];

	//
	// Put together all the contact content
	//
	$content .= "<div id='content'>\n"
		. "<article class='page'>\n"
		. "<header class='entry-title'><h1 class='entry-title'>Contact</h1></header>\n";
	if( isset($map_content) && $map_content != '' ) {
		$content .= $map_content;
	}
	$content .= "<div class='entry-content'>\n";
	if( isset($page_content) && $page_content != '' ) {
		$content .= $page_content;
	}
	if( $contact_content != '' ) {
//		$content .= "<p>" . $contact_content . "</p>";
		$content .= $contact_content;
	}

	$content .= "<br style='clear: both;'/>";
	$content .= "</div>"
		. "</article>"
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
