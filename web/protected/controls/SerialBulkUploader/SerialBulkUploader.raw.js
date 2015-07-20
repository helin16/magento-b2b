var SerialBulkUploaderJs = new Class.create();
SerialBulkUploaderJs.prototype = Object.extend(new BPCPageJs(), {
	_pageJs: null

	,initialize: function(_pageJs, product) {
		this._pageJs = _pageJs;
		this.product = product;
	}

	,getInputPanel: function() {
		var tmp = {};
		tmp.me = this;
		tmp.serials = [];
		tmp.newDiv = new Element('div', {'class': 'bulkSerialPanel'})
			.store('data', null)
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('div', {'class': 'col-sm-4'})
					.insert({'bottom': new Element('div', {'class': 'form-group form-group-sm input-group'})
						.insert({'bottom': new Element('div', {'class': 'input-group-addon'}).update('Unit Price') })
						.insert({'bottom': new Element('input', {'class': 'serial-info form-control input-sm', 'serial-info': 'unitPrice', 'type': 'number', 'required': 'Required!', 'placeholder': 'Unit Price', 'title': 'Unit Price'}) })
					})
				})
				.insert({'bottom': new Element('div', {'class': 'col-sm-4'})
					.insert({'bottom': new Element('div', {'class': 'form-group form-group-sm input-group'})
						.insert({'bottom': new Element('div', {'class': 'input-group-addon'}).update('Inv. No.') })
						.insert({'bottom': new Element('input', {'class': 'serial-info form-control input-sm', 'serial-info': 'invoiceNo', 'type': 'text', 'required': 'Required!', 'placeholder': 'Invoice Number', 'title': 'Invoice Number'}) })
					})
				})
				.insert({'bottom': new Element('div', {'class': 'col-sm-4'})
					.insert({'bottom': new Element('div', {'class': 'form-group form-group-sm input-group'})
						.insert({'bottom': new Element('div', {'class': 'input-group-addon'}).update('Comments') })
						.insert({'bottom': new Element('input', {'class': 'serial-info form-control input-sm', 'serial-info': 'comments', 'type': 'text', 'placeholder': 'Comments', 'title': 'Comments'}) })
					})
				})
			})
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('div', {'class': 'col-sm-12'})
					.insert({'bottom': new Element('span').update('<b>Serial Numbers</b>')})
					.insert({'bottom': new Element('span', {'class': 'pull-right serials-bulk-qty'}).setStyle('font-weight: bold;').update('Qty: ' + tmp.serials.length )})
				})
				.insert({'bottom': new Element('div', {'class': 'col-sm-12'})
					.insert({'bottom': new Element('textarea', {'class': 'form-control', 'rows': 20, 'name': 'serials', 'type': 'textarea', 'placeholder': 'Serial Numbers', 'title': 'Serial Numbers'})
						.observe('change', function(e){
							tmp.txtArea = $(this);
							tmp.serials = tmp.me._expressionMatch($F(tmp.txtArea));
							tmp.txtArea.up('.bulkSerialPanel').store('serials', tmp.serials);
							tmp.txtArea.value = tmp.serials.join('\n');
							tmp.txtArea.up('.bulkSerialPanel').getElementsBySelector('.serials-bulk-qty').each(function(item){
								item.update('Qty: ' + tmp.serials.length);
							});
						})
					})
				})
				.insert({'bottom': new Element('div', {'class': 'col-sm-12'})
					.insert({'bottom': new Element('span', {'class': 'pull-right serials-bulk-qty'}).setStyle('font-weight: bold;').update('Qty: ' + tmp.serials.length)})
				})
			})
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('div', {'class': 'col-sm-12'})
					.insert({'bottom': new Element('span', {'class': 'btn btn-md btn-success pull-right confimBtn'}).update('Confirm')
						.observe('click', function(e){
							tmp.btn = $(this);
							tmp.btn.clicked = true;
							tmp.me._signRandID(tmp.btn);
							jQuery('#' + tmp.btn.id).button('loading');
							if(tmp.btn.clicked === true) {
								tmp.btn.clicked = false;
								tmp.data = [];
								tmp.confirmBtn = $(this);
								tmp.serials = $(this).up('.bulkSerialPanel').retrieve('serials', tmp.serials);
								tmp.serials.each(function(item){
									tmp.rawData = tmp.me._collectFormData(tmp.confirmBtn.up('.bulkSerialPanel'), 'serial-info');
									if(tmp.rawData !== null) {
										tmp.rawData.qty = 1;
										tmp.rawData.serialNo = item;
										if(tmp.rawData.comments === '')
											tmp.rawData.comments = 'BULK IMPORTED';
										tmp.data.push(tmp.rawData);
										tmp.data.product = tmp.me.product;
									}
								});
								if(tmp.me._collectFormData(tmp.confirmBtn.up('.bulkSerialPanel'), 'serial-info') !== null)
									$(this).up('.bulkSerialPanel').store('data', tmp.data)
							}
							jQuery('#' + tmp.btn.id).button('reset');
						})
					})
				})
			})
		; // end new Div
		
		return tmp.newDiv;
	}
	,_expressionMatch: function(text, replaceSpace) {
		var tmp = {};
		tmp.me = this;
		tmp.text = text;
		tmp.replaceSpace = (replaceSpace || true);
		
		tmp.text = tmp.text.replace(/(\s*),(\s*)/g, "\n").replace(/(\s*);(\s*)/g, "\n");
		if(tmp.replaceSpace === true)
			tmp.text = tmp.text.replace(/(\s*)\s(\s*)/g, "\n");
		
		tmp.serials = [];
		tmp.text.split('\n').each(function(item){
			if(!item.blank())
				tmp.serials.push(item.strip());
		});
		
		return tmp.serials;
	}
});