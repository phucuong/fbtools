<?php

require_once 'Zend/Config/Ini.php';
require_once 'Zend/Auth.php';
require_once 'Zend/Session.php';
require_once 'Zend/Log.php';
require_once 'Zend/Log/Writer/Stream.php';

/**
 * Log_Factory class.
 *
 * @author Masaaki Sugiura
 */
class LogFactory {

	static private $initialized = false;

	static private $loggers = array();

	static private $logWriter = null;

	static private $logFilter = null;

	static private $config = null;

	static public function getLogger($className){

		if(! self::$initialized ){
			self::initialSetup();
		}
		
		if(! empty(self::$loggers[$className] )){
			return self::$loggers[$className];
		}
		
		return self::createLogger($className);
	}

	/**
	 * Create Logger instance.
	 * if logger instance created , return cached instance.
	 */
	static private function createLogger($className){
		self::$initialized = true;
		$logger = new Logger($className);
		$logger->addWriter(self::$logWriter);
		$logger->addFilter(self::$logFilter);
		self::$loggers[$className] = $logger;
		return $logger;
	}

	/**
	 * This method called first time only.
	 */
	static private function initialSetup(){
		$config = new Zend_Config_Ini( dirname(__FILE__) . "/../config.ini" );

		$logFilePath = dirname(__FILE__).'/../../'.$config->log->logFilePath;
		$logPriority = $config->log->logPriority;
		  
		$writer = new Zend_Log_Writer_Stream($logFilePath);
		self::$logWriter = $writer;
		$filter = new Zend_Log_Filter_Priority((int) $logPriority);
		self::$logFilter = $filter;
	}

}

/**
 * Logger class.
 *
 * @author Masaaki Sugiura
 */
class Logger {

	private $className = null;

	public function __construct($className){
		$this->className = "[ " . $className . " ] ";
		$this->zendLog = new Zend_Log();
	}
	
	public function addWriter($logWriter){
		$this->zendLog->addWriter($logWriter);
	}
	
	public function addFilter($logFilter){
		$this->zendLog->addFilter($logFilter);
	}

	public function debug($message){
		$this->zendLog->debug($this->createMessage($message));
	}

	public function info($message){
		$this->zendLog->info($this->createMessage($message));
	}

	public function err($message){
		$this->zendLog->err($this->createMessage($message));
	}

	private function createMessage($message){
        $prefix = "";
		if(! empty($this->className) ){
			$prefix = $prefix . $this->className;
		}

		if(Zend_Session::isStarted()){
			$auth = Zend_Auth::getInstance();
			$loginArray = $auth->getIdentity();
			$loginId = "[ " . $loginArray["user_id"] . " ] ";
			$prefix = $prefix . $loginId;
		}

		return $prefix . $message;
	}

}




