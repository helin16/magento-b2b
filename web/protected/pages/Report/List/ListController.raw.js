/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new BPCPageJs(), {
	_confirm_form_id: 'confirm_form_id'
	,bindBtns: function() {
		var tmp = {};
		tmp.me = this;
		$$('.gen-report-btn').each(function(btn){
			$(btn).observe('click', function(){
				tmp.me._showDateSelectPanel(this, btn.readAttribute('data-type'));
			});
		})
		return tmp.me;
	}
	,_initFormValdation: function(btn, type) {
		var tmp = {};
		tmp.me = this;
		tmp.mainForm = jQuery('#' + tmp.me._confirm_form_id);
		tmp.mainForm.formValidation({
	        // I am validating Bootstrap form
	        framework: 'bootstrap',
	        icon: {
	            valid: 'glyphicon glyphicon-ok',
	            invalid: 'glyphicon glyphicon-remove',
	            validating: 'glyphicon glyphicon-refresh'
	        },
	        fields: {
	            date_from: {
	                validators: {
	                	callback: {
	                		message: 'The from date is needed.',
	                		callback: function(value, validator, field) {
	                			return jQuery(field).data('DateTimePicker').date() !== null;
	                		}
	                	}
	                }
	            },
	            date_to: {
	                validators: {
	                	callback: {
	                		message: 'The to date is needed.',
	                		callback: function(value, validator, field) {
	                			return jQuery(field).data('DateTimePicker').date() !== null;
	                		}
	                	}
	                }
	            }
	        }
		})
		.on('err.form.fv', function(e) {
            if (tmp.mainForm.data('formValidation').getSubmitButton()) {
            	tmp.mainForm.data('formValidation').disableSubmitButtons(false);
            }
        })
        .on('success.form.fv', function(e) {
        	e.preventDefault();
        	if (tmp.mainForm.data('formValidation').getSubmitButton()) {
        		tmp.mainForm.data('formValidation').disableSubmitButtons(false);
        	}
        	tmp.data = {};
        	tmp.data.type = type;
        	$(tmp.me._confirm_form_id).getElementsBySelector('[report_date="date_from"]').each(function(item){
    			tmp.me._signRandID(item);
    			tmp.data[item.readAttribute('report_date')] = jQuery('#' + item.id).data('DateTimePicker').date().startOf('day').utc().format();
    		});
        	$(tmp.me._confirm_form_id).getElementsBySelector('[report_date="date_to"]').each(function(item){
    			tmp.me._signRandID(item);
    			tmp.data[item.readAttribute('report_date')] = jQuery('#' + item.id).data('DateTimePicker').date().endOf('day').utc().format();
    		});
        	tmp.me.genReport(btn, tmp.data);
        });
		tmp.mainForm.find('.datepicker').on('dp.change dp.show', function(e) {
			tmp.mainForm.formValidation('revalidateField', 'date_from');
			tmp.mainForm.formValidation('revalidateField', 'date_to');
	    })
		return tmp.me;
	}
	,_showDateSelectPanel: function(btn, type) {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = new Element('form', {'class': 'confirm-div form-horizontal', 'id': tmp.me._confirm_form_id})
			.insert({'bottom': new Element('div', {'class': 'form-group'})
				.insert({'bottom': new Element('label', {'class': 'col-sm-2 control-label'}).update('Date From:') })
				.insert({'bottom': new Element('div', {'class': 'col-sm-10'})
					.insert({'bottom': new Element('input', {'class': 'form-control datepicker', 'placeholder': 'The start of the date range', 'report_date': 'date_from', 'name': 'date_from'}) })
				})
			})
			.insert({'bottom': new Element('div', {'class': 'form-group'})
				.insert({'bottom': new Element('label', {'class': 'col-sm-2 control-label'}).update('Date To:') })
					.insert({'bottom': new Element('div', {'class': 'col-sm-10'})
					.insert({'bottom': new Element('input', {'class': 'form-control datepicker', 'placeholder': 'The end of the date range', 'report_date': 'date_to', 'name': 'date_to'}) })
				})
			})
			.insert({'bottom': new Element('div', {'class': 'text-right'})
				.insert({'bottom': new Element('span', {'class': 'btn btn-default pull-left'})
					.update('CANCEL')
					.observe('click', function(){
						tmp.me.hideModalBox();
					})
				})
				.insert({'bottom': new Element('button', {'type': 'submit', 'class': 'btn btn-primary submit-btn', 'data-loading-text': 'Generating ...'})
					.update('Generate The Report')
				})
			});
		tmp.me.showModalBox('<strong>Please provide the date range for: ' + $(btn).innerHTML + '</strong>', tmp.newDiv, false, null, {
			'shown.bs.modal': function(e){
				tmp.me._signRandID(tmp.newDiv);
				jQuery('#' + tmp.newDiv.id).find('.datepicker').datetimepicker({
					format: 'DD/MM/YYYY'
					,viewMode: 'days'
				});
				tmp.me._initFormValdation(tmp.newDiv.down('.submit-btn'), type);
			}
			,'hide.bs.modal': function(e) {
				tmp.fv = jQuery('#' + tmp.me._confirm_form_id).data('formValidation');
				if(tmp.fv)
					tmp.fv .destroy();
			}
		});
		return tmp.me;
	}
	,genReport: function(btn, data) {
		var tmp = {};
		tmp.me = this;
		tmp.me.postAjax(tmp.me.getCallbackId('genReportBtn'), data, {
			'onCreate': function() {
				tmp.me._signRandID(btn);
				jQuery('#' + btn.id).button('loading');
			}
			,'onSuccess': function(sender, param) {
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result || !tmp.result.item || !tmp.result.item.id)
						return;
					window.open(tmp.result.item.url);
					window.focus();
					tmp.me.hideModalBox();
				} catch (e) {
					tmp.me.showModalBox('<strong class="text-danger">Error</strong>', '<h4>' + e + '</h4>');
				}
			}
			,'onComplete': function(sender, param) {
				jQuery('#' + btn.id).button('reset');
			}
		}, 60000);
		return tmp.me;
	}
});