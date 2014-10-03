/**
 * The StaticsPageJs file
 */
var StaticsPageJs = new Class.create();
StaticsPageJs.prototype = Object.extend(new BPCPageJs(), {
	_htmlIds: {'resultDivId': ''}
	,_searchCriterias: {}
	
	,setHTMLIDs: function(resultDivId) {
		this._htmlIds.resultDivId = resultDivId;
		return this;
	}
	
	,_drawChart: function(result) {
		var tmp = {};
		tmp.me = this;
		jQuery('#' + tmp.me._htmlIds.resultDivId).highcharts(result);
		return tmp.me;
	}
	
	,_getData: function() {
		var tmp = {};
		tmp.me = this;
		tmp.me.postAjax(tmp.me.getCallbackId('getData'), tmp.me._searchCriterias, {
			'onLoading': function() {
				$(tmp.me._htmlIds.resultDivId).update(tmp.me.getLoadingImg());
			},
			'onSuccess': function(sender, param) {
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result)
						throw 'Syste Error: No result came back!';
					tmp.me._drawChart(tmp.result);
				} catch (e) {
					$(tmp.me._htmlIds.resultDivId).update(tmp.me.getAlertBox('ERROR:', e).addClassName('alert-danger'));
				}
			}
		});
		return tmp.me;
	}
	
	,load: function (searchCriterias) {
		this._searchCriterias = searchCriterias;
		return this._getData();
	}
});