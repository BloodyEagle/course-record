<?php
/*****************************************************************************************************************
 * Created on 14.11.2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 ****************************************************************************************************************/
//require_once('./../../configuration.php');
	
	define('DB_HOST', 'localhost');
	define('DB_NAME', 'base');
	define('DB_USER', 'user');
	define('DB_PASS', 'password');
	define('TABLE_PREFIX', 'cr_');//Префикс таблиц БД
	
	define('SITE', 'site.test');
	
	$odDebug = true;//Включение отладочной информации в консоли
	$absPath = $_SERVER['DOCUMENT_ROOT'];
//=================================================================================================================	
//	if ($odDebug){
		ini_set('display_errors', 1);
		error_reporting(E_ALL||~E_STRICT);
//	}
//Первичные настройки путей подключения============================================================================
	if (!defined("PATH_SEPARATOR"))
		define("PATH_SEPARATOR", getenv("COMSPEC")? ";" : ":");
	ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.dirname(__FILE__));
	

?>