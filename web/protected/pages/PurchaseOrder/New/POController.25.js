/**
 * The page Js file
 */
var POCreateJs = new Class.create();
POCreateJs.prototype = Object.extend(new BPCPageJs(), {
	_htmlIds: {'itemDiv': '', 'searchPanel': 'search_panel'}
	,_supplier: null
	,_item: null
	,_searchTxtBox: null
	,_isCredit: false
	,_purchaseOrderItems: []
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
	/**
	 * submitting the order details
	 */
	,_submitOrder: function(data) {
		var tmp = {};
		tmp.me = this;
		tmp.loadingDiv = new Element('div', {'class': 'text-center', 'style': 'margin-top: 100px;'})
			.insert({'bottom': new Element('h4').update('Saving PO, please do NOT close the window') })
			.insert({'bottom': new Element('span', {'class': 'fa fa-refresh fa-spin fa-5x'}) });
		tmp.me.postAjax(tmp.me.getCallbackId('saveOrder'), data, {
			'onLoading': function(sender, param) {
				tmp.me.hideModalBox();
				$(tmp.me._htmlIds.itemDiv).insert({'after': tmp.loadingDiv}).hide();
			}
			,'onSuccess': function(sender, param) {
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result || !tmp.result.item)
						return;
					tmp.me._item = tmp.result.item;
					tmp.me.refreshParentWindow();
					tmp.newDiv = new Element('div')
						.insert({'bottom': new Element('h4').update('Yah! Saved Successfully.') })
						.insert({'bottom': new Element('div')
							.insert({'bottom': new Element('span', {'class': 'btn btn-primary pull-right'}).update('Add Another PO')
								.observe('click', function() {
									window.location = document.URL;
								})
							})
							.insert({'bottom': new Element('a', {'class': 'btn btn-info goto-details', 'href': '/purchase/' + tmp.result.item.id + '.html'}).update('View the details')
							})
						})
					tmp.me.showModalBox('<strong class="text-success">Success</strong>', tmp.newDiv , false);
					jQuery('#' + tmp.me.modalId).on('hide.bs.modal', function (event) {
						if(tmp.newDiv.down('.goto-details')) {
							tmp.newDiv.down('.goto-details').click();
						}
					});
				} catch(e) {
					tmp.me.showModalBox('<strong class="text-danger">Error:</strong>', e, false);
					$(tmp.me._htmlIds.itemDiv).show();
					tmp.loadingDiv.remove();
				}
			}
			,'onComplete': function(sender, param) {
				$(tmp.me._htmlIds.itemDiv).show();
				tmp.loadingDiv.remove();
			}
		});
		return tmp.me;
	}
	,refreshParentWindow: function() {
		var tmp = {};
		tmp.me = this;
		if(!window.opener)
			return;
		tmp.parentWindow = window.opener;
		tmp.row = $(tmp.parentWindow.document.body).down('table#item-list tbody');
		if(tmp.row) {
			tmp.row = $(tmp.parentWindow.document.body).down('table#item-list tbody').insert({'top': tmp.parentWindow.pageJs._getResultRow(tmp.me._item).addClassName('success')});
		}
	}
	,_confirmSubmit: function(submit) {
		var tmp = {};
		tmp.me = this;
		tmp.submitData = {};
		tmp.submitData.submitToSupplier = (submit === true ? true : false);
		//getting all meta data
		tmp.data = tmp.me._collectFormData($(tmp.me._htmlIds.itemDiv),'save-order');
		if(tmp.data === null)
			return tmp.me;
		//getting all the items
		tmp.submitData.items = [];
		$$('.order-item-row').each(function(item){
			tmp.item = item.retrieve('data');
			tmp.item.totalPrice = tmp.item.totalPrice ? tmp.me.getValueFromCurrency(tmp.item.totalPrice) : '0';
			tmp.item.unitPrice = tmp.item.unitPrice ? tmp.me.getValueFromCurrency(tmp.item.unitPrice) : '0';
			tmp.submitData.items.push({'productId': tmp.item.product.id, 'qtyOrdered': tmp.item.qtyOrdered, 'totalPrice': tmp.item.totalPrice, 'unitPrice': tmp.item.unitPrice});
		});
		if(tmp.submitData.items.size() <= 0) {
			tmp.me.showModalBox('<strong class="text-danger">Error</strong>', 'At least one order item is needed!', true);
			return tmp.me;
		}
		//getting all the supplier details
		tmp.submitData.supplier = {};
		tmp.submitData.supplier.id = tmp.me._supplier.id;
		tmp.submitData.supplier.contactName = tmp.data.contactName ? tmp.data.contactName : tmp.me._supplier.contactName;
		tmp.submitData.supplier.contactNo = tmp.data.contactNo ? tmp.data.contactNo : tmp.me._supplier.contactNo;
		tmp.submitData.supplier.email = tmp.data.contactEmail ? tmp.data.contactEmail : tmp.me._supplier.email;
		tmp.submitData.supplierRefNum = tmp.data.supplierRefNum;
		tmp.submitData.eta = tmp.data.ETA.strip();
		tmp.submitData.comments = tmp.data.comments.strip();

		//getting all the costs
		tmp.submitData.shippingCost = tmp.data.shippingCost ? tmp.me.getValueFromCurrency( tmp.data.shippingCost ) : 0;
		tmp.submitData.handlingCost = tmp.data.handlingCost ? tmp.me.getValueFromCurrency( tmp.data.handlingCost ) : 0;
		tmp.submitData.totalPaymentDue = tmp.data.totalPaymentDue ? tmp.me.getValueFromCurrency( tmp.data.totalPaymentDue ) : 0;
		
		//for creadit po
		console.debug(tmp.me._isCredit === true);
		if(tmp.me._isCredit === true) {
			tmp.submitData.type = 'CREDIT';
			if(tmp.me._po && jQuery.isNumeric(tmp.me._po.id))
				tmp.submitData.po = tmp.me._po;
		}
		
		if(tmp.submitData.submitToSupplier === true) {
			tmp.newDiv = new Element('div', {'class': 'confirm-div'})
				.insert({'bottom': new Element('div')
					.insert({'bottom': tmp.me._getFormGroup('Do you want to send an email to this address:',
							new Element('input', {'value': tmp.submitData.supplier.email, 'confirm-po': 'po_email', 'required': true, 'placeholder': 'The email to send to. WIll NOT update the supplier\'s email with this.'})
						)
					})
				})
				.insert({'bottom': new Element('div')
					.insert({'bottom': new Element('em')
						.insert({'bottom': new Element('small').update('The above email will be used to send the email to. WIll NOT update the supplier\'s email with this.') })
					})
				})
				.insert({'bottom': new Element('div')
					.insert({'bottom': new Element('span', {'class': 'btn btn-primary pull-right'}).update('Yes, send the PO to this email address')
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
							tmp.submitData.confirmEmail = $F(tmp.confirmEmailBox).strip();
							tmp.me._submitOrder(tmp.submitData);
						})
					})
					.insert({'bottom': new Element('span', {'class': 'btn btn-info'}).update('No, push PO status but DO NOT send email')
						.observe('click', function(){
							tmp.me._submitOrder(tmp.submitData);
						})
					})
				})
			;
			tmp.me.showModalBox('<strong class="text-info">Confirm</strong>', tmp.newDiv, false);
		} else {
			tmp.me._submitOrder(tmp.submitData);
		}

		return tmp.me;
	}
	/**
	 * Getting the save btn for this order
	 */
	,_saveBtns: function() {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = new Element('div')
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
			.insert({'bottom': new Element('span', {'class': 'btn-group pull-right visible-xs visible-sm visible-md visible-lg'})
				.insert({'bottom': new Element('span', {'class': 'btn btn-primary'})
					.insert({'bottom': new Element('span').update('save & submit') })
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
			});
		return tmp.newDiv;
	}
	/**
	 * getting the customer information div
	 */
	,_getSupplierInfoPanel: function() {
		var tmp = {};
		tmp.me = this;
		tmp.supplier = tmp.me._supplier;
		tmp.newDiv = new Element('div', {'class': 'panel'}).addClassName(tmp.me._isCredit === true ? 'panel-danger' : 'panel-info')
			.insert({'bottom': new Element('div', {'class': 'panel-heading'})
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('div', {'class': 'col-sm-4'})
						.insert({'bottom': new Element('strong', {'class': 'creatingFor'}).update('Creating ' + (tmp.me._ifCredit === true ? 'PO Credit' : 'PO') + ' for: ' + tmp.supplier.name + ' ') })
						.insert({'bottom': new Element('strong', {'class': 'creatingFor'}).update(((tmp.me._po && jQuery.isNumeric(tmp.me._po.id)) ? (' for Purchase Order <a target="_blank" href="/purchase/' + tmp.me._po.id + '">' + tmp.me._po.purchaseOrderNo + '</a>') : '')) })
					})
					.insert({'bottom': new Element('div', {'class': 'col-sm-8 text-right'})
						.insert({'bottom': new Element('div', {'class': 'col-sm-8 text-right'})
							.insert({'bottom': new Element('span')
								.insert({'bottom': new Element('strong', {'style': 'padding-left: 10px'}).update('ETA: ') })
								.insert({'bottom': new Element('input', {'style': 'max-height:19px', 'class': 'datepicker', 'save-order': 'ETA', 'type': 'date', 'value': ''}) })
							})
						})
						.insert({'bottom': new Element('div', {'class': 'col-sm-4'})
							.insert({'bottom': new Element('strong').update('Total Due: ') })
							.insert({'bottom': new Element('span', {'class': 'badge total-payment-due'}).update(tmp.me.getCurrency(0) ) })
							.insert({'bottom': new Element('input', {'type': 'hidden', 'class': 'total-payment-due', 'save-order': "totalPaymentDue", 'value': tmp.me.getCurrency(0)}) })
						})
					})
				})
			})
			.insert({'bottom': new Element('div', {'class': 'panel-body'})
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('div', {'class': 'col-sm-3'}).update(tmp.me._getFormGroup('Contact Name', new Element('input', {'save-order': 'contactName', 'placeholder': 'The name of the contact person', 'type': 'text', 'value': tmp.supplier.contactName ? tmp.supplier.contactName : ''}) ) ) })
					.insert({'bottom': new Element('div', {'class': 'col-sm-3'}).update(tmp.me._getFormGroup('Contact Number', new Element('input', {'save-order': 'contactNo', 'placeholder': 'The contact number of the sales person',  'value': tmp.supplier.contactNo ? tmp.supplier.contactNo : ''}) ) ) })
					.insert({'bottom': new Element('div', {'class': 'col-sm-3'}).update(tmp.me._getFormGroup('Contact Email', new Element('input', {'save-order': 'contactEmail', 'placeholder': 'The email of the supplier', 'type': 'email', 'value': tmp.supplier.email ? tmp.supplier.email : ''}) ) ) })
					.insert({'bottom': new Element('div', {'class': 'col-sm-3'}).update(tmp.me._getFormGroup('PO Ref Num', new Element('input', {'required': 'required', 'placeholder': 'The supplier invoice number / PO reference number',  'save-order': 'supplierRefNum', 'type': 'text', 'value': ''}) ) ) })
				 })
			});
		return tmp.newDiv;
	}
	,_loadDataPicker: function () {
		$$('.datepicker').each(function(item){
			new Prado.WebUI.TDatePicker({'ID': item, 'InputMode':"TextBox",'Format':"yyyy-MM-dd 00:00:00",'FirstDayOfWeek':1,'CalendarStyle':"default",'FromYear':2009,'UpToYear':2024,'PositionMode':"Bottom", "ClassName": 'datepicker-layer-fixer'});
		});
		return this;
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
			.insert({'bottom': new Element(tmp.tag, {'class': 'uprice col-xs-2'})
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
		tmp.newRow = new Element('a', {'class': 'list-group-item search-product-result-row', 'href': 'javascript: void(0);'})
			.store('data',product)
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
						.insert({'bottom': new Element('small', {'class': 'btn btn-xs btn-info'})
							.insert({'bottom': new Element('small', {'class': 'glyphicon glyphicon-new-window'} )})
							.observe('click', function(event){
								Event.stop(event);
								$productId = $(this).up('.search-product-result-row').retrieve('data').id;
								if($productId)
									tmp.me._openProductDetailPage($productId);
							})
						})
						.insert({'bottom': new Element('div')
							.insert({'bottom': new Element('small').update(product.shortDescription) })
						})
					})
					.insert({'bottom': new Element('div', {'class': 'row', 'style': (product.minProductPrice || product.lastSupplierPrice || product.minSupplierPrice) ? 'height: 2px; background-color: brown;' : 'display:none'}).update('&nbsp;') })
					.insert({'bottom': new Element('div', {'class': 'row small'})
						.insert({'bottom': new Element('span', {'class': 'col-xs-4 btn btn-link btn-xs', 'style': product.minProductPrice ? 'text-align: left': 'display:none'}).update('Minimum product price: ')
							.insert({'bottom': new Element('strong').update(tmp.me.getCurrency(product.minProductPrice)) })
						})
						.observe('click', function(event) {
							Event.stop(event);
							tmp.me._openPOPage(product.minProductPriceId);
						})

						.insert({'bottom': new Element('span', {'class': 'col-xs-4 btn btn-link btn-xs', 'style': product.lastSupplierPrice ? 'text-align: left': 'display:none'}).update('Last supplier price: ')
							.insert({'bottom': new Element('strong').update(tmp.me.getCurrency(product.lastSupplierPrice)) })
						})
						.observe('click', function(event) {
							Event.stop(event);
							tmp.me._openPOPage(product.lastSupplierPriceId);
						})

						.insert({'bottom': new Element('span', {'class': 'col-xs-4 btn btn-link btn-xs', 'style': product.minSupplierPrice ? 'text-align: left': 'display:none'}).update('Minimum supplier price: ')
							.insert({'bottom': new Element('strong').update(tmp.me.getCurrency(product.minSupplierPrice)) })
						})
						.observe('click', function(event) {
							Event.stop(event);
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
					.insert({'bottom': new Element('small', {'class': 'btn btn-xs btn-info'})
						.insert({'bottom': new Element('small', {'class': 'glyphicon glyphicon-new-window'} )})
						.observe('click', function(event){
							Event.stop(event);
							$productId = product.id;
							if($productId)
								tmp.me._openProductDetailPage($productId);
						})
					})
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
				tmp.inputRow.down('[new-order-item=qtyOrdered]').writeAttribute('value', 1);
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
		tmp.newWindow = window.open('/purchase/' + id + '.html', 'Product Details', 'width=1300, location=no, scrollbars=yes, menubar=no, status=no, titlebar=no, fullscreen=no, toolbar=no');
		tmp.newWindow.focus();
		return tmp.me;
	}
	/**
	 * Open new Order page in new Window
	 */
	,_openNewProductPage: function(btnId) {
		var tmp = {};
		tmp.me = this;
		tmp.newWindow = window.open('/product/new.html?btnidnewpo=' + btnId, 'New Product Page', 'width=1300, location=no, scrollbars=yes, menubar=no, status=no, titlebar=no, fullscreen=no, toolbar=no');
		tmp.newWindow.onload = function(){
			tmp.newWindow.document.title = 'New Product Page';
			tmp.newWindow.focus();
		}
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
		tmp.searchTxt = $F(tmp.searchTxtBox);
		tmp.me.postAjax(tmp.me.getCallbackId('searchProduct'), {'searchTxt': tmp.searchTxt, 'supplierID': tmp.me._supplier.id, 'pageNo': tmp.pageNo}, {
			'onLoading': function() {
				jQuery('#' + tmp.btn.id).button('loading');
			}
			,'onSuccess': function(sender, param) {
				if(tmp.showMore === false)
					tmp.resultList = new Element('div', {'class': 'search-product-list'});
				else
					tmp.resultList = $(btn).up('.search-product-list');
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result || !tmp.result.items || tmp.result.items.size() === 0)
						throw new Element('span')
							.insert({'bottom': new Element('span').update('Nothing Found for: ' + tmp.searchTxt)})
							.insert({'bottom': new Element('span', {'class': 'btn btn-success btn-xs pull-right'})
								.insert({'bottom': new Element('i', {'class': 'fa fa-plus', 'title': 'add new product'})})
								.observe('click', function(e){
									tmp.newProductBtn = $(this);
									tmp.me._signRandID(tmp.newProductBtn);
									tmp.me._openNewProductPage(tmp.newProductBtn.id);
								})
							});
					tmp.me._signRandID(tmp.searchTxtBox);
					tmp.me._searchTxtBox = tmp.searchTxtBox;
					tmp.result.items.each(function(product) {
						tmp.resultList.insert({'bottom': tmp.me._getSearchPrductResultRow(product, tmp.searchTxtBox) });
					});
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
					tmp.resultList.addClassName('list-group');
				} catch(e) {
					tmp.resultList.update(tmp.me.getAlertBox('Error: ', e).addClassName('alert-danger'));
				}
				if(tmp.showMore === false)
					tmp.me.showModalBox('Products that has: ' + tmp.searchTxt, tmp.resultList, false);
			}
			,'onComplete': function(sender, param) {
				jQuery('#' + tmp.btn.id).button('reset');
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
		tmp.newWindow = window.open('/products/' + id + '.html', 'Product Details', 'width=1920, location=no, scrollbars=yes, menubar=no, status=no, titlebar=no, fullscreen=no, toolbar=no');
		tmp.newWindow.focus();
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
				.observe('click',function(){
					$(this).select();
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
	,_recalculateSummary: function() {
		var tmp = {};
		tmp.me = this;
		tmp.totalExGSTPrice = 0;
		//getting all the items rows
		$$('.item_row.order-item-row').each(function(row){
			tmp.rowData = row.retrieve('data');
			tmp.totalExGSTPrice = tmp.totalExGSTPrice * 1 + tmp.me.getValueFromCurrency( tmp.rowData.totalPrice ) * 1;
		});
		jQuery('[save-order-summary="totalExGST"]').val(tmp.me.getCurrency(tmp.totalExGSTPrice)).html(tmp.me.getCurrency(tmp.totalExGSTPrice));

		//getting total GST
		tmp.totalGST = tmp.totalExGSTPrice * 0.1;
		jQuery('[save-order-summary="totalGST"]').val(tmp.me.getCurrency(tmp.totalGST)).html(tmp.me.getCurrency(tmp.totalGST));

		//getting total include GST
		tmp.totalIncGSTPrice = tmp.totalExGSTPrice * 1 + tmp.totalGST * 1;
		jQuery('[save-order-summary="totalInGST"]').val(tmp.me.getCurrency(tmp.totalIncGSTPrice)).html(tmp.me.getCurrency(tmp.totalIncGSTPrice));

		//getting shippingCost
		tmp.shippingCost = 0;
		if(jQuery('[save-order-summary="shippingCost"]').length > 0)
			tmp.shippingCost = tmp.me.getValueFromCurrency( jQuery('[save-order-summary="shippingCost"]').val() );

		//getting handling cost
		tmp.handlingCost = 0;
		if(jQuery('[save-order-summary="handlingCost"]').length > 0)
			tmp.handlingCost = tmp.me.getValueFromCurrency( jQuery('[save-order-summary="handlingCost"]').val() );

		//totalDue
		tmp.totalDue = tmp.totalIncGSTPrice * 1 + tmp.shippingCost * 1 + tmp.handlingCost * 1;
		jQuery('.total-payment-due').val(tmp.me.getCurrency(tmp.totalDue)).html(tmp.me.getCurrency(tmp.totalDue));
		return tmp.me;
	}
	/**
	 * adding a new product row after hit save btn
	 */
	,_addNewProductRow: function(btn, poItem) {
		var tmp = {};
		tmp.me = this;
		tmp.currentRow = $(btn).up('.new-order-item-input');
		tmp.product = (typeof poItem === 'undefined') ? tmp.currentRow.retrieve('product') : poItem.product;
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
		tmp.unitPrice =  (typeof poItem === 'undefined') ? tmp.me.getValueFromCurrency($F(tmp.unitPriceBox)) : poItem.unitPrice;
		if( (!jQuery.isNumeric(tmp.unitPrice)) && (tmp.unitPrice.match(/^\d+(\.\d{1,2})?$/)  === null) ) {
			tmp.me._markFormGroupError(tmp.unitPriceBox, 'Invalid value provided!');
			return ;
		}
		tmp.qtyOrderedBox = tmp.currentRow.down('[new-order-item=qtyOrdered]');
		tmp.qtyOrdered = (typeof poItem === 'undefined') ? tmp.me.getValueFromCurrency($F(tmp.qtyOrderedBox)) : poItem.qty;
		if(tmp.qtyOrdered.match(/^\d+(\.\d{1,2})?$/) === null) {
			tmp.me._markFormGroupError(tmp.qtyOrderedBox, 'Invalid value provided!');
			return ;
		}
		tmp.totalPrice = tmp.me.getValueFromCurrency(tmp.unitPrice) * tmp.qtyOrdered;
		//clear all error msg
		tmp.currentRow.getElementsBySelector('.form-group.has-error .form-control').each(function(control){
			$(control).retrieve('clearErrFunc')();
		});
		//get all data
		tmp.data = {
			'id': poItem && poItem.id ? poItem.id : '',
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
					tmp.me._recalculateSummary();
					tmp.row.remove();
				})
			})
		};
		tmp.currentRow.insert({'after': tmp.productRow = tmp.me._getProductRow(tmp.data).addClassName('btn-hide-row') });
		tmp.me.setProductLink(tmp.productRow.down('.productSku'), tmp.product.id);
		tmp.newRow = tmp.me._getNewProductRow();
		tmp.currentRow.replace(tmp.newRow);
		tmp.newRow.down('[new-order-item=product]').focus();
		tmp.newRow.down('[new-order-item=product]').select();

		tmp.me._recalculateSummary( tmp.totalPrice );
		return tmp.me;
	}
	,setProductLink: function(dom, id) {
		var tmp = {};
		tmp.me = this;
		$(dom).setStyle('text-decoration: underline; cursor: pointer;')
		.observe('click', function(e){
			Event.stop(e);
			tmp.window = window.open('/product/' + id + '.html', '_blank');
			tmp.window.focus()
		});
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
				.observe('click', function(event){
					$(this).select();
				})
			)
			,'qtyOrdered': tmp.me._getFormGroup( null, new Element('input', {'class': 'input-sm', 'new-order-item': 'qtyOrdered', 'required': 'Required!', 'value': '1'})
				.observe('keyup', function(){
					tmp.row =$(this).up('.item_row');
					tmp.unitPrice = tmp.me.getValueFromCurrency($F(tmp.row.down('[new-order-item=unitPrice]')));
					tmp.qty = $F(this);
					$(tmp.row.down('[new-order-item=totalPrice]')).value = tmp.me.getCurrency( tmp.unitPrice * tmp.qty);
				})
				.observe('click', function(event){
					$(this).select();
				})
			)
			,'totalPrice': tmp.me._getFormGroup( null, new Element('input', {'class': 'input-sm', 'disabled': true, 'new-order-item': 'totalPrice', 'required': 'Required!', 'value': tmp.me.getCurrency(0)})
				.observe('keyup', function(){
					tmp.row =$(this).up('.item_row');
					tmp.totalPrice = tmp.me.getValueFromCurrency($F(this));
					tmp.qty = $F(tmp.row.down('[new-order-item=qtyOrdered]'));
					$(tmp.row.down('[new-order-item=unitPrice]')).value = tmp.me.getCurrency( tmp.totalPrice / tmp.qty );
				})
				.observe('click', function(event){
					$(this).select();
				})
			)
			, 'btns': new Element('span', {'class': 'btn-group btn-group-sm pull-right new-order-item-btn'})
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
						tmp.newRow.down('[new-order-item=product]').select();
					})
				})
		};
		return tmp.me._getProductRow(tmp.data, false).addClassName('new-order-item-input').addClassName(tmp.me._isCredit === true ? 'danger' : 'info').removeClassName('order-item-row');
	}
	/**
	 * Getting the parts panel
	 */
	,_getPartsTable: function () {
		var tmp = {};
		tmp.me = this;
		//header row
		tmp.productListDiv = new Element('table', {'class': 'table table-hover table-condensed order_change_details_table'})
			.insert({'bottom': tmp.me._getProductRow({'product': {'sku': 'SKU', 'name': 'Description'}, 'unitPrice': 'Unit Price (Ex GST)', 'qtyOrdered': 'Qty', 'totalPrice': 'Total Price (Ex GST)'}, true)
				.wrap( new Element('thead') )
			});
		// tbody
		tmp.productListDiv.insert({'bottom': tmp.tbody = new Element('tbody', {'style': 'border: 3px #ccc solid;'})
			.insert({'bottom': tmp.me._getNewProductRow() })
		});
		// tfooter
		tmp.productListDiv.insert({'bottom': tmp.tbody = new Element('tfoot')
			.insert({'bottom': new Element('tr')
				.insert({'bottom': new Element('td', {'colspan': 2, 'rowspan': 6})
					.insert({'bottom': tmp.me._getFormGroup( 'Comments:', new Element('textarea', {'save-order': 'comments', 'rows': 8}) ) })
				})
				.insert({'bottom': new Element('td', {'colspan': 2, 'class': 'text-right active'}).update( new Element('span').update('Total Excl. GST: ') ) })
				.insert({'bottom': new Element('td', {'save-order-summary': 'totalExGST', 'class': 'active'}).update( tmp.me.getCurrency(0) ) })
			})
			.insert({'bottom': new Element('tr')
				.insert({'bottom': new Element('td', {'colspan': 2, 'class': 'text-right active', 'style': 'border-bottom: 1px solid brown'}).update( new Element('span').update('Total GST: ') ) })
				.insert({'bottom': new Element('td', {'save-order-summary': 'totalGST', 'class': 'active', 'style': 'border-bottom: 1px solid brown'}).update( tmp.me.getCurrency(0) ) })
			})
			.insert({'bottom': new Element('tr')
				.insert({'bottom': new Element('td', {'colspan': 2, 'class': 'text-right active'}).update( new Element('strong').update('SubTotal Incl. GST: ') ) })
				.insert({'bottom': new Element('td', {'save-order-summary': 'totalInGST', 'class': 'active'}).update( tmp.me.getCurrency(0) ) })
			})
			.insert({'bottom': new Element('tr')
				.insert({'bottom': new Element('td', {'colspan': 2, 'class': 'text-right active'}).update( new Element('span').update('Shipping Cost Incl. GST: ') ) })
				.insert({'bottom': new Element('td', {'class': 'active'}).update(
						new Element('input', {'save-order': 'shippingCost', 'save-order-summary': 'shippingCost', 'placeholder':  tmp.me.getCurrency(0)})
						.observe('change', function(){
							tmp.me._recalculateSummary();
						})
				) })
			})
			.insert({'bottom': new Element('tr')
				.insert({'bottom': new Element('td', {'colspan': 2, 'class': 'text-right active', 'style': 'border-bottom: 1px solid brown'}).update( new Element('span').update('Handling Cost  Incl. GST: ') ) })
				.insert({'bottom': new Element('td', {'class': 'active', 'style': 'border-bottom: 1px solid brown'}).update(
						new Element('input', {'save-order': 'handlingCost', 'save-order-summary': 'handlingCost', 'placeholder':  tmp.me.getCurrency(0)})
						.observe('change', function(){
							tmp.me._recalculateSummary();
						})
				) })
			})
			.insert({'bottom': new Element('tr')
				.insert({'bottom': new Element('td', {'colspan': 2, 'class': 'text-right active'}).update( new Element('h4').update('Total Due: ') ) })
				.insert({'bottom': new Element('td', {'class': 'active'}).update(
					new Element('h4',{'save-order-summary': 'totalPaymentDue', 'class': 'total-payment-due'}).update(tmp.me.getCurrency(0))
				) })
			})
		});
		return new Element('div', {'class': 'panel'}).addClassName(tmp.me._isCredit === true ? 'panel-danger' : 'panel-info')
			.insert({'bottom': new Element('div', {'class': 'panel-body table-responsive'})
				.insert({'bottom':  tmp.productListDiv})
			});
	}
	/**
	 * Getting the div of the order view
	 */
	,_getViewOfPurchaseOrder: function() {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = new Element('div')
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('div', {'class': 'col-sm-12'}).update(tmp.me._getSupplierInfoPanel()) })
			})
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('div', {'class': 'col-sm-12'}).update(tmp.me._getPartsTable()) })
			})
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('div', {'class': 'col-sm-12'}).update(tmp.me._saveBtns()) })
			});
		return tmp.newDiv;
	}
	,selectSupplier: function(supplier) {
		var tmp = {};
		tmp.me = this;
		tmp.me._supplier = supplier;
		tmp.newDiv = tmp.me._getViewOfPurchaseOrder();
		$(tmp.me._htmlIds.itemDiv).update(tmp.newDiv);
		tmp.newDiv.down('input[save-order="contactName"]').focus();
		tmp.newDiv.down('input[save-order="contactName"]').select();
		tmp.me._loadDataPicker();
		return tmp.me;
	}
	/**
	 * Getting the customer row for displaying the searching result
	 */
	,_getSupplierRow: function(supplier, isTitle) {
		var tmp = {};
		tmp.me = this;
		tmp.isTitle = (isTitle || false);
		tmp.tag = (tmp.isTitle === true ? 'th': 'td');
		tmp.newDiv = new Element('tr', {'class': (tmp.isTitle === true ? 'item_top_row' : 'btn-hide-row item_row') + (supplier.active == 0 ? ' danger' : ''), 'item_id': (tmp.isTitle === true ? '' : supplier.id)}).store('data', supplier)
			.insert({'bottom': new Element(tmp.tag)
				.insert({'bottom': (tmp.isTitle === true ? '&nbsp;':
					new Element('span', {'class': 'btn btn-primary btn-xs'}).update('select')
					.observe('click', function(){
						tmp.me.selectSupplier(supplier);
					})
				) })
			})
			.insert({'bottom': new Element(tmp.tag).update(supplier.name) })
			.insert({'bottom': new Element(tmp.tag).update(supplier.contactName) })
			.insert({'bottom': new Element(tmp.tag).update(supplier.contactNo) })
			.insert({'bottom': new Element(tmp.tag).update(supplier.description) })
			.insert({'bottom': new Element(tmp.tag)
				.insert({'bottom': (tmp.isTitle === true ? supplier.active : new Element('input', {'type': 'checkbox', 'disabled': true, 'checked': supplier.active}) ) })
			})
		;
		return tmp.newDiv;
	}
	/**
	 * Ajax: searching the customer
	 */
	,_searchSupplier: function (txtbox) {
		var tmp = {};
		tmp.me = this;
		tmp.searchTxt = $F(txtbox).strip();
		tmp.searchPanel = $(txtbox).up('#' + tmp.me._htmlIds.searchPanel);
		tmp.me.postAjax(tmp.me.getCallbackId('searchSupplier'), {'searchTxt': tmp.searchTxt}, {
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
								.insert({'bottom': tmp.me._getSupplierRow({'name': 'Supplier Name', 'contactName': 'Contact Name', 'contactNo': 'Contact Number', 'description': 'Description', 'active': 'Active?'}, true)  })
							})
							.insert({'bottom': tmp.listDiv = new Element('tbody') })
						})
					});
					tmp.result.items.each(function(item) {
						tmp.listDiv.insert({'bottom': tmp.me._getSupplierRow(item) });
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
	,_getSupplierListPanel: function () {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = new Element('div', {'id': tmp.me._htmlIds.searchPanel, 'class': 'panel search-panel'}).addClassName(tmp.me._isCredit === true ? 'panel-danger' : 'panel-info')
			.insert({'bottom': new Element('div', {'class': 'panel-heading form-inline'})
				.insert({'bottom': new Element('strong').update('Creating a new order for: ') })
				.insert({'bottom': new Element('span', {'class': 'input-group col-sm-6'})
					.insert({'bottom': new Element('input', {'class': 'form-control search-txt init-focus', 'placeholder': 'Supplier name'})
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
							}, null, 9);
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
								tmp.me._searchSupplier(tmp.txtBox);
							else {
								if($(tmp.me._htmlIds.searchPanel).down('.table tbody'))
									$(tmp.me._htmlIds.searchPanel).down('.table tbody').innerHTML = null;
							}
						})
					})
				})
				.insert({'bottom': new Element('span', {'class': 'btn btn-success pull-right btn-sm'})
					.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-plus'}) })
					.insert({'bottom': ' ADD' })
					.observe('click', function(){
						jQuery.fancybox({
							'width'			: '95%',
							'height'		: '95%',
							'autoScale'     : false,
							'autoDimensions': false,
							'fitToView'     : false,
							'autoSize'      : false,
							'type'			: 'iframe',
							'href'			: '/supplier/new.html?blanklayout=1'
							,'beforeClose'	    : function() {
								tmp.supplier = $$('iframe.fancybox-iframe').first().contentWindow.pageJs._item;
								if(tmp.supplier.id) { //successfully created a supplier
									tmp.me.selectSupplier(tmp.supplier);
								}
							}
				 		});
					})
				})
			});
		return tmp.newDiv;
	}
	,init: function(supplier) {
		var tmp = {};
		tmp.me = this;
		if(supplier) {
			tmp.me.selectSupplier(supplier);
		} else {
			$(tmp.me._htmlIds.itemDiv).update(tmp.me._getSupplierListPanel());
		}
		if($$('.init-focus').size() > 0){
			$$('.init-focus').first().focus();
		}
		return tmp.me;
	}
});