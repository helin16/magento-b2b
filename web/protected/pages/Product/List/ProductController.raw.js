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
	,_autoLoading: false
	,_postIndex: null // for new rule post, start from 0
	,_selected: null // for new rule post, selected products
	,_priceMatchRule: null // for new rule post, the rule itself
	,newRuleResultContainerId: 'new_rule_result_container' // the element id for new rule post result container
	
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
		jQuery(".chosen").select2({
		    minimumResultsForSearch: Infinity
		});
		return this;
	}
	/**
	 * get selected
	 */
	,_getSelection: function() {
		var tmp = {}
		tmp.me = this;
		tmp.products = [];
		
		tmp.itemList = $('item-list');
		tmp.itemList.getElementsBySelector('.product_item.item_row').each(function(row){
			tmp.checked = row.down('input.product-selected[type="checkbox"]').checked;
			tmp.productId = row.readAttribute('product_id');
			if(tmp.checked === true && jQuery.isNumeric(tmp.productId) === true)
				tmp.products.push(row.retrieve('data'));
		});
		
		$('total-selected-count').update(tmp.products.length);
		
		return tmp.products;
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
	,postNewRule: function(btn, del) {
		var tmp = {};
		tmp.me = this;
		tmp.btn = (btn || null);
		tmp.del = (del || false);
		if(tmp.btn !== null)
			tmp.me._signRandID(tmp.btn);
		
		tmp.me._priceMatchRule.price_from = tmp.me.getValueFromCurrency(tmp.me._priceMatchRule.price_from);
		tmp.me._priceMatchRule.price_to = tmp.me.getValueFromCurrency(tmp.me._priceMatchRule.price_to);
		tmp.me._priceMatchRule.offset = tmp.me.getValueFromCurrency(tmp.me._priceMatchRule.offset);
		tmp.me._priceMatchRule.active = (tmp.del === true ? false : true);
		
		if(tmp.me._selected[tmp.me._postIndex]) {
			window.onbeforeunload = function(){
			   return "Processing... Please Do not close";
			};
			tmp.ModalBox = $(tmp.me.modalId);
			if(tmp.ModalBox)
				tmp.ModalBox.down('.modal-header').update('<h4 style="color:red;">Processing... Please Do NOT close</h4>');
			tmp.me.postAjax(tmp.me.getCallbackId('newRule'), {'productId': tmp.me._selected[tmp.me._postIndex]['id'], 'rule': tmp.me._priceMatchRule}, {
				'onLoading': function () {
					if(tmp.btn !== null)
						jQuery('.right-panel.btn').button('loading');
				}
				,'onSuccess': function(sender, param) {
					try{
						tmp.result = tmp.me.getResp(param, false, true);
						if(!tmp.result)
							return;
						$(tmp.me.newRuleResultContainerId)
							.insert({'bottom': new Element('d', {'class': 'col-xs-9'}).update(tmp.me._selected[tmp.me._postIndex].sku) })
							.insert({'bottom': new Element('div', {'class': 'col-xs-3'}).update('done') });
					} catch (e) {
						$(tmp.me.newRuleResultContainerId)
							.insert({'top': tmp.me.getAlertBox('', e).addClassName('alert-danger col-xs-12') 
								.insert({'top': new Element('b', {'class': 'col-xs-12'}).update('SKU: ' + tmp.me._selected[tmp.me._postIndex].sku) })
							});
					}
				}
				,'onComplete': function() {
					window.onbeforeunload = null;
					if(tmp.btn !== null)
						jQuery('.right-panel.btn').button('reset');
					tmp.me._postIndex = tmp.me._postIndex + 1;
					tmp.me.postNewRule(tmp.btn, tmp.del);
				}
			});
		}
		else {
			$(tmp.me.newRuleResultContainerId).insert({'top': new Element('div', {'class': 'col-xs-12'}).update('All Done!') });
			tmp.me.hideModalBox();
			jQuery('#' + tmp.me.modalId).remove();
			$('searchBtn').click();
		}
	}
	,_bindNewRuleBtn: function(btn,product) {
		var tmp = {};
		tmp.me = this;
		tmp.product = (product || null);
		tmp.btn = (btn || $('newPriceMatchRuleBtn'));
		
		tmp.me.observeClickNDbClick(
				tmp.btn
				,function(){
					tmp.selected = tmp.me._getSelection();
					tmp.totalQty = $('total-found-count').innerHTML;
					
					if(tmp.product === null && tmp.selected !== null && tmp.selected.length > 0) {
						tmp.warningMsg = new Element('div')
							.insert({'bottom': new Element('h3', {'class': 'col-lg-12'}).update('only <b>' + tmp.selected.length + '</b> out of <b>' + tmp.totalQty + '</b> is selected, Contrinue?') })
							.insert({'bottom': new Element('i', {'class': 'btn btn-danger btn-lg'}).update('No')
								.observe('click', function(){tmp.me.hideModalBox();})
							})
							.insert({'bottom': new Element('i', {'class': 'btn btn-success btn-lg pull-right'}).update('Yes').setStyle(tmp.selected.length === 0 ? 'display: none;' : '') 
								.observe('click', function(){
									jQuery("#select2-drop-mask").select2("close"); // close all select2
									$(this).up('.modal-body').update('')
										.insert({'bottom': tmp.ruleContainer = tmp.me._getPriceMatchRuleEl(null, tmp.selected) })
										.insert({'bottom': new Element('div', {'class': 'row', 'id': tmp.me.newRuleResultContainerId}) });
									tmp.me._getPriceMatchCompanySelect2(jQuery('[match_rule="company_id"]'),true);
								})
							});
						tmp.me.showModalBox('Warning', tmp.warningMsg, false, null, null, true);
					}
					else if(tmp.product && jQuery.isNumeric(tmp.product.id)) {
						
					}
				}
				,null
				);
		return tmp.me;
	}
	,_getPriceMatchCompanySelect2: function(el, product) {
		var tmp = {};
		tmp.me = this;
		tmp.product = (product || null);
		
		tmp.selectBox = jQuery(el).select2({
			ajax: {
				delay: 250
				,url: '/ajax/getAll'
				,type: 'POST'
				,data: function (params) {
					return {"searchTxt": 'companyName like ?', 'searchParams': ['%' + params + '%'], 'entityName': 'PriceMatchCompany'};
				}
				,results: function(data, page, query) {
					tmp.result = [];
					if(data.resultData && data.resultData.items) {
						data.resultData.items.each(function(item){
							if(tmp.me._checkUniquePriceMatchCompanies(tmp.result, item) === false)
								tmp.result.push({'id': item.id, 'text': item.companyName, 'data': item});
						});
					}
					return { 'results' : tmp.result };
				}
			}
			,cache: true
			,escapeMarkup: function (markup) { return markup; } // let our custom formatter work
		});
		if(tmp.product !== null && tmp.product.priceMatchRule && tmp.product.priceMatchRule.id && tmp.product.priceMatchRule.priceMatchCompany && tmp.product.priceMatchRule.priceMatchCompany.id) {
			tmp.selectBox.select2('data', {'id': tmp.product.priceMatchRule.priceMatchCompany.id, 'text': tmp.product.priceMatchRule.priceMatchCompany.companyName, 'data': tmp.product.priceMatchRule.priceMatchCompany});
		}
		return tmp.selectBox;
	}
	,_checkUniquePriceMatchCompanies: function(existItems, newItem) {
		var tmp = {};
		tmp.me = this;
		
		tmp.found = false;
		existItems.each(function(item){
			if(tmp.found === false && item.text === newItem.companyName) {
				tmp.found = true;
			}
		});
		return tmp.found;
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
	,_displayPriceMatchResult: function(prices, product) {
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
				.insert({'bottom': new Element('td', {'colspan': 3}).update(price.key).addClassName((product.priceMatchRule && price.key===product.priceMatchRule.priceMatchCompany.companyName) ? 'success' : '') })
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
					.insert({'bottom': new Element('td').update(tmp.priceInput = new Element('input', {'class': "click-to-edit price-input", 'value': tmp.me.getCurrency(prices.myPrice), 'product-id': product.id}) ) })
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
		tmp.newDiv = new Element('div', {'id': 'info_panel_' + product.id})
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
					.insert({'bottom': new Element('div', {'class': 'panel-heading'}).update('<strong>Price Match Rule</strong>')})
					.insert({'bottom': new Element('div', {'class': 'panel-body'}).update(tmp.ProductRuleEl = tmp.me._getPriceMatchRuleEl(product))})
				})
			})
			;
		return tmp.newDiv;
	}
	,_getPriceMatchRuleEl: function(product, selected) {
		var tmp = {};
		tmp.me = this;
		tmp.product = (product || null);
		tmp.selected = (selected || null);
		
		tmp.newDiv = new Element('div', {'class': ''})
			.insert({'bottom': new Element('div', {'class': 'col-xs-12'})
				.insert({'bottom': new Element('div', {'class': 'form-group form-group-sm input-group'})
					.insert({'bottom': new Element('label', {'class': 'contorl-label input-group-addon'}).update('Target Competitor') })
					.insert({'bottom': new Element('input', {'type': 'text', 'class': 'form-control input-sm rightPanel', 'match_rule': 'company_id'}) })
				})
			})
			.insert({'bottom': new Element('div', {'class': 'col-xs-6'})
				.insert({'bottom': new Element('div', {'class': 'form-group form-group-sm input-group'})
					.insert({'bottom': new Element('label', {'class': 'contorl-label input-group-addon'}).update('Lower Safty Boundary') })
					.insert({'bottom': new Element('input', {'type': 'text', 'class': 'form-control input-sm', 'match_rule': 'price_from', 'value': (tmp.product && tmp.product.priceMatchRule) ? tmp.product.priceMatchRule.price_from : ''})
						.observe('keyup', function(e){
							$(this).up('.modal-body').down('[match_rule="price_to"]').value = $F($(this));
						})
						.observe('keydown', function(event){
							tmp.txtBox = this;
							tmp.me.keydown(event, function() {
								Event.stop(event);
								$(tmp.txtBox).up('.modal-body').down('[match_rule="offset"]').focus();
								$(tmp.txtBox).up('.modal-body').down('[match_rule="offset"]').select();
							}, function(){}, Event.KEY_TAB);
						})
					})
				})
			})
			.insert({'bottom': new Element('div', {'class': 'col-xs-6'})
				.insert({'bottom': new Element('div', {'class': 'form-group form-group-sm input-group'})
					.insert({'bottom': new Element('label', {'class': 'contorl-label input-group-addon'}).update('Upper Safty Boundary') })
					.insert({'bottom': new Element('input', {'type': 'text', 'class': 'form-control input-sm', 'match_rule': 'price_to', 'value': (tmp.product && tmp.product.priceMatchRule) ? tmp.product.priceMatchRule.price_to : ''})
						.observe('keyup', function(e){
							$(this).up('.modal-body').down('[match_rule="price_from"]').value = $F($(this));
						})
					})
				})
			})
			.insert({'bottom': new Element('div', {'class': 'col-xs-9'})
				.insert({'bottom': new Element('div', {'class': 'form-group form-group-sm input-group'})
					.insert({'bottom': new Element('label', {'class': 'contorl-label input-group-addon'}).update('Extra Margin After Price Match') })
					.insert({'bottom': new Element('input', {'type': 'text', 'class': 'form-control input-sm', 'match_rule': 'offset', 'value': (tmp.product && tmp.product.priceMatchRule) ? tmp.product.priceMatchRule.offset : ''}) })
				})
			})
			.insert({'bottom': new Element('div', {'class': 'col-xs-3 text-right'})
				.insert({'bottom': new Element('i', {'class': 'btn btn-sm btn-success btn-new-rule right-panel'}).update('Confirm') 
					.observe('click', function(e){
						tmp.me._priceMatchRule = tmp.me._collectFormData($(this).up('.modal-body'), 'match_rule');
						tmp.me._selected = tmp.product === null ? tmp.selected : tmp.product;
						tmp.me._postIndex = 0;
						tmp.me.postNewRule($(this));
					})
				})
				.insert({'bottom': new Element('i', {'class': 'btn btn-sm btn-danger btn-del-rule right-panel'}).update('<i class="glyphicon glyphicon-trash"></i>') 
					.observe('click', function(e){
						tmp.me._priceMatchRule = tmp.me._collectFormData($(this).up('.modal-body'), 'match_rule');
						tmp.me._selected = tmp.product === null ? tmp.selected : tmp.product;
						tmp.me._postIndex = 0;
						tmp.me.postNewRule($(this), true);
					})
				})
			});
		return tmp.newDiv;
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
						$('info_panel_' + product.id).down('.price-match-div .price-match-listing').replace(tmp.me._displayPriceMatchResult(tmp.result, product));
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
	,getResults: function(reset, pageSize, auto, tickNew) {
		var tmp = {};
		tmp.me = this;
		
		tmp.reset = (reset || false);
		tmp.auto = (auto || false);
		tmp.tickNew = (tickNew || false);
		tmp.resultDiv = $(tmp.me.resultDivId);

		if(tmp.reset === true)
			tmp.me._pagination.pageNo = 1;
		
		// auto load next page
		if(tmp.auto === true && $$('.btn-show-more').length > 0) {
			tmp.me._autoLoading = true;
			tmp.me._pagination.pageNo = tmp.me._pagination.pageNo*1 + 1;
		}
		
		tmp.me._pagination.pageSize = (pageSize || tmp.me._pagination.pageSize);
		tmp.me.postAjax(tmp.me.getCallbackId('getItems'), {'pagination': tmp.me._pagination, 'searchCriteria': tmp.me._searchCriteria}, {
			'onLoading': function () {
				jQuery('#' + tmp.me.searchDivId + ' .btn').button('loading');
				jQuery('#' + tmp.me.searchDivId + ' input').prop('disabled', true);
				jQuery('#' + tmp.me.searchDivId + ' select').prop('disabled', true);
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
						tmp.tbody.insert({'bottom': tmp.newRow = tmp.me._getResultRow(item).addClassName('item_row').writeAttribute('item_id', item.id) });
						if(tmp.auto === true || tmp.tickNew === true)
							tmp.newRow.down('.product-selected').click();
					});
					if(tmp.me._singleProduct !== true) {
						//show the next page button
						if(tmp.result.pageStats.pageNumber < tmp.result.pageStats.totalPages)
							tmp.resultDiv.insert({'bottom': tmp.me._getNextPageBtn().addClassName('paginWrapper') });
					} else if(tmp.result.items.size() > 0) {
						tmp.me._displaySelectedProduct(tmp.result.items[0]);
					}
					tmp.me._bindPriceInput();
					// auto load next page
					if(tmp.auto === true && $$('.btn-show-more').length > 0) {
						tmp.me.getResults(false, tmp.me._pagination.pageSize, true);
					}
					else { // finished auto loading
						tmp.me._autoLoading = false;
						tmp.me.hideModalBox();
						jQuery('#' + tmp.me.searchDivId + ' .btn').button('reset');
						jQuery('#' + tmp.me.searchDivId + ' input').prop('disabled', false);
						jQuery('#' + tmp.me.searchDivId + ' select').prop('disabled', false);
					}
					
					tmp.me._getSelection();
				} catch (e) {
					tmp.resultDiv.insert({'bottom': tmp.me.getAlertBox('Error', e).addClassName('alert-danger') });
				}
			}
			,'onComplete': function() {
				if(tmp.auto !== true) {
					jQuery('#' + tmp.me.searchDivId + ' .btn').button('reset');
					jQuery('#' + tmp.me.searchDivId + ' input').prop('disabled', false);
					jQuery('#' + tmp.me.searchDivId + ' select').prop('disabled', false);
				}
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
			.on('shown.bs.popover', function (e) {
				tmp.me._getPriceMatchCompanySelect2(jQuery('.rightPanel[match_rule="company_id"]'), null, item);
				// somehow the key binding is lost by popover, redo the bindings
				tmp.container = $$('.btn-new-rule.right-panel').first().up('.panel-body');
				$$('.btn-new-rule.right-panel').first().observe('click', function(e){
					tmp.me._priceMatchRule = tmp.me._collectFormData($(this).up('.panel-body'), 'match_rule');
					tmp.me._selected = [item];
					tmp.me._postIndex = 0;
					tmp.me.postNewRule($(this));
				});
				$$('.btn-del-rule.right-panel').first().observe('click', function(e){
					tmp.me._priceMatchRule = tmp.me._collectFormData($(this).up('.panel-body'), 'match_rule');
					tmp.me._selected = [item];
					tmp.me._postIndex = 0;
					tmp.me.postNewRule($(this),true);
				});
				if(!(item.priceMatchRule && item.priceMatchRule.id && jQuery.isNumeric(item.priceMatchRule.id)))
					$$('.btn-del-rule.right-panel').first().hide();
				tmp.container.down('[match_rule="price_from"]')
					.observe('keyup', function(e){
								$(this).up('.panel-body').down('[match_rule="price_to"]').value = $F($(this));
							})
					.observe('keydown', function(event){
						tmp.txtBox = this;
						tmp.me.keydown(event, function() {
							Event.stop(event);
							$(tmp.txtBox).up('.panel-body').down('[match_rule="offset"]').focus();
							$(tmp.txtBox).up('.panel-body').down('[match_rule="offset"]').select();
						}, function(){}, Event.KEY_TAB);
					});
			})
			.popover({
				'title'    : '<div class="row"><div class="col-xs-10">Details for: ' + item.sku + '</div><div class="col-xs-2"><div class="btn-group pull-right"><a class="btn btn-primary btn-sm" href="/product/' + item.id + '.html" target="_BLANK"><span class="glyphicon glyphicon-pencil"></span></a><span class="btn btn-danger btn-sm" onclick="pageJs.deSelectProduct();"><span class="glyphicon glyphicon-remove"></span></span></div></div></div>',
				'html'     : true,
				'placement': 'right',
				'container': 'body',
				'trigger'  : 'manual',
				'viewport' : {"selector": ".list-panel", "padding": 0 },
				'content'  : function() { return tmp.rightPanel = tmp.me._showProductInfoOnRightPanel(item).wrap(new Element('div')).innerHTML; },
				'template' : '<div class="popover" role="tooltip" style="max-width: none; z-index: 0;"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>'
			})
			.addClass('popover-loaded');
		}
		tmp.selectedRow.popover('show');
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
	,_getNextPageBtn: function() {
		var tmp = {};
		tmp.me = this;
		tmp.totalQty = $('total-found-count').innerHTML;
		return new Element('tfoot')
			.insert({'bottom': new Element('tr')
				.insert({'bottom': new Element('td', {'colspan': tmp.me._nextPageColSpan, 'class': 'text-center'})
					.insert({'bottom': new Element('span', {'class': 'btn btn-primary btn-show-more', 'data-loading-text':"Fetching more results ..."}).update('Next Page')
						.observe('click', function() {
							tmp.me._pagination.pageNo = tmp.me._pagination.pageNo*1 + 1;
							jQuery(this).button('loading');
							tmp.me.getResults(false, tmp.me._pagination.pageSize, false, true);
						})
					})
					.insert({'bottom': new Element('span', {'class': 'btn btn-warning btn-show-more', 'data-loading-text':"Fetching more results ..."}).update('<b>Show ALL Pages</b>').setStyle('margin-left: 10px; color: black;')
						.observe('click', function() {
							if(tmp.totalQty > 1000)
								tmp.me.showModalBox('Warning', '<h3>There are ' + tmp.totalQty + ' products for current search conditions. <br/>Please narrow down the search');
							else
								tmp.me.getResults(false, tmp.me._pagination.pageSize, true);
						})
					})
				})
			});
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
				.observe('click', function(e){
					tmp.me._signRandID($(this));
					if (e.target.nodeName != "INPUT") {
				        jQuery('#'+$(this).id).find(":checkbox").prop("checked", !jQuery('#'+$(this).id).find(":checkbox").prop("checked"));
				        if(tmp.isTitle === true) {
				        	tmp.checked = jQuery('#'+$(this).id).find(":checkbox").prop("checked");
							$(tmp.me.resultDivId).getElementsBySelector('.product_item .product-selected').each(function(el){
								el.checked = tmp.checked;
							});
						}
				    }
					tmp.me._getSelection();
				})
				.insert({'bottom': new Element('span').setStyle('margin: 0 5px 0 0')
					.insert({'bottom': new Element('input', {'type': 'checkbox', 'class': 'product-selected'})
						.observe('click', function(e){
							tmp.checked = this.checked;
							if(tmp.isTitle === true) {
								$(tmp.me.resultDivId).getElementsBySelector('.product_item .product-selected').each(function(el){
									el.checked = tmp.checked;
								});
							}
							tmp.me._getSelection();
						})
					})
				})
				.insert({'bottom':tmp.isTitle === true ? row.sku : new Element('a', {'href': 'javascript: void(0);', 'class': 'sku-link truncate'})
					.observe('click', function(e){
						Event.stop(e);
						tmp.me._displaySelectedProduct(row);
					})
					.update(row.sku)
				})
			})
			.insert({'bottom': new Element(tmp.tag, {'class': 'product_name hidden-xs hide-when-info hidden-sm'})
				.addClassName('col-xs-2')
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
			.insert({'bottom': new Element(tmp.tag, {'class': 'hidden-xs hide-when-info hidden-sm row'}).addClassName('col-xs-3').setStyle(tmp.me._showRightPanel ? 'display: none' : '')
				.insert({'bottom': new Element('div', {'class': 'col-sm-3'}).update(tmp.isTitle === true ? 'Price' : new Element('input', {'class': "click-to-edit price-input", 'value': tmp.me.getCurrency(tmp.price), 'product-id': row.id}).setStyle('width: 100%') ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-3'}).update(tmp.isTitle === true ? 'Match' : new Element('span').update((row.priceMatchRule && row.priceMatchRule.priceMatchCompany) ? row.priceMatchRule.priceMatchCompany.companyName : '').setStyle('width: 100%') ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-3'}).update(tmp.isTitle === true ? 'Min St' : new Element('input', {'class': "click-to-edit stockMinLevel-input", 'value': row.stockMinLevel, 'product-id': row.id}).setStyle('width: 100%') ) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-3'}).update(tmp.isTitle === true ? 'Re St' : new Element('input', {'class': "click-to-edit stockReorderLevel-input", 'value': row.stockReorderLevel, 'product-id': row.id}).setStyle('width: 100%') ) })
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