/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new BPCPageJs(), {
	resultDivId: '' //the html id of the result div
	,searchDivId: '' //the html id of the search div
	,totalNoOfItemsId: '' //the html if of the total no of items
	,_pagination: {'pageNo': 1, 'pageSize': 30} //the pagination details
	,_searchCriteria: {} //the searching criteria
	,_infoTypes:{} //the infotype ids
	,orderStatuses: [] //the order statuses object
	
	,setHTMLIds: function(resultDivId, searchDivId, totalNoOfItemsId) {
		this.resultDivId = resultDivId;
		this.searchDivId = searchDivId;
		this.totalNoOfItemsId = totalNoOfItemsId;
		return this;
	}
	
	,getSearchCriteria: function() {
		var tmp = {};
		tmp.me = this;
		if(tmp.me._searchCriteria === null)
			tmp.me._searchCriteria = {};
		tmp.nothingTosearch = true;
		$(tmp.me.searchDivId).getElementsBySelector('[search_field]').each(function(item) {
			tmp.me._searchCriteria[item.readAttribute('search_field')] = $F(item);
			if(($F(item) instanceof Array && $F(item).size() > 0) || (typeof $F(item) === 'string' && !$F(item).blank()))
				tmp.nothingTosearch = false;
		});
		if(tmp.nothingTosearch === true)
			tmp.me._searchCriteria = null;
		return this;
	}
	
	,getResults: function(reset, pageSize) {
		var tmp = {};
		tmp.me = this;
		tmp.reset = (reset || false);
		tmp.resultDiv = $(tmp.me.resultDivId);
		if(tmp.me._searchCriteria === null)
		{
			tmp.resultDiv.update(tmp.me.getAlertBox('', 'Nothing to search!').addClassName('alert-warning'));
			return;
		}
		if(tmp.reset === true)
			tmp.me._pagination.pageNo = 1;
		tmp.me._pagination.pageSize = (pageSize || tmp.me._pagination.pageSize);
		tmp.me.postAjax(tmp.me.getCallbackId('getProductList'), {'pagination': tmp.me._pagination, 'searchCriteria': tmp.me._searchCriteria}, {
			'onLoading': function () {
				jQuery('#' + tmp.me.searchDivId + ' #searchBtn').button('loading');
			}
			,'onSuccess': function(sender, param) {
				try{
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result)
						return;
					$(tmp.me.totalNoOfItemsId).update(tmp.result.pageStats.totalRows);
					
					//reset div
					if(tmp.reset === true) {
						tmp.titleRow = {'sku': "SKU", 'name': 'Product Name', 'active': 'Act?'};
						tmp.resultDiv.update(tmp.me._getResultRow(tmp.titleRow, true).wrap(new Element('thead')));
					}
					//remove next page button
					tmp.resultDiv.getElementsBySelector('.paginWrapper').each(function(item){
						item.remove();
					})
					
					//show all items
					tmp.tbody = $(tmp.resultDiv).down('tbody');
					if(!tmp.tbody)
						$(tmp.resultDiv).insert({'bottom': tmp.tbody = new Element('tbody') });
					tmp.result.items.each(function(item) {
						tmp.tbody.insert({'bottom': tmp.me._getResultRow(item) });
					})
					//show the next page button
					if(tmp.result.pageStats.pageNumber < tmp.result.pageStats.totalPages)
						tmp.resultDiv.insert({'bottom': tmp.me._getNextPageBtn().addClassName('paginWrapper') });
				} catch (e) {
					alert(e);
					tmp.resultDiv.insert({'bottom': tmp.me.getAlertBox('Error', e).addClassName('alert-danger') });
				}
			}
			,'onComplete': function() {
				jQuery('#' + tmp.me.searchDivId + ' #searchBtn').button('reset');
			}
		});
	}
	
	,_getNextPageBtn: function() {
		var tmp = {}
		tmp.me = this;
		return new Element('tfoot')
			.insert({'bottom': new Element('tr')
				.insert({'bottom': new Element('td', {'colspan': '5', 'class': 'text-center'})
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
	
//	,_openDetailsPage: function(row) {
//		var tmp = {};
//		tmp.me = this;
//		jQuery.fancybox({
//			'width'			: '95%',
//			'height'		: '95%',
//			'autoScale'     : false,
//			'autoDimensions': false,
//			'fitToView'     : false,
//			'autoSize'      : false,
//			'type'			: 'iframe',
//			'href'			: '/orderdetails/' + row.id + '.html',
//			'beforeClose'	    : function() {
//				if($(tmp.me.resultDivId).down('.row[order_id=' + row.id + ']'))
//					$(tmp.me.resultDivId).down('.row[order_id=' + row.id + ']').replace(tmp.me._getResultRow($$('iframe.fancybox-iframe').first().contentWindow.pageJs._order));
//			}
// 		});
//		return tmp.me;
//	}
	
	,_saveItem: function(btn, savePanel, attrName) {
		var tmp = {};
		tmp.me = this;
		tmp.data = tmp.me._collectFormData(savePanel, attrName);
		if(tmp.data === null)
			return;
		
		tmp.me.postAjax(tmp.me.getCallbackId('saveItem'), {'item': tmp.data}, {
			'onLoading': function () { savePanel.addClassName('item_row').writeAttribute('item_id', tmp.data.id).hide(); }
			,'onSuccess': function(sender, param) {
				try{
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result || !tmp.result.item)
						return;
					tmp.row = $(tmp.me.resultDivId).down('tbody').down('.item_row[item_id=' + tmp.result.item.id + ']');
					tmp.newRow = tmp.me._getResultRow(tmp.result.item).addClassName('item_row').writeAttribute('item_id', tmp.result.item.id);
					if(!tmp.row)
					{
						$(tmp.me.resultDivId).down('tbody').insert({'top': tmp.newRow });
						savePanel.remove();
						$(tmp.me.totalNoOfItemsId).update($(tmp.me.totalNoOfItemsId).innerHTML * 1 + 1);
					}
					else
					{
						tmp.row.replace(tmp.newRow);
					}
				
				} catch (e) {
					tmp.me.showModalBox('<span class="text-danger">ERROR:</span>', e, true);
					savePanel.show();
				}
			}
		});
		return tmp.me;
	}
	
	,_deleteItem: function(row) {
		var tmp = {};
		tmp.me = this;
		tmp.row = $(tmp.me.resultDivId).down('tbody').down('.item_row[item_id=' + row.id + ']');
		tmp.me.postAjax(tmp.me.getCallbackId('deleteItems'), {'ids': [row.id]}, {
			'onLoading': function () { 
				if(tmp.row) {
					tmp.row.hide(); 
				}
			}
			,'onSuccess': function(sender, param) {
				try{
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result)
						return;
					tmp.count = $(tmp.me.totalNoOfItemsId).innerHTML * 1 - 1;
					$(tmp.me.totalNoOfItemsId).update(tmp.count <= 0 ? 0 : tmp.count);
					if(tmp.row) {
						tmp.row.remove();
					}
				} catch (e) {
					tmp.me.showModalBox('<span class="text-danger">ERROR</span>', e, true);
					if(tmp.row) {
						tmp.row.show();
					}
				}
			}
		});
		return tmp.me;
	}
	
	,_getEditPanel: function(row) {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = new Element('tr', {'class': 'save-item-panel info'}).store('data', row)
			.insert({'bottom': new Element('input', {'type': 'hidden', 'save-item-panel': 'id', 'value': row.id ? row.id : ''}) })
			.insert({'bottom': new Element('td', {'class': 'form-group'})
				.insert({'bottom': new Element('input', {'required': true, 'class': 'form-control', 'placeholder': 'The SKU of product', 'save-item-panel': 'name', 'value': row.sku ? row.sku : ''}) })
			})
			.insert({'bottom': new Element('td', {'class': 'form-group'})
				.insert({'bottom': new Element('input', {'class': 'form-control', 'placeholder': 'The name of the Product', 'save-item-panel': 'description', 'value': row.name ? row.name : ''}) })
			})
			.insert({'bottom': new Element('td', {'class': 'text-right'})
				.insert({'bottom':  new Element('span', {'class': 'btn-group btn-group-sm'})
					.insert({'bottom': new Element('span', {'class': 'btn btn-success', 'title': 'Save'})
						.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-ok'}) })
						.observe('click', function(){
							tmp.btn = this;
							tmp.me._saveItem(tmp.btn, $(tmp.btn).up('.save-item-panel'), 'save-item-panel');
						})
					})
					.insert({'bottom': new Element('span', {'class': 'btn btn-danger', 'title': 'Delete'})
						.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-remove'}) })
						.observe('click', function(){
							if(row.id)
								$(this).up('.save-item-panel').replace(tmp.me._getResultRow(row).addClassName('item_row').writeAttribute('item_id', row.id));
							else
								$(this).up('.save-item-panel').remove();
						})
					})
				})
			})
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