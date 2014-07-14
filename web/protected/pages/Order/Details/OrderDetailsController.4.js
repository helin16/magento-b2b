/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new BPCPageJs(), {
	_order: null //the order object
	,_orderStatuses: [] //the order statuses object
	,_paymentMethods: []
	,_orderItems: [] //the order items on that order
	,_resultDivId: '' //the result div id
	,_couriers: []	
	,_editMode: {'purchasing': false, 'warehouse': false, 'accounting': false, 'status': false} //the edit mode for purchasing and warehouse
	,_commentsDiv: {'pagination': {'pageSize': 5, 'pageNo': 1}, 'resultDivId': 'comments_result_div', 'type': ''} //the pagination for the comments
	,infoType_custName : 1
	,infoType_custEmail : 2
	,order_status_picked: 7
	
	,setEditMode: function(editPurchasing, editWH, editAcc, editStatus) {
		this._editMode.purchasing = (editPurchasing || false);
		this._editMode.warehouse = (editWH || false);
		this._editMode.accounting = (editAcc || false);
		this._editMode.status = (editStatus || false);
		return this;
	}
		
	,setOrder: function(order, orderItems, orderStatuses) {
		this._order = order;
		this._orderItems = orderItems;
		this._orderStatuses = orderStatuses;
		return this;
	}
	
	/* *** This function sets all the couriers to the class property *** */
	,setCourier: function(couriers) {
		var tmp = {};
		tmp.me = this;
		tmp.me._couriers = couriers;
		return tmp.me;
	}
	
	/* *** This function sets all the payment methods to the class property *** */
	,setPaymentMethods: function(paymentMethods) {
		var tmp = {};
		tmp.me = this;
		tmp.me._paymentMethods = paymentMethods;
		return tmp.me;
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
	 * Getting the field Div
	 */
	,_getfieldDiv: function(title, content) {
		return new Element('dl', {'class': 'dl-condensed'})
			.insert({'bottom': new Element('dt').update(title) })
			.insert({'bottom': new Element('dd').update(content) });
	}
	/**
	 * Getting the form group
	 */
	,_getFormGroup: function(title, content) {
		return new Element('div', {'class': 'form-group'})
		.insert({'bottom': new Element('label', {'class': 'control-label'}).update(title) })
		.insert({'bottom': content.addClassName('form-control') });
	}
	/**
	 * Collecting data from attrName
	 */
	,_collectData: function(attrName, groupIndexName) {
		var tmp = {};
		tmp.me = this;
		tmp.groupIndexName =  (groupIndexName || null);
		tmp.data = {};
		tmp.hasError = false;
		$$('[' + attrName + ']').each(function(item) {
			tmp.fieldName = item.readAttribute(attrName);
			if(item.hasAttribute('required') && $F(item).blank()) {
				tmp.me._markFormGroupError(item, 'This is requried');
				tmp.hasError = true;
			}
			
			tmp.itemValue = item.readAttribute('type') !== 'checkbox' ? $F(item).strip() : $(item).checked;
			if(item.hasAttribute('validate_currency') || item.hasAttribute('validate_number')) {
				if (tmp.me.getValueFromCurrency(tmp.itemValue).match(/^\d+(\.\d{1,2})?$/) === null) {
					tmp.me._markFormGroupError(item, (item.hasAttribute('validate_currency') ? item.readAttribute('validate_currency') : item.hasAttribute('validate_number')));
					tmp.hasErr = true;
				} else {
					tmp.value = item.hasAttribute('validate_currency') ? tmp.me.getValueFromCurrency(tmp.itemValue) : tmp.me.getValueFromCurrency(tmp.itemValue);
				}
			}
			//getting tehdata
			if(tmp.groupIndexName) {
				if(!tmp.data[tmp.groupIndexName])
					tmp.data[tmp.groupIndexName] = {};
				tmp.data[tmp.groupIndexName][tmp.fieldName] = tmp.itemValue;
			} else {
				tmp.data[tmp.fieldName] = tmp.itemValue;
			}
		});
		return (tmp.hasError === true ? null : tmp.data);
	}
	/**
	 * Ajax: check and submit payment
	 */
	,_submitPaymentConfirmation: function(button) {
		var tmp = {};
		tmp.me = this;
		
		tmp.date = tmp.me._collectData('payment_field', attrName);
		
		//validate the mandatory fields
		tmp.exception = '';
		if(tmp.paidAmount === null || tmp.paidAmount.blank() || isNaN(tmp.paidAmount))
			tmp.exception += 'Paid Amount is NOT valid\n';
		if(tmp.paymentMethod === null || tmp.paidAmount.blank())
			tmp.exception += 'Payment Method is NOT selected\n';
		if(!tmp.exception.blank()) {
			alert(tmp.exception);
			return this;
		}	
		
		tmp.amtDiff = Math.abs($F(tmp.confDiv.down('#paidAmtDiff')));
		tmp.hasErr = false;
		if(tmp.amtDiff !== 0) {
			tmp.confDiv.getElementsBySelector('.extraConf').each(function(item){
				if(item.readAttribute('type') === 'text') {
					tmp.extraComment = $F(item).strip();
					if(tmp.extraComment.blank() || tmp.extraComment === null) {
						item.insert({'before': new Element('div', {'class': 'msgDiv errorMsgDiv'}).update(new Element('div', {'class': 'msg'}).update('Additional Comment is Mandatory!') ) });
						tmp.hasErr = true;
					}
				}
				if(item.readAttribute('type') === 'checkbox') {
					if(!item.checked || item.checked === false || item.checked === null) {
						item.insert({'before': new Element('div', {'class': 'msgDiv errorMsgDiv'}).update(new Element('div', {'class': 'msg'}).update('Pls Select The Payment Check Box') ) });
						tmp.hasErr = true;
					}
				}
			});
		}
		
		if(tmp.hasErr === true)
			return this;
		
		tmp.me.postAjax(tmp.me.getCallbackId('confirmPayment'), {'paidAmt': tmp.paidAmount, 'paymentMethod': tmp.paymentMethod, 'amtDiff': tmp.amtDiff, 'extraComment': tmp.extraComment, 'order': tmp.me._order}, {
			'onLoading': function (sender, param) { 
				$(button).store('originValue', $(button).innerHTML).addClassName('disabled').update('saving ...');
			}
			,'onSuccess': function (sender, param) {
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					alert('Saved Successfully!');
					window.location = document.URL;
				} 
				catch (e) {
					alert(e);
				}
			},
			'onComplete': function (sender, param) {
				if($(button))
					$(button).update($(button).retrieve('originValue')).removeClassName('disabled');
			}
		});
		
		return this;
	}
	/**
	 * Getting the comments row
	 */
	,_getCommentsRow: function(comments) {
		return new Element('tr', {'class': 'comments_row'})
			.store('data', comments)
			.insert({'bottom': new Element('td', {'class': 'created', 'width': '15%'}).update(new Element('small').update(comments.created) ) })
			.insert({'bottom': new Element('td', {'class': 'creator', 'width': '15%'}).update(new Element('small').update(comments.creator) ) })
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
		tmp.me.postAjax(tmp.me.getCallbackId('getComments'), {'pagination': tmp.me._commentsDiv.pagination, 'order': tmp.me._order, 'type': tmp.me._commentsDiv.type}, {
			'onLoading': function (sender, param) {
				if(tmp.reset === true) {
					$(tmp.me._commentsDiv.resultDivId).update(new Element('img', {'src': '/themes/default/images/loading_big.gif'}));
				}
				if(btn) {
					jQuery('#' + btn.id).button('loading');
				}
			}
			,'onSuccess': function (sender, param) {
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					if(tmp.reset === true) {
						$(tmp.me._commentsDiv.resultDivId).update(tmp.me._getCommentsRow({'type': 'Type', 'creator': 'WHO', 'created': 'WHEN', 'comments': 'COMMENTS'}).addClassName('header').wrap( new Element('thead') ) );
					}
					if(!tmp.result)
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
					if(tmp.result.pagination.pageNumber < tmp.result.pagination.totalPages) {
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
			,'onComplete': function(sender, param) {
				if(btn) {
					jQuery('#' + btn.id).button('reset');
				}
			}
		})
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
		tmp.me.postAjax(tmp.me.getCallbackId('addComments'), {'comments': tmp.comments, 'order': tmp.me._order}, {
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
	 * Ajax: clearing the ETA
	 */
	,_clearETA: function(btn, item) {
		var tmp = {};
		tmp.me = this;
		if(!confirm('You are trying to mark a part as received/clearing the ETA?\n continue?'))
			return tmp.me;
		
		tmp.reason = prompt('The reason for clearing the ETA');
		if (tmp.reason === null) {
			return tmp.me;
		}
		tmp.me.postAjax(tmp.me.getCallbackId('clearETA'), {'item_id': item.id, 'comments': tmp.reason}, {
			'onLoading': function (sender, param) { }
			,'onSuccess': function(sender, param) {
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result)
						return;
					alert('ETA cleared Successfully!');
					window.location = document.URL;
				} catch (e) {
					alert(e);
				}
			}
			,'onComplete': function(sender, param) {}
		});
		return tmp.me;
	}
	/**
	 * Getting the edit call for purchasing
	 */
	,_getPurchasingEditCell: function(orderItem) {
		var tmp = {};
		tmp.me = this;
		tmp.hasStock = (orderItem.eta === '' ? '' : (orderItem.eta === '0001-01-01 00:00:00' ? true : false));
		tmp.isOrdered = (orderItem.isOrdered === false ? false : true);
		if(tmp.me._editMode.purchasing === false) {
			return;
		}
		tmp.selBoxDiv = tmp.me._getfieldDiv('Has Stock?',
			new Element('select', {'class': 'form-control input-sm', 'update_order_item_purchase': 'hasStock', 'required': true, 'order_item_purchase_id': orderItem.id})
				.insert({'bottom': new Element('option', {'value': ' '}).update('Not Checked')})
				.insert({'bottom': new Element('option', {'value': '1'}).update('Yes')})
				.insert({'bottom': new Element('option', {'value': '0'}).update('No')})
				.observe('change', function() {
					tmp.editPanel = $(this).up('.update_order_item_purchase_div');
					tmp.editPanel.getElementsBySelector('.no-stock-div').each(function(item) { item.remove(); });
					if($F(this) === '0') {
						tmp.editPanel.insert({'bottom': tmp.me._getfieldDiv('ETA:', 
							tmp.etaBox = new Element('input', {'class': 'form-control input-sm', 'type': 'datetime', 'update_order_item_purchase': 'eta', 'order_item_purchase_id': orderItem.id, 'required': true})
						).addClassName('no-stock-div dl-horizontal form-group') })
						.insert({'bottom': tmp.me._getfieldDiv('Has Ordered?',
							new Element('input', {'class': 'input-sm', 'type': 'checkbox', 'update_order_item_purchase': 'isOrdered', 'order_item_purchase_id': orderItem.id}) 
						).addClassName('no-stock-div dl-horizontal form-group') })
						.insert({'bottom': tmp.me._getfieldDiv('Comments:', 
							new Element('input', {'class': 'form-control input-sm', 'type': 'text', 'update_order_item_purchase': 'comments', 'order_item_purchase_id': orderItem.id, 'required': true})
						).addClassName('no-stock-div dl-horizontal form-group') });
						
						tmp.me._signRandID(tmp.etaBox);
						new Prado.WebUI.TDatePicker({'ID': tmp.etaBox.id, 'InputMode':"TextBox",'Format':"yyyy-MM-dd 17:00:00",'FirstDayOfWeek':1,'CalendarStyle':"default",'FromYear':2009,'UpToYear':2024,'PositionMode':"Bottom"});
					}
				})
		).addClassName('dl-horizontal form-group');
		return tmp.selBoxDiv.wrap(new Element('small', {'class': 'update_order_item_purchase_div update_order_item_div'}))
	}
	/**
	 * Getting the purchasing cell
	 */
	,_getPurchasingCell: function(orderItem) {
		var tmp = {};
		tmp.me = this;
		tmp.hasStock = (orderItem.eta === '' ? '' : (orderItem.eta === '0001-01-01 00:00:00' ? true : false));
		tmp.isOrdered = (orderItem.isOrdered === false ? false : true);
		//displaying only
		if(tmp.me._editMode.purchasing === false) {
			tmp.newDiv = new Element('small');
			if(tmp.hasStock === '')
				return tmp.newDiv.update('Not Checked');
			
			tmp.newDiv.insert({'bottom': new Element('div', {'class': tmp.hasStock ? 'text-success' : 'text-danger'})
				.insert({'bottom': new Element('strong').update('hasStock? ') })
				.insert({'bottom': new Element('span', {'class': 'glyphicon ' + (tmp.hasStock ? 'glyphicon-ok-circle' : 'glyphicon-remove-circle')}) })
				.insert({'bottom': new Element('a', {'href': 'javascript: void(0);', 'class': 'text-muted pull-right', 'title': 'comments'})
					.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-comment'}) })
				})
			});
			if(tmp.hasStock === false) {
				tmp.newDiv.insert({'bottom': new Element('div')
					.insert({'bottom': new Element('strong').update('ETA: ') })
					.insert({'bottom': new Element('span')
						.insert({'bottom': new Element('small').update(orderItem.eta + ' ') })
						.insert({'bottom': new Element('a', {'href': 'javascript: void(0);', 'class': 'text-danger', 'title': 'clear ETA'})
							.update(new Element('span', {'class': 'glyphicon glyphicon-remove'}))
							.observe('click', function() {
								tmp.me._clearETA(this, orderItem);
							})
						})
					})
				})
				.insert({'bottom': new Element('div')
					.insert({'bottom': new Element('strong').update('Is Ordered: ') })
					.insert({'bottom': new Element('input', {'type': 'checkbox', 'checked': tmp.isOrdered})
						.observe('change', function(event) {
							return tmp.me._changeIsOrdered(this, orderItem);
						})
					})
				});
			}
			return tmp.newDiv;
		}
		return tmp.me._getPurchasingEditCell(orderItem);
	}
	/**
	 * Getting each product row
	 */
	,_getProductRow: function(orderItem, isTitleRow) {
		var tmp = {};
		tmp.me = this;
		tmp.isTitle = (isTitleRow || false);
		tmp.tag = (tmp.isTitle === true ? 'th' : 'td');
		return new Element('tr', {'class': (tmp.isTitle === true ? '' : 'productRow'), 'order_item_id': orderItem.id})
			.store('data', orderItem)
			.insert({'bottom': new Element(tmp.tag, {'class': 'productName'}).update(orderItem.product.name + (tmp.isTitle === true ?  '' : "<div><small><strong>SKU: </strong>" + orderItem.product.sku + '</small></div>')) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'uprice'}).update(tmp.isTitle === true ? orderItem.unitPrice : tmp.me.getCurrency(orderItem.unitPrice)) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'qty'}).update(orderItem.qtyOrdered) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'tprice'}).update(tmp.isTitle === true ? orderItem.totalPrice : tmp.me.getCurrency(orderItem.totalPrice)) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'purchasing'}).update(tmp.isTitle === true ? 'Purchasing' : tmp.me._getPurchasingCell(orderItem)) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'warehouse'}).update(tmp.isTitle === true ? 'Warehouse' : tmp.me._getWarehouseCell(orderItem)) });
	}
	
	/**
	 * Getting the purchasing submit btns
	 */
	,_getPurchasingBtns: function() {
		var tmp = {};
		tmp.me = this;
		if(tmp.me._editMode.purchasing === false)
			return;
		return new Element('div')
			.insert({'bottom': new Element('span', {'class': 'btn btn-primary', 'data-loading-text': 'Saving...'})
				.update('submit')
				.observe('click', function() {
					tmp.btn = this;
					tmp.me._signRandID(tmp.btn);
					tmp.data = tmp.me._collectData('update_order_item_purchase', 'order_item_purchase_id');
					console.debug(tmp.data);
					if(tmp.data === null)
						return;
					tmp.me.postAjax(tmp.me.getCallbackId('updateOrder'), {'items': tmp.data, 'order': tmp.me._order, 'for': 'purchasing'}, {
						'onLoading': function(sender, param) {
							jQuery('#' + tmp.btn.id).button('loading');
						},
						'onSuccess': function(sender, param) {
							try {
								tmp.result = tmp.me.getResp(param, false, true);
								if(!tmp.result)
									return;
								alert('Saved Successfully!');
								window.location = document.URL;
							} catch (e) {
								alert(e);
							}
						},
						'onComplete': function(sender, param) {
							jQuery('#' + tmp.btn.id).button('reset');
						}
					});
				})
			});
	}
	/**
	 * Getting the parts panel
	 */
	,_getPartsTable: function () {
		var tmp = {};
		tmp.me = this;
		//header row
		tmp.productListDiv = new Element('table', {'class': 'table table-hover table-condensed order_change_details_table'})
			.insert({'bottom': tmp.me._getProductRow({'product': {'sku': 'SKU', 'name': 'Product Name'}, 'unitPrice': 'Unit Price', 'qtyOrdered': 'Qty', 'totalPrice': 'Total Price'}, true)
				.wrap( new Element('thead') )
			});
		
		// tbody
		tmp.productListDiv.insert({'bottom': tmp.tbody = new Element('tbody')  });
		tmp.me._orderItems.each(function(orderItem) {
			tmp.tbody.insert({'bottom': tmp.me._getProductRow(orderItem) });
		});
		// tfoot
		tmp.tfoot = new Element('tfoot').update(tmp.me._getProductRow({'product': {'sku': '', 'name': ''}, 'unitPrice': '', 'qtyOrdered':  '', 'totalPrice': ''}, true).addClassName('submitBtns'))
		tmp.tfoot.down('.purchasing').update(tmp.me._getPurchasingBtns() );
		tmp.tfoot.down('.warehouse').update(tmp.me._getWHBtns() );
		tmp.productListDiv.insert({'bottom':tmp.tfoot });
		return new Element('div', {'class': 'panel panel-default'})
			.insert({'bottom': new Element('div', {'class': 'panel-body table-responsive'})
				.insert({'bottom':  tmp.productListDiv})
			});
	}
	/**
	 * give the input box a random id
	 */
	,_signRandID: function(input) {
		if(!input.id)
			input.id = 'input_' + String.fromCharCode(65 + Math.floor(Math.random() * 26)) + Date.now();
		return this;
	}
	/**
	 * Marking a form group to has-error
	 */
	,_markFormGroupError: function(input, errMsg) {
		var tmp = {}
		tmp.me = this;
		if(input.up('.form-group')) {
			input.up('.form-group').addClassName('has-error');
			tmp.me._signRandID(input);
			jQuery('#' + input.id).tooltip({
				'trigger': 'manual'
				,'placement': 'auto'
				,'container': 'body'
				,'placement': 'bottom'
				,'html': true
				,'title': errMsg
			})
			.tooltip('show')
			.change(function() {
				input.up('.form-group').removeClassName('has-error');
				jQuery(this).tooltip('hide').tooltip('destroy').show();
			})
		}
		return tmp.me;
	}
	/**
	 * Check and save the shipment
	 */
	,_checkAndSubmitShippingOptions: function(button) {
		var tmp = {};
		tmp.me = this;
		tmp.hasErr = false;
		tmp.finalShippingDataArray = {};
		tmp.shippingDiv = $(button).up('.save_shipping_panel');
		//clear all error msgs
		tmp.shippingDiv.getElementsBySelector('.alert.alert-danger.alert-dismissible').each(function(item) { item.remove(); });
		//collect information
		tmp.shippingDiv.getElementsBySelector('[save_shipping]').each(function(item) {
			tmp.itemValue = $F(item).strip();
			if(item.hasAttribute('required')) {	
				if(tmp.itemValue.blank()) {
					tmp.me._markFormGroupError(item, 'Required!');
					tmp.hasErr = true;
				}
			}
			
			if (item.hasClassName('validate_number') ) {
				if (tmp.me.getValueFromCurrency(tmp.itemValue).match(/^\d+(\.\d{1,2})?$/) === null) {
					tmp.me._markFormGroupError(item, 'Invalid number provided!');
					tmp.hasErr = true;
				} else {
					tmp.value = tmp.me.getValueFromCurrency(tmp.itemValue);
				}
			} 
			tmp.finalShippingDataArray[item.readAttribute('save_shipping')] = tmp.itemValue;
		});
		if(tmp.hasErr === true)
			return;
		tmp.me.postAjax(tmp.me.getCallbackId('updateShippingInfo'), {'shippingInfo': tmp.finalShippingDataArray, 'order': tmp.me._order}, {
			'onLoading': function (sender, param) {
				jQuery('#' + button.id).button('loading');
			}
			,'onSuccess': function (sender, param) {
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result)
						return;
					alert('Saved Successfully!');
					window.location = document.URL;
				} catch (e) {
					$(button).insert({'before': tmp.me.getAlertBox('ERROR: ', e).addClassName('alert-danger') });
				}
			},
			'onComplete': function(sender, param) {
				jQuery('#' + button.id).button('reset');
			}
		});
	}
	/**
	 * Getting the shippment row
	 */
	,_getShippmentRow: function() {
		var tmp = {};
		tmp.me = this;
		tmp.shipmentListDiv = tmp.newShipmentDiv = null;
		if(tmp.me._order.status.id * 1 === tmp.me.order_status_picked && tmp.me._editMode.warehouse === true) {
			tmp.newShipmentDiv = new Element('div', {'class': 'panel-body save_shipping_panel'})
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('div', {'class': 'col-sm-2'})
						.insert({'bottom': tmp.me._getFormGroup('Contact Name:', new Element('input', {'type': 'text', 'save_shipping': 'contactName', 'required': true, 'class': 'input-sm', 'value': tmp.me._order.address.shipping.contactName}) ) })
					})
					.insert({'bottom': new Element('div', {'class': 'col-sm-2'})
						.insert({'bottom': tmp.me._getFormGroup('Contact No:', new Element('input', {'type': 'tel', 'save_shipping': 'contactNo', 'required': true, 'class': 'input-sm', 'value': tmp.me._order.address.shipping.contactNo}) ) })
					})
					.insert({'bottom': new Element('div', {'class': 'col-sm-2 bg-info'})
						.insert({'bottom': tmp.me._getFormGroup('Courier:', tmp.me._getCourierList().writeAttribute('save_shipping', 'courierId').writeAttribute('required', true) ) })
					})
					.insert({'bottom': new Element('div', {'class': 'col-sm-2 bg-info'})
						.insert({'bottom': tmp.me._getFormGroup('No Of Carton(s):', new Element('input', {'type': 'number', 'required': true, 'save_shipping': 'noOfCartons', 'class': 'input-sm validate_number', 'value': '1'}) ) })
					})
					.insert({'bottom': new Element('div', {'class': 'col-sm-2 bg-info'})
						.insert({'bottom': tmp.me._getFormGroup('Consignment No:', new Element('input', {'type': 'text', 'required': true, 'save_shipping': 'conNoteNo', 'class': 'input-sm'}) ) })
					})
					.insert({'bottom': new Element('div', {'class': 'col-sm-2 bg-info'})
						.insert({'bottom': tmp.me._getFormGroup('Est. Shipping Cost', new Element('input', {'type': 'text', 'required': true, 'save_shipping': 'actualShippingCost', 'class': 'input-sm validate_number'}) ) })
						.insert({'bottom': new Element('input', {'type': 'hidden', 'save_shipping': 'estShippingCost', 'class': 'input-sm'}) })
					})
				})
				.insert({'bottom': new Element('div', {'class': 'row'}) 
					.insert({'bottom': new Element('div', {'class': 'col-sm-4'})
						.insert({'bottom': tmp.me._getFormGroup('Street:', new Element('input', {'type': 'text', 'required': true, 'save_shipping': 'street', 'class': 'input-sm', 'value': tmp.me._order.address.shipping.street}) ) })
					})
					.insert({'bottom': new Element('div', {'class': 'col-sm-2'})
						.insert({'bottom': tmp.me._getFormGroup('City:', new Element('input', {'type': 'text', 'required': true, 'save_shipping': 'city', 'class': 'input-sm', 'value': tmp.me._order.address.shipping.city}) ) })
					})
					.insert({'bottom': new Element('div', {'class': 'col-sm-2'})
						.insert({'bottom': tmp.me._getFormGroup('State:', new Element('input', {'type': 'text', 'required': true, 'save_shipping': 'region', 'class': 'input-sm', 'value': tmp.me._order.address.shipping.region}) ) })
					})
					.insert({'bottom': new Element('div', {'class': 'col-sm-2'})
						.insert({'bottom': tmp.me._getFormGroup('Country:', new Element('input', {'type': 'text', 'required': true, 'save_shipping': 'country', 'class': 'input-sm', 'value': tmp.me._order.address.shipping.country}) ) })
					})
					.insert({'bottom': new Element('div', {'class': 'col-sm-2'})
						.insert({'bottom': tmp.me._getFormGroup('Post Code:', new Element('input', {'type': 'text', 'required': true, 'save_shipping': 'postCode', 'class': 'input-sm', 'value': tmp.me._order.address.shipping.postCode}) ) })
					})
				})
				.insert({'bottom': new Element('div', {'class': 'row'}) 
					.insert({'bottom': new Element('div', {'class': 'col-sm-12'})
						.insert({'bottom': tmp.me._getFormGroup('Delivery Instruction:', new Element('textarea', {'save_shipping': 'deliveryInstructions', 'class': 'input-sm'}) ) })
					})
					.insert({'bottom': new Element('div', {'class': 'col-sm-12'})
						.insert({'bottom': new Element('span', {'id': 'shipping_save_btn', 'class': 'btn btn-primary', 'data-loading-text': 'Saving...'}).update('Save')
							.observe('click', function() {
								tmp.me._checkAndSubmitShippingOptions(this);
							})
						})
					})
				})
				;
		}
		
		if(tmp.me._order.shippments.size() > 0) {
			tmp.shippingInfos = tmp.me._order.shippments;
			tmp.shipmentListDiv = new Element('small', {'class': 'viewShipping list-group'});
			tmp.shippingInfos.each(function(item) {
				tmp.shipmentListDiv.insert({'bottom': new Element('div', {'class': 'list-group-item'})
					.insert({'bottom': new Element('div', {'class': 'row'}) 
						.insert({'bottom': tmp.me._getfieldDiv('Date:', item.shippingDate).wrap(new Element('div', {'class': 'col-xs-6 col-sm-2'})) })
						.insert({'bottom': tmp.me._getfieldDiv('Est./Act.', tmp.me.getCurrency(item.estShippingCost) + ' / ' + tmp.me.getCurrency(item.actualShippingCost)).writeAttribute('title', 'Estimated Shipping Cost VS. Actual Shipping Cost').wrap(new Element('div', {'class': 'col-xs-6 col-sm-2'})) })
						.insert({'bottom': tmp.me._getfieldDiv('Receiver:', item.receiver).wrap(new Element('div', {'class': 'col-xs-6 col-sm-2'})) })
						.insert({'bottom': tmp.me._getfieldDiv('Contact No:', (item.contact)).wrap(new Element('div', {'class': 'col-xs-6 col-sm-2'})) })
						.insert({'bottom': tmp.me._getfieldDiv('Courier:', (item.courier.name)).wrap(new Element('div', {'class': 'col-xs-3 col-sm-1'}))})
						.insert({'bottom': tmp.me._getfieldDiv('Con. No:', item.conNoteNo).writeAttribute('title', 'Consignment Note Number').wrap(new Element('div', {'class': 'col-xs-6 col-sm-2'})) })
						.insert({'bottom': tmp.me._getfieldDiv('Cartons:', item.noOfCartons).writeAttribute('title', 'No Of Cartons Send On This Shipment').wrap(new Element('div', {'class': 'col-xs-3 col-sm-1'})) })
						.insert({'bottom': tmp.me._getfieldDiv('Shipping Address:', '<small><em>' + item.address.full + '</em></small>').wrap(new Element('div', {'class': 'col-xs-12 col-sm-6'})) })
						.insert({'bottom':  tmp.me._getfieldDiv('Delivery Instructions:', item.deliveryInstructions).wrap(new Element('div', {'class': 'col-xs-12 col-xs-6'}))})
					})
				})
			});
		}
		return (tmp.shipmentListDiv === null && tmp.newShipmentDiv === null) ? '' : new Element('div', {'class': 'panel panel-default'})
			.insert({'bottom': new Element('div', {'class': 'panel-heading'}).update('Shipping') })
			.insert({'bottom': tmp.newShipmentDiv })
			.insert({'bottom': tmp.shipmentListDiv });
	}
	/**
	 * Getting the address panel
	 */
	,_getAddressPanel: function() {
		var tmp = {};
		tmp.me = this;
		/// if an order does not have any info for customer name or email set the value to be N/A ///
		tmp.custName = tmp.custEmail = 'n/a';
		if(tmp.me._order.infos && tmp.me._order.infos !== null)
		{
			if(tmp.me._order.infos[tmp.me.infoType_custName] && tmp.me._order.infos[tmp.me.infoType_custName].length > 0)
				tmp.custName = tmp.me._order.infos[tmp.me.infoType_custName][0].value;
			if(tmp.me._order.infos[tmp.me.infoType_custEmail] && tmp.me._order.infos[tmp.me.infoType_custEmail].length > 0)
				tmp.custEmail = tmp.me._order.infos[tmp.me.infoType_custEmail][0].value;
		}
		return new Element('div', {'class': 'panel panel-default'})
			.insert({'bottom': new Element('div', {'class': 'panel-heading'})
				.insert({'bottom': new Element('strong').update(tmp.me._order.orderNo)
				})
				.insert({'bottom': new Element('span', {'class': 'pull-right'})
					.update('Status: ') 
					.insert({'bottom': tmp.me._getOrderStatus() })
				})
			})
			.insert({'bottom': new Element('div', {'class': 'panel-body'})
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('div', {'class': 'col-sm-6'})
						.insert({'bottom': new Element('strong').update('Customer: ') }) 
						.insert({'bottom': tmp.custName }) 
					})
					.insert({'bottom': new Element('div', {'class': 'col-sm-6'})
						.insert({'bottom': new Element('a', {'href': 'mailto:' + tmp.custEmail}).update(tmp.custEmail) })
					})
				})
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': tmp.me._getAddressDiv("Shipping Address: ", tmp.me._order.address.shipping).addClassName('col-xs-6') })
					.insert({'bottom': tmp.me._getAddressDiv("Billing Address: ", tmp.me._order.address.billing).addClassName('col-xs-6') })
				 })
			});
	}
	/**
	 * Getting the order information panel
	 */
	,_getInfoPanel: function() {
		var tmp = {};
		tmp.me = this;
		tmp.orderDate = new Date(tmp.me._order.orderDate.strip().replace(' ', 'T'));
		return new Element('div', {'class': 'panel panel-default order-info-div'})
			.insert({'bottom': new Element('div', {'class': 'panel-heading text-right'})
				.insert({'bottom': new Element('small').update('Order Date: ') })
				.insert({'bottom': new Element('strong').update( tmp.orderDate.toLocaleDateString() ) })
			})
			.insert({'bottom': new Element('div', {'class': 'panel-body'})
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('div', {'class': 'col-xs-4 text-right'}).update('<strong><small>Shipping:</small></strong>') })
					.insert({'bottom': new Element('div', {'class': 'col-xs-8'}).update('<em><small>' + tmp.me._order.infos[9][0].value + '</small></em>') })
				})
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('div', {'class': 'col-xs-4 text-right'}).update('<strong><small>Mage Payment:</small></strong>') })
					.insert({'bottom': new Element('div', {'class': 'col-xs-8'}).update(tmp.me._order.infos[6][0].value) })
				})
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('div', {'class': 'col-xs-4 text-right'}).update('<strong><small>Total Amount:</small></strong>') })
					.insert({'bottom': new Element('div', {'class': 'col-xs-8'}).update( tmp.me.getCurrency(tmp.me._order.totalAmount) ) })
				})
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('div', {'class': 'col-xs-4 text-right'}).update('<strong><small>Total Paid:</small></strong>') })
					.insert({'bottom': new Element('div', {'class': 'col-xs-8'}).update( tmp.me.getCurrency(tmp.me._order.totalAmount) ) })
				})
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('div', {'class': 'col-xs-4 text-right'}).update('<strong><small>Total Due:</small></strong>') })
					.insert({'bottom': new Element('div', {'class': 'col-xs-8'}).update( tmp.me.getCurrency(tmp.me._order.totalDue) ) })
				})
			});
	}
	/**
	 * bind Change EVENT to current box for currency formating
	 */
	,_currencyInputChanged: function(inputBox) {
		var tmp = {};
		tmp.me = this;
		if($F(inputBox).blank()) {
			return false;
		}
		tmp.inputValue = tmp.me.getValueFromCurrency($F(inputBox));
		if(tmp.inputValue.match(/^\d+(\.\d{1,2})?$/) === null) {
			tmp.me._markFormGroupError(inputBox, 'Invalid currency format provided!');
			return false;
		}
		$(inputBox).value = tmp.me.getCurrency(tmp.inputValue);
		return true;
	}
	/**
	 * Get Payment Row
	 */
	,_getPaymentRow: function() {
		var tmp = {};
		tmp.me = this;
		if(tmp.me._editMode.accounting !== true) {
			return '';
		}
		
		tmp.clearConfirmPanel = function(inputBox) {
			$(inputBox).up('.panel_row_confirm_panel').getElementsBySelector('.after_select_method').each(function(item) { item.remove(); })
		}
		tmp.selBox = new Element('select', {'class': 'input-sm', 'payment_field': 'payment_method_id'})
			.insert({'bottom': new Element('option', {'value': ''}).update('')  })
			.observe('change', function() {
				tmp.clearConfirmPanel(this);
				$(this).up('.panel_row_confirm_panel').down('[payment_field=paidAmount]').select();
			})
		tmp.me._paymentMethods.each(function(item) {
			tmp.selBox.insert({'bottom': new Element('option', {'value': item.id}).update(item.name) });
		});
		
		tmp.paymentDiv = new Element('div', {"class": 'panel panel-default payment_row_panel'})
			.insert({'bottom': new Element('div', {"class": 'panel-heading'}).update('Payments') })
			.insert({'bottom': new Element('div', {"class": 'panel-body panel_row_confirm_panel'})
				.insert({'bottom': new Element('div', {"class": 'row'})
					.insert({'bottom': new Element('div', {"class": 'col-xs-6 col-sm-2'})
						.insert({'bottom': tmp.me._getFormGroup('Method:', tmp.selBox) })
					})
					.insert({'bottom': new Element('div', {"class": 'col-xs-6 col-sm-2'})
						.insert({'bottom': tmp.me._getFormGroup('Paid:',new Element('input', {'type': 'text', 'payment_field': 'paidAmount', 'class': 'input-sm'})
							.observe('change', function() {
								if(tmp.me._currencyInputChanged(this) === false) {
									return;
								}
								tmp.clearConfirmPanel(this); //clear all after_select_method
								tmp.wrapperDiv = $(this).up('.panel_row_confirm_panel').down('.row');
								tmp.paymentCheckBox = new Element('input', {'type': 'checkbox', 'class': 'input-sm', 'payment_field': 'paymentChecked'});
								//if paid amount is different from total amount 
								if(Math.abs(Math.abs(parseFloat(tmp.me.getValueFromCurrency($F(this))).toFixed(2)) - Math.abs(parseFloat(tmp.me.getValueFromCurrency(tmp.me._order.totalAmount)).toFixed(2))) !== 0) {
									tmp.wrapperDiv.insert({'bottom': new Element('div', {"class": 'after_select_method col-xs-12 col-sm-4'})
										.insert({'bottom': tmp.me._getFormGroup('Comments:', new Element('input', {'type': 'text', 'class': 'after_select_method input-sm', 'payment_field': 'extraComments'}) ) })
									})
									.insert({'bottom': new Element('div', {"class": 'after_select_method col-xs-6 col-sm-2'})
										.insert({'bottom': tmp.me._getFormGroup('Pass?', tmp.paymentCheckBox.show() ) })
									})
								} else {
									tmp.wrapperDiv.insert({'bottom': tmp.paymentCheckBox.hide().addClassName('after_select_method').writeAttribute('checked', true) })
								}
								tmp.wrapperDiv.insert({'bottom': new Element('div', {"class": 'after_select_method col-xs-6 col-sm-2'})
									.insert({'bottom': new Element('span', {'class': 'btn btn-primary after_select_method'}).update('Confirm') 
										.observe('click', function(){
											tmp.me._submitPaymentConfirmation(this);
										}) 
									}) 
								});
							})
						) })
					})
				})
			})
		;
		return tmp.paymentDiv;
	}	
	/**
	 * initialising the doms
	 */
	,init: function(resultdiv) {
		var tmp = {};
		tmp.me = this;
		tmp.me._resultDivId = resultdiv;
		$(tmp.me._resultDivId).update(
			new Element('div')
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('div', {'class': 'col-sm-8'})
					.insert({'bottom': tmp.me._getAddressPanel() }) 	//getting the address row
				})
				.insert({'bottom': new Element('div', {'class': 'col-sm-4'})
					.insert({'bottom': tmp.me._getInfoPanel() })    	//getting the order info row
				})
			})
			.insert({'bottom': tmp.me._getPartsTable() })   	//getting the parts row
			.insert({'bottom': tmp.me._getPaymentRow() })   	//getting the payment row
			.insert({'bottom': tmp.me._getShippmentRow() }) 	//getting the EDITABLE shippment row
			.insert({'bottom': tmp.me._getEmptyCommentsDiv() }) //getting the comments row
		);
		return this;
	}
	/**
	 * load the js
	 */
	,load: function() {
		var tmp = {};
		tmp.me = this;
		//load the comments after
		tmp.me._getComments(true)
//			._getLatestPaymentMethod();
		return this;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	,_getHasStockSel: function(title, selectedValue, changeFunc) {
		var tmp = {};
		tmp.selBox = new Element('select', {'class': 'hasStock'})
			.insert({'bottom': new Element('option', {'value': ''}).update(title)})
			.insert({'bottom': new Element('option', {'value': 'Y'}).update('YES')})
			.insert({'bottom': new Element('option', {'value': 'N'}).update('NO')})
			.observe('change', changeFunc);
		if(!selectedValue.blank()) {
			tmp.options = tmp.selBox.getElementsBySelector('option');
			for( tmp.i = 0 ; tmp.i < tmp.options.size(); tmp.i++ ) {
				if(tmp.options[tmp.i].value === selectedValue)
					tmp.options[tmp.i].selected = true;
			}
		}
		return tmp.selBox;
	}
	
	,_changeIsOrdered: function(btn, orderItem) {
		var tmp = {};
		tmp.me = this;
		tmp.isOrdered = $(btn).checked;
		if(!confirm('You are going to change this order item to be: ' + (tmp.isOrdered === true ? 'ORDERED' : 'NOT ORDERED') ))
			return false;
		tmp.me.postAjax(tmp.me.getCallbackId('changeIsOrdered'), {'item_id': orderItem.id, 'isOrdered': tmp.isOrdered}, {
			'onLoading': function (sender, param) {}
			,'onSuccess': function(sender, param) {
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result)
						return;
					alert('IsOrdered flag changed Successfully!');
					window.location = document.URL;
				} catch (e) {
					alert(e);
				}
			}
			,'onComplete': function(sender, param) {}
		});
		return true;
	}
	
	,_getWarehouseCell: function(orderItem) {
		var tmp = {};
		tmp.me = this;
		if(tmp.me._editMode.warehouse === false || tmp.me._order.status.id * 1 === tmp.me.order_status_picked) {
			return new Element('div', {'class': 'order_item_details'})
				.insert({'bottom': tmp.me._getfieldDiv('Picked?: ', orderItem.isPicked ? 'Y' : 'N') })
				.insert({'bottom': tmp.me._getfieldDiv('Comments: ', 'show'	)
				});
		}
		tmp.func = function() {
			//remove error msg
			$(this).up('.cell').getElementsBySelector('.msgDiv').each(function(msg){
				msg.remove();
			});
			
			if($F(this) === 'N') {
				$(this).up('.operationDiv').update(new Element('div')
					.insert({'bottom': tmp.me._getfieldDiv('Comments: ', new Element('input', {'pick_order_item': 'comments', 'placeholder': 'The reason'})) })
					.insert({'bottom': new Element('a', {'href': 'javascript: void(0);'}).update('cancel')
						.observe('click', function() {
							$(this).up('.operationDiv').update(tmp.me._getHasStockSel('Is Picked?', '', tmp.func));
						})
					})
					.insert({'bottom': new Element('input', {'type': 'hidden', 'pick_order_item': 'isPicked', 'value': $F(this)}) })
				);
			} else {
				$(this).up('.operationDiv').insert({'bottom': new Element('input', {'type': 'hidden', 'pick_order_item': 'isPicked', 'value': $F(this)}) });
			}
		};
		return tmp.me._getHasStockSel('Is Picked?', '', tmp.func).wrap(new Element('div', {'class': 'operationDiv'}));
	}
	
	,_changeOrderStatus: function(selBox) {
		var tmp = {};
		tmp.me = this;
		tmp.msg = 'About to change the status of this order?\n Continue?';
		if(confirm(tmp.msg)) {
			tmp.comments = '';
			while(tmp.comments !== null && tmp.comments.blank()) {
				tmp.comments = window.prompt("Please Type in the reason for changing:");
			}
			//user has cancelled the input
			if(tmp.comments === null) {
				$(selBox).replace(tmp.me._getOrderStatus());
				return this;
			}
			
			tmp.me.postAjax(tmp.me.getCallbackId('changeOrderStatus'), {'order': tmp.me._order, 'orderStatusId': $F(selBox), 'comments': tmp.comments}, {
				'onLoading': function (sender, param) { 
					$(selBox).disabled = true; 
				}
				,'onSuccess': function (sender, param) {
					try {
						tmp.result = tmp.me.getResp(param, false, true);
						alert('Saved Successfully!');
						window.location = document.URL;
					} catch (e) {
						alert(e);
						$(selBox).disabled = false;
						$(selBox).replace(tmp.me._getOrderStatus());
					}
				}
				,'onComplete': function(sender, param) {
				}
			});
			return this;
		}
		$(selBox).replace(tmp.me._getOrderStatus());
		return this;
	}
	
	,_getWHBtns: function() {
		var tmp = {};
		tmp.me = this;
		if(tmp.me._editMode.warehouse === false || tmp.me._order.status.id * 1 === tmp.me.order_status_picked)
			return '';
		
		return new Element('div')
			.insert({'bottom': new Element('span', {'class': 'button'})
				.update('submit')
				.observe('click', function() {
					tmp.btn = this;
					tmp.data = tmp.me._collectData('warehouse', 'pick_order_item');
					if(tmp.data === null) {
						alert('Error Occurred, pls scroll up to see details!');
						return;
					} 
					
					//check all mandatory fields
					tmp.hasError = false;
					tmp.finalOrderItemArray = [];
					tmp.data.each(function(item) {
						if(item.warehouse.isPicked === 'N' && (!item.warehouse.comments || item.warehouse.comments === null || item.warehouse.comments.strip() === '')) {
							alert('Error Occurred, pls scroll up to see details!');
							tmp.hasError = true;
							return;
						}
						tmp.finalOrderItemArray.push(item);
					});
					if(tmp.hasError === true)
						return;
					
					tmp.me.postAjax(tmp.me.getCallbackId('updateOIForWH'), {'orderItems': tmp.finalOrderItemArray, 'order': tmp.me._order}, {
						'onLoading': function (sender, param) {
							tmp.btn.addClassName('disabled').update('Saving ...');
						}
						,'onSuccess': function (sender, param) {
							try {
								tmp.result = tmp.me.getResp(param, false, true);
								if(!tmp.result)
									return;
								alert('Saved Successfully!');
								window.location = document.URL;
							} catch (e) {
								alert(e);
							}
						},
						'onComplete': function(sender, param) {
							if(tmp.btn)
								tmp.btn.removeClassName('disabled').update('submit');
						}
					});							
				})
			});
	}
	,_getOrderStatus: function () {
		var tmp = {}
		tmp.me = this;
		if(tmp.me._editMode.status !== true)
			return tmp.me._order.status.name;
		tmp.selBox = new Element('select')
			.observe('change', function(){
				tmp.me._changeOrderStatus(this);
			});
		tmp.me._orderStatuses.each(function(status) {
			tmp.opt = new Element('option', {'value': status.id}).update(status.name);
			if(status.id === tmp.me._order.status.id)
				tmp.opt.writeAttribute('selected', true);
			tmp.selBox.insert({'bottom':  tmp.opt});
		})
		return tmp.selBox;
	}
	
	/// this function generates a select box populating all the courier infos ///
	,_getCourierList: function () {
		var tmp = {};
		tmp.me = this;
		tmp.courierSelect = new Element('select')
			.insert({'bottom': new Element('option', {'value': ''}).update('') });
		tmp.me._couriers.each(function(courier) {
			tmp.courierSelect.insert({'bottom': new Element('option', {'value': courier.id}).update(courier.name) });
		});
		return tmp.courierSelect;
	}
	,_getLatestPaymentMethod: function() {
		var tmp = {};
		tmp.me = this;
		tmp.insertPoint = $(tmp.me._resultDivId).down('#lastPaymentMethod');
		tmp.me.postAjax(tmp.me.getCallbackId('getPaymentDetails'), {'order': tmp.me._order}, {
			'onLoading': function (sender, param) { /*$(selBox).disabled = true;*/ }
			,'onSuccess': function (sender, param) {
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					if(tmp.result && tmp.result.items.length > 0)
					{
						tmp.lastPaymentMethod = tmp.result.items[0].method.name;
						tmp.insertPoint.update(tmp.lastPaymentMethod);
					}
					else
						tmp.insertPoint.update('N/A');
				} 
				catch (e) {
					alert(e);
				}
			}
		});
		return tmp.me;
	}
});
