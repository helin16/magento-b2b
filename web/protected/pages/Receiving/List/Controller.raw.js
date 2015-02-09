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
			})
		return tmp.newDiv;
	}

	,_getResultRow: function(row, isTitle) {
		var tmp = {};
		tmp.me = this;
		tmp.tag = (tmp.isTitle === true ? 'th' : 'td');
		tmp.isTitle = (isTitle || false);
		tmp.row = new Element('tr', {'class': (tmp.isTitle === true ? '' : 'btn-hide-row')})
			.store('data', row)
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-1'}).update(row.serialNo) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-1'}).update(row.qty) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-3'}).update(tmp.isTitle === true ? row.product : new Element('a', {'href': '/product/' + row.product.id + '.html', 'target': '_BLANK'}).update(row.product.sku) ) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-1'}).update(tmp.isTitle === true ? row.unitPrice : tmp.me.getCurrency(row.unitPrice)) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-3'}).update(tmp.isTitle === true ? row.purchaseOrder : new Element('a', {'href': '/purchase/' + row.purchaseOrder.id + '.html', 'target': '_BLANK'}).update(row.purchaseOrder.purchaseOrderNo + ' [' + row.purchaseOrder.status + ']') ) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-2'}).update(tmp.isTitle === true ? row.created : row.createdBy.person.fullname + ' @ ' + tmp.me.loadUTCTime(row.created).toLocaleString()) })
		;
		return tmp.row;
	}
});