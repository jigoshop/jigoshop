/**
 * jQuery Cookie plugin
 *
 * Copyright © 2010 Klaus Hartl (stilbuero.de)
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */
jQuery.cookie=function(key,value,options){if(arguments.length>1&&String(value)!=="[object Object]"){options=jQuery.extend({},options);if(value===null||value===undefined){options.expires=-1;}
if(typeof options.expires==='number'){var days=options.expires,t=options.expires=new Date();t.setDate(t.getDate()+days);}
value=String(value);return(document.cookie=[encodeURIComponent(key),'=',options.raw?value:encodeURIComponent(value),options.expires?'; expires='+options.expires.toUTCString():'',options.path?'; path='+options.path:'',options.domain?'; domain='+options.domain:'',options.secure?'; secure':''].join(''));}
options=value||{};var result,decode=options.raw?function(s){return s;}:decodeURIComponent;return(result=new RegExp('(?:^|; )'+encodeURIComponent(key)+'=([^;]*)').exec(document.cookie))?decode(result[1]):null;};

/**
 * Spoofs placeholders in browsers that don't support them (eg Firefox 3)
 *
 * Copyright 2011 Dan Bentley
 * Licensed under the Apache License 2.0
 *
 * Author: Dan Bentley [github.com/danbentley]
 */
(function($){if("placeholder"in document.createElement("input"))return;$(document).ready(function(){$(':input[placeholder]').each(function(){setupPlaceholder($(this));});$('form').submit(function(e){clearPlaceholdersBeforeSubmit($(this));});});function setupPlaceholder(input){var placeholderText=input.attr('placeholder');if(input.val()==='')input.val(placeholderText);input.bind({focus:function(e){if(input.val()===placeholderText)input.val('');},blur:function(e){if(input.val()==='')input.val(placeholderText);}});}
function clearPlaceholdersBeforeSubmit(form){form.find(':input[placeholder]').each(function(){var el=$(this);if(el.val()===el.attr('placeholder'))el.val('');});}})(jQuery);

if( jQuery.isFunction(jQuery.fn.sortable) ){
/* Modifided script from the simple-page-ordering plugin */
jQuery(document).ready(function($){$('table.widefat.wp-list-table tbody th, table.widefat tbody td').css('cursor','move');$("table.widefat.wp-list-table tbody").sortable({items:'tr:not(.inline-edit-row)',cursor:'move',axis:'y',containment:'table.widefat',placeholder:'product-cat-placeholder',scrollSensitivity:40,helper:function(e,ui){ui.children().each(function(){jQuery(this).width(jQuery(this).width());});return ui;},start:function(event,ui){if(!ui.item.hasClass('alternate'))ui.item.css('background-color','#ffffff');ui.item.children('td,th').css('border-bottom-width','0');ui.item.css('outline','1px solid #aaa');},stop:function(event,ui){ui.item.removeAttr('style');ui.item.children('td,th').css('border-bottom-width','1px');},update:function(event,ui){var termid=ui.item.find('.check-column input').val();var termparent=ui.item.find('.parent').html();var prevtermid=ui.item.prev().find('.check-column input').val();var nexttermid=ui.item.next().find('.check-column input').val();var prevtermparent=undefined;if(prevtermid!=undefined){var prevtermparent=ui.item.prev().find('.parent').html();if(prevtermparent!=termparent)prevtermid=undefined;}
var nexttermparent=undefined;if(nexttermid!=undefined){nexttermparent=ui.item.next().find('.parent').html();if(nexttermparent!=termparent)nexttermid=undefined;}
if((prevtermid==undefined&&nexttermid==undefined)||(nexttermid==undefined&&nexttermparent==prevtermid)||(nexttermid!=undefined&&prevtermparent==termid)){$("table.widefat tbody").sortable('cancel');return;}
ui.item.find('.check-column input').hide().after('<img alt="processing" src="images/wpspin_light.gif" class="waiting" style="margin-left: 6px;" />');$.post(ajaxurl,{action:'jigoshop-categories-ordering',id:termid,nextid:nexttermid},function(response){if(response=='children')window.location.reload();else{ui.item.find('.check-column input').show().siblings('img').remove();}});$('table.widefat tbody tr').each(function(){var i=jQuery('table.widefat tbody tr').index(this);if(i%2==0)jQuery(this).addClass('alternate');else jQuery(this).removeClass('alternate');});}});});
}