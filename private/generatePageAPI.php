<?php
//
// Description
// -----------
// This function will generate the API documentation available to the public.  This
// is currently a placeholder.
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_generatePageAPI($ciniki, $settings) {

	//
	// Store the content created by the page
	// Make sure everything gets generated ok before returning the content
	//
	$content = '';
	$page_content = '';

	$rsp = array('stat'=>'ok');
	

	//
	// Search for products that can be added to the cart
	//
	if( $ciniki['request']['uri_split'][0] == 'cart'
		&& $ciniki['request']['uri_split'][1] == 'search'
		&& $ciniki['request']['uri_split'][2] != '' 
		&& isset($settings['page-cart-active']) && $settings['page-cart-active'] == 'yes'
		) {
		$search_str = urldecode($ciniki['request']['uri_split'][2]);
	
		$rc = ciniki_core_loadMethod($ciniki, 'ciniki', 'products', 'web', 'searchProducts');
		if( $rc['stat'] == 'ok' ) {
			$fn = $rc['function_call'];
			$rc = $fn($ciniki, $settings, $ciniki['request']['business_id'], array(
				'search_str'=>$search_str,
				'limit'=>((isset($_GET['limit'])&&$_GET['limit']!=''&&$_GET['limit']>0)?$_GET['limit']:16)));
			if( $rc['stat'] == 'ok' ) {
				$rsp = $rc;
			}
		}
	}

	//
	// Search the site
	//
	elseif( $ciniki['request']['uri_split'][0] == 'site'
		&& $ciniki['request']['uri_split'][1] == 'search'
		&& $ciniki['request']['uri_split'][2] != '' 
		) {
        $search_str = urldecode($ciniki['request']['uri_split'][2]);

        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'indexSearch');
        $rc = ciniki_web_indexSearch($ciniki, $settings, $ciniki['request']['business_id'], $search_str, ((isset($_GET['limit'])&&$_GET['limit']>0)?$_GET['limit']:21));
        if( $rc['stat'] == 'ok' ) {
            $rsp = $rc;
        }
	}

	return $rsp;
}
?>
