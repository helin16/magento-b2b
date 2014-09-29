/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new CRUDPageJs(), {
	manufactures: []
	,suppliers: []
	,productCategories: []
	,_productTreeId: 'product_category_tree' //the html id of the tree
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
		tmp.selectionBox = $(tmp.me.searchDivId).down('#productBrandId');
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
		tmp.selectionBox = $(tmp.me.searchDivId).down('#productSupplierId');
		tmp.me.suppliers.each(function(option) {
			tmp.selectionBox.insert({'bottom': new Element('option',{'value': option.id}).update(option.name) });
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
	/**
	 * Displaying the selected product 
	 */
	,_displaySelectedProduct: function(item) {
		var tmp = {};
		tmp.me = this;
		tmp.me.iframeSrc('/statics/product/pricetrend.html?productid=' + item.id);
		tmp.me.postAjax(tmp.me.getCallbackId('priceMatching'), {'id': item.id}, {
			'onSuccess': function(sender, param) {
				try{
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result)
						return;
					tmp.me._displayPriceMatchResult(tmp.result);
				} catch (e) {
					tmp.me.showModalBox('Error', e, true);
				}
			}
		});
		return tmp.me;
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
			.insert({'bottom': new Element(tmp.tag, {'class': 'sku', 'title': row.name}).update(row.sku) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'manufacturer col-xs-2'}).update(row.manufacturer ? row.manufacturer.name : '') })
			.insert({'bottom': new Element(tmp.tag, {'class': 'supplier col-xs-2'}).update(
					row.supplierCodes ? tmp.me._getSupplierCodes(row.supplierCodes, isTitle) : ''
			) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'product_active col-xs-1'})
				.insert({'bottom': (tmp.isTitle === true ? row.active : new Element('input', {'type': 'checkbox', 'disabled': true, 'checked': row.active}) ) })
			});
		if(tmp.isTitle === false) {
			tmp.row.observe('click', function(){
				//remove all active class
				$(tmp.me.resultDivId).getElementsBySelector('.product_item.active').each(function(item){
					item.removeClassName('active');
				});
				//mark this one as active
				$(this).addClassName('active');
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
	,_displayPriceMatchResult: function(prices) {
		var tmp = {};
		tmp.me = this;
		$('priceMatchResult').innerHTML = "";
		$('priceMatchResult').style.display="inherit";
		$('priceMatchResult').insert({'bottom': new Element('table', {'class': 'table table-striped'})
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
					.insert({'bottom': new Element('td').update(prices.myPrice) })
					.insert({'bottom': new Element('td', {'class': 'price_diff'}).update(prices.priceDiff) })
					.insert({'bottom': new Element('td', {'class': 'price_min'}).update(prices.minPrice) })
				})
			})

			.insert({'bottom': new Element('thead')
				.insert({'bottom': new Element('tr')
					.insert({'bottom': new Element('th').update('CPL') })
					.insert({'bottom': new Element('th').update('MSY') })
					.insert({'bottom': new Element('th').update('PC DIY') })
					.insert({'bottom': new Element('th').update('PCCG') })
					.insert({'bottom': new Element('th').update('Scorp Tech') })
					.insert({'bottom': new Element('th').update('Umart') })
				})
			})
			.insert({'bottom': new Element('tbody')
				.insert({'bottom': new Element('tr')
				.insert({'bottom': new Element('td').update(prices["companyPrices"]["CPL"]["price"]) })
				.insert({'bottom': new Element('td').update(prices["companyPrices"]["MSY"]["price"]) })
				.insert({'bottom': new Element('td').update(prices["companyPrices"]["PC DIY"]["price"]) })
				.insert({'bottom': new Element('td').update(prices["companyPrices"]["PCCG"]["price"]) })
				.insert({'bottom': new Element('td').update(prices["companyPrices"]["Scorp Tech"]["price"]) })
				.insert({'bottom': new Element('td').update(prices["companyPrices"]["Umart"]["price"]) })
				})
			})
		});
	}
});