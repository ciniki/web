//
// The app to manage web options for a business
//
function ciniki_web_about() {
	
	this.activeToggles = {'no':'No', 'yes':'Yes'};
	
	this.userFlags = {
		'1':{'name':'Name'},
		'2':{'name':'Title'},
		'3':{'name':'Phone'},
		'4':{'name':'Cell'},
		'5':{'name':'Fax'},
		'6':{'name':'Email'},
		'7':{'name':'Bio'},
		};

	this.init = function() {
		//
		// Global functions for history and field value
		//
		this.fieldHistoryArgs = function(s, i) {
			return {'method':'ciniki.web.pageSettingsHistory', 'args':{'business_id':M.curBusinessID, 'field':i}};
		}
		this.fieldValue = function(s, i, d) { 
			if( this.data[i] == null ) { return ''; }
			return this.data[i]; 
		};

		//
		// The options and information for the about page
		//
		this.about = new M.panel('About',
			'ciniki_web_about', 'about',
			'mc', 'medium', 'sectioned', 'ciniki.web.about.about');
		this.about.data = {};
//		this.about.rotateImage = M.ciniki_web_about.rotateImage;
//		this.about.deleteImage = M.ciniki_web_about.deleteImage;
//		this.about.uploadImage = function(i) { return 'M.ciniki_web_about.uploadDropImagesAbout(\'' + i + '\');' };
//		this.about.uploadDropFn = function() { return M.ciniki_web_about.uploadDropImagesAbout; };
		this.about.sections = {
			'options':{'label':'', 'fields':{
				'page-about-active':{'label':'Display About Page', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				}},
			'subpages':{'label':'', 'list':{
				'aboutartiststatement':{'label':'Artist Statement', 'visible':'no', 'fn':'M.ciniki_web_about.showPage(\'M.ciniki_web_about.about.show();\',\'aboutartiststatement\');'},
				'aboutcv':{'label':'CV', 'visible':'no', 'fn':'M.ciniki_web_about.showPage(\'M.ciniki_web_about.about.show();\',\'aboutcv\');'},
				'aboutawards':{'label':'Awards', 'visible':'no', 'fn':'M.ciniki_web_about.showPage(\'M.ciniki_web_about.about.show();\',\'aboutawards\');'},
				'abouthistory':{'label':'History', 'visible':'no', 'fn':'M.ciniki_web_about.showPage(\'M.ciniki_web_about.about.show();\',\'abouthistory\');'},
				'aboutdonations':{'label':'Donations', 'visible':'yes', 'fn':'M.ciniki_web_about.showPage(\'M.ciniki_web_about.about.show();\',\'aboutdonations\');'},
				'aboutboardofdirectors':{'label':'Board of Directors', 'visible':'yes', 'fn':'M.ciniki_web_about.showPage(\'M.ciniki_web_about.about.show();\',\'aboutboardofdirectors\');'},
				'aboutmembership':{'label':'Membership', 'visible':'yes', 'fn':'M.ciniki_web_about.showPage(\'M.ciniki_web_about.about.show();\',\'aboutmembership\');'},
				}},
			'_image':{'label':'Image', 'fields':{
				'page-about-image':{'label':'', 'type':'image_id', 'controls':'all', 'hidelabel':'yes', 'history':'no'},
				}},
			'_image_caption':{'label':'', 'fields':{
				'page-about-image-caption':{'label':'Caption', 'type':'text'},
				}},
			'_content':{'label':'Content', 'fields':{
				'page-about-content':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'large'},
				}},
			'_users':{'label':'Business Employees', 'visible':'no', 'fields':{
				}},
			'_users_display':{'label':'', 'fields':{
				'page-about-bios-title':{'label':'Title', 'type':'text', 'hint':''},
				'page-about-bios-display':{'label':'Employee List', 'type':'multitoggle', 'default':'list', 'toggles':{'list':'2 Column', 'cilist':'3 Column'}, 'hint':''},
				}},
			'_save':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_web_about.savePage(\'about\');'},
				}},
		};
		this.about.fieldValue = this.fieldValue;
		this.about.fieldHistoryArgs = this.fieldHistoryArgs;
		this.about.addDropImage = function(iid) {
			this.setFieldValue('page-about-image', iid);
			return true;
		};
		this.about.deleteImage = function(fid) {
			this.setFieldValue(fid, 0);
			return true;
		};
		this.about.addButton('save', 'Save', 'M.ciniki_web_about.savePage(\'about\');');
		this.about.addClose('Cancel');

		//
		// The options and information for the about artist statement page
		//
		this.aboutartiststatement = new M.panel('Artist Statement',
			'ciniki_web_about', 'aboutartiststatement',
			'mc', 'medium', 'sectioned', 'ciniki.web.about.aboutartiststatement');
		this.aboutartiststatement.data = {};
		this.aboutartiststatement.sections = {
			'options':{'label':'', 'fields':{
				'page-aboutartiststatement-active':{'label':'Display Statement Page', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				}},
			'_image':{'label':'Image', 'fields':{
				'page-aboutartiststatement-image':{'label':'', 'type':'image_id', 'controls':'all', 'hidelabel':'yes', 'history':'no'},
				}},
			'_image_caption':{'label':'', 'fields':{
				'page-aboutartiststatement-image-caption':{'label':'Caption', 'type':'text'},
				}},
			'_content':{'label':'Content', 'fields':{
				'page-aboutartiststatement-content':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'large'},
				}},
			'_save':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_web_about.savePage(\'aboutartiststatement\');'},
				}},
		};
		this.aboutartiststatement.fieldValue = this.fieldValue;
		this.aboutartiststatement.fieldHistoryArgs = this.fieldHistoryArgs;
		this.aboutartiststatement.addDropImage = function(iid) {
			this.setFieldValue('page-aboutartiststatement-image', iid);
			return true;
		};
		this.aboutartiststatement.deleteImage = this.about.deleteImage;
		this.aboutartiststatement.addButton('save', 'Save', 'M.ciniki_web_about.savePage(\'aboutartiststatement\');');
		this.aboutartiststatement.addClose('Cancel');

		//
		// The options and information for the about CV page
		//
		this.aboutcv = new M.panel('CV',
			'ciniki_web_about', 'aboutcv',
			'mc', 'medium', 'sectioned', 'ciniki.web.about.aboutcv');
		this.aboutcv.data = {};
		this.aboutcv.sections = {
			'options':{'label':'', 'fields':{
				'page-aboutcv-active':{'label':'Display CV Page', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				}},
			'_image':{'label':'Image', 'fields':{
				'page-aboutcv-image':{'label':'', 'type':'image_id', 'controls':'all', 'hidelabel':'yes', 'history':'no'},
				}},
			'_image_caption':{'label':'', 'fields':{
				'page-aboutcv-image-caption':{'label':'Caption', 'type':'text'},
				}},
			'_content':{'label':'Content', 'fields':{
				'page-aboutcv-content':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'large'},
				}},
			'_save':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_web_about.savePage(\'aboutcv\');'},
				}},
		};
		this.aboutcv.fieldValue = this.fieldValue;
		this.aboutcv.fieldHistoryArgs = this.fieldHistoryArgs;
		this.aboutcv.addDropImage = function(iid) {
			this.setFieldValue('page-aboutcv-image', iid);
			return true;
		};
		this.aboutcv.deleteImage = this.about.deleteImage;
		this.aboutcv.addButton('save', 'Save', 'M.ciniki_web_about.savePage(\'aboutcv\');');
		this.aboutcv.addClose('Cancel');

		//
		// The options and information for the about artist statement page
		//
		this.aboutawards = new M.panel('Awards',
			'ciniki_web_about', 'aboutawards',
			'mc', 'medium', 'sectioned', 'ciniki.web.about.aboutawards');
		this.aboutawards.data = {};
		this.aboutawards.sections = {
			'options':{'label':'', 'fields':{
				'page-aboutawards-active':{'label':'Display Awards Page', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				}},
			'_image':{'label':'Image', 'fields':{
				'page-aboutawards-image':{'label':'', 'type':'image_id', 'controls':'all', 'hidelabel':'yes', 'history':'no'},
				}},
			'_image_caption':{'label':'', 'fields':{
				'page-aboutawards-image-caption':{'label':'Caption', 'type':'text'},
				}},
			'_content':{'label':'Content', 'fields':{
				'page-aboutawards-content':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'large'},
				}},
			'_save':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_web_about.savePage(\'aboutawards\');'},
				}},
		};
		this.aboutawards.fieldValue = this.fieldValue;
		this.aboutawards.fieldHistoryArgs = this.fieldHistoryArgs;
		this.aboutawards.addDropImage = function(iid) {
			this.setFieldValue('page-aboutawards-image', iid);
			return true;
		};
		this.aboutawards.deleteImage = this.about.deleteImage;
		this.aboutawards.addButton('save', 'Save', 'M.ciniki_web_about.savePage(\'aboutawards\');');
		this.aboutawards.addClose('Cancel');

		//
		// The options and information for the about history page
		//
		this.abouthistory = new M.panel('History',
			'ciniki_web_about', 'abouthistory',
			'mc', 'medium', 'sectioned', 'ciniki.web.about.abouthistory');
		this.abouthistory.data = {};
		this.abouthistory.sections = {
			'options':{'label':'', 'fields':{
				'page-abouthistory-active':{'label':'Display History Page', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				}},
			'_image':{'label':'Image', 'fields':{
				'page-abouthistory-image':{'label':'', 'type':'image_id', 'controls':'all', 'hidelabel':'yes', 'history':'no'},
				}},
			'_image_caption':{'label':'', 'fields':{
				'page-abouthistory-image-caption':{'label':'Caption', 'type':'text'},
				}},
			'_content':{'label':'Content', 'fields':{
				'page-abouthistory-content':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'large'},
				}},
			'_save':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_web_about.savePage(\'abouthistory\');'},
				}},
		};
		this.abouthistory.fieldValue = this.fieldValue;
		this.abouthistory.fieldHistoryArgs = this.fieldHistoryArgs;
		this.abouthistory.addDropImage = function(iid) {
			this.setFieldValue('page-abouthistory-image', iid);
			return true;
		};
		this.abouthistory.deleteImage = this.about.deleteImage;
		this.abouthistory.addButton('save', 'Save', 'M.ciniki_web_about.savePage(\'abouthistory\');');
		this.abouthistory.addClose('Cancel');

		//
		// The options and information for the about donations page
		//
		this.aboutdonations = new M.panel('Donations',
			'ciniki_web_about', 'aboutdonations',
			'mc', 'medium', 'sectioned', 'ciniki.web.about.aboutdonations');
		this.aboutdonations.data = {};
		this.aboutdonations.sections = {
			'options':{'label':'', 'fields':{
				'page-aboutdonations-active':{'label':'Display About Page', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				}},
			'_image':{'label':'Image', 'fields':{
				'page-aboutdonations-image':{'label':'', 'type':'image_id', 'controls':'all', 'hidelabel':'yes', 'history':'no'},
				}},
			'_image_caption':{'label':'', 'fields':{
				'page-aboutdonations-image-caption':{'label':'Caption', 'type':'text'},
				}},
			'_content':{'label':'Content', 'fields':{
				'page-aboutdonations-content':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'large'},
				}},
			'_save':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_web_about.savePage(\'aboutdonations\');'},
				}},
		};
		this.aboutdonations.fieldValue = this.fieldValue;
		this.aboutdonations.fieldHistoryArgs = this.fieldHistoryArgs;
		this.aboutdonations.addDropImage = function(iid) {
			this.setFieldValue('page-aboutdonations-image', iid);
			return true;
		};
		this.aboutdonations.deleteImage = this.about.deleteImage;
		this.aboutdonations.addButton('save', 'Save', 'M.ciniki_web_about.savePage(\'aboutdonations\');');
		this.aboutdonations.addClose('Cancel');

		//
		// The options and information for the about donations page
		//
		this.aboutboardofdirectors = new M.panel('Board of Directors',
			'ciniki_web_about', 'aboutboardofdirectors',
			'mc', 'medium', 'sectioned', 'ciniki.web.about.aboutboardofdirectors');
		this.aboutboardofdirectors.data = {};
		this.aboutboardofdirectors.sections = {
			'options':{'label':'', 'fields':{
				'page-aboutboardofdirectors-active':{'label':'Display Board of Directors Page', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				}},
			'_image':{'label':'Image', 'fields':{
				'page-aboutboardofdirectors-image':{'label':'', 'type':'image_id', 'controls':'all', 'hidelabel':'yes', 'history':'no'},
				}},
			'_image_caption':{'label':'', 'fields':{
				'page-aboutboardofdirectors-image-caption':{'label':'Caption', 'type':'text'},
				}},
			'_content':{'label':'Content', 'fields':{
				'page-aboutboardofdirectors-content':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'large'},
				}},
			'_save':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_web_about.savePage(\'aboutboardofdirectors\');'},
				}},
		};
		this.aboutboardofdirectors.fieldValue = this.fieldValue;
		this.aboutboardofdirectors.fieldHistoryArgs = this.fieldHistoryArgs;
		this.aboutboardofdirectors.addDropImage = function(iid) {
			this.setFieldValue('page-aboutboardofdirectors-image', iid);
			return true;
		};
		this.aboutboardofdirectors.deleteImage = this.about.deleteImage;
		this.aboutboardofdirectors.addButton('save', 'Save', 'M.ciniki_web_about.savePage(\'aboutboardofdirectors\');');
		this.aboutboardofdirectors.addClose('Cancel');

		//
		// The options and information for the about donations page
		//
		this.aboutmembership = new M.panel('Membership',
			'ciniki_web_about', 'membership',
			'mc', 'medium', 'sectioned', 'ciniki.web.about.aboutmembership');
		this.aboutmembership.data = {};
		this.aboutmembership.sections = {
			'options':{'label':'', 'fields':{
				'page-aboutmembership-active':{'label':'Display Membership Page', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				}},
			'_image':{'label':'Image', 'fields':{
				'page-aboutmembership-image':{'label':'', 'type':'image_id', 'controls':'all', 'hidelabel':'yes', 'history':'no'},
				}},
			'_image_caption':{'label':'', 'fields':{
				'page-aboutmembership-image-caption':{'label':'Caption', 'type':'text'},
				}},
			'_save':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_web_about.savePage(\'aboutmembership\');'},
				}},
		};
		this.aboutmembership.fieldValue = this.fieldValue;
		this.aboutmembership.fieldHistoryArgs = this.fieldHistoryArgs;
		this.aboutmembership.addDropImage = function(iid) {
			this.setFieldValue('page-aboutmembership-image', iid);
			return true;
		};
		this.aboutmembership.deleteImage = this.about.deleteImage;
		this.aboutmembership.addButton('save', 'Save', 'M.ciniki_web_about.savePage(\'aboutmembership\');');
		this.aboutmembership.addClose('Cancel');
	}

	this.start = function(cb, ap, aG) {
		args = {};
		if( aG != null ) {
			args = eval(aG);
		}

		//
		// Create the app container if it doesn't exist, and clear it out
		// if it does exist.
		//
		var appContainer = M.createContainer(ap, 'ciniki_web_about', 'yes');
		if( appContainer == null ) {
			alert('App Error');
			return false;
		} 

		this.showPage(cb, 'about');
	}

	this.showPage = function(cb, page, subpage, subpagetitle) {
		this[page].reset();
		var rsp = M.api.getJSONCb('ciniki.web.pageSettingsGet', 
			{'business_id':M.curBusinessID, 'page':page, 'content':'yes'}, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				}
				M.ciniki_web_about[page].data = rsp.settings;
				M.ciniki_web_about.showPageFinish(cb, page);
			});
	}

	this.showPageFinish = function(cb, page, rsp) {
		if( page == 'about' ) {
			this.about.sections.subpages.list.aboutartiststatement.visible = 'no';
			this.about.sections.subpages.list.aboutcv.visible = 'no';
			this.about.sections.subpages.list.aboutawards.visible = 'no';
			this.about.sections.subpages.list.abouthistory.visible = 'no';
			this.about.sections.subpages.list.aboutdonations.visible = 'no';
			this.about.sections.subpages.list.aboutboardofdirectors.visible = 'no';
			this.about.sections.subpages.list.aboutmembership.visible = 'no';

			// Decide which sections should be visible for the about page
			if( M.curBusiness.modules['ciniki.artcatalog'] != null ) {
				this.about.sections.subpages.list.aboutartiststatement.visible = 'yes';
				this.about.sections.subpages.list.aboutcv.visible = 'yes';
				this.about.sections.subpages.list.aboutawards.visible = 'yes';
			} else if( M.curBusiness.modules['ciniki.artgallery'] != null ) {
				this.about.sections.subpages.list.abouthistory.visible = 'yes';
				this.about.sections.subpages.list.aboutdonations.visible = 'yes';
				this.about.sections.subpages.list.aboutboardofdirectors.visible = 'yes';
				this.about.sections.subpages.list.aboutmembership.visible = 'yes';
			} else if( M.curBusiness.modules['ciniki.artclub'] != null ) {
				this.about.sections.subpages.list.abouthistory.visible = 'yes';
				this.about.sections.subpages.list.aboutmembership.visible = 'yes';
			}
			// Get the user associated with this business
			this.about.sections._users.visible = 'no';
			var rsp = M.api.getJSONCb('ciniki.web.businessUsers', {'business_id':M.curBusinessID}, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				}
				var p = M.ciniki_web_about.about;
				if( rsp.users.length > 0 ) {
					p.sections._users.visible = 'yes';
					p.sections._users.fields = {};
					for(i in rsp.users) {
						var u = rsp.users[i].user;
						p.sections._users.fields['page-about-user-display-flags-' + u.id] = {
							'label':u.firstname + ' ' + u.lastname, 'type':'flags', 'join':'yes', 'flags':M.ciniki_web_about.userFlags,
							};
					}
					p.sections._users_display.visible = 'yes';
				} else {
					p.sections._users.visible = 'no';
					p.sections._users_display.visible = 'no';
				}
				p.refresh();
				p.show(cb);
			});
		} else {
			this[page].refresh();
			this[page].show(cb);
		}
	};

	this.savePage = function(page) {
		var c = this[page].serializeForm('no');
		if( c != '' ) {
			var rsp = M.api.postJSONCb('ciniki.web.siteSettingsUpdate', 
				{'business_id':M.curBusinessID}, c, function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					} 
				M.ciniki_web_about[page].close();
			});
		} else {
			this[page].close();
		}
	};
}
