<?php
//
// Description
// -----------
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_web_pageLoad($ciniki, $settings, $tnid, $args) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');

    //
    // Get an intermediate page
    //
    if( isset($args['intermediate_permalink']) && $args['intermediate_permalink'] != '' 
        && isset($args['parent_id']) 
        ) {
        $strsql = "SELECT id, uuid, title, flags, permalink, page_password "
            . "FROM ciniki_web_pages "
            . "WHERE ciniki_web_pages.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . "AND ciniki_web_pages.parent_id = '" . ciniki_core_dbQuote($ciniki, $args['parent_id']) . "' "
            . "AND ciniki_web_pages.permalink = '" . ciniki_core_dbQuote($ciniki, $args['intermediate_permalink']) . "' "
            . "AND (ciniki_web_pages.flags&0x01) = 0x01 "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.web', 'page');
        if( $rc['stat'] != 'ok' || !isset($rc['page']) ) {
            return array('stat'=>'404', 'err'=>array('code'=>'ciniki.web.113', 'msg'=>"I'm sorry, but we were unable to find the page you requested."));
        }
        $page = $rc['page'];

        //
        // Check for children
        //
        $page['children'] = array();
        $strsql = "SELECT id, title, flags, permalink, page_password "
            . "FROM ciniki_web_pages "
            . "WHERE parent_id = '" . ciniki_core_dbQuote($ciniki, $page['id']) . "' "
            . "AND tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . "";
        if( isset($ciniki['session']['customer']['id']) && $ciniki['session']['customer']['id'] > 0 ) {
            $strsql .= "AND (flags&0x01) = 0x01 "; // Public and private pages
        } else {
            $strsql .= "AND (flags&0x03) = 0x01 ";  // Public pages only
        }
        $strsql .= "ORDER BY category, sequence, title "
            . "";
        $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.customers', array(
            array('container'=>'children', 'fname'=>'permalink', 
                'fields'=>array('id', 'name'=>'title', 'flags', 'permalink', 'page_password')),
            ));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['children']) ) {
            $page['children'] = $rc['children'];
            //
            // Check for private pages
            //
            foreach($page['children'] as $cid => $child) {
                
            }
        }
        //
        // Get any sponsors for this page, and that references for sponsors is enabled
        //
        if( isset($ciniki['tenant']['modules']['ciniki.sponsors']) 
            && ($ciniki['tenant']['modules']['ciniki.sponsors']['flags']&0x02) == 0x02
            ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'sponsors', 'web', 'sponsorRefList');
            $rc = ciniki_sponsors_web_sponsorRefList($ciniki, $settings, $tnid, 
                'ciniki.web.page', $page['id']);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            if( isset($rc['sponsors']) ) {
                $page['sponsors'] = $rc['sponsors'];
            }
        }
        return array('stat'=>'ok', 'page'=>$page);
    }

    //
    // Get the main information
    //
    $strsql = "SELECT ciniki_web_pages.id, "
        . "ciniki_web_pages.uuid, "
        . "ciniki_web_pages.parent_id, "
        . "ciniki_web_pages.title, "
        . "ciniki_web_pages.permalink, "
        . "ciniki_web_pages.sequence, "
        . "ciniki_web_pages.page_type, "
        . "ciniki_web_pages.page_redirect_url, "
        . "ciniki_web_pages.page_module, "
        . "ciniki_web_pages.flags, "
        . "ciniki_web_pages.page_password, "
        . "ciniki_web_pages.primary_image_id, "
        . "ciniki_web_pages.primary_image_caption, "
        . "ciniki_web_pages.primary_image_url, "
        . "ciniki_web_pages.child_title, "
        . "ciniki_web_pages.synopsis, "
        . "ciniki_web_pages.content, "
        . "ciniki_web_page_images.image_id, "
        . "ciniki_web_page_images.name AS image_name, "
        . "ciniki_web_page_images.permalink AS image_permalink, "
        . "ciniki_web_page_images.description AS image_description, "
        . "UNIX_TIMESTAMP(ciniki_web_page_images.last_updated) AS image_last_updated "
        . "FROM ciniki_web_pages "
        . "LEFT JOIN ciniki_web_page_images ON ("
            . "ciniki_web_pages.id = ciniki_web_page_images.page_id "
            . "AND ciniki_web_pages.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . "AND (ciniki_web_page_images.webflags&0x01) = 0 "
            . ") "
        . "WHERE ciniki_web_pages.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND (ciniki_web_pages.flags&0x01) = 0x01 "
        . "";
    //
    // Permalink or Content Type must be specified
    //
    if( isset($args['permalink']) && $args['permalink'] != '' 
        && isset($args['parent_id']) 
        ) {
        $strsql .= "AND ciniki_web_pages.permalink = '" . ciniki_core_dbQuote($ciniki, $args['permalink']) . "' "
            . "AND ciniki_web_pages.parent_id = '" . ciniki_core_dbQuote($ciniki, $args['parent_id']) . "' "
            . "";
    } else {
        return array('stat'=>'404', 'err'=>array('code'=>'ciniki.web.114', 'msg'=>'I\'m sorry, we were unable to find the page you requested.'));
    }
    $strsql .= "ORDER BY ciniki_web_pages.id, ciniki_web_page_images.sequence, ciniki_web_page_images.name, ciniki_web_page_images.date_added ";
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.info', array(
        array('container'=>'page', 'fname'=>'id',
            'fields'=>array('id', 'uuid', 'parent_id', 
                'title', 'permalink', 'sequence', 'page_type', 'page_redirect_url', 'page_module', 'flags', 'page_password',
                'image_id'=>'primary_image_id', 'image_caption'=>'primary_image_caption', 
                'image_url'=>'primary_image_url', 'child_title', 'synopsis', 'content')),
        array('container'=>'images', 'fname'=>'image_id', 
            'fields'=>array('image_id', 'title'=>'image_name', 'permalink'=>'image_permalink',
                'description'=>'image_description', 'last_updated'=>'image_last_updated')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['page']) || count($rc['page']) < 1 ) {
        return array('stat'=>'404', 'err'=>array('code'=>'ciniki.web.115', 'msg'=>"I'm sorry, but we can't find the page you requested."));
    }
    $page = array_pop($rc['page']);

    //
    // Check if any files are attached to the page
    //
    $strsql = "SELECT id, name, extension, permalink, sequence, description "
        . "FROM ciniki_web_page_files "
        . "WHERE ciniki_web_page_files.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND ciniki_web_page_files.page_id = '" . ciniki_core_dbQuote($ciniki, $page['id']) . "' "
        . "";
    if( ($page['flags']&0x1000) == 0x1000 ) {
        $strsql .= "ORDER BY sequence DESC, name DESC ";
    } else {
        $strsql .= "ORDER BY sequence ASC, name ASC ";
    }
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.info', array(
        array('container'=>'files', 'fname'=>'id', 
            'fields'=>array('id', 'name', 'extension', 'permalink', 'description')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['files']) ) {
        $page['files'] = $rc['files'];
    }

    //
    // Check if there are any children
    //
    $strsql = "SELECT id, title, permalink, "
        . "page_type, "
        . "page_redirect_url, "
        . "primary_image_id, "
        . "category, synopsis, content, "
        . "IF(content<>'','yes','no') AS is_details "
        . "FROM ciniki_web_pages "
        . "WHERE parent_id = '" . ciniki_core_dbQuote($ciniki, $page['id']) . "' "
        . "AND tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "";
    if( isset($ciniki['session']['customer']['id']) && $ciniki['session']['customer']['id'] > 0 ) {
        $strsql .= "AND (flags&0x01) = 0x01 "; // Public and private pages
    } else {
        $strsql .= "AND (flags&0x03) = 0x01 ";  // Public pages only
    }
    $strsql .= "ORDER BY category, sequence, title "
        . "";
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.customers', array(
        array('container'=>'children', 'fname'=>'category', 'fields'=>array('name'=>'category')),
        array('container'=>'list', 'fname'=>'id', 
            'fields'=>array('id', 'page_type', 'page_redirect_url', 'title', 'permalink', 'image_id'=>'primary_image_id',
                'synopsis', 'content', 'is_details')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['children']) ) {  
        // If only one category or no category, then display as a list.
        if( isset($page['flags']) && ($page['flags']&0x0400) == 0x0400 ) {
            $page['children'] = array_pop($rc['children']);
            $page['children'] = $page['children']['list'];
        }
        elseif( count($rc['children']) == 1 ) {
            $page['children'] = array();
            $list = array_pop($rc['children']);
            $list = $list['list'];
            foreach($list as $cid => $child) {
                $page['children'][$child['permalink']] = array(
                    'id'=>$child['id'], 
                    'name'=>$child['title'], 
                    'permalink'=>$child['permalink'], 
                    'list'=>array($cid=>$child),
                    );
            }
        } else {
            $page['child_categories'] = $rc['children'];
        }
    }

    //
    // Get any sponsors for this page, and that references for sponsors is enabled
    //
    if( isset($ciniki['tenant']['modules']['ciniki.sponsors']) 
        && ($ciniki['tenant']['modules']['ciniki.sponsors']['flags']&0x02) == 0x02
        ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'sponsors', 'web', 'sponsorRefList');
        $rc = ciniki_sponsors_web_sponsorRefList($ciniki, $settings, $tnid, 
            'ciniki.web.page', $page['id']);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['sponsors']) ) {
            $page['sponsors'] = $rc['sponsors'];
        }
    }

    return array('stat'=>'ok', 'page'=>$page);
}
?>
