/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new BPCPageJs(), {
	_resultDivId: '' // the id of the result div
	,_addCompanyDivId: ''	
	,_pageInfo: {'pageNo': 1, 'pageSize': 30}
	,_searchCriteria: ''
		
	,_getEditAliasRow: function(aliasData) {
		var tmp = {};
		tmp.me = this;
		tmp.editRow = new Element('div', {'class': 'input-group input-group-sm pmc_row'})
			.insert({'bottom': new Element('span', {'class': 'input-group-btn', 'title': 'Save'})
				.insert({'bottom': new Element('span', {'id': 'save_btn_' + aliasData.id, 'class': 'btn btn-success', 'data-loading-text': 'saving...'})
					.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-floppy-saved'}) })
				})
				.observe('click', function() {
					tmp.btn = this;
					tmp.aliasRow = $(tmp.btn).up('.pmc_row');
					tmp.newAlias = $F(tmp.aliasRow.down('.aliasValue'));
					if(tmp.newAlias.blank()) {
						tmp.aliasRow.addClassName('has-error');
						tmp.aliasRow.insert({'after': tmp.me.getAlertBox('Error:', 'Alias is required').addClassName('alert-danger') });
						return;
					}
					
					tmp.data = {'id': (aliasData.id || ''), 'newAliasValue': tmp.newAlias, 'companyName': (aliasData.companyName || $F(tmp.aliasRow.up('.company_row').down('.company_name')) ) };
					tmp.me.postAjax(tmp.me.getCallbackId('updatePriceMatchCompany'), tmp.data, {
						'onCreate': function (sender, param) {
							jQuery(tmp.btn.id).button('loading');
						}
						,'onSuccess': function (sender, param) {
							try {
								tmp.result = tmp.me.getResp(param, false, true);
								tmp.companyNameBox = tmp.aliasRow.up('.company_row').down('.company_name');
								if(tmp.companyNameBox && !tmp.companyNameBox.hasClassName('noborder')) {
									tmp.companyNameBox.writeAttribute('readOnly', true).addClassName('noborder');
								}
								tmp.aliasRow.replace(tmp.me._getAliasRow(tmp.result.item));
							} catch(e) {
								tmp.aliasRow.insert({'after': tmp.me.getAlertBox('Error:', e).addClassName('alert-danger') });
							}
						}
						,'onComplete': function (sender, param) {
							jQuery(tmp.btn.id).button('reset');
						}
					});
				})
			})
			.insert({'bottom': new Element('input', {'type': 'text', 'class': 'form-control aliasValue', 'value': (aliasData.companyAlias || '')}) })
			.insert({'bottom': new Element('span', {'class': 'input-group-btn', 'title': 'Cancel'})
				.insert({'bottom': new Element('span', {'class': 'btn btn-warning'})
					.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-floppy-remove'}) })
				})
				.observe('click', function() {
					if(aliasData.id) {
						$(this).up('.pmc_row').replace(tmp.me._getAliasRow(aliasData));
					} else {
						$(this).up('.pmc_row').remove();
					}
				})
			});
		return tmp.editRow;
	}
	/**
	 * Getting each alias row for the company row
	 */
	,_getAliasRow: function(alias) {
		var tmp = {};
		tmp.me = this;
		tmp.id = alias.id;
		tmp.alias = alias.companyAlias;
		if(tmp.alias && tmp.alias !== '' && tmp.alias !== null && tmp.alias !== undefined) {
			return new Element('div', {'class': 'input-group input-group-sm pmc_row'})
				.store('data', alias)
				.insert({'bottom': new Element('span', {'class': 'input-group-btn', 'title': 'Edit'}) 
					.insert({'bottom': new Element('span', {'class': 'btn btn-default'}).update(new Element('span', {'class': 'glyphicon glyphicon-pencil'})) })
					.observe('click', function() {
						tmp.editBtn = this;
						tmp.originalRow = $(tmp.editBtn).up('.pmc_row');
						tmp.editRow = tmp.me._getEditAliasRow(tmp.originalRow.retrieve('data') );
						tmp.originalRow.replace(tmp.editRow);
						tmp.editRow.down('.aliasValue').select();
					})
				})
				.insert({'bottom': new Element('div', {'class': 'form-control'}).update( tmp.alias ) })
				.insert({'bottom': new Element('span', {'class': 'input-group-btn', 'title': 'Delete'})
					.insert({'bottom': new Element('span', {'class': 'btn btn-danger'}).update(new Element('span', {'class': 'glyphicon glyphicon-trash'})) })
					.observe('click', function() {
						tmp.confirmation = confirm('Are you sure you want to delete this alias ?');
						if(tmp.confirmation === false)
							return;
						
						tmp.aliasRow = $(this).up('.pmc_row');
						tmp.me.postAjax(tmp.me.getCallbackId('deleteAliasForPriceMatchCompany'), {'data': alias}, {
							'onCreate': function (sender, param) {}
							,'onSuccess': function (sender, param) {
								try {
									tmp.result = tmp.me.getResp(param, false, true);
									tmp.companyRow = tmp.aliasRow.up('.company_row');
									tmp.aliasRow.remove();
									if(tmp.companyRow.getElementsBySelector('.pmc_row').size() === 0)
										tmp.companyRow.remove();
									
								} catch(e) {
									alert(e);
								}
							}
						});
					})
				})
				;
		}
	}
	/**
	 * Getting the company row
	 */
	,_getCompanyRow: function(item) {
		var tmp = {};
		tmp.me = this;
		tmp.aliasCol = new Element('div', {'class': 'col-xs-8 company_alias'});
		item.companyAliases.each(function(alias) {
			tmp.aliasCol.insert({'bottom': tmp.me._getAliasRow(alias) })
		});
		tmp.aliasCol.insert({'bottom': new Element('botton', {'class': 'btn btn-success btn-xs'})
			.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-plus-sign'}) }) 
			.observe('click', function() {
				$(this).insert({'before': tmp.me._getEditAliasRow({}) });
			})
		})
		tmp.companyName = new Element('input', {'class': 'company_name', 'type': 'text', 'value': item.companyName});
		if(!item.companyName.blank())
		{
			tmp.companyName.writeAttribute('readOnly', true)
			.addClassName('noborder');
		}
		return new Element('div', {'class': 'row list-group-item company_row'})
			.store('allCompanyAliasData', item)
			.insert({'bottom': new Element('div', {'class': 'col-xs-4'}).update( tmp.companyName ) })
			.insert({'bottom': tmp.aliasCol });
	}
	/**
	 * Displaying all the company & aliases
	 */
	,displayAllPriceMatchCompany: function() {
		var tmp = {};
		tmp.me = this;
		tmp.me.postAjax(tmp.me.getCallbackId('getPriceMatchCompany'), {'searchCriteria': tmp.me._searchCriteria, 'pagination': tmp.me._pageInfo}, {
			'onCreate': function (sender, param) {}
			,'onSuccess': function (sender, param) {
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					tmp.result.items.each(function(item) {
						$(tmp.me._resultDivId).insert({'bottom': tmp.me._getCompanyRow(item) });
					});
				} catch(e) {
					alert(e);
				}
			}
		});
		return tmp.me;
	}
	,newCompany: function() {
		var tmp = {};
		tmp.me = this;
		$(tmp.me._resultDivId).insert({'bottom': tmp.me._getCompanyRow({'companyName': '', 'companyAliases': []}) })
		return this;
	}
});