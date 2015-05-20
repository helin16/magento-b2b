/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new DetailsPageJs(), {
	/**
	 * Setting the task statuses
	 */
	setTaskStatuses: function(_statuses) {
		var tmp = {};
		tmp.me = this;
		tmp.me._statuses = _statuses;
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
	,_preSubmit: function (btn) {
		var tmp = {};
		tmp.me = this;
		if(!$(btn) || !$(btn).hasClassName('save-btn'))
			return tmp.me;
		tmp.data = {};
		if(tmp.me._item.id)
			tmp.data.id = tmp.me._item.id;
		$(btn).up('.task-details-wrapper').getElementsBySelector('[save-panel]').each(function(item){
			tmp.field = item.readAttribute('save-panel');
			if(item.hasClassName('datepicker')) {
				tmp.me._signRandID(item);
				tmp.value = jQuery('#' + item.id).data('DateTimePicker').date().utc().format();
			} else {
				if(item.hasClassName('rich-text')) {
					item.retrieve('editor').toggle();
					item.retrieve('editor').toggle();
				}
				tmp.value = $F(item);
			}
			tmp.data[tmp.field] = tmp.value;
		});
		tmp.me.saveItem(btn, tmp.data, function(data) {
			tmp.me.refreshParentWindow(data.item);
			tmp.me.showModalBox('<strong class="text-success">Saved</strong>', '<h4 class="text-success">Task (' + data.item.id + ') has been saved successfully</h4>');
			window.location = data.url;
		});
		return tmp.me;
	}
	/**
	 * Getting the item div
	 */
	,_getItemDiv: function() {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = new Element('div', {'class': 'task-details-wrapper'})
			.insert({'bottom': new Element('div', {'class': 'row text-center'})
				.insert({'bottom': new Element('h3').update(!tmp.me._item.id ? 'Creating a new Task' : 'Editing Task :' + tmp.me._item.id) })
			})
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('div', {'class': 'col-sm-3'})
					.insert({'bottom': tmp.me.getFormGroup(new Element('label').update('Customer: '),
							new Element('input', {'class': 'form-control select2', 'save-panel': 'customerId', 'name': 'customer', 'placeholder': 'search a customer name here'})
					) })
				})
				.insert({'bottom': new Element('div', {'class': 'col-sm-2'})
					.insert({'bottom': tmp.me.getFormGroup(new Element('label').update('Due Date: '),
						new Element('input', {'class': 'form-control datepicker', 'save-panel': 'dueDate', 'name': 'dueDate', 'value': tmp.me._item.id ? moment(tmp.me.loadUTCTime(tmp.me._item.dueDate)).format('DD/MM/YYYY hh:mm A') : ''})
					) })
				})
				.insert({'bottom': !tmp.me._item.id ? '' :  new Element('div', {'class': 'col-sm-1'})
					.insert({'bottom': tmp.me.getFormGroup(new Element('label').update('Status: '),
						tmp.statusList = new Element('select', {'class': 'form-control select2', 'save-panel': 'statusId'})
					) })
				})
				.insert({'bottom': new Element('div', {'class': 'col-sm-2'})
					.insert({'bottom': tmp.me.getFormGroup(new Element('label').update('Technician: '),
						new Element('input', {'class': 'form-control select2', 'save-panel': 'techId', 'placeholder': 'search a tech name here'})
					) })
				})
				.insert({'bottom': new Element('div', {'class': !tmp.me._item.id ? 'col-sm-5' : 'col-sm-4'})
					.insert({'bottom': tmp.me.getFormGroup(new Element('label').update('From Order: '),
						(tmp.me._item.order && tmp.me._item.order.id ? new Element('a').update(tmp.me._item.order.orderNo) : new Element('input', {'class': 'form-control select2', 'save-panel': 'orderId', 'placeholder': 'search a Order here'}))
					) })
				})
			})
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('div', {'class': 'col-sm-12'})
					.insert({'bottom': tmp.me.getFormGroup(new Element('label').update('Instructions: '),
						new Element('textarea', {'class': 'form-control rich-text', 'save-panel': 'instructions', 'rows': 10, 'name': 'instructions'}).update(!tmp.me._item.id ? '' : tmp.me._item.instructions)
					) })
				})
			})
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('div', {'class': 'col-sm-12'})
					.insert({'bottom': tmp.me._item.id ? new Element('div', {'class': 'comments-div'}) : '' })
				})
			})
			.insert({'bottom': new Element('div')
				.insert({'bottom': tmp.submitBtn = new Element('button', {'type': 'submit', 'class': 'btn btn-primary col-sm-3 col-sm-offset-9 save-btn'})
					.update('save')
				})
			});
		tmp.me._signRandID(tmp.submitBtn);
		if(tmp.statusList) {
			tmp.me._statuses.each(function(status){
				tmp.option = new Element('option', {'value': status.id}).update(status.name);
				if(tmp.me._item.id && status.id === tmp.me._item.status.id)
					tmp.option.writeAttribute('selected', true);
				tmp.statusList.insert({'bottom': tmp.option});

			});
		}
		return tmp.newDiv;
	}
	/**
	 * Loading/Bind js to a textare to load rich Text editor
	 */
	,_loadRichTextEditor: function(input) {
		var tmp = {};
		tmp.me = this;
		tmp.me._signRandID(input);
		tmp.editor = new TINY.editor.edit('editor',{
			id: input.id,
			width: '100%',
			height: 180,
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
	/**
	 * format the suggestions for the order from Select2
	 */
	,_formatOrderSelection: function(order) {
		return '<div class="row order_item"><div class="col-xs-3">' + order.orderNo + '</div><div class="col-xs-3" order_status="' + order.status.name + '">' + order.status.name + '</div><div class="col-xs-6"><small>' + ((order.customer && order.customer.name) ? order.customer.name : '') + '</small></div></div >';
	}
	/**
	 * init the customer search box
	 */
	,_initCustomerSearchBox: function() {
		var tmp = {};
		tmp.me = this;
		jQuery('[save-panel="customerId"]').select2({
			minimumInputLength: 3,
			multiple: false,
			ajax: {
				delay: 250
				,url: '/ajax/getAll'
				,type: 'POST'
				,data: function (params) {
					return {"searchTxt": 'name like ?', 'searchParams': ['%' + params + '%'], 'entityName': 'Customer', 'pageNo': 1};
				}
				,results: function(data, page, query) {
					tmp.result = [];
					if(data.resultData && data.resultData.items) {
						data.resultData.items.each(function(item){
							tmp.result.push({'id': item.id, 'text': item.name, 'data': item});
						});
					}
					return { 'results' : tmp.result };
				}
			}
			,cache: true
			,escapeMarkup: function (markup) { return markup; } // let our custom formatter work
		});
		if(tmp.me._item.id && tmp.me._item.customer && tmp.me._item.customer.id) {
			jQuery('[save-panel="customerId"]').select2('data', {'id': tmp.me._item.customer.id, 'text': tmp.me._item.customer.name, 'data': tmp.me._item.customer});
		}
		return tmp.me;
	}
	/**
	 * init order search box
	 */
	,_initOrderSearchBox: function() {
		var tmp = {};
		tmp.me = this;
		jQuery('[save-panel="orderId"]').select2({
			 minimumInputLength: 3
			 ,multiple: false
			 ,allowClear: true
			 ,ajax: {
				 delay: 250
				 ,url: '/ajax/getAll'
		         ,type: 'POST'
	        	 ,data: function (params) {
	        		 return {"searchTxt": 'orderNo like ?', 'searchParams': ['%' + params + '%'], 'entityName': 'Order', 'pageNo': 1};
	        	 }
				 ,results: function(data, page, query) {
					 tmp.result = [];
					 if(data.resultData && data.resultData.items) {
						 data.resultData.items.each(function(item){
							 tmp.result.push({'id': item.id, 'text': item.orderNo, 'data': item});
						 });
					 }
		    		 return { 'results' : tmp.result };
		    	 }
			 }
			,cache: true
			,formatResult : function(result) {
				 return (!result) ? '' : tmp.me._formatOrderSelection(result.data);
			 }
			,formatSelection: function(result){
				return (!result) ? '' : tmp.me._formatOrderSelection(result.data);
			 }
			 ,escapeMarkup: function (markup) { return markup; } // let our custom formatter work
		});
		if(tmp.me._item.id && tmp.me._item.fromEntity && tmp.me._item.fromEntity.id && tmp.me._item.fromEntityName === 'Order') {
			jQuery('[save-panel="orderId"]').select2('data', {'id': tmp.me._item.fromEntity.id, 'text': tmp.me._item.fromEntity.orderNo, 'data': tmp.me._item.fromEntity});
		}
		return tmp.me;
	}
	,_initTechSearchBox: function() {
		var tmp = {};
		tmp.me = this;
		jQuery('[save-panel="techId"]').select2({
			minimumInputLength: 3
			,multiple: false
			,allowClear: true
			,hidden: true
			,ajax: {
				delay: 250
				,url: '/ajax/getAll'
				,type: 'POST'
				,data: function (params) {
					return {
						'searchTxt': 'personId in (select id from person p where concat(p.firstName, " ", p.lastName) like ?)',
						'searchParams': ['%' + params + '%'],
						'entityName': 'UserAccount',
						'pageNo': 1
					};
				}
				,results: function(data, page, query) {
					tmp.result = [];
					if(data.resultData && data.resultData.items) {
						data.resultData.items.each(function(item){
							tmp.result.push({'id': item.id, 'text': item.person.fullname, 'data': item});
						});
					}
					return { 'results' : tmp.result };
				}
			}
			,cache: true
			,escapeMarkup: function (markup) { return markup; } // let our custom formatter work
		});
		if(tmp.me._item.id && tmp.me._item.technician && tmp.me._item.technician.id) {
			jQuery('[save-panel="techId"]').select2('data', {'id': tmp.me._item.technician.id, 'text': tmp.me._item.technician.person.fullname, 'data': tmp.me._item.technician});
		}
		return tmp.me;
	}
	,_initFormValdation: function(btn, type) {
		var tmp = {};
		tmp.me = this;
		tmp.mainForm = jQuery('#' + tmp.me.getHTMLID('main-form'));
		tmp.mainForm.formValidation({
	        // I am validating Bootstrap form
	        framework: 'bootstrap',
	        icon: {
	            valid: 'glyphicon glyphicon-ok',
	            invalid: 'glyphicon glyphicon-remove',
	            validating: 'glyphicon glyphicon-refresh'
	        },
	        excluded: ':disabled',
	        fields: {
	            dueDate: {
	                validators: {
	                	callback: {
	                		message: 'The Due Date is required.',
	                		callback: function(value, validator, field) {
	                			return jQuery(field).data('DateTimePicker').date() !== null;
	                		}
	                	}
	                }
	            }
	        	,customer: {
	        		validators: {
		        		notEmpty: {
		        			message: 'The customer is required.'
		        		}
		        	}
		        }
	        }
		})
		.on('err.form.fv', function(e) {
			e.preventDefault();
            if (tmp.mainForm.data('formValidation').getSubmitButton()) {
            	tmp.mainForm.data('formValidation').disableSubmitButtons(false);
            }
        })
        .on('success.form.fv', function(e) {
        	e.preventDefault();
        	if (tmp.mainForm.data('formValidation').getSubmitButton()) {
        		tmp.mainForm.data('formValidation').disableSubmitButtons(false);
        	}
        	tmp.me._preSubmit(tmp.mainForm.data('formValidation').getSubmitButton().attr('id'));
        });
		tmp.mainForm.find('.datepicker').on('dp.change dp.show', function(e) {
			tmp.mainForm.formValidation('revalidateField', 'dueDate');
	    });
		tmp.mainForm.find('[name="customer"]').change(function(e) {
			tmp.mainForm.formValidation('revalidateField', 'customer');
		});
		return tmp.me;
	}
	,load: function () {
		var tmp = {};
		tmp.me = this;
		$(tmp.me.getHTMLID('itemDiv')).update(tmp.div = tmp.me._getItemDiv());
		jQuery('.datepicker').datetimepicker({
			format: 'DD/MM/YYYY hh:mm A'
		});
		tmp.me._loadRichTextEditor(tmp.div.down('[save-panel="instructions"]'))
			._initTechSearchBox()
			._initOrderSearchBox()
			._initCustomerSearchBox()
			._initFormValdation();
		if(tmp.div.down('.comments-div')) {
			tmp.div.down('.comments-div')
				.store('CommentsDivJs', new CommentsDivJs(tmp.me, 'Task', tmp.me._item.id)
					._setDisplayDivId(tmp.div.down('.comments-div'))
					.render()
				);
		}
		return tmp.me;
	}
});