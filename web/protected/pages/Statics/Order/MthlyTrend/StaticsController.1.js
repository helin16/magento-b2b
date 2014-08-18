/**
 * The page Js file
 */
var PageJs = new Class.create();
PageJs.prototype = Object.extend(new BPCPageJs(), {
	load: function(resultDiv, data) {
		console.debug(data)
		jQuery(resultDiv).highcharts({
	        chart: {
	            type: 'line'
	        },
	        title: {
	            text: 'BPC: Monthly Order Trend',
	            x: -20 //center
	        },
	        subtitle: {
	            text: 'This is just order trend from last 12 month',
	            x: -20
	        },
	        xAxis: {
	            categories: data.xAxis //['Apples', 'Bananas', 'Oranges']
	        },
	        yAxis: {
	            title: {
	                text: 'No of Orders'
	            }
	        },
	        series: data.series //[{ name: 'Jane', data: [1, 0, 4]}, {name: 'John',data: [5, 7, 3]}]
	    });
	}
});
