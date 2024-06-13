/**
 * @author evileagle
 * @version 1.0.1
 **/
jQuery.fn.droplist = function(options)
{	//Настройки скрипта
	var settings = {url: '/', type: 'client', delay: '1000', charcount: 0, buttontext: '&oplus;'};
	settings = jQuery.extend(settings, options);
	
	//Копируем имя
	var name = jQuery(this).attr('name');
	
	//Заменяем <option> на <div>
	jQuery(this).children('option').each(function(i){
		jQuery(this).replaceWith('<div id="'+name+'_'+jQuery(this).val()+'" class="droplist_item '+name+'_item">'+jQuery(this).html()+'</div>');
	});
	
	//Добавляем новые поля и удаляем все старое
	jQuery(this).before('<div id="'+name+'_container" class="droplist_container">\
							<input type="text" id="'+name+'_droplist_input" class="droplist_input" value="">\
							<input type="hidden" name="'+name+'" id="'+name+'_id'+'" value="0"/> \
							<span id="'+name+'_droplist_button" class="droplist_button">'+settings.buttontext+'</span>\
							<div id="'+name+'_droplist_list" class="droplist_list">\
							'+jQuery(this).html()+'\
							</div>\
						</div>');
	jQuery(this).remove();	
	
	var dlinput = jQuery('#'+name+'_droplist_input');
	var dlbutton = jQuery('#'+name+'_droplist_button');
	var dlid = jQuery('#'+name+'_id');
	var dllist = jQuery('#'+name+'_droplist_list');
	var dlitem = jQuery('.'+name+'_item');
	
	//Обработчик нажатия кнопки раскрытия списка
	dlbutton.click(function(){
					if(dllist.is(':visible')){
						if (dlid.val() == 0 && dlinput.val().length > 0)
							alert('Вы должны выбрать какой-либо пункт или оставить поле пустым!');
						else
							dllist.slideUp('fast');
					}
					else 
						dllist.slideDown('fast');
				});
	dlinput.bind('focus', function(){dllist.slideDown('fast');})
				
	dlitem.live('click',function(){
					dlinput.val(jQuery(this).html());
					re = /\w*_(\d*)/
					x = re.exec(name+jQuery(this).attr('id'));
					dlid.val(x[1]);
					dlid.trigger('change');
					dllist.slideUp('fast');
				});
	
	var to = setTimeout(function(){},1000);
	clearTimeout(to);
	
	switch (settings.type){
		case 'server':
						dlinput.keyup(function(){
							if (dlid.val() != 0 && jQuery(this).val() != jQuery('#' + name + '_' + dlid.val()).html()) 
								dlid.val(0);
							if (jQuery(this).val().length >= settings.charcount)
										{	clearTimeout(to);
											to = setTimeout(function(){
												if (dlinput.val().length >= settings.charcount) {
													dllist.load(settings.url, {
														inputed_droplist_value: dlinput.val(), field: name
													});
												}
											}, settings.delay);
										}
						});
						break;
		
		case 'client':
				dlinput.keyup(function(){
					if (dlid.val() != 0 && jQuery(this).val() != jQuery('#' + name + '_' + dlid.val()).html()) 
						dlid.val(0);
					if (jQuery(this).val().length >= settings.charcount) {
						dlitem.each(function(){
							var reg = new RegExp(dlinput.val(), 'i');
							if (!reg.test(jQuery(this).html())) 
								jQuery(this).hide();
							else 
								jQuery(this).show();
						});
					}
					else 
						dlitem.show();
				});
				break;
		default: 
				console.error('Неверный параметр type в droplist()');
	}
		
	//Костыли убогому
	if (jQuery.browser.msie){
		function toggleHoverClass(e){
			jQuery(e.target).toggleClass("jshover");
		}
		dlitem.live('mouseover', toggleHoverClass);
		dlitem.live('mouseout', toggleHoverClass);
		dllist.addClass('ie');
	}
	
	return this;	
}
