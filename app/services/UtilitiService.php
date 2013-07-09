<?php

class UtilitiService{
	
	public static function redirectToTop(){
		$genUrl = new GenURL();
		$url = $genUrl->setTopURL();
		header("Location:{$url}");
	}
	
	public static function printDebug($data){
		echo '<meta charset="utf-8" />';
		echo '<pre>';
		var_dump($data);
		echo '</pre>';
		echo '<br />';
	}
	
	public static function createJson($json_array) {
		if (is_array($json_array) && count($json_array) > 0) {
			return Zend_Json::encode($json_array);
		} else {
			return '[]';
		}
	}
	
	public static function cutJapanString($str, $maxLength){
		$length = mb_strlen($str,'utf8');
		if($length > $maxLength){
			$str = mb_substr($str, 0, $maxLength,'utf8');
			$str.= '...';
		}
		return $str;
	}
	
	/**
	 * @param string $date. Ex: 2012-03-24
	 * @return boolean: true if valid, false if invalid
	 */
	public static function isValidDate($date){
		if (preg_match("/^(\d{4})-(\d{2})-(\d{2})$/", $date, $matches)) {
			if (checkdate($matches[2], $matches[3], $matches[1])) {
				return true;
			}
		}
		return false;
	}
}