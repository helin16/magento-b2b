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
				new Element('a').update(row.product.sku)
			) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-sm-3'}).update(tmp.isTitle === true ? 'Product' :
				new Element('a').update(row.product.name)
			) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-sm-1 text-center'}).update(tmp.isTitle === true ? 'Qty' : row.totalQty) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-sm-1'}).update(tmp.isTitle === true ? 'Unit Price' : tmp.me.getCurrency(row.totalPrice)) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-sm-1'}).update(tmp.isTitle === true ? 'Total Price' : tmp.me.getCurrency(row.totalPrice * row.totalQty)) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-sm-4'}).update(tmp.isTitle === true ? 'Serial No.' :
				tmp.serialNoList = new Element('ul', {'class': 'list-inline'})
			) });
		if(row.items && tmp.serialNoList) {
			row.items.each(function(item){
				tmp.serialNoList.insert({'bottom': new Element('li').update(item.serialNo) });
			});
		}
		return tmp.row;
	}
	,_getSummaryPanel: function() {

	}
	,getResults: function(reset, pageSize) {
		var tmp = {};
		tmp.me = this;
		tmp.reset = (reset || false);
		tmp.resultDiv = $(tmp.me.resultDivId);
		tmp.data = {'pagination': tmp.me._pagination, 'searchCriteria': {'invoiceNo': tmp.me._preLoadData.invoiceNo, 'supplierId': tmp.me._preLoadData.supplierId}};
		tmp.me.postAjax(tmp.me.getCallbackId('getItems'), tmp.data, {
			'onLoading': function () {
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
					tmp.result.items.each(function(item) {
						tmp.totalPrice += (item.totalPrice * item.totalQty);
						tmp.tbody.insert({'bottom': tmp.me._getResultRow(item).addClassName('item_row').writeAttribute('item_id', item.id) });
					});
					jQuery('.total-price').html(tmp.me.getCurrency(tmp.totalPrice));
					jQuery('.summaryPanel').html(tmp.me._getSummaryPanel(tmp.result.supplier));
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