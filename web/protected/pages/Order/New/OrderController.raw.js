/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new BPCPageJs(), {
	_htmlIds: {'itemDiv': '', 'searchPanel': 'search_panel'}
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
	 * setting the order types: quote, order and invoice
	 */
	,setOrderTypes: function(orderTypes) {
		this._orderTypes = orderTypes;
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
	,_confirmSubmit: function(printit) {
		var tmp = {};
		tmp.me = this;
		tmp.printIt = (printit === true ? true : false);
		tmp.data = tmp.me._collectFormData($(tmp.me._htmlIds.itemDiv),'save-order');
		if(tmp.data === null)
			return tmp.me;
		tmp.data.printIt = tmp.printIt;
		tmp.data.type = jQuery('[save-order-type="type"]').val();
		tmp.data.customer = {};
		tmp.data.customer.id = tmp.me._customer.id;
		
		tmp.shippAddrPanel = $$('.shipping-address.address-div').first();
		if(tmp.shippAddrPanel) {
			tmp.shippAddr = tmp.me._collectFormData(tmp.shippAddrPanel,'address-editable-field');
			if(tmp.shippAddr === null) //some error in the shipping address
				return tmp.me;
			tmp.data.shippingAddr = tmp.shippAddr;
		}
		
		tmp.data.items = [];
		$$('.order-item-row').each(function(item){
			tmp.itemData = item.retrieve('data');
			tmp.data.items.push({'product': {'id': tmp.itemData.product.id}, 'unitPrice': tmp.itemData.unitPrice, 'qtyOrdered': tmp.itemData.qtyOrdered, 'totalPrice': tmp.itemData.totalPrice, 'serials': item.retrieve('serials') });
		});
		if(tmp.data.items.size() <= 0) {
			tmp.me.showModalBox('<strong class="text-danger">Error</strong>', 'At least one order item is needed!', true);
			return tmp.me;
		}
		tmp.data.items.each(function(item){
			item.totalPrice = tmp.me.getValueFromCurrency(item.totalPrice);
			item.unitPrice = tmp.me.getValueFromCurrency(item.unitPrice);
		});
		tmp.newDiv = new Element('div')
			.insert({'bottom': new Element('h4').update('Please confirm whether you want to change it to be an INVOICE.') })
			.insert({'bottom': new Element('div')
				.insert({'bottom': new Element('span', {'class': 'btn btn-info'}).update('NO. Save as: ' + tmp.data.type)
					.observe('click', function() {
						tmp.me._submitOrder(this, tmp.data);
					})
				})
				.insert({'bottom': new Element('div', {'class': 'btn-group pull-right  visible-xs visible-sm visible-md visible-lg'})
					.insert({'bottom': new Element('span', {'class': 'btn btn-primary', 'title': 'This will push this order to be an INVOICE and status SHIPPED'}).update('YES. GOODs Given to Customer & Push to INVOICE')
						.observe('click', function() {
							tmp.data.type = 'INVOICE';
							tmp.data.shipped = true;
							tmp.me._submitOrder(this, tmp.data);
						})
					})
					.insert({'bottom': new Element('span', {'class': 'btn btn-primary dropdown-toggle', 'data-toggle': 'dropdown'})
						.insert({'bottom': new Element('span', {'class': 'caret'}) })
					})
					.insert({'bottom': new Element('ul', {'class': 'dropdown-menu save-btn-dropdown-menu'})
						.insert({'bottom': new Element('li') 
							.insert({'bottom': new Element('a', {'href': 'javascript: void(0);', 'title': 'This will JUST push this order to be an INVOICE and status NEW'}).update('YES. Push to INVOICE ONLY, goods not given')
								.observe('click', function() {
									tmp.data.type = 'INVOICE';
									tmp.data.shipped = false;
									tmp.me._submitOrder(this, tmp.data);
								})
							}) 
						})
					})
				})
			});
		tmp.me.showModalBox('<strong class="text-info">Confirmation Needed</strong>', tmp.newDiv, false);
		return tmp.me;
	}
	/**
	 * submitting order to php
	 */
	,_submitOrder: function(btn, data) {
		var tmp = {};
		tmp.me = this;
		tmp.modalBoxPanel = $(btn).up('.modal-content');
		tmp.modalBoxTitlePanel = tmp.modalBoxPanel.down('.modal-title');
		tmp.modalBoxBodyPanel = tmp.modalBoxPanel.down('.modal-body');
		tmp.me.postAjax(tmp.me.getCallbackId('saveOrder'), data, {
			'onLoading': function(sender, param) {
				tmp.modalBoxTitlePanel.update('Please wait...');
				tmp.modalBoxBodyPanel.update('<h4>Submitting the data, please be patient.</h4><div><h3 class="fa fa-spinner fa-spin"></h3></div>');
			}
			,'onSuccess': function(sender, param) {
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result || !tmp.result.item)
						return;
					tmp.me._item = tmp.result.item;
					tmp.modalBoxTitlePanel.update('<strong class="text-success">Success!</strong>');
					tmp.modalBoxBodyPanel.update('<div>The new order is succussfully added.</div><br /><div></div>'
							+ '<div>'
								+ '<span class="btn btn-primary" onclick="window.location = ' + "'"
								+ '/orderdetails/' + tmp.result.item.id + '.html' + "'"
								+ ';"><strong>Go to Order Details Page?</strong></span>'
								+ '<span class="btn btn-default pull-right" onclick="window.location = document.URL;">Create another one</span>'
							+ '</div>',
					false);
					if(tmp.result.printURL) {
						tmp.printWindow = window.open(tmp.result.printURL, 'Printing Order', 'location=no, menubar=no, status=no, titlebar=no, fullscreen=no, toolbar=no, width=800');
						tmp.printWindow.print();
					}
				} catch(e) {
					tmp.modalBoxTitlePanel.update('<h4 class="text-danger">Error:</h4>');
					tmp.modalBoxBodyPanel.update(e);
				}
			}
			,'onComplete': function(sender, param) {
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
		tmp.newDiv = new Element('div')
			.insert({'bottom': new Element('div', {'class': 'btn-group pull-right  visible-xs visible-sm visible-md visible-lg'})
				.insert({'bottom': new Element('span', {'class': 'btn btn-primary save-btn'})
					.insert({'bottom': new Element('span').update(' Save & Print ') })
					.observe('click', function() {
						tmp.me._confirmSubmit(true);
					})
				})
				.insert({'bottom': new Element('span', {'class': 'btn btn-primary dropdown-toggle', 'data-toggle': 'dropdown'})
					.insert({'bottom': new Element('span', {'class': 'caret'}) })
				})
				.insert({'bottom': new Element('ul', {'class': 'dropdown-menu save-btn-dropdown-menu'})
					.insert({'bottom': new Element('li') 
						.insert({'bottom': new Element('a', {'href': 'javascript: void(0);'}).update('Save Only')
							.observe('click', function() {
								tmp.me._confirmSubmit();
							})
						}) 
					})
				})
			})
			.insert({'bottom': new Element('div', {'class': 'btn btn-default'})
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
	,_getAddressDiv: function(title, addr, editable) {
		var tmp = {};
		tmp.me = this;
		tmp.editable = (editable || false);
		tmp.newDiv = new Element('div', {'class': 'address-div'})
			.insert({'bottom': new Element('strong').update(title) })
			.insert({'bottom': new Element('dl', {'class': 'dl-horizontal dl-condensed'})
				.insert({'bottom': new Element('dt')
					.update(new Element('span', {'class': "glyphicon glyphicon-user", 'title': "Customer Name"}) ) 
				})
				.insert({'bottom': new Element('dd')
					.insert({'bottom': new Element('div')
						.insert({'bottom': new Element('div', {'class' : 'col-sm-6'}).update(
							tmp.editable !== true ? addr.contactName : new Element('input', {'address-editable-field': 'contactName', 'required': true, 'class': 'form-control input-sm', 'placeholder': 'The name of contact person',  'value': addr.contactName})
						) })
						.insert({'bottom': new Element('div', {'class' : 'col-sm-6'}).update(
								tmp.editable !== true ? addr.contactNo : new Element('input', {'address-editable-field': 'contactNo', 'class': 'form-control input-sm', 'placeholder': 'The contact number of contact person',  'value': addr.contactNo})
						) })
					})
				})
				.insert({'bottom': new Element('dt').update(
					new Element('span', {'class': "glyphicon glyphicon-map-marker", 'title': "Address"}) 
				) })
				.insert({'bottom': new Element('dd')
					.insert({'bottom': new Element('div')
						.insert({'bottom': tmp.editable !== true ? addr.street : new Element('div', {'class': 'street col-sm-12'}).update(
								new Element('input', {'address-editable-field': 'street', 'class': 'form-control input-sm', 'required': true, 'placeholder': 'Street Number and Street name',  'value': addr.street})
						) })
					})
					.insert({'bottom': new Element('div')
						.insert({'bottom': tmp.editable !== true ? addr.city + ' ' : new Element('div', {'class': 'city col-sm-6'}).update(
								new Element('input', {'address-editable-field': 'city', 'class': 'form-control input-sm', 'required': true, 'placeholder': 'City / Suburb',  'value': addr.city})
						) })
						.insert({'bottom':  tmp.editable !== true ? addr.region + ' ' : new Element('div', {'class': 'region col-sm-3'}).update(
								new Element('input', {'address-editable-field': 'region', 'class': 'form-control input-sm', 'required': true, 'placeholder': 'State / Province',  'value': addr.region})
						) })
						.insert({'bottom': tmp.editable !== true ? addr.postCode: new Element('div', {'class': 'postcode col-sm-3'}).update(
								new Element('input', {'address-editable-field': 'postCode', 'class': 'form-control input-sm', 'required': true, 'placeholder': 'PostCode',  'value': addr.postCode})
						) })
					})
					.insert({'bottom': new Element('div')
						.insert({'bottom': tmp.editable !== true ? addr.country: new Element('div', {'class': 'postcode col-sm-4'}).update(
								new Element('input', {'address-editable-field': 'country', 'class': 'form-control input-sm', 'required': true, 'placeholder': 'Country',  'value': addr.country})
						) })
					})
				})
			});
		if(tmp.editable === true) {
			tmp.newDiv.writeAttribute('address-editable', true);
		}
		return tmp.newDiv;
	}
	/**
	 * getting the customer information div
	 */
	,_getCustomerInfoPanel: function() {
		var tmp = {};
		tmp.me = this;
		tmp.customer = tmp.me._customer;
		tmp.typeSelBox = new Element('select', {'save-order-type': 'type'});
		tmp.me._orderTypes.each(function(type){
			tmp.typeSelBox.insert({'bottom': tmp.option = new Element('option', {'value': type}).update(type) })
				.observe('change', function(){
					tmp.panels = jQuery('.panel').removeClass('panel-success').removeClass('panel-warning').removeClass('panel-info');
					tmp.inputs = jQuery('.list-group-item').removeClass('list-group-item-success').removeClass('list-group-item-warning').removeClass('list-group-item-info');
					if($F(this) === 'QUOTE') {
						tmp.panels.addClass('panel-warning');
						tmp.inputs.addClass('list-group-item-warning')
					} else if($F(this) === 'ORDER') {
						tmp.panels.addClass('panel-success');
						tmp.inputs.addClass('list-group-item-success')
					} else {
						tmp.panels.addClass('panel-info');
						tmp.inputs.addClass('list-group-item-info')
					}
				});
			if(type === 'ORDER') {
				tmp.option.writeAttribute('selected', true);
			}
		});
		tmp.newDiv = new Element('div', {'class': 'panel panel-success'})
			.insert({'bottom': new Element('div', {'class': 'panel-heading'})
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('div', {'class': 'col-sm-8'})
						.insert({'bottom': new Element('strong').update('CREATING A ') })
						.insert({'bottom': tmp.typeSelBox })
						.insert({'bottom': new Element('strong').update(' FOR:  ' + tmp.customer.name + ' ') })
						.insert({'bottom': ' <' })
						.insert({'bottom': new Element('a', {'href': 'mailto:' + tmp.customer.email}).update(tmp.customer.email) })
						.insert({'bottom': '>' })
						.insert({'bottom': new Element('strong').update(' with PO No.:') })
						.insert({'bottom': new Element('input', {'type': 'text', 'save-order': 'poNo', 'placeholder': 'Optional - PO No. From Customer'}) })
					})
					.insert({'bottom': new Element('div', {'class': 'col-sm-4 text-right'})
						.insert({'bottom': new Element('strong').update('Total Payment Due: ') })
						.insert({'bottom': new Element('span', {'class': 'badge', 'order-price-summary': 'total-payment-due'}).update(tmp.me.getCurrency(0) ) })
					})
				})
			})
			.insert({'bottom': new Element('div', {'class': 'panel-body'})
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('small').update(new Element('em').update(tmp.customer.description)) })
				})
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': tmp.me._getAddressDiv("Billing Address: ", tmp.customer.address.billing).addClassName('col-xs-6') })
					.insert({'bottom': tmp.me._getAddressDiv("Shipping Address: ", tmp.customer.address.shipping, true).addClassName('col-xs-6').addClassName('shipping-address') })
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
				.insert({'bottom': new Element(tmp.tag, {'class': 'productName col-xs-6'})
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
				.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-2'})
					.insert({'bottom': new Element('div')
						.insert({'bottom': new Element('div', {'class': 'qty col-xs-6'}).update(orderItem.qtyOrdered) })
						.insert({'bottom': new Element('div', {'class': 'discount col-xs-6'}).update(orderItem.discount) })
					})
				})
				.insert({'bottom': new Element(tmp.tag, {'class': 'tprice col-xs-1'})
					.insert({'bottom': (orderItem.totalPrice) })
				})
				.insert({'bottom': new Element(tmp.tag, {'class': 'margin col-xs-1 text-right'}).update(orderItem.margin)})
				.insert({'bottom': new Element(tmp.tag, {'class': 'btns col-xs-1 text-right'}).update(orderItem.btns) })
			});
		if(orderItem.product.sku) {
			tmp.row.down('.productName')
				.removeClassName('col-xs-6')
				.addClassName('col-xs-4')
				.insert({'before': new Element(tmp.tag, {'class': 'productSku col-xs-2'}).update(orderItem.product.sku) });
		}
		if(orderItem.scanTable) {
			tmp.row.insert({'bottom': new Element('div', {'class': 'row product-content-row'})
				.insert({'bottom': new Element('span', {'class': 'col-sm-2 show-tools'})
					.insert({'bottom': new Element('input', {'type': 'checkbox', 'checked': true, 'class': 'show-panel-check'})
						.observe('click', function(){
							tmp.btn = this;
							tmp.panel = $(tmp.btn).up('.product-content-row').down('.serial-no-scan-pane');
							if(tmp.btn.checked) {
								tmp.panel.show();
							} else {
								tmp.panel.hide();
							}
						})
					})
					.insert({'bottom': new Element('a', {'href': 'javascript: void(0);'}).update(' show serial scan panel?')
						.observe('click', function(){
							$(this).up('.show-tools').down('.show-panel-check').click();
						})
					})
				})
				.insert({'bottom': new Element('span', {'class': 'col-sm-10 serial-no-scan-pane', 'style': 'padding-top: 5px'}).update(orderItem.scanTable) })
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
							.insert({'bottom': new Element('div', {'class': 'input-group', 'title': 'stock on HAND'})
								.insert({'bottom': new Element('span', {'class': 'input-group-addon'}).update('SOH:') })
								.insert({'bottom': new Element('strong', {'class': 'form-control'}).update(product.stockOnHand) })
							})
						})
						.insert({'bottom': new Element('small', {'class': 'col-xs-4'})
							.insert({'bottom': new Element('div', {'class': 'input-group', 'title': 'stock on ORDER'})
								.insert({'bottom': new Element('span', {'class': 'input-group-addon'}).update('SOO:') })
								.insert({'bottom': new Element('strong', {'class': 'form-control'}).update(product.stockOnOrder) })
							})
						})
						.insert({'bottom': new Element('small', {'class': 'col-xs-4'})
							.insert({'bottom': new Element('div', {'class': 'input-group', 'title': 'stock on PO'})
								.insert({'bottom': new Element('span', {'class': 'input-group-addon'}).update('SOP:') })
								.insert({'bottom': new Element('strong', {'class': 'form-control'}).update(product.stockOnPO) })
							})
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
					.insert({'after': new Element('div', {'class': 'col-xs-4'})
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
				tmp.inputRow.down('[new-order-item=unitPrice]').writeAttribute('value', tmp.me.getCurrency(tmp.retailPrice)).select();
				tmp.me._calculateNewProductPrice(tmp.inputRow);
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
				.observe('keyup', function(event){
					tmp.txtBox = this;
					tmp.me.keydown(event, function() {
						$(tmp.txtBox).up('.product-autocomplete').down('.search-btn').click();
					});
				})
				.observe('keydown', function(event){
					tmp.txtBox = this;
					tmp.me.keydown(event, function() {
						$(tmp.txtBox).up('.product-autocomplete').down('.search-btn').click();
					}, function(){}, Event.KEY_TAB);
				})
			})
			.insert({'bottom': new Element('span', {'class': 'input-group-btn'}) 
				.insert({'bottom': new Element('span', {'class': ' btn btn-primary search-btn' , 'data-loading-text': 'searching...'})
					.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-search'}) })
					.observe('click', function(){
						if(!$F($(this).up('.product-autocomplete').down('.search-txt')).blank())
							tmp.me._searchProduct(this);
						else
							$(this).up('.product-autocomplete').down('.search-txt').focus();
					})
				})
			}) 
		);
		tmp.skuAutoComplete.down('.input-group').removeClassName('form-control');
		return tmp.skuAutoComplete;
	}
	,_recalculateSummary: function() {
		var tmp = {};
		tmp.me = this;
		//getting all the item row's total
		tmp.totalPriceIncGSTNoDicount = 0;
		tmp.totalPriceIncGSTWithDiscount = 0;
		tmp.totalMargin = 0;
		$$('.item_row.order-item-row').each(function(row) {
			tmp.rowData = row.retrieve('data');
			tmp.totalPriceIncGSTWithDiscount = tmp.totalPriceIncGSTWithDiscount * 1 + (tmp.me.getValueFromCurrency(tmp.rowData.totalPrice) * 1);
			tmp.totalPriceIncGSTNoDicount = tmp.totalPriceIncGSTNoDicount * 1 + (tmp.me.getValueFromCurrency(tmp.rowData.unitPrice) * tmp.rowData.qtyOrdered);
			tmp.totalMargin = tmp.totalMargin * 1 + tmp.me.getValueFromCurrency(tmp.rowData.margin); 
		});
		//calculate total price without GST
		tmp.totalPriceExcGST = ((tmp.totalPriceIncGSTWithDiscount * 1) / 1.1);
		jQuery('[order-price-summary="totalPriceExcludeGST"]').val(tmp.me.getCurrency(tmp.totalPriceExcGST)).html(tmp.me.getCurrency(tmp.totalPriceExcGST));
		
		//calculate total GST
		tmp.totalGST = (tmp.totalPriceIncGSTWithDiscount * 1 - tmp.totalPriceExcGST * 1);
		jQuery('[order-price-summary="totalPriceGST"]').val(tmp.me.getCurrency(tmp.totalGST)).html(tmp.me.getCurrency(tmp.totalGST));
		
		//calculate the total price with GST
		tmp.totalDiscount = (tmp.totalPriceIncGSTNoDicount * 1 - tmp.totalPriceIncGSTWithDiscount * 1);
		jQuery('[order-price-summary="totalDiscount"]').val(tmp.me.getCurrency(tmp.totalDiscount)).html(tmp.me.getCurrency(tmp.totalDiscount));
		
		//calculate the total price with GST
		jQuery('[order-price-summary="totalPriceIncludeGST"]').val(tmp.me.getCurrency(tmp.totalPriceIncGSTWithDiscount)).html(tmp.me.getCurrency(tmp.totalPriceIncGSTWithDiscount));
		
		//calculate the sub total
		tmp.totalShipping = (jQuery('[order-price-summary="totalShippingCost"]').length > 0 ? jQuery('[order-price-summary="totalShippingCost"]').val() : 0);
		tmp.subTotal = (tmp.totalShipping * 1 + tmp.totalPriceIncGSTWithDiscount * 1);
		jQuery('[order-price-summary="subTotal"]').val(tmp.me.getCurrency(tmp.subTotal)).html(tmp.me.getCurrency(tmp.subTotal));
		
		//calculate the total due
		tmp.totalPaid = (jQuery('[order-price-summary="totalPaidAmount"]').length > 0 ? jQuery('[order-price-summary="totalPaidAmount"]').val() : 0);
		tmp.totalDue = (tmp.subTotal * 1 - tmp.totalPaid * 1);
		if(tmp.totalDue < 0) {
			tmp.me.showModalBox(
				'<h4 class="text-danger">Attention!</h4>', 
				'<div><strong>The customer has paid more than the due amount?</strong></div><div><span class="btn btn-primary" onclick="pageJs.hideModalBox();">OK</span>',
				true
			);
		}
		jQuery('[order-price-summary="total-payment-due"]').val(tmp.me.getCurrency(tmp.totalDue)).html(tmp.me.getCurrency(tmp.totalDue));
		
		//display margin:
		jQuery('[order-price-summary="total-margin"]').val(tmp.me.getCurrency(tmp.totalMargin)).html(tmp.me.getCurrency(tmp.totalMargin));
		
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
			'margin': tmp.me.getCurrency(tmp.totalPrice * 1 - tmp.product.unitCost * tmp.qtyOrdered),
			'totalPrice': tmp.me.getCurrency(tmp.totalPrice),
			'btns': new Element('span', {'class': 'pull-right'})
				.insert({'bottom': new Element('span', {'class': 'btn btn-danger btn-xs'})
				.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-trash'}) })
				.observe('click', function() {
					if(!confirm('You remove this entry.\n\nContinue?'))
						return;
					tmp.row = $(this).up('.item_row');
					tmp.me._recalculateSummary();
					tmp.row.remove();
				})
			})
		};
		
		tmp.data.scanTable = tmp.me._getScanTable(tmp.data);
		tmp.currentRow.insert({'after': tmp.itemRow = tmp.me._getProductRow(tmp.data) });
		
		
		tmp.newRow = tmp.me._getNewProductRow();
		tmp.currentRow.replace(tmp.newRow).addClassName();
		tmp.newRow.down('[new-order-item=product]').focus();
		
		tmp.me._recalculateSummary();
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
	 * calculateNewProductPrice
	 */
	,_calculateNewProductPrice: function(row) {
		var tmp = {};
		tmp.me = this;
		tmp.row = row;
		tmp.unitPrice = tmp.me.getValueFromCurrency( $F(tmp.row.down('[new-order-item=unitPrice]')) );
		tmp.discount = $F(tmp.row.down('[new-order-item=discount]')).strip().replace('%', '');
		tmp.qty = $F(tmp.row.down('[new-order-item=qtyOrdered]')).strip();
		tmp.totalPrice = tmp.unitPrice * (1 - tmp.discount/100) * tmp.qty;
		$(tmp.row.down('[new-order-item=totalPrice]')).value = tmp.me.getCurrency( tmp.totalPrice );
		if(row.retrieve('product')) {
			tmp.unitCost = row.retrieve('product').unitCost;
			if(tmp.row.down('.margin'))
				$(tmp.row.down('.margin')).update( tmp.me.getCurrency( tmp.totalPrice * 1 - tmp.unitCost * tmp.qty ) + (parseInt(tmp.unitCost) === 0 ? '<div><small class="label label-danger">No Cost Yet</small</div>' : '') );
		}
		return tmp.me;
	}
	/**
	 * bindSubmit product row event
	 */
	,_bindSubmitNewProductRow: function(event, box) {
		var tmp = {};
		tmp.me = this;
		tmp.txtBox = box;
		tmp.me.keydown(event, function() {
			 $(tmp.txtBox).up('.item_row').down('.save-new-product-btn').click();
		});
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
			,'unitPrice': tmp.me._getFormGroup( null, new Element('input', {'class': 'input-sm', 'new-order-item': 'unitPrice', 'required': true, 'value': tmp.me.getCurrency(0)})
				.observe('click', function() {
					$(this).select();
				})
				.observe('keydown', function(event){
					tmp.me._bindSubmitNewProductRow(event, this);
				})
				.observe('keyup', function(){
					tmp.me._calculateNewProductPrice($(this).up('.item_row'));
				})
			)
			,'qtyOrdered': tmp.me._getFormGroup( null, new Element('input', {'class': 'input-sm', 'new-order-item': 'qtyOrdered', 'required': true, 'value': '1'})
				.observe('keyup', function(){
					tmp.me._calculateNewProductPrice($(this).up('.item_row'));
				})
				.observe('keydown', function(event){
					tmp.me._bindSubmitNewProductRow(event, this);
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
					tmp.me._calculateNewProductPrice($(this).up('.item_row'));
				})
				.observe('keydown', function(event){
					tmp.me._bindSubmitNewProductRow(event, this);
				})
				.observe('click', function() {
					$(this).select();
				})
			)
			,'totalPrice': tmp.me._getFormGroup( null, new Element('input', {'class': 'input-sm', 'disabled': true, 'new-order-item': 'totalPrice', 'required': true, 'value': tmp.me.getCurrency(0)}) )
			,'margin': tmp.me.getCurrency(0)
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
			.insert({'bottom': tmp.me._getProductRow({'product': {'sku': 'SKU', 'name': 'Description'}, 
				'unitPrice': 'Unit Price<div><small>(inc GST)</small><div>', 
				'qtyOrdered': 'Qty', 
				'margin': 'Margin', 
				'discount': 'Disc. %', 
				'totalPrice': 'Total Price<div><small>(inc GST)</small><div>'
				,'btns': new Element('div')
					.insert({'bottom': new Element('label', {'for': 'hide-margin-checkbox'}).update('Show Margin ') })
					.insert({'bottom': new Element('input', {'id': 'hide-margin-checkbox', 'type': 'checkbox', 'checked': true})
						.observe('click', function(){
							jQuery('.margin').toggle();
						})
					})
				}, true)
			});
		// tbody
		tmp.productListDiv.insert({'bottom': tmp.me._getNewProductRow().addClassName('list-group-item-success') });
		return tmp.productListDiv;
	}
	/**
	 * Getting summary footer for the parts list
	 */
	,_getSummaryFooter: function() {
		var tmp = {};
		tmp.me = this;
		tmp.shippingMethodSel = new Element('select', {'class': 'form-control input-sm', 'save-order': 'courierId'})
			.insert({'bottom': new Element('option', {'value': ''}).update('Shipping Via:') });
		tmp.me._shippingMethods.each(function(method){
			tmp.shippingMethodSel.insert({'bottom': new Element('option', {'value': method.id}).update(method.name) });
		})
		tmp.paymentMethodSel = new Element('select', {'class': 'form-control input-sm', 'save-order': 'paymentMethodId'})
			.insert({'bottom': new Element('option', {'value': ''}).update('Paid Via:') });
		tmp.me._paymentMethods.each(function(method){
			tmp.paymentMethodSel.insert({'bottom': new Element('option', {'value': method.id}).update(method.name) });
		});
		tmp.newDiv = new Element('div', {'class': 'panel-footer'}) 
			.insert({'bottom': new Element('div', {'class': 'row'}) 
				.insert({'bottom': new Element('div', {'class': 'col-sm-8'})
						.insert({'bottom': tmp.me._getFormGroup( 'Comments:', new Element('textarea', {'save-order': 'comments', 'rows': '8'}) ) })
					}) 
				.insert({'bottom': new Element('div', {'class': 'col-sm-4'})
					.insert({'bottom': new Element('div', {'class': 'row'})
						.insert({'bottom': new Element('div', {'class': 'col-xs-6 text-right'}).update( new Element('strong').update('Total Excl. GST: ') ) }) 
						.insert({'bottom': new Element('div', {'class': 'col-xs-6', 'order-price-summary': 'totalPriceExcludeGST'}).update( tmp.me.getCurrency(0) ) }) 
					})
					.insert({'bottom': new Element('div', {'class': 'row'}) 
						.insert({'bottom': new Element('div', {'class': 'col-xs-6 text-right'}).update( new Element('strong').update('Total GST: ') ) }) 
						.insert({'bottom': new Element('div', {'order-price-summary': 'totalPriceGST', 'class': 'col-xs-6'}).update( tmp.me.getCurrency(0) ) }) 
					})
					.insert({'bottom': new Element('div', {'class': 'row', 'style': 'border-bottom: 1px solid brown'}) 
						.insert({'bottom': new Element('div', {'class': 'col-xs-6 text-right'}).update( new Element('strong').update('Total Discount:') ) }) 
						.insert({'bottom': new Element('div', {'order-price-summary': 'totalDiscount', 'class': 'col-xs-6'}).update( tmp.me.getCurrency(0) ) })
					})
					.insert({'bottom': new Element('div', {'class': 'row'}) 
						.insert({'bottom': new Element('div', {'class': 'col-xs-6 text-right'}).update( new Element('strong').update('Total Incl. GST: ') ) }) 
						.insert({'bottom': new Element('div', {'order-price-summary': 'totalPriceIncludeGST', 'class': 'col-xs-6'}).update( tmp.me.getCurrency(0) ) })
					})
					.insert({'bottom': new Element('div', {'class': 'row', 'style': 'border-bottom: 1px solid brown'}) 
						.insert({'bottom': new Element('div', {'class': 'col-xs-6 text-right'}).update( new Element('strong').update(
							tmp.shippingMethodSel.observe('change', function() {
								tmp.btn = this;
								$(tmp.btn).up('.row').down('.input-field').update($F(tmp.btn).blank() ? tmp.me.getCurrency(0) : 
									tmp.shippingCostBox = new Element('input', {'order-price-summary': 'totalShippingCost', 'class': 'form-control input-sm', 'save-order': 'totalShippingCost', 'placeholder': tmp.me.getCurrency(0), 'required': true, 'validate_currency': 'Invalid number provided!' })
									.observe('keyup', function() {
										tmp.me._recalculateSummary();
									})
								);
								tmp.me._recalculateSummary();
								if(tmp.shippingCostBox)
									tmp.shippingCostBox.select();
							})
						) ) }) 
						.insert({'bottom': new Element('div', {'class': 'col-xs-6 input-field'}).update( tmp.me.getCurrency(0) ) })
					})
					.insert({'bottom': new Element('div', {'class': 'row'}) 
						.insert({'bottom': new Element('div', {'class': 'col-xs-6 text-right'}).update( new Element('strong').update('Sub-Total:') ) }) 
						.insert({'bottom': new Element('strong', {'order-price-summary': 'subTotal', 'class': 'col-xs-6'}).update( tmp.me.getCurrency(0) ) })
					})
					.insert({'bottom': new Element('div', {'class': 'row', 'style': 'border-bottom: 1px solid brown'}) 
						.insert({'bottom': new Element('div', {'class': 'col-xs-6 text-right'}).update( new Element('strong').update(
							tmp.paymentMethodSel.observe('change', function() {
									tmp.btn = this;
									$(tmp.btn).up('.row').down('.input-field').update($F(tmp.btn).blank() ? tmp.me.getCurrency(0) : 
										tmp.paidAmountBox = new Element('input', {'order-price-summary': 'totalPaidAmount', 'class': 'form-control input-sm', 'save-order': 'totalPaidAmount', 'placeholder': tmp.me.getCurrency(0), 'required': true, 'validate_currency': 'Invalid number provided!' })
										.observe('keyup', function() {
											tmp.me._recalculateSummary();
										})
									);
									tmp.me._recalculateSummary();
									if(tmp.paidAmountBox)
										tmp.paidAmountBox.select();
								})
						) ) }) 
						.insert({'bottom': new Element('div', {'class': 'col-xs-6 input-field'}).update( tmp.me.getCurrency(0) ) })
					})
					.insert({'bottom': new Element('div', {'class': 'row'})
						.insert({'bottom': new Element('h4', {'class': 'col-xs-6 text-right'}).update(new Element('strong').update('DUE:')) })
						.insert({'bottom': new Element('h4', {'class': 'col-xs-6', 'order-price-summary': 'total-payment-due'}).update(tmp.me.getCurrency(0)) })
					})
					.insert({'bottom': new Element('div', {'class': 'row margin'})
						.insert({'bottom': new Element('strong', {'class': 'col-xs-6 text-right'}).update(new Element('strong').update('Margin Total:')) })
						.insert({'bottom': new Element('strong', {'class': 'col-xs-6', 'order-price-summary': 'total-payment-due'}).update(tmp.me.getCurrency(0)) })
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
				.insert({'bottom': new Element('div', {'class': 'col-sm-12'})
					.insert({'bottom': new Element('div', {'class': 'panel panel-success'}).update(tmp.me._getPartsTable()) 
						.insert({'bottom': tmp.me._getSummaryFooter() }) 
					})
				})
			})
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('div', {'class': 'col-sm-12'}).update(tmp.me._saveBtns()) })
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
						.observe('keyup', function(event){
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