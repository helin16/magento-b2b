/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new BPCPageJs(), {
	_htmlIds: {'itemDiv': '', 'searchPanel': 'search_panel'}
	,_customer: null
	,_item: null
	,_searchTxtBox: null
	,_applyToOptions: null
	/**
	 * Setting the HTMLIDS
	 */
	,setHTMLIDs: function(itemDivId) {
		this._htmlIds.itemDiv = itemDivId;
		return this;
	}
	/**
	 * submitting order to php
	 */
	,_submitOrder: function(btn, data) {
		var tmp = {};
		tmp.me = this;
		tmp.modalBoxPanel = $(btn).up('.modal-content');
		tmp.modalBoxTitlePanel = tmp.modalBoxPanel.down('.modal-title');
		tmp.oldTitlePanel = tmp.modalBoxTitlePanel.clone(true);
		tmp.modalBoxBodyPanel = tmp.modalBoxPanel.down('.modal-body');
		tmp.loadingDiv = new Element('div').update('<h4>Submitting the data, please be patient.</h4><div><h3 class="fa fa-spinner fa-spin"></h3></div>');
		tmp.me.postAjax(tmp.me.getCallbackId('saveOrder'), data, {
			'onLoading': function(sender, param) {
				tmp.modalBoxTitlePanel.update('Submiting data, please wait...');
				tmp.modalBoxBodyPanel.insert({'bottom': tmp.loadingDiv});
				tmp.modalBoxBodyPanel.down('.confirm-panel').hide().getElementsBySelector('.result-msg').each(function(el){ el.remove(); });
			}
			,'onSuccess': function(sender, param) {
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result || !tmp.result.item || !tmp.result.redirectURL)
						return;
					tmp.me._item = tmp.result.item;
					jQuery('#' + tmp.me.modalId).on('hide.bs.modal', function() {
						window.location = tmp.result.redirectURL;
					});
					tmp.me.disableAll();
					tmp.modalBoxTitlePanel.update('<strong class="text-success">Success!</strong>');
					tmp.succDiv = new Element('div')
						.insert({'bottom': new Element('h4', {'class': 'text-success'}).update('Saved Successfull.') })
						.insert({'bottom': new Element('div').update(new Element('div', {'class': 'btn btn-primary col-xs-6 col-xs-offset-3'})
							.update('OK')
							.observe('click', function(){
								window.location = tmp.result.redirectURL;
							})
						) });
					tmp.modalBoxBodyPanel.update(tmp.succDiv);
					if(tmp.result.printURL) {
						tmp.printWindow = window.open(tmp.result.printURL, 'Printing Order', 'width=1300, location=no, scrollbars=yes, menubar=no, status=no, titlebar=no, fullscreen=no, toolbar=no');
						if(!tmp.printWindow)
							throw '<h4>Your browser has block the popup window, please enable it for further operations.</h4><a href="' + tmp.result.printURL + '" target="_BLANK"> click here for now</a>';
					}
				} catch(e) {
					tmp.modalBoxTitlePanel.replace(tmp.oldTitlePanel);
					tmp.modalBoxBodyPanel.down('.confirm-panel').insert({'top': tmp.me.getAlertBox('', e).addClassName('alert-danger result-msg') }).show();
				}
			}
			,'onComplete': function(sender, param) {
				tmp.loadingDiv.remove();
			}
		});
		return tmp.me;
	}
	,disableAll: function(reload) {
		reload = (reload || false);
		jQuery('input').attr("disabled", true);
		jQuery('textarea').attr("disabled", true);
		jQuery('.btn').attr("disabled", true);
		jQuery('.form-control').attr("disabled", true);
		if(reload !== false)
			window.location = document.URL
	}
	,_getShowStatusSelBox: function(forAll) {
		var tmp = {};
		tmp.me = this;
		tmp.forAll = (forAll || false);
		tmp.newSelBoxDiv = new Element('span', {'class': 'form-group'})
			.insert({'bottom': new Element('select', {'class': 'parts-confirm-status', 'required': true})
				.insert({'bottom': new Element('option', {'value': ''}).update('') })
				.insert({'bottom': new Element('option', {'value': 'StockOnHand', 'title': 'Goods will be return to StockOnHand'}).update('BrandNew, sealed') })
				.insert({'bottom': new Element('option', {'value': 'StockOnRMA', 'title': 'Goods will be return to StockOnRMA'}).update('UnSealed / Not Sure') })
				.observe('change', function(){
					tmp.msg = '';
					tmp.selectedIndex = $(this).selectedIndex;
					if(tmp.selectedIndex >= 0)
						tmp.msg  = $(this).options[tmp.selectedIndex].title;
					if(tmp.forAll === true) {
						$(this).up('.parts-confirm-table').down('tbody').getElementsBySelector('.parts-confirm-status').each(function(selBox) {
							selBox.options[tmp.selectedIndex].selected = true;
							selBox.insert({'after': new Element('small', {'class': 'msg'}).update(new Element('em').update(tmp.msg) ) })
						});
					} else {
						tmp.cell = $(this).up();
						if(tmp.cell.down('.msg'))
							tmp.cell.down('.msg').remove();
						tmp.cell.insert({'bottom': new Element('small', {'class': 'msg'}).update(new Element('em').update(tmp.msg) ) });
						tmp.allSel = tmp.cell.up('.table').down('thead .parts-confirm-status');
						if(tmp.allSel && tmp.allSel.selectedIndex !== tmp.selectedIndex)
							tmp.allSel.options[0].selected = true;
					}
				})
			});
		return tmp.newSelBoxDiv;
	}
	/**
	 * Getting the confirmation parts table
	 */
	,_getConfirmPartsTable: function(items, showStatus) {
		var tmp = {};
		tmp.me = this;
		tmp.showStatus = (showStatus || false);
		tmp.newTable = new Element('table', {'class': 'table table-hover table-condensed parts-confirm-table'})
			.insert({'bottom': new Element('thead')
				.insert({'bottom': new Element('tr')
					.insert({'bottom': new Element('th', {'class': 'col-xs-4'}).update('Product') })
					.insert({'bottom': new Element('th', {'class': 'col-xs-2'}).update('Qty') })
					.insert({'bottom': tmp.showStatus !== true ? '' : new Element('th').update('All: ').insert({'bottom': tmp.me._getShowStatusSelBox(true)}) })
				})
			})
			.insert({'bottom': tmp.tbody = new Element('tbody') });
		items.each(function(item){
			tmp.tbody.insert({'bottom': new Element('tr', {'class': 'parts-confirm-row'})
				.store('data', item)
				.insert({'bottom': new Element('td').update(item.product.sku) })
				.insert({'bottom': new Element('td').update(item.qtyOrdered) })
				.insert({'bottom': tmp.showStatus !== true ? '' : new Element('td').update(tmp.me._getShowStatusSelBox()) })
			});
		});
		return tmp.newTable;
	}
	/**
	 * confirming before submit
	 */
	,_confirmSubmit: function(btn, data, items) {
		var tmp = {};
		tmp.me = this;
		tmp.modalWrapper =$(btn).up('.modal-content');
		tmp.modalWrapper.down('.modal-title').update('<strong class="text-success">Please confirm the condition of returned goods</strong>');
		tmp.newDiv = new Element('div', {'class': 'panel confirm-panel'})
			.insert({'bottom': new Element('div')
				.insert({'bottom': new Element('h4', {'class': 'text-success'}).update('What are the confiditions of these returned goods?') })
				.insert({'bottom': tmp.partsTable = tmp.me._getConfirmPartsTable(items, true) })
			})
			.insert({'bottom': new Element('div')
				.insert({'bottom': new Element('div', {'class': 'btn btn-primary'})
					.update('Save')
					.observe('click', function() {
						tmp.btn = this;
						tmp.hasError = false;
						tmp.index = 0;
						tmp.partsTable.getElementsBySelector('.parts-confirm-row').each(function(tr){
							tmp.selBox = tr.down('.parts-confirm-status');
							if(tmp.selBox) {
								if($F(tmp.selBox).blank()) {
									tmp.hasError = true;
									tmp.me._markFormGroupError(tmp.selBox, 'Select one please.');
								} else {
									if(data.items[tmp.index])
										data.items[tmp.index].stockData = $F(tmp.selBox);
								}
							}
							tmp.index = tmp.index * 1 + 1;
						});
						if(tmp.hasError === false)
							tmp.me._submitOrder(tmp.btn, data);
					})
				})
				.insert({'bottom': new Element('div', {'class': 'btn btn-default pull-right'})
					.update('Cancel')
					.observe('click', function() {
						tmp.me.hideModalBox();
					})
				})
			});
		tmp.modalWrapper.down('.modal-body').update(tmp.newDiv);
		return tmp.me
	}
	/**
	 * pre confirming submit
	 */
	,_preConfirmSubmit: function(printit) {
		var tmp = {};
		tmp.me = this;
		tmp.data = tmp.me._collectFormData($(tmp.me._htmlIds.itemDiv),'save-order');
		tmp.data.totalPaidAmount = tmp.me.getValueFromCurrency(tmp.data.totalPaidAmount);
		if(tmp.data === null)
			return null;
		if(tmp.data.applyTo.blank()) {
			tmp.me.hideModalBox();
			tmp.me.showModalBox('Warning', 'You MUST select what this credit note is <strong>Apply To</strong>');
			return null;
		}
		tmp.data.printIt = (printit === true ? true : false);
		tmp.data.customer = {'id': tmp.me._customer.id};
		tmp.data.creditNoteId = tmp.me._creditNote && tmp.me._creditNote.id ? tmp.me._creditNote.id : '';
		tmp.data.orderId = tmp.me._order && tmp.me._order.id ? tmp.me._order.id : '';
		tmp.shippAddrPanel = $$('.shipping-address.address-div').first();
		if(tmp.shippAddrPanel) {
			tmp.shippAddr = tmp.me._collectFormData(tmp.shippAddrPanel,'address-editable-field');
			if(tmp.shippAddr === null) { //some error in the shipping address
				tmp.me.hideModalBox();
				tmp.me.showModalBox('Warning', 'some error in the shipping address');
				return null;
			}
			tmp.data.shippingAddr = tmp.shippAddr;
		}
		tmp.data.items = [];
		tmp.originalItems = [];
		$$('.order-item-row').each(function(item){
			tmp.itemData = item.retrieve('data');
			tmp.originalItems.push(tmp.itemData);
			tmp.data.items.push({'orderItemId': item.readAttribute('orderItemId'), 'creditNoteItemId': item.readAttribute('creditNoteItemId'), 'valid': item.visible(), 'product': {'id': tmp.itemData.product.id}, 'itemDescription': tmp.itemData.itemDescription,'unitPrice': tmp.itemData.unitPrice, 'qtyOrdered': tmp.itemData.qtyOrdered, 'totalPrice': tmp.itemData.totalPrice, 'serials': item.retrieve('serials') });
		});
		tmp.noValidItem = true;
		tmp.data.items.each(function(item){
			if(tmp.noValidItem === true && item.valid === true)
				tmp.noValidItem = false;
		})
		if(tmp.noValidItem === true) {
			tmp.me.hideModalBox();
			tmp.me.showModalBox('<strong class="text-danger">Error</strong>', '<strong>At least one Credit Note Item</strong> is needed!', true);
			return null;
		}
		tmp.newDiv = new Element('div', {'class': 'confirm-div'})
			.insert({'bottom': new Element('div')
				.insert({'bottom': new Element('h4', {'class': 'text-warning'}).update('Have the customer returned all the goods on this credit note already?') })
				.insert({'bottom': tmp.me._getConfirmPartsTable(tmp.originalItems) })
			})
			.insert({'bottom': new Element('div')
				.insert({'bottom': new Element('span', {'class': 'btn btn-primary'})
					.update('YES')
					.observe('click', function() {
						tmp.me._confirmSubmit(this, tmp.data, tmp.originalItems);
					})
				})
				.insert({'bottom': new Element('span', {'class': 'btn btn-default pull-right'})
					.update('NO')
					.observe('click', function() {
						tmp.modalWrapper =$(this).up('.modal-content');
						tmp.modalWrapper.down('.modal-title').update('<strong class="text-danger">Stop Now!</strong>');
						tmp.modalWrapper.down('.modal-body').update('')
							.insert({'bottom': new Element('div', {'class': 'panel'})
								.insert({'bottom': new Element('div').update('<h4 class="text-danger">You need to create a RMA ' +
										(tmp.me._order && tmp.me._order.orderNo ? '<a href="/orderdetails/' + tmp.me._order.id + '.html" target="_BLANK">' + tmp.me._order.orderNo + '</a>' : '') +
									' first, and waiting until the goods are back then create a credit note against those returned goods!</h4>')
								})
								.insert({'bottom': new Element('div')
									.insert({'bottom': new Element('div', {'class': 'btn btn-primary col-sm-6 col-sm-offset-3'})
										.update('OK')
										.observe('click', function() {
											tmp.me.hideModalBox();
										})
									})
								})
							});
					})
				})
			})
		tmp.me.showModalBox("You're about to save a credit note for : " + tmp.me._customer.name, tmp.newDiv);

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
					.insert({'bottom': new Element('span').update(' Save ') })
					.observe('click', function() {
						tmp.me._preConfirmSubmit();
					})
				})
			})
			.insert({'bottom': new Element('div', {'class': 'btn btn-default'}).setStyle('display: none;')
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
			});
		tmp.newDiv.down('.btn-group').removeClassName('btn-group');
		return tmp.newDiv;
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
		tmp.itemDescription = $F(tmp.currentRow.down('[new-order-item=itemDescription]')).replace(/\n/g, "<br />");
		//get all data
		tmp.data = {
			'product': tmp.product,
			'itemDescription': tmp.itemDescription,
			'unitPrice': tmp.me.getCurrency(tmp.unitPrice),
			'qtyOrdered': tmp.qtyOrdered,
			'discount' : tmp.discount,
			'margin': tmp.me.getCurrency(parseFloat(tmp.totalPrice) - parseFloat(tmp.product.unitCost * 1.1 * tmp.qtyOrdered)),
			'totalPrice': tmp.me.getCurrency(tmp.totalPrice)
		};
		tmp.currentRow.up('.list-group').insert({'bottom': tmp.itemRow = tmp.me._getProductRow(tmp.data) });
		tmp.newRow = tmp.me._getNewProductRow();
		tmp.currentRow.replace(tmp.newRow).addClassName();
		tmp.newRow.down('[new-order-item=product]').focus();

		tmp.me._recalculateSummary();
		return tmp.me;
	}
	/**
	 * Ajax: searching the product based on a string
	 */
	,_searchProduct: function(btn, pageNo, afterFunc) {
		var tmp = {};
		tmp.me = this;
		tmp.btn = btn;
		tmp.showMore = $(btn).retrieve('showMore') === true ? true : false;
		tmp.pageNo = (pageNo || 1);
		tmp.me._signRandID(tmp.btn);
		tmp.searchTxtBox = !$(tmp.btn).up('.product-autocomplete') || !$(tmp.btn).up('.product-autocomplete').down('.search-txt') ? $($(tmp.btn).retrieve('searchBoxId')) : $(tmp.btn).up('.product-autocomplete').down('.search-txt');

		tmp.me._signRandID(tmp.searchTxtBox);
		tmp.searchTxt = $F(tmp.searchTxtBox);

		tmp.me.postAjax(tmp.me.getCallbackId('searchProduct'), {'searchTxt': tmp.searchTxt, 'pageNo': tmp.pageNo}, {
			'onLoading': function() {
				jQuery('#' + tmp.btn.id).button('loading');
				jQuery('#' + tmp.searchTxtBox.id).button('loading');
			}
			,'onSuccess': function(sender, param) {
				if(tmp.showMore === false)
					tmp.resultList = new Element('div', {'class': 'search-product-list'});
				else
					tmp.resultList = $(btn).up('.search-product-list');
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
				if(typeof(afterFunc) === 'function')
					afterFunc();
				if(tmp.result.pagination.pageNumber < tmp.result.pagination.totalPages) {
					tmp.resultList.insert({'bottom': new Element('a', {'class': 'item-group-item'})
						.insert({'bottom': new Element('span', {'class': 'btn btn-primary', 'data-loading-text': 'Getting more ...'}).update('Show Me More') })
						.observe('click', function(){
							tmp.newBtn = $(this);
							$(tmp.newBtn).store('searchBoxId', tmp.searchTxtBox.id);
							$(tmp.newBtn).store('showMore', true);
							tmp.me._searchProduct(this, tmp.pageNo * 1 + 1, function() {
								$(tmp.newBtn).remove();
							});
						})
					});
				}
				if(tmp.showMore === false)
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
					.insert({'bottom': new Element('strong').update( new Element('a', {'href': '/product/' + id + '.html', 'target': '_BLANK'}).update(product.name) )
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
					.update(new Element('a', {'href': '/product/' + product.id + '.html', 'target': '_BLANK'}).update(product.sku))
					.removeClassName('col-xs-8')
					.addClassName('col-xs-3')
					.insert({'bottom': new Element('a', {'href': 'javascript: void(0);', 'class': 'text-danger pull-right', 'title': 'click to change the product'})
						.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-remove'})  })
						.observe('click', function() {
							tmp.newRow = tmp.me._getNewProductRow();
							$(this).up('.new-order-item-input').replace(tmp.newRow);
							tmp.newRow.down('[new-order-item=product]').select();
						})
					})
					.insert({'after': new Element('div', {'class': 'col-xs-5'})
						.update(new Element('textarea', {'new-order-item': 'itemDescription'}).setStyle('width: 100%').update(product.name))
					});
				jQuery('#' + tmp.me.modalId).modal('hide');
				tmp.retailPrice = product.prices.size() === 0 ? 0 : product.prices[0].price;
				tmp.inputRow.down('[new-order-item=unitPrice]').writeAttribute('value', tmp.me.getCurrency(tmp.retailPrice)).select();
				tmp.me._calculateNewProductPrice(tmp.inputRow);
			})
			;
		return tmp.newRow;
	}
	,_selectProduct: function(creditNoteItem) {
		var tmp = {};
		tmp.me = this;
		//get all data
		tmp.data = {
			'product': creditNoteItem.product,
			'itemDescription': creditNoteItem.itemDescription,
			'unitPrice': tmp.me.getCurrency(creditNoteItem.unitPrice),
			'qtyOrdered': creditNoteItem.qty ? creditNoteItem.qty : creditNoteItem.qtyOrdered,
			'discount' : 100,
			'margin': 0,
			'totalPrice': tmp.me.getCurrency(creditNoteItem.unitPrice * (creditNoteItem.qty ? creditNoteItem.qty : creditNoteItem.qtyOrdered))
		};
		$$('.order_change_details_table').first().insert({'bottom': tmp.newDiv = tmp.me._getProductRow(tmp.data) });

		tmp.me._recalculateSummary();
		return tmp.newDiv;
	}
	/**
	 * Getting summary footer for the parts list
	 */
	,_getSummaryFooter: function() {
		var tmp = {};
		tmp.me = this;
		tmp.ifNew = !(tmp.me._creditNote && tmp.me._creditNote.id);
		tmp.applyTo = new Element('select', {'class': 'select2 form-control input-sm', 'save-order': 'applyTo', 'data-placeholder': 'Select an option'}).insert({'bottom': new Element('option', {'value': ''}).update('') });
		tmp.me._applyToOptions.each(function(option){
			tmp.applyTo.insert({'bottom': tmp.option = new Element('option', {'value': option}).update(option) });
			if(tmp.me._creditNote && tmp.me._creditNote.applyTo == option)
				tmp.option.writeAttribute('selected', true);
		});
		tmp.paymentMethodSel = !tmp.ifNew ? 'Total Paid' : new Element('select', {'class': 'form-control input-sm', 'save-order': 'paymentMethodId'})
			.insert({'bottom': new Element('option', {'value': ''}).update('Paid Via:') });
		if(tmp.ifNew) {
			tmp.me._paymentMethods.each(function(method){
				tmp.paymentMethodSel.insert({'bottom': new Element('option', {'value': method.id}).update(method.name) });
			});
			tmp.paymentMethodSel.observe('change', function() {
				tmp.btn = this;
				$(tmp.btn).up('.row').down('.input-field').update($F(tmp.btn).blank() ? tmp.me.getCurrency(0) : new Element('span', {'class': 'form-group'}).update(
					tmp.paidAmountBox = new Element('input', {'value': ($F(tmp.btn).strip() === '5' ? '0' : ''), 'order-price-summary': 'totalPaidAmount', 'class': 'form-control input-sm', 'save-order': 'totalPaidAmount', 'placeholder': tmp.me.getCurrency(0), 'required': true, 'validate_currency': 'Invalid number provided!' })
					.observe('keyup', function() {
						tmp.me._recalculateSummary();
					})
				) );
				tmp.me._recalculateSummary();
				if(tmp.paidAmountBox)
					tmp.paidAmountBox.select();
			})
		}
		tmp.me.paymentListPanelJs = new PaymentListPanelJs(tmp.me, undefined, tmp.me._creditNote, true, false);
		tmp.newDiv = new Element('div', {'class': 'panel-footer'})
			.insert({'bottom': new Element('div', {'class': 'row list-group-item-danger'})
				.insert({'bottom': new Element('div', {'class': tmp.ifNew ? 'col-sm-8' : 'col-sm-4'})
						.insert({'bottom': tmp.me._getFormGroup( 'Description:', new Element('textarea', {'save-order': 'description', 'rows': 5}).update(tmp.me._creditNote ? tmp.me._creditNote.description : '') ) })
					})
				.insert({'bottom': tmp.ifNew ? '' : new Element('div', {'class': 'col-sm-4'})
					.insert({'bottom': tmp.me.paymentListPanelJs.getPaymentListPanel() })
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
					.insert({'bottom': new Element('div', {'class': 'row', 'style': 'border-bottom: 1px solid brown'}).setStyle('display: none;')
						.insert({'bottom': new Element('div', {'class': 'col-xs-6 text-right'}).update( new Element('strong').update('Total Discount:') ) })
						.insert({'bottom': new Element('div', {'order-price-summary': 'totalDiscount', 'class': 'col-xs-6'}).update( tmp.me.getCurrency(0) ) })
					})
					.insert({'bottom': new Element('div', {'class': 'row'})
						.insert({'bottom': new Element('div', {'class': 'col-xs-6 text-right'}).update( new Element('strong').update('Sub Total Incl. GST: ') ) })
						.insert({'bottom': new Element('div', {'order-price-summary': 'totalPriceIncludeGST', 'class': 'col-xs-6'}).update( tmp.me.getCurrency(0) ) })
					})
					.insert({'bottom': new Element('div', {'class': 'row', 'style': 'border-bottom: 1px solid brown'})
						.insert({'bottom': new Element('div', {'class': 'col-xs-6 text-right'}).update( new Element('strong').update('Apply To: ') ) })
						.insert({'bottom': new Element('div', {'class': 'col-xs-6'}).update( new Element('strong').update(tmp.applyTo)) })
					})
					.insert({'bottom': new Element('div', {'class': 'row', 'style': 'border-bottom: 1px solid brown'})
						.insert({'bottom': new Element('div', {'class': 'col-xs-6 text-right'}).update( new Element('strong').update(tmp.paymentMethodSel) ) })
						.insert({'bottom': new Element('div', {'class': 'col-xs-6 input-field'}).update( tmp.me.getCurrency(tmp.ifNew ? 0 : tmp.me._creditNote.totalPaid) ) })
					})
					.insert({'bottom': new Element('div', {'class': 'row'})
						.insert({'bottom': new Element('div', {'class': 'col-xs-6 text-right'}).update( new Element('strong').update('Total Incl. GST:') ) })
						.insert({'bottom': new Element('strong', {'order-price-summary': 'subTotal', 'class': 'col-xs-6'}).update( tmp.me.getCurrency(0) ) })
					})
					.insert({'bottom': new Element('div', {'class': 'row'})
						.insert({'bottom': new Element('h4', {'class': 'col-xs-6 text-right'}).setStyle('color: #d30014 !important').update(new Element('strong').update('DUE:')) })
						.insert({'bottom': new Element('h4', {'class': 'col-xs-6', 'order-price-summary': 'total-payment-due'}).setStyle('color: #d30014 !important').update(tmp.me.getCurrency(0)) })
					})
					.insert({'bottom': new Element('div', {'class': 'row margin'}).setStyle('display: none;')
						.insert({'bottom': new Element('strong', {'class': 'col-xs-6 text-right'}).update(new Element('strong').update('Margin Total Incl. GST:')) })
						.insert({'bottom': new Element('strong', {'class': 'col-xs-6', 'order-price-summary': 'total-margin'}).update(tmp.me.getCurrency(0)) })
					})
				})
			});
		return tmp.newDiv;
	}
	/**
	 * Getting the autocomplete input box for product
	 */
	,_getNewProductProductAutoComplete: function() {
		var tmp = {};
		tmp.me = this;
		tmp.skuAutoComplete = tmp.me._getFormGroup( null, new Element('div', {'class': 'input-group input-group-sm product-autocomplete'})
			.insert({'bottom': new Element('input', {'class': 'form-control search-txt visible-xs visible-sm visible-md visible-lg init-focus', 'new-order-item': 'product', 'required': true, 'placeholder': 'search SKU, NAME and any BARCODE for this product'})
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
			,'discount': tmp.me._getFormGroup( null, new Element('input', {'class': 'input-sm', 'new-order-item': 'discount', 'value': '0'}).setStyle('display: none;')
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
		return tmp.me._getProductRow(tmp.data, false).addClassName('new-order-item-input list-group-item-danger').removeClassName('order-item-row');
	}
	/**
	 * Getting each product row
	 */
	,_getProductRow: function(orderItem, isTitleRow) {
		var tmp = {};
		tmp.me = this;
		tmp.isTitle = (isTitleRow || false);
		tmp.tag = (tmp.isTitle === true ? 'strong' : 'div');
		tmp.btns = orderItem.btns ? orderItem.btns : new Element('span', {'class': 'pull-right'})
			.insert({'bottom': new Element('span', {'class': 'btn btn-danger btn-xs item-del-btn'})
				.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-trash'}) })
				.observe('click', function() {
					if(!confirm('You remove this entry.\n\nContinue?'))
						return;
					tmp.row = $(this).up('.item_row');
					if(tmp.row) {
						if(jQuery.isNumeric(tmp.row.readAttribute('creditNoteItemId')))
							tmp.row.hide();
						else tmp.row.remove();
					}
					tmp.me._recalculateSummary();
				})
			});
		tmp.row = new Element('div', {'class': ' list-group-item ' + (tmp.isTitle === true ? 'list-group-item-danger' : 'item_row order-item-row')})
			.store('data',orderItem)
			.insert({'bottom': new Element('div', {'class': 'row'})
				.store('data', orderItem)
				.insert({'bottom': new Element(tmp.tag, {'class': 'productName col-xs-8'})
					.insert({'bottom': orderItem.itemDescription ? orderItem.itemDescription : orderItem.product.name })
				})
				.insert({'bottom': new Element(tmp.tag, {'class': 'uprice col-xs-1'})
					.insert({'bottom': (orderItem.unitPrice) })
				})
				.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-1'})
					.insert({'bottom': new Element('div')
						.insert({'bottom': new Element('div', {'class': 'qty col-xs-12'}).update(orderItem.qtyOrdered) })
						.insert({'bottom': new Element('div', {'class': 'discount col-xs-6'}).update(orderItem.discount).setStyle('display: none;') })
					})
				})
				.insert({'bottom': new Element(tmp.tag, {'class': 'tprice col-xs-1'})
					.insert({'bottom': (orderItem.totalPrice) })
				})
				.insert({'bottom': new Element(tmp.tag, {'class': 'margin col-xs-1 text-right'}).update(orderItem.margin).setStyle('display: none;')})
				.insert({'bottom': new Element(tmp.tag, {'class': 'btns col-xs-1 text-right'}).update(tmp.btns) })
			});
		if(orderItem.product.sku) {
			tmp.row.down('.productName')
				.removeClassName('col-xs-8')
				.addClassName('col-xs-6')
				.insert({'before': new Element(tmp.tag, {'class': 'productSku col-xs-2'})
					.update( tmp.isTitle === true ? 'SKU' : new Element('a', {'href': '/product/' + orderItem.product.id + '.html', 'target': '_BLANK'}).update(orderItem.product.sku) )
				});
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
				,'btns': new Element('div').setStyle('display:none;')
					.insert({'bottom': new Element('label', {'for': 'hide-margin-checkbox'}).update('Show Margin ') })
					.insert({'bottom': new Element('input', {'id': 'hide-margin-checkbox', 'type': 'checkbox', 'checked': true})
						.observe('click', function(){
							jQuery('.margin').toggle();
						})
					})
				}, true)
			});
		// tbody
		tmp.productListDiv.insert({'bottom': tmp.me._getNewProductRow().addClassName('list-group-item-danger') });
		return tmp.productListDiv;
	}
	/**
	 * Getting the address div
	 */
	,_getAddressDiv: function(title, addr, editable) {
		var tmp = {};
		tmp.me = this;
		tmp.address = (addr || null);
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
							tmp.editable !== true ? addr.contactName : new Element('input', {'address-editable-field': 'contactName', 'class': 'form-control input-sm', 'placeholder': 'The name of contact person',  'value': tmp.address.contactName ? tmp.address.contactName : ''})
						) })
						.insert({'bottom': new Element('div', {'class' : 'col-sm-6'}).update(
								tmp.editable !== true ? addr.contactNo : new Element('input', {'address-editable-field': 'contactNo', 'class': 'form-control input-sm', 'placeholder': 'The contact number of contact person',  'value': tmp.address.contactNo ? tmp.address.contactNo : ''})
						) })
					})
				})
				.insert({'bottom': new Element('dt').update(
					new Element('span', {'class': "glyphicon glyphicon-map-marker", 'title': "Address"})
				) })
				.insert({'bottom': new Element('dd')
					.insert({'bottom': new Element('div')
						.insert({'bottom': tmp.editable !== true ? addr.street : new Element('div', {'class': 'street col-sm-12'}).update(
								new Element('input', {'address-editable-field': 'street', 'class': 'form-control input-sm', 'placeholder': 'Street Number and Street name',  'value': tmp.address.street ? tmp.address.street : ''})
						) })
					})
					.insert({'bottom': new Element('div')
						.insert({'bottom': tmp.editable !== true ? addr.city + ' ' : new Element('div', {'class': 'city col-sm-6'}).update(
								new Element('input', {'address-editable-field': 'city', 'class': 'form-control input-sm', 'placeholder': 'City / Suburb',  'value': tmp.address.city ? tmp.address.city : ''})
						) })
						.insert({'bottom':  tmp.editable !== true ? addr.region + ' ' : new Element('div', {'class': 'region col-sm-3'}).update(
								new Element('input', {'address-editable-field': 'region', 'class': 'form-control input-sm', 'placeholder': 'State / Province',  'value': tmp.address.region ? tmp.address.region : ''})
						) })
						.insert({'bottom': tmp.editable !== true ? addr.postCode: new Element('div', {'class': 'postcode col-sm-3'}).update(
								new Element('input', {'address-editable-field': 'postCode', 'class': 'form-control input-sm', 'placeholder': 'PostCode',  'value': tmp.address.postCode ? tmp.address.postCode : ''})
						) })
					})
					.insert({'bottom': new Element('div')
						.insert({'bottom': tmp.editable !== true ? addr.country: new Element('div', {'class': 'postcode col-sm-4'}).update(
								new Element('input', {'address-editable-field': 'country', 'class': 'form-control input-sm', 'placeholder': 'Country',  'value': tmp.address.country ? tmp.address.country : ''})
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
	,getCustomerInfoPanel: function() {
		var tmp = {};
		tmp.me = this;
		tmp.customer = tmp.me._customer;
		tmp.newDiv = new Element('div', {'class': 'panel panel-danger CustomerInfoPanel'})
			.insert({'bottom': new Element('div', {'class': 'panel-heading'})
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('div', {'class': 'col-sm-8'})
						.insert({'bottom': new Element('strong').update((tmp.me._creditNote && tmp.me._creditNote.id ? 'EDITING' : 'CREATING') + ' CREDIT NOTE FOR:  ') })
						.insert({'bottom': new Element('a', {'href': 'javascript: void(0);'})
							.update(tmp.customer.name)
							.observe('click', function(){
								tmp.me._openCustomerDetailsPage(tmp.customer);
							})
						})
						.insert({'bottom': ' <' })
						.insert({'bottom': new Element('a', {'href': 'mailto:' + tmp.customer.email}).update(tmp.customer.email) })
						.insert({'bottom': '>' })
						.insert({'bottom': tmp.me._order ? new Element('strong').update(' with Order No. ') : '' })
						.insert({'bottom': tmp.me._order ? new Element('a', {'target': '_blank', 'href': '/orderdetails/' + tmp.me._order.id}).update(tmp.me._order.orderNo) : '' })
					})
					.insert({'bottom': new Element('div', {'class': 'col-sm-4 text-right'}).setStyle('display: none;')
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
					.insert({'bottom': tmp.me._getAddressDiv("Billing Address: ", tmp.customer.address ? tmp.customer.address.billing : null).addClassName('col-xs-6') })
					.insert({'bottom': tmp.me._getAddressDiv("Shipping Address: ", tmp.customer.address ? tmp.customer.address.shipping : null, true).addClassName('col-xs-6').addClassName('shipping-address') })
				 })
			});
		return tmp.newDiv;
	}
	,_openCustomerDetailsPage: function(row) {
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
			'href'			: '/customer/' + row.id + '.html?blanklayout=1',
			'beforeClose'	    : function() {
				window.location = document.URL;
			}
 		});
		return tmp.me;
	}
	/**
	 * Getting the div of the order view
	 */
	,getViewOfCreditNote: function() {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = new Element('div')
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('div', {'class': 'col-sm-12'}).update(tmp.me.getCustomerInfoPanel()) })
			})
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('div', {'class': 'col-sm-12'})
					.insert({'bottom': new Element('div', {'class': 'panel panel-danger'}).update(tmp.me._getPartsTable())
						.insert({'bottom': tmp.me._getSummaryFooter() })
					})
				})
			})
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('div', {'class': 'col-sm-12'}).update(tmp.me._saveBtns()) })
			})
			.insert({'bottom': pageJs._creditNote ? new Element('div', {'class': 'row'}).setStyle('padding-top: 20px')
				.insert({'bottom': new Element('div', {'class': 'col-sm-12 comments-div'}) })
				: ''
			});
		return tmp.newDiv;
	}
	,_selectCustomer: function(customer) {
		var tmp = {};
		tmp.me = this;
		tmp.me._customer = customer;
		if($$('.CustomerInfoPanel').size() > 0)
			jQuery('.CustomerInfoPanel').replaceWith(tmp.me.getCustomerInfoPanel());
		$(tmp.me._htmlIds.itemDiv).update(tmp.me.getViewOfCreditNote());
		if($$('.init-focus').size() > 0)
			$$('.init-focus').first().focus();
		return tmp.me;
	}
	/**
	 * Getting the customer row for displaying the searching result
	 */
	,_getCustomerRow: function(row, isTitle) {
		var tmp = {};
		tmp.me = this;
		tmp.isTitle = (isTitle || false);
		tmp.tag = (tmp.isTitle === true ? 'th': 'td');
		tmp.newDiv = new Element('tr', {'class': (tmp.isTitle === true ? 'item_top_row' : 'btn-hide-row item_row') + (row.active == 0 ? ' danger' : ''), 'item_id': (tmp.isTitle === true ? '' : row.id)}).store('data', row)
			.insert({'bottom': new Element(tmp.tag, {'class': 'name col-xs-1'})
				.insert({'bottom': (tmp.isTitle === true ? '&nbsp;':
					new Element('span', {'class': 'btn btn-primary btn-xs'}).update('select')
					.observe('click', function(){
						tmp.me._selectCustomer(row);
					})
				) })
			})
			.insert({'bottom': new Element(tmp.tag, {'class': 'name col-xs-1'}).update(row.name) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'email col-xs-1', 'style': 'text-decoration: underline;'}).update(row.email) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'contact col-xs-1 truncate'}).update(row.contactNo)})
			.insert({'bottom': new Element(tmp.tag, {'class': 'description col-xs-1'}).update(row.description) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'address col-xs-1'})
				.insert({'bottom': tmp.isTitle === true ? row.addresses : new Element('span', {'style': 'display: inline-block'})
					.insert({'bottom': new Element('a', {'class': 'visible-xs visible-md visible-sm visible-lg', 'href': 'javascript: void(0);'})
						.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-plane address-shipping', 'style': 'font-size: 1.3em', 'title': 'Shipping'}) })
						.observe('click', function(){
							tmp.me.showModalBox('<strong>Shipping Address</strong>', row.address.shipping.full)
						})
					})
				})
				.insert({'bottom': tmp.isTitle === true ? '' : new Element('span', {'style': 'display: inline-block'})
					.insert({'bottom':  new Element('a', {'class': 'visible-xs visible-md visible-sm visible-lg', 'href': 'javascript: void(0);'})
						.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-usd address-billing', 'style': 'font-size: 1.3em; padding-left:10%;', 'title': 'Billing'}) })
						.observe('click', function(){
							tmp.me.showModalBox('<strong>Billing Address</strong>', row.address.shipping.full)
						})
					})
				})
			})
			.insert({'bottom': new Element(tmp.tag, {'class': 'mageId col-xs-1'}).update(row.mageId) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'cust_active col-xs-1'})
				.insert({'bottom': (tmp.isTitle === true ? row.active : new Element('input', {'type': 'checkbox', 'disabled': true, 'checked': row.active}) ) })
			});
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
								.insert({'bottom': tmp.me._getCustomerRow({'email': "Email", 'name': 'Name', 'contactNo': 'Contact Num', 'description': 'Description', 'addresses': 'Addresses',
																			'address': {'billing': {'full': 'Billing Address'}, 'shipping': {'full': 'Shipping Address'} },
																			'mageId': "Mage Id", 'active': "Active?"
																			}, true)  })
							})
							.insert({'bottom': tmp.listDiv = new Element('tbody') })
						})
					});
					tmp.result.items.each(function(item) {
						tmp.listDiv.insert({'bottom': tmp.me._getCustomerRow(item) });
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
		tmp.newDiv = new Element('div', {'id': tmp.me._htmlIds.searchPanel, 'class': 'panel panel-danger search-panel'})
			.insert({'bottom': new Element('div', {'class': 'panel-heading form-inline'})
				.insert({'bottom': new Element('strong').update('Creating a new Credit Note for: ') })
				.insert({'bottom': new Element('span', {'class': 'input-group col-sm-6'})
					.insert({'bottom': new Element('input', {'class': 'form-control search-txt init-focus', 'placeholder': 'Customer name'})
						.observe('keydown', function(event){
							tmp.txtBox = this;
							tmp.me.keydown(event, function() {
								if(tmp.txtBox.up('#'+pageJs._htmlIds.searchPanel).down('.item_row')!=undefined && tmp.txtBox.up('#'+pageJs._htmlIds.searchPanel).down('tbody').getElementsBySelector('.item_row').length===1) {
									tmp.txtBox.up('#'+pageJs._htmlIds.searchPanel).down('tbody .item_row .btn').click();
								}
								else $(tmp.me._htmlIds.searchPanel).down('.search-btn').click();
							});
							tmp.me.keydown(event, function() {
								if(tmp.txtBox.up('#'+pageJs._htmlIds.searchPanel).down('.item_row')!=undefined && tmp.txtBox.up('#'+pageJs._htmlIds.searchPanel).down('tbody').getElementsBySelector('.item_row').length===1) {
									tmp.txtBox.up('#'+pageJs._htmlIds.searchPanel).down('tbody .item_row .btn').click();
								}
								else $(tmp.me._htmlIds.searchPanel).down('.search-btn').click();
							}, function(){}, Event.KEY_TAB);
							return false;
						})
					})
					.insert({'bottom': new Element('span', {'class': 'input-group-btn search-btn'})
						.insert({'bottom': new Element('span', {'class': ' btn btn-primary'})
							.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-search'}) })
						})
						.observe('click', function(){
							tmp.btn = this;
							tmp.txtBox = $(tmp.me._htmlIds.searchPanel).down('.search-txt');
							if(!$F(tmp.txtBox).blank())
								tmp.me._searchCustomer(tmp.txtBox);
							else {
								if($(tmp.me._htmlIds.searchPanel).down('.table tbody'))
									$(tmp.me._htmlIds.searchPanel).down('.table tbody').innerHTML = null;
							}
						})
					})
				})
				.insert({'bottom': new Element('span', {'class': 'btn btn-success pull-right btn-sm btn-danger'})
					.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-remove'}) })
					.observe('click', function(){
						$(tmp.me._htmlIds.searchPanel).down('.search-txt').clear().focus();
						$(tmp.me._htmlIds.searchPanel).down('.table tbody').innerHTML = null;
					})
				})
			})
			;
		return tmp.newDiv;
	}
	,init: function() {
		var tmp = {};
		tmp.me = this;
		if(tmp.me._creditNote) {
			if(tmp.me._creditNote.order && tmp.me._creditNote.order.id && jQuery.isNumeric(tmp.me._creditNote.order.id))
				tmp.me._order = tmp.me._creditNote.order;
			tmp.me._selectCustomer(tmp.me._creditNote.customer);
			tmp.me._creditNote.items.each(function(item){
				tmp.me._selectProduct(item).writeAttribute('creditNoteItemId', item.id);
			});
			jQuery('.new-order-item-input').remove();
			jQuery('.btn').remove();
			tmp.me.disableAll();
			tmp.commentsDivWrapper = $(tmp.me._htmlIds.itemDiv).down('.comments-div');
			if(tmp.commentsDivWrapper && tmp.me._creditNote.id) {
				tmp.me._signRandID(tmp.commentsDivWrapper);
				tmp.commentsDivWrapper.store('commentsDivJs', tmp.commentsDivJs = new CommentsDivJs(tmp.me, 'CreditNote', tmp.me._creditNote.id, 10, tmp.commentsDivWrapper.id));
				tmp.commentsDivJs.render();
			}
		} else if(tmp.me._order) {
			tmp.me._selectCustomer(tmp.me._order.customer);
			tmp.me._order.items.each(function(item){
				tmp.me._selectProduct(item).writeAttribute('orderItemId', item.id);
			});
		} else if(tmp.me._customer)
			tmp.me._selectCustomer(tmp.me._customer);
		else $(tmp.me._htmlIds.itemDiv).update(tmp.me._getCustomerListPanel());

		if($$('.init-focus').size() > 0)
			$$('.init-focus').first().focus();
		jQuery('.select2').select2({});

		tmp.me.paymentListPanelJs
			.setAfterAddFunc(function() { tmp.me.disableAll(true); })
			.setAfterDeleteFunc(function() { tmp.me.disableAll(true); })
			.load();
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
				$(tmp.row.down('.margin')).update( tmp.me.getCurrency( tmp.totalPrice * 1 - tmp.unitCost * 1.1 * tmp.qty ) + (parseInt(tmp.unitCost) === 0 ? '<div><small class="label label-danger">No Cost Yet</small</div>' : '') );
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
			if(tmp.rowData.margin)
				tmp.totalMargin = tmp.totalMargin * 1 + tmp.me.getValueFromCurrency(tmp.rowData.margin) * 1;
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
		tmp.totalPaid = (jQuery('[order-price-summary="totalPaidAmount"]').length > 0 ? jQuery('[order-price-summary="totalPaidAmount"]').val() : (tmp.me._creditNote && tmp.me._creditNote.totalPaid ? tmp.me._creditNote.totalPaid : 0));
		tmp.totalDue = (tmp.subTotal * 1 - tmp.totalPaid * 1);
		if(tmp.totalDue < 0) {
			tmp.me.showModalBox(
				'<h4 class="text-danger">Attention!</h4>',
				'<div><strong>We are paying more than the due amount!</strong></div><div><span class="btn btn-primary" onclick="pageJs.hideModalBox();">OK</span>',
				true
			);
		}
		jQuery('[order-price-summary="total-payment-due"]').val(tmp.me.getCurrency(tmp.totalDue)).html(tmp.me.getCurrency(tmp.totalDue));

		//display margin:
		jQuery('[order-price-summary="total-margin"]').val(tmp.me.getCurrency(tmp.totalMargin)).html(tmp.me.getCurrency(tmp.totalMargin));

		return tmp.me;
	}
	,setPaymentMethods: function(paymentMethods) {
		this._paymentMethods = paymentMethods;
		return this;
	}
});