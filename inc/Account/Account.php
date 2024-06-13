<?php
	class Account extends Error {
		protected $uid;
		protected $user;
		protected $password;
		protected $email;
		protected $access;
		protected $realname;
		protected $note;
//===========================================================================================		
		function  __construct($user, $password){
			parent::__construct();
			$this->user = $user;
			$this->password = $password;
			//$this->load();
		}
//===========================================================================================
		function create($user, $password, $email, $access, $realname, $note){
			$query = "INSERT INTO ?_checker (name, pass, email, access, realname, note)
						VALUES(?, MD5(?), ?, ?d, ?, ?)";
			$result = SQL::getInstance()->link->query($query,$user, $password, $email, $access, $realname, $note);
			if (!$result)
			{
				$this->setError('Ошибка записи в базу данных!');
			}
		}
//===========================================================================================
		function auth($encodedpass = false){
			$query = "SELECT * FROM ?_checker WHERE name = ? AND pass = ?";
			if (!$encodedpass)
				$result = SQL::getInstance()->link->selectRow($query,$this->user, md5($this->password));
			else
				$result = SQL::getInstance()->link->selectRow($query,$this->user, $this->password);
			if ($result)
			{
				$this->debug($result);
				$this->uid = $result['id'];
				$this->user = $result['name'];
				$this->password = $result['pass'];
				$this->email = $result['email'];
				$this->access = $result['access'];
				$this->realname = $result['realname'];
				$this->note = $result['note'];
				$this->debug('Авторизация прошла успешно.');
				
				return true;
			}
			else{
				$this->setError('Такого пользователя не существует!');

				return false;
			}		
		}
//===========================================================================================
		function getid(){
			return $this->uid;
		}
//===========================================================================================
		function getaccess(){
			return $this->access;
		}
		
		 				
	}//Account
?>