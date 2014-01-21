/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new BPCPageJs(), {
	_collectingFields: function(btn, attrName) {
		var tmp = {};
		tmp.me = this;
		tmp.data = {};
		tmp.foundData = false;
		$(btn).up('.contentDiv').getElementsBySelector('[' + attrName + ']').each(function(item) {
			if(!$F(item).blank())
				tmp.foundData = true;
			tmp.data[item.readAttribute(attrName)] = $F(item);
		});
		if(tmp.foundData === true)
			return tmp.data;
		return null;
	}
	
	,changePwd: function(btn) {
		var tmp = {};
		tmp.me = this;
		if(tmp.me._collectingFields(btn, 'change_pass') === null)
			return;
	}
});