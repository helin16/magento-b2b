/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new DetailsPageJs(), {
	_purchaseorder: {}
	/**
	 * Set some pre defined data before javascript start
	 */
	,setPreData: function(purchaseorder) {
		this._purchaseorder = purchaseorder;
		console.debug(this);
		return this;
	}
	/**
	 * This function should return you the edit div for this item
	 */
	,_getItemDiv: function() {
		var tmp = {};
		tmp.me = this;
		
		console.debug(tmp.me._purchaseorder);
		tmp.newDiv = new Element('div')
		.insert({'bottom': new Element('div', {'class': 'row'})
			.insert({'bottom': new Element('div', {'class': 'col-sm-12'})
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': tmp.me._getSummaryDiv(tmp.me._purchaseorder).wrap(new Element('div', {'class': ''})) })
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
		tmp.me.saveItem(btn, tmp.data, function(data){
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
		console.debug(tmp.item);
		tmp.newDiv = new Element('div', {'class': 'panel panel-default purchaseorder-summary'})
		.insert({'bottom': new Element('div', {'class': 'panel-heading'})
			.insert({'bottom': new Element('a', {'href': 'javascript: void(0);', 'title': 'click to show/hide below'})
				.insert({'bottom': new Element('strong').update(tmp.item.supplier.name ? 'Editing: ' + tmp.item.supplier.name + ' - ' + tmp.item.id : 'Creating new purchase order: ') })
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
				.insert({'bottom': new Element('strong', {'class': 'col-sm-4 pull-left'}).update('Purchase Order Info') })
			})
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('div', {'class': 'col-sm-1 po-id'}).update(tmp.me._getFormGroup('ID', new Element('input', {'save-item': 'po-id', 'type': 'text', 'value': tmp.item.id ? tmp.item.id : ''}) ) ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-1 po-purchaseOrderNo'}).update(tmp.me._getFormGroup('Mage ID', new Element('input', {'save-item': 'po-purchaseOrderNo', 'type': 'value', 'value': tmp.item.purchaseOrderNo ? tmp.item.purchaseOrderNo : ''}) ) ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-1 po-active'}).update(tmp.me._getFormGroup('Active?', new Element('input', {'save-item': 'po-active', 'type': 'checkbox', 'checked': tmp.item.active }) ) ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-2 po-orderDate'}).update(tmp.me._getFormGroup('Ordered Date', 
							new Element('input', {'class': 'datepicker', 'save-item': 'po-orderDate', 'value': (tmp.item.orderDate ? tmp.item.orderDate : '') })  
					) ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-2 po-createDate'}).update(tmp.me._getFormGroup('Created Date', 
							new Element('input', {'class': 'datepicker', 'save-item': 'po-created', 'value': (tmp.item.created ? tmp.item.created : '') })  
					) ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-2 po-updateDate'}).update(tmp.me._getFormGroup('Updated Date', 
							new Element('input', {'class': 'datepicker', 'save-item': 'po-updated', 'value': (tmp.item.updated ? tmp.item.updated : '') })  
					) ) })
			})
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('strong', {'class': 'col-sm-4 pull-left'}).update('Supplier Info') })
			})
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('div', {'class': 'col-sm-1 supplier-name'}).update(tmp.me._getFormGroup('Name', new Element('input', {'save-item': 'supplier-name', 'type': 'text', 'value': tmp.item.supplier.name ? tmp.item.supplier.name : ''}) ) ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-1 supplier-id'}).update(tmp.me._getFormGroup('ID', new Element('input', {'save-item': 'supplier-id', 'type': 'text', 'value': tmp.item.supplier.id ? tmp.item.supplier.id : ''}) ) ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-1 supplier-mageId'}).update(tmp.me._getFormGroup('Mage ID', new Element('input', {'save-item': 'supplier-mageId', 'type': 'value', 'value': tmp.item.supplier.mageId ? tmp.item.supplier.mageId : ''}) ) ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-1 supplier-active'}).update(tmp.me._getFormGroup('Active?', new Element('input', {'save-item': 'supplier-active', 'type': 'checkbox', 'checked': tmp.item.supplier.active }) ) ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-1 supplier-contactName'}).update(tmp.me._getFormGroup('contactName', new Element('input', {'save-item': 'supplier-contactName', 'type': 'text', 'value': tmp.item.supplier.contactName ? tmp.item.supplier.contactName : '' }) ) ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-1 supplier-contactNo'}).update(tmp.me._getFormGroup('Contact No', new Element('input', {'save-item': 'supplier-contactNo', 'type': 'value', 'value': tmp.item.supplier.contactNo ? tmp.item.supplier.contactNo : '' }) ) ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-2 supplier-createDate'}).update(tmp.me._getFormGroup('Created Date', 
							new Element('input', {'class': 'datepicker', 'save-item': 'supplier-created', 'value': (tmp.item.supplier.created ? tmp.item.supplier.created : '') })  
					) ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-2 supplier-updateDate'}).update(tmp.me._getFormGroup('Updated Date', 
							new Element('input', {'class': 'datepicker', 'save-item': 'supplier-updated', 'value': (tmp.item.supplier.updated ? tmp.item.supplier.updated : '') })  
					) ) })
			})
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('strong', {'class': 'col-sm-4 pull-left'}).update('Finance Info') })
			})
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('div', {'class': 'col-sm-1 po-totalAmount'}).update(tmp.me._getFormGroup('Total Amount', new Element('input', {'save-item': 'po-totalAmount', 'type': 'value', 'value': tmp.item.totalAmount ? tmp.item.totalAmount : ''}) ) ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-1 po-totalPaid'}).update(tmp.me._getFormGroup('Total Paid', new Element('input', {'style': (tmp.item.totalAmount-tmp.item.totalPaid)?'color: red':'', 'save-item': 'po-totalPaid', 'type': 'value', 'value': tmp.item.totalPaid ? tmp.item.totalPaid : ''}) ) ) })
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