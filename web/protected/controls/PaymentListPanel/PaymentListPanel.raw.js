var PaymentListPanelJs = new Class.create();
PaymentListPanelJs.prototype = {
	_pageJs : null
	,_order: null
	,_creditNote: null
	,_canEdit: false
	,_panelHTMLID: ''
	,_showNotifyCustBox: true

	,initialize : function(_pageJs, _order, _creditNote, _canEdit, _showNotifyCustBox) {
		this._pageJs = _pageJs;
		this._panelHTMLID = 'PaymentListPanelJs_' + String.fromCharCode(65 + Math.floor(Math.random() * 26)) + Date.now();
		this._order = _order;
		this._creditNote = _creditNote;
		this._showNotifyCustBox = (_showNotifyCustBox || this._showNotifyCustBox);
		this._canEdit = (_canEdit || this._canEdit);
	}
	,setAfterAddFunc: function(_afterAddFunc) {
		var tmp = {};
		tmp.me = this;
		tmp.me._afterAddFunc = _afterAddFunc;
		return tmp.me;
	}
	,setAfterDeleteFunc: function(_afterDeleteFunc) {
		var tmp = {};
		tmp.me = this;
		tmp.me._afterDeleteFunc = _afterDeleteFunc;
		return tmp.me;
	}
	/**
	 * Getting the payment List panel
	 */
	,getPaymentListPanel: function() {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = new Element('div', {'class': 'panel panel-default', 'id': tmp.me._panelHTMLID})
			.store('PaymentListPanelJs', tmp.me)
			.insert({'bottom': new Element('div', {'class': 'panel-heading'}).update('Payments: ') })
			.insert({'bottom': new Element('div', {'class': 'panel-body'}).update(tmp.me._pageJs.getLoadingImg()) });
		return tmp.newDiv;
	}
	/**
	 * Deleting the payment
	 */
	,_deletePayment: function(btn, payment) {
		var tmp = {};
		tmp.me = this;
		tmp.confirmPanel = $(btn).up('.deletion-confirm');
		//remove all the msgs
		tmp.confirmPanel.getElementsBySelector('.msg').each(function(item){ item.remove(); });
		tmp.data = tmp.me._pageJs._collectFormData(tmp.confirmPanel, 'deletion-confirm');
		if(tmp.data === null)
			return;
		tmp.data.paymentId = payment.id;
		tmp.me._pageJs.postAjax(PaymentListPanelJs.callbackIds.delPayment, tmp.data, {
			'onLoading': function() {
				tmp.me._signRandID(btn);
				jQuery('#' + btn.id).button('loading');
			}
			,'onSuccess': function(sender, params) {
				try {
					tmp.result = tmp.me._pageJs.getResp(params, false, true);
					if(!tmp.result || !tmp.result.item)
						return;
					tmp.paymentRow = $(tmp.me._panelHTMLID).down('.payment-item[payment-id=' + tmp.result.item.id + ']');
					if(tmp.paymentRow)
						tmp.paymentRow.remove();
					tmp.confirmPanel.update('<h4 class="text-success">Payment delete successfully.</h4>');
					tmp.confirmPanel.up('.modal-content').down('.modal-header').update('<strong class="text-success">Success</strong>');
					if(typeof(tmp.me._afterDeleteFunc) === 'function')
						tmp.me._afterDeleteFunc(tmp.result.item);
				} catch (e) {
					$(btn).insert({'before': tmp.me._pageJs.getAlertBox('Error', e).addClassName('alert-danger').addClassName('msg')});
				}
			}
			,'onComplete': function() {
				jQuery('#' + btn.id).button('reset');
			}
		})
		return tmp.me;
	}
	,_showComments: function(btn) {
		var tmp = {};
		tmp.me = this;
		tmp.item = $(btn);
		tmp.me._pageJs._signRandID(btn);
		if(!tmp.item.hasClassName('popover-loaded')) {
			jQuery.ajax({
				type: 'GET',
				dataType: "json",
				url: '/ajax/getComments',
				data: {'entity': tmp.item.readAttribute('comments-entity'), 'entityId': tmp.item.readAttribute('comments-entity-Id'), 'type': '' },
				success: function(result) {
					tmp.newDiv = 'N/A';
					if(result.resultData && result.resultData.items && result.resultData.items.length > 0) {
						tmp.newDiv = '<div class="list-group">';
						jQuery.each(result.resultData.items, function(index, comments) {
							tmp.newDiv += '<div class="list-group-item">';
								tmp.newDiv += '<span class="badge">' + comments.type + '</span>';
								tmp.newDiv += '<strong class="list-group-item-heading"><small>' + comments.createdBy.person.fullname + '</small></strong>: ';
								tmp.newDiv += '<p><small><em> @ ' + tmp.me._pageJs.loadUTCTime(comments.created).toLocaleString() + '</em></small><br /><small>' + comments.comments + '</small></p>';
							tmp.newDiv += '</div>';
						})
						tmp.newDiv += '</div>';
					}
					jQuery('#' + btn.id).popover({
						'html': true,
						'placement': 'left',
						'title': '<div class="row" style="min-width: 200px;"><div class="col-xs-10">Comments:</div><div class="col-xs-2"><a class="pull-right" href="javascript:void(0);" onclick="jQuery(' + "'#" + tmp.item.readAttribute('id') + "'" + ').popover(' + "'hide'" + ');"><strong>&times;</strong></a></div></div>',
						'content': tmp.newDiv
					}).popover('show');
					tmp.item.addClassName('popover-loaded');
				}
			});
		}
		return tmp.me;
	}
	/**
	 * Getting the row for each payment
	 */
	,_getPaymentRow: function(payment, isTitle) {
		var tmp = {};
		tmp.me = this;
		tmp.isTitle = (isTitle === true ? true : false);
		tmp.tag = (tmp.isTitle === true ? 'th' : 'td');
		tmp.newDiv = new Element('tr', {'class': 'item ' + tmp.isTitle === true ? '' : 'payment-item'})
			.store('data', payment)
			.insert({'bottom': new Element(tmp.tag).update(tmp.isTitle === true ? 'Date' : moment(tmp.me._pageJs.loadUTCTime(payment.paymentDate)).format('DD/MMM/YY') ) })
			.insert({'bottom': new Element(tmp.tag).update(tmp.isTitle === true ? 'Method' : payment.method.name) })
			.insert({'bottom': new Element(tmp.tag).update(tmp.isTitle === true ? 'Value' : tmp.me._pageJs.getCurrency(payment.value) ) })
			.insert({'bottom': new Element(tmp.tag).update(tmp.isTitle === true ? 'Created' : (payment.createdBy.person.fullname + ' @ ' + moment(tmp.me._pageJs.loadUTCTime(payment.created)).format('DD/MM/YY h:mm a') ) ) })
			.insert({'bottom': new Element(tmp.tag).update(tmp.isTitle === true ? 'Comments' :
				new Element('a', {'href': 'javascript: void(0);', 'class': 'text-muted visible-lg visible-md visible-sm visible-xs', 'title': 'comments', 'comments-entity-id': payment.id, 'comments-entity': 'Payment'})
					.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-comment'}) })
					.observe('click', function() {
						tmp.me._showComments(this);
					})
			) })
			.insert({'bottom': new Element(tmp.tag).update(tmp.isTitle === true ? '' : (tmp.me._canEdit !== true ? '' :
				new Element('a', {'href': 'javascript: void(0);', 'class': 'text-danger', 'title': 'Delete this payment'})
					.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-remove'}) })
					.observe('click', function() {
						tmp.newConfirmDiv = new Element('div', {'class': 'deletion-confirm'})
							.insert({'bottom': new Element('h4').update('You are about to delete a payment with a value: ' + tmp.me._pageJs.getCurrency(payment.value) + ' from Method: ' + payment.method.name ) })
							.insert({'bottom': new Element('div', {'class': 'form-group'})
								.insert({'bottom': new Element('label').update('If you want to continue, please provide a reason/comments below and click <strong class="text-danger">"YES, Delete It"</strong> below:') })
								.insert({'bottom': tmp.deleteMsgBox = new Element('input', {'class': 'form-control', 'placeholder': 'The reason of deleting this payment', 'deletion-confirm': 'reason', 'required': true}) })
							})
							.insert({'bottom': new Element('span', {'class': 'btn btn-danger', 'data-loading-text': '<i class="fa fa-refresh fa-spin"></i>'})
								.update('YES, Delete It')
								.observe('click', function() {
									tmp.me._deletePayment(this, payment);
								})
							})
							.insert({'bottom': new Element('span', {'class': 'btn btn-default pull-right'})
								.update('NO, Cancel Deletion')
								.observe('click', function(){
									tmp.me._pageJs.hideModalBox();
								})
							})
						tmp.me._pageJs.showModalBox('Deleting a Payment?', tmp.newConfirmDiv);
						$(tmp.deleteMsgBox).focus();
					})
			) ) });
		if(payment.id) {
			tmp.newDiv.writeAttribute('payment-id', payment.id);
		}
		return tmp.newDiv;
	}
	/**
	 * Getting the FormGroup row
	 */
	,_getFormGroup: function (label, control) {
		return new Element('div', {'class': 'form-group'}).insert({'bottom': label }).insert({'bottom': control });
	}
	/**
	 * Clear New Payment Row
	 */
	,clearNewPaymentRow: function() {
		var tmp = {};
		tmp.me = this;
		$(tmp.me._panelHTMLID).down('.new-payment-row').replace(tmp.me._getCreatePaymentRow());
		return tmp.me;
	}
	/**
	 * Ajax: check and submit payment
	 */
	,_submitPayment: function(btn) {
		var tmp = {};
		tmp.me = this;
		tmp.newPaymentDiv = $(btn).up('.new-payment-div');
		tmp.newPaymentDiv.getElementsBySelector('.msg').each(function(el) {el.remove();});
		tmp.data = tmp.me._pageJs._collectFormData(tmp.newPaymentDiv, 'payment_field');
		if(tmp.data === null)
			return tmp.me;

		tmp.paymentDateBox = tmp.newPaymentDiv.down('[payment_field="paymentDate"]');
		if(tmp.paymentDateBox) {
			tmp.me._pageJs._signRandID(tmp.paymentDateBox);
			tmp.data.paymentDate = jQuery('#' + tmp.paymentDateBox.id).data('DateTimePicker').date().utc().format();
		}

		tmp.againstEntity = null;
		if (tmp.me._order && tmp.me._order.id)
			tmp.againstEntity = {'entity' : 'Order', 'entityId' : tmp.me._order.id};
		else if (tmp.me._creditNote && tmp.me._creditNote.id)
			tmp.againstEntity = {'entity' : 'CreditNote', 'entityId' : tmp.me._creditNote.id};

		if (tmp.againstEntity !== null) {
			tmp.me._pageJs.postAjax(PaymentListPanelJs.callbackIds.addPayment, {'payment': tmp.data, 'againstEntity': tmp.againstEntity}, {
				'onLoading': function (sender, param) {
					tmp.me._pageJs._signRandID(btn);
					jQuery('#' + btn.id).button('loading');
				}
				,'onSuccess': function (sender, param) {
					try {
						tmp.result = tmp.me._pageJs.getResp(param, false, true);
						if(!tmp.result || !tmp.result.item)
							return;
						tmp.newPaymentDiv.insert({'top': tmp.me._pageJs.getAlertBox('Success: ', 'Payment saved successfully!').addClassName('alert-success').addClassName('msg') });
						$(tmp.me._panelHTMLID).down('.payment-list').insert({'top': tmp.me._getPaymentRow(tmp.result.item) });
						tmp.me.clearNewPaymentRow();
						if(typeof(tmp.me._afterAddFunc) === 'function')
							tmp.me._afterAddFunc(tmp.result.item);
					}
					catch (e) {
						tmp.newPaymentDiv.insert({'top': tmp.me._pageJs.getAlertBox('', e).addClassName('alert-danger').addClassName('msg') });
					}
				},
				'onComplete': function (sender, param) {
					jQuery('#' + btn.id).button('reset');
				}
			});
		}
		return tmp.me;
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
		tmp.inputValue = tmp.me._pageJs.getValueFromCurrency($F(inputBox));
		if(tmp.inputValue.match(/^(-)?\d+(\.\d{1,4})?$/) === null) {
			tmp.me._pageJs._markFormGroupError(inputBox, 'Invalid currency format provided!');
			return false;
		}
		$(inputBox).value = tmp.me._pageJs.getCurrency(tmp.inputValue);
		return true;
	}
	/**
	 * Clearing the new payment row
	 */
	,_clearCreatePaymentRow: function(selBox, paidMountBox) {
		var tmp = {};
		tmp.me = this;
		tmp.paymentDiv = selBox.up('.new-payment-div');
		tmp.paymentDiv.getElementsBySelector('.after_select_method').each(function(item) { item.remove(); });

		if($F(paidMountBox).blank() || tmp.me._currencyInputChanged(paidMountBox) !== true) {
			$(paidMountBox).select();
			return;
		}
		//if paid amount is different from total amount
		tmp.paymentDiv
			.insert({'bottom': tmp.me._showNotifyCustBox !== true ? '' : new Element('div', {"class": 'after_select_method  col-sm-3', 'title': 'Notify Customer?'})
				.insert({'bottom': tmp.me._getFormGroup(
					new Element('label', {'class': 'control-label'}).update('Notify Cust.?'),
					new Element('div', {'class': 'text-center'}).update( new Element('input', {'type': 'checkbox', 'class': 'input-sm', 'payment_field': 'notifyCust', 'checked': true}) )
				) })
			})
			.insert({'bottom': new Element('div', {"class": 'after_select_method control-label col-sm-6'})
				.insert({'bottom': tmp.me._getFormGroup(
						new Element('label', {'class': 'control-label'}).update('Comments:'),
						tmp.commentsBox = new Element('input', {'type': 'text', 'class': 'after_select_method input-sm form-control', 'payment_field': 'extraComments', 'required': true, 'placeholder': 'Some Comments' })
							.observe('keydown', function(event) {
								tmp.me._pageJs.keydown(event, function() {
									tmp.paymentDiv.down('.add-btn').click();
								})
							})
				) })
			})
			.insert({'bottom': new Element('div', {"class": 'after_select_method control-label col-sm-3'})
				.insert({'bottom': tmp.me._getFormGroup('&nbsp;', new Element('span', {'class': 'btn btn-primary form-control add-btn', 'data-loading-text': '<i class="fa fa-refresh fa-spin"></i>'})
						.update('Save')
						.observe('click', function(){
							tmp.me._submitPayment(this);
						})
					)
				})
			});
		tmp.commentsBox.select();
		return tmp.me;
	}
	/**
	 * gettinng the create payment row
	 */
	,_getCreatePaymentRow: function() {
		var tmp = {};
		tmp.me = this;
		if(tmp.me._canEdit !== true)
			return null;
		tmp.newDiv = new Element('tr', {'class': 'new-payment-row'})
			.insert({'bottom': new Element('td', {'colspan': 4})
			.insert({'bottom': new Element('div', {'class': 'new-payment-div'})
				.insert({'bottom': new Element('div', {'class': 'col-sm-3'}).update(tmp.me._getFormGroup(
						new Element('label', {'class': 'control-label'}).update('Date: '),
						new Element('input', {'class': 'input-sm form-control', 'payment_field': 'paymentDate', 'required': true})
				) ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-5'}).update(tmp.me._getFormGroup(
						new Element('label', {'class': 'control-label'}).update('Method: '),
						tmp.paymentMethodSelBox = new Element('select', {'class': 'input-sm form-control', 'payment_field': 'payment_method_id', 'required': true})
							.insert({'bottom': new Element('option', {'value': ''}).update('Payment Method:')  })
							.observe('change', function() {
								tmp.me._clearCreatePaymentRow(this, tmp.newDiv.down('[payment_field=paidAmount]'));
							})
				) ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-4'}).update(tmp.me._getFormGroup(
						new Element('label', {'class': 'control-label'}).update('Amt.: '),
						new Element('input', {'type': 'text', 'payment_field': 'paidAmount', 'class': 'input-sm form-control', 'required': true, 'validate_currency': true, 'placeholder': 'The paid amount'})
							.observe('change', function(){
								tmp.me._clearCreatePaymentRow(tmp.newDiv.down('[payment_field=payment_method_id]'), this);
							})
				) ) })
			})
		});

		tmp.me.paymentMethods.each(function(item) {
			tmp.paymentMethodSelBox.insert({'bottom': new Element('option', {'value': item.id}).update(item.name) });
		});
		return tmp.newDiv;
	}
	/**
	 * showing the payments
	 */
	,_showPayments: function(pageNo, btn) {
		 var tmp = {};
		tmp.me = this;
		tmp.pageNo = (pageNo || 1);
		if(btn) {
			tmp.me._pageJs._signRandID(btn);
		}
		tmp.data = null;
		if (tmp.me._order && tmp.me._order.id) {
			tmp.data = {'entity' : 'Order', 'entityId' : tmp.me._order.id};
		} else if (tmp.me._creditNote && tmp.me._creditNote.id) {
			tmp.data = {'entity' : 'CreditNote', 'entityId' : tmp.me._creditNote.id};
		}
		if (tmp.data !== null) {
			tmp.data.pagination = {'pageNo': tmp.pageNo};
			tmp.loadingImg = tmp.me._pageJs.getLoadingImg();
			tmp.me._pageJs.postAjax(PaymentListPanelJs.callbackIds.getPayments, tmp.data, {
				'onLoading' : function() {
					if(tmp.pageNo === 1) {
						tmp.panelBody = $(tmp.me._panelHTMLID).down('.panel-body');
						if(tmp.panelBody)
							tmp.panelBody.update(tmp.loadingImg);
						else
							$(tmp.me._panelHTMLID).insert({'bottom': new ELement('div', {'class': 'panel-body'}).update(tmp.loadingImg) });
					}
					if(btn) {
						jQuery('#' + btn.id).button('loading');
					}
				}
				,'onSuccess': function (sender, param) {
					try {
						tmp.result = tmp.me._pageJs.getResp(param, false, true);
						if(!tmp.result || !tmp.result.items)
							return;
						tmp.panelBody = $(tmp.me._panelHTMLID).down('.panel-body');
						if(tmp.panelBody)
							tmp.panelBody.remove();
						tmp.thead = $(tmp.me._panelHTMLID).down('thead');
						tmp.listPanel = $(tmp.me._panelHTMLID).down('.payment-list');
						if(!tmp.listPanel || !tmp.thead)
							$(tmp.me._panelHTMLID).insert({'bottom': new Element('table', {'class': 'table table-hover table-condensed'})
								.insert({'bottom': tmp.thead = new Element('thead').update(tmp.me._getPaymentRow({}, true)) })
								.insert({'bottom': tmp.listPanel = new Element('tbody', {'class': 'payment-list'}) })
							});
						if(tmp.pageNo === 1 && tmp.result.paymentMethods) {
							tmp.me.paymentMethods = tmp.result.paymentMethods;
							tmp.thead.insert({'top': tmp.newRow = tmp.me._getCreatePaymentRow() });
							if(tmp.newRow) {
								tmp.paymentDateBox = tmp.newRow.down('[payment_field="paymentDate"]');
								if(tmp.paymentDateBox) {
									tmp.me._pageJs._signRandID(tmp.paymentDateBox);
									jQuery('#' + tmp.paymentDateBox.id).datetimepicker({
										format: 'DD/MM/YYYY'
									});
									jQuery('#' + tmp.paymentDateBox.id).data('DateTimePicker').date(new Date());
								}
							}
						}
						tmp.result.items.each(function(payment) {
							tmp.listPanel.insert({'bottom': tmp.me._getPaymentRow(payment) });
						});
						if(tmp.result.pagination && tmp.result.pagination.pageNumber < tmp.result.pagination.totalPages) {
							tmp.listPanel.insert({'bottom': new Element('tr', {'class': 'get-more-btn-wrapper'})
								.update(new Element('td', {'colspan': 4})
									.update(
										new Element('div', {'class': 'btn btn-primary'})
											.update('Show More Payments')
											.observe('click', function(){
												tmp.me._showPayments(tmp.pageNo * 1 + 1, btn);
											})
									)
							) });
						}

					} catch (e) {
						tmp.panelBody = $(tmp.me._panelHTMLID).down('.panel-body');
						if(tmp.panelBody)
							tmp.panelBody.update(tmp.me._pageJs.getAlertBox('Error: ', e).addClassName('alert-danger'));
						else
							tmp.me._pageJs.showModalBox('<strong class="text-danger">Error</strong>', e);
					}
				}
				,'onComplete': function() {
					tmp.loadingImg.remove();
					if(btn) {
						jQuery('#' + btn.id).button('reset');
					}
				}
			})
		}
	}

	,load: function() {
		 var tmp = {};
		 tmp.me = this;
		 //check whther the pament list panel is loaded.
		 if($(tmp.me._panelHTMLID)) {
			 tmp.me._showPayments();
		 }
		 return tmp.me;
	}
};