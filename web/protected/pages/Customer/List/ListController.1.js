/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new CRUDPageJs(), {
	_getTitleRowData: function() {
		return {'description': "Description", 'name': 'Name'};
	}

	,_getEditPanel: function(row) {

	}

	,_getResultRow: function(row, isTitle) {

	}
});