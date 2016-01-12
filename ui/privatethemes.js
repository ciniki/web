//
// The app to manage web options for a business
//
function ciniki_web_privatethemes() {
	this.init = function() {
		//
		// Global functions for history and field value
		//
		this.fieldValue = function(s, i, d) { 
			if( this.data[i] == null ) { return ''; }
			return this.data[i]; 
		};

		//
		// The options and information for the theme page
		//
		this.menu = new M.panel('Themes',
			'ciniki_web_privatethemes', 'menu',
			'mc', 'medium', 'sectioned', 'ciniki.web.privatethemes.menu');
		this.menu.data = {};
		this.menu.sections = {
			'themes':{'label':'Themes', 'type':'simplegrid', 'num_cols':2,
				'headerValues':null,
				'addTxt':'Add Theme',
				'addFn':'M.ciniki_web_privatethemes.themeEdit(\'M.ciniki_web_privatethemes.showMenu();\',0);',
				},
			};
		this.menu.cellValue = function(s, i, j, d) {
			switch(j) {
				case 0: return d.theme.name;
				case 1: return d.theme.status_text;
			}
		};
		this.menu.rowFn = function(s, i, d) {
			return 'M.ciniki_web_privatethemes.themeEdit(\'M.ciniki_web_privatethemes.showMenu();\',' + d.theme.id + ');';
		};
		this.menu.sectionData = function(s) {
			return this.data[s];
		};
		this.menu.addButton('save', 'Save', 'M.ciniki_web_privatethemes.themeSave();');
		this.menu.addClose('Back');

		//
		// the edit panel
		//
		this.edit = new M.panel('Theme',
			'ciniki_web_privatethemes', 'edit',
			'mc', 'medium mediumaside', 'sectioned', 'ciniki.web.privatethemes.edit');
		this.edit.theme_id = 0;
		this.edit.data = {};
		this.edit.additional_images = [];
		this.edit.sections = {
			'info':{'label':'', 'aside':'yes', 'fields':{
				'name':{'label':'Name', 'type':'text'},
				'status':{'label':'Status', 'type':'toggle', 'toggles':{'10':'Active', '50':'Inactive'}},
				}},
			'header':{'label':'Header', 'aside':'yes', 'fields':{
				'header-social-icons':{'label':'Social Icon Font', 'type':'toggle', 'toggles':{'MonoSocial':'Mono Social', 'FontAwesome':'Font Awesome'}},
				'header-signin-button':{'label':'Signin Button', 'type':'toggle', 'default':'no', 'toggles':{'no':'No', 'yes':'Yes'}},
				'header-article-title':{'label':'Article Title', 'type':'toggle', 'default':'no', 'toggles':{'no':'No', 'yes':'Yes'}},
				'header-breadcrumbs':{'label':'Breadcrumbs', 'type':'toggle', 'default':'no', 'toggles':{'no':'No', 'yes':'Yes'}},
				}},
			'body':{'label':'Content', 'aside':'yes', 'fields':{
				'share-social-icons':{'label':'Social Icon Font', 'type':'toggle', 'toggles':{'MonoSocial':'Mono Social', 'FontAwesome':'Font Awesome'}},
				}},
			'footer':{'label':'Footer', 'aside':'yes', 'fields':{
				'footer-layout':{'label':'Layout', 'type':'select', 'options':{
					'social-links-copyright':'Social - Links - Copyright', 
					'copyright-links-social':'Copyright - Links - Social',
					'copyright-links':'Copyright - Links',
					}},
				'footer-social-icons':{'label':'Social Icon Font', 'type':'toggle', 'toggles':{'MonoSocial':'Mono Social', 'FontAwesome':'Font Awesome'}},
				'footer-copyright-message':{'label':'Copyright Text', 'type':'text'},
				// FIXME: Add ability to link to info page
//				'footer-privacy-policy':{'label':'Privacy Policy', 'type':'toggle', 'toggles':{'no':'No', 'link':'Link', 'popup':'Popup'}},
				'footer-privacy-policy':{'label':'Privacy Policy', 'active':'no', 'type':'toggle', 'toggles':{'no':'No', 'popup':'Popup'}},
				'footer-subscription-agreement':{'label':'Subscription Agreement', 'active':'no', 'type':'toggle', 'toggles':{'no':'No', 'popup':'Popup'}},
				}},
			'csshref':{'label':'Remote CSS', 'aside':'yes', 'type':'simplegrid', 'num_cols':2,
				'addTxt':'Add CSS',
				'addFn':'M.ciniki_web_privatethemes.contentEdit(\'M.ciniki_web_privatethemes.edit.updateCSSHREF();\',0,M.ciniki_web_privatethemes.edit.theme_id,\'csshref\');',
				},
			'css':{'label':'CSS', 'type':'simplegrid', 'num_cols':2,
				'addTxt':'Add CSS',
				'addFn':'M.ciniki_web_privatethemes.contentEdit(\'M.ciniki_web_privatethemes.edit.updateCSS();\',0,M.ciniki_web_privatethemes.edit.theme_id,\'css\');',
				},
			'js':{'label':'Javascript', 'type':'simplegrid', 'num_cols':2,
				'addTxt':'Add Javascript',
				'addFn':'M.ciniki_web_privatethemes.contentEdit(\'M.ciniki_web_privatethemes.edit.updateJS();\',0,M.ciniki_web_privatethemes.edit.theme_id,\'js\');',
				},
			'images':{'label':'Images', 'type':'simplethumbs'},
			'_images':{'label':'', 'type':'simplegrid', 'num_cols':1,
				'addTxt':'Add Image',
				'addFn':'M.ciniki_web_privatethemes.imageEdit(\'M.ciniki_web_privatethemes.edit.addDropImageRefresh();\',0,M.ciniki_web_privatethemes.edit.theme_id);',
				},
			'_buttons':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_web_privatethemes.themeSave();'},
				'delete':{'label':'Delete', 'fn':'M.ciniki_web_privatethemes.themeDelete();'},
				}},
		};
		this.edit.fieldHistoryArgs = function(s, i) {
			return {'method':'ciniki.web.privateThemeHistory', 'args':{'business_id':M.curBusinessID, 
				'theme_id':this.theme_id, 'field':i}};
		}
		this.edit.fieldValue = this.fieldValue;
		this.edit.sectionData = function(s) {
			return this.data[s];
		};
		this.edit.cellValue = function(s, i, j, d) {
			switch(j) {
				case 0: return d.content.name;
				case 1: return d.content.status_text;
			}
		};
		this.edit.rowFn = function(s, i, d) {
			if( s == 'csshref' ) {
				return 'M.ciniki_web_privatethemes.contentEdit(\'M.ciniki_web_privatethemes.edit.updateCSSHREF();\',\'' + d.content.id + '\');';
			} else if( s == 'css' ) {
				return 'M.ciniki_web_privatethemes.contentEdit(\'M.ciniki_web_privatethemes.edit.updateCSS();\',\'' + d.content.id + '\');';
			} else if( s == 'js' ) {
				return 'M.ciniki_web_privatethemes.contentEdit(\'M.ciniki_web_privatethemes.edit.updateJS();\',\'' + d.content.id + '\');';
			}
		};
		this.edit.addDropImage = function(iid) {
			if( M.ciniki_web_privatethemes.edit.theme_id > 0 ) {
				var rsp = M.api.getJSON('ciniki.web.privateThemeImageAdd', 
					{'business_id':M.curBusinessID, 'image_id':iid, 'theme_id':M.ciniki_web_privatethemes.edit.theme_id});
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				}
				return true;
			} else {
				M.ciniki_web_privatethemes.edit.additional_images.push(iid);
				return true;
			}
		};
		this.edit.addDropImageRefresh = function() {
			if( M.ciniki_web_privatethemes.edit.theme_id > 0 ) {
				M.api.getJSONCb('ciniki.web.privateThemeGet', {'business_id':M.curBusinessID, 
					'theme_id':M.ciniki_web_privatethemes.edit.theme_id, 'images':'yes'}, function(rsp) {
						if( rsp.stat != 'ok' ) {
							M.api.err(rsp);
							return false;
						}
						var p = M.ciniki_web_privatethemes.edit;
						p.data.images = rsp.theme.images;
						p.refreshSection('images');
						p.show();
					});
			} else if( M.ciniki_web_privatethemes.edit.additional_images.length > 0 ) {
				M.api.getJSONCb('ciniki.web.privateThemeImages', {'business_id':M.curBusinessID, 
					'images':M.ciniki_web_privatethemes.edit.additional_images.join(',')}, function(rsp) {
						if( rsp.stat != 'ok' ) {
							M.api.err(rsp);
							return false;
						}
						var p = M.ciniki_web_privatethemes.edit;
						p.data.images = rsp.images;
						p.refreshSection('images');
						p.show();
					});
				
			} else {
				var p = M.ciniki_web_privatethemes.edit;
				p.refresh();
				p.show();
			}
			return true;
		};
		this.edit.updateCSSHREF = function() {
			M.api.getJSONCb('ciniki.web.privateThemeGet', {'business_id':M.curBusinessID, 'theme_id':this.theme_id, 'content':'yes'}, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				}
				var p = M.ciniki_web_privatethemes.edit;
				p.data.csshref = (rsp.theme.csshref!=null?rsp.theme.csshref:[]);
				p.refreshSection('csshref');
				p.show();
			});
		};
		this.edit.updateCSS = function() {
			M.api.getJSONCb('ciniki.web.privateThemeGet', {'business_id':M.curBusinessID, 'theme_id':this.theme_id, 'content':'yes'}, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				}
				var p = M.ciniki_web_privatethemes.edit;
				p.data.css = (rsp.theme.css!=null?rsp.theme.css:[]);
				p.refreshSection('css');
				p.show();
			});
		};
		this.edit.updateJS = function() {
			M.api.getJSONCb('ciniki.web.privateThemeGet', {'business_id':M.curBusinessID, 'theme_id':this.theme_id, 'content':'yes'}, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				}
				var p = M.ciniki_web_privatethemes.edit;
				p.data.js = (rsp.theme.js!=null?rsp.theme.js:[]);
				p.refreshSection('js');
				p.show();
			});
		};

		this.edit.thumbFn = function(s, i, d) {
			return 'M.ciniki_web_privatethemes.imageEdit(\'M.ciniki_web_privatethemes.edit.addDropImageRefresh();\',\''+ d.image.id + '\',\'' + d.image.image_id + '\');';
		};
		this.edit.addButton('save', 'Save', 'M.ciniki_web_privatethemes.themeSave();');
		this.edit.addClose('Cancel');

		//
		// The content edit panel
		//
		this.content = new M.panel('Theme Content',
			'ciniki_web_privatethemes', 'content',
			'mc', 'xlarge', 'sectioned', 'ciniki.web.privatethemes.content');
		this.content.content_id = 0;
		this.content.theme_id = 0;
		this.content.data = {};
		this.content.sections = {
			'info':{'label':'', 'fields':{
				'name':{'label':'Name', 'type':'text'},
				'status':{'label':'Status', 'type':'toggle', 'toggles':{'10':'Active', '50':'Inactive'}},
				'sequence':{'label':'Sequence', 'type':'text', 'size':'small'},
				'content_type':{'label':'Type', 'type':'toggle', 'toggles':{'css':'CSS', 'csshref':'Remote CSS', 'js':'Javascript'}},
				'media':{'label':'Media', 'type':'toggle', 'toggles':{'all':'All', 'print':'Print'}},
				}},
			'_content':{'label':'Content', 'fields':{
				'content':{'label':'', 'type':'textarea', 'size':'xlarge', 'hidelabel':'yes'},
				}},
			'_buttons':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_web_privatethemes.contentSave();'},
				'saveexit':{'label':'Save & Back', 'fn':'M.ciniki_web_privatethemes.contentSave(\'yes\');'},
				'delete':{'label':'Delete', 'fn':'M.ciniki_web_privatethemes.contentDelete();'},
				}},
		};
		this.content.fieldHistoryArgs = function(s, i) {
			return {'method':'ciniki.web.privateThemeContentHistory', 'args':{'business_id':M.curBusinessID, 
				'content_id':this.content_id, 'field':i}};
		}
		this.content.fieldValue = this.fieldValue;
		this.content.sectionData = function(s) {
			return this.data[s];
		};
		this.content.addButton('save', 'Save', 'M.ciniki_web_privatethemes.contentSave();');
		this.content.addClose('Cancel');


		//
		// The image edit panel
		//
		this.image = new M.panel('Edit Image',
			'ciniki_web_privatethemes', 'image',
			'mc', 'medium', 'sectioned', 'ciniki.web.images.image');
		this.image.default_data = {};
		this.image.data = {};
		this.image.theme_id = 0;
		this.image.theme_image_id = 0;
		this.image.sections = {
			'_image':{'label':'Photo', 'type':'imageform', 'fields':{
				'image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 'controls':'all', 'history':'no'},
			}},
			'info':{'label':'', 'type':'simpleform', 'fields':{
				'name':{'label':'Filename', 'type':'text'},
			}},
			'_save':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_web_privatethemes.imageSave();'},
				'delete':{'label':'Delete', 'fn':'M.ciniki_web_privatethemes.imageDelete();'},
			}},
		};
		this.image.fieldValue = function(s, i, d) { 
			if( this.data[i] != null ) {
				return this.data[i]; 
			} 
			return ''; 
		};
		this.image.fieldHistoryArgs = function(s, i) {
			return {'method':'ciniki.web.privateThemeImageHistory', 'args':{'business_id':M.curBusinessID, 
				'theme_image_id':this.theme_image_id, 'field':i}};
		};
		this.image.addDropImage = function(iid) {
			M.ciniki_web_privatethemes.image.setFieldValue('image_id', iid, null, null);
			return true;
		};
		this.image.addButton('save', 'Save', 'M.ciniki_web_privatethemes.imageSave();');
		this.image.addClose('Cancel');
	}

	this.start = function(cb, ap, aG) {
		args = {};
		if( aG != null ) { args = eval(aG); }

		
		this.edit.sections.footer.fields['footer-privacy-policy'].active = M.modFlagSet('ciniki.info', 0x8000);
		this.edit.sections.footer.fields['footer-subscription-agreement'].active = M.modFlagSet('ciniki.info', 0x02000000);

		//
		// Create the app container if it doesn't exist, and clear it out
		// if it does exist.
		//
		var appContainer = M.createContainer(ap, 'ciniki_web_privatethemes', 'yes');
		if( appContainer == null ) {
			alert('App Error');
			return false;
		} 

		this.showMenu(cb);
	}

	this.showMenu = function(cb) {
		this.menu.reset();

		M.api.getJSONCb('ciniki.web.privateThemeList', {'business_id':M.curBusinessID}, function(rsp) {
			if( rsp.stat != 'ok' ) {
				M.api.err(rsp);
				return false;
			}
			var p = M.ciniki_web_privatethemes.menu;
			p.data = rsp;
			p.refresh();
			p.show(cb);
		});
	}

	this.themeEdit = function(cb, tid) {
		if( tid != null ) { this.edit.theme_id = tid; }
		if( this.edit.theme_id > 0 ) {
			this.edit.sections._buttons.buttons.delete.visible = 'yes';
			M.api.getJSONCb('ciniki.web.privateThemeGet', {'business_id':M.curBusinessID, 
				'theme_id':this.edit.theme_id, 'content':'yes', 'images':'yes', 'settings':'yes'}, function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					}
					var p = M.ciniki_web_privatethemes.edit;
					p.data = rsp.theme;
					p.refresh();
					p.show(cb);
				});
		} else {
			this.edit.theme_id = 0;
			this.edit.sections._buttons.buttons.delete.visible = 'no';
			this.edit.reset();
			this.edit.data = {
				'status':'10', 
				'header-social-font':'MonoSocial', 
				'footer-layout':'social-links-copyright',
				'footer-social-font':'MonoSocial',
				'footer-privacy-policy':'no',
				'footer-subscription-agreement':'no',
				};
			this.edit.additional_images = [];
			this.edit.refresh();
			this.edit.show(cb);
		}
	};

	this.themeSave = function() {
		if( this.edit.theme_id > 0 ) {
			var c = this.edit.serializeForm('no');
			if( c != '' ) {
				M.api.postJSONCb('ciniki.web.privateThemeUpdate', 
					{'business_id':M.curBusinessID, 'theme_id':this.edit.theme_id}, c, function(rsp) {
						if( rsp.stat != 'ok' ) {
							M.api.err(rsp);
							return false;
						} 
						M.ciniki_web_privatethemes.edit.close();
					});
			} else {
				this.edit.close();
			}
		} else {
			var name = this.edit.formValue('name');
			if( name == '' ) {
				alert('You must enter the name of the theme first');
				return false;
			}
			var c = this.edit.serializeForm('yes');
			if( this.edit.additional_images.length > 0 ) {
				c += '&images=' + this.edit.additional_images.join(',');
			}
			M.api.postJSONCb('ciniki.web.privateThemeAdd', {'business_id':M.curBusinessID}, c, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				} 
				M.ciniki_web_privatethemes.edit.close();
			});
		}
	};

	this.themeDelete = function() {
		if( confirm('Are you sure you want to this theme?') ) {
			var rsp = M.api.getJSONCb('ciniki.web.privateThemeDelete', {'business_id':M.curBusinessID, 'theme_id':this.edit.theme_id}, 
				function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					}
					M.ciniki_web_privatethemes.edit.close();
				});
		}
	};

	this.contentEdit = function(cb, cid, tid, ctype) {
		// Create the new theme first if required
		if( cid == 0 && tid != null && tid == 0 ) {
			var name = this.edit.formValue('name');
			if( name == '' ) {
				alert('You must enter the name of the theme first');
				return false;
			}
			var c = this.edit.serializeForm('yes');
			if( this.edit.additional_images.length > 0 ) {
				c += '&images=' + this.edit.additional_images.join(',');
			}
			M.api.postJSONCb('ciniki.web.privateThemeAdd', {'business_id':M.curBusinessID}, c, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				} 
				M.ciniki_web_privatethemes.contentEdit(cb,0,rsp.id,ctype);
			});
		}
		// Get the content to edit
		if( cid != null ) { this.content.content_id = cid; }
		var args = {'business_id':M.curBusinessID, 'content_id':this.content.content_id};
		if( this.content.content_id > 0 ) {
			this.content.sections._buttons.buttons.delete.visible = 'yes';
		} else {
			this.content.sections._buttons.buttons.delete.visible = 'no';
			args['theme_id'] = tid;
			this.content.theme_id = tid;
			args['content_type'] = ctype;
		}
		M.api.getJSONCb('ciniki.web.privateThemeContentGet', args, function(rsp) {
			if( rsp.stat != 'ok' ) {
				M.api.err(rsp);
				return false;
			}
			var p = M.ciniki_web_privatethemes.content;
			p.data = rsp.content;
			p.refresh();
			p.show(cb);
		});
	};

	this.contentSave = function(ef) {
		if( this.content.content_id > 0 ) {
			var c = this.content.serializeForm('no');
			if( c != '' ) {
				M.api.postJSONCb('ciniki.web.privateThemeContentUpdate', 
					{'business_id':M.curBusinessID, 'content_id':this.content.content_id}, c, function(rsp) {
						if( rsp.stat != 'ok' ) {
							M.api.err(rsp);
							return false;
						} 
						if( ef != null && ef == 'yes' ) {
							M.ciniki_web_privatethemes.content.close();
						}
					});
			} else if( ef != null && ef == 'yes' ) {
				this.content.close();
			}
		} else {
			var name = this.content.formValue('name');
			if( name == '' ) {
				alert('You must enter the name of the theme first');
				return false;
			}
			var c = this.content.serializeForm('yes');
			c += '&theme_id=' + this.content.theme_id;
			M.api.postJSONCb('ciniki.web.privateThemeContentAdd', {'business_id':M.curBusinessID, 'theme_id':this.content.theme_id}, c, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				} 
				M.ciniki_web_privatethemes.content.close();
			});
		}
	};

	this.contentDelete = function() {
		if( confirm('Are you sure you want to this content?') ) {
			var rsp = M.api.getJSONCb('ciniki.web.privateThemeContentDelete', {'business_id':M.curBusinessID, 'content_id':this.content.content_id}, 
				function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					}
					M.ciniki_web_privatethemes.content.close();
				});
		}
	};

	this.imageEdit = function(cb, iid, tid) {
		if( iid != null ) { this.image.theme_image_id = iid; }
		if( tid != null ) { this.image.theme_id = tid; }
		if( this.image.theme_image_id > 0 ) {
			var rsp = M.api.getJSONCb('ciniki.web.privateThemeImageGet', 
				{'business_id':M.curBusinessID, 'theme_image_id':this.image.theme_image_id}, function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					}
					var p = M.ciniki_web_privatethemes.image;
					p.data = rsp.image;
					p.refresh();
                    p.show(cb);
//					p.show('M.ciniki_web_privatethemes.edit.addDropImageRefresh();');
				});
		} else {
			this.image.reset();
			this.image.data = {};
			this.image.refresh();
            this.image.show(cb);
//			this.image.show('M.ciniki_web_privatethemes.edit.addDropImageRefresh();');
		}
	};

	this.imageSave = function() {
		if( this.image.theme_image_id > 0 ) {
			var c = this.image.serializeFormData('no');
			if( c != '' ) {
				var rsp = M.api.postJSONFormData('ciniki.web.privateThemeImageUpdate', 
					{'business_id':M.curBusinessID, 
					'theme_image_id':this.image.theme_image_id}, c,
						function(rsp) {
							if( rsp.stat != 'ok' ) {
								M.api.err(rsp);
								return false;
							} else {
								M.ciniki_web_privatethemes.image.close();
							}
						});
			} else {
				this.image.close();
			}
		} else {
			var c = this.image.serializeFormData('yes');
			var rsp = M.api.postJSONFormData('ciniki.web.privateThemeImageAdd', 
				{'business_id':M.curBusinessID, 'theme_id':this.image.theme_id}, c,
					function(rsp) {
						if( rsp.stat != 'ok' ) {
							M.api.err(rsp);
							return false;
						} else {
							M.ciniki_web_privatethemes.image.close();
						}
					});
		}
	};

	this.imageDelete = function() {
		if( confirm('Are you sure you want to delete this image?') ) {
			var rsp = M.api.getJSONCb('ciniki.web.privateThemeImageDelete', {'business_id':M.curBusinessID, 
				'theme_image_id':this.image.theme_image_id}, function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					}
					M.ciniki_web_privatethemes.image.close();
				});
		}
	};
};
