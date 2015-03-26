/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new CRUDPageJs(), {
	_getTitleRowData: function() {
		return {'applyDate': "Apply Date", 'applyTo': 'Apply To', 'creditNoteNo': 'Credit Note No', 'description': 'Description', 'totalValue': 'Total Value', 'active': 'Active'
				,'customer': {'name': 'Name'}, 'order': {'orderNo': 'Order No'}
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
		tmp.selectEl = new Element('input', {'class': 'select2 form-control', 'data-placeholder': 'search for a Customers', 'search_field': 'cust.id'}).insert({'bottom': new Element('option').update('')});
		$('searchDiv').down('[search_field="cust.id"]').replace(tmp.selectEl);
		jQuery('.select2[search_field="cust.id"]').select2({
			allowClear: true,
			hidden: true,
			multiple: true,
			 ajax: { url: "/ajax/getCustomers",
					 dataType: 'json',
					 delay: 10,
					 data: function (params) {
						 return {
							 searchTxt: params, // search term
						 };
					 },
					 results: function (data) {
						 tmp.result = [];
						 data.resultData.items.each(function(item){
							 tmp.result.push({"id": item.id, 'text': item.name, 'data': item});
						 })
		                return {
		                    results:  tmp.result 
		                };
		             },
					 cache: true
				 },
				 formatResult : function(result) {
					 if(!result)
						 return '';
					 return '<div value=' + result.data.id + '>' + result.data.name + '</div >';
				 },
				 escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
				 minimumInputLength: 1,
		});
		tmp.selectEl = new Element('input', {'class': 'select2 form-control', 'data-placeholder': 'search for a Products', 'search_field': 'pro.ids'}).insert({'bottom': new Element('option').update('')});
		$('searchDiv').down('[search_field="pro.ids"]').replace(tmp.selectEl);
		jQuery('.select2[search_field="pro.ids"]').select2({
			allowClear: true,
			hidden: true,
			multiple: true,
			ajax: { url: "/ajax/getProducts",
				dataType: 'json',
				delay: 10,
				data: function (params) {
					return {
						searchTxt: params, // search term
						pageNo: 1,
						pageSize: 10,
					};
				},
				results: function (data) {
					tmp.result = [];
					data.resultData.items.each(function(item){
						tmp.result.push({"id": item.id, 'text': item.name, 'data': item});
					})
					return {
						results:  tmp.result 
					};
				},
				cache: true
			},
			formatResult : function(result) {
				if(!result)
					return '';
				return '<div value=' + result.data.id + '>' + result.data.name + '</div >';
			},
			escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
			minimumInputLength: 3,
		});
		tmp.selectEl = new Element('select', {'class': 'select2 form-control', 'data-placeholder': 'search for a Apply To', 'search_field': 'cn.applyTo', 'multiple': true}).insert({'bottom': new Element('option').update('')});
		tmp.me._applyToOptions.each(function(item){
			tmp.selectEl.insert({'bottom': new Element('option', {'value': item}).update(item)});
		});
		$('searchDiv').down('[search_field="cn.applyTo"]').replace(tmp.selectEl);
		jQuery('.select2[search_field="cn.applyTo"]').select2({
			allowClear: true,
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
			'href'			: '/creditnote/' + (row && row.id ? row.id : 'new') + '.html',
			'beforeClose'	: function() {
				tmp.iframeJs = $$('iframe.fancybox-iframe').first().contentWindow.pageJs;
				tmp.newRow = tmp.iframeJs && tmp.iframeJs._creditNote && tmp.iframeJs._creditNote.id ? tmp.me._getResultRow(tmp.iframeJs._creditNote) : null;
				if(tmp.newRow !== null) {
					if(row && row.id) {
						if($(tmp.me.resultDivId).down('.item_row[item_id=' + row.id + ']'))
							$(tmp.me.resultDivId).down('.item_row[item_id=' + row.id + ']').replace(tmp.newRow);
					} else {
						$(tmp.me.resultDivId).insert({'top': tmp.newRow });
					}
				}
			}
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
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-1'}).update(row.creditNoteNo).setStyle('cursor: pointer;')
				.observe('click',function(){
					tmp.me._openEditPage(row);
				})
			})
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-1'}).update(row.applyTo)})
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-1'}).update(row.description)})
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-1'}).update(tmp.isTitle ? tmp.me._getTitleRowData().totalValue : tmp.me.getCurrency(row.totalValue))})
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-1'}).update(row.applyDate)})
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-1'}).update(row.customer.name)})
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-1'}).update(row.order.orderNo ? row.order.orderNo : '')})
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-1'}).update(tmp.isTitle ? 'Credit NoteItems' : row.creditNoteItems ? row.creditNoteItems.length : '')})
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-1'}).setStyle('display: none;')
				.insert({'bottom': (tmp.isTitle === true ? row.active : new Element('input', {'type': 'checkbox', 'disabled': true, 'checked': row.active}) ) })
			})
			
			.insert({'bottom': new Element(tmp.tag, {'class': 'text-right col-xs-1'}).update(
				tmp.isTitle === true ?  
				''
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
	,init: function(reset, pageSize) {
		var tmp = {};
		tmp.me = this;
		
		if(tmp.me._order && jQuery.isNumeric(tmp.me._order.id) && $F($$('[search_field="ord.orderNo"]').first()).empty()) {
			$$('[search_field="ord.orderNo"]').first().value = tmp.me._order.orderNo;
			$('searchBtn').click();
		}
		tmp.me.getResults(reset, pageSize);
		return tmp.me;
	}
});