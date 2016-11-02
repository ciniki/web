<?php
//
// Description
// -----------
// This function will search the web index and return the results ready to be shipped back to the customer.
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_web_indexSearch(&$ciniki, $settings, $business_id, $search_str, $limit) {

    $words = explode(' ', $search_str);
    $primary_sql = '';
    $secondary_sql = '';
    $tertiary_sql = '';
    foreach($words as $word) {
        if( trim($word) == '' ) { 
            continue;
        }
        $primary_sql .= "AND (primary_words LIKE '" . ciniki_core_dbQuote($ciniki, $word) . "%' OR primary_words LIKE '% " . ciniki_core_dbQuote($ciniki, $word) . "%') ";
        $secondary_sql .= "AND ("
            . "primary_words LIKE '" . ciniki_core_dbQuote($ciniki, $word) . "%' OR primary_words LIKE '% " . ciniki_core_dbQuote($ciniki, $word) . "%'"
            . "OR secondary_words LIKE '" . ciniki_core_dbQuote($ciniki, $word) . "%' OR secondary_words LIKE '% " . ciniki_core_dbQuote($ciniki, $word) . "%'"
            . ") ";
        $tertiary_sql .= "AND ("
            . "primary_words LIKE '" . ciniki_core_dbQuote($ciniki, $word) . "%' OR primary_words LIKE '% " . ciniki_core_dbQuote($ciniki, $word) . "%'"
            . "OR secondary_words LIKE '" . ciniki_core_dbQuote($ciniki, $word) . "%' OR secondary_words LIKE '% " . ciniki_core_dbQuote($ciniki, $word) . "%'"
            . "OR tertiary_words LIKE '" . ciniki_core_dbQuote($ciniki, $word) . "%' OR tertiary_words LIKE '% " . ciniki_core_dbQuote($ciniki, $word) . "%'"
            . ") ";
    }

    if( $primary_sql == '' ) {
        return array('stat'=>'ok', 'results'=>array());
    }

    //
    // Start with searching primary words
    //
    $strsql = "SELECT id, label, title, subtitle, meta, primary_image_id, synopsis, object, url "
        . "FROM ciniki_web_index "
        . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
        . $primary_sql
        . "ORDER BY weight DESC "
        . "LIMIT $limit "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashIDQuery');
    $rc = ciniki_core_dbHashIDQuery($ciniki, $strsql, 'ciniki.web', 'results', 'id');
    if( $rc['stat'] == 'ok' && isset($rc['results']) ) {
        $results = $rc['results'];
    } else {
        $results = array();
    }

    //
    // Add secondary results
    //
    if( count($results) < $limit ) {
        $strsql = "SELECT id, label, title, subtitle, meta, primary_image_id, synopsis, object, url "
            . "FROM ciniki_web_index "
            . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
            . $secondary_sql
            . "ORDER BY weight DESC "
            . "LIMIT $limit "
            . "";
        $rc = ciniki_core_dbHashIDQuery($ciniki, $strsql, 'ciniki.web', 'results', 'id');
        if( $rc['stat'] == 'ok' && isset($rc['results']) ) {
            $results = array_replace($results, $rc['results']);
        } else {
            $results = array();
        }
    }

    //
    // Add tertiary results
    //
    if( count($results) < $limit ) {
        $strsql = "SELECT id, label, title, subtitle, meta, primary_image_id, synopsis, object, url "
            . "FROM ciniki_web_index "
            . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
            . $tertiary_sql
            . "ORDER BY weight DESC "
            . "LIMIT $limit "
            . "";
        $rc = ciniki_core_dbHashIDQuery($ciniki, $strsql, 'ciniki.web', 'results', 'id');
        if( $rc['stat'] == 'ok' && isset($rc['results']) ) {
            $results = array_replace($results, $rc['results']);
        } else {
            $results = array();
        }
    }

    $final_results = array();
    foreach($results as $rid => $result) {
        //
        // create image url
        //
        if( $result['primary_image_id'] > 0 ) {
//            $result['primary_image_url'] = 'http://' . $ciniki['request']['domain'] . $ciniki['request']['cache_url'] 
//                . sprintf("/%02d/%07d/search/%010d.jpg", $business_id, $business_id, $result['primary_image_id']);
            $result['primary_image_url'] = $ciniki['business']['web_cache_url'] . sprintf("/search/%012d.jpg", $result['primary_image_id']);
        } else {
            $result['primary_image_url'] = '';
        }
        $result['url'] = $ciniki['request']['base_url'] . $result['url'];
        $result['class'] = str_replace('.', '-', $result['object']);
        unset($result['object']);
        $final_results[] = $result;
    }

    return array('stat'=>'ok', 'results'=>$final_results);
}
?>
