var PageJs=new Class.create();PageJs.prototype=Object.extend(new CRUDPageJs(),{_getTitleRowData:function(){return{description:"Description",name:"Name",allowMultiple:"multi?"}},_getEditPanel:function(b){var a={};a.me=this;a.newDiv=new Element("tr",{"class":"save-item-panel info"}).store("data",b).insert({bottom:new Element("input",{type:"hidden","save-item-panel":"id",value:b.id?b.id:""})}).insert({bottom:new Element("td",{"class":"form-group"}).insert({bottom:new Element("input",{required:true,"class":"form-control",placeholder:"The name","save-item-panel":"name",value:b.name?b.name:""})})}).insert({bottom:new Element("td",{"class":"form-group"}).insert({bottom:new Element("input",{"class":"form-control",placeholder:"Optional - The description","save-item-panel":"description",value:b.description?b.description:""})})}).insert({bottom:new Element("td",{"class":"form-group"}).insert({bottom:new Element("input",{type:"checkbox","class":"form-control","save-item-panel":"allowMultiple",checked:b.allowMultiple})})}).insert({bottom:new Element("td",{"class":"text-right"}).insert({bottom:new Element("span",{"class":"btn-group btn-group-sm"}).insert({bottom:new Element("span",{"class":"btn btn-success",title:"Save"}).insert({bottom:new Element("span",{"class":"glyphicon glyphicon-ok"})}).observe("click",function(){a.btn=this;a.me._saveItem(a.btn,$(a.btn).up(".save-item-panel"),"save-item-panel")})}).insert({bottom:new Element("span",{"class":"btn btn-danger",title:"Delete"}).insert({bottom:new Element("span",{"class":"glyphicon glyphicon-remove"})}).observe("click",function(){if(b.id){$(this).up(".save-item-panel").replace(a.me._getResultRow(b))}else{$(this).up(".save-item-panel").remove()}})})})});return a.newDiv},_getResultRow:function(c,a){var b={};b.me=this;b.tag=(b.isTitle===true?"th":"td");b.isTitle=(a||false);b.row=new Element("tr",{"class":(b.isTitle===true?"":"btn-hide-row")}).store("data",c).insert({bottom:new Element(b.tag,{"class":"name col-xs-2"}).update(c.name)}).insert({bottom:new Element(b.tag,{"class":"description"}).update(c.description)}).insert({bottom:new Element(b.tag,{"class":"allowMultiple col-xs-1"}).insert({bottom:(b.isTitle===true?c.allowMultiple:new Element("input",{type:"checkbox",disabled:true,checked:c.allowMultiple}))})}).insert({bottom:new Element(b.tag,{"class":"text-right btns col-xs-2"}).update(b.isTitle===true?(new Element("span",{"class":"btn btn-primary btn-xs",title:"New"}).insert({bottom:new Element("span",{"class":"glyphicon glyphicon-plus"})}).insert({bottom:" NEW"}).observe("click",function(){$(this).up("thead").insert({bottom:b.me._getEditPanel({})})})):(new Element("span",{"class":"btn-group btn-group-xs"}).insert({bottom:new Element("span",{"class":"btn btn-default",title:"Edit"}).insert({bottom:new Element("span",{"class":"glyphicon glyphicon-pencil"})}).observe("click",function(){$(this).up(".item_row").replace(b.me._getEditPanel(c))})}).insert({bottom:new Element("span",{"class":"btn btn-danger",title:"Delete"}).insert({bottom:new Element("span",{"class":"glyphicon glyphicon-trash"})}).observe("click",function(){if(!confirm("Are you sure you want to delete this item?")){return false}b.me._deleteItem(c)})})))});return b.row}});