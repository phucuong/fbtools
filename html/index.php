<?php
    ini_set('display_errors', '1');
    class Bootstrap
    {
        private $_applicationFolder = "../app/";
        private $_config;
        public function __construct()
        {
            set_include_path(
                $this->_applicationFolder ."libs/ZendFramework-1.11.12/library" . PATH_SEPARATOR .
                $this->_applicationFolder ."services" . PATH_SEPARATOR .
                $this->_applicationFolder ."facebook" . PATH_SEPARATOR .
                get_include_path()
            );
            require_once "Zend/Loader/Autoloader.php";
            Zend_Loader_Autoloader::getInstance()->setFallbackAutoloader(true);

            $this->_config = new Zend_Config_Ini($this->_applicationFolder ."config.ini", "common");

            $this->_setRegistry();

            Zend_Session::setOptions();
            Zend_Session::start();
        }
        public function runApp ()
        {
            $path = $this->_getPathFromRegistry();
            require_once $path["smarty"] ."Smarty.class.php";
            require_once $path["views"] . "Zend_View_Smarty.class.php";
            require_once $path["common"] . "common_functions.php";
            require_once $path["facebook"] . "facebook.php";

            // ===== �u���E�U���猾���擾 ===== //
            $locale = substr($_SERVER["HTTP_ACCEPT_LANGUAGE"],0,2);
            
            $locale = empty($locale) ? "ja":strtolower($locale);    // �f�t�H���g�͓�{��ɂ���
	    	$locale = "ja";
            $view = new Zend_View_Smarty(
                $path["views"] . "templates/",
                array(
                    "compile_dir" => $path["views"] . "templates_c/"
                    ,"config_dir"  => $path["views"] . "configs"
                    ,"cache_dir"   => $path["views"] . "cache"
                    ,"left_delimiter"   => "{"
                    ,"right_delimiter"   => "}"
                )
            );

            $dev_type = "pc";

            $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper("ViewRenderer");
            $viewRenderer->setView($view)
                        ->setViewBasePathSpec($view->getEngine()->template_dir)
                        ->setViewScriptPathSpec(':controller/'.$dev_type .'_'.':action.:suffix')
                        ->setViewScriptPathNoControllerSpec($dev_type .'_'.':action.:suffix')
                        ->setViewSuffix("html");
            $frontController = $this->_setFrontController();
            $frontController->dispatch();
        }

        private function _setRegistry(){
            date_default_timezone_set($this->_config->date_default_timezone);
            Zend_Registry::set("config", $this->_config);
            /***** Set Directory Path *****/
            foreach($this->_config->path as $k=>$v){
                Zend_Registry::set($k, $this->_applicationFolder .$v);
            }
            /***** Set Web Path *****/
            foreach($this->_config->web_url as $k=>$v){
                Zend_Registry::set($k, $v);
            }
            /***** Set _Defines *****/
            foreach($this->_config->def as $k=>$v){
                Zend_Registry::set($k, $v);
            }
            Zend_Registry::set("request", $_REQUEST);
        }
        private function _getPathFromRegistry(){
            foreach($this->_config->path as $k=>$v){
                $path[str_replace("_path", "", $k)]    = Zend_Registry::get($k);
            }
            return $path;
        }
        private function _setFrontController(){
            $config_route    = new Zend_Config_Ini($this->_applicationFolder . "config.ini", "routing");
            $router            = new Zend_Controller_Router_Rewrite();
            $router->addConfig($config_route, "routes");

            $frontController = Zend_Controller_Front::getInstance();
            $frontController->throwExceptions(true);
            $frontController->setControllerDirectory($this->_applicationFolder . "controllers");
            $frontController->setParam("useDefaultControllerAlways", true);
            $frontController->setRouter($router);

            return $frontController;
        }
    }
    $bootstrap = new Bootstrap();
    try {
        $bootstrap->runApp();
    } catch (Exception $exp) {
        echo 'Exception Thrown From Admin Controller';
        echo "<br />";
        echo $exp->getMessage();
        echo "<br />";
        echo $exp->getTraceAsString();
        echo "<br />";
        echo $exp->getCode();
    }
