/**
 * The DetailsPageJs file
 */
var DetailsPageJs = new Class.create();
DetailsPageJs.prototype = Object.extend(new BPCPageJs(), {
	_item: null //the item we are dealing with

	,setItem: function(item) {
		this._item = item;
		return this;
	}

	,saveItem: function(btn, data, onSuccFunc) {
		var tmp = {};
		tmp.me = this;
		if(btn)
			tmp.me._signRandID(btn);
		tmp.me.postAjax(tmp.me.getCallbackId('saveItem'), data, {
			'onCreate': function (sender, param) {
				if(btn) {
					jQuery('#' + btn.id).button('loading');
				}
			}
			, 'onSuccess': function (sender, param) {
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					if(typeof(onSuccFunc) === 'function')
						onSuccFunc(tmp.result);
				} catch (e) {
					tmp.me.showModalBox('<strong class="text-danger">ERROR:</strong>', e);
				}
			}
			, 'onComplete': function() {
				if(btn) {
					jQuery('#' + btn.id).button('reset');
				}
			}
		});
		return tmp.me;
	}

	,_init: function(){
		var tmp = {};
		tmp.me = this;
		return tmp.me;
	}

	,load: function () {
		var tmp = {};
		tmp.me = this;
		tmp.me._init();
		$(tmp.me.getHTMLID('itemDiv')).update(tmp.me._getItemDiv());
		return tmp.me;
	}
});