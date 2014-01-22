/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new BPCPageJs(), {
	resultDivId: '' //the html id of the result div
	,searchDivId: '' //the html id of the search div
	,totalNoOfItemsId: '' //the html if of the total no of items
	,_pagination: {'pageNo': 1, 'pageSize': 30} //the pagination details
	,_searchCriteria: {} //the searching criteria
	,_infoTypes:{} //the infotype ids
	
	,getSearchCriteria: function() {
		var tmp = {};
		tmp.me = this;
		if(tmp.me._searchCriteria === null)
			tmp.me._searchCriteria = {};
		tmp.nothingTosearch = true;
		$(tmp.me.searchDivId).getElementsBySelector('[search_field]').each(function(item) {
			tmp.me._searchCriteria[item.readAttribute('search_field')] = $F(item);
			if(!$F(item).blank())
				tmp.nothingTosearch = false;
		});
		if(tmp.nothingTosearch === true)
			tmp.me._searchCriteria = null;
		return this;
	}
		
	,getResults: function(reset, pageSize) {
		var tmp = {};
		tmp.me = this;
		tmp.reset = (reset || false);
		if(tmp.me._searchCriteria === null)
		{
			alert('Nothing to search!');
			return;
		}
		if(tmp.reset === true)
			tmp.me._pagination.pageNo = 1;
		tmp.me._pagination.pageSize = (pageSize || tmp.me._pagination.pageSize);
		tmp.me.postAjax(tmp.me.getCallbackId('getOrders'), {'pagination': tmp.me._pagination, 'searchCriteria': tmp.me._searchCriteria}, {
			'onLoading': function () {}
			,'onComplete': function(sender, param) {
				try{
					tmp.result = tmp.me.getResp(param, false, true);
					$(tmp.me.totalNoOfItemsId).update(tmp.result.pageStats.totalRows);
					
					tmp.resultDiv = $(tmp.me.resultDivId);
					//reset div
					if(tmp.reset === true) {
						tmp.titleRow = {'orderNo': "Order No.", 'orderDate': 'Order Date', 'custName': 'Customer Name', 'shippingAddr': 'Shipping Address', 'invNo': 'Invoice No.', 'status': {'name': 'Status'}, 'totalDue': 'Total Due'};
						tmp.resultDiv.update(tmp.me._getResultRow(tmp.titleRow, true).addClassName('header'));
					}
					//remove next page button
					tmp.resultDiv.getElementsBySelector('.paginWrapper').each(function(item){
						item.remove();
					})
					//show all next page items
					tmp.result.items.each(function(item) {
						tmp.resultDiv.insert({'bottom': tmp.me._getResultRow(item) });
					})
					//show the next page button
					if(tmp.result.pageStats.pageNumber < tmp.result.pageStats.totalPages)
						tmp.resultDiv.insert({'bottom': tmp.me._getNextPageBtn().addClassName('paginWrapper') });
					
				} catch (e) {
					alert(e);
				}
			}
		});
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
	
	,_getAddrDiv: function(addr) {
		return new Element('div')
			.insert({'bottom': new Element('span', {'class': 'addr_contact_name'}).update(addr.contactName).insert({'top': new Element('span', {'class': 'icon'}) })  })
			.insert({'bottom': new Element('span', {'class': 'addr_contactNo'}).update(addr.contactNo).insert({'top': new Element('span', {'class': 'icon'}) }) })
			.insert({'bottom': new Element('span', {'class': 'addr_addr'}).update(addr.full).insert({'top': new Element('span', {'class': 'icon'}) }) })
		;
	}
		
	,_getResultRow: function(row, isTitle) {
		var tmp = {};
		tmp.me = this;
		tmp.isTitle = (isTitle || false);
		return new Element('div', {'class': 'row'}).store('data', row)
			.insert({'bottom': new Element('span', {'class': 'cell orderNo'}).update(
				tmp.isTitle ? row.orderNo : new Element('div').update(
						new Element('div', {'class': 'orderNoNo'}).update(row.orderNo).observe('click', function() {
							jQuery.fancybox({
								//'orig'			: jQuery(this),
								'width'			: '80%',
								'height'		: '90%',
								'autoScale'     : false,
						        'transitionIn'	: 'none',
								'transitionOut'	: 'none',
								'type'			: 'iframe',
								'autoDimensions': false,
								'autoSize'      : false,
								'href'			: '/orderdetails/' + row.id + '.html',
					 		});
						})
					)
					.insert({'bottom': new Element('div', {'class': 'qty'}).update('Qty: ' + row.infos[tmp.me._infoTypes['qty']][0].value) })
			) })
			.insert({'bottom': new Element('span', {'class': 'cell orderDate'}).update(row.orderDate) })
			.insert({'bottom': new Element('span', {'class': 'cell custName'}).update(tmp.isTitle ? row.custName : new Element('div').update(row.infos[tmp.me._infoTypes['custName']][0].value).insert({'bottom': new Element('div', {'class': 'custEmail'}).update(row.infos[tmp.me._infoTypes['custEmail']][0].value) }) ) })
			.insert({'bottom': new Element('span', {'class': 'cell shippingAddr'}).update(tmp.isTitle ? row.shippingAddr : tmp.me._getAddrDiv(row.address.shipping)) })
			.insert({'bottom': new Element('span', {'class': 'cell status'}).update(row.status ? row.status.name : '') })
			.insert({'bottom': new Element('span', {'class': 'cell payment'}).update(tmp.isTitle ? row.totalDue : tmp.me.getCurrency(row.totalDue)) })
			.insert({'bottom': new Element('span', {'class': 'cell invNo'}).update(row.invNo) });		
	}
});