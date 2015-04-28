/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new CRUDPageJs(), {
	_getTitleRowData: function() {
		return {};
	}
	/**
	 * Getting each row for displaying the result list
	 */
	,_getResultRow: function(row, isTitle) {
		var tmp = {};
		tmp.me = this;
		tmp.tag = (tmp.isTitle === true ? 'th' : 'td');
		tmp.isTitle = (isTitle || false);

		tmp.row = new Element('tr', {'class': (tmp.isTitle === true ? '' : 'item_row')})
			.store('data', row)
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-sm-2'}).update(tmp.isTitle === true ? 'Inv. No.' : row.invoiceNo) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-sm-2'}).update(tmp.isTitle === true ? 'Supplier' : row.supplier.name) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-sm-2'}).update(tmp.isTitle === true ? 'Total Qty' : row.totalQty) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-sm-2'}).update(tmp.isTitle === true ? 'Total Price' : tmp.me.getCurrency(row.totalPrice)) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-sm-4'}).update(tmp.isTitle === true ? 'Purchase Orders' :
				tmp.purchaseOrderList = new Element('ul', {'class': 'list-inline'})
			) });
		if(row.purchaseOrders && tmp.purchaseOrderList) {
			row.purchaseOrders.each(function(po){
				tmp.purchaseOrderList.insert({'bottom': new Element('li')
					.insert({'bottom': new Element('a', {'target': '_BLANK', 'href': '/purchase/' + po.id + '.html'}).update(po.purchaseOrderNo) })
				})
			});
		}
		return tmp.row;
	}
	,_initSelect2_Supplier: function() {
		var tmp = {};
		tmp.me = this;
		jQuery('.select2[search_field="supplierIds"]').select2({
			allowClear: true,
			hidden: true,
			multiple: true,
			 ajax: { url: "/ajax/getSuppliers",
					 dataType: 'json',
					 delay: 10,
					 data: function (params) {
						 return {
							 searchTxt: params, // search term
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
				 minimumInputLength: 1,
		});
		return tmp.me;
	}
	,_initSelect2_po: function() {
		var tmp = {};
		tmp.me = this;
		jQuery('.select2[search_field="purchaseOrderIds"]').select2({
			allowClear: true,
			hidden: true,
			multiple: true,
			ajax: {
				url: "/ajax/getAll",
				dataType: 'json',
				delay: 10,
				data: function (params) {
					return {
						entityName: 'PurchaseOrder',
						searchTxt: 'po.purchaseOrderNo like :poNo',
						searchParams: {'poNo': '%' + params + '%'}, // search term
					};
				},
				results: function (data) {
					tmp.result = [];
					data.resultData.items.each(function(item){
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
				return '<div value=' + result.data.id + '>' + result.data.purchaseOrderNo + ' (' + result.data.supplier.name + ')</div >';
			},
			escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
			minimumInputLength: 1,
		});
		return tmp.me;
	}
	,init: function() {
		var tmp = {};
		tmp.me = this;
		tmp.me._initSelect2_Supplier()
			._initSelect2_po();
		$$('#searchBtn').first()
			.observe('click', function(event) {
				if(!$$('#showSearch').first().checked)
					$$('#showSearch').first().click();
				else
					tmp.me.getSearchCriteria().getResults(true, tmp.me._pagination.pageSize);
			});
		return tmp.me;
	}
});