/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new DetailsPageJs(), {
	_htmlIds: {}
	,setTaskStatuses: function(_statuses) {
		var tmp = {};
		tmp.me = this;
		tmp.me._statuses = _statuses;
		return tmp.me;
	}
	,_getItemDiv: function() {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = new Element('div', {'class': 'task-details-wrapper'})
			.insert({'bottom': new Element('div')
				.insert({'bottom': new Element('div', {'class': 'col-sm-2'})
					.insert({'bottom': tmp.me.getFormGroup(new Element('label').update('Task No: '),
							tmp.me._item.id ? new Element('h4').update(tmp.me._item.id) : new Element('small').update('Will Auto Generate after save')
					) })
				})
				.insert({'bottom': new Element('div', {'class': 'col-sm-2'})
					.insert({'bottom': tmp.me.getFormGroup(new Element('label').update('Due Date: '),
						new Element('input', {'class': 'form-control datepicker', 'save-panel': 'dueDate', 'value': tmp.me._item.id ? moment(tmp.me._item.dueDate).toDate().format('DD/MM/YYYY') : ''})
					) })
				})
				.insert({'bottom': new Element('div', {'class': 'col-sm-1'})
					.insert({'bottom': tmp.me.getFormGroup(new Element('label').update('Status: '),
						tmp.statusList = new Element('select', {'class': 'form-control select2', 'save-panel': 'statusId'})
					) })
				})
				.insert({'bottom': new Element('div', {'class': 'col-sm-2'})
					.insert({'bottom': tmp.me.getFormGroup(new Element('label').update('Technician: '),
						new Element('input', {'class': 'form-control select2', 'save-panel': 'techId'})
					) })
				})
				.insert({'bottom': new Element('div', {'class': 'col-sm-5'})
					.insert({'bottom': tmp.me.getFormGroup(new Element('label').update('From Order: '),
						(tmp.me._item.order && tmp.me._item.order.id ? new Element('a').update(tmp.me._item.order.orderNo) : new Element('input', {'class': 'form-control select2', 'save-panel': 'orderId'}))
					) })
				})
			})
			.insert({'bottom': new Element('div')
				.insert({'bottom': new Element('div', {'class': 'col-sm-12'})
					.insert({'bottom': tmp.me.getFormGroup(new Element('label').update('Instructions: '),
						new Element('textarea', {'class': 'form-control select2', 'save-panel': 'instructions', 'rows': 10})
					) })
				})
			})
			.insert({'bottom': new Element('div')
				.insert({'bottom': new Element('div', {'class': 'col-sm-12'})
					.insert({'bottom': tmp.me._item.id ? new Element('div', {'class': 'comments-div'}) : '' })
				})
			});
		tmp.me._statuses.each(function(status){
			tmp.option = new Element('option', {'value': status.id}).update(status.name);
			if(tmp.me._item.id && tatus.id === tmp.me._item.status.id)
				tmp.option.writeAttribute('selected', true);
			tmp.statusList.insert({'bottom': tmp.option});

		});
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
	,_init: function(){
		var tmp = {};
		tmp.me = this;
		return tmp.me;
	}
	,load: function () {
		var tmp = {};
		tmp.me = this;
		tmp.me._init();
		$(tmp.me._htmlIds.itemDiv).update(tmp.div = tmp.me._getItemDiv());
		jQuery('.datepicker').datetimepicker({
			format: 'DD/MM/YYYY'
		});
		tmp.me._loadRichTextEditor(tmp.div.down('[save-panel="instructions"]'));
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