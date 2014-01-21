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
			if(!$F(item).blank())
				tmp.foundData = true;
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
		$(btn).store('originValue', $F(btn)).addClassName('disabled').setValue('Saving...');
		tmp.me.postAjax(tmp.me.getCallbackId('changePwd'), tmp.data, {
			'onLoading': function () {}
			,'onComplete': function(sender, param) {
				try{
					tmp.result = tmp.me.getResp(param, false, true);
					if(tmp.result.succ) {
						tmp.me._cleanForm(btn, 'change_pass');
						alert('Password changed!');
					}
				} catch (e) {
					alert(e);
				}
				$(btn).setValue($(btn).retrieve('originValue')).removeClassName('disabled');
			}
		});
	}
	
	,changePInfo: function(btn) {
		var tmp = {};
		tmp.me = this;
		tmp.data = tmp.me._collectingFields(btn, 'change_pinfo');
		if(tmp.data === null)
			return;
		$(btn).store('originValue', $F(btn)).addClassName('disabled').setValue('Saving...');
		tmp.me.postAjax(tmp.me.getCallbackId('changePersonInfo'), tmp.data, {
			'onLoading': function () {}
			,'onComplete': function(sender, param) {
				try{
					tmp.result = tmp.me.getResp(param, false, true);
					if(tmp.result.succ) {
						alert('Information changed!');
						location.reload();
					}
				} catch (e) {
					alert(e);
				}
				$(btn).setValue($(btn).retrieve('originValue')).removeClassName('disabled');
			}
		});
	}
});