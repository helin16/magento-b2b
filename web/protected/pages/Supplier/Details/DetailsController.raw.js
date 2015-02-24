/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new DetailsPageJs(), {
	_item: {}
	/**
	 * Set some pre defined data before javascript start
	 */
	,setPreData: function(item) {
		this._item = item;
		return this;
	}
	/**
	 * Ajax: saving the item
	 */
	,_submitSave: function(btn) {
		var tmp = {};
		tmp.me = this;
		tmp.data = tmp.me._collectFormData($(tmp.me._htmlIds.itemDiv), 'save-item');
		tmp.data.id = tmp.me._customer.id ? tmp.me._customer.id : '';
		if(tmp.data === null)
			return tmp.me;

		//submit all data
		tmp.me.saveItem(btn, tmp.data, function(data){
			tmp.me.showModalBox('<strong class="text-success">Saved Successfully!</strong>', 'Saved Successfully!', true);
			tmp.me._item = data.item;
			tmp.me.refreshParentWindow();
			window.parent.jQuery.fancybox.close();
		});
		return tmp.me;
	}
	/**
	 * Getting address div
	 */
	,_getAddressDiv: function (addr, title) {
		var tmp = {};
		tmp.me = this;
		tmp.addr = (addr || {});
		return new Element('div', {'class': 'address-div panel panel-default', 'address-editable': true})
			.insert({'bottom': new Element('div', {'class': 'panel-heading'}).update(title) })
			.insert({'bottom': new Element('div', {'class': 'panel-body'})
				.insert({'bottom': new Element('dl', {'class': 'dl-horizontal dl-condensed'})
					.insert({'bottom': new Element('dt')
						.update(new Element('span', {'class': "glyphicon glyphicon-user", 'title': " Name"}) )
					})
					.insert({'bottom': new Element('dd')
						.insert({'bottom': new Element('div')
							.insert({'bottom': new Element('div', {'class' : 'col-sm-6'}).update(
								new Element('input', {'address-editable-field': 'contactName', 'class': 'form-control input-sm', 'placeholder': 'The name of contact person',  'value': tmp.addr.contactName ? tmp.addr.contactName : ''})
							) })
							.insert({'bottom': new Element('div', {'class' : 'col-sm-6'}).update(
									new Element('input', {'address-editable-field': 'contactNo', 'class': 'form-control input-sm', 'placeholder': 'The contact number of contact person',  'value': tmp.addr.contactNo ? tmp.addr.contactNo : ''})
							) })
						})
					})
					.insert({'bottom': new Element('dt').update(
						new Element('span', {'class': "glyphicon glyphicon-map-marker", 'title': "Address"})
					) })
					.insert({'bottom': new Element('dd')
						.insert({'bottom': new Element('div')
							.insert({'bottom': new Element('div', {'class': 'street col-sm-12'}).update(
									new Element('input', {'address-editable-field': 'street', 'class': 'form-control input-sm', 'placeholder': 'Street Number and Street name',  'value': tmp.addr.street ? tmp.addr.street : ''})
							) })
						})
						.insert({'bottom': new Element('div')
							.insert({'bottom': new Element('div', {'class': 'city col-sm-6'}).update(
									new Element('input', {'address-editable-field': 'city', 'class': 'form-control input-sm', 'placeholder': 'City / Suburb',  'value': tmp.addr.city ? tmp.addr.city : ''})
							) })
							.insert({'bottom':  new Element('div', {'class': 'region col-sm-3'}).update(
									new Element('input', {'address-editable-field': 'region', 'class': 'form-control input-sm', 'placeholder': 'State / Province',  'value': tmp.addr.region ? tmp.addr.region : ''})
							) })
							.insert({'bottom': new Element('div', {'class': 'postcode col-sm-3'}).update(
									new Element('input', {'address-editable-field': 'postCode', 'class': 'form-control input-sm', 'placeholder': 'PostCode',  'value': tmp.addr.postCode ? tmp.addr.postCode : ''})
							) })
						})
						.insert({'bottom': new Element('div')
							.insert({'bottom': new Element('div', {'class': 'postcode col-sm-4'}).update(
									new Element('input', {'address-editable-field': 'country', 'class': 'form-control input-sm', 'placeholder': 'Country',  'value': tmp.addr.country ? tmp.addr.country : ''})
							) })
						})
					})
				})
			});
	}
	/**
	 * This function should return you the edit div for this item
	 */
	,_getItemDiv: function() {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = new Element('div', {'class': 'item-editing-container'})
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': tmp.me._getFormGroup('Name:', new Element('input', {'edit-field': 'name', 'name': 'name', 'value': tmp.me._item.name}) ).wrap(new Element('div', {'class': 'col-sm-4'})) })
				.insert({'bottom': tmp.me._getFormGroup('Description:', new Element('input', {'edit-field': 'description', 'value': tmp.me._item.description}) ).wrap(new Element('div', {'class': 'col-sm-8'})) })
			})
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': tmp.me._getFormGroup('Contact Name:', new Element('input', {'edit-field': 'contactName', 'value': tmp.me._item.contactName}) ).wrap(new Element('div', {'class': 'col-sm-4'})) })
				.insert({'bottom': tmp.me._getFormGroup('contact Number:', new Element('input', {'edit-field': 'contactNo', 'value': tmp.me._item.contactNo}) ).wrap(new Element('div', {'class': 'col-sm-4'})) })
				.insert({'bottom': tmp.me._getFormGroup('Email:', new Element('input', {'edit-field': 'email', 'name': 'email', 'value': tmp.me._item.email}) ).wrap(new Element('div', {'class': 'col-sm-4'})) })
			})
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': tmp.me._getAddressDiv(tmp.me._item.address, 'Address').wrap(new Element('div', {'class': 'col-sm-12'}) ) })
			})
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('div', {'class': 'form-group'})
					.insert({'bottom': new Element('input', {'edit-field': 'id', 'type': 'hidden', 'value': tmp.me._item.id ? tmp.me._item.id : ''})  })
					.insert({'bottom': new Element('button', {'id': 'save-btn', 'type': 'submit', 'class': 'btn btn-primary pull-right col-sm-3', 'data-loading-text': "saving ..."})
						.update('SAVE')
					})
				})
			})
		;
		return tmp.newDiv;
	}
	,refreshParentWindow: function(item) {
		var tmp = {};
		tmp.me = this;
		if(!window.parent)
			return;
		tmp.parentWindow = window.parent;
		tmp.resultDiv = $(tmp.parentWindow.document.body).down('#' + tmp.parentWindow.pageJs.resultDivId);
		tmp.parentResultTbody = tmp.resultDiv ? tmp.resultDiv.down('tbody') : undefined;
		if(tmp.parentResultTbody) {
			tmp.row =tmp.parentResultTbody.down('.item_row[item_id=' +item.id + ']');
			if(tmp.row) {
				tmp.row.replace(tmp.parentWindow.pageJs._getResultRow(item));
			} else {
				tmp.parentResultTbody.insert({'top': tmp.parentWindow.pageJs._getResultRow(item)});
			}
		}
	}
	/**
	 * Getting a form group for forms
	 */
	,_getFormGroup: function (label, input) {
		return new Element('div', {'class': 'form-group form-group-sm form-group-sm-label'})
			.insert({'bottom': new Element('label', {'class': 'control-label'}).update(label) })
			.insert({'bottom': input.addClassName('form-control') });
	}
	,_init: function(){
		var tmp = {};
		tmp.me = this;
		jQuery(window).ready(function (){
			tmp.mainForm = jQuery('#' + tmp.me.getHTMLID('main-form'));
			tmp.mainForm.formValidation({
		        // I am validating Bootstrap form
		        framework: 'bootstrap',
		        icon: {
		            valid: 'glyphicon glyphicon-ok',
		            invalid: 'glyphicon glyphicon-remove',
		            validating: 'glyphicon glyphicon-refresh'
		        },
		        fields: {
		            name: {
		                validators: {
		                    notEmpty: {
		                        message: 'The name of the supplier is needed.'
		                    },
		                }
		            },
		            email: {
		                validators: {
		                	emailAddress: {
		                        message: 'please provide a valid email address'
		                    }
		                }
		            }
		        }
			})
			.on('err.form.fv', function(e) {
	            if (tmp.mainForm.data('formValidation').getSubmitButton()) {
	            	tmp.mainForm.data('formValidation').disableSubmitButtons(false);
	            }
	        })
	        .on('success.form.fv', function(e) {
	        	e.preventDefault();
	        	if (tmp.mainForm.data('formValidation').getSubmitButton()) {
	        		tmp.mainForm.data('formValidation').disableSubmitButtons(false);
	        	}
	        	tmp.data = {};
	        	$(tmp.me._htmlIds.itemDiv).getElementsBySelector('[edit-field]').each(function(item) {
	        		tmp.data[item.readAttribute('edit-field')] = $F(item);
	        	});
	        	tmp.data.address = {};
	        	$(tmp.me._htmlIds.itemDiv).getElementsBySelector('[address-editable-field]').each(function(item) {
	        		tmp.data.address[item.readAttribute('address-editable-field')] = $F(item);
	        	});
	        	tmp.me.saveItem($('save-btn'), tmp.data, function(result){
	        		tmp.me.showModalBox('<strong class="text-success">Success</strong>', 'Saved Successfully');
	        		tmp.me._item = result.item;
	        		tmp.me.refreshParentWindow(result.item);
	        		window.location = result.url;
	        		window.parent.jQuery.fancybox.close();
	        	});
	        });
		});
		return tmp.me;
	}
});