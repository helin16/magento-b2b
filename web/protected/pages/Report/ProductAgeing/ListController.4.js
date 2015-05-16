/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new CRUDPageJs(), {
	_getTitleRowData: function() {
		return {};
	}
	,_loadDataPicker: function () {
		jQuery('.datepicker').datetimepicker({
			format: 'DD/MM/YYYY'
		});
		return this;
	}
	,_searchProduct: function () {
		var tmp = {};
		tmp.me = this;
		// search by product
		jQuery('#searchDiv .select2[search_field="pro.ids"]').select2({
			allowClear: true,
			hidden: true,
			multiple: true,
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
						tmp.result.push({"id": item.id, 'text': item.sku, 'data': item});
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
				return '<div class="row"><div class="col-xs-3 select2-word-break" style="word-wrap: break-word;">' + result.data.sku + '</div><div class="col-xs-9">' + result.data.name + '</div></div>';
			},
			escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
			minimumInputLength: 3
		});
		return tmp.me;
	}
	,_searchCategories: function () {
		var tmp = {};
		tmp.me = this;
		jQuery('[search_field="pro.categories"]').select2({
			 minimumInputLength: 3,
			 multiple: true,
			 ajax: {
				 delay: 250
				 ,url: '/ajax/getAll'
		         ,type: 'POST'
	        	 ,data: function (params) {
	        		 return {"searchTxt": 'pro_cate.name like ?', 'searchParams': ['%' + params + '%'], 'entityName': 'ProductCategory', 'pageNo': 1};
	        	 }
				 ,results: function(data, page, query) {
					 tmp.result = [];
					 if(data.resultData && data.resultData.items) {
						 data.resultData.items.each(function(item){
							 tmp.result.push({'id': item.id, 'text': item.namePath, 'data': item});
						 });
					 }
		    		 return { 'results' : tmp.result };
		    	 }
			 }
			,cache: true
			,formatResult : function(result) {
				 if(!result)
					 return '';
				 return '<div class="row order_item"><div class="col-xs-12">' + result.data.namePath + '</div></div >';
			 }
			 ,escapeMarkup: function (markup) { return markup; } // let our custom formatter work
		});
		return tmp.me;
	}
	,_searchPO: function () {
		var tmp = {};
		tmp.me = this;
		// search by product
		jQuery('#searchDiv .select2[search_field="po.id"]').select2({
			allowClear: true,
			hidden: true,
			multiple: false,
			ajax: { url: "/ajax/getAll",
				dataType: 'json',
				delay: 10,
				data: function (params) {
					return {
						searchTxt: 'purchaseOrderNo like ?', // search term
						entityName: 'PurchaseOrder',
						searchParams: ['%' + params + '%'],
						pageNo: 1,
						pageSize: 10
					};
				},
				results: function (data) {
					tmp.result = [];
					data.resultData.items.each(function(item) {
						tmp.result.push({"id": item.id, 'text': item.purchaseOrderNo, 'data': item});
					});
					return {
						results:  tmp.result
					};
				},
				cache: true
			},
			formatResult : function(result) {
				if(!result)
					return '';
				return '<div class="row po_item"><div class="col-xs-3">' + result.data.purchaseOrderNo + '</div><div class="col-xs-2" order_status="' + result.data.status + '">' + result.data.status + '</div><div class="col-xs-6">' + result.data.supplier.name + '</div></div>';
			},
			escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
			minimumInputLength: 3
		});
		return tmp.me;
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
		tmp.me._searchProduct()._searchPO()._searchCategories();
		return tmp.me;
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
		
		tmp.row = new Element('tr', {'class': (tmp.isTitle === true ? 'item_top_row' : 'btn-hide-row item_row') + (row.active == 0 ? ' danger' : ''), 'item_id': (tmp.isTitle === true ? '' : row.id)})
			.store('data', row)
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-1'}).update(tmp.isTitle === true ? 'Product SKU' : tmp.me._getLink('product', row.product.id, row.product.sku) ) })
			.insert({'bottom': new Element(tmp.tag).update(tmp.isTitle === true ? 'Product Name' : tmp.me._getLink('product', row.product.id, row.product.name)) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-2'}).update(tmp.isTitle === true ? 'Last Purchase Date' : moment(tmp.me.loadUTCTime(row.lastPurchaseTime)).format('ll') ) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-1'}).update(tmp.isTitle === true ? 'Qty' : (row.receivingItem.id ? tmp.me._getLink('productqtylog', null, row.product.stockOnHand, null, [{'productid': row.product.id}]) : 'N/A')) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-2'}).update(tmp.isTitle === true ? 'Aged Days' : moment().diff(moment(tmp.me.loadUTCTime(row.lastPurchaseTime)), 'days') ) })
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
							tmp.me._pagination.pageNo = tmp.me._pagination.pageNo * 1 + 1;
							jQuery(this).button('loading');
							tmp.me.getResults();
						})
					})
				})
			});
	}
});