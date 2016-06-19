<?php
//
// Description
// -----------
// This function will generate the gallery page for the website
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_web_generatePageProducts(&$ciniki, $settings) {

    //
    // Check if a file was specified to be downloaded
    //
    $download_err = '';
    if( isset($ciniki['business']['modules']['ciniki.products'])
        && isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] == 'product'
        && isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] != ''
        && isset($ciniki['request']['uri_split'][2]) && $ciniki['request']['uri_split'][2] == 'download'
        && isset($ciniki['request']['uri_split'][3]) && $ciniki['request']['uri_split'][3] != '' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'products', 'web', 'fileDownload');
        $rc = ciniki_products_web_fileDownload($ciniki, $ciniki['request']['business_id'], 
            $ciniki['request']['uri_split'][1], $ciniki['request']['uri_split'][3]);
        if( $rc['stat'] == 'ok' ) {
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            header("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: no-cache');
            $file = $rc['file'];
            if( $file['extension'] == 'pdf' ) {
                header('Content-Type: application/pdf');
            }
//          header('Content-Disposition: attachment;filename="' . $file['filename'] . '"');
            header('Content-Length: ' . strlen($file['binary_content']));
            header('Cache-Control: max-age=0');

            print $file['binary_content'];
            exit;
        }
        
        //
        // If there was an error locating the files, display generic error
        //
        return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'1504', 'msg'=>'The file you requested does not exist.'));
    }

    //
    // Store the content created by the page
    //
    $page_content = '';

    //
    // FIXME: Check if anything has changed, and if not load from cache
    //
        
    $page_title = "Products";
    $tags = array();
    $ciniki['response']['head']['og']['url'] = $ciniki['request']['domain_base_url'] . '/products';

    //
    // Setup the last_change date of the products module, or if images or web has been updated sooner
    //
    $cache_file = '';
    $last_change = $ciniki['business']['modules']['ciniki.products']['last_change'];
    if( isset($ciniki['business']['modules']['ciniki.images']['last_change']) 
        && $ciniki['business']['modules']['ciniki.images']['last_change'] > $last_change ) {
        $last_change = $ciniki['business']['modules']['ciniki.images']['last_change'];
    }
    if( isset($ciniki['business']['modules']['ciniki.web']['last_change']) 
        && $ciniki['business']['modules']['ciniki.web']['last_change'] > $last_change ) {
        $last_change = $ciniki['business']['modules']['ciniki.web']['last_change'];
    }

    //
    // Check for cached content
    //
    $cache_update = 'yes';
    if( isset($ciniki['business']['cache_dir']) && $ciniki['business']['cache_dir'] != '' 
        && (!isset($ciniki['config']['ciniki.web']['cache']) 
            || $ciniki['config']['ciniki.web']['cache'] != 'off') 
        ) {
        $pull_from_cache = 'yes';
        $cache_file = $ciniki['business']['cache_dir'] . '/ciniki.web/products/';
        $depth = 2;
        if( isset($ciniki['request']['uri_split'][0]) && $ciniki['request']['uri_split'][0] == 'product' ) {
            $depth = 1;
        }
        foreach($ciniki['request']['uri_split'] as $uri_index => $uri_piece) {
            // Ignore cache for gallery, it's missing javascript
            if( $uri_piece == 'gallery' ) {
                $pull_from_cache = 'no';
            }
            if( $uri_index < $depth ) {
                $cache_file .= $uri_piece . '/';
            } elseif( $uri_index == $depth ) {
                $cache_file .= $uri_piece;
            } else {
                $cache_file .= '_' . $uri_piece;
            }
        }
        if( substr($cache_file, -1) == '/' ) {
            $cache_file .= '_index';
        }
        // Check if no changes have been made since last cache file write
        if( $pull_from_cache == 'yes' && file_exists($cache_file) && filemtime($cache_file) > $last_change ) {
            $page_content = file_get_contents($cache_file);
            $cache_update = 'no';
//          error_log("CACHE: $last_change - " . $cache_file);
        }
    }

    //
    // Generate the product page
    //
    if( $page_content == '' 
        && isset($ciniki['request']['uri_split'][0]) 
        && $ciniki['request']['uri_split'][0] == 'product'
        && isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] != '' 
        ) {
        $product_permalink = $ciniki['request']['uri_split'][1];
/*
        //
        // Check for cached content
        //
        if( isset($ciniki['business']['cache_dir']) && $ciniki['business']['cache_dir'] != '' ) {
            if( isset($ciniki['request']['uri_split'][2]) && $ciniki['request']['uri_split'][2] == 'gallery'
                && isset($ciniki['request']['uri_split'][3]) && $ciniki['request']['uri_split'][3] != '' 
                ) {
                $cache_file = $ciniki['business']['cache_dir'] . '/ciniki.web/products_p_' . $product_permalink . '_g_' . $ciniki['request']['uri_split'][3];
            } else {
                $cache_file = $ciniki['business']['cache_dir'] . '/ciniki.web/products_p_' . $product_permalink;
            }
            // Check if no changes have been made since last cache file write
            if( file_exists($cache_file) && filemtime($cache_file) > $last_change ) {
                $content = file_get_contents($cache_file);
                if( $content != '' ) {
//                  error_log("WEB-CACHE: using cached $cache_file");
                    return array('stat'=>'ok', 'content'=>$content);
                }
            }
        }
*/
        ciniki_core_loadMethod($ciniki, 'ciniki', 'products', 'web', 'productDetails');
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processProduct');

        //
        // Get the product information
        //
        $rc = ciniki_products_web_productDetails($ciniki, $settings, $ciniki['request']['business_id'], 
            array('product_permalink'=>$product_permalink));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $product = $rc['product'];
        $page_title = $product['name'];

//      $ciniki['response']['head']['links'][] = array('rel'=>'canonical', 
//          'href'=>$ciniki['request']['domain_base_url'] . '/products/product/' . $product_permalink
//          );
        $ciniki['response']['head']['og']['url'] .= '/product/' . $product_permalink;
        $ciniki['response']['head']['og']['description'] = strip_tags($product['short_description']);

        //
        // Check if image requested
        //
        if( isset($ciniki['request']['uri_split'][2]) && $ciniki['request']['uri_split'][2] == 'gallery'
            && isset($ciniki['request']['uri_split'][3]) && $ciniki['request']['uri_split'][3] != '' 
            ) {
            $image_permalink = $ciniki['request']['uri_split'][3];
//          $ciniki['response']['head']['links']['canonical']['href'] .= '/gallery/' . $image_permalink;
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processGalleryImage');
            $rc = ciniki_web_processGalleryImage($ciniki, $settings, $ciniki['request']['business_id'],
                array('item'=>$product,
                    'gallery_url'=>$ciniki['request']['base_url'] . '/products/product/' . $product_permalink . '/gallery',
                    'article_title'=>"<a href='" . $ciniki['request']['base_url'] 
                        . "/products/product/" . $product_permalink . "'>" . $product['name'] . "</a>",
                    'image_permalink'=>$image_permalink,
                ));
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $page_content .= $rc['content'];
        } 
    
        //
        // Display the product
        //
        else {
            $article_title = $product['name'];
            $page_content .= "<article class='page'>\n"
                . "<header class='entry-title'><h1 class='entry-title'>" . $article_title . "</h1></header>\n"
                . "<div class='entry-content'>";

            $base_url = $ciniki['request']['base_url'] . "/products/product/" . $product_permalink;
            $rc = ciniki_web_processProduct($ciniki, $settings, $ciniki['request']['business_id'], 
                $base_url, $product, array('title'=>$page_title, 'tags'=>$product['social-tags']));
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $page_content .= $rc['content'];
            $page_content .= "</div></article>";
        }
    }
    
    //
    // Generate the product in a category
    //
    elseif( $page_content == '' 
        && isset($ciniki['request']['uri_split'][0]) 
        && $ciniki['request']['uri_split'][0] == 'category'
        && isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] != '' 
        && isset($ciniki['request']['uri_split'][2]) && $ciniki['request']['uri_split'][2] == 'product'
        && isset($ciniki['request']['uri_split'][3]) && $ciniki['request']['uri_split'][3] != '' 
        ) {
        $category_permalink = $ciniki['request']['uri_split'][1];
        $product_permalink = $ciniki['request']['uri_split'][3];
/*
        //
        // Check for cached content
        //
        if( isset($ciniki['business']['cache_dir']) && $ciniki['business']['cache_dir'] != '' ) {
            if( isset($ciniki['request']['uri_split'][4]) && $ciniki['request']['uri_split'][4] == 'gallery'
                && isset($ciniki['request']['uri_split'][5]) && $ciniki['request']['uri_split'][5] != '' 
                ) {
                $cache_file = $ciniki['business']['cache_dir'] . '/ciniki.web/products_c_' . $category_permalink . '_p_' . $product_permalink . '_g_' . $ciniki['request']['uri_split'][5];
            } else {
                $cache_file = $ciniki['business']['cache_dir'] . '/ciniki.web/products_c_' . $category_permalink . '_p_' . $product_permalink;
            }
            // Check if no changes have been made since last cache file write
            if( file_exists($cache_file) && filemtime($cache_file) > $last_change ) {
                $content = file_get_contents($cache_file);
                if( $content != '' ) {
//                  error_log("WEB-CACHE: using cached $cache_file");
                    return array('stat'=>'ok', 'content'=>$content);
                }
            }
        }
*/
        ciniki_core_loadMethod($ciniki, 'ciniki', 'products', 'web', 'productDetails');
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processProduct');
        
        //
        // Get the product information
        //
        $rc = ciniki_products_web_productDetails($ciniki, $settings, $ciniki['request']['business_id'], 
            array('product_permalink'=>$product_permalink, 
                'category_permalink'=>$category_permalink,
                ));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $product = $rc['product'];
        $page_title = $product['name'];
        $article_title = $product['name'];
        
        $ciniki['response']['head']['links'][] = array('rel'=>'canonical', 
            'href'=>$ciniki['request']['domain_base_url'] . '/products/product/' . $product_permalink
            );
        $ciniki['response']['head']['og']['url'] .= '/product/' . $product_permalink;
        $ciniki['response']['head']['og']['description'] = strip_tags($product['short_description']);

        if( isset($product['category_title']) && $product['category_title'] != '' ) {
            $article_title = "<a href='" . $ciniki['request']['base_url'] . "/products/category/" . $category_permalink
                    . "'>" . $product['category_title'] . "</a>";
        } else {
            $article_title = $page_title;
        }

        //
        // Check if image requested
        //
        if( isset($ciniki['request']['uri_split'][4]) && $ciniki['request']['uri_split'][4] == 'gallery'
            && isset($ciniki['request']['uri_split'][5]) && $ciniki['request']['uri_split'][5] != '' 
            ) {
            $image_permalink = $ciniki['request']['uri_split'][5];
            $ciniki['response']['head']['links']['canonical']['href'] .= '/gallery/' . $image_permalink;
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processGalleryImage');
            $rc = ciniki_web_processGalleryImage($ciniki, $settings, $ciniki['request']['business_id'],
                array('item'=>$product,
                    'gallery_url'=>$ciniki['request']['base_url'] . "/products/category/$category_permalink/product/$product_permalink/gallery",
                    'article_title'=>$article_title .= " - <a href='" . $ciniki['request']['base_url'] 
                        . "/products/category/$category_permalink/product/$product_permalink'>" . $product['name'] . "</a>",
                    'image_permalink'=>$image_permalink,
                ));
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $page_content .= $rc['content'];
        } 
    
        //
        // Display the product
        //
        else {
            $article_title .= ' - ' . $product['name'];
            $page_content .= "<article class='page'>\n"
                . "<header class='entry-title'><h1 class='entry-title'>" . $article_title . "</h1></header>\n"
                . "<div class='entry-content'>";

            //
            // Display the product
            //
            $base_url = $ciniki['request']['base_url'] . "/products/category/$category_permalink"
                . "/product/" . $product_permalink;
            $rc = ciniki_web_processProduct($ciniki, $settings, $ciniki['request']['business_id'], 
                $base_url, $product, array('title'=>$page_title, 'tags'=>$product['social-tags']));
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $page_content .= $rc['content'];

            $page_content .= "</div></article>";
        }
    }

    //
    // Generate the product in a sub category
    //
    elseif( $page_content == '' 
        && isset($ciniki['request']['uri_split'][0]) 
        && $ciniki['request']['uri_split'][0] == 'category'
        && isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] != '' 
        && isset($ciniki['request']['uri_split'][2]) && $ciniki['request']['uri_split'][2] != '' 
        && isset($ciniki['request']['uri_split'][3]) && $ciniki['request']['uri_split'][3] == 'product'
        && isset($ciniki['request']['uri_split'][4]) && $ciniki['request']['uri_split'][4] != '' 
        ) {
        $category_permalink = $ciniki['request']['uri_split'][1];
        $subcategory_permalink = $ciniki['request']['uri_split'][2];
        $product_permalink = $ciniki['request']['uri_split'][4];
/*
        //
        // Check for cached content
        //
        if( isset($ciniki['business']['cache_dir']) && $ciniki['business']['cache_dir'] != '' ) {
            if( isset($ciniki['request']['uri_split'][5]) && $ciniki['request']['uri_split'][5] == 'gallery'
                && isset($ciniki['request']['uri_split'][6]) && $ciniki['request']['uri_split'][6] != '' 
                ) {
                $cache_file = $ciniki['business']['cache_dir'] . '/ciniki.web/products_c_' . $category_permalink . '_s_' . $subcategory_permalink . '_p_' . $product_permalink . '_g_' . $ciniki['request']['uri_split'][6];
            } else {
                $cache_file = $ciniki['business']['cache_dir'] . '/ciniki.web/products_c_' . $category_permalink . '_s_' . $subcategory_permalink . '_p_' . $product_permalink;
            }
            // Check if no changes have been made since last cache file write
            if( file_exists($cache_file) && filemtime($cache_file) > $last_change ) {
                $content = file_get_contents($cache_file);
                if( $content != '' ) {
//                  error_log("WEB-CACHE: using cached $cache_file");
                    return array('stat'=>'ok', 'content'=>$content);
                }
            }
        }
*/
        ciniki_core_loadMethod($ciniki, 'ciniki', 'products', 'web', 'productDetails');
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processProduct');
        
        //
        // Get the product information
        //
        $rc = ciniki_products_web_productDetails($ciniki, $settings, $ciniki['request']['business_id'], 
            array('product_permalink'=>$product_permalink, 
                'category_permalink'=>$category_permalink,
                'subcategory_permalink'=>$subcategory_permalink,
                ));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $product = $rc['product'];
        $page_title = $product['name'];
        $article_title = $product['name'];

        $ciniki['response']['head']['links']['canonical'] = array('rel'=>'canonical', 
            'href'=>$ciniki['request']['domain_base_url'] . '/products/product/' . $product_permalink
            );
        $ciniki['response']['head']['og']['url'] .= '/product/' . $product_permalink;
        $ciniki['response']['head']['og']['description'] = strip_tags($product['short_description']);

        if( isset($product['category_title']) && $product['category_title'] != '' ) {
            $article_title = "<a href='" . $ciniki['request']['base_url'] . "/products/category/" . $category_permalink
                    . "'>" . $product['category_title'] . "</a>";
        } else {
            $article_title = $page_title;
        }
        if( isset($product['subcategory_title']) && $product['subcategory_title'] != '' ) {
            $article_title .= ' - ' 
                . "<a href='" . $ciniki['request']['base_url'] . "/products/category/" . $category_permalink
                    . "/" . $subcategory_permalink . "'>" . $product['subcategory_title'] . "</a>";
        }

        //
        // Check if image requested
        //
        if( isset($ciniki['request']['uri_split'][5]) && $ciniki['request']['uri_split'][5] == 'gallery'
            && isset($ciniki['request']['uri_split'][6]) && $ciniki['request']['uri_split'][6] != '' 
            ) {
            $image_permalink = $ciniki['request']['uri_split'][6];
            $ciniki['response']['head']['links']['canonical']['href'] .= '/gallery/' . $image_permalink;
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processGalleryImage');
            $rc = ciniki_web_processGalleryImage($ciniki, $settings, $ciniki['request']['business_id'],
                array('item'=>$product,
                    'gallery_url'=>$ciniki['request']['base_url'] . "/products/category/$category_permalink/$subcategory_permalink/product/$product_permalink/gallery",
                    'article_title'=>$article_title .= " - <a href='" . $ciniki['request']['base_url'] 
                        . "/products/category/$category_permalink/$subcategory_permalink"
                        . "/product/$product_permalink'>" . $product['name'] . "</a>",
                    'image_permalink'=>$image_permalink,
                ));
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $page_content .= $rc['content'];
        } 
    
        //
        // Display the product
        //
        else {
            $article_title .= ' - ' . $product['name'];
            $page_content .= "<article class='page'>\n"
                . "<header class='entry-title'><h1 class='entry-title'>" . $article_title . "</h1></header>\n"
                . "<div class='entry-content'>";

            //
            // Display the product
            //
            $base_url = $ciniki['request']['base_url'] 
                . "/products/category/$category_permalink/$subcategory_permalink" 
                . "/product/" . $product_permalink;
            $rc = ciniki_web_processProduct($ciniki, $settings, $ciniki['request']['business_id'], 
                $base_url, $product, array('title'=>$page_title, 'tags'=>$product['social-tags']));
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $page_content .= $rc['content'];
            $page_content .= "</div></article>";
        }
    }

    //
    // Generate the sub-category listing page
    //
    elseif( $page_content == '' 
        && isset($ciniki['request']['uri_split'][0]) 
        && $ciniki['request']['uri_split'][0] == 'category'
        && isset($ciniki['request']['uri_split'][1]) && $ciniki['request']['uri_split'][1] != '' 
        && isset($ciniki['request']['uri_split'][2]) && $ciniki['request']['uri_split'][2] != '' 
        ) {
        $category_permalink = urldecode($ciniki['request']['uri_split'][1]);
        $subcategory_permalink = urldecode($ciniki['request']['uri_split'][2]);
/*
        //
        // Check for cached content
        //
        if( isset($ciniki['business']['cache_dir']) && $ciniki['business']['cache_dir'] != '' ) {
            $cache_file = $ciniki['business']['cache_dir'] . '/ciniki.web/products_c_' . $category_permalink . '_s_' . $subcategory_permalink;
            // Check if no changes have been made since last cache file write
            if( file_exists($cache_file) && filemtime($cache_file) > $last_change ) {
                $content = file_get_contents($cache_file);
                if( $content != '' ) {
//                  error_log("WEB-CACHE: using cached $cache_file");
                    return array('stat'=>'ok', 'content'=>$content);
                }
            }
        }
*/
        ciniki_core_loadMethod($ciniki, 'ciniki', 'products', 'web', 'subcategoryDetails');
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');

        //
        // Get the details for a category
        //
        $rc = ciniki_products_web_subcategoryDetails($ciniki, $settings, $ciniki['request']['business_id'],
            array('category_permalink'=>$category_permalink, 'subcategory_permalink'=>$subcategory_permalink));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $details = isset($rc['details'])?$rc['details']:array();
        $products = isset($rc['products'])?$rc['products']:array();

//      print "<pre>" . print_r($rc, true) . "</pre>";

        if( isset($details['category_title']) && $details['category_title'] != '' ) {
//          $article_title = "<a href='" . $ciniki['request']['base_url'] . "/products'>$page_title</a> - "
            $article_title = "<a href='" . $ciniki['request']['base_url'] . "/products/category/" . $category_permalink
                    . "'>" . $details['category_title'] . "</a>";
            $page_title = $details['category_title'];
        } else {
            $article_title = $page_title;
        }
        if( isset($details['subcategory_title']) && $details['subcategory_title'] != '' ) {
            $article_title .= ' - ' . $details['subcategory_title'];
            $page_title .= ' - ' . $details['subcategory_title'];
        }

        $page_content .= "<article class='page'>\n"
            . "<header class='entry-title'><h1 id='entry-title' class='entry-title'>"
            . $article_title
            . "</h1></header>\n"
            . "<div class='entry-content'>\n"
            . "";

        //
        // if there's any content for the category, display it
        //
        if( (isset($details['content']) && $details['content'] != '') ) {
            // Image
            if( isset($details['image_id']) && $details['image_id'] > 0 ) {
                ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
                $rc = ciniki_web_getScaledImageURL($ciniki, $details['image_id'], 'original', '500', 0);
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
                $page_content .= "<aside><div class='image-wrap'><div class='image'>"
                    . "<img title='' alt='" . $page_title . "' src='" . $rc['url'] . "' />"
                    . "</div></div></aside>";
            }
            
            // Content
            if( isset($details['content']) ) {
                ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
                $rc = ciniki_web_processContent($ciniki, $settings, $details['content']);   
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
                $page_content .= $rc['content'];
            }
            $page_content .= "<br style='clear:both;'/>\n";
        }


        if( isset($products) && count($products) > 0 ) {
            $base_url = $ciniki['request']['base_url'] . "/products/category/" . $category_permalink 
                . "/" . $subcategory_permalink
                . "/product";
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processCIList');
            $rc = ciniki_web_processCIList($ciniki, $settings, $base_url, array('0'=>array(
                'name'=>'', 'noimage'=>'/ciniki-web-layouts/default/img/noimage_240.png',
                'list'=>$products)), array());
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $page_content .= $rc['content'];
        } else {
            $page_content .= "<p>I'm sorry, but there doesn't appear to be an products available in this category.</p>";
        }
        $page_content .= "</div>";
        $page_content .= "</article>";
    }

    //
    // Generate the category listing page
    //
    elseif( $page_content == '' 
        && isset($ciniki['request']['uri_split'][0]) 
        && $ciniki['request']['uri_split'][0] == 'category'
        && $ciniki['request']['uri_split'][1] != '' 
        ) {
        $category_permalink = urldecode($ciniki['request']['uri_split'][1]);
/*
        //
        // Check for cached content
        //
        if( isset($ciniki['business']['cache_dir']) && $ciniki['business']['cache_dir'] != '' ) {
            $cache_file = $ciniki['business']['cache_dir'] . '/ciniki.web/products_c_' . $category_permalink;
            // Check if no changes have been made since last cache file write
            if( file_exists($cache_file) && filemtime($cache_file) > $last_change ) {
                $content = file_get_contents($cache_file);
                if( $content != '' ) {
//                  error_log("WEB-CACHE: using cached $cache_file");
                    return array('stat'=>'ok', 'content'=>$content);
                }
            }
        }
*/
        ciniki_core_loadMethod($ciniki, 'ciniki', 'products', 'web', 'categoryDetails');
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');

        //
        // Get the details for a category
        //
        $rc = ciniki_products_web_categoryDetails($ciniki, $settings, $ciniki['request']['business_id'],
            array('category_permalink'=>$category_permalink));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $details = isset($rc['details'])?$rc['details']:array();
        $subcategories = isset($rc['subcategories'])?$rc['subcategories']:array();
        $subcategorytypes = isset($rc['subcategorytypes'])?$rc['subcategorytypes']:array();
        $products = isset($rc['products'])?$rc['products']:array();

//      print "<pre>" . print_r($rc, true) . "</pre>";
        
        if( isset($details['category_title']) && $details['category_title'] != '' ) {
//          $article_title = "<a href='" . $ciniki['request']['base_url'] . "/products'>$page_title</a> - "
//              . $details['category_title'];
            $article_title = $details['category_title'];
            $page_title = $details['category_title'];
        } else {
            $article_title = $page_title;
        }

        $page_content .= "<article class='page'>\n"
            . "<header class='entry-title'><h1 id='entry-title' class='entry-title'>"
            . $article_title
            . "</h1></header>\n"
            . "<div class='entry-content'>\n"
            . "";

        //
        // if there's any content or an image for the category, display it
        //
        if( (isset($details['content']) && $details['content'] != '') ) {
            // Image
            if( isset($details['image_id']) && $details['image_id'] > 0 ) {
                ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
                $rc = ciniki_web_getScaledImageURL($ciniki, $details['image_id'], 'original', '500', 0);
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
                $page_content .= "<aside><div class='image-wrap'><div class='image'>"
                    . "<img title='' alt='" . $page_title . "' src='" . $rc['url'] . "' />"
                    . "</div></div></aside>";
            }
            
            // Content
            if( isset($details['content']) ) {
                ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
                $rc = ciniki_web_processContent($ciniki, $settings, $details['content']);   
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
                $page_content .= $rc['content'];
            }
            $page_content .= "<br style='clear:both;'/>\n";
        }

        //
        // If there are sub categories, display them
        //
        if( isset($subcategories) && count($subcategories) > 0 ) {
            if( isset($settings['page-products-subcategories-size']) 
                && $settings['page-products-subcategories-size'] != '' 
                && $settings['page-products-subcategories-size'] != 'auto' 
                ) {
                $size = $settings['page-products-subcategories-size'];
            } else {
                $size = 'large';
                foreach($subcategories as $tid => $type) {
                    if( count($subcategories) > 12 ) {
                        $size = 'small';
                    } elseif( count($subcategories) > 6 ) {
                        $size = 'medium';
                    }
                }
            }
            $base_url = $ciniki['request']['base_url'] . "/products/category/" . $category_permalink . "/category";
            $page_content .= "<div class='image-categories'>";
            foreach($subcategories AS $cnum => $category) {
                $name = $category['name'];
                $permalink = $category['permalink'];
                ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
                $rc = ciniki_web_getScaledImageURL($ciniki, $category['image_id'], 'thumbnail', '240', 0);
                if( $rc['stat'] != 'ok' ) {
                    $img_url = '/ciniki-web-layouts/default/img/noimage_240.png';
                } else {
                    $img_url = $rc['url'];
                }
                $page_content .= "<div class='image-categories-thumbnail-wrap image-categories-thumbnail-$size'>"
                    . "<a href='" . $ciniki['request']['base_url'] . "/products/category/" 
                        . $category_permalink . "/" . $permalink . "' " . "title='" . $name . "'>"
                    . "<div class='image-categories-thumbnail'>"
                    . "<img title='$name' alt='$name' src='$img_url' />"
                    . "</div>"
                    . "<span class='image-categories-name'>$name</span>"
                    . "</a></div>";
            }
            $page_content .= "</div>";
        }

        //
        // If there is more than one type of subcategory
        //
        if( isset($subcategorytypes) && count($subcategorytypes) > 0 ) {
            $num_types = count($subcategorytypes);
            if( isset($settings['page-products-subcategories-size']) 
                && $settings['page-products-subcategories-size'] != '' 
                && $settings['page-products-subcategories-size'] != 'auto' 
                ) {
                $size = $settings['page-products-subcategories-size'];
            } else {
                $size = 'large';
                foreach($subcategorytypes as $tid => $type) {
                    if( count($type['categories']) > 12 ) {
                        $size = 'small';
                    } elseif( count($type['categories']) > 6 ) {
                        $size = 'medium';
                    }
                }
            }
            $num_items = 0;
            foreach($subcategorytypes as $tid => $type) {
                $subcategories = $type['categories'];
//              $base_url = $ciniki['request']['base_url'] . "/products/category/" . $category_permalink . "/category";
                if( $num_types > 1 ) {
                    $page_content .= "<h2 class='wide'>" . $type['name'] . "</h2>";
                }
                $page_content .= "<div class='image-categories'>";
                foreach($subcategories AS $cnum => $category) {
                    $name = $category['name'];
                    $permalink = $category['permalink'];
                    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
                    $rc = ciniki_web_getScaledImageURL($ciniki, $category['image_id'], 'thumbnail', '240', 0);
                    if( $rc['stat'] != 'ok' ) {
                        $img_url = '/ciniki-web-layouts/default/img/noimage_240.png';
                    } else {
                        $img_url = $rc['url'];
                    }
                    $page_content .= "<div class='image-categories-thumbnail-wrap image-categories-thumbnail-$size'>"
                        . "<a href='" . $ciniki['request']['base_url'] . "/products/category/" 
                            . $category_permalink . "/" . $permalink . "' " . "title='" . $name . "'>"
                        . "<div class='image-categories-thumbnail'>"
                        . "<img title='$name' alt='$name' src='$img_url' />"
                        . "</div>"
                        . "<span class='image-categories-name'>$name</span>"
                        . "</a></div>";
                    $num_items++;
                }
                $page_content .= "</div>";
            }
//          if( $num_items > 20 ) {
//              
//          }
//          print_r($num_items);
        }

        //
        // If there are products, display the list
        //
        if( isset($products) && count($products) > 0 ) {
            if( isset($subcategories) && count($subcategories) > 0 ) {
                $page_content .= "<br style='clear: both;' />";
            }
            $base_url = $ciniki['request']['base_url'] . "/products/category/" . $category_permalink 
                . "/product";
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processCIList');
            $rc = ciniki_web_processCIList($ciniki, $settings, $base_url, array('0'=>array(
                'name'=>'', 'noimage'=>'/ciniki-web-layouts/default/img/noimage_240.png',
                'list'=>$products)), array());
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $page_content .= $rc['content'];
        }

        //
        // Generate list of products
        //
        $page_content .= "</div>";
        $page_content .= "</article>";
    }

    //
    // Generate the main products page, showing the main categories
    //
    elseif( $page_content == '' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash');
        $rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_web_content', 
            'business_id', $ciniki['request']['business_id'], 'ciniki.web', 'content', 'page-products');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }

        $page_content .= "<article class='page'>\n"
            . "<header class='entry-title'><h1 id='entry-title' class='entry-title'>$page_title</h1></header>\n"
            . "<div class='entry-content'>\n"
            . "";

        if( isset($rc['content']['page-products-content']) ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
            $rc = ciniki_web_processContent($ciniki, $settings, $rc['content']['page-products-content']);   
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $page_content .= $rc['content'];
        }
        if( isset($settings['page-products-name']) && $settings['page-products-name'] != '' ) {
            $page_title = $settings['page-products-name'];
        } else {
            $page_title = 'Products';
        }

        //
        // List the categories the user has created in the artcatalog, 
        // OR just show all the thumbnails if they haven't created any categories
        //
        if( isset($settings['page-products-categories-format']) 
            && $settings['page-products-categories-format'] == 'list' 
            ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'products', 'web', 'categoryList');
            $rc = ciniki_products_web_categoryList($ciniki, $settings, $ciniki['request']['business_id']); 
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
//          if( isset($rc['products']) ) {
//          } else
            if( isset($rc['categories']) ) {
                ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processCIList');
                $rc = ciniki_web_processCIList($ciniki, $settings, $ciniki['request']['base_url'] . '/products/category', 
                    $rc['categories'], array('notitle'=>'yes'));
                if( $rc['content'] != '' ) {
                    $page_content .= $rc['content'];
                }
            } else {
                $page_content .= "<p>I'm sorry, but we currently don't have any products available.</p>";
            }
        } else {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'products', 'web', 'categories');
            $rc = ciniki_products_web_categories($ciniki, $settings, $ciniki['request']['business_id']); 
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            //
            // If there were categories returned, display the thumbnails
            //
            if( isset($rc['categories']) ) {
                $page_content .= "<div class='image-categories'>";
                $size = 'large';
                if( isset($settings['page-products-categories-size']) 
                    && $settings['page-products-categories-size'] != '' 
                    && $settings['page-products-categories-size'] != 'auto' 
                    ) {
                    $size = $settings['page-products-categories-size'];
                } elseif( count($rc['categories']) > 12 ) {
                    $size = 'small';
                } elseif( count($rc['categories']) > 6 ) {
                    $size = 'medium';
                }
                foreach($rc['categories'] AS $cnum => $category) {
                    $name = $category['name'];
                    $permalink = $category['permalink'];
                    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
                    $rc = ciniki_web_getScaledImageURL($ciniki, $category['image_id'], 'thumbnail', '240', 0);
                    if( $rc['stat'] != 'ok' ) {
                        $img_url = '/ciniki-web-layouts/default/img/noimage_240.png';
                    } else {
                        $img_url = $rc['url'];
                    }
                    $page_content .= "<div class='image-categories-thumbnail-wrap image-categories-thumbnail-$size'>"
                        . "<a href='" . $ciniki['request']['base_url'] . "/products/category/" . $permalink . "' "
                            . "title='" . $name . "'>"
                        . "<div class='image-categories-thumbnail'>"
                        . "<img title='$name' alt='$name' src='$img_url' />"
                        . "</div>"
                        . "<span class='image-categories-name'>$name</span>"
                        . "</a></div>";
                }
                $page_content .= "</div>";
            }
            //
            // If there were no categories but a product list, display the products
            //
            elseif( isset($rc['products']) ) {
                $page_content .= "<div class='image-categories'>";
                $size = 'large';
                if( isset($settings['page-products-categories-size']) 
                    && $settings['page-products-categories-size'] != '' 
                    && $settings['page-products-categories-size'] != 'auto' 
                    ) {
                    $size = $settings['page-products-categories-size'];
                } elseif( count($rc['products']) > 12 ) {
                    $size = 'small';
                } elseif( count($rc['products']) > 6 ) {
                    $size = 'medium';
                }
                foreach($rc['products'] AS $pnum => $product) {
                    $name = $product['title'];
                    $permalink = $product['permalink'];
                    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
                    $rc = ciniki_web_getScaledImageURL($ciniki, $product['image_id'], 'thumbnail', '240', 0);
                    if( $rc['stat'] != 'ok' ) {
                        $img_url = '/ciniki-web-layouts/default/img/noimage_240.png';
                    } else {
                        $img_url = $rc['url'];
                    }
                    $page_content .= "<div class='image-categories-thumbnail-wrap image-categories-thumbnail-$size'>"
                        . "<a href='" . $ciniki['request']['base_url'] . "/products/product/" . $permalink . "' "
                            . "title='" . $name . "'>"
                        . "<div class='image-categories-thumbnail'>"
                        . "<img title='$name' alt='$name' src='$img_url' />"
                        . "</div>"
                        . "<span class='image-categories-name'>$name</span>"
                        . "</a></div>";
                }
                $page_content .= "</div>";
            } 
            //
            // No categories or products
            //
            else {
                $page_content .= "<p>I'm sorry, but we currently don't have any products available.</p>";
            }
        }
        $page_content .= "</div>";
        $page_content .= "</article>";
    }

    $content = '';

    //
    // Add the header
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageHeader');
    $rc = ciniki_web_generatePageHeader($ciniki, $settings, $page_title, array());
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }
    $content .= $rc['content'];

    //
    // Build the page content
    //
    $content .= "<div id='content'>\n";
    if( $page_content != '' ) { $content .= $page_content; }
    $content .= "</div>";

    //
    // Add the footer
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageFooter');
    $rc = ciniki_web_generatePageFooter($ciniki, $settings);
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }
    $content .= $rc['content'];

    //
    // Save the cache file
    //
    if( $cache_file != '' && $cache_update == 'yes' ) {
        if( !file_exists(dirname($cache_file)) && mkdir(dirname($cache_file), 0755, true) === FALSE ) {
            error_log("WEB-CACHE: Failed to create dir for " . dirname($cache_file));
        } 
        elseif( file_put_contents($cache_file, $page_content) === FALSE ) {
            error_log("WEB-CACHE: Failed to write $cache_file");
        } else {
            //
            // We must force the timestamp on the file, otherwise at rackspace cloudsites it's behind
            //
            touch($cache_file, time());
        }
    }

    return array('stat'=>'ok', 'content'=>$content);
}
?>
