<?php
//
// Description
// -----------
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:         The ID of the tenant to update the page for.
//
// Returns
// -------
// <rsp stat='ok' />
//
function ciniki_web_pageUpdate(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'page_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Page ID'), 
        'parent_id'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Parent'), 
        'title'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Menu Title'), 
        'permalink'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Permalink'), 
        'article_title'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Page Title'), 
        'category'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Category'),
        'sequence'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Sequence'),
        'page_type'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Type'),
        'page_redirect_url'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Redirect'),
        'page_module'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Module'),
        'menu_flags'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Menu Options'),
        'flags'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Options'),
        'page_password'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Page Password'),
        'primary_image_id'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Image'),
        'primary_image_caption'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Image Caption'),
        'primary_image_url'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Image URL'),
        'child_title'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Children Title'),
        'synopsis'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Synopsis'),
        'content'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Content'), 
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];

    //  
    // Make sure this module is activated, and
    // check permission to run this function for this tenant
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'checkAccess');
    $rc = ciniki_web_checkAccess($ciniki, $args['tnid'], 'ciniki.web.pageUpdate'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }

    //
    // Get the existing page details 
    //
    $strsql = "SELECT id, parent_id, uuid, sequence, page_type, page_module "
        . "FROM ciniki_web_pages "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND id = '" . ciniki_core_dbQuote($ciniki, $args['page_id']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.web', 'item');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['item']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.155', 'msg'=>'Page not found'));
    }
    $item = $rc['item'];
    $old_sequence = $rc['item']['sequence'];
    $parent_id = $rc['item']['parent_id'];

    if( isset($args['title']) ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');
        $args['permalink'] = ciniki_core_makePermalink($ciniki, $args['title']);
        //
        // Make sure the permalink is unique
        //
        $strsql = "SELECT id, title, permalink "
            . "FROM ciniki_web_pages "
            . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND parent_id = '" . ciniki_core_dbQuote($ciniki, $item['parent_id']) . "' "
            . "AND permalink = '" . ciniki_core_dbQuote($ciniki, $args['permalink']) . "' "
            . "AND id <> '" . ciniki_core_dbQuote($ciniki, $args['page_id']) . "' "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.web', 'image');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( $rc['num_rows'] > 0 ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.156', 'msg'=>'You already have page with this title, please choose another title.'));
        }
    }

    //
    // Grab the old sequence
    //
/*  if( isset($args['sequence']) ) {
        $strsql = "SELECT id, parent_id, sequence "
            . "FROM ciniki_web_pages "
            . "WHERE id = '" . ciniki_core_dbQuote($ciniki, $args['page_id']) . "' "
            . "AND tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.web', 'item');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( !isset($rc['item']) ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.157', 'msg'=>'Unable to find page'));
        }
    }
*/

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
    $rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.web');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Update the page in the database
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
    $rc = ciniki_core_objectUpdate($ciniki, $args['tnid'], 'ciniki.web.page', $args['page_id'], $args);
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'ciniki.web');
        return $rc;
    }

    //
    // Update any sequences
    //
    if( isset($args['sequence']) && $parent_id > 0 ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'pageUpdateSequences');
        $rc = ciniki_web_pageUpdateSequences($ciniki, $args['tnid'], 
            $parent_id, $args['sequence'], $old_sequence);
        if( $rc['stat'] != 'ok' ) {
            ciniki_core_dbTransactionRollback($ciniki, 'ciniki.web');
            return $rc;
        }
    }

    //
    // Update any page settings for modules
    //
    if( (isset($args['page_type']) && $args['page_type'] == '30') || (!isset($args['page_type']) && $item['page_type'] == '30') ) {
        $page_module = isset($args['page_module']) ? $args['page_module'] : $item['page_module'];
        list($pkg, $mod) = explode('.', $page_module);
        //
        // Get any module options
        //
        $rc = ciniki_core_loadMethod($ciniki, $pkg, $mod, 'hooks', 'webOptions');
        if( $rc['stat'] == 'ok' ) {
            $fn = $rc['function_call'];
            $rc = $fn($ciniki, $args['tnid'], array());
            if( $rc['stat'] == 'ok' && isset($rc['pages'][$page_module]['options']) ) {
                //
                // Check for options that need updating
                //
                $options = $rc['pages'][$page_module]['options'];
                ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbInsert');
                foreach($options as $oid => $option) {
                    if( isset($ciniki['request']['args'][$option['setting']]) ) {
                        $strsql = "INSERT INTO ciniki_web_settings (tnid, detail_key, detail_value, date_added, last_updated) "
                            . "VALUES ('" . ciniki_core_dbQuote($ciniki, $ciniki['request']['args']['tnid']) . "'"
                            . ", '" . ciniki_core_dbQuote($ciniki, $option['setting']) . "' "
                            . ", '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['args'][$option['setting']]) . "'"
                            . ", UTC_TIMESTAMP(), UTC_TIMESTAMP()) "
                            . "ON DUPLICATE KEY UPDATE detail_value = '" . ciniki_core_dbQuote($ciniki, $ciniki['request']['args'][$option['setting']]) . "' "
                            . ", last_updated = UTC_TIMESTAMP() "
                            . "";
                        $rc = ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.web');
                        if( $rc['stat'] != 'ok' ) {
                            ciniki_core_dbTransactionRollback($ciniki, 'ciniki.web');
                            return $rc;
                        }
                        ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.web', 'ciniki_web_history', $args['tnid'], 
                            2, 'ciniki_web_settings', $option['setting'], 'detail_value', $ciniki['request']['args'][$option['setting']]);
                        $ciniki['syncqueue'][] = array('push'=>'ciniki.web.setting',
                            'args'=>array('id'=>$option['setting']));
                    }
                }
            }
        }
    }

    //
    // Commit the changes to the database
    //
    $rc = ciniki_core_dbTransactionCommit($ciniki, 'ciniki.web');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Update the last_change date in the tenant modules
    // Ignore the result, as we don't want to stop user updates if this fails.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'updateModuleChangeDate');
    ciniki_tenants_updateModuleChangeDate($ciniki, $args['tnid'], 'ciniki', 'web');

    return array('stat'=>'ok');
}
?>
