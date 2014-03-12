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
	,_commentsDiv: {'pagination': {'pageSize': 10, 'pageNo': 1}, 'resultDivId': '', 'type': ''} //the pagination for the comments
	,infoType_custName : 1
	,infoType_custEmail : 2
	,order_status_picked: '7'
	,comment_type_warehouse: 'WAREHOUSE'
	,comment_type_purchasing: 'PURCHASING'	
	
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
	
	,_getAddressDiv: function(title, addr) {
		return new Element('div', {'class': 'addr'})
			.insert({'bottom': new Element('div', {'class': 'title'}).update(title) })
			.insert({'bottom': new Element('div', {'class': 'addr_content'})
				.insert({'bottom': new Element('div', {'class': 'contactName'}).update(addr.contactName) })
				.insert({'bottom': new Element('div', {'class': 'street'}).update(addr.street) })
				.insert({'bottom': new Element('div')
					.insert({'bottom': new Element('span', {'class': 'city inlineblock'}).update(addr.city) })
					.insert({'bottom': new Element('span', {'class': 'region inlineblock'}).update(addr.region) })
					.insert({'bottom': new Element('span', {'class': 'postcode inlineblock'}).update(addr.postCode) })
				})
			})
	}

	,_getfieldDiv: function(title, content) {
		return new Element('span', {'class': 'fieldDiv'})
			.insert({'bottom': new Element('span', {'class': 'fieldDiv_title'}).update(title) })
			.insert({'bottom': new Element('span', {'class': 'fieldDiv_content'}).update(content) });
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
	
	,_showLastestComments: function(comments) {
		var tmp = {};
		return new Element('div', {'class': 'comments'});
	}
	
	,_getPurchasingCell: function(orderItem) {
		var tmp = {};
		tmp.me = this;
		tmp.hasStock = (orderItem.eta === '' ? '' : (orderItem.eta === '0001-01-01 00:00:00' ? 'Y' : 'N'));
		tmp.isOrdered = (orderItem.isOrdered === false ? false : true);
		
		if(tmp.me._editMode.purchasing === false) {
			tmp.newDiv = new Element('div', {'class': 'order_item_details'});
			if(tmp.hasStock === '')
				return tmp.newDiv.update('N/A');
			return tmp.newDiv
				.insert({'bottom': tmp.me._getfieldDiv('hasStock?: ', tmp.hasStock) })
				.insert({'bottom': tmp.me._getfieldDiv('ETA: ', orderItem.eta) })
				.insert({'bottom': tmp.me._getfieldDiv('Comments: ', new Element('span', {'class': 'comment', 'comment_type': tmp.me.comment_type_purchasing, 'entity_name': 'OrderItem' }).update('click me') ) })
				.insert({'bottom': tmp.me._getfieldDiv('Is Ordered: ', new Element('span', {}).update((tmp.isOrdered === true ? 'Y' : 'N'))  ) });
		}
		
		tmp.getEditDiv = function(hasStock, eta) {
			tmp.etaBox = new Element('input', {'type': 'text', 'placeholder': 'ETA:', 'update_order_item': 'eta', 'id': 'order_item_' + orderItem.id, 'readonly': true, 'value': eta ? eta : ''});
			tmp.returnDiv = new Element('div')
				.insert({'bottom': tmp.me._getfieldDiv('ETA:', tmp.etaBox) })
				.insert({'bottom': tmp.me._getfieldDiv('Comments: ', new Element('input', {'update_order_item': 'comments', 'placeholder': 'The reason'})) })
				.insert({'bottom': tmp.me._getfieldDiv('Is Ordered: ', new Element('input', {'type': 'checkbox', 'update_order_item': 'isOrdered', 'is_ordered': 'is_ordered'}) ) })
				.insert({'bottom': new Element('a', {'href': 'javascript: void(0);'}).update('cancel')
					.observe('click', function() {
						$(this).up('.operationDiv').update(tmp.me._getHasStockSel('Has Stock?', hasStock, tmp.func));
					})
				});
			
			if(tmp.isOrdered === true)
				$(tmp.returnDiv).down('[is_ordered]').writeAttribute("checked", "checked");
			
			return tmp.returnDiv;
		};
		tmp.func = function() {
			//remove error msg
			$(this).up('.cell').getElementsBySelector('.msgDiv').each(function(msg){
				msg.remove();
			});
			
			if($F(this) === 'N') {
				$(this).up('.operationDiv').update(tmp.getEditDiv(tmp.hasStock));
				new Prado.WebUI.TDatePicker({'ID':'order_item_' + orderItem.id,'InputMode':"TextBox",'Format':"yyyy-MM-dd 17:00:00",'FirstDayOfWeek':1,'CalendarStyle':"default",'FromYear':2009,'UpToYear':2024,'PositionMode':"Bottom"});
			} else {
				$(this).up('.operationDiv').getElementsBySelector('[update_order_item]').each(function(item) { 
					item.remove();
				});
				if($F(this) === 'Y')
				{	
					$(this).up('.operationDiv').insert({'bottom': new Element('input', {'type': 'hidden', 'update_order_item': 'eta', 'value': '0001-01-01 00:00:00'}) });
					$(this).up('.operationDiv').insert({'bottom': new Element('input', {'type': 'hidden', 'update_order_item': 'isOrdered', 'value': 'false'}) });
				}	
			}	
		};
		if(tmp.hasStock === 'N') {
			return new Element('div', {'class': 'operationDiv'}).update(tmp.getEditDiv('', orderItem.eta));
		}
		
		tmp.selBox = tmp.me._getHasStockSel('Has Stock?', tmp.hasStock, tmp.func);
		tmp.returnDiv = new Element('div', {'class': 'operationDiv'});
		tmp.returnDiv.insert({'bottom': tmp.selBox});
		if(tmp.hasStock === 'Y')
		{
			tmp.returnDiv
				.insert({'bottom': new Element('input', {'type': 'hidden', 'update_order_item': 'eta', 'value': '0001-01-01 00:00:00'}) })
				.insert({'bottom': new Element('input', {'type': 'hidden', 'update_order_item': 'isOrdered', 'value': 'false'}) });
		}	
		
		return tmp.returnDiv;
	}
	
	,_getWarehouseCell: function(orderItem) {
		var tmp = {};
		tmp.me = this;
		if(tmp.me._editMode.warehouse === false) {
			return new Element('div', {'class': 'order_item_details'})
				.insert({'bottom': tmp.me._getfieldDiv('Picked?: ', orderItem.isPicked ? 'Y' : 'N') })
				.insert({'bottom': tmp.me._getfieldDiv('Comments: ', new Element('span', {'class': 'comment', 'comment_type': tmp.me.comment_type_warehouse, 'entity_name': 'OrderItem'}).update('click me')  ) });
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
	
	,_getProductRow: function(orderItem, isTitleRow) {
		var tmp = {};
		tmp.me = this;
		tmp.isTitle = (isTitleRow || false);
		tmp.newDiv = new Element('div', {'class': 'row ' + (tmp.isTitle === true ? '' : 'productRow')}).store('data', orderItem).writeAttribute('order_item_id', orderItem.id)
			.insert({'bottom': new Element('span', {'class': 'inlineblock cell sku'}).update(orderItem.product.sku) })
			.insert({'bottom': new Element('span', {'class': 'inlineblock cell productName'}).update(orderItem.product.name) })
			.insert({'bottom': new Element('span', {'class': 'inlineblock cell uprice'}).update(tmp.isTitle === true ? orderItem.unitPrice : tmp.me.getCurrency(orderItem.unitPrice)) })
			.insert({'bottom': new Element('span', {'class': 'inlineblock cell qty'}).update(orderItem.qtyOrdered) })
			.insert({'bottom': new Element('span', {'class': 'inlineblock cell tprice'}).update(tmp.isTitle === true ? orderItem.totalPrice : tmp.me.getCurrency(orderItem.totalPrice)) })
			.insert({'bottom': new Element('span', {'class': 'inlineblock cell purchasing'}).update(tmp.isTitle === true ? 'Purchasing' : tmp.me._getPurchasingCell(orderItem)) })
			.insert({'bottom': new Element('span', {'class': 'inlineblock cell warehouse'}).update(tmp.isTitle === true ? 'Warehouse' : tmp.me._getWarehouseCell(orderItem)) });
		return tmp.newDiv;
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
				'onLoading': function (sender, param) { $(selBox).disabled = true; }
				,'onComplete': function (sender, param) {
					try {
						tmp.result = tmp.me.getResp(param, false, true);
						alert('Saved Successfully!');
						location.reload();
					} catch (e) {
						alert(e);
						$(selBox).disabled = false;
						$(selBox).replace(tmp.me._getOrderStatus());
					}
				}
			});
			return this;
		}
		$(selBox).replace(tmp.me._getOrderStatus());
		return this;
	}
	
	,_submitPaymentConfirmation: function(button) {
		var tmp = {};
		tmp.me = this;
		tmp.paidAmount = $F($(button).up('.wrapper').down('#paidAmount'));
		tmp.paymentMethod = $F($(button).up('.wrapper').down('#paymentMethod'));
		tmp.confDiv = $(button).up('#extraConfDiv');
		tmp.extraComment = '';
		
		$(button).up('.wrapper').getElementsBySelector('.msgDiv').each(function(div) {
			div.remove();
		});
		
		tmp.exception = '';
		if(tmp.paidAmount === null || tmp.paidAmount.blank() || isNaN(tmp.paidAmount))
			tmp.exception += 'Paid Amount is NOT valid\n';
		if(tmp.paymentMethod === null || tmp.paidAmount.blank())
			tmp.exception += 'Payment Method is NOT selected\n';
		
		if(!tmp.exception.blank())
		{
			alert(tmp.exception);
			return this;
		}	
		
		tmp.amtDiff = Math.abs(tmp.confDiv.down('#paidAmtDiff').value);
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
			'onLoading': function (sender, param) { /*$(selBox).disabled = true;*/ }
			,'onComplete': function (sender, param) {
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					alert('Saved Successfully!');
					location.reload();
				} 
				catch (e) {
					alert(e);
					return;
				}
			}
		});
	}
	
	,_checkPaidAmount: function(txtBox) {
		var tmp = {};
		tmp.me = this;
		tmp.hasError = false;
		tmp.paidAmount = $F(txtBox).strip();
		tmp.paidAmount = tmp.paidAmount.replace(new RegExp('(\\$|\\s|,)', 'g'), '');
		$(txtBox).value = tmp.paidAmount;
		
		$(txtBox).up('.wrapper').getElementsBySelector('.msgDiv').each(function(div) {
			div.remove();
		});
		
		tmp.extraInfoDiv = $(txtBox).up('.wrapper').down('#extraConfDiv');
		if(tmp.extraInfoDiv.getElementsBySelector('.fieldDiv').length > 0)
		{
			tmp.extraInfoDiv.getElementsBySelector('.fieldDiv').each(function(ec) {
				ec.remove();
			});
		}
		
		tmp.paymentMethodElement = $(txtBox).up('.wrapper').down('#paymentMethod');
		tmp.paymentMethod = $F(tmp.paymentMethodElement).strip();
		if(tmp.paymentMethod.blank() || tmp.paymentMethod === null || tmp.paymentMethod === '')
		{
			tmp.paymentMethodElement.insert({'before': new Element('div', {'class': 'msgDiv errorMsgDiv'}).update(new Element('div', {'class': 'msg'}).update('Payment Method NOT selected') ) });
			tmp.hasError = true;
		}	
		
		if(tmp.paidAmount === null || tmp.paidAmount.blank() || isNaN(tmp.paidAmount))
		{
			$(txtBox).insert({'before': new Element('div', {'class': 'msgDiv errorMsgDiv'}).update(new Element('div', {'class': 'msg'}).update('Paid Amount is NOT valid') ) });
			tmp.hasError = true;
		}
		
		if(tmp.hasError === true)
			return;
		
		tmp.diff = Math.abs(Math.abs(parseFloat(tmp.paidAmount).toFixed(2)) - Math.abs(parseFloat(tmp.me._order.totalAmount).toFixed(2)));
		tmp.extraInfoDiv.down('#paidAmtDiff').value = tmp.diff;
		if(tmp.diff !== 0)
		{
			tmp.extraInfoDiv.insert({'bottom': tmp.me._getfieldDiv('Additional Comment', new Element('input', {'type': 'text', 'class': 'extraConf', 'id': 'extraComment'})) })
						    .insert({'bottom': tmp.me._getfieldDiv('Payment Check',new Element('input', {'type': 'checkbox', 'class': 'extraConf', 'id': 'extraPaymentCheck'})) });
		}
		tmp.extraInfoDiv.insert({'bottom': tmp.me._getfieldDiv('', new Element('span', {'class': 'button'}).update('Confirm Payment') 
									.observe('click', function(){
										tmp.me._submitPaymentConfirmation(this);
									})) 
								});
	}
	
	,_generateSelectionForPaymentMethod: function(pmArray) {
		var tmp = {};
		tmp.me = this;
		if((pmArray instanceof Array && pmArray.length === 0) || !(pmArray instanceof Array))
			pmArray = tmp.me._paymentMethods;
		
		tmp.selBox = new Element('select', {'id': 'paymentMethod', 'class': 'chosen2', 'title_text': 'Select Payment Method', 'dis_search': 'false'})
						.insert({'bottom': new Element('option', {'value': ''}).update('')  });
		pmArray.each(function(item) {
			tmp.selBox.insert({'bottom': new Element('option', {'value': item.id}).update(item.name) });
		});
		return tmp.selBox;
	}
	
	,_getFinanceBtns: function() {
		var tmp = {};
		tmp.me = this;
		if(tmp.me._editMode.accounting !== true) {
			return '';
		}
		return new Element('div', {"class": 'wrapper financeBtnWrapper'})
			.insert({'bottom': tmp.me._getfieldDiv('Payment Method:', tmp.me._generateSelectionForPaymentMethod(tmp.me._paymentMethods)
					.observe('change', function() {
						if(((tmp.paymentMethod = $F(this).strip()) !== '' && tmp.paymentMethod !== null && !tmp.paymentMethod.blank())  && 
						   (tmp.paidAmount = $F($(this).up('.financeBtnWrapper').down('#paidAmount')).strip()) !== '' && (!tmp.paidAmount.blank()) && (tmp.paidAmount !== null))
						{
							tmp.me._checkPaidAmount($(this).up('.financeBtnWrapper').down('#paidAmount'));
						}	
					})
			) })
			.insert({'bottom': tmp.me._getfieldDiv('Paid:',new Element('input', {'type': 'text', 'id': 'paidAmount'})
				.observe('change', function() {
					if((tmp.paymentMethod = $F($(this).up('.financeBtnWrapper').down('#paymentMethod')).strip()) !== '' && !tmp.paymentMethod.blank() && tmp.paymentMethod !== null)
						tmp.me._checkPaidAmount(this);
					else 
					{
						$(this).up('.financeBtnWrapper').getElementsBySelector('.msgDiv').each(function(item) {
							item.remove();
						});
						$(this).up('.financeBtnWrapper').down('#extraConfDiv').getElementsBySelector('.fieldDiv').each(function(item) {
							item.remove();
						});
					}	
				})
			) })
			.insert({'bottom': new Element('div', {'id': 'extraConfDiv'}) 
				.insert({'bottom': new Element('input', {'type': 'hidden', 'id': 'paidAmtDiff'}) })
			});
	}		
	
	,_collectData: function(colname, attrName) {
		var tmp = {};
		tmp.me = this;
		tmp.data = [];
		tmp.hasError = false;
		$(tmp.me._resultDivId).getElementsBySelector('.productRow .' + colname).each(function(cell) {
			//remove msg divs
			cell.getElementsBySelector('.msgDiv').each(function(div) {
				div.remove();
			});
			
			//check whether the user has made a change
			tmp.fields = cell.getElementsBySelector('[' + attrName + ']');
			if(tmp.fields.size() === 0) {
				cell.insert({'top': new Element('div', {'class': 'msgDiv errorMsgDiv'}).update(new Element('div', {'class': 'msg'}).update('Pls Select One!') ) });
				tmp.hasError = true;
			} else {
				//check if we have any empty data
				tmp.orderItem = cell.up('.row').retrieve('data');
				tmp.attrData = {'orderItem': tmp.orderItem};
				
				tmp.fields.each(function(field) {
					tmp.fieldName = field.readAttribute(attrName);
					if(field.readAttribute('type') !== 'checkbox')
						tmp.value = $F(field);
					else
						tmp.value = $(field).checked;
					
					if((typeof tmp.value == 'string') && tmp.value.blank()) {
						field.insert({'before': new Element('div', {'class': 'msgDiv errorMsgDiv'}).update(new Element('div', {'class': 'msg'}).update(tmp.fieldName + ' Required!') ) });
						tmp.hasError = true;
					} else {
						if(!tmp.attrData[colname])
							tmp.attrData[colname] = {};
						tmp.attrData[colname][tmp.fieldName] = tmp.value;
					}
				})
				tmp.data.push(tmp.attrData);
			}
			
		});
		return (tmp.hasError === true ? null : tmp.data);
	}
	
	,_getPurchasingBtns: function() {
		var tmp = {};
		tmp.me = this;
		if(tmp.me._editMode.purchasing === false)
			return '';
		return new Element('div')
			.insert({'bottom': new Element('span', {'class': 'button'}).update('submit')
				.observe('click', function() {
					tmp.btn = this;
					tmp.data = tmp.me._collectData('purchasing', 'update_order_item');
					if(tmp.data === null) {
						alert('Error Occurred, pls scroll up to see details!');
						return;
					}
					tmp.me.postAjax(tmp.me.getCallbackId('updateOrder'), {'items': tmp.data, 'order': tmp.me._order, 'for': 'purchasing'}, {
						'onLoading': function(sender, param) {tmp.btn.addClassName('disabled').update('Saving ...');},
						'onComplete': function(sender, param) {
							try {
								tmp.result = tmp.me.getResp(param, false, true);
								alert('Saved Successfully!');
								location.reload();
							} catch (e) {
								alert(e);
							}
							tmp.btn.removeClassName('disabled').update('submit');
						},
					});
				})
			});
	}
	,_getWHBtns: function() {
		var tmp = {};
		tmp.me = this;
		if(tmp.me._editMode.warehouse === false)
			return '';
		
		tmp.hasError = false;
		
		return new Element('div')
			.insert({'bottom': new Element('span', {'class': 'button'}).update('submit')
				.observe('click', function() {
					
					tmp.data = tmp.me._collectData('warehouse', 'pick_order_item');
					if(tmp.data === null) {
						alert('Error Occurred, pls scroll up to see details!');
						tmp.hasError = true;
						return;
					}
					else {
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
						{
							alert('fasfdds');
							return;
						}	
						
						//console.debug(tmp.finalOrderItemArray);
						
						tmp.me.postAjax(tmp.me.getCallbackId('updateOIForWH'), {'orderItems': tmp.finalOrderItemArray, 'order': tmp.me._order}, {
							'onLoading': function (sender, param) { /*$(selBox).disabled = true;*/ }
							,'onComplete': function (sender, param) {
								try {
									tmp.result = tmp.me.getResp(param, false, true);
									alert('Saved Successfully!');
									location.reload();
								} 
								catch (e) {
									alert(e);
									return;
								}
							}
						});							
					}		
				})
			});
	}
	
	,_getCommentsRow: function(comments) {
		return new Element('div', {'class': 'row comments'})
			.insert({'bottom': new Element('span', {'class': 'inlineblock cell created'}).update(comments.created) })
			.insert({'bottom': new Element('span', {'class': 'inlineblock cell creator'}).update(comments.creator) })
			.insert({'bottom': new Element('span', {'class': 'inlineblock cell type'}).update(comments.type) })
			.insert({'bottom': new Element('span', {'class': 'inlineblock cell comments'}).update(comments.comments) });
	}
	
	,_getComments: function (reset, btn) {
		var tmp = {};
		tmp.me = this;
		tmp.reset = (reset || false);
		tmp.me.postAjax(tmp.me.getCallbackId('getComments'), {'pagination': tmp.me._commentsDiv.pagination, 'order': tmp.me._order, 'type': tmp.me._commentsDiv.type}, {
			'onLoading': function (sender, param) {
				if(tmp.reset === true) {
					$(tmp.me._commentsDiv.resultDivId).update(new Element('img', {'src': '/themes/default/images/loading_big.gif'}));
				}
				if(btn) {
					$(btn).store('originValue', $F(btn)).addClassName('disabled').setValue('Getting ...').disabled = true;
				}
			}
			,'onComplete': function (sender, param) {
				try {
					tmp.result = tmp.me.getResp(param, false, true);

					if(tmp.reset === true) {
						$(tmp.me._commentsDiv.resultDivId).update(tmp.me._getCommentsRow({'type': 'Type', 'creator': 'WHO', 'created': 'WHEN', 'comments': 'COMMENTS'}).addClassName('header'));
					}
					tmp.result.items.each(function(item) {
						$(tmp.me._commentsDiv.resultDivId).insert({'bottom': tmp.me._getCommentsRow(item) });
					})
					
					if(btn) 
						$(btn).remove();
					if(tmp.result.pagination.pageNumber < tmp.result.pagination.totalPages) {
						$(tmp.me._commentsDiv.resultDivId).insert({'bottom': new Element('input', {'type': 'button', 'class': 'button', 'value': 'Get More Comments'})
							.observe('click', function(){
								tmp.me._commentsDiv.pagination.pageNo = tmp.me._commentsDiv.pagination.pageNo * 1 + 1;
								tmp.me._getComments(false, this);
							})
						})
					}
				} catch (e) {
					alert(e)
					if(btn) {
						tmp.orginalValue = $(btn).retrieve('originValue');
						$(btn).removeClassName('disabled').setValue(tmp.orginalValue).disabled = false;
					}
				}
			}
		})
		return this;
	}
	
	,_addComments: function(btn, commentsResultDivId) {
		var tmp = {};
		tmp.me = this;
		tmp.commentsBox = $(btn).up('.new_comments_wrapper').down('[new_comments=comments]');
		tmp.comments = $F(tmp.commentsBox);
		if(tmp.comments.blank())
			return this;
		tmp.me.postAjax(tmp.me.getCallbackId('addComments'), {'comments': tmp.comments, 'order': tmp.me._order}, {
			'onLoading': function(sender, param) {
				$(btn).store('originValue', $F(btn)).addClassName('disabled').setValue('saving ...').disabled = true;
			}
			,'onSuccess': function (sender, param) {
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result) {
						return;
					}
					$(commentsResultDivId).down('.header').insert({'after': tmp.me._getCommentsRow(tmp.result) });
					tmp.commentsBox.setValue('');
				} catch (e) {
					alert(e);
				}
				tmp.originValue = $(btn).retrieve('originValue');
				$(btn).removeClassName('disabled').setValue(tmp.originValue).disabled = false;
			}
		})
		return this;
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
		tmp.courierSelect = new Element('select', {'class': 'chosen', 'title_text': 'Select Post Option', 'dis_search': 'false'})
			.insert({'bottom': new Element('option', {'value': ''}).update('') });
		tmp.me._couriers.each(function(courier) {
			tmp.courierSelect.insert({'bottom': new Element('option', {'value': courier.id}).update(courier.name) });
		});
		return tmp.courierSelect;
	}
	
	,_checkAndSubmitShippingOptions: function(button) {
		var tmp = {};
		tmp.me = this;
		
		tmp.hasErr = false;
		tmp.finalShippingDataArray = {};
		
		tmp.shippingDiv = $(button).up('#shippingInfoDiv');
		tmp.shippingDiv.getElementsBySelector('.msgDiv').each(function(errorDiv) {
			errorDiv.remove();
		});
		
		tmp.shippingDiv.getElementsBySelector('.shipmentInfo').each(function(item) {
			tmp.insertData = false;
			tmp.itemValue = $F(item).strip();
			if(item.readAttribute('mandatory') === 'true')
			{	
				if(tmp.itemValue === null || !tmp.itemValue || tmp.itemValue === '' || tmp.itemValue.blank())
				{
					item.insert({'before': new Element('div', {'class': 'msgDiv errorMsgDiv'}).update(new Element('div', {'class': 'msg'}).update(item.readAttribute('displayValue') + ' is Mandatory!') ) });
					tmp.hasErr = true;
				}
				else
				{
					if(item.hasAttribute('amount') && item.readAttribute('amount') === 'true')
					{
						tmp.itemValue = tmp.itemValue.replace(new RegExp('(\\$|\\s|,)', 'g'), '');
						$(item).value = tmp.itemValue;
						if(isNaN(tmp.itemValue))
						{
							item.insert({'before': new Element('div', {'class': 'msgDiv errorMsgDiv'}).update(new Element('div', {'class': 'msg'}).update(item.readAttribute('displayValue') + ' is Not Valid!') ) });
							tmp.hasErr = true;
						}
						else
							tmp.insertData = true;
					}
					else
						tmp.insertData = true;
				}
			}
			else
				tmp.insertData = true;
			
			if(tmp.insertData === true && tmp.hasErr === false)
			{
				if(!(item.readAttribute('dataType') in tmp.finalShippingDataArray))
					tmp.finalShippingDataArray[item.readAttribute('dataType')] = [];
				
				tmp.finalShippingDataArray[item.readAttribute('dataType')].push(tmp.itemValue);
			}
		});
		
		if(tmp.hasErr === true)
			return;
		
		tmp.me.postAjax(tmp.me.getCallbackId('updateShippingInfo'), {'shippingInfo': tmp.finalShippingDataArray, 'order': tmp.me._order}, {
			'onLoading': function (sender, param) { /*$(selBox).disabled = true;*/ }
			,'onComplete': function (sender, param) {
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					alert('Saved Successfully!');
					location.reload();
				} 
				catch (e) {
					alert(e);
					return;
				}
			}
		});
	}
	
	/* Generating the EDITABLE Shipping Info Receiver and Courier details. Function is used by _getShippingRow() */
	,_generateShippingReceiverInfoDetailsForEdit: function() {
		var tmp = {};
		tmp.me = this;
		return new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('span', {'class': 'courier'})
					.insert({'bottom': tmp.me._getfieldDiv('Courier:', tmp.me._getCourierList().addClassName('shipmentInfo')
																					   .writeAttribute('dataType', 'courierId')
																					   .writeAttribute('displayValue', 'Courier Info')
																					   .writeAttribute('mandatory', 'true')
																					   
						) })
				})
				.insert({'bottom': new Element('span', {'class': 'contactName'})
					.insert({'bottom': tmp.me._getfieldDiv('Contact Name:', new Element('input', {'type': 'text', 'dataType': 'contactName', 'displayValue': 'Contact Name', 'class': 'shipmentInfo'}).writeAttribute('value', tmp.me._order.address.shipping.contactName) ) })
				})
				.insert({'bottom': new Element('span', {'class': 'contactNo'})
					.insert({'bottom': tmp.me._getfieldDiv('Contact No:', new Element('input', {'type': 'text', 'dataType': 'contactNo', 'displayValue': 'Contact No', 'class': 'shipmentInfo'}).writeAttribute('value', tmp.me._order.address.shipping.contactNo) ) })
				});
	}
	
	/* Generating the EDITABLE Shipping Address Info details. Function is used by _getShippingRow() */
	,_generateShippingAddressDetailsForEdit: function() {
		var tmp = {};
		tmp.me = this;
		return new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('span', {'class': 'street'})
					.insert({'bottom': tmp.me._getfieldDiv('Street:', new Element('input', {'type': 'text', 'mandatory': 'true', 'dataType': 'street', 'displayValue': 'Street', 'class': 'shipmentInfo'}).writeAttribute('value', tmp.me._order.address.shipping.street) ) })
				})
				.insert({'bottom': new Element('span', {'class': 'city'})
					.insert({'bottom': tmp.me._getfieldDiv('City:', new Element('input', {'type': 'text', 'mandatory': 'true', 'dataType': 'city', 'displayValue': 'City', 'class': 'shipmentInfo'}).writeAttribute('value', tmp.me._order.address.shipping.city) ) })
				})
				.insert({'bottom': new Element('span', {'class': 'region'})
					.insert({'bottom': tmp.me._getfieldDiv('Region:', new Element('input', {'type': 'text', 'mandatory': 'true', 'dataType': 'region', 'displayValue': 'Region', 'class': 'shipmentInfo'}).writeAttribute('value', tmp.me._order.address.shipping.region) ) })
				})
				.insert({'bottom': new Element('span', {'class': 'country'})
					.insert({'bottom': tmp.me._getfieldDiv('Country:', new Element('input', {'type': 'text', 'mandatory': 'true', 'dataType': 'country', 'displayValue': 'Counter', 'class': 'shipmentInfo'}).writeAttribute('value', tmp.me._order.address.shipping.country) ) })
				})
				.insert({'bottom': new Element('span', {'class': 'postCode'})
					.insert({'bottom': tmp.me._getfieldDiv('Post Code:', new Element('input', {'type': 'text', 'mandatory': 'true', 'dataType': 'postCode', 'displayValue': 'Post Code', 'class': 'shipmentInfo'}).writeAttribute('value', tmp.me._order.address.shipping.postCode) ) })
				});
	}
	
	/* Generating the EDITABLE Other Shipping Info details. Function is used by _getShippingRow() */
	,_generateOtherShippingDetailsForEdit: function() {
		var tmp = {};
		tmp.me = this;
		return new Element('div', {'class': 'row'}) 
				.insert({'bottom': new Element('span', {'class': 'noOfCartons'})
					.insert({'bottom': tmp.me._getfieldDiv('No Of Carton(s):', new Element('input', {'type': 'text', 'mandatory': 'true', 'dataType': 'noOfCartons', 'displayValue': 'No Of Carton', 'class': 'shipmentInfo'}) ) })
				})
				.insert({'bottom': new Element('span', {'class': 'consignmentNo'})
					.insert({'bottom': tmp.me._getfieldDiv('Consignment No:', new Element('input', {'type': 'text', 'mandatory': 'true', 'dataType': 'conNoteNo', 'displayValue': 'Consignment No', 'class': 'shipmentInfo'}) ) })
				})
				.insert({'bottom': new Element('span', {'class': 'estShippingCost'})
					.insert({'bottom': tmp.me._getfieldDiv('Estimated Shipping Cost', new Element('input', {'type': 'text', 'mandatory': 'true', 'amount': 'true', 'dataType': 'estShippingCost', 'displayValue': 'Est Shipping Cost', 'class': 'shipmentInfo number'}) ) })
				});
	}
	
	/* Generating the EDITABLE Shipping Info details */
	,_getShippingRow: function() {
		var tmp = {};
		tmp.me = this;
		
		return  new Element('div', {'class': 'shippingWrapper', 'id': 'shippingInfoDiv'})
			.insert({'bottom': new Element('div', {'class': 'row header'}).update('Receiver Info') })
			.insert({'bottom': tmp.me._generateShippingReceiverInfoDetailsForEdit() })
			.insert({'bottom': new Element('div', {'class': 'row header'}).update('Shipping Address Info') })
			.insert({'bottom': tmp.me._generateShippingAddressDetailsForEdit() })
			.insert({'bottom': new Element('div', {'class': 'row header'}).update('Other Shipping Details') })
			.insert({'bottom': tmp.me._generateOtherShippingDetailsForEdit() })
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': tmp.me._getfieldDiv('Delivery Instruction:', new Element('textarea', {'mandatory': 'false', 'dataType': 'deliveryInstructions', 'class': 'shipmentInfo'}) ).addClassName('deliveryIns') })
				.insert({'bottom': new Element('span', {'class': 'submitShipping fieldDiv'})
					.insert({'bottom': new Element('span', {'class': 'button'}).update('Save')
							.observe('click', function() {
								tmp.me._checkAndSubmitShippingOptions(this);
							})
					})
				})
			});
	}
	
	/* *** Generating the VIEWABLE Shipping Details *** */
	,_viewShippingDetails: function(shippingInfos) {
		var tmp = {};
		tmp.me = this;
		
		if(!shippingInfos instanceof Array || shippingInfos.length === 0)
			shippingInfos = tmp.me._order.shippment;
		
		tmp.finalReturnDiv = new Element('div', {'class': 'viewShipping'});
		
		shippingInfos.each(function(item) {
			tmp.returnDiv = new Element('div');
			tmp.returnDiv.insert({'bottom': new Element('div', {}) 
				.insert({'bottom': tmp.me._getfieldDiv('Receiver:', item.receiver) })
				.insert({'bottom': tmp.me._getfieldDiv('Contact No:', (item.contact)) })
				.insert({'bottom': tmp.me._getfieldDiv('Shipping Address:', item.address) })
				.insert({'bottom': tmp.me._getfieldDiv('Courier:', (item.courier.name)) })
			})
			.insert({'bottom': new Element('div', {})
				.insert({'bottom': tmp.me._getfieldDiv('No Of Cartons:', item.noOfCartons) })
				.insert({'bottom': tmp.me._getfieldDiv('Est Shipping Cost:', (item.estShippingCost)) })
				.insert({'bottom': tmp.me._getfieldDiv('Consignment No:', item.conNoteNo) })
				.insert({'bottom': tmp.me._getfieldDiv('Shipping Date:', item.shippingDate) })
			})
			.insert({'bottom': new Element('div', {})
				.insert({'bottom': tmp.me._getfieldDiv('Delivery Instructions:', new Element('textarea', {'readonly': 'readonly'}).update(item.deliveryInstructions).addClassName('deliveryIns') ) })
				
			});
			
			tmp.finalReturnDiv.insert({'bottom': tmp.returnDiv});
		});
		
		return tmp.finalReturnDiv;
	}
	
	/* *** This function will generate the comments in a organized manner to be used in the  *** */
	,_generateCommentsForDisplayInQTip: function(comments) {
		var tmp = {};
		tmp.me = this;
		
		tmp.counter = 0;
		tmp.returnDiv = new Element('div', {});
		comments.each(function(item) {
			tmp.returnDiv.insert({'bottom': new Element('div', {})  
				.insert({'bottom': tmp.me._getfieldDiv((++tmp.counter) + '.', item.comments.strip()) })
			});
		});
		return tmp.returnDiv;
	}
	
	/* *** This function will bind the JQuery QTip event to all the OrderItem Purchasing/Warehouse Comment link(s) *** */
	,_bindAllCommentsForOrderItems: function() {
		var tmp = {};
		tmp.me = this;
		jQuery('.productlist .comment').qtip({
			content: {
				text: function(event, api) {
					tmp.orderId = api.elements.target.parents('.productRow').attr('order_item_id');
					tmp.commentType = api.elements.target.attr('comment_type');
					tmp.entityName = api.elements.target.attr('entity_name');
					tmp.data = {'entityId': tmp.orderId, 'entity': tmp.entityName, 'type': tmp.commentType};
					
                    jQuery.ajax({
                        url: '/ajax/getComments', // Use href attribute as URL
                        data: tmp.data,
                        type: 'POST',
                        dataType: 'json'
                    })
                    .then(function(result) {
                        // Set the tooltip content upon successful retrieval
                        if((result instanceof Array && result.length === 0) || result === null || !result || result === undefined)
                        	api.set('content.text', 'Nothing found');
                        else
                        {
                        	if(result instanceof Array) 
                        		api.set('content.text', pageJs._generateCommentsForDisplayInQTip(result));
                        }
                    	//api.set('content.text', );
                    }, function(xhr, status, error) {
                        // Upon failure... set the tooltip content to error
                        api.set('content.text', status + ': ' + error);
                    });
        
                    return 'Loading...'; // Set some initial text
                },
                title: function(event, api) {
                	return api.elements.target.attr('comment_type') + ' Comments:';
                },
				button: true,
			},
			show: {
				event: 'click mouseenter'
			},
			hide : {
				event: 'click',
				inactive: 5000
			}
		});
		return tmp.me;
	}
	
	,_getLatestPaymentMethod: function() {
		var tmp = {};
		tmp.me = this;
		
		tmp.insertPoint = $(tmp.me._resultDivId).down('#lastPaymentMethod');
		
		tmp.me.postAjax(tmp.me.getCallbackId('getPaymentDetails'), {'order': tmp.me._order}, {
			'onLoading': function (sender, param) { /*$(selBox).disabled = true;*/ }
			,'onComplete': function (sender, param) {
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					//console.debug(tmp.result);
					if(tmp.result.items.length > 0)
					{
						tmp.lastPaymentMethod = tmp.result.items[0].method.name;
						tmp.insertPoint.update(tmp.lastPaymentMethod);
					}
					else
						tmp.insertPoint.update('N/A');
				} 
				catch (e) {
					alert(e);
					return;
				}
			}
		});
		return tmp.me;
	}
	
	,_loadChosen: function () {
		$$(".chosen").each(function(item) {
			item.store('chosen', new Chosen(item, {
				no_results_text: "Oops, nothing found!",
				placeholder_text_single: ((titleText = $(item).readAttribute('title_text').strip()) !== '' ? titleText : 'Select Post Option'),
				disable_search: ((titleText = $(item).readAttribute('dis_search').strip()) === 'true' ? true : false),
				width: "100%"
			}) );
		});
		return this;
	}
	
	,load: function(resultdiv) {
		var tmp = {};
		tmp.me = this;
		tmp.me._resultDivId = resultdiv;
		tmp.newDiv = new Element('div');
		
		/// if an order does not have any info for customer name or email set the value to be N/A ///
		tmp.custName = tmp.custEmail = 'n/a';
		if(tmp.me._order.infos && tmp.me._order.infos !== null)
		{
			if(tmp.me._order.infos[tmp.me.infoType_custName] && tmp.me._order.infos[tmp.me.infoType_custName].length > 0)
				tmp.custName = tmp.me._order.infos[tmp.me.infoType_custName][0].value;
			if(tmp.me._order.infos[tmp.me.infoType_custEmail] && tmp.me._order.infos[tmp.me.infoType_custEmail].length > 0)
				tmp.custEmail = tmp.me._order.infos[tmp.me.infoType_custEmail][0].value;
		}
		
		//getting the order info row
		tmp.newDiv.insert({'bottom': new Element('fieldset', {'class': 'row orderInfo'})
			.insert({'bottom': new Element('legend').update('info') })
			.insert({'bottom': new Element('span', {'class': 'orderNo inlineblock'}).update(tmp.me._getfieldDiv('Order No.', tmp.me._order.orderNo)) })
			.insert({'bottom': new Element('span', {'class': 'orderDate inlineblock'}).update(tmp.me._getfieldDiv('Order Date:', tmp.me._order.orderDate)) })
			.insert({'bottom': new Element('span', {'class': 'orderStatus inlineblock'}).update(tmp.me._getfieldDiv('Order Status:', tmp.me._getOrderStatus() )) })
		});
		
		//getting the address row
		tmp.newDiv.insert({'bottom': new Element('fieldset', {'class': 'row addressRow'})
			.insert({'bottom': new Element('legend').update('Customer') })
			.insert({'bottom': new Element('div', {'class': 'customer'})
				.insert({'bottom': new Element('div').update('Customer: ') })
				.insert({'bottom': new Element('span', {'class': 'custName inlineblock'}).update(tmp.me._getfieldDiv('', tmp.custName)) })
				.insert({'bottom': new Element('span', {'class': 'custEmail inlineblock'}).update(tmp.me._getfieldDiv('', tmp.custEmail)) })
			})
			.insert({'bottom': new Element('div')
				.insert({'bottom': tmp.me._getAddressDiv("Shipping Address: ", tmp.me._order.address.shipping).addClassName('inlineblock') })
				.insert({'bottom': tmp.me._getAddressDiv("Billing Address: ", tmp.me._order.address.billing).addClassName('inlineblock') })
			 })
		});
		
		//getting the parts row
		tmp.productListDiv = new Element('div', {'class': 'productlist dataTable'})
			.insert({'bottom': tmp.me._getProductRow({'product': {'sku': 'SKU', 'name': 'Product Name'}, 
				'unitPrice': 'Unit Price', 'qtyOrdered': 'Qty', 'totalPrice': 'Total Price'}, true).addClassName('header') });
		tmp.me._orderItems.each(function(orderItem) {
			tmp.productListDiv.insert({'bottom': tmp.me._getProductRow(orderItem) });
		});
		tmp.newDiv.insert({'bottom': new Element('fieldset', {'class': 'row productsRow dataTableWrapper'})
			.insert({'bottom': new Element('legend').update('Products') })
			.insert({'bottom': tmp.productListDiv})
		});
		
		//getting the summray row
		tmp.newDiv.insert({'bottom': new Element('fieldset', {'class': 'row summary'})
			.insert({'bottom': new Element('legend').update('Magento Info') })
			.insert({'bottom': new Element('div')
				.insert({'bottom': tmp.me._getfieldDiv('Shipping', tmp.me._order.infos[9][0].value) })
				.insert({'bottom': tmp.me._getfieldDiv('Magento Payment Method', tmp.me._order.infos[6][0].value) })
				.insert({'bottom': tmp.me._getfieldDiv('Last Payment Method', new Element("span", {"id": "lastPaymentMethod"}).update(new Element('img')) ) })
				.insert({'bottom': tmp.me._getfieldDiv('Total Amount', tmp.me.getCurrency(tmp.me._order.totalAmount)).addClassName('totalAmount') })
				.insert({'bottom': tmp.me._getfieldDiv('Total Paid', tmp.me.getCurrency(tmp.me._order.totalPaid)).addClassName('totalPaid') })
				.insert({'bottom': tmp.me._getfieldDiv('Total Due', tmp.me.getCurrency(tmp.me._order.totalDue)).addClassName('totalDue') })
			})
		});
		
		//getting the submit buttons
		tmp.newDiv.insert({'bottom': new Element('fieldset', {'class': 'submitbtns'})
			.insert({'bottom': new Element('span', {'class': 'financeBtns inlineblock'}).update( tmp.me._getFinanceBtns()			) })
			.insert({'bottom': new Element('span', {'class': 'purchasingBtns inlineblock'}).update( tmp.me._getPurchasingBtns() 	) })
			.insert({'bottom': new Element('span', {'class': 'warehouseBtns inlineblock'}).update( tmp.me._getWHBtns()				) })
		});
		
		//getting the EDITABLE shippment row
		if(tmp.me._order.status.id === tmp.me.order_status_picked && tmp.me._editMode.warehouse === true) {
			tmp.newDiv.insert({'bottom': new Element('fieldset', {'class': 'row shipping'})
			.insert({'bottom': new Element('legend').update('Shipping') })
			.insert({'bottom': tmp.me._getShippingRow()  })
			});
		}
		else
		{
			if(tmp.me._order.shippment.length > 0)
			{
				tmp.shippingInfos = tmp.me._order.shippment;
				tmp.newDiv.insert({'bottom': new Element('fieldset', {'class': 'row shipping'})
					.insert({'bottom': new Element('legend').update('Shipping') })
					.insert({'bottom': tmp.me._viewShippingDetails(tmp.shippingInfos) })
				});
			}
		}	
		
		//getting the comments row
		tmp.newDiv.insert({'bottom': new Element('fieldset', {'class': 'row commentsWrapper dataTableWrapper'}) 
			.insert({'bottom': new Element('legend').update('Comments') })
			.insert({'bottom': new Element('div', {'id': 'comments_list', 'class': 'dataTable'}) })
			.insert({'bottom': new Element('div', {'class': 'comments_input_row'})
				.insert({'bottom': tmp.me._getfieldDiv('New Comments:', new Element('div', {'class': 'new_comments_wrapper'})
					.insert({'bottom': new Element('input', {'type': 'text', 'new_comments': 'comments', 'placeholder': 'add more comments to this order'})
						.observe('keydown', function(event) {
							tmp.me.keydown(event, function() {
								$(event.currentTarget).up('.new_comments_wrapper').down('[new_comments=btn]').click();
							});
						})
					})
					.insert({'bottom': new Element('input', {'type': 'button', 'new_comments': 'btn', 'value': 'Add', 'class': 'button'})
						.observe('click', function() {
							tmp.me._addComments(this, 'comments_list');
						})
					})
				) })
			})
		});
		
		//dom insert
		$(tmp.me._resultDivId).update(tmp.newDiv);
		
		//load the comments after
		tmp.me._commentsDiv.resultDivId = 'comments_list';
		tmp.me._getComments(true)
			._loadChosen()
			._bindAllCommentsForOrderItems()
			._getLatestPaymentMethod();
		
		return this;
	}
});
