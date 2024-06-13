<?php
/*
 * Created on 17.11.2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 	class SQL extends Error {
		private static $instance;
		
		protected 	$host;
		protected 	$base;
		protected 	$user;
		protected 	$password;
		protected 	$typeSQL;
		protected 	$debug = true;

        public	 	$link;//Обьект DBSimple для работы с БД

		private 	$connect_string;
//DB_HOST, DB_NAME, DB_USER, DB_PASS
		public function __construct($host = DB_HOST, $base = DB_NAME, $user = DB_USER, $pass = DB_PASS, $type = 'Mysql'){
			parent::__construct();
			$this->typeSQL = $type;
			$this->base = $base;
			$this->host = $host;
			$this->user = $user;
			$this->password = $pass;
			
			//Строка подключения типа: "mysql://Логин:Пароль@Хост/База" 
			$this->connect_string = $this->typeSQL.'://'.$this->user.':'.$this->password.'@'.$this->host.'/'.$this->base;
			$this->link = DbSimple_Generic::connect($this->connect_string);
			$this->link->setErrorHandler('SQL::databaseErrorHandler');
			$this->link->setLogger('SQL::SQLConsoleLogger');
			$this->link->setIdentPrefix(TABLE_PREFIX);//Задаем префикс БД
			//Настройка кодировки данных в БД и представления дат
			$this->link->query("SET NAMES UTF8");
	//		$this->link->query("SET DATESTYLE TO 'European, German'");

		}

		public static function getInstance() {
		   if (self::$instance === null) {
		      self::$instance = new self;
		   }
		    return self::$instance;
		  }
		  
		// Код обработчика ошибок SQL. ===================================================================================
		static function databaseErrorHandler($message, $info)
		{
		    // Выводим подробную информацию об ошибке.
		    debug("SQL Error: $message", 'SQL', '#FF0000'); 
		    debug($info, 'SQL', '#FF0000');
		    echo "SQL Error: <pre>$message</pre>";
		    //SQL::debug("SQL Error: $message");
		    exit();
		}
		
		//Функция логирования DbSimple в консоль ======================================================================== 
		static function SQLConsoleLogger($db, $sql) {
    		if ($GLOBALS['odDebug'])
	   			//Debug_HackerConsole_Main::out($sql, 'SQL query\'s log');
	   			debug($sql, 'SQL query\'s log');
		}  

	}
?>
