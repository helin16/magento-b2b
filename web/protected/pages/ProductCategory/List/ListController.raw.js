/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new CRUDPageJs(), {
	_getTitleRowData: function() {
		return {'description': "Description",
					'name': 'Name',
					'mageId': 'mageId',
					'noOfChildren': null
				};
	}
	,_bindSearchKey: function() {
		var tmp = {}
		tmp.me = this;
		$('searchPanel').getElementsBySelector('[search_field]').each(function(item) {
			item.observe('keydown', function(event) {
				tmp.me.keydown(event, function() {
					$('searchBtn').click();
				});
			})
		});
		return this;
	}
	,_saveItem: function(btn, savePanel, attrName) {
		var tmp = {};
		tmp.me = this;
		tmp.data = tmp.me._collectFormData(savePanel, attrName);
		if(tmp.data === null)
			return;
		tmp.me.postAjax(tmp.me.getCallbackId('saveItem'), {'item': tmp.data}, {
			'onLoading': function () { 
				if(tmp.data.id) {
					savePanel.addClassName('item_row').writeAttribute('item_id', tmp.data.id); 
				}
				savePanel.hide();
			}
			,'onSuccess': function(sender, param) {
				try{
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result || !tmp.result.item)
						return;
					tmp.tbody = $(tmp.me.resultDivId).down('tbody');
					tmp.row = tmp.tbody.down('.item_row[item_id=' + tmp.result.item.id + ']');
					tmp.newRow = tmp.me._getResultRow(tmp.result.item).addClassName('item_row').writeAttribute('item_id', tmp.result.item.id);
					if(!tmp.row)
					{
						if(tmp.result.parent && tmp.result.parent.id && (tmp.parentRow = tmp.tbody.down('.item_row[item_id=' + tmp.result.parent.id + ']'))) {
							if(!tmp.tbody.down('[parentId=' + tmp.result.parent.id + ']'))
								tmp.parentRow.replace(tmp.me._getResultRow(tmp.result.parent).addClassName('item_row').writeAttribute('item_id', tmp.result.parent.id));
							else
								tmp.parentRow.insert({'after': tmp.newRow });
						} else {
							tmp.tbody.insert({'top': tmp.newRow });
							savePanel.remove();
							$(tmp.me.totalNoOfItemsId).update($(tmp.me.totalNoOfItemsId).innerHTML * 1 + 1);
						}
					}
					else
					{
						tmp.row.replace(tmp.newRow);
					}
				
				} catch (e) {
					tmp.me.showModalBox('<span class="text-danger">ERROR:</span>', e, true);
					savePanel.show();
				}
			}
		});
		return tmp.me;
	}

	,_getEditPanel: function(row) {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = new Element('tr', {'class': 'save-item-panel info'}).store('data', row)
			.insert({'bottom': new Element('input', {'type': 'hidden', 'save-item-panel': 'id', 'value': row.id ? row.id : ''}) })
			.insert({'bottom': new Element('input', {'type': 'hidden', 'save-item-panel': 'parentId', 'value': row.parent && row.parent.id ? row.parent.id : ''}) })
			.insert({'bottom': new Element('td', {'class': 'form-group'})
				.insert({'bottom': new Element('input', {'required': true, 'class': 'form-control', 'placeholder': 'The name', 'save-item-panel': 'name', 'value': row.name ? row.name : ''}) })
			})
			.insert({'bottom': new Element('td', {'class': 'form-group'})
				.insert({'bottom': new Element('input', {'class': 'form-control', 'placeholder': 'Optional - The description', 'save-item-panel': 'description', 'value': row.description ? row.description : ''}) })
			})
			.insert({'bottom': new Element('td', {'class': 'text-right'})
				.insert({'bottom':  new Element('span', {'class': 'btn-group btn-group-sm'})
					.insert({'bottom': new Element('span', {'class': 'btn btn-success', 'title': 'Save'})
						.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-ok'}) })
						.observe('click', function(){
							tmp.btn = this;
							tmp.me._saveItem(tmp.btn, $(tmp.btn).up('.save-item-panel'), 'save-item-panel');
						})
					})
					.insert({'bottom': new Element('span', {'class': 'btn btn-danger', 'title': 'Delete'})
						.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-remove'}) })
						.observe('click', function(){
							if(row.id)
								$(this).up('.save-item-panel').replace(tmp.me._getResultRow(row).addClassName('item_row').writeAttribute('item_id', row.id) );
							else
								$(this).up('.save-item-panel').remove();
						})
					})
				})
			})
		return tmp.newDiv;
	}
	
	,_removeRowByParentId: function(tbody, category) {
		var tmp = {};
		tmp.me = this;
		tbody.getElementsBySelector('.item_row[parentId=' + category.id + ']').each(function(row){
			tmp.me._removeRowByParentId(tbody, row.retrieve('data'));
			row.remove();
		});
		return tmp.me;
	}
	
	,_getChildrenRows: function(btn, category) {
		var tmp = {};
		tmp.me = this;
		tmp.icon = $(btn).down('.icon');
		//clear all
		if(tmp.icon.hasClassName('glyphicon-minus-sign')) {
			tmp.icon.removeClassName('glyphicon-minus-sign').addClassName('glyphicon-plus-sign');
			tmp.me._removeRowByParentId($(tmp.me.resultDivId).down('tbody'), category);
		} else {
			tmp.icon.removeClassName('glyphicon-plus-sign').addClassName('glyphicon-minus-sign');
			tmp.me.postAjax(tmp.me.getCallbackId('getItems'), {'searchCriteria': {'parentId': category.id}, 'pagination': {'pageNo': null, 'pageSize': tmp.me._pagination.pageSize}}, {
				'onLoading': function () {}
				,'onSuccess': function(sender, param) {
					try{
						tmp.result = tmp.me.getResp(param, false, true);
						if(!tmp.result || !tmp.result.items)
							return;
						tmp.row = $(btn).up('.item_row');
						tmp.result.items.each(function(item) {
							tmp.row.insert({'after': tmp.me._getResultRow(item).addClassName('item_row').writeAttribute('item_id', item.id)})
						})
					
					} catch (e) {
						tmp.me.showModalBox('<span class="text-danger">ERROR:</span>', e, true);
					}
				}
			});
		}
		return tmp.me;
	}
	
	,_getPreName: function (category) {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = '';
		if(!category.position)
			return tmp.newDiv;
		
		tmp.levels = category.position.split('|').size();
		tmp.newDiv = new Element('small');
		for(tmp.i = 1; tmp.i < tmp.levels; tmp.i = (tmp.i * 1 + 1)) {
			tmp.newDiv.insert({'bottom': new Element('span', {'class': 'treegrid-indent'}) });
		}
		if(category.noOfChildren > 0) {
			tmp.newDiv.insert({'bottom': new Element('a', {'href': 'javascript: void(0);', 'class': 'treegrid-explander'})
				.update( new Element('span', {'class': 'icon glyphicon glyphicon-plus-sign'}) ) 
				.observe('click', function(){
					tmp.me._getChildrenRows(this, category);
				})
			});
		} else {
			tmp.newDiv.insert({'bottom': new Element('span', {'class': 'treegrid-explander'}) });
		}
		return tmp.newDiv;
	}
	
	,_deleteItem: function(row) {
		var tmp = {};
		tmp.me = this;
		tmp.row = $(tmp.me.resultDivId).down('tbody').down('.item_row[item_id=' + row.id + ']');
		tmp.me.postAjax(tmp.me.getCallbackId('deleteItems'), {'ids': [row.id]}, {
			'onLoading': function () { 
				if(tmp.row) {
					tmp.row.hide(); 
				}
			}
			,'onSuccess': function(sender, param) {
				try{
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result || !tmp.result.parents)
						return;
					tmp.count = $(tmp.me.totalNoOfItemsId).innerHTML * 1 - 1;
					$(tmp.me.totalNoOfItemsId).update(tmp.count <= 0 ? 0 : tmp.count);
					if(tmp.row) {
						tmp.row.remove();
					}
					//refresh the parents
					tmp.result.parents.each(function(parent) {
						tmp.parentRow = $(tmp.me.resultDivId).down('tbody').down('.item_row[item_id=' + parent.id + ']');
						if(tmp.parentRow) {
							tmp.parentRow.replace(tmp.me._getResultRow(parent).addClassName('item_row').writeAttribute('item_id', parent.id))
						}
					});
				} catch (e) {
					tmp.me.showModalBox('<span class="text-danger">ERROR</span>', e, true);
					if(tmp.row) {
						tmp.row.show();
					}
				}
			}
		});
		return tmp.me;
	}

	,_getResultRow: function(row, isTitle) {
		var tmp = {};
		tmp.me = this;
		tmp.tag = (tmp.isTitle === true ? 'th' : 'td');
		tmp.isTitle = (isTitle || false);
		tmp.btns = new Element('span', {'class': 'btn-group btn-group-xs'})
			.insert({'bottom': new Element('span', {'class': 'btn btn-primary', 'title': 'Add under this category'})
				.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-plus'}) })
				.observe('click', function(){
					$(this).up('.item_row').insert({'after': tmp.me._getEditPanel({'parent': {'id': row.id}}) });
				})
			})
			.insert({'bottom': new Element('span', {'class': 'btn btn-default', 'title': 'Edit'})
				.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-pencil'}) })
				.observe('click', function(){
					$(this).up('.item_row').replace(tmp.me._getEditPanel(row));
				})
			});
		
		if(!(row.noOfChildren > 0)) {
			tmp.btns.insert({'bottom': new Element('span', {'class': 'btn btn-danger', 'title': 'Delete'})
				.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-trash'}) })
				.observe('click', function(){
					if(!confirm('Are you sure you want to delete this item?'))
						return false;
					tmp.me._deleteItem(row);
				})
			});
		}
		tmp.row = new Element('tr', {'class': (tmp.isTitle === true ? '' : 'btn-hide-row')}).store('data', row)
			.insert({'bottom': new Element(tmp.tag, {'class': 'name col-xs-4'})
				.insert({'bottom': tmp.me._getPreName(row) })
				.insert({'bottom': ' ' + row.name })
			})
			.insert({'bottom': new Element(tmp.tag, {'class': 'mageId'}).update(row.mageId) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'description'}).update(row.description) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'text-right btns col-xs-2'}).update(
				tmp.isTitle === true ?  
				(new Element('span', {'class': 'btn btn-primary btn-xs', 'title': 'New'})
					.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-plus'}) })
					.insert({'bottom': ' NEW' })
					.observe('click', function(){
						$(this).up('thead').insert({'bottom': tmp.me._getEditPanel({}) });
					})
				)
				: tmp.btns
			) });
		if(row.parent && row.parent.id) {
			tmp.row.writeAttribute('parentId', row.parent.id);
		}
		return tmp.row;
	}
});