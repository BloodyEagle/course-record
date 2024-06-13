<?php
/*
 * Created on 14.11.2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 	class Error {
 		protected $oname;//Имя класса объекта
 		
 		protected $errmsg;//Сообщение об ошибке
 		protected $error;//Флаг ошибки, если true - значит ошибка произошла 
 		
 		protected $msg;//Другие сообщения...
 		protected $msgAdded;//Флаг принятого сообщения
 		
 		function __construct(){
 			$this->oname = get_class($this);
 			debug('Initializing object...'.$this->oname, 'Init');
 			
 			$this->errmsg = '';
 			$this->error = false;
 			
 			$this->msg = '';
 			$this->msgAdded = false;
 		}
 		
 		function setError($msg){
 			$this->error = true;
 			$this->errmsg .= $msg."\n";
 			$this->debug($this->errmsg, '#FF0000'); 				
 		}
 		
 		function setMessage($msg){
 			$this->msgAdded = true;
 			$this->msg .= $msg."\n";
 			$this->debug($msg, '#0000FF'); 				
 		}
 		
 		function isError(){
 			return $this->error;
 		}
 		
 		function isMessage(){
 			return $this->msgAdded;
 		}
 		
 		function getError(){
 			return $this->errmsg;
 		}
 		
 		function getHtmlError(){
 			return nl2br($this->errmsg);
 		}
 		
 		function getMessage(){
 			return $this->msg;
 		}
 		
 		function getHtmlMessage(){
 			return nl2br($this->msg);
 		}
 		
 		protected function debug($msg, $color=NULL){
 			if (is_null($color))
 				debug($msg, $this->oname);
 			else
 				debug($msg, $this->oname, $color);
 		}
 	}
?>
