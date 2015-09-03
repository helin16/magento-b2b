/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new BPCPageJs(), {
	_resultDivId: '' //the html id of the result div
	,_searchDivId: '' //the html id of the search div
	,_pagination: {'pageNo': 1, 'pageSize': 30} //the pagination details
	,_searchCriteria: {} //the searching criteria
	
	,_bindSearchKey: function() {
		var tmp = {}
		tmp.me = this;
		$(tmp.me._searchDivId).getElementsBySelector('[search_field]').each(function(item) {
			item.observe('keydown', function(event) {
				tmp.me.keydown(event, function() {
					$(tmp.me._searchDivId).down('.search_btn').click();
				});
			})
		});
		return this;
	}
	
	,init: function(resultDivId, searchDivId) {
		this._resultDivId = resultDivId;
		this._searchDivId = searchDivId;
		this._bindSearchKey();
		return this;
	}
	
	,setSearchCriteria: function(criteria) {
		var tmp = {}
		tmp.me = this;
		$(tmp.me._searchDivId).getElementsBySelector('[search_field]').each(function(item) {
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
		$(tmp.me._searchDivId).getElementsBySelector('[search_field]').each(function(item) {
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
		tmp.isTitle = (isTitle || false);
		tmp.newDiv = new Element('tr', {'class': 'item_row', 'item_id': row.id})
			.store('data', row)
			.insert({'bottom': new Element('td', {'class': 'productname'}).update(row.product.name) })
			.insert({'bottom': new Element('td', {'class': 'productsku'}).update(row.product.sku) })
			.insert({'bottom': new Element('td', {'class': 'orderno'}).update(
					tmp.isTitle ? 
					row.order.orderNo : 
					new Element('a', {'href': 'javascript: void(0);', 'class': 'orderNolink'}).update(row.order.orderNo).observe('click', function() {
						jQuery.fancybox({
							'width'			: '80%',
							'height'		: '90%',
							'autoScale'     : true,
							'type'			: 'iframe',
							'href'			: '/orderdetails/' + row.order.id + '.html',
							'beforeClose'	    : function() {
								if(!$$('iframe.fancybox-iframe') || !$$('iframe.fancybox-iframe').first().contentWindow || !$$('iframe.fancybox-iframe').first().contentWindow.pageJs) {
									return;
								}
								
								tmp.items = $$('iframe.fancybox-iframe').first().contentWindow.pageJs._orderItems;
								if(tmp.items && tmp.items.size() >0) {
									tmp.items.each(function(item) {
										tmp.itemRow = $(tmp.me._resultDivId).down('.row[item_id=' + row.id + ']');
										if(tmp.itemRow)
											tmp.itemRow.replace(tmp.me._getResultRow(item));
									});
								}
							}
				 		});
					})
			) })
			.insert({'bottom': new Element('td', {'class': 'orderstatus', 'order_status': row.order.status.name}).update(row.order.status.name) })
			.insert({'bottom': new Element('td', {'class': 'qty'}).update(row.qtyOrdered) })
			.insert({'bottom': new Element('td', {'class': 'isordered'}).update(isTitle === true ? row.isOrdered : (row.isOrdered ? new Element('span', {'class': 'ticked inlineblock'}) : '')) })
			.insert({'bottom': new Element('td', {'class': 'eta'}).update(row.eta) })
			.insert({'bottom': new Element('td', {'class': 'comments'}).update(
				isTitle === true ? 
				'': 
				new Element('a', {'href': 'javascript: void(0);', 'class': 'popovercomments visible-xs visible-sm visible-md visible-lg'})
					.update( new Element('span', {'class': 'glyphicon glyphicon-comment'}) )
					.observe('click', function(e) {
						jQuery('.popovercomments').not(this).popover('hide');
					})
			) })
		return tmp.newDiv;
	}
	
	,_getNextPageBtn: function() {
		var tmp = {}
		tmp.me = this;
		return new Element('tr')
			.insert({'bottom': new Element('td', {'colspan': '8', 'class': 'text-center'})
				.insert({'bottom': new Element('span', {'class': 'btn btn-success searching_btn', 'data-loading-text': 'Getting more ...'}).update('Show More')
					.observe('click', function() {
						tmp.me._pagination.pageNo = tmp.me._pagination.pageNo*1 + 1;
						tmp.me.getResults();
					})
				})
			});
	}
	
	,_getCommentsDiv: function(orderItem) {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = '';
		orderItem.comments.each(function(comments){
			tmp.newDiv += '<div class="list-group-item">';
				tmp.newDiv += '<span class="badge">' + comments.type + '</span>';
				tmp.newDiv += '<strong class="list-group-item-heading"><small>' + comments.createdBy.person.fullname + '</small></strong>: ';
				tmp.newDiv += '<p><small><em> @ ' + comments.created + '</em></small><br /><small>' + comments.comments + '</small></p>';
			tmp.newDiv += '</div>';
		})
		return tmp.newDiv;
	}
	
	,getResults: function (reset, pageSize) {
		var tmp = {};
		tmp.me = this;
		if(tmp.me._searchCriteria === null || tmp.me._searchCriteria === {}) {
			alert('Nothing to search!');
			return;
		}
		if(reset === true) {
			$(tmp.me._resultDivId).update('');
			$(tmp.me._resultDivId).up('.panel').down('.total_no_of_items').update('0');
		}
		
		tmp.resultDiv = $(tmp.me._resultDivId).down('table.table > tbody');
		if(!tmp.resultDiv){
			$(tmp.me._resultDivId).update( new Element('table', {'class': 'table'}).update( tmp.resultDiv = new Element('tbody') ) ) ;
		}
		tmp.me.postAjax(tmp.me.getCallbackId('getOrderitems'), {'searchCriteria': tmp.me._searchCriteria, 'pagination': tmp.me._pagination}, {
			'onCreate': function (sender, param) {
				jQuery('.searching_btn').button('loading');
			}
			, 'onSuccess': function (sender, param) {
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result || !tmp.result.items)
						return;
					
					//remove next page button
					tmp.resultDiv.getElementsBySelector('.paginWrapper').each(function(item){
						item.remove();
					});
					
					//if reset the result div
					if(reset === true) {
						tmp.resultDiv.insert({'before': 
							tmp.me._getResultRow({'order': {'orderNo': 'Order NO.', 'status': {'name': 'Order Status'}}, 'product': {'sku': 'SKU', 'name': 'Product'}, 'qtyOrdered': 'QTY', 'eta': 'ETA', 'isOrdered': 'Ordered?' }, true)
								.wrap(new Element('thead'))
						})
						$(tmp.me._resultDivId).up('.panel').down('.total_no_of_items').update(tmp.result.pageStats.totalRows);
					}
					
					//add all the items
					tmp.result.items.each(function (item) {
						tmp.resultDiv.insert({'bottom': tmp.me._getResultRow(item) })
						jQuery('.popovercomments', jQuery('.item_row[item_id=' + item.id + ']')).popover({
							container: 'body',
							content: tmp.me._getCommentsDiv(item),
							html: true,
							placement: 'left',
							title: 'Comments:',
							template: '<div class="popover" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content list-group"></div></div>'
						});
					});
					
					//show the next page button
					if(tmp.result.pageStats.pageNumber < tmp.result.pageStats.totalPages)
						tmp.resultDiv.insert({'bottom': tmp.me._getNextPageBtn().addClassName('paginWrapper') });
				} catch(e) {
					$(tmp.me._resultDivId).insert({'bottom': tmp.me.getAlertBox('ERROR: ', e).addClassName('alert-danger').wrap(new Element('div', {'class': 'panel-body'})) })
				}
			}
			,'onComplete': function(sender, param) {
				jQuery('.searching_btn').button('reset');
			}
		});
	}
});