/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new CRUDPageJs(), {
	_getTitleRowData: function() {
		return {'email': "Email", 'name': 'Name', 'contactNo': 'Contact Num', 'description': 'Description', 'addresses': 'Addresses',
			'address': {'billing': {'full': 'Billing Address'}, 'shipping': {'full': 'Shipping Address'} },
			'mageId': "Mage Id", 'active': "Active?"
			};
	}
	/**
	 * Binding the search key
	 */
	,_bindSearchKey: function() {
		var tmp = {}
		tmp.me = this;
		$$('#searchBtn').first()
			.observe('click', function(event) {
				if(!$$('#showSearch').first().checked)
					$$('#showSearch').first().click();
				else
					tmp.me.getSearchCriteria().getResults(true, tmp.me._pagination.pageSize);
			});
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
	 * Open edit page in a fancybox
	 */
	,_openEditPage: function(row) {
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
			'frameborder'	: '0',
			'border'		: '0',
			'seamless'		: 'seamless',
			'href'			: '/customer/' + (row && row.id ? row.id : 'new') + '.html'
 		});
		return tmp.me;
		
	}
	/**
	 * Highlisht seleteted row
	 */
	,_highlightSelectedRow : function (btn) {
		var tmp = {};
		tmp.me = this;
		tmp.item = btn.down('.glyphicon-plus') ? '' : $(btn).up('[item_id]').retrieve('data');
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
				'title'    : '<div class="row"><div class="col-xs-10">Details for: ' + tmp.item.name + '</div><div class="col-xs-2" style="cursor: pointer" href="javascript:void(0);" onclick="jQuery(' + "'#" + btn.id + "'" + ').popover(' + "'hide'" + ');"><span class="pull-right glyphicon glyphicon-remove" ></span></div></div>',
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
	/**
	 * get result row for data given
	 */
	,_getResultRow: function(row, isTitle) {
		var tmp = {};
		tmp.me = this;
		tmp.tag = (tmp.isTitle === true ? 'th' : 'td');
		tmp.isTitle = (isTitle || false);
		tmp.row = new Element('tr', {'class': (tmp.isTitle === true ? 'item_top_row' : 'btn-hide-row item_row') + (row.active == 0 ? ' danger' : ''), 'item_id': (tmp.isTitle === true ? '' : row.id)}).store('data', row)
			.insert({'bottom': new Element(tmp.tag, {'class': 'name col-xs-1'}).update(row.name) 
				.observe('click', function(){
					tmp.me._highlightSelectedRow(this);
					$$('.popover-loaded').each(function(item){
						jQuery(item).popover('hide');
					});
				})	
				.observe('dblclick', function(){
					$$('.popover-loaded').each(function(item){
						jQuery(item).popover('hide');
					});
					tmp.me._openEditPage(row);
				})	
			})
			.insert({'bottom': new Element(tmp.tag, {'class': 'email col-xs-1', 'style': 'text-decoration: underline;'}).update(row.email) 
				.observe('click', function(){
					tmp.me._highlightSelectedRow(this);
					$$('.popover-loaded').each(function(item){
						jQuery(item).popover('hide');
					});
				})	
				.observe('dblclick', function(){
					$$('.popover-loaded').each(function(item){
						jQuery(item).popover('hide');
					});
					tmp.newWindow = window.open('mailto:' + row.email, 'location=no, menubar=no, status=no, titlebar=no, fullscreen=yes, toolbar=no');
					tmp.newWindow.close();
				})	
			})
			.insert({'bottom': new Element(tmp.tag, {'class': 'contact col-xs-1 truncate'}).update(row.contactNo)})
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
			
			.insert({'bottom': new Element(tmp.tag, {'class': 'text-right col-xs-1'}).update(
				tmp.isTitle === true ?  
				(new Element('span', {'class': 'btn btn-primary btn-xs', 'title': 'New'})
					.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-plus'}) })
					.insert({'bottom': ' NEW' })
					.observe('click', function(){
						$(this).up('thead').insert({'bottom': tmp.me._openEditPage() });
					})
				)
				: (new Element('span', {'class': 'btn-group btn-group-xs'})
					.insert({'bottom': new Element('span', {'class': 'btn btn-default', 'title': 'Edit'})
						.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-pencil'}) })
						.observe('click', function(){
							$$('.popover-loaded').each(function(item){
								jQuery(item).popover('hide');
							});
							tmp.me._openEditPage(row);
						})
					})
					.insert({'bottom': new Element('span', {'class': 'btn btn-danger', 'title': 'Delete'})
						.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-trash'}) })
						.observe('click', function(){
							if(!confirm('Are you sure you want to deactivate this item?'))
								return false;
							if(row.active)
								tmp.me._deactivateItem(this);
							$$('.popover-loaded').each(function(item){
								jQuery(item).popover('hide');
							});
						})
					}) ) 
				)
				.observe('click', function(){
					tmp.me._highlightSelectedRow(this);
					$$('.popover-loaded').each(function(item){
						jQuery(item).popover('hide');
					});
				})	
			});
		return tmp.row;
	}
	,_getNextPageBtn: function() {
		var tmp = {}
		tmp.me = this;
		return new Element('tfoot')
			.insert({'bottom': new Element('tr')
				.insert({'bottom': new Element('td', {'colspan': '8', 'class': 'text-center'})
					.insert({'bottom': new Element('span', {'class': 'btn btn-primary', 'data-loading-text':"Fetching more results ..."}).update('Show More')
						.observe('click', function() {
							tmp.me._pagination.pageNo = tmp.me._pagination.pageNo*1 + 1;
							jQuery(this).button('loading');
							tmp.me.getResults();
						})
					})
				})
			});
	}
	/**
	 * insted of delete item, deactivate it
	 */
	,_deactivateItem: function(btn) {
		var tmp = {}
		tmp.me = this;
		tmp.row = $(btn).up('[item_id]');
		tmp.item = tmp.row.retrieve('data');
		tmp.me.postAjax(tmp.me.getCallbackId('deactivateItems'), {'item_id': tmp.item.id}, {
			'onLoading': function() {
				if(tmp.row) {
					tmp.row.toggleClassName('danger');
					tmp.row.hide(); 
				}
			}
			,'onSuccess': function(sender, param){
				try {
					tmp.row.toggleClassName('danger');
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result.item)
						throw 'errror';
					$$('[item_id="'+ tmp.result.item.id +'"]').first().replace(tmp.me._getResultRow(tmp.result.item, false));
					tmp.me._highlightSelectedRow($$('[item_id="'+ tmp.result.item.id +'"]').first().down('.glyphicon.glyphicon-trash'));
				} catch(e) {
					tmp.me.showModalBox('<span class="text-danger">ERROR</span>', e, true);
				}
			}
		})
	}
});