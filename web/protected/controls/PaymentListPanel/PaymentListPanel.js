var PaymentListPanelJs = new Class.create();

PaymentListPanelJs.prototype = {
	_pageJs : null
	,_order: null
	,_creditNote: null
	,_canEdit: false
	,_panelHTMLID: ''
	
	,initialize : function(_pageJs, _order, _creditNote, _canEdit) {
		this._pageJs = _pageJs;
		this._panelHTMLID = 'PaymentListPanelJs_' + String.fromCharCode(65 + Math.floor(Math.random() * 26)) + Date.now();
		this._order = _order;
		this._creditNote = _creditNote;
		this._canEdit = (_canEdit || this._canEdit);
	}

	,getPaymentListPanel: function() {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = new Element('div', {'class': 'panel panel-default', 'id': tmp.me._panelHTMLID})
			.store('PaymentListPanelJs', tmp.me)
			.insert({'bottom': new Element('div', {'class': 'panel-heading'}).update('Payments: ') })
			.insert({'bottom': new Element('div', {'class': 'panel-body'}).update(tmp.me._pageJs.getLoadingImg()) });
		return tmp.newDiv;
	}
	
	,_getPaymentRow: function(payment, isTitle) {
		var tmp = {};
		tmp.me = this;
		tmp.isTitle = (isTitle === true ? true : false);
		tmp.tag = (tmp.isTitle === true ? 'th' : 'td');
		tmp.newDiv = new Element('tr', {'class': 'item ' + tmp.isTitle === true ? 'payment-item' : ''})
			.store('data', payment)
			.insert({'bottom': new Element(tmp.tag).update(tmp.isTitle === true ? 'Method' : payment.method.name) })
			.insert({'bottom': new Element(tmp.tag).update(tmp.isTitle === true ? 'Value' : tmp.me._pageJs.getCurrency(payment.value) ) })
			.insert({'bottom': new Element(tmp.tag).update(tmp.isTitle === true ? 'Created' : (payment.createdBy.person.fullname + ' @ ' + tmp.me._pageJs.loadUTCTime(payment.created).toLocaleString()) ) })
			.insert({'bottom': new Element(tmp.tag).update(tmp.isTitle === true ? 'Comments' : 
				new Element('a', {'href': 'javascript: void(0);', 'class': 'text-muted popover-comments', 'title': 'comments', 'comments-entity-id': payment.id, 'comments-entity': 'Payment'})
					.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-comment'}) })
			) })
			.insert({'bottom': new Element(tmp.tag).update(tmp.isTitle === true ? '' : 
				new Element('a', {'href': 'javascript: void(0);', 'class': 'text-danger', 'title': 'Delete this payment'})
					.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-remove'}) })
					.observe('click', function() {
						tmp.newConfirmDiv = new Element('div', {'class': 'deletion-confirm'})
							.insert({'bottom': new Element('h4').update('You are about to delete a payment with a value: ' + tmp.me._pageJs.getCurrency(payment.value) + ' from Method: ' + payment.method.name ) })
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
									tmp.me._pageJs.hideModalBox();
								})
							})
						tmp.me._pageJs.showModalBox('Deleting a Payment?', tmp.newConfirmDiv);
						$(tmp.deleteMsgBox).focus();
					})
			) })
		;
		return tmp.newDiv;
	}
	,_getFormGroup: function (label, control) {
		return new Element('div', {'class': 'form-group'})
			.insert({'bottom': label })
			.insert({'bottom': control });
	}
	,_clearCreatePaymentRow: function(selBox, paidMountBox) {
		var tmp = {};
		tmp.me = this;
		tmp.paymentDiv = selBox.up('.new-payment-div');
		tmp.paymentDiv.getElementsBySelector('.after_select_method').each(function(item) { item.remove(); });
		
		if($F(paidMountBox).blank() || tmp.me._pageJs._currencyInputChanged(paidMountBox) !== true) {
			$(paidMountBox).select();
			return;
		}
		//if paid amount is different from total amount
		tmp.paymentDiv
			.insert({'bottom': new Element('div', {"class": 'after_select_method  col-sm-4', 'title': 'Notify Customer?'})
				.insert({'bottom': tmp.me._getFormGroup(
					new Element('label', {'class': 'control-label'}).update('Notify Cust.?'),
					new Element('div').update( new Element('input', {'type': 'checkbox', 'class': 'input-sm', 'payment_field': 'notifyCust', 'checked': true}) )
				) })
			})
			.insert({'bottom': new Element('div', {"class": 'after_select_method control-label col-sm-8'})
				.insert({'bottom': tmp.me._getFormGroup(
						new Element('label', {'class': 'control-label'}).update('Comments:'), 
						tmp.commentsBox = new Element('input', {'type': 'text', 'class': 'after_select_method input-sm form-control', 'payment_field': 'extraComments', 'required': true, 'placeholder': 'Some Comments' }) ) })
			})
			.insert({'bottom': new Element('div', {"class": 'after_select_method control-label col-sm-4'})
				.insert({'bottom': tmp.me._getFormGroup('&nbsp;', new Element('span', {'class': 'btn btn-primary form-control', 'data-loading-text': 'Saving...'}).update('Confirm')
						.observe('click', function(){
							tmp.me._submitPaymentConfirmation(this);
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
	,_getCreatePaymentRow: function(paymentMethods) {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = new Element('tr')
			.insert({'bottom': new Element('td', {'colspan': 4, 'class': 'new-payment-div'})
			.insert({'bottom': new Element('div', {'class': 'col-sm-4'}).update(tmp.me._getFormGroup(
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
		});
		
		paymentMethods.each(function(item) {
			tmp.paymentMethodSelBox.insert({'bottom': new Element('option', {'value': item.id}).update(item.name) });
		});
		return tmp.newDiv;
	}
	
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
						if(!tmp.result || !tmp.result.items || tmp.result.items.size() === 0)
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
							tmp.thead.insert({'top': tmp.me._getCreatePaymentRow(tmp.result.paymentMethods) })
						}
						tmp.result.items.each(function(payment) {
							tmp.listPanel.insert({'bottom': tmp.me._getPaymentRow(payment) });
						});
						if(tmp.result.pagination && tmp.result.pagination.pageNumber < tmp.result.pagination.totalPages) {
							tmp.listPanel.insert({'bottom': new Element('tr', {'class': 'get-more-btn-wrapper'})
								.update(new Element('td', {'colspan': 4}).update(
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