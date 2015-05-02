/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new CRUDPageJs(), {
	_getTitleRowData: function() {
		return {'stockOnHand': "Stock on Hand", 'stockOnHandVar': 'stockOnHandVar', 'totalOnHandValue': 'Total On Hand Value', 'totalOnHandValueVar': 'totalOnHandValueVar'
				, 'totalInPartsValue': 'Total In PartsValue', 'totalInPartsValueVar': 'totalInPartsValueVar', 'stockOnOrder': 'Stock On Order', 'stockOnOrderVar': 'stockOnOrderVar'
				, 'stockOnPO': 'Stock On PO', 'stockOnPOVar': 'stockOnPOVar', 'stockInParts': 'Stock In Parts', 'stockInPartsVar': 'stockInPartsVar'
				, 'stockInRMA': 'Stock In RMA', 'stockInRMAVar': 'stockInRMAVar', 'comments': 'Comments', 'type': 'Type', 'created': 'Date'
				, 'totalRMAValue': 'Total RMA Value'
				, 'product': {'name': 'Product', 'sku': 'sku'}
				};
	}
	,setPreData: function(from, to, productId) {
		var tmp = {};
		tmp.me = this;
		tmp.from = (from || false);
		tmp.to = (to || false);
		tmp.productId = (productId || false);
		if(tmp.from !== false)
			$('searchDiv').down('[search_field="pql.createdDate_from"]').value = tmp.from.replace(/["']/g, "");
		if(tmp.to !== false)
			$('searchDiv').down('[search_field="pql.createdDate_to"]').value = tmp.to.replace(/["']/g, "");
		if(tmp.productId !== false) {
			$('searchDiv').down('[search_field="pql.product"]').value = tmp.productId.replace(/["']/g, "");
			if($$('#showSearch').first().checked)
				$$('#showSearch').first().click();
		}
		if(tmp.from || tmp.to || tmp.productId)
			$('searchPanel').down('#searchBtn').click();
		return tmp.me;
	}
	/**
	 * Binding the search key
	 */
	,_bindSearchKey: function() {
		var tmp = {};
		tmp.me = this;
		$$('#searchBtn').first()
			.observe('click', function(event) {
				tmp.me.getSearchCriteria().getResults(true, tmp.me._pagination.pageSize);
			});
		$('searchDiv').getElementsBySelector('[search_field]').each(function(item) {
			item.observe('keydown', function(event) {
				tmp.me.keydown(event, function() {
					$('searchPanel').down('#searchBtn').click();
				});
			});
		});
		tmp.selectEl = new Element('input', {'class': 'select2 form-control', 'data-placeholder': 'search for a Products', 'search_field': 'pro.id'}).insert({'bottom': new Element('option').update('')});
		$('searchDiv').down('[search_field="pro.id"]').replace(tmp.selectEl);
		jQuery('.select2[search_field="pro.id"]').select2({
			allowClear: true,
			hidden: true,
			multiple: false,
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
		return this;
	}
	,_getEditPanel: function(row) {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = new Element('tr', {'class': 'save-item-panel info'}).store('data', row)
			.insert({'bottom': new Element('input', {'type': 'hidden', 'save-item-panel': 'id', 'value': row.id ? row.id : ''}) })
			.insert({'bottom': new Element('td', {'class': 'form-group'})
				.insert({'bottom': new Element('input', {'required': true, 'class': 'form-control', 'placeholder': 'The name of the Prefer Location Type', 'save-item-panel': 'name', 'value': row.name ? row.name : ''}) })
			})
			.insert({'bottom': new Element('td', {'class': 'form-group'})
				.insert({'bottom': new Element('input', {'class': 'form-control', 'placeholder': 'Optional - The description of the Prefer Location Type', 'save-item-panel': 'description', 'value': row.description ? row.description : ''}) })
			})
			.insert({'bottom': new Element('td', {'class': 'text-right'})
				.insert({'bottom':  new Element('span', {'class': 'btn-group btn-group-sm'})
					.insert({'bottom': new Element('span', {'class': 'btn btn-success', 'title': 'Save'})
						.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-ok'}) })
						.observe('click', function(){
							tmp.btn = this;
							tmp.me._saveItem(tmp.btn, $(tmp.btn).up('.save-item-panel'), 'save-item-panel');
						})
					})
					.insert({'bottom': new Element('span', {'class': 'btn btn-danger', 'title': 'Delete'})
						.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-remove'}) })
						.observe('click', function(){
							if(row.id)
								$(this).up('.save-item-panel').replace(tmp.me._getResultRow(row).addClassName('item_row').writeAttribute('item_id', row.id) );
							else
								$(this).up('.save-item-panel').remove();
						})
					})
				})
			});
		return tmp.newDiv;
	}

	,getTypeName: function(shortName) {
		switch(shortName) {
			case 'P':
				return 'Purchase';
			case 'S':
				return 'Sales Order';
			case 'AD':
				return 'Stock Adjustment';
			case 'SI':
				return 'Internal Stock movement';
			case 'Type':
				return shortName; // Title
			default:
				return 'Invalid type!';
		}
	}
	,_loadDataPicker: function () {
		$$('.datepicker').each(function(item){
			new Prado.WebUI.TDatePicker({'ID': item, 'InputMode':"TextBox",'Format':"yyyy-MM-dd 00:00:00",'FirstDayOfWeek':1,'CalendarStyle':"default",'FromYear':2009,'UpToYear':2024,'PositionMode':"Bottom", "ClassName": 'datepicker-layer-fixer'});
		});
		return this;
	}
	,getNumber: function(theNumber) {
		var tmp = {};
		tmp.me = this;
		tmp.theNumber = tmp.me.getValueFromCurrency(theNumber);
		return tmp.theNumber > 0 ? ("+" + tmp.theNumber) : tmp.theNumber.toString();
	}
	,_getResultRow: function(row, isTitle) {
		var tmp = {};
		tmp.me = this;
		tmp.tag = (!row.id ? 'th' : 'td');
		tmp.isTitle = (isTitle || false);
		tmp.link = '';
		if(row.order && row.order.id) {
			tmp.link = new Element('a', {'href': '/orderdetails/' + row.order.id + '.html', 'target': '_BLANK'}).update(row.order.orderNo);
		} else if (row.purchaseOrder && row.purchaseOrder.id) {
			tmp.link = new Element('a', {'href': '/purchase/' + row.purchaseOrder.id + '.html', 'target': '_BLANK'}).update(row.purchaseOrder.purchaseOrderNo);
		}
		tmp.row = new Element('tr', {'class': (tmp.isTitle === true ? '' : 'btn-hide-row')})
			.store('data', row)
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-1'}).update(tmp.isTitle === true ? row.created :
				new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('div', {'class': 'col-xs-3'})
						.insert({'bottom': new Element('abbr', {'title': tmp.me.getTypeName(row.type) }).update(row.type) })
					})
					.insert({'bottom': new Element('div', {'class': 'col-xs-9'})
						.insert({'bottom': new Element('small').update(moment(tmp.me.loadUTCTime(row.created)).format('DD/MMM/YY h:mm a')) })
					})
			) })
			.insert({'bottom': new Element(tmp.tag).update(tmp.isTitle === true ? 'Product' : new Element('a', {'href': '/product/' + row.product.id + '.html', 'target': '_BLANK'}).update(row.product.name)) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-6'})
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('div', {'class': 'col-xs-1'}).update(tmp.isTitle ? row.stockOnPO : row.stockOnPO + '(' + tmp.me.getNumber(row.stockOnPOVar) + ')') })
					.insert({'bottom': new Element('div', {'class': 'col-xs-1'}).update(tmp.isTitle ? row.stockOnHand : row.stockOnHand + '(' + tmp.me.getNumber(row.stockOnHandVar) + ')') })
					.insert({'bottom': new Element('div', {'class': 'col-xs-3'}).update(tmp.isTitle ? row.totalOnHandValue : tmp.me.getCurrency(row.totalOnHandValue) + '(' + tmp.me.getNumber(tmp.me.getCurrency(row.totalOnHandValueVar)) + ')') })
					.insert({'bottom': new Element('div', {'class': 'col-xs-1'}).update(tmp.isTitle ? row.stockOnOrder : row.stockOnOrder + '(' + tmp.me.getNumber(row.stockOnOrderVar) + ')') })
					.insert({'bottom': new Element('div', {'class': 'col-xs-1'}).update(tmp.isTitle ? row.stockInParts : row.stockInParts + '(' + tmp.me.getNumber(row.stockInPartsVar) + ')') })
					.insert({'bottom': new Element('div', {'class': 'col-xs-2'}).update(tmp.isTitle ? row.totalInPartsValue : tmp.me.getCurrency(row.totalInPartsValue) + '(' + tmp.me.getNumber(tmp.me.getCurrency(row.totalInPartsValueVar)) + ')') })
					.insert({'bottom': new Element('div', {'class': 'col-xs-1'}).update(tmp.isTitle ? row.stockInRMA : row.stockInRMA + '(' + tmp.me.getNumber(row.stockInRMAVar) + ')') })
					.insert({'bottom': new Element('div', {'class': 'col-xs-2'}).update(tmp.isTitle ? row.totalRMAValue : tmp.me.getCurrency(row.totalRMAValue) + '(' + tmp.me.getNumber(tmp.me.getCurrency(row.totalRMAValueVar)) + ')') })
				})
			})
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-2'})
				.update(row.comments + ' ')
				.insert({'bottom': tmp.link })
			})
		;
		return tmp.row;
	}
});