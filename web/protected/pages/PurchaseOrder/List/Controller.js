/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new CRUDPageJs(), {
	/**
	 * Getting each row for displaying the result list
	 */
	_getResultRow: function(row, isTitle) {
		var tmp = {};
		tmp.me = this;
		tmp.tag = (tmp.isTitle === true ? 'th' : 'td');
		tmp.isTitle = (isTitle || false);
		console.debug("test01");
		tmp.row = new Element('tr', {'class': (tmp.isTitle === true ? '' : 'btn-hide-row')}).store('data', row)
			.insert({'bottom': new Element(tmp.tag, {'class': 'id col-xs-1'}).update(row.id) 
				.observe('click', function(){
					tmp.me._highlightSelectedRow(this);
				})	
			})
			.insert({'bottom': new Element(tmp.tag, {'class': 'poNo col-xs-1'}).update(row.poNo) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'poDate col-xs-1'}).update(row.poDate)})
			.insert({'bottom': new Element(tmp.tag, {'class': 'supplierID col-xs-1'}).update(row.supplierID)})
		
		return tmp.row;
	}
});