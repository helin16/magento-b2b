/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new BPCPageJs(), {
	_order: null //the order object
	,_orderStatuses: [] //the order statuses object
	,_orderStatusID_Shipped: '' //the order statuses object
	,_orderItems: [] //the order items on that order
	,_resultDivId: '' //the result div id
	,_couriers: []
	,_courier_LocalPickUpId: ''
	,_editMode: {'purchasing': false, 'warehouse': false, 'accounting': false, 'status': false} //the edit mode for purchasing and warehouse
	,_commentsDiv: {'pagination': {'pageSize': 5, 'pageNo': 1}, 'resultDivId': 'comments_result_div', 'types': {'purchasing': '', 'warehouse': ''}} //the pagination for the comments
	,infoType_custName : 1
	,infoType_custEmail : 2
	,orderStatusIds: {'warehouseCanEdit': [], 'purchaseCanEdit': [] , 'canAddShipment': []}

	,setEditMode: function(editPurchasing, editWH, editAcc, editStatus) {
		this._editMode.purchasing = (editPurchasing || false);
		this._editMode.warehouse = (editWH || false);
		this._editMode.accounting = (editAcc || false);
		this._editMode.status = (editStatus || false);
		return this;
	}

	,setCommentType: function (purchasing, warehouse) {
		this._commentsDiv.types.purchasing = purchasing;
		this._commentsDiv.types.warehouse = warehouse;
		return this;
	}

	,setOrderStatusIds: function (purchasing, warehouse, shipment) {
		this.orderStatusIds.purchaseCanEdit = purchasing;
		this.orderStatusIds.warehouseCanEdit = warehouse;
		this.orderStatusIds.canAddShipment = shipment;
		return this;
	}

	,setOrder: function(order, orderItems, orderStatuses, _orderStatusID_Shipped) {
		this._order = order;
		this._orderItems = orderItems;
		this._orderStatuses = orderStatuses;
		this._orderStatusID_Shipped = _orderStatusID_Shipped;
		return this;
	}

	/* *** This function sets all the couriers to the class property *** */
	,setCourier: function(couriers, _courier_LocalPickUpId) {
		this._couriers = couriers;
		this._courier_LocalPickUpId = _courier_LocalPickUpId;
		return this;
	}

	,_updateAddress: function(btn, title) {
		var tmp = {};
		tmp.me = this;
		tmp.inputPane = $(btn).up('.address-div');
		tmp.data = tmp.me._collectFormData(tmp.inputPane, 'address-editable-field');
		tmp.data.title = title;
		tmp.data.orderId = tmp.me._order.id;
		tmp.me.postAjax(tmp.me.getCallbackId('updateAddress'), tmp.data, {
			'onLoading': function() {
				tmp.me._signRandID(btn);
				jQuery('#' + btn.id).button('loading');
			}
			,'onSuccess': function(sender, param) {
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result || !tmp.result.item || !tmp.result.item.id)
						return;
					tmp.inputPane.replace(tmp.me._getAddressDiv(title, tmp.result.item, tmp.data.type));

				} catch (e) {
					tmp.me.showModalBox('<strong class="text-danger">Error When Updating Address</strong>', e);
				}
			}
			,'onComplete': function() {
				jQuery('#' + btn.id).button('reset');
			}
		})
		return tmp.me;
	}
	,_getScanTable: function(item) {
		var tmp = {};
		tmp.me = this;
		tmp.item = item;
		tmp.newDiv = new Element('div', {'class': 'scanTable'});
		for(tmp.i = 0; tmp.i < item.qtyOrdered; tmp.i++) {
			tmp.sellingitem = (tmp.item.sellingitems && tmp.item.sellingitems[tmp.i] && jQuery.isNumeric(tmp.item.sellingitems[tmp.i].id)) ? tmp.item.sellingitems[tmp.i] : {};
			tmp.newDiv.insert({'bottom': new Element('div', {'class': 'col-sm-12'}).update(new Element('div', {'class': 'form-group'}).update( tmp.me._getScanTableRow(tmp.sellingitem, item) ) ) });
		}
		return tmp.newDiv;
	}
	,_getScanTableRow: function(sellingitem, orderItem) {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = new Element('div', {'class': 'serial-no-input-wrapper'}).update(
			tmp.txtBox = new Element('input', {'class': 'form-control input-sm', 'scanned-item': 'serialNo', 'type': 'text', 'placeholder': 'Serial Number:', 'value': ((sellingitem.serialNo) ? sellingitem.serialNo : '')})
				.store('data', sellingitem)
				.store('orderItem', orderItem)
				.observe('change', function() {
					tmp.txtBox = $(this);
					tmp.sellingItemData = tmp.txtBox.retrieve('data');
					tmp.sellingItemData.serialNo = $F(tmp.txtBox);
					if($F(tmp.txtBox).blank()) {
						tmp.sellingItemData.active = false;
					}
					tmp.txtBox.store('data', tmp.sellingItemData);
					tmp.me._updateSerialNumber(tmp.txtBox);
				})
		);
		if(sellingitem && sellingitem.kit && sellingitem.kit.id) {
			tmp.newDiv.addClassName('input-group input-group-sm').insert({'bottom': new Element('a', {'class': 'input-group-addon btn btn-primary', 'href': '/kit/' + sellingitem.kit.id + '.html', 'target': '_BLANK'})
				.insert({'bottom': new Element('i', {'class': 'glyphicon glyphicon-link'}) })
			});
		}
		return tmp.newDiv;
	}
	,_updateSerialNumber: function(txtBox) {
		var tmp = {};
		tmp.me = this;
		tmp.formGroupDiv = $(txtBox).up('.form-group');
		tmp.orderItem = $(txtBox).retrieve('orderItem');
		tmp.sellingitem = $(txtBox).retrieve('data');
		if(!tmp.orderItem || !tmp.orderItem.id)
			return tmp.me;
		tmp.me.postAjax(tmp.me.getCallbackId('updateSerials'), {'orderItemId': tmp.orderItem.id, 'sellingitem': tmp.sellingitem}, {
			'onLoading': function() {
				$(txtBox).writeAttribute('disabled', true).writeAttribute('title', '');
				tmp.formGroupDiv.removeClassName('has-error');
			}
			,'onSuccess': function(sender, param) {
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result || !tmp.result.orderItem)
						return;
					$(txtBox).up('.scanTable').replace(tmp.me._getScanTable(tmp.result.orderItem));
				} catch (e) {
					$(txtBox).writeAttribute('title', 'ERROR: ' + e);
					tmp.formGroupDiv.addClassName('has-error');
					tmp.me.showModalBox('<strong class="text-danger">Error Occurred When Recording The Serial Number: ' + tmp.sellingitem.serialNo + '</strong>', e);
				}
			}
			,'onComplete': function() {
				$(txtBox).writeAttribute('disabled', false);
			}
		})
		return tmp.me;
	}
	,_getAddresEditDiv: function(title, addr, type){
		var tmp = {};
		tmp.me = this;
		return new Element('div', {'class': 'address-div', 'title': 'Double click to edit this address', 'address-editable': true})
			.insert({'bottom': new Element('strong').update(title) })
			.insert({'bottom': new Element('dl', {'class': 'dl-horizontal dl-condensed'})
				.insert({'bottom': new Element('dt')
					.update(new Element('span', {'class': "glyphicon glyphicon-user", 'title': "Customer Name"}) )
				})
				.insert({'bottom': new Element('dd')
					.insert({'bottom': new Element('div')
						.insert({'bottom': new Element('div', {'class' : 'col-sm-6'}).update(
							new Element('input', {'address-editable-field': 'contactName', 'class': 'form-control input-sm', 'placeholder': 'The name of contact person',  'value': addr.contactName ? addr.contactName : ''})
						) })
						.insert({'bottom': new Element('div', {'class' : 'col-sm-6'}).update(
								new Element('input', {'address-editable-field': 'contactNo', 'class': 'form-control input-sm', 'placeholder': 'The contact number of contact person',  'value': addr.contactNo ? addr.contactNo : ''})
						) })
					})
				})
				.insert({'bottom': new Element('dt').update(
					new Element('span', {'class': "glyphicon glyphicon-map-marker", 'title': "Address"})
				) })
				.insert({'bottom': new Element('dd')
					.insert({'bottom': new Element('div')
						.insert({'bottom': new Element('div', {'class': 'street col-sm-12'}).update(
								new Element('input', {'address-editable-field': 'street', 'class': 'form-control input-sm', 'placeholder': 'Street Number and Street name',  'value': addr.street ? addr.street : ''})
						) })
					})
					.insert({'bottom': new Element('div')
						.insert({'bottom': new Element('div', {'class': 'city col-sm-6'}).update(
								new Element('input', {'address-editable-field': 'city', 'class': 'form-control input-sm', 'placeholder': 'City / Suburb',  'value': addr.city ? addr.city : ''})
						) })
						.insert({'bottom':  new Element('div', {'class': 'region col-sm-3'}).update(
								new Element('input', {'address-editable-field': 'region', 'class': 'form-control input-sm', 'placeholder': 'State / Province',  'value': addr.region ? addr.region : ''})
						) })
						.insert({'bottom': new Element('div', {'class': 'postcode col-sm-3'}).update(
								new Element('input', {'address-editable-field': 'postCode', 'class': 'form-control input-sm', 'placeholder': 'PostCode',  'value': addr.postCode ? addr.postCode : ''})
						) })
					})
					.insert({'bottom': new Element('div')
						.insert({'bottom': new Element('div', {'class': 'postcode col-sm-4'}).update(
								new Element('input', {'address-editable-field': 'country', 'class': 'form-control input-sm', 'placeholder': 'Country',  'value': addr.country ? addr.country : ''})
						) })
						.insert({'bottom': new Element('div', {'class': 'col-sm-8'})
							.insert({'bottom': new Element('input', {'type': 'hidden', 'value': addr.id ? addr.id : '', 'address-editable-field': 'id'}) })
							.insert({'bottom': new Element('input', {'type': 'hidden', 'value': type, 'address-editable-field': 'type'}) })
							.insert({'bottom': new Element('div', {'class': 'btn btn-primary btn-sm col-xs-4 pull-right', 'data-loading-text': 'updating...'})
								.update('Update')
								.observe('click', function() {
									tmp.me._updateAddress(this, title);
								})
							})
							.insert({'bottom': new Element('div', {'class': 'btn btn-default btn-sm col-xs-4 pull-right'})
								.update('Cancel')
								.observe('click', function(){
									$(this).up('.address-div').replace(tmp.me._getAddressDiv(title, addr, type));
								})
							})
						})
					})
				})
			});
	}
	/**
	 * Getting the address div
	 */
	,_getAddressDiv: function(title, addr, type) {
		var tmp = {};
		tmp.me = this;
		return new Element('div', {'class': 'address-div', 'title': 'Double click to edit this address'})
			.setStyle('cursor: pointer')
			.insert({'bottom': new Element('strong').update(title) })
			.insert({'bottom': new Element('dl', {'class': 'dl-horizontal dl-condensed'})
				.insert({'bottom': new Element('dt')
					.update(new Element('span', {'class': "glyphicon glyphicon-user", 'title': "Customer Name"}) )
				})
				.insert({'bottom': new Element('dd').update(addr.contactName ? addr.contactName : '') })
				.insert({'bottom': new Element('dt')
					.update(new Element('span', {'class': "glyphicon glyphicon-map-marker", 'title': "Address"}) )
				})
				.insert({'bottom': new Element('dd')
					.insert({'bottom': new Element('div')
						.insert({'bottom': new Element('div', {'class': 'street inlineblock'}).update(addr.street ? addr.street : '') })
						.insert({'bottom': new Element('span', {'class': 'city inlineblock'}).update(addr.city ? addr.city + ' ' : '') })
						.insert({'bottom': new Element('span', {'class': 'region inlineblock'}).update(addr.region ? addr.region + ' ' : '') })
						.insert({'bottom': new Element('span', {'class': 'postcode inlineblock'}).update(addr.postCode ? addr.postCode : '') })
					})
				})
			})
			.observe('dblclick', function() {
				$(this).replace(tmp.me._getAddresEditDiv(title, addr, type));
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
		tmp.data = {};
		tmp.hasError = false;
		$$('[' + attrName + ']').each(function(item) {
			tmp.groupIndexName = groupIndexName ? item.readAttribute(groupIndexName) : null;
			tmp.fieldName = item.readAttribute(attrName);
			if(item.hasAttribute('required') && $F(item).blank()) {
				tmp.me._markFormGroupError(item, 'This is requried');
				tmp.hasError = true;
			}

			tmp.itemValue = item.readAttribute('type') !== 'checkbox' ? $F(item) : $(item).checked;
			if(item.hasAttribute('validate_currency') || item.hasAttribute('validate_number')) {
				if (tmp.me.getValueFromCurrency(tmp.itemValue).match(/^(-)?\d+(\.\d{1,2})?$/) === null) {
					tmp.me._markFormGroupError(item, (item.hasAttribute('validate_currency') ? item.readAttribute('validate_currency') : item.hasAttribute('validate_number')));
					tmp.hasErr = true;
				}
				tmp.value = tmp.me.getValueFromCurrency(tmp.itemValue);
			}

			//getting the data
			if(tmp.groupIndexName !== null && tmp.groupIndexName !== undefined) {
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
					tmp.me.showModalBox('<strong class="text-success">Success</strong>', '<h4>ETA cleared Successfully!</h4>');
					window.location = document.URL;
				} catch (e) {
					tmp.me.showModalBox('<strong class="text-danger">Error</strong>', e);
				}
			}
			,'onComplete': function(sender, param) {}
		});
		return tmp.me;
	}
	/**
	 * Getting the edit cell panel for purchasing
	 *
	 * @param orderItem The orderItem object
	 * @param editPanel The edit panel element
	 *
	 * @return PageJs
	 */
	,_getPurchasingEditCelPanel: function(orderItem, editPanel) {
		var tmp = {};
		tmp.me = this;
		editPanel.insert({'bottom': tmp.me._getfieldDiv('ETA:',
				tmp.etaBox = new Element('input', {'class': 'form-control input-sm datepicker', 'type': 'datetime', 'value': orderItem.eta, 'update_order_item_purchase': 'eta', 'order_item_id': orderItem.id, 'required': true})
			).addClassName('no-stock-div dl-horizontal form-group')
		})
		.insert({'bottom': tmp.me._getfieldDiv('Has Ordered?',
				new Element('input', {'class': 'input-sm', 'type': 'checkbox', 'update_order_item_purchase': 'isOrdered', 'order_item_id': orderItem.id, 'checked': orderItem.isOrdered})
			).addClassName('no-stock-div dl-horizontal form-group')
		})
		.insert({'bottom': tmp.me._getfieldDiv('Comments:',
				new Element('input', {'class': 'form-control input-sm', 'type': 'text', 'update_order_item_purchase': 'comments', 'order_item_id': orderItem.id, 'required': true})
			).addClassName('no-stock-div dl-horizontal form-group')
		});
		tmp.me._signRandID(tmp.etaBox);
		try {
			new Prado.WebUI.TDatePicker({'ID': tmp.etaBox.id, 'InputMode':"TextBox",'Format':"yyyy-MM-dd 17:00:00",'FirstDayOfWeek':1,'CalendarStyle':"default",'FromYear':2009,'UpToYear':2024,'PositionMode':"Bottom", "ClassName": 'datepicker-layer-fixer'});
		} catch(e) {}
		return tmp.me;
	}
	/**
	 * Getting the edit call for purchasing
	 *
	 * @param orderItem The orderItem object
	 *
	 * @return Element
	 */
	,_getPurchasingEditCell: function(orderItem) {
		var tmp = {};
		tmp.me = this;
		tmp.hasStock = (orderItem.eta === '' ? '' : (orderItem.eta === '0001-01-01 00:00:00' ? true : false));
		tmp.isOrdered = (orderItem.isOrdered === false ? false : true);
		if(tmp.me._editMode.purchasing === false) {
			return;
		}
		tmp.editCellPanel = new Element('small', {'class': 'update_order_item_purchase_div update_order_item_div'});
		tmp.editCellPanel.insert({'bottom': tmp.me._getfieldDiv('Has Stock?',
			new Element('select', {'class': 'form-control input-sm', 'update_order_item_purchase': 'hasStock', 'required': true, 'order_item_id': orderItem.id})
				.insert({'bottom': new Element('option', {'value': ' '}).update('Not Checked')})
				.insert({'bottom': new Element('option', {'value': '1'}).update('Yes').writeAttribute('selected', tmp.hasStock === true) })
				.insert({'bottom': new Element('option', {'value': '0'}).update('No').writeAttribute('selected', tmp.hasStock === false)})
				.observe('change', function() {
					tmp.editPanel = $(this).up('.update_order_item_purchase_div');
					tmp.editPanel.getElementsBySelector('.no-stock-div').each(function(item) { item.remove(); });
					if($F(this) === '0') {
						tmp.me._getPurchasingEditCelPanel(orderItem, tmp.editPanel);
					}
				})
			).addClassName('dl-horizontal form-group')
		})
		if(tmp.hasStock === false)
			tmp.me._getPurchasingEditCelPanel(orderItem, tmp.editCellPanel);
		return tmp.editCellPanel;
	}
	/**
	 * Ajax: change isOrdered Flag
	 */
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
					tmp.me.showModalBox('<strong class="text-success">Success</strong>', '<h4>IsOrdered flag changed Successfully!</h4>');
					window.location = document.URL;
				} catch (e) {
					tmp.me.showModalBox('<strong class="text-danger">Error</strong>', e);
				}
			}
			,'onComplete': function(sender, param) {}
		});
		return true;
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
		if(tmp.me._editMode.purchasing === false || tmp.me.orderStatusIds.purchaseCanEdit.indexOf(tmp.me._order.status.id * 1) < 0) {
			tmp.newDiv = new Element('small');
			if(tmp.hasStock === '')
				return tmp.newDiv.update('Not Checked');

			tmp.newDiv.insert({'bottom': new Element('span', {'class': tmp.hasStock ? 'text-success' : 'text-danger'})
				.insert({'bottom': new Element('strong').update('hasStock? ') })
				.insert({'bottom': new Element('span', {'class': 'glyphicon ' + (tmp.hasStock ? 'glyphicon-ok-circle' : 'glyphicon-remove-circle')}) })
				.insert({'bottom': new Element('a', {'href': 'javascript: void(0);', 'class': 'text-muted pull-right popover-comments', 'title': 'comments', 'comments-type': tmp.me._commentsDiv.types.purchasing, 'comments-entity-id': orderItem.id, 'comments-entity': 'OrderItem'})
					.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-comment'}) })
				})
			});
			if(tmp.hasStock === false) {
				tmp.newDiv.insert({'bottom': new Element('span').update('&nbsp;&nbsp;') })
				.insert({'bottom': new Element('span')
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
				.insert({'bottom': new Element('span').update('&nbsp;&nbsp;') })
				.insert({'bottom': new Element('span')
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
	,_getWarehouseEditCell: function(orderItem) {
		var tmp = {};
		tmp.me = this;
		tmp.isPicked = (orderItem.isPicked === true);
		tmp.editCellPanel = new Element('small', {'class': 'update_order_item_warehouse_div update_order_item_div'});
		tmp.editcommentsDiv = tmp.me._getfieldDiv('Comments:',
					new Element('input', {'class': 'form-control input-sm', 'type': 'text', 'update_order_item_warehouse': 'comments', 'order_item_id': orderItem.id, 'required': true})
			).addClassName('no-stock-div dl-horizontal form-group');

		tmp.editCellPanel.insert({'bottom': tmp.me._getfieldDiv('Picked?',
			new Element('select', {'class': 'form-control input-sm', 'update_order_item_warehouse': 'isPicked', 'order_item_id': orderItem.id})
				.insert({'bottom': new Element('option', {'value': ''}).update('Not Picked Yet') })
				.insert({'bottom': new Element('option', {'value': '1'}).update('Yes').writeAttribute('selected', tmp.isPicked === true) })
				.insert({'bottom': new Element('option', {'value': '0'}).update('No').writeAttribute('selected', tmp.isPicked === false)})
				.observe('change', function() {
					tmp.editPanel = $(this).up('.update_order_item_warehouse_div');
					tmp.editPanel.getElementsBySelector('.no-stock-div').each(function(item) { item.remove(); });
					if($F(this) === '0') {
						tmp.editCellPanel.insert({'bottom': tmp.editcommentsDiv});
					}
				})
			).addClassName('dl-horizontal form-group')
		})
		if(tmp.isPicked === false)
			tmp.editCellPanel.insert({'bottom': tmp.editcommentsDiv});
		return tmp.editCellPanel;
	}
	/**
	 * Getting the warehouse cell for an orderItem
	 */
	,_getWarehouseCell: function(orderItem) {
		var tmp = {};
		tmp.me = this;
		if(tmp.me._editMode.warehouse === false || tmp.me.orderStatusIds.warehouseCanEdit.indexOf(tmp.me._order.status.id * 1) < 0) {
			return new Element('small').insert({'bottom': new Element('span', {'class': orderItem.isPicked ? 'text-success' : 'text-danger'})
				.insert({'bottom': new Element('strong').update('Picked? ') })
				.insert({'bottom': new Element('span', {'class': 'glyphicon ' + (orderItem.isPicked ? 'glyphicon-ok-circle' : 'glyphicon-remove-circle')}) })
				.insert({'bottom': new Element('a', {'href': 'javascript: void(0);', 'class': 'text-muted pull-right popover-comments', 'title': 'comments', 'comments-type': tmp.me._commentsDiv.types.warehouse, 'comments-entity-id': orderItem.id, 'comments-entity': 'OrderItem'})
					.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-comment'}) })
				})
			});
		}
		return tmp.me._getWarehouseEditCell(orderItem);
	}
	/**
	 * Getting each product row
	 */
	,_getProductRow: function(orderItem, isTitleRow) {
		var tmp = {};
		tmp.me = this;
		tmp.isTitle = (isTitleRow || false);
		tmp.tag = (tmp.isTitle === true ? 'th' : 'td');
		tmp.newDiv =  new Element('tr', {'class': (tmp.isTitle === true ? '' : 'productRow'), 'order_item_id': orderItem.id})
			.store('data', orderItem)
			.insert({'bottom': new Element(tmp.tag, {'class': 'productName'})
				.insert({'bottom': (tmp.isTitle === true ? orderItem.product.name :
						new Element('div')
							.insert({'bottom': new Element('div').update(new Element('a', {'href': '/product/' + orderItem.product.id + '.html', 'target': '_BLANK'}).update(
									new Element('strong', {'class': 'text-info'}).update('SKU: ' + orderItem.product.sku)
							)) })
							.insert({'bottom': tmp.itemDescriptionEl = new Element('em').update(new Element('small').update(orderItem.itemDescription)) })
				) })
			})
			.insert({'bottom': new Element(tmp.tag, {'class': 'uprice'}).update(tmp.isTitle === true ? orderItem.unitPrice : tmp.me.getCurrency(orderItem.unitPrice)) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'qty'}).update(orderItem.qtyOrdered) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'tprice'}).update(tmp.isTitle === true ? orderItem.totalPrice : tmp.me.getCurrency(orderItem.totalPrice)) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'margin'}).update(tmp.isTitle === true ? orderItem.margin :
				new Element('abbr', {'title': 'Unit Cost When This Order Created: ' + tmp.me.getCurrency(orderItem.unitCost)}).update(tmp.me.getCurrency(orderItem.margin))
			) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'purchasing'}).update(tmp.isTitle === true ? 'Purchasing' : tmp.me._getPurchasingCell(orderItem)) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'warehouse'}).update(tmp.isTitle === true ? 'Warehouse' : tmp.me._getWarehouseCell(orderItem)) });

		if(tmp.itemDescriptionEl) {
			tmp.itemDescriptionEl.insert({'bottom': new Element('div', {'class': 'row product-content-row'})
				.insert({'bottom': new Element('span', {'class': 'col-sm-12 show-tools'})
					.insert({'bottom': new Element('input', {'type': 'checkbox', 'checked': false, 'class': 'show-panel-check'})
						.observe('click', function(){
							tmp.btn = this;
							tmp.panel = $(tmp.btn).up('.product-content-row').down('.serial-no-scan-panel');
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
				.insert({'bottom': new Element('span', {'class': 'col-sm-12 serial-no-scan-panel'}).setStyle('padding-top: 5px; display: none;').update(tmp.me._getScanTable(orderItem)) })
			});
			if(orderItem && orderItem.sellingitems && orderItem.sellingitems.length > 0) {
				tmp.serials = [];
				orderItem.sellingitems.each(function(item){
					tmp.serials.push(item.serialNo);
				});
				tmp.newDiv.store('serials', tmp.serials);
			}
		}


		return tmp.newDiv;
	}
	/**
	 * Ajax: update order item and order
	 */
	,_updateOrderItems: function(btn, items, forType, notifyCustomer) {
		var tmp = {};
		tmp.me = this;
		tmp.notifyCustomer = (notifyCustomer || false);
		tmp.btn = $(btn);
		tmp.me._signRandID(tmp.btn);
		tmp.me.postAjax(tmp.me.getCallbackId('updateOrder'), {'items': items, 'order': tmp.me._order, 'for': forType, 'notifyCustomer': tmp.notifyCustomer}, {
			'onLoading': function(sender, param) {
				jQuery('#' + tmp.btn.id).button('loading');
			},
			'onSuccess': function(sender, param) {
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result)
						return;
					tmp.me.showModalBox('<strong class="text-success">Success</strong>', '<h4>Saved Successfully!</h4>');
					window.location = document.URL;
				} catch (e) {
					tmp.me.showModalBox('<strong class="text-danger">Error</strong>', e);
				}
			},
			'onComplete': function(sender, param) {
				jQuery('#' + tmp.btn.id).button('reset');
			}
		});
		return tmp.me;
	}
	/**
	 * Getting the purchasing submit btns
	 */
	,_getPurchasingBtns: function() {
		var tmp = {};
		tmp.me = this;
		if(tmp.me._editMode.purchasing === false || tmp.me.orderStatusIds.purchaseCanEdit.indexOf(tmp.me._order.status.id * 1) < 0)
			return;
		return new Element('div', {'class': 'row'})
			.insert({'bottom': new Element('span', {'class': 'col-xs-7', 'title': 'Notify Customer?'})
				.insert({'bottom': new Element('label', {'for': 'notify-customer-purchasing'}).update('Notify Cust.?') })
				.insert({'bottom': tmp.notifyCustBox = new Element('input', {'type': 'checkbox', 'id': 'notify-customer-purchasing', 'checked': true}) })
			})
			.insert({'bottom': new Element('span', {'class': 'col-xs-5', 'title': 'Notify Customer?'})
				.insert({'bottom': new Element('span', {'class': 'btn btn-primary', 'data-loading-text': 'Saving...'})
					.update('submit')
					.observe('click', function() {
						tmp.btn = this;
						tmp.me._signRandID(tmp.btn);
						tmp.data = tmp.me._collectData('update_order_item_purchase', 'order_item_id');
						if(tmp.data === null)
							return;
						tmp.me._updateOrderItems(tmp.btn, tmp.data, tmp.me._commentsDiv.types.purchasing, tmp.notifyCustBox.checked);
					})
				})
			});
	}
	/**
	 * Getting the warehouse submit btns
	 */
	,_getWHBtns: function() {
		var tmp = {};
		tmp.me = this;
		if(tmp.me._editMode.warehouse === false || tmp.me.orderStatusIds.warehouseCanEdit.indexOf(tmp.me._order.status.id * 1) < 0)
			return '';

		return new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('span', {'class': 'col-xs-7', 'title': 'Notify Customer?'})
				.insert({'bottom': new Element('label', {'for': 'notify-customer-purchasing'}).update('Notify Cust.?') })
				.insert({'bottom': tmp.notifyCustBox = new Element('input', {'type': 'checkbox', 'id': 'notify-customer-purchasing', 'checked': true}) })
			})
			.insert({'bottom': new Element('span', {'class': 'col-xs-5', 'title': 'Notify Customer?'})
				.insert({'bottom': new Element('span', {'class': 'btn btn-primary', 'data-loading-text': 'Saving...'})
					.update('submit')
					.observe('click', function() {
						tmp.btn = this;
						tmp.data = tmp.me._collectData('update_order_item_warehouse', 'order_item_id');
						if(tmp.data === null)
							return;
						tmp.me._updateOrderItems(tmp.btn, tmp.data, tmp.me._commentsDiv.types.warehouse, tmp.notifyCustBox.checked);
					})
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
			.insert({'bottom': tmp.me._getProductRow({'product': {'sku': 'SKU', 'name': 'Product Name'}, 'unitPrice': 'Unit Price Inc', 'margin': 'Margin', 'qtyOrdered': 'Qty', 'totalPrice': 'Total Price Inc'}, true)
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
	 * Check and save the shipment
	 */
	,_checkAndSubmitShippingOptions: function(button) {
		var tmp = {};
		tmp.me = this;
		tmp.shippingDiv = $(button).up('.save_shipping_panel');
		//clear all error msgs
		tmp.finalShippingDataArray = tmp.me._collectData('save_shipping');
		if(tmp.finalShippingDataArray === null)
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
					tmp.me.showModalBox('Success', 'Shipment saved successfully.')
					window.location = document.URL;
				} catch (e) {
					tmp.newDiv = new Element('div', {'class': 'shipping-reconfim-wrapper'})
						.insert({'bottom': tmp.me.getAlertBox('ERROR: ', e).addClassName('alert-danger') })
						.insert({'bottom': new Element('h4').update('Please check with your Accountant to make sure this cusomter has paid for this order!') })
						.insert({'bottom': new Element('div', {'class': 'form-group'})
							.insert({'bottom': new Element('label').update('If you really want to mark this order to be SHIPPED, please provide a comments and click save:') })
							.insert({'bottom': new Element('input', {'class': 'form-control comments', 'placeholder': 'Comments'}) })
						})
						.insert({'bottom': new Element('div', {'class': 'form-group'})
							.insert({'bottom': new Element('span', {'class': 'btn btn-primary'}).update('Confirm')
								.observe('click',function(){
									tmp.commentsBox = $(this).up('.shipping-reconfim-wrapper').down('.comments');
									tmp.comments = $F(tmp.commentsBox);
									if(tmp.comments.blank()) {
										tmp.me._markFormGroupError(tmp.commentsBox, 'Please provide some reason for this confirmation.');
										return;
									}
									tmp.me._submitOrderStatusChange(tmp.me._orderStatusID_Shipped, tmp.comments);
								})
							})
							.insert({'bottom': new Element('span', {'class': 'btn btn-default pull-right'}).update('Cancel')
								.observe('click',function(){
									tmp.me.hideModalBox();
								})
							})
						});
					tmp.me.showModalBox('Error', tmp.newDiv, false);
				}
			},
			'onComplete': function(sender, param) {
				jQuery('#' + button.id).button('reset');
			}
		});
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
	/**
	 * Getting the shippment row
	 */
	,_getShippmentRow: function() {
		var tmp = {};
		tmp.me = this;
		//display shipping information
		tmp.shipmentDiv = new Element('div', {'class': 'panel panel-default'})
			.insert({'bottom': new Element('div', {'class': 'panel-heading'})
				.update('Shipment')
				.insert({'bottom': !tmp.me._order.status || tmp.me._order.status.id != tmp.me._orderStatusID_Shipped ? '' : new Element('a', {'class': 'btn btn-danger btn-xs pull-right','href': '/rma/new.html?orderid=' + tmp.me._order.id}).update('Create RMA')	})
			});
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
			tmp.shipmentDiv.insert({'bottom': tmp.shipmentListDiv});
		}
		if(tmp.me._editMode.warehouse === false || tmp.me.orderStatusIds.canAddShipment.indexOf(tmp.me._order.status.id * 1) < 0)
			return tmp.shipmentDiv;

		tmp.shipmentDivBody = new Element('div', {'class': 'panel-body save_shipping_panel'})
		.insert({'bottom': new Element('div', {'class': 'row'})
			.insert({'bottom': new Element('div', {'class': 'col-sm-2'})
				.insert({'bottom': tmp.me._getFormGroup('Contact Name:', new Element('input', {'type': 'text', 'save_shipping': 'contactName', 'required': true, 'class': 'input-sm', 'value': tmp.me._order.address.shipping.contactName}) ) })
			})
			.insert({'bottom': new Element('div', {'class': 'col-sm-2'})
				.insert({'bottom': tmp.me._getFormGroup('Contact No:', new Element('input', {'type': 'tel', 'save_shipping': 'contactNo', 'required': true, 'class': 'input-sm', 'value': tmp.me._order.address.shipping.contactNo}) ) })
			})
			.insert({'bottom': new Element('div', {'class': 'col-sm-2 bg-info'})
				.insert({'bottom': tmp.me._getFormGroup('Courier:', tmp.me._getCourierList().writeAttribute('save_shipping', 'courierId').writeAttribute('required', true)
					.observe('change', function() {
						tmp.panel = $(this).up('.save_shipping_panel');
						if($F(this) == tmp.me._courier_LocalPickUpId ) {
							tmp.panel.down('[save_shipping="conNoteNo"]').setValue('Local Pickup').disabled = true;
							tmp.panel.down('[save_shipping="actualShippingCost"]').setValue('0').disabled = true;
						} else {
							tmp.panel.down('[save_shipping="conNoteNo"]').setValue('').disabled = false;
							tmp.panel.down('[save_shipping="actualShippingCost"]').setValue('').disabled = false;
						}
						tmp.panel.down('[save_shipping="noOfCartons"]').select();
					})
				) })
			})
			.insert({'bottom': new Element('div', {'class': 'col-sm-2 bg-info'})
				.insert({'bottom': tmp.me._getFormGroup('Carton(s):',
						new Element('input', {'type': 'number', 'required': true, 'save_shipping': 'noOfCartons', 'class': 'input-sm', 'validate_number': 'Only accept whole number!'})
						.observe('change', function() {
							tmp.inputBox = this;
							tmp.inputValue = $F(tmp.inputBox).strip();
							if(tmp.inputValue.match(/^\d+?$/) === null) {
								tmp.me._markFormGroupError(tmp.inputBox, 'Only accept whole number!');
								return false;
							}
							tmp.inputBox.value = tmp.inputValue;
						})
				) })
			})
			.insert({'bottom': new Element('div', {'class': 'col-sm-2 bg-info'})
				.insert({'bottom': tmp.me._getFormGroup('Con. No:', new Element('input', {'type': 'text', 'required': true, 'save_shipping': 'conNoteNo', 'class': 'input-sm'}) )
					.writeAttribute('title', 'The consignment number of this shipping')
				})
			})
			.insert({'bottom': new Element('div', {'class': 'col-sm-2 bg-info'})
				.insert({'bottom': tmp.me._getFormGroup('Cost($)', new Element('input', {'type': 'text', 'required': true, 'save_shipping': 'actualShippingCost', 'class': 'input-sm', 'validate_currency': 'Invalid currency provided'})
					.observe('change', function() {
						tmp.me._currencyInputChanged(this);
					})
				) .writeAttribute('title', 'The actual cost of this shipping')
				})
			})
		})
		.insert({'bottom': new Element('div', {'class': 'row'})
			.insert({'bottom': new Element('div', {'class': 'col-sm-4'})
				.insert({'bottom': tmp.me._getFormGroup('Street:', new Element('input', {'type': 'text', 'save_shipping': 'street', 'class': 'input-sm', 'value': tmp.me._order.address.shipping.street}) ) })
			})
			.insert({'bottom': new Element('div', {'class': 'col-sm-2'})
				.insert({'bottom': tmp.me._getFormGroup('City:', new Element('input', {'type': 'text', 'save_shipping': 'city', 'class': 'input-sm', 'value': tmp.me._order.address.shipping.city}) ) })
			})
			.insert({'bottom': new Element('div', {'class': 'col-sm-2'})
				.insert({'bottom': tmp.me._getFormGroup('State:', new Element('input', {'type': 'text', 'save_shipping': 'region', 'class': 'input-sm', 'value': tmp.me._order.address.shipping.region}) ) })
			})
			.insert({'bottom': new Element('div', {'class': 'col-sm-2'})
				.insert({'bottom': tmp.me._getFormGroup('Country:', new Element('input', {'type': 'text', 'save_shipping': 'country', 'class': 'input-sm', 'value': tmp.me._order.address.shipping.country}) ) })
			})
			.insert({'bottom': new Element('div', {'class': 'col-sm-2'})
				.insert({'bottom': tmp.me._getFormGroup('Post Code:', new Element('input', {'type': 'text', 'save_shipping': 'postCode', 'class': 'input-sm', 'value': tmp.me._order.address.shipping.postCode}) ) })
			})
		})
		.insert({'bottom': new Element('div', {'class': 'row'})
			.insert({'bottom': new Element('div', {'class': 'col-sm-8'})
				.insert({'bottom': tmp.me._getFormGroup('Delivery Instruction:', new Element('textarea', {'save_shipping': 'deliveryInstructions', 'class': 'input-sm', 'rows': 2}) ) })
			})
			.insert({'bottom': new Element('div', {'class': 'col-sm-2'})
				.insert({'bottom': tmp.me._getFormGroup('Notify Cust?', new Element('input', {'type': 'checkbox', 'save_shipping': 'notifyCust', 'class': 'input-sm', 'checked': true}) ) })
			})
			.insert({'bottom': new Element('div', {'class': 'col-sm-2'})
				.insert({'bottom': tmp.me._getFormGroup('&nbsp;', new Element('span', {'id': 'shipping_save_btn', 'class': 'btn btn-primary', 'data-loading-text': 'Saving...'}).update('Save')
						.observe('click', function() {
							tmp.me._checkAndSubmitShippingOptions(this);
						})
					)
				})
			})
		});
		tmp.shipmentDiv.down('.panel-heading').insert({'after': tmp.shipmentDivBody});
		return tmp.shipmentDiv;
	}
	,_submitOrderStatusChange: function(orderStatusId, comments, succFunc, failedFunc, loadingFunc, completeFunc) {
		var tmp = {};
		tmp.me = this;
		tmp.succFunc = (typeof(succFunc) === 'function' ? succFunc : function() {
			tmp.me.showModalBox('<strong class="text-success">Saved Successfully</strong>', '<h3 class="text-success">Saved Successfully!</h3>');
			window.location = document.URL;
		});
		tmp.failedFunc = (typeof(failedFunc) === 'function' ? failedFunc : function(error) {
			tmp.me.showModalbox('<strong class="text-danger">Error</strong>', error);
		});
		tmp.me.postAjax(tmp.me.getCallbackId('changeOrderStatus'), {'order': tmp.me._order, 'orderStatusId': orderStatusId, 'comments': comments}, {
			'onLoading': function (sender, param) {
				if(typeof(loadingFunc) === 'function')
					loadingFunc();
			}
			,'onSuccess': function (sender, param) {
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result)
						return;
					if(typeof(tmp.succFunc) === 'function')
						tmp.succFunc(tmp.result);
				} catch (e) {
					if(typeof(tmp.succFunc) === 'function')
						tmp.failedFunc(e);
				}
			}
			,'onComplete': function(sender, param) {
				if(typeof(completeFunc) === 'function')
					completeFunc();
			}
		});
	}
	/**
	 * Ajax: change Order Status
	 */
	,_changeOrderStatus: function(selBox) {
		var tmp = {};
		tmp.me = this;
		tmp.newStatusId = $F(selBox);
		tmp.selectedOptText = selBox.options[selBox.selectedIndex].text;
		tmp.newDiv = new Element('div', {'class': 'panel-body'})
			.insert({'bottom': new Element('div', {'class': 'confirm-div'})
				.insert({'bottom': new Element('div', {'class': 'row msg-div'}) })
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('div', {'class': 'form-group'})
						.insert({'bottom': new Element('label', {'class': 'control-label'}).update('Some Reason:') })
						.insert({'bottom': new Element('textarea', {'class': 'form-control', 'confirm-div': 'reason', 'placeholder': 'Some reason please'}) })
					})
				})
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('div', {'class': 'col-xs-6'})
						.insert({'bottom': new Element('div', {'class': 'btn btn-default'})
							.update('Cancel')
							.observe('click', function(){
								$(selBox).replace(tmp.me._getOrderStatus());
								tmp.me.hideModalBox();
							})
						})
					})
					.insert({'bottom': new Element('div', {'class': 'col-xs-6 text-right'})
						.insert({'bottom': new Element('div', {'class': 'btn btn-danger'})
							.update('Update It')
							.observe('click', function(){
								tmp.loadingDiv = new Element('div', {'class': 'loading-div'}).update(tmp.me.getLoadingImg());
								tmp.confirmDiv = $(this).up('.confirm-div');
								tmp.confirmDiv.down('.msg-div').update('');
								tmp.comments = $F(tmp.confirmDiv.down('[confirm-div="reason"]'));
								if(tmp.comments.blank()) {
									tmp.confirmDiv.down('.msg-div').update(tmp.me.getAlertBox('Error: ', 'Some reason is required!').addClassName('alert-danger'));
									return;
								}
								tmp.me._submitOrderStatusChange(tmp.newStatusId, tmp.comments, null, function(e){
									tmp.confirmDiv.down('.msg-div').update(tmp.me.getAlertBox('Error: ', e).addClassName('alert-danger'));
								}, function() {
									tmp.confirmDiv.insert({'after': tmp.loadingDiv}).hide();
								}, function() {
									tmp.loadingDiv.remove();
									tmp.confirmDiv.show();
								});
							})
						})
					})
				 })
			});
		tmp.me.showModalBox('<strong>Please provide some reason for changing this Order to: ' + tmp.selectedOptText + '</strong>', tmp.newDiv);
		return this;
	}
	/**
	 * Getting the order status dropdown list
	 */
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
	/**
	 * Ajax: updating the PO NO
	 */
	,_updatePONo: function(inputBox) {
		var tmp = {};
		tmp.me = this;
		tmp.me.postAjax(tmp.me.getCallbackId('updatePONo'), {'orderId': tmp.me._order.id, 'poNo': $F(inputBox)}, {
			'onLoading': function() {}
			,'onSuccess': function(sende, param) {
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result || !tmp.me.item)
						return;
					tmp.me._order.pONo = tmp.me.item;
				} catch (e) {
					tmp.me.showModalBox('<strong class="text-danger">Error</strong>', e);
				}
			}
		});
		return tmp.me;
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
				.insert({'bottom': new Element('strong').update(tmp.me._order.type + ': ') })
				.insert({'bottom': new Element('strong').update(tmp.me._order.orderNo) })
				.insert({'bottom': new Element('span').update('&nbsp;') })
				.insert({'bottom': new Element('span').update('PO No.:') })
				.insert({'bottom': new Element('input', {'placeholder': 'Optional - PO No. From Customer', 'value': (tmp.me._order.pONo ? tmp.me._order.pONo : '')})
					.observe('change', function() {
						tmp.me._updatePONo(this);
					})
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
						.insert({'bottom': tmp.me._order.customer.contactNo.blank() ? '' : '(' + tmp.me._order.customer.contactNo + ')'})
					})
					.insert({'bottom': new Element('div', {'class': 'col-sm-6'})
						.insert({'bottom': new Element('a', {'href': 'mailto:' + tmp.custEmail}).update(tmp.custEmail) })
					})
				})
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('div', {'class': 'col-xs-6'}).update(tmp.me._getAddressDiv("Billing Address: ", tmp.me._order.address.billing, 'billing')) })
					.insert({'bottom': new Element('div', {'class': 'col-xs-6'}).update(tmp.me._getAddressDiv("Shipping Address: ", tmp.me._order.address.shipping, 'shipping')) })
				 })
			});
	}
	,_getOperationalBtns: function() {
		var tmp = {};
		tmp.me = this;
		tmp.orderDate = tmp.me.loadUTCTime(tmp.me._order.orderDate);
		tmp.newDiv = new Element('div', {'class': 'row'})
			.insert({'bottom': new Element('div', {'class': 'col-sm-8'})
				.insert({'bottom': tmp.me.orderBtnsJs.getBtnsDiv() })
		})
		.insert({'bottom': new Element('div', {'class': 'col-sm-4 text-right'})
			.insert({'bottom': new Element('small').update('Order Date: ') })
			.insert({'bottom': new Element('strong').update( tmp.orderDate.toLocaleDateString() ) })
		});
		return tmp.newDiv;
	}
	,_getShippingMethodEditDiv: function(_shippingMethods) {
		var tmp = {};
		tmp.me = this;
		tmp.shippingMethodSel = new Element('select', {'class': 'form-control input-sm'})
			.observe('change', function(){
				tmp.sel = $(this);
				tmp.me.postAjax(tmp.me.getCallbackId('changeShippingMethod'), {'orderId': tmp.me._order.id, 'shippingMethod': $F(tmp.sel)}, {
					'onSuccess': function(sender, param) {
						try {
							tmp.result = tmp.me.getResp(param, false, true);
							if(!tmp.result || !tmp.result.item)
								return;
							window.location = document.URL;
						} catch (e) {
							tmp.me.showModalBox('<strong class="text-danger">Error</strong>', e);
						}
					}
				})
			});
		tmp.shippingMethod = (tmp.me._order && tmp.me._order.infos['9'] ? tmp.me._order.infos[9][0].value : '');
		tmp.foundMatchedShippingMethod = false;
		_shippingMethods.each(function(method){
			tmp.sameShippingMethod = (method.name.strip() === tmp.shippingMethod.strip());
			tmp.shippingMethodSel.insert({'bottom': new Element('option', {'value': method.name, 'selected': tmp.sameShippingMethod}).update(method.name) });
			if(tmp.foundMatchedShippingMethod === false && tmp.sameShippingMethod === true)
				tmp.foundMatchedShippingMethod = tmp.sameShippingMethod;
		});
		if(!tmp.shippingMethod.blank() && tmp.foundMatchedShippingMethod === false) { //shipping method from online, no matched shipping method
			tmp.shippingMethodSel.insert({'bottom': new Element('option', {'value': tmp.shippingMethod, 'selected': true}).update(tmp.shippingMethod.stripTags()) });
		}
		return tmp.shippingMethodSel;
	}
	/**
	 * Getting the display of the shipping method
	 */
	,_getShippingMethodDisplayDiv: function() {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = new Element('div')
			.setStyle('cursor: pointer')
			.insert({'bottom': new Element('em', {'title': 'Double click to change'})
				.insert({'bottom': new Element('small').update(tmp.me._order.infos['9']? tmp.me._order.infos[9][0].value : '') })
			})
			.observe('dblclick', function() {
				tmp.div = $(this);
				tmp.loadingDiv = new Element('div').update(tmp.me.getLoadingImg().removeClassName('fa-5x'));
				tmp.ajax = new Ajax.Request('/ajax/getAll', {
					method: 'get'
					,parameters: {'entityName': 'Courier','orderBy': {'name':'asc'}}
					,onLoading: function() {tmp.div.update(tmp.loadingDiv);}
					,onSuccess: function(transport) {
						try {
							tmp.result = tmp.me.getResp(transport.responseText, false, true);
							if(!tmp.result || !tmp.result.items)
								return;
							tmp.div.update(tmp.me._getShippingMethodEditDiv(tmp.result.items));
						} catch (e) {
							tmp.me.showModalBox('<strong class="text-danger">Error</strong>', e);
							tmp.div.replace(tmp.me._getShippingMethodDisplayDiv());
						}
					}
					,onComplete: function() {
						tmp.loadingDiv.remove();
					}
				});
			});
		return tmp.newDiv;
	}
	/**
	 * Getting the order information panel
	 */
	,_getInfoPanel: function() {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv =  new Element('div', {'class': 'panel panel-default order-info-div'})
			.insert({'bottom': new Element('div', {'class': 'panel-heading'})
				.setStyle('padding: 4px 5px;display: block !important;')
				.insert({'bottom': tmp.me._getOperationalBtns() })
			})
			.insert({'bottom': new Element('div', {'class': 'list-group'})
				.insert({'bottom': new Element('a', {'class': 'list-group-item'}).setStyle('padding: 3px 0px;')
					.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('div', {'class': 'col-xs-4 text-right'}).update('<strong>Inv. #:</strong>') })
					.insert({'bottom': new Element('div', {'class': 'col-xs-8'}).update('<em><small>' + (tmp.me._order.invNo) + '</small></em>') })
					})
				})
				.insert({'bottom': new Element('a', {'class': 'list-group-item'}).setStyle('padding: 3px 0px;')
					.insert({'bottom': new Element('div', {'class': 'row'})
						.insert({'bottom': new Element('div', {'class': 'col-xs-4 text-right'}).update('<strong><small>Delivery Method:</small></strong>') })
						.insert({'bottom': new Element('div', {'class': 'col-xs-8'}).update(tmp.me._getShippingMethodDisplayDiv()) })
					})
				})
				.insert({'bottom': new Element('a', {'class': 'list-group-item'}).setStyle('padding: 3px 0px;')
					.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('div', {'class': 'col-xs-4 text-right'}).update('<strong><small>Shipping Cost:</small></strong>') })
					.insert({'bottom': new Element('div', {'class': 'col-xs-8'}).update('<em><small>' + tmp.me.getCurrency(tmp.me.getValueFromCurrency(tmp.me._order.infos['14']? tmp.me._order.infos[14][0].value : '')) + '</small></em>') })
					})
				})
				.insert({'bottom': new Element('a', {'class': 'list-group-item'}).setStyle('padding: 3px 0px;')
					.insert({'bottom': new Element('div', {'class': 'row'})
						.insert({'bottom': new Element('div', {'class': 'col-xs-4 text-right'}).update('<strong><small>Payment Method:</small></strong>') })
						.insert({'bottom': new Element('div', {'class': 'col-xs-8'}).update(tmp.me._order.infos['6'] ? tmp.me._order.infos[6][0].value : '') })
					})
				})
				.insert({'bottom': new Element('a', {'class': 'list-group-item'}).setStyle('padding: 3px 0px;')
					.insert({'bottom': new Element('div', {'class': 'row'})
						.insert({'bottom': new Element('div', {'class': 'col-xs-4 text-right'}).update('<strong><small>Total Amount Incl. GST:</small></strong>') })
						.insert({'bottom': new Element('div', {'class': 'col-xs-8'}).update( tmp.me.getCurrency(tmp.me._order.totalAmount) ) })
					})
				})
				.insert({'bottom': new Element('a', {'class': 'list-group-item'}).setStyle('padding: 3px 0px;')
					.insert({'bottom': new Element('div', {'class': 'row'})
						.insert({'bottom': new Element('div', {'class': 'col-xs-4 text-right'}).update('<strong><small>Total Credit Value:</small></strong>') })
						.insert({'bottom': new Element('div', {'class': 'col-xs-8'}).update(
								new Element('a', {'class': 'text-danger', 'href': '/creditnote.html?orderid='+ tmp.me._order.id, 'target': '_BLANK'}).update(
									tmp.me.getCurrency(tmp.me._order.totalCreditNoteValue)
								)
							)
						})
					})
				})
				.insert({'bottom': new Element('a', {'class': 'list-group-item'}).setStyle('padding: 3px 0px;')
					.insert({'bottom': new Element('div', {'class': 'row'})
						.insert({'bottom': new Element('div', {'class': 'col-xs-4 text-right'}).update('<strong><small>Total Paid Incl. GST:</small></strong>') })
						.insert({'bottom': new Element('div', {'class': 'col-xs-8'}).update( tmp.me.getCurrency(tmp.me._order.totalPaid) ) })
					})
				})
				.insert({'bottom': new Element('a', {'class': 'list-group-item'}).setStyle('padding: 3px 0px;')
					.insert({'bottom': new Element('div', {'class': 'row'})
						.insert({'bottom': new Element('div', {'class': 'col-xs-4 text-right'}).update('<strong><small>Total Due Incl. GST:</small></strong>') })
						.insert({'bottom': new Element('div', {'class': 'col-xs-8'}).update( tmp.me.getCurrency(tmp.me._order.totalDue) ) })
					})
				})
				.insert({'bottom': new Element('a', {'class': 'list-group-item'}).setStyle('padding: 3px 0px;')
					.insert({'bottom': new Element('div', {'class': 'row'})
						.insert({'bottom': new Element('div', {'class': 'col-xs-4 text-right'}).update('<strong><small>Order Margin:</small></strong>') })
						.insert({'bottom': new Element('div', {'class': 'col-xs-8'}).update( tmp.me.getCurrency(tmp.me._order.margin) ) })
					})
				})
			});
		return tmp.newDiv;
	}
	,_getChildrenOrderListPanel: function() {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = new Element('div')
			.insert({'bottom': tmp.list = new Element('ul', {'class': 'list-inline'})
				.insert({'bottom': new Element('li').update('<strong>Orders Cloned from this order:</strong>') })
			});
		tmp.me._order.childrenOrders.each(function(order){
			tmp.list.insert({'bottom': new Element('li')
				.insert({'bottom': new Element('a', {'class': 'btn btn-primary btn-xs', 'href': '/orderdetails/' + order.id + '.html', 'target': '_BLANK'}).update(order.orderNo) })
			});
		})
		return tmp.newDiv;
	}
	,_getCreditNoteListPanel: function() {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = new Element('div')
		.insert({'bottom': tmp.list = new Element('ul', {'class': 'list-inline'})
		.insert({'bottom': new Element('li').update('<strong>CreditNotes from this order:</strong>') })
		});
		tmp.me._order.creditNotes.each(function(creditNote){
			tmp.list.insert({'bottom': new Element('li')
				.insert({'bottom': new Element('a', {'class': 'btn btn-warning btn-xs', 'href': '/creditnote/' + creditNote.id + '.html', 'target': '_BLANK'}).update(creditNote.creditNoteNo) })
			});
		})
		return tmp.newDiv;
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
		if(tmp.inputValue.match(/^(-)?\d+(\.\d{1,4})?$/) === null) {
			tmp.me._markFormGroupError(inputBox, 'Invalid currency format provided!');
			return false;
		}
		$(inputBox).value = tmp.me.getCurrency(tmp.inputValue);
		return true;
	}
	/**
	 * initialising the doms
	 */
	,init: function(resultdiv) {
		var tmp = {};
		tmp.me = this;
		tmp.me.orderBtnsJs = new OrderBtnsJs(tmp.me, tmp.me._order);
		tmp.me.paymentListPanelJs = new PaymentListPanelJs(tmp.me, tmp.me._order, undefined, tmp.me._editMode.accounting, true);
		tmp.me._resultDivId = resultdiv;
		$(tmp.me._resultDivId).update(
			new Element('div')
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('div', {'class': 'col-sm-8'})
						.insert({'bottom': tmp.me._getAddressPanel() }) 	//getting the address row
						.insert({'bottom': tmp.me._getChildrenOrderListPanel() }) 	//getting children list
						.insert({'bottom': tmp.me._getCreditNoteListPanel() }) 	//getting creditNote list
						.insert({'bottom': new Element('div', {'class': 'taskListPanel'}) })	//task listing panel
					})
					.insert({'bottom': new Element('div', {'class': 'col-sm-4'})
						.insert({'bottom': tmp.me._getInfoPanel() })    	//getting the order info row
					})
				})
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('div', {'class': 'col-sm-12 memo-panel'})
						.store('lastMemoJs', (tmp.lastMemoJs = new LastMemoPanelJs(tmp.me, 'Order', tmp.me._order && tmp.me._order.id ? tmp.me._order.id : '', true)))
						.update(tmp.lastMemoJs ? tmp.lastMemoJs._getPanel() : '')
					})
				})
				.insert({'bottom': tmp.me._getPartsTable() })   	//getting the parts row
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('div', {'class': 'col-md-8'}).update( tmp.me._getShippmentRow() ) })   //getting the EDITABLE shippment row
					.insert({'bottom': new Element('div', {'class': 'col-md-4'}).update( tmp.paymentsPanel = tmp.me.paymentListPanelJs.getPaymentListPanel() ) })   //getting the payment row
				})
				.insert({'bottom': new Element('div', {'class': 'comments-list-div'}) }) //getting the comments row
		);
		$(tmp.me._resultDivId).store('CommentsDivJs', new CommentsDivJs(tmp.me, 'Order', tmp.me._order.id)._setDisplayDivId($(tmp.me._resultDivId).down('.comments-list-div')).render() );
		$(tmp.me._resultDivId).down('.memo-panel').retrieve('lastMemoJs').load();
		if(tmp.paymentsPanel.down('.panel-heading'))
			tmp.paymentsPanel.down('.panel-heading').insert({'bottom': tmp.me._order.status.id == 2 ? '' : new Element('a', {'class': 'btn btn-danger btn-xs pull-right', 'href': '/creditnote/new.html?blanklayout=1&orderid=' + tmp.me._order.id}).update('Create CreditNote')})
		tmp.me.paymentListPanelJs
			.setAfterAddFunc(function() { window.location = document.URL; })
			.setAfterDeleteFunc(function() { window.location = document.URL; })
			.load();
		tmp.taskListPanel = $(tmp.me._resultDivId).down('.taskListPanel');
		if(tmp.taskListPanel) {
			tmp.taskListPanel.store('taskListPanelJs', tmp.taskStatusListPanelJs = new TaskStatusListPanelJs(tmp.me))
				.update(tmp.taskStatusListPanelJs.getDiv('Order', tmp.me._order))
			tmp.taskStatusListPanelJs.setOpenInFancyBox(tmp.me.getUrlParam('blanklayout') !== '1').render();
		}
		return this;
	}
	/**
	 * load the js
	 */
	,load: function() {
		var tmp = {};
		tmp.me = this;
		$$('.datepicker').each(function(item) {
			new Prado.WebUI.TDatePicker({'ID': item.id, 'InputMode':"TextBox",'Format':"yyyy-MM-dd 17:00:00",'FirstDayOfWeek':1,'CalendarStyle':"default",'FromYear':2009,'UpToYear':2024,'PositionMode':"Bottom", "ClassName": 'datepicker-layer-fixer'});
		});
		jQuery('.popover-comments').click(function(){
			tmp.me._signRandID($(this));
			tmp.item = jQuery(this).removeAttr('title').addClass('visible-lg visible-md visible-sm visible-xs');
			if(!tmp.item.hasClass('popover-loaded')) {
				jQuery.ajax({
					type: 'GET',
					dataType: "json",
					url: '/ajax/getComments',
					data: {'entity': tmp.item.attr('comments-entity'), 'entityId': tmp.item.attr('comments-entity-Id'), 'type': tmp.item.attr('comments-type') },
					success: function(result) {
						tmp.newDiv = 'N/A';
						if(result.resultData && result.resultData.items && result.resultData.items.length > 0) {
							tmp.newDiv = '<div class="list-group">';
							jQuery.each(result.resultData.items, function(index, comments) {
								tmp.newDiv += '<div class="list-group-item">';
									tmp.newDiv += '<span class="badge">' + comments.type + '</span>';
									tmp.newDiv += '<strong class="list-group-item-heading"><small>' + comments.createdBy.person.fullname + '</small></strong>: ';
									tmp.newDiv += '<p><small><em> @ ' + tmp.me.loadUTCTime(comments.created).toLocaleString() + '</em></small><br /><small>' + comments.comments + '</small></p>';
								tmp.newDiv += '</div>';
							})
							tmp.newDiv += '</div>';
						}
						tmp.item.popover({
							'html': true,
							'placement': 'left',
							'title': '<div class="row" style="min-width: 200px;"><div class="col-xs-10">Comments:</div><div class="col-xs-2"><a class="pull-right" href="javascript:void(0);" onclick="jQuery(' + "'#" + tmp.item.attr('id') + "'" + ').popover(' + "'hide'" + ');"><strong>&times;</strong></a></div></div>',
							'content': tmp.newDiv
						}).popover('show');
						tmp.item.addClass('popover-loaded');
					}
				})
			}
		});
		if(tmp.me._order.type === 'QUOTE')
			jQuery('.panel').removeClass('panel-default').addClass('panel-warning');
		if(tmp.me._order.type === 'ORDER')
			jQuery('.panel').removeClass('panel-default').addClass('panel-success');
		return tmp.me;
	}
});
