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
	 * Setting the statuses
	 */
	,setStatuses: function(_statuses) {
		var tmp = {};
		tmp.me = this;
		tmp.me._statuses = _statuses;
		return tmp.me;
	}
	/**
	 * Setting the preSetData
	 */
	,setPreSetData: function(_preSetData) {
		var tmp = {};
		tmp.me = this;
		tmp.me._preSetData = _preSetData;
		return tmp.me;
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
	 * show the order details Page
	 */
	,showOrderDetailsPage: function(order) {
		var tmp = {};
		tmp.me = this;
		if(!order || !order.id)
			return tmp.me;
		jQuery.fancybox({
			'width'			: '95%',
			'height'		: '95%',
			'autoScale'     : false,
			'autoDimensions': false,
			'fitToView'     : false,
			'autoSize'      : false,
			'type'			: 'iframe',
			'href'			: '/orderdetails/' + order.id + '.html?blanklayout=1'
		});
		return tmp.me;
	}
	/**
	 * show the customer details Page
	 */
	,showCustomerDetailsPage: function(customer) {
		var tmp = {};
		tmp.me = this;
		if(!customer || !customer.id)
			return tmp.me;
		jQuery.fancybox({
			'width'			: '95%',
			'height'		: '95%',
			'autoScale'     : false,
			'autoDimensions': false,
			'fitToView'     : false,
			'autoSize'      : false,
			'type'			: 'iframe',
			'href'			: '/customer/' + customer.id + '.html?blanklayout=1'
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
		tmp.row = new Element('tr', {'class': 'order_item ' + (tmp.isTitle === true ? '' : 'btn-hide-row'), 'item_id' : (tmp.isTitle === true ? '' : row.id)})
			.store('data', row)
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-1'})
				.insert({'bottom': tmp.isTitle === true ? 'Task No.' : new Element('a', {'href': 'javascript: void(0);', 'title': 'view details'})
					.update(row.id)
					.observe('click', function() {
						tmp.me.showTaskPage(row);
					})
				})
			})
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-2'})
				.insert({'bottom': tmp.isTitle === true ? 'Customer' : new Element('a', {'href': 'javascript: void(0);'})
					.update( row.customer.name )
					.observe('click', function(){
						tmp.me.showCustomerDetailsPage(row.customer);
					})
				})
			})
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-2'})
				.insert({'bottom': tmp.isTitle === true ? 'Due Date' : new Element('span').update( moment(tmp.me.loadUTCTime(row.dueDate)).format('DD/MMM/YYYY hh:mm A') ) })
			})
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-1', 'order_status' : tmp.isTitle === true ? '' : row.status.name, 'title': tmp.isTitle === true ? '' : row.status.description})
				.insert({'bottom': tmp.isTitle === true ? 'Status' : new Element('span').update( row.status.name ) })
			})
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-1'})
				.insert({'bottom': tmp.isTitle === true ? 'Tech' : new Element('span').update( row.technician && row.technician.id ? row.technician.person.fullname : '' ) })
			})
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-2'})
				.insert({'bottom': tmp.isTitle === true ? 'Created From' : ((!row.fromEntity || !row.fromEntity.orderNo) ? '' :
						new Element('div', {'class': 'row'})
							.insert({'bottom': new Element('div', {'class': 'col-xs-3'}).update(row.fromEntity.type) })
							.insert({'bottom': new Element('div', {'class': 'col-xs-3', 'order_status': row.fromEntity.status.name}).update(row.fromEntity.status.name) })
							.insert({'bottom': new Element('a', {'class': 'col-xs-6', 'href': 'javascript: void(0);', 'title': 'view details'}).update(row.fromEntity.orderNo)
								.observe('click', function(){
									tmp.me.showOrderDetailsPage(row.fromEntity);
								})
							 })
				) })
			})
			.insert({'bottom': new Element(tmp.tag)
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
		tmp.selectBox = jQuery('[search_field="ord.id"]').select2({
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
		if(tmp.me._preSetData && tmp.me._preSetData.order && tmp.me._preSetData.order.id) {
			tmp.selectBox.select2('data', [{'id': tmp.me._preSetData.order.id, 'text': tmp.me._preSetData.order.orderNo, 'data': tmp.me._preSetData.order}]);
		}
		return tmp.me;
	}
	,_initTechSearchBox: function() {
		var tmp = {};
		tmp.me = this;
		tmp.selectBox = jQuery('[search_field="techId"]').select2({
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
		if(tmp.me._preSetData && tmp.me._preSetData.technician && tmp.me._preSetData.technician.id) {
			tmp.selectBox.select2('data', [{'id': tmp.me._preSetData.technician.id, 'text': tmp.me._preSetData.technician.person.fullname, 'data': tmp.me._preSetData.technician}]);
		}
		return tmp.me;
	}
	,_initCustomerSearchBox: function() {
		var tmp = {};
		tmp.me = this;
		tmp.selectBox = jQuery('[search_field="customer.id"]').select2({
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
								'entityName': 'Customer',
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
		});
		if(tmp.me._preSetData && tmp.me._preSetData.customer && tmp.me._preSetData.customer.id) {
			tmp.selectBox.select2('data', [{'id': tmp.me._preSetData.customer.id, 'text': tmp.me._preSetData.customer.name, 'data': tmp.me._preSetData.customer}]);
		}
		return tmp.me;
	}
	,_initStatusSearchBox: function() {
		var tmp = {};
		tmp.me = this;
		tmp.preSelectedStatusIds = [];
		if(tmp.me._preSetData && tmp.me._preSetData.statuses) {
			tmp.me._preSetData.statuses.each(function(status){
				tmp.preSelectedStatusIds.push(status.id);
			});
		}
		if(tmp.me._statuses && tmp.me._statuses.size() > 0 && $$('[search_field="statusId"]').first()) {
			tmp.me._statuses.each(function(status){
				$$('[search_field="statusId"]').first().insert({'bottom': tmp.option = new Element('option', {'value': status.id}).update(status.name)});
				if(tmp.preSelectedStatusIds.indexOf(status.id) >= 0 ) {
					tmp.option.writeAttribute('selected', true);
				}
			});
		}
		tmp.selectBox = jQuery('[search_field="statusId"]').select2();
		return tmp.me;
	}
	,init: function () {
		var tmp = {};
		tmp.me = this;
		jQuery('.datepicker').datetimepicker({
			format: 'DD/MM/YYYY'
		});
		tmp.me._initCustomerSearchBox()
			._initOrderSearchBox()
			._initTechSearchBox()
			._initStatusSearchBox();
		$('searchBtn').observe('click', function() {
			tmp.me.getSearchCriteria().getResults(true, 30);
		});
		return tmp.me;
	}
});