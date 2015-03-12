/**
 * The display order print, email, clone for a order
 */
var OrderBtnsJs = new Class.create();
OrderBtnsJs.prototype = {
	SEND_EMAIL_CALLBACK_ID: ''
	,_pageJs: null	

	//constructor
	,initialize : function(_pageJs, _order) {
		this._pageJs = _pageJs;
		this._order = _order;
	}
	/**
	 * Open order print in new Window
	 */
	,openOrderPrintPage: function(pdf) {
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
	,openDocketPrintPage: function(pdf) {
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
	,_sendEmail: function(btn) {
		var tmp = {};
		tmp.me = this;
		tmp.confirmDiv = $(btn).up('.confirm-div');
		tmp.confirmDiv.getElementsBySelector('.msg').each(function(item){ item.remove(); });
		tmp.data = tmp.me._pageJs._collectFormData(tmp.confirmDiv, 'confirm-email');
		if(tmp.data === null)
			return;
		tmp.data.orderId = tmp.me._order.id;
		tmp.me._pageJs.postAjax(OrderBtnsJs.SEND_EMAIL_CALLBACK_ID, tmp.data, {
			'onLoading': function() {
				tmp.me._signRandID(btn);
				jQuery('#' + btn.id).button('loading');
			}
			,'onSuccess': function(sender, param) {
				try {
					tmp.result = tmp.me._pageJs.getResp(param, false, true);
					if(!tmp.result || !tmp.result.item)
						return;
					tmp.confirmDiv.update('<h4 class="text-success">Email Successfully added into the Message Queue. Will be sent within a minute</h4>');
					setTimeout(function() {tmp.me._pageJs.hideModalBox();}, 2000);
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
	/**
	 * Getting the form group
	 */
	,_getFormGroup: function(title, content) {
		return new Element('div', {'class': 'form-group'})
		.insert({'bottom': new Element('label', {'class': 'control-label'}).update(title) })
		.insert({'bottom': content.addClassName('form-control') });
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
						tmp.me._pageJs.hideModalBox();
					})
				})
				.insert({'bottom': new Element('span', {'class': 'btn btn-primary', 'data-loading-text': 'Sending ...'})
					.update('Yes, send this ' + tmp.me._order.type + ' to this email address')
					.observe('click', function(){
						tmp.me._sendEmail(this);
					})
				})
			});
		tmp.me._pageJs.showModalBox('<strong>Confirm Email Address:</strong>', tmp.newDiv);
		return tmp.me;
	}
	/**
	 * Getting the btns div
	 */
	,getBtnsDiv: function() {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = new Element('div', {'class': 'order-btns-div'})
			.insert({'bottom': new Element('div', {'class': 'btn-group btn-group-xs visible-xs visible-md visible-sm visible-lg'})
				.insert({'bottom': new Element('span', {'class': 'btn btn-info'})
					.insert({'bottom': new Element('span', {'class': 'hidden-xs hidden-sm'}).update('Print ') })
					.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-print'}) })
					.observe('click', function() {
						tmp.me.openOrderPrintPage(1);
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
								tmp.me.openOrderPrintPage(1);
							})
						})
					})
					.insert({'bottom': new Element('li')
						.insert({'bottom': new Element('a', {'href': 'javascript: void(0);'})
							.insert({'bottom': new Element('span').update('Print HTML') })
							.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-print'}) })
							.observe('click', function() {
								tmp.me.openOrderPrintPage(0);
							})
						})
					})
					.insert({'bottom': new Element('li', {'class': 'divider'}) })
					.insert({'bottom': new Element('li')
						.insert({'bottom': new Element('a', {'href': 'javascript: void(0);'})
							.insert({'bottom': new Element('span').update('Print Delivery Docket ') })
							.insert({'bottom': new Element('span', {'class': 'fa fa-file-pdf-o'}) })
							.observe('click', function() {
								tmp.me.openDocketPrintPage(1);
							})
						})
					})
					.insert({'bottom': new Element('li')
						.insert({'bottom': new Element('a', {'href': 'javascript: void(0);'})
							.insert({'bottom': new Element('span').update('Print Delivery Docket ') })
							.insert({'bottom': new Element('span', {'class': 'fa fa-ils'}) })
							.observe('click', function() {
								tmp.me.openDocketPrintPage(0);
							})
						})
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
			});
		return tmp.newDiv;
	}
}
