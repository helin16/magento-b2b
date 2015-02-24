/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new CRUDPageJs(), {
	_getTitleRowData: function() {
		return {'description': "Description", 'name': 'Name', 'contactName': 'Contact Name', 'email': 'Email', 'contactNo': 'Contact Number'};
	}

	,_getEditPanel: function(row) {
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
			'href'			: '/supplier/' + (row.id ? row.id : 'new' ) + '.html?blanklayout=1'
 		});
		return tmp.newDiv;
	}

	,_getResultRow: function(row, isTitle) {
		var tmp = {};
		tmp.me = this;
		tmp.tag = (tmp.isTitle === true ? 'th' : 'td');
		tmp.isTitle = (isTitle || false);
		tmp.row = new Element('tr', {'class': (tmp.isTitle === true ? '' : 'btn-hide-row')}).store('data', row)
			.insert({'bottom': new Element(tmp.tag, {'class': 'name col-xs-2'}).update(row.name) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'description'}).update(row.description) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'contactName col-xs-2'}).update(row.contactName) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'contactNo col-xs-2'}).update(row.contactNo) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'email col-xs-2'}).update(row.email) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'text-right btns col-xs-1'}).update(
				tmp.isTitle === true ? ''
				: (new Element('span', {'class': 'btn-group btn-group-xs'})
					.insert({'bottom': new Element('span', {'class': 'btn btn-default', 'title': 'Edit'})
						.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-pencil'}) })
						.observe('click', function(){
							tmp.me._getEditPanel(row);
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