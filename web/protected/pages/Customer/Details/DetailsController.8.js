/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new DetailsPageJs(), {
	_customers: []
	,_suppliers: []
	,_statuses: []
	,_priceTypes: []                         //pre defined data: productCodeType
	,_codeTypes: []                          //pre defined data: productCodeType
	,_productTreeId: 'product_category_tree' //the html id of the tree
	,_imgPanelId: 'images_panel'             //the html id of the iamges panel
	/**
	 * Getting a form group for forms
	 */
	,_getFormGroup: function (label, input) {
		return new Element('div', {'class': 'form-group form-group-sm form-group-sm-label'})
			.insert({'bottom': new Element('label').update(label) })
			.insert({'bottom': input.addClassName('form-control') });
	}
	/**
	 * Set some pre defined data before javascript start
	 */
	,setPreData: function(manufacturers, suppliers, statuses, priceTypes, codeTypes) {
		this._customers = customers;
		this._suppliers = suppliers;
		this._statuses = statuses;
		this._priceTypes = priceTypes;
		this._codeTypes = codeTypes;
		return this;
	}
	/**
	 * General getting a selection box
	 */
	,_getSelBox: function(options, selectedValue) {
		var tmp = {};
		tmp.me = this;
		tmp.selBox = new Element('select');
		options.each(function(opt){
			tmp.selBox.insert({'bottom': new Element('option', {'value': opt.id, 'selected' : (selectedValue && opt.id === selectedValue ? true : false)}).update(opt.name) })
		});
		return tmp.selBox;
	}

	/**
	 * General listing panel
	 */
	,_getListPanel: function(title, listData, titleData, selBoxData, selBoxChangeFunc) {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = new Element('div', {'class': 'panel panel-default'})
			.insert({'bottom': new Element('div', {'class': 'panel-heading'}) 
				.insert({'bottom': new Element('a', {'class': 'toggle-btn', 'href': 'javascript: void(0);', 'title': 'click show/hide content below'})
					.insert({'bottom': new Element('strong').update(title)})
					.observe('click', function() {
						$(this).up('.panel').down('.list-div').toggle();
					})
				})
				.insert({'bottom': new Element('span', {'class': 'btn btn-primary btn-xs pull-right', 'title': 'New'})
					.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-plus'}) })
					.insert({'bottom': ' NEW' })
					.observe('click', function(){
						tmp.parentPanel = $(this).up('.panel');
						tmp.parentPanel.down('.table tbody').insert({'bottom': tmp.me._getListPanelRow({}, selBoxData, titleData, false, selBoxChangeFunc).addClassName('list-panel-row').writeAttribute('item_id', '') });
						tmp.parentPanel.down('.list-div').show();
						tmp.me._bindDatePicker();
					})
				})
			})
			.insert({'bottom': new Element('div', {'class': 'list-div table-responsive'}) 
				.insert({'bottom': new Element('table', {'class': 'table table-condensed'}) 
					.insert({'bottom': new Element('thead').update( tmp.me._getListPanelRow(titleData, selBoxData, titleData, true, selBoxChangeFunc) ) })
					.insert({'bottom': tmp.listDiv = new Element('tbody') })
				})
			});
		if(listData) {
			listData.each(function(data){
				tmp.listDiv.insert({'bottom': tmp.me._getListPanelRow(data, selBoxData, titleData, false, selBoxChangeFunc).addClassName('list-panel-row').writeAttribute('item_id', data.id) });
			});
		}
		return tmp.newDiv;
	}

	/**
	 * Public: binding all the js events
	 */
	,bindAllEventNObjects: function() {
		var tmp = {};
		tmp.me = this;
		tmp.me._bindDatePicker();
		$$('textarea.rich-text-editor').each(function(item){
			tmp.me._loadRichTextEditor(item);
		});
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