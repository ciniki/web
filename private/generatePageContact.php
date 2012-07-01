<?php
//
// Description
// -----------
// This function will generate the contact page for the website
//
// Arguments
// ---------
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
	// Add the header
	//
	require_once($ciniki['config']['core']['modules_dir'] . '/web/private/generatePageHeader.php');
	$rc = ciniki_web_generatePageHeader($ciniki, $settings, 'Contact');
	if( $rc['stat'] != 'ok' ) {	
		return $rc;
	}
	$content .= $rc['content'];

	//
	// Check which parts of the business contact information to display automatically
	//
	require_once($ciniki['config']['core']['modules_dir'] . '/businesses/web/contact.php');
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
//		if( isset($contact_details['contact.address.province']) && $contact_details['contact.address.province'] != '' ) {
//			$contact_content .= $contact_details['contact.address.province'];
//		}
		if( isset($contact_details['contact.address.city']) && $contact_details['contact.address.city'] != ''
			&& isset($contact_details['contact.address.province']) && $contact_details['contact.address.province'] != '' ) {
			$contact_content .= ", " . $contact_details['contact.address.province'] . "";
//		} else {
//			$contact_content .= "<br/>\n";
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
		foreach($contact_users as $unum => $u) {
			$setting = 'page-contact-user-display-flags-' . $u['user']['id'];
			if( isset($settings[$setting]) && $settings[$setting] > 0 ) {
				$contact_content .= '<p><span class="contact-title">' . $u['user']['firstname'] . ' ' . $u['user']['lastname'] . '</span><br/>';
				if( ($settings[$setting]&0x01) == 0x01 && isset($u['user']['employee.title']) && $u['user']['employee.title'] != '' ) {
					$contact_content .= $u['user']['employee.title'] . '<br/>';
				}
				if( ($settings[$setting]&0x02) == 0x02 && isset($u['user']['contact.phone.number']) && $u['user']['contact.phone.number'] != '' ) {
					$contact_content .= 'T: ' . $u['user']['contact.phone.number'] . '<br/>';
				}
				if( ($settings[$setting]&0x04) == 0x04 && isset($u['user']['contact.cell.number']) && $u['user']['contact.cell.number'] != '' ) {
					$contact_content .= 'C: ' . $u['user']['contact.cell.number'] . '<br/>';
				}
				if( ($settings[$setting]&0x08) == 0x08 && isset($u['user']['contact.fax.number']) && $u['user']['contact.fax.number'] != '' ) {
					$contact_content .= 'F: ' . $u['user']['contact.fax.number'] . '<br/>';
				}
				if( ($settings[$setting]&0x10) == 0x10 && isset($u['user']['contact.email.address']) && $u['user']['contact.email.address'] != '' ) {
					$contact_content .= 'E: <a class="contact-email" href="mailto:' . $u['user']['contact.email.address'] . '">' . $u['user']['contact.email.address'] . '</a><br/>';
				}
			}
		}
	}

	//
	// Generate the content of the page
	//
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbDetailsQueryDash.php');
	$rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_web_content', 'business_id', $ciniki['request']['business_id'], 'web', 'content', 'page-contact');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	if( isset($rc['content']['page-contact-content']) ) {
		require_once($ciniki['config']['core']['modules_dir'] . '/web/private/processContent.php');
		$rc = ciniki_web_processContent($ciniki, $rc['content']['page-contact-content']);	
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$page_content = $rc['content'];
	}

	//
	// Put together all the contact content
	//
	$content .= "<div id='content'>\n"
		. "<article class='page'>\n"
		. "<header class='entry-title'><h1 class='entry-title'>Contact</h1></header>\n"
		. "<div class='entry-content'>\n";
	if( $contact_content != '' ) {
		$content .= "<p>" . $contact_content . "</p>";
	}
	if( isset($page_content) && $page_content != '' ) {
		$content .= $page_content;
	}

	$content .= "</div>"
		. "</article>"
		. "</div>"
		. "";

	//
	// Add the footer
	//
	require_once($ciniki['config']['core']['modules_dir'] . '/web/private/generatePageFooter.php');
	$rc = ciniki_web_generatePageFooter($ciniki, $settings);
	if( $rc['stat'] != 'ok' ) {	
		return $rc;
	}
	$content .= $rc['content'];

	return array('stat'=>'ok', 'content'=>$content);
}
?>
