var PageJs=new Class.create();PageJs.prototype=Object.extend(new BPCPageJs(),{dropShowDiv:{dropDiv:"",showDiv:"",resultDiv:""},_acceptableTypes:["csv"],_fileReader:null,csvSeperator:",",csvFileLineFormat:["sku","price"],csvNewLineSeperator:"\r\n",allFileLineArray:[],companyNameArray:[],totalLines:0,parseCSVFile:function(a){var b={};b.me=this;b.outputArray=[];b.linesArray=a.split(b.me.csvNewLineSeperator);b.linesArray.each(function(c){c=c.strip();if((c.blank()||c===null||c==="")){}else{b.tmpLineArray={};b.lineArray=c.split(b.me.csvSeperator);for(b.i=0;b.i<b.me.csvFileLineFormat.size();b.i++){b.tmpLineArray[b.me.csvFileLineFormat[b.i]]=(b.lineArray[b.i]!==undefined?b.lineArray[b.i]:"")}b.outputArray.push(b.tmpLineArray)}});return b.outputArray},initializeFileHandler:function(){var a={};a.me=this;$(a.me.dropShowDiv.dropDiv).observe("dragover",function(b){a.me.handleDragOver(b)}).observe("drop",function(b){a.me.handleFileSelect(b)});return a.me},handleDragOver:function(a){var b={};b.me=this;a.stopPropagation();a.preventDefault();a.dataTransfer.dropEffect="copy"},handleFileSelect:function(a){var b={};b.me=this;a.stopPropagation();a.preventDefault();b.me.totalLines=0;b.me.allFileLineArray=[];b.files=a.dataTransfer.files;b.validFiles=[];$(b.me.dropShowDiv.showDiv).update("");$(b.me.dropShowDiv.resultDiv).update("");for(b.i=0,b.f;b.f=b.files[b.i];b.i++){b.success=((b.extension=b.f.name.split(".").pop())!==""&&b.me._acceptableTypes.indexOf(b.extension.toLowerCase())>-1);if(b.success){b.msgTxt="File Name:"+b.f.name+"Accepted";b.validFiles.push({index:b.i,file:b.f})}else{b.msgTxt=b.f.name+" Error: Only Acceptable File Extension are "+b.me._acceptableTypes.join(", ")}$(b.me.dropShowDiv.showDiv).insert({bottom:new Element("div",{"class":"msgDiv "+(!b.success?"errorMsgDiv":"okMsgDiv"),file_sequence:b.i}).update(new Element("div",{"class":"msg"}).update(b.msgTxt))})}if(b.validFiles.size()===0){alert("NO VALID FILES UPLOADED!!! PLS TRY AGAIN");return}b.allFileLineArray=b.me._readValidCSVFiles(b.validFiles);return b.me},_generatePriceRowForProduct:function(a){var b={};b.me=this;b.ppArray=a;if(b.ppArray.sku===""||b.ppArray.sku===undefined||b.ppArray.sku===null||b.ppArray.sku.blank()){return b.me}b.rowDiv=new Element("div",{"class":"row"}).insert({bottom:new Element("span",{"class":"cell sku "+((b.ppArray.searchURL&&!b.ppArray.searchURL.blank())?"cuspntr":"")}).update(b.ppArray.sku).observe("click",function(){if(b.ppArray.searchURL&&!b.ppArray.searchURL.blank()){window.open(b.ppArray.searchURL.strip())}})}).insert({bottom:new Element("span",{"class":"cell myPrice"}).update(isNaN(b.ppArray.myPrice)?b.ppArray.myPrice:b.me.getCurrency(b.ppArray.myPrice))}).insert({bottom:new Element("span",{"class":"cell priceDiff"+(!isNaN(b.ppArray.priceDiff)&&b.ppArray.priceDiff>0?" overmin":"")}).update(isNaN(b.ppArray.priceDiff)?b.ppArray.priceDiff:b.me.getCurrency(b.ppArray.priceDiff))}).insert({bottom:new Element("span",{"class":"cell minPrice"}).update(isNaN(b.ppArray.minPrice)?b.ppArray.minPrice:b.me.getCurrency(b.ppArray.minPrice))});b.ppArray.data.each(function(c){if((c.price.blank()||c.price===""||c.price===null||c.price===undefined)&&(c.priceURL.blank()||c.priceURL===""||c.priceURL===null||c.priceURL===undefined)){b.rowDiv.insert({bottom:new Element("span",{"class":"cell company"}).update(c.company)})}else{b.url=c.priceURL.strip();b.hasUrl=(b.url!==""&&b.url!==null&&b.url!==undefined);b.rowDiv.insert({bottom:new Element("span",{"class":"cell company "+(b.hasUrl===true?"cuspntr":"")}).update(c.price).observe("click",function(){if(!c.priceURL.strip().blank()){window.open(c.priceURL.strip())}})})}});b.rowDiv.store("data",b.ppArray);return b.rowDiv},_checkLastLine:function(c,a){var b={};b.me=this;b.lineNo=c;if(b.lineNo>=b.me.totalLines){$(b.me.dropShowDiv.dropDiv).show();a.remove()}return this},_loadProductLineItems:function(){var a={};a.me=this;$(a.me.dropShowDiv.dropDiv).hide();$(a.me.dropShowDiv.showDiv).update("");a.headerCompanyArray=[];a.me.companyNameArray.each(function(b){a.headerCompanyArray.push({price:"",priceURL:"",company:b})});a.spinBar=new Element("span",{"class":"inlineblock loading"});$(a.me.dropShowDiv.resultDiv).update("").insert({bottom:a.me._generatePriceRowForProduct({sku:"SKU",minPrice:"Min Price",myPrice:"My Price",priceDiff:"Price Difference",data:a.headerCompanyArray}).addClassName("header")}).insert({after:a.spinBar});a.lineNo=0;a.me.allFileLineArray.each(function(b){b.fileContent.each(function(c){if(c.sku.blank()){a.lineNo=a.lineNo*1+1;a.me._checkLastLine(a.lineNo,a.spinBar);return}a.me.postAjax(a.me.getCallbackId("getAllPricesForProduct"),{sku:c.sku,price:c.price},{onLoading:function(d,e){},onComplete:function(d,g){try{a.result=a.me.getResp(g,false,true);if(a.result.items.sku!==""&&a.result.items.sku!==undefined&&a.result.items.sku!==null&&!a.result.items.sku.blank()){$(a.me.dropShowDiv.resultDiv).insert({bottom:a.me._generatePriceRowForProduct(a.result.items)})}a.lineNo=a.lineNo*1+1;a.me._checkLastLine(a.lineNo,a.spinBar)}catch(f){alert(f)}}})})});return a.me},_allFileLoadFinished:function(){var a={};a.me=this;if(a.me.allFileLineArray.size()===0){alert("EMPTY FILE(s) UPLOADED!!! PLS TRY AGAIN");return}$(a.me.dropShowDiv.showDiv).insert({bottom:new Element("span",{"class":"button"}).update("Start Load").observe("click",function(){a.me._loadProductLineItems()})});return a.me},_readSingleCSVFile:function(d,b,a){var c={};c.me=this;c.reader=new FileReader();c.reader.onload=function(e){c.contents=e.target.result;c.fileArray=c.me.parseCSVFile(c.contents);$(c.me.dropShowDiv.showDiv).getElementsBySelector("[file_sequence="+d.index+"]")[0].insert({bottom:new Element("div",{"class":"msg"}).update("Loaded Successfully")});$(c.me.dropShowDiv.showDiv).getElementsBySelector("[file_sequence="+d.index+"]")[0].store(c.fileArray);if(c.fileArray.size()>0){c.me.allFileLineArray.push({fileIndex:d.index,fileName:d.file.name,fileContent:c.fileArray});c.me.totalLines=(c.me.totalLines*1)+c.fileArray.size()}b=(b*1)+1;if(a.size()>b){c.me._readSingleCSVFile(a[b],b,a)}else{c.me._allFileLoadFinished()}};c.reader.readAsText(d.file);return c.me},_readValidCSVFiles:function(a){var b={};b.me=this;b.me._readSingleCSVFile(a[0],0,a);return b.me}});