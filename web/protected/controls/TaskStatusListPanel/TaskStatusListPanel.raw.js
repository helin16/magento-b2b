var TaskStatusListPanelJs = new Class.create();
TaskStatusListPanelJs.prototype = Object.extend(new BPCPageJs(), {
	_pageJs: null
	,_openinFB: true
	,initialize: function(_pageJs) {
		this._pageJs = _pageJs;
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
	 * open the task listing page
	 */
	,_getTaskListPage: function(statusId) {
		var tmp = {};
		tmp.me = this;
		tmp.url ='/tasks.html?blanklayout=1&nosearch=1&statusIds=' + statusId + ((tmp.me.entityName === 'Order' && tmp.me.entity && tmp.me.entity.id) ? ('&orderId=' + tmp.me.entity.id) : '');
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
			'href'			: tmp.url,
			'beforeClose'	: function() {
				tmp.me.render();
			}
 		});
		return tmp.me;
	}
	/**
	 * open the task listing page
	 */
	,_getTaskDetailsPage: function(row) {
		var tmp = {};
		tmp.me = this;
		tmp.rowId = ((row && row.id) ? row.id : 'new');
		tmp.url ='/task/' + tmp.rowId + '.html?blanklayout=1';
		if(tmp.rowId === 'new') {
			tmp.url = tmp.url + ((tmp.me.entity && tmp.me.entity.customer && tmp.me.entity.customer.id) ? ('&customerId=' + tmp.me.entity.customer.id) : '');
			if(tmp.me.entityName === 'Order' && tmp.me.entity && tmp.me.entity.id) {
				tmp.url = tmp.url + ('&orderId=' + tmp.me.entity.id);
			}
		}
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
			'href'			: tmp.url,
			'beforeClose'	: function() {
				tmp.me.render();
			}
		});
		return tmp.me;
	}
	,_getListDiv: function() {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = new Element('div', {'class': 'TaskStatusListPanel alert alert-info'})
			.setStyle('padding: 8px;')
			.store('data', {'entity': tmp.me.entity, 'entityName': tmp.me.entityName})
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('div', {'class': 'col-sm-2'})
					.setStyle('cursor: pointer')
					.insert({'bottom': new Element('span', {'class': 'text-success'})
						.insert({'bottom': new Element('i', {'class': 'glyphicon glyphicon-plus-sign'}) })
					})
					.insert({'bottom': ' <strong>Tasks:</strong>'})
					.observe('click', function(){
						tmp.me._getTaskDetailsPage();
					})
				})
				.insert({'bottom': new Element('div', {'class': 'col-sm-10 task-list-wrapper'}).update(new Element('small').update(tmp.me._pageJs.getLoadingImg().removeClassName('fa-5x'))) })
			});
		tmp.me._pageJs._signRandID(tmp.newDiv);
		tmp.me._divId = tmp.newDiv.id;
		return tmp.newDiv;
	}

	,getDiv: function(entityName, entity) {
		var tmp = {};
		tmp.me = this;
		tmp.me.entityName = entityName;
		tmp.me.entity = entity;
		return tmp.me._getListDiv();
	}
	,_formatResult: function(data) {
		var tmp = {};
		tmp.me = this;
		if(!tmp.me._divId || !$(tmp.me._divId) || !data || !data.resultData || !data.resultData.items)
			return tmp.me;
		tmp.listMap = {};
		tmp.listMapKeys = [];
		data.resultData.items.each(function(item){
			if(!tmp.listMap[item.status.id]) {
				tmp.listMapKeys.push(item.status.id);
				tmp.listMap[item.status.id] = {'status': item.status, 'items': []};
			}
			tmp.listMap[item.status.id]['items'].push(item);
		});
		tmp.list = new Element('div', {'class': 'list-inline order_item item_row'});
		tmp.listMapKeys.each(function(key){
			tmp.list.insert({'bottom': new Element('li', {'order_status': tmp.listMap[key].status.name})
				.setStyle('padding: 0 4px; margin: 0 10px; cursor: pointer;')
				.insert({'bottom': new Element('small').update(tmp.listMap[key].status.name + ': ') })
				.insert({'bottom': new Element('strong').update(tmp.listMap[key].items.size()) })
				.observe('click', function(){
					if(tmp.listMap[key].items && tmp.listMap[key].items.size() === 1)
						tmp.me._getTaskDetailsPage(tmp.listMap[key].items[0]);
					else
						tmp.me._getTaskListPage(tmp.listMap[key].status.id);
				})
			})
		});
		$(tmp.me._divId).down('.task-list-wrapper').update(tmp.list);
		return tmp.me;
	}

	,render: function() {
		var tmp = {};
		tmp.me = this;
		jQuery.getJSON('/ajax/getAll', {
			'entityName': 'Task',
			'searchTxt': 'fromEntityName = ? and fromEntityId = ?',
			'searchParams': [tmp.me.entityName, tmp.me.entity.id]
		}, function( data){
			tmp.me._formatResult(data);
		})
		return tmp.me;
	}
});