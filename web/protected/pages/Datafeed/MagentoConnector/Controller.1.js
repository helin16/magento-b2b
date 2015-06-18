/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new BPCPageJs(), {
	id_wrapper: '' //the html id of the wrapper
	,_config: {}
	,config_div: null

	,load: function(predata) {
		var tmp = {}
		tmp.me = this;
		tmp.me.predata = predata;

		$(tmp.me.getHTMLID('contentDiv')).update('').insert({'bottom': tmp.me.config_div = tmp.me._getConifgDiv() });
		
		return tmp.me;
	}
	,_getConifgDiv: function() {
		var tmp = {}
		tmp.me = this;
		
		tmp.newDiv = new Element('div').insert({'bottom': new Element('div').update('test') });
		
		return tmp.newDiv;
	}
});