var BPCPageJs=new Class.create();BPCPageJs.prototype={productDetailsUrl:"/product/{id}",_currentLib:null,callbackIds:{},initialize:function(){},setCallbackId:function(a,b){this.callbackIds[a]=b},getCallbackId:function(a){if(this.callbackIds[a]===undefined||this.callbackIds[a]===null){throw"Callback ID is not set for:"+a}return this.callbackIds[a]},postAjax:function(d,c,e,b){var a={};a.request=new Prado.CallbackRequest(d,e);a.request.setCallbackParameter(c);a.timeout=(b||30000);if(a.timeout<30000){a.timeout=30000}a.request.setRequestTimeOut(a.timeout);a.request.dispatch();return a.request},getResp:function(b,a,d){var c={};c.expectNonJSONResult=(a!==true?false:true);c.result=b;if(c.expectNonJSONResult===true){return c.result}if(!c.result.isJSON()){c.error="Invalid JSON string: "+c.result;if(d===true){throw c.error}else{return alert(c.error)}}c.result=c.result.evalJSON();if(c.result.errors.size()!==0){c.error="Error: \n\n"+c.result.errors.join("\n");if(d===true){throw c.error}else{return alert(c.error)}}return c.result.resultData},getCurrency:function(f,c,b,a,e){var d={};d.decimal=(isNaN(b=Math.abs(b))?2:b);d.dollar=(c==undefined?"$":c);d.decimalPoint=(a==undefined?".":a);d.thousandPoint=(e==undefined?",":e);d.sign=(f<0?"-":"");d.Int=parseInt(f=Math.abs(+f||0).toFixed(d.decimal))+"";d.j=(d.j=d.Int.length)>3?d.j%3:0;return d.dollar+d.sign+(d.j?d.Int.substr(0,d.j)+d.thousandPoint:"")+d.Int.substr(d.j).replace(/(\d{3})(?=\d)/g,"$1"+d.thousandPoint)+(d.decimal?d.decimalPoint+Math.abs(f-d.Int).toFixed(d.decimal).slice(2):"")},keydown:function(c,a,b){if(!((c.which&&c.which==13)||(c.keyCode&&c.keyCode==13))){if(typeof(b)==="function"){b()}return true}if(typeof(a)==="function"){a()}return false},_getProductThumbnail:function(b){var a={};a.me=this;a.productDiv=new Element("span",{"class":"product griditem inlineblock cursorpntr",title:b.title}).insert({bottom:a.me._getProductImgDiv(b.attributes.image_thumb||null)}).insert({bottom:new Element("div",{"class":"product_details"}).insert({bottom:new Element("div",{"class":"product_title"}).update(b.title)})}).observe("click",function(){a.me.showDetailsPage(b.id)});return a.productDiv},showDetailsPage:function(a){window.location=this.productDetailsUrl.replace("{id}",a)},_getProductImgDiv:function(a){if(a===undefined||a===null||a.size()===0){return new Element("div",{"class":"product_image noimage"})}return new Element("img",{"class":"product_image",src:"/asset/get?id="+a[0].attribute})},getUser:function(d,e,a,b){var c={};c.me=this;c.me.postAjax(c.me.getCallbackId("getUser"),{},{onLoading:function(){if(typeof(a)==="function"){a()}},onComplete:function(f,h){try{c.result=c.me.getResp(h,false,true);if(typeof(e)==="function"){e()}}catch(g){c.me.showLoginPanel(d,b)}}})},showLoginPanel:function(c,a){var b={};b.me=this;b.newDiv=new Element("div",{"class":"floatingpanel"}).insert({bottom:new Element("div",{"class":"row msgpanel"})}).insert({bottom:new Element("div",{"class":"row"}).insert({bottom:new Element("span",{"class":"inlineblock title"}).update("ç”¨æˆ·å��/ç”¨æˆ¶å��:").insert({bottom:new Element("div",{"class":"subtitle"}).update("Username:")})}).insert({bottom:new Element("span",{"class":"inlineblock content"}).insert({bottom:new Element("input",{type:"textbox","class":"username rdcrnr padding5 lightBrdr ",placeholder:"Username"}).observe("keydown",function(d){pageJs.keydown(d,function(){$(Event.element(d)).up(".loginpanel").down(".loginbtn").click()})})})})}).insert({bottom:new Element("div",{"class":"row"}).insert({bottom:new Element("span",{"class":"inlineblock title"}).update("å¯†ç �/å¯†ç¢¼:").insert({bottom:new Element("div",{"class":"subtitle"}).update("Password:")})}).insert({bottom:new Element("span",{"class":"inlineblock content"}).insert({bottom:new Element("input",{type:"password","class":"password rdcrnr padding5 lightBrdr ",placeholder:"Password"}).observe("keydown",function(d){pageJs.keydown(d,function(){$(Event.element(d)).up(".loginpanel").down(".loginbtn").click()})})})})}).insert({bottom:new Element("div",{"class":"row btns"}).insert({bottom:new Element("input",{"class":"loginbtn button rdcrnr",value:"Login",type:"button"}).observe("click",function(){b.me._login(this,null,function(){window.location=document.URL})})}).insert({bottom:new Element("input",{"class":"cancelbtn button rdcrnr",value:"Cancel",type:"button"}).observe("click",function(){$(this).up(".floatpanelwrapper").remove();if(typeof(a)==="function"){a()}})})});$(c).insert({after:b.newDiv.wrap(new Element("div",{"class":"loginpanel floatpanelwrapper"}))});b.newDiv.down(".username").focus()},_getErrMsg:function(a){return new Element("span",{"class":"errmsg smalltxt"}).update(a)},_login:function(c,a,d){var b={};b.me=this;b.panel=$(c).up(".loginpanel");b.usernamebox=b.panel.down(".username");b.passwordbox=b.panel.down(".password");if(b.me._preLogin(b.usernamebox,b.passwordbox)===false){return}b.loadingMsg=new Element("div",{"class":"loadingMsg"}).update("log into system ...");b.me.postAjax(b.me.getCallbackId("loginUser"),{username:$F(b.usernamebox),password:$F(b.passwordbox)},{onLoading:function(){$(c).up(".row").hide().insert({after:b.loadingMsg});b.panel.down(".msgpanel").update("");if(typeof(a)==="function"){a()}},onComplete:function(f,h){try{b.result=b.me.getResp(h,false,true);if(typeof(d)==="function"){d()}}catch(g){$(b.usernamebox).select();b.panel.down(".msgpanel").update(b.me._getErrMsg(g))}b.loadingMsg.remove();$(c).up(".row").show()}})},_preLogin:function(c,a){var b={};b.me=this;$(c).up(".loginpanel").getElementsBySelector(".errmsg").each(function(d){d.remove()});if($F(c).blank()){$(c).insert({after:b.me._getErrMsg("Please provide an username!")});$(c).focus();return false}if($F(a).blank()){$(a).insert({after:b.me._getErrMsg("Please provide an password!")});$(a).focus();return false}return true}};