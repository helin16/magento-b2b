/**
 * The FrontEndPageAbstract Js file
 */
var BPCPageJs = new Class.create();
BPCPageJs.prototype = {
	modalId: 'page_modal_box_id'
		
	,_ajaxRequest: null
		
	//the callback ids
	,callbackIds: {}

	//constructor
	,initialize: function () {}
	
	,setCallbackId: function(key, callbackid) {
		this.callbackIds[key] = callbackid;
		return this;
	}
	
	,getCallbackId: function(key) {
		if(this.callbackIds[key] === undefined || this.callbackIds[key] === null)
			throw 'Callback ID is not set for:' + key;
		return this.callbackIds[key];
	}
	
	//posting an ajax request
	,postAjax: function(callbackId, data, requestProperty, timeout) {
		var tmp = {};
		tmp.me = this;
		tmp.me._ajaxRequest = new Prado.CallbackRequest(callbackId, requestProperty);
		tmp.me._ajaxRequest.setCallbackParameter(data);
		tmp.timeout = (timeout || 30000);
		if(tmp.timeout < 30000) {
			tmp.timeout = 30000;
		}
		tmp.me._ajaxRequest.setRequestTimeOut(tmp.timeout);
		tmp.me._ajaxRequest.dispatch();
		return tmp.me._ajaxRequest;
	}
	
	,abortAjax: function() {
		if(tmp.me._ajaxRequest !== null)
			tmp.me._ajaxRequest.abort();
	}
	
	//parsing an ajax response
	,getResp: function (response, expectNonJSONResult, noAlert) {
		var tmp = {};
		tmp.expectNonJSONResult = (expectNonJSONResult !== true ? false : true);
		tmp.result = response;
		if(tmp.expectNonJSONResult === true)
			return tmp.result;
		if(!tmp.result || !tmp.result.isJSON()) {
			return;
//			tmp.error = 'Invalid JSON string: ' + tmp.result;
//			if (noAlert === true)
//				throw tmp.error;
//			else 
//				return alert(tmp.error);
		}
		tmp.result = tmp.result.evalJSON();
		if(tmp.result.errors.size() !== 0) {
			tmp.error = 'Error: \n\n' + tmp.result.errors.join('\n');
			if (noAlert === true)
				throw tmp.error;
			else 
				return alert(tmp.error);
		}
		return tmp.result.resultData;
	}
	//format the currency
	,getCurrency: function(number, dollar, decimal, decimalPoint, thousandPoint) {
		var tmp = {};
		tmp.decimal = (isNaN(decimal = Math.abs(decimal)) ? 2 : decimal);
		tmp.dollar = (dollar == undefined ? "$" : dollar);
		tmp.decimalPoint = (decimalPoint == undefined ? "." : decimalPoint);
		tmp.thousandPoint = (thousandPoint == undefined ? "," : thousandPoint);
		tmp.sign = (number < 0 ? "-" : "");
		tmp.Int = parseInt(number = Math.abs(+number || 0).toFixed(tmp.decimal)) + "";
		tmp.j = (tmp.j = tmp.Int.length) > 3 ? tmp.j % 3 : 0;
		return tmp.dollar + tmp.sign + (tmp.j ? tmp.Int.substr(0, tmp.j) + tmp.thousandPoint : "") + tmp.Int.substr(tmp.j).replace(/(\d{3})(?=\d)/g, "$1" + tmp.thousandPoint) + (tmp.decimal ? tmp.decimalPoint + Math.abs(number - tmp.Int).toFixed(tmp.decimal).slice(2) : "");
	}
	/**
	 * Getting the absolute value from currency
	 */
	,getValueFromCurrency: function(currency) {
		if(!currency)
			return '';
		return currency.replace(/\s*/g, '').replace(/\$/g, '').replace(/,/g, '');
	}
	//do key enter
	,keydown: function (event, enterFunc, nFunc) {
		//if it's not a enter key, then return true;
		if(!((event.which && event.which == 13) || (event.keyCode && event.keyCode == 13))) {
			if(typeof(nFunc) === 'function') {
				nFunc();
			}
			return true;
		}
		
		if(typeof(enterFunc) === 'function') {
			enterFunc();
		}
		return false;
	}
	//getting the error message box
	,getAlertBox: function(title, msg) {
		return new Element('div', {'class': 'alert alert-dismissible', 'role': 'alert'})
		.insert({'bottom': new Element('button', {'class': 'close', 'data-dismiss': 'alert'})
			.insert({'bottom': new Element('span', {'aria-hidden': 'true'}).update('&times;') })
			.insert({'bottom': new Element('span', {'class': 'sr-only'}).update('Close') })
		})
		.insert({'bottom': new Element('strong').update(title) })
		.insert({'bottom': msg })
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
			.tooltip('show');
			$(input).observe('change', function() {
				input.up('.form-group').removeClassName('has-error');
				jQuery(this).tooltip('hide').tooltip('destroy').show();
			});
		}
		return tmp.me;
	}
	/**
	 * Collecting data from attrName
	 */
	,_collectFormData: function(container, attrName, groupIndexName) {
		var tmp = {};
		tmp.me = this;
		tmp.data = {};
		tmp.hasError = false;
		$(container).getElementsBySelector('[' + attrName + ']').each(function(item) {
			tmp.groupIndexName = groupIndexName ? item.readAttribute(groupIndexName) : null;
			tmp.fieldName = item.readAttribute(attrName);
			if(item.hasAttribute('required') && $F(item).blank()) {
				tmp.me._markFormGroupError(item, 'This is requried');
				tmp.hasError = true;
			}
			
			tmp.itemValue = item.readAttribute('type') !== 'checkbox' ? $F(item) : $(item).checked;
			if(item.hasAttribute('validate_currency') || item.hasAttribute('validate_number')) {
				if (tmp.me.getValueFromCurrency(tmp.itemValue).match(/^\d+(\.\d{1,2})?$/) === null) {
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
	
	,showModalBox: function(title, content, isSM, footer) {
		var tmp = {};
		tmp.me = this;
		tmp.isSM = (isSM === true ? true : false);
		tmp.footer = (footer ? footer : null);
		tmp.newBox = new Element('div', {'class': 'modal', 'tabindex': '-1', 'role': 'dialog', 'aria-hidden': 'true', 'aria-labelledby': 'page-modal-box'})
			.insert({'bottom': new Element('div', {'class': 'modal-dialog ' + (tmp.isSM === true ? 'modal-sm' : 'modal-lg') })
				.insert({'bottom': new Element('div', {'class': 'modal-content' })
					.insert({'bottom': new Element('div', {'class': 'modal-header' })
						.insert({'bottom': new Element('div', {'class': 'close', 'type': 'button', 'data-dismiss': 'modal'})
							.insert({'bottom':new Element('span', {'aria-hidden': 'true'}).update('&times;') })
						})
						.insert({'bottom': new Element('strong', {'class': 'modal-title', 'id': 'page-modal-box'}).update(title) })
					})
					.insert({'bottom': new Element('div', {'class': 'modal-body' }).update(content) })
					.insert({'bottom': tmp.footer === null ? '' : new Element('div', {'class': 'modal-footer' }).update(tmp.footer) })
				})
			});
		
		if($(tmp.me.modalId)) {
			$(tmp.me.modalId).remove();
		}
		
		$$('body')[0].insert({'bottom': tmp.newBox.writeAttribute('id',  tmp.me.modalId)});
		jQuery('#' + tmp.me.modalId).modal({'show': true, 'target': '#' + tmp.me.modalId});
		return tmp.me;
	}
};