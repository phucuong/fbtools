<?php
    /*require_once dirname(__FILE__)."/../models/class.image.php";

    class CommonFnc_Z_Session_Class
    {
        public function __construct()
        {
            $ns    = Zend_Registry::get( "NS" );
            $this->zs = new Zend_Session_Namespace( $ns );
        }
        public function setSession($ar){
            foreach( $ar as $k=>$v ){
                $this->zs->$k    = empty($v) ? "" : $v;
            }
        }
        public function getSession(){
            return $this->zs;
        }
        public function setSessinData2Array(){
            $ret_array = array();
            foreach( $this->zs as $k=>$v ){
                $ret_array[$k] = $v;
            }
            return $ret_array;
        }
        public function clearSession(){
            foreach( $this->zs as $k=>$v ){
                $this->zs->$k = "";
            }
        }
		public function updateSession($key, $value){
			$this->zs->$key = empty($value) ? "" : $value;
		}
    }

    class CommonFnc_Message_Count_Class
    {
        public function GetMessageId($ar, $fld){
            if ( is_array($ar) && count($ar) > 0 ){
                $param["max"]    = $ar[0][$fld];
                $wk_no            = count($ar) -1;
                $param["min"]    = $ar[$wk_no][$fld];
            } else {
                $param = array( "max"=>0,"min"=>0 );
            }
            return $param;
        }
    }

    class CommonFnc_Mobile_Class
    {
        private $_is_mobile = false;
        private $_is_docomo = false;
        private $_is_au     = false;
        private $_is_sb     = false;
        private $_is_pc     = false;
        private $_is_sp     = false;

        public function is_mobile(){
            return $this->_is_mobile;
        }
        public function is_docomo(){
            return $this->_is_docomo;
        }
        public function is_au(){
            return $this->_is_au;
        }
        public function is_sb(){
            return $this->_is_sb;
        }
        public function is_pc(){
            return $this->_is_pc;
        }
        public function is_sp(){
            return $this->_is_sp;
        }
        public function __construct(){
            $ua = $_SERVER['HTTP_USER_AGENT'];
            // ドコモ
            if (preg_match('/^DoCoMo/', $ua)) {
                $this->_is_mobile = true;
                $this->_is_docomo = true;
            // au
            } elseif (preg_match('/^KDDI-|^UP\.Browser/',$ua)) {
                $this->_is_mobile = true;
                $this->_is_au     = true;
            // SoftBank
            } elseif (preg_match('#^J-(PHONE|EMULATOR)/|^(Vodafone/|MOT(EMULATOR)?-[CV]|SoftBank/|[VS]emulator/)#', $ua)) {
                $this->_is_mobile = true;
                $this->_is_sb     = true;
            // Willcom
            } elseif (preg_match('/(DDIPOCKET|WILLCOM);/', $ua)) {
                $this->_is_mobile = true;
            // e-mobile
            } elseif (preg_match('#^(emobile|Huawei|IAC)/#', $ua)) {
                $this->_is_mobile = true;
            // スマートフォン
            } elseif (preg_match('#\b(iP(hone|od);|Android )|dream|blackberry9500|blackberry9530|blackberry9520|blackberry9550|blackberry9800|CUPCAKE|webOS|incognito|webmate#', $ua)) {
                $this->_is_sp = true;
            // PC    
            } else {
                $this->_is_pc = true;
            }
        }
    }

    class CommonFnc_Image_Upload_Calss
    {

        private $_max_size = 1048576; // 1024*1024
        private $_valid_formats = array("jpg", "png","jpeg");
        private $_image_length = "150";

        public function UploadImage( $filename, $max_size, $img_path ){
            $name = $filename['photoimg']['name'];
            $size = $filename['photoimg']['size'];

            if ( !strlen($name) ) return array( "status"=>"99" );
            $name_array = explode('.', $name);
            $ext = end($name_array);
            $ext = strtolower($ext);
            if ( $ext == $name ) return array( "status"=>"98" );
            $txt = mb_substr($name, 0, (mb_strlen($name, "UTF-8") - strlen($ext) - 1), "UTF-8");
            if ( !in_array($ext,$this->_valid_formats) ) return array( "status"=>"98" );
            if ( $size > ( $this->_max_size * $max_size ) ) return array( "status"=>"97" );
            $id = empty($_SERVER["REMOTE_ADDR"])? mt_rand(0,99):str_replace(".","", $_SERVER["REMOTE_ADDR"]);
            $actual_image_name = time()."_".$id.".".$ext;

            if ( !move_uploaded_file( $filename['photoimg']['tmp_name'], $img_path."/".$actual_image_name) ) return array( "status"=>"96" );

            $base_file = $img_path."/".$actual_image_name;
            $mv_file   = $img_path."/".str_replace($id.".".$ext,$id."_org.".$ext,$actual_image_name);

            chmod($base_file,0777);

            copy($base_file,$mv_file);
            chmod($mv_file,0777);

            $size = GetImageSize($base_file);    //元画像サイズ取得
            if($size[0] > $size[1]){
                $height = $this->_image_length;
                $width = $height * ($size[0] / $size[1]);
            }else{
                $width = $this->_image_length;
                $height = $width * ($size[1] / $size[0]);
            }
              
            //画像オブジェクト生成
            $thumb = new Image($base_file); 
              
            //リサイズ
            $thumb->width($width);
            $thumb->height($height);
            $thumb->save();
              
            //トリミング
            $thumb->width($this->_image_length);
            $thumb->height($this->_image_length);
            if($width < $height){
                $thumb->crop(0, ($height - $width) / 2);
            }else{
                $thumb->crop(($width - $height) / 2, 0);
            }
            $thumb->save();


            return array( "status"=>"1", "filename"=>$actual_image_name, "ext"=>$ext, "realname"=>$name, "ext"=>$ext );
        }
        public function check_directory_exists( $new_dir, $mkdir_flag = "0" ){
            if ( is_dir($new_dir) ) return true;
            if ( $mkdir_flag == "1" ){
                if ( mkdir($new_dir) ) return true;
            }
            return false;
        }
    }
    class CommonFnc_Expand_URL
    {
        private function textlink($text){
            $text = html_entity_decode($text);
            $text = " ".$text;
            if(preg_match('(((f|ht){1}tp://)[-a-zA-Z0-9@:%_\+.~#?&//=]+)',$text,$a))
            {
            }
            else if(preg_match('(((f|ht){1}tps://)[-a-zA-Z0-9@:%_\+.~#?&//=]+)',$text,$a))
            {
            }
            else
            {
                $a=false;
            }
            return $a[0];
        }
        public function Expand_URL( $text ){
            $url = self::textlink( $text );
            $returns = "";
            if(!empty($url)){
                if(eregi("youtu",$url) or eregi("youtube",$url)){
                    if(eregi("v=",$url))
                        $splits = explode("=",$url);
                    else
                        $splits = explode("be/",$url);

                    if(!empty($splits[1])){
                        if(preg_match("/feature/i", $splits[1])){
                            $splits[1] = str_replace("&feature","",$splits[1]);    
                        }
                        $returns = '<iframe width="410" height="250" src="http://www.youtube.com/embed/'.$splits[1].'" frameborder="0"></iframe>';
                    }
                } else if(eregi("vimeo",$url)){
                    $splits = explode("com/",$url);
                    $returns = '<iframe src="http://player.vimeo.com/video/'.$splits[1].'?title=0&amp;byline=0&amp;portrait=0" width="410" height="250" frameborder="0"></iframe>';
                }
            }
            return $returns;
        }
    }
    class CommonFnc_GetAppInfo{
        public function get(){
            $app = array();
            $app["product_name"] = Zend_Registry::get("product_name");
            return $app;
        }
    }

    function commonFnc_getStaticURL($str){
        $url_array = array(
                         "UserRegistInfo"=>"/regist/",
                         "UserRegistConf"=>"/regist/conf.html",
                         "UserRegistComp"=>"/regist/comp.html",
                         "ComCreateInfo"=>"/community/create.html",
                         "ComCreateConf"=>"/community/createconf.html",
                         "ComCreateComp"=>"/community/createcomp.html",
                         "ComContactAddInfo"=>"/community/contactadd/",
                         "ComContactAddConf"=>"/community/contactaddconf.html",
                         "ComContactAddComp"=>"/community/contactaddcomp.html",
                         "ComContactUpdInfo"=>"/community/contactupd/",
                         "ComContactUpdConf"=>"/community/contactupdconf.html",
                         "ComContactUpdComp"=>"/community/contactupdcomp.html",
                         "ComTopicAddInfo"=>"/community/topicadd/",
                         "ComTopicAddConf"=>"/community/topicaddconf.html",
                         "ComTopicAddComp"=>"/community/topicaddcomp.html",
                         "ComTopicUpdInfo"=>"/community/topiupd/",
                         "ComTopicUpdConf"=>"/community/topicupdconf.html",
                         "ComTopicUpdComp"=>"/community/topicupdcomp.html",
                         "ComUpdateInfo"=>"/community/update/",
                         "ComUpdateConf"=>"/community/updateconf.html",
                         "ComUpdateComp"=>"/community/updatecomp.html",
                         "NetTopicAddComp"=>"/network/nettopicaddcomp.html",
                         "NetTopicAddInfo"=>"/network/nettopicadd/",
                         "NetTopicAddConf"=>"/network/nettopicaddconf.html",
                         "NetTopicUpdComp"=>"/network/nettopicupdcomp.html",
                         "NetTopicUpdInfo"=>"/network/nettopicupd/",
                         "NetTopicUpdConf"=>"/network/nettopicupdconf.html",
                         "NetContactAddComp"=>"/network/netcontactaddcomp.html",
                         "NetContactAddInfo"=>"/network/netcontactadd/",
                         "NetContactAddConf"=>"/network/netcontactaddconf.html",
                         "NetContactUpdComp"=>"/network/netcontactupdcomp.html",
                         "NetContactUpdInfo"=>"/network/netcontactupd/",
                         "NetContactUpdConf"=>"/network/netcontactupdconf.html",
        );
        return empty($url_array[$str]) ? "":$url_array[$str];
    }

   function commonFnc_setMsg2Japanese($str){
        $msg_array = array(
                         "user_name_sei"			=>"お名前（姓）",
                         "user_name_mei"			=>"お名前（名）",
                         "user_account_mail"		=>"メールアドレス",
                         "user_account_id"			=>"ニックネーム",
                         "user_account_password"	=>"パスワード",
                         "user_account_password_kakunin"	=>"パスワード(確認用)",
                         "user_todofuken"			=>"居住地（都道府県）",
                         "memberType"				=>"会員属性",
                         "user_jobtype"				=>"職種",
                         "user_connection"			=>"患者との関係",
                         "user_tel1"				=>"電話番号",
                         "user_tel2"				=>"電話番号",
                         "user_tel3"				=>"電話番号",
                         "com_name"					=>"コミュニティ名",
                         "com_explain"				=>"コミュニティ説明",
                         "msg"						=>"本文",
                         "title"					=>"タイトル",
                         "topic_name"				=>"トピック名",
                         "contact_name"				=>"連絡通知名",
                         "is hissu"					=>"を入力してください。",
                         "is select hissu"			=>"を選択してください。",
                         "is check hissu"			=>"をチェックしてください。",
                         "is not numeric"			=>"は数字を入力してください。",
                         "length over"				=>"は、{num}文字までです。",
                         "already_exists"			=>"既に登録されている{name}です。",
                         "invalid user_id"			=>"このユーザーIDは既に登録されています。",
                         "is invalid text"			=>"利用できない文字があります。",
                         "invalid mail"				=>"メールアドレスの形式が異なります",
                         'invalid mail confirm'		=>'メールアドレスが再入力と異なります',
                         "invalid tel"				=>"電話番号の形式が異なります",
                         "invalid password"			=>"パスワードが再入力と異なります。",
                         'wrong password'			=> 'パスワードが間違っています。',
                         "nickname exists"			=>"このニックネームは既に登録されています。",
                         "mailaddress exists"		=>"このメールアドレスは既に登録されています。",
                         "user image over"			=>"プロフィール画像は{%size%}Mバイト以内のファイルが有効です。",
                         "user image not jpg"		=>"プロフィール画像は{%type%}形式の画像のみ有効です。",
                         "com image over"			=>"コミュニティ画像は{%size%}Mバイト以内のファイルが有効です。",
                         "com image not jpg"		=>"コミュニティ画像は{%type%}形式の画像のみ有効です。",
                         "comname exists"			=>"このコミュニティ名は既に登録されています。",
        				 'in valid space'			=> "Username must not contain space!",
        				 'invalid date'				=> 'Birthday is invalid!'
        );
        return empty($msg_array[$str]) ? "":$msg_array[$str];
    }

    function commonFnc_getTodofukenList(){
        return array(
             "北海道"
            ,"青森県"
            ,"岩手県"
            ,"宮城県"
            ,"秋田県"
            ,"山形県"
            ,"福島県"
            ,"茨城県"
            ,"栃木県"
            ,"群馬県"
            ,"埼玉県"
            ,"千葉県"
            ,"東京都"
            ,"神奈川県"
            ,"新潟県"
            ,"富山県"
            ,"石川県"
            ,"福井県"
            ,"山梨県"
            ,"長野県"
            ,"岐阜県"
            ,"静岡県"
            ,"愛知県"
            ,"三重県"
            ,"滋賀県"
            ,"京都府"
            ,"大阪府"
            ,"兵庫県"
            ,"奈良県"
            ,"和歌山県"
            ,"鳥取県"
            ,"島根県"
            ,"岡山県"
            ,"広島県"
            ,"山口県"
            ,"徳島県"
            ,"香川県"
            ,"愛媛県"
            ,"高知県"
            ,"福岡県"
            ,"佐賀県"
            ,"長崎県"
            ,"熊本県"
            ,"大分県"
            ,"宮崎県"
            ,"鹿児島県"
            ,"沖縄県"
        );
    }

    // ==== メール送信共通ライブラリ ===== //
    function commonFnc_send_mail($templete_name, $assign_array, $to_name, $to_mailaddress, $mode="normal") {

        require_once(dirname(__FILE__)."/qdmail.php");
        $genUrl = new GenURL();
        $domain = $genUrl->setTopURL();
        require( dirname(__FILE__)."/../mail/".$templete_name . ".php" );

        $from_mailaddress = Zend_Registry::get('default_mail');
        $to = array( $to_mailaddress , $to_name.'　' );
        $other_heder['from'][] = array($from_mailaddress, $from_name);
        $option = array(
            'type' => 'text',
            'option'=>array('mtaOption'=>'-f '.$from_mailaddress),
        );

        $return_flag = @qd_send_mail( $option , $to , $subject , $body , $other_heder);

        if(!$return_flag){
            // ===== メール送信エラー処理 ===== //
        }
    }
    function commonFnc_paging($page,$max_rec,$rec_count,$url,$page_nav_count=10){
        if (substr($url,-1) == "/") $url = substr($url,0,strlen($url)-1);

        $ret_array = array();
        $max_page = $ret_array["max_page"] = ceil($rec_count / $max_rec);
        if ($page > $max_page) return false;
        $ret_array["max_url"] = create_paging_url($url,$max_page);
        if ($page == 1) {
        } else {
            $ret_array["prev_page"]["page"] = $page - 1;
            $ret_array["prev_page"]["url"]  = create_paging_url($url,$ret_array["prev_page"]["page"]);
        }
        if ($page == $max_page) {
        } else {
            $ret_array["next_page"]["page"] = $page + 1;
            $ret_array["next_page"]["url"]  = create_paging_url($url,$ret_array["next_page"]["page"]);
        }
        $start_point = ($page - 2) > 0 && $max_page > ($page_nav_count - 1) ? ($page - 2):"1";
        if ($start_point != "1" && $page > ($max_page - $page_nav_count + 1)){
            $start_point = $max_page - $page_nav_count + 1;
            if ($start_point < "1") $strat_point = "1";
        }
        $ret_array["page_array"] = array();
        for($i=$start_point;$i<=($start_point + ($page_nav_count - 1));$i++){
            if ($i > $max_page) break;
            $ret_array["page_array"][] = array("page"=>$i,"url"=>create_paging_url($url,$i));
        }
        return $ret_array;
    }
    function create_paging_url($url,$page){
        if ($page == "1") return $url;
        return $url ."/page/".$page;
    }
	
	function generateRandomString($length = 32) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, strlen($characters) - 1)];
		}
		return $randomString;
	}*/
?>