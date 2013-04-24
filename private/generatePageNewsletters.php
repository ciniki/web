<?php
//
// Description
// -----------
// This file will generate a page to display the list of newsletters for a business.
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_generatePageNewsletters($ciniki, $settings) {

	//
	// Check if a file was specified to be downloaded
	//
	$download_err = '';
	if( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] != '' ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'newsletters', 'web', 'fileDownload');
		$rc = ciniki_newsletters_web_fileDownload($ciniki, $ciniki['request']['business_id'], $ciniki['request']['uri_split'][0]);
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
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1007', 'msg'=>'Unable to locate file'));
	}


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
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageHeader');
	$page_title = 'Newsletters';
	$rc = ciniki_web_generatePageHeader($ciniki, $settings, $page_title);
	if( $rc['stat'] != 'ok' ) {	
		return $rc;
	}
	$content .= $rc['content'];

	//
	// Generate the content of the page
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash');
	$rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_web_content', 'business_id', $ciniki['request']['business_id'], 'ciniki.web', 'content', 'page-newsletters');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	$page_content = '';
	if( isset($rc['content']) && isset($rc['content']['page-newsletters-content']) ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
		$rc = ciniki_web_processContent($ciniki, $rc['content']['page-downloads-content']);	
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$page_content = $rc['content'];
	}
	
	$content .= "<div id='content'>\n"
		. "<article class='page'>\n"
		. "<header class='entry-title'><h1 class='entry-title'>$page_title</h1></header>\n"
		. "<div class='entry-content'>\n"
		. "";
	if( $page_content != '' ) {
		$content .= $page_content;
	}

	//
	// Get the list of downloads to be displayed
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'newsletters', 'web', 'list');
	$rc = ciniki_newsletters_web_list($ciniki, $ciniki['request']['business_id']);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( isset($rc['categories']) ) {
		$categories = $rc['categories'];
	} else {
		$categories = array();
	}

	$content .= "<table class='downloads-list'>\n"
		. "";
	$prev_category = NULL;
	foreach($categories as $cnum => $c) {
		if( $prev_category != NULL ) {
			$content .= "</td></tr>\n";
		}
		if( isset($c['category']['name']) && $c['category']['name'] != '' ) {
			$content .= "<tr><th>"
				. "<span class='downloads-category'>" . $c['category']['name'] . "</span></th>"
				. "<td>";
		} else {
			$content .= "<tr><th>"
				. "<span class='downloads-category'></span></th>"
				. "<td>";
		}
		foreach($c['category']['files'] as $fnum => $download) {
			$url = $ciniki['request']['base_url'] . '/newsletters/' . $download['file']['permalink'] . '.' . $download['file']['extension'];
			$content .= "<span class='downloads-title'>";
			if( $url != '' ) {
				$content .= "<a target='_blank' href='" . $url . "' title='" . $download['file']['name'] . "'>" . $download['file']['name'] . "</a>";
			} else {
				$content .= $download['file']['name'];
			}
			$content .= "</span>";
			if( isset($download['file']['description']) && $download['file']['description'] != '' ) {
				$content .= "<br/><span class='downloads-description'>" . $download['file']['description'] . "</span>";
			}
			$content .= "<br/><br/>";
		}
	}

	$content .= "</td></tr>\n</table>\n";

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
