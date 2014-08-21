/**
 * The DetailsPageJs file
 */
var DetailsPageJs = new Class.create();
DetailsPageJs.prototype = Object.extend(new BPCPageJs(), {
	_item: null //the item we are dealing with
	,_htmlIds: {'itemDiv': ''}
	
	,setHTMLIDs: function(itemDivId) {
		this._htmlIds.itemDiv = itemDivId;
		return this;
	}
	
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
			'onLoading': function (sender, param) {
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
					tmp.me.showModalBox('<strong class="text-danger">ERROR:</strong>', e, true);
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
	
	,load: function () {
		var tmp = {};
		tmp.me = this;
		$(tmp.me._htmlIds.itemDiv).update(tmp.me._getItemDiv());
		return tmp.me;
	}
});