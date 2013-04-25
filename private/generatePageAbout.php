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
	// Add the header
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageHeader');
	$rc = ciniki_web_generatePageHeader($ciniki, $settings, 'About');
	if( $rc['stat'] != 'ok' ) {	
		return $rc;
	}
	$content .= $rc['content'];

	//
	// Generate the content of the page
	//
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
		$page_content = $rc['content'];
	}

	$content .= "<div id='content'>\n";
	$content .= "<article class='page'>\n"
		. "<header class='entry-title'><h1 class='entry-title'>About</h1></header>\n"
		. "";
	if( isset($settings['page-about-image']) && $settings['page-about-image'] != '' && $settings['page-about-image'] > 0 ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
		$rc = ciniki_web_getScaledImageURL($ciniki, $settings['page-about-image'], 'original', '500', 0);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		//$content .= "<aside><div class='image'><img title='' alt='About' src='" . $rc['url'] . "' /></div></aside>";
		$content .= "<aside><div class='image-wrap'>"
			. "<div class='image'><img title='' alt='" . $ciniki['business']['details']['name'] . "' src='" . $rc['url'] . "' /></div>";
		if( isset($settings['page-about-image-caption']) && $settings['page-about-image-caption'] != '' ) {
			$content .= "<div class='image-caption'>" . $settings['page-about-image-caption'] . "</div>";
		}
		$content .= "</div></aside>";
	}
	//
	// Check for the first paragraph, and insert image after
	//
//	if( preg_match('/<\/p><p>/', $page_content) ) {
		// $page_content .= preg_replace('/<\/p><p>/', "</p>$aside<p>", $page_content, 1);
//	}

	$content .= "<div class='entry-content'>\n"
		. $page_content
		. "</div>"
		. "</article>"
		. "";

	//
	// Check if membership info should be displayed here
	//
	if( (isset($ciniki['business']['modules']['ciniki.artclub']) 
			|| isset($ciniki['business']['modules']['ciniki.artgallery']))
		&& isset($settings['page-about-membership-details']) && $settings['page-about-membership-details'] == 'yes' ) {
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
			$content .= "<article class='page'>\n"
				. "<header class='entry-title'><h1 class='entry-title'>Membership</h1></header>\n"
				. "<div class='entry-content'>\n"
				. "";
			$rc = ciniki_web_processContent($ciniki, $membership['details']);	
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$content .= $rc['content'];

			foreach($membership['files'] as $fid => $file) {
				$file = $file['file'];
				$url = $ciniki['request']['base_url'] . '/about/download/' . $file['permalink'] . '.' . $file['extension'];
				$content .= "<p><a target='_blank' href='" . $url . "' title='" . $file['name'] . "'>" . $file['name'] . "</a></p>";
			}

			$content .= "</div>\n"
				. "</article>";
		}
	}

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
