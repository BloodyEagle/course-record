<?php
	require_once('inc/config.inc.php');
	require_once('inc/common.inc.php');
	
	
	$coockieAuth = false;
	debug($_REQUEST,'---');
	if (isset($_COOKIE['authorized']) && $_REQUEST['act'] <> 'login'){
		$R = unserialize(stripcslashes($_COOKIE['authorized']));
		debug($R,'Other');

		$_REQUEST['user'] = $R['user'];
		$_REQUEST['pass'] = $R['pass'];
		if (!isset($_REQUEST['act']))
			$_REQUEST['act'] = 'shownew';
		$coockieAuth = true;
	}
	
	if (!isset($_REQUEST['act']))
		$_REQUEST['act'] = 'showform';
	
	$menu = '<p align="center">[ <a href="/cr/adm.php?act=logout">Выход</a> | <a href="/cr/adm.php?act=shownew">Новые заявки</a> | <a href="/cr/adm.php?act=showctree">Управление курсами</a> | <a href="/cr/adm.php?act=showchecker">Управление модераторами</a> | <a href="/cr/adm.php?act=showsprav">Справочники</a> ]</p>';

//======================================================================================
	function tree($a, $level = ''){
		$size = count($a);
		foreach ($a as $k=>$v){
			$prefix = $level.'&nbsp;&nbsp;&nbsp;&nbsp;';
			$html .=($prefix == '&nbsp;&nbsp;&nbsp;&nbsp;' ? '<p>' : '').$prefix.
			'<a href="/cr/adm.php?act=viewclist&cat='.$k.'" style="text-decoration: none;color: #333333;">'.$v['name'].
			' <img class="imgnav list" title="Список курсов раздела" src="img/1x1.gif"></a>'.
			' <a href="/cr/adm.php?act=editcat&cat='.$k.'"><img class="imgnav edit" title="Править раздел" src="img/1x1.gif"></a>'.
			' <a href="/cr/adm.php?act=addsubcat&cat='.$k.'"><img class="imgnav addcat" title="Добавить подраздел" src="img/1x1.gif"></a>'.
			' <a href="/cr/adm.php?act=addcourse&cat='.$k.'"><img class="imgnav add" title="Добавить курс" src="img/1x1.gif"></a>'.
			' <a href="/cr/adm.php?act=delcat&cat='.$k.'" onClick="if(window.confirm(\'Внимание!\nРаздел будет удален со всеми подразделами и со всеми вложенными курсами!\nОтмена данного действия будет невозможна!\nВы уверены, что хотите удалить раздел?\')==true) {return true;} else {return false;}"><img class="imgnav del" title="Удалить раздел" src="img/1x1.gif"></a>'.
			'<br />'.tree($v['childNodes'], $prefix);
		}
		return $html;
	}
	
//======================================================================================
	function seltree($a, $level = ''){
		$size = count($a);
		$html = '';
		foreach ($a as $k=>$v){
			$prefix = $level.'+++';
			$html .= '<option value="'.$k.'">'.$level.$v['name'].'</option>'.seltree($v['childNodes'], $prefix);
		}
		return $html; 
	}
	//=======================================================================================
	
	switch ($_REQUEST['act']){
		case 'showform':
			$O = new AdmCourse();
			$O->showform();
			echo $O->gethtml();
			break;
//=======================================================	
		case 'login':
			$O = new AdmCourse($_POST['user'], $_POST['pass']);
			if ($O->auth()){
			
				header('refresh:1;url=/cr/adm.php?act=shownew');
			}
			header('Location: /cr/adm.php?act=shownew');
			echo $O->gethtml();
			break;
//=======================================================	
		case 'shownew':
		case 'showaccept':
		case 'showdenid':
			$O = new AdmCourse($_REQUEST['user'], $_REQUEST['pass']);
			$Ghtml = file_get_contents(dirname(__FILE__).'/studentlist.html');
			if ($O->auth($coockieAuth)){
//-----------------------------------------------------------------------------------------				
				//$html = '<p>Всего заявок, ожидающих рассмотрения - '.count($rows).'.</p>';
				if ($_REQUEST['act'] == 'showaccept'){
					$query = "SELECT ?_group.id AS ARRAY_KEY, ?_group.name AS gname, (SELECT COUNT(*) FROM ?_cmanage WHERE ?_cmanage.group = ?_group.id) AS scount FROM ?_group, ?_gmanage WHERE ?_group.id = ?_gmanage.`group` AND ?_gmanage.course = ?d ORDER BY ?_group.name";
					$groups = SQL::getInstance()->link->select($query, $_REQUEST['cat']);
					$glist = 'групп нет';
					if ($groups){
						$glist = 'Создано групп: '.count($groups).'.';
						foreach ($groups as $k=>$v)
							$glist .=  '<br><span class="tgroupname" id="'.$k.'"><a href="/cr/adm.php?act=showgroup&cat='.$_REQUEST['cat'].'">'.$v['gname'].'</a> (<span id="c'.$k.'">'.$v['scount'].'</span>)</span>';
					}
						
					$html = '<p align="right">'.
					'Список групп&darr;<br><span id="gcount">'.$glist.'</span>'.
					'<span id="addgroup" style="display: block;border-style: solid;border-color: #e8e8e8 #e8e8e8 #cccccc #cccccc; border-width: medium; background-color: #d6d6d6; text-decoration: underline; width: 200px; cursor: pointer; text-align: center;"><img class="imgnav add addgroup" title="Создать новую группу" src="img/1x1.gif">&nbsp;Создать новую группу</span>'.
					'</p>';
				}
//-----------------------------------------------------------------------------------------				
				$query = "SELECT 
							?_cmanage.id AS `mid`, 
						    ?_cmanage.course AS cid,
							?_course.name AS cname,
						    ?_course.predmet AS cpredmet,
						    DATE_FORMAT(?_course.`start`, '%d.%m.%Y') AS start, 
							DATE_FORMAT(?_course.`stop`, '%d.%m.%Y') AS stop,
						    ?_cmanage.student AS sid,
						    ?_student.fio,
						    ?_student.region,
						    ?_student.city,
						    ?_student.school,
						    ?_student.status,
						    ?_scategory.name AS category,
						    ?_student.stage,
						    ?_student.birthday,
						    ?_student.phone,
						    ?_student.email,
						    ?_student.hotel,
						    ?_cmanage.regdate,
						    ?_cmanage.group,
							?_group.name AS gname
						FROM 
							?_cmanage, ?_course, ?_student, ?_scategory, ?_group  
						WHERE
							?_cmanage.checked = ?d AND 
							{ ?_course.id = ?d AND }
							?_course.id = ?_cmanage.course AND
							{ ?_course.owner = ?d AND }
						    ?_student.id = ?_cmanage.student AND
						    ?_scategory.id = ?_student.category AND
							?_group.id = ?_cmanage.group";
				$rows = SQL::getInstance()->link->query($query, ($_REQUEST['act'] == 'showaccept' ? 1 : ($_REQUEST['act'] == 'showdenid' ? -1 : 0)), (isset($_REQUEST['cat']) ? $_REQUEST['cat'] : DBSIMPLE_SKIP), ($O->getaccess() < 255 ? $O->getid() : DBSIMPLE_SKIP) );
				debug('Результат:');
				debug($rows);
				if (!$rows){
					$Ghtml = str_replace('<#NAV#>', $menu, $Ghtml);
					$Ghtml = str_replace('<#LIST#>', 'Нет заявок, ожидающих рассмотрения!', $Ghtml);
					echo $Ghtml;
					break;
				}
				$html .= '<p>Всего заявок, ожидающих рассмотрения - '.count($rows).'.</p>';
				foreach ($rows as $k=>$v){
					$html .='';
					$html .= '<div class="descmain">На курс <span style="color: blue;">"'.stripslashes($v['cname']).'"</span>['.$v['start'].' &ndash;'.$v['stop'].
							'] (Категория слушателей: '.stripcslashes($v['cpredmet']).') подана заявка.<br />Заявитель: <span style="font-weight : bold;">'.$v['fio'].
							'</span>.<br />Регион: '.stripcslashes($v['region']).'.<br />Город или район: '.stripcslashes($v['city']).'.<br />ОУ: '.
							stripslashes($v['school']).'.<br />Должность: '.stripcslashes($v['status']).
							'. '.($v['hotel'] == 1 ? '<br /><span style="color: red;">Нуждается в общежитии.</span>' : '').
							'<div class="nav">'.
							
							($_REQUEST['act'] == 'showaccept' ? '<span class="addtogroup" id="'.$v['mid'].'">'.$v['gname'].'</span><br><br>' : '').
							
							'<form action="/cr/adm.php?act=check" method="post" ><input type="hidden" name="invoice" value="'.$v['mid'].'"><input type="hidden" name="course" value="'.$v['cid'].'"> <input type="submit" name="accept" value="Одобрить" /> <input type="submit" name="denid" value="Отклонить" /> <input type="submit" name="edit" value="Править" /> <input type="submit" name="delete" value="Удалить" onClick="if(window.confirm(\'Вы уверены, удалить заявку?\')==true) {return true;} else {return false;}" /></form>'.'</div>'.
							'<br />Дата подачи заявки: '.$v['regdate'].
							'<br /><span class="anc" style="font-size: x-large; text-decoration: none; cursor: pointer" title="Дополнительная информация">+</span><br /><span class="descother" style="display: none;">Категория: '.
							$v['category'].'.<br />Стаж: '.$v['stage'].'.<br />Год рождения: '.$v['birthday'].
							'.<br />Телефон: '.$v['phone'].'.<br />e-mail: '.$v['email'].'.</span></div>'; 
				}

				$html .= '';
				$Ghtml = str_replace('<#NAV#>', $menu, $Ghtml);
				$Ghtml = str_replace('#cid#', $v['cid'], $Ghtml);
				$Ghtml = str_replace('<#LIST#>', $html, $Ghtml);
				echo $Ghtml;
			}else{
				$Ghtml = file_get_contents(dirname(__FILE__).'/studentlist.html');
				$Ghtml = str_replace('<#NAV#>', $menu, $Ghtml);
				$Ghtml = str_replace('<#LIST#>', $O->gethtml(), $Ghtml);
				
				echo $Ghtml;
			}
			break;
//=======================================================			
		case 'check':
			$O = new AdmCourse($_REQUEST['user'], $_REQUEST['pass']);
			if ($O->auth($coockieAuth)){
				if (isset($_POST['accept'])){
					$query = "UPDATE ?_cmanage SET checked = true WHERE id = ?d";
					if (SQL::getInstance()->link->query($query, $_POST['invoice'])){
						$query = "SELECT ?_student.fio, ?_student.email FROM ?_student, ?_cmanage WHERE ?_student.id = ?_cmanage.student AND ?_cmanage.id = ?d";
						$rows = SQL::getInstance()->link->selectRow($query, $_POST['invoice']);
						$message = '<html>
												<head>
												  <title>Изменение статуса заявки на курсы в МРИО</title>
												</head>
												<body>
												  <p>Добрый день, '.$rows['fio'].'.</p>
												  <p>Вы подавали заявку на прохождение курсов повышения квалификации в мордовском республиканском институте образования. Ваша заявка была одобрена и вы зачислены в группу слушателей.</p>
		  									  	  <p>Дополнительную информацию можно получить по телефону 8-8342-32-17-35.</p>
												</body>
												</html>';
						$headers  = 'MIME-Version: 1.0' . "\r\n";
						$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n".
								'From: Сайт МРИО.<no-reply@edurm.ru>' . "\r\n";
						mail($rows['email'], 'Изменение статуса заявки на курсы в МРИО', $message, $headers);
						header('refresh:3;url=/cr/adm.php?act=shownew');
						echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />Заявка подтверждена. Сейчас вы будете перемещены к списку заявок.';
					}
				}
				if (isset($_POST['denid'])){
					$query = "UPDATE ?_cmanage SET checked = -1 WHERE id = ?d";
					if (SQL::getInstance()->link->query($query, $_POST['invoice'])){
						$query = "SELECT ?_student.fio, ?_student.email FROM ?_student, ?_cmanage WHERE ?_student.id = ?_cmanage.student AND ?_cmanage.id = ?d";
						$rows = SQL::getInstance()->link->selectRow($query, $_POST['invoice']);
						$message = '<html>
												<head>
												  <title>Изменение статуса заявки на курсы в МРИО</title>
												</head>
												<body>
												  <p>Добрый день, '.$rows['fio'].'.</p>
												  <p>Вы подавали заявку на прохождение курсов повышения квалификации в мордовском республиканском институте образования. Ваша заявка была отклонена.</p>
		  									  	  <p>Дополнительную информацию можно получить по телефону 8-8342-32-17-35.</p>
												</body>
												</html>';
						$headers  = 'MIME-Version: 1.0' . "\r\n";
						$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n".
								'From: Сайт МРИО.<no-reply@edurm.ru>' . "\r\n";
						mail($rows['email'], 'Изменение статуса заявки на курсы в МРИО', $message, $headers);
						header('refresh:3;url=/cr/adm.php?act=shownew');
						echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />Заявка отклонена. Сейчас вы будете перемещены к списку заявок.';
					}
				}
				if (isset($_POST['delete'])){//---------------------------------------------------------
					$query = "SELECT ?_student.fio, ?_student.email FROM ?_student, ?_cmanage WHERE ?_student.id = ?_cmanage.student AND ?_cmanage.id = ?d";
					$rows = SQL::getInstance()->link->selectRow($query, $_POST['invoice']);
					$message = '<html>
												<head>
												  <title>Изменение статуса заявки на курсы в МРИО</title>
												</head>
												<body>
												  <p>Добрый день, '.$rows['fio'].'.</p>
												  <p>Вы подавали заявку на прохождение курсов повышения квалификации в мордовском республиканском институте образования. Ваша заявка была удалена. Возможно вы указали о себе неверную информацию.</p>
		  									  	  <p>Дополнительную информацию можно получить по телефону 8-8342-32-17-35.</p>
												</body>
												</html>';
					$headers  = 'MIME-Version: 1.0' . "\r\n";
					$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n".
							'From: Сайт МРИО.<no-reply@edurm.ru>' . "\r\n";
					mail($rows['email'], 'Изменение статуса заявки на курсы в МРИО', $message, $headers);
					header('refresh:3;url=/cr/adm.php?act=shownew');
					echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />Заявка удалена. Сейчас вы будете перемещены к списку заявок.';
						
					$query = "DELETE FROM ?_student WHERE id = (SELECT student FROM ?_cmanage WHERE id = ? AND course = (SELECT id FROM ?_course WHERE owner = ?d AND id = ?d))";
					//$query = "DELETE FROM ?_cmanage WHERE id = ?d";
					if (SQL::getInstance()->link->query($query, $_POST['invoice'], $O->getid(), $_POST['course'])){
						$query = "DELETE FROM ?_cmanage WHERE id = ?d";
						if (SQL::getInstance()->link->query($query, $_POST['invoice'])){
						} else {
							header('refresh:3;url=/cr/adm.php?act=shownew');
							echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />Заявку удалить не удалось. Сейчас вы будете перемещены к списку заявок.';							
						}
					}else{
						header('refresh:3;url=/cr/adm.php?act=shownew');
						echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />Заявку удалить не удалось. Сейчас вы будете перемещены к списку заявок.';
					}
				}
			if (isset($_POST['edit'])){//---------------------------------------------------------
				//debug($_SERVER);	
				$query = "SELECT 
								?_student.`id`,?_student.`fio`,?_student.`region`,?_student.`city`,
								?_student.`school`,?_student.`status`,?_student.`category`,
								?_student.`stage`,?_student.`birthday`,?_student.`phone`,?_student.`email`,?_student.`hotel`
							FROM 
								?_student, ?_cmanage
							WHERE 
								  ?_student.id = ?_cmanage.student AND
								  ?_cmanage.id = ?d";
					$rows = SQL::getInstance()->link->selectRow($query, $_POST['invoice']);
					debug($rows);
					if ($rows){
						$Ghtml = file_get_contents(dirname(__FILE__).'/inc/ShowCList/showcourse.html');
						//$html = '<input type="button" id="recToCourse" value="Подать заявку на курс" />';
						
						$code = "$('#ddd').remove();".
								"$('#recform').show();".
								//'$("#fio").html(\'<label>Ф.И.О. <input type="text" id="fname" name="fname" /><input type="hidden" id="student" name="student" value="'.$rows['id'].'" /><input type="hidden" id="ref" name="ref" value="'.$_SERVER['HTTP_REFERER'].'" /></label>\');'.
								'$("#fio").html(\'<tr><td style="vertical-align: middle; text-align: right; width: 50%;">Ф.И.О.&nbsp;</td><td style="vertical-align: middle; width: 50%;"><input type="text" id="fname" name="fname" /><input type="hidden" id="student" name="student" value="'.$rows['id'].'" /><input type="hidden" id="ref" name="ref" value="'.$_SERVER['HTTP_REFERER'].'" /></td></tr>\');'.
								"$('#fname').css('width', '400px');".
								"$('#fname').val('".$rows['fio']."');".
								"$('#region').val('".$rows['region']."');".
								"$('#city').val('".$rows['city']."');".
								"$('#school').val('".$rows['school']."');".
								"$('#status').val('".$rows['status']."');".
								"$('#category').val('".$rows['category']."');".
								"$('#stage').val('".$rows['stage']."');".
								"$('#birthday').val('".$rows['birthday']."');".
								"$('#phone').val('".$rows['phone']."');".								
								( ($rows['email'] == '') ? "$('#noemail').attr('checked', 'checked'); $('#email').attr('disabled', 'disabled');" : ("$('#email').val('".$rows['email']."');") ).
								( ($rows['hotel'] == 0) ? "" : "$('#hotel').attr('checked', 'checked');").
								"$('#send').val('Сохранить изменения').removeAttr('disabled');".
								"$('#form').attr('action','/cr/adm.php?act=editstudent');";
						
						$Ghtml = str_replace('<#COURSE#>', '', $Ghtml);
						//$Ghtml = str_replace('~#~', $rows['id'] $Ghtml);
						$Ghtml = str_replace('//CODE', $code, $Ghtml);
						echo $Ghtml;
					}
					else
						echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />Ошибка получения информации из базы данных.';
				}
				else header('refresh:3;url=/cr/adm.php?act=shownew');
			}
			break;
//=======================================================
		case 'editstudent':
			$O = new AdmCourse($_REQUEST['user'], $_REQUEST['pass']);
			if ($O->auth($coockieAuth)){
				$correct = true;
				if (!isset($_POST['fname']) || strlen($_POST['fname']) > 255) $correct = false;
				if (!isset($_POST['region']) || strlen($_POST['region']) > 255) $correct = false;
				if (!isset($_POST['city']) || strlen($_POST['city']) > 255) $correct = false;
				if (!isset($_POST['school']) || strlen($_POST['school']) > 255) $correct = false;
				if (!isset($_POST['status']) || strlen($_POST['status']) > 255) $correct = false;
				if (!isset($_POST['category'])) $correct = false;
				if (!isset($_POST['stage']) || $_POST['stage'] > 100) $correct = false;
				if (!isset($_POST['birthday']) || $_POST['birthday'] < 1917) $correct = false;
				if (!isset($_POST['phone']) || strlen($_POST['phone']) > 255) $correct = false;
				//if (!isset($_POST['email']) || strlen($_POST['email']) > 255) $correct = false;
				
				if (!isset($_POST['hotel'])) $_POST['hotel'] = false;
				else $_POST['hotel'] = true;
				
				if (!$correct){
					$Ghtml = '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />Введены некорректные параметры. Вернитесь на предыдущую страницу и попробуйте еще раз!';
					echo $Ghtml;
					break;
				}else{
					$query = "UPDATE ?_student SET
									fio = ?, region = ?, city = ?, school = ?, status = ?, 
									category = ?d, stage = ?d, birthday = ?d, phone = ?, ". 
									($_POST['email'] == '' ? 'email = NULL, ' : 'email = ?, ')." hotel = ?d
								WHERE ?_student.id = ".$_REQUEST['student'];
					debug($_POST['student']);
					$rows = SQL::getInstance()->link->query($query, $_POST['fname'],
							$_POST['region'], $_POST['city'], $_POST['school'], $_POST['status'],
							$_POST['category'], $_POST['stage'], $_POST['birthday'],
							$_POST['phone'], mysql_escape_string($_POST['email']), $_POST['hotel']);
					debug($_POST['student']);
					if (!$rows) {
						$Ghtml = '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />Ошибка записи в базу данных!';
						echo $Ghtml;
						break;
					} 
					debug($_REQUEST['ref']); 
					header('Location: '.$_REQUEST['ref']);
				}
			}
			break;
//=======================================================		
		case 'showctree':
			$O = new AdmCourse($_REQUEST['user'], $_REQUEST['pass']);
			if ($O->auth($coockieAuth)){
				$query = "SELECT id AS ARRAY_KEY, parent AS PARENT_KEY, name FROM ?_ccategory";
				$rows = SQL::getInstance()->link->query($query);
				debug($rows[$_REQUEST['cat']]);
				if (!$rows){
					echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />Нет данных!';
					break;
				}
			}
			$Ghtml = file_get_contents(dirname(__FILE__).'/tree.html');
			$Ghtml = str_replace('<#NAV#>', $menu, $Ghtml);
			echo str_replace('<#LIST#>', tree($rows), $Ghtml);
			break;
//=======================================================
		case 'logout':
			setcookie('authorized', NULL, time()-360000, '/cr', SITE);
			echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />Вы вышли. Секундочку...';
			header('refresh:3;url=/cr/adm.php');
			break;
//=======================================================	
		case 'viewclist':
			$O = new AdmCourse($_REQUEST['user'], $_REQUEST['pass']);
			if ($O->auth($coockieAuth)){
				$query = "SELECT name FROM ?_ccategory WHERE id= ?d";
				$rows = SQL::getInstance()->link->selectCell($query, $_REQUEST['cat']);
				$html = '<h4 align="center">Курсы раздела "'.$rows.'":</h4>'.
						'<p class="sort">Сортировать курсы по<br />[ <a href="/cr/adm.php?act=viewclist&cat='.$_REQUEST['cat'].'&csort=time">дате</a> '.
						'| <a href="/cr/adm.php?act=viewclist&cat='.$_REQUEST['cat'].'&csort=name">названию</a> '.
						'| <a href="/cr/adm.php?act=viewclist&cat='.$_REQUEST['cat'].'&csort=predmet">предмету</a> '.
						'| <a href="/cr/adm.php?act=viewclist&cat='.$_REQUEST['cat'].'&csort=hours">часам</a> '.
						'| <a href="/cr/adm.php?act=viewclist&cat='.$_REQUEST['cat'].'&csort=curator">руководителю</a> '.
						'| <a href="/cr/adm.php?act=viewclist&cat='.$_REQUEST['cat'].'&csort=reg">доступности для регистрации</a> ]</p>';
				$query = "SELECT 
							id AS ARRAY_KEY, 
							`name`,   predmet,
						    DATE_FORMAT(?_course.`start`, '%d.%m.%Y') AS start, 
							DATE_FORMAT(?_course.`stop`, '%d.%m.%Y') AS stop,
							owner,
						    regclosed,
						    hours,
						    curator,
						    note,
							archived 
						FROM 
							?_course 
						WHERE 
							parent = ?d AND
							archived = ?d";
				switch ($_REQUEST['csort']){
					case 'name': $query .= "\nORDER BY name";break;
					case 'predmet': $query .= "\nORDER BY predmet, ?_course.start";break;
					case 'curator': $query .= "\nORDER BY curator, ?_course.start";break;
					case 'hours': $query .= "\nORDER BY hours, ?_course.start";break;
					case 'reg': $query .= "\nORDER BY regclosed, ?_course.start";break;
					case 'time': $query .= "\nORDER BY ?_course.start";break;
					default: $query .= "\nORDER BY ?_course.start";break;
				}
				$rows = SQL::getInstance()->link->query($query, $_REQUEST['cat'], (isset($_REQUEST['arch']) ? 1 : 0));
				//debug($rows);
				if ($rows){
					foreach ($rows as $k=>$v){
						$html .= '<hr /><div class="cnav">'.
								($O->getid() == $v['owner'] || $O->getaccess() == 255 ? '<a href="/cr/adm.php?act=editcourse&cat='.$k.'"><img class="imgnav edit" title="Править курс" src="img/1x1.gif"></a>'.
								'<a href="/cr/adm.php?act=delcourse&cat='.$k.'" onClick="if(window.confirm(\'Внимание!\nПри удалении курса будут удалены все записи о заявках на этот курс!\nОтмена данного действия будет невозможна!\nВы уверены, что хотите удалить курс?\')==true) {return true;} else {return false;}"><img class="imgnav del" style="margin-left:5px;" title="Удалить курс" src="img/1x1.gif"></a>'.
								'<a href="/cr/adm.php?act='.($v['archived'] == 0 ? 'inarch' : 'fromarch').'&cat='.$k.'"><img class="imgnav arch" title="Переместить курс в архив" src="img/1x1.gif"></a>' : '').
								($O->getid() == $v['owner'] || $O->getaccess() == 255 ? '<a href="/cr/adm.php?act='.($v['regclosed'] ? 'opencourse' : 'closecourse').'&cat='.$k.'"><img class="imgnav reg" style="margin-left:5px;" title="Закрыть\открыть курс для записи" src="img/1x1.gif"></a>'.
								
								'</div><p class="course">'.($v['regclosed'] ? '<span title="Запись на курс отключена!" style="color: red; font-size: small;">[off]</span>' : '<span title="Запись на курс включена" style="color: green; font-size: small;">[on]</span>').' ['.$v['predmet'].'] "'.$v['name'].'" ('.$v['hours'].' ч.). Проходит с '.$v['start'].' по '.$v['stop'].'. Руководитель &ndash; '.$v['curator'].'.<br /><em>'.stripcslashes($v['note']).'</em>'.
								'<div class="cnav2">'.
								
								'<a href="/cr/adm.php?act=shownew&cat='.$k.'"><img class="imgnav slist" title="Просмотр новых заявок на курс" src="img/1x1.gif"></a>'.
								'<a href="/cr/adm.php?act=showaccept&cat='.$k.'"><img style="margin-left:5px;" class="imgnav list" title="Просмотр одобренных заявок на курс" src="img/1x1.gif"></a>'.
								'<a href="/cr/adm.php?act=showdenid&cat='.$k.'"><img class="imgnav dlist" title="Просмотр отклоненных заявок на курс" src="img/1x1.gif"></a><a href="/cr/adm.php?act=showgroup&cat='.$k.'"><img style="margin-left:5px;" class="imgnav group" title="Управление группами" src="img/1x1.gif"></a>' : '').
								//'<a href="/cr/adm.php?act=showgroup&cat='.$k.'"><img style="margin-left:5px;" class="imgnav group" title="Управление группами" src="img/1x1.gif"></a>'.
								'</div></p>';
					}
				}
				else {
					$html .= 'Курсы в данном разделе отсутствуют.';
				}
			}
			else 
				$html = 'Ошибка доступа! Вы не имеете прав для доступа к данной информации.';
			$Ghtml = file_get_contents(dirname(__FILE__).'/courses.html');
			$Ghtml = str_replace('<#NAV#>', $menu, $Ghtml);
			$Ghtml = str_replace('<#CAT#>', $_REQUEST['cat'], $Ghtml);
			echo str_replace('<#LIST#>', $html, $Ghtml);
			break;
//=======================================================
		case 'editcat':
			$O = new AdmCourse($_REQUEST['user'], $_REQUEST['pass']);
			if ($O->auth($coockieAuth) && $O->getaccess() >= 255){
				$Ghtml = file_get_contents(dirname(__FILE__).'/renamecat.html');
				$Ghtml = str_replace('<#NAV#>', $menu, $Ghtml);
				$query = "SELECT id AS ARRAY_KEY, parent AS PARENT_KEY, name FROM ?_ccategory";
				$rows = SQL::getInstance()->link->query($query);
				debug($rows);
				if (!$rows){
					echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />Нет данных!';
					break;
				}
				//$Ghtml = str_replace('<#NAME#>', $menu, $Ghtml);
				$Ghtml = str_replace('<#LIST#>', seltree($rows), $Ghtml);
				$Ghtml = str_replace('<#CAT#>', $_REQUEST['cat'], $Ghtml);
				$query = "SELECT name  FROM ?_ccategory WHERE id = ?d";
				$rows = SQL::getInstance()->link->selectCell($query, $_REQUEST['cat']);
				if (!$rows){
					echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />Нет данных!';
					break;
				}
				echo str_replace('<#NAME#>', $rows, $Ghtml);
			}
			break;			
//=======================================================
		case 'changecat':
			//break;
			$O = new AdmCourse($_REQUEST['user'], $_REQUEST['pass']);
			if ($O->auth($coockieAuth) && $O->getaccess() >= 255){
				$query = "UPDATE ?_ccategory SET `name` = ? { , parent = ?d } WHERE id = ?d";
				//debug($query);
				$rows = SQL::getInstance()->link->query($query, $_REQUEST['cname'], (isset($_REQUEST['removecat']) ? $_REQUEST['selcat'] : DBSIMPLE_SKIP), $_REQUEST['cat']);
				if (!$rows){
					echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />Ошибка записи в базу данных!';
					break;
				}
				header('Location: /cr/adm.php?act=showctree');
			}
			break;			
//=======================================================
		case 'addsubcat':
			//break;
			$O = new AdmCourse($_REQUEST['user'], $_REQUEST['pass']);
			if ($O->auth($coockieAuth) && $O->getaccess() >= 255){
				$Ghtml = file_get_contents(dirname(__FILE__).'/addcat.html');
				$Ghtml = str_replace('<#NAV#>', $menu, $Ghtml);
				$Ghtml = str_replace('<#CAT#>', $_REQUEST['cat'], $Ghtml);
				echo str_replace('<#NAME#>', '', $Ghtml);
			}
			break;			
//=======================================================
		case 'createcat':
			//break;
			$O = new AdmCourse($_REQUEST['user'], $_REQUEST['pass']);
			if ($O->auth($coockieAuth) && $O->getaccess() >= 255){
				if (isset($_REQUEST['cat'])){
					$query = "INSERT INTO ?_ccategory (name, parent) VALUES(?, ?d)";
					$rows = SQL::getInstance()->link->query($query, $_REQUEST['cname'], $_REQUEST['cat']);
				}
				else{
					$query = "INSERT INTO ?_ccategory (name, parent) VALUES(?, NULL)";
					$rows = SQL::getInstance()->link->query($query, $_REQUEST['cname']);
				}
				//debug($query);
				if (!$rows){
					echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />Ошибка записи в базу данных!';
					break;
				}
				header('Location: /cr/adm.php?act=showctree');
			}
			break;			
//=======================================================
		case 'delcat':
			//break;
			$O = new AdmCourse($_REQUEST['user'], $_REQUEST['pass']);
			if ($O->auth($coockieAuth) && $O->getaccess() >= 255){
				$query = "DELETE FROM ?_ccategory WHERE id = ?d";
				//debug($query);
				$rows = SQL::getInstance()->link->query($query, $_REQUEST['cat']);
				if (!$rows){
					echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />Ошибка удаления записи из базы данных!';
					break;
				}
				header('Location: /cr/adm.php?act=showctree');
			}
			break;			
//=======================================================
		case 'addcourse':
			//break;
			$O = new AdmCourse($_REQUEST['user'], $_REQUEST['pass']);
			if ($O->auth($coockieAuth)){
				$Ghtml = file_get_contents(dirname(__FILE__).'/addcourse.html');
				$Ghtml = str_replace('<#NAV#>', $menu, $Ghtml);
				$Ghtml = str_replace('<#PARENT#>', $_REQUEST['cat'], $Ghtml);
				//$Ghtml = str_replace('<#CAT#>', $_REQUEST['cat'], $Ghtml);
				//echo str_replace('<#NAME#>', '', $Ghtml);
				echo $Ghtml;
			}
			break;			
//=======================================================
		case 'createcourse':
			//break;
			$O = new AdmCourse($_REQUEST['user'], $_REQUEST['pass']);
			if ($O->auth($coockieAuth)){
				$query = "INSERT INTO ?_course(name, predmet, parent, start, stop, owner, regclosed, hours, curator, note) VALUES(?, ?, ?d, ?, ?, ?d, ?d, ?d, ?, ?)";
				//debug($query);
				$rows = SQL::getInstance()->link->query($query, 
														$_REQUEST['name'],
														$_REQUEST['predmet'], 
														$_REQUEST['parent'], 
														date("Y-m-d", strtotime($_REQUEST['start'])), 
														date("Y-m-d", strtotime($_REQUEST['stop'])), 
														$O->getid(), 
														(isset($_REQUEST['regclosed']) ? $_REQUEST['regclosed'] : 0), 
														$_REQUEST['hours'], 
														$_REQUEST['curator'], 
														$_REQUEST['note']);
				if (!$rows){
					echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />Ошибка добавления записи в базу данных!';
					break;
				}
				header('Location: /cr/adm.php?act=showctree');
				//echo 'ok';
			}
			break;			
//=======================================================
		case 'editcourse':
			//break;
			$O = new AdmCourse($_REQUEST['user'], $_REQUEST['pass']);
			if ($O->auth($coockieAuth)){
				$query = "SELECT id, name, predmet, parent, DATE_FORMAT(?_course.`start`, '%d.%m.%Y') AS start,	DATE_FORMAT(?_course.`stop`, '%d.%m.%Y') AS stop, owner, regclosed, hours, curator, note  FROM ?_course WHERE id = ?d { AND owner = ?d } ";
				$rows = SQL::getInstance()->link->selectRow($query, $_REQUEST['cat'], ($O->getaccess()<255 ? $O->getid() : DBSIMPLE_SKIP));
				debug($rows);
				if (!$rows){
					echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />Ошибка получения записи из базы данных!';
					break;
				}
				
				$Ghtml = file_get_contents(dirname(__FILE__).'/addcourse.html');
				$Ghtml = str_replace('<#NAV#>', $menu, $Ghtml);
				$Ghtml = str_replace('<#PARENT#>', $rows['parent'], $Ghtml);
				$html = '$("input[name=\'name\']").val("'.$rows['name'].'");'."\n".
						'$("input[name=\'predmet\']").val("'.str_replace('"', '\'\'', $rows['predmet']).'");'."\n".
						'$("input[name=\'start\']").val("'.$rows['start'].'");'."\n".
						'$("input[name=\'stop\']").val("'.$rows['stop'].'");'."\n".
						'$("input[name=\'hours\']").val("'.$rows['hours'].'");'."\n".
						'$("input[name=\'curator\']").val("'.$rows['curator'].'");'."\n".
						'$("#send").val("Сохранить изменения");'."\n".
						'$("form").attr("action", "/cr/adm.php?act=changecourse");'."\n".
						'$("form").append("<input type=\"hidden\" value=\"'.$rows['id'].'\" name=\"cid\">");'."\n".
						'$("#note").val("'.str_replace("\r", " \\",$rows['note']).'");'."\n".
						($rows['regclosed'] == 1 ? '$("input[name=\'regclosed\']").attr("checked", "checked");' : '')."\n";
				$Ghtml = str_replace('//CODE', $html, $Ghtml);
				//echo str_replace('<#NAME#>', '', $Ghtml);
				echo $Ghtml;
			}
			break;			
//=======================================================
		case 'changecourse':
			//break;
			$O = new AdmCourse($_REQUEST['user'], $_REQUEST['pass']);
			if ($O->auth($coockieAuth)){
				$query = "UPDATE ?_course SET `name` = ?, predmet = ?, parent = ?, start = ?, stop = ?, regclosed = ?d, hours = ?d, curator = ?, note = ? WHERE id = ?d";
				//debug($query);
				$rows = SQL::getInstance()->link->query($query, 
														$_REQUEST['name'],
														$_REQUEST['predmet'], 
														$_REQUEST['parent'], 
														date("Y-m-d", strtotime($_REQUEST['start'])), 
														date("Y-m-d", strtotime($_REQUEST['stop'])), 
														(isset($_REQUEST['regclosed']) ? 1 : 0), 
														$_REQUEST['hours'], 
														$_REQUEST['curator'], 
														$_REQUEST['note'], 
														$_REQUEST['cid']);
				if (!$rows){
					echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />Ошибка добавления записи в базу данных!';
					break;
				}
				header('Location: /cr/adm.php?act=viewclist&cat='.$_REQUEST['parent']);
			}
			break;			
//=======================================================
		case 'delcourse':
			//break;
			$O = new AdmCourse($_REQUEST['user'], $_REQUEST['pass']);
			if ($O->auth($coockieAuth)){
				$query = "SELECT parent FROM ?_course WHERE id = ?d";
				$cat = SQL::getInstance()->link->selectCell($query, $_REQUEST['cat']);
				$query = "DELETE FROM ?_course WHERE id = ?d { AND owner = ?d }";
				//debug($query);
				$rows = SQL::getInstance()->link->query($query, $_REQUEST['cat'], ($O->getaccess()<255 ? $O->getid() : DBSIMPLE_SKIP));
				if (!$rows){
					echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />Ошибка удаления записи из базы данных!';
					break;
				}
				header('Location: /cr/adm.php?act=viewclist&cat='.$cat);
			}
			break;			
//=======================================================
		case 'opencourse':
			//break;
			$O = new AdmCourse($_REQUEST['user'], $_REQUEST['pass']);
			if ($O->auth($coockieAuth)){
				$query = "UPDATE ?_course SET regclosed = 0 WHERE id = ?d { AND owner = ?d }";
				//debug($query);
				$rows = SQL::getInstance()->link->query($query,	$_REQUEST['cat'], ($O->getaccess()<255 ? $O->getid() : DBSIMPLE_SKIP));
				if (!$rows){
					echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />Ошибка изменения записи в базе данных!';
					break;
				}
				$query = "SELECT parent FROM ?_course WHERE id = ?d";
				$rows = SQL::getInstance()->link->selectCell($query,	$_REQUEST['cat']);
				header('Location: /cr/adm.php?act=viewclist&cat='.$rows);
			}
			break;			
//=======================================================
		case 'closecourse':
			//break;
			$O = new AdmCourse($_REQUEST['user'], $_REQUEST['pass']);
			if ($O->auth($coockieAuth)){
				$query = "UPDATE ?_course SET regclosed = 1 WHERE id = ?d { AND owner = ?d }";
				//debug($query);
				$rows = SQL::getInstance()->link->query($query,	$_REQUEST['cat'], ($O->getaccess()<255 ? $O->getid() : DBSIMPLE_SKIP));
				if (!$rows){
					echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />Ошибка изменения записи в базе данных!';
					break;
				}
				$query = "SELECT parent FROM ?_course WHERE id = ?d";
				$rows = SQL::getInstance()->link->selectCell($query,	$_REQUEST['cat']);
				header('Location: /cr/adm.php?act=viewclist&cat='.$rows);
			}
			break;			
//=======================================================
		case 'inarch':
		case 'fromarch':
			//break;
			$O = new AdmCourse($_REQUEST['user'], $_REQUEST['pass']);
			if ($O->auth($coockieAuth)){
				$query = "UPDATE ?_course SET archived = ?d WHERE id = ?d { AND owner = ?d }";
				//debug($query);
				$rows = SQL::getInstance()->link->query($query, ($_REQUEST['act'] == 'inarch' ? 1 : 0),	$_REQUEST['cat'], ($O->getaccess()<255 ? $O->getid() : DBSIMPLE_SKIP));
				if (!$rows){
					echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />Ошибка изменения записи в базе данных!';
					break;
				}
				$query = "SELECT parent FROM ?_course WHERE id = ?d";
				$rows = SQL::getInstance()->link->selectCell($query,	$_REQUEST['cat']);
				header('Location: /cr/adm.php?act=viewclist'.($_REQUEST['act'] == 'fromarch' ? '&arch=1' : '').'&cat='.$rows);
			}
			break;			
//=======================================================
		case 'showchecker':
			//break;
			$O = new AdmCourse($_REQUEST['user'], $_REQUEST['pass']);
			if ($O->auth($coockieAuth) && $O->getaccess() >= 255){
				$html = '<table style="text-align: left; width: 100%;" border="1"  cellpadding="5" cellspacing="0">
						  <tbody>
						    <tr>
						      <th style="vertical-align: middle; text-align: left; width: 15%;">Логин</th>
						      <th style="vertical-align: middle; text-align: left; width: 15%;">e-mail</th>
						      <th style="vertical-align: middle; text-align: left; width: 5%;">Уровень доступа</th>
						      <th style="vertical-align: middle; text-align: left; width: 15%;">ФИО</th>
						      <th style="vertical-align: middle; text-align: left; width: 30%;">Примечание</th>
							  <th style="vertical-align: middle; text-align: left; width: 5%;">Курсов</th>
						      <th align="left" valign="middle">Управление</th>
						    </tr>';
				$ulist = '';
				$query = "SELECT `id` AS ARRAY_KEY, `name`, `email`, `access`, `realname`, `note`, (SELECT COUNT(*) FROM ?_course WHERE owner = ?_checker.id) AS cc FROM ?_checker ORDER BY `name`";
				$rows = SQL::getInstance()->link->query($query);
				if (!$rows){
					echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />Ошибка получения записи из базы данных!';
					break;
				}
				foreach ($rows as $k=>$v){
					$ulist .= '<a href="/cr/adm.php?act=delegatechecker&cat='.$k.'&owner=#">[ '.$v['name'].' ] '.$v['realname'].'</a><br />';
					$html .= '  <tr>
							      <td align="left" valign="middle">'.$v['name'].($v['access'] < 0 ? '<span style="color: #f00;">(заблокирован)</span>' : '').'</td>
							      <td align="left" valign="middle">'.$v['email'].'</td>
							      <td align="left" valign="middle">'.$v['access'].'</td>
							      <td align="left" valign="middle">'.$v['realname'].'</td>
							      <td align="left" valign="middle">'.$v['note'].'</td>
							      <td align="left" valign="middle">'.$v['cc'].'</td>
							      <td align="left" valign="middle">'.
							      '<a href="/cr/adm.php?act=editchecker&cat='.$k.'"><img style="margin-right: 5px;" class="imgnav edit" src="img/1x1.gif" title="Редактировать данные пользователя"></a>'.
							      '<a class="jq" id="'.$k.'" href="/cr/adm.php?act=checkerlist&cat='.$k.'" onclick="return false;"><img style="margin-right: 5px;" class="imgnav reg" src="img/1x1.gif" title="Передать все курсы другому пользователю"></a>'.
							      '<a href="/cr/adm.php?act=blockchecker&cat='.$k.'"><img style="margin-right: 5px;" class="imgnav dlist" src="img/1x1.gif" title="Заблокировать пользователя"></a>'.
							      '<a href="/cr/adm.php?act=delchecker&cat='.$k.'" onclick="if(window.confirm(\'Внимание!\nПользователь будет удален со всеми курсами, которые он добавил!\nОтмена данного действия будет невозможна!\nВы уверены, что хотите удалить пользователя?\')==true) {return true;} else {return false;}"><img style="margin-right: 5px;" class="imgnav del" src="img/1x1.gif" title="Удалить пользователя"></a>'.
							      '</td>
							    </tr>';
				}
				$html .= '  </tbody></table>';
				$Ghtml = file_get_contents(dirname(__FILE__).'/checker.html');
				$Ghtml = str_replace('<#NAV#>', $menu, $Ghtml);
				$Ghtml = str_replace('<#LIST#>', $html, $Ghtml);
				$Ghtml = str_replace('<#ULIST#>', $ulist, $Ghtml);
				echo $Ghtml;
			}else{
				echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />Вы не имеете доступа к данной информации! Обратитесь к администратору.';
				break;
			}
			break;
//===================================================================================			
		case 'addchecker':
			//break;
			$O = new AdmCourse($_REQUEST['user'], $_REQUEST['pass']);
			if ($O->auth($coockieAuth) && $O->getaccess() == 255){
				$Ghtml = file_get_contents(dirname(__FILE__).'/addchecker.html');
				$Ghtml = str_replace('<#NAV#>', $menu, $Ghtml);
				$Ghtml = str_replace('//CODE', "$('#setpassword').remove();\n$('#passtext').replaceWith('Пароль');\n$('#password').show();\n", $Ghtml);
				echo $Ghtml;
			}else{
				echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />Вы не имеете доступа к данной информации! Обратитесь к администратору.';
				break;
			}
			break;			
//=======================================================
		case 'createchecker':
			//break;
			$O = new AdmCourse($_REQUEST['user'], $_REQUEST['pass']);
			if ($O->auth($coockieAuth) && $O->getaccess() == 255){			
				$query = "INSERT INTO ?_checker(`name`, `pass`, `email`, `access`, `realname`, `note`) VALUES(?, ?, ?, ?d, ?, ?)";
				//debug($query);
				$rows = SQL::getInstance()->link->query($query, 
														$_REQUEST['fname'],
														md5($_REQUEST['fpassword']),
														$_REQUEST['femail'], 
														$_REQUEST['faccess'], 
														$_REQUEST['frealname'], 
														$_REQUEST['fnote']);
				if (!$rows){
					echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />Ошибка добавления записи в базу данных!';
					break;
				}
				header('Location: /cr/adm.php?act=showchecker');
				//echo 'ok';
			}else echo 'Nicht';
			break;			
//===================================================================================			
		case 'editchecker':
			//break;
			$O = new AdmCourse($_REQUEST['user'], $_REQUEST['pass']);
			if ($O->auth($coockieAuth) && $O->getaccess() == 255){
				$Ghtml = file_get_contents(dirname(__FILE__).'/addchecker.html');
				$Ghtml = str_replace('<#NAV#>', $menu, $Ghtml);

				$query = "SELECT * FROM ?_checker WHERE id = ?d";
				$rows = SQL::getInstance()->link->selectRow($query, $_REQUEST['cat']);
				if (!$rows){
					echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />Ошибка чтения записи из базы данных!';
					break;
				}
				$Ghtml = str_replace('//CODE', "$('#setpassword').click(function(){\nif ($(this).is(':checked')){\n$('#password').show();\n}\nelse{\n$('#password').hide();\n}\n});\n$(\"input[name='fname']\").val('".$rows['name']."');\n$(\"input[name='femail']\").val('".$rows['email']."');\n$(\"input[name='faccess']\").val('".$rows['access']."');\n$(\"input[name='frealname']\").val('".$rows['realname']."');\n$(\"textarea[name='fnote']\").val('".$rows['note']."');\n$(\"#form\").attr('action', '/cr/adm.php?act=changechecker&cat=".$_REQUEST['cat']."');\n", $Ghtml);				
				echo $Ghtml;
			}else{
				echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />Вы не имеете доступа к данной информации! Обратитесь к администратору.';
				break;
			}
			break;			
//=======================================================
		case 'changechecker':
			//break;
			$O = new AdmCourse($_REQUEST['user'], $_REQUEST['pass']);
			if ($O->auth($coockieAuth) && $O->getaccess() == 255){			
				if (isset($_REQUEST['setpassword'])){
					$query = "UPDATE ?_checker SET `name` = ?, `pass` = ?, `email` = ?, `access` = ?d, `realname` = ?, `note` = ? WHERE id = ?d";
					$rows = SQL::getInstance()->link->query($query,
							$_REQUEST['fname'],
							md5($_REQUEST['fpassword']),
							$_REQUEST['femail'],
							$_REQUEST['faccess'],
							$_REQUEST['frealname'],
							$_REQUEST['fnote'],
							$_REQUEST['cat']);
				}
				else{
					$query = "UPDATE ?_checker SET `name` = ?, `email` = ?, `access` = ?d, `realname` = ?, `note` = ? WHERE id = ?d";
					$rows = SQL::getInstance()->link->query($query,
							$_REQUEST['fname'],
							$_REQUEST['femail'],
							$_REQUEST['faccess'],
							$_REQUEST['frealname'],
							$_REQUEST['fnote'],
							$_REQUEST['cat']);
				}
				if (!$rows){
					echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />Ошибка добавления записи в базу данных!';
					break;
				}
				header('Location: /cr/adm.php?act=showchecker');
				//echo 'ok';
			}else{
				echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />Вы не имеете доступа к данной информации! Обратитесь к администратору.';
				break;
			}	
			break;		
//=======================================================
		case 'blockchecker':
			//break;
			$O = new AdmCourse($_REQUEST['user'], $_REQUEST['pass']);
			if ($O->auth($coockieAuth) && $O->getaccess() == 255){			
				$query = "UPDATE ?_checker SET `access` = -1 WHERE id = ?d";
				$rows = SQL::getInstance()->link->query($query,	$_REQUEST['cat']);
				if (!$rows){
					echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />Ошибка добавления записи в базу данных!';
					break;
				}
				header('Location: /cr/adm.php?act=showchecker');
				//echo 'ok';
			}else{
				echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />Вы не имеете доступа к данной информации! Обратитесь к администратору.';
				break;
			}	
			break;		
			//=======================================================
		case 'delchecker':
			//break;
			$O = new AdmCourse($_REQUEST['user'], $_REQUEST['pass']);
			if ($O->auth($coockieAuth) && $O->getaccess() == 255){			
				$query = "DELETE FROM ?_checker WHERE id = ?d";
				$rows = SQL::getInstance()->link->query($query,	$_REQUEST['cat']);
				if (!$rows){
					echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />Ошибка удаления записи из базы данных!';
					break;
				}
				header('Location: /cr/adm.php?act=showchecker');
				//echo 'ok';
			}else{
				echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />Вы не имеете доступа к данной информации! Обратитесь к администратору.';
				break;
			}			

//===================================================================================			
		case 'checkerlist':
			//break;
			if (!(isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
					$_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'))
			break;
			$O = new AdmCourse($_REQUEST['user'], $_REQUEST['pass']);
			if ($O->auth($coockieAuth) && $O->getaccess() == 255){
				$query = "SELECT `id` AS ARRAY_KEY, `name`, `email`, `access`, `realname`, `note` FROM ?_checker WHERE id <> ?d ORDER BY `name`";
				$rows = SQL::getInstance()->link->query($query, $_REQUEST['cat']);
				if (!$rows){
					$html = '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />Ошибка чтения записи из базы данных!';
					echo $html;
					break;
				}
				foreach ($rows as $k=>$v){
					$html .= '<a href="/cr/adm.php?act=delegatechecker&cat='.$_REQUEST['cat'].'&owner='.$k.'">[ '.$v['name'].' ] '.$v['realname'].'<br />';
				} 				
				echo $html;
			}else{
				echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />Вы не имеете доступа к данной информации! Обратитесь к администратору.';
				break;
			}
			break;			
			//=======================================================
		case 'delegatechecker':
			//break;
			$O = new AdmCourse($_REQUEST['user'], $_REQUEST['pass']);
			if ($O->auth($coockieAuth) && $O->getaccess() == 255){			
				$query = "UPDATE ?_course SET owner = ?d WHERE owner = ?d";
				//debug($query);
				$rows = SQL::getInstance()->link->query($query, $_REQUEST['owner'],	$_REQUEST['cat']);
				if (!$rows){
					echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />Ошибка добавления записи в базу данных!';
					break;
				}
				header('Location: /cr/adm.php?act=showchecker');
				//echo 'ok';
			}else echo 'Nicht';
			break;			
			//=======================================================
		case 'showsprav':
			//break;
			$O = new AdmCourse($_REQUEST['user'], $_REQUEST['pass']);
			if ($O->auth($coockieAuth) && $O->getaccess() == 255){			
				echo str_replace('<#NAV#>', $menu, file_get_contents(dirname(__FILE__).'/sprav.html'));				
			}else echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />Вы не имеете прав доступа к данной информации!<br><a href="/cr/adm.php">Назад</a>';
			break;			
			//=======================================================
		case 'sprav':
			debug('Справочники');
			$O = new AdmCourse($_REQUEST['user'], $_REQUEST['pass']);
			if ($O->auth($coockieAuth) && $O->getaccess() == 255){
				switch ($_REQUEST['sprav']){
					case 'category':
						$query = "SELECT * FROM ?_scategory ORDER BY name";
						$rows = SQL::getInstance()->link->query($query, $_REQUEST['owner'],	$_REQUEST['cat']);
						if (!$rows){
							echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />Ошибка чтения из базы данных!';
							break;
						}
						$html = '<p>Список категорий</p>';
						foreach ($rows as $k=>$v)
							$html .= '<span class="edited" id="'.$v['id'].'">'.$v['name'].'</span><br>';
						$Ghtml = file_get_contents(dirname(__FILE__).'/editcategory.html');
						$Ghtml = str_replace('<#NAV#>', $menu, $Ghtml);
						$Ghtml = str_replace('<#LIST#>', $html, $Ghtml);
						echo $Ghtml;
						break;
				}
			}else echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />Вы не имеете прав доступа к данной информации!<br><a href="/cr/adm.php">Назад</a>';
			break;
		//=======================================================
			//=======================================================
		case 'editspravcat':
			debug($_REQUEST);
			$O = new AdmCourse($_REQUEST['user'], $_REQUEST['pass']);
			if ($O->auth($coockieAuth) && $O->getaccess() == 255){
				$query = "UPDATE ?_scategory SET `name` = ? WHERE id = ?d";
				$rows = SQL::getInstance()->link->query($query, $_REQUEST['value'],	$_REQUEST['id']);
				if (!$rows){
					echo '<span style="color: red;">Ошибка!</span>';
					break;
				}
				echo $_REQUEST['value'];
			}else echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />Вы не имеете прав доступа к данной информации!<br><a href="/cr/adm.php">Назад</a>';
			break;
		//===================================================================
		case 'creategroup':
			$O = new AdmCourse($_REQUEST['user'], $_REQUEST['pass']);
			if ($O->auth($coockieAuth)){
				$query = "INSERT INTO ?_group(`name`) VALUES(?)";
				$gid = SQL::getInstance()->link->query($query, $_REQUEST['value']);
				if ($gid){
					$query = "INSERT INTO ?_gmanage(course, `group`) VALUES(?d, ?d)";
					$gid = SQL::getInstance()->link->query($query, $_REQUEST['cid'], $gid);
					if ($gid){
						$query = "SELECT ?_group.id AS ARRAY_KEY, ?_group.name AS gname FROM ?_group, ?_gmanage WHERE ?_group.id = ?_gmanage.`group` AND ?_gmanage.course = ?d ORDER BY ?_group.name";
						$groups = SQL::getInstance()->link->selectCol($query, $_REQUEST['cid']);
						$glist = '';
						if ($groups){
							$glist = 'Создано групп: '.count($groups).'.';
							foreach ($groups as $k=>$v)
								$glist .= '<br><span class="groupname" id="'.$k.'">'.$v.'</span>';
							echo $glist;
							break;
						}
						echo "Ошибка!";
					}
					echo "Ошибка!";
				}
				echo "Ошибка!";
			}
			break;
		//===================================================================
		case 'getgroup':
			$O = new AdmCourse($_REQUEST['user'], $_REQUEST['pass']);
			if ($O->auth($coockieAuth)){
				$query = "SELECT ?_group.id AS ARRAY_KEY, ?_group.name AS gname FROM ?_group, ?_gmanage, ?_cmanage WHERE ?_group.id = ?_gmanage.`group` AND ?_gmanage.course = ?d AND ?_group.id != (SELECT ?_cmanage.`group` FROM ?_cmanage WHERE ?_cmanage.id = ?d) ORDER BY ?_group.name";
				$groups = SQL::getInstance()->link->selectCol($query, $_REQUEST['cid'], $_REQUEST['mid']);
				$glist = '';
				if ($groups){
					foreach ($groups as $k=>$v)
						$glist .= '<span class="groupname" mid="'.$_REQUEST['mid'].'" id="'.$k.'">'.$v.'</span><br>';
					}
				$glist .= '<span class="groupname" mid="'.$_REQUEST['mid'].'" id="0">Исключить из группы</span><br>';
				echo $glist;
				//echo "Ошибка!";
			}
			break;
		//===================================================================
		case 'addstogroup':
			$O = new AdmCourse($_REQUEST['user'], $_REQUEST['pass']);
			if ($O->auth($coockieAuth)){
				$query = "UPDATE ?_cmanage SET `group` = ?d WHERE id = ?d";
				$rows = SQL::getInstance()->link->query($query, $_REQUEST['gid'], $_REQUEST['mid']);
				if ($rows){
					$query = "SELECT ?_group.`name` AS `gname` FROM ?_group WHERE id = ?d";
					$rows = SQL::getInstance()->link->selectCell($query, $_REQUEST['gid']);
					echo $rows;
					$query = "SELECT COUNT(*) as scount, ?_cmanage.`group` FROM ?_cmanage WHERE ?_cmanage.course = (SELECT ?_cmanage.course FROM ?_cmanage WHERE ?_cmanage.id = ?d) GROUP BY ?_cmanage.`group`";
					$rows = SQL::getInstance()->link->query($query, $_REQUEST['mid']);
					$html = '<script  type="text/javascript">'.
								'$(document).ready(function(){';
					foreach ($rows as $v){
						$html .= '$("#c'.$v['group'].'").text("'.$v['scount'].'");';
					}
					$html .= '});'.
							'</script>';
					debug('=====');
					debug($rows);
					debug($html);
					echo $html;
					break;
				}
				echo "Ошибка!";
			}
			break;
			//===================================================================
		case 'showgroup':
			$O = new AdmCourse($_REQUEST['user'], $_REQUEST['pass']);
			if ($O->auth($coockieAuth)){
				$query = "SELECT ?_group.id AS ARRAY_KEY, ?_group.name FROM ?_group, ?_gmanage WHERE ?_group.id = ?_gmanage.group AND ?_gmanage.course = ?d";
				$rows = SQL::getInstance()->link->selectCol($query, $_REQUEST['cat']);
				if ($rows){
					foreach ($rows as $k=>$v){
						$query = "SELECT 
									?_student.`fio`, ?_student.`region`, ?_student.`city`, 
									?_student.`school`,	?_student.`status`, 
									?_scategory.`name` AS `category`, ?_student.`stage`,
    								?_student.`birthday`, ?_student.`phone`, ?_student.`email`, 
									?_student.`hotel`
								FROM
									?_student, ?_scategory, ?_cmanage
								WHERE
									?_student.id = ?_cmanage.student AND
									?_scategory.id = ?_student.category AND
								    ?_cmanage.`group` = ?d
								ORDER BY fio";
						$stud = SQL::getInstance()->link->query($query, $k);
						//debug($stud);
						if (!$stud){
							echo 'Ошибка получения списка группы!';
							break;
						}
						$Ghtml = file_get_contents(dirname(__FILE__).'/glist.html');
						debug($Ghtml);
						$html = '<p align="center">'.$v.'</p><table width="100%" cellspacing="0" cellpadding="3" border="1">
								 <tr>
									<th>№</th>
									<th>Ф.И.О.</th>
									<th>Регион</th>
									<th>Город/район</th>
									<th>ОУ</th>
									<th>Должность</th>
									<th>Категория</th>
									<th>Стаж</th>
									<th>Год рожд.</th>
									<th>Контакты</th>
									<th>Нуждается в общежитии</th>
								</tr>';
						foreach ($stud as $key=>$val){
							$html .= '<tr>
										<td>'.($key+1).'</td>
										<td>'.$val['fio'].'</td>
										<td>'.$val['region'].'</td>
										<td>'.$val['city'].'</td>
										<td>'.stripcslashes($val['school']).'</td>
										<td>'.$val['status'].'</td>
										<td>'.$val['category'].'</td>
										<td>'.$val['stage'].'</td>
										<td>'.$val['birthday'].'</td>
										<td>'.$val['phone'].'<br>'.$val['email'].'</td>
										<td>'.($val['hotel'] ? 'Да' : 'Нет').'</td>
									</tr>';
						}
						$html .= '</table><p><hr></p>';						
						$Ghtml = str_replace('<#LIST#>', $html, $Ghtml);
						echo $Ghtml;  
					}
					break;
				}
				echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">Ошибка!<br>Скорее всего для слушатели не распределены по группам! Откройте просмотр одобренных заявок на курс, создайте группы и добавьте слушателей в них.';
			}
			break;
			//===================================================================
			default: echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /><span>Нихьт ферштейн...</span>';						
	}//switch ======================================================================================

?>