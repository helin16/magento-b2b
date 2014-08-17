/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new DetailsPageJs(), {
	_manufacturers: []
	,_suppliers: []
	,_statuses: []
	,_priceTypes: []
	
	,_getFormGroup: function (label, input) {
		return new Element('div', {'class': 'form-group form-group-sm form-group-sm-label'})
			.insert({'bottom': new Element('label').update(label) })
			.insert({'bottom': input.addClassName('form-control') });
	}
	,setManufactures: function(manufacturers) {
		this._manufacturers = manufacturers;
		return this;
	}
	,setSuppliers: function(suppliers) {
		this._suppliers = suppliers;
		return this;
	}
	,setStatuses: function(statuses) {
		this._statuses = statuses;
		return this;
	}
	,setPriceTypes: function(priceTypes) {
		this._priceTypes = priceTypes;
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
							tmp.me.bindDatePicker();
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
	
	,_getSummaryDiv: function (item) {
		var tmp = {};
		tmp.me = this;
		tmp.item = item;
		tmp.newDiv = new Element('div', {'class': 'panel panel-default'})
			.insert({'bottom': new Element('div', {'class': 'panel-heading'})
				.insert({'bottom': new Element('strong').update('Editing :' + tmp.item.name) })
			})
			.insert({'bottom': new Element('div', {'class': 'panel-body'})
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('div', {'class': 'col-sm-3'}).update(tmp.me._getFormGroup('Name', new Element('input', {'save-item': 'name', 'type': 'text', 'value': tmp.item.name}) ) ) })
					.insert({'bottom': new Element('div', {'class': 'col-sm-3'}).update(tmp.me._getFormGroup('sku', new Element('input', {'save-item': 'sku', 'type': 'text', 'value': tmp.item.sku}) ) ) })
					.insert({'bottom': new Element('div', {'class': 'col-sm-2'}).update(tmp.me._getFormGroup('on Web?', new Element('div').update(new Element('input', {'save-item': 'sellOnWeb', 'type': 'checkbox', 'checked': tmp.item.sellOnWeb})) ) ) })
					.insert({'bottom': new Element('div', {'class': 'col-sm-2'}).update(tmp.me._getFormGroup('Brand/Manf.', 
							tmp.me._getSelBox(tmp.me._manufacturers, tmp.item.manufacturer ? tmp.item.manufacturer.id : null).writeAttribute('save-item', 'manufacture.id').addClassName('chosen') 
					) ) })
					.insert({'bottom': new Element('div', {'class': 'col-sm-2'}).update(tmp.me._getFormGroup('Status', 
							tmp.me._getSelBox(tmp.me._statuses, tmp.item.status ? tmp.item.status.id : null).writeAttribute('save-item', 'status.id').addClassName('chosen') 
					) ) })
				})
			});
		return tmp.newDiv;
	}
	
	,_getFullDescriptionPanel: function(item) {
		var tmp = {};
		tmp.me = this;
		tmp.checkBoxId = 'show-full-desc-' + item.id;
		tmp.newDiv = new Element('div', {'class': 'panel panel-default'})
			.insert({'bottom': new Element('div', {'class': 'panel-heading'})
				.insert({'bottom': new Element('label', {'for': tmp.checkBoxId}).update('Show Full Descripton:') })
				.insert({'bottom': new Element('input', {'id': tmp.checkBoxId, 'type': 'checkbox'}) })
			});
		return tmp.newDiv;
	}
	
	,_getImageThumb: function(img) {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = new Element('div', {'class': 'col-xs-12 col-sm-6 col-md-4'})
			.insert({'bottom': new Element('a', {'href': img.src, 'class': 'thumbnail fancybox-thumb'})
				.insert({'bottom': new Element('img', {'data-src': 'holder.js/100%x180', 'src': img.src}) })
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
						$(targetDiv).insert({'bottom': tmp.me._getImageThumb({'src': e.target.result}) });
						evt.target.value = '';
						jQuery(".fancybox-thumb").fancybox();
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
					.insert({'bottom': new Element('div', {'class': 'row'})
						.insert({'bottom': new Element('div', {'class': 'col-sm-4'}).update(
							tmp.me._getListPanel('Suppliers:', tmp.me._item.supplierCodes, {'type': 'Supplier', 'value': 'Code'}, tmp.me._suppliers)
						) })
						.insert({'bottom': new Element('div', {'class': 'col-sm-8'}).update(tmp.me._getListPanel('Prices:', tmp.me._item.prices, {'type': 'Type', 'value': 'Price', 'start': 'From', 'end': 'To'}, tmp.me._priceTypes) 
						) })
					})
				})
			})
			.insert({'bottom':new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('div', {'class': 'col-sm-12'}).update(tmp.me._getFullDescriptionPanel(tmp.me._item)) })
			});
		return tmp.newDiv;
	}
	
	,bindDatePicker: function() {
		var tmp = {};
		tmp.me = this;
		$$('.datepicker').each(function(item){
			if(!item.hasClassName('datepicked')) {
				tmp.me._signRandID(item);
				new Prado.WebUI.TDatePicker({'ID': item.id, 'InputMode':"TextBox",'Format':"yyyy-MM-dd 00:00:00",'FirstDayOfWeek':1,'CalendarStyle':"default",'FromYear':2009,'UpToYear':2024,'PositionMode':"Bottom", "ClassName": 'datepicker-layer-fixer'});
			}
		});
		return tmp.me;
	}
});