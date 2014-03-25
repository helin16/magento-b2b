/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new BPCPageJs(), {
	_resultDivId: '' // the id of the result div
	,_addCompanyDivId: ''	
	,_pageInfo: {'pageNo': 1, 'pageSize': 30}
	,_searchCriteria: ''
	
	,_getFieldDiv: function(title, content) {
		return new Element('span', {'class': 'fieldDiv'})
			.insert({'bottom': new Element('span', {'class': 'fieldDiv_title'}).update(title) })
			.insert({'bottom': new Element('span', {'class': 'fieldDiv_content'}).update(content) });
	}
	
	,_generateEditOptions: function(editSpan) {
		var tmp = {};
		tmp.me = this;
		
		tmp.parentRow = $(editSpan).up('.pmc_row');
		tmp.aliasTextSpan = tmp.parentRow.getElementsBySelector('.single_alias')[0];
		tmp.aliasText = tmp.aliasTextSpan.innerHTML;
		tmp.aliasTextSpan.update(new Element('input', {'type': 'text', 'class': 'alias_text_box'}).writeAttribute('value', tmp.aliasText) );
		$(editSpan).up('.single_alias_option').update('')	
			.insert({'bottom': new Element('span', {'class': 'button'}).update('Save')
				.observe('click', function() {
					tmp.newAliasValue = $F($(this).up('.pmc_row').getElementsBySelector('.single_alias .alias_text_box')[0]);
					tmp.newAliasValue = tmp.newAliasValue.strip();
					if(tmp.newAliasValue === '' || tmp.newAliasValue === null || tmp.newAliasValue === undefined || !tmp.newAliasValue)
					{
						alert('Empty Alias value NOT Accepted');
						return;
					}
					
					tmp.aliasObj = $(this).up('.pmc_row').retrieve('data');
					if(tmp.aliasObj.alias.strip() === tmp.newAliasValue)
					{
						$(this).up('.pmc_row').replace(tmp.me._getAliasRow(tmp.aliasObj));
						return;
					}	
					
					tmp.me.postAjax(tmp.me.getCallbackId('updatePriceMatchCompany'), {'id': tmp.aliasObj.id, 'newAliasValue': tmp.newAliasValue}, {
						'onLoading': function (sender, param) {
							//$(tmp.me.searchBtnId).store('orignValue', $F(tmp.me.searchBtnId)).addClassName('disabled').setValue('searching ...').disabled = true;
						}
						, 'onComplete': function (sender, param) {
							try 
							{
								tmp.result = tmp.me.getResp(param, false, true);
								window.location = document.URL;
							} 
							catch(e) 
							{
								alert(e);
							}
						}
					});
				})
			})
			.insert({'bottom': new Element('span', {'class': 'button'}).update('Cancel') 
				.observe('click', function() {
					tmp.aliasObj = $(this).up('.pmc_row').retrieve('data');
					$(this).up('.pmc_row').replace(tmp.me._getAliasRow(tmp.aliasObj));
				})
			});
	}
	
	,_getAliasRow: function(alias) {
		var tmp = {};
		tmp.me = this;
		tmp.id = alias.id;
		tmp.alias = alias.alias;
		if(alias && tmp.alias !== '' && tmp.alias !== null && tmp.alias !== undefined) {
			return new Element('div', {'class': 'pmc_row'}).store('data', alias)
				.insert({'bottom': new Element('span', {'class': 'single_alias'}).update(tmp.alias) })
				.insert({'bottom': new Element('span', {'class': 'single_alias_option'})
					.insert({'bottom': new Element('span', {'class': 'button'}) 
						.update('Edit')
						.observe('click', function() {
								tmp.me._generateEditOptions(this);
							})
					})
					.insert({'bottom': new Element('span', {'class': 'button'}).update('Delete') 
						.observe('click', function() {
							tmp.confirmation = confirm('Are you sure you want to delete this alias ?');
							if(tmp.confirmation === false)
								return;
							tmp.originalData = $(this).up('.pmc_row').retrieve('data');
							
							tmp.me.postAjax(tmp.me.getCallbackId('deleteAliasForPriceMatchCompany'), {'data': tmp.originalData}, {
								'onLoading': function (sender, param) {
									//$(tmp.me.searchBtnId).store('orignValue', $F(tmp.me.searchBtnId)).addClassName('disabled').setValue('searching ...').disabled = true;
								}
								, 'onComplete': function (sender, param) {
									try 
									{
										tmp.result = tmp.me.getResp(param, false, true);
										window.location = document.URL;
									} 
									catch(e) 
									{
										console.debug(e);
										//alert(e);
									}
								}
							});
						})
					})
				});
		}
	}
	
	,_showHideAddAliasButton: function(parentDiv) {
		var tmp = {};
		tmp.me = this;
		
		tmp.parentDiv = parentDiv;
		tmp.parentDiv.getElementsBySelector('.addNewAliasBtn').each(function(item) {
			item.remove();	
		});
		
		if(tmp.parentDiv.getElementsBySelector('[newAliasBox]').size() > 0)
		{
			tmp.parentDiv.insert({'bottom': new Element('span', {'class': 'button addNewAliasBtn'}).update('Add Alias(s)')
				.observe('click', function() {
					tmp.newAliasValueArray = [];
					tmp.pDiv = $(this).up('.companyNameDiv');
					tmp.companyName = tmp.pDiv.getElementsBySelector('.companyNameSpan')[0].innerHTML;
					tmp.pDiv.getElementsBySelector('[newAliasBox]').each(function(aliasBox) {
						if((tmp.newAliasValue = $F(aliasBox).strip()) !== '' && tmp.newAliasValue !== null && tmp.newAliasValue !== undefined)
							tmp.newAliasValueArray.push(tmp.newAliasValue);
					});
					if(tmp.newAliasValueArray.size() === 0)
					{
						tmp.allCompanyData = tmp.pDiv.up('.row').retrieve('allCompanyAliasData');
						tmp.pDiv.up('.row').replace(tmp.me._generateSingleRowForPriceMatchCompany(tmp.allCompanyData));
						alert('No New Aliase(s) added!');
						return;
					}
					else
					{
						tmp.me.postAjax(tmp.me.getCallbackId('addAliasForPriceMatchCompany'), {'aliasArray': tmp.newAliasValueArray, 'companyName': tmp.companyName}, {
							'onLoading': function (sender, param) {
								//$(tmp.me.searchBtnId).store('orignValue', $F(tmp.me.searchBtnId)).addClassName('disabled').setValue('searching ...').disabled = true;
							}
							, 'onComplete': function (sender, param) {
								try 
								{
									tmp.result = tmp.me.getResp(param, false, true);
									window.location = document.URL;
								} 
								catch(e) 
								{
									console.debug(e);
									//alert(e);
								}
								
							}
						});
					}	
				})
			});
		}	
		
		return tmp.me;
	}
	
	,_addNewAliasBox: function(button) {
		var tmp = {};
		tmp.me = this;
		
		tmp.parentDiv = $(button).up('.companyNameDiv');
		
		tmp.parentDiv
			.insert({'bottom': new Element('div', {'class': 'new_single_alias_box'})
				.insert({'bottom': new Element('span', {}) 
					.insert({'bottom': new Element('input', {'type': 'text', 'newAliasBox': 'newAliasBox'}) })
				})
				.insert({'bottom': new Element('span', {'class': 'button'}).update('-')
					.observe('click', function() {
						$(this).up('.new_single_alias_box').remove();
						tmp.me._showHideAddAliasButton(tmp.parentDiv);
					})
				})
			})	
			.insert({'bottom': new Element('div', {'class': 'button'}).update('+')
				.observe('click', function() {
					tmp.me._addNewAliasBox(this);
				})
			});
		
		tmp.me._showHideAddAliasButton(tmp.parentDiv);
		$(button).remove();
	}
		
	,_generateSingleRowForPriceMatchCompany: function(item) {
		var tmp = {};
		tmp.me = this;
		tmp.item = item;
		
		tmp.rowDiv = new Element('div', {'class': 'row'});
		tmp.aliasSpan = new Element('span', {'class': 'cell company_alias'});
		
		tmp.companyName = tmp.item.companyName;
		tmp.companyNameDiv = new Element('div', {'class': 'companyNameDiv'});
		tmp.companyNameDiv
			.insert({'bottom': new Element('div', {'class': 'companyNameSpan'}).update(tmp.companyName) })
			.insert({'bottom': new Element('div', {'class': 'button'}).update('+') 
				.observe('click', function() {
					tmp.me._addNewAliasBox(this);
				})
			});
		
		tmp.item.companyAliases.each(function(alias) {
			tmp.aliasSpan.insert({'bottom': tmp.me._getAliasRow(alias) })
		});
		
		tmp.rowDiv
			.store('allCompanyAliasData', item)
			.insert({'bottom': new Element('span', {'class': 'cell company_name'}).update(tmp.companyNameDiv) })
			.insert({'bottom': tmp.aliasSpan });
		
		return tmp.rowDiv;
	}
	
	,displayAllPriceMatchCompany: function() {
		var tmp = {};
		tmp.me = this;
		
		tmp.me.displayAddCompanyPanel();
		
		tmp.me.postAjax(tmp.me.getCallbackId('getPriceMatchCompany'), {'searchCriteria': tmp.me._searchCriteria, 'pagination': tmp.me._pageInfo}, {
			'onLoading': function (sender, param) {
				//$(tmp.me.searchBtnId).store('orignValue', $F(tmp.me.searchBtnId)).addClassName('disabled').setValue('searching ...').disabled = true;
			}
			, 'onComplete': function (sender, param) {
				try 
				{
					tmp.result = tmp.me.getResp(param, false, true);
					tmp.result.items.each(function(item) {
						tmp.row = tmp.me._generateSingleRowForPriceMatchCompany(item);
						$(tmp.me._resultDivId).insert({'bottom': tmp.row });
					});
				} 
				catch(e) 
				{
					console.debug(e);
					//alert(e);
				}
				
			}
		});
		
		return tmp.me;
	}
	
	,displayAddCompanyPanel: function() {
		var tmp = {};
		tmp.me = this;
		
		$(tmp.me._addCompanyDivId).update('');
		
		$(tmp.me._addCompanyDivId).insert({'bottom': new Element('span', {'class': 'add_new_company'}) 
			.insert({'bottom': new Element('span', {'class': 'button'}).update('Add New Company') 
				.observe('click', function() {
					tmp.newDiv = new Element('div', {})
									.insert({'bottom': new Element('span', {}).update(tmp.me._getFieldDiv('Company Name: ', new Element('input', {'type': 'text', 'class': 'new_company_name_box'}))) })
									.insert({'bottom': new Element('span', {})
										
									})
									.insert({'bottom': new Element('span', {'class': 'button'}).update('Cancel') 
										.observe('click', function() {
											tmp.me.displayAddCompanyPanel();
										})
									});
					
					$(this).up('.add_new_company').update(tmp.newDiv);
				})
			})
		});
		
	}
	
	
});