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
	,_nextPageColSpan: 9

	,_getTitleRowData: function() {
		return {'sku': 'SKU', 'name': 'Product Name','locations': 'Locations', 'invenAccNo': 'AccNo.', 'manufacturer' : {'name': 'Brand'}, 'supplierCodes': [{'supplier': {'name': 'Supplier'}, 'code': ''}],  'active': 'act?', 'stockOnOrder': 'OnOrder', 'stockOnHand': 'OnHand', 'stockOnPO': 'OnPO'};
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
		jQuery(".chosen").select2();
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
				else {
					tmp.me.deSelectProduct();
					tmp.me.getSearchCriteria().getResults(true, tmp.me._pagination.pageSize);
				}
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
			tmp.supplierCodeString.push(isTitle === true ? 'Supplier' : '<abbr title="Code: '  + suppliercode.code + '">' + (suppliercode.supplier && suppliercode.supplier.name ? suppliercode.supplier.name : '') + '</abbr>');
		})
		return tmp.supplierCodeString.join(', ');
	}
	/**
	 * Getting the locations for a product
	 */
	,_getLocations: function (locations, isTitle) {
		var tmp = {};
		tmp.me = this;
		if(isTitle === true)
			return 'Locations';
		tmp.locationStrings = [];
		locations.each(function(location) {
			tmp.locationStrings.push('<div><small><strong class="hidden-xs hide-when-info hidden-sm">' + location.type.name + ': </strong><abbr title="Type: '  + location.type.name + '">' + location.value + '</abbr></small></div>');
		})
		return tmp.locationStrings.join('');
	}
	/**
	 * Displaying the price matching result
	 */
	,_displayPriceMatchResult: function(prices, productId) {
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
					.insert({'bottom': new Element('td').update(tmp.priceInput = new Element('input', {'class': "click-to-edit price-input", 'value': tmp.me.getCurrency(prices.myPrice), 'product-id': productId}) ) })
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
						$('info_panel_' + product.id).down('.price-match-div .price-match-listing').replace(tmp.me._displayPriceMatchResult(tmp.result, product.id));
					tmp.me._bindPriceInput();
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
	,getResults: function(reset, pageSize) {
		var tmp = {};
		tmp.me = this;
		tmp.reset = (reset || false);
		tmp.resultDiv = $(tmp.me.resultDivId);

		if(tmp.reset === true)
			tmp.me._pagination.pageNo = 1;
		tmp.me._pagination.pageSize = (pageSize || tmp.me._pagination.pageSize);
		tmp.me.postAjax(tmp.me.getCallbackId('getItems'), {'pagination': tmp.me._pagination, 'searchCriteria': tmp.me._searchCriteria}, {
			'onLoading': function () {
				jQuery('#' + tmp.me.searchDivId + ' #searchBtn').button('loading');
				//reset div
				if(tmp.reset === true) {
					tmp.resultDiv.update( new Element('tr').update( new Element('td').update( tmp.me.getLoadingImg() ) ) );
				}
				$(tmp.me.totalQtyId).update(0);
				$(tmp.me.totalValueId).update(tmp.me.getCurrency(0));
			}
			,'onSuccess': function(sender, param) {
				try{
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result)
						return;
					$(tmp.me.totalNoOfItemsId).update(tmp.result.pageStats.totalRows);
					$(tmp.me.totalQtyId).update(tmp.result.totalStockOnHand);
					$(tmp.me.totalValueId).update(tmp.me.getCurrency(tmp.result.totalOnHandValue));

					//reset div
					if(tmp.reset === true) {
						tmp.resultDiv.update(tmp.me._getResultRow(tmp.me._getTitleRowData(), true).wrap(new Element('thead')));
					}
					//remove next page button
					tmp.resultDiv.getElementsBySelector('.paginWrapper').each(function(item){
						item.remove();
					});

					//show all items
					tmp.tbody = $(tmp.resultDiv).down('tbody');
					if(!tmp.tbody)
						$(tmp.resultDiv).insert({'bottom': tmp.tbody = new Element('tbody') });
					tmp.result.items.each(function(item) {
						tmp.tbody.insert({'bottom': tmp.me._getResultRow(item).addClassName('item_row').writeAttribute('item_id', item.id) });
					});
					if(tmp.me._singleProduct !== true) {
						//show the next page button
						if(tmp.result.pageStats.pageNumber < tmp.result.pageStats.totalPages)
							tmp.resultDiv.insert({'bottom': tmp.me._getNextPageBtn().addClassName('paginWrapper') });
					} else if(tmp.result.items.size() > 0) {
						tmp.me._displaySelectedProduct(tmp.result.items[0]);
					}
					tmp.me._bindPriceInput();
				} catch (e) {
					tmp.resultDiv.insert({'bottom': tmp.me.getAlertBox('Error', e).addClassName('alert-danger') });
				}
			}
			,'onComplete': function() {
				jQuery('#' + tmp.me.searchDivId + ' #searchBtn').button('reset');
			}
		});
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
			tmp.selectedRow
			.popover({
				'title'    : '<div class="row"><div class="col-xs-10">Details for: ' + item.sku + '</div><div class="col-xs-2"><div class="btn-group pull-right"><a class="btn btn-primary btn-sm" href="/product/' + item.id + '.html" target="_BLANK"><span class="glyphicon glyphicon-pencil"></span></a><span class="btn btn-danger btn-sm" onclick="pageJs.deSelectProduct();"><span class="glyphicon glyphicon-remove"></span></span></div></div></div>',
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
	,toggleActive: function(active, product) {
		var tmp = {};
		tmp.me = this;
		tmp.me.postAjax(tmp.me.getCallbackId('toggleActive'), {'productId': product.id, 'active': active}, {
			'onSuccess': function(sender, param) {
				try{
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result || !tmp.result.item)
						return;
					if($$('.product_item[product_id=' + product.id +']').size() >0) {
						$$('.product_item[product_id=' + product.id +']').first().replace(tmp.me._getResultRow(tmp.result.item, false));
					}
					tmp.me._bindPriceInput();
				} catch (e) {
					tmp.me.showModalBox('ERROR', e, true);
				}
			}
		})
		return tmp.me;
	}
	,toggleIsKit: function(isKit, product) {
		var tmp = {};
		tmp.me = this;
		tmp.me.postAjax(tmp.me.getCallbackId('toggleIsKit'), {'productId': product.id, 'isKit': isKit}, {
			'onSuccess': function(sender, param) {
				tmp.newProduct = product;
				try{
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result || !tmp.result.item)
						return;
					tmp.newProduct = tmp.result.item;
				} catch (e) {
					tmp.me.showModalBox('ERROR', e, true);
				}
				if($$('.product_item[product_id=' + product.id +']').size() >0) {
					$$('.product_item[product_id=' + product.id +']').first().replace(tmp.me._getResultRow(tmp.newProduct, false));
				}
				tmp.me._bindPriceInput();
			}
		})
		return tmp.me;
	}
	,_updatePrice: function(productId, newPrice, originalPrice) {
		var tmp = {};
		tmp.me = this;
		tmp.me.postAjax(tmp.me.getCallbackId('updatePrice'), {'productId': productId, 'newPrice': tmp.me.getValueFromCurrency(newPrice)}, {
			'onLoading': function() {}
			,'onSuccess': function(sender, param) {
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result || !tmp.result.item || !tmp.result.item.id)
						return;
					jQuery('.price-input[product-id=' + tmp.result.item.id + ']').attr('original-price', tmp.me.getValueFromCurrency(newPrice));
				} catch (e) {
					tmp.me.showModalBox('<strong class="text-danger">Error When Update Price:</strong>', '<strong>' + e + '</strong>');
					jQuery('.price-input[product-id=' + productId + ']').val(tmp.me.getCurrency(originalPrice));
				}
			}
		})
		return tmp.me;
	}
	,_updateStockLevel: function(productId, newValue, originalValue, type) {
		var tmp = {};
		tmp.me = this;
		if(type !== 'stockMinLevel' && type !== 'stockReorderLevel')
			tmp.me.showModalBox('Error', 'Invalid type passin to tmp.me._updateStockLevel');
		tmp.me.postAjax(tmp.me.getCallbackId('updateStockLevel'), {'productId': productId, 'newValue': newValue, 'type': type}, {
			'onLoading': function() {}
		,'onSuccess': function(sender, param) {
			try {
				tmp.result = tmp.me.getResp(param, false, true);
				if(!tmp.result || !tmp.result.item || !tmp.result.item.id)
					return;
				jQuery('.' + type + '-input[product-id=' + tmp.result.item.id + ']').attr('original-' + type, newValue);
				tmp.row = $(tmp.me.resultDivId).down('.product_item[product_id=' + tmp.result.item.id + ']');
				if(tmp.row) {
					tmp.row.replace(tmp.me._getResultRow(tmp.result.item, false));
					tmp.me._bindPriceInput();
				}
			} catch (e) {
				tmp.me.showModalBox('<strong class="text-danger">Error When Update ' + type + ':</strong>', '<strong>' + e + '</strong>');
				jQuery('.' + type + '-input[product-id=' + productId + ']').val(originalValue);
			}
		}
		})
		return tmp.me;
	}
	/**
	 * binding the price input event
	 */
	,_bindPriceInput: function() {
		var tmp = {};
		tmp.me = this;
		jQuery('.price-input[product-id]:not(".price-input-binded")')
			.click(function (){
				jQuery(this)
					.attr('original-price', tmp.me.getValueFromCurrency(jQuery(this).val()))
					.select();
			})
			.keydown(function(event) {
				tmp.inputBox = jQuery(this);
				tmp.me.keydown(event, function(){
					tmp.inputBox.blur();
				});
			})
			.focusout(function(){
				tmp.value = tmp.me.getValueFromCurrency(jQuery(this).val());
				jQuery(this).val(tmp.me.getCurrency(tmp.value));
			})
			.change(function() {
				tmp.me._updatePrice(jQuery(this).attr('product-id'), jQuery(this).val(), tmp.me.getValueFromCurrency( jQuery(this).attr('original-price') ));
			})
			.addClass('price-input-binded');
		jQuery('.stockMinLevel-input[product-id]').not('.stockMinLevel-input-binded')
			.click(function (){
				jQuery(this)
				.attr('original-stockMinLevel', jQuery(this).val())
				.select();
			})
			.keydown(function(event) {
				tmp.inputBox = jQuery(this);
				tmp.me.keydown(event, function(){
					tmp.inputBox.blur();
				});
			})
			.focusout(function(){
				tmp.value = jQuery(this).val();
				jQuery(this).val(tmp.value);
			})
			.change(function() {
				tmp.me._updateStockLevel(jQuery(this).attr('product-id'), jQuery(this).val(), jQuery(this).attr('original-stockMinLevel'), 'stockMinLevel' );
			})
			.addClass('stockMinLevel-input-binded');
		jQuery('.stockReorderLevel-input[product-id]').not('.stockReorderLevel-input-binded')
			.click(function (){
				jQuery(this)
				.attr('original-stockReorderLevel', jQuery(this).val())
				.select();
			})
			.keydown(function(event) {
				tmp.inputBox = jQuery(this);
				tmp.me.keydown(event, function(){
					tmp.inputBox.blur();
				});
			})
			.focusout(function(){
				tmp.value = jQuery(this).val();
				jQuery(this).val(tmp.value);
			})
			.change(function() {
				tmp.me._updateStockLevel(jQuery(this).attr('product-id'), jQuery(this).val(), jQuery(this).attr('original-stockReorderLevel'), 'stockReorderLevel' );
			})
			.addClass('stockReorderLevel-input-binded');
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
		tmp.price = '';
		if(row.prices) {
			row.prices.each(function(price) {
				if(price.type && parseInt(price.type.id) === 1) {
					tmp.price = price.price;
				}
			});
		}
		tmp.row = new Element('tr', {'class': 'visible-xs visible-md visible-lg visible-sm ' + (tmp.isTitle === true ? '' : 'product_item ' + (row.stockOnHand <= row.stockMinLevel ? 'danger': (row.stockOnHand <= row.stockReorderLevel ? 'warning' : '' ))), 'product_id' : row.id})
			.store('data', row)
			.insert({'bottom': new Element(tmp.tag, {'class': 'sku', 'title': row.name}).addClassName('col-xs-1')
				.insert({'bottom': new Element('span').setStyle('margin: 0 5px 0 0')
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
				.insert({'bottom':tmp.isTitle === true ? row.sku : new Element('a', {'href': 'javascript: void(0);', 'class': 'sku-link truncate'})
					.observe('click', function(e){
						tmp.me._displaySelectedProduct(row);
					})
					.update(row.sku)
				})
			})
			.insert({'bottom': new Element(tmp.tag, {'class': 'product_name hidden-xs hide-when-info hidden-sm'})
				.addClassName('col-xs-3')
				.setStyle(tmp.me._showRightPanel ? 'display: none' : '')
				.update(tmp.isTitle === true ? new Element('div', {'class': 'row'})
						.insert({'bottom': new Element('div', {'class': 'col-sm-10'}).update('Product Name')})
						.insert({'bottom': new Element('div', {'class': 'col-sm-2'}).update('IsKit?')})
					:
					new Element('div', {'class': 'row'})
						.insert({'bottom': new Element('div', {'class': 'col-sm-10'}).update(row.name)})
						.insert({'bottom': new Element('div', {'class': 'col-sm-2'}).update(new Element('input', {'type': 'checkbox', 'checked': row.isKit})
							.observe('click', function(event) {
								tmp.btn = this;
								tmp.checked = $(tmp.btn).checked;
								if(confirm(tmp.checked === true ? 'You are about to set this product to a KIT, which you can NOT PICK or SHIP without providing a KIT barcode.\n Continue?' : 'You are about to set this product to NOT a KIT, which you can PICK or SHIP without providing a KIT barcode\n Continue?'))
									tmp.me.toggleIsKit(tmp.checked, row);
							})
						)})
				)
			})
			.insert({'bottom': new Element(tmp.tag, {'class': 'hidden-xs hide-when-info hidden-sm row'}).addClassName('col-xs-2').setStyle(tmp.me._showRightPanel ? 'display: none' : '')
				.insert({'bottom': new Element('div', {'class': 'col-sm-4'}).update(tmp.isTitle === true ? 'Price' : new Element('input', {'class': "click-to-edit price-input", 'value': tmp.me.getCurrency(tmp.price), 'product-id': row.id}).setStyle('width: 100%') ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-4'}).update(tmp.isTitle === true ? 'Min St' : new Element('input', {'class': "click-to-edit stockMinLevel-input", 'value': row.stockMinLevel, 'product-id': row.id}).setStyle('width: 100%') ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-4'}).update(tmp.isTitle === true ? 'Re St' : new Element('input', {'class': "click-to-edit stockReorderLevel-input", 'value': row.stockReorderLevel, 'product-id': row.id}).setStyle('width: 100%') ) })
			})
			.insert({'bottom': new Element(tmp.tag, {'class': 'locations hide-when-info hidden-sm'}).addClassName('col-xs-1').update(
					row.locations ? tmp.me._getLocations(row.locations, isTitle) : ''
			) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'inventeryCode hide-when-info'}).addClassName('col-xs-1').update(row.invenAccNo ? row.invenAccNo : '') })
			.insert({'bottom': new Element(tmp.tag, {'class': 'manufacturer hide-when-info'}).addClassName('col-xs-1').update(row.manufacturer ? row.manufacturer.name : '') })
			.insert({'bottom': new Element(tmp.tag, {'class': 'supplier hide-when-info hidden-sm'}).addClassName('col-xs-1').update(
					row.supplierCodes ? tmp.me._getSupplierCodes(row.supplierCodes, isTitle) : ''
			) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'qty hidden-sm'}).addClassName('col-xs-1').update(
					tmp.isTitle === true ?
							new Element('div', {'class': 'row'})
								.insert({'bottom': new Element('div', {'class': 'col-xs-4', 'title': 'Stock on Hand'}).update('SH') })
								.insert({'bottom': new Element('div', {'class': 'col-xs-4', 'title': 'Average Cost'}).update('Cost') })
								.insert({'bottom': new Element('div', {'class': 'col-xs-4 hide-when-info', 'title': 'Stock On PO'}).update('SP') })
							:
							new Element('div', {'class': 'row'})
								.update(new Element('a', {'href': '/productqtylog.html?productid=' + row.id, 'target': '_BLANK'})
									.insert({'bottom': new Element('div', {'class': 'col-xs-4', 'title': 'Stock on Hand'}).update(row.stockOnHand) })
									.insert({'bottom': new Element('div', {'class': 'col-xs-4', 'title': 'Average Cost'}).update((row.totalOnHandValue != 0 && row.stockOnHand != 0) ? tmp.me.getCurrency(row.totalOnHandValue/row.stockOnHand) : 'N/A') })
									.insert({'bottom': new Element('div', {'class': 'col-xs-4 hide-when-info', 'title': 'Stock On PO'}).update(row.stockOnPO) })
								)
					)
			})
			.insert({'bottom': new Element(tmp.tag, {'class': 'product_active hide-when-info hidden-sm'}).addClassName('col-xs-1')
				.insert({'bottom': (
					new Element('div', {'class': 'row'})
						.insert({'bottom': new Element('div', {'class': 'col-xs-4 text-right'})
							.insert({'bottom': tmp.isTitle === true ? 'Act?' : new Element('input', {'type': 'checkbox', 'checked': row.active})
								.observe('click', function(event) {
									tmp.btn = this;
									tmp.checked = $(tmp.btn).checked;
									if(confirm(tmp.checked === true ? 'You are about to ReACTIVATE this product.\n Continue?' : 'You are about to deactivate this product.\n Continue?'))
										tmp.me.toggleActive(tmp.checked, row);
								})
							})
						})
						.insert({'bottom': new Element('div', {'class': 'col-xs-4'})
							.setStyle('padding: 0px;')
							.insert({'bottom': tmp.isTitle === true ? '' : new Element('a', {'href': '/serialnumbers.html?productid=' + row.id, 'target': '_BLANK', 'title': 'Serial Numbers.'}).update('SN') })
						})
						.insert({'bottom': new Element('div', {'class': 'col-xs-4'})
							.setStyle('padding: 0px;')
							.insert({'bottom': tmp.isTitle === true ? '' : new Element('div', {'class': ''})
								.insert({'bottom': new Element('a', {'class': 'btn btn-primary btn-xs', 'href': '/product/' + row.id + '.html', 'target': '_BLANK'})
									.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-pencil'}) })
								})
							})
						})
				) })
			});
		return tmp.row;
	}
});