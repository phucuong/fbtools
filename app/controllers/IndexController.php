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
