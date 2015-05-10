/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new CRUDPageJs(), {
	_getTitleRowData: function() {
		return {'lastPurchaseTime': "Last Purchase Time", 'comments': 'Comments', 'active': 'Active?'
				,'product': {'sku': 'SKU', 'name': 'Name'}
				, 'receivingItem': {'title': 'ReceivingItem', 'qty': 'Qty'}
				, 'purchaseOrderItem': {'title': 'Purchase Order Item', 'qty': 'Qty'}
				, 'orderItem': {'title': 'Order Item', 'unitCost': 'Unit Cost'}
				, 'creditNoteItem': {'title': 'Credit Note Item', 'qty': 'Qty'}
				, 'productQtyLog': {'title': 'Product Qty Log', 'type': 'Type', 'created': 'Created'}
				};
	}
	,_loadDataPicker: function () {
		$$('.datepicker').each(function(item){
			new Prado.WebUI.TDatePicker({'ID': item, 'InputMode':"TextBox",'Format':"yyyy-MM-dd 00:00:00",'FirstDayOfWeek':1,'CalendarStyle':"default",'FromYear':2009,'UpToYear':2024,'PositionMode':"Bottom", "ClassName": 'datepicker-layer-fixer'});
		});
		return this;
	}
	/**
	 * Binding the search key
	 */
	,_bindSearchKey: function() {
		var tmp = {}
		tmp.me = this;
		$$('#searchBtn').first()
			.observe('click', function(event) {
				if(!$$('#showSearch').first().checked)
					$$('#showSearch').first().click();
				else
					tmp.me.getSearchCriteria().getResults(true, tmp.me._pagination.pageSize);
			});
		$('searchDiv').getElementsBySelector('[search_field]').each(function(item) {
			item.observe('keydown', function(event) {
				tmp.me.keydown(event, function() {
					$(tmp.me.searchDivId).down('#searchBtn').click();
				});
			})
		});
		// search by product
		tmp.selectEl = new Element('input', {'class': 'select2 form-control', 'data-placeholder': 'search for a Products', 'search_field': 'pro.id'}).insert({'bottom': new Element('option').update('')});
		$('searchDiv').down('[search_field="pro.id"]').replace(tmp.selectEl);
		jQuery('.select2[search_field="pro.id"]').select2({
			allowClear: true,
			hidden: true,
			multiple: false,
			ajax: { url: "/ajax/getProducts",
				dataType: 'json',
				delay: 10,
				data: function (params) {
					return {
						searchTxt: params, // search term
						pageNo: 1,
						pageSize: 10
					};
				},
				results: function (data) {
					tmp.result = [];
					data.resultData.items.each(function(item){
						tmp.result.push({"id": item.id, 'text': item.name, 'data': item});
					})
					return {
						results:  tmp.result 
					};
				},
				cache: true
			},
			formatResult : function(result) {
				if(!result)
					return '';
				return '<div value=' + result.data.id + '>' + result.data.name + '</div >';
			},
			escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
			minimumInputLength: 3
		});
		return this;
	}
	/**
	 * Highlisht seleteted row
	 */
	,_highlightSelectedRow : function (btn) {
		var tmp = {};
		tmp.me = this;
		tmp.item = btn.down('.glyphicon-plus') ? '' : $(btn).up('[item_id]').retrieve('data');
		jQuery('.item_row.success').removeClass('success');
		tmp.selectedRow = jQuery('[item_id=' + tmp.item.id + ']')
		.addClass('success');
	}
	,_getLinkAddress: function(path, id, params) {
		var tmp = {};
		tmp.me = this;
		tmp.params = (params || []);
		tmp.paramTxt = '';
		
		if(tmp.params.length > 0) {
			tmp.paramTxt = '?';
			tmp.params.each(function(item){
				tmp.paramTxt = tmp.paramTxt + $H(item).keys()[0] + '=' + $H(item).values()[0] + '&';
			});
			tmp.paramTxt = tmp.paramTxt.substring(0,tmp.paramTxt.length-1);
		}
		
		return '/' + path + (jQuery.isNumeric(id) === true ? ('/' + id) : '') + '.html' + tmp.paramTxt;
	}
	,_getLink: function(path, id, title, url, params) {
		var tmp = {};
		tmp.me = this;
		
		tmp.url = (url || null);
		return new Element('a', {'target': '_blank', 'href': (tmp.url === null ? tmp.me._getLinkAddress(path, id, params) : tmp.url)}).update(title);
	}
	/**
	 * get result row for data given
	 */
	,_getResultRow: function(row, isTitle) {
		var tmp = {};
		tmp.me = this;
		tmp.tag = (tmp.isTitle === true ? 'th' : 'td');
		tmp.isTitle = (isTitle || false);
		
		tmp.row = new Element('tr', {'class': (tmp.isTitle === true ? 'item_top_row' : 'btn-hide-row item_row') + (row.active == 0 ? ' danger' : ''), 'item_id': (tmp.isTitle === true ? '' : row.id)}).store('data', row)
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-1'}).update(tmp.isTitle === true ? 'Product Name' : tmp.me._getLink('product', row.product.id, row.product.name)) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-1'}).update(row.product.sku) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-1'}).update(tmp.isTitle === true ? 'Last Purchase Date (UTC)' : row.productQtyLog.created) })
//			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-1'}).update(moment(tmp.me.loadUTCTime(row.productQtyLog.created)).format('ll')) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-1'}).update(tmp.isTitle === true ? row.receivingItem.title : (row.receivingItem.id ? tmp.me._getLink('productqtylog', null, tmp.me._getTitleRowData().receivingItem.title, null, [{'productid': row.product.id}]) : 'N/A')) })
			;
		return tmp.row;
	}
	,_getNextPageBtn: function() {
		var tmp = {}
		tmp.me = this;
		return new Element('tfoot')
			.insert({'bottom': new Element('tr')
				.insert({'bottom': new Element('td', {'colspan': '8', 'class': 'text-center'})
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
});