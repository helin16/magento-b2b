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
		console.debug(this._purchaseorder);
		return this;
	}
	/**
	 * setting the status options
	 */
	,setStatusOptions: function(statusOptions) {
		this._statusOptions = statusOptions;
		return this;
	}
	/**
	 * setting the Purchase Order Items
	 */
	,setPurchaseOrderItems: function(purchaseOrderItems) {
		this._purchaseOrderItems = purchaseOrderItems;
		console.debug(purchaseOrderItems);
		return this;
	}
	/**
	 * This function should return you the edit div for this item
	 */
	,_getItemDiv: function() {
		var tmp = {};
		tmp.me = this;
		
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
		
		
		//submit all data
		tmp.me.saveItem(btn, tmp.data, function(data){
			tmp.me.showModalBox('<strong class="text-success">Saved Successfully!</strong>', 'Saved Successfully!', true);
			tmp.me._item = data.item;
			tmp.me.refreshParentWindow();
			window.parent.jQuery.fancybox.close();
		});
		
		return tmp.me;
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
	 * Getting the summary div
	 */
	,_getSummaryDiv: function (item) {
		var tmp = {};
		tmp.me = this;
		tmp.item = item;
		tmp.statusOptionSel = new Element('select', {'class': '', 'save-item': 'status'})
			.insert({'bottom': new Element('option', {'value': tmp.item.status}).update(tmp.item.status) });
		tmp.me._statusOptions.each(function(option){
			if(option!==tmp.item.status)
				tmp.statusOptionSel.insert({'bottom': new Element('option', {'value': option}).update(option) });
		})
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
				.insert({'bottom': new Element('div', {'class': 'col-sm-1 purchaseOrderNo'}).update(tmp.me._getFormGroup('PO Number', new Element('input', {'disabled': 'disabled', 'type': 'value', 'value': tmp.item.purchaseOrderNo ? tmp.item.purchaseOrderNo : ''}) ) ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-1 purchaseRefNo'}).update(tmp.me._getFormGroup('PO RefNumber', new Element('input', {'save-item': 'purchaseRefNo', 'type': 'value', 'value': tmp.item.supplierRefNo ? tmp.item.supplierRefNo : ''}) ) ) })
				
				.insert({'bottom': new Element('div', {'class': 'col-sm-1 status'}).update(tmp.me._getFormGroup('Status', tmp.statusOptionSel ) ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-1 active'}).update(tmp.me._getFormGroup('Active?', new Element('input', {'save-item': 'active', 'type': 'checkbox', 'checked': tmp.item.active }) ) ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-2 orderDate'}).update(tmp.me._getFormGroup('Ordered Date', 
							new Element('input', {'class': 'datepicker', 'save-item': 'orderDate', 'value': (tmp.item.orderDate ? tmp.item.orderDate : '') })  
					) ) })
			})
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('strong', {'class': 'col-sm-4 pull-left'}).update('Supplier Info') })
			})
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('div', {'class': 'col-sm-1 supplierName'}).update(tmp.me._getFormGroup('Name', new Element('input', {'save-item': 'supplierName', 'type': 'text', 'value': tmp.item.supplier.name ? tmp.item.supplier.name : ''}) ) ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-1 supplierId'}).update(tmp.me._getFormGroup('ID', new Element('input', {'save-item': 'supplierId', 'type': 'text', 'value': tmp.item.supplier.id ? tmp.item.supplier.id : ''}) ) ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-1 supplierMageId'}).update(tmp.me._getFormGroup('Mage ID', new Element('input', {'save-item': 'supplierMageId', 'type': 'value', 'value': tmp.item.supplier.mageId ? tmp.item.supplier.mageId : ''}) ) ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-1 supplierActive'}).update(tmp.me._getFormGroup('Active?', new Element('input', {'save-item': 'supplierActive', 'type': 'checkbox', 'checked': tmp.item.supplier.active }) ) ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-1 supplierContactName'}).update(tmp.me._getFormGroup('contactName', new Element('input', {'save-item': 'supplierContactName', 'type': 'text', 'value': tmp.item.supplierContact ? tmp.item.supplierContact : '' }) ) ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-1 supplierContactNo'}).update(tmp.me._getFormGroup('Contact No', new Element('input', {'save-item': 'supplierContactNo', 'type': 'value', 'value': tmp.item.supplierContactNumber ? tmp.item.supplierContactNumber : '' }) ) ) })
			})
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('strong', {'class': 'col-sm-4 pull-left'}).update('Finance Info') })
			})
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('div', {'class': 'col-sm-1 totalAmount'}).update(tmp.me._getFormGroup('Total Amount', new Element('input', {'save-item': 'totalAmount', 'type': 'value', 'value': tmp.item.totalAmount ? tmp.item.totalAmount : ''}) ) ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-1 totalPaid'}).update(tmp.me._getFormGroup('Total Paid', new Element('input', {'style': (tmp.item.totalAmount-tmp.item.totalPaid)?'color: red':'', 'save-item': 'totalPaid', 'type': 'value', 'value': tmp.item.totalPaid ? tmp.item.totalPaid : ''}) ) ) })
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
});