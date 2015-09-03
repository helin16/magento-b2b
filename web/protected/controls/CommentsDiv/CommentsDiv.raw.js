/**
 * The display Comments div
 */
var CommentsDivJs = new Class.create();
CommentsDivJs.prototype = {
	SAVE_BTN_ID: ''
	,_pageJs: null

	//constructor
	,initialize : function(_pageJs, _entityName, _entityId, _pageSize, _displayDivId) {
		this._pageJs = _pageJs;
		this._entityName = _entityName;
		this._entityId = _entityId;
		this._displayDivId = _displayDivId;
		this._pageSize = (_pageSize || 5);
	}
	/**
	 * setting the display div id
	 */
	,_setDisplayDivId: function (_displayDivId) {
		var tmp = {};
		tmp.me = this;
		tmp.me._displayDivId = _displayDivId;
		return tmp.me;
	}
	/**
	 * Getting the comments row
	 */
	,_getCommentsRow: function(comments) {
		var tmp = {};
		tmp.me = this;
		tmp.tag = comments.id ? 'td' : 'th';
		tmp.newRow =  new Element('tr', {'class': 'comments_row'})
			.store('data', comments)
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-1'}).update(!comments.id ? comments.created : new Element('small').update(tmp.me._pageJs.loadUTCTime(comments.created).toLocaleString() ) ) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-1'}).update(!comments.id ? comments.createdBy.person.fullname :  new Element('small').update(comments.createdBy.person.fullname) ) })
			.insert({'bottom': new Element(tmp.tag, {'class': 'col-xs-1'}).update(!comments.id ? comments.type : new Element('small').update(comments.type) ) })
			.insert({'bottom': new Element(tmp.tag, {'class': ''}).update(!comments.id ? comments.comments : new Element('small').update( comments.comments) ) })
			;
		return tmp.newRow;
	}
	/**
	 * Ajax: getting the comments into the comments div
	 */
	,_getComments: function (pageNo, resultDivId, btn) {
		var tmp = {};
		tmp.me = this;
		tmp.pageNo = (pageNo || 1);
		tmp.loadingDiv = tmp.me._pageJs.getLoadingImg();
		tmp.btn = (btn ? $(btn) : undefined);
		tmp.ajax = new Ajax.Request('/ajax/getComments', {
			method: 'get'
			,parameters: {'entity': tmp.me._entityName, 'entityId': tmp.me._entityId, 'orderBy': {'created':'desc'}, 'pageNo': pageNo, 'pageSize': tmp.me._pageSize}
			,onCreate: function() {
				if(tmp.pageNo === 1) {
					$(resultDivId).update(tmp.loadingDiv);
				}
				if(tmp.btn) {
					tmp.me._pageJs._signRandID(tmp.btn);
					jQuery('#' + tmp.btn.id).button('loading');
				}
			}
			,onSuccess: function(transport) {
				try {
					if(tmp.pageNo === 1) {
						$(resultDivId).update('');
					} else {
						//remove the pagination btn
						if($(resultDivId).down('.comments_get_more_btn_div')) {
							$(resultDivId).down('.comments_get_more_btn_div').remove();
						}
					}
					tmp.tbody = $(resultDivId).down('tbody');
					if(!tmp.tbody) {
						$(resultDivId).insert({'bottom': new Element('table', {'class': 'table table-condensed table-hover'})
							.insert({'bottom': new Element('thead').update(tmp.me._getCommentsRow({'type': 'Type', 'createdBy': {'person': {'fullname': 'WHO'}}, 'created': 'WHEN', 'comments': 'COMMENTS'}) ) })
							.insert({'bottom': tmp.tbody = new Element('tbody') })
						});
					}
					tmp.result = tmp.me._pageJs.getResp(transport.responseText, false, true);
					if(!tmp.result || !tmp.result.items)
						return;

					//add new data
					tmp.result.items.each(function(item) {
						tmp.tbody.insert({'bottom': tmp.me._getCommentsRow(item) });
					})
					//who new pagination btn
					if(tmp.result.pageStats.pageNumber < tmp.result.pageStats.totalPages) {
						tmp.tbody.insert({'bottom': new Element('tr', {'class': 'comments_get_more_btn_div'})
							.insert({'bottom': new Element('td', {'colspan': 4})
								.insert({'bottom': new Element('span', {'class': 'btn btn-primary btn-xs', 'data-loading-text': 'Getting More ...'})
									.update('Get More Comments')
									.observe('click', function(){
										tmp.me._getComments(pageNo * 1 + 1, resultDivId, this);
									})
								})
							})
						})
					}
				} catch (e) {
					if(tmp.pageNo === 1) {
						$(resultDivId).insert({'bottom': tmp.me.getAlertBox('ERROR: ', e).addClassName('alert-danger') });
					} else {
						tmp.me._pageJs.showModalBox('<strong class="text-danger">Error</strong>', e);
					}
				}
			}
			,onComplete: function() {
				tmp.loadingDiv.remove();
				if(tmp.btn) {
					jQuery('#' + tmp.btn.id).button('reset');
				}
			}
		});
		return this;
	}
	/**
	 * Ajax: adding a comments to this order
	 */
	,_addComments: function(btn, resultDiv) {
		var tmp = {};
		tmp.me = this;
		tmp.commentsBox = $(btn).up('.new_comments_wrapper').down('[new_comments=comments]');
		tmp.comments = $F(tmp.commentsBox);
		if(tmp.comments.blank())
			return this;
		tmp.me._pageJs.postAjax(CommentsDivJs.SAVE_BTN_ID, {'comments': tmp.comments, 'entityId': tmp.me._entityId, 'entityName': tmp.me._entityName}, {
			'onCreate': function(sender, param) {
				jQuery('#' + btn.id).button('loading');
			}
			,'onSuccess': function (sender, param) {
				try {
					tmp.result = tmp.me._pageJs.getResp(param, false, true);
					if(!tmp.result || !tmp.result.item || !tmp.result.item.id)
						return;
					tmp.tbody = $(resultDiv).down('tbody');
					if(!tmp.tbody)
						$(resultDiv).insert({'bottom': tmp.tbody = new Element('tbody') });
					tmp.tbody.insert({'top': tmp.me._getCommentsRow(tmp.result.item)})
					tmp.commentsBox.setValue('');
				} catch (e) {
					tmp.me._pageJs.showModalBox('<strong class="text-danger">Error</strong>', e);
				}
			}
			,'onComplete': function () {
				jQuery('#' + btn.id).button('reset');
			}
		})
		return this;
	}
	/**
	 * Getting a empty comments div
	 */
	,_getEmptyCommentsDiv: function(resultDiv) {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = new Element('div', {'class': 'row new_comments_wrapper'})
			.insert({'bottom': new Element('div', {'class': 'col-xs-2 text-right'}).update('<strong>New Comments:</strong>') })
			.insert({'bottom': new Element('div', {'class': 'col-xs-10'})
				.insert({'bottom': new Element('div', {'class': 'input-group'})
					.insert({'bottom': new Element('input', {'class': 'form-control', 'type': 'text', 'new_comments': 'comments', 'placeholder': 'add more comments to this order'})
						.observe('keydown', function(event) {
							tmp.me._pageJs.keydown(event, function() {
								$(event.currentTarget).up('.new_comments_wrapper').down('[new_comments=btn]').click();
							});
						})
					})
					.insert({'bottom': new Element('span', {'class': 'input-group-btn'})
						.insert({'bottom': new Element('span', {'id': 'add_new_comments_btn', 'new_comments': 'btn', 'class': 'btn btn-primary', 'data-loading-text': 'saving...'})
							.update('add')
							.observe('click', function() {
								tmp.me._addComments(this, resultDiv);
							})
						})
					})
				})
			});
		return tmp.newDiv;
	}
	/**
	 * render the div
	 */
	,render: function() {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = new Element('div', {'class': 'panel panel-default'})
			.insert({'bottom': new Element('div', {'class': 'panel-heading'}).update('Comments:') })
			.insert({'bottom': tmp.resultDiv = new Element('div', {'class': 'comments_result_list table-responsive'})})
			.insert({'bottom': new Element('div', {'class': 'panel-footer'}).update(tmp.me._getEmptyCommentsDiv( tmp.resultDiv )) });
		$(tmp.me._displayDivId).update(tmp.newDiv);
		tmp.me._getComments(1,  tmp.resultDiv);
	}
}
