/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new BPCPageJs(), {
	_getFormFields: function(btn, attrName) {
		return $(btn).up('.contentDiv').getElementsBySelector('[' + attrName + ']');
	}

	,_collectingFields: function(btn, attrName) {
		var tmp = {};
		tmp.me = this;
		tmp.data = {};
		tmp.foundData = false;
		tmp.me._getFormFields(btn, attrName).each(function(item) {
			if(item.hasAttribute('required') && $F(item).blank()) {
				item.up('.form-group').addClassName('has-error');
			} else {
				item.up('.form-group').removeClassName('has-error');
				tmp.foundData = true;
			}
			tmp.data[item.readAttribute(attrName)] = $F(item);
		});
		if(tmp.foundData === true)
			return tmp.data;
		return null;
	}

	,_cleanForm: function(btn, attrName) {
		var tmp = {};
		tmp.me = this;
		tmp.me._getFormFields(btn, attrName).each(function(item) {
			item.setValue('');
		});
		return this;
	}
	
	,changePwd: function(btn) {
		var tmp = {};
		tmp.me = this;
		tmp.data = tmp.me._collectingFields(btn, 'change_pass');
		if(tmp.data === null)
			return;
		
		jQuery('#' + btn.id).button('loading');
		tmp.me.postAjax(tmp.me.getCallbackId('changePwd'), tmp.data, {
			'onCreate': function () {}
			,'onComplete': function(sender, param) {
				try{
					tmp.result = tmp.me.getResp(param, false, true);
					if(tmp.result.succ) {
						tmp.me._cleanForm(btn, 'change_pass');
						$(btn).insert({'before': tmp.me.getAlertBox('', 'Password changed!').addClassName('alert-success') });
					}
				} catch (e) {
					$(btn).insert({'before': tmp.me.getAlertBox('Error:', e).addClassName('alert-danger') });
				}
				jQuery('#' + btn.id).button('reset');
			}
		});
	}
	
	,changePInfo: function(btn) {
		var tmp = {};
		tmp.me = this;
		tmp.data = tmp.me._collectingFields(btn, 'change_pinfo');
		if(tmp.data === null)
			return;
		jQuery('#' + btn.id).button('loading');
		tmp.me.postAjax(tmp.me.getCallbackId('changePersonInfo'), tmp.data, {
			'onCreate': function () {}
			,'onComplete': function(sender, param) {
				try{
					tmp.result = tmp.me.getResp(param, false, true);
					if(tmp.result.succ) {
						$(btn).insert({'before': tmp.me.getAlertBox('', 'Information changed!').addClassName('alert-success') });
						location.reload();
					}
				} catch (e) {
					$(btn).insert({'before': tmp.me.getAlertBox('Error:', e).addClassName('alert-danger') });
				}
				jQuery('#' + btn.id).button('reset');
			}
		});
	}
});