/**
 * usage $(#selector).droplist(optionList, url, delay, backgroundColor, hoverColor, borderColor);
 * @author evileagle
 * @method droplist(optionList, url, delay, backgroundColor, hoverColor, borderColor);
 * @param optionList - набор DIV'ов типа '<div id="n1">xxx</div><div id="n2">xxx</div>' для выпадающего меню
 * @param url - url скрипта, генерирующего новый список по определенному алгоритму
 * 
 */
jQuery.fn.droplist = function(options)
{
	var settings = {opt: '',url: '/', delay: '1000', bgcolor: '#CCC', hovercolor: '#999', bordercolor: '#666'};
	settings = jQuery.extend(settings, options);
	var name = jQuery(this).attr('name');
	jQuery(this).removeAttr('name');
	jQuery(this).css({ width: '95%', float: 'left'});
	jQuery(this).before('<style type="text/css">#'+name+'_but {width: 3%;float:left;text-align:center;cursor: pointer;clear:right;}\
						#'+name+'_select {display: none;float:left;border: solid 1px '+settings.bordercolor+';clear:both;position:absolute;width: 95%;\
						max-height: 20em;z-index: 3;margin-top: 1.5em;	background-color: '+settings.bgcolor+';overflow-y: scroll;}\
						#'+name+'_select > div {display:block;width: 100%;border-bottom: solid 1px '+settings.bordercolor+';clear:both;\
						cursor: pointer;background-color: '+settings.bgcolor+';float: left;}\
						#'+name+'_select > div:hover {background-color: '+settings.hovercolor+'}</style><div>');
	jQuery(this).after('<input type="hidden" name="'+name+'" id="'+name+'_id'+'" value="0"/> \
	<button type="button" id="'+name+'_but">...</button>\
	<div id="'+name+'_select">'+settings.opt+'</div></div>');

	jQuery('#'+name+'_but').click(function(){
					if (jQuery('#'+name+'_select').is(':visible'))
						jQuery('#'+name+'_select').slideUp('fast');
					else
						jQuery('#'+name+'_select').slideDown('fast');
				});
				
	jQuery('#'+name+'_select > div').live('click',function(){
					jQuery('#'+name).val(jQuery(this).html());
					re = /\w*_(\d*)/
					x = re.exec(name+jQuery(this).attr('id'));
					jQuery('#'+name+'_id').val(x[1]);
					jQuery('#'+name+'_id').trigger('change');
					jQuery('#'+name+'_select').slideUp('fast');
				});
	
	var to = setTimeout(function(){},1000);
	clearTimeout(to);					
	
	jQuery('#'+name).keypress(function(){
					if (jQuery(this).val().length >= 4)
					{	clearTimeout(to);
						to = setTimeout(function(){
							if (jQuery('#'+name).val().length <= 4) exit();
							jQuery('#'+name+'_select').load(settings.url, {input: jQuery('#'+name).val()});
							jQuery('#'+name+'_select:hidden').slideDown('fast');
						}, settings.delay);
					}
				});
	
	return this;	
}