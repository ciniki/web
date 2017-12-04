<?php
//
// Description
// ===========
// When a subscription is added or updated, this function should be called
// to update the web settings either turning on or off the sign in feature.
//
// If there are subscriptions available for customer updating,
// page-subscriptions-public is set to 'yes'.
//
// The web module uses these flags to determine if there should be a menu option
// enabled for Downloads.
//
// Arguments
// =========
// ciniki:
// modules:     The array of modules enabled for the tenant.  This is returned by the 
//              ciniki_web_checkAccess function.
// tnid: The ID of the tenant to check for downloads.
//
// Returns
// =======
//
function ciniki_web_settingsUpdateSubscriptions(&$ciniki, $modules, $tnid) {

    //
    // Default set the flags to 'no'
    //
    $public = 'no';

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbCount');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbInsert');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbUpdate');

    if( isset($modules['ciniki.subscriptions']) ) {
        //
        // Check for public files
        //
        $strsql = "SELECT 'page-subscriptions-public' AS name, COUNT(*) "
            . "FROM ciniki_subscriptions "
            . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . "AND (flags&0x01) = 0x01 "
            . "";
        $rc = ciniki_core_dbCount($ciniki, $strsql, 'ciniki.subscriptions', 'public');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['public']['page-subscriptions-public']) && $rc['public']['page-subscriptions-public'] > 0 ) {
            $public = 'yes';
        }
    }

    //
    // Get the current settings
    //
    $strsql = "SELECT detail_value "
        . "FROM ciniki_web_settings "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND detail_key = 'page-subscriptions-public' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.web', 'detail');
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }
    if( isset($rc['detail']) ) {
        //
        // Update the public settings, if there has been a change
        //
        if( $rc['detail']['detail_value'] != $public ) {
            $strsql = "UPDATE ciniki_web_settings SET "
                . "detail_value = '" . ciniki_core_dbQuote($ciniki, $public) . "' "
                . ", last_updated = UTC_TIMESTAMP() "
                . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
                . "AND detail_key = 'page-subscriptions-public' "
                . "";
            $rc = ciniki_core_dbUpdate($ciniki, $strsql, 'ciniki.web');
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.web', 'ciniki_web_history', $tnid, 
                2, 'ciniki_web_settings', 'page-subscriptions-public', 'detail_value', $public);
            $ciniki['syncqueue'][] = array('push'=>'ciniki.web.setting',
                'args'=>array('id'=>'page-subscriptions-public'));
        }
    } else {
        //
        // Add the public settings
        //
        $strsql = "INSERT INTO ciniki_web_settings (tnid, detail_key, detail_value, date_added, last_updated) "
            . "VALUES ('" . ciniki_core_dbQuote($ciniki, $tnid) . "'"
            . ", '" . ciniki_core_dbQuote($ciniki, 'page-subscriptions-public') . "' "
            . ", '" . ciniki_core_dbQuote($ciniki, $public) . "'"
            . ", UTC_TIMESTAMP(), UTC_TIMESTAMP()) "
            . "";
        $rc = ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.web');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.web', 'ciniki_web_history', $tnid, 
            1, 'ciniki_web_settings', 'page-subscriptions-public', 'detail_value', $public);
        $ciniki['syncqueue'][] = array('push'=>'ciniki.web.setting',
            'args'=>array('id'=>'page-subscriptions-public'));
    }

    //
    // Update the last_change date in the tenant modules
    // Ignore the result, as we don't want to stop user updates if this fails.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'updateModuleChangeDate');
    ciniki_tenants_updateModuleChangeDate($ciniki, $tnid, 'ciniki', 'web');

    return array('stat'=>'ok');
}
?>
