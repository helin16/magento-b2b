/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new BPCPageJs(), {
	/**
	 * Getting each product row
	 */
	_getProductRow: function(orderItem, isTitleRow) {
		var tmp = {};
		tmp.me = this;
		tmp.isTitle = (isTitleRow || false);
		tmp.stockOnPOEl = new Element('div', {'class': 'form-group'} )
			.insert({'bottom': new Element('input', {'class': 'form-control', 'name': 'Stock on PO', 'save-item': 'stockOnPO', 'placeholder': 'Stock On PO', 'type': 'number', 'value': orderItem.product.stockOnPO ? orderItem.product.stockOnPO : 0}) })
			.observe('keydown', function(event){
				tmp.txtBox = $(this);
				tmp.me.keydown(event, function() {
					tmp.txtBox.up('.product-head-row').down('[save-item="stockOnHand"]').select();
				});
			})
			.observe('click', function(event){
				$(this).down('input').select();
			});
		tmp.stockOnHandEl = new Element('div', {'class': 'form-group'} )
			.insert({'bottom': new Element('input', {'class': 'form-control', 'name': 'Stock on Hand', 'save-item': 'stockOnHand', 'placeholder': 'Stock On Hand', 'type': 'number', 'value': orderItem.product.stockOnHand ? orderItem.product.stockOnHand : 0}) })
			.observe('keydown', function(event){
				tmp.txtBox = $(this);
				tmp.me.keydown(event, function() {
					tmp.txtBox.up('.item_row').down('[save-item="totalOnHandValue"]').select();
				});
			})
			.observe('keyup', function(){
				tmp.txtBox = $(this);
				tmp.valueBox = tmp.txtBox.up('.item_row').down('[save-item="totalOnHandValue"]');
//				if($F(tmp.txtBox.down('input')) != 0 && orderItem.product.unitCost != 0 && tmp.me.getValueFromCurrency($F(tmp.valueBox)) != 0)
					tmp.valueBox.value = tmp.me.getCurrency(orderItem.product.unitCost * $F(tmp.txtBox.down('input')));
			})
			.observe('click', function(event){
				$(this).down('input').select();
			});
		tmp.totalOnHandValueEl = new Element('div', {'class': 'form-group'} )
			.insert({'bottom': new Element('input', {'class': 'form-control', 'save-item': 'totalOnHandValue', 'placeholder': 'Total On Hand Value', 'type': 'value', 'value': orderItem.product.totalOnHandValue ? tmp.me.getCurrency(orderItem.product.totalOnHandValue) : tmp.me.getCurrency(0)}) })
			.observe('keydown', function(event){
				tmp.txtBox = $(this);
				tmp.me.keydown(event, function() {
					tmp.txtBox.up('.item_row').down('[save-item="stockOnOrder"]').select();
				});
			})
			.observe('click', function(event){
				$(this).down('input').select();
			})
			.observe('change', function(event){
				$(this).down('input').value = tmp.me.getCurrency(tmp.me.getValueFromCurrency($(this).down('input').value));
			});
		tmp.stockOnOrderEl = new Element('div', {'class': 'form-group'} )
			.insert({'bottom': new Element('input', {'class': 'form-control', 'name': 'Stock on Order', 'save-item': 'stockOnOrder', 'placeholder': 'Stock On Order', 'type': 'number', 'value': orderItem.product.stockOnOrder ? orderItem.product.stockOnOrder : 0}) })
			.observe('keydown', function(event){
				tmp.txtBox = $(this);
				tmp.me.keydown(event, function() {
					tmp.txtBox.up('.item_row').down('[save-item="stockInRMA"]').select();
				});
			})
			.observe('click', function(event){
				$(this).down('input').select();
			});
		tmp.stockInRMAEl = new Element('div', {'class': 'form-group'} )
			.insert({'bottom': new Element('input', {'class': 'form-control', 'name': 'Stock in RMA', 'save-item': 'stockInRMA', 'placeholder': 'Stock In RMA', 'type': 'number', 'value': orderItem.product.stockInRMA ? orderItem.product.stockInRMA : 0}) })
			.observe('keydown', function(event){
				tmp.txtBox = $(this);
				tmp.me.keydown(event, function() {
					tmp.txtBox.up('.item_row').down('[save-item="stockInParts"]').select();
				});
			})
			.observe('click', function(event){
				$(this).down('input').select();
			});
		tmp.stockInPartsEl = new Element('div', {'class': 'form-group'} )
			.insert({'bottom': new Element('input', {'class': 'form-control', 'name': 'Stock in Parts', 'save-item': 'stockInParts', 'placeholder': 'Stock In Parts', 'type': 'number', 'value': orderItem.product.stockInParts ? orderItem.product.stockInParts : 0}) })
			.observe('keydown', function(event){
				tmp.txtBox = $(this);
				tmp.me.keydown(event, function() {
					tmp.txtBox.up('.item_row').down('[save-item="totalInPartsValue"]').select();
				});
			})
			.observe('keyup', function(){
				tmp.txtBox = $(this);
				tmp.valueBox = tmp.txtBox.up('.item_row').down('[save-item="totalInPartsValue"]');
				if($F(tmp.txtBox.down('input')) != 0 && orderItem.product.unitCost != 0 && tmp.me.getValueFromCurrency($F(tmp.valueBox)) != 0)
					tmp.valueBox.value = tmp.me.getCurrency(orderItem.product.unitCost * $F(tmp.txtBox.down('input')));
			})
			.observe('click', function(event){
				$(this).down('input').select();
			});
		tmp.totalInPartsValueEl = new Element('div', {'class': 'form-group'} )
			.insert({'bottom': new Element('input', {'class': 'form-control', 'save-item': 'totalInPartsValue', 'placeholder': 'Total In Parts Value', 'type': 'value', 'value': orderItem.product.totalInPartsValue ? tmp.me.getCurrency(orderItem.product.totalInPartsValue) : tmp.me.getCurrency(0)}) })
			.observe('keydown', function(event){
				tmp.txtBox = $(this);
				tmp.me.keydown(event, function() {
					$(tmp.me.getHTMLID('barcodeInput')).focus();
				});
			})
			.observe('click', function(event){
				$(this).down('input').select();
			})
			.observe('change', function(event){
				$(this).down('input').value = tmp.me.getCurrency(tmp.me.getValueFromCurrency($(this).down('input').value));
			});
		tmp.row = new Element((tmp.isTitle === true ? 'strong' : 'div'), {'class': 'item_row list-group-item'})
			.store('data', orderItem.product)
			.insert({'bottom': tmp.infoRow = new Element('div', {'class': tmp.isTitle ? 'row btn-hide-row' : 'row btn-hide-row product-head-row'})
				.insert({'bottom': new Element('span', {'class': ' col-sm-2 productName'})
					.insert({'bottom': orderItem.product.name ? orderItem.product.name : orderItem.product.barcode })
				})
				.insert({'bottom': new Element('span', {'class': ' col-sm-1 StockOnPO'})
					.update(orderItem.product.id ? tmp.stockOnPOEl : orderItem.product.stockOnPO)
				})
				.insert({'bottom': new Element('span', {'class': ' col-sm-1 StockOnHand'})
					.update(orderItem.product.id ? tmp.stockOnHandEl : orderItem.product.stockOnHand)
				})
				.insert({'bottom': new Element('span', {'class': ' col-sm-1 TotalOnHandValue'})
					.update(orderItem.product.id ? tmp.totalOnHandValueEl : orderItem.product.totalOnHandValue)
				})
				.insert({'bottom': new Element('span', {'class': ' col-sm-1 StockOnOrder'})
					.update(orderItem.product.id ? tmp.stockOnOrderEl : orderItem.product.stockOnOrder)
				})
				.insert({'bottom': new Element('span', {'class': ' col-sm-1 StockInRMA'})
					.update(orderItem.product.id ? tmp.stockInRMAEl : orderItem.product.stockInRMA)
				})
				.insert({'bottom': new Element('span', {'class': ' col-sm-1 StockInParts'})
					.update(orderItem.product.id ? tmp.stockInPartsEl : orderItem.product.stockInParts)
				})
				.insert({'bottom': new Element('span', {'class': ' col-sm-1 TotalInPartsValue'})
					.update(orderItem.product.id ? tmp.totalInPartsValueEl : orderItem.product.totalInPartsValue)
				})
				.insert({'bottom': tmp.btns = new Element('span', {'class': 'btns col-sm-1'}).update(orderItem.btns ? orderItem.btns : '') })
			});
			tmp.productSkuEl = orderItem.product.sku ? new Element('a', {'href': '/product/' + orderItem.product.id + '.html', 'target': '_BLANK'}).update(orderItem.product.sku) : '';
			tmp.infoRow.insert({'top': new Element('span', {'class': 'col-sm-2 productSku'}).update(tmp.productSkuEl) });
			tmp.row.insert({'bottom': new Element('div', {'class': 'row product-content-row'})
				.insert({'bottom': new Element('span', {'class': 'col-sm-10 col-sm-offset-2'}).update(orderItem.scanTable) })
			});

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
			,'btns': ''
		};
		tmp.newRow = tmp.me._getProductRow(tmp.data, false).addClassName('new-order-item-input list-group-item-warning').removeClassName('order-item-row btn-hide-row');
		return tmp.newRow
	}
	/**
	 * Getting the autocomplete input box for product
	 */
	,_getNewProductProductAutoComplete: function() {
		var tmp = {};
		tmp.me = this;
		tmp.skuAutoComplete = tmp.me._getFormGroup( null, new Element('div', {'class': 'input-group input-group-sm product-autocomplete'})
			.insert({'bottom': new Element('input', {'id': tmp.me.getHTMLID('barcodeInput'), 'class': 'form-control search-txt visible-xs visible-sm visible-md visible-lg', 'new-order-item': 'product', 'required': 'Required!', 'placeholder': 'Enter BARCODE for products'})
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
	 * Getting the form group
	 */
	,_getFormGroup: function(title, content) {
		return new Element('div', {'class': 'form-group'})
			.insert({'bottom': title ? new Element('label', {'class': 'control-label'}).update(title) : '' })
			.insert({'bottom': content.addClassName('form-control') });
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
		if (!tmp.searchTxt)
			return;
		tmp.currentRow = $(btn).up('.new-order-item-input');
		tmp.product = {
				'name': ''
				,'id' : ''
				,'qty': 0
				,'barcode': tmp.searchTxt
		};
		tmp.data = {
				'product': tmp.product,
				'btns': new Element('span', {'class': 'pull-right'})
					.insert({'bottom': new Element('span', {'class': 'btn btn-danger btn-xs'})
					.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-trash'}) })
					.observe('click', function(event) {
						Event.stop(event);
						if(!confirm('You are about to remove this entry.\n\nContinue?'))
							return;
						tmp.row = $(this).up('.item_row');
						tmp.row.remove();
					})
				})
			};
		tmp.currentRow.insert({'after': tmp.lastRow = new Element('div') });
		tmp.newRow = tmp.me._getNewProductRow();
		tmp.currentRow.replace(tmp.newRow);

		tmp.inputBox = jQuery('#' + tmp.me.getHTMLID('barcodeInput'));
		tmp.me.postAjax(tmp.me.getCallbackId('searchProduct'), {'searchTxt': tmp.searchTxt}, {
			'onCreate': function() {
				jQuery('#' + tmp.me.getHTMLID('barcodeInput')).button('loading');
				tmp.lastRow.hide();
			}
			,'onSuccess': function(sender, param) {
				tmp.resultList = new Element('div', {'style': 'overflow: auto; max-height: 400px;'});
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result || !tmp.result.items || tmp.result.items.size() === 0) {
						tmp.ModalBoxBody = new Element('div')
							.insert({'bottom': new Element('span').update('You are searching for <b>' + tmp.searchTxt + '</b>') })
							.insert({'bottom': new Element('span', {'class': 'btn btn-success btn-md pull-right'}).update('OK')
								.observe('click', function(){
									tmp.me.hideModalBox();
									$(tmp.me.getHTMLID('barcodeInput')).focus();
								})
							});
						tmp.me.showModalBox('Product no found!', tmp.ModalBoxBody, false);
						tmp.lastRow.remove();
					}
					tmp.lastRow.show();
					if(tmp.result.items.size()>1) {
						tmp.searchTxtBox = tmp.newRow.down('.search-txt');
						tmp.resultList = new Element('div', {'style': 'overflow: auto; max-height: 400px;', 'class': 'selectProductPanel'});
						tmp.result.items.each(function(product) {
							tmp.resultList.insert({'bottom': tmp.me._getSearchPrductResultRow(product, tmp.searchTxtBox,tmp.lastRow,tmp.newRow) });
						});
						tmp.resultList.addClassName('list-group');
						tmp.me.showModalBox('Products that has: ' + tmp.searchTxt, tmp.resultList, false);
					} else {
						tmp.data.product = tmp.result.items[0];
						if(!tmp.me._checkProductExist(tmp.data.product))
							tmp.me._selectProduct(tmp.data.product,tmp.lastRow);
						else
							tmp.me.showModalBox('Product ' + tmp.data.product.name + ' already in the list!', '<b>SKU</b>: ' + tmp.data.product.sku + ', <b>Name</b>: ' + tmp.data.product.name, false);
						$$('[save-item="stockOnPO"]').first().click();
					}
				} catch(e) {
					tmp.resultList.update(tmp.me.getAlertBox('Error: ', e).addClassName('alert-danger'));
				}
			}
			,'onComplete': function(sender, param) {
				jQuery('#' + tmp.me.getHTMLID('barcodeInput')).button('reset');
			}
		});
		return tmp.me;
	}
	,_checkProductExist: function(product) {
		var tmp = {};
		tmp.me = this;
		tmp.ifExist = false;
		$(tmp.me.getHTMLID('productsTable')).select('.item_row:not(.item-title-row, .new-order-item-input)').each(function(item){
			if(!tmp.ifExist && item.retrieve('data').id === product.id) {
				tmp.ifExist = true;
			}
		});
		return tmp.ifExist;
	}
	,_selectProduct: function(product,lastRow) {
		var tmp = {};
		tmp.me = this;
		tmp.data = [];
		tmp.product = product;
		tmp.lastRow = lastRow;

		tmp.btn = $('barcode_input');
		tmp.me._signRandID(tmp.btn);

		tmp.data = {
				'product': tmp.product,
				'btns': new Element('span', {'class': 'pull-right'})
					.insert({'bottom': new Element('span', {'class': 'btn btn-danger btn-xs'})
					.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-trash'}) })
					.observe('click', function(event) {
						Event.stop(event);
						if(!confirm('You are about to remove this entry.\n\nContinue?'))
							return;
						tmp.row = $(this).up('.item_row');
						tmp.row.remove();
					})
				}),
				'qty': 0
			};
		tmp.lastRow.replace(tmp.newRow = tmp.me._getProductRow(tmp.data, false) );
		return tmp.me;
	}
	/**
	 * Getting the search product result row
	 */
	,_getSearchPrductResultRow: function(product, searchTxtBox,lastRow,newRow) {
		var tmp = {};
		tmp.me = this;
		tmp.lastRow = lastRow;
		tmp.newRow = newRow;
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

				})
			})
			.observe('click', function(){
				tmp.inputRow = $(searchTxtBox).up('.new-order-item-input').store('product', product);
				if(!tmp.me._checkProductExist(product)) {
					tmp.me._selectProduct(product,tmp.lastRow);
					jQuery('#' + tmp.me.modalId).modal('hide');
					$$('[save-item="stockOnPO"]').first().click();
				}
				else {
					jQuery('#' + tmp.me.modalId).modal('hide');
					tmp.me.showModalBox('Product ' + product.name + ' already in the list!', '<b>SKU</b>: ' + product.sku + ', <b>Name</b>: ' + product.name, false);
				}
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
		tmp.newDiv = new Element('span', {'class': 'btn-group pull-right'})
			.insert({'bottom': new Element('span', {'class': 'btn btn-primary', 'data-loading-text' : 'saving...'})
				.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-ok-circle'}) })
				.insert({'bottom': new Element('span').update(' save ') })
				.observe('click', function() {
					tmp.me._submitQuantities($(this));
				})
			})
			.insert({'bottom': new Element('span', {'class': 'btn btn-default'})
				.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-remove-sign'}) })
				.insert({'bottom': new Element('span').update(' cancel ') })
				.observe('click', function(){
					tmp.me.showModalBox('<strong class="text-danger">Cancelling this Quantity adjustment</strong>',
							'<div>You are about to cancel this quantity adjustment process, all input data will be lost.</div><br /><div>Continue?</div>'
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
	 * Ajax: collect data and post ajax
	 */
	,_submitQuantities: function(btn) {
		var tmp = {};
		tmp.me = this;
		tmp.btn = btn;
		tmp.data = tmp.me._collectFormData($(tmp.me.getHTMLID('itemDiv')),'save-order');
		tmp.data.products = [];
		$(tmp.me.getHTMLID('productsTable')).getElementsBySelector('div.item_row').each(function(item) {
			tmp.product = item.retrieve('data');
			tmp.formData = tmp.me._collectFormData(item, 'save-item');
			if(tmp.product.id) {
				tmp.data.products.push({
					'productId': tmp.product.id
					, 'stockOnPO': tmp.formData.stockOnPO
					, 'stockOnOrder': tmp.formData.stockOnOrder
					, 'stockOnHand': tmp.formData.stockOnHand
					, 'stockInRMA': tmp.formData.stockInRMA
					, 'stockInParts': tmp.formData.stockInParts
					, 'totalInPartsValue': tmp.me.getValueFromCurrency(tmp.formData.totalInPartsValue)
					, 'totalOnHandValue': tmp.me.getValueFromCurrency(tmp.formData.totalOnHandValue)
				});
			}
		});
		if(tmp.data === null)
			return tmp.me;
		if(!tmp.data.products.length) {
			tmp.me.showModalBox('<strong class="text-danger">Error</strong>', 'At least one item is needed!', true);
			return tmp.me;
		}
		tmp.me._signRandID(tmp.btn);
		tmp.me.postAjax(tmp.me.getCallbackId('saveQuantities'), tmp.data, {
			'onCreate': function(sender, param) {
				jQuery('#' + tmp.btn.id).button('loading');
			}
			,'onSuccess': function(sender, param) {
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result || !tmp.result.items|| !tmp.result.items.size() < 0)
						return;
					tmp.me.showModalBox('<strong class="text-success">Success!</strong>', '<h3>Saved successfully</h3>', true);
					window.location = document.URL;
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
	 * Getting the parts panel
	 */
	,_getProductsTable: function () {
		var tmp = {};
		tmp.me = this;
		//header row
		tmp.productListDiv = new Element('div', {'class': 'list-group', 'id': tmp.me.getHTMLID('productsTable')})
			.insert({'bottom': tmp.newDiv = tmp.me._getProductRow({'product': {'sku': 'SKU', 'name': 'Product Name', 'stockOnPO': 'S: PO', 'stockOnHand': 'S: Hand', 'stockOnOrder': 'S: Order', 'stockInRMA': 'S: RMA', 'stockInParts': 'S: Parts', 'totalInPartsValue': 'V: Parts', 'totalOnHandValue': 'V: Hand'} }, true).addClassName('item-title-row') });
		tmp.newDiv.setStyle({cursor:'pointer'});
		tmp.productListDiv.insert({'bottom': tmp.me._getNewProductRow()});
		return new Element('div', {'class': 'panel panel-warning'}).insert({'bottom':  tmp.productListDiv});
	}
	,init: function() {
		var tmp = {};
		tmp.me = this;
		$(tmp.me.getHTMLID('itemDiv'))
			.insert({'bottom': tmp.me._getProductsTable() })
			.insert({'bottom': new Element('div', {'class': 'row', 'style': 'padding: 0 15px'})
//				.insert({'bottom': new Element('div', {'class': 'col-sm-1'})
//					.insert({'bottom': new Element('label', {'class': 'control-label'}).update('Comment: ') })
//				})
//				.insert({'bottom': new Element('div', {'class': 'col-sm-9'})
//					.insert({'bottom': new Element('textarea', {'save-order': 'comments', 'style': 'height:33px; width: 100%;'}) })
//				})
				.insert({'bottom': new Element('div', {'class': 'col-sm-2 pull-right'}).update(tmp.me._saveBtns()) })
			});
		$(tmp.me.getHTMLID('itemDiv')).down('#'+tmp.me.getHTMLID('barcodeInput')).focus();
	}
});

