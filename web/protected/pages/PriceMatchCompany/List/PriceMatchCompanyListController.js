/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new BPCPageJs(), {
	_resultDivId: '' // the id of the result div
	,_pageInfo: {'pageNo': 1, 'pageSize': 30}
	,_searchCriteria: ''
		
	,displayAllPriceMatchCompany: function() {
		var tmp = {};
		tmp.me = this;
		
		tmp.me.postAjax(tmp.me.getCallbackId('getPriceMatchCompany'), {'searchCriteria': tmp.me._searchCriteria, 'pagination': tmp.me._pageInfo}, {
			'onLoading': function (sender, param) {
				//$(tmp.me.searchBtnId).store('orignValue', $F(tmp.me.searchBtnId)).addClassName('disabled').setValue('searching ...').disabled = true;
			}
			, 'onComplete': function (sender, param) {
				try 
				{
					tmp.result = tmp.me.getResp(param, false, true);
				} 
				catch(e) 
				{
					alert(e);
				}
				
			}
		});
		
	}
	
});