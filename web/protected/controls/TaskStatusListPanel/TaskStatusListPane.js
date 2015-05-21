var TaskStatusListPanelJs = new Class.create();
TaskStatusListPanelJs.prototype = Object.extend(new BPCPageJs(), {
	_pageJs: null

	,initialize: function(_pageJs) {
		this._pageJs = _pageJs;
	}

	,render: function(entityName, entityId) {
		var tmp = {};
		tmp.me = this;
		tmp.me.entityName = entityName;
		tmp.me.entityId = entityId;


		return tmp.serials;
	}
});