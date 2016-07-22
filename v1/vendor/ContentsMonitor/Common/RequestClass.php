<?php
namespace ContentsMonitor\Common;

/**
 * 監視サイトで使用するリクエスト情報
 *
 * @package ContentsMonitor
 * @author  FST goto
 * @since   PHP 5.6, Zendframwork 2.4.9
 * @version 1.0.0
 */
class RequestClass
{
	/**
	 * 配列から指定のキーに対応する値を返す
	 *
	 * @param array $array 配列
	 * @param string $key キー
	 * @param mixed $default デフォルト
	 * @param boolean $flag 配列フラグ
	 * @return mixed 指定のキーに対応する値を返します。
	 */
	public static function arrayValue($array, $key, $default = null, $flag = false)
	{
		$ret = isset($array[$key]) ? $array[$key] : $default;
		if ($flag) {
			if (!is_array($ret)) {
				$ret = $default;
			}
		} else {
			if (is_array($ret)) {
				$ret = $default;
			}
		}
		return $ret;
	}
	
	/**
	 * リクエストメソッドがGETであるかの確認
	 *
	 * @return bool リクエストメソッドがGETの場合に TRUE、その他の場合に FALSE を返します。
	 */
	public static function isRequestMethodGet()
	{
		$requestMethod = isset($_SERVER["REQUEST_METHOD"]) ? $_SERVER["REQUEST_METHOD"] : '';
		return (($requestMethod == 'GET') ? true : false);
	}
	
	/**
	 * リクエストメソッドがPOSTであるかの確認
	 *
	 * @return bool リクエストメソッドがPOSTの場合に TRUE、その他の場合に FALSE を返します。
	 */
	public static function isRequestMethodPost()
	{
		$requestMethod = isset($_SERVER["REQUEST_METHOD"]) ? $_SERVER["REQUEST_METHOD"] : '';
		return (($requestMethod == 'POST') ? true : false);
	}
	
	/**
	 * リクエストがajaxであるかの確認
	 *
	 * @return bool リクエストがajaxの場合に TRUE を、その他の場合に FALSE を返します。
	 */
	public static function isRequestAjax()
	{
		if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == "XMLHttpRequest") {
			return true;
		}
		return false;
	}
}
