/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new BPCPageJs(), {
	_order: null //the order object
	,_resultDivId: '' //the result div id
	
	,_getAddressDiv: function(title, addr) {
		return new Element('div', {'class': 'addr'})
			.insert({'bottom': new Element('div', {'class': 'title'}).update(title) })
			.insert({'bottom': new Element('div', {'class': 'addr_content'})
				.insert({'bottom': new Element('div', {'class': 'contactName'}).update(addr.contactName) })
				.insert({'bottom': new Element('div', {'class': 'street'}).update(addr.street) })
				.insert({'bottom': new Element('div')
					.insert({'bottom': new Element('span', {'class': 'city inlineblock'}).update(addr.city) })
					.insert({'bottom': new Element('span', {'class': 'region inlineblock'}).update(addr.region) })
					.insert({'bottom': new Element('span', {'class': 'postcode inlineblock'}).update(addr.postCode) })
				})
			})
	}

	,_getfieldDiv: function(title, content) {
		return new Element('span', {'class': 'fieldDiv'})
			.insert({'bottom': new Element('span', {'class': 'fieldDiv_title'}).update(title) })
			.insert({'bottom': new Element('span', {'class': 'fieldDiv_content'}).update(content) });
	}
	
	,_getProducts: function(productListDivId) {
		var tmp = {};
		tmp.me = this;
		tmp.me.postAjax(tmp.me.getCallbackId('getProducts'), {'orderId': tmp.me._order.id}, {
			'onLoading': function(sender, param){
				$(productListDivId).update(new Element('img', {'src': '/themes/default/images/loading_big.gif'}));
			}
			,'onComplete': function(sender, param) {
				try {
					tmp.result = tmp.me.getResp(param, true, false);
					console.debug(tmp.result);
					
				} catch (e) {
					$(productListDivId).update(e);
				}
			}
		})
		return this;
	}
	
	,load: function(resultdiv, order) {
		var tmp = {};
		tmp.me = this;
		tmp.me._resultDivId = resultdiv;
		tmp.me._order = order;
		tmp.newDiv = new Element('div');
		
		//getting the order info row
		tmp.newDiv.insert({'bottom': new Element('fieldset', {'class': 'row orderInfo'})
			.insert({'bottom': new Element('legend').update('info') })
			.insert({'bottom': new Element('span', {'class': 'orderNo inlineblock'}).update(tmp.me._getfieldDiv('Order No.', tmp.me._order.orderNo)) })
			.insert({'bottom': new Element('span', {'class': 'orderDate inlineblock'}).update(tmp.me._getfieldDiv('Order Date:', tmp.me._order.orderDate)) })
			.insert({'bottom': new Element('span', {'class': 'orderStatus inlineblock'}).update(tmp.me._getfieldDiv('Order Status:', tmp.me._order.status.name)) })
		});
		
		//getting the address row
		tmp.newDiv.insert({'bottom': new Element('fieldset', {'class': 'row addressRow'})
			.insert({'bottom': new Element('legend').update('Customer') })
			.insert({'bottom': new Element('div', {'class': 'customer'})
				.insert({'bottom': new Element('div').update('Customer: ') })
				.insert({'bottom': new Element('span', {'class': 'custName inlineblock'}).update(tmp.me._getfieldDiv('', tmp.me._order.infos[1][0].value)) })
				.insert({'bottom': new Element('span', {'class': 'custEmail inlineblock'}).update(tmp.me._getfieldDiv('', tmp.me._order.infos[2][0].value)) })
			})
			.insert({'bottom': new Element('div')
				.insert({'bottom': tmp.me._getAddressDiv("Shipping Address: ", tmp.me._order.address.shipping).addClassName('inlineblock') })
				.insert({'bottom': tmp.me._getAddressDiv("Billing Address: ", tmp.me._order.address.billing).addClassName('inlineblock') })
			 })
		});
		
		//getting the parts row
		tmp.newDiv.insert({'bottom': new Element('fieldset', {'class': 'row productsRow'})
			.insert({'bottom': new Element('legend').update('Products') })
			.insert({'bottom': new Element('div', {'id': 'productlist'}) })
		});
		
		$(tmp.me._resultDivId).update(tmp.newDiv);
		tmp.me._getProducts('productlist');
		return this;
	}
});
