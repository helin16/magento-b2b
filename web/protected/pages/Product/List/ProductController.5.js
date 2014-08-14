/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new CRUDPageJs(), {
	
	_getTitleRowData: function() {
		return {'sku': 'SKU', 'name': 'Product Name', 'active': 'act?'};
	}
	
	,_getEditPanel: function(row) {
		var tmp = {};
		tmp.me = this;
		///open row.id
		return tmp.newDiv;
	}
	
	,_getResultRow: function(row, isTitle) {
		var tmp = {};
		tmp.me = this;
		tmp.tag = (tmp.isTitle === true ? 'th' : 'td');
		tmp.isTitle = (isTitle || false);
		tmp.row = new Element('tr', {'class': (tmp.isTitle === true ? '' : 'product_item'), 'product_id' : row.id}).store('data', row)
			.insert({'bottom': new Element(tmp.tag, {'class': 'sku'}).update(row.sku) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'name'}).update(row.name) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'text-center product_active'}).update(
				tmp.isTitle === true ? row.active : new Element('input', {'type': 'checkbox', 'checked': row.active}) 
			) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'text-right btns col-xs-2'}).update(
				tmp.isTitle === true ?  
				(new Element('span', {'class': 'btn btn-primary btn-xs', 'title': 'New'})
					.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-plus'}) })
					.insert({'bottom': ' NEW' })
					.observe('click', function(){
						$(this).up('thead').insert({'bottom': tmp.me._getEditPanel({}) });
					})
				)
				: (new Element('span', {'class': 'btn-group btn-group-xs'})
					.insert({'bottom': new Element('span', {'class': 'btn btn-default', 'title': 'Edit'})
						.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-pencil'}) })
						.observe('click', function(){
							$(this).up('.item_row').replace(tmp.me._getEditPanel(row));
						})
					})
					.insert({'bottom': new Element('span', {'class': 'btn btn-danger', 'title': 'Delete'})
						.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-trash'}) })
						.observe('click', function(){
							if(!confirm('Are you sure you want to delete this item?'))
								return false;
							tmp.me._deleteItem(row);
						})
					}) ) 
			) })
		;
		return tmp.row;
	}
});