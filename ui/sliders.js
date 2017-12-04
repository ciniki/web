//
// The app to manage web options for a tenant
//
function ciniki_web_sliders() {
    this.sizeOptions = {
        'tiny':'Tiny',
        'small':'Small',
        'medium':'Medium',
        'large':'Large',
        'xlarge':'X-Large',
        'xxlarge':'XX-Large',
        };
    this.speedOptions = {
        'xslow':'X-Slow',
        'slow':'Slow',
        'medium':'Medium',
        'fast':'Fast',
        'xfast':'X-Fast',
        };
    this.resizeOptions = {
        'cropped':'Cropped',
        'scaled':'Scaled',
        };
    this.effectOptions = {
        'slide':'Slide',
        };
    this.init = function() {
        //
        // Global functions for history and field value
        //
        this.fieldHistoryArgs = function(s, i) {
            return {'method':'ciniki.web.pageSettingsHistory','args':{'tnid':M.curTenantID, 'field':i}};
        }
        this.fieldValue = function(s, i, d) { 
            if( this.data[i] == null ) { return ''; }
            return this.data[i]; 
        };

        //
        // The options and information for the slider page
        //
        this.main = new M.panel('Sliders',
            'ciniki_web_sliders', 'main',
            'mc', 'medium', 'sectioned', 'ciniki.web.sliders.main');
        this.main.data = {};
        this.main.sections = {
            'sliders':{'label':'Sliders', 'type':'simplegrid', 'num_cols':1,
                'headerValues':null,
                'addTxt':'Add Slider',
                'addFn':'M.ciniki_web_sliders.editSlider(\'M.ciniki_web_sliders.showMain();\',0);',
                },
            };
        this.main.cellValue = function(s, i, j, d) {
            return d.slider.name;
        };
        this.main.rowFn = function(s, i, d) {
            return 'M.ciniki_web_sliders.editSlider(\'M.ciniki_web_sliders.showMain();\',' + d.slider.id + ');';
        };
        this.main.sectionData = function(s) {
            return this.data[s];
        };
        this.main.fieldHistoryArgs = this.fieldHistoryArgs;
        this.main.addButton('save', 'Save', 'M.ciniki_web_sliders.saveSlider();');
        this.main.addClose('Back');

        //
        // the edit panel
        //
        this.edit = new M.panel('Slider',
            'ciniki_web_sliders', 'edit',
            'mc', 'medium', 'sectioned', 'ciniki.web.sliders.edit');
        this.edit.slider_id = 0;
        this.edit.data = {};
        this.edit.sections = {
            'info':{'label':'', 'fields':{
                'name':{'label':'Name', 'type':'text'},
                'size':{'label':'Size', 'type':'select', 'options':this.sizeOptions},
                'speed':{'label':'Speed', 'type':'select', 'options':this.speedOptions},
                'resize':{'label':'Format', 'type':'select', 'options':this.resizeOptions},
                }},
            'images':{'label':'Images', 'type':'simplethumbs'},
            '_images':{'label':'', 'type':'simplegrid', 'num_cols':1,
                'addTxt':'Add Image',
                'addFn':'M.ciniki_web_sliders.editImage();',
//              'addFn':'M.startApp(\'ciniki.web.sliderimages\',null,\'M.ciniki_web_sliders.edit.addDropImageRefresh();\',\'mc\',{\'slider_id\':M.ciniki_web_sliders.edit.slider_id,\'add\':\'yes\'});',
                },
            '_modules':{'label':'Modules', 'active':function() {return (M.userPerms&0x01) == 1 ? 'yes' : 'no';}, 'fields':{
                'modules':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'small'},
                }},
            '_buttons':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_web_sliders.saveSlider();'},
                'delete':{'label':'Delete', 'fn':'M.ciniki_web_sliders.deleteSlider();'},
                }},
        };
        this.edit.fieldHistoryArgs = function(s, i) {
            return {'method':'ciniki.web.sliderHistory', 'args':{'tnid':M.curTenantID, 
                'slider_id':this.slider_id, 'field':i}};
        }
        this.edit.fieldValue = this.fieldValue;
        this.edit.sectionData = function(s) {
            return this.data[s];
        };
        this.edit.addDropImage = function(iid) {
            if( M.ciniki_web_sliders.edit.slider_id > 0 ) {
                var rsp = M.api.getJSON('ciniki.web.sliderImageAdd', 
                    {'tnid':M.curTenantID, 'image_id':iid, 
                    'slider_id':M.ciniki_web_sliders.edit.slider_id});
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                return true;
            } else {
                M.ciniki_web_sliders.edit.additional_images.push(iid);
                return true;
            }
        };
        this.edit.addDropImageRefresh = function() {
            if( M.ciniki_web_sliders.edit.slider_id > 0 ) {
                M.api.getJSONCb('ciniki.web.sliderGet', {'tnid':M.curTenantID, 
                    'slider_id':M.ciniki_web_sliders.edit.slider_id, 'images':'yes'}, function(rsp) {
                        if( rsp.stat != 'ok' ) {
                            M.api.err(rsp);
                            return false;
                        }
                        var p = M.ciniki_web_sliders.edit;
                        p.data.images = rsp.slider.images;
                        p.refreshSection('images');
                        p.show();
                    });
            } else if( M.ciniki_web_sliders.edit.additional_images.length > 0 ) {
                M.api.getJSONCb('ciniki.web.sliderImages', {'tnid':M.curTenantID, 
                    'images':M.ciniki_web_sliders.edit.additional_images.join(',')}, function(rsp) {
                        if( rsp.stat != 'ok' ) {
                            M.api.err(rsp);
                            return false;
                        }
                        var p = M.ciniki_web_sliders.edit;
                        p.data.images = rsp.images;
                        p.refreshSection('images');
                        p.show();
                    });
                
            } else {
                var p = M.ciniki_web_sliders.edit;
                p.refresh();
                p.show();
            }
            return true;
        };
        this.edit.thumbFn = function(s, i, d) {
            return 'M.ciniki_web_sliders.editImage(\''+ d.image.id + '\',\'' + d.image.image_id + '\');';
        };
        this.edit.addButton('save', 'Save', 'M.ciniki_web_sliders.saveSlider();');
        this.edit.addClose('Cancel');
    }

    this.start = function(cb, ap, aG) {
        args = {};
        if( aG != null ) { args = eval(aG); }

        //
        // Create the app container if it doesn't exist, and clear it out
        // if it does exist.
        //
        var appContainer = M.createContainer(ap, 'ciniki_web_sliders', 'yes');
        if( appContainer == null ) {
            alert('App Error');
            return false;
        } 

        this.showMain(cb);
    }

    this.showMain = function(cb) {
        this.main.reset();

        M.api.getJSONCb('ciniki.web.sliderList', {'tnid':M.curTenantID}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.ciniki_web_sliders.main;
            p.data = {'sliders':rsp.sliders};
            p.refresh();
            p.show(cb);
        });
    }

    this.editSlider = function(cb, sid) {
        if( sid != null ) { this.edit.slider_id = sid; }
        if( this.edit.slider_id > 0 ) {
            this.edit.sections._buttons.buttons.delete.visible = 'yes';
            M.api.getJSONCb('ciniki.web.sliderGet', {'tnid':M.curTenantID, 
                'slider_id':this.edit.slider_id}, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    var p = M.ciniki_web_sliders.edit;
                    p.data = rsp.slider;
                    p.refresh();
                    p.show(cb);
                });
        } else {
            this.edit.slider_id = 0;
            this.edit.sections._buttons.buttons.delete.visible = 'no';
            this.edit.reset();
            this.edit.data = {'size':'medium'};
            this.edit.additional_images = [];
            this.edit.refresh();
            this.edit.show(cb);
        }
    };

    this.editImage = function(iid, img_id) {
        if( this.edit.slider_id > 0 ) {
            M.startApp('ciniki.web.sliderimages',null,'M.ciniki_web_sliders.edit.addDropImageRefresh();','mc',{'slider_id':this.edit.slider_id,'slider_image_id':iid});
        } else {
            var name = this.edit.formValue('name');
            if( name == '' ) {
                alert('You must enter the name of the slider first');
                return false;
            }
            // Save the slider
            var c = this.edit.serializeForm('yes');
            if( this.edit.additional_images.length > 0 ) {
                c += '&images=' + this.edit.additional_images.join(',');
            }
            M.api.postJSONCb('ciniki.web.sliderAdd', 
                {'tnid':M.curTenantID}, c, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    M.ciniki_web_sliders.edit.slider_id = rsp.id;
                    if( rsp.images != null ) {
                        for(i in rsp.images) {
                            if( rsp.images[i].image.image_id == img_id ) {
                                iid = rsp.images[i].image.id;
                            }
                        }
                    }

                    M.startApp('ciniki.web.sliderimages',null,'M.ciniki_web_sliders.editSlider();','mc',{'slider_id':rsp.id,'slider_image_id':iid});
                });
        }
    };

    this.saveSlider = function() {
        if( this.edit.slider_id > 0 ) {
            var c = this.edit.serializeForm('no');
            if( c != '' ) {
                M.api.postJSONCb('ciniki.web.sliderUpdate', 
                    {'tnid':M.curTenantID, 'slider_id':this.edit.slider_id}, c, function(rsp) {
                        if( rsp.stat != 'ok' ) {
                            M.api.err(rsp);
                            return false;
                        } 
                        M.ciniki_web_sliders.edit.close();
                    });
            } else {
                this.edit.close();
            }
        } else {
            var name = this.edit.formValue('name');
            if( name == '' ) {
                alert('You must enter the name of the slider first');
                return false;
            }
            var c = this.edit.serializeForm('yes');
            if( this.edit.additional_images.length > 0 ) {
                c += '&images=' + this.edit.additional_images.join(',');
            }
            M.api.postJSONCb('ciniki.web.sliderAdd', 
                {'tnid':M.curTenantID}, c, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    } 
                    M.ciniki_web_sliders.edit.close();
                });
        }
    };

    this.deleteSlider = function() {
        if( confirm('Are you sure you want to this slider?') ) {
            var rsp = M.api.getJSONCb('ciniki.web.sliderDelete', {'tnid':M.curTenantID, 
                'slider_id':this.edit.slider_id}, 
                function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    M.ciniki_web_sliders.edit.close();
                });
        }
    };
};
