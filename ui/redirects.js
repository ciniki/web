//
// This will manage the redirects for a website
//
function ciniki_web_redirects() {
    //
    // Panels
    //
    this.init = function() {
        //
        // events panel
        //
        this.menu = new M.panel('Redirects',
            'ciniki_web_redirects', 'menu',
            'mc', 'medium', 'sectioned', 'ciniki.web.redirects.menu');
        this.menu.sections = {
            'redirects':{'label':'Redirects', 'type':'simplegrid', 'num_cols':2,
                'headerValues':['From', 'To'],
                'noData':'No redirects',
                'addTxt':'Add Redirect',
                'addFn':'M.ciniki_web_redirects.redirectEdit(\'M.ciniki_web_redirects.menuShow();\',0);',
                },
            };
        this.menu.sectionData = function(s) { return this.data[s]; }
        this.menu.noData = function(s) { return this.sections[s].noData; }
        this.menu.cellValue = function(s, i, j, d) {
            switch(j) {
                case 0: return d.oldurl;
                case 1: return d.newurl;
            }
        };
        this.menu.rowFn = function(s, i, d) {
            return 'M.ciniki_web_redirects.redirectEdit(\'M.ciniki_web_redirects.menuShow();\',\'' + d.id + '\');';
        };
        this.menu.addButton('add', 'Add', 'M.ciniki_web_redirects.redirectEdit(\'M.ciniki_web_redirects.menuShow();\',0);');
        this.menu.addClose('Back');

        //
        // The panel for a site's menu
        //
        this.edit = new M.panel('Redirect',
            'ciniki_web_redirects', 'edit',
            'mc', 'medium', 'sectioned', 'ciniki.web.redirects.edit');
        this.edit.data = null;
        this.edit.redirect_id = 0;
        this.edit.sections = { 
            'redirect':{'label':'Redirect', 'aside':'yes', 'fields':{
                'oldurl':{'label':'Old URL', 'type':'text'},
                'newurl':{'label':'New URL', 'type':'text'},
                }},
            '_buttons':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_web_redirects.redirectSave();'},
                'delete':{'label':'Delete', 'fn':'M.ciniki_web_redirects.redirectDelete();'},
                }},
            };  
        this.edit.fieldValue = function(s, i, d) { return this.data[i]; }
        this.edit.fieldHistoryArgs = function(s, i) {
            return {'method':'ciniki.web.redirectHistory', 'args':{'tnid':M.curTenantID, 
                'redirect_id':this.redirect_id, 'field':i}};
        }
        this.edit.addButton('save', 'Save', 'M.ciniki_web_redirects.redirectSave();');
        this.edit.addClose('Cancel');
    }

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
        var appContainer = M.createContainer(appPrefix, 'ciniki_web_redirects', 'yes');
        if( appContainer == null ) {
            M.alert('App Error');
            return false;
        } 

        this.menuShow(cb);
    }

    this.menuShow = function(cb) {
        this.menu.data = {};
        M.api.getJSONCb('ciniki.web.redirectList', {'tnid':M.curTenantID}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.ciniki_web_redirects.menu;
            p.data = rsp;
            p.refresh();
            p.show(cb);
        });
    };

    this.redirectEdit = function(cb, rid) {
        this.edit.reset();
        if( rid != null ) { this.edit.redirect_id = rid; }
        this.edit.sections._buttons.buttons.delete.visible = (this.edit.redirect_id>0?'yes':'no');
        M.api.getJSONCb('ciniki.web.redirectGet', {'tnid':M.curTenantID, 'redirect_id':this.edit.redirect_id}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.ciniki_web_redirects.edit;
            p.data = rsp.redirect;
            p.refresh();
            p.show(cb);
        });
    };

    this.redirectSave = function() {
        if( this.edit.redirect_id > 0 ) {
            var c = this.edit.serializeForm('no');
            if( c != '' ) {
                M.api.postJSONCb('ciniki.web.redirectUpdate', {'tnid':M.curTenantID, 'redirect_id':M.ciniki_web_redirects.edit.redirect_id}, c,
                    function(rsp) {
                        if( rsp.stat != 'ok' ) {
                            M.api.err(rsp);
                            return false;
                        } 
                    M.ciniki_web_redirects.edit.close();
                    });
            } else {
                this.edit.close();
            }
        } else {
            var c = this.edit.serializeForm('yes');
            if( c != '' ) {
                var rsp = M.api.postJSONCb('ciniki.web.redirectAdd', {'tnid':M.curTenantID}, c, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    } 
                    if( rsp.id > 0 ) {
                        var cb = M.ciniki_web_redirects.edit.cb;
                        M.ciniki_web_redirects.edit.close();
                        M.ciniki_web_redirects.showEvent(cb,rsp.id);
                    } else {
                        M.ciniki_web_redirects.edit.close();
                    }
                });
            } else {
                this.edit.close();
            }
        }
    };

    this.redirectDelete = function() {
        M.confirm("Are you sure you want to remove this redirect?",null,function() {
            var rsp = M.api.getJSONCb('ciniki.web.redirectDelete', 
                {'tnid':M.curTenantID, 'redirect_id':M.ciniki_web_redirects.edit.redirect_id}, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    M.ciniki_web_redirects.edit.close();
                });
        });
    }
};
