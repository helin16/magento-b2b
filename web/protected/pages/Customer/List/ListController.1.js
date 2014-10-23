/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new CRUDPageJs(), {
	_getTitleRowData: function() {
		return {'email': "Email", 'name': 'Name'};
	}

	,_getEditPanel: function(row) {

	}

	,_getResultRow: function(row, isTitle) {
		var tmp = {};
		tmp.me = this;
		tmp.tag = (tmp.isTitle === true ? 'th' : 'td');
		tmp.isTitle = (isTitle || false);
		console.debug(row);
		tmp.row = new Element('tr', {'class': (tmp.isTitle === true ? '' : 'btn-hide-row')}).store('data', row)
			.insert({'bottom': new Element(tmp.tag, {'class': 'name col-xs-2'}).update(row.name) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'description'}).update(row.email) })
		;
		return tmp.row;
	}
});