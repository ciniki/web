//
// The app to manage web options for a business
//
function ciniki_web_about() {
	
	this.activeToggles = {'no':'No', 'yes':'Yes'};
	this.subpages = {
		'2':{'name':'Artist Statement', 'ui':'artiststatement', 'permalink':'artiststatement', 'flags':0x02},
		'3':{'name':'CV', 'ui':'cv', 'permalink':'cv', 'flags':0x04},
		'4':{'name':'Awards', 'ui':'awards', 'permalink':'awards', 'flags':0x08},
		'5':{'name':'History', 'ui':'history', 'permalink':'history', 'flags':0x10},
		'6':{'name':'Donations', 'ui':'donations', 'permalink':'donations', 'flags':0x20},
		'9':{'name':'Facilities', 'ui':'facilities', 'permalink':'facilities', 'flags':0x100},
		'8':{'name':'Board of Directors', 'ui':'boardofdirectors', 'permalink':'boardofdirectors', 'flags':0x80},
		'7':{'name':'Membership', 'ui':'membership', 'permalink':'membership', 'flags':0x40},
		'11':{'name':'Warranty', 'ui':'warranty', 'permalink':'warranty', 'flags':0x0400},
		'12':{'name':'Testimonials', 'ui':'testimonials', 'permalink':'testimonials', 'flags':0x0800},
		'13':{'name':'Reviews', 'ui':'reviews', 'permalink':'reviews', 'flags':0x1000},
		'14':{'name':'Green Policy', 'ui':'greenpolicy', 'permalink':'greenpolicy', 'flags':0x2000},
		'15':{'name':'Why us', 'ui':'whyus', 'permalink':'whyus', 'flags':0x4000},
		'16':{'name':'Privacy Policy', 'ui':'privacypolicy', 'permalink':'privacypolicy', 'flags':0x8000},
		'17':{'name':'Volunteer', 'ui':'volunteer', 'permalink':'volunteer', 'flags':0x010000},
		'18':{'name':'Rental', 'ui':'rental', 'permalink':'rental', 'flags':0x020000},
		'19':{'name':'Financial Assistance', 'ui':'financialassistance', 'permalink':'financialassistance', 'flags':0x040000},
		'20':{'name':'Artists', 'ui':'artists', 'permalink':'artists', 'flags':0x080000},
		'21':{'name':'Employment', 'ui':'employment', 'permalink':'employment', 'flags':0x100000},
		'22':{'name':'Staff', 'ui':'staff', 'permalink':'staff', 'flags':0x200000},
		'23':{'name':'Sponsorship', 'ui':'sponsorship', 'permalink':'sponsorship', 'flags':0x400000},
		'24':{'name':'Jobs', 'ui':'jobs', 'permalink':'jobs', 'flags':0x800000},
		'25':{'name':'Extended Bio', 'ui':'extendedbio', 'permalink':'extended-bio', 'flags':0x01000000},
	};
	
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
		this.page = new M.panel('About',
			'ciniki_web_about', 'page',
			'mc', 'medium', 'sectioned', 'ciniki.web.about.page');
		this.page.data = {};
		this.page.sections = {
			'options':{'label':'', 'fields':{
				'page-about-active':{'label':'Display About Page', 'type':'toggle', 'default':'no', 'toggles':this.activeToggles}, //, 'editFn':'M.ciniki_web_about.editInfo(\'1\');'},
				'page-about-title':{'label':'About Page Title', 'type':'text', 'hint':'About'},
				}},
//			'subpages':{'label':'', 'active':'no', 'fields':{}},
			'subpagesedit':{'label':'', 'active':'no', 'list':{}},
			'_users':{'label':'Business Employees', 'visible':'no', 'active':'no', 'fields':{
				}},
			'_users_display':{'label':'', 'visible':'no', 'active':'no', 'fields':{
				'page-about-bios-title':{'label':'Title', 'type':'text', 'hint':''},
				'page-about-bios-display':{'label':'Employee List', 'type':'multitoggle', 'default':'list', 'toggles':{'list':'2 Column', 'cilist':'3 Column'}, 'hint':''},
				}},
			'_save':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_web_about.savePage();'},
				}},
		};
		this.page.fieldValue = this.fieldValue;
		this.page.fieldHistoryArgs = this.fieldHistoryArgs;
		this.page.listValue = function(s, i, d) { return d.label; }
		this.page.listFn = function(s, i, d) { return d.fn; }
		this.page.addButton('save', 'Save', 'M.ciniki_web_about.savePage();');
		this.page.addLeftButton('website', 'Preview', 'M.showWebsite(\'/about\');');
		this.page.addClose('Cancel');
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

		this.showPage(cb);
	}

	this.showPage = function(cb) {
		this.page.reset();
		var rsp = M.api.getJSONCb('ciniki.web.pageSettingsGet', 
			{'business_id':M.curBusinessID, 'page':'about', 'content':'yes'}, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				}
				M.ciniki_web_about.page.data = rsp.settings;
				M.ciniki_web_about.showPageFinish(cb);
			});
	}

	this.showPageFinish = function(cb, page, rsp) {
		var p = this.page;
		var flags = M.curBusiness.modules['ciniki.info'].flags;
//		var options = {};
		var spgs = M.ciniki_web_about.subpages;
		p.sections.subpagesedit.list = {'_':{'label':'Edit About Page', 'fn':'M.ciniki_web_about.editInfo(\'1\');'}};
//		p.sections.subpages.active = 'no';
		for(i in spgs) {
			if( (spgs[i].flags&flags) > 0 ) {	
//				p.sections.subpages.active = 'yes';
//				options[i] = spgs[i].name;
//				p.sections.subpages.fields['page-about-' + spgs[i].permalink + '-active'] = {'label':spgs[i].name,
//					'editFn':'M.ciniki_web_about.editInfo(\'' + i + '\');',
//					'type':'toggle', 'default':'no', 'toggles':M.ciniki_web_about.activeToggles};
				p.sections.options.fields['page-about-' + spgs[i].permalink + '-active'] = {'label':'Display ' + spgs[i].name + ' Page',
//					'editFn':'M.ciniki_web_about.editInfo(\'' + i + '\');',
					'type':'toggle', 'default':'no', 'toggles':M.ciniki_web_about.activeToggles};
				p.sections.subpagesedit.active = 'yes';
				p.sections.subpagesedit.list['page_' + i] = {'label':'Edit ' + spgs[i].name + ' Page', 'fn':'M.ciniki_web_about.editInfo(\'' + i + '\');'};
			}
		}

		// Get the user associated with this business
		this.page.sections._users.visible = 'no';
		this.page.sections._users_display.visible = 'no';
		if( M.curBusiness.modules['ciniki.businesses']!=null && (M.curBusiness.modules['ciniki.businesses'].flags&0x01) == 1 ) {
			M.api.getJSONCb('ciniki.web.businessUsers', {'business_id':M.curBusinessID}, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				}
				var p = M.ciniki_web_about.page;
				if( rsp.users.length > 0 ) {
					p.sections._users.visible = 'yes';
					p.sections._users.active = 'yes';
					p.sections._users.fields = {};
					for(i in rsp.users) {
						var u = rsp.users[i].user;
						p.sections._users.fields['page-about-user-display-flags-' + u.id] = {
							'label':u.firstname + ' ' + u.lastname, 'type':'flags', 'join':'yes', 'flags':M.ciniki_web_about.userFlags,
							'editFn':'M.startApp(\'ciniki.businesses.users\',null,\'M.ciniki_web_about.page.show();\',\'mc\',{\'user_id\':\'' + u.id + '\'});',
							};
					}
					p.sections._users_display.visible = 'yes';
					p.sections._users_display.active = 'yes';
				} else {
					p.sections._users.visible = 'no';
					p.sections._users_display.visible = 'no';
					p.sections._users.active = 'no';
					p.sections._users_display.active = 'no';
				}
				p.refresh();
				p.show(cb);
			});
		} else {
			this.page.refresh();
			this.page.show(cb);
		}
	};

	this.editInfo = function(ct) {
		if( ct == 1 ) {
			M.startApp('ciniki.info.about',null,'M.ciniki_web_about.page.show();');
		} else if( this.subpages[ct] != null ) {
			M.startApp('ciniki.info.' + this.subpages[ct].ui,null,'M.ciniki_web_about.page.show();');
		}
	}

	this.savePage = function() {
		var c = this.page.serializeForm('no');
		if( c != '' ) {
			var rsp = M.api.postJSONCb('ciniki.web.siteSettingsUpdate', 
				{'business_id':M.curBusinessID}, c, function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					} 
				M.ciniki_web_about.page.close();
			});
		} else {
			this.page.close();
		}
	};
}
