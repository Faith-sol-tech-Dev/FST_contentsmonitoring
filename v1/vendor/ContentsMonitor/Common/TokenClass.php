<?php
namespace ContentsMonitor\Common;

use Zend\Config\Config as Config;
use ContentsMonitor\Common\LogClass as Log;
use ContentsMonitor\Common\UtilityClass as Utility;
use ContentsMonitor\Exception\AuthExceptionClass as AuthException;

/**
 * 監視サイトで使用するワンタイムトークン
 *
 * @package ContentsMonitor
 * @author  FST goto
 * @since   PHP 5.6, Zendframwork 2.4.9
 * @version 1.0.0
 */
class TokenClass
{
	/**
	 * ワンタイムトークン確認(セッション)
	 *
	 * @param  string $index  ワンタイムトークン種別
	 * @param  string $string ワンタイムトークンキー
	 * @param  string $expire ワンタイムトークン有効期限（日付）
	 * @return bool 成功した場合に TRUE、失敗した場合に FALSE を返します。
	 */
	public static function checkOneTimeToken($index, $string, $expire)
	{
		Log::debug(__FILE__, __LINE__, sprintf('INFO   checkOneTimeToken() -- Param:$index=%s, $string=%s, $expire=%s',$index, $string, $expire));

		// デフォルト設定値をセット（設定ファイル値）
		$config = new Config(include CONF_PATH);
		$index = $config->token->index;
		//-----------------------------------------------------
		
		$ret = false;
		try {

			Log::debug(__FILE__, __LINE__, sprintf('--  $_SESSION["index"]=%s',$_SESSION['index']));
			if (!isset($_SESSION['index'])) {
				return $ret;
			}
			$indexkey = UtilityClass::getDecryptionString( $_SESSION['index'] );
			Log::debug(__FILE__, __LINE__, sprintf('--  $_SESSION["token"][%s]=%s', $indexkey, $_SESSION['token'][$indexkey]['key']));
			if (!isset($_SESSION['token'][$indexkey])) {
				return $ret;
			}
				
			// 有効期限切れトークン削除
			if ($expire < NOW) {
				unset($tokenList);
				$_SESSION['token'][$indexkey] = null;
				$_SESSION['index'] = null;
				Log::debug(__FILE__, __LINE__, 'INFO   checkOneTimeToken() --  this token is expired.');
			}
			else {
				$tokenList = $_SESSION['token'][$indexkey];
				if ($tokenList['key'] == $string) {
					$ret = true;
					Log::debug(__FILE__, __LINE__, 'INFO   checkOneTimeToken() --  this token is valid.');
				}
			}
		}
		catch( AuthException $ae )	{
			Log::debug(__FILE__, __LINE__, 'ERROR checkOneTimeToken() --  this token check failed.');
			Log::error(__FILE__, __LINE__, $ae->getMessage());
			throw $ae;
		}
		
		return $ret;
	}
	
	/**
	 * ワンタイムトークンの使用期日を定義
	 * 
	 * @return トークンの使用期日
	 */
	public static function createOneTimeTokenExpire()
	{
		Log::debug(__FILE__, __LINE__, 'INFO   createOneTimeTokenExpire() --  update for token expire.');
		
		// デフォルト設定値をセット（設定ファイル値）
		$config = new Config(include CONF_PATH);
		$expire = $config->session->expire;
		
		return NOW + $expire;
	}
	
	/**
	 * ワンタイムトークン作成(セッション)
	 *
	 * @param string  $index 作成するワンタイムトークン種別
	 * @param integer $length 作成するワンタイムトークンキーの長さ
	 * @param integer $expire 作成するワンタイムトークンの有効期限（単位：秒）
	 * @param integer $limit 作成するワンタイムトークンのストック数
	 * @return string ワンタイムトークンキー
	 */
	public static function createOneTimeToken($index=null, $length = 40, $expire = 1800, $limit = 100)
	{
		$start_time=microtime(true);

		// デフォルト設定値をセット（設定ファイル値）
		$config = new Config(include CONF_PATH);
		$length = $config->session->length;
		$expire = $config->session->expire;
		$limit = $config->session->limit;
		$index = $config->token->index;
		//-----------------------------------------------------

		$tokenList = array();
		$tokenData = array();
		try {
			
			if (isset($_SESSION['token'][$index])) {
				$tokenList = $_SESSION['token'][$index];
				Log::debug(__FILE__, __LINE__, sprintf('DEBUG トークンリストあり。$index=%s',$index));
			}
			
			// 一意キー作成
			$tokenListCount = count($tokenList);
			while (true) {
				$key = UtilityClass::getRandomString($length);
				$flag = true;
				for ($i = 0; $i < $tokenListCount; $i++) {
					if ($tokenList['key'] == $key) {
						Log::debug(__FILE__, __LINE__, sprintf('DEBUG 生成したトークンが存在しています。$key=%s',$key));
						$flag = false;
						break;
					}
				}
				if ($flag) {
					break;
				}
			}
			
			// トークン設定
			$tokenData['key'] = $key;
			$_SESSION['token'][$index] = $tokenData;
			Log::debug(__FILE__, __LINE__, sprintf('--  $_SESSION["token"][%s]=%s', $index, $_SESSION['token'][$index]['key']));
			$_SESSION['index'] = UtilityClass::getEncryptionString($index);
			Log::debug(__FILE__, __LINE__, sprintf('--  $_SESSION["index"]=%s',$_SESSION['index']));
				
			// トークン有効期限を設定（SESSIONに含めない）
			$tokenData['expire'] = NOW + $expire;
			Log::debug(__FILE__, __LINE__, sprintf('--  NOW=%s',NOW));
			Log::debug(__FILE__, __LINE__, sprintf('--  expire=%s',$tokenData['expire']));
		}
		catch( AuthException $ae ) {
			Log::debug(__FILE__, __LINE__, 'ERROR createOneTimeToken() --  failed create Onetime token.');
			Log::error(__FILE__, __LINE__, $ae->getMessage());
			throw $ae;
		}

        $diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
		Log::debug(__FILE__, __LINE__, 'INFO   createOneTimeToken() --  successed create Onetime token.('.$diff_time.')');
		return $tokenData;
	}
	
	/**
	 * ワンタイムトークン削除（セッション）
	 * 
	 * @param unknown $index 削除するワンタイムトークンの種別
	 */
	public static function deleteOneTimeToken($index = null)
	{
		// デフォルト設定値をセット（設定ファイル値）
		$config = new Config(include CONF_PATH);
		$index = $config->token->index;
		//-----------------------------------------------------
		
		if( !empty($index) ) {
			if (isset($_SESSION['token'][$index])) {
				$_SESSION['token'][$index] = null;
			}
			if (isset($_SESSION['index'])) {
				$_SESSION['index'] = null;
			}
		}
		else {
			$_SESSION['token'] = null;
			$_SESSION['index'] = null;
		}
	}
	
	/**
	 * ワンタイムトークン格納値更新(セッション) ???
	 *
	 * @param  string $index ワンタイムトークン種別
	 * @param  string $string ワンタイムトークンキー
	 * @param  mixed  $value 格納値
	 * @param  bool   $flag 有効期限更新フラグ(true:更新する false:更新しない)
	 * @param  integer $expire 作成するワンタイムトークンの有効期限（単位：秒）
	 * @return mixed 成功した場合に TRUE、失敗した場合に FALSE を返します。
	 */
	public static function updateOneTimeTokenEx($index, $string, $value, $flag = true, $expire = 3600)
	{
		$ret = false;
		if (!isset($_SESSION['tokenEx'][$index])) {
			return $ret;
		}
		$tokenList = $_SESSION['tokenEx'][$index];
		$tokenListCount = count($tokenList);
		for ($i = 0; $i < $tokenListCount; $i++) {
			// 有効期限切れトークン削除
			if ($tokenList[$i]['expire'] < NOW) {
				unset($tokenList[$i]);
				continue;
			}
			// トークン確認
			if ($tokenList[$i]['key'] == $string) {
				$tokenData = array();
				$tokenData['key'] = $tokenList[$i]['key'];
				$tokenData['value'] = $value;
				if ($flag) {
					$tokenData['expire'] = NOW + $expire;
				}
				unset($tokenList[$i]);
				$tokenList[] = $tokenData;
				$ret = true;
				break;
			}
		}
		$tokenList = array_values($tokenList);
		$_SESSION['tokenEx'][$index] = $tokenList;
		
		return $ret;
	}
	
	/**
	 * ワンタイムトークン確認(セッション) ???
	 *
	 * @param string $index ワンタイムトークン種別
	 * @param string $string ワンタイムトークンキー
	 * @param bool   $flag 消込フラグ(true:消し込む false:消し込まない)
	 * @return mixed 成功した場合に 格納値、失敗した場合に FALSE を返します。
	 */
	public static function checkOneTimeTokenEx($index, $string, $flag = true)
	{
		$ret = false;
		if (!isset($_SESSION['tokenEx'][$index])) {
			return $ret;
		}
		$tokenList = $_SESSION['tokenEx'][$index];
		$tokenListCount = count($tokenList);
		for ($i = 0; $i < $tokenListCount; $i++) {
			// 有効期限切れトークン削除
			if ($tokenList[$i]['expire'] < NOW) {
				unset($tokenList[$i]);
				continue;
			}
			// トークン確認
			if ($tokenList[$i]['key'] == $string) {
				$ret = $tokenList[$i]['value'];
				if ($flag) {
					unset($tokenList[$i]);
				}
				break;
			}
		}
		$tokenList = array_values($tokenList);
		$_SESSION['tokenEx'][$index] = $tokenList;
		
		return $ret;
	}
	
	/**
	 * ワンタイムトークン作成(セッション) ???
	 *
	 * @param string  $index 作成するワンタイムトークン種別
	 * @param mixed   $value 格納値
	 * @param integer $length 作成するワンタイムトークンキーの長さ
	 * @param integer $expire 作成するワンタイムトークンの有効期限（単位：秒）
	 * @param integer $limit 作成するワンタイムトークンのストック数
	 * @return string ワンタイムトークンキー
	 */
	public static function createOneTimeTokenEx($index, $value, $length = 40, $expire = 3600, $limit = 100)
	{
		$tokenList = array();
		if (isset($_SESSION['tokenEx'][$index])) {
			$tokenList = $_SESSION['tokenEx'][$index];
		}
		
		// 有効期限切れトークン削除
		$tokenListCount = count($tokenList);
		for ($i = 0; $i < $tokenListCount; $i++) {
			if ($tokenList[$i]['expire'] < NOW) {
				unset($tokenList[$i]);
			}
		}
		$tokenList = array_values($tokenList);
		
		// 一意キー作成
		$tokenListCount = count($tokenList);
		while (true) {
			$key = UtilityClass::getRandomString($length);
			$flag = true;
			for ($i = 0; $i < $tokenListCount; $i++) {
				if ($tokenList[$i]['key'] == $key) {
					$flag = false;
					break;
				}
			}
			if ($flag) {
				break;
			}
		}
		
		// トークン設定
		$tokenData = array();
		$tokenData['key'] = $key;
		$tokenData['value'] = $value;
		$tokenData['expire'] = NOW + $expire;
		
		$tokenList[] = $tokenData;
		
		// ストック上限時、一番古いトークンを消す
		$tokenListCount = count($tokenList);
		if ($tokenListCount > $limit) {
			unset($tokenList[0]);
			$tokenList = array_values($tokenList);
		}
		$_SESSION['tokenEx'][$index] = $tokenList;
		
		return $key;
	}
	
	/**
	 * ワンタイムログイントークン確認(DB)???
	 *
	 * @param string $token トークン
	 * @param bool   $flag 消込フラグ(true:消し込む false:消し込まない)
	 * @return boolean 成功した場合に ユーザID を、失敗した場合に FALSE を返します。
	 */
	public static function checkOneTimeTokenDB($token, $flag)
	{
		if (!preg_match('/\A[a-zA-Z0-9]+\z/u', $token)) {
			vs_log::notice(__FILE__, __LINE__, 'token = [' . $token . ']');
			return false;
		}
		try {
			$ret = $GLOBALS['db']->getOneTimeToken($token);
		} catch (Exception $e) {
			vs_log::error(__FILE__, __LINE__, 'token = [' . $token . ']');
			return false;
		}
		if ($ret === false) {
			// トークンが存在しない
			vs_log::notice(__FILE__, __LINE__, 'token = [' . $token . ']');
			return false;
		}
		if (strtotime($ret['expire']) < NOW) {
			// 有効期限切れ
			vs_log::notice(__FILE__, __LINE__, 'token = [' . $token . ']');
			return false;
		}
		if ($flag) {
			try {
				$GLOBALS['db']->deleteOneTimeToken($token);
			} catch (Exception $e) {
				vs_log::error(__FILE__, __LINE__, 'token = [' . $token . ']');
				return false;
			}
		}
		return $ret['user_id'];
	}
	
	/**
	 * ワンタイムログイントークン作成(DB)???
	 *
	 * @param integer $user_id ユーザID
	 * @param integer $length 作成するワンタイムトークンキーの長さ
	 * @param integer $expire 作成するワンタイムトークンの有効期限（単位：秒）
	 * @param integer $limit 作成するワンタイムトークンのストック数
	 * @return array トークン情報
	 */
	public static function createOneTimeTokenDB($user_id, $length = 40, $expire = 86400, $limit = 100)
	{
		$nowTimeStamp = NOW;
		$nowDatetime = strftime('%Y-%m-%d %H:%M:%S', $nowTimeStamp);
		
		try {
			// 個数制限（削除対象取得）
			$GLOBALS['db']->deleteOneTimeTokenByExpire($nowDatetime);
		} catch (Exception $e) {
			vs_log::error(__FILE__, __LINE__, 'expire = [' . $nowDatetime . ']');
			return false;
		}
		
		try {
			// 個数制限（削除対象取得）
			$deleteList = $GLOBALS['db']->getOneTimeTokenDeleteList($user_id, $limit);
		} catch (Exception $e) {
			vs_log::error(__FILE__, __LINE__, 'user_id = [' . $user_id . '], limit = [' . $limit . ']');
			return false;
		}
		
		try {
			$loop = count($deleteList);
			for ($i = 0; $i < $loop; $i++) {
				// 削除
				$GLOBALS['db']->deleteOneTimeToken($deleteList[$i]['token']);
			}
		} catch (Exception $e) {
			vs_log::error(__FILE__, __LINE__, 'token = [' . $deleteList[$i]['token'] . ']');
			return false;
		}
		
		$token = false;
		$duplicationCountMax = 10;
		$duplicationCount = 0;
		while (true) {
			// 重複がN回続いた場合エラー
			if ($duplicationCount == $duplicationCountMax) {
				vs_log::error(__FILE__, __LINE__, '');
				return false;
			}
			
			// トークン生成
			$token = vs_utility::getRandomString($length);
			
			try {
				// 重複チェック
				$ret = $GLOBALS['db']->getOneTimeToken($token);
			} catch (Exception $e) {
				vs_log::error(__FILE__, __LINE__, 'token = [' . $token . ']');
				return false;
			}
			if ($ret === false) {
				break;
			} else {
				$duplicationCount++;
			}
		}
		
		if ($token === false) {
			vs_log::error(__FILE__, __LINE__, '');
			return false;
		}
		
		// 有効期限設定
		$expireTimeStamp = $nowTimeStamp + $expire;
		$expireDatetime = strftime('%Y-%m-%d %H:%M:%S', $expireTimeStamp);
		
		// 登録
		try {
			$temp = array();
			$temp['user_id'] = $user_id;
			$temp['token'] = $token;
			$temp['expire'] = $expireDatetime;
			$GLOBALS['db']->insertOneTimeToken($temp);
		} catch (Exception $e) {
			vs_log::error(__FILE__, __LINE__, 'user_id = [' . $user_id . '], token = [' . $token . '], expireDatetime = [' . $expireDatetime . ']');
			return false;
		}
		
		$data = array();
		$data['token'] = $token;
		$data['expire'] = $expireDatetime;
		
		return $data;
	}
	
	/**
	 * トークン作成
	 * @return string トークンキー
	 */
	public static function createAccessToken()
	{
		Log::debug(__FILE__, __LINE__, 'Dedub::createAccessToken() Start.');
	
		$tokenList = array();
		$tokenData = array();
		try {
			while(true){
				//生成
				$key = UtilityClass::getRandomString(20);
				
				//重複チェック
				
				break;
			}
		}
		catch( AuthException $ae ) {
			throw $ae;
		}
	
		Log::debug(__FILE__, __LINE__, 'Dedub::createAccessToken() End.'.$key);
		return $key;
	}
	
}
