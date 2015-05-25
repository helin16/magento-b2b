/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new DetailsPageJs(), {
	_getUnitPrice: function(product) {
		var tmp = {};
		tmp.me = this;
		tmp.unitPrice = 0;
		if(product && product.prices && product.prices.size() > 0) {
			product.prices.each(function(price){
				if(price.type && parseInt(price.type.id) === 1) {
					tmp.unitPrice = price.price;
				}
			})
		}
		return tmp.unitPrice;
	}
	/**
	 * confirmDeleting the row
	 */
	,_confirmDelRow: function(btn) {
		var tmp = {};
		tmp.me = this;
		tmp.row = $(btn).up('.item_row');
		if(!tmp.row || tmp.row.hasAttribute('item_id'))
			return tmp.me;

		tmp.rowData = tmp.row.retrieve('data');
		tmp.productData = tmp.row.retrieve('product');
		tmp.newDiv = new Element('div')
			.insert({"bottom": new Element('div')
				.insert({"bottom": new Element('strong').update('You are about to delete this selected row, with information:') })
				.insert({"bottom": new Element('ul')
					.insert({"bottom": new Element('li').update('<strong>Product SKU: </strong>' + tmp.rowData.productData.sku) })
					.insert({"bottom": new Element('li').update('<strong>Product Name: </strong>' + tmp.rowData.productData.name) })
					.insert({"bottom": new Element('li').update('<strong>Unit Price: </strong>' + tmp.me.getCurrency(tmp.me.getValueFromCurrency($F(tmp.row.down('[row-field="unit-price"]'))))) })
					.insert({"bottom": new Element('li').update('<strong>Qty: </strong>' +  $F(tmp.row.down('[row-field="qty"]'))) })
					.insert({"bottom": new Element('li').update('<strong>Total Price: </strong>' + $F(tmp.row.down('[row-field="total-price"]'))) })
				})
			})
			.insert({"bottom": new Element('div')
				.insert({"bottom": new Element('strong').update('Are you sure to continue?') })
			})
			.insert({"bottom": new Element('div')
				.insert({"bottom": new Element('div', {'class': 'col-xs-6'}).update(new Element('div', {'class': 'btn btn-default'})
					.update('No. Cancel')
					.observe('click', function() {
						tmp.me.hideModalBox();
					})
				) })
				.insert({"bottom": new Element('div', {'class': 'col-xs-6 text-right'}).update(new Element('div', {'class': 'btn btn-default'})
					.update('Yes. Delete it.')
					.observe('click', function() {
						if(tmp.rowData.id) {
							tmp.row.writeAttribute('deactived', '1').hide();
						} else {
							tmp.row.remove();
						}
					})
				) })
			});
		tmp.me.showModalBox('<strong>Confirm</strong>', tmp.newDiv);
		return tmp.me;
	}
	/**
	 * Gettig the product Row for the result table
	 */
	,_getProductRow: function(isTitle, row) {
		var tmp = {};
		tmp.me = this;
		tmp.isTitle = (isTitle === true) ? true : false;
		tmp.tag = 'div';
		tmp.newDiv = new Element('div', {'class': 'list-group-item item_row'})
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-8 product'}).update(tmp.isTitle === true ? new Element('strong').update('Product') : tmp.me._getProductDetailsDiv(row.product, true) )})
				.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-4 row-details'})
					.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-4 unitPrice   text-right'}).update(tmp.isTitle === true ? new Element('strong').update('Unit Price (inc. GST)') : tmp.me.getCurrency(row.unitPrice) )})
					.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-3 qty'}).update(tmp.isTitle === true ? new Element('strong').update('Qty') : row.qty )})
					.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-4 totalPrice  text-right'}).update(tmp.isTitle === true ? new Element('strong').update('Total Price (inc. GST)') : tmp.me.getCurrency(row.unitPrice * row.qty) )})
					.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-1 btns text-right'}).update(tmp.isTitle === true ? new Element('strong').update('&nbsp;') : ((!row || !row.id) ? '' :
						new Element('span', {'class': 'btn btn-danger btn-xs'})
							.update( new Element('span', {'class': 'glyphicon glyphicon-trash'}) )
							.observe('click', function (){
								if(row && row.id)
									tmp.me._confirmDelRow(this);
							})
					) ) })
				})
			});
		if(row && row.id) {
			tmp.newDiv.store(row).writeAttribute('item_id', row.id);
		}
		return tmp.newDiv;
	}
	/**
	 * Getting the new product row
	 */
	,_inputRow: function() {
		var tmp = {};
		tmp.me = this;
		tmp.newRow = tmp.me._getProductRow(true).addClassName('list-group-item-info item_row_new');
		tmp.newRow.down('.product').update(tmp.me.getFormGroup('',
			new Element('input', {'class': 'form-control select2 input-sm product-search input-row-field', 'name': 'rowField[productId]', 'row-field': 'product-id', 'isKit': '0', 'placeholder': 'Search a product', 'onSelectFunc': '_selectRowProduct'})
		));
		tmp.newRow.down('.unitPrice').update(tmp.me.getFormGroup('',
			new Element('div', {'class': 'input-group'})
				.insert({'bottom': new Element('span', {'class': 'input-group-addon'}).update('$')})
				.insert({'bottom': new Element('input', {'class': 'form-control input-sm input-row-field', 'row-field': 'unit-price', 'name': 'rowField[unitPrice]', 'placeholder': 'Unit price inc. GST'})
					.observe('change', function(){

					})
				})
		));
		tmp.newRow.down('.qty').update(tmp.me.getFormGroup('',
			new Element('input', {'class': 'form-control input-sm input-row-field', 'row-field': 'qty', 'name': 'rowField[qty]', 'placeholder': 'Quanity'})
				.observe('change', function(){

				})
		));
		tmp.newRow.down('.totalPrice').update(tmp.me.getCurrency(0));
		tmp.newRow.down('.btns').update(new Element('span', {'class': 'btn btn-primary btn-sm row-field-save-btn', 'valid-target': 'input-row-field'})
			.update(new Element('i', {'class': 'glyphicon glyphicon-floppy-saved'}))
		);
		return tmp.newRow;
	}
	,_addNewRow: function(data) {
		var tmp = {};
		tmp.me = this;
		tmp.list = $$('.kit-components-list').first();
		if(!tmp.list)
			return tmp.me;
		tmp.list.insert({'bottom': tmp.me._getProductRow(false, data)});
		return tmp.me;
	}
	/**
	 * init input row form validation
	 */
	,_initInputRowValidation: function(btn) {
		var tmp = {};
		tmp.me = this;
		tmp.me._signRandID(btn);
		tmp.jQueryForm = jQuery('#' + tmp.me.getHTMLID('main-form'));
		tmp.fields = {
				'rowField[productId]': {
					validators: {
						notEmpty: {
			                message: 'Please select a product'
			            }
					}
				},
				'rowField[unitPrice]': {
					validators: {
						notEmpty: {
							message: 'Unit Price is required'
						},
						callback: {
							message: 'Invalid unit price provided.',
							callback: function(value, validator, $field) {
								if (tmp.me.getValueFromCurrency(value).match(/^\d+(\.\d{1,4})?$/) === null) {
									return {
			                            valid: false,
			                            message: 'Invalid unit price provided.'
			                        }
								}
								return true;
							}
						}
					}
				},
				'rowField[qty]': {
					validators: {
						 notEmpty: {
			                 message: 'Quantity is required'
			             },
			             integer: {
			 				 message: 'Quantity is not an integer'
			 			}
					}
				}
		};
		tmp.jQueryForm.find('[row-field="product-id"]').on('change', function(){
			tmp.jQueryForm.formValidation('revalidateField', 'rowField[productId]');
		});
		jQuery.each(tmp.fields, function(fieldName, options) {
			tmp.jQueryForm.formValidation('addField', fieldName, options);
		});
		tmp.jQueryForm.on('click', '#' + $(btn).id, function(e) {
			console.debug('test');
            jQuery.each(jQuery('.form-control'), function(index, element){
            	jQuery(tmp.me.jQueryFormSelector).bootstrapValidator('enableFieldValidators', jQuery(element).attr('name'), jQuery(element).hasClass($(btn).readAttribute('valid-target')));
            });
            if(tmp.jQueryForm.bootstrapValidator('validate').data('bootstrapValidator').isValid() === true) {
            	tmp.me._addNewRow({
            		'product': tmp.jQueryForm.find('[row-field="product-id"]').select2('data').data,
            		'unitPrice': tmp.me.getValueFromCurrency(tmp.jQueryForm.find('[row-field="unit-price"]').val()),
            		'qty':tmp.jQueryForm.find('[row-field="qty"]').val()
            	});
            	jQuery.each(tmp.fields, function(fieldName, options) {
        			tmp.jQueryForm.formValidation('removeField', fieldName, options);
        		});
            	tmp.newRow = tmp.me._inputRow();
            	$(btn).up('.item_row_new').replace(tmp.newRow);
            	tmp.me._initProductSearch()
    				._initInputRowValidation( tmp.newRow.down('.row-field-save-btn') );
            	tmp.jQueryForm.bootstrapValidator('resetForm');
            }
        });
		return tmp.me;
	}
	/**
	 * show KitDetails
	 */
	,_showKitDetails: function() {
		var tmp = {};
		tmp.me = this;
		tmp.detailsDiv = $( tmp.me.getHTMLID('kitsDetailsDiv') );
		if(!tmp.detailsDiv)
			return tmp.me;
		tmp.newTable = new Element('div', {'class': 'list-group kit-components-list'})
			.insert({'bottom': tmp.me._getProductRow(true).addClassName('item_row_header list-group-item-success') })
			.insert({'bottom': tmp.inputRow = tmp.me._inputRow() })
		tmp.detailsDiv.update(new Element('h4').update('List of Parts Inside This Kit:'))
			.insert({'bottom': tmp.newTable});
		tmp.me._initProductSearch()
			._initInputRowValidation( tmp.inputRow.down('.row-field-save-btn') );
		return tmp.me;
	}
	/**
	 * Getting the item div row
	 */
	,_getItemDiv: function() {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = new Element('div', {'class': 'save-panel'})
			.insert({'bottom': new Element('h3', {'class': 'text-center'}).update(tmp.me._item.id ? 'Editing KIT: ' + tmp.me._item.barcode : 'Building New Kit') })
			.insert({'bottom': new Element('div', {'class': 'form-horizontal'})
				.insert({'bottom':  tmp.me.getFormGroup(new Element('label').update('You are trying to build a kit as: ').addClassName('col-sm-2'),
					new Element('div', {'class': 'col-xs-10 rm-form-control kit-product-div'}).update(
						new Element('input', {'class': 'form-control select2 input-sm product-search', 'save-panel': 'kit-product-id', 'isKit': '0', 'onSelectFunc': '_selectKitProduct'})
					)
				) })
				.insert({'bottom':  new Element('div', {'id': tmp.me.getHTMLID('kitsDetailsDiv')})})
			});
		tmp.newDiv.getElementsBySelector('.rm-form-control').each(function(item) {
			item.removeClassName('form-control').removeClassName('rm-form-control');
		});
		return tmp.newDiv;
	}
	/**
	 * Getting the product searching row
	 */
	,_getProductDetailsDiv: function(product, noSOHInfo) {
		var tmp = {};
		tmp.me = this;
		tmp.SOHInfo = (noSOHInfo === true ? false : true);
		tmp.newDiv = new Element('div', {'class': 'row'})
		if(!product || !product.id)
			return tmp.newDiv;

		tmp.defaultImg = new Element('img', {'data-src': 'holder.js/100%x64', 'src': 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI2NCIgaGVpZ2h0PSI2NCI+PHJlY3Qgd2lkdGg9IjY0IiBoZWlnaHQ9IjY0IiBmaWxsPSIjZWVlIi8+PHRleHQgdGV4dC1hbmNob3I9Im1pZGRsZSIgeD0iMzIiIHk9IjMyIiBzdHlsZT0iZmlsbDojYWFhO2ZvbnQtd2VpZ2h0OmJvbGQ7Zm9udC1zaXplOjEycHg7Zm9udC1mYW1pbHk6QXJpYWwsSGVsdmV0aWNhLHNhbnMtc2VyaWY7ZG9taW5hbnQtYmFzZWxpbmU6Y2VudHJhbCI+NjR4NjQ8L3RleHQ+PC9zdmc+'});
		tmp.newDiv.store('data', product)
			.insert({'bottom': new Element('div', {'class': 'col-xs-1'}).update(tmp.defaultImg) })
			.insert({'bottom': new Element('div', {'class': 'col-xs-11'})
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('div', {'class': 'col-xs-8'}).update(product.name)			})
					.insert({'bottom': new Element('div', {'class': 'col-xs-2'})
						.insert({'bottom': new Element('div', {'class': 'col-xs-4 text-right'}).update('<strong>Brand:</strong>:')})
						.insert({'bottom': new Element('div', {'class': 'col-xs-8 '}).update(product.manufacturer ? product.manufacturer.name : '')})
					})
					.insert({'bottom': new Element('div', {'class': 'col-xs-2'})
						.insert({'bottom': new Element('div', {'class': 'col-xs-4 text-right'}).update('<strong>sku</strong>:')})
						.insert({'bottom': new Element('div', {'class': 'col-xs-8 '}).update(product.sku)})
					})
				})
				.insert({'bottom': new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('small', {'class': 'col-xs-12'}).update('<em>' + product.shortDescription + '</em>')			})
				})
				.insert({'bottom': tmp.SOHInfo !== true ? '' : new Element('div', {'class': 'row'})
					.insert({'bottom': new Element('div', {'class': 'col-xs-2'})
						.insert({'bottom': new Element('div', {'class': 'input-group input-group-sm'})
							.insert({'bottom': new Element('div', {'class': 'input-group-addon'}).update('SOH:') })
							.insert({'bottom': new Element('div', {'class': 'form-control'}).update(product.stockOnHand) })
						})
					})
					.insert({'bottom': new Element('div', {'class': 'col-xs-3'})
						.insert({'bottom': new Element('div', {'class': 'input-group input-group-sm'})
							.insert({'bottom': new Element('div', {'class': 'input-group-addon'}).update('Unit Price (inc GST):') })
							.insert({'bottom': new Element('div', {'class': 'form-control'}).update(tmp.me.getCurrency(tmp.me._getUnitPrice(product))) })
						})
					})
				})
			})
		return tmp.newDiv;
	}
	/**
	 * Selecting a row product
	 */
	,_selectRowProduct: function(product) {
		var tmp = {};
		tmp.me = this;
		tmp.rowDiv = $$('.item_row.item_row_new').first();
		if(!tmp.rowDiv)
			return tmp.me;
		tmp.productDetailsDiv = tmp.rowDiv.down('.product').down('.product-details');
		if(!tmp.productDetailsDiv) {
			tmp.rowDiv.down('.product').insert({'bottom': tmp.productDetailsDiv = new Element('div', {'class': 'product-details'})});
		}
		tmp.productDetailsDiv.update(tmp.me._getProductDetailsDiv(product, true));
		tmp.rowDiv.down('[row-field="qty"]').setValue(1);
		tmp.rowDiv.down('[row-field="unit-price"]').setValue(tmp.me.getCurrency(tmp.me.getValueFromCurrency(tmp.me._getUnitPrice(product)), ''));
		return tmp.me;
	}
	/**
	 * Selecting a kit product
	 */
	,_selectKitProduct: function(product) {
		var tmp = {};
		tmp.me = this;
		tmp.kitProductDiv = $$('.kit-product-div').first();
		if(!tmp.kitProductDiv)
			return tmp.me;
		tmp.kitProductDiv.getElementsBySelector('.selected-kit-product-view').each(function(element) { element.remove(); });
		tmp.kitProductDiv.insert({"bottom": tmp.me._getProductDetailsDiv(product).addClassName('selected-kit-product-view panel-body')});
		tmp.me._showKitDetails();
		return tmp.me;
	}
	/**
	 * initProduct search
	 */
	,_initProductSearch: function() {
		var tmp = {};
		tmp.me = this;
		tmp.pageSize = 10;
		tmp.select2 = jQuery('.select2.product-search:not(.loaded)');
		tmp.select2.select2({
			 placeholder: "Search a product",
			 minimumInputLength: 3,
			 data: [],
			 ajax: {
				 url: "/ajax/getAll",
				 dataType: 'json',
				 quietMillis: 250,
				 data: function (term, page) { // page is the one-based page number tracked by Select2
					 return {
						 'entityName': 'Product',
						 'searchTxt': '(name like :searchTxt or mageId = :searchTxtExact or sku = :searchTxtExact) and isKit = ' + tmp.select2.attr('isKit'), //search term
						 'searchParams': {'searchTxt': '%' + term + '%', 'searchTxtExact': term},
						 'pageNo': page, // page number
						 'pageSize': tmp.pageSize
					 };
				 },
				 results: function (data, page) {
					tmp.result = [];
					data.resultData.items.each(function(item){
						tmp.result.push({"id": item.id, 'text': '[' + item.sku + '] ' + item.name, 'data': item});
					})
					return {
						results:  tmp.result,
						more: (page * tmp.pageSize) < data.resultData.pagination.totalRows
					};
				}
			 },
			 formatResult : function(result) {
				 if(!result)
					 return '';
				 return tmp.me._getProductDetailsDiv(result.data);
			 },
			 escapeMarkup: function (markup) { return markup; } // let our custom formatter work
		});
		tmp.select2.addClass('loaded');
		tmp.select2.on("select2-selecting", function(event) {
			tmp.txtBox = $(event.target);
			tmp.onSelectFunc = tmp.txtBox.readAttribute('onSelectFunc');
			if(typeof(tmp.me[tmp.onSelectFunc]) === 'function')
				tmp.me[tmp.onSelectFunc](event.object.data);
		});
		return tmp.me;
	}
	/**
	 * init form validataion
	 */
	,_initFormValidation: function() {
		var tmp = {};
		tmp.me = this;
		jQuery('#' + tmp.me.getHTMLID('main-form')).bootstrapValidator({
	        message: 'This value is not valid',
	        excluded: ':disabled',
	        feedbackIcons: {
	            valid: 'glyphicon glyphicon-ok',
	            invalid: 'glyphicon glyphicon-remove',
	            validating: 'glyphicon glyphicon-refresh'
	        },
	        fields: {}
		})
		.on('success.form.bv', function(e) {
            // Prevent form submission
            e.preventDefault();
        })
        .on('error.field.bv', function(e, data) {
        	data.bv.disableSubmitButtons(false);
        })
        .on('success.field.bv', function(e, data) {
        	data.bv.disableSubmitButtons(false);
        });
		return tmp.me;
	}
	/**
	 * init
	 */
	,_init: function(){
		var tmp = {};
		tmp.me = this;
		return tmp.me;
	}
	/**
	 * load
	 */
	,load: function () {
		var tmp = {};
		tmp.me = this;
		tmp.me._init();
		$(tmp.me.getHTMLID('itemDiv')).update(tmp.div = tmp.me._getItemDiv());
		tmp.me._initProductSearch()
			._initFormValidation();
		return tmp.me;
	}
});