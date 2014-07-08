/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new BPCPageJs(), {
	
	_user: null //the current user
	,_roles: [] //the list of roles
	,_savePanelId: '' //the html id of the saving panel
	,_editUrl: '' //the edit user url pattern

	,setHtmlIDs: function(savePanel) {
		this._savePanelId = savePanel;
		return this;
	}

	,setEditUrl: function(editUrl) {
		this._editUrl = editUrl;
		return this;
	}

	,_getFieldDiv: function (title, content) {
		return new Element('div', {'class': 'form-group'})
			.insert({'bottom': new Element('label', {'class': 'col-sm-2 control-label'}).update(title) })
			.insert({'bottom': content.addClassName('form-control').wrap(new Element('div', {'class': 'col-sm-10'})) });
	}
	
	,_getRoleSelBox: function () {
		var tmp = {};
		tmp.me = this;
		//getting the old role ids
		tmp.oldRoleIds = [];
		if(tmp.me._user) {
			tmp.me._user.roles.each(function(role) {
				tmp.oldRoleIds.push(role.id);
			});
		}
		//getting the selection box
		tmp.selBox = new Element('select');
		tmp.me._roles.each(function(role) {
			tmp.opt = new Element('option', {'value': role.id}).update(role.name);
			if(tmp.oldRoleIds.indexOf(role.id) >= 0) {
				tmp.opt.writeAttribute('selected', true);
			}
			tmp.selBox.insert({'bottom': tmp.opt });
		});
		return tmp.selBox;
	}
	
	,_chkPassword: function(passTxtbox, confirmPassTxtBox) {
		var tmp = {};
		tmp.me = this;
		//clean up all the error msg
		$$('.errorMsgDiv.passNotMatch').each(function(msg) {
			msg.remove();
		});
		//check whether the same
		tmp.isSame = ($F(passTxtbox) === $F(confirmPassTxtBox));
		if(!tmp.isSame) {
			$(confirmPassTxtBox).insert({'after': new Element('div', {'class': 'passNotMatch msgDiv errorMsgDiv'}).update(new Element('div', {'class': 'msg'}).update('Password do NOT match!') ) });
		}
		return tmp.isSame;
	}
	,_collectData: function(btn) {
		var tmp = {};
		tmp.me = this;
		tmp.data = {'userid': (tmp.me._user ? tmp.me._user.id : '')};
		tmp.savePanel = $(tmp.me._savePanelId);
		//clean up all the error msg
		tmp.savePanel.getElementsBySelector('.msgDiv').each(function(msg) {
			msg.remove();
		});
		//start collect
		tmp.hasError = false;
		if(tmp.savePanel) {
			tmp.hasError = !tmp.me._chkPassword(tmp.savePanel.down('[password=new]'), tmp.savePanel.down('[password=confirm]'))
			tmp.savePanel.getElementsBySelector('[save_panel]').each(function(field) {
				tmp.fieldName = $(field).readAttribute('save_panel');
				tmp.isMand = $(field).readAttribute('mandatory');
				tmp.isMand = (tmp.isMand && (tmp.isMand * 1) === 1 ? true : false);
				if(tmp.isMand === true && $F(field).blank()) {
					$(field).insert({'after': new Element('div', {'class': 'msgDiv errorMsgDiv'}).update(new Element('div', {'class': 'msg'}).update(tmp.fieldName + ' is mandatory!') ) });
					tmp.hasError = true;
				}
				tmp.data[tmp.fieldName] = $F(field);
			});
		}
		return tmp.hasError === true ? null : tmp.data;
	}
	
	,_saveUser: function(btn) {
		var tmp = {};
		tmp.me = this;
		tmp.data = tmp.me._collectData(btn);
		if(tmp.data === null)
			return tmp.me;
		tmp.me.postAjax(tmp.me.getCallbackId('saveUser'), tmp.data, {
			'onLoading': function () {
				jQuery('#' + btn.id).button('loading');
			}
			,'onSuccess': function(sender, param) {
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result)
						return;
					alert('User Saved Successfully');
					window.location = tmp.me._editUrl.replace('{uid}', tmp.result.id);
				} catch (e) {
					$(btn).insert({'before': tmp.me.getAlertBox('Error:', e).addClassName('alert-danger') })
				}
			}
			,'onComplete': function () {
				jQuery('#' + btn.id).button('reset');
			}
		});
		return tmp.me;
	}

	,_getSavePanel: function () {
		var tmp = {};
		tmp.me = this;
		tmp.firstName = tmp.me._user ? tmp.me._user.person.firstName : '';
		tmp.lastName = tmp.me._user ? tmp.me._user.person.lastName : '';
		tmp.username = tmp.me._user ? tmp.me._user.username : '';
		tmp.newPanel = new Element('div', {'class': 'form-horizontal change_form', 'role': 'form'})
			.insert({'bottom': tmp.me._getFieldDiv('First Name:', new Element('input', {'type':'text', 'save_panel': 'firstName', 'value': tmp.firstName, 'mandatory': 1}) ) })
			.insert({'bottom': tmp.me._getFieldDiv('Last Name:', new Element('input', {'type':'text', 'save_panel': 'lastName', 'value': tmp.lastName, 'mandatory': 1}) ) })
			.insert({'bottom': tmp.me._getFieldDiv('Role:', tmp.me._getRoleSelBox()
					.writeAttribute('save_panel', 'roleid')
					.writeAttribute("mandatory", true) 
				) 
			})
			.insert({'bottom': tmp.me._getFieldDiv('Username:', new Element('input', {'type':'text', 'save_panel': 'userName', 'value': tmp.username, 'mandatory': 1})  ) })
			.insert({'bottom': tmp.me._getFieldDiv('New Password:', new Element('input', {'type':'password', 'save_panel': 'newpassword', 'password': 'new', 'value': '', 'mandatory': (tmp.me._user ? 0 : 1) }) 
					.observe('change', function(){
						tmp.me._chkPassword(this, $(this).up('.change_form').down('[password=confirm]'));
					})
				) 
			})
			.insert({'bottom': tmp.me._getFieldDiv('Confirm Password:', new Element('input', {'type':'password', 'password': 'confirm', 'value': ''})
					.observe('change', function(){
						tmp.me._chkPassword($(this).up('.change_form').down('[password=new]'), this);
					})
				) 
			})
			.insert({'bottom': new Element('div', {'class': 'form-group'})
				.insert({'bottom': new Element('div', {'class': 'col-sm-offset-2 col-sm-10'})
					.insert({'bottom': new Element('span', {'id': 'saveBtn', 'class': 'btn btn-success', 'data-loading-text': 'Saving...'})
						.update('Save')
						.observe('click', function(){
							tmp.me._saveUser(this);
						})
					})
				})
			}) 
			;
		return tmp.newPanel;
	}
	
	,load: function(user, roles) {
		var tmp = {}
		tmp.me = this;
		tmp.me._user = user;
		tmp.me._roles = roles;
		if($(tmp.me._savePanelId))
			$(tmp.me._savePanelId).update(tmp.me._getSavePanel());
		return tmp.me;
	}
});