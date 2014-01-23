/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new BPCPageJs(), {
	init: function() {
		$$('.operationWrapper .operation_box .operation_title.expandable').each(function(item){
			item.observe('click', function(){
				if(item.hasClassName('collapsed')) {
					item.up('.operation_box').down('.operation_content').show();
					item.removeClassName('collapsed');
				} else {
					item.up('.operation_box').down('.operation_content').hide();
					item.addClassName('collapsed');
				}
			});
		});
	}
});