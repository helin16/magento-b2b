/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new BPCPageJs(), {
	_htmlIds: {'paymentPanel': 'payment_panel', 'itemDiv': '', 'searchPanel': 'search_panel', 'totalPriceExcludeGST': 'total_price_exclude_gst', 'totalPriceGST': 'total_price_gst', 'totalPriceIncludeGST': 'total_price_include_gst', 'totalPaidAmount': 'total-paid-amount', 'totalShippingCost': 'total-shipping-cost'}
	,_supplier: null
	/**
	 * Setting the HTMLIDS
	 */
	,setHTMLIDs: function(itemDivId) {
		this._htmlIds.itemDiv = itemDivId;
		return this;
	}
	/**
	 * setting the payment methods
	 */
	,setPaymentMethods: function(paymentMethods) {
		this._paymentMethods = paymentMethods;
		return this;
	}
	/**
	 * setting the payment methods
	 */
	,setShippingMethods: function(shippingMethods) {
		this._shippingMethods = shippingMethods;
		return this;
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
	 * Getting the form group
	 */
	,_getFormGroup: function(title, content) {
		return new Element('div', {'class': 'form-group'})
			.insert({'bottom': title ? new Element('label', {'class': 'control-label'}).update(title) : '' })
			.insert({'bottom': content.addClassName('form-control') });
	}
	,_submitOrder: function(btn) {
		var tmp = {};
		tmp.me = this;
		tmp.btn = btn;
		tmp.data = tmp.me._collectFormData($(tmp.me._htmlIds.itemDiv),'save-order');
		if(tmp.data === null)
			return tmp.me;
		tmp.data.items = [];
		$$('.order-item-row').each(function(item){
			tmp.item = item.retrieve('data');
			tmp.item.totalPrice = tmp.item.totalPrice ? tmp.me.getValueFromCurrency(tmp.item.totalPrice) : '';
			tmp.item.unitPrice = tmp.item.unitPrice ? tmp.me.getValueFromCurrency(tmp.item.unitPrice) : '';
			tmp.data.items.push(tmp.item);
		});
		if(tmp.data.items.size() <= 0) {
			tmp.me.showModalBox('<strong class="text-danger">Error</strong>', 'At least one order item is needed!', true);
			return tmp.me;
		}
		tmp.data.supplier = tmp.me._supplier;
		tmp.data.totalAmount = tmp.data.totalAmount ? tmp.me.getValueFromCurrency(tmp.data.totalAmount) : '';
		tmp.me._signRandID(tmp.btn);
		tmp.me.postAjax(tmp.me.getCallbackId('saveOrder'), tmp.data, {
			'onLoading': function(sender, param) {
				jQuery('#' + tmp.btn.id).button('loading');
			}
			,'onSuccess': function(sender, param) {
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result || !tmp.result.item)
						return;
					tmp.me._item = tmp.result.item;
					tmp.me.refreshParentWindow();
					window.parent.jQuery.fancybox.close();
				} catch(e) {
					tmp.me.showModalBox('Error!', e, false);
				}
			}
			,'onComplete': function(sender, param) {
				jQuery('#' + tmp.btn.id).button('reset');
			}
		});
		return tmp.me;
	}
	,refreshParentWindow: function() {
		var tmp = {};
		tmp.me = this;
		if(!window.parent)
			return;
		tmp.parentWindow = window.parent;
		tmp.row = $(tmp.parentWindow.document.body).down('table#item-list tbody').insert({'top': tmp.parentWindow.pageJs._getResultRow(tmp.me._item).addClassName('success')});
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
					tmp.me._submitOrder(this);
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
	/**
	 * getting the customer information div
	 */
	,_getSupplierInfoPanel: function() {
		var tmp = {};
		tmp.me = this;
		tmp.supplier = tmp.me._supplier;
		tmp.newDiv = new Element('div', {'class': 'panel panel-info'})
			.insert({'bottom': new Element('div', {'class': 'panel-heading'})
				.insert({'bottom': new Element('strong').update('Creating purchase order for: ' + tmp.supplier.name + ' ') })
				.insert({'bottom': new Element('div', {'class': 'pull-right'})
					.insert({'bottom': new Element('strong').update('Status: ') })
					.insert({'bottom': tmp.me._getOrderStatus() })
				})
			})
			.insert({'bottom': new Element('div', {'class': 'panel-body'})
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('div', {'class': 'col-sm-3'}).update(tmp.me._getFormGroup('Name', new Element('input', {'disabled': 'disabled', 'type': 'text', 'value': tmp.supplier.name ? tmp.supplier.name : ''}) ) ) })
					.insert({'bottom': new Element('div', {'class': 'col-sm-3'}).update(tmp.me._getFormGroup('Contact Name', new Element('input', {'save-order': 'contactName', 'type': 'text', 'value': tmp.supplier.contactName ? tmp.supplier.contactName : ''}) ) ) })
					.insert({'bottom': new Element('div', {'class': 'col-sm-3'}).update(tmp.me._getFormGroup('Contact Number', new Element('input', {'save-order': 'contactNo', 'type': 'value', 'value': tmp.supplier.contactNo ? tmp.supplier.contactNo : ''}) ) ) })
					.insert({'bottom': new Element('div', {'class': 'col-sm-3'}).update(tmp.me._getFormGroup('Supplier Ref Num', new Element('input', {'required': 'required', 'save-order': 'supplierRefNum', 'type': 'text', 'value': ''}) ) ) })
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
		tmp.selBox = new Element('select', {'save-order': 'status'});
		tmp.me._statusOptions.each(function(status) {
			tmp.selBox.insert({'bottom': new Element('option').update(status) });
		});
		return tmp.selBox;
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
							.insert({'bottom': new Element('small', {'class': '', 'style': 'padding-left: 10px;'}).update('SKU: ' + product.sku) })
						})
						.insert({'bottom': new Element('div')
							.insert({'bottom': new Element('small').update(product.shortDescription) })
						})
					})
					.insert({'bottom': new Element('div', {'class': 'row', 'style': (product.minProductPrice || product.lastSupplierPrice || product.minSupplierPrice) ? 'height: 2px; background-color: brown;' : 'display:none'}).update('&nbsp;') })
					.insert({'bottom': new Element('div', {'class': 'row small'})
						.insert({'bottom': new Element('span', {'class': 'btn btn-link btn-xs', 'style': product.minProductPrice ? '': 'display:none'}).update('Minimum product price: ')
							.insert({'bottom': new Element('strong').update(tmp.me.getCurrency(product.minProductPrice)) })
						})
						.observe('click', function(event) {
							tmp.me._openPOPage(product.minProductPriceId);
						})
					})
					.insert({'bottom': new Element('div', {'class': 'row small'})
						.insert({'bottom': new Element('span', {'class': 'btn btn-link btn-xs', 'style': product.lastSupplierPrice ? '': 'display:none'}).update('Last supplier price: ')
							.insert({'bottom': new Element('strong').update(tmp.me.getCurrency(product.lastSupplierPrice)) })
						})
						.observe('click', function(event) {
							tmp.me._openPOPage(product.lastSupplierPriceId);
						})
					})
					.insert({'bottom': new Element('div', {'class': 'row small'})
						.insert({'bottom': new Element('span', {'class': 'btn btn-link btn-xs', 'style': product.minSupplierPrice ? '': 'display:none'}).update('Minimum supplier price: ')
							.insert({'bottom': new Element('strong').update(tmp.me.getCurrency(product.minSupplierPrice)) })
						})
						.observe('click', function(event) {
							tmp.me._openPOPage(product.minSupplierPriceId);
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
				tmp.inputRow.down('[new-order-item=totalPrice]').writeAttribute('value', tmp.me.getCurrency(product.minProductPrice));
				tmp.inputRow.down('[new-order-item=unitPrice]').writeAttribute('value', tmp.me.getCurrency(product.minProductPrice)).select();
			})
			;
		return tmp.newRow;
	}
	/**
	 * Open PO page in a fancybox
	 */
	,_openPOPage: function(id) {
		var tmp = {};
		tmp.me = this;
		tmp.newWindow = window.open('/purchase/' + id + '.html', 'Product Details', 'location=no, menubar=no, status=no, titlebar=no, fullscreen=yes, toolbar=no');
		tmp.newWindow.focus();
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
	,_recalculateSummary: function(amount) {
		var tmp = {};
		tmp.me = this;
		tmp.totalIncGSTBox = $(tmp.me._htmlIds.totalPriceIncludeGST);
		tmp.totalGSTBox = $(tmp.me._htmlIds.totalPriceGST);
		tmp.totalExcGSTBox = $(tmp.me._htmlIds.totalPriceExcludeGST);
		
		tmp.totalExcGST = tmp.me.getValueFromCurrency(tmp.totalExcGSTBox.innerHTML) * 1  + amount * 1;
		tmp.totalIncGST = tmp.totalExcGST ? (tmp.totalExcGST * 1 * 1.1) : 0;
		
		tmp.totalGST = tmp.totalExcGST ? (tmp.totalIncGST * 1 - tmp.totalExcGST * 1) : 0;
		
		tmp.totalIncGSTBox.update(tmp.me.getCurrency(tmp.totalIncGST));
		tmp.totalGSTBox.update(tmp.me.getCurrency(tmp.totalGST));
		tmp.totalExcGSTBox.update(tmp.me.getCurrency(tmp.totalExcGST));
		
		
		tmp.totalPaidAmount = ($(tmp.me._htmlIds.totalPaidAmount) ? tmp.me.getValueFromCurrency($F(tmp.me._htmlIds.totalPaidAmount)) : 0);
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
		$$('#'+tmp.me._htmlIds.paymentPanel).first().down('[save-order]="totalAmount"[disabled]="disabled"').value = tmp.me.getCurrency(tmp.totalExcGST);
		return tmp.me;
	}
	/**
	 * adding a new product row after hit save btn
	 */
	,_addNewProductRow: function(btn) {
		var tmp = {};
		tmp.me = this;
		tmp.currentRow = $(btn).up('.new-order-item-input');
		tmp.product = tmp.currentRow.retrieve('product');
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
		tmp.unitPrice = tmp.me.getValueFromCurrency($F(tmp.unitPriceBox));
		if(tmp.unitPrice.match(/^\d+(\.\d{1,2})?$/) === null) {
			tmp.me._markFormGroupError(tmp.unitPriceBox, 'Invalid value provided!');
			return ;
		}
		tmp.qtyOrderedBox = tmp.currentRow.down('[new-order-item=qtyOrdered]');
		tmp.qtyOrdered = tmp.me.getValueFromCurrency($F(tmp.qtyOrderedBox));
		if(tmp.qtyOrdered.match(/^\d+(\.\d{1,2})?$/) === null) {
			tmp.me._markFormGroupError(tmp.qtyOrderedBox, 'Invalid value provided!');
			return ;
		}
		tmp.totalPriceBox = tmp.currentRow.down('[new-order-item=totalPrice]');
		tmp.totalPrice = tmp.me.getValueFromCurrency($F(tmp.totalPriceBox));
		if(tmp.totalPrice.match(/^\d+(\.\d{1,2})?$/) === null) {
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
					tmp.me._recalculateSummary( 0 - tmp.me.getValueFromCurrency(tmp.row.retrieve('data').totalPrice) * 1 );
					tmp.row.remove();
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
					.insert({'bottom': tmp.me._getFormGroup( 'Comments:', new Element('textarea', {'save-order': 'comments'}) ) })
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
		return new Element('div', {'class': 'panel panel-info'})
			.insert({'bottom': new Element('div', {'class': 'panel-body table-responsive'})
				.insert({'bottom':  tmp.productListDiv})
			});
	}
	,_getPaymentPanel: function () {
		var tmp = {};
		tmp.me = this;
		tmp.supplier = tmp.me._supplier;
		tmp.shippingCostEl = new Element('input', {'class': 'text-right', 'id': 'shipping_cost', 'save-order': 'shippingCost'});
		tmp.handlingCostEl = new Element('input', {'class': 'text-right', 'id': 'handling_cost', 'save-order': 'handlingCost'});
		tmp.totalAmountExGstEl = new Element('input', {'class': 'text-right', 'disabled': 'disabled', 'save-order': 'totalAmount'});
		tmp.totalPaidEl = new Element('input', {'class': 'text-right', 'id': tmp.me._htmlIds.totalPaidAmount, 'save-order': 'totalPaid'})
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

		tmp.newDiv = new Element('div', {'class': 'panel panel-info', 'id': tmp.me._htmlIds.paymentPanel})
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
	 * Getting the div of the order view
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
				.insert({'bottom': new Element('div', {'class': 'col-sm-12'}).update(tmp.me._saveBtns()) })
			})
		return tmp.newDiv;
	}
	,selectSupplier: function(supplier) {
		var tmp = {};
		tmp.me = this;
		tmp.me._supplier = supplier;
		tmp.newDiv = tmp.me._getViewOfPurchaseOrder();
		$(tmp.me._htmlIds.itemDiv).update(tmp.newDiv);return;
		tmp.newDiv.down('.new-order-item-input [new-order-item="product"]').focus();
		return tmp.me;
	}
	/**
	 * Getting the PO row for displaying the searching result
	 */
	,_getPORow: function(po, isTitle) {
		var tmp = {};
		tmp.me = this;
//		console.debug(po);
		tmp.isTitle = (isTitle || false);
		tmp.tag = (tmp.isTitle === true ? 'th': 'td');
		tmp.newDiv = new Element('tr', {'class': (tmp.isTitle === true ? 'item_top_row' : 'btn-hide-row item_row') + (po.active == 0 ? ' danger' : ''), 'item_id': (tmp.isTitle === true ? '' : po.id)}).store('data', po)
			.insert({'bottom': new Element(tmp.tag)
				.insert({'bottom': (tmp.isTitle === true ? '&nbsp;':
					new Element('span', {'class': 'btn btn-primary btn-xs'}).update('select')	
					.observe('click', function(){
						tmp.me.selectSupplier(supplier);
					})
				) })
			})
			.insert({'bottom': new Element(tmp.tag).update(po.purchaseOrderNo) })
			.insert({'bottom': new Element(tmp.tag).update(po.supplier.name) })
			.insert({'bottom': new Element(tmp.tag).update(po.supplierRefNo) })
			.insert({'bottom': new Element(tmp.tag).update(po.orderDate) })
			.insert({'bottom': new Element(tmp.tag).update(po.totalAmount) })
			.insert({'bottom': new Element(tmp.tag).update(po.totalProdcutCount) })
			.insert({'bottom': new Element(tmp.tag)
				.insert({'bottom': (tmp.isTitle === true ? po.active : new Element('input', {'type': 'checkbox', 'disabled': true, 'checked': po.active}) ) })
			})
		;
		return tmp.newDiv;
	}
	/**
	 * Ajax: searching the customer
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
								.insert({'bottom': tmp.me._getPORow({'purchaseOrderNo': 'PO Number', 'supplier': {'name': 'Supplier'} , 'supplierRefNo': 'Supplier Ref', 'orderDate': 'Order Date', 'totalAmount': 'Total Amount', 'totalProdcutCount': 'Total Prodcut Count', 'active': 'Active?'}, true)  })
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
	 * Getting the customer list panel
	 */
	,_getPOListPanel: function () {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = new Element('div', {'id': tmp.me._htmlIds.searchPanel, 'class': 'panel panel-info search-panel'})
			.insert({'bottom': new Element('div', {'class': 'panel-heading form-inline'})
				.insert({'bottom': new Element('strong').update('Searching for PO: ') })
				.insert({'bottom': new Element('span', {'class': 'input-group col-sm-6'})
					.insert({'bottom': new Element('input', {'class': 'form-control search-txt init-focus', 'placeholder': 'any of PO number, Supplier, Supplier Ref Number ...'}) 
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
	,init: function(supplier) {
		var tmp = {};
		tmp.me = this;
		$(tmp.me._htmlIds.itemDiv).update(tmp.me._getPOListPanel());
		if($$('.init-focus').size() > 0){
			$$('.init-focus').first().focus();
		}
		return tmp.me;
	}
});


//TODO: link to all min prices to PO in new window