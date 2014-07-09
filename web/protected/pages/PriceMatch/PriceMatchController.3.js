/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new BPCPageJs(), {
	id_wrapper: '' //the html id of the wrapper
	,_acceptableTypes: ['csv']
	,csvFileLineFormat: ['sku', 'price']
	,_fileReader: null
	,_uploadedData: {}
	,_html_ids: {'uploadedFileList': 'uploaded_file_list', 'uploadInputDiv': 'upload_input_div', 'resultListDiv': 'result_list_div'}

	,load: function(id_wrapper) {
		var tmp = {}
		tmp.me = this;
		tmp.me.id_wrapper = id_wrapper;
		if (window.File && window.FileReader && window.FileList && window.Blob) { //the browser supports file reading api
			tmp.me._fileReader = new FileReader();
			$(tmp.me.id_wrapper).update( tmp.me._getFileUploadDiv() )
		} else {
			$(tmp.me.id_wrapper).update(tmp.me.getAlertBox('Warning:', 'Your browser does NOT support this feature. pls change and try again').addClassName('alert-warning') );
		}
		return tmp.me;
	}

	,_genTemplate: function() {
		var tmp = {};
		tmp.me = this;
		tmp.data = [];
		tmp.data.push(tmp.me.csvFileLineFormat.join(', ') + '\n');
		tmp.now = new Date();
		tmp.fileName = 'pricematch_template_' + tmp.now.getFullYear() + '_' + tmp.now.getMonth() + '_' + tmp.now.getDate() + '_' + tmp.now.getHours() + '_' + tmp.now.getMinutes() + '_' + tmp.now.getSeconds() + '.csv';
		tmp.blob = new Blob(tmp.data, {type: "text/csv;charset=utf-8"});
		saveAs(tmp.blob, tmp.fileName);
		return tmp.me;
	}
	
	,_getFileUploadDiv: function() {
		var tmp = {};
		tmp.me = this;
		return new Element('div',  {'class': 'panel panel-default drop_file_div', 'title': 'You can drag multiple files here!'})
			.insert({'bottom': new Element('div', {'class': 'panel-body'})
				.insert({'bottom': new Element('div', {'class': 'btn-group pull-right'})
					.insert({'bottom': new Element('span', {'class': 'btn btn-default', 'title': 'Edit Companies'})
						.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-cog'}) })
						.observe('click', function(){
							jQuery.fancybox({
								'width'			: '90%',
								'height'		: '90%',
								'autoScale'     : false,
								'autoDimensions': false,
								'fitToView'     : false,
								'autoSize'      : false,
								'type'			: 'iframe',
								'href'			: '/pricematchcompanies.html',
								'beforeClose'	: function() {
									window.location = document.URL;
								}
					 		});
						})
					})
					.insert({'bottom': new Element('span', {'class': 'btn btn-default', 'title': 'Download Template'})
						.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-download-alt'}) })
						.observe('click', function() {
							tmp.me._genTemplate();
						})
					})
				})
				.insert({'bottom': new Element('div', {'class': 'form-group center-block text-left', 'style': 'width: 50%'})
					.insert({'bottom': new Element('label').update('Drop you files here or select your file below:') })
					.insert({'bottom': tmp.inputFile = new Element('input', {'type': 'file', 'style': 'display: none;', 'multiple': true}) 
						.observe('change', function(event) {
							tmp.me._readFiles(event.target.files);
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
					.insert({'bottom': new Element('small').update('ONLY ACCEPT file formats: ' + tmp.me._acceptableTypes.join(', ')) })
				})
			})
			.observe('dragover', function(evt) {
				evt.stopPropagation();
				evt.preventDefault();
				evt.dataTransfer.dropEffect = 'copy';
			})
			.observe('drop', function(evt) {
				evt.stopPropagation();
				evt.preventDefault();
				tmp.me._readFiles(evt.dataTransfer.files);
			})
		;
	}
	
	,_readFiles: function(files) {
		var tmp = {};
		tmp.me = this;
		tmp.me._uploadedData = {};
		tmp.fileLists = new Element('div', {'class': 'list-group'});
		for(tmp.i = 0, tmp.file; tmp.file = files[tmp.i]; tmp.i++) {
			tmp.fileRow = new Element('div', {'class': 'row'}).update( new Element('div', {'class': 'col-lg-6 col-md-6'}).update(tmp.file.name) );
			if((tmp.extension = tmp.file.name.split('.').pop()) !== '' && tmp.me._acceptableTypes.indexOf(tmp.extension.toLowerCase()) > -1) {
				tmp.me._fileReader = new FileReader();
				tmp.me._fileReader.onload = function(event) {
					event.target.result.split('\r\n').each(function(line) {
						if(line !== null && !line.blank()) {
							tmp.cols = [];
							line.split(',').each(function(col) {
								if(col !== null && !col.blank()) {
									tmp.cols.push(col.strip());
								}
							})
							tmp.key = tmp.cols.join(',');
							if(tmp.key !== tmp.me.csvFileLineFormat.join(',')) { //this is not the header line
								tmp.colArray = {};
								for(tmp.j = 0; tmp.j < tmp.me.csvFileLineFormat.size(); tmp.j++) {
									tmp.colArray[tmp.me.csvFileLineFormat[tmp.j]] = tmp.cols[tmp.j];
								}
								tmp.me._uploadedData[tmp.key] = tmp.colArray;
							}
						}
					})
				}
				tmp.me._fileReader.readAsText(tmp.file);
				tmp.supported = true;
			} else {
				tmp.fileRow.insert({'bottom': new Element('div', {'class': 'col-lg-6 col-md-6'}).update(new Element('small').update('Not supported file extension: ' + tmp.extension) )})
				tmp.supported = false;
			}
			tmp.fileLists.insert({'bottom': new Element('div', {'class': 'list-group-item ' + (tmp.supported === true ? 'list-group-item-success' : 'list-group-item-danger')})
				.insert({'bottom': tmp.fileRow })
			});
		}
		$(tmp.me.id_wrapper).update(
			new Element('div', {'class': 'panel panel-default'})
			.insert({'bottom': new Element('div', {'class': 'panel-heading'})
				.update('Files Selected:')
				.insert({'bottom': new Element('small', {'class': 'pull-right'}).update('ONLY ACCEPT file formats: ' + tmp.me._acceptableTypes.join(', ')) })
			})
			.insert({'bottom': tmp.fileLists })
			.insert({'bottom': new Element('div', {'class': 'panel-footer'})
				.insert({'bottom': new Element('span', {'class': 'btn btn-success'})
					.update('Start')
					.observe('click', function() {
						console.debug(tmp.me._uploadedData);
					})
				})
				.insert({'bottom': new Element('span', {'class': 'btn btn-warning pull-right'})
					.update('Cancel')
					.observe('click', function(){
						$(tmp.me.id_wrapper).update( tmp.me._getFileUploadDiv() );
					})
				})
			})
		);
		return tmp.me;
	}
	
	,genCSV: function(btn) {
		var tmp = {};
		tmp.me = this;
		if(!$(tmp.me.dropShowDiv.resultDiv))
			return tmp.me;
		
		//collect data
		tmp.data = [];
		tmp.i = 0;
		$(tmp.me.dropShowDiv.resultDiv).getElementsBySelector('.row').each(function(row){
			tmp.originalData = row.retrieve('data');
			tmp.csvRow = tmp.originalData.sku + ', ' + tmp.originalData.myPrice + ', ' + tmp.originalData.minPrice + ', ' + tmp.originalData.priceDiff;
			tmp.originalData.data.each(function(compData) {
				tmp.csvRow = tmp.csvRow + ', ' + (tmp.i === 0 ? compData.company : compData.price);
			})
			tmp.csvRow = tmp.csvRow + '\n';
			tmp.data.push(tmp.csvRow);
			tmp.i = tmp.i * 1 + 1;
		});
		tmp.now = new Date();
		tmp.fileName = 'pricematch_' + tmp.now.getFullYear() + '_' + tmp.now.getMonth() + '_' + tmp.now.getDate() + '_' + tmp.now.getHours() + '_' + tmp.now.getMinutes() + '_' + tmp.now.getSeconds() + '.csv';
		tmp.blob = new Blob(tmp.data, {type: "text/csv;charset=utf-8"});
		saveAs(tmp.blob, tmp.fileName);
		return tmp.me;
	}
	
	,_loadProductLineItems: function() {
		var tmp = {};
		tmp.me = this;
		
		$(tmp.me.dropShowDiv.dropDiv).hide();
		$(tmp.me.dropShowDiv.showDiv).update('');
		
		/// Generate the header for the price compare table ///
		tmp.headerCompanyArray = [];
		tmp.me.companyNameArray.each(function(cName) {
			tmp.headerCompanyArray.push({'price': '', 'priceURL': '', 'company': cName});
		});
		
		tmp.spinBar = new Element('span', {"class": "inlineblock loading"});
		$(tmp.me.dropShowDiv.resultDiv)
			.update('')
			.insert({'bottom': tmp.me.
				_generatePriceRowForProduct({'sku': 'SKU', 'minPrice': 'Min Price', 'myPrice': 'My Price', 'priceDiff': 'Price Diff.', 'data': tmp.headerCompanyArray})
				.addClassName('header')
			})
			.insert({'after': tmp.spinBar});
		///////////////////////////////////////////////////////////
		tmp.lineNo = 0;
		tmp.me.allFileLineArray.each(function(item) {
			item.fileContent.each(function(line) {
				if(line.sku.blank())
				{
					tmp.lineNo = tmp.lineNo * 1 + 1;
					tmp.me._checkLastLine(tmp.lineNo, tmp.spinBar);
					return;
				}	
				tmp.me.postAjax(tmp.me.getCallbackId('getAllPricesForProduct'), {'sku': line.sku, 'price': line.price}, {
					'onLoading': function(sender, param) {}
					,'onSuccess': function (sender, param) {
						try 
						{
							tmp.result = tmp.me.getResp(param, false, true);
							if(!tmp.result)
								return;
							if(tmp.result.items.sku !== '' && tmp.result.items.sku !== undefined && tmp.result.items.sku !== null && !tmp.result.items.sku.blank())
								$(tmp.me.dropShowDiv.resultDiv).insert({'bottom': tmp.me._generatePriceRowForProduct(tmp.result.items)});
							
							tmp.lineNo = tmp.lineNo * 1 + 1;
							tmp.me._checkLastLine(tmp.lineNo, tmp.spinBar);
						} 
						catch (e) 
						{
							alert(e);
						}
					}
				})
			});
		});
		return tmp.me;
	}

});