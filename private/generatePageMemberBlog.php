<?php
//
// Description
// -----------
// This function will generate the gallery page for the website
//
// The blog URL's can consist of
//      /blog/ - Display the latest blog entries
//      /blog/archive - Display the archive for the blog
//      /blog/category/categoryname - Display the entries for the category
//      /blog/tag/tagname - Display the entries for a tag
//      /blog/permalink - Display a blog entry
//      /blog/permalink/gallery/imagepermalink - Display a blog entry image gallery
//      /blog/permalink/download/filepermalink - Download a blog entry file
//
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_generatePageMemberBlog($ciniki, $settings) {

    //
    // Check if member blog/news is enabled, and if the member is signed in
    //
    if( !isset($ciniki['business']['modules']['ciniki.blog'])
        || ($ciniki['business']['modules']['ciniki.blog']['flags']&0x0100) == 0 ) {
        return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'1610', 'msg'=>'Page does not exist.'));
    }
    if( !isset($ciniki['session']['customer']['member_status']) 
        || $ciniki['session']['customer']['member_status'] != '10' ) {
        
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageAccount');
        return ciniki_web_generatePageAccount($ciniki, $settings);
    }
        
    //
    // The member is logged in, and can view the content
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageBlog');
    return ciniki_web_generatePageBlog($ciniki, $settings, 'memberblog');
}
?>
