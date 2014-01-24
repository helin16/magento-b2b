/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new BPCPageJs(), {
	order: null //the order object
	,opDiv: '' //the operations div
	,_getRow: function(title, content) {
		return new Element('div', {'class': 'row'})
		.insert({"bottom": new Element('span', {'class': 'title inlineblock'}).update(title) })
		.insert({"bottom": new Element('span', {'class': 'rowContent inlineblock'}).update(content) });
	}
	,_toggleOpBox: function(item) {
		if(item.hasClassName('collapsed')) {
			item.up('.operation_box').down('.operation_content').show();
			item.removeClassName('collapsed');
		} else {
			item.up('.operation_box').down('.operation_content').hide();
			item.addClassName('collapsed');
		}
		return this;
	}
	,_getOperationBox: function(title, content) {
		var tmp = {};
		tmp.me = this;
		return new Element('div', {'class': 'operation_box'})
			.insert({'bottom': new Element('div', {'class': 'operation_title expandable'})
				.update(title)
				.observe('click', function() {
					tmp.me._toggleOpBox(this);
				})
				.insert({'bottom': new Element('span', {'class': 'icon inlineblock'}) })
			})
			.insert({'bottom': new Element('div', {'class': 'operation_content'}).update(content) });
	}
	,_getDetailsDiv: function() {
		var tmp = {};
		tmp.me = this;
		tmp.div = new Element('div', {'class': 'detailsWrapper'})
			.insert({"bottom": tmp.me._getRow('Status', new Element('div', {'class': 'order_status', 'order_details': 'status'}).update(tmp.me.order.status.name) ) })
		;
		return tmp.div;
	}
	,loadOperations: function() {
		var tmp = {};
		tmp.me = this;
		
		$(tmp.me.opDiv).insert({'bottom': new Element('div', {'class': 'btns'})
			.insert({'bottom': new Element('span', {'class': 'inlineblock Actions'}).update('Actions') })	
		})
		//load order details div
			.insert({'bottom': tmp.me._getOperationBox('OrderDetails', tmp.me._getDetailsDiv()) });
	}
	
});