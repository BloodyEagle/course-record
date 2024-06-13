<?php
	
/*	define('SHOW',1);
	define('SHOWMORE',2);
	define('REC',3);
*/	
//======================================================================================
function tree($a, $level = 0, $prefix = '&nbsp;&nbsp;&nbsp;'){
	$size = count($a);
	for ($i = 0; $i <$level+1; $i++)
		$prefix .= '&nbsp;&nbsp;&nbsp;';
	foreach ($a as $k=>$v){
		$html .=($level == 0 ? '<p>' : $prefix).
		'<a href="/cr/index.php?action=show&cat='.$k.'">'.
		$v['name'].
		'</a>'.
		' &mdash; ('.$v['count'].')'.
		'<br />'.tree($v['childNodes'], $level+1, $prefix);
	}
	return $html;
}
//=======================================================================================
	
	class ShowCList extends Error {
		public function __construct($action = SHOW, $cat = NULL){
			parent::__construct();
			$Ghtml = $html = '';
			switch ($action){
				case SHOW:
						$Ghtml = file_get_contents(dirname(__FILE__).'/showcoursecategory.html');
						if (is_null($cat)){
							$query = 'SELECT DISTINCT
											?_ccategory.id AS ARRAY_KEY,
											?_ccategory.parent AS PARENT_KEY,
											?_ccategory.parent, 
											?_ccategory.name,
											(SELECT COUNT(*) FROM ?_course WHERE ?_course.parent = ?_ccategory.id AND ?_course.archived = 0) AS `count`
										FROM
											?_ccategory';
							$rows = SQL::getInstance()->link->query($query);
							$this->debug($rows);
							$html = '<h3 align="center">Выберите категорию курсов.</h3><div class="cat"><div class="cat1"><p><strong>Наименование категории &mdash; (Количество курсов).</strong></p>';
							$html .= tree($rows);
							$html .= '</div></div>';
							//======================================================================================
											
						}
						else{
							$query =   'SELECT 
											id AS ARRAY_KEY, name, predmet, 
											DATE_FORMAT(?_course.`start`, \'%d.%m.%Y\') AS start, 
											DATE_FORMAT(?_course.`stop`, \'%d.%m.%Y\') AS stop, 
											regclosed, hours, curator, note
										FROM
											?_course
										WHERE
											?_course.parent = ?d AND
											?_course.archived = 0
										ORDER BY 
											?_course.`start`';
							$Crows = SQL::getInstance()->link->query($query, $cat);
							$this->debug($Crows);
						
						
						//$this->debug($rows);
												
							if (!empty($Crows)){
								$html .= '<h3 align="center">Выберите курс.</h3>';
								$html .= '<p><table width="100%" border="1" cellpadding="5" cellspacing="0">'.chr(13);
								$html .= '<tr><th width="50%">Курс повышения квалификации</th><th width="5%">Часы</th><th width="15%">Дата</th><th>Руководитель</th></tr>'.chr(13);
								foreach ($Crows as $key => $val){
									$html .= '<tr><td><a href="/cr/index.php?action=showmore&cat='.$key.'">&laquo;'.stripslashes($val['name']).'&raquo;</a><br>['.stripslashes($val['predmet']).'] '.
									//'<br /><small>'.stripslashes($val['note']).'</small>'.
									'</td><td>'.$val['hours'].'</td><td>'.$val['start'].' &ndash; '.$val['stop'].'</td><td>'.stripslashes($val['curator']).'</td></tr>'.chr(13);
								}
								$html .= '</table></p>';
							}
						}
						$Ghtml = str_replace('<#COURSELIST#>', $html, $Ghtml);
						break;
				//==================================================================================						
				case SHOWMORE:
						if (isset($cat)){	
							$Ghtml = file_get_contents(dirname(__FILE__).'/showcourse.html');
							$html = '';
							
								$query = 'SELECT *
	  									  FROM
												?_course
											WHERE
												id = ?d';
								$rows = SQL::getInstance()->link->selectRow($query, $cat);
								$this->debug($rows);
							
							$html .= '<h1><b>Курс:</b> &laquo;'.stripslashes($rows['name']).'&raquo;</h1>'.
									 '<p><b>Категория слушателей:</b> '.stripslashes($rows['predmet']).'</p>'.
									 '<p><b>Дата проведения:</b> '.$rows['start'].' &ndash; '.$rows['stop'].'</p>'.
									 '<p><b>Количество учебных часов:</b> '.$rows['hours'].'</p>'.
									 '<p><b>Руководитель курсов:</b> '.stripslashes($rows['curator']).'</p>'.
									 '<p><b>Описание курса:</b><br>'.stripslashes($rows['note']).'</p>';
							if ($rows['regclosed'] == 0)
								$html .= '<input type="button" id="recToCourse" value="Подать заявку на курс" />';
							
							//$Ghtml = str_replace('<#TITLE#>', $rows['name'], $Ghtml);
							$Ghtml = str_replace('<#COURSE#>', $html, $Ghtml);
							$Ghtml = str_replace('~#~', $rows['id'], $Ghtml);
						}else 
							$Ghtml = '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />Не указан курс для отображения! Вернитесь назад и попробуйте еще раз.';
						break;
				//==================================================================================						
				case REC:
						$correct = true;
						if (!isset($_POST['fname']) || strlen($_POST['fname']) > 255) $correct = false;
						if (!isset($_POST['name']) || strlen($_POST['name']) > 255) $correct = false;
						if (!isset($_POST['sname']) || strlen($_POST['sname']) > 255) $correct = false;
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
						}else{
							foreach ($_POST as $k=>$v)
								$_POST[$k] = trim($v);
							$this->debug($_POST);
							$query = "INSERT INTO 
											?_student
											(fio, region, city, school, status, category, 
											stage, birthday, phone, email, hotel)
										VALUES 
											(?, ?, ?, ?, ?, ?d, ?d, ?d, ?, ?, ?)";
							$rows = SQL::getInstance()->link->query($query, $_POST['fname'].' '.$_POST['name'].' '.$_POST['sname'], 
																	$_POST['region'], $_POST['city'], $_POST['school'], $_POST['status'], 
																	$_POST['category'], $_POST['stage'], $_POST['birthday'], 
																	$_POST['phone'], mysql_escape_string($_POST['email']), $_POST['hotel']);
							if ($rows) {
								$query = "INSERT INTO 
											?_cmanage(course, student, regdate)
										  VALUES
											(?d, ?d, NOW())";
								$rows = SQL::getInstance()->link->query($query, $cat, $rows);
								if ($rows){
									$query = "SELECT ?_checker.email FROM ?_checker, ?_course WHERE ?_checker.id  = ?_course.owner AND ?_course.id = ?d";
									$rows = SQL::getInstance()->link->selectCell($query, $cat);
									$message = '<html>
												<head>
												  <title>Имеются новые заявки на курсы</title>
												</head>
												<body>
												  <p>На один из ваших курсов поступила новая заявка! Для проверки заявки, пожалуйста, пройдите по <a href="http://mrio.edurm.ru/cr/adm.php">этой ссылке.</a></p>
												</body>
												</html>';
									$headers  = 'MIME-Version: 1.0' . "\r\n";
									$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n".
												'From: Сайт МРИО.<no-reply@edurm.ru>' . "\r\n";
									debug('адрес: '.$rows);
									mail($rows, 'Новая заявка на курсы повышения квалификации', $message, $headers);
									$Ghtml = 'Ваша заявка принята. Вам сообщат о результате рассмотрения по электронной почте, указанной при регистрации.<p><a href="/cr/">Назад</a>';
									break;
								}
							}
							$Ghtml = '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />Ошибка записи в базу данных! Обратитесь к администратору!';
						}
						break;
				//==================================================================================						 
			}
			header('Content-Type: text/html; charset=UTF-8');
			echo $Ghtml;
		}
	}
	
?>