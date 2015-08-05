var PageJs=new Class.create;
PageJs.prototype=Object.extend(new CRUDPageJs,{_getTitleRowData:function(){return{description:"Description",name:"Name",mageId:"mageId",noOfChildren:null}},_bindSearchKey:function(){var b;b=this;$("searchPanel").getElementsBySelector("[search_field]").each(function(c){c.observe("keydown",function(a){b.keydown(a,function(){$("searchBtn").click()})})});return this},_saveItem:function(b,c,a){var d,e,f,h,k,g,l;d=this;e=d._collectFormData(c,a);if(null!==e)return d.postAjax(d.getCallbackId("saveItem"),{item:e},
{onLoading:function(){e.id&&c.addClassName("item_row").writeAttribute("item_id",e.id);c.hide()},onSuccess:function(a,b){try{(f=d.getResp(b,!1,!0))&&f.item&&(h=$(d.resultDivId).down("tbody"),k=h.down(".item_row[item_id="+f.item.id+"]"),g=d._getResultRow(f.item).addClassName("item_row").writeAttribute("item_id",f.item.id),k?k.replace(g):f.parent&&f.parent.id&&(l=h.down(".item_row[item_id="+f.parent.id+"]"))?h.down("[parentId="+f.parent.id+"]")?l.insert({after:g}):l.replace(d._getResultRow(f.parent).addClassName("item_row").writeAttribute("item_id",
f.parent.id)):(h.insert({top:g}),c.remove(),$(d.totalNoOfItemsId).update(1*$(d.totalNoOfItemsId).innerHTML+1)))}catch(e){d.showModalBox('<span class="text-danger">ERROR:</span>',e,!0),c.show()}}}),d},_getEditPanel:function(b){var c,a;c=this;return(new Element("tr",{"class":"save-item-panel info"})).store("data",b).insert({bottom:new Element("input",{type:"hidden","save-item-panel":"id",value:b.id?b.id:""})}).insert({bottom:new Element("input",{type:"hidden","save-item-panel":"parentId",value:b.parent&&
b.parent.id?b.parent.id:""})}).insert({bottom:(new Element("td",{"class":"form-group"})).insert({bottom:new Element("input",{required:!0,"class":"form-control",placeholder:"The name","save-item-panel":"name",value:b.name?b.name:""})})}).insert({bottom:(new Element("td",{"class":"form-group"})).insert({bottom:new Element("input",{"class":"form-control",placeholder:"Optional - The description","save-item-panel":"description",value:b.description?b.description:""})})}).insert({bottom:(new Element("td",
{"class":"text-right"})).insert({bottom:(new Element("span",{"class":"btn-group btn-group-sm"})).insert({bottom:(new Element("span",{"class":"btn btn-success",title:"Save"})).insert({bottom:new Element("span",{"class":"glyphicon glyphicon-ok"})}).observe("click",function(){a=this;c._saveItem(a,$(a).up(".save-item-panel"),"save-item-panel")})}).insert({bottom:(new Element("span",{"class":"btn btn-danger",title:"Delete"})).insert({bottom:new Element("span",{"class":"glyphicon glyphicon-remove"})}).observe("click",
function(){b.id?$(this).up(".save-item-panel").replace(c._getResultRow(b).addClassName("item_row").writeAttribute("item_id",b.id)):$(this).up(".save-item-panel").remove()})})})})},_removeRowByParentId:function(b,c){var a;a=this;b.getElementsBySelector(".item_row[parentId="+c.id+"]").each(function(c){a._removeRowByParentId(b,c.retrieve("data"));c.remove()});return a},_getChildrenRows:function(b,c){var a,d,e,f;a=this;d=$(b).down(".icon");d.hasClassName("glyphicon-minus-sign")?(d.removeClassName("glyphicon-minus-sign").addClassName("glyphicon-plus-sign"),
a._removeRowByParentId($(a.resultDivId).down("tbody"),c)):(d.removeClassName("glyphicon-plus-sign").addClassName("glyphicon-minus-sign"),a.postAjax(a.getCallbackId("getItems"),{searchCriteria:{parentId:c.id},pagination:{pageNo:null,pageSize:a._pagination.pageSize}},{onLoading:function(){},onSuccess:function(c,d){try{(e=a.getResp(d,!1,!0))&&e.items&&(f=$(b).up(".item_row"),e.items.each(function(b){f.insert({after:a._getResultRow(b).addClassName("item_row").writeAttribute("item_id",b.id)})}))}catch(g){a.showModalBox('<span class="text-danger">ERROR:</span>',
g,!0)}}}));return a},_getPreName:function(b){var c,a,d,e;c=this;a="";if(!b.position)return a;d=b.position.split("|").size();a=new Element("small");for(e=1;e<d;e=1*e+1)a.insert({bottom:new Element("span",{"class":"treegrid-indent"})});0<b.noOfChildren?a.insert({bottom:(new Element("a",{href:"javascript: void(0);","class":"treegrid-explander"})).update(new Element("span",{"class":"icon glyphicon glyphicon-plus-sign"})).observe("click",function(){c._getChildrenRows(this,b)})}):a.insert({bottom:new Element("span",
{"class":"treegrid-explander"})});return a},_deleteItem:function(b){var c,a,d,e,f;c=this;a=$(c.resultDivId).down("tbody").down(".item_row[item_id="+b.id+"]");c.postAjax(c.getCallbackId("deleteItems"),{ids:[b.id]},{onLoading:function(){a&&a.hide()},onSuccess:function(b,k){try{(d=c.getResp(k,!1,!0))&&d.parents&&(e=1*$(c.totalNoOfItemsId).innerHTML-1,$(c.totalNoOfItemsId).update(0>=e?0:e),a&&a.remove(),d.parents.each(function(a){(f=$(c.resultDivId).down("tbody").down(".item_row[item_id="+a.id+"]"))&&
f.replace(c._getResultRow(a).addClassName("item_row").writeAttribute("item_id",a.id))}))}catch(g){c.showModalBox('<span class="text-danger">ERROR</span>',g,!0),a&&a.show()}}});return c},_getResultRow:function(b,c){var a={me:this};a.tag=!0===a.isTitle?"th":"td";a.isTitle=c||!1;a.btns=(new Element("span",{"class":"btn-group btn-group-xs"})).insert({bottom:(new Element("span",{"class":"btn btn-primary",title:"Add under this category"})).insert({bottom:new Element("span",{"class":"glyphicon glyphicon-plus"})}).observe("click",
function(){$(this).up(".item_row").insert({after:a.me._getEditPanel({parent:{id:b.id}})})})}).insert({bottom:(new Element("span",{"class":"btn btn-default",title:"Edit"})).insert({bottom:new Element("span",{"class":"glyphicon glyphicon-pencil"})}).observe("click",function(){$(this).up(".item_row").replace(a.me._getEditPanel(b))})});0<b.noOfChildren||a.btns.insert({bottom:(new Element("span",{"class":"btn btn-danger",title:"Delete"})).insert({bottom:new Element("span",{"class":"glyphicon glyphicon-trash"})}).observe("click",
function(){if(!confirm("Are you sure you want to delete this item?"))return!1;a.me._deleteItem(b)})});a.row=(new Element("tr",{"class":!0===a.isTitle?"":"btn-hide-row"})).store("data",b).insert({bottom:(new Element(a.tag,{"class":"name col-xs-4"})).insert({bottom:a.me._getPreName(b)}).insert({bottom:" "+b.name})}).insert({bottom:(new Element(a.tag,{"class":"mageId"})).update(b.mageId)}).insert({bottom:(new Element(a.tag,{"class":"description"})).update(b.description)}).insert({bottom:(new Element(a.tag,
{"class":"text-right btns col-xs-2"})).update(!0===a.isTitle?(new Element("span",{"class":"btn btn-primary btn-xs",title:"New"})).insert({bottom:new Element("span",{"class":"glyphicon glyphicon-plus"})}).insert({bottom:" NEW"}).observe("click",function(){$(this).up("thead").insert({bottom:a.me._getEditPanel({})})}):a.btns)});b.parent&&b.parent.id&&a.row.writeAttribute("parentId",b.parent.id);return a.row}});