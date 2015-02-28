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
		$('searchDiv').getElementsBySelector('[search_field]').each(function(item) {
			item.observe('keydown', function(event) {
				tmp.me.keydown(event, function() {
					$('searchPanel').down('#searchBtn').click();
				});
			});
		});
		return this;
	}
	,_loadDataPicker: function () {
		$$('.datepicker').each(function(item){
			new Prado.WebUI.TDatePicker({'ID': item, 'InputMode':"TextBox",'Format':"yyyy-MM-dd 00:00:00",'FirstDayOfWeek':1,'CalendarStyle':"default",'FromYear':2009,'UpToYear':2024,'PositionMode':"Bottom", "ClassName": 'datepicker-layer-fixer'});
		});
		return this;
	}
	/**
	 * setting the status options
	 */
	,_setStatusOptions: function(statusOptions) {
		var tmp = {};
		tmp.me = this;
		tmp.selBox = $$('#searchPanel').first().down('#statusSelect');
		tmp.me._statusOptions = statusOptions;
		tmp.me._statusOptions.each(function(status) {
			tmp.selBox.insert({'bottom': new Element('option').update(status) });
		});
		return tmp.me;
	}
	,_loadSuppliers: function(suppliers) {
		var tmp = {};
		tmp.me = this;
		tmp.me.suppliers = suppliers;
		tmp.listBox = $(tmp.me.searchDivId).down('[search_field="po.supplierIds"]');
		tmp.me.suppliers.each(function(supplier) {
			tmp.listBox.insert({'bottom': new Element('option', {'value': supplier.id}).update(supplier.name) });
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
	/**
	 * Getting each row for displaying the result list
	 */
	,_getResultRow: function(row, isTitle) {
		var tmp = {};
		tmp.me = this;
		tmp.tag = (tmp.isTitle === true ? 'th' : 'td');
		tmp.isTitle = (isTitle || false);
		tmp.row = new Element('tr', {'class': (tmp.isTitle === true ? 'item_top_row' : 'btn-hide-row item_row po_item') + (row.active == 0 ? ' danger' : ''), 'item_id': (tmp.isTitle === true ? '' : row.id)}).store('data', row)
			.insert({'bottom': new Element(tmp.tag, {'class': 'purchaseOrderNo col-xs-1'}).update(row.purchaseOrderNo)})
				.observe('dblclick', function(){
					if(!isTitle)
						tmp.me._openEditPage(row);
				})
				.observe('click', function(){
					//tmp.me._highlightSelectedRow(this.down('.btn'));
				})
			.insert({'bottom': new Element(tmp.tag, {'class': ' col-xs-1'}).update(tmp.me.loadUTCTime(row.orderDate).toLocaleString())})
			.insert({'bottom': new Element(tmp.tag, {'class': ' col-xs-1'}).update(row.supplier.name ? row.supplier.name : '')})
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
				.insert({'bottom': (!(row.id && (row.status === 'ORDERED' || row.status === 'RECEIVING')) || row.active !== true)  ? '' : new Element('span', {'class': 'btn btn-success btn-sm', 'title': 'Receiving Items'}).update('Receiving')
					.observe('click', function(){
						tmp.newWindow = window.open('/receiving/' + row.id + '.html', 'PO Details','width=1300, location=no, scrollbars=yes, menubar=no, status=no, titlebar=no, fullscreen=no, toolbar=no');
						tmp.newWindow.focus();
					})
				})
				.insert({'bottom': new Element('span', {'class': 'btn btn-default btn-sm', 'title': 'Edit'})
					.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-pencil'}) })
					.observe('click', function(){
							tmp.me._openEditPage(row);
					})
				})
				.insert({'bottom': new Element('span', {'class': 'btn btn-danger btn-sm', 'title': 'Delete'})
					.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-trash'}) })
					.observe('click', function(){
						if(!confirm('Are you sure you want to delete this item?'))
							return false;
						if(row.active)
							tmp.me._deactivateItem(this);
					})
				})
			});
		else
			tmp.btns.insert({'bottom': new Element('div', {'class': 'btn-group'})
				.insert({'bottom': new Element('span', {'class': 'btn btn-primary btn-sm', 'title': 'New'})
					.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-plus'}) })
					.insert({'bottom': ' NEW' })
					.observe('click', function(){
						$(this).up('thead').insert({'bottom': tmp.me._openNewPage() });
					})
				})
			});
		return tmp.row;
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
	/**
	 * Open edit page in a fancybox
	 */
	,_openEditPage: function(row) {
		var tmp = {};
		tmp.me = this;
		tmp.newWindow = window.open('/purchase/' + (row && row.id ? row.id : 'new') + '.html', 'PO Details','width=1300, location=no, scrollbars=yes, menubar=no, status=no, titlebar=no, fullscreen=no, toolbar=no');
		tmp.newWindow.focus();
		return tmp.me;
	}
	/**
	 * Open edit page in a fancybox
	 */
	,_openNewPage: function(row) {
		var tmp = {};
		tmp.me = this;
		tmp.newWindow = window.open('/purchase/new.html', 'PO Details','width=1300, location=no, scrollbars=yes, menubar=no, status=no, titlebar=no, fullscreen=no, toolbar=no');
		tmp.newWindow.focus();
		return tmp.me;
	}
	,_deactivateItem: function(btn) {
		var tmp = {};
		tmp.me = this;
		tmp.row = $(btn).up('[item_id]');
		tmp.item = tmp.row.retrieve('data');
		tmp.me.postAjax(tmp.me.getCallbackId('deactivateItems'), {'item_id': tmp.item.id}, {
			'onLoading': function() {
				if(tmp.row) {
					tmp.row.toggleClassName('danger');
					tmp.row.hide();
				}
			}
			,'onSuccess': function(sender, param){
				try {
					tmp.row.toggleClassName('danger');
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result.item)
						throw 'errror';
					$$('[item_id="'+ tmp.result.item.id +'"]').first().replace(tmp.me._getResultRow(tmp.result.item, false));
					//tmp.me._highlightSelectedRow($$('[item_id="'+ tmp.result.item.id +'"]').first().down('.glyphicon.glyphicon-trash'));
				} catch(e) {
					tmp.me.showModalBox('<span class="text-danger">ERROR</span>', e, true);
				}
			}
		});
	}
});