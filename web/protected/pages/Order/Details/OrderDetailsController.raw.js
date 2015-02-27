/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new BPCPageJs(), {
	_order: null //the order object
	,_orderStatuses: [] //the order statuses object
	,_orderStatusID_Shipped: '' //the order statuses object
	,_paymentMethods: []
	,_payments: []
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

	/* *** This function sets all the payment methods to the class property *** */
	,setPaymentMethods: function(paymentMethods) {
		this._paymentMethods = paymentMethods;
		return this;
	}
	/**
	 * Setter for the payments
	 */
	,setPayments: function(payments) {
		this._payments = payments;
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
	 * Ajax: check and submit payment
	 */
	,_submitPaymentConfirmation: function(button) {
		var tmp = {};
		tmp.me = this;
		tmp.data = tmp.me._collectData('payment_field');
		if(tmp.data === null)
			return;
		tmp.me._signRandID(button);
		tmp.me.postAjax(tmp.me.getCallbackId('confirmPayment'), {'payment': tmp.data, 'order': tmp.me._order}, {
			'onLoading': function (sender, param) {
				jQuery('#' + button.id).button('loading');
			}
			,'onSuccess': function (sender, param) {
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					if(tmp.result && tmp.result.items) {
						tmp.me.showModalBox('<h4 class="text-success">Success</h4>', 'Payment saved successfully!');
						window.location = document.URL;
					}
				}
				catch (e) {
					tmp.me.showModalBox('<h4 class="text-danger">Error</h4>', e);
				}
			},
			'onComplete': function (sender, param) {
				jQuery('#' + button.id).button('reset');
			}
		});

		return this;
	}
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
			,parameters: {'entity': 'Order', 'entityId': tmp.me._order.id, 'orderBy': {'created':'desc'}, 'pageNo': tmp.me._commentsDiv.pagination.pageNo, 'pageSize': tmp.me._commentsDiv.pagination.pageSize}
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
		return new Element('tr', {'class': (tmp.isTitle === true ? '' : 'productRow'), 'order_item_id': orderItem.id})
			.store('data', orderItem)
			.insert({'bottom': new Element(tmp.tag, {'class': 'productName'})
				.insert({'bottom': (tmp.isTitle === true ? orderItem.product.name :
						new Element('div')
							.insert({'bottom': new Element('div').update(new Element('a', {'href': '/product/' + orderItem.product.id + '.html', 'target': '_BLANK'}).update(
									new Element('strong', {'class': 'text-info'}).update('SKU: ' + orderItem.product.sku)
							)) })
							.insert({'bottom': new Element('em').update(new Element('small').update(orderItem.itemDescription)) })
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
			.insert({'bottom': new Element('div', {'class': 'panel-heading'}).update('Shipment') });
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
	,_submitOrderStatusChange: function(orderStatusId, comments) {
		var tmp = {};
		tmp.me = this;
		tmp.me.postAjax(tmp.me.getCallbackId('changeOrderStatus'), {'order': tmp.me._order, 'orderStatusId': orderStatusId, 'comments': comments}, {
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
	}
	/**
	 * Ajax: change Order Status
	 */
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
			tmp.me._submitOrderStatusChange($F(selBox), tmp.comments);

			return this;
		}
		$(selBox).replace(tmp.me._getOrderStatus());
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
					.insert({'bottom': new Element('div', {'class': 'col-xs-6'}).update(tmp.me._getAddressDiv("Shipping Address: ", tmp.me._order.address.shipping, 'shipping')) })
					.insert({'bottom': new Element('div', {'class': 'col-xs-6'}).update(tmp.me._getAddressDiv("Billing Address: ", tmp.me._order.address.billing, 'billing')) })
				 })
			});
	}
	/**
	 * Open order print in new Window
	 */
	,_openOrderPrintPage: function(pdf) {
		var tmp = {};
		tmp.me = this;
		tmp.pdf = (pdf || 0);
		tmp.newWindow = window.open('/print/order/' + tmp.me._order.id + '.html?pdf=' + parseInt(tmp.pdf), tmp.me._order.status.name + ' Order ' + tmp.me._order.orderNo, 'width=1300, location=no, scrollbars=yes, menubar=no, status=no, titlebar=no, fullscreen=no, toolbar=no');
		tmp.newWindow.onload = function(){
			tmp.newWindow.document.title = tmp.me._order.status.name + ' Order ' + tmp.me._order.orderNo;
			tmp.newWindow.focus();
			tmp.newWindow.print();
			tmp.newWindow.close();
		}
		return tmp.me;
	}
	/**
	 * Open order delivery docket print page in new Window
	 */
	,_openDocketPrintPage: function(pdf) {
		var tmp = {};
		tmp.me = this;
		tmp.pdf = (pdf || 0);
		tmp.newWindow = window.open('/printdocket/order/' + tmp.me._order.id + '.html?pdf=' + parseInt(tmp.pdf), tmp.me._order.status.name + ' Order ' + tmp.me._order.orderNo, 'width=1300, location=no, scrollbars=yes, menubar=no, status=no, titlebar=no, fullscreen=no, toolbar=no');
		tmp.newWindow.onload = function(){
			tmp.newWindow.document.title = tmp.me._order.status.name + ' Order ' + tmp.me._order.orderNo;
			tmp.newWindow.focus();
			tmp.newWindow.print();
			tmp.newWindow.close();
		}
		return tmp.me;
	}
	,_setOrderType: function(btn) {
		var tmp = {};
		tmp.me = pageJs;
		tmp.btn = btn;
		tmp.me._signRandID(tmp.btn);
		tmp.me.postAjax(tmp.me.getCallbackId('setOrderType'), {'id': tmp.me._order.id, 'type': $(tmp.btn).readAttribute('data-type')}, {
			'onLoading': function() {
				jQuery('#' + tmp.btn.id).button('loading');
			}
			,'onSuccess': function(sender, param) {
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result || !tmp.result.item || !tmp.result.item.id)
						return;
					window.location = document.URL;
				} catch(e) {
					tmp.me.showModalBox('<strong class="text-danger">Error:</strong>', e);
				}
			}
			,'onComplete': function(sender, param) {
				jQuery('#' + tmp.btn.id).button('reset');
			}
		});
		return tmp.me;
	}
	,_sendEmail: function(btn) {
		var tmp = {};
		tmp.me = this;
		tmp.confirmDiv = $(btn).up('.confirm-div');
		tmp.confirmDiv.getElementsBySelector('.msg').each(function(item){ item.remove(); });
		tmp.data = tmp.me._collectFormData(tmp.confirmDiv, 'confirm-email');
		if(tmp.data === null)
			return;
		tmp.data.orderId = tmp.me._order.id;
		tmp.me.postAjax(tmp.me.getCallbackId('sendEmail'), tmp.data, {
			'onLoading': function() {
				tmp.me._signRandID(btn);
				jQuery('#' + btn.id).button('loading');
			}
			,'onSuccess': function(sender, param) {
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result || !tmp.result.item)
						return;
					tmp.confirmDiv.update('<h4 class="text-success">Email Successfully added into the Message Queue. Will be sent within a minute</h4>');
					setTimeout(function() {tmp.me.hideModalBox();}, 2000);
				} catch (e) {
					tmp.confirmDiv.insert({'top': new Element('h4', {'class': 'msg'}).update(new Element('span', {'class': 'label label-danger'}).update(e) ) });
				}
			}
			,'onComplete': function() {
				jQuery('#' + btn.id).button('reset');
			}
		})
		return tmp.me;
	}
	,_showEmailPanel: function(btn) {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = new Element('div', {'class': 'confirm-div'})
			.insert({'bottom': new Element('div')
				.insert({'bottom': tmp.me._getFormGroup('Do you want to send an email to this address:',
						new Element('input', {'value': tmp.me._order.customer.email, 'confirm-email': 'emailAddress', 'required': true, 'placeholder': 'The email to send to. WIll NOT update the customer\'s email with this.'})
					)
				})
			})
			.insert({'bottom': new Element('div')
				.insert({'bottom': new Element('em')
					.insert({'bottom': new Element('small').update('The above email will be used to send the email to. WIll NOT update the customer\'s email with this.') })
				})
			})
			.insert({'bottom': new Element('div')
				.insert({'bottom': tmp.me._getFormGroup('Something you want to say:',
						new Element('textarea', {'confirm-email': 'emailBody'})
				) })
			})
			.insert({'bottom': new Element('div', {'class': 'text-right'})
				.insert({'bottom': new Element('span', {'class': 'btn btn-default pull-left'})
					.update('CANCEL')
					.observe('click', function(){
						tmp.me.hideModalBox();
					})
				})
				.insert({'bottom': new Element('span', {'class': 'btn btn-primary', 'data-loading-text': 'Sending ...'})
					.update('Yes, send this ' + tmp.me._order.type + ' to this email address')
					.observe('click', function(){
						tmp.me._sendEmail(this);
					})
				})
			});
		tmp.me.showModalBox('<strong>Confirm Email Address:</strong>', tmp.newDiv);
		return tmp.me;
	}
	,_getOperationalBtns: function() {
		var tmp = {};
		tmp.me = this;
		tmp.orderDate = tmp.me.loadUTCTime(tmp.me._order.orderDate);
		tmp.newDiv = new Element('div', {'class': 'row'})
			.insert({'bottom': new Element('div', {'class': 'col-sm-8'})
				.insert({'bottom': new Element('div', {'class': 'btn-group btn-group-xs visible-xs visible-md visible-sm visible-lg'})
					.insert({'bottom': new Element('span', {'class': 'btn btn-info'})
						.insert({'bottom': new Element('span', {'class': 'hidden-xs hidden-sm'}).update('Print ') })
						.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-print'}) })
						.observe('click', function() {
							tmp.me._openOrderPrintPage(1);
						})
					})
					.insert({'bottom': new Element('span', {'class': 'btn btn-info dropdown-toggle', 'data-toggle': 'dropdown', 'aria-expanded': "false"})
						.insert({'bottom': new Element('span', {'class': 'caret'}) })
					})
					.insert({'bottom': new Element('ul', {'class': 'dropdown-menu', 'role': 'menu'})
						.insert({'bottom': new Element('li')
							.insert({'bottom': new Element('a', {'href': 'javascript: void(0);'})
								.insert({'bottom': new Element('span').update('Print PDF ') })
								.insert({'bottom': new Element('span', {'class': 'fa fa-file-pdf-o'}) })
								.observe('click', function() {
									tmp.me._openOrderPrintPage(1);
								})
							})
						})
						.insert({'bottom': new Element('li')
							.insert({'bottom': new Element('a', {'href': 'javascript: void(0);'})
								.insert({'bottom': new Element('span').update('Print HTML') })
								.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-print'}) })
								.observe('click', function() {
									tmp.me._openOrderPrintPage(0);
								})
							})
						})
						.insert({'bottom': new Element('li', {'class': 'divider'}) })
						.insert({'bottom': new Element('li')
							.insert({'bottom': new Element('a', {'href': 'javascript: void(0);'})
								.insert({'bottom': new Element('span').update('Print Delivery Docket ') })
								.insert({'bottom': new Element('span', {'class': 'fa fa-file-pdf-o'}) })
								.observe('click', function() {
									tmp.me._openDocketPrintPage(1);
								})
							})
						})
						.insert({'bottom': new Element('li')
							.insert({'bottom': new Element('a', {'href': 'javascript: void(0);'})
								.insert({'bottom': new Element('span').update('Print Delivery Docket ') })
								.insert({'bottom': new Element('span', {'class': 'fa fa-ils'}) })
								.observe('click', function() {
									tmp.me._openDocketPrintPage(0);
								})
							})
						})
					})
				})
				.insert({'bottom': new Element('div', {'class': 'btn-group btn-group-xs visible-xs visible-md visible-sm visible-lg'})
					.setStyle('margin-left: 3px;')
					.insert({'bottom': tmp.me._order.type === 'QUOTE' ? new Element('span', {'class': 'btn btn-warning type-change-btn', 'data-type': 'ORDER'})
						.insert({'bottom': new Element('span', {'class': 'hidden-xs hidden-sm'}).update('ORDER ') })
						.insert({'bottom': new Element('span', {'class': 'fa fa-credit-card'}) })
						.observe('click', function() {
							tmp.me._setOrderType(this);
						})
						: new Element('span', {'class': 'btn btn-success type-change-btn', 'data-type': 'INVOICE'})
							.insert({'bottom': new Element('span', {'class': 'hidden-xs hidden-sm'}).update('INVOICE ') })
							.insert({'bottom': new Element('span', {'class': 'fa fa-credit-card'}) })
							.observe('click', function() {
								tmp.me._setOrderType(this);
							})
					})
				})
				.insert({'bottom': new Element('div', {'class': 'btn-group btn-group-xs visible-xs visible-md visible-sm visible-lg'})
					.setStyle('margin-left: 3px;')
					.insert({'bottom': new Element('span', {'class': 'btn btn-primary'})
						.insert({'bottom': new Element('span', {'class': 'hidden-xs hidden-sm'}).update('Email ') })
						.insert({'bottom': new Element('span', {'class': 'fa fa-envelope'}) })
						.observe('click', function() {
							tmp.me._showEmailPanel(this);
						})
					})
				})
				.insert({'bottom': new Element('div', {'class': 'btn-group btn-group-xs visible-xs visible-md visible-sm visible-lg'})
					.setStyle('margin-left: 3px;')
					.insert({'bottom': new Element('a', {'class': 'btn btn-warning','href': '/order/new.html?cloneorderid=' + tmp.me._order.id, 'target': '_BLANK'}).update('Clone') })
				})
		})
		.insert({'bottom': new Element('div', {'class': 'col-sm-4 text-right'})
			.insert({'bottom': new Element('small').update('Order Date: ') })
			.insert({'bottom': new Element('strong').update( tmp.orderDate.toLocaleDateString() ) })
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
						.insert({'bottom': new Element('div', {'class': 'col-xs-4 text-right'}).update('<strong><small>Delivery Method:</small></strong>') })
						.insert({'bottom': new Element('div', {'class': 'col-xs-8'}).update('<em><small>' + (tmp.me._order.infos['9']? tmp.me._order.infos[9][0].value : '') + '</small></em>') })
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
		if(tmp.me._order.type === 'INVOICE') {
			tmp.newDiv.down('.type-change-btn').writeAttribute('disabled', true).down('span').update('INVOICED ');
		}

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
	,_deletePayment: function(btn, payment) {
		var tmp = {};
		tmp.me = this;
		tmp.confirmPanel = $(btn).up('.deletion-confirm');
		//remove all the msgs
		tmp.confirmPanel.getElementsBySelector('.msg').each(function(item){ item.remove(); });
		tmp.data = tmp.me._collectFormData(tmp.confirmPanel, 'deletion-confirm');
		if(tmp.data === true)
			return;
		tmp.data.paymentId = payment.id;
		tmp.me._signRandID(btn);
		tmp.me.postAjax(tmp.me.getCallbackId('deletePayment'), tmp.data, {
			'onLoading': function() {
				jQuery('#' + btn.id).button('loading');
			}
			,'onSuccess': function(sender, params) {
				try {
					tmp.result = tmp.me.getResp(params, false, true);
					if(!tmp.result || !tmp.result.item)
						return;
					tmp.confirmPanel.update('<h4 class="text-success">Payment delete successfully</h4>Please do NOT click anywhere, page will automaticall refersh.');
					window.location = document.URL;
				} catch (e) {
					$(btn).insert({'before': tmp.me.getAlertBox('Error', e).addClassName('alert-danger').addClassName('msg')});
				}
			}
			,'onComplete': function() {
				jQuery('#' + btn.id).button('reset');
			}
		})
		return tmp.me;
	}
	,_getPaymentListTable: function(payments) {
		var tmp = {};
		tmp.me = this;
		if(payments.size() === 0)
			return '';
		tmp.paymentDiv = new Element('table', {"class": 'table table-hover table-condensed'})
			.insert({'bottom': new Element('thead')
				.insert({'bottom': new Element('th').update('Method') })
				.insert({'bottom': new Element('th').update('Type') })
				.insert({'bottom': new Element('th').update('value') })
				.insert({'bottom': new Element('th').update('Confirmed By') })
				.insert({'bottom': new Element('th').update('Comments') })
				.insert({'bottom': new Element('th').update('&nbsp;') })
			})
			.insert({'bottom': tmp.tbody = new Element('tbody')});
		payments.each(function(payment) {
			tmp.tbody.insert({'bottom':  new Element('tr')
				.insert({'bottom': new Element('td').update(payment.method.name) })
				.insert({'bottom': new Element('td').update(payment.type) })
				.insert({'bottom': new Element('td').update(tmp.me.getCurrency(payment.value)) })
				.insert({'bottom': new Element('td').update(payment.createdBy.person.fullname + ' @ ' + tmp.me.loadUTCTime(payment.created).toLocaleString()) })
				.insert({'bottom': new Element('td')
					.insert({'bottom': new Element('a', {'href': 'javascript: void(0);', 'class': 'text-muted popover-comments', 'title': 'comments', 'comments-entity-id': payment.id, 'comments-entity': 'Payment'})
						.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-comment'}) })
					})
				})
				.insert({'bottom': new Element('td')
					.insert({'bottom': new Element('a', {'href': 'javascript: void(0);', 'class': 'text-danger', 'title': 'Delete this payment'})
						.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-remove'}) })
						.observe('click', function() {
							tmp.newConfirmDiv = new Element('div', {'class': 'deletion-confirm'})
								.insert({'bottom': new Element('h4').update('You are about to delete a payment with a value: ' + tmp.me.getCurrency(payment.value) ) })
								.insert({'bottom': new Element('div', {'class': 'form-group'})
									.insert({'bottom': new Element('label').update('If you want to continue, please provide a reason/comments below and click <strong class="text-danger">"YES, Delete It"</strong> below:') })
									.insert({'bottom': tmp.deleteMsgBox = new Element('input', {'class': 'form-control', 'placeholder': 'The reason of deleting this payment', 'deletion-confirm': 'reason', 'required': true}) })
								})
								.insert({'bottom': new Element('span', {'class': 'btn btn-danger'})
									.update('YES, Delete It')
									.observe('click', function() {
										tmp.me._deletePayment(this, payment);
									})
								})
								.insert({'bottom': new Element('span', {'class': 'btn btn-default pull-right'})
									.update('NO, Cancel Deletion')
									.observe('click', function(){
										tmp.me.hideModalBox();
									})
								})
							tmp.me.showModalBox('Deleting a Payment?', tmp.newConfirmDiv);
							$(tmp.deleteMsgBox).focus();
						})
					})
				})
			})
		});
		return tmp.paymentDiv;
	}
	/**
	 * Get Payment Row
	 */
	,_getPaymentRow: function() {
		var tmp = {};
		tmp.me = this;
		tmp.paymentDiv = new Element('div', {"class": 'panel panel-default payment_row_panel'})
			.insert({'bottom': new Element('div', {"class": 'panel-heading'}).update('Payments') });
		if(tmp.me._editMode.accounting === true) {
			//get payment div
			//clearConfirmPanel function
			tmp.clearConfirmPanel = function(paymentMethodBox, paidMountBox) {
				tmp.paymentDiv.getElementsBySelector('.after_select_method').each(function(item) { item.remove(); });
				if($F(paidMountBox).blank() || tmp.me._currencyInputChanged(paidMountBox) !== true) {
					$(paidMountBox).select();
					return;
				}
				//if paid amount is different from total amount
				tmp.wrapperDiv = tmp.paymentDivBody.down('.row');
				tmp.wrapperDiv.insert({'bottom': new Element('div', {"class": 'after_select_method col-sm-4', 'title': 'Notify Customer?'})
					.insert({'bottom': tmp.me._getFormGroup('Notify Cust.?', new Element('input', {'type': 'checkbox', 'class': 'input-sm', 'payment_field': 'notifyCust', 'checked': true}) ) })
				});
				tmp.wrapperDiv.insert({'bottom': new Element('div', {"class": 'after_select_method col-sm-8'})
					.insert({'bottom': tmp.me._getFormGroup('Comments:', tmp.commentsBox = new Element('input', {'type': 'text', 'class': 'after_select_method input-sm', 'payment_field': 'extraComments', 'required': true, 'placeholder': 'The reason why the paidAmount is different to Total Amount Due: ' + tmp.me.getCurrency(tmp.me._order.totalAmount) }) ) })
				});
				tmp.commentsBox.select();
				tmp.wrapperDiv.insert({'bottom': new Element('div', {"class": 'after_select_method col-sm-4'})
					.insert({'bottom': tmp.me._getFormGroup('&nbsp;', new Element('span', {'class': 'btn btn-primary after_select_method', 'data-loading-text': 'Saving...'}).update('Confirm')
							.observe('click', function(){
								tmp.me._submitPaymentConfirmation(this);
							})
						)
					})
				});
			}
			//getting the Payment method selection box
			tmp.paymentMethodSelBox = new Element('select', {'class': 'input-sm', 'payment_field': 'payment_method_id', 'required': true})
				.insert({'bottom': new Element('option', {'value': ''}).update('Payment Method:')  })
				.observe('change', function() {
					tmp.clearConfirmPanel(this, tmp.paymentDiv.down('[payment_field=paidAmount]'));
				});
			tmp.me._paymentMethods.each(function(item) {
				tmp.paymentMethodSelBox.insert({'bottom': new Element('option', {'value': item.id}).update(item.name) });
			});
			//insert the content
			tmp.paymentDivBody = new Element('div', {"class": 'panel-body panel_row_confirm_panel'})
			.insert({'bottom': new Element('div', {"class": 'row'})
				.insert({'bottom': new Element('div', {"class": 'col-sm-4'})
					.insert({'bottom': tmp.me._getFormGroup('Method:', tmp.paymentMethodSelBox) })
				})
				.insert({'bottom': new Element('div', {"class": 'col-sm-4'})
					.insert({'bottom': tmp.me._getFormGroup('Paid:', new Element('input', {'type': 'text', 'payment_field': 'paidAmount', 'class': 'input-sm', 'required': true, 'validate_currency': true, 'placeholder': 'The paid amount'})
						.observe('change', function() {
							tmp.clearConfirmPanel(tmp.paymentMethodSelBox, this); //clear all after_select_method
						})
					) })
				})
			});
			tmp.paymentDiv.insert({'bottom': tmp.paymentDivBody });
			tmp.paymentDiv.insert({'bottom': tmp.me._getPaymentListTable(tmp.me._payments) });
		}
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
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('div', {'class': 'col-md-8'}).update( tmp.me._getShippmentRow() ) })   //getting the EDITABLE shippment row
				.insert({'bottom': new Element('div', {'class': 'col-md-4'}).update( tmp.me._getPaymentRow() ) })   //getting the payment row
			})
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
		tmp.me._getComments(true);
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
