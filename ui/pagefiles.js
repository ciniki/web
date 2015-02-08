//
// The files manager for web pages
//
function ciniki_web_pagefiles() {
	this.init = function() {
		//
		// The panel to display the add form
		//
		this.add = new M.panel('Add File',
			'ciniki_web_pagefiles', 'add',
			'mc', 'medium', 'sectioned', 'ciniki.web.pagefiles.edit');
		this.add.default_data = {'type':'20'};
		this.add.data = {};	
		this.add.sections = {
			'_file':{'label':'File', 'fields':{
				'uploadfile':{'label':'', 'type':'file', 'hidelabel':'yes'},
			}},
			'info':{'label':'Information', 'type':'simpleform', 'fields':{
				'name':{'label':'Title', 'type':'text'},
			}},
			'_save':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_web_pagefiles.addFile();'},
			}},
		};
		this.add.fieldValue = function(s, i, d) { 
			if( this.data[i] != null ) {
				return this.data[i]; 
			} 
			return ''; 
		};
		this.add.addButton('save', 'Save', 'M.ciniki_web_pagefiles.addFile();');
		this.add.addClose('Cancel');

		//
		// The panel to display the edit form
		//
		this.edit = new M.panel('File',
			'ciniki_web_pagefiles', 'edit',
			'mc', 'medium', 'sectioned', 'ciniki.web.pagefiles.edit');
		this.edit.file_id = 0;
		this.edit.data = null;
		this.edit.sections = {
			'info':{'label':'Details', 'type':'simpleform', 'fields':{
				'name':{'label':'Title', 'type':'text'},
			}},
			'_save':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_web_pagefiles.saveFile();'},
				'download':{'label':'Download', 'fn':'M.ciniki_web_pagefiles.downloadFile(M.ciniki_web_pagefiles.edit.file_id);'},
				'delete':{'label':'Delete', 'fn':'M.ciniki_web_pagefiles.deleteFile();'},
			}},
		};
		this.edit.fieldValue = function(s, i, d) { 
			return this.data[i]; 
		}
		this.edit.sectionData = function(s) {
			return this.data[s];
		};
		this.edit.fieldHistoryArgs = function(s, i) {
			return {'method':'ciniki.web.pageFileHistory', 'args':{'business_id':M.curBusinessID, 
				'file_id':this.file_id, 'field':i}};
		};
		this.edit.addButton('save', 'Save', 'M.ciniki_web_pagefiles.saveFile();');
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
			alert('App Error');
			return false;
		}

		if( args.file_id != null && args.file_id > 0 ) {
			this.showEditFile(cb, args.file_id);
		} else if( args.page_id != null && args.page_id > 0 ) {
			this.showAddFile(cb, args.page_id);
		} else {
			alert('Invalid request');
		}
	}

	this.showMenu = function(cb) {
		this.menu.refresh();
		this.menu.show(cb);
	};

	this.showAddFile = function(cb, eid) {
		this.add.reset();
		this.add.data = {'name':''};
		this.add.file_id = 0;
		this.add.page_id = eid;
		this.add.refresh();
		this.add.show(cb);
	};

	this.addFile = function() {
		var c = this.add.serializeFormData('yes');

		if( c != '' ) {
			var rsp = M.api.postJSONFormData('ciniki.web.pageFileAdd', 
				{'business_id':M.curBusinessID, 'page_id':M.ciniki_web_pagefiles.add.page_id}, c,
					function(rsp) {
						if( rsp.stat != 'ok' ) {
							M.api.err(rsp);
							return false;
						} else {
							var p = M.ciniki_web_pagefiles.add;
							p.file_id = rsp.id;
							p.close();
						}
					});
		} else {
			M.ciniki_web_pagefiles.add.close();
		}
	};

	this.showEditFile = function(cb, fid) {
		if( fid != null ) {
			this.edit.file_id = fid;
		}
		var rsp = M.api.getJSONCb('ciniki.web.pageFileGet', {'business_id':M.curBusinessID, 
			'file_id':this.edit.file_id}, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				}
				var p = M.ciniki_web_pagefiles.edit;
				p.data = rsp.file;
				p.refresh();
				p.show(cb);
			});
	};

	this.saveFile = function() {
		var c = this.edit.serializeFormData('no');

		if( c != '' ) {
			var rsp = M.api.postJSONFormData('ciniki.web.pageFileUpdate', 
				{'business_id':M.curBusinessID, 'file_id':this.edit.file_id}, c,
					function(rsp) {
						if( rsp.stat != 'ok' ) {
							M.api.err(rsp);
							return false;
						} else {
							M.ciniki_web_pagefiles.edit.close();
						}
					});
		}
	};

	this.deleteFile = function() {
		if( confirm('Are you sure you want to delete \'' + this.edit.data.name + '\'?  All information about it will be removed and unrecoverable.') ) {
			var rsp = M.api.getJSONCb('ciniki.web.pageFileDelete', {'business_id':M.curBusinessID, 
				'file_id':M.ciniki_web_pagefiles.edit.file_id}, function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					} 
					M.ciniki_web_pagefiles.edit.close();
				});
		}
	};

	this.downloadFile = function(fid) {
		M.api.openFile('ciniki.web.pageFileDownload', {'business_id':M.curBusinessID, 'file_id':fid});
	};
}
