/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new BPCPageJs(), {
	id_wrapper: '' //the html id of the wrapper
	,_acceptableTypes: ['csv']
	,csvFileLineFormat: []
	,_fileReader: null
	,_uploadedData: {}
	,_htmlIds: {}
	,_importDataTypes: {}
	,_rowNo: null
	,_selectTypeTxt: 'Select a Import Type'

	,setHTMLIDs: function(importerDivId, importDataTypesDropdownId) {
		var tmp = {};
		tmp.me = this;
		
		tmp.me._htmlIds.importerDiv = importerDivId;
		tmp.me._htmlIds.importDataTypesDropdown = importDataTypesDropdownId;
		
		tmp.me.id_wrapper = tmp.me._htmlIds.importerDiv;
		
		return this;
	}
	
	,load: function(importDataTypes) {
		var tmp = {}
		tmp.me = this;
		tmp.me._rowNo = 1;
		tmp.me._importDataTypes = importDataTypes;
		
		$(tmp.me._htmlIds.importerDiv).update('test');
		
		if (window.File && window.FileReader && window.FileList && window.Blob) { //the browser supports file reading api
			tmp.me._fileReader = new FileReader();
			$(tmp.me._htmlIds.importerDiv).update( tmp.me._getFileUploadDiv() );
			tmp.me._loadChosen();
		} else {
			$(tmp.me.id_wrapper).update(tmp.me.getAlertBox('Warning:', 'Your browser does NOT support this feature. pls change and try again').addClassName('alert-warning') );
		}
		return tmp.me;
	}

	,_genTemplate: function() {
		var tmp = {};
		tmp.me = this;
		if(tmp.me.type = tmp.me._getUploadType()) {
			tmp.data = [];
			tmp.data.push(tmp.me.csvFileLineFormat.join(', ') + "\n");
			tmp.now = new Date();
			tmp.fileName = tmp.me.type + '_' + tmp.now.getFullYear() + '_' + tmp.now.getMonth() + '_' + tmp.now.getDate() + '_' + tmp.now.getHours() + '_' + tmp.now.getMinutes() + '_' + tmp.now.getSeconds() + '.csv';
			tmp.blob = new Blob(tmp.data, {type: "text/csv;charset=utf-8"});
			saveAs(tmp.blob, tmp.fileName);
		}
		return tmp.me;
	}
	/**
	 * initiating the chosen input
	 */
	,_loadChosen: function () {
		jQuery(".chosen").chosen({
				search_contains: true,
				inherit_select_classes: true,
				no_results_text: "No code type found!",
				width: "250px",
		});
		return this;
	}
	,_getFileUploadDiv: function() {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv =  new Element('div',  {'class': 'panel panel-default drop_file_div', 'title': 'You can drag multiple files here!'})
			.insert({'bottom': new Element('div', {'class': 'panel-body'})
				.insert({'bottom': new Element('div', {'class': 'pull-right'})
					.insert({'bottom': tmp.dropdown = new Element('select', {'class': 'chosen', 'data-placeholder': 'Code Type: ' ,'id': tmp.me._htmlIds.importDataTypesDropdown}) 
						.insert({'bottom': new Element('option', {'value': tmp.me._selectTypeTxt}).update(tmp.me._selectTypeTxt) })
					})
					.insert({'bottom': new Element('span', {'class': 'btn btn-default btn-xs', 'title': 'Download Template'})
						.insert({'bottom': new Element('span', {'class': 'glyphicon glyphicon-download-alt'}) })
						.observe('click', function() {
							tmp.me._genTemplate();
						})
					})
				})
				.insert({'bottom': new Element('div', {'class': 'form-group center-block text-left', 'style': 'width: 50%'})
					.insert({'bottom': new Element('label').update('Drop you files here or select your file below:') })
					.insert({'bottom': tmp.inputFile = new Element('input', {'type': 'file', 'style': 'display: none;'}) 
						.observe('change', function(event) {
							tmp.me._readFiles(event.target.files);
						})
					})
					.insert({'bottom': new Element('div', {'class': 'clearfix'}) })
					.insert({'bottom': new Element('span', {'class': 'btn btn-success clearfix'})
						.update('Click to select your file')
						.observe('click', function(event) {
							if(tmp.me._getUploadType())
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
		
		return tmp.newDiv;
	}
	
	,_getUploadType: function() {
		var tmp = {};
		tmp.me = this;
		tmp.me.dropdown = $(tmp.me._htmlIds.importDataTypesDropdown);
		tmp.me._importDataTypes = $F(tmp.me.dropdown);
		
		if(tmp.me._importDataTypes === tmp.me._selectTypeTxt) {
			tmp.me.showModalBox('Please select a import type first', 'Invalid inport type'); 
			return false;
		}
		switch(tmp.me._importDataTypes) {
			case 'myob_ean':
			case 'myob_upc':
				tmp.me.csvFileLineFormat = ['sku', 'itemNo'];
				break;
			case 'stockAdjustment':
				tmp.me.csvFileLineFormat = ['sku', 'stockOnPO', 'stockOnHand', 'stockOnOrder', 'stockInRMA', 'stockInParts' , 'totalInPartsValue', 'totalOnHandValue'];
				break;
			case 'accounting':
				tmp.me.csvFileLineFormat = ['sku', 'assetAccNo', 'costAccNo', 'revenueAccNo'];
				break;
			case 'accountingCode':
				tmp.me.csvFileLineFormat = ['description', 'code'];
				break;
			default:
				tmp.me.csvFileLineFormat = [];
		}
		return tmp.me._importDataTypes;
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
					tmp.me._rowNo = 1; // reset rowNo for each file
					event.target.result.split(/\r\n|\n|\r/).each(function(line) {
						if(line !== null && !line.blank()) {
							tmp.cols = [];
							line.split(',').each(function(col) {
								if(col !== null) {
									tmp.cols.push(col.strip());
								}
							})
							tmp.key = tmp.cols.join(',');
							if(tmp.key !== tmp.me.csvFileLineFormat.join(',')) { //this is not the header line
								tmp.colArray = {};
								for(tmp.j = 0; tmp.j < tmp.me.csvFileLineFormat.size(); tmp.j++) {
									tmp.colArray[tmp.me.csvFileLineFormat[tmp.j]] = tmp.cols[tmp.j];
								}
								tmp.colArray['index'] = tmp.me._rowNo++; // tmp.me._rowNo starts at 1
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
						tmp.me._loadProductLineItems();
					})
				})
				.insert({'bottom': new Element('span', {'class': 'btn btn-warning pull-right'})
					.update('Cancel')
					.observe('click', function(){
						jQuery('.btn').attr('disabled','disabled');
						window.location = document.URL;
					})
				})
			})
		);
		return tmp.me;
	}
	
	,genCSV: function(btn) {
		var tmp = {};
		tmp.me = this;
		
		//collect data
		tmp.data = [];
		tmp.originalDataRows = $(btn).up('.panel').getElementsBySelector('.result_row.result-done');
		if(tmp.originalDataRows.length < 1)
			return;
		
		tmp.headerRow = '';
		$H(tmp.originalDataRows[0].retrieve('data')).each(function(item){
			tmp.headerRow = tmp.headerRow + item.key + ', ';
		});
		tmp.data.push(tmp.headerRow + '\n');
		
		$(btn).up('.panel').getElementsBySelector('.result_row.result-done').each(function(row){
			tmp.csvRow = '';
			
			$H(row.retrieve('data')).each(function(item){
				tmp.csvRow = tmp.csvRow + item.value + ', ';
			});
			
			tmp.csvRow = tmp.csvRow + '\n';
			tmp.data.push(tmp.csvRow);
			tmp.i = tmp.i * 1 + 1;
		});
		
		tmp.now = new Date();
		tmp.fileName = tmp.me._importDataTypes + '_match_' + tmp.now.getFullYear() + '_' + tmp.now.getMonth() + '_' + tmp.now.getDate() + '_' + tmp.now.getHours() + '_' + tmp.now.getMinutes() + '_' + tmp.now.getSeconds() + '.csv';
		tmp.blob = new Blob(tmp.data, {type: "text/csv;charset=utf-8"});
		saveAs(tmp.blob, tmp.fileName);
		return tmp.me;
	}
	/**
	 * Open detail page
	 */
	,_openDetailPage: function(path, id) {
		var tmp = {};
		tmp.me = this;
		tmp.newWindow = window.open('/' + path + '/' + id + '.html', path + ' details', 'width=1300, location=no, scrollbars=yes, menubar=no, status=no, titlebar=no, fullscreen=no, toolbar=no');
		tmp.newWindow.focus();
		return tmp.me;
	}
	/**
	 * Getting a single row of the result table
	 */
	,_getProductLineItem: function(listGroupDiv, dataKeyIndex, dataKeys) {
		var tmp = {};
		tmp.me = this;
		tmp.data = tmp.me._uploadedData[dataKeys[dataKeyIndex]];
		
		tmp.newRow = new Element('tr', {'class': 'result_row info'});
		tmp.me.csvFileLineFormat.each(function(name){
			$H(tmp.data).each(function(item){
				if(item.key === name) {
					tmp.newRow.insert({'bottom': new Element('th', {'style': item.value ? '' : 'color:red;'}).update(item.value ? item.value : 'Blank ' + name) })
				}
			});
		});
		tmp.data.importDataTypes = tmp.me._importDataTypes;
		
		tmp.me.postAjax(tmp.me.getCallbackId('getAllCodeForProduct'), tmp.data, {
			'onLoading': function(sender, param) {
				listGroupDiv.insert({'bottom': tmp.newRow });
			}
			,'onSuccess': function (sender, param) {
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result.item.id) {
						tmp.newRow.update('');
						return;
					}
					tmp.newRow.removeClassName('info').addClassName('result-done').store('data', tmp.result.item).down('th')
					if(tmp.result.path) {
						tmp.newRow.down('th')
							.setStyle({
								'cursor': 'pointer',
						    	'text-decoration': 'underline'
							})
							.observe('click',function(){
								tmp.me._openDetailPage(tmp.result.path, tmp.result.item.id);
							});
					}
				}  catch (e) {
					tmp.newRow.removeClassName('info').addClassName('danger').store('data', tmp.data)
						.insert({'bottom': new Element('td',{'colspan': 2}).update('<strong>ERROR:</strong>' + e) });
					listGroupDiv.insert({'top': tmp.newRow });
				}
			}
			,'onComplete': function(sender, param) {
				try {
					tmp.nextDataKeyIndex = dataKeyIndex * 1 + 1;
					if(tmp.nextDataKeyIndex >= dataKeys.size()) { //this is the last row
						tmp.errRows = $(tmp.me.id_wrapper).getElementsBySelector('.result_row.danger');
						listGroupDiv.up('.panel').removeClassName('panel-danger').addClassName(tmp.errRows.size() > 0 ? 'panel-warning' : 'panel-success').down('.panel-heading').update('')
							.insert({'bottom': new Element('panel-title').update((tmp.errRows.size() > 0 ? 'All provided rows have been proccessed, but with ' + tmp.errRows.size() + ' error(s)' : 'All provided rows have been proccessed successfully') ) })
							.insert({'bottom': new Element('span',{'class': 'btn-group btn-group-sm pull-right'})
								.insert({'bottom': new Element('span',{'class': 'btn hidden'})
									.writeAttribute('title', tmp.errRows.size() > 0 ? 'Export Success Rows' : 'Export To Excel')
									.update(new Element('span', {'class': 'glyphicon glyphicon-save'}))
									.addClassName(tmp.errRows.size() > 0 ? 'btn-warning' : 'btn-success')
									.observe('click', function() {
										tmp.me.genCSV(this);
									})
								})
								.insert({'bottom': new Element('span',{'class': 'btn btn-default'})
									.writeAttribute('title', 'Start Again')
									.update(new Element('span', {'class': 'glyphicon glyphicon-repeat'}))
									.observe('click', function() {
										window.location = document.URL;
									})
								})
							})
					} else {
						tmp.me._getProductLineItem(listGroupDiv, tmp.nextDataKeyIndex, dataKeys);
					}
				} catch (e) {
					alert(e);
				}
			}
		});
		return tmp.me;
	}
	
	/**
	 * Getting the result list table
	 */
	,_loadProductLineItems: function() {
		var tmp = {};
		tmp.me = this;
		tmp.keys = [];
		$H(tmp.me._uploadedData).each(function(data){
			tmp.keys.push(data.key);
		});
		
		//get header row
		tmp.theadRow = new Element('tr');
		tmp.me.csvFileLineFormat.each(function(item){
			tmp.theadRow.insert({'bottom': new Element('th').update(item) })
		});
		
		$(tmp.me.id_wrapper).update(
			new Element('div', {'class': 'price_search_result panel panel-danger table-responsive'})
			.insert({'bottom': new Element('div', {'class': 'panel-heading'})
				.update('Total of <strong>' + tmp.keys.size() + '</strong> unique row(s) received:') 
				.insert({'bottom': new Element('strong',{'class': 'pull-right'}).update('please waiting for it to finish') })
			})
			.insert({'bottom': new Element('table', {'class': 'table table-striped'})
				.insert({'bottom': new Element('thead').update(tmp.theadRow) })
				.insert({'bottom': tmp.resultList = new Element('tbody') })
			})
		);
		tmp.me._getProductLineItem(tmp.resultList, 0, tmp.keys);
		return tmp.me;
	}

});