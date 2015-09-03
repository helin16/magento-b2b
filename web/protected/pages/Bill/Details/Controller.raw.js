/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new CRUDPageJs(), {
	_preLoadData: {}
	,_getTitleRowData: function() {
		return {};
	}
	,setPreloadData: function(_preLoadData) {
		var tmp = {};
		tmp.me = this;
		tmp.me._preLoadData = _preLoadData;
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

		tmp.row = new Element('tr', {'class': (tmp.isTitle === true ? '' : 'item_row')})
			.store('data', row)
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-sm-2'}).update(tmp.isTitle === true ? 'SKU' :
				new Element('div')
					.insert({'bottom': new Element('div').update( new Element('a', {'href': '/products/' + row.product.id + '.html', 'target': '_BLANK'}).update(row.product.sku) ) })
					.insert({'bottom': new Element('small').update(row.product.name) })
			) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-sm-1 text-center'}).update(tmp.isTitle === true ? 'Qty' : row.totalQty) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-sm-1'}).update(tmp.isTitle === true ? 'Unit Price (Ex GST)' : tmp.me.getCurrency(row.totalPrice / row.totalQty)) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-sm-1'}).update(tmp.isTitle === true ? 'Total Price (Ex GST)' : tmp.me.getCurrency(row.totalPrice)) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-sm-2'}).update(tmp.isTitle === true ? 'PurchaseOrders' :
				tmp.poList = new Element('ul', {'class': 'list-inline'})
			) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-sm-5'}).update(tmp.isTitle === true ? 'Serial No.' :
				tmp.serialNoList = new Element('ul', {'class': 'list-inline'})
			) });
		if(row.items && tmp.serialNoList) {
			row.items.each(function(item){
				tmp.serialNoList.insert({'bottom': new Element('li').update(item.serialNo) });
			});
		}
		if(row.purchaseOrders && tmp.poList) {
			row.purchaseOrders.each(function(po){
				tmp.poList.insert({'bottom': new Element('li').update( new Element('a', {'href': '/purchase/' + po.id + '.html', 'target': '_BLANK'}).update(po.purchaseOrderNo) ) });
			});
		}
		return tmp.row;
	}
	,_getSummaryPanel: function(supplier, invoiceNo, purchaseOrders) {
		var tmp = {};
		tmp.newDiv = new Element('div',{'class': 'panel panel-default'})
			.insert({'bottom': new Element('div', {'class': 'panel-body'})
				.insert({'bottom': new Element('h3').update('Invoice No.: ' + invoiceNo) })
				.insert({'bottom': new Element('div').update('Supplier: ' + supplier.name) })
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('div', {'class': 'col-xs-2 text-right'}).update('Purchase Order(s): ') })
					.insert({'bottom': new Element('div', {'class': 'col-xs-10'})
						.insert({'bottom': tmp.poList = new Element('ul', {'class': 'list-inline'}) })
					})
				})
			});
		if(purchaseOrders && tmp.poList) {
			$H(purchaseOrders).each(function(po){
				tmp.poList.insert({'bottom': new Element('li').update( new Element('a', {'href': '/purchase/' + po.value.id + '.html', 'target': '_BLANK'}).update(po.value.purchaseOrderNo) ) });
			});
		}
		return tmp.newDiv;
	}
	,getResults: function(reset, pageSize) {
		var tmp = {};
		tmp.me = this;
		tmp.reset = (reset || false);
		tmp.resultDiv = $(tmp.me.resultDivId);
		tmp.data = {'pagination': tmp.me._pagination, 'searchCriteria': {'invoiceNo': tmp.me._preLoadData.invoiceNo, 'supplierId': tmp.me._preLoadData.supplierId}};
		tmp.me.postAjax(tmp.me.getCallbackId('getItems'), tmp.data, {
			'onCreate': function () {
				tmp.resultDiv.update( new Element('tr').update( new Element('td').update( tmp.me.getLoadingImg() ) ) );
			}
			,'onSuccess': function(sender, param) {
				try{
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result)
						return;
					$(tmp.me.totalNoOfItemsId).update(tmp.result.items.size());
					tmp.resultDiv.update(tmp.me._getResultRow(tmp.me._getTitleRowData(), true).wrap(new Element('thead')));
					//show all items
					tmp.tbody = $(tmp.resultDiv).down('tbody');
					if(!tmp.tbody)
						$(tmp.resultDiv).insert({'bottom': tmp.tbody = new Element('tbody') });
					tmp.totalPrice = 0;
					tmp.purchaseOrders = {};
					tmp.result.items.each(function(item) {
						tmp.totalPrice += (item.totalPrice);
						tmp.tbody.insert({'bottom': tmp.me._getResultRow(item).addClassName('item_row').writeAttribute('item_id', item.id) });
						item.purchaseOrders.each(function(po){
							tmp.purchaseOrders[po.id] = po;
						})
					});
					jQuery('.total-price-ex').html(tmp.me.getCurrency(tmp.totalPrice));
					jQuery('.total-price-inc').html(tmp.me.getCurrency(tmp.totalPrice * 1.1));
					jQuery('.summaryPanel').html(tmp.me._getSummaryPanel(tmp.result.supplier, tmp.me._preLoadData.invoiceNo, tmp.purchaseOrders));
				} catch (e) {
					tmp.resultDiv.insert({'bottom': tmp.me.getAlertBox('Error', e).addClassName('alert-danger') });
				}
			}
			,'onComplete': function() {
				jQuery('#' + tmp.me.searchDivId + ' #searchBtn').button('reset');
			}
		});
	}
});