/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new CRUDPageJs(), {
	/**
	 * Getting the title row data
	 */
	_getTitleRowData: function() {
		return {};
	}
	/**
	 * Gathering the search criteria
	 */
	,getSearchCriteria: function() {
		var tmp = {};
		tmp.me = this;
		if(tmp.me._searchCriteria === null)
			tmp.me._searchCriteria = {};
		tmp.nothingTosearch = true;
		$(tmp.me.searchDivId).getElementsBySelector('[search_field]').each(function(item) {
			if(item.hasClassName('datepicker')) {
				tmp.me._signRandID(item);
				tmp.date = jQuery('#' + item.id).data('DateTimePicker').date();
				tmp.me._searchCriteria[item.readAttribute('search_field')] = tmp.date;
			}
			else
				tmp.me._searchCriteria[item.readAttribute('search_field')] = $F(item);
			if(($F(item) instanceof Array && $F(item).size() > 0) || (typeof $F(item) === 'string' && !$F(item).blank()))
				tmp.nothingTosearch = false;
		});
		if(tmp.nothingTosearch === true)
			tmp.me._searchCriteria = null;
		return this;
	}
	,refreshResultRow: function(row) {
		var tmp = {};
		tmp.me = this;
		tmp.tbody = $(tmp.me.resultDivId).down('tbody');
		if(!tmp.tbody)
			tmp.tbody = $(tmp.me.resultDivId);
		tmp.row = tmp.tbody.down('.item_row[item_id=' + row.id + ']');
		if(tmp.row)
			tmp.row.replace(tmp.me._getResultRow(row, false).addClassName('item_row'));
		else
			tmp.tbody.insert({'top': tmp.me._getResultRow(row, false).addClassName('item_row')});
		return tmp.me;
	}
	/**
	 * show the task details Page
	 */
	,showTaskPage: function(row) {
		var tmp = {};
		tmp.me = this;
		jQuery.fancybox({
			'width'			: '95%',
			'height'		: '95%',
			'autoScale'     : false,
			'autoDimensions': false,
			'fitToView'     : false,
			'autoSize'      : false,
			'type'			: 'iframe',
			'href'			: '/task/' + ((row && row.id) ? row.id : 'new') + '.html?blanklayout=1'
 		});
		return tmp.me;
	}
	/**
	 * Getting the result row for the table
	 */
	,_getResultRow: function(row, isTitle) {
		var tmp = {};
		tmp.me = this;
		tmp.tag = (tmp.isTitle === true ? 'th' : 'td');
		tmp.isTitle = (isTitle || false);
		tmp.row = new Element('tr', {'class': 'order_item ' + (tmp.isTitle === true ? '' : 'btn-hide-row')})
			.store('data', row)
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-1'})
				.insert({'bottom': tmp.isTitle === true ? 'Task No.' : new Element('a', {'href': 'javascript: void(0);', 'title': 'view details'})
					.update(row.id)
					.observe('click', function() {
						tmp.me.showTaskPage(row);
					})
				})
			})
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-1'})
				.insert({'bottom': tmp.isTitle === true ? 'Due Date' : new Element('span').update( moment(tmp.me.loadUTCTime(row.dueDate)).format('DD/MMM/YYYY hh:mm A') ) })
			})
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-1', 'order_status' : tmp.isTitle === true ? '' : row.status.name, 'title': tmp.isTitle === true ? '' : row.status.description})
				.insert({'bottom': tmp.isTitle === true ? 'Status' : new Element('span').update( row.status.name ) })
			})
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-1'})
				.insert({'bottom': tmp.isTitle === true ? 'Tech' : new Element('span').update( row.technician && row.technician.id ? row.technician.person.fullname : '' ) })
			})
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-1'})
				.insert({'bottom': tmp.isTitle === true ? 'Created From' : ((row.fromEntity && row.fromEntity.orderNo) ? new Element('a', {'href': '/orderdetails/' + row.fromEntity.id + '.html', 'target': '_BLANK'}).update('[' + row.fromEntityName + '] ' + row.fromEntity.orderNo) : '') })
			})
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-4'})
				.insert({'bottom': tmp.isTitle === true ? 'Created By' : new Element('div')
					.insert({'bottom': new Element('div', {'class': 'col-xs-4'}).update(row.createdBy.person.fullname) })
					.insert({'bottom': new Element('div', {'class': 'col-xs-8'}).update(  moment(tmp.me.loadUTCTime(row.created)).format('DD/MMM/YYYY hh:mm A') ) })
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
				 return '<div class="row order_item"><div class="col-xs-3">' + result.data.orderNo + '</div><div class="col-xs-3" order_status="' + result.data.status.name + '">' + result.data.status.name + '</div><div class="col-xs-6"><small>' + ((result.data.customer && result.data.customer.name) ? result.data.customer.name : '') + '</small></div></div >';
			 }
			 ,escapeMarkup: function (markup) { return markup; } // let our custom formatter work
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
		});
		return tmp.me;
	}
	,_initStatusSearchBox: function() {
		var tmp = {};
		tmp.me = this;
		jQuery('[search_field="statusId"]').select2({
			multiple: true
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
		$('searchBtn').observe('click', function() {
			tmp.me.getSearchCriteria().getResults(true, 30);
		});
		return tmp.me;
	}
});