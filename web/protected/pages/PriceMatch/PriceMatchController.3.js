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
	,_companyAliases: {}

	,load: function(id_wrapper, companyAliases) {
		var tmp = {}
		tmp.me = this;
		tmp.me.id_wrapper = id_wrapper;
		tmp.me._companyAliases = companyAliases;
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
					event.target.result.replace('\n', '{EOL}').replace('\r', '{EOL}').replace('\r\n', '{EOL}').split('{EOL}').each(function(line) {
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
						tmp.me._loadProductLineItems();
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
		
		//collect data
		tmp.data = [];
		tmp.headerRow = 'SKU, My Price, Price Diff., Min. Price';
		$H(tmp.me._companyAliases).each(function(alias){
			tmp.headerRow = tmp.headerRow + ', ' + alias.key;
		});
		tmp.data.push(tmp.headerRow);
		
		$(btn).up('.panel').getElementsBySelector('.result_row').each(function(row){
			tmp.originalData = row.retrieve('data');
			tmp.csvRow = tmp.originalData.sku + ', ' + tmp.originalData.myPrice + ', ' + tmp.originalData.minPrice + ', ' + tmp.originalData.priceDiff;
			$H(tmp.me._companyAliases).each(function(alias){
				tmp.csvRow = tmp.csvRow + ', ' + tmp.me.getCurrency(tmp.originalData.companyPrices[alias.key].price);
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
	/**
	 * Getting a single row of the result table
	 */
	,_getProductLineItem: function(listGroupDiv, dataKeyIndex, dataKeys) {
		var tmp = {};
		tmp.me = this;
		tmp.data = tmp.me._uploadedData[dataKeys[dataKeyIndex]];
		tmp.newRow = new Element('tr', {'class': 'result_row info'})
			.insert({'bottom': new Element('td').update(tmp.data.sku) })
			.insert({'bottom': new Element('td').update(tmp.me.getCurrency(tmp.data.price)) })
			.insert({'bottom': new Element('td',{'colspan': 2}).update('<strong>Loading...</strong>') });
		tmp.me.postAjax(tmp.me.getCallbackId('getAllPricesForProduct'), {'sku': tmp.data.sku, 'price': tmp.data.price, 'companyAliases': tmp.me._companyAliases}, {
			'onLoading': function(sender, param) {
				listGroupDiv.insert({'bottom': tmp.newRow });
			}
			,'onSuccess': function (sender, param) {
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					if(!tmp.result || !tmp.result.item)
						return;
					
					tmp.newRow.update('').removeClassName('info').store('data', tmp.result.item)
						.insert({'bottom': new Element('td').update(tmp.result.item.sku) })
						.insert({'bottom': new Element('td').update(tmp.me.getCurrency(tmp.result.item.myPrice)) })
						.insert({'bottom': new Element('td', {'class': 'price_diff' + (tmp.result.item.priceDiff > 0 ? ' over_priced' : '')}).update(tmp.me.getCurrency(tmp.result.item.priceDiff)) })
						.insert({'bottom': new Element('td', {'class': 'price_min', 'title': 'Min. Price among all Companies'}).update(tmp.me.getCurrency(tmp.result.item.minPrice)) });
					$H(tmp.me._companyAliases).each(function(alias){
						tmp.newRow.insert({'bottom': new Element('td').update(
							tmp.result.item.companyPrices[alias.key].priceURL.blank() ? 
							new Element('span', {'title': 'No value has been found for "' + alias.key + '" based on sku: ' + tmp.data.sku}).update( tmp.me.getCurrency(tmp.result.item.companyPrices[alias.key].price) ) : 
							new Element('a', {'href': tmp.result.item.companyPrices[alias.key].priceURL}).update( tmp.me.getCurrency(tmp.result.item.companyPrices[alias.key].price) )
						) })
					});
				}  catch (e) {
					tmp.newRow.update('').removeClassName('info').addClassName('danger').store('data', tmp.data)
						.insert({'bottom': new Element('td').update(tmp.data.sku) })
						.insert({'bottom': new Element('td').update(tmp.me.getCurrency(tmp.data.price)) })
						.insert({'bottom': new Element('td',{'colspan': 2}).update('<strong>ERROR:</strong>' + e) })
				}
			}
			,'onComplete': function(sender, param) {
				try {
					tmp.nextDataKeyIndex = dataKeyIndex * 1 + 1;
					if(tmp.nextDataKeyIndex >= dataKeys.size()) { //this is the last row
						tmp.errRows = $(tmp.me.id_wrapper).getElementsBySelector('.result_row.danger');
						listGroupDiv.up('.panel').removeClassName('panel-danger').addClassName(tmp.errRows.size() > 0 ? 'panel-warning' : 'panel-success').down('.panel-heading').update('')
							.insert({'bottom': new Element('panel-title').update((tmp.errRows.size() > 0 ? 'All provided rows have been proccessed, but with ' + tmp.errRows.size() + ' error(s)' : 'All provided rows have been proccessed successfully') ) })
							.insert({'bottom': new Element('span',{'class': 'btn btn-success btn-xs pull-right'})
								.update('Export To Excel') 
								.observe('click', function() {
									tmp.me.genCSV(this);
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
		tmp.theadRow = new Element('tr')
			.insert({'bottom': new Element('th').update('SKU') })
			.insert({'bottom': new Element('th').update('My Price') })
			.insert({'bottom': new Element('th', {'class': 'price_diff'}).update('Price Diff.') })
			.insert({'bottom': new Element('th', {'class': 'price_min'}).update('Min Price') });
		$H(tmp.me._companyAliases).each(function(alias){
			tmp.theadRow.insert({'bottom': new Element('th').update(alias.key) })
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