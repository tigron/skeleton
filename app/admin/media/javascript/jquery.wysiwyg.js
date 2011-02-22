/**
 * WYSIWYG - jQuery plugin 0.93
 * (koken)
 *
 * Copyright (c) 2008-2009 Juan M Martinez, 2010 Akzhan Abdulin and all contrbutors
 * http://plugins.jquery.com/project/jWYSIWYG
 *
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 *
 * $Id: $
 */

/*jslint browser: true, forin: true */

(function(d){var f=function(h,g){this.init(h,g)};var b=function(h){var g=d(h).get(0);if(g.nodeName.toLowerCase()=="iframe"){return g.contentWindow.document}return g};var e=function(){var g=this.get(0);if(g.contentWindow.document.selection){return g.contentWindow.document.selection.createRange().text}else{return g.contentWindow.getSelection().toString()}};d.fn.wysiwyg=function(h){if(arguments.length>0&&arguments[0].constructor==String){var k=arguments[0].toString();var n=[];if(k=="enabled"){return this.data("wysiwyg")!==null}for(var j=1;j<arguments.length;j++){n[j-1]=arguments[j]}var m=null;this.filter("textarea").each(function(){d.data(this,"wysiwyg").designMode();m=f[k].apply(this,n)});return m}if(this.data("wysiwyg")){return this}var g={};if(h&&h.controls){g=h.controls;delete h.controls}h=d.extend({},d.fn.wysiwyg.defaults,h);h.controls=d.extend(true,h.controls,d.fn.wysiwyg.controls);for(var l in g){if(l in h.controls){d.extend(h.controls[l],g[l])}else{h.controls[l]=g[l]}}return this.each(function(){new f(this,h)})};d.fn.wysiwyg.defaults={html:'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"><html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">STYLE_SHEET</head><body style="margin: 0px;">INITIAL_CONTENT</body></html>',formTableHtml:'<form class="wysiwyg"><fieldset><legend>Insert table</legend><label>Count of columns: <input type="text" name="colCount" value="3" /></label><label><br />Count of rows: <input type="text" name="rowCount" value="3" /></label><input type="submit" class="button" value="Insert table" /> <input type="reset" value="Cancel" /></fieldset></form>',formImageHtml:'<form class="wysiwyg"><fieldset><legend>Insert Image</legend><label>Image URL: <input type="text" name="url" value="http://" /></label><label>Image Title: <input type="text" name="imagetitle" value="" /></label><label>Image Description: <input type="text" name="description" value="" /></label><input type="submit" class="button" value="Insert Image" /> <input type="reset" value="Cancel" /></fieldset></form>',formWidth:440,formHeight:270,tableFiller:"Lorem ipsum",css:{},debug:false,autoSave:true,rmUnwantedBr:true,brIE:true,iFrameClass:null,messages:{nonSelection:"select the text you wish to link"},events:{},controls:{},resizeOptions:false};d.wysiwyg={addControl:function(g,h){var i={};i[g]={visible:false,custom:true,options:h};d.extend(d.fn.wysiwyg.controls,d.fn.wysiwyg.controls,i)}};d.fn.wysiwyg.controls={bold:{visible:true,tags:["b","strong"],css:{fontWeight:"bold"},tooltip:"Bold"},italic:{visible:true,tags:["i","em"],css:{fontStyle:"italic"},tooltip:"Italic"},strikeThrough:{visible:true,tags:["s","strike"],css:{textDecoration:"line-through"},tooltip:"Strike-through"},underline:{visible:true,tags:["u"],css:{textDecoration:"underline"},tooltip:"Underline"},justifyLeft:{visible:true,groupIndex:1,css:{textAlign:"left"},tooltip:"Justify Left"},justifyCenter:{visible:true,tags:["center"],css:{textAlign:"center"},tooltip:"Justify Center"},justifyRight:{visible:true,css:{textAlign:"right"},tooltip:"Justify Right"},justifyFull:{visible:true,css:{textAlign:"justify"},tooltip:"Justify Full"},indent:{groupIndex:2,visible:true,tooltip:"Indent"},outdent:{visible:true,tooltip:"Outdent"},subscript:{groupIndex:3,visible:true,tags:["sub"],tooltip:"Subscript"},superscript:{visible:true,tags:["sup"],tooltip:"Superscript"},undo:{groupIndex:4,visible:true,tooltip:"Undo"},redo:{visible:true,tooltip:"Redo"},insertOrderedList:{groupIndex:5,visible:true,tags:["ol"],tooltip:"Insert Ordered List"},insertUnorderedList:{visible:true,tags:["ul"],tooltip:"Insert Unordered List"},insertHorizontalRule:{visible:true,tags:["hr"],tooltip:"Insert Horizontal Rule"},createLink:{groupIndex:6,visible:true,exec:function(){var h=e.call(d(this.editor));if(h&&h.length>0){if(d.browser.msie){this.focus();this.editorDoc.execCommand("createLink",true,null)}else{var g=prompt("URL","http://");if(g&&g.length>0){this.editorDoc.execCommand("unlink",false,null);this.editorDoc.execCommand("createLink",false,g)}}}else{if(this.options.messages.nonSelection){alert(this.options.messages.nonSelection)}}},tags:["a"],tooltip:"Create link"},insertImage:{visible:true,exec:function(){var g=this;if(d.modal){d.modal(d.fn.wysiwyg.defaults.formImageHtml,{onShow:function(j){d("input:submit",j.data).click(function(n){n.preventDefault();var l=d('input[name="url"]',j.data).val();var o=d('input[name="imagetitle"]',j.data).val();var m=d('input[name="description"]',j.data).val();var k="<img src='"+l+"' title='"+o+"' alt='"+m+"' />";g.insertHtml(k);d.modal.close()});d("input:reset",j.data).click(function(k){k.preventDefault();d.modal.close()})},maxWidth:d.fn.wysiwyg.defaults.formWidth,maxHeight:d.fn.wysiwyg.defaults.formHeight,overlayClose:true})}else{if(d.fn.dialog){var h=d(d.fn.wysiwyg.defaults.formImageHtml).appendTo("body");h.dialog({modal:true,width:d.fn.wysiwyg.defaults.formWidth,height:d.fn.wysiwyg.defaults.formHeight,open:function(j,k){d("input:submit",d(this)).click(function(o){o.preventDefault();var m=d('input[name="url"]',h).val();var p=d('input[name="imagetitle"]',h).val();var n=d('input[name="description"]',h).val();var l="<img src='"+m+"' title='"+p+"' alt='"+n+"' />";g.insertHtml(l);d(h).dialog("close")});d("input:reset",d(this)).click(function(l){l.preventDefault();d(h).dialog("close")})},close:function(j,k){d(this).dialog("destroy")}})}else{if(d.browser.msie){this.focus();this.editorDoc.execCommand("insertImage",true,null)}else{var i=prompt("URL","http://");if(i&&i.length>0){this.editorDoc.execCommand("insertImage",false,i)}}}}},tags:["img"],tooltip:"Insert image"},insertTable:{visible:true,exec:function(){var h=this;if(d.fn.modal){d.modal(d.fn.wysiwyg.defaults.formTableHtml,{onShow:function(k){d("input:submit",k.data).click(function(n){n.preventDefault();var l=d('input[name="rowCount"]',k.data).val();var m=d('input[name="colCount"]',k.data).val();h.insertTable(m,l,d.fn.wysiwyg.defaults.tableFiller);d.modal.close()});d("input:reset",k.data).click(function(l){l.preventDefault();d.modal.close()})},maxWidth:d.fn.wysiwyg.defaults.formWidth,maxHeight:d.fn.wysiwyg.defaults.formHeight,overlayClose:true})}else{if(d.fn.dialog){var i=d(d.fn.wysiwyg.defaults.formTableHtml).appendTo("body");i.dialog({modal:true,width:d.fn.wysiwyg.defaults.formWidth,height:d.fn.wysiwyg.defaults.formHeight,open:function(k,l){d("input:submit",d(this)).click(function(o){o.preventDefault();var m=d('input[name="rowCount"]',i).val();var n=d('input[name="colCount"]',i).val();h.insertTable(n,m,d.fn.wysiwyg.defaults.tableFiller);d(i).dialog("close")});d("input:reset",d(this)).click(function(m){m.preventDefault();d(i).dialog("close")})},close:function(k,l){d(this).dialog("destroy")}})}else{var j=prompt("Count of columns","3");var g=prompt("Count of rows","3");this.insertTable(j,g,d.fn.wysiwyg.defaults.tableFiller)}}},tags:["table"],tooltip:"Insert table"},h1:{visible:true,groupIndex:7,className:"h1",command:(d.browser.msie||d.browser.safari)?"FormatBlock":"heading","arguments":(d.browser.msie||d.browser.safari)?"<h1>":"h1",tags:["h1"],tooltip:"Header 1"},h2:{visible:true,className:"h2",command:(d.browser.msie||d.browser.safari)?"FormatBlock":"heading","arguments":(d.browser.msie||d.browser.safari)?"<h2>":"h2",tags:["h2"],tooltip:"Header 2"},h3:{visible:true,className:"h3",command:(d.browser.msie||d.browser.safari)?"FormatBlock":"heading","arguments":(d.browser.msie||d.browser.safari)?"<h3>":"h3",tags:["h3"],tooltip:"Header 3"},cut:{groupIndex:8,visible:false,tooltip:"Cut"},copy:{visible:false,tooltip:"Copy"},paste:{visible:false,tooltip:"Paste"},increaseFontSize:{groupIndex:9,visible:false&&!(d.browser.msie),tags:["big"],tooltip:"Increase font size"},decreaseFontSize:{visible:false&&!(d.browser.msie),tags:["small"],tooltip:"Decrease font size"},removeFormat:{visible:true,exec:function(){if(d.browser.msie){this.focus()}this.editorDoc.execCommand("formatBlock",false,"<P>");this.editorDoc.execCommand("removeFormat",false,null);this.editorDoc.execCommand("unlink",false,null)},tooltip:"Remove formatting"},html:{groupIndex:10,visible:false,exec:function(){if(this.viewHTML){this.setContent(d(this.original).val());d(this.original).hide();d(this.editor).show()}else{var g=d(this.editor);this.saveContent();d(this.original).css({width:d(this.element).outerWidth()-6,height:d(this.element).height()-d(this.panel).height()-6,resize:"none"}).show();g.hide()}this.viewHTML=!(this.viewHTML)},tooltip:"View source code"},rtl:{visible:false,exec:function(){var g=d(this.editor).documentSelection();if(d("<div />").append(g).children().length>0){g=d(g).attr("dir","rtl")}else{g=d("<div />").attr("dir","rtl").append(g)}this.editorDoc.execCommand("inserthtml",false,d("<div />").append(g).html())},tooltip:"Right to Left"},ltr:{visible:false,exec:function(){var g=d(this.editor).documentSelection();if(d("<div />").append(g).children().length>0){g=d(g).attr("dir","ltr")}else{g=d("<div />").attr("dir","ltr").append(g)}this.editorDoc.execCommand("inserthtml",false,d("<div />").append(g).html())},tooltip:"Left to Right"}};d.extend(f,{insertImage:function(j,i){var h=d.data(this,"wysiwyg");if(h.constructor==f&&j&&j.length>0){if(d.browser.msie){h.focus()}if(i){h.editorDoc.execCommand("insertImage",false,"#jwysiwyg#");var g=h.getElementByAttributeValue("img","src","#jwysiwyg#");if(g){g.src=j;for(var k in i){g.setAttribute(k,i[k])}}}else{h.editorDoc.execCommand("insertImage",false,j)}}return this},createLink:function(i){var g=d.data(this,"wysiwyg");if(g.constructor==f&&i&&i.length>0){var h=e.call(d(g.editor));if(h&&h.length>0){if(d.browser.msie){g.focus()}g.editorDoc.execCommand("unlink",false,null);g.editorDoc.execCommand("createLink",false,i)}else{if(g.options.messages.nonSelection){alert(g.options.messages.nonSelection)}}}return this},insertHtml:function(g){var h=d.data(this,"wysiwyg");h.insertHtml(g);return this},insertTable:function(i,g,h){d.data(this,"wysiwyg").insertTable(i,g,h);return this},getContent:function(){var g=d.data(this,"wysiwyg");return g.getContent()},setContent:function(g){var h=d.data(this,"wysiwyg");h.setContent(g);h.saveContent();return this},clear:function(){var g=d.data(this,"wysiwyg");g.setContent("");g.saveContent();return this},removeFormat:function(){var g=d.data(this,"wysiwyg");g.removeFormat();return this},save:function(){var g=d.data(this,"wysiwyg");g.saveContent();return this},document:function(){var g=d.data(this,"wysiwyg");return d(g.editorDoc)},destroy:function(){var g=d.data(this,"wysiwyg");g.destroy();return this}});var c=function(){d(this).addClass("wysiwyg-button-hover")};var a=function(){d(this).removeClass("wysiwyg-button-hover")};d.extend(f.prototype,{original:null,options:{},element:null,rangeSaver:null,editor:null,removeFormat:function(){if(d.browser.msie){this.focus()}this.editorDoc.execCommand("removeFormat",false,null);this.editorDoc.execCommand("unlink",false,null);return this},destroy:function(){var g=d(this.element).closest("form");g.unbind("submit",this.autoSaveFunction).unbind("reset",this.resetFunction);d(this.element).remove();d.removeData(this.original,"wysiwyg");d(this.original).show();return this},focus:function(){this.editor.get(0).contentWindow.focus();return this},init:function(k,j){var i=this;this.editor=k;this.options=j||{};d.data(k,"wysiwyg",this);var m=k.width||k.clientWidth||0;var l=k.height||k.clientHeight||0;if(k.nodeName.toLowerCase()=="textarea"){this.original=k;if(m===0&&k.cols){m=(k.cols*8)+21;k.cols=1}if(l===0&&k.rows){l=(k.rows*16)+16;k.rows=1}this.editor=d(location.protocol=="https:"?'<iframe src="javascript:false;"></iframe>':"<iframe></iframe>").attr("frameborder","0");if(j.iFrameClass){this.editor.addClass(iFrameClass)}else{this.editor.css({minHeight:(l-6).toString()+"px",width:(m>50)?(m-8).toString()+"px":""});if(d.browser.msie){this.editor.css("height",l.toString()+"px")}}this.editor.attr("tabindex",d(k).attr("tabindex"))}var g=this.panel=d('<ul role="menu" class="panel"></ul>');this.appendControls();this.element=d("<div></div>").addClass("wysiwyg").append(g).append(d("<div><!-- --></div>").css({clear:"both"})).append(this.editor);if(!j.iFrameClass){this.element.css({width:(m>0)?m.toString()+"px":"100%"})}d(k).hide().before(this.element);this.viewHTML=false;this.initialHeight=l-8;this.initialContent=d(k).val();this.initFrame();this.autoSaveFunction=function(){i.saveContent()};this.resetFunction=function(){i.setContent(i.initialContent);i.saveContent()};if(this.options.resizeOptions&&d.fn.resizable){this.element.resizable(d.extend(true,{alsoResize:this.editor},this.options.resizeOptions))}var h=d(k).closest("form");if(this.options.autoSave){h.submit(i.autoSaveFunction)}h.bind("reset",i.resetFunction)},initFrame:function(){var g=this;var i="";if(this.options.css&&this.options.css.constructor==String){i='<link rel="stylesheet" type="text/css" media="screen" href="'+this.options.css+'" />'}this.editorDoc=b(this.editor);this.editorDoc_designMode=false;this.designMode();this.editorDoc.open();this.editorDoc.write(this.options.html.replace(/INITIAL_CONTENT/,function(){return g.initialContent}).replace(/STYLE_SHEET/,function(){return i}));this.editorDoc.close();if(d.browser.msie){window.setTimeout(function(){d(g.editorDoc.body).css("border","none")},0)}d(this.editorDoc).click(function(j){g.checkTargets(j.target?j.target:j.srcElement)});d(this.original).focus(function(){if(d(this).filter(":visible")){return}g.focus()});if(!d.browser.msie){d(this.editorDoc).keydown(function(j){if(j.ctrlKey){switch(j.keyCode){case 66:this.execCommand("Bold",false,false);return false;case 73:this.execCommand("Italic",false,false);return false;case 85:this.execCommand("Underline",false,false);return false}}return true})}else{if(this.options.brIE){d(this.editorDoc).keydown(function(k){if(k.keyCode==13){var j=g.getRange();j.pasteHTML("<br />");j.collapse(false);j.select();return false}return true})}}if(this.options.autoSave){var h=function(){g.saveContent()};d(this.editorDoc).keydown(h).keyup(h).mousedown(h).bind(d.support.noCloneEvent?"input":"paste",h)}if(this.options.css){window.setTimeout(function(){if(g.options.css.constructor==String){}else{d(g.editorDoc).find("body").css(g.options.css)}},0)}if(this.initialContent.length===0){this.setContent("<p>initial content</p>")}d.each(this.options.events,function(j,k){d(g.editorDoc).bind(j,k)});d(g.editor).blur(function(){g.rangeSaver=g.getInternalRange()});d(this.editorDoc.body).addClass("wysiwyg");if(this.options.events&&this.options.events.save){var h=this.options.events.save;d(g.editorDoc).bind("keyup",h);d(g.editorDoc).bind("change",h);if(d.support.noCloneEvent){d(g.editorDoc).bind("input",h)}else{d(g.editorDoc).bind("paste",h);d(g.editorDoc).bind("cut",h)}}},focusEditor:function(){if(this.rangeSaver!=null){if(window.getSelection){var g=window.getSelection();if(g.rangeCount>0){g.removeAllRanges()}g.addRange(savedRange)}else{if(document.createRange){window.getSelection().addRange(savedRange)}else{if(document.selection){savedRange.select()}}}}},execute:function(h,g){if(typeof(g)=="undefined"){g=null}this.editorDoc.execCommand(h,false,g)},designMode:function(){var h=3;var i;var g=this;var j=this.editorDoc;i=function(){if(b(g.editor)!==j){g.initFrame();return}try{j.designMode="on"}catch(k){}h--;if(h>0&&d.browser.mozilla){setTimeout(i,100)}};i();this.editorDoc_designMode=true},getSelection:function(){return(window.getSelection)?window.getSelection():document.selection},getInternalSelection:function(){return(this.editor[0].contentWindow.getSelection)?this.editor[0].contentWindow.getSelection():this.editor[0].contentDocument.selection},getRange:function(){var g=this.getSelection();if(!g){return null}return(g.rangeCount>0)?g.getRangeAt(0):(g.createRange?g.createRange():null)},getInternalRange:function(){var g=this.getInternalSelection();if(!g){return null}return(g.rangeCount>0)?g.getRangeAt(0):(g.createRange?g.createRange():null)},getContent:function(){return d(b(this.editor)).find("body").html()},setContent:function(g){d(b(this.editor)).find("body").html(g);return this},insertHtml:function(g){if(g&&g.length>0){if(d.browser.msie){this.focus();this.editorDoc.execCommand("insertImage",false,"#jwysiwyg#");var h=this.getElementByAttributeValue("img","src","#jwysiwyg#");if(h){d(h).replaceWith(g)}}else{this.editorDoc.execCommand("insertHTML",false,g)}}return this},insertTable:function(n,g,m){if(isNaN(g)||isNaN(n)||g===null||n===null){return}n=parseInt(n,10);g=parseInt(g,10);if(m===null){m="&nbsp;"}m="<td>"+m+"</td>";var l=['<table border="1" style="width: 100%;"><tbody>'];for(var k=g;k>0;k--){l.push("<tr>");for(var h=n;h>0;h--){l.push(m)}l.push("</tr>")}l.push("</tbody></table>");return this.insertHtml(l.join(""))},saveContent:function(){if(this.original){var g=this.getContent();if(this.options.rmUnwantedBr){g=(g.substr(-4)=="<br>")?g.substr(0,g.length-4):g}d(this.original).val(g);if(this.options.events&&this.options.events.save){this.options.events.save.call(this)}}return this},withoutCss:function(){if(d.browser.mozilla){try{this.editorDoc.execCommand("styleWithCSS",false,false)}catch(h){try{this.editorDoc.execCommand("useCSS",false,true)}catch(g){}}}return this},appendMenuCustom:function(i,h){var g=this;d(window).bind("wysiwyg-trigger-"+i,h.callback);return d('<li role="menuitem" UNSELECTABLE="on"><img src="'+h.icon+'" class="jwysiwyg-custom-icon" />'+(i)+"</li>").addClass("custom-command-"+i).addClass("jwysiwyg-custom-command").addClass(i).attr("title",h.tooltip).hover(c,a).click(function(){g.triggerCallback(i)}).appendTo(this.panel)},triggerCallback:function(g){d(window).trigger("wysiwyg-trigger-"+g,[this.getInternalRange(),this,this.getInternalSelection()]);d(".custom-command-"+g,this.panel).blur();this.focusEditor()},appendMenu:function(l,h,j,i,k){var g=this;h=h||[];return d('<li role="menuitem" UNSELECTABLE="on">'+(j||l)+"</li>").addClass(j||l).attr("title",k).hover(c,a).click(function(){if(i){i.apply(g)}else{g.focus();g.withoutCss();g.editorDoc.execCommand(l,false,h)}if(g.options.autoSave){g.saveContent()}this.blur();g.focusEditor()}).appendTo(this.panel)},appendMenuSeparator:function(){return d('<li role="separator" class="separator"></li>').appendTo(this.panel)},parseControls:function(){if(this.options.parseControls){return this.options.parseControls.call(this)}return this.options.controls},appendControls:function(){var h=this.parseControls();var k=0;var g=true;for(var i in h){var j=h[i];if(j.groupIndex&&k!=j.groupIndex){k=j.groupIndex;g=false}if(!j.visible){continue}if(!g){this.appendMenuSeparator();g=true}if(j.custom){this.appendMenuCustom(i,j.options)}else{this.appendMenu(j.command||i,j["arguments"]||"",j.className||j.command||i||"empty",j.exec,j.tooltip||j.command||i||"")}}},checkTargets:function(j){for(var g in this.options.controls){var i=this.options.controls[g];var m=i.className||i.command||g||"empty";d("."+m,this.panel).removeClass("active");if(i.tags||(i.options&&i.options.tags)){var o=i.tags||(i.options&&i.options.tags);var l=j;do{if(l.nodeType!=1){break}if(d.inArray(l.tagName.toLowerCase(),o)!=-1){d("."+m,this.panel).addClass("active")}}while((l=l.parentNode))}if(i.css||(i.options&&i.options.css)){var k=i.css||(i.options&&i.options.css);var h=d(j);do{if(h[0].nodeType!=1){break}for(var n in k){if(h.css(n).toString().toLowerCase()==k[n]){d("."+m,this.panel).addClass("active")}}}while((h=h.parent()))}}},getElementByAttributeValue:function(j,g,k){var m=this.editorDoc.getElementsByTagName(j);for(var h=0;h<m.length;h++){var l=m[h].getAttribute(g);if(d.browser.msie){l=l.substr(l.length-k.length)}if(l==k){return m[h]}}return false}})})(jQuery);
