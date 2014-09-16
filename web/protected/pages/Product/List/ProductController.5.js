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
	,_loadProductCategories: function(productCategories) {
		this.productCategories = productCategories;
		var tmp = {};
		tmp.me = this;
		tmp.selectionBox = $(tmp.me.searchDivId).down('#productCategoryId');
		tmp.me.productCategories.each(function(option) {
			tmp.selectionBox.insert({'bottom': new Element('option',{'value': option.id}).update(option.name) });
		});
		return this;
	}
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
	,openToolsURL: function(url, refreshFunc) {
		var tmp = {};
		tmp.me = this;
		tmp.options = {
				'width'			: '95%',
				'height'		: '95%',
				'autoScale'     : false,
				'autoDimensions': false,
				'fitToView'     : false,
				'autoSize'      : false,
				'type'			: 'iframe',
				'href'			: url
	 		};
		if(typeof(refreshFunc) === 'function') {
			tmp.options.beforeClose = refreshFunc;
		}
		jQuery.fancybox(tmp.options);
		return tmp.me;
	}
	,iframeSrc: function(url){
		var tmp = {};
		tmp.me = this;
	    $('productTrend').src = url;
	    $('productTrend').src = $('productTrend').src;
		return tmp.me;
	}
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
	,_getChildCategoryJson: function(category, selectedCateIds) {
		var tmp = {};
		tmp.me = this;
		tmp.cate = {'text': category.name, 'id': category.id};
		if(selectedCateIds.indexOf(category.id) >= 0){
			tmp.cate.checked = true;
		}
		if(category.children && category.children.size() > 0) {
			tmp.cate.children = [];
			category.children.each(function(child){
				tmp.cate.children.push( tmp.me._getChildCategoryJson(child, selectedCateIds) );
			});
		}
		return tmp.cate;
	}
	/**
	 * initialising the tree
	 */
	,_initTree: function(categories, selector) {
		var tmp = {};
		tmp.me = this;
		tmp.me._item.categories= {};
		tmp.categoies = [];
		tmp.selectedCateIds = [];
		tmp.me._item.categories.each(function(cate) {
			tmp.selectedCateIds.push(cate.id);
		})
		categories.each(function(category) {
			tmp.categoies.push(tmp.me._getChildCategoryJson(category, tmp.selectedCateIds));
		});
		jQuery(selector).tree({
			data: tmp.categoies
		});
		return tmp.me;
	}
	/**
	 * Ajax: getting all categories from server
	 */
	,_getCategories: function(resultDiv) {
		var tmp = {};
		tmp.me = this;
		tmp.me.postAjax(tmp.me.getCallbackId('getCategories'), {}, {
			'onLoading': function (sender, param) {
				$(resultDiv).update(tmp.me.getLoadingImg());
			}
			, 'onSuccess': function (sender, param) {
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result || !tmp.result.items)
						return;
					tmp.treeDiv = new Element('ul', {'id': tmp.me._productTreeId, 'data-options': 'animate:true, checkbox:true'}) ;
					$(resultDiv).update(new Element('div', {'class': 'easyui-panel'}).update(tmp.treeDiv) );
					tmp.me._signRandID(tmp.treeDiv);
					tmp.me._initTree(tmp.result.items, '#' + tmp.treeDiv.id);
					$(resultDiv).addClassName('loaded');
				} catch (e) {
					$(resultDiv).update(tmp.me.getAlertBox('Error:', e).addClassName('alert-danger'));
				}
			}
		});
		return tmp.me;
	}
	/**
	 * Getting the product category panel
	 */
	,_getCategoryPanel: function(item) {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = new Element('div', {'class': 'panel panel-default'})
			.insert({'bottom': new Element('div', {'class': 'panel-heading'})
				.insert({'bottom': new Element('a', {'href': 'javascript: void(0);'})
					.insert({'bottom': new Element('strong').update( 'Categories: ' + (tmp.me._item.categories ? tmp.me._item.categories.size() + ' Selected' : ''))	})
				})
				.observe('click', function() {
					tmp.btn = this;
					tmp.panelBody = $(tmp.btn).up('.panel').down('.panel-body');
					if(!tmp.panelBody.hasClassName('loaded')) {
						tmp.me._getCategories(tmp.panelBody);
					}
					tmp.panelBody.toggle();
				})
			})
			.insert({'bottom': new Element('div', {'class': 'panel-body', 'style': 'display: none'}) })
		return tmp.newDiv;
	}
	,_getSupplierCodes: function(supplierCodes, isTitle) {
		var tmp = {};
		tmp.me = this;
		tmp.supplierCodeString = [];
		supplierCodes.each(function(suppliercode) {
			tmp.supplierCodeString.push(isTitle === true ? 'Supplier' : '<abbr title="Code: '  + suppliercode.code + '">' + suppliercode.supplier.name + '</abbr>');
		})
		return tmp.supplierCodeString.join(', ');
	}
	,_getResultRow: function(row, isTitle) {
		var tmp = {};
		tmp.me = this;
		//tmp.me._item = tmp.me;
		//console.debug(tmp.me_get);
		tmp.tag = (tmp.isTitle === true ? 'th' : 'td');
		tmp.isTitle = (isTitle || false);
		tmp.row = new Element('tr', {'class': (tmp.isTitle === true ? '' : 'product_item'), 'product_id' : row.id}).store('data', row)
			.observe('click', function(){
				if(document.getElementsByClassName("product_item item_row active")[0])
					document.getElementsByClassName("product_item item_row active")[0].removeClassName('active')
				this.addClassName('active')
			})
			.insert({'bottom': new Element(tmp.tag, {'class': 'sku'}).update(row.sku) 
				.observe('click', function(){
					//tmp.me._getCategories(this);
				})
			})
			//.insert({'bottom': tmp.me._getCategoryPanel(tmp.me._item) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'name'}).update(row.name) 
				.observe('click', function(){
					tmp.me.iframeSrc('/statics/product/pricetrend.html?productid=' + row.id);
					
					tmp.me.postAjax(tmp.me.getCallbackId('priceMatching'), {'id': row.id}, {
						'onSuccess': function(sender, param) {
							try{
								tmp.result = tmp.me.getResp(param, false, true);
								if(!tmp.result)
									return;
								
								tmp.me._displayPriceMatchResult(tmp.result);
							} catch (e) {
								alert(e);
							}
						}
					});
				})
				.observe('dblclick', function(){
					tmp.me.openToolsURL('/product/' + row.id + '.html',
						function() {
							if($(tmp.me.resultDivId).down('.product_item[product_id=' + row.id + ']'))
								$(tmp.me.resultDivId).down('.product_item[product_id=' + row.id + ']').replace(tmp.me._getResultRow($$('iframe.fancybox-iframe').first().contentWindow.pageJs._item));
						}
					)
				})
			})
			.insert({'bottom': new Element(tmp.tag, {'class': 'manufacturer'}).update(row.manufacturer ? row.manufacturer.name : '') })
			.insert({'bottom': new Element(tmp.tag, {'class': 'supplier col-xs-2'}).update(
					row.supplierCodes ? tmp.me._getSupplierCodes(row.supplierCodes, isTitle) : ''
			) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'product_active col-xs-1'})
				.insert({'bottom': (tmp.isTitle === true ? row.active : new Element('input', {'type': 'checkbox', 'disabled': false, 'checked': row.active})
					.observe('click', function(){
						tmp.active = $(this).checked;
						tmp.me.postAjax(tmp.me.getCallbackId('toggleItem'), {'id': row.id, 'active': tmp.active}, {});
					})
				) })
			})
			.insert({'bottom': new Element(tmp.tag, {'class': 'text-right btns col-xs-1'}).update(
				tmp.isTitle === true ?  
				(new Element('span', {'class': 'btn btn-primary btn-xs', 'title': 'New'})
					.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-plus'}) })
					.insert({'bottom': ' NEW' })
					.observe('click', function(){
						tmp.me.openToolsURL('/product/' + 'new' + '.html',
								function() {
									if($(tmp.me.resultDivId).down('.product_item[product_id=' + row.id + ']'))
										$(tmp.me.resultDivId).down('.product_item[product_id=' + row.id + ']').replace(tmp.me._getResultRow($$('iframe.fancybox-iframe').first().contentWindow.pageJs._item));
								}
							)
					})
				)
				: (new Element('span', {'class': 'btn-group btn-group-xs'})
					.insert({'bottom': new Element('span', {'class': 'btn btn-default', 'title': 'Edit'})
						.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-pencil'}) })
						.observe('click', function(){
							tmp.me.openToolsURL('/product/' + row.id + '.html',
								function() {
									if($(tmp.me.resultDivId).down('.product_item[product_id=' + row.id + ']'))
										$(tmp.me.resultDivId).down('.product_item[product_id=' + row.id + ']').replace(tmp.me._getResultRow($$('iframe.fancybox-iframe').first().contentWindow.pageJs._item));
								}
							)
						})
					})
					.insert({'bottom': new Element('span', {'class': 'btn btn-default', 'title': 'Trend'})
						.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-cog'}) })
						.observe('click', function(){
							tmp.me.iframeSrc('/statics/product/pricetrend.html?productid=' + row.id);
							tmp.me.postAjax(tmp.me.getCallbackId('priceMatching'), {'id': row.id}, {
								'onSuccess': function(sender, param) {
									try{
										tmp.result = tmp.me.getResp(param, false, true);
										if(!tmp.result)
											return;
										
										tmp.me._displayPriceMatchResult(tmp.result);
									} catch (e) {
										alert(e);
									}
								}
							});
						})
					}) ) 
			) })
		;
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
					.insert({'bottom': new Element('th').update('') })
					.insert({'bottom': new Element('th').update('') })
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