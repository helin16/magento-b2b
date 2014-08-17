/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new DetailsPageJs(), {
	_getItemDiv: function() {
		var tmp = {};
		tmp.me = this;
	}
});