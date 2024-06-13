<?php
/*
 * Created on 14.11.2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
	define('SHOW',1);
	define('SHOWMORE',2);
	define('REC',3);
	
//Функция автоподгрузки файлов с описаниями классов================================================================
	function __autoload($class)
	{
	    $path = explode('_', $class);
	    $file = './inc/';
	    foreach ($path as $v)
	    	$file .= $v.'/';
	    $file .= $v.'.php';
	    //echo '>'.$file.'<';
	    if (file_exists($file))
	    {
	        require_once($file);
	    }
	    else
	    {
	        throw new Exception("Class $class not found");
	    }
	}

//Консоль отладочных сообщений =======================================================================================
	//file_put_contents('/var/www/html/ajax', "A?===>>>>> ".substr($_SERVER['QUERY_STRING'], 0, 4)."\n", FILE_APPEND);
	if ($GLOBALS['odDebug'] && !(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest')) 
		new Debug_HackerConsole_Main(true);
	
	function debug($msg, $group="message", $color=null){
		if ($GLOBALS['odDebug'] && !(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest'))
			call_user_func(array('Debug_HackerConsole_Main', 'out'), $msg, $group, $color);
	}

//Функция для шифрования паролей ==================================================================================	
	function encrypt($txt){
		return base64_encode(pack('H*', sha1(utf8_encode($txt))));
	}

//Функция проверки значения для DBSimple
function setDBS($name){
	//debug($name);
	//debug($$name);
	if (!isset($name)) return DBSIMPLE_SKIP;
	if (empty($name)) return DBSIMPLE_SKIP;
	if (isset($GLOBALS['INPUT']->post['halfSearch']))
		return trim($name).'%';
	else
		return '%'.trim($name).'%';
}
//===========================================================================================================    	
/*function conv($str){
	return iconv('cp1251', 'UTF-8', $str);	
}*/

?>
