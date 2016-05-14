<?php
//
// Description
// -----------
// This function will switch the session to another customer the current customer has access to.
// This is done with parent/child accounds in ciniki.customers.
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_generatePageAccountSwitch(&$ciniki, $settings, $business_id, $customer_id) {

    //
    // Make sure the new account exists
    //
    if( !isset($ciniki['session']['customers'][$customer_id]) ) {
        return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'2901', 'msg'=>'Account does not exist'));
    }

    //
    // Switch the session variables
    //
    $_SESSION['customer'] = $ciniki['session']['customers'][$customer_id];
    $_SESSION['customer']['email'] = $ciniki['session']['login']['email'];
    $ciniki['session']['customer'] = $_SESSION['customer'];

    //
    // call each modules session unload
    //
    foreach($ciniki['business']['modules'] as $module => $m) {
        list($pkg, $mod) = explode('.', $module);
        $rc = ciniki_core_loadMethod($ciniki, $pkg, $mod, 'web', 'accountSessionUnload');
        if( $rc['stat'] == 'ok' ) {
            $fn = $rc['function_call'];
            $rc = $fn($ciniki, $settings, $business_id);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'2903', 'msg'=>'Unable to unload account information', 'err'=>$rc['err']));
            }
        }
        if( isset($ciniki['session'][$module]) ) {
            unset($ciniki['session'][$module]);
        }
        if( isset($_SESSION[$module]) ) {
            unset($_SESSION[$module]);
        }
    }

    //
    // Call each modules session load for the new user
    //
    foreach($ciniki['business']['modules'] as $module => $m) {
        list($pkg, $mod) = explode('.', $module);
        $rc = ciniki_core_loadMethod($ciniki, $pkg, $mod, 'web', 'accountSessionLoad');
        if( $rc['stat'] == 'ok' ) {
            $fn = $rc['function_call'];
            $rc = $fn($ciniki, $settings, $business_id);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'2904', 'msg'=>'Unable to load account information', 'err'=>$rc['err']));
            }
        }
    }

    //
    // Check for a account switch redirect
    //
    if( isset($_SESSION['account_chooser_redirect']) && $_SESSION['account_chooser_redirect'] != '' ) {
        $redirect = $_SESSION['account_chooser_redirect'];
        $_SESSION['account_chooser_redirect'] = '';
        if( $redirect == 'back' 
            && isset($_SESSION['login_referer']) && $_SESSION['login_referer'] != '' ) {
            header('Location: ' . $_SESSION['login_referer']);
            $_SESSION['login_referer'] = '';
            exit;
        }
        if( $redirect != '' ) {
            header('Location: ' . $ciniki['request']['ssl_domain_base_url'] . $redirect);
            exit;
        }
    } 
    header('Location: ' . ($ciniki['request']['ssl_domain_base_url']!=''?$ciniki['request']['ssl_domain_base_url']:'') . '/account');
    exit;

	return array('stat'=>'ok');
}
?>
