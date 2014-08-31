//
// The app to manage web options for a business
//
function ciniki_web_main() {
	
//	this.domainFlags = {
//		'1':{'name':'Site'},
//		'5':{'name':'Domain'},
//		'6':{'name':'Primary'},
//		};
	this.domainStatus = {
		'1':'Active',
		'50':'Suspended',
		'60':'Deleted',
		};
	this.themesAvailable = {
		'default':'Simple - Black/White',
		'black':'Midnight - Blue/Black',
		'davinci':'Davinci - Brown/Beige',
//		'field':'Field - Green/White',
		};
	if( M.userPerms&0x01 == 0x01 ) {
		this.themesAvailable['field'] = 'Field - Green/White';
		this.themesAvailable['redbrick'] = 'Red Brick';
	}
	
	this.layoutsAvailable = {
		'default':'Default',
		'aspen':'Aspen',
		};
	this.activeToggles = {'no':'No', 'yes':'Yes'};
	this.productThumbnailToggles = {'auto':'Auto', 'small':'Small', 'medium':'Medium', 'large':'Large'};
	this.linksDisplayToggles = {'wordlist':'List', 'wordcloud':'Cloud'};
	this.userFlags = {
		'1':{'name':'Name'},
		'2':{'name':'Title'},
		'3':{'name':'Phone'},
		'4':{'name':'Cell'},
		'5':{'name':'Fax'},
		'6':{'name':'Email'},
		'7':{'name':'Bio'},
		};

	this.deleteImage = function(fid) {
		this.setFieldValue(fid, 0);
		return true;
	}
	
	this.init = function() {
		this.menu = new M.panel('Website',
			'ciniki_web_main', 'menu',
			'mc', 'medium', 'sectioned', 'ciniki.web.main.menu');
		this.menu.data = {};
		this.menu.sections = {
			'settings':{'label':'Settings', 'aside':'no', 'type':'simplegrid', 'num_cols':2, 'sortable':'no',
				'headerValues':null,
				'cellClasses':['',''],
				},
			'pages':{'label':'Pages', 'aside':'no', 'type':'simplegrid', 'num_cols':1, 'sortable':'yes',
				'headerValues':null,
				},
			'advanced':{'label':'Advanced', 'list':{
				'header':{'label':'Header', 'fn':'M.ciniki_web_main.showHeader(\'M.ciniki_web_main.showMenu();\');'},
				'social':{'label':'Social Media Links', 'fn':'M.startApp(\'ciniki.web.social\',null,\'M.ciniki_web_main.showMenu();\');'},
				}},
//			'advanced':{'label':'Advanced', 'type':'simplegrid', 'num_cols':1, 'sortable':'no',
//				'headerValues':null,
//				'cellClasses':['',''],
//				},
		};
		this.menu.noData = function(s) { return 'No options added'; }
		this.menu.sectionData = function(s) { 
			if( s == 'advanced' ) { return this.sections.advanced.list; }
			if( s == 'adm' ) { return this.sections.adm.list; }
			return this.data[s]; 
		};
		this.menu.listValue = function(s, i, d) { return d.label; };
		this.menu.cellValue = function(s, i, j, d) {
			if( s == 'settings' ) {
				if( j == 0 && d.setting.display_name == 'Theme' ) { return 'Color Scheme'; }
				if( j == 1 && d.setting.display_name == 'Theme' ) { return M.ciniki_web_main.themesAvailable[d.setting.value]; }
				switch(j) {
					case 0: return d.setting.display_name;
					case 1: return d.setting.value;
				}
			} 
			else if( s == 'advanced' ) {
				if( j == 1 && d.setting.name == 'site-header-image' ) { 
					if( d.setting.value == '0' && d.setting.value == 0 ) {
						return 'none';
					} else {
						return 'Yes';
					}
				}
				switch(j) {
					case 0: return d.setting.display_name;
					case 1: return d.setting.value;
				}
			} else if( s == 'pages' ) {
				if( d.page.active == 'yes' ) {
					return d.page.display_name;
				}
				return d.page.display_name + ' (disabled)';
			}
		}
		this.menu.rowFn = function(s, i, d) {
			if( s == 'settings' && d.setting.name == 'theme') { 
				return 'M.ciniki_web_main.showThemes(\'M.ciniki_web_main.showMenu();\',\'' + d.setting.value + '\');'; 
			}
//			if( s == 'advanced' && d.setting.name == 'site-header-image' ) { 
//				return 'M.ciniki_web_main.showHeaderImage(\'M.ciniki_web_main.showMenu();\',\'' + d.setting.value + '\');'; 
//			}
//			if( s == 'advanced' && d.setting.name == 'site-logo-display' ) { 
//				return 'M.ciniki_web_main.showLogo(\'M.ciniki_web_main.showMenu();\',\'' + d.setting.value + '\');'; 
//			}
			if( s == 'pages' ) { 
//				if( d.page.name == 'about' && M.curBusiness.modules['ciniki.artgallery'] != null ) {
//					return 'M.ciniki_web_main.showPage(\'M.ciniki_web_main.showMenu();\',\'aboutmenu\');'; 
//				}
				if( d.page.name == 'about' ) {
					return 'M.startApp(\'ciniki.web.about\',null,\'M.ciniki_web_main.showMenu();\')';
				} else if( d.page.name == 'faq' ) {
					return 'M.startApp(\'ciniki.web.faq\',null,\'M.ciniki_web_main.showMenu();\')';
				}
				return 'M.ciniki_web_main.showPage(\'M.ciniki_web_main.showMenu();\',\'' + d.page.name + '\');'; 
			}
		};
		this.menu.addClose('Back');

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
		// The panel to allow the user to select a theme
		//
		this.theme = new M.panel('Color Scheme',
			'ciniki_web_main', 'theme',
			'mc', 'narrow', 'sectioned', 'ciniki.web.main.theme');
		this.theme.data = {'site-theme':'default'};
		this.theme.sections = {
			'_theme':{'label':'', 'fields':{
				'site-theme':{'label':'Color Scheme', 'type':'select', 'options':this.themesAvailable},
				}},
			'_save':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'theme\');'},
				}},
		};
		this.theme.fieldValue = this.fieldValue;
		this.theme.fieldHistoryArgs = this.fieldHistoryArgs;
		this.theme.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'theme\');');
		this.theme.addClose('Cancel');

		//
		// The panel to allow the user to select a layout
		//
		this.layout = new M.panel('Color Scheme',
			'ciniki_web_main', 'layout',
			'mc', 'narrow', 'sectioned', 'ciniki.web.main.layout');
		this.layout.data = {'site-layout':'default'};
		this.layout.sections = {
			'_layout':{'label':'', 'fields':{
				'site-layout':{'label':'Layout', 'type':'select', 'options':this.layoutsAvailable},
				}},
			'_save':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'layout\');'},
				}},
		};
		this.layout.fieldValue = this.fieldValue;
		this.layout.fieldHistoryArgs = this.fieldHistoryArgs;
		this.layout.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'layout\');');
		this.layout.addClose('Cancel');

		//
		// The panel to allow the user to select a theme
		//
		this.header = new M.panel('Header',
			'ciniki_web_main', 'header',
			'mc', 'narrow', 'sectioned', 'ciniki.web.main.header');
		this.header.data = {'site-header-image':'0'};
		this.header.sections = {
			'_image':{'label':'Image', 'fields':{
				'site-header-image':{'label':'', 'type':'image_id', 'controls':'all', 'hidelabel':'yes', 'history':'no'},
				}},
			'options':{'label':'Options', 'fields':{
				'site-header-title':{'label':'Display Business Name', 'type':'multitoggle', 'default':'yes', 'toggles':this.activeToggles},
				}},
			'_save':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'header\');'},
//				'delete':{'label':'Delete', 'fn':'M.ciniki_web_main.deleteHeaderImage();'},
				}},
		};
		this.header.fieldValue = this.fieldValue;
		this.header.fieldHistoryArgs = this.fieldHistoryArgs;
		this.header.addDropImage = function(iid) {
			this.setFieldValue('site-header-image', iid);
			return true;
		};
		this.header.deleteImage = this.deleteImage;
		this.header.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'header\');');
		this.header.addClose('Cancel');

		//
		// The options and information for the logo page
		//
//		this.logo = new M.panel('Business Logo',
//			'ciniki_web_main', 'logo',
//			'mc', 'medium', 'sectioned', 'ciniki.web.main.logo');
//		this.logo.data = {};
//		this.logo.sections = {
//			'options':{'label':'', 'fields':{
//				'site-logo-display':{'label':'Display Logo', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
//				}},
//			'_save':{'label':'', 'buttons':{
//				'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'logo\');'},
//				}},
//		};
//		this.logo.fieldValue = this.fieldValue;
//		this.logo.fieldHistoryArgs = this.fieldHistoryArgs;
//		this.logo.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'logo\');');
//		this.logo.addClose('Cancel');

		//
		// The panel to allow the user to setup google analytics
		//
		this.google = new M.panel('Google Settings',
			'ciniki_web_main', 'google',
			'mc', 'narrow', 'sectioned', 'ciniki.web.main.google');
		this.google.data = {'site-google-analytics-account':'0'};
		this.google.sections = {
			'_analytics':{'label':'Google Analytics User Account', 'fields':{
				'site-google-analytics-account':{'label':'', 'type':'text', 'hidelabel':'yes'},
				}},
			'_verification':{'label':'Google Meta Tag Verification', 'fields':{
				'site-google-site-verification':{'label':'', 'type':'text', 'hidelabel':'yes'},
				}},
			'_save':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'google\');'},
				}},
		};
		this.google.fieldValue = this.fieldValue;
		this.google.fieldHistoryArgs = this.fieldHistoryArgs;
		this.google.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'google\');');
		this.google.addClose('Cancel');

		//
		// The panel to allow the user to setup custom css
		//
		this.css = new M.panel('Custom CSS',
			'ciniki_web_main', 'css',
			'mc', 'medium', 'sectioned', 'ciniki.web.main.css');
		this.css.data = {'site-customer-css':''};
		this.css.sections = {
			'_css':{'label':'Custom CSS', 'fields':{
				'site-custom-css':{'label':'', 'type':'textarea', 'size':'large', 'hidelabel':'yes'},
				}},
			'_save':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'css\');'},
				}},
		};
		this.css.fieldValue = this.fieldValue;
		this.css.fieldHistoryArgs = this.fieldHistoryArgs;
		this.css.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'css\');');
		this.css.addClose('Cancel');

		//
		// The panel setup the SSL settings for the site
		//
		this.ssl = new M.panel('SSL',
			'ciniki_web_main', 'ssl',
			'mc', 'medium', 'sectioned', 'ciniki.web.main.ssl');
		this.ssl.data = {'site-customer-ssl':''};
		this.ssl.sections = {
			'_ssl':{'label':'Enable SSL', 'fields':{
				'site-ssl-active':{'label':'SSL Enabled', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				'site-ssl-force-cart':{'label':'SSL Cart Only', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				'site-ssl-force-account':{'label':'SSL Account Only', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				}},
			'_save':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'ssl\');'},
				}},
		};
		this.ssl.fieldValue = this.fieldValue;
		this.ssl.fieldHistoryArgs = this.fieldHistoryArgs;
		this.ssl.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'ssl\');');
		this.ssl.addClose('Cancel');

		//
		// The options and information for the home page
		//
		this.home = new M.panel('Home',
			'ciniki_web_main', 'home',
			'mc', 'medium', 'sectioned', 'ciniki.web.main.home');
		this.home.data = {};
		this.home.sections = {
			'options':{'label':'', 'fields':{
				'page-home-active':{'label':'Display Home Page', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				'page-home-gallery-latest':{'label':'Display Latest Work', 'active':'no', 'type':'multitoggle', 'default':'yes', 'toggles':this.activeToggles},
				'page-home-gallery-latest-title':{'label':'Latest Work Title', 'active':'no', 'type':'text', 'size':'small', 'hint':'Latest Work'},
				'page-home-gallery-random':{'label':'Display Random Example Work', 'active':'no', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				'page-home-gallery-random-title':{'label':'Random Example Work Title', 'active':'no', 'type':'text', 'size':'small', 'hint':'Example Work'},
				'page-home-latest-recipes':{'label':'Display Latest Recipes', 'active':'no', 'type':'multitoggle', 'default':'yes', 'toggles':this.activeToggles},
				'page-home-upcoming-events':{'label':'Display Upcoming Events', 'active':'no', 'type':'multitoggle', 'default':'yes', 'toggles':this.activeToggles},
				'page-home-upcoming-workshops':{'label':'Display Upcoming Workshops', 'active':'no', 'type':'multitoggle', 'default':'yes', 'toggles':this.activeToggles},
				'page-home-upcoming-artgalleryexhibitions':{'label':'Display Upcoming Exhibtions', 'active':'no', 'type':'multitoggle', 'default':'yes', 'toggles':this.activeToggles},
				'page-home-products-latest':{'label':'Display Latest Products', 'active':'no', 'type':'multitoggle', 'default':'yes', 'toggles':this.activeToggles},
				'page-home-products-latest-title':{'label':'Latest Products Title', 'active':'no', 'type':'text', 'size':'small', 'hint':'New Products'},
				}},
			'_slider':{'label':'Image Slider', 'active':'no', 'fields':{
				'page-home-slider':{'label':'Slider', 'active':'no', 'type':'select', 'options':{}},
				}},
			'_slider_buttons':{'label':'', 'visible':'no', 'type':'simplegrid', 'num_cols':1,
				'addTxt':'Manage Sliders',
				'addFn':'M.startApp(\'ciniki.web.sliders\',null,\'M.ciniki_web_main.showPage(null,"home");\');',
				},
			'_image':{'label':'Image', 'fields':{
				'page-home-image':{'label':'', 'type':'image_id', 'controls':'all', 'hidelabel':'yes', 'history':'no'},
				}},
			'_image_caption':{'label':'', 'fields':{
				'page-home-image-caption':{'label':'Caption', 'type':'text'},
				'page-home-image-url':{'label':'Link', 'type':'text'},
				}},
			'_content':{'label':'Welcome Message (optional)', 'fields':{
				'page-home-content':{'label':'', 'hidelabel':'yes', 'hint':'', 'type':'textarea', 'size':'large'},
				}},
			'redirects':{'label':'Redirect Home', 'active':'no', 'fields':{
				'page-home-url':{'label':'URL', 'type':'text'},
				}},
			'_save':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'home\');'},
				}},
		};
		this.home.fieldValue = this.fieldValue;
		this.home.fieldHistoryArgs = this.fieldHistoryArgs;
		this.home.addDropImage = function(iid) {
			this.setFieldValue('page-home-image', iid);
			return true;
		};
		this.home.deleteImage = this.deleteImage;
		this.home.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'home\');');
		this.home.addClose('Cancel');

		//
		// The options and information for the custom 001 page
		//
		this.custom = new M.panel('Custom',
			'ciniki_web_main', 'custom',
			'mc', 'medium', 'sectioned', 'ciniki.web.main.custom');
		this.custom.data = {};
		this.custom.number = 1;
		this.custom.sections = {
//			'options':{'label':'', 'fields':{
//				'page-custom-001-active':{'label':'Display Page', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
//				'page-custom-001-name':{'label':'Name', 'type':'text', 'hint':''},
//				'page-custom-001-permalink':{'label':'URL', 'type':'text', 'hint':''},
//				}},
//			'_image':{'label':'Image', 'fields':{
//				'page-custom-001-image':{'label':'', 'type':'image_id', 'controls':'all', 'hidelabel':'yes', 'history':'no'},
//				}},
//			'_image_caption':{'label':'', 'fields':{
//				'page-custom-001-image-caption':{'label':'Caption', 'type':'text'},
//				}},
//			'_content':{'label':'Content', 'fields':{
//				'page-custom-001-content':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'large'},
//				}},
//			'_save':{'label':'', 'buttons':{
//				'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'custom\');'},
//				}},
		};
		this.custom.fieldValue = this.fieldValue;
		this.custom.fieldHistoryArgs = this.fieldHistoryArgs;
//		this.custom.addDropImage = function(iid) {
//			this.setFieldValue('page-custom-001-image', iid);
//			return true;
//		};
		this.custom.deleteImage = this.deleteImage;
		this.custom.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'custom\');');
		this.custom.addClose('Cancel');

		//
		// The options and information for the contact page
		//
		this.contact = new M.panel('Contact',
			'ciniki_web_main', 'contact',
			'mc', 'medium', 'sectioned', 'ciniki.web.main.Contact');
		this.contact.data = {};
		this.contact.sections = {
			'options':{'label':'', 'fields':{
				'page-contact-active':{'label':'Display Contact Page', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				}},
			'_display':{'label':'Business Information', 'fields':{
				'page-contact-business-name-display':{'label':'Business Name', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles, 'hint':''},
				'page-contact-person-name-display':{'label':'Contact Name', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles, 'hint':''},
				'page-contact-address-display':{'label':'Address', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles, 'hint':''},
				'page-contact-phone-display':{'label':'Phone', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles, 'hint':''},
				'page-contact-fax-display':{'label':'Fax', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles, 'hint':''},
				'page-contact-email-display':{'label':'Email', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles, 'hint':''},
				}},
			'_users':{'label':'Business Employees', 'active':'no', 'fields':{
				}},
			'_users_display':{'label':'', 'active':'no', 'fields':{
				'page-contact-bios-display':{'label':'Employee List', 'type':'multitoggle', 'default':'list', 'toggles':{'list':'2 Column', 'cilist':'3 Column'}, 'hint':''},
				}},
			'_map':{'label':'Location Map', 'visible':'yes', 'fields':{
				'page-contact-google-map':{'label':'Display Map', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				'page-contact-map-latitude':{'label':'Latitude', 'type':'text', 'size':'small'},
				'page-contact-map-longitude':{'label':'Longitude', 'type':'text', 'size':'small'},
				}},
			'_map_buttons':{'label':'', 'buttons':{
				'_latlong':{'label':'Lookup Lat/Long', 'fn':'M.ciniki_web_main.contact.lookupLatLong();'},
				}},
			'_content':{'label':'Content', 'fields':{
				'page-contact-content':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'large'},
				}},
			'_mailchimp':{'label':'Mailchimp', 'fields':{
				'page-contact-mailchimp-signup':{'label':'Enable Mailchimp', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles, 'hint':''},
				'page-contact-mailchimp-submit-url':{'label':'Submit URL', 'type':'text'},
				}},
			'_save':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'contact\');'},
				}},
		};
		this.contact.fieldValue = this.fieldValue;
		this.contact.fieldHistoryArgs = this.fieldHistoryArgs;
		this.contact.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'contact\');');
		this.contact.addClose('Cancel');
		this.contact.lookupLatLong = function() {
			M.startLoad();
			if( document.getElementById('googlemaps_js') == null) {
				var script = document.createElement("script");
				script.id = 'googlemaps_js';
				script.type = "text/javascript";
				script.src = "https://maps.googleapis.com/maps/api/js?key=" + M.curBusiness.settings['googlemapsapikey'] + "&sensor=false&callback=M.ciniki_web_main.contact.lookupGoogleLatLong";
				document.body.appendChild(script);
			} else {
				this.lookupGoogleLatLong();
			}
		};

		this.contact.lookupGoogleLatLong = function() {
			var address = this.business_address;
			var geocoder = new google.maps.Geocoder();
			geocoder.geocode( { 'address': address}, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK) {
					M.ciniki_web_main.contact.setFieldValue('page-contact-map-latitude', results[0].geometry.location.lat());
					M.ciniki_web_main.contact.setFieldValue('page-contact-map-longitude', results[0].geometry.location.lng());
					M.stopLoad();
				} else {
					alert('We were unable to lookup your latitude/longitude, please check your address in Settings: ' + status);
					M.stopLoad();
				}
			});	
		};

		//
		// The options and information for the Features page
		//
		this.features = new M.panel('Features',
			'ciniki_web_main', 'features',
			'mc', 'narrow', 'sectioned', 'ciniki.web.main.features');
		this.features.data = {};
		this.features.sections = {
			'options':{'label':'Options', 'fields':{
				'page-features-active':{'label':'Show features', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				}},
			'_save':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'features\');'},
				}},
		};
		this.features.fieldValue = this.fieldValue;
		this.features.fieldHistoryArgs = this.fieldHistoryArgs;
		this.features.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'features\');');
		this.features.addClose('Cancel');

		//
		// The options and information for the Events page
		//
		this.events = new M.panel('Events',
			'ciniki_web_main', 'events',
			'mc', 'narrow', 'sectioned', 'ciniki.web.main.events');
		this.events.data = {};
		this.events.sections = {
			'options':{'label':'Options', 'fields':{
				'page-events-active':{'label':'Show events', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				'page-events-past':{'label':'Include past events', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				}},
			'_save':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'events\');'},
				}},
		};
		this.events.fieldValue = this.fieldValue;
		this.events.fieldHistoryArgs = this.fieldHistoryArgs;
		this.events.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'events\');');
		this.events.addClose('Cancel');

		//
		// The options and information for the Workshops page
		//
		this.workshops = new M.panel('Workshops',
			'ciniki_web_main', 'workshops',
			'mc', 'narrow', 'sectioned', 'ciniki.web.main.workshops');
		this.workshops.data = {};
		this.workshops.sections = {
			'options':{'label':'Options', 'fields':{
				'page-workshops-active':{'label':'Show workshops', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				'page-workshops-past':{'label':'Include past workshops', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				}},
			'_save':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'workshops\');'},
				}},
		};
		this.workshops.fieldValue = this.fieldValue;
		this.workshops.fieldHistoryArgs = this.fieldHistoryArgs;
		this.workshops.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'workshops\');');
		this.workshops.addClose('Cancel');

		//
		// The options and information for the friends page
		//
		this.friends = new M.panel('Friends',
			'ciniki_web_main', 'friends',
			'mc', 'narrow', 'sectioned', 'ciniki.web.main.friends');
		this.friends.data = {};
		this.friends.sections = {
			'options':{'label':'', 'fields':{
				'page-friends-active':{'label':'Display links page', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				}},
			'_save':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'friends\');'},
				}},
		};
		this.friends.fieldValue = this.fieldValue;
		this.friends.fieldHistoryArgs = this.fieldHistoryArgs;
		this.friends.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'friends\');');
		this.friends.addClose('Cancel');

		//
		// The options and information for the directory page
		//
		this.directory = new M.panel('Directory',
			'ciniki_web_main', 'directory',
			'mc', 'narrow', 'sectioned', 'ciniki.web.main.directory');
		this.directory.data = {};
		this.directory.sections = {
			'options':{'label':'', 'fields':{
				'page-directory-active':{'label':'Display Directory Page', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				}},
			'_save':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'directory\');'},
				}},
		};
		this.directory.fieldValue = this.fieldValue;
		this.directory.fieldHistoryArgs = this.fieldHistoryArgs;
		this.directory.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'directory\');');
		this.directory.addClose('Cancel');

		//
		// The options and information for the links page
		//
		this.links = new M.panel('Links',
			'ciniki_web_main', 'links',
			'mc', 'narrow', 'sectioned', 'ciniki.web.main.links');
		this.links.data = {};
		this.links.sections = {
			'options':{'label':'', 'fields':{
				'page-links-active':{'label':'Display Links Page', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				'page-links-categories-format':{'label':'Categories Format', 'type':'multitoggle', 'default':'cloud', 'toggles':this.linksDisplayToggles},
				'page-links-tags-format':{'label':'Tags Format', 'type':'multitoggle', 'default':'cloud', 'toggles':this.linksDisplayToggles},
				}},
			'_save':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'links\');'},
				}},
		};
		this.links.fieldValue = this.fieldValue;
		this.links.fieldHistoryArgs = this.fieldHistoryArgs;
		this.links.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'links\');');
		this.links.addClose('Cancel');

		//
		// The options and information for the gallery page
		//
		this.gallery = new M.panel('Gallery',
			'ciniki_web_main', 'gallery',
			'mc', 'narrow', 'sectioned', 'ciniki.web.main.gallery');
		this.gallery.data = {};
		this.gallery.sections = {
			'options':{'label':'', 'fields':{
				'page-gallery-active':{'label':'Display Gallery', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				'page-gallery-name':{'label':'Name', 'type':'text', 'hint':'default is Gallery'},
				'page-gallery-artcatalog-split':{'label':'Split Menu', 'active':'no', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				'page-gallery-artcatalog-format':{'label':'Format', 'active':'no', 'type':'multitoggle', 'default':'icons', 'toggles':{'icons':'Icons', 'list':'List'}},
				}},
			'social':{'label':'Social Media', 'visible':'no', 'fields':{
				'page-gallery-share-buttons':{'label':'Sharing', 'active':'no', 'type':'multitoggle', 'default':'yes', 'toggles':this.activeToggles},
				}},
			'_save':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'gallery\');'},
				}},
		};
		this.gallery.fieldValue = this.fieldValue;
		this.gallery.fieldHistoryArgs = this.fieldHistoryArgs;
		this.gallery.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'gallery\');');
		this.gallery.addClose('Cancel');

		//
		// The options and information for the products page
		//
		this.products = new M.panel('Products',
			'ciniki_web_main', 'products',
			'mc', 'medium', 'sectioned', 'ciniki.web.main.products');
		this.products.data = {};
		this.products.sections = {
			'options':{'label':'', 'fields':{
				'page-products-active':{'label':'Display Products', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				'page-products-name':{'label':'Name', 'type':'text', 'hint':'default is Products'},
				'page-products-categories-size':{'label':'Category Thumbnail Size', 'type':'toggle', 'default':'auto', 'toggles':this.productThumbnailToggles},
				'page-products-subcategories-size':{'label':'Sub-Category Thumbnail Size', 'type':'toggle', 'default':'auto', 'toggles':this.productThumbnailToggles},
				}},
			'social':{'label':'Social Media', 'visible':'yes', 'fields':{
				'page-products-share-buttons':{'label':'Sharing', 'active':'yes', 'type':'multitoggle', 'default':'yes', 'toggles':this.activeToggles},
				}},
			'_save':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'products\');'},
				}},
		};
		this.products.fieldValue = this.fieldValue;
		this.products.fieldHistoryArgs = this.fieldHistoryArgs;
		this.products.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'products\');');
		this.products.addClose('Cancel');

		//
		// The options and information for the recipes page
		//
		this.recipes = new M.panel('Recipes',
			'ciniki_web_main', 'recipes',
			'mc', 'medium', 'sectioned', 'ciniki.web.main.recipes');
		this.recipes.data = {};
		this.recipes.sections = {
			'options':{'label':'', 'fields':{
				'page-recipes-active':{'label':'Display Recipes', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				'page-recipes-name':{'label':'Name', 'type':'text', 'hint':'default is Recipes'},
				'page-recipes-tags':{'label':'Tags', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				}},
			'_save':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'recipes\');'},
				}},
		};
		this.recipes.fieldValue = this.fieldValue;
		this.recipes.fieldHistoryArgs = this.fieldHistoryArgs;
		this.recipes.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'recipes\');');
		this.recipes.addClose('Cancel');

		//
		// The options and information for the blog page
		//
		this.blog = new M.panel('Blog',
			'ciniki_web_main', 'blog',
			'mc', 'medium', 'sectioned', 'ciniki.web.main.blog');
		this.blog.data = {};
		this.blog.sections = {
			'options':{'label':'', 'fields':{
				'page-blog-active':{'label':'Display Blog', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				'page-blog-name':{'label':'Name', 'type':'text', 'hint':'default is Blog'},
				}},
			'_save':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'blog\');'},
				}},
		};
		this.blog.fieldValue = this.fieldValue;
		this.blog.fieldHistoryArgs = this.fieldHistoryArgs;
		this.blog.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'recipes\');');
		this.blog.addClose('Cancel');

		//
		// The options and information for the members page
		//
		this.members = new M.panel('Members',
			'ciniki_web_main', 'members',
			'mc', 'medium', 'sectioned', 'ciniki.web.main.members');
		this.members.data = {};
		this.members.sections = {
			'options':{'label':'', 'fields':{
				'page-members-active':{'label':'Display Members', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				'page-members-name':{'label':'Name', 'type':'text', 'hint':'Members'},
				'page-members-categories-display':{'label':'Display Member Categories', 'active':'no', 'type':'toggle', 'default':'no', 'toggles':{
					'no':'No',
					'wordlist':'List',
					'wordcloud':'Cloud',
					}},
				'page-members-list-format':{'label':'Listing Content', 'type':'select', 'options':{
					'shortbio':'Short Bio',
					'shortbio-links':'Short Bio, Links',
					'shortbio-townsprovinces-links':'Short Bio, Town, Links',
					'shortbio-emails-links':'Short Bio, Emails, Links',
					'shortbio-townsprovinces-emails-links':'Short Bio, Town, Emails, Links',
					'shortbio-phones-emails-links':'Short Bio, Phones, Emails, Links',
					'shortbio-blank-townsprovinces-phones-emails-links':'Short Bio, Town, Phones, Emails, Links',
					'shortbio-blank-addresses-phones-emails-links':'Short Bio, Addresses, Phones, Emails, Links',
					'addresses-blank-shortbio-phones-emails-links':'Addresses, Short Bio, Phones, Emails, Links',
					}},
				'page-members-membership-details':{'label':'Display Membership Information', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				}},
			'_save':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'members\');'},
				}},
		};
		this.members.fieldValue = this.fieldValue;
		this.members.fieldHistoryArgs = this.fieldHistoryArgs;
		this.members.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'members\');');
		this.members.addClose('Cancel');

		//
		// The options and information for the dealers page
		//
		this.dealers = new M.panel('Dealers',
			'ciniki_web_main', 'dealers',
			'mc', 'medium', 'sectioned', 'ciniki.web.main.dealers');
		this.dealers.data = {};
		this.dealers.sections = {
			'options':{'label':'', 'fields':{
				'page-dealers-active':{'label':'Display Dealers', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				'page-dealers-name':{'label':'Name', 'type':'text', 'hint':'Dealers'},
//				'page-dealers-categories-display':{'label':'Display Dealer Categories', 'type':'toggle', 'default':'no', 'toggles':{
//					'no':'No',
//					'wordlist':'List',
//					'wordcloud':'Cloud',
//					}},
				'page-dealers-locations-map-names':{'label':'Expand Short Names', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				'page-dealers-locations-display':{'label':'Display Dealer Locations', 'type':'toggle', 'default':'no', 'toggles':{
					'no':'No',
					'wordlist':'List',
					'wordcloud':'Cloud',
					}},
				'page-dealers-list-format':{'label':'Listing Content', 'type':'select', 'options':{
					'shortbio':'Short Bio',
					'shortbio-blank-addressesnl-phones-emails-links':'Short Bio, Addresses, Phones, Emails, Links',
					'addressesnl-blank-shortbio-phones-emails-links':'Addresses, Short Bio, Phones, Emails, Links',
					}},
			}},
			'_save':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'dealers\');'},
				}},
		};
		this.dealers.fieldValue = this.fieldValue;
		this.dealers.fieldHistoryArgs = this.fieldHistoryArgs;
		this.dealers.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'dealers\');');
		this.dealers.addClose('Cancel');

		//
		// The options and information for the members news page
		//
		this.memberblog = new M.panel('Members',
			'ciniki_web_main', 'memberblog',
			'mc', 'narrow', 'sectioned', 'ciniki.web.main.memberblog');
		this.memberblog.data = {};
		this.memberblog.sections = {
			'options':{'label':'', 'fields':{
				'page-memberblog-active':{'label':'Display Member News', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				'page-memberblog-name':{'label':'Name', 'type':'text', 'hint':'Member News'},
				}},
			'_save':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'memberblog\');'},
				}},
		};
		this.memberblog.fieldValue = this.fieldValue;
		this.memberblog.fieldHistoryArgs = this.fieldHistoryArgs;
		this.memberblog.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'memberblog\');');
		this.memberblog.addClose('Cancel');

		//
		// The options and information for the sponsors page
		//
		this.sponsors = new M.panel('Sponsors',
			'ciniki_web_main', 'sponsors',
			'mc', 'narrow', 'sectioned', 'ciniki.web.main.sponsors');
		this.sponsors.data = {};
		this.sponsors.sections = {
			'options':{'label':'', 'fields':{
				'page-sponsors-active':{'label':'Display Sponsors', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				}},
			'_save':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'sponsors\');'},
				}},
		};
		this.sponsors.fieldValue = this.fieldValue;
		this.sponsors.fieldHistoryArgs = this.fieldHistoryArgs;
		this.sponsors.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'sponsors\');');
		this.sponsors.addClose('Cancel');

		//
		// The options and information for the sponsors page
		//
		this.newsletters = new M.panel('Newsletters',
			'ciniki_web_main', 'newsletters',
			'mc', 'narrow', 'sectioned', 'ciniki.web.main.newsletters');
		this.newsletters.data = {};
		this.newsletters.sections = {
			'options':{'label':'', 'fields':{
				'page-newsletters-active':{'label':'Display Newsletters', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				}},
			'_save':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'newsletters\');'},
				}},
		};
		this.newsletters.fieldValue = this.fieldValue;
		this.newsletters.fieldHistoryArgs = this.fieldHistoryArgs;
		this.newsletters.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'newsletters\');');
		this.newsletters.addClose('Cancel');

		//
		// The options and information for the sponsors page
		//
		this.surveys = new M.panel('Newsletters',
			'ciniki_web_main', 'surveys',
			'mc', 'narrow', 'sectioned', 'ciniki.web.main.surveys');
		this.surveys.data = {};
		this.surveys.sections = {
			'options':{'label':'', 'fields':{
				'page-surveys-active':{'label':'Enable Surveys', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				}},
			'_save':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'surveys\');'},
				}},
		};
		this.surveys.fieldValue = this.fieldValue;
		this.surveys.fieldHistoryArgs = this.fieldHistoryArgs;
		this.surveys.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'surveys\');');
		this.surveys.addClose('Cancel');

		//
		// The options and information for the exhibitions pages
		//
		this.exhibitions = new M.panel('Exhibitions',
			'ciniki_web_main', 'exhibitions',
			'mc', 'medium', 'sectioned', 'ciniki.web.main.exhibitions');
		this.exhibitions.data = {};
		this.exhibitions.sections = {
			'options':{'label':'Exhibition', 'fields':{
				'page-exhibitions-exhibition':{'label':'Display Exhibition', 'type':'select', 'options':{}},
				}},
			'exhibitors':{'label':'Exhibitors', 'fields':{
				'page-exhibitions-exhibitors-active':{'label':'Display Exhibitors', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				'page-exhibitions-exhibitors-name':{'label':'Name', 'type':'text'},
				}},
			'sponsors':{'label':'Sponsors', 'fields':{
				'page-exhibitions-sponsors-active':{'label':'Display Sponsors', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				}},
			'tourexhibitors':{'label':'Tour Exhibitors', 'fields':{
				'page-exhibitions-tourexhibitors-active':{'label':'Display Tour Exhibitors', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				'page-exhibitions-tourexhibitors-name':{'label':'Name', 'type':'text'},
				}},
			'_save':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'exhibitions\');'},
				}},
		};
		this.exhibitions.fieldValue = this.fieldValue;
		this.exhibitions.fieldHistoryArgs = this.fieldHistoryArgs;
		this.exhibitions.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'exhibitions\');');
		this.exhibitions.addClose('Cancel');

		//
		// The options and information for the exhibitions pages
		//
		this.artgalleryexhibitions = new M.panel('Exhibitions',
			'ciniki_web_main', 'artgalleryexhibitions',
			'mc', 'medium', 'sectioned', 'ciniki.web.main.artgalleryexhibitions');
		this.artgalleryexhibitions.data = {};
		this.artgalleryexhibitions.sections = {
			'options':{'label':'Exhibition', 'fields':{
				'page-artgalleryexhibitions-active':{'label':'Display Exhibitions', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				'page-artgalleryexhibitions-past':{'label':'Include Past Exhibitions', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				'page-artgalleryexhibitions-application-details':{'label':'Display Application Information', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				}},
			'_image':{'label':'Image', 'fields':{
				'page-artgalleryexhibitions-image':{'label':'', 'type':'image_id', 'controls':'all', 'hidelabel':'yes', 'history':'no'},
				}},
			'_image_caption':{'label':'', 'fields':{
				'page-artgalleryexhibitions-image-caption':{'label':'Caption', 'type':'text'},
				}},
			'_content':{'label':'Content', 'fields':{
				'page-artgalleryexhibitions-content':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'large'},
				}},
			'_save':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'artgalleryexhibitions\');'},
				}},
		};
		this.artgalleryexhibitions.fieldValue = this.fieldValue;
		this.artgalleryexhibitions.fieldHistoryArgs = this.fieldHistoryArgs;
		this.artgalleryexhibitions.addDropImage = function(iid) {
			this.setFieldValue('page-artgalleryexhibitions-image', iid);
			return true;
		};
		this.artgalleryexhibitions.deleteImage = this.deleteImage;
		this.artgalleryexhibitions.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'artgalleryexhibitions\');');
		this.artgalleryexhibitions.addClose('Cancel');

		//
		// The options and information for the exhibitions pages
		//
		this.courses = new M.panel('Courses',
			'ciniki_web_main', 'courses',
			'mc', 'medium', 'sectioned', 'ciniki.web.main.courses');
		this.courses.data = {};
		this.courses.sections = {
			'options':{'label':'Courses', 'fields':{
				'page-courses-active':{'label':'Display Courses', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				'page-courses-name':{'label':'Name', 'type':'text', 'hint':'Courses'},
				'page-courses-upcoming-active':{'label':'Upcoming Courses', 'type':'multitoggle', 'default':'yes', 'toggles':this.activeToggles},
//				'page-courses-upcoming-name':{'label':'Name', 'type':'text', 'hint':'Upcoming Courses'},
				'page-courses-current-active':{'label':'Current Courses', 'type':'multitoggle', 'default':'yes', 'toggles':this.activeToggles},
//				'page-courses-current-name':{'label':'Name', 'type':'text', 'hint':'Current Courses'},
				'page-courses-past-active':{'label':'Past Courses', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				'page-courses-catalog-download-active':{'label':'Display Catalog Download', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				'page-courses-level-display':{'label':'Display course level', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
//				'page-courses-past-name':{'label':'Name', 'type':'text', 'hint':'Past Courses'},
				}},
			'_content':{'label':'Content', 'fields':{
				'page-courses-content':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'large'},
				}},
			'_image':{'label':'Image', 'fields':{
				'page-courses-image':{'label':'', 'type':'image_id', 'controls':'all', 'hidelabel':'yes', 'history':'no'},
				}},
			'_image_caption':{'label':'', 'fields':{
				'page-courses-image-caption':{'label':'Caption', 'type':'text'},
				}},
			'subpages':{'label':'', 'visible':'yes', 'list':{
				}},
			'_save':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'courses\');'},
				}},
		};
		this.courses.fieldValue = this.fieldValue;
		this.courses.fieldHistoryArgs = this.fieldHistoryArgs;
		this.courses.addDropImage = function(iid) {
			this.setFieldValue('page-courses-image', iid);
			return true;
		};
		this.courses.deleteImage = this.deleteImage;
		this.courses.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'courses\');');
		this.courses.addClose('Cancel');

		this.coursestype = new M.panel('Course Types',
			'ciniki_web_main', 'coursestype',
			'mc', 'medium', 'sectioned', 'ciniki.web.main.coursestype');
		this.coursestype.type_name = '';
		this.coursestype.data = {};
		this.coursestype.rotateImage = M.ciniki_web_main.rotateImage;
		this.coursestype.deleteImage = M.ciniki_web_main.deleteImage;
		this.coursestype.uploadImage = function(i) { return 'M.ciniki_web_main.uploadDropImagesCoursesType(\'' + i + '\');' };
		this.coursestype.uploadDropFn = function() { return M.ciniki_web_main.uploadDropImagesCoursesType; };
		this.coursestype.sections = {};
		this.coursestype.fieldValue = this.fieldValue;
		this.coursestype.fieldHistoryArgs = this.fieldHistoryArgs;
		this.coursestype.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'coursestype\');');
		this.coursestype.addClose('Cancel');

		//
		// The options and information for the exhibitions pages
		//
		this.classes = new M.panel('Classes',
			'ciniki_web_main', 'classes',
			'mc', 'medium', 'sectioned', 'ciniki.web.main.classes');
		this.classes.data = {};
		this.classes.sections = {
			'options':{'label':'Courses', 'fields':{
				'page-classes-active':{'label':'Display Classes', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				'page-classes-name':{'label':'Menu Name', 'type':'text', 'hint':'Classes'},
				'page-classes-title':{'label':'Page Title', 'type':'text', 'hint':'Available Classes'},
				}},
			'_save':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'classes\');'},
				}},
		};
		this.classes.fieldValue = this.fieldValue;
		this.classes.fieldHistoryArgs = this.fieldHistoryArgs;
		this.classes.deleteImage = this.deleteImage;
		this.classes.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'classes\');');
		this.classes.addClose('Cancel');

		//
		// The options and information for the courses registration pages
		//
		this.coursesregistration = new M.panel('Course Registration',
			'ciniki_web_main', 'coursesregistration',
			'mc', 'medium', 'sectioned', 'ciniki.web.main.coursesregistration');
		this.coursesregistration.data = {};
		this.coursesregistration.sections = {
			'registration':{'label':'Registration', 'fields':{
				'page-courses-registration-active':{'label':'Display Registration Info', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				}},
			'_image':{'label':'Registration Image', 'fields':{
				'page-courses-registration-image':{'label':'', 'type':'image_id', 'controls':'all', 'hidelabel':'yes', 'history':'no'},
				}},
			'_image_caption':{'label':'', 'fields':{
				'page-courses-registration-image-caption':{'label':'Caption', 'type':'text'},
				}},
			'_save':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'coursesregistration\');'},
				}},
		};
		this.coursesregistration.fieldValue = this.fieldValue;
		this.coursesregistration.fieldHistoryArgs = this.fieldHistoryArgs;
		this.coursesregistration.addDropImage = function(iid) {
			this.setFieldValue('page-courses-registration-image', iid);
			return true;
		};
		this.coursesregistration.deleteImage = this.deleteImage;
		this.coursesregistration.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'coursesregistration\');');
		this.coursesregistration.addClose('Cancel');

		//
		// The options and information for the downloads page
		//
		this.downloads = new M.panel('Downloads',
			'ciniki_web_main', 'downloads',
			'mc', 'medium', 'sectioned', 'ciniki.web.main.downloads');
		this.downloads.data = {};
		this.downloads.sections = {
			'options':{'label':'', 'fields':{
				'page-downloads-active':{'label':'Display Downloads', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				'page-downloads-name':{'label':'Name', 'type':'text', 'hint':'default is Downloads'},
				}},
			'_save':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'downloads\');'},
				}},
		};
		this.downloads.fieldValue = this.fieldValue;
		this.downloads.fieldHistoryArgs = this.fieldHistoryArgs;
		this.downloads.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'downloads\');');
		this.downloads.addClose('Cancel');

		//
		// The options and information for the customer account page
		//
		this.account = new M.panel('Customer Account',
			'ciniki_web_main', 'account',
			'mc', 'narrow', 'sectioned', 'ciniki.web.main.account');
		this.account.data = {};
		this.account.sections = {
//			'info':{'label':'', 'html':'If you want to allow customers the ability to login and manage their account
			'options':{'label':'', 'fields':{
				'page-account-active':{'label':'Customer Logins', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				}},
			'redirect':{'label':'On login', 'fields':{
				'page-account-signin-redirect':{'label':'Redirect to', 'type':'select', 'options':{}},
				}},
			'welcome':{'label':'Sign in Greeting', 'fields':{
				'page-account-content':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'medium', 
					'hint':'This appears when the customer signs into your website'},
				}},
			'subscriptions':{'label':'Subscription Message', 'active':'no', 'fields':{
				'page-account-content-subscriptions':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'medium', 'hint':''},
				}},
			'_save':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'account\');'},
				}},
		};
		this.account.fieldValue = this.fieldValue;
		this.account.fieldHistoryArgs = this.fieldHistoryArgs;
		this.account.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'account\');');
		this.account.addClose('Cancel');

		//
		// The options for the shopping cart
		//
		this.cart = new M.panel('Shopping Cart',
			'ciniki_web_main', 'cart',
			'mc', 'narrow', 'sectioned', 'ciniki.web.main.cart');
		this.cart.data = {};
		this.cart.sections = {
			'options':{'label':'', 'fields':{
				'page-cart-active':{'label':'Enable Cart', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				'page-cart-product-search':{'label':'Product Search', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				'page-cart-product-list':{'label':'Product List', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				}},
			'_inventory':{'label':'Current Inventory Visible To', 'fields':{
				'page-cart-inventory-customers-display':{'label':'Customers', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				'page-cart-inventory-members-display':{'label':'Members', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				'page-cart-inventory-dealers-display':{'label':'Dealers', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				'page-cart-inventory-distributors-display':{'label':'Distributors', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				}},
			'_save':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'cart\');'},
				}},
		};
		this.cart.fieldValue = this.fieldValue;
		this.cart.fieldHistoryArgs = this.fieldHistoryArgs;
		this.cart.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'cart\');');
		this.cart.addClose('Cancel');

		//
		// The options and information for the signup page
		//
		this.signup = new M.panel('Signup',
			'ciniki_web_main', 'signup',
			'mc', 'medium', 'sectioned', 'ciniki.web.main.signup');
		this.signup.data = {};
		this.signup.sections = {
			'options':{'label':'', 'fields':{
				'page-signup-active':{'label':'Display Sign Up Page', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				'page-signup-menu':{'label':'Show in Menu', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				}},
			'_content':{'label':'Content', 'fields':{
				'page-signup-content':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'large'},
				}},
			'_agreement':{'label':'Agreement', 'fields':{
				'page-signup-agreement':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'medium'},
				}},
			'_submit':{'label':'Form Submitted Message', 'fields':{
				'page-signup-submit':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'medium'},
				}},
			'_success':{'label':'Success Message', 'fields':{
				'page-signup-success':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'medium'},
				}},
			'_save':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'signup\');'},
				}},
		};
		this.signup.fieldValue = this.fieldValue;
		this.signup.fieldHistoryArgs = this.fieldHistoryArgs;
		this.signup.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'signup\');');
		this.signup.addClose('Cancel');

		//
		// The options and information for the api page
		//
		this.api = new M.panel('API',
			'ciniki_web_main', 'api',
			'mc', 'medium', 'sectioned', 'ciniki.web.main.api');
		this.api.data = {};
		this.api.sections = {
			'options':{'label':'Options', 'fields':{
				'page-api-active':{'label':'Active', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
				}},
			'_save':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'api\');'},
				}},
		};
		this.api.fieldValue = this.fieldValue;
		this.api.fieldHistoryArgs = this.fieldHistoryArgs;
		this.api.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'api\');');
		this.api.addClose('Cancel');
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
		var appContainer = M.createContainer(ap, 'ciniki_web_main', 'yes');
		if( appContainer == null ) {
			alert('App Error');
			return false;
		} 

		this.showMenu(cb);
	}

	this.showMenu = function(cb) {
		//
		// If the user is a sysadmin, then add the clear web cache button
		// This may become available to users, but might be too complicated
		//
		if( M.userPerms&0x01 == 0x01 ) {
			this.menu.size = 'medium mediumaside';
			this.menu.sections.settings.aside = 'yes';
			this.menu.sections.pages.aside = 'yes';
			this.menu.sections.adm = {'label':'Admin Options', 'list':{
				'google':{'label':'Google Settings', 'fn':'M.ciniki_web_main.showSiteSettings(\'M.ciniki_web_main.showMenu();\',\'google\');' },
				'ssl':{'label':'SSL', 'fn':'M.ciniki_web_main.showSiteSettings(\'M.ciniki_web_main.showMenu();\',\'ssl\');'},
				'css':{'label':'Custom CSS', 'fn':'M.ciniki_web_main.showSiteSettings(\'M.ciniki_web_main.showMenu();\',\'css\');'},
				'layout':{'label':'Layout', 'fn':'M.ciniki_web_main.showLayouts(\'M.ciniki_web_main.showMenu();\');'},
				}};
			this.menu.sections.admin = {'label':'Admin Options', 'buttons':{
				'clearimagecache':{'label':'Clear Image Cache', 'fn':'M.ciniki_web_main.clearImageCache();'},
				'clearcontentcache':{'label':'Clear Content Cache', 'fn':'M.ciniki_web_main.clearContentCache();'},
				}};
			this.home.sections.redirects.active = 'yes';
		} else {
			this.menu.size = 'medium';
			this.menu.sections.settings.aside = 'no';
			this.menu.sections.pages.aside = 'no';
			this.home.sections.redirects.active = 'no';
		}
		
		//
		// Load domain list
		//
		var rsp = M.api.getJSONCb('ciniki.web.siteSettings', {'business_id':M.curBusinessID}, function(rsp) {
			if( rsp.stat != 'ok' ) {
				M.api.err(rsp);
				return false;
			}
			M.ciniki_web_main.menu.data.pages = rsp.pages;

			M.ciniki_web_main.menu.data.settings = [];
			for(i in rsp.settings) {
				if( rsp.settings[i].setting.name == 'theme' ) {
					M.ciniki_web_main.menu.data.settings[i] = rsp.settings[i];
				}
				if( rsp.settings[i].setting.name == 'layout' ) {
					M.ciniki_web_main.layout.data['site-layout'] = rsp.settings[i].setting.value;
				}
			}
//			M.ciniki_web_main.menu.data.settings = rsp.settings;
			M.ciniki_web_main.header.data = {};
			for(i in rsp.header) {
				M.ciniki_web_main.header.data[rsp.header[i].setting.name] = rsp.header[i].setting.value;
			}
			if( rsp.settings['page-home-url'] != null ) {
				M.ciniki_web_main.header.data['page-home-url'] = rsp.settings['page-home-url'];
			}
			M.ciniki_web_main.menu.data.advanced = rsp.advanced;
			
			//
			// Allow sysadmins to mark a site as featured for the home page
			//
			if( M.userPerms&0x01 == 0x01 ) {
				if( rsp.featured == 'yes' ) {
					M.ciniki_web_main.menu.sections.admin.buttons.featured = {'label':'Remove Featured', 'fn':'M.ciniki_web_main.removeFeatured();'};
				} else {
					M.ciniki_web_main.menu.sections.admin.buttons.featured = {'label':'Make Featured', 'fn':'M.ciniki_web_main.makeFeatured();'};
				}
			}

			M.ciniki_web_main.menu.refresh();
			M.ciniki_web_main.menu.show(cb);
		});
	}

	this.showThemes = function(cb, themeName) {
		this.theme.reset();
		this.theme.data = {'site-theme':themeName};
		this.theme.refresh();
		this.theme.show(cb);
	};

	this.showLayouts = function(cb) {
		this.layout.refresh();
		this.layout.show(cb);
	};

	this.showHeader = function(cb) {
		this.header.refresh();
		this.header.show(cb);
	};

	this.showPage = function(cb, page, subpage, subpagetitle) {
		if( page.match(/^custom-/) ) {
			return this.showCustom(cb, page, subpage, subpagetitle);
		}
		this[page].reset();
		if( cb != null ) {
			this[page].cb = cb;
		}

		if( page == 'coursestype' && subpage != null ) {
			var rsp = M.api.getJSONCb('ciniki.web.pageSettingsGet', 
				{'business_id':M.curBusinessID, 'page':'courses-' + subpage, 'content':'yes'}, function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					}
					M.ciniki_web_main.showPageFinish(cb, page, subpage, subpagetitle, rsp);
				});
		} else if( page == 'coursesregistration' ) {
			var rsp = M.api.getJSONCb('ciniki.web.pageSettingsGet', 
				{'business_id':M.curBusinessID, 'page':'courses-registration', 'content':'yes'}, function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					}
					M.ciniki_web_main.showPageFinish(cb, page, subpage, subpagetitle, rsp);
				});
		} else {
			var rsp = M.api.getJSONCb('ciniki.web.pageSettingsGet', 
				{'business_id':M.curBusinessID, 'page':page, 'content':'yes'}, function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					}
					M.ciniki_web_main.showPageFinish(cb, page, subpage, subpagetitle, rsp);
				});
		}
	}

	this.showPageFinish = function(cb, page, subpage, subpagetitle, rsp) {
		this[page].data = rsp.settings;
		if( page == 'contact' ) {
			this.contact.business_address = rsp.business_address;
			this.showContact(cb);
		} else if( page == 'home' ) {
			if( (M.curBusiness.modules['ciniki.web'].flags&0x02) > 0 ) {
				this.home.sections._slider.active = 'yes';
				this.home.sections._slider_buttons.visible = 'yes';
				this.home.sections._slider.fields['page-home-slider'].active = 'yes';
				this.home.sections._slider.fields['page-home-slider'].options = {};
				this.home.sections._slider.fields['page-home-slider'].options[0] = 'None';
				if( rsp.sliders != null ) {
					for(i in rsp.sliders) {
						this.home.sections._slider.fields['page-home-slider'].options[rsp.sliders[i].slider.id] = rsp.sliders[i].slider.name;
					}
				}
			} else {
				this.home.sections._slider.active = 'no';
				this.home.sections._slider_buttons.visible = 'no';
				this.home.sections._slider.fields['page-home-slider'].active = 'no';
			}
			if( M.curBusiness.modules['ciniki.artcatalog'] != null ) {
				this.home.sections.options.fields['page-home-gallery-latest'].active = 'yes';
				this.home.sections.options.fields['page-home-gallery-latest-title'].active = 'yes';
				this.home.sections.options.fields['page-home-gallery-random'].active = 'yes';
				this.home.sections.options.fields['page-home-gallery-random-title'].active = 'yes';
			} else if( M.curBusiness.modules['ciniki.products'] != null ) {
				this.home.sections.options.fields['page-home-products-latest'].active = 'yes';
				this.home.sections.options.fields['page-home-products-latest-title'].active = 'yes';
			} else {
				this.home.sections.options.fields['page-home-gallery-latest'].active = 'no';
				this.home.sections.options.fields['page-home-gallery-latest-title'].active = 'no';
				this.home.sections.options.fields['page-home-gallery-random'].active = 'no';
				this.home.sections.options.fields['page-home-gallery-random-title'].active = 'no';
				this.home.sections.options.fields['page-home-products-latest'].active = 'no';
				this.home.sections.options.fields['page-home-products-latest-title'].active = 'no';
			}
			this.home.sections.options.fields['page-home-upcoming-events'].active = (M.curBusiness.modules['ciniki.events']!=null)?'yes':'no';
			this.home.sections.options.fields['page-home-latest-recipes'].active=(M.curBusiness.modules['ciniki.recipes']!=null)?'yes':'no';
			this.home.sections.options.fields['page-home-upcoming-workshops'].active = (M.curBusiness.modules['ciniki.workshops']!=null)?'yes':'no';
			this.home.sections.options.fields['page-home-upcoming-artgalleryexhibitions'].active = (M.curBusiness.modules['ciniki.artgallery']!=null)?'yes':'no';
			this[page].refresh();
			this[page].show(cb);
		} else if( page == 'gallery' ) {
			if( M.curBusiness.modules['ciniki.artcatalog'] != null ) {
				this.gallery.sections.options.fields['page-gallery-artcatalog-split'].active = 'yes';
				this.gallery.sections.options.fields['page-gallery-artcatalog-format'].active = 'yes';
				this.gallery.sections.social.fields['page-gallery-share-buttons'].active = 'yes';
				this.gallery.sections.social.visible = 'yes';
			} else {
				this.gallery.sections.options.fields['page-gallery-artcatalog-split'].active = 'no';
				this.gallery.sections.options.fields['page-gallery-artcatalog-format'].active = 'no';
				this.gallery.sections.social.visible = 'yes';
				this.gallery.sections.social.fields['page-gallery-share-buttons'].active = 'yes';
			}
			this[page].refresh();
			this[page].show(cb);
		} else if( page == 'members' ) {
			this.members.sections.options.fields['page-members-membership-details'].active=(M.curBusiness.modules['ciniki.info']!=null&&(M.curBusiness.modules['ciniki.info'].flags&0x40)>0)?'yes':'no';
			this.members.sections.options.fields['page-members-categories-display'].active=(M.curBusiness.modules['ciniki.customers']!=null&&(M.curBusiness.modules['ciniki.customers'].flags&0x04)>0)?'yes':'no';
			this[page].refresh();
			this[page].show(cb);
		} else if( page == 'account' ) {
			if( M.curBusiness.modules['ciniki.subscriptions'] != null ) {
				this.account.sections.subscriptions.active = 'yes';
			} else {
				this.account.sections.subscriptions.active = 'no';
			}
			// Setup the redirects
			var popts = {'':'Nowhere', '/':'Home', 'back':'Previous Page'};
//			if( M.curBusiness.modules['ciniki.artcatalog'] != null ) { popts['/gallery'] = 'Gallery'; }
//			if( M.curBusiness.modules['ciniki.gallery'] != null ) { popts['/gallery'] = 'Gallery'; }
			if( M.curBusiness.modules['ciniki.blog'] != null 
				&& (M.curBusiness.modules['ciniki.blog'].flags&0x100) > 0) { popts['/memberblog'] = 'Member Blog'; }
			this.account.sections.redirect.fields['page-account-signin-redirect'].options = popts;
			this[page].refresh();
			this[page].show(cb);
		} else if( page == 'exhibitions' ) {
			var rsp = M.api.getJSONCb('ciniki.exhibitions.exhibitionList', 
				{'business_id':M.curBusinessID}, function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					}
					var p = M.ciniki_web_main[page];
					p.sections.options.fields['page-exhibitions-exhibition'].options = {};
					for(i in rsp.exhibitions) {
						p.sections.options.fields['page-exhibitions-exhibition'].options[rsp.exhibitions[i].exhibition.id] = rsp.exhibitions[i].exhibition.name;
					}
					p.refresh();
					p.show(cb);
				});
		} else if( page == 'courses' ) {
			this.showCourses(cb);
		} else if( page == 'coursestype' && subpage != null ) {
			this[page].type_name = subpage;
			this[page].title = unescape(subpagetitle);
			this[page].sections = {
				'_content':{'label':'Content', 'fields':{}},
				'_image':{'label':'Image', 'fields':{}},
				'_image_caption':{'label':'', 'fields':{}},
				'_save':{'label':'', 'buttons':{
					'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'coursestype\');'},
					}},
			};
			this[page].sections._content.fields['page-courses-' + subpage + '-content'] = {'label':'', 'hidelabel':'yes', 'hint':'', 'type':'textarea', 'size':'large'};
			this[page].sections._image.fields['page-courses-' + subpage + '-image'] = {'label':'', 'type':'image_id', 'hidelabel':'yes', 'history':'no'};
			this[page].sections._image_caption.fields['page-courses-' + subpage + '-image-caption'] = {'label':'Caption', 'type':'text'};
			this[page].refresh();
			this[page].show(cb);
		} else {
			this[page].refresh();
			this[page].show(cb);
		}
	};

//	this.showLogo = function(cb, logo) {
//		this.logo.reset();
//		this.logo.data = {'site-logo-display':logo};
//		this.logo.refresh();
//		this.logo.show(cb);
//	};

	this.showCustom = function(cb, page, subpage, subpagetitle) {
		this.custom.reset();
		this.custom.number = parseInt(page.match(/-([0-9][0-9][0-9])/));
		this.custom.sections = {
			'options':{'label':'', 'fields':{}},
			'_image':{'label':'Image', 'fields':{}},
			'_image_caption':{'label':'', 'fields':{}},
			'_content':{'label':'Content', 'fields':{}},
			'_save':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'custom\');'},
				}},
			};
		this.custom.sections.options.fields['page-' + page + '-active'] = {'label':'Display Page', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles};
		this.custom.sections.options.fields['page-' + page + '-name'] = {'label':'Menu Name', 'type':'text', 'hint':''};
		this.custom.sections.options.fields['page-' + page + '-permalink'] = {'label':'URL', 'type':'text', 'hint':''};
		this.custom.sections.options.fields['page-' + page + '-title'] = {'label':'Title', 'type':'text', 'hint':''};
		this.custom.sections._image.fields['page-' + page + '-image'] = {'label':'', 'type':'image_id', 'controls':'all', 'hidelabel':'yes', 'history':'no'},
		this.custom.sections._image_caption.fields['page-' + page + '-image-caption'] = {'label':'Caption', 'type':'text'};

		this.custom.sections._content.fields['page-' + page + '-content'] = {'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'large'};

		this.custom.addDropImage = function(iid) {
			this.setFieldValue('page-' + page + '-image', iid);
			return true;
		};


		M.api.getJSONCb('ciniki.web.pageSettingsGet', 
			{'business_id':M.curBusinessID, 'page':page, 'content':'yes'}, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				}
				var p = M.ciniki_web_main.custom;
				p.data = rsp.settings;
				p.refresh();
				p.show(cb);
			});

	};

	this.showContact = function(cb) {
		// Get the user associated with this business
		this.contact.sections._users.active = 'no';
		var rsp = M.api.getJSONCb('ciniki.web.businessUsers', {'business_id':M.curBusinessID}, function(rsp) {
			if( rsp.stat != 'ok' ) {
				M.api.err(rsp);
				return false;
			}
			var p = M.ciniki_web_main.contact;
			if( rsp.users.length > 0 ) {
				p.sections._users.active = 'yes';
				p.sections._users.fields = {};
				for(i in rsp.users) {
					var u = rsp.users[i].user;
					p.sections._users.fields['page-contact-user-display-flags-' + u.id] = {
						'label':u.firstname + ' ' + u.lastname, 'type':'flags', 'join':'yes', 'flags':M.ciniki_web_main.userFlags,
						};
				}
				p.sections._users_display.active = 'yes';
			} else {
				p.sections._users.active = 'no';
				p.sections._users_display.active = 'no';
			}
			p.refresh();
			p.show(cb);
		});
	};

	this.showCourses = function(cb) {
		this.courses.sections.options.fields['page-courses-catalog-download-active'].active = 'no';
		if( M.curBusiness.modules['ciniki.courses'].flags != null 
			&& (M.curBusiness.modules['ciniki.courses'].flags&0x02) == 0x02) {
			// If they have instructors, they might want course catalog
			var rsp = M.api.getJSONCb('ciniki.courses.fileList',
				{'business_id':M.curBusinessID, 'type':'2'}, function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					}
					if( rsp.files != null && rsp.files.length > 0 ) {
						M.ciniki_web_main.courses.sections.options.fields['page-courses-catalog-download-active'].active = 'yes';
					} 
					M.ciniki_web_main.showCoursesFinish(cb);
				});
		} else {
			this.showCoursesFinish(cb);
		}
	};

	this.showCoursesFinish = function(cb) {
		// Get the course types incase we need a submenu
		var rsp = M.api.getJSONCb('ciniki.courses.courseTypes',
			{'business_id':M.curBusinessID}, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				}
				var p = M.ciniki_web_main.courses;
				p.sections.subpages.list = {};
				if( rsp.types != null ) {
					for(i in rsp.types) {
						if( rsp.types[i].type.name != '' ) {
							p.sections.subpages.list[rsp.types[i].type.settings_name] = {'label':rsp.types[i].type.name, 'fn':'M.ciniki_web_main.showPage(\'M.ciniki_web_main.courses.show();\',\'coursestype\',\'' + rsp.types[i].type.settings_name + '\',\'' + escape(rsp.types[i].type.name) + '\');'};
						}
					}
				}
				p.sections.subpages.list['registration'] = {'label':'Registration Information', 'fn':'M.ciniki_web_main.showPage(\'M.ciniki_web_main.courses.show();\',\'coursesregistration\',\'Registration\',\'Registration\');'};
				p.refresh();
				p.show(cb);
			});
	};

	this.showSiteSettings = function(cb, page) {
		var rsp = M.api.getJSONCb('ciniki.web.siteSettingsGet', 
			{'business_id':M.curBusinessID, 'content':'yes'}, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				}
				M.ciniki_web_main[page].data = rsp.settings;
				M.ciniki_web_main[page].refresh();
				M.ciniki_web_main[page].show(cb);
			});
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
					M.ciniki_web_main[page].close();
				});
		} else {
			this[page].close();
		}
	};

	this.deleteHeaderImage = function() {
		if( confirm('Are you sure you want to remove the header image from your website?') ) {
			var rsp = M.api.getJSONCb('ciniki.web.siteSettingsUpdate', 
				{'business_id':M.curBusinessID, 'site-header-image':'0'}, function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					} 
					M.ciniki_web_main.header.setFieldValue('site-header-image', 0, null, null);
				});
		}
	};

	this.clearImageCache = function(page) {
		if( confirm('Are you sure you wish to clear the web cache?') ) {
			var rsp = M.api.getJSONCb('ciniki.web.clearImageCache', {'business_id':M.curBusinessID}, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				}
				alert("The cache has been cleared");
			});
		}
	};

	this.clearContentCache = function(page) {
		if( confirm('Are you sure you wish to clear the web cache?') ) {
			var rsp = M.api.getJSONCb('ciniki.web.clearContentCache', {'business_id':M.curBusinessID}, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				}
				alert("The cache has been cleared");
			});
		}
	};

	this.makeFeatured = function() {
		var rsp = M.api.getJSONCb('ciniki.web.siteSettingsUpdate', 
			{'business_id':M.curBusinessID, 'site-featured':'yes'}, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				}
				M.ciniki_web_main.showMenu();
			});
	};

	this.removeFeatured = function() {
		var rsp = M.api.getJSONCb('ciniki.web.siteSettingsUpdate', 
			{'business_id':M.curBusinessID, 'site-featured':'no'}, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				}
				M.ciniki_web_main.showMenu();
			});
	};
};
