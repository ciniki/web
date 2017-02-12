<?php
//
// Description
// -----------
// This page will generate the page for search results
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_generatePageSearch(&$ciniki, $settings) {

    $search_str = '';
    if( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] != '' ) {
        $search_str = urldecode($ciniki['request']['uri_split'][0]);
    }

    $page = array(
        'title'=>'Search',
        'page-container-class'=>'page-search',
        );
    $breadcrumbs = array();
    $article_title = 'Search';

    if( !isset($ciniki['request']['page-container-class']) ) {
        $ciniki['request']['page-container-class'] = 'page-search';
    } else {
        $ciniki['request']['page-container-class'] .= ' page-search';
    }
    $ciniki['request']['ciniki_api'] = 'yes';
    if( !isset($ciniki['request']['inline_javascript']) ) {
        $ciniki['request']['inline_javascript'] = '';
    }
    $limit = 21;
    $ciniki['request']['inline_javascript'] .= "<script type='text/javascript'>\n"
        . "var prev_live_search_str = '';\n"
        . "function update_live_search() {\n"
            . "var str = document.getElementById('live-search-str').value;\n"
            . "if( prev_live_search_str != str ) {\n"
                . "if( str != prev_live_search_str ) {\n"
                    . "window.history.replaceState(null,null,'" . $ciniki['request']['base_url'] . "/search/'+str);"
                    . "C.getBg('site/search/'+encodeURIComponent(str),'',update_search_results);\n"
                . "}\n"
                . "prev_live_search_str = str;\n"
            . "}\n"
            . "return false;"
        . "};"
        . "function update_search_results(rsp) {"
            . "var d = document.getElementById('live-search-results');"
            . "C.clr(d);"
            . "if(rsp.results!=null&&rsp.results.length>0) {"
                . "var ct=0;"
                . "for(i in rsp.results) {"
                    . "var r=rsp.results[i];"
                    . "var c=\"<div class='image-list-entry'>\";"
//                    . "c+=\"<div class='image-list-label-wrap'><div class='image-list-label'>\"+r.label+\"</div></div>\";"
                    . "if(r.primary_image_url!=''){"
                        . "c+=\"<div class='image-list-image'><div class='image-list-wrap image-list-thumbnail'><a href='\"+r.url+\"'><img alt='\"+r.title+\"' src='\"+r.primary_image_url+\"'/></a></div></div>\";"
                    . "}else{"
                        . "c+=\"<div class='image-list-image'><div class='image-list-wrap image-list-thumbnail'><a href='\"+r.url+\"'><img alt='\"+r.title+\"' src='/ciniki-web-layouts/default/img/noimage_240.png'/></a></div></div>\";"
                    . "}"
                    . "c+=\"<div class='image-list-details'><div class='image-list-title'><h2><a href='\"+r.url+\"' title='\"+r.title+\"'>\"+r.title+\"</a></h2>\";"
                    . "if(r.subtitle!=''){"
                        . "c+=\"<h3><a href='\"+r.url+\"' title='\"+r.title+\"'>\"+r.subtitle+\"</a></h3>\";"
                    . "}"
                    . "c+='</div>';"
                    . "if(r.meta!=''){"
                        . "c+=\"<div class='image-list-meta'>\"+r.meta+\"</div>\";"
                    . "}"
                    . "c+=\"<div class='image-list-content'>\"+r.synopsis+\"</div>\";"
                    . "c+=\"<div class='image-list-more'><a href='\"+r.url+\"'>... more</a></div>\";"
                    . "c+='</div>';"
                    . "var e=C.aE('div',null,'image-list-entry-wrap '+r.class,c);"
                    . "d.appendChild(e);"
                . "}"
            . "}else if(prev_live_search_str==''){"
                . "d.innerHTML='<div class=\"live-search-empty\"></div>';"
            . "}else{"
                . "d.innerHTML='<div class=\"live-search-empty\">I\'m sorry we couldn\'t find what you were looking for.</div>';"
            . "}"
        . "};";
    if( $search_str != '' ) {
        $ciniki['request']['inline_javascript'] .= "window.onload = update_live_search;";
    }
    $ciniki['request']['inline_javascript'] .= "</script>\n";

    //
    // Add the header
    //
    $submenu = array();
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageHeader');
    $rc = ciniki_web_generatePageHeader($ciniki, $settings, 'Search', $submenu);
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }
    $page_content = $rc['content'];
    
    //
    // Check if article title and breadcrumbs should be displayed above content
    //
    if( (isset($settings['theme']['header-article-title']) && $settings['theme']['header-article-title'] == 'yes')
        || (isset($settings['theme']['header-breadcrumbs']) && $settings['theme']['header-breadcrumbs'] == 'yes')
        ) {
        $page_content .= "<div class='page-header'>";
        if( isset($settings['theme']['header-article-title']) && $settings['theme']['header-article-title'] == 'yes' ) {
            $page_content .= "<h1 class='page-header-title'>" . $page['title'] . "</h1>";
        }
        if( isset($settings['theme']['header-breadcrumbs']) && $settings['theme']['header-breadcrumbs'] == 'yes' && isset($breadcrumbs) ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processBreadcrumbs');
            $rc = ciniki_web_processBreadcrumbs($ciniki, $settings, $ciniki['request']['business_id'], $breadcrumbs);
            if( $rc['stat'] == 'ok' ) {
                $page_content .= $rc['content'];
            }
        }
        $page_content .= "</div>";
    }

    $page_content .= "<div id='content'>";
    $page_content .= "<article class='page'>";

    $page_content .= "<header class='entry-title'><h1 id='entry-title' class='entry-title'>$article_title</h1></header>";

    $page_content .= "<div class='entry-content'>";
    $page_content .= "<div class='live-search'>";
    $page_content .= "<label for='search_str'></label>"
        . "<input id='live-search-str' class='input' type='text' autofocus placeholder='What are you looking for?' name='search_str' value='$search_str' "
            . "onkeyup='return update_live_search();' onsearch='return update_live_search();' onsubmit='return false;' autocomplete='off' />"
            . "";
    $page_content .= "</div>";
    $page_content .= "<div id='live-search-results' class='image-list'>\n";
    $page_content .= "</div>";
    $page_content .= "</div>";

    $page_content .= "</article>";
    $page_content .= "</div>";

    //
    // Add the footer
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageFooter');
    $rc = ciniki_web_generatePageFooter($ciniki, $settings);
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }
    $page_content .= $rc['content'];

    return array('stat'=>'ok', 'content'=>$page_content);
}
?>
