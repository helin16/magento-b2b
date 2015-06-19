/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new BPCPageJs(), {
	id_wrapper: '' //the html id of the wrapper
	,_config: {}

	,load: function(predata) {
		var tmp = {}
		tmp.me = this;
		tmp.me.predata = predata;

		$(tmp.me.getHTMLID('contentDiv')).update('').insert({'bottom': tmp.me._config.container = tmp.me._getConifgDiv() });
		
		return tmp.me._loadSelect2()._loadBootstrapSwitch();
	}
	,_getConifgDiv: function() {
		var tmp = {}
		tmp.me = this;
		
		//proudct selector
		tmp.productsSelector = tmp.me.getFormGroup(new Element('label').update('Products: '),
				new Element('input', {'class': 'form-control select2', 'config': 'products', 'name': 'products', 'placeholder': 'SKU or Name'}) );
		
//		//product listing
//		tmp.productsListingHeaderNames = ['SKU', 'Name', 'Price', 'Cost', 'Active?'];
//		tmp.productsListing = tmp.me._config.productListingTable = new Element('table', {'class': 'table table-hover table-striped'})
//			.insert({'bottom': new Element('thead')
//				.insert({'bottom': tmp.productListingHeaderEl = new Element('tr', {'class': 'visible-xs visible-md visible-lg visible-sm'}) }) // class b/c boostrap bug
//			})
//			.insert({'bottom': new Element('tbody')
//			});
//		tmp.productsListingHeaderNames.each(function(name){
//			tmp.productListingHeaderEl.insert({'bottom': new Element('td').update(name) });
//		});
		
		//return div
		tmp.newDiv = new Element('div')  
			.insert({'bottom': new Element('div',  {'class': 'row'})
				.insert({'bottom': new Element('div', {'class': 'col-xs-12'})
					.insert({'bottom': tmp.productsSelector })
//					.insert({'bottom': tmp.productsListing })
				})
			});
		
		
		return tmp.newDiv;
	}
	
	,_loadSelect2: function() {
		var tmp = {};
		tmp.me = this;
		tmp.selectBox = jQuery('[config="products"]').select2({
			minimumInputLength: 1,
			multiple: true,
			dropdownAutoWidth : true,
			ajax: {
				delay: 250
				,multiple: true
				,url: '/ajax/getProducts'
				,type: 'POST'
				,data: function (params) {
					return {"searchTxt": params, 'entityName': 'Product', 'pageNo': 1};
				}
				,results: function(data, page, query) {
					tmp.result = [];
					if(data.resultData && data.resultData.items) {
						data.resultData.items.each(function(item){
							tmp.result.push({'id': item.id, 'text': item.name, 'data': item});
						});
					}
					return { 'results' : tmp.result };
				}
			}
			,cache: true
			,formatResult : function(result) {
				 return (!result) ? '' : tmp.me._formatProductResult(result.data);
			 }
			,formatSelection: function(result){
				return (!result) ? '' : tmp.me._formatProductSelection(result.data);
			 }
			,escapeMarkup: function (markup) { return markup; } // let our custom formatter work
		});
		
		return tmp.me;
	}
//	,_addToProductListing: function(product) {
//		var tmp = {};
//		tmp.me = this;
//		tmp.price = '';
//		if(product.prices) {
//			product.prices.each(function(price) {
//				if(price.type && parseInt(price.type.id) === 1) {
//					tmp.price = price.price;
//				}
//			});
//		};
//		tmp.me._config.productListingTable.down('tbody')
//			.insert({'bottom': new Element('tr').store(product)
//				.insert({'bottom': new Element('td').update(product.sku) })
//				.insert({'bottom': new Element('td').update(product.name) })
//				.insert({'bottom': new Element('td').update(tmp.price) })
//				.insert({'bottom': new Element('td').update((product.totalOnHandValue != 0 && product.stockOnHand != 0) ? tmp.me.getCurrency(product.totalOnHandValue/product.stockOnHand) : '') })
//				.insert({'bottom': new Element('td').insert({'bottom': new Element('input', {'type': 'checkbox', 'disabled': true, 'checked': product.active, 'data-size': 'mini', 'class': 'bootstrap-switch'}) }) })
//			});
//		
//		return tmp.me;
//	}
	/**
	 * format the result for the product from Select2
	 */
	,_formatProductResult: function(product) {
		return new Element('div', {'class': 'row product_item product_item_result'})
			.insert({'bottom': new Element('div', {'class': 'col-xs-6'}).update(product.sku)})
			.insert({'bottom': new Element('div', {'class': 'col-xs-6'}).update(product.name)});
	}
	/**
	 * format the selection for the product from Select2
	 */
	,_formatProductSelection: function(product) {
		return new Element('div', {'class': 'row product_item product_item_selection'}).store(product)
			.insert({'bottom': new Element('div', {'class': 'col-xs-12'})
				.insert({'bottom': new Element('small').update(product.sku) })
			});
	}
	,_loadBootstrapSwitch: function() {
		jQuery('.bootstrap-switch').bootstrapSwitch('state', true, true);
		return this;
	}
});