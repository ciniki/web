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
function ciniki_web_generateCinikiJavascript($ciniki) {

    //
    // Javascript to resize the image, and arrow overlays once the image is loaded.
    // This is done so the image can be properly fit to the size of the screen.
    //
    if( (isset($_SERVER['HTTP_CLUSTER_HTTPS']) && $_SERVER['HTTP_CLUSTER_HTTPS'] == 'on')
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
        || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') )  {
        $url = $ciniki['request']['ssl_domain_base_url'];
    } else {
        $url = $ciniki['request']['domain_base_url'];
    }

    $javascript = "<script type='text/javascript'>\n"
        . "window.C={"
            . "'url':'" . $url . "/api/'"
            . "};"
        // The command, parameters, callback function
        . "C.getBg=function(c,p,f) {"
            . "var u='';"
            . "if(p!=null){for(i in p){"
                . "u+=(u==''?'?':'&')+i+'='+encodeURIComponent(p[i]);"
            . "}};"
            . "u=this.url+c+u;"
            . "var x=new XMLHttpRequest();"
            . "x.open('GET',u,true);"
            . "x.onreadystatechange=function(){"
                . "if(x.readyState==4&&x.status==200){"
                    . "var r=eval('('+x.responseText+')');"
                    . "if(r.stat!='ok'&&r.stat!='noavail'){console.log(x.responseText);}"
                    . "f(r);"
                . "};"
                . "if(x.readyState>2&&x.status>=300){f({'stat':'fail', 'err':{'code':'300', 'msg':'Error connecting to server.'}});console.log('apierr:'+x.status);}"
            . "};"
            . "x.send(null);"
        . "};"  // end this.getBg
        // Clear a element in the dom
        . "C.clr=function(i){"
            . "var e=(typeof i=='object'?i:document.getElementById(i));"
            . "if(e!=null&e.children!=null){while(e.children.length>0){e.removeChild(e.children[0]);}}"
            . "return e;"
        . "};"
        // Create a new element
        . "C.aE=function(t,i,c,h,f){"
            . "var e=document.createElement(t);"
            . "if(i!=null){e.setAttribute('id',i);}"
            . "if(c!=null){e.className=c;}"
            . "if(h!=null){e.innerHTML=h;}"
            . "if(f!=null&&f!=''){e.setAttribute('onclick',f);}"
            . "return e;"
        . "};"
        // Lookup an element
        . "C.gE=function(i){return document.getElementById(i);};"
        . "";
    $javascript.= "</script>\n";

    return array('stat'=>'ok', 'javascript'=>$javascript);
}
?>
