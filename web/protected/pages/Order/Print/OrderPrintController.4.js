/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new BPCPageJs(), {
	_maxRowsPerPage: 8
	,genPage: function(table, pageNo, totalPages, rows) {
		var tmp = {};
		tmp.me = this;
		tmp.table = table.clone(true);
		tmp.thead = tmp.table.down('thead').clone(true);
		tmp.table.down('thead').remove();
		tmp.tfoot = tmp.table.down('tfoot').clone(true);
		tmp.table.down('tfoot').remove();
		tmp.newPageDiv = new Element('div', {'class': 'print-page-wrap', 'style': 'margin: 20px 0;'})
			.update(tmp.table)
			.insert({'bottom': new Element('div', {'class': 'print-page-footer'}).update(tmp.tfoot.down('tr').wrap( new Element('table', {'class': 'orderview'}) ) )});
		tmp.tbody = tmp.table.down('tbody').update(tmp.thead.down('tr.header').clone(true));
		tmp.thead.down('tr.header').remove();
		tmp.newPageDiv.insert({'top': new Element('div', {'class': 'print-page-header'}).update(
				new Element('table', {'class': 'orderview'}).update(tmp.thead.innerHTML)
		) });
		rows.each(function(tr) {
			tmp.tbody.insert({'bottom': tr});
		});
		tmp.noColumns = rows[0].getElementsBySelector('td').size();
		if(rows.size() < tmp.me._maxRowsPerPage) {
			for(tmp.j = 0 ; tmp.j < (tmp.me._maxRowsPerPage * 1 - rows.size()); tmp.j = tmp.j * 1 + 1) {
				tmp.emptyTr = rows[0].clone(true).update('');
				for(tmp.i = 0 ; tmp.i < tmp.noColumns; tmp.i = tmp.i * 1 + 1) {
					tmp.emptyTr.insert({'bottom': new Element('td').update('&nbsp;')});
				}
				tmp.tbody.insert({'bottom': tmp.emptyTr});
			}
		}
//		tmp.table.down('#tfoot').insert({'bottom': new Element('td', {'colspan': tmp.noColumns})
//			.setStyle('text-align: right')
//			.update('Page: ' + pageNo + ' / ' + totalPages)
//			.wrap(new Element('tr'))
//		});
		return tmp.newPageDiv;
	}
	,formatForPDF: function() {
		var tmp = {};
		tmp.me = this;
		tmp.mainTable = $('main-table').clone(true);

		tmp.pageTrs = [];
		tmp.pageRows = [];
		tmp.index = 0;
		tmp.mainTable.down('#tbody').getElementsBySelector('tr').each(function(row) {
			if(tmp.index >= tmp.me._maxRowsPerPage) {
				tmp.pageTrs.push(tmp.pageRows.clone(true));
				tmp.pageRows = [];
				tmp.index = 0;
			}
			if(!row.down('.sku').innerHTML.blank()) {
				tmp.pageRows.push(row);
				tmp.index = tmp.index * 1 + 1;
			}
		});
		if(tmp.pageRows.size() > 0) {
			tmp.pageTrs.push(tmp.pageRows);
		}

		tmp.wrapper = $('main-table').up().update('');
		tmp.pageNo = 1;
		tmp.totalPages = tmp.pageTrs.size();
		tmp.pageTrs.each(function(pageRows) {
			tmp.wrapper.insert({'bottom': tmp.me.genPage(tmp.mainTable, tmp.pageNo++, tmp.totalPages, pageRows)});
		});


		return tmp.me;
	}
});