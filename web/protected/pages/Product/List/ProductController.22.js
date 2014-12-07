/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new CRUDPageJs(), {
	manufactures: []
	,suppliers: []
	,productCategories: []
	,productStatuses: []
	,_showRightPanel: false
	,_getTitleRowData: function() {
		return {'sku': 'SKU', 'name': 'Product Name', 'manufacturer' : {'name': 'Brand'}, 'supplierCodes': [{'supplier': {'name': 'Supplier'}, 'code': ''}],  'active': 'act?', 'stockOnOrder': 'OnOrder', 'stockOnHand': 'OnHand', 'stockOnPO': 'OnPO'};
	}
	,toggleSearchPanel: function(panel) {
		var tmp = {};
		tmp.me = this;
		$(panel).toggle();
		tmp.me.deSelectProduct();
		return tmp.me;
	}
	/**
	 * Load the manufacturers
	 */
	,_loadManufactures: function(manufactures) {
		this.manufactures = manufactures;
		var tmp = {};
		tmp.me = this;
		tmp.selectionBox = $(tmp.me.searchDivId).down('[search_field="pro.manufacturerIds"]');
		tmp.me.manufactures.each(function(option) {
			tmp.selectionBox.insert({'bottom': new Element('option',{'value': option.id}).update(option.name) });
		});
		return this;
	}
	/**
	 * Load the _loadProductStatuses
	 */
	,_loadProductStatuses: function(productStatuses) {
		this.productStatuses = productStatuses;
		var tmp = {};
		tmp.me = this;
		tmp.selectionBox = $(tmp.me.searchDivId).down('[search_field="pro.productStatusIds"]');
		tmp.me.productStatuses.each(function(option) {
			tmp.selectionBox.insert({'bottom': new Element('option',{'value': option.id}).update(option.name) });
		});
		return this;
	}
	/**
	 * Load the suppliers
	 */
	,_loadSuppliers: function(suppliers) {
		this.suppliers = suppliers;
		var tmp = {};
		tmp.me = this;
		tmp.selectionBox = $(tmp.me.searchDivId).down('[search_field="pro.supplierIds"]');
		tmp.me.suppliers.each(function(option) {
			tmp.selectionBox.insert({'bottom': new Element('option',{'value': option.id}).update(option.name) });
		});
		return this;
	}
	/**
	 * Load thecategories
	 */
	,_loadCategories: function(categories) {
		this.categories = categories;
		var tmp = {};
		tmp.me = this;
		tmp.selectionBox = $(tmp.me.searchDivId).down('[search_field="pro.productCategoryIds"]');
		tmp.me.categories.sort(function(a, b){
			return a.namePath > b.namePath;
		}).each(function(option) {
			tmp.selectionBox.insert({'bottom': new Element('option',{'value': option.id}).update(option.namePath) });
		});
		return this;
	}
	/**
	 * initiating the chosen input
	 */
	,_loadChosen: function () {
		jQuery(".chosen").chosen({
				search_contains: true,
				inherit_select_classes: true,
				no_results_text: "Oops, nothing found!",
				width: "95%"
		});
		return this;
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
	 * Getting the supplier codes for display result list per row
	 */
	,_getSupplierCodes: function(supplierCodes, isTitle) {
		var tmp = {};
		tmp.me = this;
		tmp.supplierCodeString = [];
		supplierCodes.each(function(suppliercode) {
			tmp.supplierCodeString.push(isTitle === true ? 'Supplier' : '<abbr title="Code: '  + suppliercode.code + '">' + suppliercode.supplier.name + '</abbr>');
		})
		return tmp.supplierCodeString.join(', ');
	}
	/**
	 * Displaying the price matching result
	 */
	,_displayPriceMatchResult: function(prices) {
		var tmp = {};
		tmp.me = this;
		tmp.minPrice = 0;
		tmp.tbody = new Element('tbody');
		$H(prices["companyPrices"]).each(function(price){
			if(parseInt(price.value.price) !== 0) {
				if((parseInt(tmp.minPrice) === 0 && parseFloat(price.value.price) > 0) || parseFloat(price.value.price) < parseFloat(tmp.minPrice))
					tmp.minPrice = price.value.price;
			}
			tmp.tbody.insert({'bottom': new Element('tr')
				.insert({'bottom': new Element('td', {'colspan': 3}).update(price.key) })
				.insert({'bottom': new Element('td').update(price.value.priceURL && !price.value.priceURL.blank() ? new Element('a', {'href': price.value.priceURL, 'target': '__blank'}).update(tmp.me.getCurrency(price.value.price)) : tmp.me.getCurrency(price.value.price)) })
			})
		});
		tmp.priceDiff = parseFloat(prices.myPrice) - parseFloat(tmp.minPrice);
		tmp.priceDiffClass = '';
		if(parseInt(tmp.minPrice) !== 0) {
			if(parseInt(tmp.priceDiff) > 0)
				tmp.priceDiffClass = 'label label-danger';
			else if (parseInt(tmp.priceDiff) < 0)
				tmp.priceDiffClass = 'label label-success';
		}
		tmp.newDiv = new Element('table', {'class': 'table table-striped table-hover price-match-listing'})
			.insert({'bottom': new Element('thead')
				.insert({'bottom': new Element('tr')
					.insert({'bottom': new Element('th').update('SKU') })
					.insert({'bottom': new Element('th').update('My Price') })
					.insert({'bottom': new Element('th', {'class': 'price_diff'}).update('Price Diff.') })
					.insert({'bottom': new Element('th').update('Min Price') })
				})
			})
			.insert({'bottom': new Element('tbody')
				.insert({'bottom': new Element('tr')
					.insert({'bottom': new Element('td').update(prices.sku) })
					.insert({'bottom': new Element('td').update(tmp.me.getCurrency(prices.myPrice)) })
					.insert({'bottom': new Element('td', {'class': 'price_diff'}).update(new Element('span', {'class': '' + tmp.priceDiffClass}).update(tmp.me.getCurrency(tmp.priceDiff)) ) })
					.insert({'bottom': new Element('td', {'class': 'price_min'}).update(tmp.me.getCurrency(tmp.minPrice) ) })
				})
			})
			.insert({'bottom': new Element('thead')
				.insert({'bottom': new Element('tr')
					.insert({'bottom': new Element('th', {'colspan': 3}).update('Company') })
					.insert({'bottom': new Element('th').update('Price') })
				})
			})
			.insert({'bottom': tmp.tbody });
		return tmp.newDiv;
	}
	,_getInfoPanel: function(product) {
		var tmp = {};
		tmp.me = this;
		return new Element('div', {'id': 'info_panel_' + product.id})
			.insert({'bottom': new Element('div', {'class': 'col-md-6'})
				.insert({'bottom': new Element('div', {'class': 'panel panel-default price-match-div'})
					.insert({'bottom': new Element('div', {'class': 'panel-heading'}).update('<strong>Price Match</strong>') })
					.insert({'bottom': new Element('div', {'class': 'panel-body price-match-listing'}).update(tmp.me.getLoadingImg()) })
				})
			})
			.insert({'bottom': new Element('div', {'class': 'col-md-6'})
				.insert({'bottom': new Element('div', {'class': 'panel panel-default price-trend-div'})
					.insert({'bottom': new Element('div', {'class': 'panel-body'})
						.insert({'bottom': new Element('iframe', {'frameborder': '0', 'scrolling': 'auto', 'width': '100%', 'height': '400px'}) })
					})
				})
			})
			.insert({'bottom': new Element('div', {'class': 'col-md-6'})
				.insert({'bottom': new Element('div', {'class': 'panel panel-default'})
					.insert({'bottom': new Element('div', {'class': 'panel-body'}).update('<h4>Reserved for Next Phase of Developing</h4>')})
				})
			})
			.insert({'bottom': new Element('div', {'class': 'col-md-6'})
				.insert({'bottom': new Element('div', {'class': 'panel panel-default'})
					.insert({'bottom': new Element('div', {'class': 'panel-body'}).update('<h4>Reserved for Next Phase of Developing</h4>')})
				})
			});
	}
	,_showProductInfoOnRightPanel: function(product) {
		var tmp = {};
		tmp.me = this;
		tmp.infoPanel = tmp.me._getInfoPanel(product);
		tmp.infoPanel.down('.price-trend-div iframe').writeAttribute('src', '/statics/product/pricetrend.html?productid=' + product.id);
		tmp.me.postAjax(tmp.me.getCallbackId('priceMatching'), {'id': product.id}, {
			'onLoading': function() {
				tmp.infoPanel.down('.price-match-div .price-match-listing').replace(new Element('div', {'class': 'panel-body price-match-listing'}).update(tmp.me.getLoadingImg()));
			}
			,'onSuccess': function(sender, param) {
				try{
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result)
						return;
					if($('info_panel_' + product.id))
						$('info_panel_' + product.id).down('.price-match-div .price-match-listing').replace(tmp.me._displayPriceMatchResult(tmp.result));
				} catch (e) {
					tmp.me.showModalBox('Error', e, true);
				}
			}
		});
		return tmp.infoPanel;
	}
	,deSelectProduct: function() {
		var tmp = {};
		tmp.me = this;
		jQuery('.product_item.success', jQuery('#' + tmp.me.resultDivId)).removeClass('success').popover('hide');
		$(tmp.me.resultDivId).up('.list-panel').removeClassName('col-xs-4').addClassName('col-xs-12');
		jQuery('.hide-when-info', jQuery('#' + tmp.me.resultDivId)).show();
		tmp.me._showRightPanel = false;
		return tmp.me;
	}
	/**
	 * Displaying the selected product 
	 */
	,_displaySelectedProduct: function(item) {
		var tmp = {};
		tmp.me = this;
		$(tmp.me.resultDivId).up('.list-panel').removeClassName('col-xs-12').addClassName('col-xs-4');
		jQuery('.hide-when-info', jQuery('#' + tmp.me.resultDivId)).hide();
		tmp.me._showRightPanel = true;
		
		//remove all active class
		jQuery('.product_item.success', jQuery('#' + tmp.me.resultDivId)).removeClass('success').popover('hide');
		//mark this one as active
		tmp.selectedRow = jQuery('[product_id="' + item.id + '"]', jQuery('#' + tmp.me.resultDivId))
			.addClass('success');
		if(!tmp.selectedRow.hasClass('popover-loaded')) {
			tmp.selectedRow.popover({
				'title'    : '<div class="row"><div class="col-xs-10">Details for: ' + item.sku + '</div><div class="col-xs-2"><span class="btn btn-danger pull-right btn-sm" onclick="pageJs.deSelectProduct();"><span class="glyphicon glyphicon-remove"></span></span></div></div>',
				'html'     : true, 
				'placement': 'right', 
				'container': 'body', 
				'trigger'  : 'manual', 
				'viewport' : {"selector": ".list-panel", "padding": 0 },
				'content'  : function() { return tmp.me._showProductInfoOnRightPanel(item).wrap(new Element('div')).innerHTML; }, 
				'template' : '<div class="popover" role="tooltip" style="max-width: none; z-index: 0;"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>'
			})
			.addClass('popover-loaded');
		}
		tmp.selectedRow.popover('toggle');
		return tmp.me;
	}
	/**
	 * open the product details page
	 */
	,_openProductDetails: function(product) {
		var tmp = {};
		tmp.newWindow = window.open('/product/' + product.id + '.html', 'Product Details for: ' + product.sku, 'location=no, menubar=no, status=no, titlebar=no, fullscreen=no, toolbar=no, width=1024');
		tmp.newWindow.focus();
	}
	/**
	 * Getting each row for displaying the result list
	 */
	,_getResultRow: function(row, isTitle) {
		var tmp = {};
		tmp.me = this;
		tmp.tag = (tmp.isTitle === true ? 'th' : 'td');
		tmp.isTitle = (isTitle || false);
		tmp.price = '';
		if(row.prices) {
			row.prices.each(function(price) {
				if(price.type && parseInt(price.type.id) === 1) {
					tmp.price = price.price;
				}
			});
		}
		tmp.row = new Element('tr', {'class': 'visible-xs visible-md visible-lg visible-sm ' + (tmp.isTitle === true ? '' : 'product_item'), 'product_id' : row.id}).store('data', row)
			.insert({'bottom': new Element(tmp.tag, {'class': 'sku', 'title': row.name})
				.insert({'bottom': new Element('span', {'style': 'margin: 0 5px 0 0;'})
					.insert({'bottom': new Element('input', {'type': 'checkbox', 'class': 'product-selected'})
						.observe('click', function(){
							tmp.checked = this.checked;
							if(tmp.isTitle === true) {
								$(tmp.me.resultDivId).getElementsBySelector('.product_item .product-selected').each(function(el){
									el.checked = tmp.checked;
								});
							}
						})
					})
				})
				.insert({'bottom':tmp.isTitle === true ? row.sku : new Element('a', {'href': 'javascript: void(0);', 'class': 'sku-link'})
					.update(row.sku)
				}) 
			})
			.insert({'bottom': new Element(tmp.tag, {'class': 'product_name hidden-xs hide-when-info hidden-sm', 'style': (tmp.me._showRightPanel ? 'display: none' : '')}).update(row.name) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'product_price hidden-xs hide-when-info hidden-sm', 'style': (tmp.me._showRightPanel ? 'display: none' : '')}).update(tmp.isTitle === true ? 'Price' : (tmp.price.blank() ? '' : tmp.me.getCurrency(tmp.price))) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'manufacturer col-xs-1'}).update(row.manufacturer ? row.manufacturer.name : '') })
			.insert({'bottom': new Element(tmp.tag, {'class': 'supplier col-xs-1 hidden-sm'}).update(
					row.supplierCodes ? tmp.me._getSupplierCodes(row.supplierCodes, isTitle) : ''
			) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'qty col-xs-1 hidden-sm'}).update(
					tmp.isTitle === true ? 'Qty' : new Element('div', {'class': 'row'})
						.insert({'bottom': new Element(tmp.tag, {'class': 'stockOnPO col-xs-4', 'title': 'Stock on PurchaseOrder'}).update(row.stockOnPO) })
						.insert({'bottom': new Element(tmp.tag, {'class': 'stockOnHand col-xs-4', 'title': 'Stock on hand'}).update(row.stockOnHand) })
						.insert({'bottom': new Element(tmp.tag, {'class': 'stockOnOrder col-xs-4', 'title': 'Stock on Order'}).update(row.stockOnOrder) })
			) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'product_active col-xs-1  hidden-sm'})
				.insert({'bottom': (tmp.isTitle === true ? row.active : new Element('input', {'type': 'checkbox', 'disabled': true, 'checked': row.active}) ) })
			})
			.insert({'bottom': tmp.isTitle === true ? '' : new Element(tmp.tag, {'class': 'btns hidden-xs hide-when-info hidden-sm', 'style': (tmp.me._showRightPanel ? 'display: none' : '')})
				.insert({'bottom': new Element('span', {'class': 'btn btn-primary btn-xs'})
					.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-pencil'}) })
				})
				.observe('click', function(){
					tmp.me._openProductDetails(row);
				})
			});
		if(tmp.isTitle === false) {
			tmp.row.down('.sku-link').observe('click', function(){
				//display details of the selected item
				tmp.me._displaySelectedProduct(row);
			})
			.observe('dblclick', function(){
				tmp.me._openProductDetails(row);
			})
		}
		return tmp.row;
	}
});