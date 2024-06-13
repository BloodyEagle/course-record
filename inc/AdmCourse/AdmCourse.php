<?php
	class AdmCourse extends Account {
		protected $html;

		function __construct($user = NULL, $pass = NULL){
//			$this->debug($_REQUEST);
			//$this->debug($_COOKIE);
			if (!is_null($user) && !is_null($pass))
				parent::__construct($user, $pass);
			
		}
//===========================================================================================
		function showform(){
			$this->html = file_get_contents(dirname(__FILE__).'/authform.html');
		} 
//===========================================================================================
		function  gethtml(){
			return $this->html;
		}
//===========================================================================================
		function auth($encodedpass = false){
			if (parent::auth($encodedpass)){
				$R = array();
				$R['user'] = $this->user;
				$R['pass'] = $this->password;				
				setcookie('authorized', serialize($R), time()+60*60, '/cr', SITE);
//				header('refresh:5;url=/cr/adm.php?act=shownew');
				$this->html .='Вы вошли, подождите, сейчас вы будете перемещены на другую страницу.';
				return true;
			}else{
				header('refresh:5;url=/cr/adm.php?act=showform');
				$this->html .='Такого пользователя не существует! Подождите, сейчас вы будете перемещены на другую страницу.';
				return false;
			}
		}
//===========================================================================================
	}
?>