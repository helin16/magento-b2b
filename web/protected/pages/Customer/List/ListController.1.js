/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new CRUDPageJs(), {
	_getTitleRowData: function() {
		return {'email': "Email", 'name': 'Name', 'contactNo': 'Contact Num', 'description': 'Description', 
			'address': {'billing': {'full': 'Billing Address'}, 'shipping': {'full': 'shipping Address'} },
			'mageId': "Mage Id", 'active': "Active?"
			//mageId
			};
	}

	/**
	 * Binding the search key
	 */
	,_bindSearchKey: function() {
		var tmp = {}
		tmp.me = this;
		$('searchDiv').getElementsBySelector('[search_field]').each(function(item) {
			item.observe('keydown', function(event) {
				tmp.me.keydown(event, function() {
					$(tmp.me.searchDivId).down('#searchBtn').click();
				});
			})
		});
		return this;
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
			.insert({'bottom': new Element(tmp.tag, {'class': 'name col-xs-1'}).update(row.name) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'email col-xs-1'}).update(row.email) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'contact col-xs-1'}).update(row.contactNo)})
			.insert({'bottom': new Element(tmp.tag, {'class': 'description col-xs-1'}).update(row.description) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'address col-xs-2'}).update(row.address.billing.full) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'address col-xs-2'}).update(row.address.shipping.full) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'mageId col-xs-1'}).update(row.mageId) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'cust_active col-xs-1'})
				.insert({'bottom': (tmp.isTitle === true ? row.active : new Element('input', {'type': 'checkbox', 'disabled': true, 'checked': row.active}) ) })
			})
			
		;
		return tmp.row;
	}
});