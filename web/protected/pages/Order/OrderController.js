/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new BPCPageJs(), {
	resultDivId: '' //the html id of the result div
		
	,getResults: function(reset) {
		var tmp = {};
		tmp.me = this;
		tmp.reset = (reset || false);
		tmp.me.postAjax(tmp.me.getCallbackId('getOrders'), {}, {
			'onLoading': function () {}
			,'onComplete': function(sender, param) {
				try{
					tmp.result = tmp.me.getResp(param, false, true);
					if(tmp.reset === true) {
						tmp.titleRow = {'orderNo': "Order No.", 'orderDate': 'Order Date', 'invNo': 'Invoice No.', 'status': {'name': 'Status'}, 'payment': 'Payment'};
						$(tmp.me.resultDivId).update(tmp.me._getResultRow(tmp.titleRow).addClassName('header'));
					}
					
					tmp.result.items.each(function(item) {
						$(tmp.me.resultDivId).insert({'bottom': tmp.me._getResultRow(item) });
					})
					
				} catch (e) {
					alert(e);
				}
			}
		});
	}
		
	,_getResultRow: function(row) {
		var tmp = {};
		tmp.me = this;
		return new Element('div', {'class': 'row'}).store('data', row)
			.insert({'bottom': new Element('span', {'class': 'cell orderNo'}).update(row.orderNo) })
			.insert({'bottom': new Element('span', {'class': 'cell orderDate'}).update(row.orderDate) })
			.insert({'bottom': new Element('span', {'class': 'cell invNo'}).update(row.invNo) })
			.insert({'bottom': new Element('span', {'class': 'cell status'}).update(row.status ? row.status.name : '') })
			.insert({'bottom': new Element('span', {'class': 'cell payment'}).update(row.payment) });
	}
});