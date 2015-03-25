var InsufficientStockOrdersListPanelJs = new Class.create();
InsufficientStockOrdersListPanelJs.prototype = {
	_pageJs : null
	,_panelHTMLID: ''

	,initialize : function(_pageJs) {
		this._pageJs = _pageJs;
		this._panelHTMLID = 'PaymentListPanelJs_' + String.fromCharCode(65 + Math.floor(Math.random() * 26)) + Date.now();
	}

	,getListPanel: function() {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = new Element('div', {'class': 'panel', 'id': tmp.me._panelHTMLID})
			.insert({'bottom': new Element('div', {'class': 'panel-heading'}).update('Insufficient Stock Against Orders:') });
		return tmp.newDiv;
	}
	,_getListItem: function(item) {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = new Element('a', {'class': 'list-group-item', 'href': 'javascript: void(0);'})
			.store('data', item)
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('strong', {'class': 'col-sm-4'})
					.insert({'bottom': new Element('a', {'href': '/product/' + item.product.id + '.html', 'target': '_BLANK', 'title': item.product.sku})
						.setStyle('overflow: hidden; text-align: right; text-overflow: ellipsis; white-space: nowrap;')
						.update(item.product.sku)
					})
				})
				.insert({'bottom': new Element('small', {'class': 'col-sm-8', 'title': item.product.name}).setStyle('overflow: hidden; text-align: right; text-overflow: ellipsis; white-space: nowrap;').update(item.product.name) })
			})
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('div', {'class': 'col-sm-4'})
					.insert({'bottom': new Element('a', {'class': 'pull-right', 'href': '/orderdetails/' + item.order.id + '.html', 'target': '_BLANK'})
						.update(item.order.orderNo + ':')
					})
				})
				.insert({'bottom': new Element('div', {'class': 'col-sm-1'})
					.insert({'bottom': new Element('small').update(item.order.status.name) })
				})
				.insert({'bottom': new Element('div', {'class': 'col-sm-3'})
					.insert({'bottom': new Element('em').update(moment(item.order.orderDate).format('DD/MMM/YY')) })
				})
			})
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('div', {'class': 'col-sm-3'}).update('SOH: <strong>' + item.product.stockOnHand + '</strong>') })
				.insert({'bottom': new Element('div', {'class': 'col-sm-3'}).update('SPO: <strong>' + item.product.stockOnPO + '</strong>') })
				.insert({'bottom': new Element('div', {'class': 'col-sm-3', 'title': 'Total quantity ordered for this product'}).update('Total Ord: <strong>' + item.totalOrderedQty + '</strong>') })
				.insert({'bottom': new Element('div', {'class': 'col-sm-3', 'title': 'Quantity ordered for this order and this product'}).update('This Ord: <strong>' + item.qtyOrdered + '</strong>') })
			});
		return tmp.newDiv;
	}
	/**
	 * Show the list
	 */
	,showList: function() {
		var tmp = {};
		tmp.me = this;
		if(!$(tmp.me._panelHTMLID))
			return tmp.me;
		tmp.loadingDiv = new Element('div', {'class': 'panel-body'}).update(tmp.me._pageJs.getLoadingImg());
		tmp.ajax = new Ajax.Request('/ajax/getInsufficientStockOrders', {
			method: 'get'
			,parameters: {'pageNo': 1, 'pageSize': 15}
			,onLoading: function() {
				$(tmp.me._panelHTMLID).insert({'bottom': tmp.loadingDiv});
			}
			,onSuccess: function(transport) {
				try {
					tmp.result = tmp.me._pageJs.getResp(transport.responseText, false, true);
					if(!tmp.result || !tmp.result.items)
						return;
					if($(tmp.me._panelHTMLID).down('panel-body'))
						$(tmp.me._panelHTMLID).down('panel-body').remove();

					tmp.list = $(tmp.me._panelHTMLID).down('list-group');
					if(!tmp.list)
						$(tmp.me._panelHTMLID).insert({'bottom': tmp.list = new Element('div', {'class': 'list-group'}) });
					tmp.result.items.each(function(item){
						tmp.list.insert({'bottom': tmp.me._getListItem(item) });
					})
				} catch (e) {
					$(tmp.me._panelHTMLID).insert({'bottom': new Element('div', {'class': 'panel-body'}).update(tmp.me.getAlertBox('ERROR: ', e).addClassName('alert-danger')) });
				}
			}
			,onComplete: function() {
				tmp.loadingDiv.remove();
			}
		});
		return tmp.me;
	}
	/**
	 * loading the data
	 */
	,load: function() {
		 var tmp = {};
		 tmp.me = this;
		 //check whther the pament list panel is loaded.
		 if($(tmp.me._panelHTMLID)) {
			 tmp.me.showList();
		 }
		 return tmp.me;
	}
};