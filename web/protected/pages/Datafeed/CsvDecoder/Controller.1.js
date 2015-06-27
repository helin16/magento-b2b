/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new BPCPageJs(), {
	id_wrapper: '' //the html id of the wrapper
	,_acceptableTypes: ['csv', 'txt']
//	,csvFileLineFormat: []
	,_fileReader: null
	,_uploadedData: {}
	,_config: {ifHeader: true, ifShowTableContent: true, supplier: null, delimiter: ""}
	,config_div: null
	,file_upload_div: null
	,listing_div: null

	,load: function(predata) {
		var tmp = {}
		tmp.me = this;
		tmp.me._rowNo = 1;
		tmp.me.predata = predata;

		if (window.File && window.FileReader && window.FileList && window.Blob) { //the browser supports file reading api
			tmp.me._fileReader = new FileReader();
			$(tmp.me.getHTMLID('importerDiv')).update('')
				.insert({'bottom': tmp.me.config_div = tmp.me._getConifgDiv() })
				.insert({'bottom': tmp.me.file_upload_div = tmp.me._getFileUploadDiv() })
				.insert({'bottom': tmp.me.listing_div = tmp.me._getListingDiv().hide() });
		} else {
			$(tmp.me.getHTMLID('importerDiv')).update(tmp.me.getAlertBox('Warning:', 'Your browser does NOT support this feature. pls change and try again').addClassName('alert-warning') );
		}
		
		tmp.me._loadSelect2();
		tmp.me._loadBootstrapSwitch();
		return tmp.me;
	}
	,_getConifgDiv: function() {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = new Element('div')  
			.insert({'bottom': new Element('div',  {'class': 'row pre-config'})
				.insert({'bottom': new Element('div', {'class': 'col-xs-3'})
					.insert({'bottom': tmp.me.getFormGroup(new Element('label').update('Supplier: '),
							new Element('input', {'class': 'form-control select2', 'config': 'supplierId', 'name': 'supplier', 'placeholder': 'search a supplier name here'}) )
					})
				})
				.insert({'bottom': new Element('div', {'class': 'col-xs-1'})
					.insert({'bottom': tmp.me.getFormGroup(new Element('label').update('Header? ').setStyle('display: block;'),
							new Element('input', {'type': 'checkbox', 'data-off-text': 'No', 'data-on-text': 'Yes', 'class': 'form-control bootstrap-switch', 'config': 'ifHeader', 'name': 'ifHeader', 'title': 'does the csv include header?'}) )
					})
				})
				.insert({'bottom': new Element('div', {'class': 'col-xs-1'})
					.insert({'bottom': tmp.me.getFormGroup(new Element('label').update('Limit Table Rows? '),
							new Element('input', {'type': 'checkbox', 'data-off-text': 'No', 'data-on-text': 'Yes', 'class': 'form-control bootstrap-switch', 'config': 'ifShowTableContent', 'name': 'ifHeader', 'title': 'To speed up javascript'}) )
					})
				})
			});
		
		return tmp.newDiv;
	}
	,_getFileUploadDiv: function() {
		var tmp = {};
		tmp.me = this;
		tmp.me.FileUploader = new FileUploaderJs(tmp.me);
		tmp.newDiv =  tmp.me.FileUploader._getFileUploadDiv(tmp.me._acceptableTypes, function(data){tmp.me.displayLineItems(data);}, tmp.me._config.ifHeader, tmp.me._config.delimiter);
		
		return tmp.newDiv;
	}
	,_getListingDiv: function() {
		var tmp = {};
		tmp.me = this;
		
		tmp.newDiv = new Element('table', {'class': 'table table-xs table-hover table-striped table-condensed'})
			.insert({'bottom': new Element('thead')
				.insert({'bottom': new Element('tr', {'class': 'visible-xs visible-md visible-lg visible-sm'}) }) // class b/c boostrap bug
			})
			.insert({'bottom': new Element('tbody')
			});
		
		tmp.newDiv = new Element('div', {'class': 'col-lg-12'});
		tmp.newDiv.setStyle('width: ' + window.innerWidth*0.9 + 'px; height: ' + window.innerHeight*0.8 + 'px; overflow: hidden; outline: 0px none; position: relative;');
		
		return tmp.newDiv;
	}
	,displayLineItems: function(data) {
		var tmp = {};
		tmp.me = this;
		tmp.data = data;
		tmp.displayData = data;
		
		console.debug(tmp.data);
		//prepare data for SlickGrid
		tmp.columns = [];
		tmp.options = {
			enableCellNavigation : true,
			enableColumnReorder : false
		};
		tmp.me.listing_div.show();
		tmp.me._signRandID(tmp.me.listing_div);
		tmp.data.meta.fields.each(function(field){
			tmp.columns.push({id: field, name: field, field: field});
//			tmp.me.listing_div.show().down('thead tr').insert({'bottom': new Element('td').update(field) });
		});
		
		console.debug(tmp.data.data);
		tmp.grid = new Slick.Grid(jQuery("#" + tmp.me.listing_div.id), tmp.data.data, tmp.columns, tmp.options);
		jQuery("#" + tmp.me.listing_div.id).on('shown', tmp.grid.resizeCanvas)
		
//		if(tmp.me._config.ifShowTableContent === true)
//			tmp.displayData = tmp.data.data.slice(0,20);
//		tmp.displayData.each(function(row){
//			tmp.me.listing_div.show().down('tbody').insert({'bottom': tmp.tr = new Element('tr') });
//			$H(row).each(function(column){
//				tmp.tr.insert({'bottom': tmp.td = new Element('td', {'class': 'truncate', 'title': column.value}).update(column.value) });
//				tmp.me.observeClickNDbClick(tmp.td, null, function(){tmp.me.showModalBox('<b>'+column.key+'</b>', column.value)});
//			});
//		});
		
		return tmp.me;
	}
	,_processDatafeed: function(data, btn) {
		var tmp = {};
		tmp.me = this;
		tmp.data = data;
		tmp.btn = btn;
		tmp.me._signRandID(tmp.btn);
		
		// validate data
		if(!jQuery.isNumeric(tmp.me._config.supplier)) {
			tmp.me.showModalBox('<b>Error: </b>','<p class="text-danger">You must choose a <b>Supplier</b> fist</p>');
			return tmp.me;
		}
		
		tmp.me.postAjax(tmp.me.getCallbackId('processDatafeed'), {'data': tmp.data, 'config': tmp.me._config}, {
			'onLoading': function(sender, param) {
				jQuery('#' + tmp.btn.id).button('loading');
			}
			,'onSuccess': function (sender, param) {
				try {
					tmp.result = tmp.me.getResp(param, false, true);
					console.debug(tmp.result);
				}  catch (e) {
					alert(e);
				}
			}
			,'onComplete': function(sender, param) {
				try {
					jQuery('#' + tmp.btn.id).button('reset');
				} catch (e) {
					alert(e);
				}
			}
		});
		
		return tmp.me;
	}
//	,genCSV: function(btn) {
//		var tmp = {};
//		tmp.me = this;
//
//		//collect data
//		tmp.data = [];
//		tmp.originalDataRows = $(btn).up('.panel').getElementsBySelector('.result_row.result-done');
//		if(tmp.originalDataRows.length < 1)
//			return;
//
//		tmp.headerRow = '';
//		$H(tmp.originalDataRows[0].retrieve('data')).each(function(item){
//			tmp.headerRow = tmp.headerRow + item.key + ', ';
//		});
//		tmp.data.push(tmp.headerRow + '\n');
//
//		$(btn).up('.panel').getElementsBySelector('.result_row.result-done').each(function(row){
//			tmp.csvRow = '';
//
//			$H(row.retrieve('data')).each(function(item){
//				tmp.csvRow = tmp.csvRow + item.value + ', ';
//			});
//
//			tmp.csvRow = tmp.csvRow + '\n';
//			tmp.data.push(tmp.csvRow);
//			tmp.i = tmp.i * 1 + 1;
//		});
//
//		tmp.now = new Date();
//		tmp.fileName = tmp.me._importDataTypes + '_match_' + tmp.now.getFullYear() + '_' + tmp.now.getMonth() + '_' + tmp.now.getDate() + '_' + tmp.now.getHours() + '_' + tmp.now.getMinutes() + '_' + tmp.now.getSeconds() + '.csv';
//		tmp.blob = new Blob(tmp.data, {type: "text/csv;charset=utf-8"});
//		saveAs(tmp.blob, tmp.fileName);
//		return tmp.me;
//	}
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
						tmp.errRows = $(tmp.me.getHTMLID('importerDiv')).getElementsBySelector('.result_row.danger');
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

		//get header row
		
		tmp.headerData = tmp.me._uploadedData.slice(0,1);
		tmp.bodyData = tmp.me._uploadedData.slice(1);
		
		$(tmp.me.getHTMLID('importerDiv')).down('.list-group').removeClassName('list-group').addClassName('panel-body').insert({'bottom': tmp.table = new Element('table') });
		tmp.table.addClassName('table table-striped');
		
		
		tmp.bodyData.each(function(items){
			tmp.table.insert({'bottom': tmp.newRow = new Element('tr', {'class': ''}) });
			tmp.colCount = 0;
			items.each(function(item){
				if(tmp.colCount < 12) {
					tmp.newRow.insert({'bottom': new Element('td').update(item) });
					tmp.colCount += 1;
				}
			});
		});

		return tmp.me;
	}
	,_loadSelect2: function() {
		var tmp = {};
		tmp.me = this;
		tmp.selectBox = jQuery('[config="supplierId"]').select2({
			minimumInputLength: 1,
			multiple: false,
			ajax: {
				delay: 250
				,url: '/ajax/getAll'
				,type: 'POST'
				,data: function (params) {
					return {"searchTxt": 'name like ?', 'searchParams': ['%' + params + '%'], 'entityName': 'Supplier', 'pageNo': 1};
				}
				,results: function(data, page, query) {
					tmp.result = [];
					if(data.resultData && data.resultData.items) {
						data.resultData.items.each(function(item){
							tmp.result.push({'id': item.id, 'text': item.name, 'data': item});
						});
					}
					return { 'results' : tmp.result };
				}
			}
			,cache: true
			,escapeMarkup: function (markup) { return markup; } // let our custom formatter work
		})
		.on('change', function(){
			tmp.me._config.supplier = $(this).value;
		});
		
		return tmp.me;
	}
	,_loadBootstrapSwitch: function() {
		var tmp = {};
		tmp.me = this;
		tmp.checkBox = jQuery('[config="ifHeader"]').bootstrapSwitch('state', tmp.me._config.ifHeader === true, true)
			.on('switchChange.bootstrapSwitch',function(e,state){
				tmp.me._config.ifHeader = state
			});
		tmp.checkBox = jQuery('[config="ifShowTableContent"]').bootstrapSwitch('state', tmp.me._config.ifShowTableContent === true, true)
		.on('switchChange.bootstrapSwitch',function(e,state){
			tmp.me._config.ifShowTableContent = state
		});
		
		return tmp.me;
	}
});