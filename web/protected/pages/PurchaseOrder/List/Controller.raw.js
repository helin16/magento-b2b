/**
 * The page Js file
 */
var PageJs = new Class.create();

PageJs.prototype = Object.extend(new CRUDPageJs(), {
	_getTitleRowData: function() {
		return {'totalAmount': 'PO Amount Inc. GST', 'totalReceivedValue': 'Bill Amount Inc. GST', 'totalPaid': 'Total Paid', 'purchaseOrderNo': 'PO Number', 'supplier': {'name': 'Supplier'}, 'status': 'Status', 'supplierRefNo': 'PO Ref.', 'orderDate': 'Order Date', 'active': 'Active'};

	}
	,_loadChosen: function () {
		jQuery(".chosen").chosen({
			disable_search_threshold: 10,
			no_results_text: "Oops, nothing found!",
			width: "95%"
		});
		jQuery('.chosen-container input[type="text"]').keydown(function(event) {
		  if(event.which == 13 || event.keyCode == 13) {
			event.preventDefault();
			$('searchPanel').down('#searchBtn').click();
		  }
		});
		return this;
	}
	/**
	 * Binding the search key
	 */
	,_bindSearchKey: function() {
		var tmp = {};
		tmp.me = this;
		$$('#searchBtn').first()
			.observe('click', function(event) {
				if(!$$('#showSearch').first().checked)
					$$('#showSearch').first().click();
				else
					tmp.me.getSearchCriteria().getResults(true, tmp.me._pagination.pageSize);
			});
		tmp.selectEl = new Element('input', {'class': 'select2 form-control', 'data-placeholder': 'the Name of Supplier', 'search_field': 'po.supplierIds'}).insert({'bottom': new Element('option').update('')});
		$(tmp.me.searchDivId).down('[search_field="po.supplierIds"]').replace(tmp.selectEl);
		jQuery('.select2[search_field="po.supplierIds"]').select2({
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
		tmp.selectEl = new Element('select', {'class': 'select2 form-control', 'multiple': true, 'data-placeholder': 'the Status of PO', 'search_field': 'po.status'});
		tmp.me._status.each(function(item){
			tmp.selectEl.insert({'bottom': new Element('option', {'value': item}).update(item)});
		});
		$(tmp.me.searchDivId).down('[search_field="po.status"]').replace(tmp.selectEl);
		jQuery('.select2[search_field="po.status"]').select2({
			allowClear: true,
			hidden: true,
		});
		tmp.selectEl = new Element('input', {'class': 'select2 form-control', 'data-placeholder': 'search for a Products', 'search_field': 'pro.ids'}).insert({'bottom': new Element('option').update('')});
		$('searchDiv').down('[search_field="pro.ids"]').replace(tmp.selectEl);
		jQuery('.select2[search_field="pro.ids"]').select2({
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
						pageSize: 10,
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
			minimumInputLength: 3,
		});
		// bind search key
		$('searchDiv').getElementsBySelector('[search_field]').each(function(item) {
			item.observe('keydown', function(event) {
				tmp.me.keydown(event, function() {
					$(tmp.me.searchDivId).down('#searchBtn').click();
				});
			})
		});
		return this;
	}
	,_loadDataPicker: function () {
		$$('.datepicker').each(function(item){
			new Prado.WebUI.TDatePicker({'ID': item, 'InputMode':"TextBox",'Format':"yyyy-MM-dd 00:00:00",'FirstDayOfWeek':1,'CalendarStyle':"default",'FromYear':2009,'UpToYear':2024,'PositionMode':"Bottom", "ClassName": 'datepicker-layer-fixer'});
		});
		return this;
	}
	,getResults: function(reset, pageSize) {
		var tmp = {};
		tmp.me = this;
		tmp.reset = (reset || false);
		tmp.resultDiv = $(tmp.me.resultDivId);

		if(tmp.reset === true)
			tmp.me._pagination.pageNo = 1;
		tmp.me._pagination.pageSize = (pageSize || tmp.me._pagination.pageSize);
		tmp.me.postAjax(tmp.me.getCallbackId('getItems'), {'pagination': tmp.me._pagination, 'searchCriteria': tmp.me._searchCriteria}, {
			'onLoading': function () {
				jQuery('#' + tmp.me.searchDivId + ' #searchBtn').button('loading');
				//reset div
				if(tmp.reset === true) {
					tmp.resultDiv.update( new Element('tr').update( new Element('td').update( tmp.me.getLoadingImg() ) ) );
				}
			}
			,'onSuccess': function(sender, param) {
				try{
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result)
						return;
					$(tmp.me.totalNoOfItemsId).update(tmp.result.pageStats.totalRows);

					//reset div
					if(tmp.reset === true) {
						tmp.resultDiv.update(tmp.me._getResultRow(tmp.me._getTitleRowData(), true).wrap(new Element('thead')));
					}
					//remove next page button
					tmp.resultDiv.getElementsBySelector('.paginWrapper').each(function(item){
						item.remove();
					});

					//show all items
					tmp.tbody = $(tmp.resultDiv).down('tbody');
					if(!tmp.tbody)
						$(tmp.resultDiv).insert({'bottom': tmp.tbody = new Element('tbody') });
					tmp.result.items.each(function(item) {
						item.item.totalProdcutCount = item.totalProdcutCount;
						item = item.item;
						tmp.tbody.insert({'bottom': tmp.me._getResultRow(item).addClassName('item_row').writeAttribute('item_id', item.id) });
					});
					//show the next page button
					if(tmp.result.pageStats.pageNumber < tmp.result.pageStats.totalPages)
						tmp.resultDiv.insert({'bottom': tmp.me._getNextPageBtn().addClassName('paginWrapper') });
				} catch (e) {
					tmp.resultDiv.insert({'bottom': tmp.me.getAlertBox('Error', e).addClassName('alert-danger') });
				}
			}
			,'onComplete': function() {
				jQuery('#' + tmp.me.searchDivId + ' #searchBtn').button('reset');
			}
		});
	}
	,_deactivateItem: function(po) {
		var tmp = {};
		tmp.me = this;
		tmp.row = $$('[item_id="'+ po.id +'"]').first();
		tmp.me.postAjax(tmp.me.getCallbackId('deactivateItems'), {'item_id': po.id}, {
			'onLoading': function() {
				if(tmp.row)
					tmp.row.hide();
				tmp.me.hideModalBox();
			}
			,'onSuccess': function(sender, param){
				try {
					tmp.row.toggleClassName('danger');
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result.item)
						throw 'errror';
					tmp.row.replace(tmp.me._getResultRow(tmp.result.item, false));
				} catch(e) {
					tmp.me.showModalBox('<span class="text-danger">ERROR</span>', e, true);
				}
			}
			,'onComplete': function() {
				if(tmp.row)
					tmp.row.show();
			}
		});
	}
	/**
	 * showing the confirmation panel for deleting the po
	 */
	,_shoConfirmDel: function(purchaseorder) {
		var tmp = {};
		tmp.me = this;
		tmp.confirmDiv = new Element('div')
			.insert({'bottom': new Element('strong').update('You are about to delete a Purchase Order: ' + purchaseorder.purchaseOrderNo) })
			.insert({'bottom': new Element('strong').update('After confirming deletion:') })
			.insert({'bottom': new Element('ul')
				.insert({'bottom': new Element('li').update(' - All received item will be deleted, and stock will be reverted from StockOnHand to StockOnPO.') })
				.insert({'bottom': new Element('li').update(' - This PO will be dactivated.') })
			})
			.insert({'bottom': new Element('div').update(new Element('strong').update('Are you sure you want to continue?')) })
			.insert({'bottom': new Element('div')
				.insert({'bottom': new Element('span', {'class': 'btn btn-danger'})
					.update('YES, deactivate it')
					.observe('click', function(){
						tmp.me._deactivateItem(purchaseorder);
					})
				})
				.insert({'bottom': new Element('span', {'class': 'btn btn-default pull-right'})
					.update('NO, cancel this')
					.observe('click', function(){
						tmp.me.hideModalBox();
					})
				})
			});
		tmp.me.showModalBox('<strong class="text-warning">Confirm</strong>', tmp.confirmDiv);
		return tmp.me;
	}
	/**
	 * Getting each row for displaying the result list
	 */
	,_getResultRow: function(row, isTitle) {
		var tmp = {};
		tmp.me = this;
		tmp.tag = (tmp.isTitle === true ? 'th' : 'td');
		tmp.isTitle = (isTitle || false);
		tmp.invoiceNoDiv = new Element('div');
		if(!isTitle)
			row.supplierInvoices.each(function(item){
				tmp.invoiceNoDiv.insert({'bottom': new Element('div').update(item)})
			});
		tmp.row = new Element('tr', {'class': (tmp.isTitle === true ? 'item_top_row' : 'btn-hide-row item_row po_item') + (row.active == 0 ? ' danger' : ''), 'item_id': (tmp.isTitle === true ? '' : row.id)}).store('data', row)
			.insert({'bottom': new Element(tmp.tag, {'class': 'purchaseOrderNo col-xs-1'}).update( tmp.isTitle ? row.purchaseOrderNo :
				new Element('a', {'href': '/purchase/' + row.id + '.html', 'target': '_BLANK'})
					.update(row.purchaseOrderNo)
			) })
			.insert({'bottom': new Element(tmp.tag, {'class': ' col-xs-1'}).update(tmp.me.loadUTCTime(row.orderDate).toLocaleString())})
			.insert({'bottom': new Element(tmp.tag, {'class': ' col-xs-1'}).update(row.supplier.name ? row.supplier.name : '')})
			.insert({'bottom': new Element(tmp.tag, {'class': ' col-xs-1'}).update(!tmp.isTitle ? row.totalPaid ? tmp.invoiceNoDiv : '' : 'Invoice No(s)')})
			.insert({'bottom': new Element(tmp.tag, {'class': ' col-xs-1'}).update(row.supplierRefNo ? row.supplierRefNo : '')})
			.insert({'bottom': new Element(tmp.tag, {'class': ' col-xs-1'}).update(tmp.isTitle ? 'Products Count' :
				new Element('a', {'href': '/serialnumbers.html?purchaseorderid=' + row.id, 'target': '_BLANK' })
					.insert({'bottom': new Element('abbr',{'title': 'Received Product Count on this PO'}).update(row.totalReceivedCount) })
					.insert({'bottom': new Element('span').update(' / ') })
					.insert({'bottom': new Element('abbr', {'title': 'Total Product Count on this PO'}).update(row.totalProductCount) })

			) })
			.insert({'bottom': new Element(tmp.tag, {'class': ' col-xs-1'}).update(!tmp.isTitle ? row.eta ? tmp.me.loadUTCTime(row.eta).toDateString() : '' : 'ETA')})
			.insert({'bottom': new Element(tmp.tag, {'class': ' col-xs-1'}).update(!tmp.isTitle ? tmp.me.getCurrency(row.totalAmount) : row.totalAmount)})
			.insert({'bottom': new Element(tmp.tag, {'class': ' col-xs-1'}).update(!tmp.isTitle ? tmp.me.getCurrency(row.totalReceivedValue * 1.1) : row.totalReceivedValue)})
			.insert({'bottom': new Element(tmp.tag, {'class': ' col-xs-1'}).update(!tmp.isTitle ? row.totalPaid ? tmp.me.getCurrency(row.totalPaid) : '' : 'Total Paid')})
			.insert({'bottom': new Element(tmp.tag, {'class': ' col-xs-1', 'order_status': row.status}).update(row.status)})
			.insert({'bottom': tmp.btns = new Element(tmp.tag, {'class': 'col-xs-1 text-right'}) 	});
		if(tmp.isTitle !== true)
			tmp.btns.insert({'bottom': new Element('div', {'class': 'btn-group'})
				.insert({'bottom': (!(row.id && (row.status === 'ORDERED' || row.status === 'RECEIVING')) || row.active !== true)  ? '' : new Element('a', {'class': 'btn btn-success btn-xs', 'href': '/receiving/' + row.id + '.html', 'target': '_BLANK', 'title': 'Receiving Items'})
					.update('Receiving')
				})
				.insert({'bottom': new Element('a', {'class': 'btn btn-default btn-xs', 'title': 'Edit', 'href': '/purchase/' + row.id + '.html', 'target': '_BLANK'})
					.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-pencil'}) })
				})
				.insert({'bottom': new Element('span', {'class': 'btn btn-danger btn-xs', 'title': 'Delete'})
					.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-trash'}) })
					.observe('click', function(){
						tmp.me._shoConfirmDel(row);
					})
				})
			});
		return tmp.row;
	}
});