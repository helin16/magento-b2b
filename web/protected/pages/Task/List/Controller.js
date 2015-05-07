/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new CRUDPageJs(), {
	_getTitleRowData: function() {
		return {};
	}
	,_getResultRow: function(row, isTitle) {
		var tmp = {};
		tmp.me = this;
		tmp.tag = (tmp.isTitle === true ? 'th' : 'td');
		tmp.isTitle = (isTitle || false);
		tmp.row = new Element('tr', {'class': (tmp.isTitle === true ? '' : 'btn-hide-row')})
			.store('data', row)
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-1'})
				.insert({'bottom': tmp.isTitle === true ? 'Task No.' : new Element('a').update(row.id) })
			})
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-1'})
				.insert({'bottom': tmp.isTitle === true ? 'Due Date' : new Element('span').update( moment(tmp.me.loadUTCTime(row.dueDate)).format('lll') ) })
			})
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-1'})
				.insert({'bottom': tmp.isTitle === true ? 'Status' : new Element('span').update( row.status.name ) })
			})
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-1'})
				.insert({'bottom': tmp.isTitle === true ? 'Tech' : new Element('span').update( row.technician && row.technician.id ? row.technician.fullName : '' ) })
			})
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-2'})
				.insert({'bottom': tmp.isTitle === true ? 'Created From' : new Element('a').update( row.fromEntityId ) })
			})
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-4'})
				.insert({'bottom': tmp.isTitle === true ? 'Created By' : new Element('div', {'class': 'row'})
					.insert({'bottom': new Elmenet('div', {'class': 'col-xs-6'}).update(row.createdBy.fullName) })
					.insert({'bottom': new Elmenet('div', {'class': 'col-xs-6'}).update(  moment(tmp.me.loadUTCTime(row.created)).format('lll') ) })
				})
			});
		return tmp.row;
	}

	,_initOrderSearchBox: function() {
		var tmp = {};
		tmp.me = this;
		jQuery('[search_field="ord.id"]').select2({
			 minimumInputLength: 3,
			 multiple: true,
			 ajax: {
				 delay: 250
				 ,url: '/ajax/getAll'
		         ,type: 'POST'
	        	 ,data: function (params) {
	        		 return {"searchTxt": 'orderNo like ?', 'searchParams': ['%' + params + '%'], 'entityName': 'Order', 'pageNo': 1};
	        	 }
				 ,results: function(data, page, query) {
					 tmp.result = [];
					 if(data.resultData && data.resultData.items) {
						 data.resultData.items.each(function(item){
							 tmp.result.push({'id': item.id, 'text': item.orderNo, 'data': item});
						 });
					 }
		    		 return { 'results' : tmp.result };
		    	 }
			 }
			,cache: true
			,formatResult : function(result) {
				 if(!result)
					 return '';
				 return '<div class="row order_item"><div class="col-xs-3">' + result.data.orderNo + '</div><div class="col-xs-3" order_status="' + result.data.status.name + '">' + result.data.status.name + '</div><div class="col-xs-6"><small>' + result.data.customer.name + '</small></div></div >';
			 }
			 ,escapeMarkup: function (markup) { return markup; } // let our custom formatter work
			 ,minimumInputLength: 1
		});
		return tmp.me;
	}
	,_initTechSearchBox: function() {
		var tmp = {};
		tmp.me = this;
		jQuery('[search_field="techId"]').select2({
			minimumInputLength: 3
			,multiple: true
			,ajax: {
				delay: 250
				,url: '/ajax/getAll'
				,type: 'POST'
				,data: function (params) {
					return {
						'searchTxt': 'personId in (select id from person p where concat(p.firstName, " ", p.lastName) like ?)',
						'searchParams': ['%' + params + '%'],
						'entityName': 'UserAccount',
						'pageNo': 1
					};
				}
				,results: function(data, page, query) {
					tmp.result = [];
					if(data.resultData && data.resultData.items) {
						data.resultData.items.each(function(item){
							tmp.result.push({'id': item.id, 'text': item.person.fullname, 'data': item});
						});
					}
					return { 'results' : tmp.result };
				}
			}
			,cache: true
			,escapeMarkup: function (markup) { return markup; } // let our custom formatter work
			,minimumInputLength: 1
		});
		return tmp.me;
	}
	,_initStatusSearchBox: function() {
		var tmp = {};
		tmp.me = this;
		jQuery('[search_field="statusId"]').select2({
			minimumInputLength: 3
			,multiple: true
			,ajax: {
				delay: 250
				,url: '/ajax/getAll'
					,type: 'POST'
						,data: function (params) {
							return {
								'searchTxt': 'name like ?',
								'searchParams': ['%' + params + '%'],
								'entityName': 'TaskStatus',
								'pageNo': 1
							};
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
		,minimumInputLength: 1
		});
		return tmp.me;
	}

	,init: function () {
		var tmp = {};
		tmp.me = this;
		jQuery('.datepicker').datetimepicker({
			format: 'DD/MM/YYYY'
		});
		tmp.me._initOrderSearchBox()
			._initTechSearchBox()
			._initStatusSearchBox();
		return tmp.me;
	}
});