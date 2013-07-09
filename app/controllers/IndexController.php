<?php
	class IndexController extends Zend_Controller_Action
	{
		private $APP_ID = '';
		private $SECRET = '';
		
		public function init()
		{
			$this->view->app_id = $this->APP_ID = Zend_Registry::get('app_id');
			$this->SECRET = Zend_Registry::get('secret');
		}
		public function indexAction()
		{
			
		}
		
		public function fbloginAction(){
			$this->_helper->getHelper('viewRenderer')->setNoRender();
			
			$facebook = new Facebook(array(
  				'appId'  => $this->APP_ID,
  				'secret' => $this->SECRET,
			));
			$data['success'] = 0;
			$user_id = $facebook->getUser();
			if($user_id){
				try {
			        $user_profile = $facebook->api('/me?fields=picture,first_name,last_name,gender,username,email,link','GET');
			        $id = $user_profile['id'];
			        if(empty($id)){
			        	echo 'loi~ rui`';
			        	exit;
			        }
			        $_SESSION['user_info'] = $user_profile;
			        $data['success'] = 1;
			        $json = UtilitiService::createJson($data);
			        echo $json;
			        //$email = $user_profile['email'];
			        //$username = $user_profile['username'];
			        //$urlImage = 'http://graph.facebook.com/'.$username.'/picture?width=150&height=150';
			      } catch(FacebookApiException $e) {
					exit;
			      }   
			}
			else {
				$json = UtilitiService::createJson($data);
			    echo $json;
		    }
		}
	}
