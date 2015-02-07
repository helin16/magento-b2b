/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new BPCPageJs(), {
	bindBtns: function() {
		var tmp = {};
		tmp.me = this;
		$$('.gen-report-btn').each(function(btn){
			$(btn).observe('click', function(){
				tmp.me.genReport(this, btn.readAttribute('data-type'));
			});
		})
		return tmp.me;
	}
	,genReport: function(btn, type) {
		var tmp = {};
		tmp.me = this;
		tmp.me._signRandID(btn);
		tmp.me.postAjax(tmp.me.getCallbackId('genReportBtn'), {'type': type}, {
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