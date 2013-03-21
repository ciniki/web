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
function ciniki_web_generateGalleryJavascript($ciniki, $next, $prev) {

	//
	// Javascript to resize the image, and arrow overlays once the image is loaded.
	// This is done so the image can be properly fit to the size of the screen.
	//
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
			. "var wwidth = document.getElementById('main-menu-container').offsetWidth;"
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
			. "document.getElementById('gallery-image-prev').style.height = i.height + 'px';"
			. "document.getElementById('gallery-image-next').style.height = i.height + 'px';"
			. "document.getElementById('gallery-image-prev').style.width = (i.offsetLeft + (i.offsetWidth/2)) + 'px';"
			. "document.getElementById('gallery-image-next').style.width = ((i.offsetLeft-2)+100) + 'px';"
			. "document.getElementById('gallery-image-prev').style.left = '0px';"
			. "document.getElementById('gallery-image-next').style.left = (i.offsetLeft+i.width) + 'px';"
			. "var p = document.getElementById('gallery-image-prev-img');"
			. "p.style.left = (i.offsetLeft-21) + 'px';"
			. "p.style.top = ((i.height/2)-(p.offsetHeight/2)) + 'px';"
			. "var n = document.getElementById('gallery-image-next-img');"
			. "n.style.left = '1px';"
			. "n.style.top = ((i.height/2)-(p.offsetHeight/2)) + 'px';"
			. "var w = document.getElementById('gallery-image-wrap');"
			. "d.style.width = w.offsetWidth + 'px';"
			. "window.scrollTo(0, t.offsetTop - 10);"
		. "}\n"
		. "function scrollto_header() {"
			. "var e = document.getElementById('entry-title');"
			. "window.scrollTo(0, e.offsetTop - 10);"
		. "}\n"
		. "document.onkeydown = function(e) {"
		. "";
	if( $prev != null && isset($prev['permalink']) ) {
		$javascript .=  ""
			. "if( e.keyCode == 37 || e.keyCode == 72 ) {"
				. "document.location.href='" . $prev['permalink'] . "';"
			. "}";
	}
	if( $next != null && isset($next['permalink']) ) {
		$javascript .=  ""
			. "if( e.keyCode == 39 || e.keyCode == 76 ) {"
				. "document.location.href='" . $next['permalink'] . "';"
			. "}";
	}
	$javascript .=  ""
		. "}\n"
		. "</script>\n";

	return array('stat'=>'ok', 'javascript'=>$javascript);
}
?>