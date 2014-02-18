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
		var tmp = {}
		tmp.me = this;
		$(tmp.me.searchDivId).getElementsBySelector('[search_field]').each(function(item) {
			tmp.field = item.readAttribute('search_field');
			if(criteria[tmp.field])
				$(item).setValue(criteria[tmp.field]);
		});
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
	
	,_getResultRow: function(row, isTitle) {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = new Element('div', {'class': 'row', 'item_id': row.id}).store('data', row)
			.insert({'bottom': new Element('span', {'class': 'cell  productsku'}).update(row.product.sku) })
			.insert({'bottom': new Element('span', {'class': 'cell  productname'}).update(row.product.name) })
			.insert({'bottom': new Element('span', {'class': 'cell  orderno'}).update(
					tmp.isTitle ? row.order.orderNo : new Element('div', {'class': 'orderNolink cuspntr'}).update(row.order.orderNo).observe('click', function() {
						jQuery.fancybox({
							'width'			: '80%',
							'height'		: '90%',
							'autoScale'     : true,
							'type'			: 'iframe',
							'href'			: '/orderdetails/' + row.id + '.html',
							'beforeClose'	    : function() {
								tmp.items = $$('iframe.fancybox-iframe').first().contentWindow.pageJs._orderItems;
								if(tmp.items && tmp.items.size() >0) {
									tmp.items.each(function(item) {
										tmp.itemRow = $(tmp.me.resultDivId).down('.row[item_id=' + row.id + ']');
										if(tmp.itemRow)
											tmp.itemRow.replace(tmp.me._getResultRow(item));
									});
								}
							}
				 		});
					})
			) })
			.insert({'bottom': new Element('span', {'class': 'cell  orderstatus'}).update(row.order.status.name) })
			.insert({'bottom': new Element('span', {'class': 'cell  qty'}).update(row.qtyOrdered) })
			.insert({'bottom': new Element('span', {'class': 'cell  isordered'}).update(isTitle === true ? row.isOrdered : (row.isOrdered ? new Element('span', {'class': 'ticked inlineblock'}) : '')) })
			.insert({'bottom': new Element('span', {'class': 'cell  eta'}).update(row.eta) })
			.insert({'bottom': new Element('span', {'class': 'cell  comments'}).update(
				isTitle === true? 'Comments': ''
			) })
		return tmp.newDiv;
	}
	
	,_getNextPageBtn: function() {
		var tmp = {}
		tmp.me = this;
		return new Element('div', {'class': 'pagination'})
			.insert({'bottom': new Element('span', {'class': 'button'}).update('Show More')
				.observe('click', function() {
					tmp.me._pagination.pageNo = tmp.me._pagination.pageNo*1 + 1;
					$(this).update('Fetching more results ...').addClassName('disabled');
					tmp.me.getResults();
				})
			});
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
					
					tmp.resultDiv = $(tmp.me.resultDivId);
					
					//remove next page button
					tmp.resultDiv.getElementsBySelector('.paginWrapper').each(function(item){
						item.remove();
					});
					
					//if reset the result div
					if(reset === true) {
						tmp.resultDiv.update(
							tmp.me._getResultRow({'order': {'orderNo': 'ORDER NO', 'status': {'name': 'Order Status'}}, 'product': {'sku': 'SKU', 'name': 'Product Name'}, 'qtyOrdered': 'QTY', 'eta': 'ETA', 'isOrdered': 'Ordered?' }, true)
								.addClassName('header')
						)
					}
					//add all the items
					tmp.result.items.each(function (item) {
						tmp.resultDiv.insert({'bottom': tmp.me._getResultRow(item) })
					});
					//show the next page button
					if(tmp.result.pageStats.pageNumber < tmp.result.pageStats.totalPages)
						tmp.resultDiv.insert({'bottom': tmp.me._getNextPageBtn().addClassName('paginWrapper') });
				} catch(e) {
					alert(e);
				}
				$(tmp.me.searchBtnId).removeClassName('disabled').setValue($(tmp.me.searchBtnId).retrieve('orignValue')).disabled = false;
			}
		});
	}
});