/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new BPCPageJs(), {
	_order: null //the order object
	,_orderItems: [] //the order items on that order
	,_resultDivId: '' //the result div id
	,_editMode: {'purchasing': false, 'warehouse': false} //the edit mode for purchasing and warehouse
	
	,setEditMode: function(editPurchasing, editWH) {
		this._editMode.purchasing = (editPurchasing || false);
		this._editMode.warehouse = (editWH || false);
		return this;
	}
		
	,setOrder: function(order, orderItems) {
		this._order = order;
		this._orderItems = orderItems;
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
		tmp.hasStock = (orderItem.eta === '' ? '' : (orderItem.eta === '0000-01-01 00:00:00' ? 'Y' : 'N'));
		if(tmp.me._editMode.purchasing === false) {
			tmp.newDiv = new Element('div');
			if(tmp.hasStock === '')
				return tmp.newDiv.update('N/A');
			return tmp.newDiv
				.insert({'bottom': 'hasStock?: ' + tmp.hasStock })
				.insert({'bottom': 'ETA: ' + orderItem.eta })
				.insert({'bottom': tmp.me._showLastestComments() });
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
			return new Element('div').insert({'bottom': tmp.me._showLastestComments(orderItem.comments) });
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
	
	,_getFinanceBtns: function() {
		var tmp = {};
		tmp.me = this;
		return new Element('div', {"class": 'wrapper'})
			.insert({'bottom': tmp.me._getfieldDiv('Paid:',new Element('input')) });
	}
	,_collectData: function(colname, attrName) {
		var tmp = {};
		tmp.me = this;
		tmp.data = {};
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
				tmp.data[tmp.orderItem.id] = {'orderItem': tmp.orderItem};
				tmp.attrData = {};
				
				tmp.fields.each(function(field) {
					tmp.fieldName = field.readAttribute(attrName);
					tmp.value = $F(field);
					if(tmp.value.blank()) {
						field.insert({'before': new Element('div', {'class': 'msgDiv errorMsgDiv'}).update(new Element('div', {'class': 'msg'}).update(tmp.fieldName + ' Required!') ) });
						tmp.hasError = true;
					} else {
						tmp.attrData[tmp.fieldName] = tmp.value;
					}
				})
				tmp.data[tmp.orderItem.id][colname] = tmp.attrData;
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
					tmp.data = tmp.me._collectData('purchasing', 'update_order_item');
					if(tmp.data === null) {
						alert('Error Occurred, pls scroll up to see details!');
						return;
					}
					console.debug(tmp.data);
				})
			});
	}
	,_getWHBtns: function() {
		var tmp = {};
		tmp.me = this;
		if(tmp.me._editMode.warehouse === false)
			return '';
		return new Element('div')
			.insert({'bottom': new Element('span', {'class': 'button'}).update('submit')
				.observe('click', function() {
					tmp.data = tmp.me._collectData('warehouse', 'pick_order_item');
					if(tmp.data === null) {
						alert('Error Occurred, pls scroll up to see details!');
						return;
					}
					console.debug(tmp.data);
				})
			});
	}
	
	,load: function(resultdiv) {
		var tmp = {};
		tmp.me = this;
		tmp.me._resultDivId = resultdiv;
		tmp.newDiv = new Element('div');
		
		//getting the order info row
		tmp.newDiv.insert({'bottom': new Element('fieldset', {'class': 'row orderInfo'})
			.insert({'bottom': new Element('legend').update('info') })
			.insert({'bottom': new Element('span', {'class': 'orderNo inlineblock'}).update(tmp.me._getfieldDiv('Order No.', tmp.me._order.orderNo)) })
			.insert({'bottom': new Element('span', {'class': 'orderDate inlineblock'}).update(tmp.me._getfieldDiv('Order Date:', tmp.me._order.orderDate)) })
			.insert({'bottom': new Element('span', {'class': 'orderStatus inlineblock'}).update(tmp.me._getfieldDiv('Order Status:', tmp.me._order.status.name)) })
		});
		
		//getting the address row
		tmp.newDiv.insert({'bottom': new Element('fieldset', {'class': 'row addressRow'})
			.insert({'bottom': new Element('legend').update('Customer') })
			.insert({'bottom': new Element('div', {'class': 'customer'})
				.insert({'bottom': new Element('div').update('Customer: ') })
				.insert({'bottom': new Element('span', {'class': 'custName inlineblock'}).update(tmp.me._getfieldDiv('', tmp.me._order.infos[1][0].value)) })
				.insert({'bottom': new Element('span', {'class': 'custEmail inlineblock'}).update(tmp.me._getfieldDiv('', tmp.me._order.infos[2][0].value)) })
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
				.insert({'bottom': tmp.me._getfieldDiv('Total Amount', tmp.me.getCurrency(tmp.me._order.totalAmount)).addClassName('totalAmount') })
				.insert({'bottom': tmp.me._getfieldDiv('Total Paid', tmp.me.getCurrency(tmp.me._order.totalPaid)).addClassName('totalPaid') })
				.insert({'bottom': tmp.me._getfieldDiv('Total Due', tmp.me.getCurrency(tmp.me._order.totalDue)).addClassName('totalDue') })
			})
		});
		
		//getting the submit buttons
		tmp.newDiv.insert({'bottom': new Element('fieldset', {'class': 'submitbtns'})
			.insert({'bottom': new Element('span', {'class': 'financeBtns inlineblock'}).update( tmp.me._getFinanceBtns()
			) })
			.insert({'bottom': new Element('span', {'class': 'purchasingBtns inlineblock'}).update( tmp.me._getPurchasingBtns()
			) })
			.insert({'bottom': new Element('span', {'class': 'warehouseBtns inlineblock'}).update( tmp.me._getWHBtns()
			) })
		});
		
		
		$(tmp.me._resultDivId).update(tmp.newDiv);
		return this;
	}
});
