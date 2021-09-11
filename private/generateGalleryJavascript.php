<?php
//
// Description
// -----------
// This function will setup the javascript for image resize and positioning in gallery view.
//
// Arguments
// ---------
// ciniki:
//
// Returns
// -------
//
function ciniki_web_generateGalleryJavascript(&$ciniki, $next, $prev) {

    //
    // Javascript to resize the image, and arrow overlays once the image is loaded.
    // This is done so the image can be properly fit to the size of the screen.
    //
//  $images = 'image = new Image();';
    $images = '';
    $javascript = "<script type='text/javascript'>\n"
        . "function gallery_resize_arrows() {"
            . "var i = document.getElementById('gallery-image-img');"
            . "var t = document.getElementById('entry-title');"
            . "var d = document.getElementById('gallery-image-details');"
            . "var w = document.getElementById('gallery-image-wrap');"
            // Detect IE
            . "try {"
                . "var bwidth = parseInt(getComputedStyle(w, null).getPropertyValue('border-right-width'), 10);"
                . "var mheight = parseInt(getComputedStyle(t, null).getPropertyValue('margin-bottom'), 10);"
            . "} catch(e) {"
                . "var bwidth = parseInt(w.currentStyle.borderWidth, 10);"
                . "var mheight = 20;"
            . "}"
            . "var cheight = (t.offsetHeight + i.offsetHeight);"
            . "var wheight = window.innerHeight;"
            . "if( cheight > wheight ) {"
                . "i.style.maxHeight = (wheight - t.offsetHeight - mheight - (bwidth*2)-20) + 'px';"
                . "i.style.width = 'auto';"
                . "}"
            . "var cwidth = i.offsetWidth;"
//          . "var wwidth = document.getElementById('main-menu-container').offsetWidth;"
            . "var wwidth = document.getElementById('gallery-image').offsetWidth;"
            . "if( cwidth > wwidth ) {"
                . "if( navigator.appName == 'Microsoft Internet Explorer') {"
                    . "var ua = navigator.userAgent;"
                    . "var re = new RegExp('MSIE ([0-9]{1,}[\.0-9]{0,})');"
                    . "if (re.exec(ua) != null) {"
                        . "rv = parseFloat(RegExp.$1); }"
                    . "if( rv <= 8 ) {"
                    . "w.style.maxWidth = (wwidth - (bwidth*2)) + 'px';"
                    . "i.style.maxWidth = '100%';"
                    . "i.style.height = 'auto';"
                    . "}"
                . "} else {"
                    . "i.style.maxWidth = (wwidth - (bwidth*2)) + 'px';"
                    . "i.style.height = 'auto';"
                . "}"
            . "}"
            . "if( document.getElementById('gallery-image-prev') != null ) {"
                . "document.getElementById('gallery-image-prev').style.height = i.height + 'px';"
                . "document.getElementById('gallery-image-prev').style.width = (i.offsetLeft + (i.offsetWidth/2)) + 'px';"
                . "document.getElementById('gallery-image-prev').style.left = '0px';"
                . "var p = document.getElementById('gallery-image-prev-img');"
                . "p.style.left = (i.offsetLeft-21) + 'px';"
                . "p.style.top = ((i.height/2)-(p.offsetHeight/2)) + 'px';"
            . "}"
            . "if( document.getElementById('gallery-image-next') != null ) {"
                . "document.getElementById('gallery-image-next').style.width = ((i.offsetLeft-2)+100) + 'px';"
                . "document.getElementById('gallery-image-next').style.height = i.height + 'px';"
                . "document.getElementById('gallery-image-next').style.left = (i.offsetLeft+i.width) + 'px';"
                . "var n = document.getElementById('gallery-image-next-img');"
                . "n.style.left = '1px';"
                . "n.style.top = ((i.height/2)-(n.offsetHeight/2)) + 'px';"
            . "}"
            . "var w = document.getElementById('gallery-image-wrap');"
            . "d.style.width = w.offsetWidth + 'px';"
            . "window.scrollTo(0, t.offsetTop - 10);"
        . "}\n"
        . "function scrollto_header() {"
            . "var e = document.getElementById('entry-title');"
            . "window.scrollTo(0, e.offsetTop - 10);"
            . "preload_images();"
        . "}\n"
        . "document.onkeydown = function(e) {"
        . "";
    //
    // FIXME: Remove old code that uses permalink once transition to new web structure is completed (msg added sep 23, 2015)
    //
    if( $prev != null && isset($prev['permalink']) ) {
        if( isset($prev['image_id']) && $prev['image_id'] > 0 ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
            $rc = ciniki_web_getScaledImageURL($ciniki, $prev['image_id'], 'original', 0, 600);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $img_url = $rc['url'];
//          $ciniki['response']['head']['links'][] = array('rel'=>'prev', 'title'=>'Prev', 'href'=>$prev['permalink']);
            $images .= "prev_pic = new Image(); prev_pic.src = '" . $img_url . "';";
        }
        $javascript .=  ""
            . "if( e.keyCode == 37 || e.keyCode == 72 ) {"
                . "document.location.href='" . $prev['permalink'] . "';"
            . "}";
    }
    if( $next != null && isset($next['permalink']) ) {
        if( isset($next['image_id']) && $next['image_id'] > 0 ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
            $rc = ciniki_web_getScaledImageURL($ciniki, $next['image_id'], 'original', 0, 600);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $img_url = $rc['url'];
//          $ciniki['response']['head']['links'][] = array('rel'=>'next', 'title'=>'Next', 'href'=>$next['permalink']);
            $images .= "next_pic = new Image(); next_pic.src = '" . $img_url . "';";
        }
        $javascript .=  ""
            . "if( e.keyCode == 39 || e.keyCode == 76 ) {"
                . "document.location.href='" . $next['permalink'] . "';"
            . "}";
    }
    if( $prev != null && isset($prev['url']) ) {
        if( isset($prev['image_id']) && $prev['image_id'] > 0 ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
            $rc = ciniki_web_getScaledImageURL($ciniki, $prev['image_id'], 'original', 0, 600);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $img_url = $rc['url'];
            $images .= "prev_pic = new Image(); prev_pic.src = '" . $img_url . "';";
        }
        $javascript .=  ""
            . "if( e.keyCode == 37 || e.keyCode == 72 ) {"
                . "document.location.href='" . $prev['url'] . "';"
            . "}";
    }
    if( $next != null && isset($next['url']) ) {
        if( isset($next['image_id']) && $next['image_id'] > 0 ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
            $rc = ciniki_web_getScaledImageURL($ciniki, $next['image_id'], 'original', 0, 600);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $img_url = $rc['url'];
            $images .= "next_pic = new Image(); next_pic.src = '" . $img_url . "';";
        }
        if( isset($next['url']) ) {
            $javascript .=  ""
                . "if( e.keyCode == 39 || e.keyCode == 76 ) {"
                    . "document.location.href='" . $next['url'] . "';"
                . "}";
        }
    }
    $javascript .=  ""
        . "}\n";
    $javascript .= ""
        . "function gallery_swap_image(u) {"
            . "var i = document.getElementById('gallery-image-img');"
            . "i.src = u;"
            . "gallery_resize_arrows();"
            . "return false;"
        . "}\n";
    $javascript .= ""
        . "function preload_images() {"
            . $images
        . "}\n";
    $javascript .= "</script>\n";

    return array('stat'=>'ok', 'javascript'=>$javascript);
}
?>
