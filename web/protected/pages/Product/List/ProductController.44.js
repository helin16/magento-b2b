var PageJs=new Class.create;PageJs.prototype=Object.extend(new CRUDPageJs,{manufactures:[],suppliers:[],productCategories:[],productStatuses:[],_showRightPanel:!1,_getTitleRowData:function(){return{sku:"SKU",name:"Product Name",locations:"Locations",invenAccNo:"AccNo.",manufacturer:{name:"Brand"},supplierCodes:[{supplier:{name:"Supplier"},code:""}],active:"act?",stockOnOrder:"OnOrder",stockOnHand:"OnHand",stockOnPO:"OnPO"}},toggleSearchPanel:function(e){var t={};return t.me=this,$(e).toggle(),t.me.deSelectProduct(),t.me},_loadManufactures:function(e){this.manufactures=e;var t={};return t.me=this,t.selectionBox=$(t.me.searchDivId).down('[search_field="pro.manufacturerIds"]'),t.me.manufactures.each(function(e){t.selectionBox.insert({bottom:new Element("option",{value:e.id}).update(e.name)})}),this},_loadProductStatuses:function(e){this.productStatuses=e;var t={};return t.me=this,t.selectionBox=$(t.me.searchDivId).down('[search_field="pro.productStatusIds"]'),t.me.productStatuses.each(function(e){t.selectionBox.insert({bottom:new Element("option",{value:e.id}).update(e.name)})}),this},_loadSuppliers:function(e){this.suppliers=e;var t={};return t.me=this,t.selectionBox=$(t.me.searchDivId).down('[search_field="pro.supplierIds"]'),t.me.suppliers.each(function(e){t.selectionBox.insert({bottom:new Element("option",{value:e.id}).update(e.name)})}),this},_loadCategories:function(e){this.categories=e;var t={};return t.me=this,t.selectionBox=$(t.me.searchDivId).down('[search_field="pro.productCategoryIds"]'),t.me.categories.sort(function(e,t){return e.namePath>t.namePath}).each(function(e){t.selectionBox.insert({bottom:new Element("option",{value:e.id}).update(e.namePath)})}),this},_loadChosen:function(){return jQuery(".chosen").chosen({search_contains:!0,inherit_select_classes:!0,no_results_text:"Oops, nothing found!",width:"95%"}),this},_bindSearchKey:function(){var e={};return e.me=this,$$("#searchBtn").first().observe("click",function(){$$("#showSearch").first().checked?(e.me.deSelectProduct(),e.me.getSearchCriteria().getResults(!0,e.me._pagination.pageSize)):$$("#showSearch").first().click()}),$("searchDiv").getElementsBySelector("[search_field]").each(function(t){t.observe("keydown",function(t){e.me.keydown(t,function(){$(e.me.searchDivId).down("#searchBtn").click()})})}),this},_getSupplierCodes:function(e,t){var n={};return n.me=this,n.supplierCodeString=[],e.each(function(e){n.supplierCodeString.push(t===!0?"Supplier":'<abbr title="Code: '+e.code+'">'+(e.supplier&&e.supplier.name?e.supplier.name:"")+"</abbr>")}),n.supplierCodeString.join(", ")},_getLocations:function(e,t){var n={};return n.me=this,t===!0?"Locations":(n.locationStrings=[],e.each(function(e){n.locationStrings.push('<div><small><strong class="hidden-xs hide-when-info hidden-sm">'+e.type.name+': </strong><abbr title="Type: '+e.type.name+'">'+e.value+"</abbr></small></div>")}),n.locationStrings.join(""))},_displayPriceMatchResult:function(e,t){var n={};return n.me=this,n.minPrice=0,n.tbody=new Element("tbody"),$H(e.companyPrices).each(function(e){0!==parseInt(e.value.price)&&(0===parseInt(n.minPrice)&&parseFloat(e.value.price)>0||parseFloat(e.value.price)<parseFloat(n.minPrice))&&(n.minPrice=e.value.price),n.tbody.insert({bottom:new Element("tr").insert({bottom:new Element("td",{colspan:3}).update(e.key)}).insert({bottom:new Element("td").update(e.value.priceURL&&!e.value.priceURL.blank()?new Element("a",{href:e.value.priceURL,target:"__blank"}).update(n.me.getCurrency(e.value.price)):n.me.getCurrency(e.value.price))})})}),n.priceDiff=parseFloat(e.myPrice)-parseFloat(n.minPrice),n.priceDiffClass="",0!==parseInt(n.minPrice)&&(parseInt(n.priceDiff)>0?n.priceDiffClass="label label-danger":parseInt(n.priceDiff)<0&&(n.priceDiffClass="label label-success")),n.newDiv=new Element("table",{"class":"table table-striped table-hover price-match-listing"}).insert({bottom:new Element("thead").insert({bottom:new Element("tr").insert({bottom:new Element("th").update("SKU")}).insert({bottom:new Element("th").update("My Price")}).insert({bottom:new Element("th",{"class":"price_diff"}).update("Price Diff.")}).insert({bottom:new Element("th").update("Min Price")})})}).insert({bottom:new Element("tbody").insert({bottom:new Element("tr").insert({bottom:new Element("td").update(e.sku)}).insert({bottom:new Element("td").update(n.priceInput=new Element("input",{"class":"click-to-edit price-input",value:n.me.getCurrency(e.myPrice),"product-id":t}))}).insert({bottom:new Element("td",{"class":"price_diff"}).update(new Element("span",{"class":""+n.priceDiffClass}).update(n.me.getCurrency(n.priceDiff)))}).insert({bottom:new Element("td",{"class":"price_min"}).update(n.me.getCurrency(n.minPrice))})})}).insert({bottom:new Element("thead").insert({bottom:new Element("tr").insert({bottom:new Element("th",{colspan:3}).update("Company")}).insert({bottom:new Element("th").update("Price")})})}).insert({bottom:n.tbody}),n.newDiv},_getInfoPanel:function(e){var t={};return t.me=this,new Element("div",{id:"info_panel_"+e.id}).insert({bottom:new Element("div",{"class":"col-md-6"}).insert({bottom:new Element("div",{"class":"panel panel-default price-match-div"}).insert({bottom:new Element("div",{"class":"panel-heading"}).update("<strong>Price Match</strong>")}).insert({bottom:new Element("div",{"class":"panel-body price-match-listing"}).update(t.me.getLoadingImg())})})}).insert({bottom:new Element("div",{"class":"col-md-6"}).insert({bottom:new Element("div",{"class":"panel panel-default price-trend-div"}).insert({bottom:new Element("div",{"class":"panel-body"}).insert({bottom:new Element("iframe",{frameborder:"0",scrolling:"auto",width:"100%",height:"400px"})})})})}).insert({bottom:new Element("div",{"class":"col-md-6"}).insert({bottom:new Element("div",{"class":"panel panel-default"}).insert({bottom:new Element("div",{"class":"panel-body"}).update("<h4>Reserved for Next Phase of Developing</h4>")})})}).insert({bottom:new Element("div",{"class":"col-md-6"}).insert({bottom:new Element("div",{"class":"panel panel-default"}).insert({bottom:new Element("div",{"class":"panel-body"}).update("<h4>Reserved for Next Phase of Developing</h4>")})})})},_showProductInfoOnRightPanel:function(e){var t={};return t.me=this,t.infoPanel=t.me._getInfoPanel(e),t.infoPanel.down(".price-trend-div iframe").writeAttribute("src","/statics/product/pricetrend.html?productid="+e.id),t.me.postAjax(t.me.getCallbackId("priceMatching"),{id:e.id},{onLoading:function(){t.infoPanel.down(".price-match-div .price-match-listing").replace(new Element("div",{"class":"panel-body price-match-listing"}).update(t.me.getLoadingImg()))},onSuccess:function(n,i){try{if(t.result=t.me.getResp(i,!1,!0),!t.result)return;$("info_panel_"+e.id)&&$("info_panel_"+e.id).down(".price-match-div .price-match-listing").replace(t.me._displayPriceMatchResult(t.result,e.id)),t.me._bindPriceInput()}catch(o){t.me.showModalBox("Error",o,!0)}}}),t.infoPanel},deSelectProduct:function(){var e={};return e.me=this,jQuery(".product_item.success",jQuery("#"+e.me.resultDivId)).removeClass("success").popover("hide"),$(e.me.resultDivId).up(".list-panel").removeClassName("col-xs-4").addClassName("col-xs-12"),jQuery(".hide-when-info",jQuery("#"+e.me.resultDivId)).show(),e.me._showRightPanel=!1,e.me},getResults:function(e,t){var n={};n.me=this,n.reset=e||!1,n.resultDiv=$(n.me.resultDivId),n.reset===!0&&(n.me._pagination.pageNo=1),n.me._pagination.pageSize=t||n.me._pagination.pageSize,n.me.postAjax(n.me.getCallbackId("getItems"),{pagination:n.me._pagination,searchCriteria:n.me._searchCriteria},{onLoading:function(){jQuery("#"+n.me.searchDivId+" #searchBtn").button("loading"),n.reset===!0&&n.resultDiv.update(new Element("tr").update(new Element("td").update(n.me.getLoadingImg())))},onSuccess:function(e,t){try{if(n.result=n.me.getResp(t,!1,!0),!n.result)return;$(n.me.totalNoOfItemsId).update(n.result.pageStats.totalRows),n.reset===!0&&n.resultDiv.update(n.me._getResultRow(n.me._getTitleRowData(),!0).wrap(new Element("thead"))),n.resultDiv.getElementsBySelector(".paginWrapper").each(function(e){e.remove()}),n.tbody=$(n.resultDiv).down("tbody"),n.tbody||$(n.resultDiv).insert({bottom:n.tbody=new Element("tbody")}),n.result.items.each(function(e){n.tbody.insert({bottom:n.me._getResultRow(e).addClassName("item_row").writeAttribute("item_id",e.id)})}),n.me._singleProduct!==!0?n.result.pageStats.pageNumber<n.result.pageStats.totalPages&&n.resultDiv.insert({bottom:n.me._getNextPageBtn().addClassName("paginWrapper")}):n.result.items.size()>0&&n.me._displaySelectedProduct(n.result.items[0]),n.me._bindPriceInput()}catch(i){n.resultDiv.insert({bottom:n.me.getAlertBox("Error",i).addClassName("alert-danger")})}},onComplete:function(){jQuery("#"+n.me.searchDivId+" #searchBtn").button("reset")}})},_displaySelectedProduct:function(e){var t={};return t.me=this,$(t.me.resultDivId).up(".list-panel").removeClassName("col-xs-12").addClassName("col-xs-4"),jQuery(".hide-when-info",jQuery("#"+t.me.resultDivId)).hide(),t.me._showRightPanel=!0,jQuery(".product_item.success",jQuery("#"+t.me.resultDivId)).removeClass("success").popover("hide"),t.selectedRow=jQuery('[product_id="'+e.id+'"]',jQuery("#"+t.me.resultDivId)).addClass("success"),t.selectedRow.hasClass("popover-loaded")||t.selectedRow.popover({title:'<div class="row"><div class="col-xs-10">Details for: '+e.sku+'</div><div class="col-xs-2"><span class="btn btn-danger pull-right btn-sm" onclick="pageJs.deSelectProduct();"><span class="glyphicon glyphicon-remove"></span></span></div></div>',html:!0,placement:"right",container:"body",trigger:"manual",viewport:{selector:".list-panel",padding:0},content:function(){return t.me._showProductInfoOnRightPanel(e).wrap(new Element("div")).innerHTML},template:'<div class="popover" role="tooltip" style="max-width: none; z-index: 0;"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>'}).addClass("popover-loaded"),t.selectedRow.popover("toggle"),t.me},_openProductDetails:function(e){var t={};t.newWindow=window.open("/product/"+("new"==e?e:e.id)+".html","Product Details for: "+e.sku,"width=1300, location=no, scrollbars=yes, menubar=no, status=no, titlebar=no, fullscreen=no, toolbar=no"),t.newWindow.focus()},toggleActive:function(e,t){var n={};return n.me=this,n.me.postAjax(n.me.getCallbackId("toggleActive"),{productId:t.id,active:e},{onSuccess:function(e,i){try{if(n.result=n.me.getResp(i,!1,!0),!n.result||!n.result.item)return;$$(".product_item[product_id="+t.id+"]").size()>0&&$$(".product_item[product_id="+t.id+"]").first().replace(n.me._getResultRow(n.result.item,!1))}catch(o){n.me.showModalBox("ERROR",o,!0)}}}),n.me},_openProductQtyLogPage:function(e){var t={};return t.me=this,t.newWindow=window.open("/productqtylog.html?productid="+e,"Product Details","width=1920, location=no, scrollbars=yes, menubar=no, status=no, titlebar=no, fullscreen=no, toolbar=no"),t.newWindow.focus(),t.me},_updatePrice:function(e,t,n){var i={};return i.me=this,i.me.postAjax(i.me.getCallbackId("updatePrice"),{productId:e,newPrice:i.me.getValueFromCurrency(t)},{onLoading:function(){},onSuccess:function(o,s){try{if(i.result=i.me.getResp(s,!1,!0),!i.result||!i.result.item||!i.result.item.id)return;jQuery(".price-input[product-id="+i.result.item.id+"]").attr("original-price",i.me.getValueFromCurrency(t))}catch(r){i.me.showModalBox('<strong class="text-danger">Error When Update Price:</strong>',"<strong>"+r+"</strong>"),jQuery(".price-input[product-id="+e+"]").val(i.me.getCurrency(n))}}}),i.me},_bindPriceInput:function(){var e={};return e.me=this,jQuery(".price-input[product-id]").not(".price-input-binded").click(function(){jQuery(this).attr("original-price",e.me.getValueFromCurrency(jQuery(this).val())).select()}).keydown(function(t){e.inputBox=jQuery(this),e.me.keydown(t,function(){e.inputBox.blur()})}).focusout(function(){e.value=e.me.getValueFromCurrency(jQuery(this).val()),jQuery(this).val(e.me.getCurrency(e.value))}).change(function(){e.me._updatePrice(jQuery(this).attr("product-id"),jQuery(this).val(),e.me.getValueFromCurrency(jQuery(this).attr("original-price")))}).addClass("price-input-binded"),e.me},_getResultRow:function(e,t){var n={};return n.me=this,n.tag=n.isTitle===!0?"th":"td",n.isTitle=t||!1,n.price="",e.prices&&e.prices.each(function(e){e.type&&1===parseInt(e.type.id)&&(n.price=e.price)}),n.row=new Element("tr",{"class":"visible-xs visible-md visible-lg visible-sm "+(n.isTitle===!0?"":"product_item"),product_id:e.id}).store("data",e).insert({bottom:new Element(n.tag,{"class":"sku",title:e.name}).insert({bottom:new Element("span",{style:"margin: 0 5px 0 0;"}).insert({bottom:new Element("input",{type:"checkbox","class":"product-selected"}).observe("click",function(){n.checked=this.checked,n.isTitle===!0&&$(n.me.resultDivId).getElementsBySelector(".product_item .product-selected").each(function(e){e.checked=n.checked})})})}).insert({bottom:n.isTitle===!0?e.sku:new Element("a",{href:"javascript: void(0);","class":"sku-link"}).observe("click",function(){n.me._displaySelectedProduct(e)}).observe("dblclick",function(){n.me._openProductDetails(e)}).update(e.sku)})}).insert({bottom:new Element(n.tag,{"class":"product_name hidden-xs hide-when-info hidden-sm",style:n.me._showRightPanel?"display: none":""}).update(e.name)}).insert({bottom:new Element(n.tag,{"class":"product_price hidden-xs hide-when-info hidden-sm",style:n.me._showRightPanel?"display: none":""}).update(n.isTitle===!0?"Price":new Element("input",{"class":"click-to-edit price-input",value:n.me.getCurrency(n.price),"product-id":e.id}))}).insert({bottom:new Element(n.tag,{"class":"locations col-xs-1  hide-when-info hidden-sm"}).update(e.locations?n.me._getLocations(e.locations,t):"")}).insert({bottom:new Element(n.tag,{"class":"inventeryCode col-xs-1 hide-when-info"}).update(e.invenAccNo?e.invenAccNo:"")}).insert({bottom:new Element(n.tag,{"class":"manufacturer col-xs-1 hide-when-info"}).update(e.manufacturer?e.manufacturer.name:"")}).insert({bottom:new Element(n.tag,{"class":"supplier col-xs-1 hide-when-info hidden-sm"}).update(e.supplierCodes?n.me._getSupplierCodes(e.supplierCodes,t):"")}).insert({bottom:new Element(n.tag,{"class":"qty col-xs-2 hidden-sm"}).update(n.isTitle===!0?new Element("div",{"class":"row"}).insert({bottom:new Element("div",{"class":"col-xs-4 hide-when-info",title:"Stock on Hand"}).update("SH")}).insert({bottom:new Element("div",{"class":"col-xs-4",title:"Average Cost"}).update("Cost")}).insert({bottom:new Element("div",{"class":"col-xs-4 hide-when-info",title:"Stock On PO"}).update("SP")}):new Element("div",{"class":"row"}).update(new Element("a",{href:"javascript: void(0);"}).insert({bottom:new Element("div",{"class":"col-xs-4 hide-when-info",title:"Stock on Hand"}).update(e.stockOnHand)}).insert({bottom:new Element("div",{"class":"col-xs-4",title:"Average Cost"}).update(0!=e.totalOnHandValue&&0!=e.stockOnHand?n.me.getCurrency(e.totalOnHandValue/e.stockOnHand):"N/A")}).insert({bottom:new Element("div",{"class":"col-xs-4 hide-when-info",title:"Stock On PO"}).update(e.stockOnPO)}).observe("dblclick",function(){n.me._openProductQtyLogPage(e.id)})))}).insert({bottom:new Element(n.tag,{"class":"product_active col-xs-1 hide-when-info hidden-sm"}).insert({bottom:n.isTitle===!0?e.active:new Element("div",{"class":"row"}).insert({bottom:new Element("div",{"class":"col-xs-4"}).insert({bottom:new Element("input",{type:"checkbox",disabled:!0,checked:e.active})})}).insert({bottom:new Element("div",{"class":"col-xs-8"}).insert({bottom:new Element("div",{"class":"btn-group"}).insert({bottom:new Element("span",{"class":"btn btn-primary btn-xs"}).insert({bottom:new Element("span",{"class":"glyphicon glyphicon-pencil"})}).observe("click",function(t){Event.stop(t),n.me._openProductDetails(e)})}).insert({bottom:e.active===!0?new Element("span",{"class":"btn btn-danger btn-xs"}).insert({bottom:new Element("span",{"class":"glyphicon glyphicon-trash"})}).observe("click",function(t){Event.stop(t),n.btn=this,confirm("You are about to deactivate this product.\n Continue?")&&n.me.toggleActive(!1,e)}):new Element("span",{"class":"btn btn-success btn-xs"}).insert({bottom:new Element("span",{"class":"glyphicon glyphicon-repeat"})}).observe("click",function(t){Event.stop(t),n.btn=this,confirm("You are about to ReACTIVATE this product.\n Continue?")&&n.me.toggleActive(!0,e)})})})})})}),n.row}});