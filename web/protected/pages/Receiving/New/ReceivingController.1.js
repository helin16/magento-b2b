/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new BPCPageJs(), {
	_htmlIds: {itemDiv: '',searchPanel: '',paymentPanel: '',supplierInfoPanel: '',partsTable:'',barcodeInput:''}
	,_purchaseOrder: null
	/**
	 * Setting the HTMLIDS
	 */
	,setHTMLIDs: function(itemDivId,searchPanelDivId,paymentPanelDivId,supplierInfoPanelDivId,partsTableDivId,barcodeInputDivId) {
		this._htmlIds.itemDiv = itemDivId;
		this._htmlIds.searchPanel = searchPanelDivId;
		this._htmlIds.paymentPanel = paymentPanelDivId;
		this._htmlIds.supplierInfoPanel = supplierInfoPanelDivId;
		this._htmlIds.partsTable = partsTableDivId;
		this._htmlIds.barcodeInput = barcodeInputDivId;
		return this;
	}
	/**
	 * Getting the customer list panel
	 */
	,_getPOListPanel: function () {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = new Element('div', {'id': tmp.me._htmlIds.searchPanel, 'class': 'panel panel-info search-panel'})
			.insert({'bottom': new Element('div', {'class': 'panel-heading form-inline'})
				.insert({'bottom': new Element('strong').update('Searching for PO: ') })
				.insert({'bottom': new Element('span', {'class': 'input-group col-sm-6'})
					.insert({'bottom': new Element('input', {'required': true, 'class': 'form-control search-txt init-focus', 'placeholder': 'any of PO number, Supplier, Supplier Ref Number ...'}) 
					.observe('keydown', function(event){
						tmp.txtBox = this;
						tmp.me.keydown(event, function() {
							$(tmp.me._htmlIds.searchPanel).down('.search-btn').click();
					});
							return false;
						})
					})
					.insert({'bottom': new Element('span', {'class': 'input-group-btn search-btn'})
						.insert({'bottom': new Element('span', {'class': ' btn btn-primary'})
							.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-search'}) })
						})
						.observe('click', function(){
							tmp.btn = this;
							if($(tmp.me._htmlIds.searchPanel).down('.search-txt').value!=='')
								tmp.me._searchPO($(tmp.me._htmlIds.searchPanel).down('.search-txt'));
						})
					})
				})
				.insert({'bottom': new Element('span', {'class': 'btn btn-success pull-right btn-sm btn-danger'})
					.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-remove'}) })
					.observe('click', function(){
						$(tmp.me._htmlIds.searchPanel).down('.search-txt').clear();
						tmp.me._searchPO($(tmp.me._htmlIds.searchPanel).down('.search-txt'));
					})
				})
			})
			;
		return tmp.newDiv;
	}
	/**
	 * Ajax: searching for POs
	 */
	,_searchPO: function (txtbox) {
		var tmp = {};
		tmp.me = this;
		tmp.searchTxt = $F(txtbox).strip();
		tmp.searchPanel = $(txtbox).up('#' + tmp.me._htmlIds.searchPanel);
		tmp.me.postAjax(tmp.me.getCallbackId('searchPO'), {'searchTxt': tmp.searchTxt}, {
			'onLoading': function() {
				if($(tmp.searchPanel).down('.list-div'))
					$(tmp.searchPanel).down('.list-div').remove();
				$(tmp.searchPanel).insert({'bottom': new Element('div', {'class': 'panel-body'}).update(tmp.me.getLoadingImg()) });
			}
			,'onSuccess': function (sender, param) {
				$(tmp.searchPanel).down('.panel-body').remove();
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result || !tmp.result.items)
						return;
					$(tmp.searchPanel).insert({'bottom': new Element('small', {'class': 'table-responsive list-div'})
						.insert({'bottom': new Element('table', {'class': 'table table-hover table-condensed'})
							.insert({'bottom': new Element('thead') 
								.insert({'bottom': tmp.me._getPORow({'purchaseOrderNo': 'PO Number', 'supplier': {'name': 'Supplier'} , 'supplierRefNo': 'Supplier Ref', 'orderDate': 'Order Date', 'totalAmount': 'Total Amount', 'totalProdcutCount': 'Total Prodcut Count','status': 'Status', 'active': 'Active?'}, true)  })
							})
							.insert({'bottom': tmp.listDiv = new Element('tbody') })
						})
					});
					tmp.result.items.each(function(item) {
						tmp.listDiv.insert({'bottom': tmp.me._getPORow(item) })
					});
				} catch (e) {
					$(tmp.searchPanel).insert({'bottom': new Element('div', {'class': 'panel-body'}).update(tmp.me.getAlertBox('ERROR', e).addClassName('alert-danger')) });
				}
			}
		});
		return tmp.me;
	}
	/**
	 * Getting the PO row for displaying the searching result
	 */
	,_getPORow: function(po, isTitle) {
		var tmp = {};
		tmp.me = this;
		tmp.isTitle = (isTitle || false);
		tmp.tag = (tmp.isTitle === true ? 'th': 'td');
		tmp.newDiv = new Element('tr', {'class': (tmp.isTitle === true ? 'item_top_row' : 'btn-hide-row item_row') + (po.active == 0 ? ' danger' : ''), 'item_id': (tmp.isTitle === true ? '' : po.id)})
			.insert({'bottom': new Element(tmp.tag)
				.insert({'bottom': (tmp.isTitle === true ? '&nbsp;':
					new Element('span', {'class': 'btn btn-primary btn-xs'}).update('select')	
					.observe('click', function(){
						tmp.me.selectPO(po);
					})
				) })
			})
			.insert({'bottom': new Element(tmp.tag).update(po.purchaseOrderNo) })
			.insert({'bottom': new Element(tmp.tag).update(po.supplier.name) })
			.insert({'bottom': new Element(tmp.tag).update(po.supplierRefNo) })
			.insert({'bottom': new Element(tmp.tag).update(po.orderDate) })
			.insert({'bottom': new Element(tmp.tag).update(po.totalAmount) })
			.insert({'bottom': new Element(tmp.tag).update(po.totalProdcutCount) })
			.insert({'bottom': new Element(tmp.tag).update(po.status) })
			.insert({'bottom': new Element(tmp.tag)
				.insert({'bottom': (tmp.isTitle === true ? po.active : new Element('input', {'type': 'checkbox', 'disabled': true, 'checked': po.active}) ) })
			})
		;
		return tmp.newDiv;
	}
	/**
	 * when user select (click) one PO
	 */
	,selectPO: function(po) {
		var tmp = {};
		tmp.me = this;
		tmp.me._purchaseOrder = po;
		tmp.newDiv = tmp.me._getViewOfPurchaseOrder();
		$(tmp.me._htmlIds.itemDiv).update(tmp.newDiv);
		return tmp.me;
	}
	/**
	 * Getting the div of the PO view
	 */
	,_getViewOfPurchaseOrder: function() {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = new Element('div')
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('div', {'class': 'col-sm-6'}).update(tmp.me._getSupplierInfoPanel()) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-6'}).update(tmp.me._getPaymentPanel()) })
			})
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('div', {'class': 'col-sm-12'}).update(tmp.me._getPartsTable()) })
			})
			.insert({'bottom': new Element('div', {'class': 'row'})
//				.insert({'bottom': new Element('div', {'class': 'col-sm-12'}).update(tmp.me._saveBtns()) })
			});
//		tmp.me._purchaseOrderItems.each(function(product){
//			tmp.me._addNewProductRow(tmp.me._newDiv.down('.glyphicon.glyphicon-floppy-saved'),product);
//		});
//		tmp.me._newDiv.getElementsBySelector('.order-item-row').each(function(item){
//			item.addClassName('order-item-row-old');
//		});
		return tmp.newDiv;
	}
	/**
	 * getting the supplier information div
	 */
	,_getSupplierInfoPanel: function() {
		var tmp = {};
		tmp.me = this;
		tmp.purchaseOrder = tmp.me._purchaseOrder;
		tmp.supplier = tmp.purchaseOrder.supplier;
		tmp.newDiv = new Element('div', {'class': 'panel panel-info', 'id': tmp.me._htmlIds.supplierInfoPanel})
			.insert({'bottom': new Element('div', {'class': 'panel-heading'})
				.insert({'bottom': new Element('span').update('Receiving items for PO: ') 
					.insert({'bottom': new Element('strong').update(tmp.purchaseOrder.purchaseOrderNo + ' ') })
				})
				.insert({'bottom': new Element('div', {'class': 'pull-right'})
					.insert({'bottom': new Element('strong').update('Status: ') })
					.insert({'bottom': tmp.me._getOrderStatus() })
				})
			})
			.insert({'bottom': new Element('div', {'class': 'panel-body', 'style': 'padding: 0 10px'})
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('div', {'class': 'col-sm-3'}).update(tmp.me._getFormGroup('Name', new Element('input', {'disabled': true, 'type': 'text', 'value': tmp.supplier.name ? tmp.supplier.name : ''}) ) ) })
					.insert({'bottom': new Element('div', {'class': 'col-sm-3'}).update(tmp.me._getFormGroup('Contact Name', new Element('input', {'disabled': true, 'type': 'text', 'value': tmp.purchaseOrder.supplierContact ? tmp.purchaseOrder.supplierContact : tmp.supplier.contactName}) ) ) })
					.insert({'bottom': new Element('div', {'class': 'col-sm-3'}).update(tmp.me._getFormGroup('Contact Number', new Element('input', {'disabled': true, 'type': 'value', 'value': tmp.purchaseOrder.supplierContactNumber ? tmp.purchaseOrder.supplierContactNumber : tmp.supplier.contactNo}) ) ) })
					.insert({'bottom': new Element('div', {'class': 'col-sm-3'}).update(tmp.me._getFormGroup('Supplier Ref Num', new Element('input', {'disabled': true, 'type': 'text', 'value': tmp.purchaseOrder.supplierRefNo ? tmp.purchaseOrder.supplierRefNo : ''}) ) ) })
				 })
			});
		return tmp.newDiv;
	}
	/**
	 * Getting the order status dropdown list
	 */
	,_getOrderStatus: function () {
		var tmp = {}
		tmp.me = this;
		tmp.selBox = new Element('select', {'disabled': true})
			.insert({'bottom': new Element('option').update(tmp.me._purchaseOrder.status) });
		return tmp.selBox;
	}
	/**
	 * Getting the form group
	 */
	,_getFormGroup: function(title, content) {
		return new Element('div', {'class': 'form-group'})
			.insert({'bottom': title ? new Element('label', {'class': 'control-label'}).update(title) : '' })
			.insert({'bottom': content.addClassName('form-control') });
	}
	,_getPaymentPanel: function () {
		var tmp = {};
		tmp.me = this;
		tmp.purchaseOrder = tmp.me._purchaseOrder;
		tmp.supplier = tmp.purchaseOrder.supplier;
		tmp.shippingCostEl = new Element('input', {'disabled': true, 'class': 'text-right', 'value': tmp.purchaseOrder.shippingCost ? tmp.purchaseOrder.shippingCost : 0});
		tmp.handlingCostEl = new Element('input', {'disabled': true, 'class': 'text-right', 'value': tmp.purchaseOrder.handlingCost ? tmp.purchaseOrder.handlingCost : 0});
		tmp.totalAmountExGstEl = new Element('input', {'disabled': true, 'class': 'text-right', 'value': tmp.purchaseOrder.totalAmount ? tmp.purchaseOrder.totalAmount : 0});
		tmp.totalPaidEl = new Element('input', {'disabled': true, 'class': 'text-right', 'value': tmp.purchaseOrder.totalPaid ? tmp.purchaseOrder.totalPaid : 0});
		
		tmp.newDiv = new Element('div', {'class': 'panel panel-info', 'id': tmp.me._htmlIds.paymentPanel})
			.insert({'bottom': new Element('div', {'class':'panel-heading'})
				.insert({'bottom': new Element('strong').update('Payment Info: ') })
			})
			.insert({'bottom': new Element('div', {'class': 'panel-body', 'style': 'padding: 0 10px'})
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('div', {'class': 'col-sm-3'}).update(tmp.me._getFormGroup('Total Ex GST', new Element('input', {'disabled': true, 'type': 'text', 'value': tmp.purchaseOrder.totalAmount ? tmp.me.getCurrency(tmp.purchaseOrder.totalAmount) : ''}) ) ) })
					.insert({'bottom': new Element('div', {'class': 'col-sm-3'}).update(tmp.me._getFormGroup('Contact Name', new Element('input', {'disabled': true, 'type': 'text', 'value': tmp.purchaseOrder.totalPaid ? tmp.me.getCurrency(tmp.purchaseOrder.totalPaid) : ''}) ) ) })
					.insert({'bottom': new Element('div', {'class': 'col-sm-3'}).update(tmp.me._getFormGroup('Contact Number', new Element('input', {'disabled': true, 'type': 'value', 'value': tmp.purchaseOrder.shippingCost ? tmp.me.getCurrency(tmp.purchaseOrder.shippingCost) : ''}) ) ) })
					.insert({'bottom': new Element('div', {'class': 'col-sm-3'}).update(tmp.me._getFormGroup('Supplier Ref Num', new Element('input', {'disabled': true, 'type': 'text', 'value': tmp.purchaseOrder.handlingCost ? tmp.me.getCurrency(tmp.purchaseOrder.handlingCost) : ''}) ) ) })
				 })
			});
		return tmp.newDiv;
	}
	/**
	 * Getting the parts panel
	 */
	,_getPartsTable: function () {
		var tmp = {};
		tmp.me = this;
		//header row
		tmp.productListDiv = new Element('table', {'class': 'table table-hover table-condensed', 'id': tmp.me._htmlIds.partsTable})
			.insert({'bottom': tmp.me._getProductRow({'product': {'sku': 'SKU', 'name': 'Description'} }, true)
				.wrap( new Element('thead') )
			});
		// tbody
		tmp.productListDiv.insert({'bottom': tmp.tbody = new Element('tbody', {'style': 'border: 3px #ccc solid;'})
			.insert({'bottom': tmp.me._getNewProductRow() })
		});
		tmp.productListDiv.down('[new-order-item=product]').focus();
		return new Element('div', {'class': 'panel panel-info'})
			.insert({'bottom': new Element('div', {'class': 'panel-body table-responsive'})
				.insert({'bottom':  tmp.productListDiv})
			});
	}
	/**
	 * Getting each product row
	 */
	,_getProductRow: function(orderItem, isTitleRow) {
		var tmp = {};
		tmp.me = this;
		tmp.isTitle = (isTitleRow || false);
		tmp.tag = (tmp.isTitle === true ? 'th' : 'td');
		tmp.row = new Element('tr', {'class': (tmp.isTitle === true ? '' : 'item_row order-item-row')})
			.store('data', orderItem.product)
			.insert({'bottom': new Element(tmp.tag, {'class': 'productName'})
				.insert({'bottom': orderItem.product.name ? orderItem.product.name : orderItem.product.barcode })
			})
			.insert({'bottom': new Element(tmp.tag, {'class': 'btns  col-xs-1'}).update(orderItem.btns ? orderItem.btns : '') });
		if(orderItem.product.sku) {
			tmp.row.insert({'top': new Element(tmp.tag, {'class': 'productSku'}).update(orderItem.product.sku) });
		} else {
			tmp.row.down('.productName').writeAttribute('colspan', 2);
		}
		return tmp.row;
	}
	/**
	 * Getting the new product row
	 */
	,_getNewProductRow: function() {
		var tmp = {};
		tmp.me = this;
		tmp.skuAutoComplete = tmp.me._getNewProductProductAutoComplete();
		tmp.data = {
			'product': {'name': tmp.skuAutoComplete	}
			, 'btns': new Element('span', {'class': 'btn-group btn-group-sm pull-right'})
					.insert({'bottom': new Element('span', {'class': 'btn btn-primary'})
					.insert({'bottom': new Element('span', {'class': ' glyphicon glyphicon-floppy-saved'}) })
					.observe('click', function() {
						tmp.me._addNewProductRow(this);
					})
				})
				.insert({'bottom': new Element('span', {'class': 'btn btn-default'})
					.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-floppy-remove'}) })
					.observe('click', function() {
						if(!confirm('You about to clear this entry. All input data for this entry will be lost.\n\nContinue?'))
							return;
						tmp.newRow = tmp.me._getNewProductRow();
						tmp.currentRow = $(this).up('.new-order-item-input');
						tmp.currentRow.getElementsBySelector('.form-group.has-error .form-control').each(function(control){
							$(control).retrieve('clearErrFunc')();
						});
						tmp.currentRow.replace(tmp.newRow);
						tmp.newRow.down('[new-order-item=product]').focus();
					})
				})
		};
		return tmp.me._getProductRow(tmp.data, false).addClassName('new-order-item-input info').removeClassName('order-item-row');
	}
	/**
	 * Getting the autocomplete input box for product
	 */
	,_getNewProductProductAutoComplete: function() {
		var tmp = {};
		tmp.me = this;
		tmp.skuAutoComplete = tmp.me._getFormGroup( null, new Element('div', {'class': 'input-group input-group-sm product-autocomplete'})
			.insert({'bottom': new Element('input', {'id': tmp.me._htmlIds.barcodeInput, 'class': 'form-control search-txt visible-xs visible-sm visible-md visible-lg', 'new-order-item': 'product', 'required': 'Required!', 'placeholder': 'Enter BARCODE for products'})
				.observe('keydown', function(event){
					tmp.txtBox = this;
					tmp.me.keydown(event, function() {
						$(tmp.txtBox).up('.product-autocomplete').down('.search-btn').click();
					});
					tmp.me.keydown(event, function() {
						$(tmp.txtBox).up('.product-autocomplete').down('.search-btn').click();
					}, null, 9);
					return false;
				})
			})
			.insert({'bottom': new Element('span', {'class': 'input-group-btn'}) 
				.insert({'bottom': new Element('span', {'class': ' btn btn-primary search-btn' , 'data-loading-text': 'searching...'})
					.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-search'}) })
					.observe('click', function(){
						tmp.me._searchProduct(this);
					})
				})
			}) 
		);
		tmp.skuAutoComplete.down('.input-group').removeClassName('form-control');
		return tmp.skuAutoComplete;
	}
	/**
	 * Ajax: searching the product based on a string
	 */
	,_searchProduct: function(btn) {
		var tmp = {};
		tmp.me = this;
		tmp.btn = btn;
		tmp.me._signRandID(tmp.btn);
		tmp.searchTxtBox = $(tmp.btn).up('.product-autocomplete').down('.search-txt');
		tmp.searchTxt = $F(tmp.searchTxtBox);
		tmp.currentRow = $(btn).up('.new-order-item-input');
		tmp.product = {
				'name': '',
				'id' : '',
				'barcode': tmp.searchTxt
		};
		tmp.data = {
				'product': tmp.product, 
				'btns': new Element('span', {'class': 'pull-right'})
					.insert({'bottom': new Element('span', {'class': 'btn btn-danger btn-xs'})
					.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-trash'}) })
					.observe('click', function() {
						if(!confirm('You remove this entry.\n\nContinue?'))
							return;
						tmp.row = $(this).up('.item_row');
						tmp.me._recalculateSummary( 0 - tmp.me.getValueFromCurrency(tmp.row.retrieve('data').totalPrice) * 1 );
						if (tmp.row.hasClassName('order-item-row-old')) {
							tmp.row.addClassName('order-item-row-old-removed');
							tmp.row.hide();
						} else {
							tmp.row.remove();
						}
					})
				})
			};
		tmp.currentRow.insert({'after': tmp.lastRow = tmp.me._getProductRow(tmp.data, false) });
		tmp.newRow = tmp.me._getNewProductRow();
		tmp.newRow.addClassName('btn-hide-row');
		tmp.currentRow.replace(tmp.newRow);
		tmp.newRow.down('[new-order-item=product]').focus();
		
		tmp.delBtn = tmp.lastRow.down('.btn');
		tmp.me._signRandID(tmp.delBtn);
		
		tmp.me.postAjax(tmp.me.getCallbackId('searchProduct'), {'searchTxt': tmp.searchTxt}, {
			'onLoading': function() {
				jQuery('#' + tmp.me._htmlIds.barcodeInput).button('loading');
			}
			,'onSuccess': function(sender, param) {
				tmp.resultList = new Element('div', {'style': 'overflow: auto; max-height: 400px;'});
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result || !tmp.result.items || tmp.result.items.size() === 0)
						throw 'Nothing Found for: ' + tmp.searchTxt;
					
					tmp.me._signRandID(tmp.searchTxtBox);
					tmp.result.items.each(function(product) {
						tmp.data.product = product;
						tmp.lastRow.replace(tmp.newRow = tmp.me._getProductRow(tmp.data, false) );
					});
					tmp.resultList.addClassName('list-group'); 
				} catch(e) {
					tmp.resultList.update(tmp.me.getAlertBox('Error: ', e).addClassName('alert-danger'));
				}
//				tmp.me.showModalBox('Products that has: ' + tmp.searchTxt, tmp.resultList, false);
			}
			,'onComplete': function(sender, param) {
				jQuery('#' + tmp.me._htmlIds.barcodeInput).button('reset');
			}
		});
		
		
		
//		tmp.me.postAjax(tmp.me.getCallbackId('searchProduct'), {'searchTxt': tmp.searchTxt, 'supplierID': tmp.me._supplier.id}, {
//			'onLoading': function() {
//				jQuery('#' + tmp.btn.id).button('loading');
//			}
//			,'onSuccess': function(sender, param) {
//				tmp.resultList = new Element('div', {'style': 'overflow: auto; max-height: 400px;'});
//				try {
//					tmp.result = tmp.me.getResp(param, false, true);
//					if(!tmp.result || !tmp.result.items || tmp.result.items.size() === 0)
//						throw 'Nothing Found for: ' + tmp.searchTxt;
//					tmp.me._signRandID(tmp.searchTxtBox);
//					tmp.result.items.each(function(product) {
//						tmp.resultList.insert({'bottom': tmp.me._getSearchPrductResultRow(product, tmp.searchTxtBox) });
//					});
//					tmp.resultList.addClassName('list-group'); 
//				} catch(e) {
//					tmp.resultList.update(tmp.me.getAlertBox('Error: ', e).addClassName('alert-danger'));
//				}
//				tmp.me.showModalBox('Products that has: ' + tmp.searchTxt, tmp.resultList, false);
//			}
//			,'onComplete': function(sender, param) {
//				jQuery('#' + tmp.btn.id).button('reset');
//			}
//		});
		return tmp.me;
	}
	,init: function() {
		var tmp = {};
		tmp.me = this;
		$(tmp.me._htmlIds.itemDiv).update(tmp.me._getPOListPanel()).down('.init-focus').focus();
	}
});

