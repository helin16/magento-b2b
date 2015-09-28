/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new BPCPageJs(), {
	init: function() {
		var tmp = {};
		tmp.me = this;
		jQuery.each(jQuery('.select2'), function(index, element){
			jQuery(element).select2({
				multiple: true,
				ajax: { url: "/ajax/getAll",
					dataType: 'json',
					delay: 10,
					type: 'POST',
					data: function(params, page) {
						return {"searchTxt": 'name like ?', 'searchParams': ['%' + params + '%'], 'entityName': jQuery(element).attr('entityName'), 'pageNo': page};
					},
					results: function (data, page, query) {
						 tmp.result = [];
						 if(data.resultData && data.resultData.items) {
							 data.resultData.items.each(function(item){
								 tmp.result.push({'id': item.id, 'text': item.orderNo, 'data': item});
							 });
						 }
			    		 return { 'results' : tmp.result, 'more': (data.resultData && data.resultData.pagination && data.resultData.pagination.totalPages && page < data.resultData.pagination.totalPages) };
					},
					cache: true
				},
				formatResult : function(result) {
					if(!result)
						return '';
					if(jQuery(element).attr('entityName') === 'Product')
						return '<div class="row"><div class="col-xs-4">' + result.data.sku + '</div><div class="col-xs-8">' + result.data.name + '</div></div>';
					if(jQuery(element).attr('entityName') === 'ProductCategory')
						return '<div class="row"><div class="col-xs-12">' + result.data.namePath + '</div></div>';
					return '<div class="row"><div class="col-xs-12">' + result.data.name + '</div></div>';
				},
				formatSelection: function(result) {
					if(!result)
						 return '';
					tmp.text = result.data.name;
					if(jQuery(element).attr('entityName') === 'Product')
						tmp.text = result.data.sku;
					else if(jQuery(element).attr('entityName') === 'ProductCategory')
						tmp.text = result.data.namePath;
					tmp.newDiv = new Element('div').update(tmp.text);
					return tmp.newDiv;
				},
				escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
				minimumInputLength: 3
			});
		});
		return tmp.me;
	}
	,renderResult: function() {
		var tmp = {};
		tmp.me = this;
		tmp.me.columns = [
          {id: "title", name: "Title", field: "title", width: 200, formatter: function (row, cell, value, columnDef, dataContext) {
		    return '<a href="#">' + value + '</a>';
		  }},
          {id: "duration", name: "Duration", field: "duration", width: 100},
          {id: "%", name: "% Complete", field: "percentComplete", width: 100},
          {id: "start", name: "Start", field: "start", width: 100},
          {id: "finish", name: "Finish", field: "finish", width: 100},
          {id: "effort-driven", name: "Effort Driven", field: "effortDriven", width: 100}
        ].slice();
		tmp.me.data = [];
		for (var i = 0; i < 2000; i++) {
			tmp.me.data[i] = {
		      id: 'id_' + i, // needed for DataView
		      title: "Task " + i,
		      duration: "5 days",
		      percentComplete: Math.round(Math.random() * 100),
		      start: "01/01/2009",
		      finish: "01/05/2009",
		      effortDriven: (i % 5 == 0)
		    };
		  };

		jQuery('#' + tmp.me.getHTMLID('resultDiv')).slickgrid({
			columns: tmp.me.columns,
			data:tmp.me.data.slice(),
			slickGridOptions: {
				enableCellNavigation: true,
				enableColumnReorder: false,
				orceFitColumns: true,
				rowHeight: 35
			},
		});
		return tmp.me;
//		    columns: columns,
//		    data: data,
//		    slickGridOptions: {
//		      enableCellNavigation: true,
//		      enableColumnReorder: true,
//		      forceFitColumns: true,
//		      rowHeight: 35
//		    },
//		    // handleCreate takes some extra options:
//		    sortCol: undefined,
//		    sortDir: true,
//		    handleCreate: function () {
//		      var o = this.wrapperOptions;
//
//		      // checkbox column: add it
//		      var columns = o.columns.slice();
//		      var checkboxSelector = new Slick.CheckboxSelectColumn({});
//		      columns.unshift(checkboxSelector.getColumnDefinition());
//
//		      // configure grid with client-side data view
//		      var dataView = new Slick.Data.DataView();
//		      var grid = new Slick.Grid(this.element, dataView,
//		        columns, o.slickGridOptions);
//
//		      // selection model
//		      grid.setSelectionModel(new Slick.RowSelectionModel());
//		      grid.registerPlugin(checkboxSelector);
//
//		      // sorting
//		      var sortCol = o.sortCol;
//		      var sortDir = o.sortDir;
//		      function comparer(a, b) {
//		        var x = a[sortCol], y = b[sortCol];
//		        return (x == y ? 0 : (x > y ? 1 : -1));
//		      }
//		      grid.onSort.subscribe(function (e, args) {
//		          sortDir = args.sortAsc;
//		          sortCol = args.sortCol.field;
//		          dataView.sort(comparer, sortDir);
//		          grid.invalidateAllRows();
//		          grid.render();
//		      });
//
//		      // set the initial sorting to be shown in the header
//		      if (sortCol) {
//		          grid.setSortColumn(sortCol, sortDir);
//		      }
//
//		       // initialize the model after all the events have been hooked up
//		      dataView.beginUpdate();
//		      dataView.setItems(o.data);
//		      dataView.endUpdate();
//
//		      // if you don't want the items that are not visible (due to being filtered out
//		      // or being on a different page) to stay selected, pass 'false' to the second arg
//		      dataView.syncGridSelection(grid, true);
//
//		      grid.resizeCanvas(); // XXX Why is this needed? A possible bug?
//		                           // If this is missing, the grid will have
//		                           // a horizontal scrollbar, and the vertical
//		                           // scrollbar cannot be moved. A column reorder
//		                           // action fixes the situation.
//
//		    }

//		  });
	}
});