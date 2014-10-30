/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new DetailsPageJs(), {
	_customer: {}
	/**
	 * Set some pre defined data before javascript start
	 */
	,setPreData: function(customer) {
		this._customer = customer;
		return this;
	}
	/**
	 * displaying the item
	 */
	,_getItemDiv: function() {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv2 = new Element('div')
			.insert({'bottom': new Element('div', {'class': 'row'}).update('hello') })
		;
		tmp.newDiv = new Element('div')
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('div', {'class': 'col-sm-12'})
					.insert({'bottom': new Element('div', {'class': 'row'})
						.insert({'bottom': tmp.newDiv2.wrap(new Element('div', {'class': 'col-sm-12'})) })
					})
					.insert({'bottom': new Element('div', {'class': 'row'})
						.insert({'bottom': new Element('span', {'class': 'btn btn-primary pull-right col-sm-4', 'data-loading-text': 'saving ...'}).update('Save')
							.observe('click', function() {
								tmp.me._submitSave(this);
							})
						})
					})
				})
			});
		return tmp.newDiv;
	}
	/**
	 * Public: binding all the js events
	 */
	,bindAllEventNObjects: function() {
		var tmp = {};
		tmp.me = this;
//		tmp.me._bindDatePicker();
//		$$('textarea.rich-text-editor').each(function(item){
//			tmp.me._loadRichTextEditor(item);
//		});
		return tmp.me;
	}
	
	,refreshParentWindow: function() {
		var tmp = {};
		tmp.me = this;
		if(!window.opener)
			return;
		tmp.parentWindow = window.opener;
		tmp.row = $(tmp.parentWindow.document.body).down('#' + tmp.parentWindow.pageJs.resultDivId + ' .product_item[product_id=' + tmp.me._item.id + ']');
		if(tmp.row) {
			tmp.row.replace(tmp.parentWindow.pageJs._getResultRow(tmp.me._item));
			if(tmp.row.hasClassName('success'))
				tmp.row.addClassName('success');
		}
	}
});