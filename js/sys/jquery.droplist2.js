/**
 * @author evileagle
 * usage $(selector).droplist(parameters);
 * @param url - url скрипта, генерирующего новый список по определенному алгоритму в виде 
 * 				<div id="name_ID" class="droplist_item">Option</div> 
 * 				где name - имя исходного select\'а
 * 				ID - значение value соответствующего тэга <option>
 * @param type - тип фильтрации списка, принимает значения 'client' или  'server'
 * 			при 'client' - фильтрует на стороне клиента (в браузере)
 * 			при 'local' - отсылает POST запрос на сервер с параметром 'inputed_droplist_value', равным введенной строке
 * @param delay - интервал после нажатия клавиши на клавиатуре, по истечении которого происходит фильтрация или запрос
 * @param charcount - минимальное количество символов, при котором производится сортировка или запрос 
 */
jQuery.fn.droplist = function(options)
{	//Настройки скрипта
	var settings = {url: '/', type: 'client', delay: '1000', charcount: 3};
	settings = jQuery.extend(settings, options);
	
	//Копируем имя
	var name = jQuery(this).attr('name');
	
	//Заменяем <option> на <div>
	jQuery(this).children('option').each(function(i){
		jQuery(this).replaceWith('<div id="'+name+'_'+jQuery(this).val()+'" class="droplist_item">'+jQuery(this).html()+'</div>');
	});
	
	//Добавляем новые поля и удаляем все старое
	jQuery(this).before('<div id="'+name+'_container" class="droplist_container">\
							<input type="text" id="'+name+'_droplist_input" class="droplist_input" value="">\
							<input type="hidden" name="'+name+'" id="'+name+'_id'+'" value="0"/> \
							<button type="button" id="'+name+'_button" class="droplist_button">...</button>\
							<div id="'+name+'_droplist_list" class="droplist_list">\
							'+jQuery(this).html()+'\
							</div>\
						</div>');
	jQuery(this).remove();	
	
	//Костыли убогому
	if ($.browser.msie){
		$('.droplist_item').bind("mouseenter mouseleave", 
		function(e){
	       	$(this).toggleClass("jshover");
	   	});
		$('#'+name+'_droplist_list').css({height: '20em', marginTop: '0em'});
	}

	//Обработчик нажатия кнопки раскрытия списка
	jQuery('#'+name+'_button').click(function(){
					jQuery('#'+name+'_droplist_list').slideToggle('fast');
				});
				
	jQuery('#'+name+'_droplist_list > div').live('click',function(){
					jQuery('#'+name+'_droplist_input').val(jQuery(this).html());
					re = /\w*_(\d*)/
					x = re.exec(name+jQuery(this).attr('id'));
					jQuery('#'+name+'_id').val(x[1]);
					jQuery('#'+name+'_id').trigger('change');
					jQuery('#'+name+'_droplist_list').slideUp('fast');
				});
	
	var to = setTimeout(function(){},1000);
	clearTimeout(to);					
	
	switch (settings.type){
		case 'server':
						jQuery('#'+name+'_droplist_input').keyup(function(){
										if (jQuery(this).val().length >= settings.charcount)
										{	clearTimeout(to);
											to = setTimeout(function(){
												if (jQuery('#'+name+'_droplist_input').val().length <= 4) exit();
												jQuery('#'+name+'_droplist_list').load(settings.url, {inputed_droplist_value: jQuery('#'+name+'_droplist_input').val()});
												jQuery('#'+name+'_droplist_list:hidden').slideDown('fast');
											}, settings.delay);
										}
						});
						break;
		case 'client':
						jQuery('#'+name+'_droplist_input').keyup(function(){
							if (jQuery(this).val().length >= settings.charcount) {
							jQuery('#' + name + '_droplist_list > div').each(function(){
								var reg = new RegExp(jQuery('#' + name + '_droplist_input').val(), 'i');
								if ( !reg.test( jQuery(this).html() ) ) 
									jQuery(this).hide();
								else 
									jQuery(this).show();
								jQuery('#'+name+'_droplist_list:hidden').slideDown('fast');
								});
							}
							else
								jQuery('#' + name + '_droplist_list > div').show();
						});
						break;
		default: 
				console.error('Неверный параметр type в droplist()');
	}
	
	return this;	
}
