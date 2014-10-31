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
		
		console.debug(tmp.me._customer);
				
		tmp.newDiv = new Element('div')
		.insert({'bottom': new Element('div', {'class': 'row'})
			.insert({'bottom': new Element('div', {'class': 'col-sm-12'})
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': tmp.me._getSummaryDiv(tmp.me._customer).wrap(new Element('div', {'class': ''})) })
				})
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('span', {'class': 'btn btn-primary pull-right', 'data-loading-text': 'saving ...'}).update('Save')
						.observe('click', function() {
							tmp.me._submitSave(this);
						})
					})
				})
			})
		});

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
		
		tmp.data = tmp.me._collectFormData($(tmp.me._htmlIds.itemDiv).down('.customer-summary'),'save-item');
		
		//console.debug(tmp.data);
		
		//submit all data
		tmp.me.saveItem(btn, tmp.data, function(data=tmp.data){
			data.url = '/customer/' + data.id + '.html';
			console.debug(data);
			if(!data.url)
				throw 'System Error: no return product url';
			
			tmp.me._item = data;
			tmp.me.refreshParentWindow();
			//tmp.me.showModalBox('<strong class="text-success">Saved Successfully!</strong>', 'Saved Successfully!', true);
			window.location = data.url; 
			window.close();
		});
		
		
		/*
		//submit all data
		tmp.me.saveItem(btn, tmp.data, function(data){
			if(!data.url)
				throw 'System Error: no return product url';
			tmp.me._item = data.item;
			tmp.me.refreshParentWindow();
			tmp.me.showModalBox('<strong class="text-success">Saved Successfully!</strong>', 'Saved Successfully!', true);
			window.location = data.url; 
		});
		*/
		return tmp.me;
	}
	,refreshParentWindow: function() {
		var tmp = {};
		tmp.me = this;
		if(!window.opener)
			return;
		tmp.parentWindow = window.opener;
		tmp.row = $(tmp.parentWindow.document.body).down('#' + ' .item_row[item_id=' + tmp.me._item.id + ']');
		if(tmp.row) {
			tmp.row.replace(tmp.parentWindow.pageJs._getResultRow(tmp.me._item));
			if(tmp.row.hasClassName('success'))
				tmp.row.addClassName('success');
		}
	}
	/**
	 * Getting the summary div
	 */
	,_getSummaryDiv: function (item) {
		var tmp = {};
		tmp.me = this;
		tmp.item = item;

		tmp.newDiv = new Element('div', {'class': 'panel panel-default customer-summary'})
		.insert({'bottom': new Element('div', {'class': 'panel-heading'})
			.insert({'bottom': new Element('a', {'href': 'javascript: void(0);', 'title': 'click to show/hide below'})
				.insert({'bottom': new Element('strong').update(tmp.item.name ? 'Editing: ' + tmp.item.name : 'Creating new product: ') })
				.insert({'bottom': new Element('small', {'class': 'pull-right'}) 
					.insert({'bottom': new Element('label', {'for': 'showOnWeb_' + tmp.item.id}).update('Show on Web?') })
					.insert({'bottom': new Element('input', {'id': 'showOnWeb_' + tmp.item.id, 'style': 'margin-left:10px;', 'save-item': 'sellOnWeb', 'type': 'checkbox'/*, 'checked': tmp.item.sellOnWeb*/}) })
				})
			})
			.observe('click', function() {
				$(this).up('.panel').down('.panel-body').toggle();
			})
		})
		.insert({'bottom': new Element('div', {'class': 'panel-body'})
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('strong', {'class': 'col-sm-4 pull-left'}).update('Customer Info') })
			})
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('div', {'class': 'col-sm-2 item-name'}).update(tmp.me._getFormGroup('Name', new Element('input', {'save-item': 'name', 'type': 'text', 'value': tmp.item.name ? tmp.item.name : ''}) ) ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-1 item-id'}).update(tmp.me._getFormGroup('ID', new Element('input', {'save-item': 'id', 'type': 'text', 'value': tmp.item.id ? tmp.item.id : ''}) ) ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-1 item-mageId'}).update(tmp.me._getFormGroup('Mage ID', new Element('input', {'save-item': 'mageId', 'type': 'value', 'value': tmp.item.mageId ? tmp.item.mageId : ''}) ) ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-1 item-active'}).update(tmp.me._getFormGroup('Active?', new Element('input', {'save-item': 'active', 'type': 'checkbox', 'checked': tmp.item.active }) ) ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-2 item-email'}).update(tmp.me._getFormGroup('Email', new Element('input', {'save-item': 'email', 'type': 'email', 'value': tmp.item.email ? tmp.item.email : '' }) ) ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-1 item-contactNo'}).update(tmp.me._getFormGroup('Contact No', new Element('input', {'save-item': 'contactNo', 'type': 'value', 'value': tmp.item.contactNo ? tmp.item.contactNo : '' }) ) ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-2 item-createDate'}).update(tmp.me._getFormGroup('Created Date', 
							new Element('input', {'class': 'datepicker', 'save-item': 'created', 'value': (tmp.item.created ? tmp.item.created : '') })  
					) ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-2 item-updateDate'}).update(tmp.me._getFormGroup('Updated Date', 
							new Element('input', {'class': 'datepicker', 'save-item': 'updated', 'value': (tmp.item.updated ? tmp.item.updated : '') })  
					) ) })
			})
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('strong', {'class': 'col-sm-4 pull-left'}).update('Billing Info') })
			})
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('div', {'class': 'col-sm-2'}).update(tmp.me._getFormGroup('Name', new Element('input', {'save-item': 'billingName', 'type': 'text', 'value': tmp.item.address.billing.contactName ? tmp.item.address.billing.contactName : ''}) ) ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-1'}).update(tmp.me._getFormGroup('Contact No', new Element('input', {'save-item': 'billingContactNo', 'type': 'value', 'value': tmp.item.address.billing.contactNo ? tmp.item.address.billing.contactNo : ''}) ) ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-1'}).update(tmp.me._getFormGroup('Street', new Element('input', {'save-item': 'billingSteet', 'type': 'text', 'value': tmp.item.address.billing.street ? tmp.item.address.billing.street : ''}) ) ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-1'}).update(tmp.me._getFormGroup('City', new Element('input', {'save-item': 'billingCity', 'type': 'text', 'value': tmp.item.address.billing.city ? tmp.item.address.billing.city : ''}) ) ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-1'}).update(tmp.me._getFormGroup('State', new Element('input', {'save-item': 'billingState', 'type': 'text', 'value': tmp.item.address.billing.region ? tmp.item.address.billing.region : ''}) ) ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-1'}).update(tmp.me._getFormGroup('Country', new Element('input', {'save-item': 'billingCountry', 'type': 'text', 'value': tmp.item.address.billing.country ? tmp.item.address.billing.country : ''}) ) ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-1'}).update(tmp.me._getFormGroup('Post Code', new Element('input', {'save-item': 'billingPosecode', 'type': 'value', 'value': tmp.item.address.billing.postCode ? tmp.item.address.billing.postCode : ''}) ) ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-2'}).update(tmp.me._getFormGroup('Created Date', 
							new Element('input', {'class': 'datepicker', 'save-item': 'billing-created', 'value': (tmp.item.address.billing.created ? tmp.item.address.billing.created : '') })  
					) ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-2'}).update(tmp.me._getFormGroup('Updated Date', 
							new Element('input', {'class': 'datepicker', 'save-item': 'billing-updated', 'value': (tmp.item.address.billing.updated ? tmp.item.address.billing.updated : '') })  
					) ) })
			})
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('strong', {'class': 'col-sm-4 pull-left'}).update('Shipping Info') })
			})
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('div', {'class': 'col-sm-2'}).update(tmp.me._getFormGroup('Name', new Element('input', {'save-item': 'shippingName', 'type': 'text', 'value': tmp.item.address.shipping.contactName ? tmp.item.address.shipping.contactName : ''}) ) ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-1'}).update(tmp.me._getFormGroup('Contact No', new Element('input', {'save-item': 'shippingContactNo', 'type': 'value', 'value': tmp.item.address.shipping.contactNo ? tmp.item.address.shipping.contactNo : ''}) ) ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-1'}).update(tmp.me._getFormGroup('Street', new Element('input', {'save-item': 'shippingSteet', 'type': 'text', 'value': tmp.item.address.shipping.street ? tmp.item.address.shipping.street : ''}) ) ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-1'}).update(tmp.me._getFormGroup('City', new Element('input', {'save-item': 'shippingCity', 'type': 'text', 'value': tmp.item.address.shipping.city ? tmp.item.address.shipping.city : ''}) ) ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-1'}).update(tmp.me._getFormGroup('State', new Element('input', {'save-item': 'shippingState', 'type': 'text', 'value': tmp.item.address.shipping.region ? tmp.item.address.shipping.region : ''}) ) ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-1'}).update(tmp.me._getFormGroup('Country', new Element('input', {'save-item': 'shippingCountry', 'type': 'text', 'value': tmp.item.address.shipping.country ? tmp.item.address.shipping.country : ''}) ) ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-1'}).update(tmp.me._getFormGroup('Post Code', new Element('input', {'save-item': 'shippingPosecode', 'type': 'value', 'value': tmp.item.address.shipping.postCode ? tmp.item.address.shipping.postCode : ''}) ) ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-2'}).update(tmp.me._getFormGroup('Created Date', 
							new Element('input', {'class': 'datepicker', 'save-item': 'shipping-created', 'value': (tmp.item.address.billing.created ? tmp.item.address.shipping.created : '') })  
					) ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-2'}).update(tmp.me._getFormGroup('Updated Date', 
							new Element('input', {'class': 'datepicker', 'save-item': 'shipping-updated', 'value': (tmp.item.address.billing.updated ? tmp.item.address.shipping.updated : '') })  
					) ) })
			})
		});
		
		
		
		
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
//		tmp.me._bindDatePicker();
//		$$('textarea.rich-text-editor').each(function(item){
//			tmp.me._loadRichTextEditor(item);
//		});
		return tmp.me;
	}
	,refreshParentWindow: function() {
		var tmp = {};
		tmp.me = this;
		if(!window.opener)
			return;
		tmp.parentWindow = window.opener;
		tmp.row = $(tmp.parentWindow.document.body).down('#' + ' .item_row[item_id=' + tmp.me._item.id + ']');
		if(tmp.row) {
			tmp.row.replace(tmp.parentWindow.pageJs._getResultRow(tmp.me._item));
			if(tmp.row.hasClassName('success'))
				tmp.row.addClassName('success');
		}
	}
});