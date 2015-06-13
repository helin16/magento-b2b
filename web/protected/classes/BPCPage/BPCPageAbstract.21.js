var BPCPageJs=new Class.create;
BPCPageJs.prototype={modalId:"page_modal_box_id",_htmlIDs:{},_ajaxRequest:null,callbackIds:{},initialize:function(){},setCallbackId:function(a,b){this.callbackIds[a]=b;return this},getCallbackId:function(a){if(void 0===this.callbackIds[a]||null===this.callbackIds[a])throw"Callback ID is not set for:"+a;return this.callbackIds[a]},setHTMLID:function(a,b){this._htmlIDs[a]=b;return this},getHTMLID:function(a){return this._htmlIDs[a]},getFormGroup:function(a,b,d){var c;d=d||!1;c=new Element("div",{"class":"form-group"});
a&&c.insert({bottom:a.addClassName("control-label")});b&&(!0===d&&b.addClassName("form-control"),c.insert({bottom:b}));return c},postAjax:function(a,b,d,c){this._ajaxRequest=new Prado.CallbackRequest(a,d);this._ajaxRequest.setCallbackParameter(b);a=c||3E4;3E4>a&&(a=3E4);this._ajaxRequest.setRequestTimeOut(a);this._ajaxRequest.dispatch();return this._ajaxRequest},abortAjax:function(){null!==tmp.me._ajaxRequest&&tmp.me._ajaxRequest.abort()},getResp:function(a,b,d){if(!0===(!0!==b?!1:!0))return a;if(a&&
a.isJSON()){a=a.evalJSON();if(0!==a.errors.size()){b="Error: \n\n"+a.errors.join("\n");if(!0===d)throw b;return alert(b)}return a.resultData}},getCurrency:function(a,b,d,c,f){var e,g,h;e=isNaN(d=Math.abs(d))?2:d;b=void 0==b?"$":b;c=void 0==c?".":c;f=void 0==f?",":f;d=0>a?"-":"";g=parseInt(a=Math.abs(+a||0).toFixed(e))+"";h=3<(h=g.length)?h%3:0;return b+d+(h?g.substr(0,h)+f:"")+g.substr(h).replace(/(\d{3})(?=\d)/g,"$1"+f)+(e?c+Math.abs(a-g).toFixed(e).slice(2):"")},getValueFromCurrency:function(a){return a?
(a+"").replace(/\s*/g,"").replace(/\$/g,"").replace(/,/g,""):""},keydown:function(a,b,d,c){c=c?c:13;if(!(a.which&&a.which==c||a.keyCode&&a.keyCode==c))return"function"===typeof d&&d(),!0;"function"===typeof b&&b();return!1},getAlertBox:function(a,b){return(new Element("div",{"class":"alert alert-dismissible",role:"alert"})).insert({bottom:(new Element("button",{"class":"close","data-dismiss":"alert"})).insert({bottom:(new Element("span",{"aria-hidden":"true"})).update("&times;")}).insert({bottom:(new Element("span",
{"class":"sr-only"})).update("Close")})}).insert({bottom:(new Element("strong")).update(a)}).insert({bottom:b})},_signRandID:function(a){a.id||(a.id="input_"+String.fromCharCode(65+Math.floor(26*Math.random()))+Date.now());return this},_markFormGroupError:function(a,b){var d={me:this};a.up(".form-group")&&(a.store("clearErrFunc",function(b){a.up(".form-group").removeClassName("has-error");jQuery("#"+a.id).tooltip("hide").tooltip("destroy").show()}).up(".form-group").addClassName("has-error"),d.me._signRandID(a),
jQuery("#"+a.id).tooltip({trigger:"manual",placement:"auto",container:"body",placement:"bottom",html:!0,title:b,content:b}).tooltip("show"),$(a).observe("change",function(){d.func=$(a).retrieve("clearErrFunc");"function"===typeof d.func&&d.func()}));return d.me},_collectFormData:function(a,b,d){var c,f,e,g,h,k;c=this;f={};e=!1;$(a).getElementsBySelector("["+b+"]").each(function(a){g=d?a.readAttribute(d):null;h=a.readAttribute(b);a.hasAttribute("required")&&$F(a).blank()&&(c._markFormGroupError(a,
"This is requried"),e=!0);k="checkbox"!==a.readAttribute("type")?$F(a):$(a).checked;if(a.hasAttribute("validate_currency")||a.hasAttribute("validate_number"))null===c.getValueFromCurrency(k).match(/^(-)?\d+(\.\d{1,4})?$/)&&(c._markFormGroupError(a,a.hasAttribute("validate_currency")?a.readAttribute("validate_currency"):a.hasAttribute("validate_number")),e=!0),c.getValueFromCurrency(k);null!==g&&void 0!==g?(f[g]||(f[g]={}),f[g][h]=k):f[h]=k});return!0===e?null:f},showModalBox:function(a,b,d,c,f){var e;
d=!0===d?!0:!1;c=c||null;$(this.modalId)?(e=jQuery("#"+this.modalId),f=e.find(".modal-dialog").removeClass("modal-sm").removeClass("modal-lg").addClass(!0===d?"modal-sm":"modal-lg"),e.find(".modal-title").html(a),e.find(".modal-body").html(b),0<e.find(".modal-footer").length?null!==c?e.find(".modal-footer").html(c):e.find(".modal-footer").remove():null!==c&&jQuery('<div class="modal-footer"></div>').html(c).appendTo(f.find(".modal-content"))):(a=(new Element("div",{id:this.modalId,"class":"modal",
tabindex:"-1",role:"dialog","aria-hidden":"true","aria-labelledby":"page-modal-box"})).insert({bottom:(new Element("div",{"class":"modal-dialog "+(!0===d?"modal-sm":"modal-lg")})).insert({bottom:(new Element("div",{"class":"modal-content"})).insert({bottom:(new Element("div",{"class":"modal-header"})).insert({bottom:(new Element("div",{"class":"close",type:"button","data-dismiss":"modal"})).insert({bottom:(new Element("span",{"aria-hidden":"true"})).update("&times;")})}).insert({bottom:(new Element("strong",
{"class":"modal-title"})).update(a)})}).insert({bottom:(new Element("div",{"class":"modal-body"})).update(b)}).insert({bottom:null===c?"":(new Element("div",{"class":"modal-footer"})).update(c)})})}),$$("body")[0].insert({bottom:a}),e=jQuery("#"+this.modalId),f&&"object"===typeof f&&$H(f).each(function(a){e.on(a.key,a.value)}));e.hasClass("in")||e.modal().show();return this},hideModalBox:function(){jQuery("#"+this.modalId).modal("hide");return this},getLoadingImg:function(){return Element("span",
{"class":"loading-img fa fa-refresh fa-5x fa-spin"})},loadUTCTime:function(a){var b;b=a.strip().split(" ");a=b[0].split("-");b=b[1].split(":");return new Date(Date.UTC(a[0],1*a[1]-1,a[2],b[0],b[1],b[2]))},observeClickNDbClick:function(a,b,d){$(a).observe("click",function(c){!0===$(a).retrieve("alreadyclicked")?($(a).store("alreadyclicked",!1),$(a).retrieve("alreadyclickedTimeout")&&clearTimeout($(a).retrieve("alreadyclickedTimeout")),"function"===typeof d&&d(c)):($(a).store("alreadyclicked",!0),$(a).store("alreadyclickedTimeout",
setTimeout(function(){$(a).store("alreadyclicked",!1);"function"===typeof b&&b(c)},300)))});return this},getUrlParam:function(a){a=a.replace(/[\[]/,"\\[").replace(/[\]]/,"\\]");a=(new RegExp("[\\?&]"+a+"=([^&#]*)")).exec(location.search);return null===a?"":decodeURIComponent(a[1].replace(/\+/g," "))},openInNewTab:function(a){window.open(a,"_blank").focus();return this}};