/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new BPCPageJs(), {
	resultDivId: '' //the html id of the result div
	,searchDivId: '' //the html id of the search div
	,searchBtnId: 'searchBtn' //the html id of the search button
	,totalNoOfItemsId: '' //the html if of the total no of items
	,_pagination: {'pageNo': 1, 'pageSize': 30} //the pagination details
	,_searchCriteria: {} //the searching criteria
	
	,_loadChosen: function () {
		$$(".chosen").each(function(item) {
			item.store('chosen', new Chosen(item, {
				disable_search_threshold: 10,
				no_results_text: "Oops, nothing found!"
			}) );
		});
		return this;
	}
	
	,_bindSearchKey: function() {
		var tmp = {}
		tmp.me = this;
		$(tmp.me.searchDivId).getElementsBySelector('[search_field]').each(function(item) {
			item.observe('keydown', function(event) {
				tmp.me.keydown(event, function() {
					$('searchBtn').click();
				});
			})
		});
		return this;
	}
	
	,init: function() {
		this._bindSearchKey()
			._loadChosen();
		return this;
	}
	
	,setSearchCriteria: function(criteria) {
		this._searchCriteria = criteria;
		return this;
	}
	
	,getSearchCriteria: function() {
		var tmp = {};
		tmp.me = this;
		tmp.me._searchCriteria = {};
		
		tmp.nothingTosearch = true;
		$(tmp.me.searchDivId).getElementsBySelector('[search_field]').each(function(item) {
			tmp.me._searchCriteria[item.readAttribute('search_field')] = $F(item);
			if(($F(item) instanceof Array && $F(item).size() > 0) || (typeof $F(item) === 'string' && !$F(item).blank()))
				tmp.nothingTosearch = false;
		});
		if(tmp.nothingTosearch === true)
			tmp.me._searchCriteria = null;
		return this;
	}
	
	,getResults: function (reset, pageSize) {
		var tmp = {};
		tmp.me = this;
		if(tmp.me._searchCriteria === null) {
			alert('Nothing to search!');
			return;
		}
		tmp.me.postAjax(tmp.me.getCallbackId('getOrderitems'), {'searchCriteria': tmp.me._searchCriteria, 'pagination': tmp.me._pagination}, {
			'onLoading': function (sender, param) {
				$(tmp.me.searchBtnId).store('orignValue', $F(tmp.me.searchBtnId)).addClassName('disabled').setValue('searching ...').disabled = true;
			}
			, 'onComplete': function (sender, param) {
				try {
					tmp.result = tmp.me.getResp(param, false, true);
				} catch(e) {
					alert(e);
				}
				$(tmp.me.searchBtnId).removeClassName('disabled').setValue($(tmp.me.searchBtnId).retrieve('orignValue')).disabled = false;
			}
		});
	}
});