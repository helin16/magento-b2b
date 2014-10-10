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
		jQuery(".chosen").chosen({
			disable_search_threshold: 10,
			no_results_text: "Oops, nothing found!",
			width: "95%"
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
		tmp.reset = (reset || false);
		if(tmp.me._searchCriteria === null)
		{
			tmp.me.showModalBox('Warning', 'Nothing to search!', true);
			return;
		}
		if(tmp.reset === true)
			tmp.me._pagination.pageNo = 1;
		tmp.me._pagination.pageSize = (pageSize || tmp.me._pagination.pageSize);
		tmp.me.postAjax(tmp.me.getCallbackId('getOrders'), {'pagination': tmp.me._pagination, 'searchCriteria': tmp.me._searchCriteria}, {
			'onLoading': function () {
				jQuery('#searchBtn').button('loading');
				jQuery('.popovershipping').popover('hide');
				if(tmp.reset === true) {
					$(tmp.me.totalNoOfItemsId).update('0');
					$(tmp.me.resultDivId).update('').insert({'after': new Element('div', {'class': 'panel-body'}).update(tmp.me.getLoadingImg()) });
				}
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
						tmp.resultDiv.update(tmp.me._getResultRow(tmp.titleRow, true).wrap(new Element('thead')));
					}
					//remove next page button
					tmp.resultDiv.getElementsBySelector('.paginWrapper').each(function(item){
						item.remove();
					})
					
					tmp.tbody = $(tmp.resultDiv).down('tbody');
					if(!tmp.tbody)
						$(tmp.resultDiv).insert({'bottom': tmp.tbody = new Element('tbody') });
					//show all next page items
					tmp.result.items.each(function(item) {
						tmp.tbody.insert({'bottom': tmp.me._getResultRow(item) });
					})
					//show the next page button
					if(tmp.result.pageStats.pageNumber < tmp.result.pageStats.totalPages)
						tmp.resultDiv.insert({'bottom': tmp.me._getNextPageBtn().addClassName('paginWrapper') });
					
					tmp.resultDiv.getElementsBySelector('.popovershipping.newPopover').each(function(item) {
						item.removeClassName('newPopover');
						tmp.rowData = item.up('.order_item').retrieve('data');
						jQuery('#' + item.id).popover({
							'container': 'body',
							'title': '<div class="row"><div class="col-xs-10">Details for: ' + tmp.rowData.orderNo + '</div><div class="col-xs-2"><a class="pull-right" href="javascript:void(0);" onclick="jQuery(' + "'#" + item.id + "'" + ').popover(' + "'hide'" + ');"><strong>&times;</strong></a></div></div>',
							'content':  jQuery('.popover_content', jQuery('#' + item.id)).html(),
							'html': true,
							'placement': 'right',
							'trigger': 'manual'
						})
					})
					
				} catch (e) {
					tmp.me.showModalBox('Error', e, true);
				}
			}
			,'onComplete': function() {
				jQuery('#searchBtn').button('reset');
				if($(tmp.me.resultDivId).up('.panel').down('.panel-body'))
					$(tmp.me.resultDivId).up('.panel').down('.panel-body').remove();
			}
		});
	}
	
	,_getNextPageBtn: function() {
		var tmp = {}
		tmp.me = this;
		return new Element('tfoot')
			.insert({'bottom': new Element('tr')
				.insert({'bottom': new Element('td', {'colspan': '5', 'class': 'text-center'})
					.insert({'bottom': new Element('span', {'class': 'btn btn-primary', 'data-loading-text':"Fetching more results ..."}).update('Show More')
						.observe('click', function() {
							tmp.me._pagination.pageNo = tmp.me._pagination.pageNo*1 + 1;
							jQuery(this).button('loading');
							tmp.me.getResults();
						})
					})
				})
			});
	}
	
	,_getOrderDetailsDiv: function(order) {
		var tmp = {};
		tmp.me = this;
		tmp.custName = order.infos[tmp.me._infoTypes['custName']][0].value;
		tmp.custEmail = order.infos[tmp.me._infoTypes['custEmail']][0].value;
		return new Element('div')
			.insert({'bottom': new Element('div').update('<span class="glyphicon glyphicon-user" title="Customer Name"></span>: ' + tmp.custName) })
			.insert({'bottom': new Element('div').update('<span class="glyphicon glyphicon-envelope" title="Customer Email"></span>: <a href="mailto:' + tmp.custEmail + '">' + tmp.custEmail + '</a>') })
			.insert({'bottom': new Element('div').update('<span class="glyphicon glyphicon-shopping-cart" title="Order Date"></span>: ' + order.orderDate) })
			.insert({'bottom': new Element('div').update('<strong>Shipping</strong>:') })
			.insert({'bottom': new Element('div').update('<span class="glyphicon glyphicon-user" title="Customer Name"></span>: ' + order.address.shipping.contactName)	})
			.insert({'bottom': new Element('div').update('<span class="glyphicon glyphicon-phone-alt" title="Phone"></span>: ' + order.address.shipping.contactNo)	})
			.insert({'bottom': new Element('div').update('<span class="glyphicon glyphicon-map-marker" title="Address"></span>: ' + order.address.shipping.full)	})
			;
	}
	
	,_getTitledDiv: function(title, content) {
		return new Element('div', {'class': 'field_div'})
			.insert({'bottom': new Element('span', {'class': 'inlineblock title'}).update(title) })
			.insert({'bottom': new Element('span', {'class': 'inlineblock divcontent'}).update(content) });
	}
	
	,_openDetailsPage: function(row) {
		var tmp = {};
		tmp.me = this;
		jQuery.fancybox({
			'width'			: '95%',
			'height'		: '95%',
			'autoScale'     : false,
			'autoDimensions': false,
			'fitToView'     : false,
			'autoSize'      : false,
			'type'			: 'iframe',
			'href'			: '/orderdetails/' + row.id + '.html',
			'beforeClose'	    : function() {
				if($(tmp.me.resultDivId).down('.order_item[order_id=' + row.id + ']'))
					$(tmp.me.resultDivId).down('.order_item[order_id=' + row.id + ']').replace(tmp.me._getResultRow($$('iframe.fancybox-iframe').first().contentWindow.pageJs._order));
			}
 		});
		return tmp.me;
	}
	
	,_getOpenDetailBtn: function(row) {
		var tmp = {};
		tmp.me = this;
		return new Element('a', {'href': 'javascript: void(0)', 'title': 'Click to view the order'})
			.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-new-window'}) })
			.observe('click', function() {
				tmp.me._openDetailsPage(row);
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
		return new Element('div')
			.insert({'bottom': tmp.me._getOpenDetailBtn(row) })
			.insert({'bottom': ' '})
			.insert({'bottom':  new Element('span')
				.insert({'bottom': new Element('a', {'id': 'orderno-btn-' + row.id, 'class': 'orderNo visible-xs visible-sm visible-md visible-lg newPopover popovershipping', 'href': 'javascript:void(0);'})
					.update(row.orderNo) 
					.insert({'bottom': new Element('div', {'style': 'display: none;', 'class': 'popover_content'}).update(tmp.me._getOrderDetailsDiv(row)) })
					.observe('click', function() {
						jQuery(this).popover('show');
						jQuery('.popovershipping').not(this).popover('hide');
					})
					.observe('dblclick', function() {
						jQuery(this).popover('hide');
						tmp.me._openDetailsPage(row);
					})
				})
			})
		;
	}
	
	,_getPaymentCell: function(row) {
		var tmp = {};
		tmp.me = this
		return new Element('a', {'href': 'javascript: void(0);'})
			.insert({'bottom': ( !row.passPaymentCheck ? '' : 
					new Element('span', {'title': (row.totalDue === 0 ? 'Full Paid' : 'Short Paid'), 'class': (row.totalDue === 0 ? 'text-success' : 'text-danger') })
						.update(new Element('span', {'class': 'glyphicon ' + (row.totalDue === 0 ? 'glyphicon-ok-sign' : 'glyphicon-warning-sign') }))
				) })
				.insert({'bottom': " " })
				.insert({'bottom': new Element('span')
					.update(tmp.me.getCurrency(row.totalDue))
					.writeAttribute('title', 'Total Due Amount:' + tmp.me.getCurrency(row.totalDue))  
				})
				.observe('click', function() {
					tmp.me._openDetailsPage(row);
				});
	}
	
	,_getPurchasingCell: function(row) {
		var tmp = {};
		tmp.me = this;
		tmp.statusId_stockchecked =['4', '5', '6', '7', '8'];
		tmp.statusId_stockchecked_not_passed =['4', '6'];
		
		tmp.hasCheckedStock = (tmp.statusId_stockchecked.indexOf(row.status.id) >= 0);
		tmp.stockChkedWIssues = (tmp.statusId_stockchecked_not_passed.indexOf(row.status.id) >= 0);
		return new Element('div')
			.insert({'bottom': (!tmp.hasCheckedStock ? '' : 
				new Element('a', {'href': 'javascript: void(0);', 'class': (!tmp.stockChkedWIssues ? 'text-success' : 'text-danger'), 'title': (!row.stockChkedWIssues ? 'Stock checked' : 'insufficient stock')})
					.update(new Element('span', {'class': 'glyphicon ' + (!tmp.stockChkedWIssues ? 'glyphicon-ok-sign' : 'glyphicon-warning-sign') }))
					.observe('click', function() {
						tmp.me._openDetailsPage(row);
					})
			 ) })
			;
	}
	
	,_getWarehouseCell: function(row) {
		var tmp = {};
		tmp.me = this;
		tmp.statusId_whchecked =['6', '7', '8'];
		tmp.statusId_whchecked_not_passed =['6'];
		
		tmp.hasChecked = (tmp.statusId_whchecked.indexOf(row.status.id) >= 0);
		tmp.chkedWIssues = (tmp.statusId_whchecked_not_passed.indexOf(row.status.id) >= 0);
		return new Element('div')
			.insert({'bottom': (!tmp.hasChecked ? '' : 
				new Element('a', {'href': 'javascript: void(0);', 'class': (!tmp.chkedWIssues ? 'text-success' : 'text-danger'), 'title': (!row.chkedWIssues ? 'Stock Handled successfully' : 'insufficient stock')})
					.update(new Element('span', {'class': 'glyphicon ' + (!tmp.chkedWIssues ? 'glyphicon-ok-sign' : 'glyphicon-warning-sign') }))
					.observe('click', function() {
						tmp.me._openDetailsPage(row);
					})
			 ) })
			;
	}
		
	,_getResultRow: function(row, isTitle) {
		var tmp = {};
		tmp.me = this;
		tmp.isTitle = (isTitle || false);
		tmp.row = new Element('tr', {'class': (tmp.isTitle === true ? '' : 'order_item'), 'order_id' : row.id}).store('data', row)
			.insert({'bottom': new Element('td', {'class': 'orderInfo  col-xs-2'}).update(
				tmp.isTitle ? row.orderNo : tmp.me._getOrderInfoCell(row)
			) })
			.insert({'bottom': new Element('td', {'class': 'order-date'}).update(
					tmp.isTitle === true ? 'Order Date' : tmp.me.loadUTCTime(row.orderDate).toLocaleFormat('%d/%b/%Y')
			) })
			.insert({'bottom': new Element('td', {'class': 'status col-middle col-xs-2', 'order_status': row.status.name}).update(
					row.status ? row.status.name : ''
			) })
			.insert({'bottom': new Element('td', {'class': 'text-right', 'payment': true}).update(
				tmp.isTitle ? 'Payments' : tmp.me._getPaymentCell(row)
			) })		
			.insert({'bottom': new Element('td', {'class': 'text-center', 'purchasing': true}).update(
					tmp.isTitle ? 'Purchasing' : tmp.me._getPurchasingCell(row)
			) })
			.insert({'bottom': new Element('td', {'class': 'text-center', 'warehouse': true}).update(
					tmp.isTitle ? 'Warehouse' : tmp.me._getWarehouseCell(row)
			) })
		;
		return tmp.row;
	}
});