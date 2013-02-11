<?php
//
// Description
// -----------
// This method will return the list of owners and employee's for a business, whos
// contact information could be displayed on contact page.
//
// The returned email element is the email address of the users account, the contact.email.address
// is the publically available contact email address for the user, which can be different.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id: 			The ID of the business to get the users for.
//
// Returns
// -------
// <users>
//		<user id="4" firstname="Andrew" lastname="Rivett" email="andrew@ciniki.ca" display_name="Andrew"
//			employee.title="President" contact.phone.number="555-555-1234" contact.cell.number="555-555-1234"
//			contact.fax.number="123-456-7890" contact.email.address="andrew@ciniki.com" />
//		...
// </users>
//
function ciniki_web_businessUsers($ciniki) {
	//
	// Find all the required and optional arguments
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
	$rc = ciniki_core_prepareArgs($ciniki, 'no', array(
		'business_id'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No business specified'), 
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$args = $rc['args'];

	//
	// Check access 
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'checkAccess');
	$rc = ciniki_web_checkAccess($ciniki, $args['business_id'], 'ciniki.web.businessUsers');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	//
	// Get details for a user
	//
	$strsql = "SELECT ciniki_business_users.user_id, "
		. "ciniki_users.firstname, ciniki_users.lastname, "
		. "ciniki_users.email, ciniki_users.display_name, "
		. "ciniki_business_user_details.detail_key, ciniki_business_user_details.detail_value "
		. "FROM ciniki_business_users "
		. "LEFT JOIN ciniki_users ON (ciniki_business_users.user_id = ciniki_users.id ) "
		. "LEFT OUTER JOIN ciniki_business_user_details ON (ciniki_business_users.business_id = ciniki_business_user_details.business_id "
			. "AND ciniki_business_users.user_id = ciniki_business_user_details.user_id ) "
		. "WHERE ciniki_business_users.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND ciniki_business_users.status = 1 "
		. "";
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
	$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.businesses', array(
		array('container'=>'users', 'fname'=>'user_id', 'name'=>'user', 
			'fields'=>array('id'=>'user_id', 'firstname', 'lastname', 'email', 'display_name'),
			'details'=>array('detail_key'=>'detail_value'),
			),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['users']) ) {
		return array('stat'=>'ok', 'users'=>array());
	}

	return array('stat'=>'ok', 'users'=>$rc['users']);
}
?>
