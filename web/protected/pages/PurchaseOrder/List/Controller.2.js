/**
 * The page Js file
 */
var PageJs = new Class.create();

PageJs.prototype = Object.extend(new CRUDPageJs(), {
	_getTitleRowData: function() {
		return {'totalAmount': 'Total Amount', 'totalPaid': 'Total Paid', 'purchaseOrderNo': 'PO Number', 'supplier': {'name': 'Supplier'}, 'status': 'Status', 'supplierRefNo': 'Supplier Ref.', 'orderDate': 'Order Date', 'active': 'Active'};
		
	}
	,_loadChosen: function () {
		jQuery(".chosen").chosen({
			disable_search_threshold: 10,
			no_results_text: "Oops, nothing found!",
			width: "95%"
		});
		return this;
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
	/**
	 * Getting each row for displaying the result list
	 */

	,_getResultRow: function(row, isTitle) {
		var tmp = {};
		tmp.me = this;
		tmp.tag = (tmp.isTitle === true ? 'th' : 'td');
		tmp.isTitle = (isTitle || false);
		tmp.row = new Element('tr', {'class': (tmp.isTitle === true ? 'item_top_row' : 'btn-hide-row item_row') + (row.active == 0 ? ' danger' : ''), 'item_id': (tmp.isTitle === true ? '' : row.id)}).store('data', row)
			.insert({'bottom': new Element(tmp.tag, {'class': 'purchaseOrderNo col-xs-1'}).update(row.purchaseOrderNo)})
				.observe('dblclick', function(){
					tmp.me._openEditPage(row);
				})
				.observe('click', function(){
					tmp.me._highlightSelectedRow(this.down('.btn'));
				})
			.insert({'bottom': new Element(tmp.tag, {'class': 'status col-xs-1'}).update(row.status)})
			.insert({'bottom': new Element(tmp.tag, {'class': 'supplierId col-xs-1'}).update(row.supplier.name ? row.supplier.name : '')})
			.insert({'bottom': new Element(tmp.tag, {'class': 'supplierRefNo col-xs-1'}).update(row.supplierRefNo ? row.supplierRefNo : '')})
			.insert({'bottom': new Element(tmp.tag, {'class': 'orderDate col-xs-1'}).update(row.orderDate)})
			.insert({'bottom': new Element(tmp.tag, {'class': 'orderDate col-xs-1'}).update(!tmp.isTitle ? tmp.me.getCurrency(row.totalAmount) : 'Total Amount')})
			.insert({'bottom': new Element(tmp.tag, {'class': 'orderDate col-xs-1'}).update(!tmp.isTitle ? row.totalPaid ? tmp.me.getCurrency(row.totalPaid) : '' : 'Total Paid')})
			.insert({'bottom': new Element(tmp.tag, {'class': 'cust_active col-xs-1'})
				.insert({'bottom': (tmp.isTitle === true ? row.active : new Element('input', {'type': 'checkbox', 'disabled': true, 'checked': row.active}) ) })
			})
			.insert({'bottom': tmp.btns = new Element(tmp.tag, {'class': 'col-xs-1 text-right'}) 	});
		if(tmp.isTitle !== true)
			tmp.btns.insert({'bottom': new Element('div', {'class': 'btn-group'})
				.insert({'bottom': new Element('span', {'class': 'btn btn-default btn-sm', 'title': 'Edit'})
					.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-pencil'}) })
					.observe('click', function(){
							tmp.me._openEditPage(row);
							tmp.me._highlightSelectedRow(this);
					})
				})
				.insert({'bottom': new Element('span', {'class': 'btn btn-danger btn-sm', 'title': 'Delete'})
					.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-trash'}) })
					.observe('click', function(){
						if(!confirm('Are you sure you want to delete this item?'))
							return false;
						if(row.active)
							tmp.me._deactivateItem(this);
						tmp.me._highlightSelectedRow(this);
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
		jQuery.fancybox({
			'width'			: '95%',
			'height'		: '95%',
			'autoScale'     : false,
			'autoDimensions': false,
			'fitToView'     : false,
			'autoSize'      : false,
			'type'			: 'iframe',
			'href'			: '/purchase/' + (row && row.id ? row.id : 'new') + '.html'
 		});
		return tmp.me;
	}
	/**
	 * Open edit page in a fancybox
	 */
	,_openNewPage: function(row) {
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
		    'afterLoad'		: function(current, previous) {
		    	tmp.iframe = $$('iframe.fancybox-iframe').first();
		    	tmp.innerDoc = tmp.iframe.contentDocument || tmp.iframe.contentWindow.document;
		    	$(tmp.innerDoc).body.down('.init-focus').select();
		    },
			'href'			: '/purchase/new.html'
		});
		return tmp.me;
	}
	,_highlightSelectedRow : function (btn) {
		var tmp = {};
		tmp.me = this;
		tmp.item = $(btn).up('[item_id]').retrieve('data');
		jQuery('.item_row.success').removeClass('success');
		tmp.selectedRow = jQuery('[item_id=' + tmp.item.id + ']')
		.addClass('success');
	}
	,_deactivateItem: function(btn) {
		var tmp = {}
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
					tmp.me._highlightSelectedRow($$('[item_id="'+ tmp.result.item.id +'"]').first().down('.glyphicon.glyphicon-trash'));
				} catch(e) {
					tmp.me.showModalBox('<span class="text-danger">ERROR</span>', e, true);
				}
			}
		})
	}
});