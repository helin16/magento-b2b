/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new BPCPageJs(), {
	bindBtns: function() {
		var tmp = {};
		tmp.me = this;
		$('btn-sales-report').observe('click', function(){tmp.me.salesReport(this)});
		$('btn-bills-report').observe('click', function(){tmp.me.billsReport(this)});
		
		return tmp.me;
	}
	,salesReport: function(btn) {
		var tmp = {};
		tmp.me = this;
		tmp.me._signRandID(btn);
		tmp.me.postAjax(tmp.me.getCallbackId('salesReport'), {}, {
			'onLoading': function() {
				jQuery('#' + btn.id).button('loading');
			}
			,'onSuccess': function(sender, param) {
				tmp.me.showModalBox('Success', '<h4>Report will be sent to nominated emails <b>shortly</b></h4>');
//				location.reload();
			}
			,'onComplete': function(sender, param) {
//				jQuery('#' + btn.id).button('reset');
			}
		});
		
		return tmp.me;
	}
	,billsReport: function(btn) {
		var tmp = {};
		tmp.me = this;
		tmp.me._signRandID(btn);
		tmp.me.postAjax(tmp.me.getCallbackId('billsReport'), {}, {
			'onLoading': function() {
				jQuery('#' + btn.id).button('loading');
			}
			,'onSuccess': function(sender, param) {
				tmp.me.showModalBox('Success', '<h4>Report will be sent to nominated emails <b>shortly</b></h4>');
				location.reload();
			}
			,'onComplete': function(sender, param) {
				jQuery('#' + btn.id).button('reset');
			}
		});
		
		return tmp.me;
	}
});