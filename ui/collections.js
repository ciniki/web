//
// The app to manage web options for a business
//
function ciniki_web_collections() {
	this.collectionStatuses = {
		'10':'Active',
		'20':'Hidden',
		'60':'Deleted',
		};
	this.init = function() {
		//
		// Global functions for history and field value
		//
		this.fieldHistoryArgs = function(s, i) {
			return {'method':'ciniki.web.pageSettingsHistory','args':{'business_id':M.curBusinessID, 'field':i}};
		}
		this.fieldValue = function(s, i, d) { 
			if( this.data[i] == null ) { return ''; }
			return this.data[i]; 
		};

		//
		// The options and information for the collection page
		//
		this.main = new M.panel('Collections',
			'ciniki_web_collections', 'main',
			'mc', 'medium', 'sectioned', 'ciniki.web.collections.main');
		this.main.data = {};
		this.main.sections = {
			'collections':{'label':'Collections', 'type':'simplegrid', 'num_cols':1,
				'headerValues':null,
				'addTxt':'Add Collection',
				'addFn':'M.ciniki_web_collections.editCollection(\'M.ciniki_web_collections.showMain();\',0);',
				},
			};
		this.main.cellValue = function(s, i, j, d) {
			return d.collection.name;
		};
		this.main.rowFn = function(s, i, d) {
			return 'M.ciniki_web_collections.editCollection(\'M.ciniki_web_collections.showMain();\',' + d.collection.id + ');';
		};
		this.main.sectionData = function(s) {
			return this.data[s];
		};
		this.main.fieldHistoryArgs = this.fieldHistoryArgs;
		this.main.addButton('save', 'Save', 'M.ciniki_web_collections.saveCollection();');
		this.main.addClose('Back');

		//
		// the edit panel
		//
		this.edit = new M.panel('Collection',
			'ciniki_web_collections', 'edit',
			'mc', 'medium mediumaside', 'sectioned', 'ciniki.web.collections.edit');
		this.edit.collection_id = 0;
		this.edit.data = {};
		this.edit.sections = {
			'_image':{'label':'', 'aside':'yes', 'type':'imageform', 'fields':{
				'image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 'controls':'all', 'history':'no'},
			}},
			'_image_caption':{'label':'', 'aside':'yes', 'fields':{
				'image_caption':{'label':'Caption', 'type':'text'},
			}},
			'info':{'label':'', 'aside':'yes', 'fields':{
				'status':{'label':'Status', 'default':'10', 'type':'toggle', 'toggles':this.collectionStatuses},
				'name':{'label':'Name', 'type':'text'},
				'sequence':{'label':'Sequence', 'type':'text', 'size':'small'},
				}},
			'ciniki.blog':{'label':'Blog', 'active':'no', 'aside':'yes', 'fields':{
				'ciniki.blog.post-title':{'label':'Name', 'type':'text', 'hint':'Blog Posts'},
				'ciniki.blog.post-sequence':{'label':'Sequence', 'type':'text', 'size':'small'},
				'ciniki.blog.post-num_items':{'label':'Number of entries', 'type':'text', 'size':'small'},
				'ciniki.blog.post-more':{'label':'More', 'type':'text', 'hint':'... more blog posts'},
				}},
			'ciniki.artgallery':{'label':'Exhibitions', 'active':'no', 'aside':'yes', 'fields':{
				'ciniki.artgallery.exhibition-title':{'label':'Name', 'type':'text', 'hint':'Exhibitions'},
				'ciniki.artgallery.exhibition-sequence':{'label':'Sequence', 'type':'text', 'size':'small'},
				'ciniki.artgallery.exhibition-num_items':{'label':'Number of events', 'type':'text', 'size':'small'},
				'ciniki.artgallery.exhibition-more':{'label':'More', 'type':'text', 'hint':'... more exhibitions'},
				}},
			'ciniki.events':{'label':'Events', 'active':'no', 'aside':'yes', 'fields':{
				'ciniki.events.event-title':{'label':'Name', 'type':'text', 'hint':'Events'},
				'ciniki.events.event-sequence':{'label':'Sequence', 'type':'text', 'size':'small'},
				'ciniki.events.event-num_items':{'label':'Number of events', 'type':'text', 'size':'small'},
				'ciniki.events.event-more':{'label':'More', 'type':'text', 'hint':'... more events'},
				}},
			'ciniki.workshops':{'label':'Workshops', 'active':'no', 'aside':'yes', 'fields':{
				'ciniki.workshops.workshop-title':{'label':'Name', 'type':'text', 'hint':'Workshop'},
				'ciniki.workshops.workshop-sequence':{'label':'Sequence', 'type':'text', 'size':'small'},
				'ciniki.workshops.workshop-num_items':{'label':'Number of workshops', 'type':'text', 'size':'small'},
				'ciniki.workshops.workshop-more':{'label':'More', 'type':'text', 'hint':'... more workshops'},
				}},
			'_synopsis':{'label':'Synopsis', 'fields':{
				'synopsis':{'label':'', 'hidelabel':'yes', 'size':'small', 'hint':'', 'type':'textarea'},
			}},
			'_description':{'label':'Collection Introduction', 'fields':{
				'description':{'label':'', 'hidelabel':'yes', 'hint':'', 'size':'large', 'type':'textarea'},
			}},
			'_buttons':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_web_collections.saveCollection();'},
				'delete':{'label':'Delete', 'fn':'M.ciniki_web_collections.deleteCollection();'},
				}},
		};
		this.edit.fieldHistoryArgs = function(s, i) {
			return {'method':'ciniki.web.collectionHistory', 'args':{'business_id':M.curBusinessID, 
				'collection_id':this.collection_id, 'field':i}};
		}
		this.edit.addDropImage = function(iid) {
			this.setFieldValue('image_id', iid);
			return true;
		};
		this.edit.deleteImage = function(fid) {
			this.setFieldValue(fid, 0);
			return true;
		};
		this.edit.fieldValue = this.fieldValue;
		this.edit.sectionData = function(s) {
			return this.data[s];
		};
		this.edit.addButton('save', 'Save', 'M.ciniki_web_collections.saveCollection();');
		this.edit.addClose('Cancel');
	}

	this.start = function(cb, ap, aG) {
		args = {};
		if( aG != null ) { args = eval(aG); }

		//
		// Create the app container if it doesn't exist, and clear it out
		// if it does exist.
		//
		var appContainer = M.createContainer(ap, 'ciniki_web_collections', 'yes');
		if( appContainer == null ) {
			alert('App Error');
			return false;
		} 

		//
		// Determine which objects should be shown
		//
		for(i in this.edit.sections) {
			if( i.match(/\./) ) {
				this.edit.sections[i].active = 'no';
			}
		}
		for(i in M.curBusiness.modules) {
			if( this.edit.sections[i] != null ) {
				this.edit.sections[i].active = 'yes';
			}
		}

		this.showMain(cb);
	}

	this.showMain = function(cb) {
		this.main.reset();

		M.api.getJSONCb('ciniki.web.collectionList', {'business_id':M.curBusinessID}, function(rsp) {
			if( rsp.stat != 'ok' ) {
				M.api.err(rsp);
				return false;
			}
			var p = M.ciniki_web_collections.main;
			p.data = {'collections':rsp.collections};
			p.refresh();
			p.show(cb);
		});
	}

	this.editCollection = function(cb, sid) {
		if( sid != null ) { this.edit.collection_id = sid; }
		if( this.edit.collection_id > 0 ) {
			this.edit.sections._buttons.buttons.delete.visible = 'yes';
			M.api.getJSONCb('ciniki.web.collectionGet', {'business_id':M.curBusinessID, 
				'collection_id':this.edit.collection_id}, function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					}
					var p = M.ciniki_web_collections.edit;
					p.data = rsp.collection;
					p.refresh();
					p.show(cb);
				});
		} else {
			this.edit.collection_id = 0;
			this.edit.sections._buttons.buttons.delete.visible = 'no';
			this.edit.reset();
			this.edit.data = {'status':'10'};
			this.edit.refresh();
			this.edit.show(cb);
		}
	};

	this.saveCollection = function() {
		if( this.edit.collection_id > 0 ) {
			var c = this.edit.serializeForm('no');
			if( c != '' ) {
				M.api.postJSONCb('ciniki.web.collectionUpdate', 
					{'business_id':M.curBusinessID, 'collection_id':this.edit.collection_id}, c, function(rsp) {
						if( rsp.stat != 'ok' ) {
							M.api.err(rsp);
							return false;
						} 
						M.ciniki_web_collections.edit.close();
					});
			} else {
				this.edit.close();
			}
		} else {
			var name = this.edit.formValue('name');
			if( name == '' ) {
				alert('You must enter the name of the collection first');
				return false;
			}
			var c = this.edit.serializeForm('yes');
			M.api.postJSONCb('ciniki.web.collectionAdd', 
				{'business_id':M.curBusinessID}, c, function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					} 
					M.ciniki_web_collections.edit.close();
				});
		}
	};

	this.deleteCollection = function() {
		if( confirm('Are you sure you want to this collection?') ) {
			var rsp = M.api.getJSONCb('ciniki.web.collectionDelete', {'business_id':M.curBusinessID, 
				'collection_id':this.edit.collection_id}, 
				function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					}
					M.ciniki_web_collections.edit.close();
				});
		}
	};
};
