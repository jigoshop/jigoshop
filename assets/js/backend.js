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

jQuery(function($){
	// Tooltips
	if (typeof($.fn.tipTip) == 'function'){
		$('.tips, .help_tip').tipTip({
			'attribute': 'data-tip',
			'fadeIn': 50,
			'fadeOut': 50,
			'delay': 200
		});
	}
	// Regular select boxes
	$(':input.jigoshop-enhanced-select').filter(':not(.enhanced)').each(function(){
		$(this).select2({
			minimumResultsForSearch: 10,
			allowClear: $(this).data('allow_clear') ? true : false,
			placeholder: $(this).data('placeholder')
		}).addClass('enhanced');
	});
	// Ajax product search box
	$(':input.jigoshop-product-search').filter(':not(.enhanced)').each(function(){
		var select2_args = {
			allowClear: $(this).data('allow_clear') ? true : false,
			placeholder: $(this).data('placeholder'),
			minimumInputLength: $(this).data('minimum_input_length') ? $(this).data('minimum_input_length') : '3',
			escapeMarkup: function(m){
				return m;
			},
			ajax: {
				url: jigoshop_params.ajax_url,
				dataType: 'json',
				quietMillis: 250,
				data: function(term, page){
					return {
						term: term,
						action: $(this).data('action') || 'jigoshop_json_search_products_and_variations',
						security: jigoshop_params.search_products_nonce
					};
				},
				results: function(data, page){
					return {results: data};
				},
				cache: true
			}
		};
		if($(this).data('multiple') === true){
			select2_args.multiple = true;
			select2_args.initSelection = function(element, callback){
				var data = $.parseJSON(element.attr('data-selected'));
				var selected = [];
				$(element.val().split(",")).each(function(i, val){
					selected.push({id: val, text: data[val]});
				});
				return callback(selected);
			};
			select2_args.formatSelection = function(data){
				return '<div class="selected-option" data-id="' + data.id + '">' + data.text + '</div>';
			};
		} else {
			select2_args.multiple = false;
			select2_args.initSelection = function(element, callback){
				var data = {id: element.val(), text: element.attr('data-selected')};
				return callback(data);
			};
		}
		$(this).select2(select2_args).addClass('enhanced');
	});
});
