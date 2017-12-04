//
// This app will handle the listing, additions and deletions of events.  These are associated tenant.
//
function ciniki_web_pages() {
    //
    // Panels
    //
    this.childFormat = {
        '5':{'name':'List'},
        '8':{'name':'Image List'},
        '10':{'name':'Name List'},
//      '6':{'name':'Menu'},
//      '32':{'name':'List'},
        };
    this.parentChildrenFormat = {
        '5':{'name':'List'},
        '6':{'name':'Menu'},
        '7':{'name':'Page Menu'},
        '8':{'name':'Image List'},
//      '32':{'name':'List'},
        };
    this.menuFlags = {
        '1':{'name':'Header'},
        '2':{'name':'Footer'},
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
                'mc', 'medium', 'sectioned', 'ciniki.web.pages.edit');
            this[pn].data = {}; 
            this[pn].modules_pages = {};
            this[pn].stackedData = [];
            this[pn].page_id = pid;
            this[pn].sections = {
                'details':{'label':'', 'aside':'yes', 'fields':{
                    'parent_id':{'label':'Parent Page', 'type':'select', 'options':{}},
                    'title':{'label':'Menu Title', 'type':'text'},
                    'article_title':{'label':'Page Title', 'visible':'no', 'type':'text'},
                    'sequence':{'label':'Page Order', 'type':'text', 'size':'small'},
                    '_flags_1':{'label':'Visible', 'type':'flagtoggle', 'bit':0x01, 'field':'flags_1', 'default':'on'},
                    '_flags_2':{'label':'Private', 'type':'flagtoggle', 'bit':0x02, 'field':'flags_2', 'default':'off',
                        'active':(M.curTenant.modules['ciniki.customers'] != null ? 'yes' : 'no'),
                        },
                    'menu_flags':{'label':'Menu Options', 'type':'flags', 'flags':this.menuFlags},
                }},
                '_page_type':{'label':'', 'aside':'yes', 'visible':'hidden', 'fields':{
                    'page_type':{'label':'Page Type', 'type':'toggle', 'toggles':{}, 'onchange':'M.ciniki_web_pages[\'' + pn + '\'].setPageType();'},
                    }},
                '_redirect':{'label':'Redirect', 'visible':'hidden', 'fields':{
                    'page_redirect_url':{'label':'URL', 'type':'text'},
                    }},
                '_module':{'label':'Module', 'visible':'hidden', 'fields':{
                    'page_module':{'label':'Module', 'type':'select', 'options':{}, 'onchangeFn':'M.ciniki_web_pages[\'' + pn + '\'].setModuleOptions();'},
                    }},
                '_module_options':{'label':'Options', 'visible':'hidden', 'fields':{
                    }},
                '_image':{'label':'', 'aside':'yes', 'visible':'hidden', 'type':'imageform', 'fields':{
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
                '_image_caption':{'label':'', 'aside':'yes', 'visible':'hidden', 'fields':{
                    'primary_image_caption':{'label':'Caption', 'type':'text'},
    //              'primary_image_url':{'label':'URL', 'type':'text'},
                }},
                '_synopsis':{'label':'Synopsis', 'visible':'hidden', 'fields':{
                    'synopsis':{'label':'', 'type':'textarea', 'size':'small', 'hidelabel':'yes'},
                }},
                '_content':{'label':'Content', 'visible':'hidden', 'fields':{
                    'content':{'label':'', 'type':'textarea', 'size':'large', 'hidelabel':'yes'},
                }},
                'files':{'label':'Files', 'aside':'yes', 'visible':'hidden', 
                    'type':'simplegrid', 'num_cols':1,
                    'headerValues':null,
                    'cellClasses':[''],
                    'addTxt':'Add File',
                    'addFn':'M.ciniki_web_pages.'+pn+'.editComponent(\'ciniki.web.pagefiles\',\'M.ciniki_web_pages.'+pn+'.updateFiles();\',{\'file_id\':\'0\'});',
                    },
                '_files':{'label':'', 'aside':'yes', 'visible':'hidden', 'fields':{
                    '_flags_14':{'label':'Reverse Order', 'type':'flagtoggle', 'bit':0x1000, 'field':'flags_14', 'default':'on'},
                }},
                'images':{'label':'Gallery', 'aside':'yes', 'visible':'hidden', 'type':'simplethumbs'},
                '_images':{'label':'', 'aside':'yes', 'visible':'hidden', 'type':'simplegrid', 'num_cols':1,
                    'addTxt':'Add Image',
                    'addFn':'M.ciniki_web_pages.'+pn+'.editComponent(\'ciniki.web.pageimages\',\'M.ciniki_web_pages.'+pn+'.addDropImageRefresh();\',{\'add\':\'yes\'});',
                    },
                '_children':{'label':'Child Pages', 'visible':'hidden', 'fields':{
                    'child_title':{'label':'Heading', 'type':'text'},
                    'child_format':{'label':'Format', 'active':'yes', 'type':'flags', 'toggle':'yes', 'none':'no', 'join':'yes', 'flags':this.childFormat},
                }},
                'pages':{'label':'', 'type':'simplegrid', 'visible':'hidden', 'num_cols':1, 
                    'addTxt':'Add Child Page',
                    'addFn':'M.ciniki_web_pages.'+pn+'.childEdit(0);',
                    },
                'sponsors':{'label':'Sponsors', 'type':'simplegrid', 'visible':'hidden', 'num_cols':1,
                    'addTxt':'Manage Sponsors',
                    'addFn':'M.ciniki_web_pages.'+pn+'.sponsorEdit(0);',
                    },
                '_buttons':{'label':'', 'buttons':{
                    'save':{'label':'Save', 'fn':'M.ciniki_web_pages.'+pn+'.savePage();'},
                    'delete':{'label':'Delete', 'visible':(pid==0?'no':'yes'), 'fn':'M.ciniki_web_pages.'+pn+'.deletePage();'},
                }},
            };
            this[pn].fieldHistoryArgs = function(s, i) {
                return {'method':'ciniki.web.pageHistory', 'args':{'tnid':M.curTenantID,
                    'page_id':this.page_id, 'field':i}};
            };
            this[pn].sectionData = function(s) { 
                return this.data[s];
            };
            this[pn].fieldValue = function(s, i, j, d) {
                if( i == 'parent_id' ) { return ' ' + this.data[i]; }
                return this.data[i];
            };
            this[pn].cellValue = function(s, i, j, d) {
                if( s == 'pages' ) {
                    return d.page.title;
                } else if( s == 'files' ) {
                    return d.file.name;
                } else if( s == 'sponsors' && j == 0 ) { 
                    return '<span class="maintext">' + d.sponsor.title + '</span>';
                }
            };
            this[pn].rowFn = function(s, i, d) {
                if( s == 'pages' ) {
                    return 'M.ciniki_web_pages.'+pn+'.childEdit(\'' + d.page.id + '\');';
                } else if( s == 'files' ) {
                    return 'M.startApp(\'ciniki.web.pagefiles\',null,\'M.ciniki_web_pages.'+pn+'.updateFiles();\',\'mc\',{\'file_id\':\'' + d.file.id + '\'});';
                } else if( s == 'sponsors' ) {
                    return 'M.startApp(\'ciniki.sponsors.ref\',null,\'M.ciniki_web_pages.'+pn+'.updateSponsors();\',\'mc\',{\'ref_id\':\'' + d.sponsor.ref_id + '\'});';
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
                        {'tnid':M.curTenantID}, c);
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    this.page_id = rsp.id;
                }
                var rsp = M.api.getJSON('ciniki.web.pageImageAdd', 
                    {'tnid':M.curTenantID, 'image_id':iid, 'page_id':this.page_id});
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                return true;
            };
            this[pn].addDropImageRefresh = function() {
                if( M.ciniki_web_pages[pn].page_id > 0 ) {
                    M.api.getJSONCb('ciniki.web.pageGet', {'tnid':M.curTenantID, 
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
                        {'tnid':M.curTenantID}, c, function(rsp) {
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
                    M.api.getJSONCb('ciniki.web.pageGet', {'tnid':M.curTenantID, 
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
                    M.api.getJSONCb('ciniki.web.pageGet', {'tnid':M.curTenantID, 
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
            this[pn].updateSponsors = function() {
                if( this.page_id > 0 ) {
                    M.api.getJSONCb('ciniki.web.pageGet', {'tnid':M.curTenantID, 
                        'page_id':this.page_id, 'sponsors':'yes'}, function(rsp) {
                            if( rsp.stat != 'ok' ) {
                                M.api.err(rsp);
                                return false;
                            }
                            var p = M.ciniki_web_pages[pn];
                            p.data.sponsors = rsp.page.sponsors;
                            p.refreshSection('sponsors');
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
                        {'tnid':M.curTenantID}, c, function(rsp) {
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
            this[pn].sponsorEdit = function(cid) {
                if( this.page_id == 0 ) {
                    // Save existing data as new page
                    var p = this;
                    var c = this.serializeFormData('yes');
                    M.api.postJSONFormData('ciniki.web.pageAdd', 
                        {'tnid':M.curTenantID}, c, function(rsp) {
                            if( rsp.stat != 'ok' ) {
                                M.api.err(rsp);
                                return false;
                            }
                            p.page_id = rsp.id;
//                          M.ciniki_web_pages.pageEdit('M.ciniki_web_pages.'+pn+'.updateChildren();',cid,p.page_id);
                            M.startApp('ciniki.sponsors.ref',null,p.panelRef+'.updateSponsors();','mc',{'object':'ciniki.web.page','object_id':p.page_id});
                        });
                } else {
                    M.startApp('ciniki.sponsors.ref',null,this.panelRef+'.updateSponsors();','mc',{'object':'ciniki.web.page','object_id':this.page_id});
                }
            };
            // 
            // Add or remove sections based on page type
            //
            this[pn].setPageType = function() {
                var pt = this.formValue('page_type');
                var p = M.gE(this.panelUID);
                if( pt == '10' ) {
                    p.children[0].className = 'medium mediumaside';
                } else if( pt == '11' ) {
                    p.children[0].className = 'xlarge';
                } else {
                    p.children[0].className = 'medium';
                }
                this.sections._module_options.visible = 'hidden';
                this.sections._image.visible = (pt=='10' || (pt==20 && this.data.parent_id > 0)?'yes':'hidden');
                this.sections._image_caption.visible = (pt=='10'?'yes':'hidden');
                this.sections._synopsis.visible = (pt=='10' || (pt==20 && this.data.parent_id > 0)?'yes':'hidden');
                this.sections._content.visible = ((pt=='10'||pt==11)?'yes':'hidden');
                this.sections.files.visible = (pt=='10'?'yes':'hidden');
                this.sections._files.visible = (pt=='10'?'yes':'hidden');
                this.sections.images.visible = (pt=='10'?'yes':'hidden');
                this.sections._images.visible = (pt=='10'?'yes':'hidden');
                this.sections._children.visible = (pt=='10'?'yes':'hidden');
                this.sections.pages.visible = (pt=='10'||pt=='11'?'yes':'hidden');
                this.sections.sponsors.visible = (pt=='10'?'yes':'hidden');
                this.sections._redirect.visible = (pt=='20'?'yes':'hidden');
                this.sections._module.visible = (pt=='30'?'yes':'hidden');
                this.sections._module_options.visible = (pt=='30'?'yes':'hidden');
                if( pt == '30' ) { 
                    this.setModuleOptions();
                }
                for(i in this.sections) {
                    var e = M.gE(this.panelUID + '_section_' + i);
                    if( e != null && this.sections[i].visible != null && this.sections[i].visible != 'no' ) {
                        if( this.sections[i].visible == 'hidden' ) {
                            e.style.display = 'none';
                        } else if( this.sections[i].visible == 'yes' ) {
                            e.style.display = 'block';
                        }
                    }
                }
                var e = M.gE(this.panelUID + '_article_title');
                if( pt == 10 || pt == 11 ) {
                    this.sections.details.fields.article_title.visible = 'yes';
                    e.parentNode.parentNode.style.display = '';
                } else {
                    this.sections.details.fields.article_title.visible = 'no';
                    e.parentNode.parentNode.style.display = 'none';
                }
            };
            this[pn].setModuleOptions = function() {
                this.sections._module_options.visible = 'hidden';
                var mod = this.formValue('page_module');
                this.sections._module_options.fields = {};
                for(var i in this.modules_pages) {
                    if( i == mod ) {
                        if( this.modules_pages[i].options != null ) {
                            for(var j in this.modules_pages[i].options) {
                                this.sections._module_options.visible = 'yes';
                                this.setModuleOptionsField(this.modules_pages[i].options[j]);
                            }
                        }
                        break;
                    }
                }
                var e = M.gE(this.panelUID + '_section__module_options');
                if( e != null && this.sections._module_options.visible == 'yes' && this.sections._module.visible == 'yes' ) {
                    e.style.display = 'block';
                    this.refreshSection('_module_options');
                } else {
                    e.style.display = 'none';
                }
            };
            this[pn].setModuleOptionsField = function(option) {
                this.sections._module_options.fields[option.setting] = {'label':option.label, 'type':option.type, 'hint':(option.hint!=null?option.hint:'')};
                if( option.type == 'toggle' ) {
                    this.sections._module_options.fields[option.setting].toggles = {};
                    for(var i in option.toggles) {
                        this.sections._module_options.fields[option.setting].toggles[option.toggles[i].value] = option.toggles[i].label;
                    }
                }
                else if( option.type == 'select' ) {
                    this.sections._module_options.fields[option.setting].options = {};
                    for(var i in option.options) {
                        this.sections._module_options.fields[option.setting].options[option.options[i].value] = option.options[i].label;
                    }
                }
                this.data[option.setting] = option.value;
            };
            this[pn].addButton('save', 'Save', 'M.ciniki_web_pages.'+pn+'.savePage();');
            this[pn].addClose('Cancel');
            this[pn].addLeftButton('website', 'Preview', 'M.ciniki_web_pages.'+pn+'.previewPage();');
            this[pn].savePage = function(preview) {
                var p = this;
                var flags = this.formValue('child_format');
                if( this.formValue('_flags_1') == 'on' ) {
                    flags |= 0x01;
                } else {
                    flags &= ~0x01;
                }
                if( this.formValue('_flags_2') == 'on' ) {
                    flags |= 0x02;
                } else {
                    flags &= ~0x02;
                }
                if( this.formValue('_flags_14') == 'on' ) {
                    flags |= 0x1000;
                } else {
                    flags &= ~0x1000;
                }
                if( this.page_id > 0 ) {
                    var c = this.serializeFormData('no');
                    if( c != null || flags != this.data.flags ) {
                        if( c == null ) { c = new FormData; }
                        if( flags != this.data.flags ) {
                            c.append('flags', flags);
                        }
                        M.api.postJSONFormData('ciniki.web.pageUpdate', 
                            {'tnid':M.curTenantID, 'page_id':this.page_id}, c, function(rsp) {
                                if( rsp.stat != 'ok' ) {
                                    M.api.err(rsp);
                                    return false;
                                }
                                if( preview != null ) {
                                    M.showWebsite(preview);
                                } else {
                                    p.close();
                                }
                            });
                    } else {
                        if( preview != null ) {
                            M.showWebsite(preview);
                        } else {
                            this.close();
                        }
                    }
                } else {
                    var c = this.serializeFormData('yes');
                    c.append('flags', flags);
                    M.api.postJSONFormData('ciniki.web.pageAdd', 
                        {'tnid':M.curTenantID}, c, function(rsp) {
                            if( rsp.stat != 'ok' ) {
                                M.api.err(rsp);
                                return false;
                            }
                            if( preview != null ) {
                                M.showWebsite(preview);
                            } else {
                                p.close();
                            }
                        });
                }
            };
            this[pn].previewPage = function() {
                this.savePage(this.data.full_permalink);
            };
            this[pn].deletePage = function() {
                var p = this;
                if( confirm('Are you sure you want to delete this page? All files and images will also be removed from this page.') ) {
                    M.api.getJSONCb('ciniki.web.pageDelete', {'tnid':M.curTenantID, 
                        'page_id':p.page_id}, function(rsp) {
                            if( rsp.stat != 'ok' ) {
                                M.api.err(rsp);
                                return false;
                            }
                            p.close();
                        });
                }
            };
        }

//      this[pn].sections.details.fields.parent_id.options = {'0':'None'};
        if( rsp.parentlist != null && rsp.parentlist.length > 0 ) {
            this[pn].sections.details.fields.parent_id.active = 'yes';
            this[pn].sections.details.fields.parent_id.options = {};
            this[pn].sections.details.fields.parent_id.options[' ' + 0] = 'None';
            for(var i in rsp.parentlist) {
                if( rsp.parentlist[i].page.id != this[pn].page_id ) {
                    this[pn].sections.details.fields.parent_id.options[' ' + rsp.parentlist[i].page.id] = rsp.parentlist[i].page.title;
                }
            }
        } else {
            this[pn].sections.details.fields.parent_id.active = 'no';
        }
        this[pn].data = rsp.page;
        this[pn].modules_pages = rsp.modules_pages;
        // Remove child_format flags
        this[pn].data.flags_1 = (rsp.page.flags&0xFFFFFF0F);
        this[pn].data.flags_2 = (rsp.page.flags&0xFFFFFF0F);
        this[pn].data.flags_14 = (rsp.page.flags&0x0000F000);
        this[pn].data.child_format = (rsp.page.flags&0x00000FF0);
        this[pn].sections.details.fields.parent_id.active = 'yes';
        if( this[pn].page_id == 0 && parent_id != null ) {
            this[pn].data.parent_id = parent_id;
            if( parent_id == 0 ) {
                this[pn].data.title = '';
            }
        }
        this[pn].sections._page_type.visible = 'hidden';
        this[pn].sections._page_type.fields.page_type.toggles = {'10':'Custom'};
        // Check if flags for page menu and page redirects
        if( (M.curTenant.modules['ciniki.web'].flags&0x0600) > 0 ) {
            this[pn].sections._page_type.visible = 'yes';
            if( (M.curTenant.modules['ciniki.web'].flags&0x0800) > 0 ) {
                this[pn].sections._page_type.fields.page_type.toggles['11'] = 'Manual';
            }
            if( (M.curTenant.modules['ciniki.web'].flags&0x0400) > 0 ) {
                this[pn].sections._page_type.fields.page_type.toggles['20'] = 'Redirect';
            }
            if( (M.curTenant.modules['ciniki.web'].flags&0x0200) > 0 ) {
                this[pn].sections._page_type.fields.page_type.toggles['30'] = 'Module';
                this[pn].sections._module.fields.page_module.options = {};
                if( rsp.modules_pages != null ) {
                    for(i in rsp.modules_pages) {
                        this[pn].sections._module.fields.page_module.options[i] = rsp.modules_pages[i].name;
                    }
                }
            }
        } else {
            this[pn].data.page_type = 10;
        }
        if( this[pn].data.parent_id == 0 ) {
            // Give them the option of how to display sub pages
            this[pn].sections._children.fields.child_format.flags = this.parentChildrenFormat;
            this[pn].sections.details.fields.menu_flags.visible = 'yes';
        } else {
            this[pn].sections._children.fields.child_format.flags = this.childFormat;
            this[pn].sections.details.fields.menu_flags.visible = 'no';
        }
        if( M.curTenant.modules['ciniki.sponsors'] != null 
            && (M.curTenant.modules['ciniki.sponsors'].flags&0x02) ) {
            this[pn].sections.sponsors.visible = 'hidden';
        } else {
            this[pn].sections.sponsors.visible = 'no';
        }

        this[pn].refresh();
        this[pn].show(cb);
        this[pn].setPageType();
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
        M.api.getJSONCb('ciniki.web.pageGet', {'tnid':M.curTenantID,
            'page_id':pid, 'parent_id':parent_id, 'images':'yes', 'files':'yes', 
                'children':'yes', 'parentlist':'yes', 'sponsors':'yes'}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                M.ciniki_web_pages.createEditPanel(cb, pid, parent_id, rsp);    
            });
    };
};
