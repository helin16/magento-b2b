var PageJs=new Class.create;
PageJs.prototype=Object.extend(new BPCPageJs,{_resultDivId:"",_totalUserCountId:"",_pagination:{pageNo:1,pageSize:10},_searchCriteria:"",_roles:[],setResultDiv:function(b){this._resultDivId=b;return this},setTotalUserCountDiv:function(b){this._totalUserCountId=b;return this},_getEditField:function(b,c){return(new Element("fieldset")).insert({bottom:(new Element("label")).update(b+": ")}).insert({bottom:c})},_showEditPanel:function(b){var c,a,d;c=this;jQuery.fancybox({width:"80%",height:"90%",autoScale:!1,
autoDimensions:!1,fitToView:!1,autoSize:!1,type:"iframe",href:b?"/useraccount/edit/"+b.id+".html":"/useraccount/add.html",beforeClose:function(){$(c._resultDivId)&&$$("iframe.fancybox-iframe").first().contentWindow.pageJs&&(a=$$("iframe.fancybox-iframe").first().contentWindow.pageJs._user)&&((d=$(c._resultDivId).down(".useraccount_item[useraccount_id="+a.id+"]"))?d.replace(c._getItemRow(a)):$(c._resultDivId).down(".header").insert({after:c._getItemRow(a)}))}});return c},deleteUser:function(b,c){var a,
d,f;a=this;a.postAjax(a.getCallbackId("deleteUser"),{userId:b.id},{onCreate:function(a,b){$(c)&&jQuery("#"+c.id).button("loading")},onSuccess:function(b,c){try{if(d=a.getResp(c,!1,!0))(f=$(a._resultDivId).down("[useraccount_id="+d.id+"]"))&&f.remove(),$(a._totalUserCountId)&&$(a._totalUserCountId).update(1*$(a._totalUserCountId).innerHTML-1)}catch(h){alert(h)}},onComplete:function(a,b){jQuery("#"+c.id).button("reset")}});return a},_getItemRow:function(b,c){var a,d,f,e,g;a=this;d=c||!1;f="Roles";e=
new Element("div",{"class":"btn-group"});!0!==d&&(g=[],b.roles.each(function(a){g.push(a.name)}),f=g.join(", "),e.insert({bottom:(new Element("span",{id:"edit_btn_"+b.id,"class":"btn btn-default edit","data-loading-text":"Loading..."})).update(new Element("span",{"class":"glyphicon glyphicon-pencil",title:"Edit this user"})).observe("click",function(){a._showEditPanel(b)})}).insert({bottom:(new Element("span",{id:"del_btn_"+b.id,"class":"btn btn-danger delete","data-loading-text":"Processing..."})).update(new Element("span",
{"class":"glyphicon glyphicon-trash",title:"Delete this user"})).observe("click",function(){confirm("You are trying to delete user: "+b.person.fullname+"\nContinue?")&&a.deleteUser(b,this)})}));return(new Element("div",{"class":"useraccount_item list-group-item "+(!0===d?"disabled header":""),useraccount_id:b.id})).store("data",b).insert({bottom:(new Element("div",{"class":"row"})).insert({bottom:(new Element("div",{"class":"col-xs-2 firstName"})).update(b.person.firstName)}).insert({bottom:(new Element("div",
{"class":"col-xs-2 lastName"})).update(b.person.lastName)}).insert({bottom:(new Element("div",{"class":"col-xs-4 roles"})).update(f)}).insert({bottom:(new Element("div",{"class":"col-xs-2 username"})).update(b.username)}).insert({bottom:(new Element("div",{"class":"col-xs-2 btns"})).update(e)})})},_getNextPageBtn:function(){var b;b=this;return(new Element("div",{"class":"pagination"})).insert({bottom:(new Element("span",{"class":"button"})).update("Show More").observe("click",function(){b._pagination.pageNo=
1*b._pagination.pageNo+1;$(this).store("originVal",$(this).innerHTML).update("Fetching more results ...").addClassName("disabled");b.getUsers(this)})})},getUsers:function(b,c){var a,d,f,e,g;a=this;d=b||null;f=c||!1;e=$(a._resultDivId);a.postAjax(a.getCallbackId("getUsers"),{searchCriteria:a._searchCriteria,pagination:a._pagination},{onCreate:function(b,c){!0===f&&(e&&e.update(""),$(a._totalUserCountId)&&$(a._totalUserCountId).update(0));d&&jQuery(d.id).button("loading")},onSuccess:function(b,c){try{if(g=
a.getResp(c,!1,!0))$(a._totalUserCountId)&&$(a._totalUserCountId).update(g.pageStats.totalRows),e&&(!0===f&&e.update(a._getItemRow({person:{firstName:"First Name",lastName:"Last Name"},username:"Username"},!0).addClassName("header")),e.getElementsBySelector(".paginWrapper").each(function(a){a.remove()}),g.items.each(function(b){e.insert({bottom:a._getItemRow(b)})}),g.pageStats.pageNumber<g.pageStats.totalPages&&e.insert({bottom:a._getNextPageBtn().addClassName("paginWrapper")}))}catch(d){e.up(".panel").down(".panel-body").insert({bottom:a.getAlertBox("Error:",
d).addClassName("alert-danger")})}},onComplete:function(a,b){d&&jQuery(d.id).button("reset")}});return a}});