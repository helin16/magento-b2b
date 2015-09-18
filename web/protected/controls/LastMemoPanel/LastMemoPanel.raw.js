var LastMemoPanelJs = new Class.create();
LastMemoPanelJs.prototype = {
	_pageJs : null
	,_entityName: null
	,_entityId: null
	,_canEdit: false
	,_panelHTMLID: ''
	/**
	 * constructor
	 */
	,initialize : function(_pageJs, _entityName, _entityId, _canEdit) {
		this._pageJs = _pageJs;
		this._panelHTMLID = 'LastMemoPanelJs_' + String.fromCharCode(65 + Math.floor(Math.random() * 26)) + Date.now();
		this._entityName = _entityName;
		this._entityId = _entityId;
		this._canEdit = (_canEdit || this._canEdit);
	}
	/**
	 * Setter for the AfterAddFunction
	 */
	,setAfterAddFunc: function(_afterAddFunc) {
		var tmp = {};
		tmp.me = this;
		tmp.me._afterAddFunc = _afterAddFunc;
		return tmp.me;
	}
	/**
	 *
	 */
	,_getPanel: function() {
		 var tmp = {};
		 tmp.me = this;
		 tmp.newDiv = new Element('div', {'class': 'memo-wrapper', 'id': tmp.me._panelHTMLID});
		 return tmp.newDiv;
	}
	/**
	 * Ajax: submitting the memo
	 */
	,_submitMemo: function(btn) {
		 var tmp = {};
		 tmp.me = this;
		 tmp.newMemoDiv = $(btn).up('.new-memo-div');
		 tmp.data = tmp.me._pageJs._collectFormData(tmp.newMemoDiv, 'new-memo');
		 if(tmp.data === null)
			 return tmp.me;
		 tmp.oldData = tmp.newMemoDiv.retrieve('data');
		 if( tmp.oldData && tmp.oldData.id && tmp.oldData.comments === tmp.data.comments) {
			 $(tmp.me._panelHTMLID).update(tmp.me._getMemoDiv(tmp.oldData));
			 return tmp.me;
		 }

		 tmp.newMemoDiv.hide();
		 tmp.me._pageJs.postAjax(LastMemoPanelJs.callbackIds.addMemo, {'entity': tmp.me._entityName, 'entityId': tmp.me._entityId, 'data': tmp.data}, {
			 'onSuccess': function (sender, param) {
		 		 try {
		 			 tmp.result = tmp.me._pageJs.getResp(param, false, true);
		 			 if(!tmp.result || !tmp.result.item)
		 				 return;
		 			$(tmp.me._panelHTMLID).update(tmp.me._getMemoDiv(tmp.result.item));
		 			if(typeof(tmp.me._afterAddFunc) === 'function')
						tmp.me._afterAddFunc(tmp.result.item);
		 		 } catch (e) {
		 			tmp.newMemoDiv.insert({'top': tmp.me._pageJs.getAlertBox('', e).addClassName('alert-danger').addClassName('.msg') });
		 			tmp.newMemoDiv.show();
		 		 }
		 	 }
		 	 , 'onComplete': function() {
		 		 if($(btn).up('.new-memo-div'))
		 			$(btn).up('.new-memo-div').show();
		 	 }
		 })
		 return tmp.me;
	}
	/**
	 * Getting the create/edit memo div
	 */
	,_getAddMemoDiv: function(comment) {
		 var tmp = {};
		 tmp.me = this;
		 tmp.newDiv = new Element('div', {'class': 'panel-body new-memo-div'})
		 	 .store('data', comment)
			 .insert({'bottom': new Element('div', {'class': 'form-group'})
				 .insert({'bottom': tmp.me._entityId && !tmp.me._entityId.blank() ? '' : new Element('small', {'class': 'pull-right'}).update('Please click the save btn down to save this memo') })
				 .insert({'bottom': new Element('label', {'class': 'control-label'})
				 	.update('Memo :')
				 })
				 .insert({'bottom':  new Element('div', {'class': 'input-group'})
					 	.insert({'bottom': new Element('input', {'new-memo': 'comments', 'required': true, 'class': 'form-control', 'value': comment && comment.comments ? comment.comments : ''})
					 		.observe('keydown', function(event) {
					 			tmp.btn = $(this).up('.new-memo-div').down('.submit-btn');
					 			tmp.me._pageJs.keydown(event, function() {
					 				tmp.btn.click();
					 			})
					 		})
					 	})
					 	.insert({'bottom': tmp.me._entityId.blank() ? '' : new Element('div', {'class': 'input-group-btn'})
						 	.insert({'bottom': new Element('div', {'class': 'btn btn-primary submit-btn'})
						 		.update('Save')
						 		.observe('click', function() {
					 				tmp.me._submitMemo(this);
					 			})
						 	})
					 	})
					 	.insert({'bottom': new Element('div', {'class': 'input-group-btn'})
					 		.insert({'bottom': new Element('div', {'class': 'btn btn-default'})
					 			.update('Cancel')
					 			.observe('click', function() {
					 				$(tmp.me._panelHTMLID).update(tmp.me._getMemoDiv(comment));
					 			})
					 		})
					 	})
				 })
			 });
		 return tmp.newDiv;
	}
	/**
	 * Getting the add memo btn
	 */
	,_getAddMemoBtn: function() {
		var tmp = {};
		tmp.me = this;
		if(tmp.me._canEdit !== true)
			return '';
		tmp.newDiv = new Element('div')
			.insert({'bottom': new Element('div', {'class': 'btn btn-success btn-xs', 'title': 'Click here to create a memo.'})
				.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-plus'}) })
				.insert({'bottom': new Element('span').update(' Create a Memo') })
				.observe('click', function(){
					$(tmp.me._panelHTMLID).update(tmp.me._getAddMemoDiv());
				})
			});
		return tmp.newDiv;
	}
	/**
	 * Getting the memo div
	 */
	,_getMemoDiv: function(comment) {
		var tmp = {};
		tmp.me = this;
		if(!comment || !comment.id || !comment.comments)
			return tmp.me._getAddMemoBtn();
		tmp.newDiv = new Element('div', {'class': 'panel-body'})
			.insert({'bottom': new Element('h4', {'class': 'memo-content'})
				.update(tmp.me._pageJs.getAlertBox('Memo: ', comment.comments).addClassName('alert-danger') )
			});
		if(tmp.me._canEdit === true) {
			tmp.newDiv.writeAttribute('title', 'Double click the memo text to edit change it')
				.down('.memo-content')
				.setStyle('cursor: pointer')
				.observe('dblclick', function() {
					$(tmp.me._panelHTMLID).update(tmp.me._getAddMemoDiv(comment));
				});
		}
		return tmp.newDiv;
	}
	/**
	 * Showing the memo
	 */
	,_showMemo: function() {
		 var tmp = {};
		 tmp.me = this;
		 if(!tmp.me._entityId || tmp.me._entityId.blank()) {
			 $(tmp.me._panelHTMLID).update(tmp.me._getMemoDiv({}));
			 return tmp.me;
		 }

		 tmp.loadingDiv = new Element('div')
		 	.insert({'bottom': tmp.me._pageJs.getLoadingImg().removeClassName('fa-5x') })
		 	.insert({'bottom': new Element('span').update(' Loading Memo...') });
		 if($(tmp.me._panelHTMLID))
			 $(tmp.me._panelHTMLID).update(tmp.loadingDiv);
		 tmp.ajax = new Ajax.Request('/ajax/getComments', {
				method: 'get'
				,parameters: {'entity': tmp.me._entityName, 'entityId': tmp.me._entityId, 'orderBy': {'id':'desc'}, 'pageNo': 1, 'pageSize': 1, 'type': 'MEMO'}
				,'onSuccess': function(transport) {
					try {
						tmp.result = tmp.me._pageJs.getResp(transport.responseText, false, true);
						if(!tmp.result || !tmp.result.items)
							return;
						$(tmp.me._panelHTMLID).update(tmp.me._getMemoDiv(tmp.result.items.size() === 0 ? undefined : tmp.result.items[0]));
					} catch (e) {
						$(tmp.me._panelHTMLID).update(tmp.me.getAlertBox('ERROR: ', e).addClassName('alert-danger'));
					}
				}
				,onComplete: function() {
					tmp.loadingDiv.remove();
				}
			});
		 return tmp.me;
	}

	,load: function() {
		 var tmp = {};
		 tmp.me = this;
		 //check whther the pament list panel is loaded.
		 if($(tmp.me._panelHTMLID)) {
			 tmp.me._showMemo();
		 }
		 return tmp.me;
	}
};