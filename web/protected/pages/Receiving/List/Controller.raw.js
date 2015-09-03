/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new CRUDPageJs(), {
	_getTitleRowData: function() {
		return {'serialNo': "Serial No.", 'qty': 'Qty', 'product': 'Product', 'unitPrice': 'Unit Cost(Excl. GST)', 'invoiceNo': 'Invoice No.', 'created': 'Received By', 'purchaseOrder': 'Purchase Order'};
	}
	,_bindSearchKey: function() {
		var tmp = {}
		tmp.me = this;
		$('searchPanel').getElementsBySelector('[search_field]').each(function(item) {
			item.observe('keydown', function(event) {
				tmp.me.keydown(event, function() {
					$(tmp.me.searchDivId).down('#searchBtn').click();
				});
			})
		});
		// product search
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
		// po search
		tmp.selectEl = new Element('input', {'class': 'select2 form-control', 'data-placeholder': 'search for a PO', 'search_field': 'purchaseorderids'}).insert({'bottom': new Element('option').update('')});
		$('searchDiv').down('[search_field="purchaseorderids"]').replace(tmp.selectEl);
		jQuery('.select2[search_field="purchaseorderids"]').select2({
			allowClear: true,
			hidden: true,
			multiple: true,
			ajax: { url: "/ajax/getPurchaseOrders",
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
						tmp.result.push({"id": item.id, 'text': item.purchaseOrderNo, 'data': item});
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
				return '<div value=' + result.data.id + '>' + result.data.purchaseOrderNo + '</div >';
			},
			escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
			minimumInputLength: 3
		});
		return this;
	}
	,_submitDeletion: function(btn, row) {
		var tmp = {};
		tmp.me = this;
		tmp.confirmDiv = $(btn).up('.confirm-div');
		tmp.confirmDiv.getElementsBySelector('.msg').each(function(item){ item.remove(); });
		tmp.me.postAjax(tmp.me.getCallbackId('deleteItem'), {'id': row.id}, {
			'onCreate': function() {
				tmp.me._signRandID(btn);
				jQuery('#' + btn.id).button('loading');
			}
			,'onComplete': function() {
				jQuery('#' + btn.id).button('reset');
			}
			,'onSuccess': function(sender, param) {
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result || !tmp.result.item || !tmp.result.item.id)
						return null;
					$$('.item_row[item_id="' + tmp.result.item.id + '"]').each(function(item) { item.remove(); });
					tmp.me.hideModalBox();
				} catch (e) {
					tmp.confirmDiv.insert({'top': new Element('h4', {'class': 'msg'}).update(new Element('span', {'class': 'label label-danger'}).update(e) ) });
				}
			}
		})
	}
	,_showDeleteConfirmPanel: function(row) {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = new Element('div', {'class': 'confirm-div'})
			.insert({'bottom': new Element('div')
				.insert({'bottom': new Element('h4').update('You are about to delete this received item, by doing so, it may:') })
				.insert({'bottom': new Element('ul')
					.insert({'bottom': new Element('li').update('This <strong>serial number(' + row.serialNo + ')</strong> will not be searchable or accessible any more in the future.') })
					.insert({'bottom': new Element('li').update('The Status <strong>Purchase Order(' + row.purchaseOrder.purchaseOrderNo + ')</strong> may change') })
					.insert({'bottom': new Element('li').update('The total <strong>StockOnHand count</strong> will change') })
					.insert({'bottom': new Element('li').update('The total <strong>StockOnHand value</strong> will change') })
				})
			})
			.insert({'bottom': new Element('div', {'class': 'text-right'})
				.insert({'bottom': new Element('span', {'class': 'btn btn-default pull-left'})
					.update('CANCEL')
					.observe('click', function(){
						tmp.me.hideModalBox();
					})
				})
				.insert({'bottom': new Element('span', {'class': 'btn btn-danger', 'data-loading-text': 'Deleting ...'})
					.update('Yes, Delete It')
					.observe('click', function(){
						tmp.me._submitDeletion(this, row);
					})
				})
			});
		tmp.me.showModalBox('<strong class="text-danger">Confirm Deletion:</strong>', tmp.newDiv);
		return tmp.me;
	}

	,_getResultRow: function(row, isTitle) {
		var tmp = {};
		tmp.me = this;
		tmp.tag = (tmp.isTitle === true ? 'th' : 'td');
		tmp.isTitle = (isTitle || false);
		tmp.row = new Element('tr', {'class': (tmp.isTitle === true ? '' : 'item-data-row')})
			.store('data', row)
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-1'}).update(row.serialNo) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-1'}).update(row.qty) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-3'}).update(tmp.isTitle === true ? row.product : new Element('a', {'href': '/product/' + row.product.id + '.html', 'target': '_BLANK'}).update(row.product.sku) ) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-1'}).update(tmp.isTitle === true ? row.unitPrice : tmp.me.getCurrency(row.unitPrice)) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-3'}).update(tmp.isTitle === true ? row.purchaseOrder : new Element('a', {'href': '/purchase/' + row.purchaseOrder.id + '.html', 'target': '_BLANK'}).update(row.purchaseOrder.purchaseOrderNo + ' [' + row.purchaseOrder.status + ']') ) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-2'}).update(tmp.isTitle === true ? row.created : row.createdBy.person.fullname + ' @ ' + tmp.me.loadUTCTime(row.created).toLocaleString()) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-1'}).update(tmp.isTitle === true ? '': new Element('div', {'class': 'btn-group'})
				.insert({'bottom': new Element('span', {'class': 'btn btn-xs btn-danger'})
					.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-trash'}) })
					.observe('click', function() {
						tmp.me._showDeleteConfirmPanel(row);
					})
				})
			) })
		;
		return tmp.row;
	}
});