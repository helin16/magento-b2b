/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new BPCPageJs(), {
	
	dropShowDiv: {'dropDiv': '', 'showDiv': '', 'resultDiv': ''}
	,_acceptableTypes: ['csv']
	,_fileReader: null
	,csvSeperator: ','
	,csvFileLineFormat: ['sku', 'price']
	,csvNewLineSeperator: '\r\n'
	,allFileLineArray: []
	,companyNameArray: []
	
	/*
	,intializeFileReader: function() {
		var tmp = {};
		tmp.me = this;
		tmp.me._fileReader = new FileReader();
		return tmp.me;
	}
	*/
		
	,parseCSVFile: function(lines) {
		var tmp = {};
		tmp.me = this;
		tmp.outputArray = [];
		tmp.linesArray = lines.split(tmp.me.csvNewLineSeperator);
		tmp.linesArray.each(function(line) {
			line = line.strip();
			if((line.blank() || line === null || line === ''))
			{
//					tmp.outputArray.push({});
				//console.debug('empty line');
			}	
			else
			{
				tmp.tmpLineArray = {};
				tmp.lineArray = line.split(tmp.me.csvSeperator);
				for(tmp.i = 0; tmp.i < tmp.me.csvFileLineFormat.size(); tmp.i++)
					tmp.tmpLineArray[tmp.me.csvFileLineFormat[tmp.i]] = (tmp.lineArray[tmp.i] !== undefined ? tmp.lineArray[tmp.i] : '');
				
				tmp.outputArray.push(tmp.tmpLineArray);
			}	
		});
		return tmp.outputArray;
	}	
	
	,initializeFileHandler: function() {
		var tmp = {};
		tmp.me = this;
		
		$(tmp.me.dropShowDiv.dropDiv).observe('dragover', function(event) {
				tmp.me.handleDragOver(event);
			})
			.observe('drop', function(event) {
				tmp.me.handleFileSelect(event);
			});
		
		return tmp.me;
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
		
		/// reset the centralized file array ///
		tmp.me.allFileLineArray = [];
		
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
		
		if(tmp.validFiles.size() === 0)
		{
			alert("NO VALID FILES UPLOADED!!! PLS TRY AGAIN");
			return;	
		}
		
		/// we have found some valid files , so start reading them ///
		tmp.allFileLineArray = tmp.me._readValidCSVFiles(tmp.validFiles);
		
		return tmp.me;
	}
	
	,_generatePriceRowForProduct: function(productPriceArray) {
		var tmp = {};
		tmp.me = this;
		tmp.ppArray = productPriceArray;
		
		if(tmp.ppArray.sku === '' || tmp.ppArray.sku === undefined || tmp.ppArray.sku === null || tmp.ppArray.sku.blank())
			return tmp.me;

		tmp.rowDiv = new Element('div', {'class': 'row'})
						.insert({'bottom': new Element('span', {'class': 'sku'}).update(tmp.ppArray.sku) })
						.insert({'bottom': new Element('span', {'class': 'minPrice'}).update(tmp.ppArray.minPrice) })
						.insert({'bottom': new Element('span', {'class': 'myPrice'}).update(tmp.ppArray.myPrice) });
		
		tmp.ppArray.data.each(function(item) {
			if((item.price.blank() || item.price === '' || item.price === null || item.price === undefined) && (item.priceURL.blank() || item.priceURL === '' || item.priceURL === null || item.priceURL === undefined))
				tmp.rowDiv.insert({'bottom': new Element('span', {'class': 'company'}).update(item.company) });
			else
				tmp.rowDiv.insert({'bottom': new Element('span', {'class': 'company'}).update(item.price) });
		});
		
		$(tmp.me.dropShowDiv.resultDiv).insert({'bottom': tmp.rowDiv });
		return tmp.me;
	}
	
	/// This function will be triggered when all the file(s) have finished loading ///
	,_allFileLoadFinished: function() {
		var tmp = {};
		tmp.me = this;
		
		if(tmp.me.allFileLineArray.size() === 0)
		{
			alert("EMPTY FILE(s) UPLOADED!!! PLS TRY AGAIN");
			return;	
		}
		console.debug(tmp.me.allFileLineArray);
		
		$(tmp.me.dropShowDiv.showDiv).insert({'bottom': new Element('span', {'class': 'button'})
			.update('Start Load')
			.observe('click', function() {
				$(tmp.me.dropShowDiv.resultDiv).update('');
				
				/// Generate the header for the price compare table ///
				tmp.headerCompanyArray = [];
				tmp.me.companyNameArray.each(function(cName) {
					tmp.headerCompanyArray.push({'price': '', 'priceURL': '', 'company': cName});
				});
				tmp.me._generatePriceRowForProduct({'sku': 'SKU', 'minPrice': 'Min Price', 'myPrice': 'My Price', 'data': tmp.headerCompanyArray});
				///////////////////////////////////////////////////////////
				
				tmp.me.allFileLineArray.each(function(item) {
					item.fileContent.each(function(line) {
						tmp.me.postAjax(tmp.me.getCallbackId('getAllPricesForProduct'), {'sku': line.sku, 'price': line.price}, {
							'onLoading': function(sender, param) {
								//$(btn).store('originValue', $F(btn)).addClassName('disabled').setValue('saving ...').disabled = true;
							}
							,'onComplete': function (sender, param) {
								try 
								{
									tmp.result = tmp.me.getResp(param, false, true);
									console.debug(tmp.result);
									tmp.me._generatePriceRowForProduct(tmp.result.items);
								} 
								catch (e) 
								{
									alert(e);
								}
							}
						})
					});
				});
			})
		});
		
		return tmp.me;
	}
	
	/*  This function read one file at a time and checks if this the last file to read,
	 * 	If not call itself to read the next file, 
	 * 	If yes show submit button  
	 */
	,_readSingleCSVFile: function(file, index, validFiles) {
		var tmp = {};
		tmp.me = this;
		tmp.reader = new FileReader();
	      
		tmp.reader.onload = function(event) { 
			tmp.contents = event.target.result;
			tmp.fileArray = tmp.me.parseCSVFile(tmp.contents);
//			console.debug(file.file.name);
//			console.debug(tmp.fileArray);
//			console.debug(tmp.fileArray.size());
//			console.debug('---------------------------------------------------------');
			
			$(tmp.me.dropShowDiv.showDiv).getElementsBySelector('[file_sequence='+ file.index +']')[0].insert({'bottom': new Element('div', {'class': 'msg'}).update('Loaded Successfully') });
			$(tmp.me.dropShowDiv.showDiv).getElementsBySelector('[file_sequence='+ file.index +']')[0].store(tmp.fileArray);
			if(tmp.fileArray.size() > 0)
				tmp.me.allFileLineArray.push({'fileIndex': file.index, 'fileName': file.file.name, 'fileContent': tmp.fileArray});
			
			index = (index*1) + 1;
			if(validFiles.size() > index)
				tmp.me._readSingleCSVFile(validFiles[index], index, validFiles);
			else
				tmp.me._allFileLoadFinished();
				
		}
		tmp.reader.readAsText(file.file);
		
		return tmp.me;
	}
	
	,_readValidCSVFiles: function(validFiles) {
		var tmp = {};
		tmp.me = this;
		tmp.me._readSingleCSVFile(validFiles[0], 0, validFiles);
		return tmp.me;
	}

});