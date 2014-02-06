/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new BPCPageJs(), {
	_order: null //the order object
	,_orderStatuses: [] //the order statuses object
	,_orderItems: [] //the order items on that order
	,_resultDivId: '' //the result div id
	,_editMode: {'purchasing': false, 'warehouse': false, 'accounting': false, 'status': false} //the edit mode for purchasing and warehouse
	,_commentsDiv: {'pagination': {'pageSize': 10, 'pageNo': 1}, 'resultDivId': '', 'type': ''} //the pagination for the comments
	,infoType_custName : 1
	,infoType_custEmail : 2
	
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
		return new Element('div', {'class': 'comments'})
			.insert({'bottom': new Element('div', {'class': 'lastest'}).update(!comments ? 'N/A' : comments[0]) });
	}
	
	,_getPurchasingCell: function(orderItem) {
		var tmp = {};
		tmp.me = this;
		tmp.hasStock = (orderItem.eta === '' ? '' : (orderItem.eta === '0001-01-01 00:00:00' ? 'Y' : 'N'));
		if(tmp.me._editMode.purchasing === false) {
			tmp.newDiv = new Element('div', {'class': 'order_item_details'});
			if(tmp.hasStock === '')
				return tmp.newDiv.update('N/A');
			return tmp.newDiv
				.insert({'bottom': tmp.me._getfieldDiv('hasStock?: ', tmp.hasStock) })
				.insert({'bottom': tmp.me._getfieldDiv('ETA: ', orderItem.eta) })
				.insert({'bottom': tmp.me._getfieldDiv('Comments: ', tmp.me._showLastestComments()) });
		}
		tmp.getEditDiv = function(hasStock, eta) {
			tmp.etaBox = new Element('input', {'type': 'text', 'placeholder': 'ETA:', 'update_order_item': 'eta', 'id': 'order_item_' + orderItem.id, 'readonly': true, 'value': eta ? eta : ''});
			return new Element('div')
				.insert({'bottom': tmp.me._getfieldDiv('ETA:', tmp.etaBox) })
				.insert({'bottom': tmp.me._getfieldDiv('Comments: ', new Element('input', {'update_order_item': 'comments', 'placeholder': 'The reason'})) })
				.insert({'bottom': new Element('a', {'href': 'javascript: void(0);'}).update('cancel')
					.observe('click', function() {
						$(this).up('.operationDiv').update(tmp.me._getHasStockSel('Has Stock?', hasStock, tmp.func));
					})
				})
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
				$(this).up('.operationDiv').insert({'bottom': new Element('input', {'type': 'hidden', 'update_order_item': 'eta', 'value': '0001-01-01 00:00:00'}) });
			}
		};
		if(tmp.hasStock === 'N') {
			return new Element('div', {'class': 'operationDiv'}).update(tmp.getEditDiv('', orderItem.eta));
		}
		return tmp.me._getHasStockSel('Has Stock?', tmp.hasStock, tmp.func).wrap(new Element('div', {'class': 'operationDiv'}));
	}
	
	,_getWarehouseCell: function(orderItem) {
		var tmp = {};
		tmp.me = this;
		if(tmp.me._editMode.warehouse === false) {
			return new Element('div', {'class': 'order_item_details'})
				.insert({'bottom': tmp.me._getfieldDiv('Picked?: ', orderItem.isPicked ? 'Y' : 'N') })
				.insert({'bottom': tmp.me._getfieldDiv('Comments: ', tmp.me._showLastestComments()) });
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
		tmp.newDiv = new Element('div', {'class': 'row ' + (tmp.isTitle === true ? '' : 'productRow')}).store('data', orderItem)
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
		tmp.confDiv = $(button).up('#extraConfDiv');
		tmp.extraComment = '';
		
		$(button).up('.wrapper').getElementsBySelector('.msgDiv').each(function(div) {
			div.remove();
		});
		
		if(tmp.paidAmount === null || tmp.paidAmount.blank() || isNaN(tmp.paidAmount))
		{
			alert('Paid Amount is NOT valid');
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
		
		tmp.me.postAjax(tmp.me.getCallbackId('confirmPayment'), {'paidAmt': tmp.paidAmount, 'amtDiff': tmp.amtDiff, 'extraComment': tmp.extraComment, 'order': tmp.me._order}, {
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
		
		if(tmp.paidAmount === null || tmp.paidAmount.blank() || isNaN(tmp.paidAmount))
		{
			$(txtBox).insert({'before': new Element('div', {'class': 'msgDiv errorMsgDiv'}).update(new Element('div', {'class': 'msg'}).update('Paid Amount is NOT valid') ) });
			return;
		}
		
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
	
	,_getFinanceBtns: function() {
		var tmp = {};
		tmp.me = this;
		if(tmp.me._editMode.accounting !== true) {
			return '';
		}
		return new Element('div', {"class": 'wrapper'})
			.insert({'bottom': tmp.me._getfieldDiv('Paid:',new Element('input', {'type': 'text', 'id': 'paidAmount'})
				.observe('change', function(){
					tmp.me._checkPaidAmount(this);
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
					tmp.value = $F(field);
					if(tmp.value.blank()) {
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
							return;
						
						console.debug(tmp.finalOrderItemArray);
						
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
			,'onComplete': function (sender, param) {
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					$(commentsResultDivId).down('.header').insert({'after': tmp.me._getCommentsRow(tmp.result) });
					tmp.commentsBox.setValue('');
				} catch (e) {
					console.error(e);
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
	
	,_getShippingRow: function() {
		var tmp = {};
		tmp.me = this;
		return  new Element('div', {'class': 'shippingWrapper'})
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': tmp.me._getfieldDiv('Courier:', '') })
			});
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
			.insert({'bottom': new Element('legend').update('Summary') })
			.insert({'bottom': new Element('div')
				.insert({'bottom': tmp.me._getfieldDiv('Shipping', tmp.me._order.infos[9][0].value) })
				.insert({'bottom': tmp.me._getfieldDiv('Payment Method', tmp.me._order.infos[6][0].value) })
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
		
		//getting the shippment row
		tmp.newDiv.insert({'bottom': new Element('fieldset', {'class': 'shipping'})
			.insert({'bottom': tmp.me._getShippingRow()  })
		});
		
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
		tmp.me._getComments(true);
		return this;
	}
});
