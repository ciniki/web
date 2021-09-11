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
