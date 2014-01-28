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
	
	,_getHasStockSel: function(changeFunc) {
		return new Element('select', {'class': 'hasStock'})
			.insert({'bottom': new Element('option', {'value': ''}).update('Has Stock?')})
			.insert({'bottom': new Element('option', {'value': '1'}).update('YES')})
			.insert({'bottom': new Element('option', {'value': '0'}).update('NO')})
			.observe('change', changeFunc);
	}
	
	,_getPurchasingCell: function(orderItem) {
		var tmp = {};
		tmp.me = this;
		tmp.func = function() {
			if($F(this) === '0') {
				tmp.etaBox = new Element('input', {'type': 'text', 'placeholder': 'ETA:', 'update_order_item': 'eta', 'id': 'order_item_' + orderItem.id, 'readonly': true});
				$(this).up('.operationDiv').update(new Element('div')
					.insert({'bottom': tmp.me._getfieldDiv('ETA:', tmp.etaBox) })
					.insert({'bottom': tmp.me._getfieldDiv('Comments: ', new Element('input', {'update_order_item': 'comments', 'placeholder': 'The reason'})) })
					.insert({'bottom': new Element('a', {'href': 'javascript: void(0);'}).update('cancel')
						.observe('click', function() {
							$(this).up('.operationDiv').update(tmp.me._getHasStockSel(tmp.func));
						})
					})
				);
				new Prado.WebUI.TDatePicker({'ID':'order_item_' + orderItem.id,'InputMode':"TextBox",'Format':"dd/MMM/yyyy",'FirstDayOfWeek':1,'CalendarStyle':"default",'FromYear':2009,'UpToYear':2024,'PositionMode':"Bottom"});
			} else {
				$(this).up('.operationDiv').insert({'bottom': new Element('input', {'type': 'hidden', 'update_order_item': 'eta', 'value': 'NOW'}) });
			}
		}
		tmp.newDiv = new Element('div', {'class': 'operationDiv'})
			.insert({'bottom': tmp.me._getHasStockSel(tmp.func)	});
		return tmp.newDiv;
	}
	,_getWarehouseCell: function(orderItem) {
		var tmp = {};
		tmp.me = this;
	}
	
	,_getProductRow: function(orderItem, isTitleRow) {
		var tmp = {};
		tmp.me = this;
		tmp.isTitle = (isTitleRow || false);
		tmp.newDiv = new Element('div', {'class': 'row ' + (tmp.isTitle === true ? '' : 'productRow')})
			.insert({'bottom': new Element('span', {'class': 'inlineblock cell sku'}).update(orderItem.product.sku) })
			.insert({'bottom': new Element('span', {'class': 'inlineblock cell productName'}).update(orderItem.product.name) })
			.insert({'bottom': new Element('span', {'class': 'inlineblock cell uprice'}).update(tmp.isTitle === true ? orderItem.unitPrice : tmp.me.getCurrency(orderItem.unitPrice)) })
			.insert({'bottom': new Element('span', {'class': 'inlineblock cell qty'}).update(orderItem.qtyOrdered) })
			.insert({'bottom': new Element('span', {'class': 'inlineblock cell tprice'}).update(tmp.isTitle === true ? orderItem.totalPrice : tmp.me.getCurrency(orderItem.totalPrice)) })
			.insert({'bottom': new Element('span', {'class': 'inlineblock cell purchasing'}).update(tmp.isTitle === true ? 'Purchasing' : tmp.me._getPurchasingCell(orderItem)) })
			.insert({'bottom': new Element('span', {'class': 'inlineblock cell warehouse'}).update(tmp.isTitle === true ? 'Warehouse' : tmp.me._getWarehouseCell(orderItem)) });
		return tmp.newDiv;
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
		console.debug(tmp.me._orderItems);
		tmp.me._orderItems.each(function(orderItem) {
			tmp.productListDiv.insert({'bottom': tmp.me._getProductRow(orderItem) });
		});
		tmp.newDiv.insert({'bottom': new Element('fieldset', {'class': 'row productsRow dataTableWrapper'})
			.insert({'bottom': new Element('legend').update('Products') })
			.insert({'bottom': tmp.productListDiv})
		});
		
		$(tmp.me._resultDivId).update(tmp.newDiv);
		return this;
	}
});
