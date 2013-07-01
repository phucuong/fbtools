<?php
	class IndexController extends Zend_Controller_Action
	{
		private $_nameSpace;
		protected $_login_cookie_id = "AL";
		private $MAX_UP_FILE = 25000;
		private $APP_ID = '';
		private $SECRET = '';
		
		public function init()
		{
			$app = new CommonFnc_GetAppInfo;
			$this->view->app = $app->get();
			$this->view->app_id = $this->APP_ID = Zend_Registry::get('app_id');
			$this->SECRET = Zend_Registry::get('secret');
		}
		public function indexAction()
		{
			$zs = new CommonFnc_Z_Session_Class();
            $user_info = $zs->setSessinData2Array();
			if ( !empty($user_info["user_id"]) ){
				header("Location: /home");
                exit();
            }
			/*if (isset($_COOKIE[$this->_login_cookie_id])){
				$cua = new CustUsersAutologin;
				$user_array = $cua->search($_COOKIE[$this->_login_cookie_id]);
				if (empty($user_array["user_mail"]) || empty($user_array["password"])){
					$this->delete_cookie($_COOKIE[$this->_login_cookie_id]);
					return;
				}
				$cust_user = new CustUsers();
				$ret = $cust_user->validateUser( $user_array["user_mail"], $user_array["password"] );
				if ( empty($ret["user_id"]) ){
					$this->delete_cookie($_COOKIE[$this->_login_cookie_id]);
					header("location:/");
					exit();
				}
				$zs = new CommonFnc_Z_Session_Class();
				$zs->setSession($ret);
				$url = new GenURL();
				header("location:".$url->setHomeURL($ret));
				exit();
			}*/
		}
		
		public function ajaxuploadAction(){
			$this->_helper->viewRenderer->setNoRender();
			$status = 0;
			$img_url = "";
			$error = "";

			if (!empty($_FILES["file_input"]["tmp_name"])){
				$voi = new validateUserInfo();
                $img_ret = $voi->checkUserImage($_FILES["file_input"]);
                if (!empty($img_ret)){
                    $error = $img_ret;
                } else {
                	$img_url = "http://".$_SERVER["SERVER_NAME"].$voi->copyImage();
					$status = 1;
                }
            }
		
			$val = array("status" => $status,"image_url"=>$img_url, 'error'=>$error);
			$json = UtilitiService::createJson($val);
			header('Content-type: text/html; charset=utf-8');
			echo $json;
		}
		
		public function ajaxregistAction(){
			$this->_helper->viewRenderer->setNoRender();
			$voi = new validateUserInfo();
			$user_info_array = $voi->setPostToArray($this->_request);
            $check_flag= $voi->validateRegistUser($user_info_array);
			$status = 1;
            $val = array();
            if (!$check_flag){
            	$status = 0;
            	$val['error'] = $user_info_array;
            }
            else{
            	$custUsersPre = new CustUsersPre();
            	$registCode = md5(rand().date('Ymdhis'));
            	$image_path = $user_info_array['user_account_avatar']['value'];
            	$index = strpos($image_path,'/users_photo');
            	$image_path = substr($image_path,$index);
            	$insert_data = array(
	        		'user_mail'			=> $user_info_array['user_account_mail']['value'],
	        		'user_name_sei'		=> $user_info_array['user_account_first_name']['value'],
	        		'user_name_mei'		=> $user_info_array['user_account_last_name']['value'],
	        		'user_password'		=> $user_info_array['user_account_password']['value'],
	        		'user_avatar'		=> $image_path,
            		'regist_code'		=> $registCode
	        	);
            	$success = $custUsersPre->insertUser($insert_data);
            	if(empty($success)){
            		$val['error'] = '登録に失敗しました。';
            	}
            	else{
            		$val['error'] = '登録が完了しました。';
            	}
            }
            
            $val['status'] = $status;
            $val['url'] = "/";
            $json = UtilitiService::createJson($val);
            echo $json;
            
            if(!empty($success)){
            	$genUrl = new GenURL();
            	$templete_name  = "pc_regist_mail";
            	$assign_array   = array("user_name"	=> $user_info_array['user_account_first_name']['value']." ".$user_info_array['user_account_last_name']['value']
                                   		,"url"		=> $genUrl->setTopURL().'confirm/'.$registCode
                                   );
            	$to_name        = "";
            	$to_mailaddress = $user_info_array['user_account_mail']['value'];
            	commonFnc_send_mail($templete_name, $assign_array, $to_name, $to_mailaddress);
            }
            exit;
		}
		
		public function registconfirmAction(){
			$this->_helper->viewRenderer->setNoRender();
			$registCode = $this->_request->getParam('code',"");
			if(empty($registCode)){
				header("location:/");
			}
			
			$custUserPre = new CustUsersPre();
			$user = $custUserPre->getUserByRegistCode($registCode);
			if(empty($user)){
				header("location:/");
			}

			$insert_data = array(
	        		'user_mail'		=>$user['user_mail'],
	        		'user_name_sei'	=>$user['user_name_sei'],
	        		'user_name_mei'	=>$user['user_name_mei'],
	        		'user_password'	=>$user['user_password'],
	        		'user_avatar'	=>$user['user_avatar']
	        	);
	        $custUser = new CustUsers();
	        $id = $custUser->insertUser($insert_data);
	        if(!empty($id)){
	        	$insert_data = array();
	        	$insert_data['user_id'] = $id;
	        	$insert_data['user_tel1'] = "";
	        	$insert_data['user_tel2'] = "";
	        	$insert_data['user_tel3'] = "";
	        	$insert_data['user_school'] = "";
	        	$insert_data['user_address'] = "";
	        	$insert_data['user_introduction'] = "";
	        	$custUserProfile = new CustUsersProfile();
	        	$custUserProfile->insertUser($insert_data);
	        	
	        	$insert_data = array();
	        	$insert_data['user_id'] = $id;
	        	$custUserSettingMail = new CustUsersSettingMail();
	        	$custUserSettingMail->insertUser($insert_data);
	        	
	        	$update_data = array('can_kb'=>1);
	        	$custUserPre->updateByUserId($user['user_id'], $update_data);
	        	
	        	$genUrl = new GenURL();
            	$templete_name  = "pc_agree_user";
            	$assign_array   = array("user_name"		=> $user['user_name_sei']." ".$user['user_name_mei']
                                   		,"user_mail"	=> $user['user_mail']
                                   );
            	$to_name        = "";
            	$to_mailaddress = $user['user_mail'];
            	commonFnc_send_mail($templete_name, $assign_array, $to_name, $to_mailaddress);
	        }
	        header("location:/");
		}
		
		public function ajaxloginAction()
		{
			$name_or_mail = $this->_request->getParam( "login_id", "" );
            $password = $this->_request->getParam( "login_password", "" );
			$status = 0;
			// var_dump($name_or_mail, $password);
            if ( empty($name_or_mail) ) {
                $this->exportJson(array("ok"=>0));
				exit(0);
            }
            if ( empty($password) ) {
                $this->exportJson(array("ok"=>3));
                exit();
            }
            $cust_user = new CustUsers();
            $ret = $cust_user->getUser( $name_or_mail, $password );
            if ( empty($ret["user_id"]) ){
                $this->exportJson(array("ok"=>2));
                exit();
            }
            
            $zs = new CommonFnc_Z_Session_Class();
            $zs->setSession($ret);
            
			$this->exportJson(array("ok"=>1));
		}
		
		private function exportJson($arr){
			$json = json::createJson($arr);
			header('Content-type: text/javascript; charset=utf-8');
			$this->_helper->viewRenderer->setNoRender();
			echo $json;
		}
		
		public function logoutAction(){
			$zs = new CommonFnc_Z_Session_Class();
			$zs->clearSession();
			if (isset($_COOKIE[$this->_login_cookie_id])){
				$this->delete_cookie($this->_login_cookie_id);
			}
			header("Location:/");
			exit();
		}
		
		public function forgotAction(){
		
		}
		
		public function resetAction(){
			$reset_code = $this->_request->getParam("reset_code", "");
			$model = new CustUsers();
			$arr = $model->getResetPasswordInfo($reset_code);
			if(!empty($arr["user_id"])){
				$this->view->reset_code = $reset_code;
			} else {
				header("Location:/");
				exit();
			}
		}
		private function delete_cookie($id){
			setcookie($id,"",time() - 3600,"/");
		}
		
		public function fbloginAction(){
			$this->_helper->getHelper('viewRenderer')->setNoRender();
			
			$facebook = new Facebook(array(
  				'appId'  => $this->APP_ID,
  				'secret' => $this->SECRET,
			));
			$user_id = $facebook->getUser();
			$data['success'] = 0;
			if($user_id){
				try {
			        $user_profile = $facebook->api('/me?fields=picture,first_name,last_name,gender,username,email,link','GET');
			        $email = $user_profile['email'];
			        $username = $user_profile['username'];
			        if(!empty($user_profile) && !empty($email) && !empty($username)){
			        	$custUser = new CustUsers();
			        	$userInfo = $custUser->getUserFromMail($email);
			        	if(empty($userInfo)){
			        		//$url = $user_profile['picture']['data']['url'];
			        		$url = 'http://graph.facebook.com/'.$username.'/picture?width=150&height=150';
							$str = mt_rand(0,999).date("Ymdhis");
							$file_path = dirname(__FILE__).Zend_Registry::get("userimg_path");
            				$filename =  md5($str).".jpg";
            				$copyFile = $file_path . $filename;
							$byte = file_put_contents($copyFile, @file_get_contents($url));
							if($byte > 0){
								$avatar = Zend_Registry::get("userimg_url").$filename;
							}
							else{
								$avatar = Zend_Registry::get('default_photo');
							}
			        		$insert_data = array(
				        		'user_mail'=>$email,
			        			'user_name_sei'=>$user_profile['first_name'],
			        			'user_name_mei'=>$user_profile['last_name'],
			        			'user_password'=>md5(rand()),
				        		'user_avatar'=>$avatar
				        	);
					        $custUser = new CustUsers();
					        $id = $custUser->insertUser($insert_data);
					        if(!empty($id)){
					        	$insert_data = array();
					        	$insert_data['user_id'] = $id;
					        	$insert_data['user_gender'] = $user_profile['gender'] === 'male' ? 1 : ($user_profile['gender'] === 'female' ? 2 : 0);
					        	$insert_data['user_tel1'] = "";
					        	$insert_data['user_tel2'] = "";
					        	$insert_data['user_tel3'] = "";
					        	$insert_data['user_school'] = "";
					        	$insert_data['user_address'] = "";
					        	$insert_data['user_introduction'] = "";
					        	$custUserProfile = new CustUsersProfile();
					        	$custUserProfile->insertUser($insert_data);
					        	
					        	$insert_data = array();
					        	$insert_data['user_id'] = $id;
					        	$custUserSettingMail = new CustUsersSettingMail();
					        	$custUserSettingMail->insertUser($insert_data);
					        	
					        	$ret = $custUser->getUserWithNoPass($email);
					            if ( !empty($ret["user_id"]) ){
					            	$zs = new CommonFnc_Z_Session_Class();
						            $zs->setSession($ret);
						            $data['success'] = 1;
					            }
				        	}
			        	}
			        	else{
			        		$custUser = new CustUsers();
			        		$ret = $custUser->getUserWithNoPass($email);
					        if ( !empty($ret["user_id"]) ){
					        	$zs = new CommonFnc_Z_Session_Class();
					            $zs->setSession($ret);
					            $data['success'] = 1;
				            }
			        	}
			        }
			      } catch(FacebookApiException $e) {
			        /*$login_url = $facebook->getLoginUrl(); 
			        $this->view->login_url = $login_url;
			        error_log($e->getType());
			        error_log($e->getMessage());*/
			      }   
			}
			else {
				$data = array('success'=>0);
		      // No user, print a link for the user to login
		      //$login_url = $facebook->getLoginUrl();
		      //$this->view->login_url = $login_url;
		    }
		    $json = UtilitiService::createJson($data);
			echo $json;
		}
	}
