/**
 * The page Js file
 */
var TooltipComments = new Class.create();
TooltipComments.prototype = {
	_requestData: {'entityId': null, 'entity': null, 'type': null, 'pagination': {pageNo: 1, pageSize: 30}}
	,_target: {'element': null, 'event': null}
	,_pageObj: null	

	//constructor
	,initialize : function(pageJs) {
		this._pageObj = pageJs;
	}

	,_getCommentRows: function(items) {
		var tmp = {};
		tmp.me = this;
		tmp.newDiv = jQuery('<div class="tipcommentslist"></div>');
		jQuery.each(items, function(index, value) {
			jQuery('<fieldset class="row"></fieldset>')
				.append(jQuery('<legend class="inlineblock type"></legend>').html(value.type))
				.append(jQuery('<span class="inlineblock who"></span>').html(value.createdBy.person.fullname))
				.append(jQuery('<span class="inlineblock when"></span>').html(value.created))
				.append(jQuery('<span class="inlineblock what"></span>').html(value.comments))
				.appendTo(tmp.newDiv);
		});
		return tmp.newDiv;
	}
		
	,_getCommentsData: function(event, api) {
		var tmp = {};
		tmp.me = this;
		jQuery.ajax({
			url: '/ajax/getComments', // Use href attribute as URL
			data: tmp.me._requestData,
			type: 'POST',
			dataType: 'json'
		})
		.then(function(result) {
			
			// Set the tooltip content upon successful retrieval
			tmp.result = 'Nothing found'
			if(result.items && result.items instanceof Array && result.items.length > 0) {
				tmp.result = tmp.me._getCommentRows(result.items);
			}
			api.set('content.text', tmp.result);
			
			//api.set('content.text', );
		}, function(xhr, status, error) {
			// Upon failure... set the tooltip content to error
			api.set('content.text', status + ': ' + error);
		});
		
		return 'Loading...'; // Set some initial text
	}
	
	,_getComments: function(btn, event) {
		var tmp = {};
		tmp.me = this;
		tmp.me._target.element = btn;
		tmp.me._target.event = event;
		tmp.me._requestData.entity = $(btn).readAttribute('tooltipcomments_entity');
		tmp.me._requestData.entityId = $(btn).readAttribute('tooltipcomments_entityid');
		tmp.me._requestData.type = $(btn).readAttribute('tooltipcomments_commentstype');
		jQuery(btn).qtip({
			position: {
	            my: 'right center',
	            at: 'left center',
	            target: jQuery(btn),
	            viewport: jQuery(window)
	        },
			overwrite: false, // Don't overwrite tooltips already bound
			show: {
                event: event.type, // Use the same event type as above
                ready: true // Show immediately - important!
            },
            hide: {
                fixed: true,
                delay: 300
            },
            style: {
                classes: 'qtip-bootstrap qtip-shadow'
            },
            content: {
            	text: function(event, api) {
            		return tmp.me._getCommentsData(event, api);
            	},
            	title: function(event, api) {
            		return 'Comments:';
            	}
            }
		});
	}
};