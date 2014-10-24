/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new CRUDPageJs(), {
	_getTitleRowData: function() {
		return {'email': "Email", 'name': 'Name', 'contactNo': 'Contact Num', 'description': 'Description', 'addresses': 'Addresses',
			'address': {'billing': {'full': 'Billing Address'}, 'shipping': {'full': 'Shipping Address'} },
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
	/**
	 * Displaying the selected address 
	 */
	,_displaySelectedAddress: function(item) {
		var tmp = {};
		tmp.me = this;
		//remove highlight
		jQuery('.item_row.success').removeClass('success');
		//mark this one as active
		tmp.selectedRow = jQuery('[item_id=' + item.id + ']')
			.addClass('success');
		
		if(!tmp.selectedRow.hasClass('popover-loaded')) {
			tmp.selectedRow.popover({
				'title'    : 'title',
				'html'     : true, 
				'placement': 'right', 
				'trigger'  : 'click', 
				'content'  : '<p>test content</p>', 
			})
			.addClass('popover-loaded');
		}
		//tmp.selectedRow.popover('toggle');

		return tmp.me;
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
			//.insert({'bottom': new Element(tmp.tag, {'class': 'address col-xs-2'}).update(row.address.billing.full) })
			//glyphicon glyphicon-send
			.insert({'bottom': tmp.isTitle === true ? row.addresses : new Element(tmp.tag, {'class': 'address col-xs-1'})
				.insert({'bottom': (new Element('span', {'class': 'glyphicon glyphicon-plane address-shipping'})
				.observe('click', function(){
					console.debug('works');
					tmp.me._displaySelectedAddress(row);
				})
				) })
				.insert({'bottom': (new Element('span', {'class': 'glyphicon glyphicon-usd address-billing'})
				.observe('click', function(){
					console.debug('works2');
				})
				) })
			})
			
			.insert({'bottom': new Element(tmp.tag, {'class': 'address col-xs-2'}).update(row.address.shipping.full) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'mageId col-xs-1'}).update(row.mageId) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'cust_active col-xs-1'})
				.insert({'bottom': (tmp.isTitle === true ? row.active : new Element('input', {'type': 'checkbox', 'disabled': true, 'checked': row.active}) ) })
			})
			
		;
		return tmp.row;
	}
});