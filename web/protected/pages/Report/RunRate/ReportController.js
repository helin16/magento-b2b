/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new BPCPageJs(), {
	init: function() {
		var tmp = {};
		tmp.me = this;
		jQuery.each(jQuery('.select2'), function(index, element){
			jQuery(element).select2({
				multiple: true,
				ajax: { url: "/ajax/getAll",
					dataType: 'json',
					delay: 10,
					type: 'POST',
					data: function(params, page) {
						return {"searchTxt": 'name like ?', 'searchParams': ['%' + params + '%'], 'entityName': jQuery(element).attr('entityName'), 'pageNo': page};
					},
					results: function (data, page, query) {
						 tmp.result = [];
						 if(data.resultData && data.resultData.items) {
							 data.resultData.items.each(function(item){
								 tmp.result.push({'id': item.id, 'text': item.orderNo, 'data': item});
							 });
						 }
			    		 return { 'results' : tmp.result, 'more': (data.resultData && data.resultData.pagination && data.resultData.pagination.totalPages && page < data.resultData.pagination.totalPages) };
					},
					cache: true
				},
				formatResult : function(result) {
					if(!result)
						return '';
					if(jQuery(element).attr('entityName') === 'Product')
						return '<div class="row"><div class="col-xs-4">' + result.data.sku + '</div><div class="col-xs-8">' + result.data.name + '</div></div>';
					if(jQuery(element).attr('entityName') === 'ProductCategory')
						return '<div class="row"><div class="col-xs-12">' + result.data.namePath + '</div></div>';
					return '<div class="row"><div class="col-xs-12">' + result.data.name + '</div></div>';
				},
				formatSelection: function(result) {
					if(!result)
						 return '';
					tmp.text = result.data.name;
					if(jQuery(element).attr('entityName') === 'Product')
						tmp.text = result.data.sku;
					else if(jQuery(element).attr('entityName') === 'ProductCategory')
						tmp.text = result.data.namePath;
					tmp.newDiv = new Element('div').update(tmp.text);
					return tmp.newDiv;
				},
				escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
				minimumInputLength: 3
			});
		});
		return tmp.me;
	}
	,genReport: function(btn) {
		var tmp = {};
		tmp.me = this;
		tmp.data = {};
		$$('[search_field]').each(function(item){
			tmp.data[item.readAttribute('search_field')] = $F(item);
		});
		tmp.me.postAjax(tmp.me.getCallbackId('genReportmBtn'), tmp.data, {
			'onLoading': function() {
				jQuery(btn).button('loading');
			}
			,'onSuccess': function (sender, param) {
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result || !tmp.result.url)
						return;
					tmp.newWind = window.open(tmp.result.url);
					if(!tmp.newWind) {
						throw 'You browser is blocking the popup window, please click <a class="btn btn-xs btn-primary" href="' + tmp.result.url + '" target="__BLANK">here</a> to open it manually.';
					}
				} catch (e) {
					tmp.me.showModalBox('<b>Error:</b>', '<b class="text-danger">' + e + '</b>');
				}
			}
			,'onComplete': function() {
				jQuery(btn).button('reset');
			}
		})
		return tmp.me;
	}
});