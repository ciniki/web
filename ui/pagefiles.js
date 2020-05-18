//
// The files manager for web pages
//
function ciniki_web_pagefiles() {
    this.init = function() {
        //
        // The panel to display the add form
        //
        this.add = new M.panel('Add File', 'ciniki_web_pagefiles', 'add', 'mc', 'medium', 'sectioned', 'ciniki.web.pagefiles.edit');
        this.add.default_data = {'type':'20'};
        this.add.data = {}; 
        this.add.sections = {
            '_file':{'label':'File', 'fields':{
                'uploadfile':{'label':'', 'type':'file', 'hidelabel':'yes'},
            }},
            'info':{'label':'Information', 'fields':{
                'name':{'label':'Title', 'type':'text'},
            }},
            '_description':{'label':'Description', 
                'fields':{
                    'description':{'label':'', 'hidelabel':'yes', 'type':'textarea'},
            }},
            '_save':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_web_pagefiles.add.save();'},
            }},
        }
        this.add.fieldValue = function(s, i, d) { 
            if( this.data[i] != null ) {
                return this.data[i]; 
            } 
            return ''; 
        }
        this.add.open = function(cb, eid) {
            this.reset();
            this.data = {'name':''};
            this.file_id = 0;
            this.page_id = eid;
            this.refresh();
            this.show(cb);
        }
        this.add.save = function() {
            var c = this.serializeFormData('yes');
            if( c != '' ) {
                var rsp = M.api.postJSONFormData('ciniki.web.pageFileAdd', {'tnid':M.curTenantID, 'page_id':this.page_id}, c, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    var p = M.ciniki_web_pagefiles.add;
                    p.file_id = rsp.id;
                    p.close();
                });
            } else {
                this.close();
            }
        }
        this.add.addButton('save', 'Save', 'M.ciniki_web_pagefiles.add.save();');
        this.add.addClose('Cancel');

        //
        // The panel to display the edit form
        //
        this.edit = new M.panel('File', 'ciniki_web_pagefiles', 'edit', 'mc', 'medium', 'sectioned', 'ciniki.web.pagefiles.edit');
        this.edit.file_id = 0;
        this.edit.data = null;
        this.edit.sections = {
            'info':{'label':'Details', 'type':'simpleform', 'fields':{
                'name':{'label':'Title', 'type':'text'},
            }},
            '_description':{'label':'Description', 
                'fields':{
                    'description':{'label':'', 'hidelabel':'yes', 'type':'textarea'},
            }},
            '_save':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_web_pagefiles.edit.save();'},
                'download':{'label':'Download', 'fn':'M.ciniki_web_pagefiles.edit.downloadFile(M.ciniki_web_pagefiles.edit.file_id);'},
                'delete':{'label':'Delete', 'fn':'M.ciniki_web_pagefiles.edit.remove();'},
            }},
        }
        this.edit.fieldValue = function(s, i, d) { 
            return this.data[i]; 
        }
        this.edit.sectionData = function(s) {
            return this.data[s];
        }
        this.edit.fieldHistoryArgs = function(s, i) {
            return {'method':'ciniki.web.pageFileHistory', 'args':{'tnid':M.curTenantID, 'file_id':this.file_id, 'field':i}};
        }
        this.edit.downloadFile = function(fid) {
            M.api.openFile('ciniki.web.pageFileDownload', {'tnid':M.curTenantID, 'file_id':fid});
        }
        this.edit.open = function(cb, fid) {
            if( fid != null ) { this.file_id = fid; }
            var rsp = M.api.getJSONCb('ciniki.web.pageFileGet', {'tnid':M.curTenantID, 'file_id':this.file_id}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                var p = M.ciniki_web_pagefiles.edit;
                p.data = rsp.file;
                p.refresh();
                p.show(cb);
            });
        }
        this.edit.save = function() {
            var c = this.serializeFormData('no');
            if( c != '' ) {
                M.api.postJSONFormData('ciniki.web.pageFileUpdate', {'tnid':M.curTenantID, 'file_id':this.file_id}, c,
                    function(rsp) {
                        if( rsp.stat != 'ok' ) {
                            M.api.err(rsp);
                            return false;
                        } else {
                            M.ciniki_web_pagefiles.edit.close();
                        }
                    });
            }
        }
        this.edit.remove = function() {
            M.confirm('Are you sure you want to delete \'' + this.data.name + '\'?  All information about it will be removed and unrecoverable.',null,function() {
                var rsp = M.api.getJSONCb('ciniki.web.pageFileDelete', {'tnid':M.curTenantID, 
                    'file_id':M.ciniki_web_pagefiles.edit.file_id}, function(rsp) {
                        if( rsp.stat != 'ok' ) {
                            M.api.err(rsp);
                            return false;
                        } 
                        M.ciniki_web_pagefiles.edit.close();
                    });
            });
        }
        this.edit.addButton('save', 'Save', 'M.ciniki_web_pagefiles.edit.save();');
        this.edit.addClose('Cancel');
    }

    this.start = function(cb, appPrefix, aG) {
        args = {};
        if( aG != null ) { args = eval(aG); }

        //
        // Create container
        //
        var appContainer = M.createContainer(appPrefix, 'ciniki_web_pagefiles', 'yes');
        if( appContainer == null ) {
            M.alert('App Error');
            return false;
        }

        if( args.file_id != null && args.file_id > 0 ) {
            this.edit.open(cb, args.file_id);
        } else if( args.page_id != null && args.page_id > 0 ) {
            this.add.open(cb, args.page_id);
        } else {
            M.alert('Invalid request');
        }
    }
}
