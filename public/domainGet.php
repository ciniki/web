<?php
//
// Description
// ===========
// This function will return all the details for a web.
//
// Arguments
// ---------
// user_id: 		The user making the request
// 
// Returns
// -------
//
function ciniki_web_domainGet($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    require_once($ciniki['config']['core']['modules_dir'] . '/core/private/prepareArgs.php');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No business specified'), 
        'domain_id'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No domain specified'), 
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];
    
    //  
    // Make sure this module is activated, and
    // check permission to run this function for this business
    //  
    require_once($ciniki['config']['core']['modules_dir'] . '/web/private/checkAccess.php');
    $rc = ciniki_web_checkAccess($ciniki, $args['business_id'], 'ciniki.web.domainGet'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

	$strsql = "SELECT ciniki_web_domains.id, domain, flags, status, "
		. "date_added, last_updated "
		. "FROM ciniki_web_domains "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND ciniki_web_domains.id = '" . ciniki_core_dbQuote($ciniki, $args['domain_id']) . "' "
		. "";
	
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'web', 'domain');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['domain']) ) {
		return array('stat'=>'ok', 'err'=>array('pkg'=>'ciniki', 'code'=>'621', 'msg'=>'Unable to find domain'));
	}

	return array('stat'=>'ok', 'domain'=>$rc['domain']);
}
?>
