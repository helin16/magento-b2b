var PageJs=new Class.create;PageJs.prototype=Object.extend(new CRUDPageJs,{_getTitleRowData:function(){return{serialNo:"Serial No.",qty:"Qty",product:"Product",unitPrice:"Unit Cost(Excl. GST)",invoiceNo:"Invoice No.",created:"Received By",purchaseOrder:"Purchase Order"}},_bindSearchKey:function(){var e={};return e.me=this,$("searchPanel").getElementsBySelector("[search_field]").each(function(t){t.observe("keydown",function(t){e.me.keydown(t,function(){$(e.me.searchDivId).down("#searchBtn").click()})})}),this},_getEditPanel:function(e){var t={};return t.me=this,t.newDiv=new Element("tr",{"class":"save-item-panel info"}).store("data",e).insert({bottom:new Element("input",{type:"hidden","save-item-panel":"id",value:e.id?e.id:""})}).insert({bottom:new Element("td",{"class":"form-group"}).insert({bottom:new Element("input",{required:!0,"class":"form-control",placeholder:"The name of the Prefer Location Type","save-item-panel":"name",value:e.name?e.name:""})})}).insert({bottom:new Element("td",{"class":"form-group"}).insert({bottom:new Element("input",{"class":"form-control",placeholder:"Optional - The description of the Prefer Location Type","save-item-panel":"description",value:e.description?e.description:""})})}).insert({bottom:new Element("td",{"class":"text-right"}).insert({bottom:new Element("span",{"class":"btn-group btn-group-sm"}).insert({bottom:new Element("span",{"class":"btn btn-success",title:"Save"}).insert({bottom:new Element("span",{"class":"glyphicon glyphicon-ok"})}).observe("click",function(){t.btn=this,t.me._saveItem(t.btn,$(t.btn).up(".save-item-panel"),"save-item-panel")})}).insert({bottom:new Element("span",{"class":"btn btn-danger",title:"Delete"}).insert({bottom:new Element("span",{"class":"glyphicon glyphicon-remove"})}).observe("click",function(){e.id?$(this).up(".save-item-panel").replace(t.me._getResultRow(e).addClassName("item_row").writeAttribute("item_id",e.id)):$(this).up(".save-item-panel").remove()})})})}),t.newDiv},_getResultRow:function(e,t){var n={};return n.me=this,n.tag=n.isTitle===!0?"th":"td",n.isTitle=t||!1,n.row=new Element("tr",{"class":n.isTitle===!0?"":"btn-hide-row"}).store("data",e).insert({bottom:new Element(n.tag,{"class":"col-xs-1"}).update(e.serialNo)}).insert({bottom:new Element(n.tag,{"class":"col-xs-1"}).update(e.qty)}).insert({bottom:new Element(n.tag,{"class":"col-xs-3"}).update(n.isTitle===!0?e.product:new Element("a",{href:"/product/"+e.product.id+".html",target:"_BLANK"}).update(e.product.sku))}).insert({bottom:new Element(n.tag,{"class":"col-xs-1"}).update(n.isTitle===!0?e.unitPrice:n.me.getCurrency(e.unitPrice))}).insert({bottom:new Element(n.tag,{"class":"col-xs-3"}).update(n.isTitle===!0?e.purchaseOrder:new Element("a",{href:"/purchase/"+e.purchaseOrder.id+".html",target:"_BLANK"}).update(e.purchaseOrder.purchaseOrderNo+" ["+e.purchaseOrder.status+"]"))}).insert({bottom:new Element(n.tag,{"class":"col-xs-2"}).update(n.isTitle===!0?e.created:e.createdBy.person.fullname+" @ "+n.me.loadUTCTime(e.created).toLocaleString())}),n.row}});