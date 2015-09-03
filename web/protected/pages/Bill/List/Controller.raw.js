/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new CRUDPageJs(), {
	_getTitleRowData: function() {
		return {};
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
			'href'			: '/bills/' + row.supplier.id + '.html?blanklayout=1&invoiceNo=' + row.invoiceNo
		});
		return tmp.me;
	}
	,_getShowInvoiceNoDiv: function(row) {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = new Element('div', {'class': 'inovice-input-div'})
			.insert({'bottom': new Element('a', {'href': 'javascript: void(0);'})
				.update(row.invoiceNo)
				.observe('click', function() {
					tmp.me._openDetailsPage(row);
				})
			})
			.insert({'bottom': new Element('span', {'class': 'btn btn-default btn-xs pull-right'})
				.update('edit')
				.observe('click', function() {
					$(this).up('.inovice-input-div').update(tmp.newInput = tmp.me._getInvoiceNoInputBox(row));
					tmp.newInput.select();
				})
			});
		return tmp.newDiv;
	}
	,_getInvoiceNoInputBox: function(row) {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = new Element('div')
			.insert({'bottom': new Element('input', {'class': 'form-control input-sm', 'value': row && row.invoiceNo ? row.invoiceNo : ''}) 
				.observe('change', function() {
					tmp.inputBox = $(this);
					tmp.newInvoiceNo = $F(tmp.inputBox);
					if(tmp.newInvoiceNo.blank()) {
						alert('Invoice No. can NOT be empty!');
						return;
					}
					tmp.me.postAjax(tmp.me.getCallbackId('updateInvoiceNo'), {'oldInvoiceNo': row.invoiceNo, 'newInoviceNo': tmp.newInvoiceNo, 'supplierId': row.supplier.id}, {
						'onCreate': function() {}
						,'onSuccess': function(sender, param){
							try {
								tmp.result = tmp.me.getResp(param, false, true);
								if(!tmp.result)
									return;
								row.invoiceNo = tmp.newInvoiceNo;
								$(tmp.inputBox).up('.inovice-input-div').replace(tmp.me._getShowInvoiceNoDiv(row));
							} catch (e) {
								tmp.me.showModalBox('<strong class="text-danger">Error</strong>', new Element('h3').update(e));
							}
						}
						,'onComplete': function(){}
					});
				})
			});
		return tmp.newDiv;
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
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-sm-1'}).update(tmp.isTitle === true ? 'Inv. No.' : tmp.me._getShowInvoiceNoDiv(row) ) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-sm-1'}).update(tmp.isTitle === true ? 'Created' : moment(tmp.me.loadUTCTime(row.created)).format('ll')) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-sm-2'}).update(tmp.isTitle === true ? 'Supplier' : row.supplier.name) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-sm-2'}).update(tmp.isTitle === true ? 'Total Qty' : row.totalQty) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-sm-2'}).update(tmp.isTitle === true ? 'Total Price(ex GST)' : tmp.me.getCurrency(row.totalPrice)) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-sm-2'}).update(tmp.isTitle === true ? 'Total Price(inc GST)' : tmp.me.getCurrency(row.totalPrice * 1.1)) })
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
							 searchTxt: params // search term
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
				 minimumInputLength: 1
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
						searchParams: {'poNo': '%' + params + '%'} // search term
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
			minimumInputLength: 1
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