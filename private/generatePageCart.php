<?php
//
// Description
// -----------
// This function will generate a customers cart, add/update/delete items and process checkout
// through paypal.
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_generatePageCart(&$ciniki, $settings) {

	//
	// Store the content created by the page
	// Make sure everything gets generated ok before returning the content
	//
	$content = '';
	$display_cart = 'yes';
	$cart_err_msg = '';
	$cart = NULL;

	//
	// Required methods
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'web', 'cartLoad');

	//
	// Load the business modules
	//
	$modules = array();
	ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'getActiveModules');
	$rc = ciniki_businesses_getActiveModules($ciniki, $ciniki['request']['business_id']);
	if( $rc['stat'] == 'ok' ) {
		$modules = $rc['modules'];
	}

	//
	// Check if a cart already exists
	//
	$rc = ciniki_sapos_web_cartLoad($ciniki, $settings, $ciniki['request']['business_id']);
	if( $rc['stat'] == 'noexist' ) {
		$cart = NULL;
	} elseif( $rc['stat'] != 'ok' ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1693', 'msg'=>'Error processing shopping cart, please try again.'));
	}
	$cart = $rc['cart'];

	//
	// FIXME: Add check for cookies
	//

	//
	// Check if a item is being added to the cart
	//
	if( isset($_POST['action']) && $_POST['action'] == 'add' ) {
		if( $cart == NULL ) {
			// Create a shopping cart
			ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'web', 'cartCreate');
			$rc = ciniki_sapos_web_cartCreate($ciniki, $settings, $ciniki['request']['business_id'], array());
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			$cart = $rc['cart'];
			$_SESSION['cart']['sapos_id'] = $cart['id'];
			$_SESSION['cart']['num_items'] = count($cart['items']);
			$ciniki['session']['cart'] = array();
			$ciniki['session']['cart']['sapos_id'] = $cart['id'];
			$ciniki['session']['cart']['num_items'] = count($cart['items']);
		}
		//
		// Add the item to the cart
		//
		ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'web', 'cartItemAdd');
		$rc = ciniki_sapos_web_cartItemAdd($ciniki, $settings, $ciniki['request']['business_id'],
			array());
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		//
		// Load the updated cart
		//
		$rc = ciniki_sapos_web_cartLoad($ciniki, $settings, $ciniki['request']['business_id']);
		if( $rc['stat'] != 'ok' ) {	
			return $rc;
		}
	}
	
	//
	// Check if cart quantities were updated
	//

	//
	// Check if checkout
	//

	//
	// Check if returned from Paypal
	//

	//
	// Display the contents of the shopping cart
	//
	if( $display_cart == 'yes' && $cart != NULL) {
		//
		// Display cart items
		//
		if( isset($cart['items']) && count($cart['items']) > 0 ) {
			foreach($cart['items'] as $item_id => $item) {
				$page_content .= "Item: " . print_r($item, true);
				$page_content .= "<br/>";
			}
		}

		//
		// Display checkout button
		//
	}


	//
	// Add the header
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageHeader');
	$rc = ciniki_web_generatePageHeader($ciniki, $settings, 'Shopping Cart', array());
	if( $rc['stat'] != 'ok' ) {	
		return $rc;
	}
	$page_content = $rc['content'];
	
	if( $content != '' ) {
		$page_content .= $content;
	}

	//
	// Add the footer
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageFooter');
	$rc = ciniki_web_generatePageFooter($ciniki, $settings);
	if( $rc['stat'] != 'ok' ) {	
		return $rc;
	}
	$page_content .= $rc['content'];

	return array('stat'=>'ok', 'content'=>$page_content);
}
?>
