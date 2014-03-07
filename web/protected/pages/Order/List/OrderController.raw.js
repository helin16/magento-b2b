/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new BPCPageJs(), {
	resultDivId: '' //the html id of the result div
	,searchDivId: '' //the html id of the search div
	,totalNoOfItemsId: '' //the html if of the total no of items
	,_pagination: {'pageNo': 1, 'pageSize': 10} //the pagination details
	,_searchCriteria: {} //the searching criteria
	,_infoTypes:{} //the infotype ids
	,orderStatuses: [] //the order statuses object
	
	,_loadChosen: function () {
		$$(".chosen").each(function(item) {
			item.store('chosen', new Chosen(item, {
				disable_search_threshold: 10,
				no_results_text: "Oops, nothing found!",
				width: "95%"
			}) );
		});
		return this;
	}
	
	,_bindSearchKey: function() {
		var tmp = {}
		tmp.me = this;
		$('searchDiv').getElementsBySelector('[search_field]').each(function(item) {
			item.observe('keydown', function(event) {
				tmp.me.keydown(event, function() {
					$('searchBtn').click();
				});
			})
		});
		return this;
	}
	
	,_loadStatuses: function(orderStatuses) {
		this.orderStatuses = orderStatuses;
		var tmp = {};
		tmp.me = this;
		tmp.statusBox = $(tmp.me.searchDivId).down('#orderStatusId');
		tmp.me.orderStatuses.each(function(status) {
			tmp.statusBox.insert({'bottom': new Element('option', {'value': status.id}).update(status.name) });
		});
		return this;
	}
	
	,setSearchCriteria: function(criteria) {
		var tmp = {};
		tmp.me = this;
		tmp.searchPanel = $(tmp.me.searchDivId);
		$H(criteria).each(function(cri){
			tmp.field = cri.key;
			tmp.value = cri.value;
			tmp.fieldBox = tmp.searchPanel.down('[search_field="' + tmp.field + '"]');
			if(tmp.fieldBox) {
				tmp.optlength = tmp.fieldBox.options.length;
				for(tmp.i = 0; tmp.i < tmp.optlength; tmp.i++) {
					if(tmp.value.indexOf(tmp.fieldBox.options[tmp.i].value * 1) >= 0) {
						tmp.fieldBox.options[tmp.i].selected = true;
					}
				}
			}
		});
		tmp.me._loadChosen()._bindSearchKey();
		return this;
	}
	
	,getSearchCriteria: function() {
		var tmp = {};
		tmp.me = this;
		if(tmp.me._searchCriteria === null)
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
		
	,getResults: function(reset, pageSize) {
		var tmp = {};
		tmp.me = this;
		tmp.searchBtn = $('searchBtn');
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
			'onLoading': function () {
				tmp.searchBtn.store('originValue', $F(tmp.searchBtn)).addClassName('disabled').setValue('Searching ...').disabled = true;
			}
			,'onSuccess': function(sender, param) {
				try{
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result)
						return;
					$(tmp.me.totalNoOfItemsId).update(tmp.result.pageStats.totalRows);
					
					tmp.resultDiv = $(tmp.me.resultDivId);
					//reset div
					if(tmp.reset === true) {
						tmp.titleRow = {'orderNo': "Order Info.", 'custName': 'Customer Name', 'shippingAddr': 'Shipping Address', 'invNo': 'Invoice No.', 'status': {'name': 'Status'}, 'totalDue': 'Total Due', 'passPaymentCheck': 'Payment Cleared?'};
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
				tmp.searchBtn.removeClassName('disabled').setValue(tmp.searchBtn.retrieve('originValue')).disabled = false;
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
		var tmp = {};
		tmp.me = this;
		return new Element('div')
			.insert({'bottom':tmp.me._getTitledDiv(new Element('span', {'class': 'icon'}), addr.contactName ).addClassName('addr_contact_name')  })
			.insert({'bottom':tmp.me._getTitledDiv(new Element('span', {'class': 'icon'}), addr.contactNo ).addClassName('addr_contactNo')  })
			.insert({'bottom':tmp.me._getTitledDiv(new Element('span', {'class': 'icon'}), addr.full ).addClassName('addr_addr')  })
		;
	}
	
	,_getTitledDiv: function(title, content) {
		return new Element('div', {'class': 'field_div'})
			.insert({'bottom': new Element('span', {'class': 'inlineblock title'}).update(title) })
			.insert({'bottom': new Element('span', {'class': 'inlineblock divcontent'}).update(content) });
	}
	
	,_openDetailPage: function(row) {
		var tmp = {};
		tmp.me = this;
		jQuery.fancybox({
			'width'			: '80%',
			'height'		: '90%',
			'autoScale'     : true,
			'type'			: 'iframe',
			'href'			: '/orderdetails/' + row.id + '.html',
			'beforeClose'	    : function() {
				if($(tmp.me.resultDivId).down('.row[order_id=' + row.id + ']'))
					$(tmp.me.resultDivId).down('.row[order_id=' + row.id + ']').replace(tmp.me._getResultRow($$('iframe.fancybox-iframe').first().contentWindow.pageJs._order));
			}
 		});
	}
	
	,_getOrderInfoCell: function(row) {
		var tmp = {};
		tmp.me = this
		tmp.quantity = 'n/a';
		tmp.custName = 'n/a';
		tmp.custEmail = 'n/a';
		
		if(row.infos && row.infos !== null)
		{
			if(tmp.me._infoTypes['qty'] in row.infos && row.infos[tmp.me._infoTypes['qty']].length > 0)
				tmp.quantity = row.infos[tmp.me._infoTypes['qty']][0].value;
			if(tmp.me._infoTypes['custName'] in row.infos && row.infos[tmp.me._infoTypes['custName']].length > 0)
				tmp.custName = row.infos[tmp.me._infoTypes['custName']][0].value;
			if(tmp.me._infoTypes['custEmail'] in row.infos && row.infos[tmp.me._infoTypes['custEmail']].length > 0)
				tmp.custEmail = row.infos[tmp.me._infoTypes['custEmail']][0].value;
		}
		return new Element('div').update(
				new Element('div', {'class': 'orderNo'}).update(row.orderNo).observe('click', function() {
					tmp.me._openDetailPage(row);
				})
			)
			.insert({'bottom': tmp.me._getTitledDiv(new Element('span', {'class': 'icon'}), tmp.custName).addClassName('custName addr_contact_name') })
			.insert({'bottom': tmp.me._getTitledDiv(new Element('span', {'class': 'icon'}), new Element('a', {'href': 'mailto:' + tmp.custEmail}).update(tmp.custEmail) ).addClassName('custEmail addr_contact_email') })
			.insert({'bottom': tmp.me._getTitledDiv(new Element('span', {'class': 'icon'}) , row.orderDate).addClassName('orderDate') });
	}
	
	,_getPaymentCell: function(row) {
		var tmp = {};
		tmp.me = this
		return new Element('div')
			.insert({'bottom': tmp.me._getTitledDiv('', 
					!row.passPaymentCheck ? '': new Element('span', {'class': 'icon'})
							.addClassName(row.totalDue === 0 ? 'ok' : 'warning').writeAttribute('title', row.totalDue === 0 ? 'Full Paied' : 'Short Paid')
				) })
				.insert({'bottom': tmp.me._getTitledDiv(new Element('span', {'class': 'icon'}), tmp.me.getCurrency(row.totalDue) ).addClassName('totalDue').writeAttribute('title', 'Total Due Amount:' + tmp.me.getCurrency(row.totalDue)) })
				.insert({'bottom': !row.passPaymentCheck ? '' : tmp.me._getTitledDiv(new Element('span', {'class': 'icon'}), 
						new Element('span', {'class': 'tooltipTarget'}).update('payment details').writeAttribute('title', 'click to view payment details')
							.observe('click', function() {
								tmp.me._openDetailPage(row);
							})
				).addClassName('paymentdetails') });
	}
	
	,_getPurchasingCell: function(row) {
		var tmp = {};
		tmp.me = this;
		tmp.statusId_stockchecked =['4', '5', '6', '7', '8'];
		tmp.statusId_stockchecked_not_passed =['4', '6'];
		
		tmp.hasCheckedStock = (tmp.statusId_stockchecked.indexOf(row.status.id) >= 0);
		tmp.stockChkedWIssues = (tmp.statusId_stockchecked_not_passed.indexOf(row.status.id) >= 0);
		return new Element('div')
			.insert({'bottom': tmp.me._getTitledDiv('', 
					!tmp.hasCheckedStock ? '' : new Element('span', {'class': 'icon'})
						.addClassName(!tmp.stockChkedWIssues ? 'ok' : 'warning').writeAttribute('title', tmp.stockChkedWIssues ? 'insufficient stock' :'Stocked checked' )
			) })
			.insert({'bottom': !tmp.hasCheckedStock ? '' : tmp.me._getTitledDiv(new Element('span', {'class': 'icon'}), new Element('span', {'class': 'tooltipTarget'}).update('view items').writeAttribute('title', 'click to view order items')
					.observe('click', function() {
						tmp.me._openDetailPage(row);
					})
			).addClassName('vieworderitems') });
	}
	
	,_getWarehouseCell: function(row) {
		var tmp = {};
		tmp.me = this;
		tmp.statusId_whchecked =['6', '7', '8'];
		tmp.statusId_whchecked_not_passed =['6'];
		
		tmp.hasChecked = (tmp.statusId_whchecked.indexOf(row.status.id) >= 0);
		tmp.chkedWIssues = (tmp.statusId_whchecked_not_passed.indexOf(row.status.id) >= 0);
		return new Element('div')
			.insert({'bottom': tmp.me._getTitledDiv('', 
					!tmp.hasChecked ? '' : new Element('span', {'class': 'icon'})
						.addClassName(!tmp.chkedWIssues ? 'ok' : 'warning').writeAttribute('title', tmp.chkedWIssues ? 'insufficient stock' : 'Stock Handled successfully!' )
			) })
			.insert({'bottom': !tmp.hasChecked ? '' : tmp.me._getTitledDiv(new Element('span', {'class': 'icon'}), new Element('span', {'class': 'tooltipTarget'}).update('view items').writeAttribute('title', 'click to view order items')
					.observe('click', function() {
						tmp.me._openDetailPage(row);
					})
			).addClassName('vieworderitems') });
	}
		
	,_getResultRow: function(row, isTitle) {
		var tmp = {};
		tmp.me = this;
		tmp.isTitle = (isTitle || false);
		
		return new Element('div', {'class': 'row', 'order_id' : row.id}).store('data', row)
			.insert({'bottom': new Element('span', {'class': 'cell orderInfo'}).update(
				tmp.isTitle ? row.orderNo : tmp.me._getOrderInfoCell(row)
			) })
			.insert({'bottom': new Element('span', {'class': 'cell shippingAddr'}).update(
					tmp.isTitle ? row.shippingAddr : tmp.me._getAddrDiv(row.address.shipping)
			) })
			.insert({'bottom': new Element('span', {'class': 'cell status', 'order_status': row.status.name}).update(
					row.status ? row.status.name : ''
			) })
			.insert({'bottom': new Element('span', {'class': 'cell payment'}).update(
				tmp.isTitle ? 'Payments' : tmp.me._getPaymentCell(row)
			) })		
			.insert({'bottom': new Element('span', {'class': 'cell purchasing'}).update(
					tmp.isTitle ? 'Purchasing' : tmp.me._getPurchasingCell(row)
			) })
			.insert({'bottom': new Element('span', {'class': 'cell warehouse'}).update(
					tmp.isTitle ? 'Warehouse' : tmp.me._getWarehouseCell(row)
			) });
	}
});