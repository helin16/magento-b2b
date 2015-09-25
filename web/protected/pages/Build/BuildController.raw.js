/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new BPCPageJs(), {
	resultDivId: '' //the html id of the result div
	,init: function() {
		var tmp = {};
		tmp.me = this;
		
		tmp.me.initProductSelect2();
		tmp.me._bindDownloadBtn();
		return tmp.me;
	}
	,_bindDownloadBtn: function() {
		var tmp = {};
		tmp.me = this;
		jQuery('#'+tmp.me.downloadBtnId).click(function(){
			tmp.data = [];
			jQuery('#'+pageJs.resultDivId+' input.select2').select2('data').each(function(item){
				tmp.data.push({
					'sku': item.data.sku,
					'price': (item.data.prices.length > 0 ? item.data.prices[0].price : 'N/A')
					});
			});
			tmp.me._genTemplate(tmp.data);
		});
	}
	,_genTemplate: function(data) {
		var tmp = {};
		tmp.me = this;
		if(data.length > 0) {
			tmp.data = Papa.unparse({
					quotes: false,
					delimiter: ",",
					newline: "\r\n",
					data: data
			});
			tmp.now = new Date();
			tmp.fileName = 'system_build' + '_' + tmp.now.getFullYear() + '_' + tmp.now.getMonth() + '_' + tmp.now.getDate() + '_' + tmp.now.getHours() + '_' + tmp.now.getMinutes() + '_' + tmp.now.getSeconds() + '.csv';
			tmp.blob = new Blob([tmp.data], {type: "text/csv;charset=utf-8"});
			saveAs(tmp.blob, tmp.fileName);
		}
		return tmp.me;
	}
	,_updateSetting: function(data) {
		var tmp = {};
		tmp.me = this;
		jQuery('#'+pageJs.resultDivId+' input.select2').prop("disabled", true);
		
		tmp.me.postAjax(tmp.me.getCallbackId('updateSetting'), data, {
			'onSuccess': function (sender, param) {
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result)
						return;
					if(tmp.me.productIds) {
						tmp.data = [];
						tmp.result.each(function(item){
							tmp.data.push({"id": item.id, 'text': '[' + item.sku + '] ' + item.name, 'data': item});
						});
						jQuery('#'+pageJs.resultDivId+' input.select2').select2('data', tmp.data);
					}
					jQuery('#'+pageJs.resultDivId+' input.select2').prop("disabled", false);
				} catch (e) {
					$(resultDiv).update(tmp.me.getAlertBox('Error:', e).addClassName('alert-danger'));
				}
			}
		});
		return tmp.me;
	}
	,initProductSelect2: function() {
		var tmp = {};
		tmp.me = this;
		tmp.select2 = jQuery('#'+pageJs.resultDivId+' .select2');
		tmp.select2.select2({
			 placeholder: "Search a product",
			 multiple: true,
			 minimumInputLength: 3,
			 data: [],
			 ajax: {
				 url: "/ajax/getProducts",
				 dataType: 'json',
				 quietMillis: 250,
				 data: function (term, page) { // page is the one-based page number tracked by Select2
					 return {
						 'entityName': 'Product',
						 'searchTxt': term,
						 'isKit': tmp.select2.attr('isKit'),
						 'pageNo': page, // page number
						 'pageSize': tmp.pageSize
					 };
				 },
				 results: function (data, page) {
					tmp.result = [];
					data.resultData.items.each(function(item){
						tmp.result.push({"id": item.id, 'text': '[' + item.sku + '] ' + item.name, 'data': item});
					})
					return {
						results:  tmp.result,
						more: (page * tmp.pageSize) < data.resultData.pagination.totalRows
					};
				}
			 },
			 formatResult : function(result) {
				 if(!result)
					 return '';
				 return tmp.me._getProductDetailsDiv(result.data);
			 },
			 escapeMarkup: function (markup) { return markup; } // let our custom formatter work
		});
		tmp.select2.on("select2-selecting", function(event) {
			tmp.txtBox = $(event.target);
			tmp.onSelectFunc = tmp.txtBox.readAttribute('onSelectFunc');
			if(typeof(tmp.me[tmp.onSelectFunc]) === 'function')
				tmp.me[tmp.onSelectFunc](event.object.data);
		});
		tmp.select2.on('change', function(e) {
			tmp.me._updateSetting(e.val);
		});
		if(tmp.me.productIds) {
			tmp.data = [];
			tmp.me.productIds.each(function(item){
				tmp.data.push({"id": item.id, 'text': '[' + item.sku + '] ' + item.name, 'data': item});
			});
			tmp.select2.select2('data', tmp.data);
		}
		return tmp.me;
	}
	/**
	 * Getting the product searching row
	 */
	,_getProductDetailsDiv: function(product, noSOHInfo) {
		var tmp = {};
		tmp.me = this;
		tmp.SOHInfo = (noSOHInfo === true ? false : true);
		tmp.newDiv = new Element('div', {'class': 'row'})
		if(!product || !product.id)
			return tmp.newDiv;

		tmp.defaultImg = new Element('img', {'data-src': 'holder.js/100%x64', 'src': 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI2NCIgaGVpZ2h0PSI2NCI+PHJlY3Qgd2lkdGg9IjY0IiBoZWlnaHQ9IjY0IiBmaWxsPSIjZWVlIi8+PHRleHQgdGV4dC1hbmNob3I9Im1pZGRsZSIgeD0iMzIiIHk9IjMyIiBzdHlsZT0iZmlsbDojYWFhO2ZvbnQtd2VpZ2h0OmJvbGQ7Zm9udC1zaXplOjEycHg7Zm9udC1mYW1pbHk6QXJpYWwsSGVsdmV0aWNhLHNhbnMtc2VyaWY7ZG9taW5hbnQtYmFzZWxpbmU6Y2VudHJhbCI+NjR4NjQ8L3RleHQ+PC9zdmc+'});
		tmp.newDiv.store('data', product)
			.insert({'bottom': new Element('div', {'class': 'col-xs-4 col-sm-3 col-md-2 col-lg-1'}).update(tmp.defaultImg) })
			.insert({'bottom': new Element('div', {'class': 'col-xs-8 col-sm-9 col-md-10 col-lg-11'})
				.insert({'bottom': new Element('div')
					.insert({'bottom': new Element('div', {'class': 'col-md-3 truncate'})
						.setStyle('max-width:none;')
						.insert({'bottom': new Element('span', {'class': 'btn btn-warning btn-xs', 'title': 'SKU: ' + product.sku}).update(product.sku)
							.observe('click', function(){
								tmp.me._openURL('/product/' + product.id + '.html?blanklayout=1');
							})
						})
					})
					.insert({'bottom': new Element('div', {'class': 'col-md-3'})
						.insert({'bottom': new Element('div', {'class': 'col-xs-4 text-right'}).update('<strong>Brand</strong>:')})
						.insert({'bottom': new Element('div', {'class': 'col-xs-8 truncate'}).update(product.manufacturer ? product.manufacturer.name : '')})
					})
					.insert({'bottom': new Element('div', {'class': 'col-md-6 truncate', 'title': product.name}).setStyle('max-width:none;').update(new Element('small').update(product.name)) })
					.insert({'bottom': new Element('small', {'class': 'col-md-12'}).update('<em>' + product.shortDescription + '</em>')			})
				})
				.insert({'bottom': tmp.SOHInfo !== true ? '' : new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('div', {'class': 'col-sm-3 col-md-2'})
						.insert({'bottom': new Element('div', {'class': 'input-group input-group-sm'})
							.insert({'bottom': new Element('div', {'class': 'input-group-addon'}).update('SOH:') })
							.insert({'bottom': new Element('div', {'class': 'form-control'}).update(product.stockOnHand) })
						})
					})
					.insert({'bottom': new Element('div', {'class': 'col-sm-4 col-md-3'})
						.insert({'bottom': new Element('div', {'class': 'input-group input-group-sm'})
							.insert({'bottom': new Element('div', {'class': 'input-group-addon'}).update('Unit Price (inc GST):') })
							.insert({'bottom': new Element('div', {'class': 'form-control'}).update(tmp.me.getCurrency(tmp.me._getUnitPrice(product))) })
						})
					})
				})
			});
		return tmp.newDiv;
	}
	,_getUnitPrice: function(product) {
		var tmp = {};
		tmp.me = this;
		tmp.unitPrice = 0;
		if(product && product.prices && product.prices.size() > 0) {
			product.prices.each(function(price){
				if(price.type && parseInt(price.type.id) === 1) {
					tmp.unitPrice = price.price;
				}
			})
		}
		return tmp.unitPrice;
	}
});