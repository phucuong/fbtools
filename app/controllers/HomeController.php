<?php
class HomeController extends Zend_Controller_Action
{
	private $info = null;
	private $APP_ID = '';
	private $SECRET = '';
	private $logger = null;
	
	public function init(){
		$this->info = $this->view->info = $_SESSION['user_info'];
		$this->logger = LogFactory::getLogger('HomeController');
		//UtilitiService::printDebug($this->info);
		if(empty($this->info)){
			header('location:/');
		}
		$this->APP_ID = Zend_Registry::get('app_id');
		$this->SECRET = Zend_Registry::get('secret');
	}
	
	public function homeAction(){
		$facebook = new Facebook(array(
  				'appId'  => $this->APP_ID,
  				'secret' => $this->SECRET,
			));
		$id = $this->info['id'];
		//$access = $facebook->api("/oauth/access_token?client_id={$this->APP_ID}&redirect_uri=http://tools.com",'GET');
		//$access = $facebook->api("/$id/permissions",'GET');
		//UtilitiService::printDebug($access);
		$access_token = $facebook->getAccessToken();
		$groupInfo = $facebook->api("/$id/groups");
		$data = $groupInfo['data'];
		$_SESSION['groups'] = $data;
		//UtilitiService::printDebug($data);
		$this->view->data = $data;
		$strGroupId = "";
		foreach ($data as $value) {
			$strGroupId.=$value['id'].',';
		}
		$strGroupId = substr($strGroupId, 0,strlen($strGroupId) - 1);
		$this->view->all_group = $strGroupId;
		/*if(empty($data)){
			header('location:/');
		}
		$uploadSupported = $facebook->useFileUploadSupport();
		//UtilitiService::printDebug($uploadSupported);
		UtilitiService::printDebug($groupInfo);*/
	}
	
	public function postfeedAction(){
		$message = $this->_request->getParam('message');
		$groups = $this->_request->getParam('hid_group');
		$arrGroups = explode(',', $groups);
		$facebook = new Facebook(array(
  				'appId'  => $this->APP_ID,
  				'secret' => $this->SECRET,
			));
		//$facebook->setFileUploadSupport(true);
		$access_token = $facebook->getAccessToken();
		
		/*	if ((($_FILES["source"]["type"] == "image/gif")
			|| ($_FILES["source"]["type"] == "image/jpeg")
			|| ($_FILES["source"]["type"] == "image/png")
			|| ($_FILES["source"]["type"] == "image/pjpeg"))
			&& ($_FILES["source"]["size"] < 2000000)){
			if ($_FILES["source"]["error"] > 0){
			    $error .= "Return Code: " . $_FILES["source"]["error"] . "<br />";
			}else{
			    //echo "Upload: " . $_FILES["source"]["name"] . "<br />";
			    //echo "Type: " . $_FILES["source"]["type"] . "<br />";
			    //echo "Size: " . ($_FILES["source"]["size"] / 1024) . " Kb<br />";
			    //echo "Temp file: " . $_FILES["source"]["tmp_name"] . "<br />";
			
				//$args = array('message' => $message);
				$param['image'] = '@' . realpath($_FILES["source"]["tmp_name"]);
				//$args['fileName'] = 'FILE';
				//$data = $facebook->api('/326862114102997/photos', 'post', $args);
				//UtilitiService::printDebug($data);
			}
		}*/	
		
		$count = count($arrGroups);
		$allGroup = $_SESSION['groups'];
		$success = array();
		$fails = array();
		$param['message'] = $message;
		foreach ($arrGroups as $groupId) {
			try{
				$post = $facebook->api("/{$groupId}/feed",'post',$param);
				if(!empty($post['id'])){
					foreach ($allGroup as $group) {
						if($group['id'] == $groupId){
							$success[] = $group;
							break;
						}
					}
				}
				else{
					foreach ($allGroup as $group) {
						if($group['id'] == $groupId){
							$fails[] = $group;
							break;
						}
					}
				}
			}
			catch (Exception $e){
				foreach ($allGroup as $group) {
					if($group['id'] == $groupId){
						$fails[] = $group;
						$this->logger->err("can not post to group '{$group['name']}', Group Id: {$group['id']}. Message: ".$e->getMessage());
						break;
					}
				}
			}
		}
		$this->view->count = $count;
		$this->view->success = $success;
		$this->view->fails = $fails;
		/*echo $count;
		UtilitiService::printDebug($success);
		UtilitiService::printDebug($fails);exit;
		//$post = $facebook->api("/326862114102997/feed?message=$message",'post');
		UtilitiService::printDebug($post);exit;*/
	}
}