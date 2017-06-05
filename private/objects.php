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
function ciniki_web_objects($ciniki) {
    
    $objects = array();
    $objects['content'] = array(
        'name'=>'Web Content',
        'sync'=>'yes',
        'table'=>'ciniki_web_content',
        'type'=>'settings',
        'history_table'=>'ciniki_web_history',
        );
    $objects['setting'] = array(
        'type'=>'settings',
        'name'=>'Web Setting',
        'sync'=>'yes',
        'table'=>'ciniki_web_settings',
        'refs'=>array(
            'page-home-image'=>array('ref'=>'ciniki.images.image'),
            'page-about-image'=>array('ref'=>'ciniki.images.image'),
            'page-exhibitions-exhibition'=>array('ref'=>'ciniki.exhibitions.exhibition'),
            'site-header-image'=>array('ref'=>'ciniki.images.image'),
            ),
        'history_table'=>'ciniki_web_history',
        );
    $objects['faq'] = array(
        'name'=>'Web FAQ',
        'sync'=>'yes',
        'table'=>'ciniki_web_faqs',
        'fields'=>array(
            'flags'=>array(),
            'category'=>array(),
            'question'=>array(),
            'answer'=>array(),
            ),
        'history_table'=>'ciniki_web_history',
        );
    $objects['shorturl'] = array(
        'name'=>'Shortened URL',
        'sync'=>'yes',
        'table'=>'ciniki_web_shorturls',
        'fields'=>array(
            'surl'=>array(),
            'furl'=>array(),
            ),
        'history_table'=>'ciniki_web_history',
        );
    $objects['slider'] = array(
        'name'=>'Web Slider',
        'o_name'=>'slider',
        'o_container'=>'sliders',
        'sync'=>'yes',
        'table'=>'ciniki_web_sliders',
        'fields'=>array(
            'name'=>array(),
            'size'=>array(),
            'effect'=>array(),
            'speed'=>array(),
            'resize'=>array(),
            'modules'=>array(),
            ),
        'history_table'=>'ciniki_web_history',
        );
    $objects['slider_image'] = array(
        'name'=>'Web Slider Image',
        'o_name'=>'image',
        'o_container'=>'images',
        'listsort'=>'sequence',
        'sync'=>'yes',
        'table'=>'ciniki_web_slider_images',
        'fields'=>array(
            'slider_id'=>array('ref'=>'ciniki.web.slider'),
            'image_id'=>array('ref'=>'ciniki.images.image'),
            'sequence'=>array(),
            'object'=>array(),
            'object_id'=>array(),
            'caption'=>array(),
            'url'=>array(),
            'image_offset'=>array(),
            'overlay'=>array(),
            'overlay_position'=>array(),
            'start_date'=>array('type'=>'utcdatetime'),
            'end_date'=>array('type'=>'utcdatetime'),
            ),
        'history_table'=>'ciniki_web_history',
        );
    $objects['collection'] = array(
        'name'=>'Web Collection',
        'o_name'=>'collection',
        'o_container'=>'collections',
        'listsort'=>'sequence',
        'sync'=>'yes',
        'table'=>'ciniki_web_collections',
        'fields'=>array(
            'name'=>array(),
            'permalink'=>array(),
            'status'=>array(),
            'sequence'=>array(),
            'image_id'=>array('ref'=>'ciniki.images.image'),
            'image_caption'=>array(),
            'synopsis'=>array(),
            'description'=>array(),
            ),
        'history_table'=>'ciniki_web_history',
        );
    $objects['collection_obj'] = array(
        'name'=>'Web Collection Object',
        'o_name'=>'obj',
        'o_container'=>'objs',
        'sync'=>'yes',
        'table'=>'ciniki_web_collection_objs',
        'fields'=>array(
            'collection_id'=>array(),
            'object'=>array(),
            'sequence'=>array(),
            'num_items'=>array(),
            'title'=>array(),
            'more'=>array(),
            ),
        'history_table'=>'ciniki_web_history',
        );
    $objects['collection_objref'] = array(
        'name'=>'Web Collection Reference',
        'o_name'=>'ref',
        'o_container'=>'refs',
        'sync'=>'yes',
        'table'=>'ciniki_web_collection_objrefs',
        'fields'=>array(
            'collection_id'=>array(),
            'object'=>array(),
            'object_id'=>array(),
            ),
        'history_table'=>'ciniki_web_history',
        );
    $objects['page'] = array(
        'name'=>'Page',
        'sync'=>'yes',
        'table'=>'ciniki_web_pages',
        'fields'=>array(
            'parent_id'=>array('ref'=>'ciniki.web.page'),
            'title'=>array(),
            'permalink'=>array(),
            'article_title'=>array('default'=>''),
            'category'=>array('default'=>''),
            'sequence'=>array('default'=>'1'),
            'page_type'=>array('default'=>'10'),
            'page_redirect_url'=>array('default'=>''),
            'page_module'=>array('default'=>''),
            'menu_flags'=>array('default'=>'1'),
            'flags'=>array('default'=>'1'),
            'primary_image_id'=>array('default'=>'0'),
            'primary_image_caption'=>array('default'=>''),
            'primary_image_url'=>array('default'=>''),
            'synopsis'=>array('default'=>''),
            'content'=>array('default'=>''),
            'child_title'=>array('default'=>''),
            ),
        'history_table'=>'ciniki_web_history',
        );
    $objects['page_image'] = array(
        'name'=>'Content Image',
        'sync'=>'yes',
        'table'=>'ciniki_web_page_images',
        'fields'=>array(
            'page_id'=>array('ref'=>'ciniki.web.page'),
            'name'=>array(),
            'permalink'=>array(),
            'webflags'=>array(),
            'image_id'=>array('ref'=>'ciniki.images.image'),
            'description'=>array(),
            ),
        'history_table'=>'ciniki_web_history',
        );
    $objects['page_file'] = array(
        'name'=>'Content File',
        'sync'=>'yes',
        'table'=>'ciniki_web_page_files',
        'fields'=>array(
            'page_id'=>array('ref'=>'ciniki.web.page'),
            'extension'=>array(),
            'name'=>array(),
            'permalink'=>array(),
            'webflags'=>array(),
            'description'=>array(),
            'org_filename'=>array(),
            'binary_content'=>array('history'=>'no'),
            ),
        'history_table'=>'ciniki_web_history',
        );
    $objects['theme'] = array(
        'name'=>'Web Theme',
        'o_name'=>'theme',
        'o_container'=>'themes',
        'sync'=>'yes',
        'table'=>'ciniki_web_themes',
        'fields'=>array(
            'name'=>array(),
            'permalink'=>array(),
            'status'=>array('default'=>'10'),
            ),
        'history_table'=>'ciniki_web_history',
        );
    $objects['theme_setting'] = array(
        'name'=>'Web Theme Setting',
        'o_name'=>'setting',
        'o_container'=>'settings',
        'sync'=>'yes',
        'table'=>'ciniki_web_theme_settings',
        'fields'=>array(
            'theme_id'=>array('ref'=>'ciniki.web.theme'),
            'detail_key'=>array(),
            'detail_value'=>array(),
            ),
        'history_table'=>'ciniki_web_history',
        );
    $objects['theme_content'] = array(
        'name'=>'Web Theme Content',
        'o_name'=>'content',
        'o_container'=>'content',
        'sync'=>'yes',
        'table'=>'ciniki_web_theme_content',
        'fields'=>array(
            'theme_id'=>array('ref'=>'ciniki.web.theme'),
            'name'=>array('default'=>''),
            'status'=>array('default'=>'10'),
            'sequence'=>array('default'=>'1'),
            'content_type'=>array('default'=>''),
            'media'=>array('default'=>''),
            'content'=>array('default'=>''),
            ),
        'history_table'=>'ciniki_web_history',
        );
    $objects['theme_image'] = array(
        'name'=>'Web Theme Image',
        'o_name'=>'image',
        'o_container'=>'images',
        'sync'=>'yes',
        'table'=>'ciniki_web_theme_images',
        'fields'=>array(
            'theme_id'=>array('ref'=>'ciniki.web.theme'),
            'image_id'=>array('ref'=>'ciniki.images.image'),
            'name'=>array(),
            ),
        'history_table'=>'ciniki_web_history',
        );
    $objects['redirect'] = array(
        'name'=>'Web Redirect',
        'o_name'=>'redirect',
        'o_container'=>'redirects',
        'sync'=>'yes',
        'table'=>'ciniki_web_redirects',
        'fields'=>array(
            'oldurl'=>array('name'=>'Old URL'),
            'newurl'=>array('name'=>'New URL'),
            ),
        'history_table'=>'ciniki_web_history',
        );
    $objects['index'] = array(
        'name'=>'Web Index',
        'o_name'=>'index',
        'o_container'=>'index',
        'sync'=>'yes',
        'table'=>'ciniki_web_index',
        'fields'=>array(
            'label'=>array('name'=>'Label'),
            'title'=>array('name'=>'Title'),
            'subtitle'=>array('name'=>'Subtitle', 'default'=>''),
            'meta'=>array('name'=>'Meta', 'default'=>''),
            'primary_image_id'=>array('name'=>'Image', 'default'=>'0'),
            'synopsis'=>array('name'=>'Synopsis', 'default'=>''),
            'object'=>array('name'=>'Object'),
            'object_id'=>array('name'=>'Object ID'),
            'primary_words'=>array('name'=>'Primary Words'),
            'secondary_words'=>array('name'=>'Secondary Words'),
            'tertiary_words'=>array('name'=>'Tertiary Words'),
            'weight'=>array('name'=>'Weight'),
            'url'=>array('name'=>'URL'),
            ),
        'history_table'=>'ciniki_web_history',
        );
    $objects['hplink'] = array(
        'name'=>'Home Page Link',
        'o_name'=>'hplink',
        'o_container'=>'hplinks',
        'sync'=>'yes',
        'table'=>'ciniki_web_hplinks',
        'fields'=>array(
            'parent_id'=>array('name'=>'Parent', 'default'=>'0'),
            'title'=>array('name'=>'Title'),
            'url'=>array('name'=>'URL'),
            'sequence'=>array('name'=>'Sequence', 'default'=>'1'),
            'image_id'=>array('name'=>'Image', 'default'=>'0', 'ref'=>'ciniki.images.image'),
            ),
        'history_table'=>'ciniki_web_history',
        );
    
    return array('stat'=>'ok', 'objects'=>$objects);
}
?>
