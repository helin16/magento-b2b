/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new BPCPageJs(), {
	_htmlIds: {'itemDiv': '', 'searchPanel': 'search_panel', 'totalPriceExcludeGST': 'total_price_exclude_gst', 'totalPriceGST': 'total_price_gst', 'totalPriceIncludeGST': 'total_price_include_gst', 'shippingCostIncludeGST': 'shipping_cost_include_gst', 'shippingCostGST': 'shipping_cost_gst', 'totalPaidAmount': 'total-paid-amount', 'totalShippingCost': 'total-shipping-cost'}
	,_customer: null
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
	 * Getting the form group
	 */
	,_getFormGroup: function(title, content) {
		return new Element('div', {'class': 'form-group'})
			.insert({'bottom': title ? new Element('label', {'class': 'control-label'}).update(title) : '' })
			.insert({'bottom': content.addClassName('form-control') });
	}
	,_submitOrder: function() {
		var tmp = {};
		tmp.me = this;
		tmp.data = tmp.me._collectFormData($(tmp.me._htmlIds.itemDiv),'save-order');
		if(tmp.data === null)
			return tmp.me;
		tmp.data.items = [];
		$$('.order-item-row').each(function(item){
			tmp.array = item.retrieve('data');
			tmp.array.serials = item.retrieve('serials');
			tmp.data.items.push(tmp.array);
		});
		if(tmp.data.items.size() <= 0) {
			tmp.me.showModalBox('<strong class="text-danger">Error</strong>', 'At least one order item is needed!', true);
			return tmp.me;
		}
		tmp.data.customer = tmp.me._customer;
		tmp.data.items.each(function(item){
			item.totalPrice = tmp.me.getValueFromCurrency(item.totalPrice);
			item.unitPrice = tmp.me.getValueFromCurrency(item.unitPrice);
		});
		tmp.btn = $$('.save-btn').first();
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
					
					tmp.me.showModalBox('<strong class="text-success">Success!</strong>', 
							'<div>The new order is succussfully added.</div><br /><div><strong>Go to Order Details Page?</strong></div>'
							+ '<div>'
								+ '<span class="btn btn-primary" onclick="window.location = ' + "'"
								+ '/orderdetails/' + tmp.result.item.id + '.html' + "'"
								+ ';"><span class="glyphicon glyphicon-ok"></span> YES</span>'
								+ '<span class="btn btn-default pull-right" data-dismiss="modal" onclick="window.location = ' + "'"
								+ '/order/new.html' + "'"
								+ ';"><span aria-hidden="true"><span class="glyphicon glyphicon-remove-sign"></span> NO</span></span>'
							+ '</div>',
					true);
					
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
	/**
	 * Getting the save btn for this order
	 */
	,_saveBtns: function() {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = new Element('span', {'class': 'btn-group'})
			.insert({'bottom': new Element('span', {'class': 'btn btn-primary save-btn'})
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
	/**
	 * Getting the address div
	 */
	,_getAddressDiv: function(title, addr) {
		return new Element('div', {'class': 'address-div'})
			.insert({'bottom': new Element('strong').update(title) })
			.insert({'bottom': new Element('dl', {'class': 'dl-horizontal dl-condensed'})
				.insert({'bottom': new Element('dt')
					.update(new Element('span', {'class': "glyphicon glyphicon-user", 'title': "Customer Name"}) ) 
				})
				.insert({'bottom': new Element('dd').update(addr.contactName) })
				.insert({'bottom': new Element('dt')
					.update(new Element('span', {'class': "glyphicon glyphicon-map-marker", 'title': "Address"}) ) 
				})
				.insert({'bottom': new Element('dd')
					.insert({'bottom': new Element('div')
						.insert({'bottom': new Element('div', {'class': 'street inlineblock'}).update(addr.street) })
						.insert({'bottom': new Element('span', {'class': 'city inlineblock'}).update(addr.city + ' ') })
						.insert({'bottom': new Element('span', {'class': 'region inlineblock'}).update(addr.region + ' ') })
						.insert({'bottom': new Element('span', {'class': 'postcode inlineblock'}).update(addr.postCode) })
					})
				})
			})
	}
	/**
	 * getting the customer information div
	 */
	,_getCustomerInfoPanel: function() {
		var tmp = {};
		tmp.me = this;
		tmp.customer = tmp.me._customer;
		tmp.newDiv = new Element('div', {'class': 'panel panel-success'})
			.insert({'bottom': new Element('div', {'class': 'panel-heading'})
				.insert({'bottom': new Element('strong').update('Creating order for: ' + tmp.customer.name + ' ') })
				.insert({'bottom': ' <' })
				.insert({'bottom': new Element('a', {'href': 'mailto:' + tmp.customer.email}).update(tmp.customer.email) })
				.insert({'bottom': '>' })
			})
			.insert({'bottom': new Element('div', {'class': 'panel-body'})
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': tmp.me._getAddressDiv("Billing Address: ", tmp.customer.address.billing).addClassName('col-xs-6') })
					.insert({'bottom': tmp.me._getAddressDiv("Shipping Address: ", tmp.customer.address.shipping).addClassName('col-xs-6') })
				 })
			});
		return tmp.newDiv;
	}
	/**
	 * Getting each product row
	 */
	,_getProductRow: function(orderItem, isTitleRow) {
		var tmp = {};
		tmp.me = this;
		tmp.isTitle = (isTitleRow || false);
		tmp.tag = (tmp.isTitle === true ? 'strong' : 'div');
		tmp.row = new Element('div', {'class': ' list-group-item ' + (tmp.isTitle === true ? '' : 'item_row order-item-row')})
			.store('data',orderItem)
			.insert({'bottom': new Element('div', {'class': 'row'})
				.store('data', orderItem)
				.insert({'bottom': new Element(tmp.tag, {'class': 'productName col-xs-7'})
					.insert({'bottom': orderItem.product.name })
					.insert({'bottom': new Element('small', {'class': orderItem.product.id ? 'btn btn-xs btn-info' : 'hidden'})
							.insert({'bottom': new Element('small', {'class': 'glyphicon glyphicon-new-window'} )})
						})
					.observe('click', function(event){
						Event.stop(event);
						$productId = orderItem.product.id;
						if($productId)
							tmp.me._openProductDetailPage($productId);
					})
				})
				.insert({'bottom': new Element(tmp.tag, {'class': 'uprice col-xs-1'})
					.insert({'bottom': (orderItem.unitPrice) })
				})
				.insert({'bottom': new Element(tmp.tag, {'class': 'qty col-xs-1'})
					.insert({'bottom': (orderItem.qtyOrdered) })
				})
				.insert({'bottom': new Element(tmp.tag, {'class': 'discount col-xs-1'})
					.insert({'bottom': (orderItem.discount) })
				})
				.insert({'bottom': new Element(tmp.tag, {'class': 'tprice col-xs-1'})
					.insert({'bottom': (orderItem.totalPrice) })
				})
				.insert({'bottom': new Element(tmp.tag, {'class': 'btns  col-xs-1'}).update(orderItem.btns ? orderItem.btns : '') })
			});
		if(orderItem.product.sku) {
			tmp.row.down('.productName')
				.removeClassName('col-xs-7')
				.addClassName('col-xs-5')
				.insert({'before': new Element(tmp.tag, {'class': 'productSku col-xs-2'}).update(orderItem.product.sku) });
		}
		if(orderItem.scanTable) {
			tmp.row.insert({'bottom': new Element('div', {'class': 'row product-content-row'})
				.insert({'bottom': new Element('span', {'class': 'col-sm-10 col-sm-offset-2', 'style': 'padding-top: 5px'}).update(orderItem.scanTable) })
			});
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
		tmp.newRow = new Element('a', {'class': 'list-group-item search-product-result-row', 'href': 'javascript: void(0);'})
			.store('data',product)
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('div', {'class': 'col-xs-2'})
					.insert({'bottom': new Element('div', {'class': 'thumbnail'})
						.insert({'bottom': new Element('img', {'data-src': 'holder.js/100%x64', 'alert': 'Product Image', 'src': product.images.size() === 0 ? tmp.defaultImgSrc : product.images[0].asset.url}) })
					})
				})
				.insert({'bottom': new Element('div', {'class': 'col-xs-10'})
					.insert({'bottom': new Element('strong').update(product.name)
						.insert({'bottom': new Element('small', {'class': 'btn btn-xs btn-info'})
							.insert({'bottom': new Element('small', {'class': 'glyphicon glyphicon-new-window'} )})
						})
						.observe('click', function(event){
							Event.stop(event);
							$productId = $(this).up('.search-product-result-row').retrieve('data').id;
							if($productId)
								tmp.me._openProductDetailPage($productId);
						})
						.insert({'bottom': new Element('small', {'class': 'pull-right'}).update('SKU: ' + product.sku) })
					})
					.insert({'bottom': new Element('div')
						.insert({'bottom': new Element('small').update(product.shortDescription) })
					})
					.insert({'bottom': new Element('div')
						.insert({'bottom': new Element('small', {'class': 'col-xs-4'})
							.insert({'bottom': new Element('span', {'style': ' font-style: italic'}).update('Stock on Hand: ') })
							.insert({'bottom': new Element('strong').update(product.stockOnHand) })
						})
						.insert({'bottom': new Element('small', {'class': 'col-xs-4'})
							.insert({'bottom': new Element('span', {'style': ' font-style: italic'}).update('Stock on Order: ') })
							.insert({'bottom': new Element('strong').update(product.stockOnOrder) })
						})
						.insert({'bottom': new Element('small', {'class': 'col-xs-4'})
							.insert({'bottom': new Element('span', {'style': ' font-style: italic'}).update('Stock on PO: ') })
							.insert({'bottom': new Element('strong').update(product.stockOnPO) })
						})
					})
				})
			})
			.observe('click', function(){
				tmp.inputRow = $(searchTxtBox).up('.new-order-item-input').store('product', product);
				searchTxtBox.up('.productName')
					.writeAttribute('colspan', false)
					.update(product.sku)
					.removeClassName('col-xs-8')
					.addClassName('col-xs-2')
					.insert({'after': new Element('div', {'class': 'col-xs-5'})
						.update(product.name) 
						.insert({'bottom': new Element('small', {'class': 'btn btn-xs btn-info'})
							.insert({'bottom': new Element('small', {'class': 'glyphicon glyphicon-new-window'} )})
							.observe('click', function(event){
								Event.stop(event);
								$productId = product.id;
								if($productId)
									tmp.me._openProductDetailPage($productId);
							})
						})
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
	 * Ajax: searching the product based on a string
	 */
	,_searchProduct: function(btn) {
		var tmp = {};
		tmp.me = this;
		tmp.btn = btn;
		tmp.me._signRandID(tmp.btn);
		tmp.searchTxtBox = $(tmp.btn).up('.product-autocomplete').down('.search-txt');
		tmp.me._signRandID(tmp.searchTxtBox);
		tmp.searchTxt = $F(tmp.searchTxtBox);
		tmp.me.postAjax(tmp.me.getCallbackId('searchProduct'), {'searchTxt': tmp.searchTxt}, {
			'onLoading': function() {
				jQuery('#' + tmp.btn.id).button('loading');
				jQuery('#' + tmp.searchTxtBox.id).button('loading');
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
				jQuery('#' + tmp.searchTxtBox.id).button('reset');
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
			.insert({'bottom': new Element('input', {'class': 'form-control search-txt visible-xs visible-sm visible-md visible-lg', 'new-order-item': 'product', 'required': true, 'placeholder': 'search SKU, NAME and any BARCODE for this product'})
				.observe('keydown', function(event){
					tmp.txtBox = this;
					tmp.me.keydown(event, function() {
						$(tmp.txtBox).up('.product-autocomplete').down('.search-btn').click();
					});
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
		tmp.shippingCostIncludeGSTBox = $(tmp.me._htmlIds.shippingCostIncludeGST);
		tmp.shippingCostGSTBox = $(tmp.me._htmlIds.shippingCostGST);
		tmp.totalShippingCostBox = $(tmp.me._htmlIds.totalShippingCost);
		
		tmp.totalIncGST = tmp.me.getValueFromCurrency(tmp.totalIncGSTBox.innerHTML) * 1 + amount * 1;
		tmp.totalExcGST = tmp.totalIncGST * 1 / 1.1;
		tmp.totalGST = tmp.totalIncGST * 1 - tmp.totalExcGST * 1;
		tmp.totalShippingCost = (tmp.totalShippingCostBox ? (tmp.me.getValueFromCurrency($F(tmp.totalShippingCostBox)) !== '' ? tmp.me.getValueFromCurrency($F(tmp.totalShippingCostBox)) : 0 ) : 0);
		tmp.shippingCostGST = tmp.totalShippingCost * 1 - (tmp.totalShippingCost * 1 / 1.1);
		
		tmp.totalIncGSTBox.update(tmp.me.getCurrency(tmp.totalIncGST));
		tmp.totalGSTBox.update(tmp.me.getCurrency(tmp.totalGST));
		tmp.totalExcGSTBox.update(tmp.me.getCurrency(tmp.totalExcGST));
		tmp.shippingCostIncludeGSTBox.update(tmp.me.getCurrency(tmp.totalShippingCost));
		tmp.shippingCostGSTBox.update(tmp.me.getCurrency(tmp.shippingCostGST));
		
		tmp.totalPaidAmount = ($(tmp.me._htmlIds.totalPaidAmount) ? tmp.me.getValueFromCurrency($F(tmp.me._htmlIds.totalPaidAmount)) : 0);
		tmp.totalPaymentDue = tmp.totalIncGST * 1 + tmp.totalShippingCost * 1 - tmp.totalPaidAmount * 1;
		$$('.total-payment-due').each(function(item) {
			tmp.newEl = new Element('strong', {'class': 'label'}).update(tmp.me.getCurrency(tmp.totalPaymentDue) + ' ');
			if(tmp.totalPaymentDue * 1 < 0) {
				tmp.newEl.addClassName('label-danger').writeAttribute('title', 'Customer over paid!')
					.insert({'bottom': new Element('span', {'class': ' glyphicon glyphicon-warning-sign'})});
			} else if (tmp.totalPaymentDue * 1 === 0) {
				tmp.newEl.addClassName('label-success')
					.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-ok'})});
			} else {
				tmp.newEl.addClassName('label-default');
			}
			item.update(tmp.newEl);
		})
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
		tmp.discountBox = tmp.currentRow.down('[new-order-item=discount]');
		tmp.discount = tmp.me.getValueFromCurrency($F(tmp.discountBox));
		if(tmp.discount.match(/^\d+(\.\d{1,2})?$/) === null) {
			tmp.me._markFormGroupError(tmp.discountBox, 'Invalid value provided!');
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
			'discount' : tmp.discount,
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
		
		tmp.data.scanTable = tmp.me._getScanTable(tmp.data);
		tmp.currentRow.insert({'after': tmp.itemRow = tmp.me._getProductRow(tmp.data) });
		
		
		tmp.newRow = tmp.me._getNewProductRow();
		tmp.currentRow.replace(tmp.newRow).addClassName();
		tmp.newRow.down('[new-order-item=product]').focus();
		
		tmp.me._recalculateSummary( tmp.totalPrice );
		return tmp.me;
	}
	,_getScanTable: function(item) {
		var tmp = {};
		tmp.me = this;
		tmp.item = item;
		tmp.newDiv = new Element('div', {'class': 'scanTable'});
		for(tmp.i = 0; tmp.i < item.qtyOrdered; tmp.i++) {
			tmp.newDiv.insert({'bottom': new Element('input', {'class': 'form-control', 'scanned-item': 'serialNo', 'type': 'text', 'placeholder': 'Serial Number:'})
				.observe('change', function() {
					tmp.emptyIput = null;
					tmp.serials = [];
					$(this).up('.scanTable').getElementsBySelector('input[scanned-item="serialNo"]').each(function(input){
						if(!$F(input).blank())
							tmp.serials.push($F(input));
						if(tmp.emptyIput === null && $F(input).blank())
							tmp.emptyIput = input;
					});
					$(this).up('.order-item-row').store('serials', tmp.serials);
					if(tmp.emptyIput !== null)
						tmp.emptyIput.select();
				})
				.wrap(new Element('div', {'class': 'col-sm-3'}))
			});
		}
		return tmp.newDiv;
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
			,'unitPrice': tmp.me._getFormGroup( null, new Element('input', {'class': 'input-sm', 'new-order-item': 'unitPrice', 'required': true, 'value': tmp.me.getCurrency(0)})
				.observe('change', function(){
					tmp.row =$(this).up('.item_row');
					tmp.unitPrice = tmp.me.getValueFromCurrency($F(this));
					tmp.discount = $F(tmp.row.down('[new-order-item=discount]'));
					tmp.qty = $F(tmp.row.down('[new-order-item=qtyOrdered]'));
					$(tmp.row.down('[new-order-item=totalPrice]')).value = tmp.me.getCurrency( tmp.unitPrice * (1 - tmp.discount/100) * tmp.qty);
				})
				.observe('click', function() {
					$(this).select();
				})
				.observe('keydown', function(event){
					tmp.txtBox = this;
					tmp.row = $(this).up('.item_row');
					tmp.me.keydown(event, function() {
						tmp.row.down('.save-new-product-btn').click();
					});
				})
				.observe('keyup', function(){
					tmp.row =$(this).up('.item_row');
					tmp.unitPrice = tmp.me.getValueFromCurrency($F(this));
					tmp.discount = $F(tmp.row.down('[new-order-item=discount]'));
					tmp.qty = $F(tmp.row.down('[new-order-item="qtyOrdered"]'));
					$(tmp.row.down('[new-order-item="totalPrice"]')).value = tmp.me.getCurrency( tmp.unitPrice * (1 - tmp.discount/100) * tmp.qty);
				})
			)
			,'qtyOrdered': tmp.me._getFormGroup( null, new Element('input', {'class': 'input-sm', 'new-order-item': 'qtyOrdered', 'required': true, 'value': '1'})
				.observe('keyup', function(){
					tmp.row =$(this).up('.item_row');
					tmp.unitPrice = tmp.me.getValueFromCurrency($F(tmp.row.down('[new-order-item=unitPrice]')));
					tmp.discount = $F(tmp.row.down('[new-order-item=discount]'));
					tmp.qty = $F(this);
					$(tmp.row.down('[new-order-item=totalPrice]')).value = tmp.me.getCurrency( tmp.unitPrice * (1 - tmp.discount/100) * tmp.qty);
				})
				.observe('keydown', function(event){
					tmp.txtBox = this;
					tmp.row = $(this).up('.item_row');
					tmp.me.keydown(event, function() {
						tmp.row.down('.save-new-product-btn').click();
					});
				})
				.observe('click', function() {
					$(this).select();
				})
			)
			,'discount': tmp.me._getFormGroup( null, new Element('input', {'class': 'input-sm', 'new-order-item': 'discount', 'value': '0'})
				.observe('keyup', function(){
					if($F($(this)).blank() || $F($(this)) > 100) {
						$(this).value = 0;
						$(this).select();
					}
					tmp.row =$(this).up('.item_row');
					tmp.unitPrice = tmp.me.getValueFromCurrency($F(tmp.row.down('[new-order-item=unitPrice]')));
					tmp.qty = $F(tmp.row.down('[new-order-item=qtyOrdered]'));
					tmp.discount = $F(this);
					$(tmp.row.down('[new-order-item=totalPrice]')).value = tmp.me.getCurrency( tmp.unitPrice * (1 - tmp.discount/100) * tmp.qty);
				})
				.observe('keydown', function(event){
					tmp.txtBox = this;
					tmp.row = $(this).up('.item_row');
					tmp.me.keydown(event, function() {
						tmp.row.down('.save-new-product-btn').click();
					});
				})
				.observe('click', function() {
					$(this).select();
				})
			)
			,'totalPrice': tmp.me._getFormGroup( null, new Element('input', {'class': 'input-sm', 'disabled': true, 'new-order-item': 'totalPrice', 'required': true, 'value': tmp.me.getCurrency(0)})
				.observe('change', function(){
					tmp.row =$(this).up('.item_row');
					tmp.totalPrice = tmp.me.getValueFromCurrency($F(this));
					tmp.qty = $F(tmp.row.down('[new-order-item=qtyOrdered]'));
					$(tmp.row.down('[new-order-item=unitPrice]')).value = tmp.me.getCurrency( tmp.totalPrice / tmp.qty );
				})
				.observe('click', function() {
					$(this).select();
				})
			)
			, 'btns': new Element('span', {'class': 'btn-group btn-group-sm pull-right'})
					.insert({'bottom': new Element('span', {'class': 'btn btn-primary'})
					.insert({'bottom': new Element('span', {'class': ' glyphicon glyphicon-floppy-saved save-new-product-btn'}) })
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
		return tmp.me._getProductRow(tmp.data, false).addClassName('new-order-item-input list-group-item-success').removeClassName('order-item-row');
	}
	/**
	 * Getting the parts panel
	 */
	,_getPartsTable: function () {
		var tmp = {};
		tmp.me = this;
		//header row
		tmp.productListDiv = new Element('div', {'class': 'list-group order_change_details_table'})
			.insert({'bottom': tmp.me._getProductRow({'product': {'sku': 'SKU', 'name': 'Description'}, 'unitPrice': 'Unit Price', 'qtyOrdered': 'Qty', 'discount': 'Discount %', 'totalPrice': 'Total Price'}, true)
			});
		// tbody
		tmp.productListDiv.insert({'bottom': tmp.me._getNewProductRow().addClassName('list-group-item-success') });
		// tfooter
		tmp.productListDiv.insert({'bottom': new Element('div', {'class': 'panel-footer'}) 
			.insert({'bottom': new Element('div', {'class': 'row'}) 
				.insert({'bottom': new Element('div', {'class': 'col-sm-8'})
						.insert({'bottom': tmp.me._getFormGroup( 'Comments:', new Element('textarea', {'save-order': 'comments'}) ) })
					}) 
				.insert({'bottom': new Element('div', {'class': 'col-sm-4'})
					.insert({'bottom': new Element('div')
						.insert({'bottom': new Element('div', {'class': 'col-xs-6 text-right active'}).update( new Element('strong').update('Total Excl. GST: ') ) }) 
						.insert({'bottom': new Element('div', {'class': 'col-xs-6 active', 'id': tmp.me._htmlIds.totalPriceExcludeGST}).update( tmp.me.getCurrency(0) ) }) 
					})
					.insert({'bottom': new Element('div') 
						.insert({'bottom': new Element('td', {'class': 'col-xs-6 text-right active'}).update( new Element('strong').update('Total GST: ') ) }) 
						.insert({'bottom': new Element('td', {'id': tmp.me._htmlIds.totalPriceGST, 'class': 'col-xs-6 active'}).update( tmp.me.getCurrency(0) ) }) 
					})
					.insert({'bottom': new Element('div') 
						.insert({'bottom': new Element('td', {'class': 'col-xs-6 text-right active'}).update( new Element('strong').update('Total Incl. GST: ') ) }) 
						.insert({'bottom': new Element('td', {'id': tmp.me._htmlIds.totalPriceIncludeGST, 'class': 'col-xs-6 active'}).update( tmp.me.getCurrency(0) ) })
					})
					.insert({'bottom': new Element('div') 
						.insert({'bottom': new Element('td', {'class': 'col-xs-6 text-right active', 'style': 'border-top: 1px solid brown'}).update( new Element('strong').update('Shipping Cost Inc: ') ) }) 
						.insert({'bottom': new Element('td', {'id': tmp.me._htmlIds.shippingCostIncludeGST, 'class': 'col-xs-6 active', 'style': 'border-top: 1px solid brown'}).update( tmp.me.getCurrency(0) ) })
					})
					.insert({'bottom': new Element('div') 
						.insert({'bottom': new Element('td', {'class': 'col-xs-6 text-right active'}).update( new Element('strong').update('Shipping GST: ') ) }) 
						.insert({'bottom': new Element('td', {'id': tmp.me._htmlIds.shippingCostGST, 'class': 'col-xs-6 active'}).update( tmp.me.getCurrency(0) ) })
					})
				})
			})
		});
		return tmp.productListDiv;
	}
	,_getPaymentPanel: function () {
		var tmp = {};
		tmp.me = this;
		tmp.paymentMethodSel = new Element('select', {'class': '', 'save-order': 'paymentMethodId'})
			.insert({'bottom': new Element('option', {'value': ''}).update('Payment Received via:') });
		tmp.me._paymentMethods.each(function(method){
			tmp.paymentMethodSel.insert({'bottom': new Element('option', {'value': method.id}).update(method.name) });
		})
		tmp.shippingMethodSel = new Element('select', {'class': '', 'save-order': 'courierId'})
			.insert({'bottom': new Element('option', {'value': ''}).update('Please Select:') });
		tmp.me._shippingMethods.each(function(method){
			tmp.shippingMethodSel.insert({'bottom': new Element('option', {'value': method.id}).update(method.name) });
		})
		
		tmp.newDiv = new Element('div', {'class': 'panel panel-success'})
			.insert({'bottom': new Element('div', {'class':'panel-heading'})
				.insert({'bottom': new Element('strong').update('Total Payment Due: ') })
				.insert({'bottom': new Element('span', {'class': 'pull-right total-payment-due'}).update(tmp.me.getCurrency(0) ) })
			})
			.insert({'bottom': new Element('div', {'class':'list-group'})
				.insert({'bottom': new Element('div', {'class': 'list-group-item'})
					.insert({'bottom': new Element('div', {'class': 'row'})
						.insert({'bottom': new Element('div', {'class': 'col-xs-6 text-right form-group', 'style': 'margin: 0px;'})
							.insert({'bottom': tmp.paymentMethodSel.addClassName('form-control input-sm')
								.observe('change', function() {
									tmp.btn = this;
									$(tmp.btn).up('.row').down('.input-field').update($F(tmp.btn).blank() ? '' : 
										tmp.paidAmountBox = new Element('input', {'id': tmp.me._htmlIds.totalPaidAmount, 'class': 'form-control input-sm', 'save-order': 'totalPaidAmount', 'placeholder': tmp.me.getCurrency(0), 'required': true, 'validate_currency': 'Invalid number provided!' })
										.observe('keyup', function() {
											tmp.me._recalculateSummary(0);
										})
									);
									tmp.me._recalculateSummary(0);
									if(tmp.paidAmountBox)
										tmp.paidAmountBox.select();
								})
							})
						})
						.insert({'bottom': new Element('div', {'class': 'col-xs-6 form-group input-field', 'style': 'margin: 0px;'}) })
					})
				})
				.insert({'bottom': new Element('div', {'class': 'list-group-item'})
					.insert({'bottom': new Element('div', {'class': 'row'})
						.insert({'bottom': new Element('div', {'class': 'col-xs-6 form-group', 'style': 'margin: 0px;'})
							.insert({'bottom': tmp.shippingMethodSel.addClassName('form-control input-sm') 
								.observe('change', function() {
									tmp.btn = this;
									$(tmp.btn).up('.row').down('.input-field').update($F(tmp.btn).blank() ? '' : 
										tmp.shippingCostBox = new Element('input', {'id': tmp.me._htmlIds.totalShippingCost, 'class': 'form-control input-sm', 'save-order': 'totalShippingCost', 'placeholder': tmp.me.getCurrency(0), 'required': true, 'validate_currency': 'Invalid number provided!' })
										.observe('keyup', function() {
											tmp.me._recalculateSummary(0);
										})
									);
									tmp.me._recalculateSummary(0);
									if(tmp.shippingCostBox)
										tmp.shippingCostBox.select();
								})
							})
						})
						.insert({'bottom': new Element('strong', {'class': 'col-xs-6 input-field'})})
					}) 
				})
			});
		return tmp.newDiv;
	}
	/**
	 * Getting the div of the order view
	 */
	,_getViewOfOrder: function() {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = new Element('div')
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('div', {'class': 'col-sm-12'}).update(tmp.me._getCustomerInfoPanel()) })
			})
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('div', {'class': 'col-sm-12 panel panel-success'}).update(tmp.me._getPartsTable()) })
			})
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('div', {'class': 'col-sm-8'}).update(tmp.me._saveBtns()) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-4'}).update(tmp.me._getPaymentPanel()) })
			})
		return tmp.newDiv;
	}
	,selectCustomer: function(customer) {
		var tmp = {};
		tmp.me = this;
		tmp.me._customer = customer;
		tmp.newDiv = tmp.me._getViewOfOrder();
		$(tmp.me._htmlIds.itemDiv).update(tmp.newDiv);
		tmp.newDiv.down('.new-order-item-input [new-order-item="product"]').focus();
		return tmp.me;
	}
	/**
	 * Getting the customer row for displaying the searching result
	 */
	,_getCustomerRow: function(customer, isTitle) {
		var tmp = {};
		tmp.me = this;
		tmp.isTitle = (isTitle || false);
		tmp.tag = (tmp.isTitle === true ? 'th': 'td');
		tmp.newDiv = new Element('tr').store('data', customer)
			.insert({'bottom': new Element(tmp.tag)
				.insert({'bottom': (tmp.isTitle === true ? '&nbsp;':
					new Element('span', {'class': 'btn btn-primary btn-xs'}).update('select')	
					.observe('click', function(){
						tmp.me.selectCustomer(customer);
					})
				) })
			})
			.insert({'bottom': new Element(tmp.tag).update(customer.name) })
			.insert({'bottom': new Element(tmp.tag).update(customer.email) })
			.insert({'bottom': new Element(tmp.tag).update(customer.address && customer.address.billing ? customer.address.billing.full : '') })
		return tmp.newDiv;
	}
	/**
	 * Ajax: searching the customer
	 */
	,_searchCustomer: function (txtbox) {
		var tmp = {};
		tmp.me = this;
		tmp.searchTxt = $F(txtbox).strip();
		tmp.searchPanel = $(txtbox).up('#' + tmp.me._htmlIds.searchPanel);
		tmp.me.postAjax(tmp.me.getCallbackId('searchCustomer'), {'searchTxt': tmp.searchTxt}, {
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
								.insert({'bottom': tmp.me._getCustomerRow({'name': 'Customer Name', 'email': 'Email', 'address': {'billing': {'full': 'Address'}}}, true)  })
							})
							.insert({'bottom': tmp.listDiv = new Element('tbody') })
						})
					});
					tmp.result.items.each(function(item) {
						tmp.listDiv.insert({'bottom': tmp.me._getCustomerRow(item) })
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
	,_getCustomerListPanel: function () {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = new Element('div', {'id': tmp.me._htmlIds.searchPanel, 'class': 'panel panel-default search-panel'})
			.insert({'bottom': new Element('div', {'class': 'panel-heading form-inline'})
				.insert({'bottom': new Element('strong').update('Creating a new order for: ') })
				.insert({'bottom': new Element('span', {'class': 'input-group col-sm-6'})
					.insert({'bottom': new Element('input', {'class': 'form-control search-txt init-focus', 'placeholder': 'customer name or email'}) 
//						.observe('keyup', function(event){
//							if(!document.getElementsByClassName('loading-img').length) {
//								tmp.txtBox = this;
//								$(tmp.me._htmlIds.searchPanel).down('.search-btn').click();
//							}
//						})
						.observe('keydown', function(event){
							tmp.txtBox = this;
							tmp.me.keydown(event, function() {
								$(tmp.me._htmlIds.searchPanel).down('.search-btn').click();
							});
						})
					})
					.insert({'bottom': new Element('span', {'class': 'input-group-btn search-btn'})
						.insert({'bottom': new Element('span', {'class': ' btn btn-primary'})
							.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-search'}) })
						})
						.observe('click', function(){
							tmp.btn = this;
							tmp.me._searchCustomer($(tmp.me._htmlIds.searchPanel).down('.search-txt'));
						})
					})
				})
				.insert({'bottom': new Element('span', {'class': 'btn btn-success pull-right btn-sm'})
					.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-plus-sign'}) })
					.insert({'bottom': ' NEW' })
					.observe('click', function(){
						tmp.me._openNewCustomerPage();
					})
				})
			})
			;
		return tmp.newDiv;
	}
	,_openNewCustomerPage: function(row) {
		var tmp = {};
		tmp.me = this;
		jQuery.fancybox({
			'width'			: '95%',
			'height'		: '95%',
			'autoScale'     : false,
			'autoDimensions': false,
			'fitToView'     : false,
			'autoSize'      : false,
			'type'			: 'iframe',
			'href'			: '/customer/new.html',
			'beforeClose'	    : function() {
				tmp.newCustomer = $$('iframe.fancybox-iframe').first().contentWindow.pageJs._item;
				if(tmp.newCustomer.id) { //successfully created a new customer
					tmp.me.selectCustomer(tmp.newCustomer);
				}
			}
 		});
		return tmp.me;
	}
	/**
	 * Open product Details Page in new Window
	 */
	,_openProductDetailPage: function(id) {
		var tmp = {};
		tmp.me = this;
		tmp.newWindow = window.open('/products/' + id + '.html', 'Product Details', 'location=no, menubar=no, status=no, titlebar=no, fullscreen=yes, toolbar=no');
		tmp.newWindow.focus();
		return tmp.me;
	}
	,init: function(customer) {
		var tmp = {};
		tmp.me = this;
		if(customer) {
			tmp.me.selectCustomer(customer);
		} else {
			$(tmp.me._htmlIds.itemDiv).update(tmp.me._getCustomerListPanel());
		}
		if($$('.init-focus').size() > 0)
			$$('.init-focus').first().focus();
		return tmp.me;
	}
});