//
// The app to manage web options for a business
//
function ciniki_web_social() {
	
	this.activeToggles = {'no':'No', 'yes':'Yes'};
	
	this.init = function() {
		//
		// The options and information for the faq page
		//
		this.main = new M.panel('FAQ',
			'ciniki_web_social', 'main',
			'mc', 'medium mediumaside', 'sectioned', 'ciniki.web.faq.faq');
		this.main.data = {};
		this.main.sections = {
			'_og_image':{'label':'Default Social Image', 'aside':'yes', 'type':'imageform', 'fields':{
				'site-header-og-image':{'label':'', 'type':'image_id', 'controls':'all', 'hidelabel':'yes', 'history':'no'},
				}},
			'setup':{'label':'', 'list':{
				'settings':{'label':'Social Media Accounts', 'fn':'M.ciniki_web_social.showSocialAccounts();'},
			}},
			'header':{'label':'Header Links', 'visible':'no', 'fields':{
				'site-social-facebook-header-active':{'label':'Facebook', 'active':'no', 'type':'multitoggle', 'default':'yes', 'toggles':this.activeToggles},
				'site-social-twitter-header-active':{'label':'Twitter', 'active':'no', 'type':'multitoggle', 'default':'yes', 'toggles':this.activeToggles},
				'site-social-linkedin-header-active':{'label':'Linked In', 'active':'no', 'type':'multitoggle', 'default':'yes', 'toggles':this.activeToggles},
				'site-social-etsy-header-active':{'label':'Etsy', 'active':'no', 'type':'multitoggle', 'default':'yes', 'toggles':this.activeToggles},
				'site-social-pinterest-header-active':{'label':'Pinterest', 'active':'no', 'type':'multitoggle', 'default':'yes', 'toggles':this.activeToggles},
				'site-social-tumblr-header-active':{'label':'Tumblr', 'active':'no', 'type':'multitoggle', 'default':'yes', 'toggles':this.activeToggles},
				'site-social-flickr-header-active':{'label':'Flickr', 'active':'no', 'type':'multitoggle', 'default':'yes', 'toggles':this.activeToggles},
				'site-social-youtube-header-active':{'label':'YouTube', 'active':'no', 'type':'multitoggle', 'default':'yes', 'toggles':this.activeToggles},
				'site-social-vimeo-header-active':{'label':'Vimeo', 'active':'no', 'type':'multitoggle', 'default':'yes', 'toggles':this.activeToggles},
				'site-social-instagram-header-active':{'label':'Instagram', 'active':'no', 'type':'multitoggle', 'default':'yes', 'toggles':this.activeToggles},
			}},
			'footer':{'label':'Footer Links', 'fields':{
				'site-social-facebook-footer-active':{'label':'Facebook', 'type':'multitoggle', 'default':'yes', 'toggles':this.activeToggles},
				'site-social-twitter-footer-active':{'label':'Twitter', 'type':'multitoggle', 'default':'yes', 'toggles':this.activeToggles},
				'site-social-linkedin-footer-active':{'label':'Linked In', 'active':'no', 'type':'multitoggle', 'default':'yes', 'toggles':this.activeToggles},
				'site-social-etsy-footer-active':{'label':'Etsy', 'type':'multitoggle', 'default':'yes', 'toggles':this.activeToggles},
				'site-social-pinterest-footer-active':{'label':'Pinterest', 'type':'multitoggle', 'default':'yes', 'toggles':this.activeToggles},
				'site-social-tumblr-footer-active':{'label':'Tumblr', 'type':'multitoggle', 'default':'yes', 'toggles':this.activeToggles},
				'site-social-flickr-footer-active':{'label':'Flickr', 'type':'multitoggle', 'default':'yes', 'toggles':this.activeToggles},
				'site-social-youtube-footer-active':{'label':'YouTube', 'type':'multitoggle', 'default':'yes', 'toggles':this.activeToggles},
				'site-social-vimeo-footer-active':{'label':'Vimeo', 'type':'multitoggle', 'default':'yes', 'toggles':this.activeToggles},
				'site-social-instagram-footer-active':{'label':'Instagram', 'type':'multitoggle', 'default':'yes', 'toggles':this.activeToggles},
			}},
			'_buttons':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_web_social.save(\'M.ciniki_web_social.main.close();\');'},
			}},
		};
		this.main.fieldValue = function(s, i, d) { 
			if( this.data[i] == null ) { return ''; }
			return this.data[i]; 
		};
		this.main.fieldHistoryArgs = function(s, i) {
			return {'method':'ciniki.web.pageSettingsHistory', 'args':{'business_id':M.curBusinessID, 'field':i}};
		}
		this.main.addDropImage = function(iid) {
			this.setFieldValue('site-header-og-image', iid);
			return true;
		};
		this.main.deleteImage = this.deleteImage;
		this.main.addButton('save', 'Save', 'M.ciniki_web_faq.save(\'M.ciniki_web_social.main.close();\');');
		this.main.addClose('Cancel');
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
		var appContainer = M.createContainer(ap, 'ciniki_web_social', 'yes');
		if( appContainer == null ) {
			alert('App Error');
			return false;
		} 

		this.showMain(cb);
	}

	this.showMain = function(cb) {
		this.main.reset();
		if( cb != null ) {
			this.main.cb = cb;
		}

		var rsp = M.api.getJSONCb('ciniki.web.siteSettingsGet', 
			{'business_id':M.curBusinessID, 'content':'no'}, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				}
				M.ciniki_web_social.main.data = rsp.settings;
				M.ciniki_web_social.showMainFinish(cb);
			});
	}

	this.showMainFinish = function(cb) {
		var rsp = M.api.getJSONCb('ciniki.businesses.getDetails', 
			{'business_id':M.curBusinessID, 'keys':'social'}, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				}
				var p = M.ciniki_web_social.main;
				var header = 'no';
				var footer = 'no';
				// Facebook
				if( rsp.details['social-facebook-url'] != null && rsp.details['social-facebook-url'] != '' ) {
					p.sections.header.fields['site-social-facebook-header-active'].active = 'yes';
					p.sections.footer.fields['site-social-facebook-footer-active'].active = 'yes';
					header = 'yes';
					footer = 'yes';
				} else {
					p.sections.header.fields['site-social-facebook-header-active'].active = 'no';
					p.sections.footer.fields['site-social-facebook-footer-active'].active = 'no';
				}
				// Twitter
				if( rsp.details['social-twitter-username'] != null && rsp.details['social-twitter-username'] != '' ) {
					p.sections.header.fields['site-social-twitter-header-active'].active = 'yes';
					p.sections.footer.fields['site-social-twitter-footer-active'].active = 'yes';
					header = 'yes';
					footer = 'yes';
				} else {
					p.sections.header.fields['site-social-twitter-header-active'].active = 'no';
					p.sections.footer.fields['site-social-twitter-footer-active'].active = 'no';
				}
				// Etsy
				if( rsp.details['social-etsy-url'] != null && rsp.details['social-etsy-url'] != '' ) {
					p.sections.header.fields['site-social-etsy-header-active'].active = 'yes';
					p.sections.footer.fields['site-social-etsy-footer-active'].active = 'yes';
					header = 'yes';
					footer = 'yes';
				} else {
					p.sections.header.fields['site-social-etsy-header-active'].active = 'no';
					p.sections.footer.fields['site-social-etsy-footer-active'].active = 'no';
				}
				// Pinterest
				if( rsp.details['social-pinterest-username'] != null && rsp.details['social-pinterest-username'] != '' ) {
					p.sections.header.fields['site-social-pinterest-header-active'].active = 'yes';
					p.sections.footer.fields['site-social-pinterest-footer-active'].active = 'yes';
					header = 'yes';
					footer = 'yes';
				} else {
					p.sections.header.fields['site-social-pinterest-header-active'].active = 'no';
					p.sections.footer.fields['site-social-pinterest-footer-active'].active = 'no';
				}
				// Flickr
				if( rsp.details['social-flickr-url'] != null && rsp.details['social-flickr-url'] != '' ) {
					p.sections.header.fields['site-social-flickr-header-active'].active = 'yes';
					p.sections.footer.fields['site-social-flickr-footer-active'].active = 'yes';
					header = 'yes';
					footer = 'yes';
				} else {
					p.sections.header.fields['site-social-flickr-header-active'].active = 'no';
					p.sections.footer.fields['site-social-flickr-footer-active'].active = 'no';
				}
				// Tumblr
				if( rsp.details['social-tumblr-username'] != null && rsp.details['social-tumblr-username'] != '' ) {
					p.sections.header.fields['site-social-tumblr-header-active'].active = 'yes';
					p.sections.footer.fields['site-social-tumblr-footer-active'].active = 'yes';
					header = 'yes';
					footer = 'yes';
				} else {
					p.sections.header.fields['site-social-tumblr-header-active'].active = 'no';
					p.sections.footer.fields['site-social-tumblr-footer-active'].active = 'no';
				}
				// YouTube
				if( rsp.details['social-youtube-username'] != null && rsp.details['social-youtube-username'] != '' ) {
					p.sections.header.fields['site-social-youtube-header-active'].active = 'yes';
					p.sections.footer.fields['site-social-youtube-footer-active'].active = 'yes';
					header = 'yes';
					footer = 'yes';
				} else {
					p.sections.header.fields['site-social-youtube-header-active'].active = 'no';
					p.sections.footer.fields['site-social-youtube-footer-active'].active = 'no';
				}
				// Vimeo
				if( rsp.details['social-vimeo-url'] != null && rsp.details['social-vimeo-url'] != '' ) {
					p.sections.header.fields['site-social-vimeo-header-active'].active = 'yes';
					p.sections.footer.fields['site-social-vimeo-footer-active'].active = 'yes';
					header = 'yes';
					footer = 'yes';
				} else {
					p.sections.header.fields['site-social-vimeo-header-active'].active = 'no';
					p.sections.footer.fields['site-social-vimeo-footer-active'].active = 'no';
				}
				// Instagram
				if( rsp.details['social-instagram-username'] != null && rsp.details['social-instagram-username'] != '' ) {
					p.sections.header.fields['site-social-instagram-header-active'].active = 'yes';
					p.sections.footer.fields['site-social-instagram-footer-active'].active = 'yes';
					header = 'yes';
					footer = 'yes';
				} else {
					p.sections.header.fields['site-social-instagram-header-active'].active = 'no';
					p.sections.footer.fields['site-social-instagram-footer-active'].active = 'no';
				}

				// Linkedin
				if( rsp.details['social-linkedin-url'] != null && rsp.details['social-linkedin-url'] != '' ) {
					p.sections.header.fields['site-social-linkedin-header-active'].active = 'yes';
					p.sections.footer.fields['site-social-linkedin-footer-active'].active = 'yes';
					header = 'yes';
					footer = 'yes';
				} else {
					p.sections.header.fields['site-social-linkedin-header-active'].active = 'no';
					p.sections.footer.fields['site-social-linkedin-footer-active'].active = 'no';
				}

				p.sections.header.visible = header;
				p.sections.footer.visible = footer;

				p.refresh();
				p.show(cb);
			});
	};

	//
	// The save function can be used to save the current state of the form to the database,
	// before opening up the social media page.
	//
	this.save = function(cb) {
		// Save the web settings
		var c = this.main.serializeForm('no');
		if( c != '' ) {
			var rsp = M.api.postJSONCb('ciniki.web.siteSettingsUpdate', {'business_id':M.curBusinessID}, c, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				} 
				eval(cb);
			});
		} else {
			eval(cb);
		}
	};

	this.showSocialAccounts = function() {
		this.save('M.startApp(\'ciniki.businesses.social\',null,\'M.ciniki_web_social.showMain();\');');
	};
};
