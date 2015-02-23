var PageJs=new Class.create;PageJs.prototype=Object.extend(new DetailsPageJs,{_customer:{},setPreData:function(e){return this._customer=e,this},_getItemDiv:function(){var e={};return e.me=this,e.newDiv=new Element("div",{"class":"customer-editing-container"}).insert({bottom:new Element("div",{"class":"row"}).insert({bottom:e.me._getCustomerSummaryDiv(e.me._item).wrap(new Element("div",{"class":"col-sm-12"}))})}).insert({bottom:new Element("div",{"class":"row"}).insert({bottom:e.me._getCustomerBillingSummaryDiv(e.me._item).wrap(new Element("div",{"class":"col-sm-6"}))}).insert({bottom:e.me._getCustomerShippingSummaryDiv(e.me._item).wrap(new Element("div",{"class":"col-sm-6"}))})}).insert({bottom:new Element("div",{"class":"row"}).insert({bottom:new Element("span",{id:"saveBtn","class":"btn btn-primary pull-right col-sm-4","data-loading-text":"saving ..."}).update("Save").observe("click",function(){e.me._submitSave(this)})})}),e.newDiv},_submitSave:function(e){var t={};return t.me=this,t.data=t.me._collectFormData($(t.me._htmlIds.itemDiv),"save-item"),t.data.id=t.me._customer.id?t.me._customer.id:"",null===t.data?t.me:(t.me.saveItem(e,t.data,function(e){t.me.showModalBox('<strong class="text-success">Saved Successfully!</strong>',"Saved Successfully!",!0),t.me._item=e.item,t.me.refreshParentWindow(),window.parent.jQuery.fancybox.close()}),t.me)},_bindSaveKey:function(){var e={};return e.me=this,$$(".customer-editing-container").first().getElementsBySelector("[save-item]").each(function(t){t.observe("keydown",function(t){e.me.keydown(t,function(){$$(".customer-editing-container").first().down("#saveBtn").click()})})}),this},refreshParentWindow:function(){var e={};e.me=this,window.parent&&(e.parentWindow=window.parent,e.row=$(e.parentWindow.document.body).down("#"+e.parentWindow.pageJs.resultDivId+" .item_row[item_id="+e.me._item.id+"]"),e.row&&e.row.replace(e.parentWindow.pageJs._getResultRow(e.me._item).addClassName("success")))},_getCustomerSummaryDiv:function(e){var t={};return t.me=this,t.item=e,t.newDiv=new Element("div",{"class":"panel panel-default"}).insert({bottom:new Element("div",{"class":"panel-heading"}).insert({bottom:new Element("a",{href:"javascript: void(0);",title:"click to show/hide below"}).insert({bottom:new Element("strong").update(t.item.name?"Editing: "+t.item.name:"Creating new customer: ")})}).observe("click",function(){$(this).up(".panel").down(".panel-body").toggle()})}).insert({bottom:new Element("div",{"class":"panel-body"}).insert({bottom:new Element("div",{"class":"row"}).insert({bottom:new Element("div",{"class":"col-sm-4"}).update(t.me._getFormGroup("Company Name / Customer Name",new Element("input",{required:!0,"save-item":"name",type:"text",value:t.item.name?t.item.name:""})))}).insert({bottom:new Element("div",{"class":"col-sm-4"}).update(t.me._getFormGroup("Email",new Element("input",{"save-item":"email",type:"email",value:t.item.email?t.item.email:""})))}).insert({bottom:new Element("div",{"class":"col-sm-3"}).update(t.me._getFormGroup("Contact No?",new Element("input",{"save-item":"contactNo",type:"value",value:t.item.contactNo?t.item.contactNo:""})))}).insert({bottom:new Element("div",{"class":"col-sm-1"}).update(t.me._getFormGroup("Active?",new Element("input",{"save-item":"active",type:"checkbox",checked:t.item.active?t.item.active:!0})))})})}),t.newDiv},_copyInfoFields:function(e,t,i,n){var s={};s.me=this,""!==$$(".customer-editing-container").first().down("#"+t+"-info #"+t+n+" input").value&&$(e).up(".panel").down("#"+i+n+" input").writeAttribute("value",$$(".customer-editing-container").first().down("#"+t+"-info #"+t+n+" input").value)},_getCustomerBillingSummaryDiv:function(e){var t={};return t.me=this,t.item=e,t.newDiv=new Element("div",{"class":"panel panel-default",id:"billing-info"}).insert({bottom:new Element("div",{"class":"panel-heading"}).insert({bottom:new Element("strong").update(t.item.name?"Billing Info: "+t.item.name:"Billing Info: new customer")}).insert({bottom:new Element("small",{"class":"pull-right"}).insert({bottom:new Element("button",{"class":"btn btn-default btn-xs",type:"button"}).update("Copy from Shipping")})}).observe("click",function(){t.me._copyInfoFields($(this),"shipping","billing","Name"),t.me._copyInfoFields($(this),"shipping","billing","ContactNo"),t.me._copyInfoFields($(this),"shipping","billing","Street"),t.me._copyInfoFields($(this),"shipping","billing","City"),t.me._copyInfoFields($(this),"shipping","billing","State"),t.me._copyInfoFields($(this),"shipping","billing","Country"),t.me._copyInfoFields($(this),"shipping","billing","Posecode")})}).insert({bottom:new Element("div",{"class":"panel-body"}).insert({bottom:new Element("div",{"class":"row"}).insert({bottom:new Element("div",{"class":"col-sm-12",id:"billingName"}).update(t.me._getFormGroup("Name",new Element("input",{"save-item":"billingName",type:"text",value:t.item.id?t.item.address.billing.contactName:""})))})}).insert({bottom:new Element("div",{"class":"row"}).insert({bottom:new Element("div",{"class":"col-sm-12",id:"billingContactNo"}).update(t.me._getFormGroup("Contact No.",new Element("input",{"save-item":"billingContactNo",type:"value",value:t.item.id?t.item.address.billing.contactNo:""})))})}).insert({bottom:new Element("div",{"class":"row"}).insert({bottom:new Element("div",{"class":"col-sm-6",id:"billingStreet"}).update(t.me._getFormGroup("Street",new Element("input",{"save-item":"billingStreet",type:"text",value:t.item.id?t.item.address.billing.street:""})))}).insert({bottom:new Element("div",{"class":"col-sm-6",id:"billingCity"}).update(t.me._getFormGroup("City",new Element("input",{"save-item":"billingCity",type:"text",value:t.item.id?t.item.address.billing.city:""})))})}).insert({bottom:new Element("div",{"class":"row"}).insert({bottom:new Element("div",{"class":"col-sm-4",id:"billingState"}).update(t.me._getFormGroup("State",new Element("input",{"save-item":"billingState",type:"text",value:t.item.id?t.item.address.billing.region:""})))}).insert({bottom:new Element("div",{"class":"col-sm-4",id:"billingCountry"}).update(t.me._getFormGroup("Country",new Element("input",{"save-item":"billingCountry",type:"text",value:t.item.id?t.item.address.billing.country:"AU"})))}).insert({bottom:new Element("div",{"class":"col-sm-4",id:"billingPosecode"}).update(t.me._getFormGroup("Post Code",new Element("input",{"save-item":"billingPosecode",type:"text",value:t.item.id?t.item.address.billing.postCode:""})))})})}),t.newDiv},_getCustomerShippingSummaryDiv:function(e){var t={};return t.me=this,t.item=e,t.newDiv=new Element("div",{"class":"panel panel-default",id:"shipping-info"}).insert({bottom:new Element("div",{"class":"panel-heading"}).insert({bottom:new Element("strong").update(t.item.name?"Shipping Info: "+t.item.name:"Shipping Info: new customer")}).insert({bottom:new Element("small",{"class":"pull-right"}).insert({bottom:new Element("button",{"class":"btn btn-default btn-xs",type:"button"}).update("Copy from Billing")})}).observe("click",function(){t.me._copyInfoFields($(this),"billing","shipping","Name"),t.me._copyInfoFields($(this),"billing","shipping","ContactNo"),t.me._copyInfoFields($(this),"billing","shipping","Street"),t.me._copyInfoFields($(this),"billing","shipping","City"),t.me._copyInfoFields($(this),"billing","shipping","State"),t.me._copyInfoFields($(this),"billing","shipping","Country"),t.me._copyInfoFields($(this),"billing","shipping","Posecode")})}).insert({bottom:new Element("div",{"class":"panel-body"}).insert({bottom:new Element("div",{"class":"row"}).insert({bottom:new Element("div",{"class":"col-sm-12",id:"shippingName"}).update(t.me._getFormGroup("Name",new Element("input",{"save-item":"shippingName",type:"text",value:t.item.id?t.item.address.shipping.contactName:""})))})}).insert({bottom:new Element("div",{"class":"row"}).insert({bottom:new Element("div",{"class":"col-sm-12",id:"shippingContactNo"}).update(t.me._getFormGroup("Contact No.",new Element("input",{"save-item":"shippingContactNo",type:"value",value:t.item.id?t.item.address.shipping.contactNo:""})))})}).insert({bottom:new Element("div",{"class":"row"}).insert({bottom:new Element("div",{"class":"col-sm-6",id:"shippingStreet"}).update(t.me._getFormGroup("Street",new Element("input",{"save-item":"shippingStreet",type:"text",value:t.item.id?t.item.address.shipping.street:""})))}).insert({bottom:new Element("div",{"class":"col-sm-6",id:"shippingCity"}).update(t.me._getFormGroup("City",new Element("input",{"save-item":"shippingCity",type:"text",value:t.item.id?t.item.address.shipping.city:""})))})}).insert({bottom:new Element("div",{"class":"row"}).insert({bottom:new Element("div",{"class":"col-sm-4",id:"shippingState"}).update(t.me._getFormGroup("State",new Element("input",{"save-item":"shippingState",type:"text",value:t.item.id?t.item.address.shipping.region:""})))}).insert({bottom:new Element("div",{"class":"col-sm-4",id:"shippingCountry"}).update(t.me._getFormGroup("Country",new Element("input",{"save-item":"shippingCountry",type:"text",value:t.item.id?t.item.address.shipping.country:"AU"})))}).insert({bottom:new Element("div",{"class":"col-sm-4",id:"shippingPosecode"}).update(t.me._getFormGroup("Post Code",new Element("input",{"save-item":"shippingPosecode",type:"text",value:t.item.id?t.item.address.shipping.postCode:""})))})})}),t.newDiv},_getFormGroup:function(e,t){return new Element("div",{"class":"form-group form-group-sm form-group-sm-label"}).insert({bottom:new Element("label").update(e)}).insert({bottom:t.addClassName("form-control")})},bindAllEventNObjects:function(){var e={};return e.me=this,e.me}});