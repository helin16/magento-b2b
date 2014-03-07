/**
 * The LatestETAPanel js file
 */
var LatestETAPanel = new Class.create();
LatestETAPanel.prototype = {
		
	_pageObj: null	

	//constructor
	,initialize : function(pageJs) {
		this._pageObj = pageJs;
		//alert('constructor loaded');
	}
	
	,resultDiv: ''
	,callBackId: ''
	,pagination: {"pageNo" : 1, "pageSize": 10}	
	
	,setPagination: function(pageNo, pageSize) {
		var tmp = {};
		tmp.me = this;
		tmp.pageNo = (pageNo && !isNaN(pageNo)) ? pageNo : tmp.me.pagination.pageNo;
		tmp.pageSize = (pageSize && !isNaN(pageSize)) ? pageSize : tmp.me.pagination.pageSize;
		tmp.me.pagination.pageNo = tmp.pageNo;
		tmp.me.pagination.pageSize = tmp.pageSize;
		return tmp.me;
	}

	,_generateResultRow: function(row, isTitle) {
		var tmp = {};
		tmp.me = this;
		tmp.isTitle = (isTitle === true ? true : false);
		
		tmp.returnDiv = new Element("div", {"class": (tmp.isTitle === true ? "row header" : "row"), "order_item_id": (tmp.isTitle === true ? "undefined" : row.id)});
		
		return tmp.returnDiv
					.insert({'bottom': new Element("span", {"class": "cell sku"}).update(row.sku) })
					.insert({'bottom': new Element("span", {"class": "cell product_name"}).update(row.productName) })
					.insert({'bottom': new Element("span", {"class": "cell eta"}).update(row.eta) })
					.insert({'bottom': new Element("span", {"class": "cell order_no"}).update(row.orderNo) 
						.observe('click', function() {
							jQuery.fancybox({
								'width'			: '80%',
								'height'		: '90%',
								'autoScale'     : true,
								'type'			: 'iframe',
								'href'			: '/orderdetails/' + row.orderId + '.html'
					 		});
						})
					});
	}
	
	,loadLatestETA: function(reset) {
		var tmp = {};
		tmp.me = this;
		tmp.reset = (reset || true);
		
		tmp.me._pageObj.postAjax(tmp.me.callBackId, {'pagination': tmp.me.pagination}, {
			'onLoading': function () {
			}
			,'onComplete': function(sender, param) {
				try
				{
					tmp.result = tmp.me._pageObj.getResp(param, false, true);
					if(!tmp.result)
						return;
					if(tmp.reset === true) {
						if(tmp.result.items.length === 0) {
							if($(tmp.me.resultDiv)) {
								$(tmp.me.resultDiv).update("No ETA found!");
									return;	
							}
						}
						//title row
						tmp.headerRow = {"id": "", "sku": "SKU", "productName": "Name", "eta": "ETA", "orderNo": "Order No"};
						tmp.titleDiv = tmp.me._generateResultRow(tmp.headerRow,  true);
						$(tmp.me.resultDiv).insert({'bottom': tmp.titleDiv});
					}
					
					//add data
					tmp.result.items.each(function(row) {
						$(tmp.me.resultDiv).insert({'bottom': tmp.me._generateResultRow(row) });
					});
					
					if(tmp.result.totalPages > tmp.result.pageNumber) {
						$(tmp.me.resultDiv).insert({'bottom': new Element('span', {}).update("Show More") 
							.observe('click', function() {
								tmp.me.pagination.pageNo = (tmp.me.pagination.pageNo*1) + 1;
								tmp.me.loadLatestETA(false);
							})
						});
					}
					
				} 
				catch (e) 
				{
					alert(e);
				}
			}
		});
	}

}
