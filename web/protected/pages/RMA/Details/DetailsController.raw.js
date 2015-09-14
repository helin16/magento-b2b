/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new BPCPageJs(), {
	_customer: null
	,_item: null
	,_searchTxtBox: null
	,_statusOptions: null
	,_commentsDiv: {'pagination': {'pageSize': 5, 'pageNo': 1}, 'resultDivId': 'comments_result_div', 'types': {'purchasing': '', 'warehouse': ''}} //the pagination for the comments
	/**
	 * Getting the comments row
	 */
	,_getCommentsRow: function(comments) {
		var tmp = {};
		tmp.me = this;
		return new Element('tr', {'class': 'comments_row'})
			.store('data', comments)
			.insert({'bottom': new Element('td', {'class': 'created', 'width': '15%'}).update(new Element('small').update(!comments.id ? comments.created : tmp.me.loadUTCTime(comments.created).toLocaleString() ) ) })
			.insert({'bottom': new Element('td', {'class': 'creator', 'width': '15%'}).update(new Element('small').update(comments.createdBy.person.fullname) ) })
			.insert({'bottom': new Element('td', {'class': 'type', 'width': '10%'}).update(new Element('small').update(comments.type) ) })
			.insert({'bottom': new Element('td', {'class': 'comments', 'width': 'auto'}).update(comments.comments) })
			;
	}
	/**
	 * Ajax: getting the comments into the comments div
	 */
	,_getComments: function (reset, btn) {
		var tmp = {};
		tmp.me = this;
		tmp.reset = (reset || false);
		if(tmp.reset === true) {
			$(tmp.me._commentsDiv.resultDivId).update('');
		}
		tmp.ajax = new Ajax.Request('/ajax/getComments', {
			method:'get'
			,parameters: {'entity': 'RMA', 'entityId': tmp.me._RMA.id, 'orderBy': {'created':'desc'}, 'pageNo': tmp.me._commentsDiv.pagination.pageNo, 'pageSize': tmp.me._commentsDiv.pagination.pageSize}
			,onLoading: function() {
				if(btn) {
					jQuery('#' + btn.id).button('loading');
				}
			}
			,onSuccess: function(transport) {
				try {
					if(tmp.reset === true) {
						$(tmp.me._commentsDiv.resultDivId).update(tmp.me._getCommentsRow({'type': 'Type', 'createdBy': {'person': {'fullname': 'WHO'}}, 'created': 'WHEN', 'comments': 'COMMENTS'}).addClassName('header').wrap( new Element('thead') ) );
					}
					tmp.result = tmp.me.getResp(transport.responseText, false, true);
					if(!tmp.result || !tmp.result.items)
						return;
					//remove the pagination btn
					if($$('.new-page-btn-div').size() > 0) {
						$$('.new-page-btn-div').each(function(item){
							item.remove();
						})
					}
					//add each item
					tmp.tbody = $(tmp.me._commentsDiv.resultDivId).down('tbody');
					if(!tmp.tbody) {
						$(tmp.me._commentsDiv.resultDivId).insert({'bottom': tmp.tbody = new Element('tbody') })
					}
					//add new data
					tmp.result.items.each(function(item) {
						tmp.tbody.insert({'bottom': tmp.me._getCommentsRow(item) });
					})
					//who new pagination btn
					if(tmp.result.pageStats.pageNumber < tmp.result.pageStats.totalPages) {
						tmp.tbody.insert({'bottom': new Element('tr', {'class': 'new-page-btn-div'})
							.insert({'bottom': new Element('td', {'colspan': 4})
								.insert({'bottom': new Element('span', {'id': 'comments_get_more_btn', 'class': 'btn btn-primary', 'data-loading-text': 'Getting More ...'})
									.update('Get More Comments')
									.observe('click', function(){
										tmp.me._commentsDiv.pagination.pageNo = tmp.me._commentsDiv.pagination.pageNo * 1 + 1;
										tmp.me._getComments(false, this);
									})
								})
							})
						})
					}
				} catch (e) {
					$(tmp.me._commentsDiv.resultDivId).insert({'bottom': tmp.me.getAlertBox('ERROR: ', e).addClassName('alert-danger') });
				}
			}
			,onComplete: function() {
				if(btn) {
					jQuery('#' + btn.id).button('reset');
				}
			}
		});
		return this;
	}
	/**
	 * Ajax: adding a comments to this order
	 */
	,_addComments: function(btn) {
		var tmp = {};
		tmp.me = this;
		tmp.commentsBox = $(btn).up('.new_comments_wrapper').down('[new_comments=comments]');
		tmp.comments = $F(tmp.commentsBox);
		if(tmp.comments.blank())
			return this;
		tmp.me.postAjax(tmp.me.getCallbackId('addComments'), {'comments': tmp.comments, 'RMA': tmp.me._RMA}, {
			'onLoading': function(sender, param) {
				jQuery('#' + btn.id).button('loading');
			}
			,'onSuccess': function (sender, param) {
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result) {
						return;
					}
					tmp.tbody = $(tmp.me._commentsDiv.resultDivId).down('tbody');
					if(!tmp.tbody)
						$(tmp.me._commentsDiv.resultDivId).insert({'bottom': tmp.tbody = new Element('tbody') });
					tmp.tbody.insert({'top': tmp.me._getCommentsRow(tmp.result)})
					tmp.commentsBox.setValue('');
				} catch (e) {
					alert(e);
				}
			}
			,'onComplete': function () {
				jQuery('#' + btn.id).button('reset');
			}
		})
		return this;
	}
	/**
	 * Getting a empty comments div
	 */
	,_getEmptyCommentsDiv: function() {
		var tmp = {};
		tmp.me = this;
		return new Element('div', {'class': 'panel panel-default'})
			.insert({'bottom': new Element('div', {'class': 'panel-heading'} ).update('Comments') })
			.insert({'bottom': new Element('div', {'class': 'table-responsive'})
				.insert({'bottom': new Element('table', {'id': tmp.me._commentsDiv.resultDivId, 'class': 'table table-hover table-condensed'}) })
			})
			.insert({'bottom': new Element('div', {'class': 'panel-body new_comments_wrapper'})
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('div', {'class': 'col-xs-2'}).update('<strong>New Comments:</strong>') })
					.insert({'bottom': new Element('div', {'class': 'col-xs-10'})
						.insert({'bottom': new Element('div', {'class': 'input-group'})
							.insert({'bottom': new Element('input', {'class': 'form-control', 'type': 'text', 'new_comments': 'comments', 'placeholder': 'add more comments to this order'})
								.observe('keydown', function(event) {
									tmp.me.keydown(event, function() {
										$(event.currentTarget).up('.new_comments_wrapper').down('[new_comments=btn]').click();
									});
								})
							})
							.insert({'bottom': new Element('span', {'class': 'input-group-btn'})
								.insert({'bottom': new Element('span', {'id': 'add_new_comments_btn', 'new_comments': 'btn', 'class': 'btn btn-primary', 'data-loading-text': 'saving...'})
									.update('add')
									.observe('click', function() {
										tmp.me._addComments(this);
									})
								})
							})
						})
					})
				})
			});
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
					tmp.me.hideModalBox();
					tmp.me.disableAll();
					window.location = '/rma/' + tmp.result.item.id + '.html';
					if(tmp.result.printURL) {
						tmp.printWindow = window.open(tmp.result.printURL, 'Printing Order', 'width=1300, location=no, scrollbars=yes, menubar=no, status=no, titlebar=no, fullscreen=no, toolbar=no');
						if(!tmp.printWindow)
							throw '<h4>Your browser has block the popup window, please enable it for further operations.</h4><a href="' + tmp.result.printURL + '" target="_BLANK"> click here for now</a>';
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
	,disableAll: function() {
		jQuery('input').attr("disabled", true);
		jQuery('textarea').attr("disabled", true);
		jQuery('.btn').attr("disabled", true);
		jQuery('.form-control').attr("disabled", true);
	}
	,_preConfirmSubmit: function(printit) {
		var tmp = {};
		tmp.me = this;
		tmp.data = null;

		tmp.printIt = (printit === true ? true : false);
		tmp.data = tmp.me._collectFormData($(tmp.me.getHTMLID('itemDiv')),'save-order');
		if(tmp.data === null)
			return null;
		if(tmp.data.status.blank()) {
			tmp.me.hideModalBox();
			tmp.me.showModalBox('Warning', 'You MUST select the <strong>Status</strong> for this RMA');
			return null;
		}
		tmp.data.printIt = tmp.printIt;
		tmp.data.customer = {};
		tmp.data.customer.id = tmp.me._customer.id;
		tmp.data.RMA = tmp.me._RMA;
		tmp.data.order = tmp.me._order;

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
		$$('.order-item-row').each(function(item){
			tmp.itemData = item.retrieve('data');
			tmp.data.items.push({'orderItemId': item.readAttribute('orderItemId'), 'RMAItemId': item.readAttribute('RMAItemId'), 'valid': item.visible(), 'product': {'id': tmp.itemData.product.id}, 'itemDescription': tmp.itemData.itemDescription,'unitPrice': tmp.itemData.unitPrice, 'qtyOrdered': tmp.itemData.qtyOrdered, 'totalPrice': tmp.itemData.totalPrice, 'serials': item.retrieve('serials') });
		});
		tmp.noValidItem = true;
		tmp.data.items.each(function(item){
			if(tmp.noValidItem === true && item.valid === true)
				tmp.noValidItem = false;
		})
		if(tmp.noValidItem === true) {
			tmp.me.hideModalBox();
			tmp.me.showModalBox('<strong class="text-danger">Error</strong>', '<strong>At least one RMA Item</strong> is needed!', true);
			return null;
		}
		tmp.data.items.each(function(item){
			item.totalPrice = tmp.me.getValueFromCurrency(item.totalPrice);
			item.unitPrice = tmp.me.getValueFromCurrency(item.unitPrice);
		});

		tmp.newDiv = new Element('div', {'class': 'confirm-div'})
			.insert({'bottom': new Element('div')
				.insert({'bottom': tmp.me._getFormGroup('Do you want to send an email to this address:',
						new Element('input', {'value': tmp.me._customer.email, 'confirm-po': 'po_email', 'required': true, 'placeholder': 'The email to send to. WIll NOT update the customer\'s email with this.'})
					)
				})
			})
			.insert({'bottom': new Element('div')
				.insert({'bottom': new Element('em')
					.insert({'bottom': new Element('small').update('The above email will be used to send the email to. WIll NOT update the customer\'s email with this.') })
				})
			})
			.insert({'bottom': new Element('div')
				.insert({'bottom': new Element('span', {'class': 'btn btn-primary pull-right'}).update('Yes, send the RMA to this email address')
					.observe('click', function(){
						tmp.confirmEmailBox = $(this).up('.confirm-div').down('[confirm-po="po_email"]');
						if($F(tmp.confirmEmailBox).blank()) {
							tmp.me._markFormGroupError(tmp.confirmEmailBox, 'Email Address Required Here');
							return;
						}
						if(!/^.+@.+(\..)*$/.test($F(tmp.confirmEmailBox).strip())) {
							tmp.me._markFormGroupError(tmp.confirmEmailBox, 'Please provide an valid email address');
							return;
						}
						tmp.data.confirmEmail = $F(tmp.confirmEmailBox).strip();
						tmp.me._submitOrder($(this),tmp.data);
					})
				})
				.insert({'bottom': new Element('span', {'class': 'btn btn-info'}).update('No, save RMA but DO NOT send email')
					.observe('click', function(){
						tmp.me._submitOrder($(this),tmp.data);
					})
				})
			});
		tmp.me.showModalBox("You're about to save creadit note for : " + tmp.me._customer.name, tmp.newDiv);

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
					.insert({'bottom': new Element('span').update(' Save & Email ') })
					.observe('click', function() {
						tmp.me._preConfirmSubmit();
					})
				})
				.insert({'bottom': new Element('span', {'class': 'btn btn-primary dropdown-toggle', 'data-toggle': 'dropdown'}).setStyle('display: none;')
					.insert({'bottom': new Element('span', {'class': 'caret'}) })
				})
				.insert({'bottom': new Element('ul', {'class': 'dropdown-menu save-btn-dropdown-menu'}).setStyle('display: none;')
					.insert({'bottom': new Element('li')
						.insert({'bottom': new Element('a', {'href': 'javascript: void(0);'}).update('Save Only')
							.observe('click', function() {
								tmp.me._preConfirmSubmit();
							})
						})
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

//		tmp.data.scanTable = tmp.me._getScanTable(tmp.data);
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
					.insert({'bottom': new Element('strong').update(product.name)
						.insert({'bottom': new Element('small', {'class': 'btn btn-xs btn-info'}).setStyle('margin-left: 10px;')
							.insert({'bottom': new Element('small', {'class': 'glyphicon glyphicon-new-window'}) })
							.observe('click', function(event){
								Event.stop(event);
								$productId = $(this).up('.search-product-result-row').retrieve('data').id;
								if($productId)
									tmp.me._openProductDetailPage($productId);
							})
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
					.addClassName('col-xs-3')
					.insert({'bottom': new Element('small', {'class': 'btn btn-xs btn-info'}).setStyle('margin-left: 10px;')
						.insert({'bottom': new Element('small', {'class': 'glyphicon glyphicon-new-window'}) })
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
					.insert({'after': new Element('div', {'class': 'col-xs-7'}) // productName
						.update(new Element('textarea', {'new-order-item': 'itemDescription'}).setStyle('width: 100%').update(product.name))
					});
				jQuery('#' + tmp.me.modalId).modal('hide');
				tmp.retailPrice = product.prices.size() === 0 ? 0 : product.prices[0].price;
				tmp.inputRow.down('[new-order-item=qtyOrdered]').select();
				tmp.me._calculateNewProductPrice(tmp.inputRow);
			})
			;
		return tmp.newRow;
	}
	,_selectProduct: function(RMAItem) {
		var tmp = {};
		tmp.me = this;
		//get all data
		tmp.data = {
			'product': RMAItem.product,
			'itemDescription': RMAItem.itemDescription,
			'unitPrice': tmp.me.getCurrency(RMAItem.unitPrice),
			'qtyOrdered': RMAItem.qty ? RMAItem.qty : RMAItem.qtyOrdered,
			'discount' : 100,
			'margin': 0,
			'totalPrice': tmp.me.getCurrency(RMAItem.unitPrice * (RMAItem.qty ? RMAItem.qty : RMAItem.qtyOrdered))
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
		tmp.status = new Element('select', {'class': 'select2 form-control input-sm', 'save-order': 'status', 'data-placeholder': 'Select an option'}).insert({'bottom': new Element('option', {'value': ''}).update('') });
		tmp.me._statusOptions.each(function(option){
			tmp.status.insert({'bottom': tmp.option = new Element('option', {'value': option}).update(option) });
			if(tmp.me._RMA && tmp.me._RMA.status == option)
				tmp.option.writeAttribute('selected', true);
		});
		tmp.newDiv = new Element('div', {'class': 'panel-footer'})
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('div', {'class': 'col-sm-8'})
						.insert({'bottom': tmp.me._getFormGroup( 'Description:', new Element('textarea', {'save-order': 'description', 'rows': tmp.me._RMA ? 1 : 1}).update(tmp.me._RMA ? tmp.me._RMA.description : '') ) })
						.insert({'bottom': tmp.me._RMA ? '' : tmp.me._getFormGroup( 'Comments:', new Element('textarea', {'save-order': 'comments', 'rows': '1'}) ) })
					})
				.insert({'bottom': new Element('div', {'class': 'col-sm-4'})
					.insert({'bottom': new Element('div', {'class': 'row'}).setStyle('display:none;')
						.insert({'bottom': new Element('div', {'class': 'col-xs-6 text-right'}).update( new Element('strong').update('Total Excl. GST: ') ) })
						.insert({'bottom': new Element('div', {'class': 'col-xs-6', 'order-price-summary': 'totalPriceExcludeGST'}).update( tmp.me.getCurrency(0) ) })
					})
					.insert({'bottom': new Element('div', {'class': 'row'}).setStyle('display:none;')
						.insert({'bottom': new Element('div', {'class': 'col-xs-6 text-right'}).update( new Element('strong').update('Total GST: ') ) })
						.insert({'bottom': new Element('div', {'order-price-summary': 'totalPriceGST', 'class': 'col-xs-6'}).update( tmp.me.getCurrency(0) ) })
					})
					.insert({'bottom': new Element('div', {'class': 'row', 'style': 'border-bottom: 1px solid brown'}).setStyle('display: none;')
						.insert({'bottom': new Element('div', {'class': 'col-xs-6 text-right'}).update( new Element('strong').update('Total Discount:') ) })
						.insert({'bottom': new Element('div', {'order-price-summary': 'totalDiscount', 'class': 'col-xs-6'}).update( tmp.me.getCurrency(0) ) })
					})
					.insert({'bottom': new Element('div', {'class': 'row'}).setStyle('display:none;')
						.insert({'bottom': new Element('div', {'class': 'col-xs-6 text-right'}).update( new Element('strong').update('Sub Total Incl. GST: ') ) })
						.insert({'bottom': new Element('div', {'order-price-summary': 'totalPriceIncludeGST', 'class': 'col-xs-6'}).update( tmp.me.getCurrency(0) ) })
					})
					.insert({'bottom': new Element('div', {'class': 'row', 'style': 'border-bottom: 1px solid brown'})
						.insert({'bottom': new Element('div', {'class': 'col-xs-6 text-right'}).update( new Element('strong').update('Status: ') ) })
						.insert({'bottom': new Element('div', {'class': 'col-xs-6'}).update( new Element('strong').update(tmp.status)) })
					})
					.insert({'bottom': new Element('div', {'class': 'row'})
						.insert({'bottom': new Element('div', {'class': 'col-xs-6 text-right'}).update( new Element('strong').update('Total Qty:') ) })
						.insert({'bottom': new Element('strong', {'order-price-summary': 'totalQty', 'class': 'col-xs-6'}).update(0) })
					})
					.insert({'bottom': new Element('div', {'class': 'row'}).setStyle('display:none;')
						.insert({'bottom': new Element('div', {'class': 'col-xs-6 text-right'}).update( new Element('strong').update('Total Incl. GST:') ) })
						.insert({'bottom': new Element('strong', {'order-price-summary': 'subTotal', 'class': 'col-xs-6'}).update( tmp.me.getCurrency(0) ) })
					})
					.insert({'bottom': new Element('div', {'class': 'row'}).setStyle('display: none;')
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
			.insert({'bottom': new Element('span', {'class': 'btn btn-danger btn-xs'})
				.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-trash'}) })
				.observe('click', function() {
					if(!confirm('You remove this entry.\n\nContinue?'))
						return;
					tmp.row = $(this).up('.item_row');
					if(tmp.row) {
						if(jQuery.isNumeric(tmp.row.readAttribute('RMAItemId')))
							tmp.row.hide();
						else tmp.row.remove();
					}
					tmp.me._recalculateSummary();
				})
			});
		tmp.row = new Element('div', {'class': ' list-group-item ' + (tmp.isTitle === true ? '' : 'item_row order-item-row')})
			.store('data',orderItem)
			.insert({'bottom': new Element('div', {'class': 'row'})
				.store('data', orderItem)
				.insert({'bottom': new Element(tmp.tag, {'class': 'productName col-xs-10'})
					.insert({'bottom': orderItem.itemDescription ? orderItem.itemDescription : orderItem.product.name })
				})
				.insert({'bottom': new Element(tmp.tag, {'class': 'uprice col-xs-1'}).setStyle('display:none;')
					.insert({'bottom': (orderItem.unitPrice) })
				})
				.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-1'})
					.insert({'bottom': new Element('div')
						.insert({'bottom': new Element('div', {'class': 'qty col-xs-12'}).update(orderItem.qtyOrdered) })
						.insert({'bottom': new Element('div', {'class': 'discount col-xs-6'}).update(orderItem.discount).setStyle('display: none;') })
					})
				})
				.insert({'bottom': new Element(tmp.tag, {'class': 'tprice col-xs-1'}).setStyle('display: none;')
					.insert({'bottom': (orderItem.totalPrice) })
				})
				.insert({'bottom': new Element(tmp.tag, {'class': 'margin col-xs-1 text-right'}).update(orderItem.margin).setStyle('display: none;')})
				.insert({'bottom': new Element(tmp.tag, {'class': 'btns col-xs-1 text-right'}).update(tmp.btns) })
			});
		if(orderItem.product.sku) {
			tmp.row.down('.productName')
				.removeClassName('col-xs-10')
				.addClassName('col-xs-7')
				.insert({'before': new Element(tmp.tag, {'class': 'productSku col-xs-3'}).update(orderItem.product.sku)
					.insert({'bottom': new Element('small', {'class': orderItem.product.id ? 'btn btn-xs btn-info' : 'hidden'}).setStyle('margin-left: 10px;')
						.insert({'bottom': new Element('small', {'class': 'glyphicon glyphicon-new-window'}) })
						.observe('click', function(event){
							Event.stop(event);
							$productId = orderItem.product.id;
							if($productId)
								tmp.me._openProductDetailPage($productId);
						})
					})
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
				'qtyOrdered': 'Quantity',
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
						.insert({'bottom': new Element('strong').update((tmp.me._RMA && tmp.me._RMA.id ? 'EDITING' : 'CREATING') + '  RMA FOR:  ') })
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
				tmp.customer = $$('iframe.fancybox-iframe').first().contentWindow.pageJs._item;
				if(tmp.customer && tmp.customer.id) {
					tmp.me._selectCustomer(tmp.customer);
				}
			}
 		});
		return tmp.me;
	}
	/**
	 * Getting the div of the order view
	 */
	,getViewOfRMA: function() {
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
			.insert({'bottom': tmp.me._RMA ? new Element('div', {'class': 'row'}).setStyle('padding-top: 20px')
				.insert({'bottom': new Element('div', {'class': 'col-sm-12'}).update(tmp.me._getEmptyCommentsDiv()) })
				: ''
			})
			;
		return tmp.newDiv;
	}
	,_selectCustomer: function(customer) {
		var tmp = {};
		tmp.me = this;
		tmp.me._customer = customer;
		if($$('.CustomerInfoPanel').size() > 0)
			jQuery('.CustomerInfoPanel').replaceWith(tmp.me.getCustomerInfoPanel());
		$(tmp.me.getHTMLID('itemDiv')).update(tmp.me.getViewOfRMA());
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
		tmp.searchPanel = $(txtbox).up('#' + tmp.me.getHTMLID('searchPanel'));
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
		tmp.newDiv = new Element('div', {'id': tmp.me.getHTMLID('searchPanel'), 'class': 'panel panel-info search-panel'})
			.insert({'bottom': new Element('div', {'class': 'panel-heading form-inline'})
				.insert({'bottom': new Element('strong').update('Creating a new order for: ') })
				.insert({'bottom': new Element('span', {'class': 'input-group col-sm-6'})
					.insert({'bottom': new Element('input', {'class': 'form-control search-txt init-focus', 'placeholder': 'Customer name'})
						.observe('keydown', function(event){
							tmp.txtBox = this;
							tmp.me.keydown(event, function() {
								if(tmp.txtBox.up('#'+pageJs._htmlIds.searchPanel).down('.item_row')!=undefined && tmp.txtBox.up('#'+pageJs._htmlIds.searchPanel).down('tbody').getElementsBySelector('.item_row').length===1) {
									tmp.txtBox.up('#'+pageJs._htmlIds.searchPanel).down('tbody .item_row .btn').click();
								}
								else $(tmp.me.getHTMLID('searchPanel')).down('.search-btn').click();
							}, function(){}, Event.KEY_TAB);
							tmp.me.keydown(event, function() {
								if(tmp.txtBox.up('#'+pageJs._htmlIds.searchPanel).down('.item_row')!=undefined && tmp.txtBox.up('#'+pageJs._htmlIds.searchPanel).down('tbody').getElementsBySelector('.item_row').length===1) {
									tmp.txtBox.up('#'+pageJs._htmlIds.searchPanel).down('tbody .item_row .btn').click();
								}
								else $(tmp.me.getHTMLID('searchPanel')).down('.search-btn').click();
							}, function(){});
							return false;
						})
					})
					.insert({'bottom': new Element('span', {'class': 'input-group-btn search-btn'})
						.insert({'bottom': new Element('span', {'class': ' btn btn-primary'})
							.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-search'}) })
						})
						.observe('click', function(){
							tmp.btn = this;
							tmp.txtBox = $(tmp.me.getHTMLID('searchPanel')).down('.search-txt');
							if(!$F(tmp.txtBox).blank())
								tmp.me._searchCustomer(tmp.txtBox);
							else {
								if($(tmp.me.getHTMLID('searchPanel')).down('.table tbody'))
									$(tmp.me.getHTMLID('searchPanel')).down('.table tbody').innerHTML = null;
							}
						})
					})
				})
				.insert({'bottom': new Element('span', {'class': 'btn btn-success pull-right btn-sm btn-danger'})
					.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-remove'}) })
					.observe('click', function(){
						$(tmp.me.getHTMLID('searchPanel')).down('.search-txt').clear().focus();
						$(tmp.me.getHTMLID('searchPanel')).down('.table tbody').innerHTML = null;
					})
				})
			})
			;
		return tmp.newDiv;
	}
	,init: function() {
		var tmp = {};
		tmp.me = this;
		if(tmp.me._RMA) {
			if(tmp.me._RMA.order && tmp.me._RMA.order.id && jQuery.isNumeric(tmp.me._RMA.order.id))
				tmp.me._order = tmp.me._RMA.order;
			tmp.me._selectCustomer(tmp.me._RMA.customer);
		} else if(tmp.me._customer)
			tmp.me._selectCustomer(tmp.me._customer);
		else if(tmp.me._order)
			tmp.me._selectCustomer(tmp.me._order.customer);
		else $(tmp.me.getHTMLID('itemDiv')).update(tmp.me._getCustomerListPanel());

		if($$('.init-focus').size() > 0)
			$$('.init-focus').first().focus();
		jQuery('.select2').select2({});

		if(tmp.me._RMA) {
			tmp.me._RMA.items.each(function(item){
				tmp.me._selectProduct(item).writeAttribute('RMAItemId', item.id);
			});
		}
		else if(tmp.me._order) {
			tmp.me._order.items.each(function(item){
				tmp.me._selectProduct(item).writeAttribute('orderItemId', item.id);
			});
		}
		if(tmp.me._RMA)
			tmp.me._getComments(true);
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
	 * Open product Details Page in new Window
	 */
	,_openProductDetailPage: function(id) {
		var tmp = {};
		tmp.me = this;
		tmp.newWindow = window.open('/product/' + id + '.html', 'Product Details', 'width=1300, location=no, scrollbars=yes, menubar=no, status=no, titlebar=no, fullscreen=no, toolbar=no');
		tmp.newWindow.focus();
		return tmp.me;
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
		tmp.totalQty = 0;
		$$('.item_row.order-item-row').each(function(row) {
			tmp.rowData = row.retrieve('data');
			tmp.totalPriceIncGSTWithDiscount = tmp.totalPriceIncGSTWithDiscount * 1 + (tmp.me.getValueFromCurrency(tmp.rowData.totalPrice) * 1);
			tmp.totalPriceIncGSTNoDicount = tmp.totalPriceIncGSTNoDicount * 1 + (tmp.me.getValueFromCurrency(tmp.rowData.unitPrice) * tmp.rowData.qtyOrdered);
			tmp.totalQty = tmp.totalQty + parseInt(tmp.rowData.qtyOrdered);
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

		//calculate the total Qty
		jQuery('[order-price-summary="totalQty"]').val(tmp.totalQty).html(tmp.totalQty);

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
});