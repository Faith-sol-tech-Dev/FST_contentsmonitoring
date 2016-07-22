<?php
namespace ContentsMonitor\Common;

use Zend\Config\Config as Config;

use ContentsMonitor\Common\LogClass as Log;
use ContentsMonitor\Common\RequestClass as Request;
use ContentsMonitor\Common\MessageClass as Message;


/**
 * 監視サイトで使用する検証関数群
 *
 * @package ContentsMonitor
 * @author  FST goto
 * @since   PHP 5.6, Zendframwork 2.4.9
 * @version 1.0.0
 */
class ValidationClass
{
	/**
	 * エンコードチェック （mb_check_encoding）
	 * 
	 * @param string $vl チェック対象値
	 * @return bool true:OK / false:NG
	 */
	public static function mb_check_encoding($vl)
	{
		if (mb_check_encoding($request['id'], 'UTF-8')) {
			return true;
		}
		return false;
	}
	
	/**
	 * メールアドレスチェック
	 *
	 * @param string $vl チェック対象値
	 * @return bool true:OK / false:NG
	 */
	public static function checkMailaddress($vl)
	{
		$config = new Config(include CONF_PATH);
		$regex = $config->const->regex_mail;
		
		if (self::_validate($value, $regex, $matches)) {
			return true;
			
			if (getmxrr($matches[1], $mxhosts)) {
				return true;
			}
			if (checkdnsrr($matches[1], 'MX')) {
				return true;
			}
			if (is_array(gethostbynamel($matches[1]))) {
				return true;
			}
			return false;
		}
		return false;
	}
	
	/**
	 * URL
	 *
	 * @param string $value 文字列
	 * @return bool URLの場合に TRUE、その他の場合に FALSE を返します。
	 */
	public static function url($value)
	{
		$config = new Config(include CONF_PATH);
		$regex = $config->const->regex_url;
		
		if (self::_validate($value, $regex, $matches)) {
			return true;
		}
		return false;
	}
	
	/**
	 * バリデート
	 *
	 * @param string $value 文字列
	 * @param string $regex 正規表現
	 * @param array  &$matches 検索結果
	 * @return bool 正規表現に一致する場合に TRUE、その他の場合に FALSE を返します。
	 */
	private static function _validate($value, $regex, &$matches)
	{
		if (!is_string($value)) {
			return false;
		}
		if (!preg_match($regex, $value, $matches)) {
			return false;
		}
		return true;
	}
	
	/**
	 * 入力フォーム汎用バリデート
	 *
	 * @param array &$request (参照渡し)リクエストデータ
	 * @param string $column 項目(カラム)名
	 * @param string $title 項目タイトル
	 * @param bool $requiredFlag モード（false:任意、true:必須）
	 * @param integer $pattern パターン（0:制御文字以外、1:数値のみ、2:メールアドレス、3:パスワード、4:日付、5:カナ、6：制御文字も含める（字数制限のみ）、7:URL）
	 * @param integer $minLength 項目の最小長（nullで無視）
	 * @param integer $maxLength 項目の最大長（nullで無視）
	 */
	public static function validateForm(&$request, $column, $title, $requiredFlag, $pattern, $minLength, $maxLength)
	{
	    $message = new Message();
		$value = Request::arrayValue($request, $column, '');
		
		if (!mb_check_encoding($value, 'UTF-8')) {
			Log::notice(__FILE__, __LINE__, $column . ' = [' . $value . ']');
			return ($title != '' ? sprintf($message->HTTP_DISPLAY_MESSAGE['content_search_encode_title'], $title) : $message->HTTP_DISPLAY_MESSAGE['content_search_encode'] );
		}
		
		if ($requiredFlag) {
			if ($value == '') {
				Log::notice(__FILE__, __LINE__, $column . ' = []');
				return ($title != '' ? sprintf($message->HTTP_DISPLAY_MESSAGE['content_search_required_title'], $title) : $message->HTTP_DISPLAY_MESSAGE['content_search_required'] );
			}
		}
		
		switch ($pattern) {
			case 0:
				// 制御文字以外
				if (!preg_match('/\A[^[:cntrl:]]*\z/u', $value)) {
					Log::notice(__FILE__, __LINE__, $column . ' = [' . $value . ']');
					return ($title != '' ? sprintf($message->HTTP_DISPLAY_MESSAGE['content_search_control_title'], $title) : $message->HTTP_DISPLAY_MESSAGE['content_search_control'] );
				}
				break;
			case 1:
				// 数値
				if (!preg_match('/\A[0-9]*\z/u', $value)) {
					Log::notice(__FILE__, __LINE__, $column . ' = [' . $value . ']');
					return ($title != '' ? sprintf($message->HTTP_DISPLAY_MESSAGE['content_search_numeric_title'], $title) : $message->HTTP_DISPLAY_MESSAGE['content_search_numeric'] );
				}
				break;
			case 2: 
				// メールアドレス
				if (!self::emailAddress($value)) {
					Log::notice(__FILE__, __LINE__, $column . ' = [' . $value . ']');
					return ($title != '' ? sprintf($message->HTTP_DISPLAY_MESSAGE['content_search_mail_title'], $title) : $message->HTTP_DISPLAY_MESSAGE['content_search_mail'] );
				}
				break;
			case 3:
				// パスワード
				if ($value != '') {
					if (!preg_match('/\A[a-zA-Z0-9]*\z/u', $value)) {
						Log::notice(__FILE__, __LINE__, $column . ' = [' . $value . ']');
						return ($title != '' ? sprintf($message->HTTP_DISPLAY_MESSAGE['content_search_pass_title'], $title) : $message->HTTP_DISPLAY_MESSAGE['content_search_pass'] );
					}
					if (!(preg_match('/[a-zA-Z]+/u', $value) && preg_match('/[0-9]+/u', $value))) {
						Log::notice(__FILE__, __LINE__, $column . ' = [' . $value . ']');
						return ($title != '' ? sprintf($message->HTTP_DISPLAY_MESSAGE['content_search_pass_title'], $title) : $message->HTTP_DISPLAY_MESSAGE['content_search_pass'] );
					}
				}
				break;
			case 4:
				// 日付
				// 空白はOK
				if (empty($value)) {
					return '';
				}
				
				if (preg_match('/\A([0-9]{4})[-\/]([01]?[0-9])[-\/]([0123]?[0-9])\z/u', $value, $parts)) {
					if (!checkdate($parts[2], $parts[3], $parts[1])) {
						Log::notice(__FILE__, __LINE__, $column . ' = [' . $value . ']');
						return ($title != '' ? sprintf($message->HTTP_DISPLAY_MESSAGE['content_search_date_title'], $title) : $message->HTTP_DISPLAY_MESSAGE['content_search_date'] );
					}
						
				} else {
					return ($title != '' ? sprintf($message->HTTP_DISPLAY_MESSAGE['content_search_date_title'], $title) : $message->HTTP_DISPLAY_MESSAGE['content_search_date'] );
				}
				break;
			case 5:
				// カナ文字列
				if ($value != '') {
					if (!preg_match('/\A[ｦ-ﾟァ-ヾ]+\z/u', $value)) {
						Log::notice(__FILE__, __LINE__, $column . ' = [' . $value . ']');
						return ($title != '' ? sprintf($message->HTTP_DISPLAY_MESSAGE['content_search_kana_title'], $title) : $message->HTTP_DISPLAY_MESSAGE['content_search_kana'] );
					}
				}
				break;
			case 6:
				// 制御文字も含める（字数制限のみ）
				if ($value != '') {
					if (!preg_match('/\A[\r\n[:^cntrl:]]+\z/u', $value)) {
						Log::notice(__FILE__, __LINE__, $column . ' = [' . $value . ']');
						return ($title != '' ? sprintf($message->HTTP_DISPLAY_MESSAGE['content_search_string_title'], $title) : $message->HTTP_DISPLAY_MESSAGE['content_search_string'] );
					}
				}
				break;
			case 7:
				// URL
				if ($value != '') {
					if (!self::url($value)) {
						Log::notice(__FILE__, __LINE__, $column . ' = [' . $value . ']');
						return ($title != '' ? sprintf($message->HTTP_DISPLAY_MESSAGE['content_search_url_title'], $title) : $message->HTTP_DISPLAY_MESSAGE['content_search_url'] );
					}
				}
			case 8:
				// 英数値
				if (!preg_match('/^[a-zA-Z0-9_-]+$/', $value)) {
					Log::notice(__FILE__, __LINE__, $column . ' = [' . $value . ']');
					return ($title != '' ? sprintf($message->HTTP_DISPLAY_MESSAGE['content_search_numeric_title'], $title) : $message->HTTP_DISPLAY_MESSAGE['content_search_numeric'] );
				}
				break;
			default:
				break;
		}
		
		if ($minLength !== null && mb_strlen($value, "UTF-8") < $minLength){
			if (!($pattern == 3 && $value == '')) {
				Log::notice(__FILE__, __LINE__, $column . ' = [' . $value . ']');
				return ($title != '' ? sprintf($message->HTTP_DISPLAY_MESSAGE['content_search_length_min_item'], $title, $minLength) : 
																					$message->HTTP_DISPLAY_MESSAGE['content_search_length_min'] );
			}
		}
		
		if ($maxLength !== null && mb_strlen($value, "UTF-8") > $maxLength){
			Log::notice(__FILE__, __LINE__, $column . ' = [' . $value . ']');
			return ($title != '' ? sprintf($message->HTTP_DISPLAY_MESSAGE['content_search_length_max_item'], $title, $minLength) : 
																					$message->HTTP_DISPLAY_MESSAGE['content_search_length_max'] );
		}
		
		return '';
	}
	
	/**
	 * API汎用バリデート
	 *
	 * @param array &$request (参照渡し)リクエストデータ
	 * @param string $column 項目(カラム)名
	 * @param bool $requiredFlag モード（false:任意、true:必須）
	 * @param integer $pattern パターン（0:制御文字以外、1:数値のみ、2:日時、3:URL、4:制御文字(\r\n)も含める、5:制御文字(\r\n\t)も含める）
	 * @param integer $minLength 項目の最小長（nullで無視）
	 * @param integer $maxLength 項目の最大長（nullで無視）
	 * @return $value バリデートに合格した場合のみ、値を返す。（不正の場合は、null）
	 */
	public static function validateApi(&$request, $column, $requiredFlag, $pattern, $minLength, $maxLength)
	{
		$result = null;
		try {
			$value = Request::arrayValue($request, $column, '');
			
			if (!mb_check_encoding($value, 'UTF-8')) {
				throw new \Exception($column . ' = [' . $value . ']');
			}
			
			$value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
			
			if ($requiredFlag) {
				if ($value == '') {
					throw new \Exception($column . ' = []');
				}
			}
			
			if (is_array($pattern)) {
				if (!in_array($value, $pattern)) {
					throw new \Exception($column . ' = [' . $value . ']');
				}
			} else {
				switch ($pattern) {
					case 0:
						// 制御文字以外
						if (!preg_match('/\A[^[:cntrl:]]*\z/u', $value)) {
							throw new \Exception($column . ' = [' . $value . ']');
						}
						break;
					case 1:
						// 数値
						if (!preg_match('/\A[0-9]*\z/u', $value)) {
							throw new \Exception($column . ' = [' . $value . ']');
						}
						break;
					case 2:
						// 日時
						if (preg_match('/\A([0-9]{4})[\/\-]([[0-9]{1,2})[\/\-]([0-9]{1,2}) ([0-1]?[0-9]|2[0-3]):([0-5]?[0-9]):([0-5]?[0-9])\z/u', $value, $parts)) {
							if (!checkdate($parts[2], $parts[3], $parts[1])) {
								throw new \Exception($column . ' = [' . $value . ']');
							}
						} else {
							throw new \Exception($column . ' = [' . $value . ']');
						}
						break;
					case 3:
						// URL
						if ($value != '') {
							if (!self::url($value)) {
								throw new \Exception($column . ' = [' . $value . ']');
							}
						}
					case 4:
						// 制御文字(\r\n)も含める（字数制限のみ）
						if ($value != '') {
							if (!preg_match('/\A[\r\n[:^cntrl:]]+\z/u', $value)) {
								throw new \Exception($column . ' = [' . $value . ']');
							}
						}
						break;
					case 5:
						// 制御文字(\r\n\t)も含める（字数制限のみ）
						if ($value != '') {
							if (!preg_match('/\A[\r\n\t[:^cntrl:]]+\z/u', $value)) {
								throw new \Exception($column . ' = [' . $value . ']');
							}
						}
						break;
					default:
						break;
				}
			}
			
			if ($minLength !== null && mb_strlen($value, "UTF-8") < $minLength){
				if (!($pattern == 3 && $value == '')) {
					throw new \Exception($column . ' = [' . $value . ']');
				}
			}
			
			if ($maxLength !== null && mb_strlen($value, "UTF-8") > $maxLength){
				throw new \Exception($column . ' = [' . $value . ']');
			}
			$result = $value;
		} catch (\Exception $e) {
			Log::batch($e->getFile(), $e->getLine(), $e->getMessage());
			$result = null;
		}
		return $result;
	}
}
