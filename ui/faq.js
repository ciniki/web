//
// The app to manage web options for a business
//
function ciniki_web_faq() {
	
	this.activeToggles = {'no':'No', 'yes':'Yes'};
	this.faqFlags = {'1':{'name':'Hidden'}};
	
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
		// The options and information for the faq page
		//
		this.main = new M.panel('FAQ',
			'ciniki_web_faq', 'main',
			'mc', 'medium', 'sectioned', 'ciniki.web.faq.faq');
		this.main.data = {};
		this.main.sections = {};
		this.main.cellValue = function(s, i, j, d) {
			if( (d.faq.flags&0x01) == 1 ) { return d.faq.question + ' (hidden)'; }
			return d.faq.question;
		};
		this.main.rowFn = function(s, i, d) {
			return 'M.ciniki_web_faq.editFAQ(\'M.ciniki_web_faq.showMain();\',' + d.faq.id + ');';
		};
		this.main.sectionData = function(s) {
			return this.data[s];
		};
		this.main.fieldValue = this.fieldValue;
		this.main.fieldHistoryArgs = this.fieldHistoryArgs;
		this.main.addButton('save', 'Save', 'M.ciniki_web_faq.saveFAQs();');
		this.main.addClose('Cancel');

		//
		// the edit panel
		//
		this.edit = new M.panel('Question',
			'ciniki_web_faq', 'edit',
			'mc', 'medium', 'sectioned', 'ciniki.web.faq.edit');
		this.edit.faq_id = 0;
		this.edit.data = {};
		this.edit.sections = {
			'faq':{'label':'', 'fields':{
				'category':{'label':'Category', 'type':'text', 'livesearch':'yes', 'livesearchempty':'yes'},
				'flags':{'label':'Options', 'type':'flags', 'flags':this.faqFlags, 'join':'yes'},
				'question':{'label':'Question', 'type':'text'},
				}},
			'_answer':{'label':'Answer', 'fields':{
				'answer':{'label':'', 'hidelabel':'yes', 'type':'textarea'},
				}},
			'_buttons':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_web_faq.saveFAQ();'},
				'delete':{'label':'Delete', 'fn':'M.ciniki_web_faq.deleteFAQ();'},
				}},
		};
		this.edit.liveSearchCb = function(s, i, value) {
			if( i == 'category' ) {
				var rsp = M.api.getJSONBgCb('ciniki.web.faqSearchCategory', {'business_id':M.curBusinessID, 'field':i, 'start_needle':value, 'limit':15},
					function(rsp) {
						M.ciniki_web_faq.edit.liveSearchShow(s, i, M.gE(M.ciniki_web_faq.edit.panelUID + '_' + i), rsp.results);
					});
			}
		};
		this.edit.liveSearchResultValue = function(s, f, i, j, d) {
			if( f == 'category' && d.result != null ) { return d.result.name; }
			return '';
		};
		this.edit.liveSearchResultRowFn = function(s, f, i, j, d) { 
			if( f == 'category' && d.result != null ) {
				return 'M.ciniki_web_faq.edit.updateField(\'' + s + '\',\'' + f + '\',\'' + escape(d.result.name) + '\');';
			}
		};
		this.edit.updateField = function(s, fid, result) {
			M.gE(this.panelUID + '_' + fid).value = unescape(result);
			this.removeLiveSearch(s, fid);
		};
		this.edit.fieldHistoryArgs = function(s, i) {
			return {'method':'ciniki.web.faqHistory', 'args':{'business_id':M.curBusinessID, 
				'faq_id':this.faq_id, 'field':i}};
		}
		this.edit.fieldValue = this.fieldValue;
		this.edit.addButton('save', 'Save', 'M.ciniki_web_faq.saveFAQ();');
		this.edit.addClose('Cancel');
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
		var appContainer = M.createContainer(ap, 'ciniki_web_faq', 'yes');
		if( appContainer == null ) {
			alert('App Error');
			return false;
		} 

		this.showMain(cb);
	}

	this.showMain = function(cb) {
		this.main.reset();

		var rsp = M.api.getJSONCb('ciniki.web.pageSettingsGet', 
			{'business_id':M.curBusinessID, 'page':'faq', 'content':'yes'}, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				}
				M.ciniki_web_faq.main.data = rsp.settings;
				M.ciniki_web_faq.showMainFinish(cb);
			});
	}

	this.showMainFinish = function(cb) {
		var rsp = M.api.getJSONCb('ciniki.web.faqList', {'business_id':M.curBusinessID}, function(rsp) {
			if( rsp.stat != 'ok' ) {
				M.api.err(rsp);
				return false;
			}
			var p = M.ciniki_web_faq.main;
			// Setup the main panel sections, based on existing categories
			p.sections = {
				'_options':{'label':'', 'fields':{
					'page-faq-active':{'label':'Display FAQ Page', 'type':'multitoggle', 'default':'no', 'toggles':M.ciniki_web_faq.activeToggles},
					}},
			};
			if( rsp.categories != null ) {
				for(i in rsp.categories) {
					var name = '_' + rsp.categories[i].category.name.replace(/[^a-zA-Z0-9]/,'');
					p.sections[name] = {
						'label':rsp.categories[i].category.name, 'type':'simplegrid', 'num_cols':1,
						'addTxt':'Add Question',
						'addFn':'M.ciniki_web_faq.editFAQ(\'M.ciniki_web_faq.showMain();\',0,\'' + escape(rsp.categories[i].category.name) + '\')',
						};
					p.data[name] = rsp.categories[i].category.faqs;
				};
			} else {
				p.sections._blank = {
					'label':'', 'type':'simplegrid',
					'addTxt':'Add Question',
					'addFn':'M.ciniki_web_faq.editFAQ(\'M.ciniki_web_faq.showMain();\',0,\'\')',
				};
				p.data._blank = {};
			}
			p.sections._buttons = {'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_web_faq.saveFAQs();'},
				}};

			p.refresh();
			p.show(cb);
		});
	};

	this.saveFAQs = function(page) {
		var c = this.main.serializeForm('no');
		if( c != '' ) {
			var rsp = M.api.postJSONCb('ciniki.web.siteSettingsUpdate', 
				{'business_id':M.curBusinessID}, c, function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					} 
					M.ciniki_web_faq.main.close();
				});
		} else {
			this.main.close();
		}
	};

	this.editFAQ = function(cb, fid, category) {
		if( fid != null ) {
			this.edit.faq_id = fid;
		}
		if( this.edit.faq_id > 0 ) {
			this.edit.sections._buttons.buttons.delete.visible = 'yes';
			var rsp = M.api.getJSONCb('ciniki.web.faqGet', 
				{'business_id':M.curBusinessID, 'faq_id':this.edit.faq_id}, 
				function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					}
					var p = M.ciniki_web_faq.edit;
					p.data = rsp.faq;
					p.refresh();
					p.show(cb);
				});
		} else {
			this.edit.sections._buttons.buttons.delete.visible = 'no';
			this.edit.reset();
			this.edit.data = {};
			if( category != null ) {
				this.edit.data.category = unescape(category);
			}
			this.edit.refresh();
			this.edit.show(cb);
		}
	};

	this.saveFAQ = function() {
		if( this.edit.faq_id > 0 ) {
			var c = this.edit.serializeForm('no');
			if( c != '' ) {
				var rsp = M.api.postJSONCb('ciniki.web.faqUpdate', 
					{'business_id':M.curBusinessID, 'faq_id':this.edit.faq_id}, c, function(rsp) {
						if( rsp.stat != 'ok' ) {
							M.api.err(rsp);
							return false;
						}
						M.ciniki_web_faq.edit.close();
					});
			} else {
				M.ciniki_web_faq.edit.close();
			}
		} else {
			var c = this.edit.serializeForm('yes');
			var rsp = M.api.postJSONCb('ciniki.web.faqAdd', {'business_id':M.curBusinessID}, c, 
				function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					}
					M.ciniki_web_faq.edit.close();
				});
		}
	};

	this.deleteFAQ = function() {
		if( confirm('Are you sure you want to this question?') ) {
			var rsp = M.api.getJSONCb('ciniki.web.faqDelete', {'business_id':M.curBusinessID, 
				'faq_id':this.edit.faq_id}, 
				function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					}
					M.ciniki_web_faq.edit.close();
				});
		}
	};
};
