/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new DetailsPageJs(), {
	_openinFB: true
	/**
	 * whether to open in fancybox for details page
	 */
	,setOpenInFancyBox: function(_openinFB) {
		var tmp = {};
		tmp.me = this;
		tmp.me._openinFB = _openinFB;
		return tmp.me;
	}
	,_getUnitPrice: function(product) {
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
	,_openURL: function(url) {
		var tmp = {};
		tmp.me = this;
		tmp.url = url;
		if(tmp.me._openinFB !== true) {
			window.location = tmp.url;
			return tmp.me;
		}
		jQuery.fancybox({
			'width'			: '95%',
			'height'		: '95%',
			'autoScale'     : false,
			'autoDimensions': false,
			'fitToView'     : false,
			'autoSize'      : false,
			'type'			: 'iframe',
			'href'			: tmp.url
			});
		return tmp.me;
	}
	/**
	 * refresh the parent's window's result
	 */
	,refreshParentWindow: function(row) {
		var tmp = {};
		tmp.me = this;
		if(!window.parent)
			return;
		tmp.parentWindow = window.parent;
		if(tmp.parentWindow.pageJs.refreshResultRow) {
			tmp.parentWindow.pageJs.refreshResultRow(row);
		}
	}
	/**
	 * Getting the summary
	 */
	,_getSummary: function() {
		var tmp = {};
		tmp.me = this;
		tmp.totalIncGst = 0;
		tmp.rowData = [];
		$$('.item_data_row').each(function(row){
			if(!row.hasClassName('deactivated')) {
				tmp.unitPrice = tmp.me.getValueFromCurrency($F(row.down('[row-field="unit-price"]')));
				tmp.qty = $F(row.down('[row-field="qty"]'));
				tmp.totalIncGst = tmp.totalIncGst + (tmp.unitPrice  * tmp.qty);
			}
			tmp.rowOriginData = row.retrieve('data');
			tmp.rowData.push({'productId': tmp.rowOriginData.product.id, 'unitPrice': tmp.unitPrice, 'qty': tmp.qty, 'id': (tmp.rowOriginData.id ? tmp.rowOriginData.id : ''), 'active': !row.hasClassName('deactivated')});
		});
		tmp.totalExclGst = (tmp.totalIncGst / 1.1);
		tmp.totalGST = tmp.totalIncGst * 1 - tmp.totalExclGst * 1;
		return {'totalIncGst': tmp.totalIncGst, 'totalGST': tmp.totalGST, 'totalExclGst': tmp.totalExclGst, 'rowData': tmp.rowData};
	}
	/**
	 * confirmDeleting the row
	 */
	,_confirmDelRow: function(btn) {
		var tmp = {};
		tmp.me = this;
		tmp.row = $(btn).up('.item_row');
		if(!tmp.row)
			return tmp.me;

		tmp.rowData = tmp.row.retrieve('data');
		tmp.unitPrice = $F(tmp.row.down('[row-field="unit-price"]'));
		tmp.qty = $F(tmp.row.down('[row-field="qty"]'));
		tmp.newDiv = new Element('div')
			.insert({"bottom": new Element('div')
				.insert({"bottom": new Element('strong').update('You are about to delete this selected row, with information:') })
				.insert({"bottom": new Element('ul')
					.insert({"bottom": new Element('li').update('<strong>Product SKU: </strong>' + tmp.rowData.product.sku) })
					.insert({"bottom": new Element('li').update('<strong>Product Name: </strong>' + tmp.rowData.product.name) })
					.insert({"bottom": new Element('li').update('<strong>Unit Price: </strong>' + tmp.me.getCurrency(tmp.me.getValueFromCurrency(tmp.unitPrice)) ) })
					.insert({"bottom": new Element('li').update('<strong>Qty: </strong>' +  tmp.qty) })
					.insert({"bottom": new Element('li').update('<strong>Total Price: </strong>' + tmp.me.getCurrency(tmp.qty * tmp.unitPrice)) })
				})
			})
			.insert({"bottom": new Element('div')
				.insert({"bottom": new Element('strong').update('Are you sure to continue?') })
			})
			.insert({"bottom": new Element('div', {'class': 'row'})
				.insert({"bottom": new Element('div', {'class': 'col-xs-6'}).update(new Element('div', {'class': 'btn btn-default'})
					.update('No. Cancel')
					.observe('click', function() {
						tmp.me.hideModalBox();
					})
				) })
				.insert({"bottom": new Element('div', {'class': 'col-xs-6 text-right'}).update(new Element('div', {'class': 'btn btn-danger'})
					.update('Yes. Delete it.')
					.observe('click', function() {
						if(tmp.rowData.id) {
							tmp.row.addClassName('deactivated').hide();
						} else {
							tmp.row.remove();
						}
						tmp.me._recalculateSummary()
							.hideModalBox();
					})
				) })
			});
		tmp.me.showModalBox('<strong>Confirm Deletion</strong>', tmp.newDiv);
		return tmp.me;
	}
	,_recalculateSummary: function() {
		var tmp = {};
		tmp.me = this;
		tmp.summary = tmp.me._getSummary();
		jQuery('[summary="total-excl-gst"]').html(tmp.me.getCurrency(tmp.summary.totalExclGst)).val(tmp.me.getCurrency(tmp.summary.totalExclGst));
		jQuery('[summary="total-gst"]').html(tmp.me.getCurrency(tmp.summary.totalGST)).val(tmp.me.getCurrency(tmp.summary.totalGST));
		jQuery('[summary="total-inc-gst"]').html(tmp.me.getCurrency(tmp.summary.totalIncGst)).val(tmp.me.getCurrency(tmp.summary.totalIncGst));
		return tmp.me;
	}
	,_getUnitPriceCell: function(unitPrice) {
		var tmp = {};
		tmp.me = this;
		tmp.unitPrice = (unitPrice || 0);
		return tmp.me.getFormGroup('',
			new Element('div', {'class': 'input-group'})
				.insert({'bottom': new Element('span', {'class': 'input-group-addon'}).update('$')})
				.insert({'bottom': new Element('input', {'class': 'form-control input-sm input-row-field', 'row-field': 'unit-price', 'name': 'rowField[unitPrice]', 'placeholder': 'Unit price inc. GST', 'value': tmp.me.getCurrency(tmp.unitPrice, '')})
					.observe('change', function(){
						tmp.unitPriceBox = this;
						tmp.newRow = $(tmp.unitPriceBox).up('.item_row');
						tmp.unitPrice = tmp.me.getValueFromCurrency($F(tmp.unitPriceBox));
						if (tmp.unitPrice.match(/^\d+(\.\d{1,4})?$/) !== null)
							$(tmp.unitPriceBox).setValue(tmp.me.getCurrency(tmp.unitPrice, ''));
						tmp.newRow.down('.totalPrice').update(tmp.me.getCurrency(tmp.unitPrice * $F(tmp.newRow.down('[row-field="qty"]'))));
						tmp.me._recalculateSummary();
					})
					.observe('keydown', function(event){
						tmp.btn = $(this);
						tmp.me.keydown(event, function(){
							tmp.saveBtn = tmp.btn.up('.item_row').down('.row-field-save-btn');
							if(tmp.saveBtn)
								tmp.saveBtn.click();
						});
					})
				})
		);
	}
	,_getQtyCell: function(qty) {
		var tmp = {};
		tmp.me = this;
		tmp.qty = (qty || 1);
		return tmp.me.getFormGroup('',
			new Element('input', {'class': 'form-control input-sm input-row-field', 'row-field': 'qty', 'name': 'rowField[qty]', 'placeholder': 'Quanity', 'value': tmp.qty, 'type': 'number'})
				.observe('change', function(){
					tmp.qtyBox = this;
					tmp.newRow = $(tmp.qtyBox).up('.item_row');
					tmp.qty = $F(this);
					$(tmp.qtyBox).setValue(parseInt(tmp.qty));
					tmp.newRow.down('.totalPrice').update(tmp.me.getCurrency(tmp.qty * $F(tmp.newRow.down('[row-field="unit-price"]'))));
					tmp.me._recalculateSummary();
				})
				.observe('keydown', function(event){
					tmp.btn = $(this);
					tmp.me.keydown(event, function(){
						tmp.saveBtn = tmp.btn.up('.item_row').down('.row-field-save-btn');
						if(tmp.saveBtn)
							tmp.saveBtn.click();
					});
				})
		)
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
					.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-4 unitPrice   text-right'}).update(tmp.isTitle === true ? new Element('strong').update('Unit Price (inc. GST)') : tmp.me._getUnitPriceCell(row.unitPrice) )})
					.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-3 qty'}).update(tmp.isTitle === true ? new Element('strong').update('Qty') :  tmp.me._getQtyCell(row.qty) )})
					.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-3 totalPrice  text-right'}).update(tmp.isTitle === true ? new Element('strong').update('Total Price (inc. GST)') : tmp.me.getCurrency(row.unitPrice * row.qty) )})
					.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-2 btns text-right'}).update(tmp.isTitle === true ? new Element('strong').update('&nbsp;') : ((!row) ? '' :
						new Element('span', {'class': 'btn btn-danger btn-xs'})
							.update( new Element('span', {'class': 'glyphicon glyphicon-trash'}) )
							.observe('click', function (){
								if(row)
									tmp.me._confirmDelRow(this);
							})
					) ) })
				})
			});
		if(row) {
			tmp.newDiv.store('data', row);
			if(row.id)
				tmp.newDiv.writeAttribute('item_id', row.id);
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
		tmp.newRow.down('.unitPrice').update(tmp.me._getUnitPriceCell());
		tmp.newRow.down('.qty').update(tmp.me._getQtyCell());
		tmp.newRow.down('.totalPrice').update(tmp.me.getCurrency(0));
		tmp.newRow.down('.btns').update(new Element('span', {'class': 'btn btn-primary btn-sm row-field-save-btn', 'valid-target': 'input-row-field'})
			.update(new Element('i', {'class': 'glyphicon glyphicon-floppy-saved'}))
		);
		return tmp.newRow;
	}
	/**
	 * adding a new component row
	 */
	,_addNewRow: function(data) {
		var tmp = {};
		tmp.me = this;
		tmp.list = $$('.kit-components-list').first().down('.item_row_footer');
		if(!tmp.list)
			return tmp.me;
		tmp.list.insert({'before': tmp.me._getProductRow(false, data).addClassName('item_data_row')});
		tmp.me._recalculateSummary();
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
			            },
			            regexp: {
	                        regexp: /^\d+$/,
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
		jQuery.each(tmp.fields, function(fieldName, options) {
			tmp.jQueryForm.formValidation('addField', fieldName, options);
		});
		tmp.jQueryForm.find('[row-field="product-id"]').on('change', function(){
			tmp.jQueryForm.formValidation('revalidateField', 'rowField[productId]');
		});
		if(!jQuery('#' + $(btn).id).hasClass('loaded')) {
			tmp.jQueryForm.on('click', '#' + $(btn).id, function(e) {
				tmp.inputRow = $(btn).up('.item_row_new');
				tmp.formValidator = tmp.jQueryForm.data('formValidation');
	            jQuery.each(jQuery('.form-control'), function(index, element){
	            	tmp.fieldName = jQuery(element).attr('name');
	            	if(tmp.formValidator.getFieldElements(tmp.fieldName).length > 0) {
	            		tmp.formValidator.enableFieldValidators(tmp.fieldName, jQuery(element).hasClass($(btn).readAttribute('valid-target')) );
	            		console.debug(jQuery(element));
	            		console.debug(jQuery(element).hasClass($(btn).readAttribute('valid-target')));
	            	}
	            });
	            tmp.formValidator.validate();
	            if(tmp.formValidator.isValid() === true) {
	            	tmp.productSelectBox = tmp.inputRow.down('[row-field="product-id"]');
	            	tmp.me._signRandID(tmp.productSelectBox);
	            	tmp.unitPriceBox = tmp.inputRow.down('[row-field="unit-price"]');
	            	tmp.qtyBox = tmp.inputRow.down('[row-field="qty"]');
	            	tmp.me._addNewRow({
	            		'product': jQuery('#' + tmp.productSelectBox.id).select2('data').data,
	            		'unitPrice': tmp.me.getValueFromCurrency($F(tmp.unitPriceBox)),
	            		'qty': $F(tmp.qtyBox)
	            	});
	            	tmp.jQueryForm.bootstrapValidator('resetForm');
	            	jQuery('#' + tmp.productSelectBox.id).select2('val', '');
	            	tmp.unitPriceBox.setValue(tmp.me.getCurrency(0, ''));
	            	tmp.qtyBox.setValue(1);
	            	tmp.productSelectBox.up('.product').down('.product-details').remove();
	            }
	        });
			jQuery('#' + $(btn).id).addClass('loaded');
		}
		return tmp.me;
	}
	,_preSave: function(btn) {
		var tmp = {};
		tmp.me = this;
		tmp.productId = ((tmp.me._item.product && tmp.me._item.product.id ) ? tmp.me._item.product.id : $F($$('[save-panel="kit-product-id"]').first()));
		if(tmp.productId.blank()) {
			tmp.me.showModalBox('<strong class="text-danger">Error:</strong>', new Element('div')
				.update(tmp.me.getAlertBox('', 'You need to provide a product for the kit.').addClassName('alert-danger'))
				.insert({'bottom': new Element('div', {'class': 'row'}).update(new Element('span', {'class': 'btn btn-primary col-xs-4 col-xs-offset-4'})
					.update('OK')
					.observe('click', function(){
						tmp.me.hideModalBox();
					})
				) })
			);
			return tmp.me;
		}
		tmp.summary = tmp.me._getSummary();
		tmp.hasItems = false;
		if(tmp.summary.rowData) {
			tmp.summary.rowData.each(function(rowData){
				if(rowData.active === true)
					tmp.hasItems = true;
			});
		}
		if(tmp.hasItems !== true) {
			tmp.me.showModalBox('<strong class="text-danger">Error:</strong>', new Element('div')
				.update( tmp.me.getAlertBox('', 'Required at least one component to build a kit.').addClassName('alert-danger') )
				.insert({'bottom': new Element('div', {'class': 'row'}).update(new Element('span', {'class': 'btn btn-primary col-xs-4 col-xs-offset-4'})
					.update('OK')
					.observe('click', function(){
						tmp.me.hideModalBox();
					})
				) })
			);
			return tmp.me;
		}
		tmp.data = {'items': tmp.summary.rowData, 'productId': tmp.productId};
		tmp.taskIdBox = $$('[save-panel="task-id"]').first();
		if(tmp.taskIdBox && !$F(tmp.taskIdBox).blank()) {
			tmp.data.taskId = $F(tmp.taskIdBox);
		}
		tmp.me.saveItem(btn, tmp.data, function(data){
			tmp.me.refreshParentWindow(data.item);
			tmp.me._item = data.item;
			if(data.url && !data.url.blank()) {
				window.location = data.url;
				return;
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
			.insert({'bottom': tmp.summaryRow = tmp.me._getProductRow(true).addClassName('item_row_footer list-group-item-success') });
		tmp.summaryRow.down('.product').update('');
		tmp.summaryRow.down('.row-details').update(new Element('div')
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('div', {'class': 'col-xs-8 text-right'}).update('<strong>Total Price(Exc. GST):</strong>') })
				.insert({'bottom': new Element('div', {'class': 'col-xs-2 text-right', 'summary': 'total-excl-gst'}).update(tmp.me.getCurrency(0)) })
			})
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('div', {'class': 'col-xs-8 text-right'}).update('<strong>Total GST:</strong>') })
				.insert({'bottom': new Element('div', {'class': 'col-xs-2 text-right', 'summary': 'total-gst'}).update(tmp.me.getCurrency(0)) })
			})
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('div', {'class': 'col-xs-8 text-right'}).update('<strong>Total Price(Inc. GST):</strong>') })
				.insert({'bottom': new Element('div', {'class': 'col-xs-2 text-right', 'summary': 'total-inc-gst'}).update(tmp.me.getCurrency(0)) })
			})
		);
		tmp.detailsDiv.update(new Element('h4').update('List of Parts Inside This Kit:'))
			.insert({'bottom': tmp.newTable})
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('span', {'class': 'btn btn-primary col-md-4 col-md-push-8'})
					.update('Save')
					.observe('click', function(){
						tmp.me._preSave(this);
					})
				})
				.insert({'bottom': new Element('span', {'class': 'col-md-8 col-md-pull-4'})
				.	insert({'bottom': tmp.me._item.id ? new Element('div', {'class': 'comments-div'}) : '' })
				})
			});
		tmp.me._initProductSearch()
			._initInputRowValidation( tmp.inputRow.down('.row-field-save-btn') );
		return tmp.me;
	}
	,_getExtraInfoDiv: function() {
		var tmp = {};
		tmp.me = this;
		if(!tmp.me._item.id)
			return '';
		tmp.newDiv = new Element('div', {'class': 'row'})
			.insert({'bottom': !(tmp.me._item.soldToCustomer && tmp.me._item.soldToCustomer.id) ? '' : new Element('div', {'class': 'col-md-3'})
				.insert({'bottom': tmp.me.getFormGroup(new Element('label').update('Sold to Customer: '), 
						new Element('div', {'class': 'form-control input-sm'}).update(
							new Element('a', {'href': 'javascript:void(0);'}).update(tmp.me._item.soldToCustomer.name)
								.observe('click', function(){
									tmp.me._openURL('/customer/' + tmp.me._item.soldToCustomer.id + '.html?blanklayout=1');
								})
						)
				) })
			})
			.insert({'bottom': !tmp.me._item.soldDate ? '' : new Element('div', {'class': 'col-md-2'})
				.insert({'bottom': tmp.me.getFormGroup(new Element('label').update('Sold Time: '), 
						new Element('div', {'class': 'form-control input-sm'}).update(
							moment(tmp.me.loadUTCTime(tmp.me._item.soldDate)).format('lll')
						)
				) })
			})
			.insert({'bottom': !tmp.me._item.soldOnOrder ? '' : new Element('div', {'class': 'col-md-4'})
				.insert({'bottom': tmp.me.getFormGroup(new Element('label').update('Sold on Order: '), 
					new Element('div', {'class': 'form-control input-sm order_item'})
						.insert({'bottom': new Element('div', {'class': 'col-md-3'})
							.insert({'bottom': new Element('a', {'href': 'javascript:void(0);'}).update(tmp.me._item.soldOnOrder.orderNo)
								.observe('click', function(){
									tmp.me._openURL('/orderdetails/' + tmp.me._item.soldOnOrder.id + '.html?blanklayout=1');
								})
							})
						})
						.insert({'bottom': new Element('div', {'class': 'col-md-3', 'order_status': tmp.me._item.soldOnOrder.status.name}).update(tmp.me._item.soldOnOrder.status.name) })
						.insert({'bottom': new Element('div', {'class': 'col-md-6 truncate', 'title': tmp.me._item.soldOnOrder.customer.name}).update(
							new Element('a', {'href': 'javascript:void(0);'}).update(tmp.me._item.soldOnOrder.customer.name)
								.observe('click', function(){
									tmp.me._openURL('/customer/' + tmp.me._item.soldOnOrder.customer.id + '.html?blanklayout=1');
								})
						) })
				) })
			})
			.insert({'bottom': !(tmp.me._item.shippment && tmp.me._item.shippment.id) ? '' : new Element('div', {'class': 'col-md-3'})
				.insert({'bottom': tmp.me.getFormGroup(new Element('label').update('Shipped Via: '), 
					new Element('div', {'class': 'form-control input-sm'})
						.insert({'bottom': new Element('div', {'class': 'col-md-3 truncate', 'title': tmp.me._item.shippment.courier.name}).update(tmp.me._item.shippment.courier.name) })
						.insert({'bottom': new Element('div', {'class': 'col-md-3 truncate', 'title': 'Con. No.:' + tmp.me._item.shippment.conNoteNo}).update(tmp.me._item.shippment.conNoteNo) })
						.insert({'bottom': new Element('div', {'class': 'col-md-6', 'title': 'shipped on date'}).update( moment(tmp.me.loadUTCTime(tmp.me._item.shippment.shippingDate)).format('lll') ) })
				) })
			})
			;
		return tmp.newDiv;
	}
	/**
	 * Getting the item div row
	 */
	,_getItemDiv: function() {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = new Element('div', {'class': 'save-panel'})
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('div', {'class': 'col-md-4 col-md-offset-4'})
					.insert({'bottom': new Element('h3', {'class': 'text-center'}).update((tmp.me._item.barcode && !tmp.me._item.barcode.blank()) ?
							'Editing KIT: <img src="/asset/renderBarcode?text=' + tmp.me._item.barcode + '" alt="' + tmp.me._item.barcode + '" title="' + tmp.me._item.barcode + '"/>'
							: 'Building New Kit'
					) })
				})
				.insert({'bottom': new Element('div', {'class': 'col-md-2 col-md-offset-2'})
					.insert({'bottom': tmp.me.getFormGroup(new Element('label').update('For Task: '),
							new Element('input', {'class': 'form-control select2 input-sm task-search', 'save-panel': 'task-id', 'placeholder': 'For Task.'})
					) })
				})
			})
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('div', {'class': 'form-horizontal'})
					.insert({'bottom':  tmp.me.getFormGroup(new Element('label').update(tmp.me._item.id ? 'Kit Product: ' : 'Building a kit as: ').addClassName('col-md-1 col-sm-2'),
						new Element('div', {'class': 'col-md-11 col-sm-10 rm-form-control kit-product-div'}).update(tmp.me._item.id ? '' : 
							new Element('input', {'class': 'form-control select2 input-sm product-search', 'save-panel': 'kit-product-id', 'isKit': '1', 'onSelectFunc': '_selectKitProduct'})
						)
					) })
				})
			})
			.insert({'bottom':  tmp.me._getExtraInfoDiv() })
			.insert({'bottom':  new Element('div', {'id': tmp.me.getHTMLID('kitsDetailsDiv')})})
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
				.insert({'bottom': new Element('div')
					.insert({'bottom': new Element('div', {'class': 'col-xs-8'}).update(product.name)			})
					.insert({'bottom': new Element('div', {'class': 'col-xs-2'})
						.insert({'bottom': new Element('div', {'class': 'col-xs-4 text-right'}).update('<strong>Brand</strong>:')})
						.insert({'bottom': new Element('div', {'class': 'col-xs-8 '}).update(product.manufacturer ? product.manufacturer.name : '')})
					})
					.insert({'bottom': new Element('div', {'class': 'col-xs-2'})
						.insert({'bottom': new Element('div', {'class': 'col-xs-4 text-right'}).update('<strong>sku</strong>:')})
						.insert({'bottom': new Element('div', {'class': 'col-xs-8 truncate'})
							.setStyle('max-width:none;')
							.insert({'bottom': new Element('a', {'href': 'javascript: void(0);', 'title': product.sku}).update(product.sku) 
								.observe('click', function(){
									tmp.me._openURL('/product/' + product.id + '.html?blanklayout=1');
								})
							})
						})
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
		tmp.unitPrice = tmp.me.getValueFromCurrency(tmp.me._getUnitPrice(product));
		tmp.rowDiv.down('[row-field="qty"]').setValue(1);
		tmp.rowDiv.down('.totalPrice').update(tmp.me.getCurrency(tmp.unitPrice));
		tmp.rowDiv.down('[row-field="unit-price"]').setValue(tmp.me.getCurrency(tmp.unitPrice, ''));
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
		tmp.select2 = jQuery('.select2.product-search:not(.loaded)').addClass('loaded');
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
	,_initTaskSearch: function() {
		var tmp = {};
		tmp.me = this;
		tmp.pageSize = 10;
		tmp.select2 = jQuery('.select2.task-search:not(.loaded)').addClass('loaded');
		tmp.select2.select2({
			 minimumInputLength: 1,
			 allowClear: true,
			 data: [],
			 ajax: {
				 url: "/ajax/getAll",
				 dataType: 'json',
				 quietMillis: 250,
				 data: function (term, page) { // page is the one-based page number tracked by Select2
					 return {
						 'entityName': 'Task',
						 'searchTxt': '(id like :searchTxt)', //search term
						 'searchParams': {'searchTxt': '%' + term + '%'},
						 'pageNo': page, // page number
						 'pageSize': tmp.pageSize
					 };
				 },
				 results: function (data, page) {
					tmp.result = [];
					data.resultData.items.each(function(item){
						tmp.result.push({"id": item.id, 'text': item.id + ' [' + item.status.name + ']', 'data': item});
					})
					return {
						results:  tmp.result,
						more: (page * tmp.pageSize) < data.resultData.pagination.totalRows
					};
				}
			 }
		});
		if(tmp.me._item.task && tmp.me._item.task.id){
			tmp.select2.select2('data', {"id": tmp.me._item.task.id, 'text': tmp.me._item.task.id + ' [' + tmp.me._item.task.status.name + ']', 'data': tmp.me._item.task});
		}
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
		if(tmp.me._item.product && tmp.me._item.product.id){
			tmp.me._selectKitProduct(tmp.me._item.product);
			if(tmp.me._item.components && tmp.me._item.components.size() > 0) {
				tmp.me._item.components.each(function(component){
					tmp.me._addNewRow(component, tmp.newTable);
				});
			}
		} else {
			tmp.me._initProductSearch();
		}
		tmp.me._initTaskSearch()
			._initFormValidation();
		
		if(tmp.div.down('.comments-div')) {
			tmp.div.down('.comments-div')
				.store('CommentsDivJs', new CommentsDivJs(tmp.me, 'Kit', tmp.me._item.id)
					._setDisplayDivId(tmp.div.down('.comments-div'))
					.render()
				);
		}
		return tmp.me;
	}
});