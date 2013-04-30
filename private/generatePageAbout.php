<?php
//
// Description
// -----------
// This function will generate the about page for the business.
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_generatePageAbout($ciniki, $settings) {

	//
	// Check if a file was specified to be downloaded
	//
	$download_err = '';
	if( (isset($ciniki['business']['modules']['ciniki.artclub'])
			|| isset($ciniki['business']['modules']['ciniki.artgallery']))
		&& isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] == 'download'
		&& isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] != '' ) {
		if( isset($ciniki['business']['modules']['ciniki.artgallery']) ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'artgallery', 'web', 'fileDownload');
			$rc = ciniki_artgallery_web_fileDownload($ciniki, $ciniki['request']['business_id'], $ciniki['request']['uri_split'][1]);
		} else {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'artclub', 'web', 'fileDownload');
			$rc = ciniki_artclub_web_fileDownload($ciniki, $ciniki['request']['business_id'], $ciniki['request']['uri_split'][1]);
		}
		if( $rc['stat'] == 'ok' ) {
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			header("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
			header('Cache-Control: no-cache, must-revalidate');
			header('Pragma: no-cache');
			$file = $rc['file'];
			if( $file['extension'] == 'pdf' ) {
				header('Content-Type: application/pdf');
			}
			header('Content-Disposition: attachment;filename="' . $file['filename'] . '"');
			header('Content-Length: ' . strlen($file['binary_content']));
			header('Cache-Control: max-age=0');

			print $file['binary_content'];
			exit;
		}
		
		//
		// If there was an error locating the files, display generic error
		//
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1054', 'msg'=>'Unable to locate file'));
	}

	//
	// Store the content created by the page
	// Make sure everything gets generated ok before returning the content
	//
	$content = '';
	$page_content = '';

	//
	// FIXME: Check if anything has changed, and if not load from cache
	//

	
	//
	// Check if history
	//
	if( isset($ciniki['request']['uri_split'][0]) 
		&& ($ciniki['request']['uri_split'][0] == 'history'
			|| $ciniki['request']['uri_split'][0] == 'donations'
			)
		&& isset($settings['page-abouthistory-active']) && $settings['page-abouthistory-active'] == 'yes' 
		) {
		$page = $ciniki['request']['uri_split'][0];
		$page_content .= "<article class='page'>\n";
		if( $page == 'history' ) {
			$page_content .= "<header class='entry-title'><h1 class='entry-title'>History</h1></header>\n";
		} elseif( $page == 'donations' ) {
			$page_content .= "<header class='entry-title'><h1 class='entry-title'>Donations</h1></header>\n";
		} else {
			$page_content .= "<header class='entry-title'><h1 class='entry-title'>About</h1></header>\n";
		}
		if( isset($settings["page-about$page-image"]) && $settings["page-about$page-image"] != '' && $settings["page-about$page-image"] > 0 ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
			$rc = ciniki_web_getScaledImageURL($ciniki, $settings["page-about$page-image"], 'original', '500', 0);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= "<aside><div class='image-wrap'>"
				. "<div class='image'><img title='' alt='" . $ciniki['business']['details']['name'] . "' src='" . $rc['url'] . "' /></div>";
			if( isset($settings["page-about$page-image-caption"]) && $settings["page-about$page-image-caption"] != '' ) {
				$page_content .= "<div class='image-caption'>" . $settings["page-about$page-image-caption"] . "</div>";
			}
			$page_content .= "</div></aside>";
		}

		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash');
		$rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_web_content', 'business_id', $ciniki['request']['business_id'], 'ciniki.web', 'content', "page-about$page");
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}

		if( isset($rc['content']["page-about$page-content"]) ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
			$rc = ciniki_web_processContent($ciniki, $rc['content']["page-about$page-content"]);	
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= "<div class='entry-content'>"
				. $rc['content']
				. "</div>";
		}

		$page_content .= "</div>\n"
			. "</article>\n";
	}

	elseif( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] == 'boardofdirectors'
		&& isset($settings['page-aboutboardofdirectors-active']) && $settings['page-aboutboardofdirectors-active'] == 'yes' 
		) {
		$page = 'boardofdirectors';
		$page_content .= "<article class='page'>\n"
			. "<header class='entry-title'><h1 class='entry-title'>Board of Directors</h1></header>\n"
			. "<div class='entry-content'>\n"
			. "";
		if( isset($settings["page-about$page-image"]) && $settings["page-about$page-image"] != '' && $settings["page-about$page-image"] > 0 ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
			$rc = ciniki_web_getScaledImageURL($ciniki, $settings["page-about$page-image"], 'original', '500', 0);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= "<aside><div class='image-wrap'>"
				. "<div class='image'><img title='' alt='" . $ciniki['business']['details']['name'] . "' src='" . $rc['url'] . "' /></div>";
			if( isset($settings["page-about$page-image-caption"]) && $settings["page-about$page-image-caption"] != '' ) {
				$page_content .= "<div class='image-caption'>" . $settings["page-about$page-image-caption"] . "</div>";
			}
			$page_content .= "</div></aside>";
		}

		$page_content .= "<p>Board of directors list goes here</p>";

		$page_content .= "</div>\n"
			. "</article>";
	}

	//
	// Check if membership info should be displayed here
	//
	elseif( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] == 'membership'
		&& (isset($ciniki['business']['modules']['ciniki.artclub']) 
			|| isset($ciniki['business']['modules']['ciniki.artgallery']))
		&& isset($settings['page-aboutmembership-active']) && $settings['page-aboutmembership-active'] == 'yes' 
		) {
		$page = 'membership';
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
		if( isset($ciniki['business']['modules']['ciniki.artgallery']) ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'artgallery', 'web', 'membershipDetails');
			$rc = ciniki_artgallery_web_membershipDetails($ciniki, $settings, $ciniki['request']['business_id']);
		} else {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'artclub', 'web', 'membershipDetails');
			$rc = ciniki_artclub_web_membershipDetails($ciniki, $settings, $ciniki['request']['business_id']);
		}
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$membership = $rc['membership'];
		if( $membership['details'] != '' ) {
			$page_content .= "<article class='page'>\n"
				. "<header class='entry-title'><h1 class='entry-title'>Membership</h1></header>\n"
				. "<div class='entry-content'>\n"
				. "";
			if( isset($settings["page-about$page-image"]) && $settings["page-about$page-image"] != '' && $settings["page-about$page-image"] > 0 ) {
				ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
				$rc = ciniki_web_getScaledImageURL($ciniki, $settings["page-about$page-image"], 'original', '500', 0);
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
				$page_content .= "<aside><div class='image-wrap'>"
					. "<div class='image'><img title='' alt='" . $ciniki['business']['details']['name'] . "' src='" . $rc['url'] . "' /></div>";
				if( isset($settings["page-about$page-image-caption"]) && $settings["page-about$page-image-caption"] != '' ) {
					$page_content .= "<div class='image-caption'>" . $settings["page-about$page-image-caption"] . "</div>";
				}
				$page_content .= "</div></aside>";
			}
			$rc = ciniki_web_processContent($ciniki, $membership['details']);	
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= $rc['content'];

			foreach($membership['files'] as $fid => $file) {
				$file = $file['file'];
				$url = $ciniki['request']['base_url'] . '/about/download/' . $file['permalink'] . '.' . $file['extension'];
				$page_content .= "<p><a target='_blank' href='" . $url . "' title='" . $file['name'] . "'>" . $file['name'] . "</a></p>";
			}

			$page_content .= "</div>\n"
				. "</article>";
		}
	}

	//
	// Generate the content of the page
	//
	else {
		$page_content .= "<article class='page'>\n"
			. "<header class='entry-title'><h1 class='entry-title'>About</h1></header>\n"
			. "";
		if( isset($settings['page-about-image']) && $settings['page-about-image'] != '' && $settings['page-about-image'] > 0 ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
			$rc = ciniki_web_getScaledImageURL($ciniki, $settings['page-about-image'], 'original', '500', 0);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= "<aside><div class='image-wrap'>"
				. "<div class='image'><img title='' alt='" . $ciniki['business']['details']['name'] . "' src='" . $rc['url'] . "' /></div>";
			if( isset($settings['page-about-image-caption']) && $settings['page-about-image-caption'] != '' ) {
				$page_content .= "<div class='image-caption'>" . $settings['page-about-image-caption'] . "</div>";
			}
			$page_content .= "</div></aside>";
		}

		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash');
		$rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_web_content', 'business_id', $ciniki['request']['business_id'], 'ciniki.web', 'content', 'page-about');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}

		if( isset($rc['content']['page-about-content']) ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
			$rc = ciniki_web_processContent($ciniki, $rc['content']['page-about-content']);	
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$page_content .= "<div class='entry-content'>"
				. $rc['content']
				. "</div>";
		}

		$page_content .= "</div>\n"
			. "</article>\n";
	}	

	//
	// Check if we are to display a submenu
	//
	$submenu = array();
//	$submenu['about'] = array('name'=>'About', 'url'=>$ciniki['request']['base_url'] . '/about');
	if( isset($settings['page-abouthistory-active']) && $settings['page-abouthistory-active'] == 'yes' ) {
		$submenu['history'] = array('name'=>'History', 'url'=>$ciniki['request']['base_url'] . '/about/history');
	}
	if( isset($settings['page-aboutdonations-active']) && $settings['page-aboutdonations-active'] == 'yes' ) {
		$submenu['donations'] = array('name'=>'Donations', 'url'=>$ciniki['request']['base_url'] . '/about/donations');
	}
	if( isset($settings['page-aboutboardofdirectors-active']) && $settings['page-aboutboardofdirectors-active'] == 'yes' ) {
		$submenu['boardofdirectors'] = array('name'=>'Board of Directors', 'url'=>$ciniki['request']['base_url'] . '/about/boardofdirectors');
	}
	if( isset($settings['page-aboutmembership-active']) && $settings['page-aboutmembership-active'] == 'yes' ) {
		$submenu['membership'] = array('name'=>'Membership', 'url'=>$ciniki['request']['base_url'] . '/about/membership');
	}

	//
	// Add the header
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageHeader');
	$rc = ciniki_web_generatePageHeader($ciniki, $settings, 'About', $submenu);
	if( $rc['stat'] != 'ok' ) {	
		return $rc;
	}
	$content .= $rc['content'];

	$content .= "<div id='content'>\n";
	$content .= $page_content;
	$content .= "</div>\n";

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
