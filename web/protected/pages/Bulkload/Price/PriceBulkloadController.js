/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new BPCPageJs(), {
	
	dropShowDiv: {'dropDiv': '', 'showDiv': ''}
	,_acceptableTypes: ['csv']
	,_fileReader: null
	
	/*
	,intializeFileReader: function() {
		var tmp = {};
		tmp.me = this;
		tmp.me._fileReader = new FileReader();
		return tmp.me;
	}
	*/
	
	,initializeFileHandler: function() {
		var tmp = {};
		tmp.me = this;
		
		$(tmp.me.dropShowDiv.dropDiv).observe('dragover', function(event) {
				tmp.me.handleDragOver(event);
			})
			.observe('drop', function(event) {
				tmp.me.handleFileSelect(event);
			});
	}

	,handleDragOver: function(evt) {
		var tmp = {};
		tmp.me = this;
		evt.stopPropagation();
		evt.preventDefault();
		
		evt.dataTransfer.dropEffect = 'copy';
	}
	
	,handleFileSelect: function(evt) {
		var tmp = {};
		tmp.me = this;
		evt.stopPropagation();
		evt.preventDefault();
		
		tmp.files = evt.dataTransfer.files;
		//console.debug(tmp.files);
		tmp.validFiles = [];
		
		$(tmp.me.dropShowDiv.showDiv).update('');
		
		for(tmp.i = 0, tmp.f; tmp.f = tmp.files[tmp.i]; tmp.i++) 
		{
			tmp.success = ((tmp.extension = tmp.f.name.split('.').pop()) !== '' && tmp.me._acceptableTypes.indexOf(tmp.extension.toLowerCase()) > -1);
			if(tmp.success)
			{
				tmp.msgTxt = 'File Name:' + tmp.f.name + 'Accepted';
				tmp.validFiles.push({'index': tmp.i, 'file': tmp.f});	
			}	
			else
				tmp.msgTxt = tmp.f.name + ' Error: Only Acceptable File Extension are ' + tmp.me._acceptableTypes.join(', ');
			
			$(tmp.me.dropShowDiv.showDiv).insert({'bottom':  new Element('div', {'class': 'msgDiv ' + (!tmp.success ? 'errorMsgDiv' : 'okMsgDiv'), 'file_sequence': tmp.i})
				.update(new Element('div', {'class': 'msg'})
					.update(tmp.msgTxt)
				) 
			});
		}
		
		tmp.validFiles.each(function(file) {
			tmp.reader = new FileReader();
			      
			tmp.reader.onload = function(event) { 
				tmp.contents = event.target.result;
				//console.debug(tmp.contents);
				$(tmp.me.dropShowDiv.showDiv).getElementsBySelector('[file_sequence='+ file.index +']')[0].insert({'bottom': new Element('div', {'class': 'msg'}).update('Loaded Successfully') });
				
			}
			tmp.reader.readAsText(file.file);
		});
		
	}

});