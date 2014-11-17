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
//		console.debug($(tmp.parentWindow.document.body));return;
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
				.insert({'bottom': new Element('div', {'class': 'col-sm-1 id'}).update(tmp.me._getFormGroup('ID', new Element('input', {'save-item': 'id', 'type': 'text', 'value': tmp.item.id ? tmp.item.id : ''}) ) ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-1 purchaseOrderNo'}).update(tmp.me._getFormGroup('Mage ID', new Element('input', {'save-item': 'purchaseOrderNo', 'type': 'value', 'value': tmp.item.purchaseOrderNo ? tmp.item.purchaseOrderNo : ''}) ) ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-1 active'}).update(tmp.me._getFormGroup('Active?', new Element('input', {'save-item': 'active', 'type': 'checkbox', 'checked': tmp.item.active }) ) ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-2 orderDate'}).update(tmp.me._getFormGroup('Ordered Date', 
							new Element('input', {'class': 'datepicker', 'save-item': 'orderDate', 'value': (tmp.item.orderDate ? tmp.item.orderDate : '') })  
					) ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-2 createDate'}).update(tmp.me._getFormGroup('Created Date', 
							new Element('input', {'class': 'datepicker', 'save-item': 'created', 'value': (tmp.item.created ? tmp.item.created : '') })  
					) ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-2 updateDate'}).update(tmp.me._getFormGroup('Updated Date', 
							new Element('input', {'class': 'datepicker', 'save-item': 'updated', 'value': (tmp.item.updated ? tmp.item.updated : '') })  
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
				.insert({'bottom': new Element('div', {'class': 'col-sm-1 supplierContactName'}).update(tmp.me._getFormGroup('contactName', new Element('input', {'save-item': 'supplierContactName', 'type': 'text', 'value': tmp.item.supplier.contactName ? tmp.item.supplier.contactName : '' }) ) ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-1 supplierContactNo'}).update(tmp.me._getFormGroup('Contact No', new Element('input', {'save-item': 'supplierContactNo', 'type': 'value', 'value': tmp.item.supplier.contactNo ? tmp.item.supplier.contactNo : '' }) ) ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-2 supplierCreateDate'}).update(tmp.me._getFormGroup('Created Date', 
							new Element('input', {'class': 'datepicker', 'save-item': 'supplierCreated', 'value': (tmp.item.supplier.created ? tmp.item.supplier.created : '') })  
					) ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-2 supplierUpdateDate'}).update(tmp.me._getFormGroup('Updated Date', 
							new Element('input', {'class': 'datepicker', 'save-item': 'supplierUpdated', 'value': (tmp.item.supplier.updated ? tmp.item.supplier.updated : '') })  
					) ) })
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