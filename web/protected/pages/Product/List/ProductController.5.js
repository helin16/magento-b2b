/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new CRUDPageJs(), {
	manufactures: []
	,suppliers: []
	,productCategories: []
	,_productTreeId: 'product_category_tree' //the html id of the tree
	,_htmlIDs: {'infoPanel': 'product-info-panel'} //the html ids
	,_getTitleRowData: function() {
		return {'sku': 'SKU', 'name': 'Product Name', 'manufacturer' : {'name': 'Brand'}, 'supplierCodes': [{'supplier': {'name': 'Supplier'}, 'code': ''}],  'active': 'act?'};
	}
	,setItem: function(item) {
		this._item = item;
		return this;
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
		$$(".chosen").each(function(item) {
			item.store('chosen', new Chosen(item, {
				disable_search_threshold: 10,
				no_results_text: "Oops, nothing found!",
				width: "95%"
			}) );
		});
		return this;
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
	
	,_displayPriceMatchResult: function(prices) {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = new Element('table', {'class': 'table table-striped price-match-listing'})
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
					.insert({'bottom': new Element('td', {'class': 'price_diff'}).update(tmp.me.getCurrency(prices.priceDiff) ) })
					.insert({'bottom': new Element('td', {'class': 'price_min'}).update(new Element('a', {"href": "javascript: void(0);"})
						.update(tmp.me.getCurrency(prices.minPrice))
						.observe('click', function() {
							tmp.table = new Element('table', {'class': 'table table-striped'})
								.insert({'bottom': new Element('thead')
									.insert({'bottom': new Element('tr')
										.insert({'bottom': new Element('th').update('Company') })
										.insert({'bottom': new Element('th').update('Price') })
									})
								})
								.insert({'bottom': tmp.tbody = new Element('tbody') });
							$H(prices["companyPrices"]).each(function(price){
								tmp.tbody.insert({'bottom': new Element('tr')
									.insert({'bottom': new Element('td').update(price.key) })
									.insert({'bottom': new Element('td').update(tmp.me.getCurrency(price.value.price)) })
								})
							});
							tmp.me.showModalBox('Min Price Details: ', tmp.table, false);
						})
					) })
				})
			});
		return tmp.newDiv;
	}
	,_getInfoPanel: function(product) {
		var tmp = {};
		tmp.me = this;
		return new Element('div', {'id': tmp.me._htmlIDs.infoPanel})
			.insert({'bottom': new Element('div', {'class': 'col-lg-6'})
				.insert({'bottom': new Element('div', {'class': 'panel panel-default price-trend-div'})
					.insert({'bottom': new Element('div', {'class': 'panel-body'})
						.insert({'bottom': new Element('iframe', {'frameborder': '0', 'scrolling': 'auto', 'width': '100%', 'height': '400px'}) })
					})
				})
			})
			.insert({'bottom': new Element('div', {'class': 'col-lg-6'})
				.insert({'bottom': new Element('div', {'class': 'panel panel-default price-match-div'})
					.insert({'bottom': new Element('div', {'class': 'panel-heading'}).update('<strong>Price Match</strong>') })
					.insert({'bottom': new Element('div', {'class': 'panel-body price-match-listing'}).update(tmp.me.getLoadingImg()) })
				})
			})
			.insert({'bottom': new Element('div', {'class': 'col-lg-6'})
				.insert({'bottom': new Element('div', {'class': 'panel panel-default'})
					.insert({'bottom': new Element('div', {'class': 'panel-body'}).update('<h4>Reserved for Next Phase of Developing</h4>')})
				})
			})
			.insert({'bottom': new Element('div', {'class': 'col-lg-6'})
				.insert({'bottom': new Element('div', {'class': 'panel panel-default'})
					.insert({'bottom': new Element('div', {'class': 'panel-body'}).update('<h4>Reserved for Next Phase of Developing</h4>')})
				})
			});
	}
	,_showProductInfoOnRightPanel: function(product) {
		var tmp = {};
		tmp.me = this;
		tmp.infoPanel = $(tmp.me._htmlIDs.infoPanel);
		if(!tmp.infoPanel)
			$(tmp.me.resultDivId).up('.list-panel').insert({'after': tmp.me._getInfoPanel(product).wrap(new Element('div').addClassName('col-lg-8')) });
		$(tmp.me._htmlIDs.infoPanel).down('.price-trend-div iframe').writeAttribute('src', '/statics/product/pricetrend.html?productid=' + product.id);
		tmp.me.postAjax(tmp.me.getCallbackId('priceMatching'), {'id': product.id}, {
			'onLoading': function() {
			}
			,'onSuccess': function(sender, param) {
				try{
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result)
						return;
					$(tmp.me._htmlIDs.infoPanel).down('.price-match-div .price-match-listing').replace(tmp.me._displayPriceMatchResult(tmp.result));
				} catch (e) {
					tmp.me.showModalBox('Error', e, true);
				}
			}
		});
		return tmp.me;
	}
	/**
	 * Displaying the selected product 
	 */
	,_displaySelectedProduct: function(item) {
		var tmp = {};
		tmp.me = this;
		//remove all active class
		$(tmp.me.resultDivId).getElementsBySelector('.product_item.success').each(function(el){
			el.removeClassName('success');
		});
		//mark this one as active
		$(tmp.me.resultDivId).down('[product_id="' + item.id + '"]').addClassName('success');
		$(tmp.me.resultDivId).getElementsBySelector('.product_name').each(function(el) {
			el.remove();
		});
		$(tmp.me.resultDivId).up('.list-panel').removeClassName('col-lg-12').addClassName('col-lg-4');
		return tmp.me._showProductInfoOnRightPanel(item);
	}
	/**
	 * Getting each row for displaying the result list
	 */
	,_getResultRow: function(row, isTitle) {
		var tmp = {};
		tmp.me = this;
		tmp.tag = (tmp.isTitle === true ? 'th' : 'td');
		tmp.isTitle = (isTitle || false);
		tmp.row = new Element('tr', {'class': (tmp.isTitle === true ? '' : 'product_item'), 'product_id' : row.id}).store('data', row)
			.insert({'bottom': new Element(tmp.tag)
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
			.insert({'bottom': new Element(tmp.tag, {'class': 'sku', 'title': row.name}).update(row.sku) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'product_name'}).update(row.name) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'manufacturer col-xs-2'}).update(row.manufacturer ? row.manufacturer.name : '') })
			.insert({'bottom': new Element(tmp.tag, {'class': 'supplier col-xs-2'}).update(
					row.supplierCodes ? tmp.me._getSupplierCodes(row.supplierCodes, isTitle) : ''
			) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'product_active col-xs-1'})
				.insert({'bottom': (tmp.isTitle === true ? row.active : new Element('input', {'type': 'checkbox', 'disabled': true, 'checked': row.active}) ) })
			});
		if(tmp.isTitle === false) {
			tmp.row.observe('click', function(){
				//display details of the selected item
				tmp.me._displaySelectedProduct(row);
			})
			.observe('dblclick', function(){
				tmp.me.openToolsURL('/product/' + row.id + '.html',
					function() {
						if($(tmp.me.resultDivId).down('.product_item[product_id=' + row.id + ']'))
							$(tmp.me.resultDivId).down('.product_item[product_id=' + row.id + ']').replace(tmp.me._getResultRow($$('iframe.fancybox-iframe').first().contentWindow.pageJs._item));
					}
				)
			})
		}
		return tmp.row;
	}
});