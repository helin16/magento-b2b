/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new CRUDPageJs(), {
	_openinFB: true
	/**
	 * Getting the title row data
	 */
	,_getTitleRowData: function() {
		return {};
	}
	/**
	 * whether to open in fancybox for details page
	 */
	,setOpenInFancyBox: function(_openinFB) {
		var tmp = {};
		tmp.me = this;
		tmp.me._openinFB = _openinFB;
		return tmp.me;
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
	,refreshTaskRow: function(row) {
		var tmp = {};
		tmp.me = this;
		return tmp.me.refreshResultRow(row);
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
	,_openURL: function(url) {
		var tmp = {};
		tmp.me = this;
		tmp.url = url;
		if(tmp.me._openinFB !== true) {
			window.location =tmp.url;
			return tmp.me;
		}
		jQuery.fancybox({
			'width'			: '95%',
			'height'		: '95%',
			'autoScale'     : false,
			'autoDimensions': false,
			'fitToView'     : false,
			'autoSize'      : false,
			'type'			: 'iframe',
			'href'			: tmp.url
 		});
		return tmp.me;
	}
	/**
	 * show the task details Page
	 */
	,showTaskPage: function(row) {
		var tmp = {};
		tmp.me = this;
		tmp.url = '/task/' + ((row && row.id) ? row.id : 'new') + '.html?blanklayout=1';
		return tmp.me._openURL(tmp.url);
	}
	/**
	 * show the order details Page
	 */
	,showOrderDetailsPage: function(order) {
		var tmp = {};
		tmp.me = this;
		if(!order || !order.id)
			return tmp.me;
		return tmp.me._openURL('/orderdetails/' + order.id + '.html?blanklayout=1');
	}
	/**
	 * show the customer details Page
	 */
	,showCustomerDetailsPage: function(customer) {
		var tmp = {};
		tmp.me = this;
		if(!customer || !customer.id)
			return tmp.me;
		return tmp.me._openURL('/customer/' + customer.id + '.html?blanklayout=1');
	}
	,_updateDueDateCell: function(row, dueDateCell) {
		var tmp = {};
		tmp.me = this;
		tmp.dueDateCell = dueDateCell;
		tmp.nowUTC = moment().utc();
		tmp.dueDateUTC = tmp.dueDateCell.retrieve('data');
		if(tmp.me._preSetData && tmp.me._preSetData.noDueDateStatusIds && tmp.me._preSetData.noDueDateStatusIds.indexOf(row.status.id) >= 0){
			return tmp.me;
		}
		tmp.overDued = (tmp.dueDateUTC.diff(tmp.nowUTC) < 0);
		if(tmp.overDued === true)
			tmp.dueDateCell.up('td').addClassName('danger').writeAttribute('title', 'Overdued!!!');

		tmp.diffHours = tmp.dueDateUTC.diff(tmp.nowUTC, 'hours', true).toFixed(1);
		if(Math.abs(tmp.diffHours) > 24) {
			tmp.dueDateCell.down('.left-time').update('<strong>' + tmp.dueDateUTC.diff(tmp.nowUTC, 'days') + '</strong> <small>Day(s)</small>');
		} else {
			tmp.dueDateCell.down('.left-time').update('<strong>' + tmp.diffHours + '</strong> <small>Hr(s)</small>');
			if(tmp.overDued !== true)
				tmp.dueDateCell.up('td').removeClassName('danger').addClassName('warning').writeAttribute('title', 'About to be overdued');
		}
		return tmp.me;
	}
	/**
	 * Ajax: Action of a Task
	 */
	,_actionTask: function (data, succFunc) {
		var tmp = {};
		tmp.me = this;
		tmp.me.postAjax(tmp.me.getCallbackId('actionTask'), data, {
			'onSuccess': function(sender, param) {
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result || !tmp.result.item || !tmp.result.item.id)
						return;
					tmp.me.refreshResultRow(tmp.result.item);
					if(typeof(succFunc) === 'function')
						succFunc(tmp.result);
				} catch(e) {
					tmp.me.hideModalBox();
					tmp.me.showModalBox('<strong class="text-danger">Error</strong>', e);
				}
			}
		})
		return tmp.me;
	}
	,_preActionTask: function(data) {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = new Element('div', {'class': 'confirm-div'})
			.insert({'bottom': new Element('div', {'class': 'msg-div'}) })
			.insert({'bottom': new Element('div', {'class': 'form-group'})
				.insert({'bottom': new Element('label', {'class': 'control-label'}).update(tmp.msg = 'Reason for action: ' + data.method) })
				.insert({'bottom': new Element('textarea', {'class': 'form-control', 'confirm-div': 'comments', 'placeholder': tmp.msg }) })
			})
			.insert({'bottom': new Element('div')
				.insert({'bottom': new Element('div', {'class': 'btn btn-default'})
					.update('Cancel')
					.observe('click', function() {
						tmp.me.hideModalBox();
					})
				})
				.insert({'bottom': new Element('div', {'class': 'btn btn-danger pull-right'})
					.update('Comfirm')
					.observe('click', function() {
						tmp.confirmDiv = $(this).up('.confirm-div');
						tmp.confirmDiv.down('.msg-div').update('');
						tmp.comments = $F(tmp.confirmDiv.down('[confirm-div="comments"]'));
						if(tmp.comments.blank()) {
							tmp.confirmDiv.down('.msg-div').update(tmp.me.getAlertBox('', 'Please provide some reason.').addClassName('alert-danger'));
							return;
						}
						data.comments = tmp.comments ;
						tmp.me._actionTask(data, function(){
							tmp.me.hideModalBox();
						});
					})
				})
			})
		tmp.me.hideModalBox();
		tmp.me.showModalBox('<strong class="text-danger">Please Confirm</strong>', tmp.newDiv);
		return tmp.me;
	}
	/**
	 * getting the technician cell
	 */
	,_getTechCell: function(row){
		var tmp = {};
		tmp.me = this;
		tmp.techName = (row.technician && row.technician.id ? row.technician.person.fullname : '')
		tmp.newDiv = new Element('div', {'class': 'row'})
			.update(tmp.nameCell = new Element('span', {'class': 'col-xs-10 truncate'}).update(tmp.techName))
			.insert({'bottom': new Element('div', {'class': 'pull-right'}).setStyle('margin: 0 5px 0 0;').update( tmp.btns = new Element('div', {'class': 'btn-group btn-group-xs visible-xs visible-sm visible-md visible-lg'}) ) });
		if(tmp.me._preSetData && tmp.me._preSetData.noDueDateStatusIds && tmp.me._preSetData.noDueDateStatusIds.indexOf(row.status.id) < 0) {
			if(tmp.techName.blank()) {
				tmp.btns.insert({'bottom': new Element('span', {'class': 'btn btn-primary', 'title': 'Take this Task'})
					.update('Take')
					.observe('click', function() {
						tmp.me._actionTask({'taskId': row.id, 'method': 'take'});
					})
				});
			} else if(tmp.me._preSetData && tmp.me._preSetData.meId && tmp.me._preSetData.meId === row.technician.id) {
				tmp.btns.insert({'bottom': new Element('span', {'class': 'btn btn-success dropdown-toggle', 'data-toggle': "dropdown",'aria-expanded': "false"})
					.update(new Element('span', {'class': 'caret'}) )
				})
				.insert({'bottom': tmp.menu = new Element('ul', {'class': 'dropdown-menu'})
					.insert({'bottom': row.status.id !== '3' ? new Element('li').update( new Element('a', {'href': 'javascript:void(0);'})
								.update('Start')
								.observe('click', function() {
									tmp.me._actionTask({'taskId': row.id, 'method': 'start'});
								})
							)
						:
						new Element('li').update( new Element('a', {'href': 'javascript:void(0);'})
							.update('Finish')
							.observe('click', function() {
								tmp.me._actionTask({'taskId': row.id, 'method': 'finish'});
							})
						)
					})
					.insert({'bottom': new Element('li').update( new Element('a', {'href': 'javascript:void(0);'})
						.update('Release')
						.observe('click', function() {
							tmp.me._actionTask({'taskId': row.id, 'method': 'release'});
						})
					) })
					.insert({'bottom': new Element('li', {'class': 'divider'}) })
					.insert({'bottom': new Element('li').update( new Element('a', {'href': 'javascript:void(0);'})
						.update('<strong class="text-danger">ON HOLD</strong>')
						.observe('click', function(){
							tmp.me._preActionTask({'taskId': row.id, 'method': 'onHold'});
						})
					) })
					.insert({'bottom': new Element('li').update( new Element('a', {'href': 'javascript:void(0);'})
						.update('<strong class="text-danger">CANCEL</strong>')
						.observe('click', function(){
							tmp.me._preActionTask({'taskId': row.id, 'method': 'cancel'});
						})
					) })
				});
				if(tmp.menu && row.status.id === '3') {
					tmp.menu.insert({'top': new Element('li', {'class': 'divider'}) })
					tmp.menu.insert({'top': new Element('li').update( new Element('a', {'href': 'javascript:void(0);'})
						.update('Build a Kit')
						.observe('click', function() {
							tmp.me._openURL('/kit/new.html?taskid=' + row.id + '&blanklayout=1')
						})
					) });
				}
			}
		}
		if(!tmp.techName.blank())
			tmp.nameCell.writeAttribute('title', tmp.techName);
		return tmp.newDiv;
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
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-1'})
				.insert({'bottom': tmp.isTitle === true ? 'Customer' : new Element('a', {'href': 'javascript: void(0);', "class": 'truncate', 'title': row.customer.name})
					.update( row.customer.name )
					.observe('click', function(){
						tmp.me.showCustomerDetailsPage(row.customer);
					})
				})
			})
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-2'})
				.insert({'bottom': tmp.isTitle === true ? 'Due Date<font class="pull-right">Time Left</font> ' : tmp.dueDateCell = new Element('div', {'class': 'row'}).store('data', tmp.dueDateUTC = moment(tmp.me.loadUTCTime(row.dueDate)))
					.insert({'bottom': new Element('div', {'class': 'col-xs-6'}).update( new Element('small').update( tmp.dueDateUTC.format('DD/MMM/YYYY hh:mm A') ) ) })
					.insert({'bottom': new Element('div', {'class': 'col-xs-6  text-right'}).update( new Element('span', {'class': 'left-time'}).update( '' ) ) })
				})
			})
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-1', 'order_status' : tmp.isTitle === true ? '' : row.status.name, 'title': tmp.isTitle === true ? '' : row.status.description})
				.insert({'bottom': tmp.isTitle === true ? 'Status' : new Element('span').update( row.status.name ) })
			})
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-1'})
				.insert({'bottom': tmp.isTitle === true ? 'Tech' :  tmp.me._getTechCell(row) })
			})
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-2 truncate'})
				.insert({'bottom': tmp.isTitle === true ? 'Instructions' : new Element('a', {'href': 'javascript: void(0);', 'title': 'click to view all'})
					.update(row.instructions.stripTags())
					.observe('click', function() {
						tmp.me.hideModalBox();
						tmp.me.showModalBox('<strong>Instructions for Task: ' + row.id + '</strong>', row.instructions);
					})
				})
			})
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-1'})
				.insert({'bottom': tmp.isTitle === true ? 'No. Of Kits' : new Element('div')
					.insert({'bottom': new Element('div', {'class': 'col-xs-8'}).update(!(row.noOfKits && row.noOfKits > 0) ? '' : new Element('a', {'href': 'javascript: void(0);'})
							.update(row.noOfKits)
							.observe('click', function(){
								tmp.me._openURL('/kits.html?nosearch=1&blanklayout=1&taskId=' + row.id);
							})
					) })
					.insert({'bottom': new Element('div', {'class': 'col-xs-4'}).update( !(tmp.me._preSetData && tmp.me._preSetData.meId && tmp.me._preSetData.meId === row.technician.id && row.status && row.status.id === '3') ? '' : new Element('span', {'class': 'btn btn-xs btn-success'})
						.insert({'bottom': new Element('i', {'class': 'fa fa-plus'})})
						.observe('click', function(){
							tmp.me._openURL('/kit/new.html?blanklayout=1&taskid=' + row.id);
						})
					) })
				})
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
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-1 truncate'})
				.insert({'bottom': tmp.isTitle === true ? 'Created By' : new Element('div', {'title': 'By: ' + row.createdBy.person.fullname + ' @ ' + moment(tmp.me.loadUTCTime(row.created)).format('DD/MMM/YYYY hh:mm A')})
					.insert({'bottom': new Element('div', {'class': 'col-xs-12'}).update(row.createdBy.person.fullname) })
				})
			});
		if(tmp.dueDateCell) {
			tmp.me._updateDueDateCell(row, tmp.dueDateCell)
		}
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