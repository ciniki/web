<?php
//
// Description
// -----------
// This function will generate the links page for the website
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_generatePageLinks($ciniki, $settings) {

    //
    // Store the content created by the page
    // Make sure everything gets generated ok before returning the content
    //
    if( isset($settings['page-links-title']) && $settings['page-links-title'] != '' ) {
        $page_title = $settings['page-links-title'];
        $article_title = $settings['page-links-title'];
    } else {
        $page_title = 'Links';
        $article_title = 'Links';
    }
    $content = '';
    $page_content = '';

    //
    // FIXME: Check if anything has changed, and if not load from cache
    //

    $tag_type = 0;
    $tag_permalink = '';
    $base_url = $ciniki['request']['base_url'] . '/links';

    $show_intro = 'no';
    $show_tags = 'no';
    $show_list = 'yes';
    //
    // Get the list of links for a category
    //
    if( isset($ciniki['request']['uri_split'][0])
        && $ciniki['request']['uri_split'][0] == 'category' 
        && isset($ciniki['request']['uri_split'][1])
        && $ciniki['request']['uri_split'][1] != '' 
        ) {
        $tag_type = 10;
        $tag_permalink = $ciniki['request']['uri_split'][1];
        $article_title = "<a href='$base_url/categories'>$article_title</a>";
        $show_tags = 'no';
        $show_list = 'yes';
    }

    //
    // Get the list of links for a tag
    //
    elseif( isset($ciniki['request']['uri_split'][0])
        && $ciniki['request']['uri_split'][0] == 'tag' 
        && isset($ciniki['request']['uri_split'][1])
        && $ciniki['request']['uri_split'][1] != '' 
        ) {
        $tag_type = 40;
        $tag_permalink = $ciniki['request']['uri_split'][1];
        $article_title = "<a href='$base_url/tags'>$article_title</a>";
        $show_tags = 'no';
        $show_list = 'yes';
    } 

    //
    // Get the stats for the number links, categories and tags
    //
    $stats = array('links'=>0, 'categories'=>0, 'tags'=>0);
    if( $tag_type == 0 ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'links', 'web', 'count');
        $rc = ciniki_links_web_count($ciniki, $ciniki['request']['business_id']);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $stats = $rc;
    }

    //
    // Check if categories list was requested
    //
    if( $tag_type == 0 && isset($ciniki['request']['uri_split'][0])
        && $ciniki['request']['uri_split'][0] == 'categories' 
        ) {
        $tag_type = 10;
        $tag_permalink = '';
//      $article_title = 'Links';
//      $page_title = 'Links';
        if( $stats['categories'] > 1
            && (($stats['links'] > 20 && $stats['categories'] > 5)
                || ($stats['links'] > 30 && $stats['categories'] > 4)
                || ($stats['links'] > 40 && $stats['categories'] > 3)
                || ($stats['links'] > 50 && $stats['categories'] > 2)
                )
            ) {
            $show_tags = 'yes';
            $show_list = 'no';
        } else {
            $show_tags = 'no';
            $show_list = 'yes';
        }
    } 

    //
    // Check if tags list was requested
    //
    if( $tag_type == 0 && isset($ciniki['request']['uri_split'][0])
        && $ciniki['request']['uri_split'][0] == 'tags' 
        ) {
        $tag_type = 40;
        $tag_permalink = '';
//      $article_title = 'Links';
//      $page_title = 'Links';
        if( $stats['tags'] > 1
            && (($stats['links'] > 20 && $stats['tags'] > 5)
                || ($stats['links'] > 30 && $stats['tags'] > 4)
                || ($stats['links'] > 40 && $stats['tags'] > 3)
                || ($stats['links'] > 50 && $stats['tags'] > 2)
                )
            ) {
            $show_tags = 'yes';
            $show_list = 'no';
        } else {
            $show_tags = 'no';
            $show_list = 'yes';
        }
    }
    //
    // If nothing requested, decide what should be displayed
    //
    if( $tag_type == 0 ) {
        if( isset($ciniki['business']['modules']['ciniki.links']['flags'])
            && ($ciniki['business']['modules']['ciniki.links']['flags']&0x01) > 0 
            && $stats['categories'] > 1
            && (($stats['links'] > 20 && $stats['categories'] > 5)
                || ($stats['links'] > 30 && $stats['categories'] > 4)
                || ($stats['links'] > 40 && $stats['categories'] > 3)
                || ($stats['links'] > 50 && $stats['categories'] > 2)
                )
            ) {
            $tag_type = 10;
            $tag_permalink = '';
//          $article_title = 'Links';
//          $page_title = 'Links';
            $show_tags = 'yes';
            $show_list = 'no';
        }
        elseif( isset($ciniki['business']['modules']['ciniki.links']['flags'])
            && ($ciniki['business']['modules']['ciniki.links']['flags']&0x02) > 0
            && $stats['tags'] > 1
            && (($stats['links'] > 20 && $stats['tags'] > 5)
                || ($stats['links'] > 30 && $stats['tags'] > 4)
                || ($stats['links'] > 40 && $stats['tags'] > 3)
                || ($stats['links'] > 50 && $stats['tags'] > 2)
                )
            ) {
            $tag_type = 40;
            $tag_permalink = '';
//          $article_title = 'Links';
//          $page_title = 'Links';
            $show_tags = 'yes';
            $show_list = 'no';
        } 
    }

    if( $tag_type == 0 ) {
        $show_tags = 'no';
        $show_list = 'yes';
        if( isset($ciniki['business']['modules']['ciniki.links']['flags'])
            && ($ciniki['business']['modules']['ciniki.links']['flags']&0x03) == 2 ) {
            // Get the links organized by tag
            $tag_type = '40';
            $tag_permalink = '';
        } else {
            // Default to category list
            $tag_type = '10';
            $tag_permalink = '';
            $base_url .= '/category';
        }
    }

    //
    // Display the introduction content to the links page
    // FIXME: Not yet enabled in UI
    //
    if( $show_intro == 'yes' ) {
        //
        // Generate the content of the page
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash');
        $rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_web_content', 'business_id', $ciniki['request']['business_id'], 'ciniki.web', 'content', 'page-links');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }

        $page_content = '';
        if( isset($rc['content']) && isset($rc['content']['page-links-content']) ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
            $rc = ciniki_web_processContent($ciniki, $settings, $rc['content']['page-links-content']);  
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $page_content = $rc['content'];
        }
    }

    //
    // Display the tag cloud/list
    //
    if( $show_tags == 'yes' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'links', 'web', 'tagCloud');
        $rc = ciniki_links_web_tagCloud($ciniki, $settings, $ciniki['request']['business_id'], array(
            'tag_type'=>$tag_type,
            'permalink'=>$tag_permalink,
            ));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $page_content .= "<article class='page links'>\n"
            . "<header class='entry-title'><h1 class='entry-title'>$article_title</h1></header>\n"
            . "<div class='entry-content'>\n"
            . "";
        if( $tag_type == 40 ) {
            $base_url .= '/tag';
        } else {
            $base_url .= '/category';
        }
        if( ($tag_type == 40 
                && isset($settings['page-links-tags-format'])
                && $settings['page-links-tags-format'] == 'wordlist'
            ) || ($tag_type == 10 
                && isset($settings['page-links-categories-format'])
                && $settings['page-links-categories-format'] == 'wordlist'
                )
            ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processTagList');
            $rc = ciniki_web_processTagList($ciniki, $settings, $base_url, $rc['tags'], array());   
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $page_content .= $rc['content'];
        } else {
            // Default to wordcloud
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processTagCloud');
            $rc = ciniki_web_processTagCloud($ciniki, $settings, $base_url, $rc['tags']);   
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $page_content .= $rc['content'];
        }
        $page_content .= "</div>"
            . "</article>"
            . "";
    }

    if( $show_list == 'yes' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'links', 'web', 'list');
        $rc = ciniki_links_web_list($ciniki, $ciniki['request']['business_id'], array(
            'tag_type'=>$tag_type,
            'tag_permalink'=>$tag_permalink,
            ));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $sections = isset($rc['categories'])?$rc['categories']:array();
        if( $tag_permalink != '' ) {
            $skeys = array_keys($sections);
            $section_name = $skeys[0];
            $sections[$section_name]['name'] = '';
            if( $section_name != '' ) {
                $article_title .= ' - ' . $section_name;
                $page_title .= ' - ' . $section_name;
            } 
        }
        $page_content .= "<article class='page links'>\n"
            . "<header class='entry-title'><h1 class='entry-title'>$article_title</h1></header>\n"
            . "<div class='entry-content'>\n"
            . "";
        if( count($sections) > 0 ) {
            $page_content .= "<table class='clist'>\n"
                . "";
            $prev_sections = NULL;
            foreach($sections as $cnum => $c) {
                if( !isset($c['list']) ) {
                    continue;
                }
                if( $prev_sections != NULL ) {
                    $page_content .= "</td></tr>\n";
                }
                if( isset($c['name']) && $c['name'] != '' ) {
                    $page_content .= "<tr><th>"
                        . "<span class='clist-category'>" . $c['name'] . "</span></th>"
                        . "<td>";
                    // $page_content .= "<h2>" . $c['name'] . "</h2>";
                } else {
                    $page_content .= "<tr><th>"
                        . "<span class='clist-category'></span></th>"
                        . "<td>";
                }
                foreach($c['list'] as $fnum => $link) {
                    //$page_content .= "<p>";
                    if( isset($link['url']) ) {
                        $url = $link['url'];
                    } else {
                        $url = '';
                    }
                    if( $url != '' && !preg_match('/^\s*http/i', $url) ) {
                        $display_url = $url;
                        $url = "http://" . $url;
                    } else {
                        $display_url = preg_replace('/^\s*http:\/\//i', '', $url);
                        $display_url = preg_replace('/\/$/i', '', $display_url);
                    }
                    $page_content .= "<span class='clist-title'>";
                    if( $url != '' ) {
                        $page_content .= "<a target='_blank' href='" . $url . "' title='" . $link['name'] . "'>" . $link['name'] . "</a>";
                    } else {
                        $page_content .= $link['name'];
                    }
                    $page_content .= "</span>";
                    if( isset($link['description']) && $link['description'] != '' ) {
                        $page_content .= "<br/><span class='clist-description'>" . $link['description'] . "</span>";
                    }
                    if( $url != '' ) {
                        $page_content .= "<br/><span class='oneline'><a class='clist-url' target='_blank' href='" . $url . "' title='" . $link['name'] . "'>" . $display_url . "</a></span>";
                    }
                    $page_content .= "<br/><br/>";
                    // $page_content .= "</p>";
                }
            } 
            $page_content .= "</td></tr>\n</table>\n";
        } else {
            $page_content .= "<p>I'm sorry, there are no links.</p>";
        }
        $page_content .= "</div>"
            . "</article>"
            . "";
    }

    
    $submenu = array();
    if( isset($ciniki['business']['modules']['ciniki.links']['flags']) && ($ciniki['business']['modules']['ciniki.links']['flags']&0x03) == 0x03 ) {
        // Display the category/tags buttons
        $submenu['categories'] = array('name'=>'Categories',
            'url'=>$ciniki['request']['base_url'] . '/links/categories');
        $submenu['tags'] = array('name'=>'Tags',
            'url'=>$ciniki['request']['base_url'] . '/links/tags');
    } 

    //
    // Add the header
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageHeader');
    $rc = ciniki_web_generatePageHeader($ciniki, $settings, $page_title, $submenu);
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }
    $content .= $rc['content'];

    $content .= "<div id='content'>\n"
        . $page_content
        . "</div>"
        . "";

    //
    // Add the footer
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageFooter');
    $rc = ciniki_web_generatePageFooter($ciniki, $settings);
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }
    $content .= $rc['content'];

    return array('stat'=>'ok', 'content'=>$content);
}
?>
