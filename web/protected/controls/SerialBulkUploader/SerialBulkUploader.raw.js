var SerialBulkUploaderJs = new Class.create();
SerialBulkUploaderJs.prototype = {
	_pageJs: null

	,initialize: function(_pageJs) {
		this._pageJs = _pageJs;
	}

	,getInputPanel: function() {
		var tmp = {};
		tmp.me = this;
		tmp.serials = [];
		
		tmp.newDiv = new Element('div')
			.insert({'bottom': new Element('div', {'class': 'row'})
				.insert({'bottom': new Element('div', {'class': 'col-sm-4'})
					.insert({'bottom': new Element('div', {'class': 'form-group form-group-sm input-group'})
						.insert({'bottom': new Element('div', {'class': 'input-group-addon'}).update('Unit Price') })
						.insert({'bottom': new Element('input', {'class': 'form-control input-sm', 'name': 'unitPrice', 'type': 'value', 'placeholder': 'Unit Price', 'title': 'Unit Price'}) })
					})
				})
				.insert({'bottom': new Element('div', {'class': 'col-sm-4'})
					.insert({'bottom': new Element('div', {'class': 'form-group form-group-sm input-group'})
						.insert({'bottom': new Element('div', {'class': 'input-group-addon'}).update('Inv. No.') })
						.insert({'bottom': new Element('input', {'class': 'form-control input-sm', 'name': 'invNumber', 'type': 'text', 'placeholder': 'Invoice Number', 'title': 'Invoice Number'}) })
					})
				})
				.insert({'bottom': new Element('div', {'class': 'col-sm-4'})
					.insert({'bottom': new Element('div', {'class': 'form-group form-group-sm input-group'})
						.insert({'bottom': new Element('div', {'class': 'input-group-addon'}).update('Comments') })
						.insert({'bottom': new Element('input', {'class': 'form-control input-sm', 'name': 'comments', 'type': 'text', 'placeholder': 'Comments', 'title': 'Comments'}) })
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
							tmp.serials = tmp.me._expressionMatch($F(this));
							this.value = tmp.serials.join('\n');
							$$('.serials-bulk-qty').each(function(item){
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
					.insert({'bottom': new Element('span', {'class': 'btn btn-md btn-success pull-right'}).update('Confirm')
						.observe('click', function(e){
							console.debug(tmp.serials);
						})
					})
				})
			})
		; // end new Div
		
		return tmp.newDiv;
	}
	,_expressionMatch: function(text) {
		var tmp = {};
		tmp.me = this;
		tmp.text = text;
		
		tmp.text = tmp.text.replace(/(\s*),(\s*)/g, "\n").replace(/(\s*);(\s*)/g, "\n");
		tmp.serials = [];
		tmp.text.split('\n').each(function(item){
			if(!item.blank())
				tmp.serials.push(item.strip());
		});
		
		return tmp.serials;
	}
};