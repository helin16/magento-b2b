/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new DetailsPageJs(), {
	_htmlIds: {}
	,_getItemDiv: function() {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = new Element('div', {'class': 'save-panel'})
			.insert({'bottom': new Element('h3', {'class': 'text-center'}).update(tmp.me._item.id ? 'Editing KIT: ' + tmp.me._item.barcode : 'Building New Kit') })
			.insert({'bottom': new Element('div', {'class': 'form-horizontal'})
				.insert({'bottom':  tmp.me.getFormGroup(new Element('label').update('You are trying to build a kit: ').addClassName('col-sm-2'),
					new Element('div', {'class': 'col-xs-10 rm-form-control'}).update(
						new Element('input', {'class': 'form-control select2 input-sm product-search', 'save-panel': 'kit-product-id'})
					)
				) })
			});
		tmp.newDiv.getElementsBySelector('.rm-form-control').each(function(item) {
			item.removeClassName('form-control').removeClassName('rm-form-control');
		});
		return tmp.newDiv;
	}
	,selectKitProduct: function(product) {
		console.debug(product);
	}
	,_getProductSearchRow: function(product) {
		var tmp = {};
		tmp.me = this;
		tmp.unitPrice = 0;
		if(product.prices && product.prices.size() > 0) {
			product.prices.each(function(price){
				if(price.type & price.type.id === 1) {
					tmp.unitPrice = price.price;
				}
			})
		}
		tmp.defaultImg = new Element('img', {'data-src': 'holder.js/100%x64', 'src': 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI2NCIgaGVpZ2h0PSI2NCI+PHJlY3Qgd2lkdGg9IjY0IiBoZWlnaHQ9IjY0IiBmaWxsPSIjZWVlIi8+PHRleHQgdGV4dC1hbmNob3I9Im1pZGRsZSIgeD0iMzIiIHk9IjMyIiBzdHlsZT0iZmlsbDojYWFhO2ZvbnQtd2VpZ2h0OmJvbGQ7Zm9udC1zaXplOjEycHg7Zm9udC1mYW1pbHk6QXJpYWwsSGVsdmV0aWNhLHNhbnMtc2VyaWY7ZG9taW5hbnQtYmFzZWxpbmU6Y2VudHJhbCI+NjR4NjQ8L3RleHQ+PC9zdmc+'});
		tmp.newDiv = new Element('div', {'class': 'row', 'onclick': 'pageJs.selectKitProduct(this)'})
			.store('data', product)
			.insert({'bottom': new Element('div', {'class': 'col-xs-1'}).update(tmp.defaultImg) })
			.insert({'bottom': new Element('div', {'class': 'col-xs-11'})
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('div', {'class': 'col-xs-8'}).update(product.name)			})
					.insert({'bottom': new Element('div', {'class': 'col-xs-2'})
						.insert({'bottom': new Element('div', {'class': 'col-xs-4 text-right'}).update('<strong>Brand:</strong>:')})
						.insert({'bottom': new Element('div', {'class': 'col-xs-8 '}).update(product.manufacturer ? product.manufacturer.name : '')})
					})
					.insert({'bottom': new Element('div', {'class': 'col-xs-2'})
						.insert({'bottom': new Element('div', {'class': 'col-xs-4 text-right'}).update('<strong>sku</strong>:')})
						.insert({'bottom': new Element('div', {'class': 'col-xs-8 '}).update(product.sku)})
					})
				})
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('small', {'class': 'col-xs-12'}).update('<em>' + product.shortDescription + '</em>')			})
				})
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('div', {'class': 'col-xs-2'})
						.insert({'bottom': new Element('div', {'class': 'input-group input-group-sm'})
							.insert({'bottom': new Element('div', {'class': 'input-group-addon'}).update('SOH:') })
							.insert({'bottom': new Element('div', {'class': 'form-control'}).update(product.stockOnHand) })
						})
					})
					.insert({'bottom': new Element('div', {'class': 'col-xs-3'})
						.insert({'bottom': new Element('div', {'class': 'input-group input-group-sm'})
							.insert({'bottom': new Element('div', {'class': 'input-group-addon'}).update('Unit Price (inc GST):') })
							.insert({'bottom': new Element('div', {'class': 'form-control'}).update(tmp.me.getCurrency(tmp.unitPrice)) })
						})
					})
					.insert({'bottom': new Element('div', {'class': 'col-xs-3'})
						.insert({'bottom': new Element('div', {'class': 'input-group input-group-sm'})
							.insert({'bottom': new Element('div', {'class': 'input-group-addon'}).update('Unit Cost (ex GST):') })
							.insert({'bottom': new Element('div', {'class': 'form-control'}).update(tmp.me.getCurrency(tmp.unitCost)) })
						})
					})
				})
			})
		return tmp.newDiv;
	}
	,_initProductSearch: function() {
		var tmp = {};
		tmp.me = this;
		tmp.pageSize = 30;
		tmp.select2 = jQuery('.select2.product-search').select2({
			 placeholder: "Search a product",
			 minimumInputLength: 3,
			 ajax: {
				 url: "/ajax/getProducts",
				 dataType: 'json',
				 quietMillis: 250,
				 data: function (term, page) { // page is the one-based page number tracked by Select2
					 return {
						 searchTxt: term, //search term
						 pageNo: page, // page number
						 pageSize: tmp.pageSize
					 };
				 },
				 results: function (data, page) {
					tmp.result = [];
					data.resultData.items.each(function(item){
						tmp.result.push({"id": item.id, 'text': '[' + item.sku + '] ' + item.name, 'data': item});
					})
					return {
						results:  tmp.result,
						more: (page * tmp.pageSize) < data.resultData.pageStats.totalRows
					};
				}
			 },
			 formatResult : function(result) {
				 if(!result)
					 return '';
				 return tmp.me._getProductSearchRow(result.data);
			 },
//			 formatSelection: repoFormatSelection, // omitted for brevity, see the source of this page
			 escapeMarkup: function (markup) { return markup; } // let our custom formatter work
		}).data('select2');
		tmp.select2.onSelect = (function(fn) {
			return function(selectedData, options) {
				fn.apply(this, arguments);
				tmp.me.selectKitProduct(selectedData.data);
			}
		})(tmp.select2.onSelect);
		return tmp.me;
	}
	,_init: function(){
		var tmp = {};
		tmp.me = this;
		return tmp.me;
	}
	,load: function () {
		var tmp = {};
		tmp.me = this;
		tmp.me._init();
		$(tmp.me._htmlIds.itemDiv).update(tmp.div = tmp.me._getItemDiv());
		tmp.me._initProductSearch();
		return tmp.me;
	}
});