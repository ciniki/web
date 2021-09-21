function toggleMainMenu() {
    tE('main-menu-container', 'visible');
    tE('main-menu-toggle', 'close');
}
function gE(i) {
    return document.getElementById(i);
}
function tE(n,c) {
    var e = gE(n);
    if( e.classList != null ) {
        if( e.classList.contains(c) ) {
            e.classList.remove(c);
        } else {
            e.classList.add(c);
        }
    } else if( e.className != null ) {
        if( e.className.indexOf(c) > -1 ) {
            e.className = e.className.replace(c, '');
        } else {
            e.className += ' ' + c;
        }
    }
}
function gallery_swap_image(u) {
    var i = gE('gallery-image-img');
    i.src = u;
    var e = gE('gallery-image');
    if( e != null ) { 
        window.scrollTo(0, e.offsetTop - 10);
    }
    return false;
}
