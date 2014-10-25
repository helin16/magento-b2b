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
	/**
	 * open the customer edit popup page
	 */
	,_openEditPage: function(customer) {
		var tmp = {};
		tmp.newWindow = window.open('/customer/' + customer.id + '.html', 'Product Details for: ' + customer.name, 'location=no, menubar=no, status=no, titlebar=no, fullscreen=yes, toolbar=no');
		tmp.newWindow.focus();
	}
	,_getEditPanel: function(row) {

	}
	,_highlightSelectedRow : function (btn) {
		var tmp = {};
		tmp.me = this;
		tmp.item = $(btn).up('[item_id]').retrieve('data');
		console.debug(tmp.item);
		jQuery('.item_row.success').removeClass('success');
		tmp.selectedRow = jQuery('[item_id=' + tmp.item.id + ']')
		.addClass('success');
	}
	/**
	 * Displaying the selected address 
	 */
	,_displaySelectedAddress: function(btn) {
		var tmp = {};
		tmp.me = this;
		tmp.item = $(btn).up('[item_id]').retrieve('data');
		console.debug(tmp.item);
		tmp.type = $(btn).down('span').classList.contains('address-shipping');
		
		jQuery('.popover-loaded').popover('hide');
		//remove highlight
		jQuery('.item_row.success').removeClass('success');
		//mark this one as active
		tmp.selectedRow = jQuery('[item_id=' + tmp.item.id + ']')
			.addClass('success');
		
		tmp.me._signRandID(btn); //sign it with a HTML ID to commnunicate with jQuery
		if(!jQuery('#' + btn.id).hasClass('popover-loaded')) {
			jQuery('#' + btn.id).popover({
				'title'    : '<div class="row"><div class="col-xs-10">Details for: ' + tmp.item.name + '</div><div class="col-xs-2" style="cursor: pointer"><span class="pull-right glyphicon glyphicon-remove" href="javascript:void(0);" onclick="jQuery(' + "'#" + btn.id + "'" + ').popover(' + "'hide'" + ');"></span></div></div>',
				'html'     : true, 
				'placement': function () {return tmp.type? 'left' : 'right'},
				'container': 'body', 
				'trigger'  : 'manual', 
				'viewport' : {"selector": ".list-panel", "padding": 0 },
				'content'  : function () {
					return tmp.type? '<p>' + tmp.item.address.shipping.full +'</p>' : '<p>' + tmp.item.address.billing.full +'</p>'
				},
				'template' : '<div class="popover" role="tooltip" style="max-width: none; z-index: 0;"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>'
			})
			.addClass('popover-loaded');
		}
		jQuery('#' + btn.id).popover('toggle');
		return tmp.me;
	}
	,_getResultRow: function(row, isTitle) {
		var tmp = {};
		tmp.me = this;
		tmp.tag = (tmp.isTitle === true ? 'th' : 'td');
		tmp.isTitle = (isTitle || false);
		tmp.row = new Element('tr', {'class': (tmp.isTitle === true ? '' : 'btn-hide-row')}).store('data', row)
			.insert({'bottom': new Element(tmp.tag, {'class': 'name col-xs-1'}).update(row.name) 
				.observe('click', function(){
					tmp.me._highlightSelectedRow(this);
				})	
			})
			.insert({'bottom': new Element(tmp.tag, {'class': 'email col-xs-1'}).update(row.email) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'contact col-xs-1'}).update(row.contactNo)})
			.insert({'bottom': new Element(tmp.tag, {'class': 'description col-xs-1'}).update(row.description) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'address col-xs-1'})
				.insert({'bottom': tmp.isTitle === true ? row.addresses : new Element('span', {'style': 'display: inline-block'})
					.insert({'bottom': new Element('a', {'class': 'visible-xs visible-md visible-sm visible-lg', 'href': 'javascript: void(0);'})
						.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-plane address-shipping', 'style': 'font-size: 1.3em'}) })
						.observe('click', function(){
							tmp.me._displaySelectedAddress(this);
						})
					})
				})
				.insert({'bottom': tmp.isTitle === true ? '' : new Element('span', {'style': 'display: inline-block'})
					.insert({'bottom':  new Element('a', {'class': 'visible-xs visible-md visible-sm visible-lg', 'href': 'javascript: void(0);'})
						.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-usd address-billing', 'style': 'font-size: 1.3em; padding-left:10%;'}) })
						.observe('click', function(){
							tmp.me._displaySelectedAddress(this);
						})
					})
				})
			})
			.insert({'bottom': new Element(tmp.tag, {'class': 'mageId col-xs-1'}).update(row.mageId) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'cust_active col-xs-1'})
				.insert({'bottom': (tmp.isTitle === true ? row.active : new Element('input', {'type': 'checkbox', 'disabled': true, 'checked': row.active}) ) })
			})
			
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-1' })
				.insert({'bottom': tmp.isTitle === true ? '' : new Element('span', {'class': 'btn btn-primary btn-xs'})
					.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-pencil'}) })
				})
				.observe('click', function(){
					tmp.me._openEditPage(row);
					tmp.me._highlightSelectedRow(this);
				})
			});
			
			
		;
		return tmp.row;
	}
});