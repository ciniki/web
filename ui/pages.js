//
// This app will handle the listing, additions and deletions of events.  These are associated business.
//
function ciniki_web_pages() {
	//
	// Panels
	//
	this.childFormat = {
		'5':{'name':'List'},
//		'6':{'name':'Menu'},
//		'32':{'name':'List'},
		};
	this.parentChildrenFormat = {
		'5':{'name':'List'},
		'6':{'name':'Menu'},
//		'32':{'name':'List'},
		};
	this.init = function() {
	}

	this.createEditPanel = function(cb, pid, parent_id, rsp) {
		var pn = 'edit_' + pid;
		//
		// Check if panel already exists, and reset for use
		//
		if( this.pn == null ) {
			//
			// The panel to display the edit form
			//
			this[pn] = new M.panel('Page',
				'ciniki_web_pages', pn,
				'mc', 'medium mediumaside', 'sectioned', 'ciniki.web.pages.' + pn);
			this[pn].data = {};	
			this[pn].stackedData = [];
			this[pn].page_id = pid;
			this[pn].sections = {
				'_image':{'label':'', 'aside':'yes', 'fields':{
					'primary_image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 
						'controls':'all', 'history':'no', 
						'addDropImage':function(iid) {
							M.ciniki_web_pages[pn].setFieldValue('primary_image_id', iid, null, null);
							return true;
							},
						'addDropImageRefresh':'',
						'deleteImage':'M.ciniki_web_pages.'+pn+'.deletePrimaryImage',
						},
				}},
				'_image_caption':{'label':'', 'aside':'yes', 'fields':{
					'primary_image_caption':{'label':'Caption', 'type':'text'},
	//				'primary_image_url':{'label':'URL', 'type':'text'},
				}},
				'details':{'label':'', 'aside':'yes', 'fields':{
					'parent_id':{'label':'Parent Page', 'type':'select', 'options':{}},
					'title':{'label':'Title', 'type':'text'},
					'sequence':{'label':'Page Order', 'type':'text', 'size':'small'},
					'_flags_1':{'label':'Visible', 'type':'flagtoggle', 'bit':0x01, 'field':'flags_1', 'default':'on'},
				}},
				'_synopsis':{'label':'Synopsis', 'fields':{
					'synopsis':{'label':'', 'type':'textarea', 'size':'small', 'hidelabel':'yes'},
				}},
				'_content':{'label':'Content', 'fields':{
					'content':{'label':'', 'type':'textarea', 'size':'large', 'hidelabel':'yes'},
				}},
				'files':{'label':'Files', 'aside':'yes',
					'type':'simplegrid', 'num_cols':1,
					'headerValues':null,
					'cellClasses':[''],
					'addTxt':'Add File',
					'addFn':'M.ciniki_web_pages.'+pn+'.editComponent(\'ciniki.web.pagefiles\',\'M.ciniki_web_pages.'+pn+'.updateFiles();\',{\'file_id\':\'0\'});',
					},
				'images':{'label':'Gallery', 'aside':'yes', 'type':'simplethumbs'},
				'_images':{'label':'', 'aside':'yes', 'type':'simplegrid', 'num_cols':1,
					'addTxt':'Add Image',
					'addFn':'M.ciniki_web_pages.'+pn+'.editComponent(\'ciniki.web.pageimages\',\'M.ciniki_web_pages.'+pn+'.addDropImageRefresh();\',{\'add\':\'yes\'});',
					},
				'_children':{'label':'Child Pages', 'fields':{
					'child_title':{'label':'Heading', 'type':'text'},
					'child_format':{'label':'Format', 'active':'yes', 'type':'flags', 'toggle':'yes', 'none':'no', 'join':'yes', 'flags':this.childFormat},
				}},
				'pages':{'label':'', 'type':'simplegrid', 'num_cols':1, 
					'addTxt':'Add Child Page',
					'addFn':'M.ciniki_web_pages.'+pn+'.childEdit(0);',
					},
				'_buttons':{'label':'', 'buttons':{
					'save':{'label':'Save', 'fn':'M.ciniki_web_pages.'+pn+'.savePage();'},
					'delete':{'label':'Delete', 'fn':'M.ciniki_web_pages.'+pn+'.deletePage();'},
				}},
			};
			this[pn].fieldHistoryArgs = function(s, i) {
				return {'method':'ciniki.web.pageHistory', 'args':{'business_id':M.curBusinessID,
					'page_id':this.page_id, 'field':i}};
			};
			this[pn].sectionData = function(s) { 
				return this.data[s];
			};
			this[pn].fieldValue = function(s, i, j, d) {
				return this.data[i];
			};
			this[pn].cellValue = function(s, i, j, d) {
				if( s == 'pages' ) {
					return d.page.title;
				} else if( s == 'files' ) {
					return d.file.name;
				}
			};
			this[pn].rowFn = function(s, i, d) {
				if( s == 'pages' ) {
	//				return 'M.ciniki_web_pages.pageEdit(\'M.ciniki_web_pages.updateChildren();\',\'' + d.page.id + '\',0);';
					return 'M.ciniki_web_pages.'+pn+'.childEdit(\'' + d.page.id + '\');';
				} else if( s == 'files' ) {
					return 'M.startApp(\'ciniki.web.pagefiles\',null,\'M.ciniki_web_pages.'+pn+'.updateFiles();\',\'mc\',{\'file_id\':\'' + d.file.id + '\'});';
				}
			};
			this[pn].thumbFn = function(s, i, d) {
				return 'M.startApp(\'ciniki.web.pageimages\',null,\'M.ciniki_web_pages.'+pn+'.addDropImageRefresh();\',\'mc\',{\'page_id\':M.ciniki_web_pages.'+pn+'.page_id,\'page_image_id\':\'' + d.image.id + '\'});';
			};
			this[pn].deletePrimaryImage = function(fid) {
				this.setFieldValue(fid, 0, null, null);
				return true;
			};
			this[pn].addDropImage = function(iid) {
				if( this.page_id == 0 ) {
					var c = this.serializeForm('yes');
					var rsp = M.api.postJSON('ciniki.web.pageAdd', 
						{'business_id':M.curBusinessID}, c);
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					}
					this.page_id = rsp.id;
				}
				var rsp = M.api.getJSON('ciniki.web.pageImageAdd', 
					{'business_id':M.curBusinessID, 'image_id':iid, 'page_id':this.page_id});
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				}
				return true;
			};
			this[pn].addDropImageRefresh = function() {
				if( M.ciniki_web_pages[pn].page_id > 0 ) {
					M.api.getJSONCb('ciniki.web.pageGet', {'business_id':M.curBusinessID, 
						'page_id':M.ciniki_web_pages[pn].page_id, 'images':'yes'}, function(rsp) {
							if( rsp.stat != 'ok' ) {
								M.api.err(rsp);
								return false;
							}
							var p = M.ciniki_web_pages[pn];
							p.data.images = rsp.page.images;
							p.refreshSection('images');
							p.show();
						});
				}
				return true;
			};
			this[pn].editComponent = function(a,cb,args) {
				if( this.page_id == 0 ) {
					var p = this;
					var c = this.serializeFormData('yes');
					M.api.postJSONFormData('ciniki.web.pageAdd', 
						{'business_id':M.curBusinessID}, c, function(rsp) {
							if( rsp.stat != 'ok' ) {
								M.api.err(rsp);
								return false;
							}
							p.page_id = rsp.id;
							args['page_id'] = rsp.id;
							M.startApp(a,null,cb,'mc',args);
						});
				} else {
					args['page_id'] = this.page_id;
					M.startApp(a,null,cb,'mc',args);
				}
			};

			this[pn].updateFiles = function() {
				if( this.page_id > 0 ) {
					M.api.getJSONCb('ciniki.web.pageGet', {'business_id':M.curBusinessID, 
						'page_id':this.page_id, 'files':'yes'}, function(rsp) {
							if( rsp.stat != 'ok' ) {
								M.api.err(rsp);
								return false;
							}
							var p = M.ciniki_web_pages[pn];
							p.data.files = rsp.page.files;
							p.refreshSection('files');
							p.show();
						});
				}
				return true;
			};

			this[pn].updateChildren = function() {
				if( this.page_id > 0 ) {
					M.api.getJSONCb('ciniki.web.pageGet', {'business_id':M.curBusinessID, 
						'page_id':this.page_id, 'children':'yes'}, function(rsp) {
							if( rsp.stat != 'ok' ) {
								M.api.err(rsp);
								return false;
							}
							var p = M.ciniki_web_pages[pn];
							p.data.pages = rsp.page.pages;
							p.refreshSection('pages');
							p.show();
						});
				}
				return true;
			};

			this[pn].childEdit = function(cid) {
				if( this.page_id == 0 ) {
					// Save existing data as new page
					var p = this;
					var c = this.serializeFormData('yes');
					M.api.postJSONFormData('ciniki.web.pageAdd', 
						{'business_id':M.curBusinessID}, c, function(rsp) {
							if( rsp.stat != 'ok' ) {
								M.api.err(rsp);
								return false;
							}
							p.page_id = rsp.id;
							M.ciniki_web_pages.pageEdit('M.ciniki_web_pages.'+pn+'.updateChildren();',cid,p.page_id);
						});
				} else {
					M.ciniki_web_pages.pageEdit('M.ciniki_web_pages.'+pn+'.updateChildren();',cid,this.page_id);
				}
			};
			this[pn].addButton('save', 'Save', 'M.ciniki_web_pages.'+pn+'.savePage();');
			this[pn].addClose('Cancel');
			this[pn].savePage = function() {
				var p = this;
				var flags = this.formValue('child_format');
				if( this.formValue('_flags_1') == 'on' ) {
					flags |= 0x01;
				} else {
					flags &= ~0x01;
				}
				if( this.page_id > 0 ) {
					var c = this.serializeFormData('no');
					if( c != null || flags != this.data.flags ) {
						if( c == null ) { c = new FormData; }
						if( flags != this.data.flags ) {
							c.append('flags', flags);
						}
						M.api.postJSONFormData('ciniki.web.pageUpdate', 
							{'business_id':M.curBusinessID, 'page_id':this.page_id}, c, function(rsp) {
								if( rsp.stat != 'ok' ) {
									M.api.err(rsp);
									return false;
								}
								p.close();
							});
					} else {
						this.close();
					}
				} else {
					var c = this.serializeFormData('yes');
					c.append('flags', flags);
					M.api.postJSONFormData('ciniki.web.pageAdd', 
						{'business_id':M.curBusinessID}, c, function(rsp) {
							if( rsp.stat != 'ok' ) {
								M.api.err(rsp);
								return false;
							}
							p.close();
						});
				}
			};
			this[pn].pageDelete = function() {
				var p = this;
				if( confirm('Are you sure you want to delete this page? All files and images will also be removed from this page.') ) {
					var rsp = M.api.getJSONCb('ciniki.web.pageDelete', {'business_id':M.curBusinessID, 
						'page_id':this.page_id}, function(rsp) {
							if( rsp.stat != 'ok' ) {
								M.api.err(rsp);
								return false;
							}
							p.close();
						});
				}
			};
		}

//		this[pn].sections.details.fields.parent_id.options = {'0':'None'};
		if( rsp.parentlist != null && rsp.parentlist.length > 0 ) {
			this[pn].sections.details.fields.parent_id.active = 'yes';
			this[pn].sections.details.fields.parent_id.options[0] = 'None';
			for(i in rsp.parentlist) {
				if( rsp.parentlist[i].page.id != this[pn].page_id ) {
					this[pn].sections.details.fields.parent_id.options[rsp.parentlist[i].page.id] = rsp.parentlist[i].page.title;
				}
			}
		} else {
			this[pn].sections.details.fields.parent_id.active = 'no';
		}
		this[pn].data = rsp.page;
		// Remove child_format flags
		this[pn].data.flags_1 = (rsp.page.flags&0xFFFFFF0F);
		this[pn].data.child_format = (rsp.page.flags&0x000000F0);
		this[pn].sections.details.fields.parent_id.active = 'yes';
		if( this[pn].page_id == 0 && parent_id != null ) {
			this[pn].data.parent_id = parent_id;
			if( parent_id == 0 ) {
				this[pn].data.title = '';
			}
		}
		if( this[pn].data.parent_id == 0 ) {
			this[pn].sections._children.fields.child_format.flags = this.parentChildrenFormat;
		} else {
			this[pn].sections._children.fields.child_format.flags = this.childFormat;
		}
		this[pn].refresh();
		this[pn].show(cb);
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
		var appContainer = M.createContainer(appPrefix, 'ciniki_web_pages', 'yes');
		if( appContainer == null ) {
			alert('App Error');
			return false;
		} 

		this.pageEdit(cb, args.page_id, args.parent_id);	
	}

	this.pageEdit = function(cb, pid, parent_id) {
		M.api.getJSONCb('ciniki.web.pageGet', {'business_id':M.curBusinessID,
			'page_id':pid, 'images':'yes', 'files':'yes', 
				'children':'yes', 'parentlist':'yes'}, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				}
				M.ciniki_web_pages.createEditPanel(cb, pid, parent_id, rsp);	
			});
	};
};
