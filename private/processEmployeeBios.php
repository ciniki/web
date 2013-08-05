<?php
//
// Description
// -----------
// This function will process the list of employees and their bios for display on the contact page.
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure, similar to ciniki variable but only web specific information.
// 
//
// Returns
// -------
//
function ciniki_web_processEmployeeBios($ciniki, $settings, $page, $employees) {

	$content = '';

	if( isset($settings["page-$page-bios-display"]) && $settings["page-$page-bios-display"] == 'cilist' ) {
		$content = "<table class='cilist'><tbody>";
		$display_names = 'no';
		// Check if any employees have name display turned on
		foreach($employees as $unum => $u) {
			$setting = 'page-contact-user-display-flags-' . $u['user']['id'];
			if( ($settings[$setting]&0x01) == 0x01 && isset($u['user']['employee.title']) && $u['user']['employee.title'] != '' ) {
				$display_names = 'yes';
			}
		}

		foreach($employees as $unum => $u) {
			if( $display_names == 'yes' ) {
				$contact_name = '';
				if( ($settings[$setting]&0x01) == 0x01 && ((isset($u['user']['firstname']) && $u['user']['firstname'] != '' )
					|| (isset($u['user']['lastname']) && $u['user']['lastname'] != '')) ) {
					$contact_name .= '<span class="contact-title">' . $u['user']['firstname'] . ' ' . $u['user']['lastname'] . '</span><br/>';
				}
				if( ($settings[$setting]&0x02) == 0x02 && isset($u['user']['employee.title']) && $u['user']['employee.title'] != '' ) {
					$contact_name .= $u['user']['employee.title'];
				}
				$content .= "<tr><th>$contact_name</th><td>\n";
			} else {
				$content .= "<tr>";
			}
			$content .= "<table class='cilist-categories'><tbody>\n";

			// Setup the event image
			$content .= "<tr><td class='cilist-image' rowspan='1'>";
			if( ($settings[$setting]&0x40) == 0x40 ) {
				if( isset($u['user']['employee-bio-image']) && $u['user']['employee-bio-image'] != '' && $u['user']['employee-bio-image'] > 0 ) {
					ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
					$rc = ciniki_web_getScaledImageURL($ciniki, $u['user']['employee-bio-image'], 'thumbnail', '150', 0);
					if( $rc['stat'] != 'ok' ) {
						return $rc;
					}
					$content .= "<div class='image-cilist-thumbnail'>"
						. "<img title='' alt='" . $u['user']['firstname'] . ' ' . $u['user']['lastname'] . "' src='" . $rc['url'] . "' /></div>";
				}
			}
			$content .= "</td>";

			// Setup the details
			$content .= "<td class='cilist-details'>";
			// Check if employee bio content is to be displayed.
			if( ($settings[$setting]&0x40) == 0x40 ) {
				if( isset($u['user']['employee-bio-content']) && $u['user']['employee-bio-content'] != '' ) {
					ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
					$rc = ciniki_web_processContent($ciniki, $u['user']['employee-bio-content']);	
					if( $rc['stat'] != 'ok' ) {
						return $rc;
					}
					$content .= '<p>' . $rc['content'] . '</p>';
				}
			}
			$content .= '<p>';
			if( ($settings[$setting]&0x04) == 0x04 && isset($u['user']['contact.phone.number']) && $u['user']['contact.phone.number'] != '' ) {
				$content .= 'T: ' . $u['user']['contact.phone.number'] . '<br/>';
			}
			if( ($settings[$setting]&0x08) == 0x08 && isset($u['user']['contact.cell.number']) && $u['user']['contact.cell.number'] != '' ) {
				$content .= 'C: ' . $u['user']['contact.cell.number'] . '<br/>';
			}
			if( ($settings[$setting]&0x10) == 0x10 && isset($u['user']['contact.fax.number']) && $u['user']['contact.fax.number'] != '' ) {
				$content .= 'F: ' . $u['user']['contact.fax.number'] . '<br/>';
			}
			if( ($settings[$setting]&0x20) == 0x20 && isset($u['user']['contact.email.address']) && $u['user']['contact.email.address'] != '' ) {
				$content .= 'E: <a class="contact-email" href="mailto:' . $u['user']['contact.email.address'] . '">' . $u['user']['contact.email.address'] . '</a><br/>';
			}
			$content .= '</p>';
			$content .= "</tbody></table>";
			$content .= "</td></tr>";
		}
		$content .= "</tbody></table>\n";
	} else {
		foreach($employees as $unum => $u) {
			$setting = 'page-contact-user-display-flags-' . $u['user']['id'];
			if( isset($settings[$setting]) && $settings[$setting] > 0 ) {
				$content .= '<p>';
				// Check if employee bio image is to be displayed
				if( ($settings[$setting]&0x40) == 0x40 ) {
					if( isset($u['user']['employee-bio-image']) && $u['user']['employee-bio-image'] != '' && $u['user']['employee-bio-image'] > 0 ) {
						ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
						$rc = ciniki_web_getScaledImageURL($ciniki, $u['user']['employee-bio-image'], 'original', '500', 0);
						if( $rc['stat'] != 'ok' ) {
							return $rc;
						}
						$content .= "<aside><div class='image-wrap'>"
							. "<div class='image'><img title='' alt='" . $u['user']['firstname'] . ' ' . $u['user']['lastname'] . "' src='" . $rc['url'] . "' /></div>";
						if( isset($u['user']["employee-bio-image-caption"]) && $u['user']["employee-bio-image-caption"] != '' ) {
							$content .= "<div class='image-caption'>" . $u['user']["employee-bio-image-caption"] . "</div>";
						}
						$content .= "</div></aside>";
					}
				}
				if( ($settings[$setting]&0x01) == 0x01 && isset($u['user']['employee.title']) && $u['user']['employee.title'] != '' ) {
					$content .= '<span class="contact-title">' . $u['user']['firstname'] . ' ' . $u['user']['lastname'] . '</span><br/>';
				}
				if( ($settings[$setting]&0x02) == 0x02 && isset($u['user']['employee.title']) && $u['user']['employee.title'] != '' ) {
					$content .= $u['user']['employee.title'] . '<br/>';
				}
				// Check if employee bio content is to be displayed.
				if( ($settings[$setting]&0x40) == 0x40 ) {
					if( isset($u['user']['employee-bio-content']) && $u['user']['employee-bio-content'] != '' ) {
						ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
						$rc = ciniki_web_processContent($ciniki, $u['user']['employee-bio-content']);	
						if( $rc['stat'] != 'ok' ) {
							return $rc;
						}
						$content .= "</p><p>" . $rc['content'] . "</p><p>";
					}
				}
				if( ($settings[$setting]&0x04) == 0x04 && isset($u['user']['contact.phone.number']) && $u['user']['contact.phone.number'] != '' ) {
					$content .= 'T: ' . $u['user']['contact.phone.number'] . '<br/>';
				}
				if( ($settings[$setting]&0x08) == 0x08 && isset($u['user']['contact.cell.number']) && $u['user']['contact.cell.number'] != '' ) {
					$content .= 'C: ' . $u['user']['contact.cell.number'] . '<br/>';
				}
				if( ($settings[$setting]&0x10) == 0x10 && isset($u['user']['contact.fax.number']) && $u['user']['contact.fax.number'] != '' ) {
					$content .= 'F: ' . $u['user']['contact.fax.number'] . '<br/>';
				}
				if( ($settings[$setting]&0x20) == 0x20 && isset($u['user']['contact.email.address']) && $u['user']['contact.email.address'] != '' ) {
					$content .= 'E: <a class="contact-email" href="mailto:' . $u['user']['contact.email.address'] . '">' . $u['user']['contact.email.address'] . '</a><br/>';
				}
			}
		}

	}

	return array('stat'=>'ok', 'content'=>$content);
}
?>
