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
function ciniki_web_privateThemeGet($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'theme_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Theme'),
        'settings'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Settings'),
        'content'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Content'),
        'images'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Images'),
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
    $rc = ciniki_web_checkAccess($ciniki, $args['tnid'], 'ciniki.web.privateThemeGet'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
    $date_format = ciniki_users_dateFormat($ciniki);

    //
    // Load event maps
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'maps');
    $rc = ciniki_web_maps($ciniki);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $maps = $rc['maps'];

    if( $args['theme_id'] == 0 ) {
        $content = array('id'=>'0', 
            'name'=>'',
            'status'=>'10',
            );
    } else {
        //
        // Get the theme
        //
        $strsql = "SELECT ciniki_web_themes.id, "
            . "ciniki_web_themes.name, "
            . "ciniki_web_themes.status, "
            . "ciniki_web_themes.status AS status_text "
            . "FROM ciniki_web_themes "
            . "WHERE ciniki_web_themes.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND ciniki_web_themes.id = '" . ciniki_core_dbQuote($ciniki, $args['theme_id']) . "' "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
        $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.web', array(
            array('container'=>'themes', 'fname'=>'id', 'name'=>'theme',
                'fields'=>array('id', 'name', 'status', 'status_text'),
                'maps'=>array('status_text'=>$maps['theme']['status'])),
            ));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $theme = $rc['themes'][0]['theme'];

        //
        // Get the theme settings
        //
        if( isset($args['settings']) && $args['settings'] == 'yes' ) {
            $strsql = "SELECT id, detail_key, detail_value "
                . "FROM ciniki_web_theme_settings "
                . "WHERE theme_id = '" . ciniki_core_dbQuote($ciniki, $args['theme_id']) . "' "
                . "AND tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
                . "";
            $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.web', array(
                array('container'=>'settings', 'fname'=>'id', 'name'=>'setting',
                    'fields'=>array('id', 'detail_key', 'detail_value')),
                ));
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            if( isset($rc['settings']) ) {
                foreach($rc['settings'] as $setting) {
                    $theme[$setting['setting']['detail_key']] = $setting['setting']['detail_value'];
                }
            }
        }

        //
        // Get the theme images
        //
        if( isset($args['images']) && $args['images'] == 'yes' ) {
            $strsql = "SELECT id, name, image_id "
                . "FROM ciniki_web_theme_images "
                . "WHERE theme_id = '" . ciniki_core_dbQuote($ciniki, $args['theme_id']) . "' "
                . "AND tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
                . "";
            $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.web', array(
                array('container'=>'images', 'fname'=>'id', 'name'=>'image',
                    'fields'=>array('id', 'name', 'image_id')),
                ));
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            if( isset($rc['images']) ) {
                $theme['images'] = $rc['images'];
                ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'private', 'loadCacheThumbnail');
                foreach($theme['images'] as $inum => $img) {
                    if( isset($img['image']['image_id']) && $img['image']['image_id'] > 0 ) {
                        $rc = ciniki_images_loadCacheThumbnail($ciniki, $args['tnid'], 
                            $img['image']['image_id'], 75);
                        if( $rc['stat'] != 'ok' ) {
                            return $rc;
                        }
                        $theme['images'][$inum]['image']['image_data'] = 'data:image/jpg;base64,' . base64_encode($rc['image']);
                    }
                }
            }
        }

        //
        // Get the theme css and js content
        //
        if( isset($args['content']) && $args['content'] == 'yes' ) {
            $strsql = "SELECT id, content_type, name, status, status AS status_text, sequence, last_updated "
                . "FROM ciniki_web_theme_content "
                . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
                . "AND ciniki_web_theme_content.theme_id = '" . ciniki_core_dbQuote($ciniki, $args['theme_id']) . "' "
                . "ORDER BY ciniki_web_theme_content.content_type, ciniki_web_theme_content.sequence "
                . "";
            $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.web', array(
                array('container'=>'types', 'fname'=>'content_type', 'name'=>'type',
                    'fields'=>array('content_type')),
                array('container'=>'content', 'fname'=>'id', 'name'=>'content',
                    'fields'=>array('id', 'content_type', 'name', 'status', 'status_text', 'sequence', 'last_updated'),
                    'maps'=>array('status_text'=>$maps['theme_content']['status']),
                    'utctotz'=>array('last_updated'=>array('timezone'=>'UTC', 'format'=>'M d, y H:i:s')),
                    ),
            ));
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            if( isset($rc['types']) ) {
                foreach($rc['types'] as $type) {
                    $theme[$type['type']['content_type']] = $type['type']['content'];
                }
            } 
        }
    }


    return array('stat'=>'ok', 'theme'=>$theme);
}
?>
