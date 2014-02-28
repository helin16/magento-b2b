/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new BPCPageJs(), {
	mainContentDiv: ''
	,validOptions: {}
		
	,loadBulkloadOptions: function() {
		var tmp = {};
		tmp.me = this;
		tmp.container = $(tmp.me.mainContentDiv);
		if(tmp.me.validOptions instanceof Array && tmp.me.validOptions.size() > 0)
		{
			tmp.optionList = new Element('select', {'id': 'bulkload_selection'})
								.insert({'bottom': new Element('option', {'value': ''}).update('Please Select An Option') });
			
			tmp.me.validOptions.each(function(item){
				tmp.optionList.insert({'bottom': new Element('option', {'value': item.value, 'url': item.url}).update(item.display) });
			});
				
			tmp.optionList.observe('change', function() {
					if((tmp.value = $F(this).strip()) !== '' && tmp.value !== null)
					{
						if((tmp.url = $(this).readAttribute('url')) === '' || tmp.url === null || tmp.url === undefined)
							tmp.url = "/bulkload/"+tmp.value+".html";
						
						location.href = tmp.url;
					}	
			});
			
			tmp.container.insert({'bottom': tmp.optionList});
		}	
	}	
});