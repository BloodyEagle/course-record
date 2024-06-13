<?php 
/*
 * Created on 14.11.2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
//	session_start();
 $SQL = false;
 require_once('inc/config.inc.php');
 require_once('inc/common.inc.php');
  
 	if (!isset($_GET['action']))
 		$_GET['action'] = 'show';
 	switch ($_GET['action']){
 		case 'show':
 				$act = SHOW;
 				break;
		case 'showmore':
				$act = SHOWMORE;
				break; 			
		case 'rec':
				$act = REC;
				break;					
 	}
 	if (!isset($_GET['cat']))
 		$cat = NULL;
 	else 
 		$cat = $_GET['cat'];
 	$O = new ShowCList($act, $cat);
//phpinfo();

?>
