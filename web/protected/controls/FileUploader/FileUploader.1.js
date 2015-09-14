var FileUploaderJs = new Class.create();
FileUploaderJs.prototype = Object.extend(new BPCPageJs(), {
	_pageJs: null

	,initialize: function(_pageJs) {
		this._pageJs = _pageJs;
	}
	,_validateUploadFiles: function(files, acceptableTypes) {
		var tmp = {};
		tmp.me = this;
		tmp.errors = [];
		//check number of files
		if(files.length === 0)
			tmp.me.showModalBox('Warning', 'at leave <b>one file</b> is required');
		if(files.length !== 1)
			tmp.me.showModalBox('Warning', 'can only handle <b>one file</b> for now');
		//check acceptable file types
		for(tmp.i = 0, tmp.file; tmp.file = files[tmp.i]; tmp.i++) {
			tmp.extension = tmp.file.name.split('.').pop();
			if(tmp.extension === '')
				tmp.errors.push({'file':tmp.file, 'msg': 'cannot validate file extension'});
			if(acceptableTypes.indexOf(tmp.extension.toLowerCase()) === -1)
				tmp.errors.push({'file':tmp.file, 'msg': 'unacceptable extension ' + tmp.extension + ', acceptable file extensions are ' + acceptableTypes.join(', ')});
		}
		if(tmp.errors.length > 0) {
			tmp.me.showModalBox('Error', tmp.errorsMsgList = new Element('table', {'class': 'table'}) );
			tmp.errors.each(function(error){
				tmp.errorsMsgList.insert({'bottom': new Element('tr')
					.insert({'bottom': new Element('td').update('File Name: ' + error.file.name)})
					.insert({'bottom': new Element('td').update(error.msg)})
				});
			});
		}
		
		return tmp.errors;
	}
	,_getFileUploadDiv: function(acceptableTypes, completeFunc, ifHeader, delimiter) {
		var tmp = {};
		tmp.me = this;
		tmp.ifHeader = (ifHeader || false);
		tmp.delimiter = (delimiter || ""); // "" is auto delimiter for papa parse
		tmp.acceptableTypes = (acceptableTypes || ['csv']);
		tmp.newDiv =  new Element('div',  {'class': 'panel panel-default drop_file_div', 'title': 'You can drag multiple files here!'})
			.insert({'bottom': new Element('div', {'class': 'panel-body'})
				.insert({'bottom': new Element('div', {'class': 'form-group center-block text-left', 'style': 'width: 50%'})
					.insert({'bottom': new Element('label').update('Drop you files here or select your file below:') })
					.insert({'bottom': tmp.inputFile = new Element('input', {'type': 'file', 'multiple': true, 'style': 'display: none;'})
						.observe('change', function(event) {
							if(tmp.me._validateUploadFiles(event.target.files, tmp.acceptableTypes).length === 0) {
								tmp.me._readFiles(event.target.files, completeFunc, tmp.ifHeader, tmp.delimiter);
							}
						})
					})
					.insert({'bottom': new Element('div', {'class': 'clearfix'}) })
					.insert({'bottom': new Element('span', {'class': 'btn btn-success clearfix'})
						.update('Click to select your file')
						.observe('click', function(event) {
							tmp.inputFile.click();
						})
					})
					.insert({'bottom': new Element('div', {'class': 'clearfix'}) })
					.insert({'bottom': new Element('small').update('ONLY ACCEPT file formats: ' + tmp.acceptableTypes.join(', ')) })
				})
			})
			.observe('dragover', function(evt) {
				evt.stopPropagation();
				evt.preventDefault();
				evt.dataTransfer.dropEffect = 'copy';
			})
			.observe('drop', function(evt) {
				if(tmp.me._getUploadType()) {
					evt.stopPropagation();
					evt.preventDefault();
					tmp.me._readFiles(evt.dataTransfer.files);
				}
			})
		;

		$H(tmp.me._importDataTypes).each(function(item){
			tmp.dropdown.insert({'bottom': new Element('option', {'value': item.key})
				.store('data', item.key)
				.update(item.value)
			});
		});

		tmp.me.file_upload_div = tmp.newDiv;
		return tmp.newDiv;
	}
	,_readFiles: function(files, completeFunc, ifHeader, delimiter) {
		var tmp = {};
		tmp.me = this;
		for(tmp.i = 0, tmp.file; tmp.file = files[tmp.i]; tmp.i++) {
			tmp.me.file_upload_div.update('processing file: ' + tmp.file.name).setStyle('font-weight: bold;');
			Papa.parse(tmp.file, {
				skipEmptyLines: true,
				header: ifHeader,
				delimiter: delimiter,
				complete: function(results) {
					tmp.me._pageJs._uploadedData = results;
//					tmp.me.file_upload_div.writeAttribute('done', true).removeClassName('drop_file_div').update('done');
					tmp.me.file_upload_div.remove();
					if(typeof completeFunc === 'function')
						completeFunc(results);
				}
			});
		}
		return tmp.me;
	}
});
