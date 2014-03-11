/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new BPCPageJs(), {
	_resultDivId: '' // the id of the result div
	,_pageInfo: {'pageNo': 1, 'pageSize': 30}
	,_searchCriteria: ''
	
	,_generateEditOptions: function(editSpan) {
		var tmp = {};
		tmp.me = this;
		
		tmp.parentRow = $(editSpan).up('.pmc_row');
		tmp.pmcId = tmp.parentRow.retrieve('id');
		tmp.aliasTextSpan = tmp.parentRow.getElementsBySelector('.single_alias')[0];
		tmp.aliasText = tmp.aliasTextSpan.innerHTML;
		tmp.aliasTextSpan.update(new Element('input', {'type': 'text', 'class': 'alias_text_box'}).writeAttribute('value', tmp.aliasText) );
		$(editSpan).up('.single_alias_option').update('')	
			.insert({'bottom': new Element('span', {}).update('Save') })
			.insert({'bottom': new Element('span', {}).update('Cancel') 
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
					.insert({'bottom': new Element('span') 
						.update('Edit')
						.observe('click', function() {
								tmp.me._generateEditOptions(this);
							})
					})
				});
		}
	}
		
	,_generateSingleRowForPriceMatchCompany: function(item) {
		var tmp = {};
		tmp.me = this;
		tmp.item = item;
		
		tmp.rowDiv = new Element('div', {'class': 'row'});
		tmp.aliasSpan = new Element('span', {'class': 'cell company_alias'});
		
		tmp.companyName = tmp.item.companyName;
		tmp.item.companyAliases.each(function(alias) {
			tmp.aliasSpan.insert({'bottom': tmp.me._getAliasRow(alias) })
		});
		
		tmp.rowDiv
			.insert({'bottom': new Element('span', {'class': 'cell company_name'}).update(tmp.companyName) })
			.insert({'bottom': tmp.aliasSpan });
		
		return tmp.rowDiv;
	}	
		
	,displayAllPriceMatchCompany: function() {
		var tmp = {};
		tmp.me = this;
		
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
	
});