<?php
//
// Description
// -----------
// This function will process raw text content into HTML.
//
// Arguments
// ---------
// ciniki:
// unprocessed_content:		The unprocessed text content that needs to be turned into html.
//
// Returns
// -------
//
function ciniki_web_shortenURL($ciniki, $business_id, $url) {

	//
	// Check if it already exists
	//
	$strsql = "SELECT surl "
		. "FROM ciniki_web_shorturls "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
		. "AND furl = '" . ciniki_core_dbQuote($ciniki, $url) . "' "
		. "";
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.web', 'url');
	if( $rc['stat'] != 'ok' ) {
		error_log('ERR: ' . print_r($rc, true));
		return $url;
	}
	if( isset($rc['url']) ) {
		return 'http://' . $ciniki['config']['ciniki.web']['url.shorten.domain'] . '/' . $rc['url']['surl'];
	}

	//
	// Create shortened url
	//
	$strsql = "SELECT `AUTO_INCREMENT` AS max_id "
		. "FROM  INFORMATION_SCHEMA.TABLES "
		. "WHERE TABLE_NAME = 'ciniki_web_shorturls'";
//	$strsql = "SELECT MAX(id) AS max_id "
//		. "FROM ciniki_web_shorturls ";
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.web', 'max');
	if( $rc['stat'] != 'ok') {
		error_log('ERR: ' . print_r($rc, true));
		return $url;
	}
	if( isset($rc['max']['max_id']) ) {
		$number = $rc['max']['max_id'] + 1;
	} else {
		$number = 1;
	}

	$codes = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	$surl = '';
	while( $number > 61 ) {
		$key = $number % 62;
		$number = floor($number/62) - 1;
		$surl = $codes[$key] . $surl;
	}
	$surl = $codes[$number] . $surl;

	//
	// Add the shortened URL
	//
	$args = array(
		'surl'=>$surl,
		'furl'=>$url);
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
	$rc = ciniki_core_objectAdd($ciniki, $business_id, 'ciniki.web.shorturl', $args, 0x07);
	if( $rc['stat'] != 'ok' ) {
		error_log('ERR: ' . print_r($rc, true));
		return $url;
	}

	return 'http://' . $ciniki['config']['ciniki.web']['url.shorten.domain'] . '/' . $surl;
}
?>
