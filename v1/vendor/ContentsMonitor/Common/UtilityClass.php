<?php
namespace ContentsMonitor\Common;

use Zend\Config\Config as Config;

/**
 * 監視サイトで使用する関数群
 *
 * @package ContentsMonitor
 * @author  FST goto
 * @since   PHP 5.6, Zendframwork 2.4.9
 * @version 1.0.0
 */
class UtilityClass
{
	/**
	 * ランダム文字列作成(a-zA-Z0-9)
	 *
	 * @param  integer $length 作成するランダム文字列の長さ
	 * @param  string $list 作成するランダム文字列の文字一覧
	 * @return string ランダム文字列
	 */
	public static function getRandomString($length, $list = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789')
	{
		// デフォルト設定値をセット（設定ファイル値）
		$config = new Config(include CONF_PATH);
		$list = $config->const->rand_list;
		
		//-----------------------------------------------------
		
		$ret = '';
		mt_srand(microtime() * 1000000);
		for($i = 0; $i < $length; $i++) {
			$ret .= $list[mt_rand(0, strlen($list) - 1)];
		}

		return $ret;
	}
	
	/**
	 * 全角スペース対応tirm(?)
	 *
	 * @param  string $val 文字列
	 * @return string trim後文字列
	 */
	public static function mb_trim($val)
	{
		return preg_replace('/\A[\s　]*(.*?)[\s　]*\z/u', '\1', $val);
	}
	
	/**
	 * 文字列の暗号化 (OpenSSL関数)
	 * 
	 * @param string $length 文字列
	 * @return string 暗号化した文字列
	 */
	public static function getEncryptionString( $length )
	{
		// デフォルト設定値をセット（設定ファイル値）
		$config = new Config(include CONF_PATH);
		$key = $config->const->cryptography_key;
		
		//-----------------------------------------------------
		return openssl_encrypt($length, 'AES-128-ECB', $key);
	}

	/**
	 * 文字列の復号化 (OpenSSL関数)
	 *
	 * @param string $length 文字列
	 * @return string 復号化した文字列
	 */
	public static function getDecryptionString( $length )
	{
		// デフォルト設定値をセット（設定ファイル値）
		$config = new Config(include CONF_PATH);
		$key = $config->const->cryptography_key;
	
		//-----------------------------------------------------
		return openssl_decrypt($length, 'AES-128-ECB', $key);
	}
	
	/**
	 * 文字列のエンコーディング (UTF-8)
	 * 
	 * @param  string $val 文字列
	 * @return string encoding後文字列
	 */
	public static function mb_convert_encoding($val)
	{
		return mb_convert_encoding($val, 'UTF-8', 'UTF-8');
	}

	/**
	 * XMLオブジェクトを配列に変換
	 * 
	 * @param unknown $data XMLオブジェクト
	 * @return array 配列オブジェクト
	 */	
	public static function simplexml_load_string($data)
	{
		return simplexml_load_string($data);
	}

	/**
	 * JSONオブジェクトを配列に変換
	 * 
	 * @param unknown $data JSONオブジェクト
	 * @return array 配列オブジェクト
	 */	
	public static function json_decode($data, $opt=true)
	{
		return json_decode($data, $opt);
	}
	
	/**
	 * デバッグ用、時間フォーマット変換
	 * 
	 * @param float $time 処理時間
	 * @param string $format
	 * @return string
	 */
	public static function formatMicrotime( $time, $format = null )
	{
		if (is_string($format)) {
			$sec  = (int)$time;
			$msec = (int)(($time - $sec) * 100000);
			$formated = date($format, $sec). '.'. $msec;
		} else {
			$formated = sprintf('%0.5f', $time);
		}
		return $formated;
	}
	
	/**
	 * 監視管理画面のクッキーのセット
	 * 
	 * @param string $title クッキーデータ
	 */
	public static function setCookie( $data )
	{
		setcookie($data, TRUE);
	}
	
	/**
	 * クッキーが有効化のチェック
	 * 
	 * @param string $data　クッキーデータ
	 */
	public static function isCookie( $data )
	{
		$ret = false;
		if(isset($_COOKIE[$data])) {
			$cookie = $_COOKIE[$data];
			setcookie($data);     //クッキー消去
			if ($cookie) {
				//クッキーが有効
				$ret = true;
			} else {
				//クッキーが無効であれば、メッセージを出す
				$ret = false;
			}
		}
		return $ret;
	}
}
