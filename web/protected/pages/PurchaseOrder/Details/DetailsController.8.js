/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new DetailsPageJs(), {
	_htmlIds: {'paymentPanel': 'payment_panel', 'itemDiv': '', 'searchPanel': 'search_panel', 'totalPriceExcludeGST': 'total_price_exclude_gst', 'totalPriceGST': 'total_price_gst', 'totalPriceIncludeGST': 'total_price_include_gst', 'totalPaidAmount': 'total-paid-amount', 'totalShippingCost': 'total-shipping-cost'}
	,_supplier: null
	,_purchaseorder: {}
	,_newDiv: null
	/**
	 * Setting the HTMLIDS
	 */
	,setHTMLIDs: function(itemDivId) {
		var tmp = {};
		tmp.me = this;
		tmp.me._htmlIds.itemDiv = itemDivId;
		return tmp.me;
	}
	/**
	 * Set some pre defined data before javascript start
	 */
	,setPreData: function(purchaseorder) {
		var tmp = {};
		tmp.me = this;
		tmp.me._purchaseorder = purchaseorder;
		tmp.me._supplier = purchaseorder.supplier;
		return tmp.me;
	}
	/**
	 * setting the status options
	 */
	,setStatusOptions: function(statusOptions) {
		var tmp = {};
		tmp.me = this;
		tmp.me._statusOptions = statusOptions;
		return tmp.me;
	}
	/**
	 * setting the comment
	 */
	,setComment: function(comment) {
		var tmp = {};
		tmp.me = this;
		tmp.me._comment = comment.comments;
		return tmp.me;
	}
	/**
	 * setting the Purchase Order Items
	 */
	,setPurchaseOrderItems: function(purchaseOrderItems) {
		var tmp = {};
		tmp.me = this;
		tmp.me._purchaseOrderItems = purchaseOrderItems;
		return tmp.me;
	}
	/**
	 * This function should return you the edit div for this item
	 */
	,_getItemDiv: function() {
		var tmp = {};
		tmp.me = this;
		tmp.me._newDiv = new Element('div')
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('div', {'class': 'col-sm-6'}).update(tmp.me._getSupplierInfoPanel()) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-6'}).update(tmp.me._getPaymentPanel()) })
			})
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('div', {'class': 'col-sm-12'}).update(tmp.me._getPartsTable()) })
			})
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('div', {'class': 'col-sm-12'}).update(tmp.me._saveBtns()) })
			})
		;
		tmp.me._purchaseOrderItems.each(function(product){
			tmp.me._addNewProductRow(tmp.me._newDiv.down('.glyphicon.glyphicon-floppy-saved'),product);
		});
		tmp.me._newDiv.getElementsBySelector('.order-item-row').each(function(item){
//			item.removeClassName('order-item-row');
			item.addClassName('order-item-row-old');
		});
		return tmp.me._newDiv;
	}
	
	/**
	 * getting the customer information div
	 */
	,_getSupplierInfoPanel: function() {
		var tmp = {};
		tmp.me = this;
		tmp.supplier = tmp.me._purchaseorder.supplier;
		tmp.purchaseOrder = tmp.me._purchaseorder;
		tmp.newDiv = new Element('div', {'class': 'panel panel-default'})
			.insert({'bottom': new Element('div', {'class': 'panel-heading'})
				.insert({'bottom': new Element('strong').update('Creating purchase order for: ' + tmp.supplier.name + ' ') })
			})
			.insert({'bottom': new Element('div', {'class': 'panel-body'})
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('div', {'class': 'col-sm-3'}).update(tmp.me._getFormGroup('Name', new Element('input', {'disabled': 'disabled', 'type': 'text', 'value': tmp.supplier.name ? tmp.supplier.name : ''}) ) ) })
					.insert({'bottom': new Element('div', {'class': 'col-sm-3'}).update(tmp.me._getFormGroup('Contact Name', new Element('input', {'save-order': 'contactName', 'type': 'text', 'value': tmp.purchaseOrder.supplierContact ? tmp.purchaseOrder.supplierContact : ''}) ) ) })
					.insert({'bottom': new Element('div', {'class': 'col-sm-3'}).update(tmp.me._getFormGroup('Contact Number', new Element('input', {'save-order': 'contactNo', 'type': 'value', 'value': tmp.purchaseOrder.supplierContactNumber ? tmp.purchaseOrder.supplierContactNumber : ''}) ) ) })
					.insert({'bottom': new Element('div', {'class': 'col-sm-3'}).update(tmp.me._getFormGroup('Supplier Ref Num', new Element('input', {'required': 'required', 'save-order': 'supplierRefNum', 'type': 'text', 'value': tmp.purchaseOrder.supplierRefNo ? tmp.purchaseOrder.supplierRefNo : ''}) ) ) })
				 })
			});
		return tmp.newDiv;
	}
	,_getPaymentPanel: function () {
		var tmp = {};
		tmp.me = this;
		tmp.supplier = tmp.me._supplier;
		tmp.shippingCostEl = new Element('input', {'class': 'text-right', 'id': 'shipping_cost', 'save-order': 'shippingCost' , 'value': tmp.me._purchaseorder.shippingCost ? tmp.me.getCurrency(tmp.me._purchaseorder.shippingCost) : tmp.me.getCurrency(0)});
		tmp.handlingCostEl = new Element('input', {'class': 'text-right', 'id': 'handling_cost', 'save-order': 'handlingCost' , 'value': tmp.me._purchaseorder.handlingCost ? tmp.me.getCurrency(tmp.me._purchaseorder.handlingCost) : tmp.me.getCurrency(0)});
		tmp.totalAmountExGstEl = new Element('input', {'class': 'text-right', 'disabled': 'disabled', 'save-order': 'totalAmount'});
		tmp.totalPaidEl = new Element('input', {'class': 'text-right', 'id': tmp.me._htmlIds.totalPaidAmount, 'save-order': 'totalPaid' , 'value': tmp.me._purchaseorder.totalPaid ? tmp.me.getCurrency(tmp.me._purchaseorder.totalPaid) : tmp.me.getCurrency(0)})
			.observe('keyup',function(){
				tmp.totalPaidAmount = this.value==='' ? 0 : this.value;
				if(jQuery.isNumeric(tmp.totalPaidAmount)) {
					tmp.totalExcGST = tmp.me.getValueFromCurrency($(tmp.me._htmlIds.totalPriceExcludeGST).innerHTML) * 1;
					tmp.totalPaymentDue = tmp.totalExcGST * 1 - tmp.totalPaidAmount;
					$$('.total-payment-due').each(function(item) {
						tmp.newEl = new Element('strong', {'class': 'label'}).update(tmp.me.getCurrency(tmp.totalPaymentDue) + ' ');
						if(tmp.totalPaymentDue * 1 > 0) {
							tmp.newEl.addClassName('label-info').writeAttribute('title', 'Need to pay supplier')
								.insert({'bottom': new Element('span', {'class': ' glyphicon glyphicon-import'})});
						} else if (tmp.totalPaymentDue * 1 === 0) {
							tmp.newEl.addClassName('label-success')
								.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-ok'})});
						} else {
							tmp.newEl.addClassName('label-danger').writeAttribute('title', 'Over paid to supplier')
								.insert({'bottom': new Element('span', {'class': ' glyphicon glyphicon-export'})});
						}
						item.update(tmp.newEl);
					});					
				}
			});
		tmp.newDiv = new Element('div', {'class': 'panel panel-default', 'id': tmp.me._htmlIds.paymentPanel})
			.insert({'bottom': new Element('div', {'class':'panel-heading'})
				.insert({'bottom': new Element('strong').update('Total Payment Due: ') })
				.insert({'bottom': new Element('span', {'class': 'pull-right total-payment-due'}).update(tmp.me.getCurrency(0) ) })
			})
			.insert({'bottom': new Element('div', {'class':'row'})
				.insert({'bottom': new Element('div', {'class':'col-md-6'})
					.insert({'bottom': new Element('div', {'class': 'list-group-item'})
						.insert({'bottom': new Element('div', {'class': 'row'})
							.insert({'bottom': new Element('div', {'class': 'col-xs-6 text-left form-group', 'style': 'margin: 0px;'})
								.insert({'bottom': new Element('lable', {'class': 'text-left active'}).update( new Element('span').update('Total Ex GST') ) }) 
							})
							.insert({'bottom': new Element('div', {'class': 'col-xs-6 text-left form-group', 'style': 'margin: 0px;'})
								.insert({'bottom': tmp.totalAmountExGstEl.addClassName('form-control input-sm col-xs-6') })
							})
						})
					})
					.insert({'bottom': new Element('div', {'class': 'list-group-item'})
						.insert({'bottom': new Element('div', {'class': 'row'})
							.insert({'bottom': new Element('div', {'class': 'col-xs-6 text-left form-group', 'style': 'margin: 0px;'})
								.insert({'bottom': new Element('lable', {'class': 'text-left active'}).update( new Element('span').update('Total Paid') ) }) 
							})
							.insert({'bottom': new Element('div', {'class': 'col-xs-6 form-group', 'style': 'margin: 0px;'})
								.insert({'bottom': tmp.totalPaidEl.addClassName('form-control input-sm') })
							})
						}) 
					})
				})
				.insert({'bottom': new Element('div', {'class':'col-md-6'})
					.insert({'bottom': new Element('div', {'class': 'list-group-item'})
						.insert({'bottom': new Element('div', {'class': 'row'})
							.insert({'bottom': new Element('div', {'class': 'col-xs-6 text-left form-group', 'style': 'margin: 0px;'})
								.insert({'bottom': new Element('lable', {'class': 'text-left active'}).update( new Element('span').update('Shipping Cost') ) }) 
							})
							.insert({'bottom': new Element('div', {'class': 'col-xs-6 form-group', 'style': 'margin: 0px;'})
								.insert({'bottom': tmp.shippingCostEl.addClassName('form-control input-sm') })
							})
						}) 
					})
					.insert({'bottom': new Element('div', {'class': 'list-group-item'})
						.insert({'bottom': new Element('div', {'class': 'row'})
							.insert({'bottom': new Element('div', {'class': 'col-xs-6 text-left form-group', 'style': 'margin: 0px;'})
								.insert({'bottom': new Element('lable', {'class': 'text-left active'}).update( new Element('span').update('Handling Cost') ) }) 
							})
							.insert({'bottom': new Element('div', {'class': 'col-xs-6 form-group', 'style': 'margin: 0px;'})
								.insert({'bottom': tmp.handlingCostEl.addClassName('form-control input-sm') })
							})
						}) 
					})
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
		tmp.productListDiv = new Element('table', {'class': 'table table-hover table-condensed order_change_details_table'})
			.insert({'bottom': tmp.me._getProductRow({'product': {'sku': 'SKU', 'name': 'Description'}, 'unitPrice': 'Unit Price', 'qtyOrdered': 'Qty', 'totalPrice': 'Total Price'}, true)
				.wrap( new Element('thead') )
			});
		// tbody
		tmp.productListDiv.insert({'bottom': tmp.tbody = new Element('tbody', {'style': 'border: 3px #ccc solid;'})
			.insert({'bottom': tmp.me._getNewProductRow() })
		});
		// tfooter
		tmp.productListDiv.insert({'bottom': tmp.tbody = new Element('tfoot')
			.insert({'bottom': new Element('tr') 
				.insert({'bottom': new Element('td', {'colspan': 2, 'rowspan': 4})
					.insert({'bottom': tmp.me._getFormGroup( 'Comments:', new Element('textarea', {'save-order': 'comments'}).update(tmp.me._comment ? tmp.me._comment : '') ) })
				}) 
				.insert({'bottom': new Element('td', {'colspan': 2, 'class': 'text-right active'}).update( new Element('strong').update('Total Excl. GST: ') ) }) 
				.insert({'bottom': new Element('td', {'id': tmp.me._htmlIds.totalPriceExcludeGST, 'class': 'active'}).update( tmp.me.getCurrency(0) ) }) 
				.insert({'bottom': new Element('td', {'rowspan': 4}).update('&nbsp;') }) 
			})
			.insert({'bottom': new Element('tr') 
				.insert({'bottom': new Element('td', {'colspan': 2, 'class': 'text-right active'}).update( new Element('strong').update('Total GST: ') ) }) 
				.insert({'bottom': new Element('td', {'id': tmp.me._htmlIds.totalPriceGST, 'class': 'active'}).update( tmp.me.getCurrency(0) ) }) 
			})
			.insert({'bottom': new Element('tr') 
				.insert({'bottom': new Element('td', {'colspan': 2, 'class': 'text-right active'}).update( new Element('strong').update('Total Incl. GST: ') ) }) 
				.insert({'bottom': new Element('td', {'id': tmp.me._htmlIds.totalPriceIncludeGST, 'class': 'active'}).update( tmp.me.getCurrency(0) ) })
			})
		});
		return new Element('div', {'class': 'panel panel-default'})
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
			.store('data', orderItem)
			.insert({'bottom': new Element(tmp.tag, {'class': 'productName'})
				.insert({'bottom': orderItem.product.name })
			})
			.insert({'bottom': new Element(tmp.tag, {'class': 'uprice col-xs-1'})
				.insert({'bottom': (orderItem.unitPrice) })
				.observe('keydown', function(event){
					tmp.txtBox = this;
					tmp.me.keydown(event, function() {
						$(tmp.txtBox).up('.item_row').down('.tprice input').value = tmp.me.getCurrency($(tmp.txtBox).down('input').value);
						$(tmp.txtBox).up('.item_row').down('.glyphicon.glyphicon-floppy-saved').click();
					});
					return false;
				})
			})
			.insert({'bottom': new Element(tmp.tag, {'class': 'qty col-xs-1'})
				.insert({'bottom': (orderItem.qtyOrdered) })
				.observe('keydown', function(event){
					tmp.txtBox = this;
					tmp.me.keydown(event, function() {
						$(tmp.txtBox).up('.item_row').down('.glyphicon.glyphicon-floppy-saved').click();
					});
					return false;
				})
			})
			.insert({'bottom': new Element(tmp.tag, {'class': 'tprice col-xs-1'})
				.insert({'bottom': (orderItem.totalPrice) })
				.observe('keydown', function(event){
					tmp.txtBox = this;
					tmp.me.keydown(event, function() {
						$(tmp.txtBox).up('.item_row').down('.glyphicon.glyphicon-floppy-saved').click();
					});
					return false;
				})
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
			,'unitPrice': tmp.me._getFormGroup( null, new Element('input', {'class': 'input-sm', 'new-order-item': 'unitPrice', 'required': 'Required!' , 'value': tmp.me.getCurrency(0)})
				.observe('keyup', function(){
					tmp.row =$(this).up('.item_row');
					tmp.unitPrice = tmp.me.getValueFromCurrency($F(this));
					tmp.qty = $F(tmp.row.down('[new-order-item=qtyOrdered]'));
					$(tmp.row.down('[new-order-item=totalPrice]')).value = tmp.me.getCurrency( tmp.unitPrice * tmp.qty);
				})
			)
			,'qtyOrdered': tmp.me._getFormGroup( null, new Element('input', {'class': 'input-sm', 'new-order-item': 'qtyOrdered', 'required': 'Required!', 'value': '1'})
				.observe('keyup', function(){
					tmp.row =$(this).up('.item_row');
					tmp.unitPrice = tmp.me.getValueFromCurrency($F(tmp.row.down('[new-order-item=unitPrice]')));
					tmp.qty = $F(this);
					$(tmp.row.down('[new-order-item=totalPrice]')).value = tmp.me.getCurrency( tmp.unitPrice * tmp.qty);
				})
			)
			,'totalPrice': tmp.me._getFormGroup( null, new Element('input', {'class': 'input-sm', 'new-order-item': 'totalPrice', 'required': 'Required!', 'value': tmp.me.getCurrency(0)})
				.observe('keyup', function(){
					tmp.row =$(this).up('.item_row');
					tmp.totalPrice = tmp.me.getValueFromCurrency($F(this));
					tmp.qty = $F(tmp.row.down('[new-order-item=qtyOrdered]'));
					$(tmp.row.down('[new-order-item=unitPrice]')).value = tmp.me.getCurrency( tmp.totalPrice / tmp.qty );
				})
			)
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
			.insert({'bottom': new Element('input', {'class': 'form-control search-txt visible-xs visible-sm visible-md visible-lg', 'new-order-item': 'product', 'required': 'Required!', 'placeholder': 'search SKU, NAME and any BARCODE for this product'})
				.observe('keydown', function(event){
					tmp.txtBox = this;
					tmp.me.keydown(event, function() {
						$(tmp.txtBox).up('.product-autocomplete').down('.search-btn').click();
					});
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
	,_recalculateSummary: function(amount) {
		var tmp = {};
		tmp.me = this;
		tmp.totalIncGSTBox = $(tmp.me._htmlIds.totalPriceIncludeGST) ? $(tmp.me._htmlIds.totalPriceIncludeGST) : tmp.me._newDiv.down('#'+tmp.me._htmlIds.totalPriceIncludeGST);
		tmp.totalGSTBox = $(tmp.me._htmlIds.totalPriceGST) ? $(tmp.me._htmlIds.totalPriceGST) : tmp.me._newDiv.down('#'+tmp.me._htmlIds.totalPriceGST);
		tmp.totalExcGSTBox = $(tmp.me._htmlIds.totalPriceExcludeGST) ? $(tmp.me._htmlIds.totalPriceExcludeGST) : tmp.me._newDiv.down('#'+tmp.me._htmlIds.totalPriceExcludeGST);
		
		tmp.totalExcGST = tmp.me.getValueFromCurrency(tmp.totalExcGSTBox.innerHTML) * 1  + amount * 1;
		tmp.totalIncGST = tmp.totalExcGST ? (tmp.totalExcGST * 1 + 1.1) : 0;
		
		tmp.totalGST = tmp.totalExcGST ? (tmp.totalIncGST * 1 - tmp.totalExcGST * 1) : 0;
		
		tmp.totalIncGSTBox.update(tmp.me.getCurrency(tmp.totalIncGST));
		tmp.totalGSTBox.update(tmp.me.getCurrency(tmp.totalGST));
		tmp.totalExcGSTBox.update(tmp.me.getCurrency(tmp.totalExcGST));
		
		
		tmp.totalPaidAmount = $$('.pull-right.total-payment-due').first() ?
				($(tmp.me._htmlIds.totalPaidAmount) ? tmp.me.getValueFromCurrency($F(tmp.me._htmlIds.totalPaidAmount)) : 0)
				: tmp.me._purchaseorder.totalPaid;
		tmp.totalPaymentDue = tmp.totalExcGST * 1 - tmp.totalPaidAmount;
		$$('.total-payment-due').each(function(item) {
			tmp.newEl = new Element('strong', {'class': 'label'}).update(tmp.me.getCurrency(tmp.totalPaymentDue) + ' ');
			if(tmp.totalPaymentDue * 1 > 0) {
				tmp.newEl.addClassName('label-info').writeAttribute('title', 'Need to pay supplier')
					.insert({'bottom': new Element('span', {'class': ' glyphicon glyphicon-import'})});
			} else if (tmp.totalPaymentDue * 1 === 0) {
				tmp.newEl.addClassName('label-success')
					.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-ok'})});
			} else {
				tmp.newEl.addClassName('label-danger').writeAttribute('title', 'Over paid to supplier')
					.insert({'bottom': new Element('span', {'class': ' glyphicon glyphicon-export'})});
			}
			item.update(tmp.newEl);
		});
		if(!($$('.pull-right.total-payment-due').first() ) ) {
			tmp.me._newDiv.getElementsBySelector('.total-payment-due').each(function(item) {
				tmp.newEl = new Element('strong', {'class': 'label'}).update(tmp.me.getCurrency(tmp.totalPaymentDue) + ' ');
				if(tmp.totalPaymentDue * 1 > 0) {
					tmp.newEl.addClassName('label-info').writeAttribute('title', 'Need to pay supplier')
						.insert({'bottom': new Element('span', {'class': ' glyphicon glyphicon-import'})});
				} else if (tmp.totalPaymentDue * 1 === 0) {
					tmp.newEl.addClassName('label-success')
						.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-ok'})});
				} else {
					tmp.newEl.addClassName('label-danger').writeAttribute('title', 'Over paid to supplier')
						.insert({'bottom': new Element('span', {'class': ' glyphicon glyphicon-export'})});
				}
				item.update(tmp.newEl);
			});
		}
		if($$('#'+tmp.me._htmlIds.paymentPanel).first()) {
			$$('#'+tmp.me._htmlIds.paymentPanel).first().down('[save-order]="totalAmount"[disabled]="disabled"').value = tmp.me.getCurrency(tmp.totalExcGST);
		} else {
			tmp.me._newDiv.down('[save-order]="totalAmount"[disabled]="disabled"').value = tmp.me.getCurrency(tmp.totalExcGST);
		}
		return tmp.me;
	}
	/**
	 * adding a new product row after hit save btn
	 */
	,_addNewProductRow: function(btn,product) {
		var tmp = {};
		tmp.me = this;
		tmp.currentRow = $(btn).up('.new-order-item-input');
		tmp.product = (typeof product === 'undefined') ? tmp.currentRow.retrieve('product') : product.product;
		if(!tmp.product) {
			tmp.productBox =tmp.currentRow.down('[new-order-item=product]');
			if(tmp.currentRow.down('[new-order-item=product]')) {
				tmp.me._markFormGroupError(tmp.productBox, 'Select a product first!');
			} else {
				tmp.me.showModalBox('Product Needed', 'Select a product first!', true);
			}
			return ;
		}
		tmp.unitPriceBox = tmp.currentRow.down('[new-order-item=unitPrice]');
		tmp.unitPrice =  (typeof product === 'undefined') ? tmp.me.getValueFromCurrency($F(tmp.unitPriceBox)) : product.unitPrice;
		if( (!jQuery.isNumeric(tmp.unitPrice)) && (tmp.unitPrice.match(/^\d+(\.\d{1,2})?$/)  === null) ) {
			tmp.me._markFormGroupError(tmp.unitPriceBox, 'Invalid value provided!');
			return ;
		}
		tmp.qtyOrderedBox = tmp.currentRow.down('[new-order-item=qtyOrdered]');
		tmp.qtyOrdered = (typeof product === 'undefined') ? tmp.me.getValueFromCurrency($F(tmp.qtyOrderedBox)) : product.qrt;
		if(tmp.qtyOrdered.match(/^\d+(\.\d{1,2})?$/) === null) {
			tmp.me._markFormGroupError(tmp.qtyOrderedBox, 'Invalid value provided!');
			return ;
		}
		tmp.totalPriceBox = tmp.currentRow.down('[new-order-item=totalPrice]');
		tmp.totalPrice = (typeof product === 'undefined') ? tmp.me.getValueFromCurrency($F(tmp.totalPriceBox)) : product.totalPrice;
		if( (!jQuery.isNumeric(tmp.totalPrice)) && (tmp.totalPrice.match(/^\d+(\.\d{1,2})?$/) === null) ) {
			tmp.me._markFormGroupError(tmp.totalPriceBox, 'Invalid value provided!');
			return ;
		}
		//clear all error msg
		tmp.currentRow.getElementsBySelector('.form-group.has-error .form-control').each(function(control){
			$(control).retrieve('clearErrFunc')();
		});
		//get all data
		tmp.data = {
			'product': tmp.product, 
			'unitPrice': tmp.me.getCurrency(tmp.unitPrice), 
			'qtyOrdered': tmp.qtyOrdered, 
			'totalPrice': tmp.me.getCurrency(tmp.totalPrice),
			'btns': new Element('span', {'class': 'pull-right'})
				.insert({'bottom': new Element('span', {'class': 'btn btn-danger btn-xs'})
				.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-trash'}) })
				.observe('click', function() {
					if(!confirm('You remove this entry.\n\nContinue?'))
						return;
					tmp.row = $(this).up('.item_row');
					console.debug(tmp.row);
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
		tmp.currentRow.insert({'after': tmp.me._getProductRow(tmp.data).addClassName('btn-hide-row') });
		tmp.newRow = tmp.me._getNewProductRow();
		tmp.currentRow.replace(tmp.newRow);
		tmp.newRow.down('[new-order-item=product]').focus();
		
		tmp.me._recalculateSummary( tmp.totalPrice );
		return tmp.me;
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
		tmp.me.postAjax(tmp.me.getCallbackId('searchProduct'), {'searchTxt': tmp.searchTxt, 'supplierID': tmp.me._supplier.id}, {
			'onLoading': function() {
				jQuery('#' + tmp.btn.id).button('loading');
			}
			,'onSuccess': function(sender, param) {
				tmp.resultList = new Element('div', {'style': 'overflow: auto; max-height: 400px;'});
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result || !tmp.result.items || tmp.result.items.size() === 0)
						throw 'Nothing Found for: ' + tmp.searchTxt;
					tmp.me._signRandID(tmp.searchTxtBox);
					tmp.result.items.each(function(product) {
						tmp.resultList.insert({'bottom': tmp.me._getSearchPrductResultRow(product, tmp.searchTxtBox) });
					});
					tmp.resultList.addClassName('list-group'); 
				} catch(e) {
					tmp.resultList.update(tmp.me.getAlertBox('Error: ', e).addClassName('alert-danger'));
				}
				tmp.me.showModalBox('Products that has: ' + tmp.searchTxt, tmp.resultList, false);
			}
			,'onComplete': function(sender, param) {
				jQuery('#' + tmp.btn.id).button('reset');
			}
		});
		return tmp.me;
	}
	/**
	 * Getting the search product result row
	 */
	,_getSearchPrductResultRow: function(product, searchTxtBox) {
		var tmp = {};
		tmp.me = this;
		tmp.defaultImgSrc = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI2NCIgaGVpZ2h0PSI2NCI+PHJlY3Qgd2lkdGg9IjY0IiBoZWlnaHQ9IjY0IiBmaWxsPSIjZWVlIi8+PHRleHQgdGV4dC1hbmNob3I9Im1pZGRsZSIgeD0iMzIiIHk9IjMyIiBzdHlsZT0iZmlsbDojYWFhO2ZvbnQtd2VpZ2h0OmJvbGQ7Zm9udC1zaXplOjEycHg7Zm9udC1mYW1pbHk6QXJpYWwsSGVsdmV0aWNhLHNhbnMtc2VyaWY7ZG9taW5hbnQtYmFzZWxpbmU6Y2VudHJhbCI+NjR4NjQ8L3RleHQ+PC9zdmc+';
		tmp.newRow = new Element('a', {'class': 'list-group-item', 'href': 'javascript: void(0);'})
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('div', {'class': 'col-xs-2'})
					.insert({'bottom': new Element('div', {'class': 'thumbnail'})
						.insert({'bottom': new Element('img', {'data-src': 'holder.js/100%x64', 'alert': 'Product Image', 'src': product.images.size() === 0 ? tmp.defaultImgSrc : product.images[0].asset.url}) })
					})
				})
				.insert({'bottom': new Element('div', {'class': 'col-xs-10'})
					.insert({'bottom': new Element('div', {'class': 'row'})
						.insert({'bottom': new Element('strong').update(product.name)
							.insert({'bottom': new Element('small', {'class': 'pull-right'}).update('SKU: ' + product.sku) })
						})
						.insert({'bottom': new Element('div')
							.insert({'bottom': new Element('small').update(product.shortDescription) })
						})
					})
					.insert({'bottom': new Element('div', {'class': 'row', 'style': (product.minProductPrice || product.lastSupplierPrice || product.minSupplierPrice) ? 'height: 2px; background-color: brown;' : 'display:none'}).update('&nbsp;') })
					.insert({'bottom': new Element('div', {'class': 'row small'})
						.insert({'bottom': new Element('span', {'style': product.minProductPrice ? '': 'display:none'}).update('Minimum product price: ')
							.insert({'bottom': new Element('strong').update(tmp.me.getCurrency(product.minProductPrice)) })
						})
					})
					.insert({'bottom': new Element('div', {'class': 'row small'})
						.insert({'bottom': new Element('span', {'style': product.lastSupplierPrice ? '': 'display:none'}).update('Last supplier price: ')
							.insert({'bottom': new Element('strong').update(tmp.me.getCurrency(product.lastSupplierPrice)) })
						})
					})
					.insert({'bottom': new Element('div', {'class': 'row small'})
						.insert({'bottom': new Element('span', {'style': product.minSupplierPrice ? '': 'display:none'}).update('Minimum supplier price: ')
							.insert({'bottom': new Element('strong').update(tmp.me.getCurrency(product.minSupplierPrice)) })
						})
					})
					
				})
			})
			.observe('click', function(){
				tmp.inputRow = $(searchTxtBox).up('.new-order-item-input').store('product', product);
				searchTxtBox.up('.productName')
					.writeAttribute('colspan', false)
					.update(product.sku)
					.insert({'after': new Element('td')
						.update(product.name) 
						.insert({'bottom': new Element('a', {'href': 'javascript: void(0);', 'class': 'text-danger pull-right', 'title': 'click to change the product'}) 
							.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-remove'})  })
							.observe('click', function() {
								tmp.newRow = tmp.me._getNewProductRow();
								$(this).up('.new-order-item-input').replace(tmp.newRow);
								tmp.newRow.down('[new-order-item=product]').select();
							})
						})
					});
				jQuery('#' + tmp.me.modalId).modal('hide');
				tmp.retailPrice = product.prices.size() === 0 ? 0 : product.prices[0].price;
				tmp.inputRow.down('[new-order-item=totalPrice]').writeAttribute('value', tmp.me.getCurrency(tmp.retailPrice));
				tmp.inputRow.down('[new-order-item=unitPrice]').writeAttribute('value', tmp.me.getCurrency(tmp.retailPrice)).select();
			})
			;
		return tmp.newRow;
	}
	/**
	 * Getting the save btn for this order
	 */
	,_saveBtns: function() {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = new Element('span', {'class': 'btn-group'})
			.insert({'bottom': new Element('span', {'class': 'btn btn-primary'})
				.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-ok-circle'}) })
				.insert({'bottom': new Element('span').update(' save ') })
				.observe('click', function() {
					tmp.me._submitOrder();
				})
			})
			.insert({'bottom': new Element('span', {'class': 'btn btn-default'})
				.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-remove-sign'}) })
				.insert({'bottom': new Element('span').update(' cancel ') })
				.observe('click', function(){
					tmp.me.showModalBox('<strong class="text-danger">Cancelling the current order</strong>', 
							'<div>You are about to cancel this new order, all input data will be lost.</div><br /><div>Continue?</div>'
							+ '<div>'
								+ '<span class="btn btn-primary" onclick="window.location = document.URL;"><span class="glyphicon glyphicon-ok"></span> YES</span>'
								+ '<span class="btn btn-default pull-right" data-dismiss="modal"><span aria-hidden="true"><span class="glyphicon glyphicon-remove-sign"></span> NO</span></span>'
							+ '</div>',
					true);
				})
			})
		;
		return tmp.newDiv;
	}
	,_submitOrder: function() {
		var tmp = {};
		tmp.me = this;
		tmp.data = tmp.me._collectFormData($(tmp.me._htmlIds.itemDiv),'save-order');
		if(tmp.data === null)
			return tmp.me;
		tmp.data.newItems = [];
		tmp.data.removedOldItems = [];
		$$('.order-item-row').each(function(item){
			tmp.item = item.retrieve('data');
			tmp.item.totalPrice = tmp.item.totalPrice ? tmp.me.getValueFromCurrency(tmp.item.totalPrice) : '';
			tmp.item.unitPrice = tmp.item.unitPrice ? tmp.me.getValueFromCurrency(tmp.item.unitPrice) : '';
			console.debug(item);
			console.debug(item.hasClassName('order-item-row-old order-item-row-old-removed'));
			if (item.hasClassName('order-item-row-old order-item-row-old-removed'))
				tmp.data.removedOldItems.push(tmp.item);
			else if (!item.hasClassName('order-item-row-old'))
				tmp.data.newItems.push(tmp.item);
		});
//		if(tmp.data.items.size() <= 0) {
//			tmp.me.showModalBox('<strong class="text-danger">Error</strong>', 'At least one order item is needed!', true);
//			return tmp.me;
//		}
		tmp.data.id = tmp.me._purchaseorder.id;
		tmp.data.supplier = tmp.me._supplier;
		tmp.data.totalAmount = tmp.data.totalAmount ? tmp.me.getValueFromCurrency(tmp.data.totalAmount) : '';
		console.debug(tmp.data);
		tmp.me.postAjax(tmp.me.getCallbackId('saveOrder'), tmp.data, {
		});
		return tmp.me;
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
	 * Getting the form group
	 */
	,_getFormGroup: function(title, content) {
		return new Element('div', {'class': 'form-group'})
			.insert({'bottom': title ? new Element('label', {'class': 'control-label'}).update(title) : '' })
			.insert({'bottom': content.addClassName('form-control') });
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