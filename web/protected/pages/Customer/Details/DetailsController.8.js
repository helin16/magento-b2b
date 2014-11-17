/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new DetailsPageJs(), {
	_customer: {}
	/**
	 * Set some pre defined data before javascript start
	 */
	,setPreData: function(customer) {
		this._customer = customer;
		return this;
	}
	/**
	 * This function should return you the edit div for this item
	 */
	,_getItemDiv: function() {
		var tmp = {};
		tmp.me = this;

		tmp.newDiv = new Element('div', {'class': 'customer-editing-container'})
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': tmp.me._getCustomerSummaryDiv(tmp.me._item).wrap(new Element('div', {'class': 'col-sm-12'})) })
			})
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': tmp.me._getCustomerBillingSummaryDiv(tmp.me._item).wrap(new Element('div', {'class': 'col-sm-6'})) })
				.insert({'bottom': tmp.me._getCustomerShippingSummaryDiv(tmp.me._item).wrap(new Element('div', {'class': 'col-sm-6'})) })
			})
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('span', {'id': 'saveBtn', 'class': 'btn btn-primary pull-right col-sm-4', 'data-loading-text': 'saving ...'}).update('Save')
					.observe('click', function() {
						tmp.me._submitSave(this);
					})
				})
			})
		;
		return tmp.newDiv;
	}
	/**
	 * Ajax: saving the item
	 */
	,_submitSave: function(btn) {
		var tmp = {};
		tmp.me = this;
		tmp.data = tmp.me._collectFormData($(tmp.me._htmlIds.itemDiv), 'save-item');
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
	 * Binding the save key
	 */
	,_bindSaveKey: function() {
		var tmp = {}
		tmp.me = this;
		$$('.customer-editing-container').first().getElementsBySelector('[save-item]').each(function(item) {
			item.observe('keydown', function(event) {
				tmp.me.keydown(event, function() {
					$$('.customer-editing-container').first().down('#saveBtn').click();
				});
			})
		});
		return this;
	}
	,refreshParentWindow: function() {
		var tmp = {};
		tmp.me = this;
		if(!window.parent)
			return;
		tmp.parentWindow = window.parent;
		tmp.row = $(tmp.parentWindow.document.body).down('#' + tmp.parentWindow.pageJs.resultDivId + ' .item_row[item_id=' + tmp.me._item.id + ']');
		if(tmp.row) {
			tmp.row.replace(tmp.parentWindow.pageJs._getResultRow(tmp.me._item));
			if(tmp.row.hasClassName('success'))
				tmp.row.addClassName('success');
		}
	}
	/**
	 * Getting the customer summary div
	 */
	,_getCustomerSummaryDiv: function (item) {
		var tmp = {};
		tmp.me = this;
		tmp.item = item;

		tmp.newDiv = new Element('div', {'class': 'panel panel-default'})
			.insert({'bottom': new Element('div', {'class': 'panel-heading'})
				.insert({'bottom': new Element('a', {'href': 'javascript: void(0);', 'title': 'click to show/hide below'})
					.insert({'bottom': new Element('strong').update(tmp.item.name ? 'Editing: ' + tmp.item.name : 'Creating new customer: ') })
				})
				.observe('click', function() {
					$(this).up('.panel').down('.panel-body').toggle();
				})
				.insert({'bottom': new Element('small', {'class': 'pull-right'}) 
					.insert({'bottom': new Element('label', {'for': 'showOnWeb_' + tmp.item.id}).update('Show on Web?') })
					.insert({'bottom': new Element('input', {'id': 'showOnWeb_' + tmp.item.id, 'save-item': 'sellOnWeb', 'type': 'checkbox', 'checked': tmp.item.sellOnWeb}) })
				})
			})
			.insert({'bottom': new Element('div', {'class': 'panel-body'})
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('div', {'class': 'col-sm-6'}).update(tmp.me._getFormGroup('Name', new Element('input', {'required': 'required', 'save-item': 'name', 'type': 'text', 'value': tmp.item.name ? tmp.item.name : ''}) ) ) })
					.insert({'bottom': new Element('div', {'class': 'col-sm-2'}).update(tmp.me._getFormGroup('ID', new Element('input', {'disabled': 'disabled', 'save-item': 'id', 'type': 'text', 'value': tmp.item.id ? tmp.item.id : ''}) ) ) })
					.insert({'bottom': new Element('div', {'class': 'col-sm-2'}).update(tmp.me._getFormGroup('Mage ID', new Element('input', {'save-item': 'mageId', 'type': 'value', 'value': tmp.item.mageId ? tmp.item.mageId : ''}) ) ) })
					.insert({'bottom': new Element('div', {'class': 'col-sm-2'}).update(tmp.me._getFormGroup('Active?', new Element('input', {'save-item': 'active', 'type': 'checkbox', 'value': tmp.item.active}) ) ) })
				})
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('div', {'class': 'col-sm-6'}).update(tmp.me._getFormGroup('Email', new Element('input', {'save-item': 'email', 'type': 'email', 'value': tmp.item.email ? tmp.item.email : ''}) ) ) })
					.insert({'bottom': new Element('div', {'class': 'col-sm-6'}).update(tmp.me._getFormGroup('Contact No?', new Element('input', {'save-item': 'contactNo', 'type': 'value', 'value': tmp.item.contactNo ? tmp.item.contactNo : ''}) ) ) })
				})
			})
		;
		return tmp.newDiv;
	}
	/**
	 * Getting the customer billing summary div
	 */
	,_getCustomerBillingSummaryDiv: function (item) {
		var tmp = {};
		tmp.me = this;
		tmp.item = item;

		tmp.newDiv = new Element('div', {'class': 'panel panel-default'})
			.insert({'bottom': new Element('div', {'class': 'panel-heading'})
				.insert({'bottom': new Element('a', {'href': 'javascript: void(0);', 'title': 'click to show/hide below'})
					.insert({'bottom': new Element('strong').update(tmp.item.name ? 'Billing Info: ' + tmp.item.name : 'New customer: ') })
				})
			})
			.insert({'bottom': new Element('div', {'class': 'panel-body'})
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('div', {'class': 'col-sm-12'}).update(tmp.me._getFormGroup('Name', new Element('input', {'save-item': 'billingName', 'type': 'text', 'value': tmp.item.id ? tmp.item.address.billing.contactName : ''}) ) ) })
				})
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('div', {'class': 'col-sm-12'}).update(tmp.me._getFormGroup('Contact No.', new Element('input', {'save-item': 'billingContactNo', 'type': 'value', 'value': tmp.item.id ? tmp.item.address.billing.contactNo : ''}) ) ) })
				})
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('div', {'class': 'col-sm-6'}).update(tmp.me._getFormGroup('Street', new Element('input', {'save-item': 'billingStreet', 'type': 'text', 'value': tmp.item.id ? tmp.item.address.billing.street : ''}) ) ) })
					.insert({'bottom': new Element('div', {'class': 'col-sm-6'}).update(tmp.me._getFormGroup('City', new Element('input', {'save-item': 'billingCity', 'type': 'text', 'value': tmp.item.id ? tmp.item.address.billing.city : ''}) ) ) })
				})
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('div', {'class': 'col-sm-4'}).update(tmp.me._getFormGroup('State', new Element('input', {'save-item': 'billingState', 'type': 'text', 'value': tmp.item.id ? tmp.item.address.billing.region : ''}) ) ) })
					.insert({'bottom': new Element('div', {'class': 'col-sm-4'}).update(tmp.me._getFormGroup('Country', new Element('input', {'save-item': 'billingCountry', 'type': 'text', 'value': tmp.item.id ? tmp.item.address.billing.country : 'AU'}) ) ) })
					.insert({'bottom': new Element('div', {'class': 'col-sm-4'}).update(tmp.me._getFormGroup('Post Code', new Element('input', {'save-item': 'billingPosecode', 'type': 'text', 'value': tmp.item.id ? tmp.item.address.billing.postCode : ''}) ) ) })
				})
			})
		;
		return tmp.newDiv;
	}
	/**
	 * Getting the customer shipping summary div
	 */
	,_getCustomerShippingSummaryDiv: function (item) {
		var tmp = {};
		tmp.me = this;
		tmp.item = item;

		tmp.newDiv = new Element('div', {'class': 'panel panel-default'})
			.insert({'bottom': new Element('div', {'class': 'panel-heading'})
				.insert({'bottom': new Element('a', {'href': 'javascript: void(0);', 'title': 'click to show/hide below'})
					.insert({'bottom': new Element('strong').update(tmp.item.name ? 'Shipping Info: ' + tmp.item.name : 'New customer: ') })
				})
			})
			.insert({'bottom': new Element('div', {'class': 'panel-body'})
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('div', {'class': 'col-sm-12'}).update(tmp.me._getFormGroup('Name', new Element('input', {'save-item': 'shippingName', 'type': 'text', 'value': tmp.item.id ? tmp.item.address.shipping.contactName : ''}) ) ) })
				})
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('div', {'class': 'col-sm-12'}).update(tmp.me._getFormGroup('Contact No.', new Element('input', {'save-item': 'shippingContactNo', 'type': 'value', 'value': tmp.item.id ? tmp.item.address.shipping.contactNo : ''}) ) ) })
				})
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('div', {'class': 'col-sm-6'}).update(tmp.me._getFormGroup('Street', new Element('input', {'save-item': 'shippingStreet', 'type': 'text', 'value': tmp.item.id ? tmp.item.address.shipping.street : ''}) ) ) })
					.insert({'bottom': new Element('div', {'class': 'col-sm-6'}).update(tmp.me._getFormGroup('City', new Element('input', {'save-item': 'shippingCity', 'type': 'text', 'value': tmp.item.id ? tmp.item.address.shipping.city : ''}) ) ) })
				})
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('div', {'class': 'col-sm-4'}).update(tmp.me._getFormGroup('State', new Element('input', {'save-item': 'shippingState', 'type': 'text', 'value': tmp.item.id ? tmp.item.address.shipping.region : ''}) ) ) })
					.insert({'bottom': new Element('div', {'class': 'col-sm-4'}).update(tmp.me._getFormGroup('Country', new Element('input', {'save-item': 'shippingCountry', 'type': 'text', 'value': tmp.item.id ? tmp.item.address.shipping.country : 'AU'}) ) ) })
					.insert({'bottom': new Element('div', {'class': 'col-sm-4'}).update(tmp.me._getFormGroup('Post Code', new Element('input', {'save-item': 'shippingPosecode', 'type': 'text', 'value': tmp.item.id ? tmp.item.address.shipping.postCode : ''}) ) ) })
				})
			})
		;
		return tmp.newDiv;
	}
	/**
	 * Getting a form group for forms
	 */
	,_getFormGroup: function (label, input) {
		return new Element('div', {'class': 'form-group form-group-sm form-group-sm-label'})
			.insert({'bottom': new Element('label').update(label) })
			.insert({'bottom': input.addClassName('form-control') });
	}
	/**
	 * Public: binding all the js events
	 */
	,bindAllEventNObjects: function() {
		var tmp = {};
		tmp.me = this;
		return tmp.me;
	}
});