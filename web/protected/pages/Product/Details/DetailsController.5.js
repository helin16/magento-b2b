/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new DetailsPageJs(), {
	_manufacturers: []
	,_suppliers: []
	,_statuses: []
	,_priceTypes: []
	,_codeTypes: []
	,_productCategories: []
	
	,_getFormGroup: function (label, input) {
		return new Element('div', {'class': 'form-group form-group-sm form-group-sm-label'})
			.insert({'bottom': new Element('label').update(label) })
			.insert({'bottom': input.addClassName('form-control') });
	}
	,setPreData: function(manufacturers, suppliers, statuses, priceTypes, codeTypes, productCategories) {
		this._manufacturers = manufacturers;
		this._suppliers = suppliers;
		this._statuses = statuses;
		this._priceTypes = priceTypes;
		this._codeTypes = codeTypes;
		this._productCategories = productCategories;
		return this;
	}
	
	,_getSelBox: function(options, selectedValue) {
		var tmp = {};
		tmp.me = this;
		tmp.selBox = new Element('select');
		options.each(function(opt){
			tmp.selBox.insert({'bottom': new Element('option', {'value': opt.id, 'selected' : (selectedValue && opt.id === selectedValue ? true : false)}).update(opt.name) })
		});
		return tmp.selBox;
	}
	
	,_getListPanelRow: function(data, selBoxData, titleData, isTitle) {
		var tmp = {};
		tmp.me = this;
		tmp.isTitle = (isTitle || false);
		tmp.tag = (tmp.isTitle === true ? 'th' : 'td');
		tmp.typeString = titleData.type.toLowerCase();
		tmp.valueString = titleData.value.toLowerCase();
		
		tmp.inputBoxDiv = new Element('div', {'class': 'input-group input-group-sm'})
			.insert({'bottom': new Element('input', {'type': 'text', 'class': 'form-control', 'list-panel-row': 'value', 'required': true, 'value': (data[tmp.valueString] ? data[tmp.valueString]: '') }) })
			.insert({'bottom': new Element('span', {'class': 'btn btn-danger input-group-addon'})
				.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-trash'}) }) 
				.observe('click', function() {
					$(this).up('.list-panel-row').remove();
				})
			});
		tmp.newRow = new Element('tr')
			.insert({'bottom': new Element(tmp.tag).update(tmp.isTitle === true ? titleData.type : tmp.me._getSelBox(selBoxData, (data[tmp.typeString] && data[tmp.typeString].id ? data[tmp.typeString].id : '')).addClassName('form-control input-sm').writeAttribute('list-panel-row', 'typeId').writeAttribute('required', true) )});
		if(titleData.start){
			tmp.newRow.insert({'bottom': new Element(tmp.tag).update( tmp.isTitle === true ? titleData.start : new Element('input', {'class': 'form-control input-sm datepicker', 'list-panel-row': 'start', 'value': data.start ? data.start : ''}) ) });
		}
		if(titleData.end){
			tmp.newRow.insert({'bottom': new Element(tmp.tag).update( tmp.isTitle === true ? titleData.end : new Element('input', {'class': 'form-control input-sm datepicker', 'list-panel-row': 'end', 'value': data.end ? data.end : ''}) ) });
		}
		tmp.newRow.insert({'bottom': new Element(tmp.tag).update( tmp.isTitle === true ? titleData.value : tmp.inputBoxDiv) });
		return tmp.newRow;
	}
	
	,_getListPanel: function(title, listData, titleData, selBoxData) {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = new Element('div', {'class': 'panel panel-default'})
			.insert({'bottom': new Element('div', {'class': 'panel-heading'}) 
				.insert({'bottom': new Element('strong').update(title) 
					.insert({'bottom': new Element('span', {'class': 'btn btn-primary btn-xs pull-right', 'title': 'New'})
						.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-plus'}) })
						.insert({'bottom': ' NEW' })
						.observe('click', function(){
							$(this).up('.panel').down('.table tbody').insert({'bottom': tmp.me._getListPanelRow({}, selBoxData, titleData, false).addClassName('list-panel-row').writeAttribute('item_id', '') });
							tmp.me._bindDatePicker();
						})
					})
				})
			})
			.insert({'bottom': new Element('div', {'class': 'table-responsive'}) 
				.insert({'bottom': new Element('table', {'class': 'table table-condensed'}) 
					.insert({'bottom': new Element('thead').update( tmp.me._getListPanelRow(titleData, selBoxData, titleData, true) ) })
					.insert({'bottom': tmp.listDiv = new Element('tbody') })
				})
			});
		listData.each(function(data){
			tmp.listDiv.insert({'bottom': tmp.me._getListPanelRow(data, selBoxData, titleData, false).addClassName('list-panel-row').writeAttribute('item_id', data.id) });
		});
		return tmp.newDiv;
	}
	
	,_loadRichTextEditor: function(input) {
		var tmp = {};
		tmp.me = this;
		tmp.me._signRandID(input);
		tmp.editor = new TINY.editor.edit('editor',{
			id: input.id,
			width: '100%',
			height: 100,
			cssclass: 'tinyeditor',
			controlclass: 'tinyeditor-control',
			rowclass: 'tinyeditor-header',
			dividerclass: 'tinyeditor-divider',
			controls: ['bold', 'italic', 'underline', 'strikethrough', '|', 'subscript', 'superscript', '|',
				'orderedlist', 'unorderedlist', '|', 'outdent', 'indent', '|', 'leftalign',
				'centeralign', 'rightalign', 'blockjustify', '|', 'unformat', '|', 'undo', 'redo', 'n',
				'font', 'size', 'style', '|', 'image', 'hr', 'link', 'unlink', '|', 'print'],
			footer: true,
			fonts: ['Verdana','Arial','Georgia','Trebuchet MS'],
			xhtml: true,
			cssfile: 'custom.css',
			bodyid: 'editor',
			footerclass: 'tinyeditor-footer',
			toggle: {text: 'source', activetext: 'wysiwyg', cssclass: 'toggle'},
			resize: {cssclass: 'resize'}
		});
		input.store('editor', tmp.editor);
		return tmp.me;
	}
	
	,_getRichTextEditor: function(text) {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = new Element('textarea', {'class': 'rich-text-editor', 'save-item': 'fullDescription'}).update(text ? text : '');
		return tmp.newDiv;
	}
	
	,_getFullDescriptionPanel: function(item) {
		var tmp = {};
		tmp.me = this;
		tmp.fullDescriptioAssetId = item.fullDescAssetId ? item.fullDescAssetId : '';
		tmp.loadFullBtn = tmp.fullDescriptioAssetId.blank() ? tmp.me._getRichTextEditor('') : new Element('span', {'class': 'btn btn-default'}).update('click to load the full description')
			.observe('click', function(){
				tmp.newTextarea = tmp.me._getRichTextEditor('');
				$(this).replace(tmp.newTextarea);
				tmp.me._loadRichTextEditor(tmp.newTextarea);
			});
		tmp.newDiv = tmp.me._getFormGroup('Full Description:', tmp.loadFullBtn);
		return tmp.newDiv;
	}
	
	,_getSummaryDiv: function (item) {
		var tmp = {};
		tmp.me = this;
		tmp.item = item;
		tmp.treePanel = tmp.me._getFormGroup('Categories:',  new Element('div', {'class': 'easyui-panel', 'style': 'overflow: auto; height: 340px; min-width: 200px;'}).update(new Element('ul', {'id': 'product_category_tree', 'data-options': 'animate:true, checkbox:true'}) ) );
		tmp.treePanel.down('.form-control').removeClassName('form-control');
		
		tmp.newDiv = new Element('div', {'class': 'panel panel-default'})
			.insert({'bottom': new Element('div', {'class': 'panel-heading'})
				.insert({'bottom': new Element('strong').update('Editing: ' + tmp.item.name) })
				.insert({'bottom': new Element('small', {'class': 'pull-right'}) 
					.insert({'bottom': new Element('label', {'for': 'showOnWeb_' + tmp.item.id}).update('Show on Web?') })
					.insert({'bottom': new Element('input', {'id': 'showOnWeb_' + tmp.item.id, 'save-item': 'sellOnWeb', 'type': 'checkbox', 'checked': tmp.item.sellOnWeb}) })
				})
			})
			.insert({'bottom': new Element('div', {'class': 'panel-body'})
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('div', {'class': 'col-md-8'})
						.insert({'bottom': new Element('div', {'class': 'row'})
							.insert({'bottom': new Element('div', {'class': 'col-sm-3'}).update(tmp.me._getFormGroup('Name', new Element('input', {'save-item': 'name', 'type': 'text', 'value': tmp.item.name}) ) ) })
							.insert({'bottom': new Element('div', {'class': 'col-sm-3'}).update(tmp.me._getFormGroup('sku', new Element('input', {'save-item': 'sku', 'type': 'text', 'value': tmp.item.sku}) ) ) })
							.insert({'bottom': new Element('div', {'class': 'col-sm-2'}).update(tmp.me._getFormGroup('Brand/Manf.', 
									tmp.me._getSelBox(tmp.me._manufacturers, tmp.item.manufacturer ? tmp.item.manufacturer.id : null).writeAttribute('save-item', 'manufacture.id').addClassName('chosen') 
							) ) })
							.insert({'bottom': new Element('div', {'class': 'col-sm-2'}).update(tmp.me._getFormGroup('Status', 
									tmp.me._getSelBox(tmp.me._statuses, tmp.item.status ? tmp.item.status.id : null).writeAttribute('save-item', 'status.id').addClassName('chosen') 
							) ) })
						})
						.insert({'bottom': new Element('div', {'class': 'row'})
							.insert({'bottom': new Element('div', {'class': 'col-sm-12'}).update(tmp.me._getFormGroup('Short Description:', new Element('input', {'save-item': 'shortDescription', 'type': 'text', 'value': tmp.item.shortDescription}) ) ) })
						})
						.insert({'bottom': new Element('div', {'class': 'row'})
							.insert({'bottom': new Element('div', {'class': 'col-sm-12'}).update(tmp.me._getFullDescriptionPanel(tmp.item) ) })
						})
					})
					.insert({'bottom': new Element('div', {'class': 'col-md-4'})
						.insert({'bottom': tmp.treePanel })
					})
				})
			});
		return tmp.newDiv;
	}
	
	
	,_loadFancyBox: function(elements) {
		var tmp = {};
		tmp.me = this;
		elements.each(function(item){
			item.observe('click', function(){
				tmp.imgs = [];
				elements.each(function(el){
					tmp.imgs.push({'href': el.down('img').readAttribute('src')});
				});
				jQuery.fancybox(tmp.imgs, {
					prevEffect	: 'none',
					nextEffect	: 'none',
					helpers	: {
						title	: {
							type: 'outside'
						},
						thumbs	: {
							height	: 50
						}
					}
				})
			});
		});
		return this;
	}
	
	,_getImageThumb: function(img) {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = new Element('div', {'class': 'col-xs-12 col-sm-6 col-md-4 thumbnail-holder btn-hide-row'})
			.insert({'bottom': new Element('a', {'href': 'javascript: void(0)', 'class': 'thumbnail fancybox-thumb', 'ref': 'product_thumbs'})
				.insert({'bottom': new Element('img', {'data-src': 'holder.js/100%x180', 'src': img.src}) })
			})
			.insert({'bottom': new Element('span', {'class': 'btns'})
				.insert({'bottom': new Element('small', {'class': 'btn btn-danger btn-xs'}) 
					.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-trash'}) })
				})
				.observe('click', function(){
					if(!confirm('Delete this image?'))
						return false;
					$(this).up('.thumbnail-holder').remove();
				})
			});
		return tmp.newDiv;
	}
	
	,_readImages: function(evt, targetDiv) {
		var tmp = {};
		tmp.me = this;
		tmp.files = evt.target.files; // FileList object
		// Loop through the FileList and render image files as thumbnails.
		for (tmp.i = 0; tmp.file = tmp.files[tmp.i]; tmp.i++) {
			// Only process image files.
			if (tmp.file.type.match('image.*')) {
				tmp.reader = new FileReader();
				// Closure to capture the file information.
				tmp.reader.onload = (function(theFile){
					return function(e) {
						// Render thumbnail.
						tmp.thumb = tmp.me._getImageThumb({'src': e.target.result});
						$(targetDiv).insert({'bottom': tmp.thumb });
						evt.target.value = '';
						tmp.me._loadFancyBox($$('.fancybox-thumb'));
			        };
				})(tmp.file);
				// Read in the image file as a data URL.
				tmp.reader.readAsDataURL(tmp.file);
			}
		}
		return tmp.me;
	}
	
	,_getImagesPanel: function(item) {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = new Element('div', {'class': 'panel panel-default'})
			.insert({'bottom': new Element('div', {'class': 'panel-heading'})
				.insert({'bottom': new Element('input', {'type': 'file', 'multiple': true})
					.observe('change', function(evt) {
						tmp.me._readImages(evt, $(this).up('.panel').down('.panel-body'))
					})
				})
			})
			.insert({'bottom': new Element('div', {'class': 'panel-body'})
			});
		return tmp.newDiv;
	}
	
	/**
	 * Ajax: saving the item
	 */
	,_submitSave: function(btn) {
		var tmp = {};
		tmp.me = this;
		tmp.data = tmp.me._collectFormData($(tmp.me._htmlIds.itemDiv), 'save-item');
		console.debug(tmp.data);
		return tmp.me;
	}
	/**
	 * displaying the item
	 */
	,_getItemDiv: function() {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = new Element('div')
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('div', {'class': 'col-sm-4'})
					.insert({'bottom': tmp.me._getImagesPanel(tmp.me._item) })
				})
				.insert({'bottom': new Element('div', {'class': 'col-sm-8'})
					.insert({'bottom': tmp.me._getSummaryDiv(tmp.me._item) })
				})
			})
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('div', {'class': 'col-sm-3'}).update(
					tmp.me._getListPanel('Suppliers:', tmp.me._item.supplierCodes, {'type': 'Supplier', 'value': 'Code'}, tmp.me._suppliers)
				) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-3'}).update(
						tmp.me._getListPanel('Codes:', tmp.me._item.supplierCodes, {'type': 'Type', 'value': 'Code'}, tmp.me._codeTypes)
				) })
				.insert({'bottom': new Element('div', {'class': 'col-sm-6'}).update(tmp.me._getListPanel('Prices:', tmp.me._item.prices, {'type': 'Type', 'value': 'Price', 'start': 'From', 'end': 'To'}, tmp.me._priceTypes) 
				) })
			})
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('span', {'class': 'btn btn-primary pull-right col-sm-2', 'data-loading-text': 'saving ...'}).update('Save')
					.observe('click', function() {
						tmp.me._submitSave(this);
					})
				})
			});
		return tmp.newDiv;
	}
	
	/**
	 * Getting each row of the category tree panel
	 */
	,_getChildCategoryJson: function(category, selectedCateIds) {
		var tmp = {};
		tmp.me = this;
		tmp.cate = {'text': category.name, 'id': category.id};
		if(selectedCateIds.indexOf(category.id) >= 0){
			tmp.cate.checked = true;
		}
		if(category.children && category.children.size() > 0) {
			tmp.cate.children = [];
			category.children.each(function(child){
				tmp.cate.children.push( tmp.me._getChildCategoryJson(child, selectedCateIds) );
			});
		}
		return tmp.cate;
	}
	/**
	 * initialising the tree
	 */
	,_initTree: function(selector) {
		var tmp = {};
		tmp.me = this;
		tmp.categoies = [];
		tmp.selectedCateIds = [];
		tmp.me._item.categories.each(function(cate) {
			tmp.selectedCateIds.push(cate.id);
		})
		tmp.me._productCategories.each(function(category) {
			tmp.categoies.push(tmp.me._getChildCategoryJson(category, tmp.selectedCateIds));
		});
		jQuery(selector).tree({
			data: tmp.categoies
		});
		return tmp.me;
	}
	/**
	 * initialing the js for date picker
	 */
	,_bindDatePicker: function() {
		var tmp = {};
		tmp.me = this;
		$$('.datepicker').each(function(item){
			if(!item.hasClassName('datepicked')) {
				tmp.me._signRandID(item);
				tmp.picker = new Prado.WebUI.TDatePicker({'ID': item.id, 'InputMode':"TextBox",'Format':"yyyy-MM-dd 00:00:00",'FirstDayOfWeek':1,'CalendarStyle':"default",'FromYear':2009,'UpToYear':2024,'PositionMode':"Bottom", "ClassName": 'datepicker-layer-fixer'});
				item.store('picker', tmp.picker);
			}
		});
		return tmp.me;
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
		tmp.me._initTree('#product_category_tree');
		return tmp.me;
	}
});