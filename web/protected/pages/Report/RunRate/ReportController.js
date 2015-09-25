/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new BPCPageJs(), {
	select2_formatResult_category: function(result) {
		if(!result)
			return '';
		return '<div class="row"><div class="col-xs-3">' + result.data.sku + '</div><div class="col-xs-9">' + result.data.name + '</div></div>';
	}
	,init: function() {
		var tmp = {};
		tmp.me = this;
		jQuery.each(jQuery('.select2'), function(index, element){
			jQuery(element).select2({
				allowClear: true,
				hidden: true,
				multiple: false,
				ajax: { url: "/ajax/getAll",
					dataType: 'json',
					delay: 10,
					type: 'POST',
					data: function(params, sender, test) {
						return {"searchTxt": 'name like ?', 'searchParams': ['%' + params + '%'], 'entityName': jQuery(element).attr('entityName'), 'pageNo': 1};
					},
					results: function (data, page, query) {
						tmp.result = [];
						 if(data.resultData && data.resultData.items) {
							 data.resultData.items.each(function(item){
								 tmp.result.push({'id': item.id, 'text': item.orderNo, 'data': item});
							 });
						 }
			    		 return { 'results' : tmp.result };
					},
					cache: true
				},
				formatResult : function(result) {
					if(!result)
						return '';
					return '<div class="row"><div class="col-xs-12">' + result.data.name + '</div>';
				},
				formatSelection: function(result) {
					if(!result)
						 return '';
					 tmp.newDiv = new Element('div', {'class': 'row'})
					 	.insert({'bottom': new Element('div', {'class': 'col-xs-5 truncate'}).setStyle('max-width: none;').update(result.data.name)})
					 return tmp.newDiv;
				},
				escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
				minimumInputLength: 3
			});
		});
		return tmp.me;
	}
});