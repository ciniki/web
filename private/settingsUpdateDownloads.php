<?php
//
// Description
// ===========
// When file uploaded to the file depot module are available to the public
// this function makes sure page-downloads-public settings is set to 'yes'.
//
// If the file is only available to customers, the setting 
// page-downloads-customers is set to 'yes'.
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
function ciniki_web_settingsUpdateDownloads($ciniki, $modules, $tnid) {

    //
    // Default set the flags to 'no'
    //
    $public = 'no';
    $customers = 'no';

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbCount');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbInsert');

    if( isset($modules['ciniki.filedepot']) ) {
        //
        // Check for public files
        //
        $strsql = "SELECT 'page-downloads-public' AS name, COUNT(*) "
            . "FROM ciniki_filedepot_files "
            . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . "AND (sharing_flags&0x01) = 0x01 "
            . "AND status = 1 "
            . "";
        $rc = ciniki_core_dbCount($ciniki, $strsql, 'ciniki.filedepot', 'public');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['public']['page-downloads-public']) && $rc['public']['page-downloads-public'] > 0 ) {
            $public = 'yes';
        }

        //
        // Check for customer only files
        //
        $strsql = "SELECT 'page-downloads-customers' AS name, COUNT(*) "
            . "FROM ciniki_filedepot_files "
            . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . "AND (sharing_flags&0x02) = 0x02 "
            . "AND status = 1 "
            . "";
        $rc = ciniki_core_dbCount($ciniki, $strsql, 'ciniki.filedepot', 'customers');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['customers']['page-downloads-customers']) && $rc['customers']['page-downloads-customers'] > 0 ) {
            $customers = 'yes';
        }
    }

    //
    // Future: Add other module checks here
    //

    //
    // Get the current settings
    //
    $strsql = "SELECT detail_value "
        . "FROM ciniki_web_settings "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND detail_key = 'page-downloads-public' "
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
                . "AND detail_key = 'page-downloads-public' "
                . "";
            $rc = ciniki_core_dbUpdate($ciniki, $strsql, 'ciniki.web');
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.web', 'ciniki_web_history', $tnid, 
                2, 'ciniki_web_settings', 'page-downloads-public', 'detail_value', $public);
            $ciniki['syncqueue'][] = array('push'=>'ciniki.web.setting',
                'args'=>array('id'=>'page-downloads-public'));
        }
    } else {
        //
        // Add the public settings
        //
        $strsql = "INSERT INTO ciniki_web_settings (tnid, detail_key, detail_value, date_added, last_updated) "
            . "VALUES ('" . ciniki_core_dbQuote($ciniki, $tnid) . "'"
            . ", '" . ciniki_core_dbQuote($ciniki, 'page-downloads-public') . "' "
            . ", '" . ciniki_core_dbQuote($ciniki, $public) . "'"
            . ", UTC_TIMESTAMP(), UTC_TIMESTAMP()) "
            . "";
        $rc = ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.web');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.web', 'ciniki_web_history', $tnid, 
            1, 'ciniki_web_settings', 'page-downloads-public', 'detail_value', $public);
        $ciniki['syncqueue'][] = array('push'=>'ciniki.web.setting',
            'args'=>array('id'=>'page-downloads-public'));
    }

    //
    // Get the current settings
    //
    $strsql = "SELECT detail_value "
        . "FROM ciniki_web_settings "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND detail_key = 'page-downloads-customers' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.web', 'detail');
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }
    if( isset($rc['detail']) ) {
        //
        // Update the customers settings, if there has been a change
        //
        if( $rc['detail']['detail_value'] != $customers ) {
            $strsql = "UPDATE ciniki_web_settings SET "
                . "detail_value = '" . ciniki_core_dbQuote($ciniki, $customers) . "' "
                . ", last_updated = UTC_TIMESTAMP() "
                . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
                . "AND detail_key = 'page-downloads-customers' "
                . "";
            $rc = ciniki_core_dbUpdate($ciniki, $strsql, 'ciniki.web');
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.web', 'ciniki_web_history', $tnid, 
                2, 'ciniki_web_settings', 'page-downloads-customers', 'detail_value', $customers);
            $ciniki['syncqueue'][] = array('push'=>'ciniki.web.setting',
                'args'=>array('id'=>'page-downloads-customers'));
        }
    } else {
        //
        // Add the customers settings
        //
        $strsql = "INSERT INTO ciniki_web_settings (tnid, detail_key, detail_value, date_added, last_updated) "
            . "VALUES ('" . ciniki_core_dbQuote($ciniki, $tnid) . "'"
            . ", '" . ciniki_core_dbQuote($ciniki, 'page-downloads-customers') . "' "
            . ", '" . ciniki_core_dbQuote($ciniki, $customers) . "'"
            . ", UTC_TIMESTAMP(), UTC_TIMESTAMP()) "
            . "";
        $rc = ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.web');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.web', 'ciniki_web_history', $tnid, 
            1, 'ciniki_web_settings', 'page-downloads-customers', 'detail_value', $customers);
        $ciniki['syncqueue'][] = array('push'=>'ciniki.web.setting',
            'args'=>array('id'=>'page-downloads-customers'));
    }

    //
    // Update the public settings
    //
//  $strsql = "INSERT INTO ciniki_web_settings (tnid, detail_key, detail_value, date_added, last_updated) "
//      . "VALUES ('" . ciniki_core_dbQuote($ciniki, $tnid) . "'"
//      . ", '" . ciniki_core_dbQuote($ciniki, 'page-downloads-public') . "' "
//      . ", '" . ciniki_core_dbQuote($ciniki, $public) . "'"
//      . ", UTC_TIMESTAMP(), UTC_TIMESTAMP()) "
//      . "ON DUPLICATE KEY UPDATE detail_value = '" . ciniki_core_dbQuote($ciniki, $public) . "' "
//      . ", last_updated = UTC_TIMESTAMP() "
//      . "";
//  $rc = ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.web');
//  if( $rc['stat'] != 'ok' ) {
//      return $rc;
//  }

    //
    // Update the customers settings
    //
//  $strsql = "INSERT INTO ciniki_web_settings (tnid, detail_key, detail_value, date_added, last_updated) "
//      . "VALUES ('" . ciniki_core_dbQuote($ciniki, $tnid) . "'"
//      . ", '" . ciniki_core_dbQuote($ciniki, 'page-downloads-customers') . "' "
//      . ", '" . ciniki_core_dbQuote($ciniki, $customers) . "'"
//      . ", UTC_TIMESTAMP(), UTC_TIMESTAMP()) "
//      . "ON DUPLICATE KEY UPDATE detail_value = '" . ciniki_core_dbQuote($ciniki, $customers) . "' "
//      . ", last_updated = UTC_TIMESTAMP() "
//      . "";
//  $rc = ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.web');
//  if( $rc['stat'] != 'ok' ) {
//      return $rc;
//  }

    //
    // Update the last_change date in the tenant modules
    // Ignore the result, as we don't want to stop user updates if this fails.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'updateModuleChangeDate');
    ciniki_tenants_updateModuleChangeDate($ciniki, $tnid, 'ciniki', 'web');

    return array('stat'=>'ok');
}
?>
