/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new BPCPageJs(), {
	_resultDivId: '' // the id of the result div
	,_totalUserCountId: '' // the id of the total user count
	,_pagination: {'pageNo': 1, 'pageSize': 10} //the pagination details
	,_searchCriteria: '' //the searching criteria
	,_roles: [] //all the roles
	
	,setResultDiv: function(resultDivId) {
		var tmp = {};
		tmp.me = this;
		tmp.me._resultDivId = resultDivId;
		return tmp.me;
	}
	
	,setTotalUserCountDiv: function(totalUserCountId) {
		var tmp = {};
		tmp.me = this;
		tmp.me._totalUserCountId = totalUserCountId;
		return tmp.me;
	}
	
	,_getEditField: function(title, input){
		var tmp = {}
		tmp.me = this;
		return new Element('fieldset')
			.insert({'bottom': new Element('label').update(title + ': ') })
			.insert({'bottom': input });
	}
	
	,_showEditPanel: function(item) {
		var tmp = {}
		tmp.me = this;
		jQuery.fancybox({
			'width'			: '80%',
			'height'		: '90%',
			'autoScale'     : false,
			'autoDimensions': false,
			'fitToView'     : false,
			'autoSize'      : false,
			'type'			: 'iframe',
			'href'			: item ? '/useraccount/edit/' + item.id + '.html' : '/useraccount/add.html',
			'beforeClose'	: function() {
				if(!$(tmp.me._resultDivId))
					return;
				if(!$$('iframe.fancybox-iframe').first().contentWindow.pageJs)
					return;
				tmp.userAccount = $$('iframe.fancybox-iframe').first().contentWindow.pageJs._user;
				if(tmp.userAccount) {
					tmp.itemRow = $(tmp.me._resultDivId).down('.useraccount_item[useraccount_id=' + tmp.userAccount.id + ']');
					if(tmp.itemRow)
						tmp.itemRow.replace(tmp.me._getItemRow(tmp.userAccount));
					else
						$(tmp.me._resultDivId).down('.header').insert({'after': tmp.me._getItemRow(tmp.userAccount) })
				}
			}
 		});
		return tmp.me;
	}
	
	,deleteUser: function(item, btn) {
		var tmp = {};
		tmp.me = this;
		tmp.me.postAjax(tmp.me.getCallbackId('deleteUser'), {"userId": item.id}, {
			'onCreate': function(sender, param) {
				if($(btn)) {
					jQuery('#' + btn.id).button('loading');
				}
			}
			,'onSuccess': function(sender, param) {
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result)
						return;
					//remove the current row
					tmp.resultRow = $(tmp.me._resultDivId).down("[useraccount_id=" + tmp.result.id + "]");
					if(tmp.resultRow) {
						tmp.resultRow.remove();
					}
					//reduce the count of users
					if($(tmp.me._totalUserCountId)) {
						$(tmp.me._totalUserCountId).update($(tmp.me._totalUserCountId).innerHTML * 1 - 1);
					}
				} catch (e) {
					alert(e);
				}
			}
			,'onComplete': function (sender, params) {
				jQuery('#' + btn.id).button('reset');
			}
		});
		return tmp.me;
	}
	
	,_getItemRow: function(item, isTitle) {
		var tmp = {};
		tmp.me = this;
		tmp.isTitle = (isTitle || false);
		
		tmp.roles = 'Roles';
		tmp.btnDiv = new Element('div', {'class': 'btn-group'});
		if(tmp.isTitle !== true) {
			tmp.roleNames = [];
			item.roles.each(function(role){
				tmp.roleNames.push(role.name);
			});
			tmp.roles = tmp.roleNames.join(', ');
			
			//btnsdiv
			tmp.btnDiv
				.insert({'bottom': new Element('span', {'id': 'edit_btn_' + item.id, 'class': 'btn btn-default edit', 'data-loading-text': 'Loading...'})
					.update(new Element('span', {'class': 'glyphicon glyphicon-pencil', 'title': 'Edit this user'}))
					.observe('click', function() {
						tmp.me._showEditPanel(item);
					})
				})
				.insert({'bottom': new Element('span', {'id': 'del_btn_' + item.id, 'class': 'btn btn-danger delete', 'data-loading-text': 'Processing...'})
					.update(new Element('span', {'class': 'glyphicon glyphicon-trash', 'title': 'Delete this user'}))
					.observe('click', function() {
						if(!confirm("You are trying to delete user: " + item.person.fullname + '\nContinue?'))
							return;
						tmp.me.deleteUser(item, this);
					})
				});
		}
		
		tmp.newDiv = new Element('div', {'class': 'useraccount_item list-group-item ' + (tmp.isTitle === true ? 'disabled header' : ''), 'useraccount_id': item.id})
			.store('data', item)
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('div', {'class': 'col-xs-2 firstName'}).update(item.person.firstName) })
				.insert({'bottom': new Element('div', {'class': 'col-xs-2 lastName'}).update(item.person.lastName) })
				.insert({'bottom': new Element('div', {'class': 'col-xs-4 roles'}).update(tmp.roles) })
				.insert({'bottom': new Element('div', {'class': 'col-xs-2 username'}).update(item.username) })
				.insert({'bottom': new Element('div', {'class': 'col-xs-2 btns'}).update(tmp.btnDiv)})
			});
		return tmp.newDiv;
	}
	
	,_getNextPageBtn: function() {
		var tmp = {}
		tmp.me = this;
		return new Element('div', {'class': 'pagination'})
			.insert({'bottom': new Element('span', {'class': 'button'}).update('Show More')
				.observe('click', function() {
					tmp.me._pagination.pageNo = tmp.me._pagination.pageNo*1 + 1;
					$(this).store('originVal', $(this).innerHTML).update('Fetching more results ...').addClassName('disabled');
					tmp.me.getUsers(this);
				})
			});
	}

	,getUsers: function(btn, reset) {
		var tmp = {};
		tmp.me = this;
		tmp.btn = (btn || null);
		tmp.reset = (reset || false);
		tmp.resultDiv = $(tmp.me._resultDivId);
		tmp.me.postAjax(tmp.me.getCallbackId('getUsers'), {"searchCriteria": tmp.me._searchCriteria, 'pagination': tmp.me._pagination}, {
			'onCreate': function(sender, param) {
				if(tmp.reset === true) {
					if(tmp.resultDiv) {
						tmp.resultDiv.update('');
					}
					if($(tmp.me._totalUserCountId)) {
						$(tmp.me._totalUserCountId).update(0);
					}
				}
				if(tmp.btn) {
					jQuery(tmp.btn.id).button('loading');
				}
			}
			,'onSuccess': function(sender, param) {
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result)
						return;
					//if total count div is set
					if($(tmp.me._totalUserCountId)) {
						$(tmp.me._totalUserCountId).update(tmp.result.pageStats.totalRows);
					}
					//if the result div is set
					if(tmp.resultDiv) {
						if(tmp.reset === true) {
							tmp.resultDiv.update( tmp.me._getItemRow({'person':{'firstName': 'First Name', 'lastName': 'Last Name'}, 'username': 'Username'}, true).addClassName('header') );
						}
						//remove next page button
						tmp.resultDiv.getElementsBySelector('.paginWrapper').each(function(item){
							item.remove();
						});
						
						//show all items
						tmp.result.items.each(function(item){
							tmp.resultDiv.insert({'bottom': tmp.me._getItemRow(item) });
						});
						//show the next page button
						if(tmp.result.pageStats.pageNumber < tmp.result.pageStats.totalPages)
							tmp.resultDiv.insert({'bottom': tmp.me._getNextPageBtn().addClassName('paginWrapper') });
					}
					
				} catch (e){
					tmp.resultDiv.up('.panel').down('.panel-body').insert({'bottom': tmp.me.getAlertBox('Error:', e).addClassName('alert-danger')})
				}
			}
			,'onComplete': function (sender, params) {
				if(tmp.btn) {
					jQuery(tmp.btn.id).button('reset');
				}
			}
		});
		return tmp.me;
	}
});