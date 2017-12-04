<?php
//
// Description
// -----------
// Whenever contact information is added about a owner or employee, that
// information can be added to the contact page on the website if the
// page-contact-user-display-flags-<user_id>.  This function check if there are any
// users who have this flag turned on, and then make sure the global
// page-contact-user-display setting to is set to 'yes'.   This function also
// checks for page-about-user-display.
//
// The generatePageContact function uses this to determine if owner/employee
// contact information should be listed on the website.
//
// Arguments
// ---------
// ciniki:
// tnid:         The ID of the tenant to update the contact information for.
//
// Returns
// -------
//
function ciniki_web_updateUserDisplay($ciniki, $tnid) {
    //
    // Check for the contact page settings
    //
    $strsql = "SELECT COUNT(*) AS num_users "
        . "FROM ciniki_web_settings "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND detail_key LIKE 'page-contact-user-display-flags%' "
        . "AND detail_value > 0 "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.web', 'users');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['users']) && $rc['users']['num_users'] > 0 ) {
        $detail_value = 'yes';
    } else {
        $detail_value = 'no';
    }

    //
    // Get the current value
    //
    $strsql = "SELECT detail_value "
        . "FROM ciniki_web_settings "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND detail_key = 'page-contact-user-display' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.web', 'setting');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['setting']['detail_value']) ) {
        $current_value = $rc['setting']['detail_value'];
    } else {
        $current_value = '';
    }
    
    $field = 'page-contact-user-display';
    if( $current_value != $detail_value ) {
        $strsql = "INSERT INTO ciniki_web_settings (tnid, detail_key, detail_value, date_added, last_updated) "
            . "VALUES ('" . ciniki_core_dbQuote($ciniki, $tnid) . "'"
            . ", '" . ciniki_core_dbQuote($ciniki, $field) . "' "
            . ", '" . ciniki_core_dbQuote($ciniki, $detail_value) . "'"
            . ", UTC_TIMESTAMP(), UTC_TIMESTAMP()) "
            . "ON DUPLICATE KEY UPDATE detail_value = '" . ciniki_core_dbQuote($ciniki, $detail_value) . "' "
            . ", last_updated = UTC_TIMESTAMP() "
            . "";
        $rc = ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.web');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.web', 'ciniki_web_history', $tnid, 
            2, 'ciniki_web_settings', $field, 'detail_value', $detail_value);
    }

    //
    // Check for the about page settings
    //
    $strsql = "SELECT COUNT(*) AS num_users "
        . "FROM ciniki_web_settings "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND detail_key LIKE 'page-about-user-display-flags%' "
        . "AND detail_value > 0 "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.web', 'users');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['users']) && $rc['users']['num_users'] > 0 ) {
        $detail_value = 'yes';
    } else {
        $detail_value = 'no';
    }

    //
    // Get the current value
    //
    $strsql = "SELECT detail_value "
        . "FROM ciniki_web_settings "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND detail_key = 'page-about-user-display' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.web', 'setting');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['setting']['detail_value']) ) {
        $current_value = $rc['setting']['detail_value'];
    } else {
        $current_value = '';
    }
    
    $field = 'page-about-user-display';
    if( $current_value != $detail_value ) {
        $strsql = "INSERT INTO ciniki_web_settings (tnid, detail_key, detail_value, date_added, last_updated) "
            . "VALUES ('" . ciniki_core_dbQuote($ciniki, $tnid) . "'"
            . ", '" . ciniki_core_dbQuote($ciniki, $field) . "' "
            . ", '" . ciniki_core_dbQuote($ciniki, $detail_value) . "'"
            . ", UTC_TIMESTAMP(), UTC_TIMESTAMP()) "
            . "ON DUPLICATE KEY UPDATE detail_value = '" . ciniki_core_dbQuote($ciniki, $detail_value) . "' "
            . ", last_updated = UTC_TIMESTAMP() "
            . "";
        $rc = ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.web');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.web', 'ciniki_web_history', $tnid, 
            2, 'ciniki_web_settings', $field, 'detail_value', $detail_value);
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
