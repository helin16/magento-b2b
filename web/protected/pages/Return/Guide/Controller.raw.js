/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new BPCPageJs(), {
	divWrapper: ''
	,_getPageTitle: function(subTitle) {
		return new Element('div', {'class': 'page-header'})
			.insert({'bottom': new Element('h3').update('Wizard of Returns: ')
				.insert({'bottom': new Element('small').update(subTitle) })
			});
	}
	,_showWelcomePanel: function() {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = new Element('div', {'class': 'jumbotron'})
			.insert({'bottom': new Element('h2').update('Welcome to Return Wizard:') })
			.insert({'bottom': new Element('span').update('With this wizard, you just need to follow the prompts, and the system will try to guide you to do:')
				.insert({'bottom': new Element('ul')
					.insert({'bottom': new Element('li').update('reverse an order') })
					.insert({'bottom': new Element('li').update('reverse an payment') })
					.insert({'bottom': new Element('li').update('return a part') })
					.insert({'bottom': new Element('li').update('issue a RA') })
				})
			})
			.insert({'bottom': new Element('div', {'class': 'text-right'})
				.insert({'bottom': new Element('span', {'class': 'btn btn-primary'}).update('Start NOW') })
				.observe('click', function() {
					tmp.me._showOrderSearchPanel();
				})
			})
			;
		$(tmp.me.divWrapper).update(tmp.newDiv);
		return tmp.me;
	}
	,_getSearchOrderResultRow: function(order) {
		var tmp = {};
		tmp.me = this;
		tmp.isTitle = order.id;
		tmp.tag = order.id ? 'td' : 'th';
		tmp.newRow = new Element('tr')
			.store('data', order)
			.insert({'bottom': new Element(tmp.tag).update(order.orderNo) })
			.insert({'bottom': new Element(tmp.tag).update(order.status.name) })
		;
		return tmp.newRow;
	}
	,_searchOrders: function(btn, pageNo) {
		var tmp = {};
		tmp.me = this;
		tmp.pageNo = (pageNo || 1);
		tmp.searchPanel =$(btn).up('.order-search-panel');
		tmp.resultPanel = tmp.searchPanel.down('.order-list');
		tmp.me._signRandID(btn);
		tmp.loadingImage = tmp.me.getLoadingImg();
		tmp.data = {'pagination': {'pageNo': tmp.pageNo, 'pageSize': 30}, 'searchCriteria': tmp.me._collectFormData(tmp.searchPanel, 'search-order')};
		tmp.me.postAjax(tmp.me.getCallbackId('searchOrders'), tmp.data, {
			'onCreate': function() {
				jQuery('#' + btn.id).button('loading');
				if(tmp.pageNo === 1) {
					tmp.resultPanel.update(tmp.loadingImage);
				}
			}
			,'onSuccess': function(sender, param) {
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result || !tmp.result.items)
						return;
					if(tmp.pageNo === 1) {
						tmp.resultPanel.update(tmp.me._getSearchOrderResultRow({}).wrap(new Element('thead')));
					} else {
						$(btn).remove();
					}
					tmp.tbody = tmp.resultPanel.down('tbody');
					if(!tmp.tbody)
						tmp.resultPanel.insert({'bottom': tmp.tbody = new Element('tbody')});
					tmp.result.items.each(function(item) {
						tmp.tbody.insert({'bottom': tmp.me._getSearchOrderResultRow(item) });
					});
					if(tmp.result.pageStats.pageNumber < tmp.result.pageStats.totalPages) {
						tmp.resultPanel.insert({'after': new Element('span', {'class': 'btn btn-primary', 'data-loading-text': 'Getting More ...'})
							.update('Show Me More')
							.observe('click', function() {
								tmp.me._searchOrders(this, tmp.pageNo * 1 + 1);
							})
						});
					}
				} catch (e) {
					if(tmp.pageNo === 1) {
						tmp.resultPanel.update(tmp.me.getAlertBox('ERR: ', e).addClassName('alert-danger'));
					} else {
						tmp.me.showModalBox('<strong class="text-danger">ERR</strong>', e);
					}
				}
			}
			,'onComplete': function() {
				jQuery('#' + btn.id).button('reset');
				tmp.loadingImage.remove();
			}
		})
		return tmp.me;
	}
	,_showOrderSearchPanel: function() {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = new Element('div')
			.insert({'bottom': tmp.me._getPageTitle('Search for an order') })
			.insert({'bottom': new Element('div' , {'class': 'panel panel-success order-search-panel'})
				.insert({'bottom':  new Element('div' , {'class': 'panel-heading'})
					.insert({'bottom':  new Element('strong').update('We need to find an order/invoice to be able to start the return:') })
				})
				.insert({'bottom': new Element('div' , {'class': 'panel-body'})
					.insert({'bottom': new Element('div', {'class': 'form-inline'})
						.insert({'bottom': new Element('div', {'class': 'form-group'})
							.insert({'bottom': new Element('input', {'class': 'form-control', 'placeholder': 'Order No.:', 'search-order': 'ord.orderNo'}) })
						})
						.insert({'bottom': new Element('div', {'class': 'form-group'})
							.insert({'bottom': new Element('input', {'class': 'form-control', 'placeholder': 'Invoice No.:', 'search-order': 'ord.invNo'}) })
						})
						.insert({'bottom': new Element('div', {'class': 'form-group'})
							.insert({'bottom': new Element('input', {'class': 'form-control', 'placeholder': 'Customer:', 'search-order': 'customer'}) })
						})
						.insert({'bottom': new Element('span', {'class': 'btn btn-primary', 'data-loading-text': 'searching ...'})
							.update('Search Orders')
							.observe('click', function() {
								tmp.me._searchOrders(this, 1);
							})
						})
					})
					.insert({'bottom': new Element('div', {'class': 'table order-list'}) })
				})
			});
		$(tmp.me.divWrapper).update(tmp.newDiv);
		return tmp.me;
	}
	,init: function(divWrapper) {
		var tmp = {};
		tmp.me = this;
		tmp.me.divWrapper = divWrapper;
		tmp.me._showWelcomePanel();
		return tmp.me;
	}
});