<?php
//
// Description
// -----------
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:         The ID of the tenant.
//
// Returns
// -------
//
function ciniki_web_pageGet($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'page_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Page'),
        'parent_id'=>array('required'=>'no', 'default'=>'0', 'blank'=>'no', 'name'=>'Parent'),
        'images'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Images'),
        'files'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Files'),
        'parentlist'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Parent List'),
        'children'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Children'),
        'sponsors'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Sponsors'),
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
    $rc = ciniki_web_checkAccess($ciniki, $args['tnid'], 'ciniki.web.pageGet'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
    $date_format = ciniki_users_dateFormat($ciniki);

    if( $args['page_id'] > 0 ) {
        //
        // Get the main webrmation
        //
        $strsql = "SELECT ciniki_web_pages.id, "
            . "ciniki_web_pages.parent_id, "
            . "ciniki_web_pages.title, "
            . "ciniki_web_pages.permalink, "
            . "ciniki_web_pages.article_title, "
            . "ciniki_web_pages.category, "
            . "ciniki_web_pages.sequence, "
            . "ciniki_web_pages.menu_flags, "
            . "ciniki_web_pages.flags, "
            . "ciniki_web_pages.page_password, "
            . "ciniki_web_pages.page_type, "
            . "ciniki_web_pages.page_redirect_url, "
            . "ciniki_web_pages.page_module, "
            . "ciniki_web_pages.primary_image_id, "
            . "ciniki_web_pages.primary_image_caption, "
            . "ciniki_web_pages.primary_image_url, "
            . "ciniki_web_pages.child_title, "
            . "ciniki_web_pages.synopsis, "
            . "ciniki_web_pages.content "
            . "FROM ciniki_web_pages "
            . "WHERE ciniki_web_pages.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND ciniki_web_pages.id = '" . ciniki_core_dbQuote($ciniki, $args['page_id']) . "' "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
        $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.web', array(
            array('container'=>'pages', 'fname'=>'id', 'name'=>'page',
                'fields'=>array('id', 'parent_id',
                    'title', 'permalink', 'article_title', 'category', 'sequence', 'page_type', 'page_redirect_url', 
                    'page_module', 'menu_flags', 'flags', 'page_password', 
                    'primary_image_id', 'primary_image_caption', 'primary_image_url', 
                    'child_title', 'synopsis', 'content')),
            ));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $page = $rc['pages'][0]['page'];

        //
        // Get the images
        //
        if( isset($args['images']) && $args['images'] == 'yes' ) {
            $strsql = "SELECT id, name, image_id, webflags "
                . "FROM ciniki_web_page_images "
                . "WHERE page_id = '" . ciniki_core_dbQuote($ciniki, $args['page_id']) . "' "
                . "AND tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
                . "";
            $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.web', array(
                array('container'=>'images', 'fname'=>'id', 'name'=>'image',
                    'fields'=>array('id', 'name', 'image_id', 'webflags')),
                ));
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            if( isset($rc['images']) ) {
                $page['images'] = $rc['images'];
                ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'private', 'loadCacheThumbnail');
                foreach($page['images'] as $inum => $img) {
                    if( isset($img['image']['image_id']) && $img['image']['image_id'] > 0 ) {
                        $rc = ciniki_images_loadCacheThumbnail($ciniki, $args['tnid'], 
                            $img['image']['image_id'], 75);
                        if( $rc['stat'] != 'ok' ) {
                            return $rc;
                        }
                        $page['images'][$inum]['image']['image_data'] = 'data:image/jpg;base64,' . base64_encode($rc['image']);
                    }
                }
            }
        }

        //
        // Get the files
        //
        if( isset($args['files']) && $args['files'] == 'yes' ) {
            $strsql = "SELECT id, name, sequence, extension, permalink "
                . "FROM ciniki_web_page_files "
                . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
                . "AND ciniki_web_page_files.page_id = '" . ciniki_core_dbQuote($ciniki, $args['page_id']) . "' "
                . "ORDER BY sequence, name "
                . "";
            $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.web', array(
                array('container'=>'files', 'fname'=>'id', 'name'=>'file',
                    'fields'=>array('id', 'name', 'extension', 'permalink')),
            ));
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            if( isset($rc['files']) ) {
                $page['files'] = $rc['files'];
            } else {
                $page['files'] = array();
            } 
        }

        //
        // Get the child items
        //
        if( isset($args['children']) && $args['children'] == 'yes' ) {
            $strsql = "SELECT id, title "
                . "FROM ciniki_web_pages "
                . "WHERE parent_id = '" . ciniki_core_dbQuote($ciniki, $args['page_id']) . "' "
                . "AND tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
                . "ORDER BY sequence, title "
                . "";
            $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.web', array(
                array('container'=>'pages', 'fname'=>'id', 'name'=>'page',
                    'fields'=>array('id', 'title')),
                    ));
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            if( isset($rc['pages']) ) {
                $page['pages'] = $rc['pages'];
            } else {
                $page['pages'] = array();
            }
        }
    } else {
        $strsql = "SELECT MAX(sequence) AS sequence "
            . "FROM ciniki_web_pages "
            . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND parent_id = '" . ciniki_core_dbQuote($ciniki, $args['parent_id']) . "' "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.web', 'max');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['max']['sequence']) ) {
            $sequence = $rc['max']['sequence'] + 1;
        } else {
            $sequence = 1;
        }
        $page = array('id'=>'0', 
            'title'=>'',
            'permalink'=>'',
            'parent_id'=>$args['parent_id'],
            'category'=>'',
            'sequence'=>$sequence,
            'menu_flags'=>'1',
            'flags'=>'17',
            'primary_image_id'=>'0',
            'primary_image_caption'=>'',
            'primary_image_url'=>'',
            'synopsis'=>'',
            'content'=>'',
            'child_title'=>'',
            );
    }

    //
    // Get the complete list of pages for the parent list
    //
    $parentlist = array();
    if( isset($args['parentlist']) && $args['parentlist'] == 'yes' ) {
        $strsql = "SELECT id, parent_id, title, permalink "
            . "FROM ciniki_web_pages "
            . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "ORDER BY parent_id, sequence, title ";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
        $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.web', array(
            array('container'=>'pages', 'fname'=>'parent_id',
                'fields'=>array('parent_id', 'title', 'permalink')),
            array('container'=>'pages', 'fname'=>'id',
                'fields'=>array('id', 'parent_id', 'title', 'permalink')),
            ));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        //
        // Check if there are pages with no parent
        //
        if( isset($rc['pages'][0]['pages']) ) {
            function buildParentList($parentlist, $depth, $cpage, $pages, $permalink) {
                // Check for children
                if( $depth > 0 ) {
                    $indent = '';
                    for($i=1;$i<$depth;$i++) {
                        $indent .= ' - ';
                    }
                    $parentlist[] = array('page'=>array(
                        'id'=>$cpage['id'], 
                        'depth'=>$depth,
                        'permalink'=>$permalink . '/' . $cpage['permalink'],
                        'title'=>$indent . $cpage['title'],
                        ));
                }
                if( isset($pages[$cpage['id']]['pages']) ) {
                    $child_pages = $pages[$cpage['id']]['pages'];
                    foreach($child_pages as $child_id => $child_page) {
                        $parentlist = buildParentList($parentlist, $depth+1, $child_page, $pages, ($depth>0?$permalink . '/':'') . $cpage['permalink']);
                    }
                }
                return $parentlist;
            }
            $pages = $rc['pages'];
            $parentlist = buildParentList(array(), 0, array('id'=>'0', 'title'=>'', 'permalink'=>''), $pages, '');
            foreach($parentlist as $pl_page) {
                if( $pl_page['page']['id'] == $args['page_id'] ) {
                    $page['full_permalink'] = $pl_page['page']['permalink'] . '/' . $page['permalink'];
                }
            }
        }
    }

    //
    // Get any sponsors for this page, and that references for sponsors is enabled
    //
    if( isset($args['sponsors']) && $args['sponsors'] == 'yes' 
        && isset($ciniki['tenant']['modules']['ciniki.sponsors']) 
        && ($ciniki['tenant']['modules']['ciniki.sponsors']['flags']&0x02) == 0x02
        && $page['id'] > 0 
        ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'sponsors', 'hooks', 'sponsorList');
        $rc = ciniki_sponsors_hooks_sponsorList($ciniki, $args['tnid'], 
            array('object'=>'ciniki.web.page', 'object_id'=>$page['id']));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['sponsors']) ) {
            $page['sponsors'] = $rc['sponsors'];
        }
    }

    //
    // Get the list of module pages available to embed into pages. These are used for custom pages
    // to get their content from the module instead of their own content. Each module may only have one
    // page or multiple pages.
    //
    $modules_pages = array();
    foreach($ciniki['tenant']['modules'] as $module => $m) {
        list($pkg, $mod) = explode('.', $module);
        $rc = ciniki_core_loadMethod($ciniki, $pkg, $mod, 'hooks', 'webOptions');
        if( $rc['stat'] == 'ok' ) {
            $fn = $rc['function_call'];
            $rc = $fn($ciniki, $args['tnid'], array());
            if( $rc['stat'] == 'ok' && isset($rc['pages']) ) {
                $modules_pages = array_merge($modules_pages, $rc['pages']);
            }
        }
    }

    //
    // Check if a list of module available to the tenant should be returned
    //
/*  $modules = array();
    foreach($ciniki['tenant']['modules'] as $mod_name => $module) {
        //
        // Check if the module is a none core module and the processRequest.php file exists in the modules web directory
        //
        if( $module['module_status'] == 1 
            && file_exists($ciniki['config']['ciniki.core']['root_dir'] . '/' . $module['package'] . '-mods/' . $module['module'] . '/web/processRequest.php') ) {

            //
            // Get any module options
            //
            $rc = ciniki_core_loadMethod($ciniki, $module['package'], $module['module'], 'hooks', 'webOptions');
            if( $rc['stat'] == 'ok' ) {
                $fn = $rc['function_call'];
                $rc = $fn($ciniki, $args['tnid'], array());
                if( $rc['stat'] == 'ok' && isset($rc['options']) ) {
                    $module['options'] = $rc['options'];
                }
            }

            $modules[] = array('module'=>$module);
        }

    } */

    return array('stat'=>'ok', 'page'=>$page, 'parentlist'=>$parentlist, 'modules_pages'=>$modules_pages);
}
?>
