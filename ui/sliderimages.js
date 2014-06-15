//
// The app to add/edit web slider images
//
function ciniki_web_sliderimages() {
	this.webFlags = {
		'1':{'name':'Hidden'},
		};
	this.init = function() {
		//
		// The panel to display the edit form
		//
		this.edit = new M.panel('Edit Image',
			'ciniki_web_sliderimages', 'edit',
			'mc', 'medium', 'sectioned', 'ciniki.web.images.edit');
		this.edit.default_data = {};
		this.edit.data = {};
		this.edit.slider_id = 0;
		this.edit.sections = {
			'_image':{'label':'Photo', 'fields':{
				'image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 'controls':'all', 'history':'no'},
			}},
			'info':{'label':'Information', 'type':'simpleform', 'fields':{
				'sequence':{'label':'Sequence', 'type':'text', 'size':'small'},
//				'caption':{'label':'Sequence', 'type':'text', 'size':'small'},
				'url':{'label':'URL', 'type':'text'},
			}},
			'_save':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_web_sliderimages.saveImage();'},
				'delete':{'label':'Delete', 'fn':'M.ciniki_web_sliderimages.deleteImage();'},
			}},
		};
		this.edit.fieldValue = function(s, i, d) { 
			if( this.data[i] != null ) {
				return this.data[i]; 
			} 
			return ''; 
		};
		this.edit.fieldHistoryArgs = function(s, i) {
			return {'method':'ciniki.web.sliderImageHistory', 'args':{'business_id':M.curBusinessID, 
				'slider_image_id':this.slider_image_id, 'field':i}};
		};
		this.edit.addDropImage = function(iid) {
			M.ciniki_web_sliderimages.edit.setFieldValue('image_id', iid, null, null);
			return true;
		};
		this.edit.addButton('save', 'Save', 'M.ciniki_web_sliderimages.saveImage();');
		this.edit.addClose('Cancel');
	};

	this.start = function(cb, appPrefix, aG) {
		args = {};
		if( aG != null ) {
			args = eval(aG);
		}

		//
		// Create container
		//
		var appContainer = M.createContainer(appPrefix, 'ciniki_web_sliderimages', 'yes');
		if( appContainer == null ) {
			alert('App Error');
			return false;
		}

		if( args.add != null && args.add == 'yes' ) {
			this.showEdit(cb, 0, args.slider_id);
		} else if( args.slider_image_id != null && args.slider_image_id > 0 ) {
			this.showEdit(cb, args.slider_image_id);
		}
		return false;
	}

	this.showEdit = function(cb, iid, eid) {
		if( iid != null ) {
			this.edit.slider_image_id = iid;
		}
		if( eid != null ) {
			this.edit.slider_id = eid;
		}
		if( this.edit.slider_image_id > 0 ) {
			var rsp = M.api.getJSONCb('ciniki.web.sliderImageGet', 
				{'business_id':M.curBusinessID, 'slider_image_id':this.edit.slider_image_id}, function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					}
					M.ciniki_web_sliderimages.edit.data = rsp.image;
					M.ciniki_web_sliderimages.edit.refresh();
					M.ciniki_web_sliderimages.edit.show(cb);
				});
		} else {
			this.edit.reset();
			this.edit.data = {};
			this.edit.refresh();
			this.edit.show(cb);
		}
	};

	this.saveImage = function() {
		if( this.edit.slider_image_id > 0 ) {
			var c = this.edit.serializeFormData('no');
			if( c != '' ) {
				var rsp = M.api.postJSONFormData('ciniki.web.sliderImageUpdate', 
					{'business_id':M.curBusinessID, 
					'slider_image_id':this.edit.slider_image_id}, c,
						function(rsp) {
							if( rsp.stat != 'ok' ) {
								M.api.err(rsp);
								return false;
							} else {
								M.ciniki_web_sliderimages.edit.close();
							}
						});
			} else {
				this.edit.close();
			}
		} else {
			var c = this.edit.serializeFormData('yes');
			var rsp = M.api.postJSONFormData('ciniki.web.sliderImageAdd', 
				{'business_id':M.curBusinessID, 'slider_id':this.edit.slider_id}, c,
					function(rsp) {
						if( rsp.stat != 'ok' ) {
							M.api.err(rsp);
							return false;
						} else {
							M.ciniki_web_sliderimages.edit.close();
						}
					});
		}
	};

	this.deleteImage = function() {
		if( confirm('Are you sure you want to delete this image?') ) {
			var rsp = M.api.getJSONCb('ciniki.web.sliderImageDelete', {'business_id':M.curBusinessID, 
				'slider_image_id':this.edit.slider_image_id}, function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					}
					M.ciniki_web_sliderimages.edit.close();
				});
		}
	};
}
