//
// The app to manage web options for a business
//
function ciniki_web_main() {
    
//  this.domainFlags = {
//      '1':{'name':'Site'},
//      '5':{'name':'Domain'},
//      '6':{'name':'Primary'},
//      };
    this.sliderSizeOptions = {
        'tiny':'Tiny',
        'small':'Small',
        'medium':'Medium',
        'large':'Large',
        'xlarge':'X-Large',
        'xxlarge':'XX-Large',
        };
    this.domainStatus = {
        '1':'Active',
        '50':'Suspended',
        '60':'Deleted',
        };
    // 
    // Theme and Layouts also listed in landingpages/ui/main
    //
    this.themesAvailable = {
        'default':'Simple - Black/White',
        'black':'Midnight Blue - Blue/Black',
        'midnightorange':'Midnight Orange - Orange/Black',
        'davinci':'Davinci - Brown/Beige',
        'orangebrick':'Orange Brick - Brown/Beige',
        'orangebrick2':'Brick - Brown/Beige',
        'stone1':'Stone - Brown/Orange',
        'stone2':'Stone - Black/White',
        'splatter':'Purple Splatter - Purple/White',
//      'field':'Field - Green/White',
        };
    if( M.userPerms&0x01 == 0x01 ) {
        this.themesAvailable['field'] = 'Field - Green/White';
        this.themesAvailable['redbrick'] = 'Red Brick';
        this.themesAvailable['redbrick2'] = 'Red Brick 2';
        this.themesAvailable['private'] = 'Private';
//      this.themesAvailable['orangebrick'] = 'Orange Brick';
//      this.themesAvailable['splatter'] = 'Splatter';
    }
    this.layoutsAvailable = {
        'default':'Default',
        'private':'Private',
        };

    this.headerImageSize = {'small':'Small', 'medium':'Medium', 'large':'Large', 'xlarge':'XLarge', 'xxlarge':'XXLarge'};
    if( M.userPerms&0x01 == 0x01 ) {
        this.headerImageSize['original'] = 'Original';
    }
    
    this.directoryLayouts = {
        'list':'List',
        'categories':'Categories',
        };
    this.eventCategoryDisplay = {
        'off':'Off',
        'submenu':'Menu',
        };
    this.activeToggles = {'no':'No', 'yes':'Yes'};
    this.activeRequiredToggles = {'no':'No', 'yes':'Yes', 'required':'Required'};
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
    this.dealerSubmitTemplates = {
        'none':'No Email', 
        'order':'Default',
        };

    this.landingpages = {
        '':'None',
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
            '_url':{'label':'', 'aside':'no', 'list':{
                'url':{'label':'Website', 'value':'', 'fn':''},
                }},
            'settings':{'label':'Settings', 'aside':'no', 'type':'simplegrid', 'num_cols':2, 'sortable':'no',
                'headerValues':null,
                'cellClasses':['',''],
                },
            'pages':{'label':'Pages', 'aside':'no', 'type':'simplegrid', 'num_cols':1, 'sortable':'yes',
                'headerValues':null,
                'addTxt':'',
                'addFn':'M.startApp(\'ciniki.web.pages\',null,\'M.ciniki_web_main.showMenu();\',\'mc\',{\'page_id\':0,\'parent_id\':0});',
                },
            'module_pages':{'label':'Other Pages', 'aside':'no', 'type':'simplegrid', 'num_cols':1, 'sortable':'yes',
                'headerValues':null,
                'addTxt':'',
                'addFn':'M.startApp(\'ciniki.web.pages\',null,\'M.ciniki_web_main.showMenu();\',\'mc\',{\'page_id\':0,\'parent_id\':0});',
                },
            'advanced':{'label':'Advanced', 'list':{
                'privatethemes':{'label':'Private Themes', 'visible':'no', 'fn':'M.startApp(\'ciniki.web.privatethemes\',null,\'M.ciniki_web_main.showMenu();\');'},
                'metatags':{'label':'Meta Tags', 'fn':'M.ciniki_web_main.showSiteSettings(\'M.ciniki_web_main.showMenu();\',\'metatags\');'},
                'social':{'label':'Social Media Links', 'fn':'M.startApp(\'ciniki.web.social\',null,\'M.ciniki_web_main.showMenu();\');'},
                'collections':{'label':'Web Collections', 'visible':'no', 'fn':'M.startApp(\'ciniki.web.collections\',null,\'M.ciniki_web_main.showMenu();\');'},
                'background':{'label':'Background', 'fn':'M.ciniki_web_main.showBackground(\'M.ciniki_web_main.showMenu();\');'},
                'header':{'label':'Header', 'fn':'M.ciniki_web_main.showHeader(\'M.ciniki_web_main.showMenu();\');'},
                'footer':{'label':'Footer', 'fn':'M.ciniki_web_main.showFooter(\'M.ciniki_web_main.showMenu();\');'},
                'mylivechat':{'label':'My Live Chat', 
                    'visible':function() { return (M.curBusiness.modules['ciniki.web'].flags&0x02000000)>0?'yes':'no';}, 
                    'fn':'M.ciniki_web_main.showMyLiveChat(\'M.ciniki_web_main.showMenu();\');',
                    },
                'redirects':{'label':'Redirects', 
                    'visible':function() { return (M.curBusiness.modules['ciniki.web'].flags&0x04000000)>0?'yes':'no';}, 
                    'fn':'M.startApp(\'ciniki.web.redirects\',null,\'M.ciniki_web_main.showMenu();\');',
                    },
                }},
//          'advanced':{'label':'Advanced', 'type':'simplegrid', 'num_cols':1, 'sortable':'no',
//              'headerValues':null,
//              'cellClasses':['',''],
//              },
        };
        this.menu.noData = function(s) { return 'No options added'; }
        this.menu.sectionData = function(s) { 
            if( s == '_url' ) { return this.sections._url.list; }
            if( s == 'advanced' ) { return this.sections.advanced.list; }
            if( s == 'adm' ) { return this.sections.adm.list; }
            return this.data[s]; 
        };
        this.menu.listLabel = function(s, i, d) { 
            if( s == '_url' ) { return d.label; }
            return '';
        };
        this.menu.listValue = function(s, i, d) { 
            if( s == '_url' ) { return "<a class='website' target='_mycinikisite' href='" + d.value + "'>" + d.value + '</a>'; }
            return d.label; 
        };
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
            } else if( s == 'pages' || s == 'module_pages' ) {
                if( d.page != null && d.page.active == 'yes' ) {
                    return d.page.display_name;
                }
                return d.page.display_name + ' (disabled)';
            }
        }
        this.menu.rowFn = function(s, i, d) {
            if( s == 'settings' && d.setting.name == 'theme') { 
                return 'M.ciniki_web_main.showThemes(\'M.ciniki_web_main.showMenu();\',\'' + d.setting.value + '\');'; 
            }
            if( s == 'pages' || s == 'module_pages' ) { 
                if( d.page.id != null && d.page.id > 0 ) {
                    return 'M.startApp(\'ciniki.web.pages\',null,\'M.ciniki_web_main.showMenu();\',\'mc\',{\'page_id\':\'' + d.page.id + '\',\'parent_id\':0});';
                }
//              if( d.page.name == 'about' && M.curBusiness.modules['ciniki.artgallery'] != null ) {
//                  return 'M.ciniki_web_main.showPage(\'M.ciniki_web_main.showMenu();\',\'aboutmenu\');'; 
//              }
                if( d.page.name == 'about' ) {
                    return 'M.startApp(\'ciniki.web.about\',null,\'M.ciniki_web_main.showMenu();\')';
                } else if( d.page.name == 'info' ) {
                    return 'M.startApp(\'ciniki.web.info\',null,\'M.ciniki_web_main.showMenu();\')';
                } else if( d.page.name == 'faq' ) {
                    return 'M.startApp(\'ciniki.web.faq\',null,\'M.ciniki_web_main.showMenu();\')';
                }
                return 'M.ciniki_web_main.showPage(\'M.ciniki_web_main.showMenu();\',\'' + d.page.name + '\');'; 
            }
        };
        this.menu.addClose('Back');
        this.menu.addLeftButton('website', 'Preview', 'M.showWebsite(\'/\');');

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
            'mc', 'medium', 'sectioned', 'ciniki.web.main.theme');
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
        this.layout = new M.panel('Layout', 'ciniki_web_main', 'layout', 'mc', 'narrow', 'sectioned', 'ciniki.web.main.layout');
        this.layout.data = {'site-layout':'default'};
        this.layout.sections = {
            '_layout':{'label':'', 'fields':{
                'site-layout':{'label':'Layout', 'type':'select', 'options':this.layoutsAvailable},
                }},
            '_homepage_sections':{'label':'Home Page Photos', 'fields':{
                'page-home-number-photos':{'label':'# of Photos', 'type':'toggle', 'default':'1', 'toggles':{'1':'1', '2':'2'}},
                }},
            '_homepage_sequences':{'label':'Home Page Sequence', 'fields':{
                'page-home-content-sequence':{'label':'Content', 'type':'text', 'size':'small'},
//                'page-home-gallery-slider-sequence':{'label':'Gallery Slider', 'type':'text', 'size':'small'},
//                'page-home-gallery-sequence':{'label':'Gallery', 'type':'text', 'size':'small'},
//                'page-home-membergallery-sequence':{'label':'Member Gallery', 'type':'text', 'size':'small'},
//                'page-home-blog-sequence':{'label':'Blog', 'type':'text', 'size':'small'},
//                'page-home-events-sequence':{'label':'Events', 'type':'text', 'size':'small'},
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
            'mc', 'medium mediumaside', 'sectioned', 'ciniki.web.main.header');
        this.header.data = {'site-header-image':'0'};
        this.header.sections = {
            '_image':{'label':'Image', 'aside':'yes', 'type':'imageform', 'fields':{
                'site-header-image':{'label':'', 'type':'image_id', 'controls':'all', 'hidelabel':'yes', 'history':'no'},
                }},
            'options':{'label':'Options', 'fields':{
                'site-header-image-size':{'label':'Image Size', 'type':'select', 'default':'medium', 'options':this.headerImageSize},
                'site-header-title':{'label':'Display Business Name', 'type':'multitoggle', 'default':'yes', 'toggles':this.activeToggles, 'editFn':'M.startApp(\'ciniki.businesses.info\',null,\'M.ciniki_web_main.header.show();\');'},
                }},
            '_landingpage1':{'label':'Landing Page', 'active':function() {return (M.curBusiness.modules['ciniki.landingpages']!=null?'yes':'no');}, 'fields':{
                'site-header-landingpage1-title':{'label':'Title', 'type':'text'},
                'site-header-landingpage1-permalink':{'label':'Landing Page', 'type':'select', 'options':this.landingpages},
                }},
            '_content':{'label':'Header Address', 'active':function() {return (M.curBusiness.modules['ciniki.web'].flags&0x10000)>0?'yes':'no';}, 'fields':{
                'site-header-address':{'label':'', 'hidelabel':'yes', 'hint':'', 'type':'textarea', 'size':'medium'},
                }},
            '_save':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'header\');'},
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
        // The panel to allow the user to select a theme
        //
        this.background = new M.panel('Background', 'ciniki_web_main', 'background', 'mc', 'medium', 'sectioned', 'ciniki.web.main.background');
        this.background.data = {'site-background-image':'0'};
        this.background.sections = {
            '_image':{'label':'Image', 'aside':'yes', 'type':'imageform', 'fields':{
                'site-background-image':{'label':'', 'type':'image_id', 'controls':'all', 'hidelabel':'yes', 'history':'no'},
                }},
//            'options':{'label':'Options', 'fields':{
//                'site-background-image-size':{'label':'Image Size', 'type':'select', 'default':'medium', 'options':this.headerImageSize},
//                'site-background-title':{'label':'Display Business Name', 'type':'multitoggle', 'default':'yes', 'toggles':this.activeToggles, 'editFn':'M.startApp(\'ciniki.businesses.info\',null,\'M.ciniki_web_main.background.show();\');'},
//                }},
            '_save':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'background\');'},
                }},
        };
        this.background.fieldValue = this.fieldValue;
        this.background.fieldHistoryArgs = this.fieldHistoryArgs;
        this.background.addDropImage = function(iid) {
            this.setFieldValue('site-background-image', iid);
            return true;
        };
        this.background.deleteImage = this.deleteImage;
        this.background.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'background\');');
        this.background.addClose('Cancel');

        //
        // The panel to allow the user to set footer properties
        //
        this.footer = new M.panel('Footer',
            'ciniki_web_main', 'footer',
            'mc', 'medium', 'sectioned', 'ciniki.web.main.footer');
        this.footer.data = {};
        this.footer.sections = {
            '_message':{'label':'Message', 'active':function() {console.log('test'); return ((M.curBusiness.modules['ciniki.web'].flags&0x100000)>0?'yes':'no');}, 'fields':{
                'site-footer-message':{'label':'', 'type':'textarea', 'size':'medium', 'hidelabel':'yes'},
                }},
            'options':{'label':'Options', 'fields':{
                'site-footer-copyright-name':{'label':'Copyright Name', 'type':'text', 'hint':M.curBusiness.name},
                }},
            '_copyright':{'label':'Copyright Message', 'fields':{
                'site-footer-copyright-message':{'label':'', 'type':'textarea', 'size':'medium', 'hidelabel':'yes'},
                }},
            '_landingpage1':{'label':'Landing Page', 'active':function() {return (M.curBusiness.modules['ciniki.landingpages']!=null?'yes':'no');}, 'fields':{
                'site-footer-landingpage1-title':{'label':'Title', 'type':'text'},
                'site-footer-landingpage1-permalink':{'label':'Landing Page', 'type':'select', 'options':this.landingpages},
                }},
            'sponsors':{'label':'Sponsors', 'type':'simplegrid', 'num_cols':1,
                'addTxt':'Manage Sponsors',
                'addFn':'M.startApp(\'ciniki.sponsors.ref\',null,\'M.ciniki_web_main.updateSponsors("footer");\',\'mc\',{\'object\':\'ciniki.web.page\',\'object_id\':\'footer\'});',
                },
            '_save':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'footer\');'},
                }},
        };
        this.footer.fieldValue = this.fieldValue;
        this.footer.fieldHistoryArgs = this.fieldHistoryArgs;
        this.footer.deleteImage = this.deleteImage;
        this.footer.cellValue = function(s, i, j, d) { return d.sponsor.title; }
        this.footer.rowFn = function(s, i, d) {
            return 'M.startApp(\'ciniki.sponsors.ref\',null,\'M.ciniki_web_main.updateSponsors("footer");\',\'mc\',{\'ref_id\':\'' + d.sponsor.ref_id + '\'});';
        };
        this.footer.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'footer\');');
        this.footer.addClose('Cancel');

        //
        // The options and information for the logo page
        //
//      this.logo = new M.panel('Business Logo',
//          'ciniki_web_main', 'logo',
//          'mc', 'medium', 'sectioned', 'ciniki.web.main.logo');
//      this.logo.data = {};
//      this.logo.sections = {
//          'options':{'label':'', 'fields':{
//              'site-logo-display':{'label':'Display Logo', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
//              }},
//          '_save':{'label':'', 'buttons':{
//              'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'logo\');'},
//              }},
//      };
//      this.logo.fieldValue = this.fieldValue;
//      this.logo.fieldHistoryArgs = this.fieldHistoryArgs;
//      this.logo.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'logo\');');
//      this.logo.addClose('Cancel');

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
            '_verification':{'label':'Google Site Verification', 'fields':{
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
        // The panel to allow the user to meta tags for your site
        //
        this.metatags = new M.panel('Meta Tags',
            'ciniki_web_main', 'metatags',
            'mc', 'medium', 'sectioned', 'ciniki.web.main.metatags');
        this.metatags.data = {'site-google-analytics-account':'0'};
        this.metatags.sections = {
            '_pinterest':{'label':'Pinterest Domain Verification', 'fields':{
                'site-pinterest-site-verification':{'label':'content', 'type':'text'},
                }},
            '_save':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'metatags\');'},
                }},
        };
        this.metatags.fieldValue = this.fieldValue;
        this.metatags.fieldHistoryArgs = this.fieldHistoryArgs;
        this.metatags.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'metatags\');');
        this.metatags.addClose('Cancel');

        //
        // This panel is for My Live Chat
        //
        this.mylivechat = new M.panel('My Live Chat Settings',
            'ciniki_web_main', 'mylivechat',
            'mc', 'medium', 'sectioned', 'ciniki.web.main.mylivechat');
        this.mylivechat.data = {'site-mylivechat-enable':'no'};
        this.mylivechat.sections = {
            '_mylivechat':{'label':'Meta Settings', 'fields':{
                'site-mylivechat-enable':{'label':'Enable', 'type':'toggle', 'default':'no', 'toggles':this.activeToggles},
                'site-mylivechat-userid':{'label':'ID', 'type':'text'},
                }},
            '_save':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'mylivechat\');'},
                }},
        };
        this.mylivechat.fieldValue = this.fieldValue;
        this.mylivechat.fieldHistoryArgs = this.fieldHistoryArgs;
        this.mylivechat.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'mylivechat\');');
        this.mylivechat.addClose('Cancel');

        //
        // The panel to allow the user to setup google analytics
        //
        this.meta = new M.panel('Meta Settings',
            'ciniki_web_main', 'meta',
            'mc', 'medium', 'sectioned', 'ciniki.web.main.meta');
        this.meta.data = {'site-google-analytics-account':'0'};
        this.meta.sections = {
            '_meta':{'label':'Meta Settings', 'fields':{
                'site-meta-robots':{'label':'Robots', 'type':'text'},
                }},
            '_save':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'meta\');'},
                }},
        };
        this.meta.fieldValue = this.fieldValue;
        this.meta.fieldHistoryArgs = this.fieldHistoryArgs;
        this.meta.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'meta\');');
        this.meta.addClose('Cancel');

        //
        // The panel to allow the user to setup custom css
        //
        this.css = new M.panel('Custom CSS',
            'ciniki_web_main', 'css',
            'mc', 'xlarge', 'sectioned', 'ciniki.web.main.css');
        this.css.data = {'site-customer-css':''};
        this.css.sections = {
            '_css':{'label':'Custom CSS', 'fields':{
                'site-custom-css':{'label':'', 'type':'textarea', 'size':'xlarge', 'hidelabel':'yes'},
                }},
            '_save':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'css\', \'\');'},
                }},
        };
        this.css.fieldValue = this.fieldValue;
        this.css.fieldHistoryArgs = this.fieldHistoryArgs;
        this.css.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'css\',\'\');');
        this.css.addLeftButton('back', 'Back', 'M.ciniki_web_main.savePage(\'css\',null);');
//        this.css.addClose('Cancel');

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
                'site-ssl-shop':{'label':'SSL Shopping Domain', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
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
            'mc', 'medium mediumaside', 'sectioned', 'ciniki.web.main.home');
        this.home.data = {};
        this.home.sections = {
            'options':{'label':'', 'aside':'yes', 'fields':{
                'page-home-active':{'label':'Display Home Page', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                }},
            '_slideshow':{'label':'Slide Show', 'aside':'yes', 'fields':{
                'page-home-gallery-slider-type':{'label':'Display Slide Show', 'type':'toggle', 'default':'no', 'toggles':{'no':'Off', 'random':'Random', 'latest':'Latest', 'forsale':'For Sale'}},
                'page-home-gallery-slider-size':{'label':'Size', 'type':'select', 'default':'large', 'options':this.sliderSizeOptions},
                'page-home-gallery-slider-title':{'label':'Title', 'type':'text'},
                }},
            '_memberslideshow':{'label':'Members Slide Show', 'aside':'yes', 'fields':{
                'page-home-membergallery-slider-type':{'label':'Display Slide Show', 'type':'toggle', 'default':'no', 'toggles':{'no':'Off', 'random':'Random', 'latest':'Latest'}},
                'page-home-membergallery-slider-size':{'label':'Size', 'type':'select', 'default':'large', 'options':this.sliderSizeOptions},
                }},
            '_artcatalog':{'label':'Art Catalog', 'aside':'yes', 'fields':{
                'page-home-gallery-latest':{'label':'Latest Work', 'type':'multitoggle', 'default':'yes', 'toggles':this.activeToggles},
                'page-home-gallery-latest-title':{'label':'Latest Work Title', 'type':'text', 'hint':'Latest Work'},
                'page-home-gallery-random':{'label':'Random Example Work', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                'page-home-gallery-random-title':{'label':'Random Example Work Title', 'type':'text', 'hint':'Example Work'},
                }},
            '_gallery':{'label':'Gallery', 'aside':'yes', 'fields':{
                'page-home-gallery-latest':{'label':'Display Latest Work', 'type':'multitoggle', 'default':'yes', 'toggles':this.activeToggles},
                'page-home-gallery-latest-title':{'label':'Latest Work Title', 'type':'text', 'size':'small', 'hint':'Latest Work'},
                'page-home-gallery-random':{'label':'Display Random Example Work', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                'page-home-gallery-random-title':{'label':'Random Example Work Title', 'type':'text', 'size':'small', 'hint':'Example Work'},
                }},
            '_artgalleryexhibitions':{'label':'Exhibitions', 'aside':'yes', 'active':'no', 'fields':{
                'page-home-current-artgalleryexhibitions':{'label':'Display Current', 'active':'yes', 'type':'multitoggle', 'default':'yes', 'toggles':this.activeToggles},
                'page-home-current-artgalleryexhibitions-title':{'label':'Current Title', 'active':'yes', 'type':'text', 'hint':'Current Exhibitions'},
                'page-home-current-artgalleryexhibitions-more':{'label':'More', 'active':'yes', 'type':'text', 'hint':'... more exhibitions'},
                'page-home-current-artgalleryexhibitions-number':{'label':'Number of Current', 'active':'yes', 'type':'text', 'size':'small', 'hint':'2'},
                'page-home-upcoming-artgalleryexhibitions':{'label':'Display Upcoming', 'active':'yes', 'type':'multitoggle', 'default':'yes', 'toggles':this.activeToggles},
                'page-home-upcoming-artgalleryexhibitions-title':{'label':'Upcoming Title', 'active':'yes', 'type':'text', 'hint':'Upcoming Exhibitions'},
                'page-home-upcoming-artgalleryexhibitions-more':{'label':'More', 'active':'yes', 'type':'text', 'hint':'... more exhibitions'},
                'page-home-upcoming-artgalleryexhibitions-number':{'label':'Number of Upcoming', 'active':'yes', 'type':'text', 'size':'small', 'hint':'2'},
                }},
            '_blog':{'label':'Blog', 'aside':'yes', 'active':'no', 'fields':{
                'page-home-latest-blog':{'label':'Display Latest', 'active':'yes', 'type':'multitoggle', 'default':'yes', 'toggles':this.activeToggles},
                'page-home-latest-blog-title':{'label':'Title', 'active':'yes', 'type':'text', 'hint':'Latest Blog Posts'},
                'page-home-latest-blog-more':{'label':'More', 'active':'yes', 'type':'text', 'hint':'... more blog posts'},
                'page-home-latest-blog-number':{'label':'Number of Entries', 'active':'yes', 'type':'text', 'size':'small', 'hint':'2'},
                }},
            '_recipes':{'label':'Recipes', 'aside':'yes', 'active':'no', 'fields':{
                'page-home-recipes-latest':{'label':'Display Latest', 'active':'yes', 'type':'multitoggle', 'default':'yes', 'toggles':this.activeToggles},
                'page-home-recipes-latest-title':{'label':'Title', 'active':'yes', 'type':'text', 'hint':'New Recipes'},
                'page-home-recipes-latest-more':{'label':'More', 'active':'yes', 'type':'text', 'hint':'... more recipes'},
                'page-home-recipes-latest-number':{'label':'Number of recipes', 'active':'yes', 'type':'text', 'size':'small', 'hint':'2'},
                }},
            '_workshops':{'label':'Workshops', 'aside':'yes', 'active':'no', 'fields':{
                'page-home-upcoming-workshops':{'label':'Display Upcoming', 'active':'yes', 'type':'multitoggle', 'default':'yes', 'toggles':this.activeToggles},
                'page-home-upcoming-workshops-title':{'label':'Title', 'active':'yes', 'type':'text', 'hint':'Upcoming Workshops'},
                'page-home-upcoming-workshops-more':{'label':'More', 'active':'yes', 'type':'text', 'hint':'... more workshops'},
                'page-home-upcoming-workshops-number':{'label':'Number of Upcoming', 'active':'yes', 'type':'text', 'size':'small', 'hint':'2'},
                }},
            '_filmschedule':{'label':'Schedule', 'aside':'yes', 'active':'no', 'fields':{
                'page-home-upcoming-filmschedule':{'label':'Display Upcoming', 'active':'yes', 'type':'multitoggle', 'default':'yes', 'toggles':this.activeToggles},
                'page-home-upcoming-filmschedule-title':{'label':'Title', 'active':'yes', 'type':'text', 'hint':'Upcoming Films'},
                'page-home-upcoming-filmschedule-more':{'label':'More', 'active':'yes', 'type':'text', 'hint':'... more films'},
                'page-home-upcoming-filmschedule-number':{'label':'Number of Upcoming', 'active':'yes', 'type':'text', 'size':'small', 'hint':'2'},
                }},
            '_events':{'label':'Events', 'aside':'yes', 'active':'no', 'fields':{
                'page-home-current-events':{'label':'Display Current', 'active':'yes', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                'page-home-current-events-title':{'label':'Title', 'active':'yes', 'type':'text', 'hint':'Current Events'},
                'page-home-current-events-more':{'label':'More', 'active':'yes', 'type':'text', 'hint':'... more events'},
                'page-home-current-events-number':{'label':'Number of Current', 'active':'yes', 'type':'text', 'size':'small', 'hint':'2'},
                'page-home-upcoming-events':{'label':'Display Upcoming', 'active':'yes', 'type':'multitoggle', 'default':'yes', 'toggles':this.activeToggles},
                'page-home-upcoming-events-title':{'label':'Title', 'active':'yes', 'type':'text', 'hint':'Upcoming Events'},
                'page-home-upcoming-events-more':{'label':'More', 'active':'yes', 'type':'text', 'hint':'... more events'},
                'page-home-upcoming-events-number':{'label':'Number of Upcoming', 'active':'yes', 'type':'text', 'size':'small', 'hint':'2'},
                }},
            '_products':{'label':'Products', 'aside':'yes', 'active':'no', 'fields':{
                'page-home-products-latest':{'label':'Display New Products', 'active':'yes', 'type':'multitoggle', 'default':'yes', 'toggles':this.activeToggles},
                'page-home-products-latest-title':{'label':'Title', 'active':'yes', 'type':'text', 'hint':'New Products'},
                'page-home-products-latest-more':{'label':'More', 'active':'yes', 'type':'text', 'hint':'... more products'},
                'page-home-products-latest-number':{'label':'Number of products', 'active':'yes', 'type':'text', 'size':'small', 'hint':'2'},
                }},
            '_writings':{'label':'Writings', 'aside':'yes', 'active':'no', 'fields':{
                'page-home-writings-covers':{'label':'Display Book Covers', 'active':'yes', 'type':'multitoggle', 'default':'yes', 'toggles':this.activeToggles},
                'page-home-writings-covers-title':{'label':'Title', 'active':'yes', 'type':'text', 'hint':'Books'},
                }},
            '_slider':{'label':'Image Slider', 'aside':'yes', 'active':'no', 'fields':{
                'page-home-slider':{'label':'Slider', 'active':'no', 'type':'select', 'options':{}},
                }},
            '_slider_buttons':{'label':'', 'aside':'yes', 'visible':'no', 'type':'simplegrid', 'num_cols':1,
                'addTxt':'Manage Sliders',
                'addFn':'M.startApp(\'ciniki.web.sliders\',null,\'M.ciniki_web_main.showPage(null,"home");\');',
                },
            '_imagetabs':{'label':'', 'type':'paneltabs', 'selected':'1', 
                'visible':function() { return (M.ciniki_web_main.home.data['page-home-number-photos'] != null && M.ciniki_web_main.home.data['page-home-number-photos'] > 1 ? 'yes' : 'no'); },
                'tabs':{
                    '1':{'label':' 1 ', 'fn':'M.ciniki_web_main.home.switchImage(\'1\');'},
                    '2':{'label':' 2 ', 'fn':'M.ciniki_web_main.home.switchImage(\'2\');'},
                }},
            '_image':{'label':'Image', 'type':'imageform', 
                'visible':function() { return (M.ciniki_web_main.home.sections._imagetabs.selected == '1' ? 'yes' : 'hidden'); },
                'fields':{
                    'page-home-image':{'label':'', 'type':'image_id', 'controls':'all', 'hidelabel':'yes', 'history':'no'},
                }},
            '_image_caption':{'label':'', 
                'visible':function() { return (M.ciniki_web_main.home.sections._imagetabs.selected == '1' ? 'yes' : 'hidden'); },
                'fields':{
                    'page-home-image-caption':{'label':'Caption', 'type':'text'},
                    'page-home-image-url':{'label':'Link', 'type':'text'},
                }},
            '_image2':{'label':'2nd Image', 'type':'imageform', 
                'visible':function() { return (M.ciniki_web_main.home.sections._imagetabs.selected == '2' ? 'yes' : 'hidden'); },

                'fields':{
                    'page-home-image2':{'label':'', 'type':'image_id', 'controls':'all', 'hidelabel':'yes', 'history':'no'},
                }},
            '_image2_caption':{'label':'', 
                'visible':function() { return (M.ciniki_web_main.home.sections._imagetabs.selected == '2' ? 'yes' : 'hidden'); },
                'fields':{
                    'page-home-image2-caption':{'label':'Caption', 'type':'text'},
                    'page-home-image2-url':{'label':'Link', 'type':'text'},
                }},
            '_title':{'label':'Title', 'fields':{
                'page-home-title':{'label':'', 'hidelabel':'yes', 'hint':'', 'type':'text'},
                }},
            '_content_layout':{'label':'', 'active':'no', 'fields':{
                'page-home-content-layout':{'label':'Page Type', 'type':'toggle', 'default':'custom', 'toggles':{'custom':'Custom', 'manual':'Manual'}},
                }},
            '_content':{'label':'Welcome Message (optional)', 'fields':{
                'page-home-content':{'label':'', 'hidelabel':'yes', 'hint':'', 'type':'textarea', 'size':'large'},
                }},
            '_collections':{'label':'Web Collections', 'active':'no', 'fields':{
                'page-home-collections-display':{'label':'Display Web Collections', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                'page-home-collections-title':{'label':'Title', 'type':'text', 'hint':'Collections'},
                }},
            '_quicklinks':{'label':'Highlights', 'active':'no', 'fields':{
                'page-home-quicklinks-title':{'label':'Title', 'type':'text', 'hint':''},
                'page-home-quicklinks-001-name':{'label':'1) Name', 'type':'text'},
                'page-home-quicklinks-001-url':{'label':'1) URL', 'type':'text', 'hint':'Enter the http:// address for the website'},
                'page-home-quicklinks-002-name':{'label':'2) Name', 'type':'text'},
                'page-home-quicklinks-002-url':{'label':'2) URL', 'type':'text', 'hint':'Enter the http:// address for the website'},
                'page-home-quicklinks-003-name':{'label':'3) Name', 'type':'text'},
                'page-home-quicklinks-003-url':{'label':'3) URL', 'type':'text', 'hint':'Enter the http:// address for the website'},
                'page-home-quicklinks-004-name':{'label':'4) Name', 'type':'text'},
                'page-home-quicklinks-004-url':{'label':'4) URL', 'type':'text', 'hint':'Enter the http:// address for the website'},
                'page-home-quicklinks-005-name':{'label':'5) Name', 'type':'text'},
                'page-home-quicklinks-005-url':{'label':'5) URL', 'type':'text', 'hint':'Enter the http:// address for the website'},
                'page-home-quicklinks-006-name':{'label':'6) Name', 'type':'text'},
                'page-home-quicklinks-006-url':{'label':'6) URL', 'type':'text', 'hint':'Enter the http:// address for the website'},
                'page-home-quicklinks-007-name':{'label':'7) Name', 'type':'text'},
                'page-home-quicklinks-007-url':{'label':'7) URL', 'type':'text', 'hint':'Enter the http:// address for the website'},
                'page-home-quicklinks-008-name':{'label':'8) Name', 'type':'text'},
                'page-home-quicklinks-008-url':{'label':'8) URL', 'type':'text', 'hint':'Enter the http:// address for the website'},
                'page-home-quicklinks-009-name':{'label':'9) Name', 'type':'text'},
                'page-home-quicklinks-009-url':{'label':'9) URL', 'type':'text', 'hint':'Enter the http:// address for the website'},
                }},
            '_seo':{'label':'SEO Title', 'active':'no', 
                'active':function() { return M.modFlagSet('ciniki.web', 0x8000); },
                'fields':{
                    'page-home-seo-title':{'label':'Title', 'hidelabel':'yes', 'hint':'', 'type':'text'},
                }},
            '_seo_description':{'label':'SEO Description', 
                'active':function() { return M.modFlagSet('ciniki.web', 0x8000); },
                'fields':{
                    'page-home-seo-description':{'label':'', 'hidelabel':'yes', 'hint':'', 'type':'textarea', 'size':'small'},
                }},
            'redirects':{'label':'Redirect Home', 'active':'no', 'fields':{
                'page-home-url':{'label':'URL', 'type':'text'},
                }},
            'sponsors':{'label':'Sponsors', 'type':'simplegrid', 'num_cols':1,
                'addTxt':'Manage Sponsors',
                'addFn':'M.startApp(\'ciniki.sponsors.ref\',null,\'M.ciniki_web_main.updateSponsors("home");\',\'mc\',{\'object\':\'ciniki.web.page\',\'object_id\':\'home\'});',
                },
            '_save':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'home\');'},
                }},
        };
        this.home.fieldValue = this.fieldValue;
        this.home.fieldHistoryArgs = this.fieldHistoryArgs;
        this.home.sectionData = function(s) { 
            return this.data[s];
        };
        this.home.addDropImage = function(iid) {
            if( this.sections._imagetabs.selected == '2' ) {
                this.setFieldValue('page-home-image2', iid);
            } else {
                this.setFieldValue('page-home-image', iid);
            }
            return true;
        };
        this.home.cellValue = function(s, i, j, d) { return d.sponsor.title; }
        this.home.rowFn = function(s, i, d) {
            return 'M.startApp(\'ciniki.sponsors.ref\',null,\'M.ciniki_web_main.updateSponsors("home");\',\'mc\',{\'ref_id\':\'' + d.sponsor.ref_id + '\'});';
        };
        this.home.switchImage = function(i) {
            this.sections._imagetabs.selected = i;
            this.refreshSection('_imagetabs');
            this.showHideSection('_image');
            this.showHideSection('_image_caption');
            this.showHideSection('_image2');
            this.showHideSection('_image2_caption');
        }
        this.home.deleteImage = this.deleteImage;
        this.home.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'home\');');
//      this.home.addLeftButton('website', 'Preview', 'M.showWebsite(\'/\');');
        this.home.addClose('Cancel');

        //
        // The options and information for the shop page
        //
        this.shop = new M.panel('Shop',
            'ciniki_web_main', 'shop',
            'mc', 'medium mediumaside', 'sectioned', 'ciniki.web.main.shop');
        this.shop.data = {};
        this.shop.sections = {
            'options':{'label':'', 'aside':'yes', 'fields':{
                'page-shop-active':{'label':'Display Home Page', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                }},
            '_image':{'label':'Image', 'type':'imageform', 'aside':'yes', 'fields':{
                'page-shop-image':{'label':'', 'type':'image_id', 'controls':'all', 'hidelabel':'yes', 'history':'no'},
                }},
            '_content':{'label':'Welcome Message (optional)', 'fields':{
                'page-shop-content':{'label':'', 'hidelabel':'yes', 'hint':'', 'type':'textarea', 'size':'large'},
                }},
            '_save':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'shop\');'},
                }},
        };
        this.shop.fieldValue = this.fieldValue;
        this.shop.fieldHistoryArgs = this.fieldHistoryArgs;
        this.shop.sectionData = function(s) { 
            return this.data[s];
        };
        this.shop.addDropImage = function(iid) {
            this.setFieldValue('page-shop-image', iid);
            return true;
        };
        this.shop.deleteImage = this.deleteImage;
        this.shop.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'shop\');');
//      this.shop.addLeftButton('website', 'Preview', 'M.showWebsite(\'/\');');
        this.shop.addClose('Cancel');

        //
        // The options and information for the custom 001 page
        //
        this.custom = new M.panel('Custom',
            'ciniki_web_main', 'custom',
            'mc', 'medium', 'sectioned', 'ciniki.web.main.custom');
        this.custom.data = {};
        this.custom.number = 1;
        this.custom.sections = {
//          'options':{'label':'', 'fields':{
//              'page-custom-001-active':{'label':'Display Page', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
//              'page-custom-001-name':{'label':'Name', 'type':'text', 'hint':''},
//              'page-custom-001-permalink':{'label':'URL', 'type':'text', 'hint':''},
//              }},
//          '_image':{'label':'Image', 'type':'imageform', 'fields':{
//              'page-custom-001-image':{'label':'', 'type':'image_id', 'controls':'all', 'hidelabel':'yes', 'history':'no'},
//              }},
//          '_image_caption':{'label':'', 'fields':{
//              'page-custom-001-image-caption':{'label':'Caption', 'type':'text'},
//              }},
//          '_content':{'label':'Content', 'fields':{
//              'page-custom-001-content':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'large'},
//              }},
//          '_save':{'label':'', 'buttons':{
//              'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'custom\');'},
//              }},
        };
        this.custom.fieldValue = this.fieldValue;
        this.custom.fieldHistoryArgs = this.fieldHistoryArgs;
//      this.custom.addDropImage = function(iid) {
//          this.setFieldValue('page-custom-001-image', iid);
//          return true;
//      };
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
                'page-contact-business-name-display':{'label':'Business Name', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles, 'hint':'', 'editFn':'M.startApp(\'ciniki.businesses.info\',null,\'M.ciniki_web_main.contact.show();\');'},
                'page-contact-person-name-display':{'label':'Contact Name', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles, 'hint':'', 'editFn':'M.startApp(\'ciniki.businesses.info\',null,\'M.ciniki_web_main.contact.show();\');'},
                'page-contact-address-display':{'label':'Address', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles, 'hint':'', 'editFn':'M.startApp(\'ciniki.businesses.info\',null,\'M.ciniki_web_main.contact.show();\');'},
                'page-contact-phone-display':{'label':'Phone', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles, 'hint':'', 'editFn':'M.startApp(\'ciniki.businesses.info\',null,\'M.ciniki_web_main.contact.show();\');'},
                'page-contact-fax-display':{'label':'Fax', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles, 'hint':'', 'editFn':'M.startApp(\'ciniki.businesses.info\',null,\'M.ciniki_web_main.contact.show();\');'},
                'page-contact-email-display':{'label':'Email', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles, 'hint':'', 'editFn':'M.startApp(\'ciniki.businesses.info\',null,\'M.ciniki_web_main.contact.show();\');'},
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
            '_contact_form':{'label':'Contact Form', 'active':'no', 'fields':{
                'page-contact-form-display':{'label':'Display Form', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                'page-contact-form-phone':{'label':'Phone #', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                'page-contact-form-emails':{'label':'Emails', 'type':'text'},
                }},
            '_contact_form_intro_message':{'label':'Contact Form Introduction', 'active':'no', 'fields':{
                'page-contact-form-intro-message':{'label':'Form Message', 'hidelabel':'yes', 'type':'textarea', 'size':'small'},
                }},
            '_contact_form_submitted_message':{'label':'Contact Form Thank You', 'active':'no', 'fields':{
                'page-contact-form-submitted-message':{'label':'Thank you message', 'hidelabel':'yes', 'type':'textarea', 'size':'small'},
                }},
            '_mailchimp':{'label':'Mailchimp', 
                'active':function() {return (M.curBusiness.modules['ciniki.web'].flags&0x01000000)>0?'yes':'no';}, 
                'fields':{
                    'page-contact-mailchimp-signup':{'label':'Enable Mailchimp', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles, 'hint':''},
                    'page-contact-mailchimp-submit-url':{'label':'Submit URL', 'type':'text'},
                }},
            '_subscriptions':{'label':'Mailing List Signup', 
                'active':function() {return (M.curBusiness.modules['ciniki.subscriptions'])?'yes':'no';}, 
                'fields':{
                    'page-contact-subscriptions-signup':{'label':'Enable Signups', 'type':'toggle', 'default':'no', 'toggles':this.activeToggles, 'hint':''},
                }},
            '_subscriptions_message':{'label':'Mailing List Intro', 
                'active':function() {return (M.curBusiness.modules['ciniki.subscriptions'])?'yes':'no';}, 
                'fields':{
                    'page-contact-subscriptions-intro-message':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'small'},
                }},
            '_save':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'contact\');'},
                }},
        };
        this.contact.fieldValue = this.fieldValue;
        this.contact.fieldHistoryArgs = this.fieldHistoryArgs;
        this.contact.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'contact\');');
//      this.contact.addLeftButton('website', 'Preview', 'M.showWebsite(\'/contact\');');
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
        // The options and information for the Merchandise page
        //
        this.merchandise = new M.panel('Merchandise',
            'ciniki_web_main', 'merchandise',
            'mc', 'narrow', 'sectioned', 'ciniki.web.main.merchandise');
        this.merchandise.data = {};
        this.merchandise.sections = {
            'options':{'label':'Options', 'fields':{
                'page-merchandise-active':{'label':'Show Shop', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                'page-merchandise-name':{'label':'Name', 'type':'text'},
                }},
            '_save':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'merchandise\');'},
                }},
        };
        this.merchandise.fieldValue = this.fieldValue;
        this.merchandise.fieldHistoryArgs = this.fieldHistoryArgs;
        this.merchandise.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'merchandise\');');
        this.merchandise.addClose('Cancel');

        //
        // The options and information for the Properties page
        //
        this.propertyrentals = new M.panel('Properties',
            'ciniki_web_main', 'propertyrentals',
            'mc', 'narrow', 'sectioned', 'ciniki.web.main.propertyrentals');
        this.propertyrentals.data = {};
        this.propertyrentals.sections = {
            'options':{'label':'Options', 'fields':{
                'page-propertyrentals-active':{'label':'Show Properties', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                'page-propertyrentals-rented':{'label':'Show Rented', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                }},
            '_save':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'propertyrentals\');'},
                }},
        };
        this.propertyrentals.fieldValue = this.fieldValue;
        this.propertyrentals.fieldHistoryArgs = this.fieldHistoryArgs;
        this.propertyrentals.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'propertyrentals\');');
//      this.features.addLeftButton('website', 'Preview', 'M.showWebsite(\'/features\');');
        this.propertyrentals.addClose('Cancel');

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
//      this.features.addLeftButton('website', 'Preview', 'M.showWebsite(\'/features\');');
        this.features.addClose('Cancel');

        //
        // The options and information for the Events page
        //
        this.events = new M.panel('Events',
            'ciniki_web_main', 'events',
            'mc', 'medium', 'sectioned', 'ciniki.web.main.events');
        this.events.data = {};
        this.events.sections = {
            'options':{'label':'Options', 'fields':{
                'page-events-active':{'label':'Show events', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                'page-events-title':{'label':'Title', 'hint':'Events', 'type':'text'},
                'page-events-current':{'label':'Separate Current Events', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles, 'onchange':'M.ciniki_web_main.events.updateVisible'},
                'page-events-past':{'label':'Include past events', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles, 'onchange':'M.ciniki_web_main.events.updateVisible'},
                'page-events-upcoming-empty-hide':{'label':'Hide empty upcoming', 'visible':'no', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles, 'visible':function() {return M.ciniki_web_main.events.data['page-events-past']}},
                'page-events-categories-display':{'label':'Display Categories', 'type':'toggle', 'default':'off', 'toggles':this.eventCategoryDisplay},
                'page-events-thumbnail-format':{'label':'Thumbnail Format', 'type':'toggle', 'default':'square-cropped', 'toggles':{'square-cropped':'Cropped', 'square-padded':'Padded'}},
                'page-events-thumbnail-padding-color':{'label':'Padding Color', 'type':'colour'},
                }},
            '_image':{'label':'Image', 'active':'no', 'type':'imageform', 'fields':{
                'page-events-image':{'label':'', 'type':'image_id', 'controls':'all', 'hidelabel':'yes', 'history':'no'},
                }},
            '_image_caption':{'label':'', 'active':'no', 'fields':{
                'page-events-image-caption':{'label':'Caption', 'type':'text'},
                }},
            '_content':{'label':'Content', 'active':'no', 'fields':{
                'page-events-content':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'large'},
                }},
            '_save':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'events\');'},
                }},
        };
        this.events.updateVisible = function(e, s, i) {
            if( i == 'page-events-past' ) {
                if( this.formFieldValue(this.sections[s].fields[i], i) == 'no' ) {
                    M.gE(this.panelUID + '_page-events-upcoming-empty-hide').parentNode.parentNode.style.display = 'none';
                } else {
                    M.gE(this.panelUID + '_page-events-upcoming-empty-hide').parentNode.parentNode.style.display = '';
                }
            }
        };
        this.events.fieldValue = this.fieldValue;
        this.events.fieldHistoryArgs = this.fieldHistoryArgs;
        this.events.addDropImage = function(iid) {
            this.setFieldValue('page-events-image', iid);
            return true;
        };
        this.events.deleteImage = this.deleteImage;
        this.events.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'events\');');
//      this.events.addLeftButton('website', 'Preview', 'M.showWebsite(\'/events\');');
        this.events.addClose('Cancel');

        //
        // The options and information for the Music Festival page
        //
        this.musicfestivals = new M.panel('Music Festival', 'ciniki_web_main', 'musicfestivals', 'mc', 'medium', 'sectioned', 'ciniki.web.main.musicfestivals');
        this.musicfestivals.data = {};
        this.musicfestivals.sections = {
            'options':{'label':'Options', 'fields':{
                'page-musicfestivals-active':{'label':'Show Music Festival', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                'page-musicfestivals-title':{'label':'Title', 'hint':'Music Festival', 'type':'text'},
                }},
            '_save':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'musicfestivals\');'},
                }},
        };
        this.musicfestivals.fieldValue = this.fieldValue;
        this.musicfestivals.fieldHistoryArgs = this.fieldHistoryArgs;
        this.musicfestivals.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'musicfestivals\');');
        this.musicfestivals.addClose('Cancel');

        //
        // The options and information for the Events page
        //
        this.filmschedule = new M.panel('Schedule',
            'ciniki_web_main', 'filmschedule',
            'mc', 'medium', 'sectioned', 'ciniki.web.main.filmschedule');
        this.filmschedule.data = {};
        this.filmschedule.sections = {
            'options':{'label':'Options', 'fields':{
                'page-filmschedule-active':{'label':'Show Schedule', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                'page-filmschedule-title':{'label':'Title', 'hint':'Schedule', 'type':'text'},
                'page-filmschedule-past':{'label':'Include past Films', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                }},
            '_save':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'filmschedule\');'},
                }},
        };
        this.filmschedule.fieldValue = this.fieldValue;
        this.filmschedule.fieldHistoryArgs = this.fieldHistoryArgs;
        this.filmschedule.addDropImage = function(iid) {
            this.setFieldValue('page-filmschedule-image', iid);
            return true;
        };
        this.filmschedule.deleteImage = this.deleteImage;
        this.filmschedule.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'filmschedule\');');
//      this.filmschedule.addLeftButton('website', 'Preview', 'M.showWebsite(\'/filmschedule\');');
        this.filmschedule.addClose('Cancel');

        //
        // The options and information for the Tutorials page
        //
        this.tutorials = new M.panel('Tutorials',
            'ciniki_web_main', 'tutorials',
            'mc', 'medium', 'sectioned', 'ciniki.web.main.tutorials');
        this.tutorials.data = {};
        this.tutorials.sections = {
            'options':{'label':'Options', 'fields':{
                'page-tutorials-active':{'label':'Show tutorials', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                }},
            '_image':{'label':'Image', 'type':'imageform', 'fields':{
                'page-tutorials-image':{'label':'', 'type':'image_id', 'controls':'all', 'hidelabel':'yes', 'history':'no'},
                }},
            '_image_caption':{'label':'', 'fields':{
                'page-tutorials-image-caption':{'label':'Caption', 'type':'text'},
                }},
            '_content':{'label':'Content', 'fields':{
                'page-tutorials-content':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'large'},
                }},
            '_save':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'tutorials\');'},
                }},
        };
        this.tutorials.fieldValue = this.fieldValue;
        this.tutorials.fieldHistoryArgs = this.fieldHistoryArgs;
        this.tutorials.addDropImage = function(iid) {
            this.setFieldValue('page-tutorials-image', iid);
            return true;
        };
        this.tutorials.deleteImage = this.deleteImage;
        this.tutorials.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'tutorials\');');
//      this.tutorials.addLeftButton('website', 'Preview', 'M.showWebsite(\'/tutorials\');');
        this.tutorials.addClose('Cancel');

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
//      this.workshops.addLeftButton('website', 'Preview', 'M.showWebsite(\'/workshops\');');
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
//      this.friends.addLeftButton('website', 'Preview', 'M.showWebsite(\'/friends\');');
        this.friends.addClose('Cancel');

        //
        // The options and information for the directory page
        //
        this.directory = new M.panel('Directory',
            'ciniki_web_main', 'directory',
            'mc', 'medium', 'sectioned', 'ciniki.web.main.directory');
        this.directory.data = {};
        this.directory.sections = {
            'options':{'label':'', 'fields':{
                'page-directory-active':{'label':'Display Directory Page', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                'page-directory-title':{'label':'Title', 'type':'text'},
                'page-directory-layout':{'label':'Layout', 'type':'multitoggle', 'default':'categories', 'toggles':this.directoryLayouts},
                }},
            '_save':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'directory\');'},
                }},
        };
        this.directory.fieldValue = this.fieldValue;
        this.directory.fieldHistoryArgs = this.fieldHistoryArgs;
        this.directory.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'directory\');');
//      this.directory.addLeftButton('website', 'Preview', 'M.showWebsite(\'/directory\');');
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
                'page-links-title':{'label':'Title', 'type':'text'},
                'page-links-categories-format':{'label':'Categories Format', 'type':'multitoggle', 'default':'wordcloud', 'toggles':this.linksDisplayToggles},
                'page-links-tags-format':{'label':'Tags Format', 'type':'multitoggle', 'default':'wordcloud', 'toggles':this.linksDisplayToggles},
                }},
            '_save':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'links\');'},
                }},
        };
        this.links.fieldValue = this.fieldValue;
        this.links.fieldHistoryArgs = this.fieldHistoryArgs;
        this.links.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'links\');');
//      this.links.addLeftButton('website', 'Preview', 'M.showWebsite(\'/links\');');
        this.links.addClose('Cancel');

        //
        // The options and information for the gallery page
        //
        this.gallery = new M.panel('Gallery',
            'ciniki_web_main', 'gallery',
            'mc', 'medium', 'sectioned', 'ciniki.web.main.gallery');
        this.gallery.data = {};
        this.gallery.sections = {
            'options':{'label':'', 'fields':{
                'page-gallery-active':{'label':'Display Gallery', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                'page-gallery-name':{'label':'Name', 'type':'text', 'hint':'default is Gallery'},
                'page-gallery-artcatalog-split':{'label':'Split Menu', 'active':'no', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                'page-gallery-artcatalog-format':{'label':'Format', 'active':'no', 'type':'multitoggle', 'default':'icons', 'toggles':{'icons':'Icons', 'list':'List'}},
                }},
            'sort':{'label':'Sorting', 'active':'no', 'fields':{
                'page-gallery-album-sort':{'label':'List albums by', 'type':'select', 'default':'name-asc', 'options':{}},
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
//      this.gallery.addLeftButton('website', 'Preview', 'M.showWebsite(\'/gallery\');');
        this.gallery.addClose('Cancel');

        //
        // The options and information for the writings page
        //
        this.writings = new M.panel('Writings',
            'ciniki_web_main', 'writings',
            'mc', 'medium', 'sectioned', 'ciniki.web.main.writings');
        this.writings.data = {};
        this.writings.sections = {
            'options':{'label':'', 'fields':{
                'page-writings-active':{'label':'Display Writings', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                'page-writings-name':{'label':'Name', 'type':'text', 'hint':'default is Writings'},
//              'page-writings-writingcatalog-split':{'label':'Split Menu', 'active':'no', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
//              'page-writings-writingcatalog-format':{'label':'Format', 'active':'no', 'type':'multitoggle', 'default':'icons', 'toggles':{'icons':'Icons', 'list':'List'}},
                }},
//          'sort':{'label':'Sorting', 'active':'no', 'fields':{
//              'page-writings-album-sort':{'label':'List albums by', 'type':'select', 'default':'name-asc', 'options':{}},
//              }},
            'social':{'label':'Social Media', 'visible':'no', 'fields':{
                'page-writings-share-buttons':{'label':'Sharing', 'active':'no', 'type':'multitoggle', 'default':'yes', 'toggles':this.activeToggles},
                }},
            '_save':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'writings\');'},
                }},
        };
        this.writings.fieldValue = this.fieldValue;
        this.writings.fieldHistoryArgs = this.fieldHistoryArgs;
        this.writings.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'writings\');');
//      this.writings.addLeftButton('website', 'Preview', 'M.showWebsite(\'/writings\');');
        this.writings.addClose('Cancel');

        //
        // The options and information for the products pdf catalogs page
        //
        this.pdfcatalogs = new M.panel('PDF Catalogs',
            'ciniki_web_main', 'pdfcatalogs',
            'mc', 'medium', 'sectioned', 'ciniki.web.main.pdfcatalogs');
        this.pdfcatalogs.data = {};
        this.pdfcatalogs.sections = {
            'options':{'label':'', 'fields':{
                'page-pdfcatalogs-active':{'label':'Display Catalogs', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                'page-pdfcatalogs-name':{'label':'Name', 'type':'text', 'hint':'default is Catalogs'},
                'page-pdfcatalogs-thumbnail-format':{'label':'Thumbnail Format', 'type':'toggle', 'default':'square-cropped', 'toggles':{'square-cropped':'Cropped', 'square-padded':'Padded'}},
                'page-pdfcatalogs-thumbnail-padding-color':{'label':'Padding Color', 'type':'colour'},
                }},
            '_save':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'pdfcatalogs\');'},
                }},
        };
        this.pdfcatalogs.fieldValue = this.fieldValue;
        this.pdfcatalogs.fieldHistoryArgs = this.fieldHistoryArgs;
        this.pdfcatalogs.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'pdfcatalogs\');');
        this.pdfcatalogs.addClose('Cancel');

        //
        // The options and information for the herbalist page
        //
        this.herbalist = new M.panel('Products',
            'ciniki_web_main', 'herbalist',
            'mc', 'medium', 'sectioned', 'ciniki.web.main.herbalist');
        this.herbalist.data = {};
        this.herbalist.sections = {
            'options':{'label':'', 'fields':{
                'page-herbalist-active':{'label':'Display Products', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                'page-herbalist-name':{'label':'Name', 'type':'text', 'hint':'default is Products'},
//                'page-herbalist-categories-format':{'label':'Category Format', 'type':'toggle', 'default':'thumbnails', 'toggles':{'thumbnails':'Thumbnails', 'list':'List'}},
//                'page-herbalist-categories-size':{'label':'Category Thumbnail Size', 'type':'toggle', 'default':'auto', 'toggles':this.productThumbnailToggles},
//                'page-herbalist-subcategories-size':{'label':'Sub-Category Thumbnail Size', 'type':'toggle', 'default':'auto', 'toggles':this.productThumbnailToggles},
//                'page-herbalist-thumbnail-format':{'label':'Thumbnail Format', 'type':'toggle', 'default':'square-cropped', 'toggles':{'square-cropped':'Cropped', 'square-padded':'Padded'}},
//                'page-herbalist-thumbnail-padding-color':{'label':'Padding Color', 'type':'colour'},
//                'page-herbalist-path':{'label':'Path', 'type':'toggle', 'default':'yes', 'toggles':this.activeToggles},
                }},
            'social':{'label':'Social Media', 'visible':'yes', 'fields':{
                'page-herbalist-share-buttons':{'label':'Sharing', 'active':'yes', 'type':'multitoggle', 'default':'yes', 'toggles':this.activeToggles},
                }},
            '_save':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'herbalist\');'},
                }},
        };
        this.herbalist.fieldValue = this.fieldValue;
        this.herbalist.fieldHistoryArgs = this.fieldHistoryArgs;
        this.herbalist.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'herbalist\');');
//      this.herbalist.addLeftButton('website', 'Preview', 'M.showWebsite(\'/herbalist\');');
        this.herbalist.addClose('Cancel');

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
                'page-products-categories-format':{'label':'Category Format', 'type':'toggle', 'default':'thumbnails', 'toggles':{'thumbnails':'Thumbnails', 'list':'List'}},
                'page-products-categories-size':{'label':'Category Thumbnail Size', 'type':'toggle', 'default':'auto', 'toggles':this.productThumbnailToggles},
                'page-products-subcategories-size':{'label':'Sub-Category Thumbnail Size', 'type':'toggle', 'default':'auto', 'toggles':this.productThumbnailToggles},
                'page-products-thumbnail-format':{'label':'Thumbnail Format', 'type':'toggle', 'default':'square-cropped', 'toggles':{'square-cropped':'Cropped', 'square-padded':'Padded'}},
                'page-products-thumbnail-padding-color':{'label':'Padding Color', 'type':'colour'},
                'page-products-path':{'label':'Path', 'type':'toggle', 'default':'yes', 'toggles':this.activeToggles},
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
//      this.products.addLeftButton('website', 'Preview', 'M.showWebsite(\'/products\');');
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
//              'page-recipes-tags':{'label':'Tags', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                }},
            '_save':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'recipes\');'},
                }},
        };
        this.recipes.fieldValue = this.fieldValue;
        this.recipes.fieldHistoryArgs = this.fieldHistoryArgs;
        this.recipes.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'recipes\');');
//      this.recipes.addLeftButton('website', 'Preview', 'M.showWebsite(\'/recipes\');');
        this.recipes.addClose('Cancel');

        //
        // The options and information for the blog page
        //
        this.jiji = new M.panel('Buy/Sell',
            'ciniki_web_main', 'jiji',
            'mc', 'medium', 'sectioned', 'ciniki.web.main.jiji');
        this.jiji.data = {};
        this.jiji.sections = {
            'options':{'label':'', 'fields':{
                'page-jiji-active':{'label':'Display Buy/Sell', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                'page-jiji-name':{'label':'Name', 'type':'text', 'hint':'default is Buy/Sell'},
                }},
            '_save':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'jiji\');'},
                }},
        };
        this.jiji.fieldValue = this.fieldValue;
        this.jiji.fieldHistoryArgs = this.fieldHistoryArgs;
        this.jiji.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'jiji\');');
        this.jiji.addClose('Cancel');

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
                'page-blog-share-buttons':{'label':'Sharing', 'active':'yes', 'type':'multitoggle', 'default':'yes', 'toggles':this.activeToggles},
                'page-blog-thumbnail-format':{'label':'Thumbnail Format', 'type':'toggle', 'default':'square-cropped', 'toggles':{'square-cropped':'Cropped', 'square-padded':'Padded'}},
                'page-blog-thumbnail-padding-color':{'label':'Padding Color', 'type':'colour'},
                }},
            '_save':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'blog\');'},
                }},
        };
        this.blog.fieldValue = this.fieldValue;
        this.blog.fieldHistoryArgs = this.fieldHistoryArgs;
        this.blog.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'blog\');');
//      this.blog.addLeftButton('website', 'Preview', 'M.showWebsite(\'/blog\');');
        this.blog.addClose('Cancel');

        //
        // The options and information for the patents page
        //
        this.patents = new M.panel('Patents',
            'ciniki_web_main', 'patents',
            'mc', 'medium', 'sectioned', 'ciniki.web.main.patents');
        this.patents.data = {};
        this.patents.sections = {
            'options':{'label':'', 'fields':{
                'page-patents-active':{'label':'Display Patents', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                'page-patents-name':{'label':'Name', 'type':'text', 'hint':'default is Patents'},
                'page-patents-share-buttons':{'label':'Sharing', 'active':'yes', 'type':'multitoggle', 'default':'yes', 'toggles':this.activeToggles},
                }},
            '_save':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'patents\');'},
                }},
        };
        this.patents.fieldValue = this.fieldValue;
        this.patents.fieldHistoryArgs = this.fieldHistoryArgs;
        this.patents.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'patents\');');
        this.patents.addClose('Cancel');

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
                    'thumbnail-list':'Thumbnails with Names',
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
//      this.members.addLeftButton('website', 'Preview', 'M.showWebsite(\'/members\');');
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
//              'page-dealers-categories-display':{'label':'Display Dealer Categories', 'type':'toggle', 'default':'no', 'toggles':{
//                  'no':'No',
//                  'wordlist':'List',
//                  'wordcloud':'Cloud',
//                  }},
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
                    'shortbio-blank-addressesnl-phones-links':'Short Bio, Addresses, Phones, Links',
                    'addressesnl-phones-emails-links':'Addresses, Phones, Emails, Links',
                    }},
            }},
            '_save':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'dealers\');'},
                }},
        };
        this.dealers.fieldValue = this.fieldValue;
        this.dealers.fieldHistoryArgs = this.fieldHistoryArgs;
        this.dealers.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'dealers\');');
//      this.dealers.addLeftButton('website', 'Preview', 'M.showWebsite(\'/dealers\');');
        this.dealers.addClose('Cancel');

        //
        // The options and information for the distributors page
        //
        this.distributors = new M.panel('Distributors',
            'ciniki_web_main', 'distributors',
            'mc', 'medium', 'sectioned', 'ciniki.web.main.distributors');
        this.distributors.data = {};
        this.distributors.sections = {
            'options':{'label':'', 'fields':{
                'page-distributors-active':{'label':'Display Distributors', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                'page-distributors-name':{'label':'Name', 'type':'text', 'hint':'Distributors'},
//              'page-distributors-categories-display':{'label':'Display Distributor Categories', 'type':'toggle', 'default':'no', 'toggles':{
//                  'no':'No',
//                  'wordlist':'List',
//                  'wordcloud':'Cloud',
//                  }},
                'page-distributors-locations-map-names':{'label':'Expand Short Names', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                'page-distributors-locations-display':{'label':'Display Distributor Locations', 'type':'toggle', 'default':'no', 'toggles':{
                    'no':'No',
                    'wordlist':'List',
                    'wordcloud':'Cloud',
                    }},
                'page-distributors-list-format':{'label':'Listing Content', 'type':'select', 'options':{
                    'shortbio':'Short Bio',
                    'shortbio-blank-addressesnl-phones-emails-links':'Short Bio, Addresses, Phones, Emails, Links',
                    'addressesnl-blank-shortbio-phones-emails-links':'Addresses, Short Bio, Phones, Emails, Links',
                    'shortbio-blank-addressesnl-phones-links':'Short Bio, Addresses, Phones, Links',
                    'addressesnl-phones-emails-links':'Addresses, Phones, Emails, Links',
                    }},
            }},
            '_save':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'distributors\');'},
                }},
        };
        this.distributors.fieldValue = this.fieldValue;
        this.distributors.fieldHistoryArgs = this.fieldHistoryArgs;
        this.distributors.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'distributors\');');
//      this.distributors.addLeftButton('website', 'Preview', 'M.showWebsite(\'/distributors\');');
        this.distributors.addClose('Cancel');

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
                'page-memberblog-menu-active':{'label':'Always in Menu', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
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
        // The options and information for the membersonly page
        //
        this.membersonly = new M.panel('Members Only',
            'ciniki_web_main', 'membersonly',
            'mc', 'medium', 'sectioned', 'ciniki.web.main.membersonly');
        this.membersonly.data = {};
        this.membersonly.sections = {
            'options':{'label':'', 'fields':{
                'page-membersonly-active':{'label':'Display Members Only Pages', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                'page-membersonly-menu-active':{'label':'Always in Menu', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                'page-membersonly-name':{'label':'Name', 'type':'text', 'hint':'Members Only'},
                'page-membersonly-password':{'label':'Password', 'type':'text', 'hint':''},
                }},
            '_save':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'membersonly\');'},
                }},
        };
        this.membersonly.fieldValue = this.fieldValue;
        this.membersonly.fieldHistoryArgs = this.fieldHistoryArgs;
        this.membersonly.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'membersonly\');');
//      this.membersonly.addLeftButton('website', 'Preview', 'M.showWebsite(\'/membersonly\');');
        this.membersonly.addClose('Cancel');

        //
        // The options and information for the sponsors page
        //
        this.sponsors = new M.panel('Sponsors',
            'ciniki_web_main', 'sponsors',
            'mc', 'narrow', 'sectioned', 'ciniki.web.main.sponsors');
        this.sponsors.data = {};
        this.sponsors.sections = {
            'options':{'label':'', 'fields':{
                'page-sponsors-active':{'label':'Display Sponsors', 'type':'toggle', 'default':'no', 'toggles':this.activeToggles},
                'page-sponsors-sponsorship-active':{'label':'Display Sponsorship', 'type':'toggle', 'default':'no', 'toggles':this.activeToggles},
                }},
            '_save':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'sponsors\');'},
                }},
        };
        this.sponsors.fieldValue = this.fieldValue;
        this.sponsors.fieldHistoryArgs = this.fieldHistoryArgs;
        this.sponsors.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'sponsors\');');
//      this.sponsors.addLeftButton('website', 'Preview', 'M.showWebsite(\'/sponsors\');');
        this.sponsors.addClose('Cancel');

        //
        // The options and information for the newsletters page
        //
        this.newsletters = new M.panel('Newsletters',
            'ciniki_web_main', 'newsletters',
            'mc', 'medium', 'sectioned', 'ciniki.web.main.newsletters');
        this.newsletters.data = {};
        this.newsletters.sections = {
            'options':{'label':'', 'fields':{
                'page-newsletters-active':{'label':'Display Newsletters', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                'page-newsletters-title':{'label':'Title', 'hint':'Events', 'type':'text'},
                }},
            '_save':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'newsletters\');'},
                }},
        };
        this.newsletters.fieldValue = this.fieldValue;
        this.newsletters.fieldHistoryArgs = this.fieldHistoryArgs;
        this.newsletters.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'newsletters\');');
//      this.newsletters.addLeftButton('website', 'Preview', 'M.showWebsite(\'/newsletter\');');
        this.newsletters.addClose('Cancel');

        //
        // The options and information for the surveys page
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
//      this.surveys.addLeftButton('website', 'Preview', 'M.showWebsite(\'/surveys\');');
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
//      this.exhibitions.addLeftButton('website', 'Preview', 'M.showWebsite(\'/exhibitions\');');
        this.exhibitions.addClose('Cancel');

        //
        // The options and information for the exhibitions pages
        //
        this.artgalleryexhibitions = new M.panel('Exhibitions',
            'ciniki_web_main', 'artgalleryexhibitions',
            'mc', 'medium mediumaside', 'sectioned', 'ciniki.web.main.artgalleryexhibitions');
        this.artgalleryexhibitions.data = {};
        this.artgalleryexhibitions.sections = {
            'options':{'label':'Exhibition', 'aside':'yes', 'fields':{
                'page-artgalleryexhibitions-active':{'label':'Display Exhibitions', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                'page-artgalleryexhibitions-past':{'label':'Include Past Exhibitions', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                'page-artgalleryexhibitions-initial-number':{'label':'Initial Exhibitions/page', 'active':'yes', 'type':'text', 'size':'small', 'hint':'2'},
                'page-artgalleryexhibitions-archive-number':{'label':'Archive Exhibitions/page', 'active':'yes', 'type':'text', 'size':'small', 'hint':'10'},
                'page-artgalleryexhibitions-application-details':{'label':'Display Application Information', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                }},
            '_image':{'label':'Image', 'type':'imageform', 'fields':{
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
//      this.artgalleryexhibitions.addLeftButton('website', 'Preview', 'M.showWebsite(\'/exhibitions\');');
        this.artgalleryexhibitions.addClose('Cancel');

        //
        // The options and information for the first aid pages
        //
        this.fatt = new M.panel('First Aid Courses',
            'ciniki_web_main', 'fatt',
            'mc', 'medium', 'sectioned', 'ciniki.web.main.fatt');
        this.fatt.data = {};
        this.fatt.sections = {
            'options':{'label':'Courses', 'aside':'yes', 'fields':{
                'page-fatt-active':{'label':'Display Courses', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                'page-fatt-menu-categories':{'label':'Split Menu', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                }},
            '_save':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'fatt\');'},
                }},
        };
        this.fatt.fieldValue = this.fieldValue;
        this.fatt.fieldHistoryArgs = this.fieldHistoryArgs;
//      this.fatt.addDropImage = function(iid) {
//          this.setFieldValue('page-firstaid-image', iid);
//          return true;
//      };
        this.fatt.deleteImage = this.deleteImage;
        this.fatt.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'fatt\');');
//      this.fatt.addLeftButton('website', 'Preview', 'M.showWebsite(\'/courses\');');
        this.fatt.addClose('Cancel');

        //
        // The options and information for the exhibitions pages
        //
        this.courses = new M.panel('Courses',
            'ciniki_web_main', 'courses',
            'mc', 'medium mediumaside', 'sectioned', 'ciniki.web.main.courses');
        this.courses.data = {};
        this.courses.sections = {
            'options':{'label':'Courses', 'aside':'yes', 'fields':{
                'page-courses-active':{'label':'Display Courses', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                'page-courses-name':{'label':'Name', 'type':'text', 'hint':'Courses'},
                'page-courses-upcoming-active':{'label':'Upcoming Courses', 'type':'multitoggle', 'default':'yes', 'toggles':this.activeToggles},
//              'page-courses-upcoming-name':{'label':'Name', 'type':'text', 'hint':'Upcoming Courses'},
                'page-courses-current-active':{'label':'Current Courses', 'type':'multitoggle', 'default':'yes', 'toggles':this.activeToggles},
//              'page-courses-current-name':{'label':'Name', 'type':'text', 'hint':'Current Courses'},
                'page-courses-past-active':{'label':'Past Courses', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                'page-courses-catalog-download-active':{'label':'Display Catalog Download', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                'page-courses-level-display':{'label':'Display course level', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
//              'page-courses-past-name':{'label':'Name', 'type':'text', 'hint':'Past Courses'},
                }},
            '_image':{'label':'Image', 'type':'imageform', 'fields':{
                'page-courses-image':{'label':'', 'type':'image_id', 'controls':'all', 'hidelabel':'yes', 'history':'no'},
                }},
            '_image_caption':{'label':'', 'fields':{
                'page-courses-image-caption':{'label':'Caption', 'type':'text'},
                }},
            '_content':{'label':'Content', 'fields':{
                'page-courses-content':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'large'},
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
//      this.courses.addLeftButton('website', 'Preview', 'M.showWebsite(\'/courses\');');
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
//      this.classes.addLeftButton('website', 'Preview', 'M.showWebsite(\'/classes\');');
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
            '_image':{'label':'Registration Image', 'type':'imageform', 'fields':{
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
//      this.coursesregistration.addLeftButton('website', 'Preview', 'M.showWebsite(\'/coursesregistration\');');
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
//      this.downloads.addLeftButton('website', 'Preview', 'M.showWebsite(\'/downloads\');');
        this.downloads.addClose('Cancel');

        //
        // The options and information for the customer account page
        //
        this.account = new M.panel('Customer Account',
            'ciniki_web_main', 'account',
            'mc', 'medium', 'sectioned', 'ciniki.web.main.account');
        this.account.data = {};
        this.account.sections = {
//          'info':{'label':'', 'html':'If you want to allow customers the ability to login and manage their account
            'options':{'label':'', 'fields':{
                'page-account-active':{'label':'Customer Logins', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                'page-account-dealers-only':{'label':'Dealers Only', 
                    'active':function() { return M.modFlagSet('ciniki.customers', 0x10); },
                    'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                'page-account-child-logins':{'label':'Child Logins', 
                    'active':function() { return M.modFlagSet('ciniki.customers', 0x200000); },
                    'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                'page-account-header-buttons':{'label':'Header Buttons', 'type':'multitoggle', 'default':'yes', 'toggles':this.activeToggles},
                'page-account-sidebar':{'label':'Sidebar', 'visible':function() {return (M.curBusiness.modules['ciniki.web'].flags&0x0100)?'yes':'no';},
                    'type':'multitoggle', 'default':'no', 'toggles':{'no':'No', 'left':'Left', 'right':'Right'}},
                'page-account-header-signin-text':{'label':'Signin Text', 'type':'text', 'size':'small'},
                'page-account-password-change':{'label':'Change Password', 'type':'multitoggle', 'default':'yes', 'toggles':this.activeToggles},
                'page-account-phone-update':{'label':'Phone Update', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                'page-account-email-update':{'label':'Email Update', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                'page-account-address-update':{'label':'Address Update', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                'page-account-timeout':{'label':'Page Timeout', 'type':'text', 'size':'small'},
                'page-account-invoices-list':{'label':'View Orders', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                'page-account-invoices-view-details':{'label':'View Order Details', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                'page-account-invoices-view-pdf':{'label':'Download Invoice', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                'page-account-children-update':{'label':'Children Update', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                }},
            'options2':{'label':'Child Accounts', 
                'active':function() { return M.modFlagSet('ciniki.customers', 0x02); },
                'fields':{
                    'page-account-children-member-10-update':{'label':'Regular Member Children', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                    'page-account-children-member-20-update':{'label':'Student Member Children', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                    'page-account-children-member-30-update':{'label':'Individual Member Children', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                    'page-account-children-member-40-update':{'label':'Family Member Children', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                    'page-account-children-member-110-update':{'label':'Complimentary Member Children', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                    'page-account-children-member-150-update':{'label':'Reciprocal Member Children', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                    'page-account-children-member-lifetime-update':{'label':'Lifetime Membership', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                    'page-account-children-member-non-update':{'label':'Non-Member', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                }},
            'redirect':{'label':'On login', 'fields':{
                //'page-account-signin-redirect':{'label':'Redirect to', 'type':'select', 'options':{}},
                'page-account-signin-redirect':{'label':'Redirect to', 'type':'text'},
                }},
            'welcome':{'label':'Sign in Greeting', 'fields':{
                'page-account-content':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'medium', 
                    'hint':'This appears when the customer signs into your website'},
                }},
//          'subscriptions':{'label':'Subscription Message', 'active':'no', 'fields':{
//              'page-account-content-subscriptions':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'medium', 'hint':''},
//              }},
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
            'mc', 'medium', 'sectioned', 'ciniki.web.main.cart');
        this.cart.data = {};
        this.cart.sections = {
            'options':{'label':'', 'fields':{
                'page-cart-active':{'label':'Enable Cart', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                'page-cart-product-search':{'label':'Product Search', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                'page-cart-product-list':{'label':'Product List', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                'page-cart-po-number':{'label':'Purchase Order Number', 'type':'multitoggle', 'default':'no', 'toggles':this.activeRequiredToggles},
                'page-cart-customer-notes':{'label':'Customer Notes', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                'page-cart-currency-display':{'label':'Display Currency', 'type':'multitoggle', 'default':'yes', 'toggles':this.activeToggles},
                'page-cart-currency-display':{'label':'Display Currency', 'type':'multitoggle', 'default':'yes', 'toggles':this.activeToggles},
                'page-cart-registration-child-select':{'label':'Registration Children', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                'page-cart-account-create-button':{'label':'Create Account Button', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                'page-cart-child-create-button':{'label':'Create Child Button', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                }},
            '_inventory':{'label':'Current Inventory Visible To', 'fields':{
                'page-cart-inventory-customers-display':{'label':'Customers', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                'page-cart-inventory-members-display':{'label':'Members', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                'page-cart-inventory-dealers-display':{'label':'Dealers', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                'page-cart-inventory-distributors-display':{'label':'Distributors', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                }},
            '_nologinmessage':{'label':'No Account Message', 'fields':{
                'page-cart-noaccount-message':{'label':'', 'hidelabel':'yes', 'type':'textarea'},
                }},
            '_paymentsuccessmessage':{'label':'Payment Success Message', 'fields':{
                'page-cart-payment-success-message':{'label':'', 'hidelabel':'yes', 'type':'textarea'},
                }},
            '_paymentsuccessmessageemails':{'label':'Notification emails for payments', 'fields':{
                'page-cart-payment-success-emails':{'label':'', 'hidelabel':'yes', 'type':'text'},
                }},
            '_dealersubmit':{'label':'', 'active':'no', 'fields':{
                'page-cart-dealersubmit-email-template':{'label':'Email Dealer Order Template', 'type':'multitoggle', 'default':'none', 'toggles':this.dealerSubmitTemplates},
                }},
            '_dealersubmit_email_textmsg':{'label':'Dealer Submit Order Email Message', 'active':'no', 'fields':{
                'page-cart-dealersubmit-email-textmsg':{'label':'', 'hidelabel':'yes', 'type':'textarea'},
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

        //
        // The options and information for the search page
        //
        this.search = new M.panel('Search',
            'ciniki_web_main', 'search',
            'mc', 'medium', 'sectioned', 'ciniki.web.main.search');
        this.search.data = {};
        this.search.sections = {
            'options':{'label':'Options', 'fields':{
                'page-search-active':{'label':'Active', 'type':'multitoggle', 'default':'no', 'toggles':this.activeToggles},
                }},
            '_save':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_web_main.savePage(\'search\');'},
                }},
        };
        this.search.fieldValue = this.fieldValue;
        this.search.fieldHistoryArgs = this.fieldHistoryArgs;
        this.search.addButton('save', 'Save', 'M.ciniki_web_main.savePage(\'search\');');
        this.search.addClose('Cancel');
    }

    this.start = function(cb, ap, aG) {
        args = {};
        if( aG != null ) { args = eval(aG); }

        //
        // Create the app container if it doesn't exist, and clear it out
        // if it does exist.
        //
        var appContainer = M.createContainer(ap, 'ciniki_web_main', 'yes');
        if( appContainer == null ) {
            alert('App Error');
            return false;
        } 

        //
        // Setup active fields
        //
        if( M.curBusiness.modules['ciniki.links'] != null ) {
            this.links.sections.options.fields['page-links-categories-format'].active = 
                ((M.curBusiness.modules['ciniki.links'].flags&0x01)>0?'yes':'no');
            this.links.sections.options.fields['page-links-tags-format'].active = 
                ((M.curBusiness.modules['ciniki.links'].flags&0x02)>0?'yes':'no');
        }
        if( M.curBusiness.modules['ciniki.web'] != null ) {
            if( (M.curBusiness.modules['ciniki.web'].flags&0x04) > 0 ) {
                this.contact.sections._contact_form.active = 'yes';
                this.contact.sections._contact_form_intro_message.active = 'yes';
                this.contact.sections._contact_form_submitted_message.active = 'yes';
            } else {
                this.contact.sections._contact_form.active = 'no';
                this.contact.sections._contact_form_intro_message.active = 'no';
                this.contact.sections._contact_form_submitted_message.active = 'no';
            }
            if( (M.curBusiness.modules['ciniki.web'].flags&0x08) > 0 ) {
                this.menu.sections.advanced.list.collections.visible = 'yes';
                this.home.sections._collections.active = 'yes';
            } else {
                this.menu.sections.advanced.list.collections.visible = 'no';
                this.home.sections._collections.active = 'no';
            }
            if( (M.curBusiness.modules['ciniki.web'].flags&0x10) > 0 ) {
                this.home.sections._quicklinks.active = 'yes';
            } else {
                this.home.sections._quicklinks.active = 'no';
            }
            if( (M.curBusiness.modules['ciniki.web'].flags&0x40) > 0 ) {
                this.menu.sections.pages.addTxt = 'Add Page';
            } else {
                this.menu.sections.pages.addTxt = '';
            }
            if( (M.curBusiness.modules['ciniki.web'].flags&0x0100) > 0 ) {
                this.menu.sections.advanced.list.privatethemes.visible = 'yes';
            } else {
                this.menu.sections.advanced.list.privatethemes.visible = 'no';
            }
            this.home.sections._content_layout.active = ((M.curBusiness.modules['ciniki.web'].flags&0x0800)>0?'yes':'no');
        }
        this.home.sections._slideshow.active=(M.curBusiness.modules['ciniki.artcatalog']!=null)?'yes':'no';
        this.home.sections._memberslideshow.active=(M.curBusiness.modules['ciniki.customers']!=null&&(M.curBusiness.modules['ciniki.customers'].flags&0x02)>0)?'yes':'no';
        this.home.sections._artcatalog.active=(M.curBusiness.modules['ciniki.artcatalog']!=null)?'yes':'no';
        this.home.sections._gallery.active=(M.curBusiness.modules['ciniki.gallery']!=null)?'yes':'no';
        this.home.sections._recipes.active=(M.curBusiness.modules['ciniki.recipes']!=null)?'yes':'no';
        this.home.sections._events.active=(M.curBusiness.modules['ciniki.events']!=null)?'yes':'no';
        this.home.sections._filmschedule.active=(M.curBusiness.modules['ciniki.filmschedule']!=null)?'yes':'no';
        this.home.sections._products.active=(M.curBusiness.modules['ciniki.products']!=null)?'yes':'no';
        this.home.sections._writings.active=(M.curBusiness.modules['ciniki.writingcatalog']!=null)?'yes':'no';
        this.home.sections._workshops.active = (M.curBusiness.modules['ciniki.workshops']!=null)?'yes':'no';
        this.home.sections._artgalleryexhibitions.active=(M.curBusiness.modules['ciniki.artgallery']!=null)?'yes':'no';
//        this.home.sections._seo.active=((M.curBusiness.modules['ciniki.web'].flags&0x8000)>0)?'yes':'no';

        if( M.curBusiness.modules['ciniki.blog'] != null ) {
            if( (M.curBusiness.modules['ciniki.blog'].flags&0x01) > 0 ) {
                this.home.sections._blog.active = 'yes';
            } else {
                this.home.sections._blog.active = 'no';
            }
        }
        if( M.curBusiness.modules['ciniki.events'] != null ) {
            if( (M.curBusiness.modules['ciniki.events'].flags&0x10) > 0 ) {
                this.events.sections.options.fields['page-events-categories-display'].active = 'yes';
                this.events.sections._image.active = 'yes';
                this.events.sections._image_caption.active = 'yes';
                this.events.sections._content.active = 'yes';
            } else {
                this.events.sections.options.fields['page-events-categories-display'].active = 'no';
                this.events.sections._image.active = 'no';
                this.events.sections._image_caption.active = 'no';
                this.events.sections._content.active = 'no';
            }
        }

        //
        // Setup for cart
        //
        if( M.modOn('ciniki.sapos') && M.modOn('ciniki.mail') && M.modFlagOn('ciniki.customers', 0x10) ) {
            this.cart.sections._dealersubmit.active = 'yes';
            this.cart.sections._dealersubmit_email_textmsg.active = 'yes';
        } else {
            this.cart.sections._dealersubmit.active = 'no';
            this.cart.sections._dealersubmit_email_textmsg.active = 'no';
        }

        //
        // Setup for sponsors
        //
        if( M.modOn('ciniki.sponsors') && (M.curBusiness.modules['ciniki.sponsors'].flags&0x02) ) {
            this.home.sections.sponsors.visible = 'yes';
        } else {
            this.home.sections.sponsors.visible = 'no';
        }


        //
        // Setup the gallery sort fields
        //
        if( M.curBusiness.modules['ciniki.gallery'] != null ) {
            var options = {
                'name-asc':'Name A-Z',
                'name-desc':'Name Z-A',
            };
            if( (M.curBusiness.modules['ciniki.gallery'].flags&0x01) > 0 ) {
                options['sequence-asc'] = 'Sequence, 1-999';
                options['sequence-desc'] = 'Sequence, 999-1';
            }
            if( (M.curBusiness.modules['ciniki.gallery'].flags&0x02) > 0 ) {
                options['startdate-desc'] = 'Date, newest first';
                options['startdate-asc'] ='Date, oldest first';
            }
            this.gallery.sections.sort.fields['page-gallery-album-sort'].options = options;
            this.gallery.sections.sort.active = 'yes';
        } else {
            this.gallery.sections.sort.active = 'no';
        }

        this.showMenu(cb);
    }

    this.showMenu = function(cb) {
        //
        // If the user is a sysadmin, then add the clear web cache button
        // This may become available to users, but might be too complicated
        //
        if( M.userPerms&0x01 == 0x01 || M.curBusiness.permissions.resellers != null ) {
            this.menu.size = 'medium mediumaside';
            this.menu.sections._url.aside = 'yes';
            this.menu.sections.settings.aside = 'yes';
            this.menu.sections.pages.aside = 'yes';
            this.menu.sections.module_pages.aside = 'yes';
            this.menu.sections.adm = {'label':'Admin Options', 'list':{
                'google':{'label':'Google Settings', 'fn':'M.ciniki_web_main.showSiteSettings(\'M.ciniki_web_main.showMenu();\',\'google\');' },
                'meta':{'label':'Meta Settings', 'fn':'M.ciniki_web_main.showSiteSettings(\'M.ciniki_web_main.showMenu();\',\'meta\');' },
                'ssl':{'label':'SSL', 'fn':'M.ciniki_web_main.showSiteSettings(\'M.ciniki_web_main.showMenu();\',\'ssl\');'},
                'css':{'label':'Custom CSS', 'fn':'M.ciniki_web_main.showSiteSettings(\'M.ciniki_web_main.showMenu();\',\'css\');'},
                'layout':{'label':'Layout', 'fn':'M.ciniki_web_main.showLayouts(\'M.ciniki_web_main.showMenu();\');'},
                }};
            this.menu.sections.admin = {'label':'Admin Options', 'buttons':{
                'clearimagecache':{'label':'Clear Cache', 'fn':'M.ciniki_web_main.clearCache();'},
                'clearcontentcache':{'label':'Clear Content Cache', 'fn':'M.ciniki_web_main.clearContentCache();'},
                'updateindex':{'label':'Update Index', 'fn':'M.ciniki_web_main.updateIndex();'},
                }};
            this.home.sections.redirects.active = 'yes';
        } else {
            this.menu.size = 'medium';
            this.menu.sections._url.aside = 'no';
            this.menu.sections.settings.aside = 'no';
            this.menu.sections.pages.aside = 'no';
            this.menu.sections.module_pages.aside = 'no';
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
            var p = M.ciniki_web_main.menu;
            p.data.pages = rsp.pages;
            if( rsp.module_pages != null && rsp.module_pages.length > 0 ) {
                p.sections.module_pages.active = 'yes';
                p.data.module_pages = rsp.module_pages;
            } else {
                p.sections.module_pages.active = 'no';
            }

            p.data.settings = [];
            for(i in rsp.settings) {
                if( rsp.settings[i].setting.name == 'theme' ) {
                    p.data.settings[i] = rsp.settings[i];
                }
                if( rsp.settings[i].setting.name == 'layout' ) {
                    M.ciniki_web_main.layout.data['site-layout'] = rsp.settings[i].setting.value;
                }
            }
            p.sections._url.list.url.value = rsp.url;
//          p.sections._url.list.url.fn = 'window.open(\'' + rsp.url + '\');';
//          p.data.settings = rsp.settings;
            M.ciniki_web_main.background.data = {};
            for(i in rsp.background) {
                M.ciniki_web_main.background.data[rsp.background[i].setting.name] = rsp.background[i].setting.value;
            }
            M.ciniki_web_main.header.data = {};
            for(i in rsp.header) {
                M.ciniki_web_main.header.data[rsp.header[i].setting.name] = rsp.header[i].setting.value;
            }
            if( rsp.landingpages != null ) {
                this.landingpages = {'':'None'};
                for(i in rsp.landingpages) {
                    this.landingpages[rsp.landingpages[i].permalink] = rsp.landingpages[i].short_title;
                }
                M.ciniki_web_main.header.sections._landingpage1.fields['site-header-landingpage1-permalink'].options = this.landingpages;
                M.ciniki_web_main.footer.sections._landingpage1.fields['site-footer-landingpage1-permalink'].options = this.landingpages;
            }
            M.ciniki_web_main.footer.data = {};
            for(i in rsp.footer) {
                M.ciniki_web_main.footer.data[rsp.footer[i].setting.name] = rsp.footer[i].setting.value;
            }
            if( rsp.settings['page-home-url'] != null ) {
                M.ciniki_web_main.header.data['page-home-url'] = rsp.settings['page-home-url'];
            }
            p.data.advanced = rsp.advanced;
            
            //
            // Allow sysadmins to mark a site as featured for the home page
            //
            if( M.userPerms&0x01 == 0x01 ) {
                if( rsp.featured == 'yes' ) {
                    p.sections.admin.buttons.featured = {'label':'Remove Featured', 'fn':'M.ciniki_web_main.removeFeatured();'};
                } else {
                    p.sections.admin.buttons.featured = {'label':'Make Featured', 'fn':'M.ciniki_web_main.makeFeatured();'};
                }
            }

            p.refresh();
            p.show(cb);
        });
    }

    this.showThemes = function(cb, themeName) {
        this.theme.reset();
        this.theme.data = {'site-theme':themeName};
        this.theme.refresh();
        this.theme.show(cb);
    };

    this.showLayouts = function(cb) {
        this.showPage(cb, 'layout');
//        this.layout.refresh();
//        this.layout.show(cb);
    };

    this.showHeader = function(cb) {
        this.header.refresh();
        this.header.show(cb);
    };
    this.showBackground = function(cb) {
        this.background.refresh();
        this.background.show(cb);
    };

    this.showFooter = function(cb) {
        this.footer.refresh();
        this.footer.show(cb);
    };

    this.showMyLiveChat = function(cb) {
        M.api.getJSONCb('ciniki.web.siteSettingsGet', {'business_id':M.curBusinessID}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.ciniki_web_main.mylivechat;
            p.data = rsp.settings;
            p.refresh();
            p.show(cb);
        });
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
            M.api.getJSONCb('ciniki.web.pageSettingsGet', {'business_id':M.curBusinessID, 'page':'courses-' + subpage, 'content':'yes'}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                M.ciniki_web_main.showPageFinish(cb, page, subpage, subpagetitle, rsp);
            });
        } else if( page == 'coursesregistration' ) {
            M.api.getJSONCb('ciniki.web.pageSettingsGet', {'business_id':M.curBusinessID, 'page':'courses-registration', 'content':'yes'}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                M.ciniki_web_main.showPageFinish(cb, page, subpage, subpagetitle, rsp);
            });
        } else if( page == 'layout' ) {
            M.api.getJSONCb('ciniki.web.pageSettingsGet', {'business_id':M.curBusinessID, 'page':'home', 'content':'yes'}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                M.ciniki_web_main.showPageFinish(cb, page, subpage, subpagetitle, rsp);
            });
        } else {
            M.api.getJSONCb('ciniki.web.pageSettingsGet', {'business_id':M.curBusinessID, 'page':page, 'content':'yes', 'sponsors':'yes'}, function(rsp) {
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
        if( rsp.sponsors != null ) { 
            this[page].data.sponsors = rsp.sponsors;
        } else {
            this[page].data.sponsors = null;
        }

        if( page == 'contact' ) {
            this.contact.business_address = rsp.business_address;
            this.showContact(cb);
        } else if( page == 'home' ) {
            if( this[page].data != null && this[page].data['page-home-gallery-slider-size'] == null ) {
                this[page].data['page-home-gallery-slider-size'] = 'xlarge';
            }
            if( this[page].data != null && this[page].data['page-home-membergallery-slider-size'] == null ) {
                this[page].data['page-home-membergallery-slider-size'] = 'xlarge';
            }
            this.home.sections._imagetabs.selected = 1;
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
//          if( M.curBusiness.modules['ciniki.artcatalog'] != null ) {
//              this.home.sections.options.fields['page-home-gallery-slider-type'].active = 'yes';
//              this.home.sections.options.fields['page-home-gallery-slider-size'].active = 'yes';
//              this.home.sections.options.fields['page-home-gallery-latest'].active = 'yes';
//              this.home.sections.options.fields['page-home-gallery-latest-title'].active = 'yes';
//              this.home.sections.options.fields['page-home-gallery-random'].active = 'yes';
//              this.home.sections.options.fields['page-home-gallery-random-title'].active = 'yes';
//          } else if( M.curBusiness.modules['ciniki.products'] != null ) {
//              this.home.sections.options.fields['page-home-gallery-slider-type'].active = 'no';
//              this.home.sections.options.fields['page-home-gallery-slider-size'].active = 'no';
//              this.home.sections.options.fields['page-home-gallery-slider'].active = 'no';
//              this.home.sections.options.fields['page-home-products-latest'].active = 'yes';
//              this.home.sections.options.fields['page-home-products-latest-title'].active = 'yes';
//          } else {
//              this.home.sections.options.fields['page-home-gallery-slider-type'].active = 'no';
//              this.home.sections.options.fields['page-home-gallery-slider-size'].active = 'no';
//              this.home.sections.options.fields['page-home-gallery-latest'].active = 'no';
//              this.home.sections.options.fields['page-home-gallery-latest-title'].active = 'no';
//              this.home.sections.options.fields['page-home-gallery-random'].active = 'no';
//              this.home.sections.options.fields['page-home-gallery-random-title'].active = 'no';
//          }
//          this.home.sections.options.fields['page-home-upcoming-artgalleryexhibitions'].active = (M.curBusiness.modules['ciniki.artgallery']!=null)?'yes':'no';
            this[page].refresh();
            this[page].show(cb);
        } else if( page == 'events' ) {
            if( this[page].data['page-events-past'] == 'yes' ) {
                this[page].sections.options.fields['page-events-upcoming-empty-hide'].visible = 'yes';
            } else {
                this[page].sections.options.fields['page-events-upcoming-empty-hide'].visible = 'no';
            }
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
//          if( M.curBusiness.modules['ciniki.subscriptions'] != null ) {
//              this.account.sections.subscriptions.active = 'yes';
//          } else {
//              this.account.sections.subscriptions.active = 'no';
//          }
            if( M.curBusiness.modules['ciniki.sapos'] != null ) {
                this.account.sections.options.fields['page-account-invoices-list'].active = 'yes';
                this.account.sections.options.fields['page-account-invoices-view-details'].active = 'yes';
                this.account.sections.options.fields['page-account-invoices-view-pdf'].active = 'yes';
                this.account.sections.options.fields['page-account-password-change'].active = 'no';
                this.account.sections.options.fields['page-account-header-buttons'].active = 'no';
            } else {
                this.account.sections.options.fields['page-account-invoices-list'].active = 'no';
                this.account.sections.options.fields['page-account-invoices-view-details'].active = 'no';
                this.account.sections.options.fields['page-account-invoices-view-pdf'].active = 'no';
                this.account.sections.options.fields['page-account-password-change'].active = 'yes';
                this.account.sections.options.fields['page-account-header-buttons'].active = 'yes';
            }
            // Setup the redirects
//            var popts = {'':'Nowhere', '/':'Home', 'back':'Previous Page'};
//          if( M.curBusiness.modules['ciniki.artcatalog'] != null ) { popts['/gallery'] = 'Gallery'; }
//          if( M.curBusiness.modules['ciniki.gallery'] != null ) { popts['/gallery'] = 'Gallery'; }
//            if( M.curBusiness.modules['ciniki.blog'] != null 
//                && (M.curBusiness.modules['ciniki.blog'].flags&0x100) > 0) { popts['/memberblog'] = 'Member Blog'; }
//            if( M.curBusiness.modules['ciniki.membersonly'] != null ) { popts['/membersonly'] = 'Members Only'; }
//            this.account.sections.redirect.fields['page-account-signin-redirect'].options = popts;
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
                '_image':{'label':'Image', 'type':'imageform', 'fields':{}},
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

    this.updateSponsors = function(page) {
        M.api.getJSONCb('ciniki.web.pageSettingsGet', {'business_id':M.curBusinessID, 'page':page, 'sponsors':'yes'}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.ciniki_web_main[page];
            if( rsp.sponsors != null ) {
                p.data.sponsors = rsp.sponsors;
            } else {
                p.data.sponsors = null;
            }
            p.refreshSection('sponsors');
            p.show();
        });
    };

//  this.showLogo = function(cb, logo) {
//      this.logo.reset();
//      this.logo.data = {'site-logo-display':logo};
//      this.logo.refresh();
//      this.logo.show(cb);
//  };

    this.showCustom = function(cb, page, subpage, subpagetitle) {
        this.custom.reset();
        this.custom.number = parseInt(page.match(/-([0-9][0-9][0-9])/));
        this.custom.sections = {
            'options':{'label':'', 'fields':{}},
            '_image':{'label':'Image', 'type':'imageform', 'fields':{}},
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
                        'label':u.firstname + ' ' + u.lastname, 
                        'editFn':'M.startApp(\'ciniki.businesses.users\',null,\'M.ciniki_web_main.contact.show();\',\'mc\',{\'user_id\':\'' + u.id + '\'});',
                        'type':'flags', 'join':'yes', 'flags':M.ciniki_web_main.userFlags,
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

    this.savePage = function(page, cb) {
        if( cb == null ) { cb = 'M.ciniki_web_main[\'' + page + '\'].close();'; }
        var c = this[page].serializeForm('no');
        if( c != '' ) {
            M.api.postJSONCb('ciniki.web.siteSettingsUpdate', {'business_id':M.curBusinessID}, c, function(rsp) {
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

    this.clearCache = function(page) {
        if( confirm('Are you sure you wish to clear the web cache?') ) {
            var rsp = M.api.getJSONCb('ciniki.web.clearCache', {'business_id':M.curBusinessID}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                alert("The cache has been cleared. ****** REBUILD INDEX ******* ");
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

    this.updateIndex = function(page) {
        var rsp = M.api.getJSONCb('ciniki.web.indexUpdateNow', {'business_id':M.curBusinessID}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            alert("The index has been updated");
        });
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
