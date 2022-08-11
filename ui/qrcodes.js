//
// This app will handle the listing, additions and deletions of events.  These are associated tenant.
//
function ciniki_web_qrcodes() {
    //
    // The options and information for the slider page
    //
    this.main = new M.panel('QR Codes',
        'ciniki_web_qrcodes', 'main',
        'mc', 'medium', 'sectioned', 'ciniki.web.qrcodes.main');
    this.main.data = {};
    this.main.sections = {
        'info':{'label':'QR Code Info', 'fields':{
            'url':{'label':'URL', 'type':'text'},    
            'output':{'label':'Format', 'type':'toggle', 'default':'svg', 'toggles':{'svg':'SVG', 'png':'PNG'}},    
            }},
        '_buttons':{'label':'', 'buttons':{
            'generate':{'label':'Download QR Code', 'fn':'M.ciniki_web_qrcodes.main.generate();'},
            }},
        };
    this.main.open = function(cb) {
        this.refresh();
        this.show(cb);
    }
    this.main.generate = function() {
        M.api.openFile('ciniki.web.qrcode', {
            'tnid':M.curTenantID, 
            'url':this.formValue('url'), 
            'output':this.formValue('output'),
            });
    }
    this.main.addClose('Back');

    //
    // Arguments:
    // aG - The arguments to be parsed into args
    //
    this.start = function(cb, appPrefix, aG) {
        args = {};
        if( aG != null ) { args = eval(aG); }

        //
        // Create the app container if it doesn't exist, and clear it out
        // if it does exist.
        //
        var appContainer = M.createContainer(appPrefix, 'ciniki_web_qrcodes', 'yes');
        if( appContainer == null ) {
            M.alert('App Error');
            return false;
        } 

        this.main.open();
    }
};
