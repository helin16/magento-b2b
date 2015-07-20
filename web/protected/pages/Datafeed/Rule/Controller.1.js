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
		
		return tmp.me._loadSelect2();
	}
	,_getConifgDiv: function() {
		var tmp = {}
		tmp.me = this;
		
		//supplier selector
		tmp.supplierSelector = tmp.me.getFormGroup(new Element('label').update('Supplier: '),
				new Element('input', {'class': 'form-control select2', 'config': 'supplier', 'name': 'supplier', 'placeholder': 'Name of Supplier ... '}) );
		//Manufacturers (brands) selector
		tmp.manufacturersSelectorTitle = new Element('div')
			.insert({'bottom': new Element('label').update('Manufacturers / Brands : ')})
			.insert({'bottom': new Element('div').addClassName('pull-right col-sm-6')
				.insert({'bottom': new Element('input', {'type': 'text', 'placeholder': 'New Brand Name'}).setStyle('width: 80%; place-holder') })
				.insert({'bottom': new Element('i', {'class': 'btn btn-xs btn-primary pull-right'}).setStyle('width: 19%;').update('Add')})
			})
		tmp.manufacturersSelector = tmp.me.getFormGroup(tmp.manufacturersSelectorTitle,
				new Element('input', {'class': 'form-control select2 col-sm-12', 'config': 'manufacturers', 'name': 'manufacturers', 'placeholder': 'Name of manufacturer ... '}) );
		
		//return div
		tmp.newDiv = new Element('div')  
			.insert({'bottom': new Element('div',  {'class': 'row'})
				.insert({'bottom': new Element('div', {'class': 'col-xs-12'})
					.insert({'bottom': tmp.supplierSelector })
					.insert({'bottom': tmp.manufacturersSelector })
				})
			});
		return tmp.newDiv;
	}
	
	,_loadSelect2: function() {
		var tmp = {};
		tmp.me = this;
		tmp.selectBox = jQuery('[config="supplier"]').select2({
			dropdownAutoWidth : true,
			ajax: {
				delay: 250
				,url: '/ajax/getSuppliers'
				,type: 'POST'
				,data: function (params) {
					return {"searchTxt": params, 'entityName': 'Supplier', 'pageNo': 1};
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
			,escapeMarkup: function (markup) { return markup; } // let our custom formatter work
		});
		tmp.selectBox = jQuery('[config="manufacturers"]').select2({
			dropdownAutoWidth : true,
			multiple: true,
			ajax: {
				delay: 250
				,multiple: true
				,url: '/ajax/getAll'
					,type: 'POST'
						,data: function (params) {
							return {"searchTxt": params, 'entityName': 'Manufacturer', 'pageNo': 1, 'pageSize': 50};
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
		,escapeMarkup: function (markup) { return markup; } // let our custom formatter work
		});
		
		return tmp.me;
	}
});