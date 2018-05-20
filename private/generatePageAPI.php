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
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_generatePageAPI(&$ciniki, $settings) {

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
            $rc = $fn($ciniki, $settings, $ciniki['request']['tnid'], array(
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
        $rc = ciniki_web_indexSearch($ciniki, $settings, $ciniki['request']['tnid'], $search_str, ((isset($_GET['limit'])&&$_GET['limit']>0)?$_GET['limit']:21));
        if( $rc['stat'] == 'ok' ) {
            $rsp = $rc;
        }
    }

    //
    // Check for Callback requests
    //
    elseif( $ciniki['request']['uri_split'][0] == 'callback'
        && $ciniki['request']['uri_split'][1] != ''
        && $ciniki['request']['uri_split'][2] != '' 
        ) {
        $number = $ciniki['request']['uri_split'][1];
        $key = $ciniki['request']['uri_split'][2];
        if( !isset($ciniki['session']['ciniki.web']['callback-key']) || $key != $ciniki['session']['ciniki.web']['callback-key'] ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.197', 'msg'=>'Invalid request'));
        }

        error_log("CALLBACK: " . $number);

        //
        // Submit text message
        //
        if( isset($settings['site-callbacks-active']) && $settings['site-callbacks-active'] == 'yes' 
            && isset($settings['site-callbacks-number']) && $settings['site-callbacks-number'] != '' 
            ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'sms', 'hooks', 'addMessage');
            $rc = ciniki_sms_hooks_addMessage($ciniki, $ciniki['request']['tnid'], array(
                'cell_number' => $settings['site-callbacks-number'],
                'content' => "Website callback requested at: " . $number,
                'object' => 'ciniki.web.callback',
                'object_id' => 0,
                ));
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.198', 'msg'=>'Error trying to send sms alert.', 'err'=>$rc['err']));
            }
            $ciniki['smsqueue'][] = array('sms_id'=>$rc['id'], 'tnid' => $ciniki['request']['tnid']);
        }

        //
        // Submit email
        //
        if( isset($settings['site-callbacks-active']) && $settings['site-callbacks-active'] == 'yes' 
            && isset($settings['site-callbacks-email']) && $settings['site-callbacks-email'] != '' 
            ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'mail', 'hooks', 'addMessage');
            $rc = ciniki_mail_hooks_addMessage($ciniki, $ciniki['request']['tnid'], array(
                'customer_email' => $settings['site-callbacks-email'],
                'subject' => "Website callback requested: " . $number,
                'text_content' => "A visitor to your website has requested a callback at: " . $number,
                'html_content' => "A visitor to your website has requested a callback at: " . $number,
                'object' => 'ciniki.web.callback',
                'object_id' => 0,
                ));
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.198', 'msg'=>'Error trying to send email alert.', 'err'=>$rc['err']));
            }
            $ciniki['emailqueue'][] = array('mail_id'=>$rc['id'], 'tnid' => $ciniki['request']['tnid']);
        }

        //
        // Return ok
        //
        return array('stat'=>'ok');
    }
    //
    // Check for module processing
    //
    elseif( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] != '' 
        && isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] != '' 
        ) {
        $pkg = $ciniki['request']['uri_split'][0];
        $mod = $ciniki['request']['uri_split'][1];
        
        $rc = ciniki_core_loadMethod($ciniki, $pkg, $mod, 'web', 'processAPI');
        if( $rc['stat'] == 'ok' ) {
            $fn = $rc['function_call'];
            $args = array(
                'uri_split'=>$ciniki['request']['uri_split'],
                );
            array_shift($args['uri_split']);
            array_shift($args['uri_split']);
            $rsp = $fn($ciniki, $settings, $ciniki['request']['tnid'], $args);
        }
    }

    return $rsp;
}
?>
