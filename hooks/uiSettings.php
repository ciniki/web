<?php
//
// Description
// -----------
// This function will return a list of user interface settings for the module.
//
// Arguments
// ---------
// ciniki:
// tnid:     The ID of the tenant to get events for.
//
// Returns
// -------
//
function ciniki_web_hooks_uiSettings($ciniki, $tnid, $args) {

    $settings = array();

    //
    // Get the base url for their website
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'lookupTenantURL');
    $rc = ciniki_web_lookupTenantURL($ciniki, $tnid);
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }
    if( isset($rc['url']) ) {
        $settings['base_url'] = $rc['url'];
    }

    //
    // Get the sitename if no domain is specified
    //
    $strsql = "SELECT sitename FROM ciniki_tenants "
        . "WHERE id = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.tenants', 'tenant');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['tenant']) ) {
        $settings['sitename'] = $rc['tenant']['sitename'];
    }

    $rsp = array('stat'=>'ok', 'settings'=>$settings, 'menu_items'=>array());  

    //
    // Check permissions for what menu items should be available
    //
    if( isset($ciniki['tenant']['modules']['ciniki.web'])
        && (isset($args['permissions']['owners'])
            || isset($args['permissions']['employees'])
            || isset($args['permissions']['resellers'])
            || ($ciniki['session']['user']['perms']&0x01) == 0x01
            )
        ) {
        $menu_item = array(
            'priority'=>1000,
//            'label'=>'Website' . (ciniki_core_checkModuleActive($ciniki, 'ciniki.wng') ? ' (legacy)' : ''), 
            'label'=>'Website',
            'edit'=>array('app'=>'ciniki.web.main'),
            );
        $rsp['menu_items'][] = $menu_item;
    } 

    return $rsp;
}
?>
