<?php
namespace ContentsMonitor\Service\Auth;

use Zend\Authentication\Adapter\DbTable as AuthAdapter;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Storage\Session as SessionStorage;
use Zend\Config\Config as Config;

use ContentsMonitor\Common\TokenClass as Token;
use ContentsMonitor\Service\Data\UserTable as UserTable;
use ContentsMonitor\Common\LogClass as Log;
use ContentsMonitor\Common\UtilityClass as Utility;
use ContentsMonitor\Common\MessageClass as Message;
use ContentsMonitor\Exception\AuthExceptionClass as AuthException;
use ContentsMonitor\Exception\DbAccessExceptionClass as DbAccessException;


/**
 * 監視サイトで使用する認証処理
 *
 * @package ContentsMonitor
 * @author  FST goto
 * @since   PHP 5.6, Zendframwork 2.4.9
 * @version 1.0.0
 */
class AuthClass
{
	/**
	 * サービスロケータ
	 * @var $service_locator
	 */
	public $service_locator;

	/**
	 * 認証オブジェクト
	 * @var $auth
	 */
	protected $auth;

	/**
	 * コンストラクタ
	 * @param AuthenticationService $service_locator  サービスロケートオブジェクト
	 */
	public function __construct( $service_locator )
	{
		// MVC巡廻のアプリ情報を取得する(コントローラ）
		$this->service_locator = $service_locator;

		// 認証情報を取得する
		$this->auth = new AuthenticationService( new SessionStorage( 'session_name_space' ) );
		Log::debug(__FILE__, __LINE__, 'BEGIN AuthClass/__construct() --  create AuthClass object.');
	}

	/**
	 * ログイン
	 * @param  string $key   ログインキーID
	 * @param  string $pass  ログインパスワード
	 * @return bool 		 認証チェック結果（true:OK ／ false:NG）
	 */
	public function login( $key, $pass )
	{
		try{

			// DBアダプタの取得
			$dbAdapter = $this->service_locator->get('Zend\Db\Adapter\Adapter');
	
			// 認証アダプターを作成
			$authAdapter = new AuthAdapter( $dbAdapter );
	
			// 認証項目内容を設定
			$authAdapter
			  // 検索するテーブル名
			  ->setTableName('MST_USER')
	
			  // 認証対象カラム
			  ->setIdentityColumn( 'login_id' )
	
			  // 認証パスワードカラム
			  ->setCredentialColumn( 'login_pass' );
	
			// デリートフラグが落ちている事も認証条件に加える
			$select = $authAdapter->getDbSelect();
			$select->where("invalid = 1");
	
			// フォームからの入力値をセットする
			//----------------------
			// パスワード暗号化(SHA-2)
			//----------------------
			$hpass = hash("sha256",$pass);
			$authAdapter
			  // 認証対象値
			  ->setIdentity( $key )
	
			  // 認証パスワード
			  ->setCredential( $hpass );
	
			// 認証クエリを実行し、認証結果を保存する
			$result = $this->auth->authenticate( $authAdapter );
	
			// 認証成功(isValid()で認証結果のチェック）
			if( $result->isValid() ){
				Log::debug(__FILE__, __LINE__, 'INFO   login() --  login data was valid.');
				
				// 認証ストレージを取得する
				$storage = $this->auth->getStorage();
	
				// 結果オブジェクトをストレージに書き込む
				$clm_toReturn = array( 'user_id', 'user_name', 'user_mail', 'login_id', 'user_auth', 'invalid' );
				$storage->write( $authAdapter->getResultRowObject($clm_toReturn) );
	
				return true;
			}
			// 認証失敗
			else{
				Log::debug(__FILE__, __LINE__, 'NOTICE login() --  login data was not valid. login failed.');
				return false;
			}
		}
		catch( \Exception $e) {
			Log::error(__FILE__, __LINE__, $e->getMessage());
			throw new AuthException();
		}
	}

	/**
	 * ログアウト
	 * @param 
	 * @return 
	 */
	public function logout()
	{
		// ユーザ情報を取得
		$identity = $this->getLoginUser();
		// トークンのインデックスキーを作成
		$config = new Config(include CONF_PATH);
		$index = sprintf($config->token->key, $identity->login_id);
		// トークン削除
		$this->deleteToken($index);
		// ストレージと認証情報を破棄する
		$this->auth->getStorage()->clear();
		$this->auth->clearIdentity();
		// セッション情報を破棄
		$_SESSION = array();
		if (isset($_COOKIE[session_name()])) {
			setcookie(session_name(), '', time()-42000, '/');
		}
		session_destroy();
		Log::debug(__FILE__, __LINE__, 'INFO   logout() --  let go User Data. ...logout...');
	}

	/**
	 * ログイン状態を確認(ログイン状態ならログイン情報を返却）
	 * @param 
	 * @return bool ログイン状態（true:ログイン済み／false：未ログイン）
	 */
	public function checkLogin()
	{
		// ログイン確認
		if( $this->auth->hasIdentity() ){
			Log::debug(__FILE__, __LINE__, 'INFO   checkLogin() --  have User Data. checked true.');
			return true;
		}
		Log::debug(__FILE__, __LINE__, 'WARN   checkLogin() --  dont have User Data. checked false.');
		return false;
	}

	/**
	 * ログインユーザの情報を取得
	 * @param 
	 * @return $identity array ユーザ情報／ ユーザ情報がない場合、falseを返却
	 */
	public function getLoginUser()
	{
		// ログイン確認
		if( $this->auth->hasIdentity() ){
			// ログイン情報を取得する
			$identity = $this->auth->getIdentity();
			Log::debug(__FILE__, __LINE__, 'INFO   getLoginUser() --  get User Data.');
			return $identity;
		}
		Log::debug(__FILE__, __LINE__, 'WARN   getLoginUser() --  dont get User Data.');
		return false;
	}

	/**
	 * CSRFを確認（ワンタイムトークンの確認）
	 * 
	 * @param string $index ワンタイムトークンの種別
	 * @return bool チェック状態（true:正常動作／false：異常動作）
	 */
	public function checkToken( $index=null )
	{
		$start_time=microtime(true);
		
		try {
			$message = new Message();

			// ユーザ情報を取得
			$identity = $this->getLoginUser();
			if( !$identity ) { throw new AuthException( $message->HTTP_ERROR_MESSAGE['5'] ); }
			if( empty($identity->user_id) ) { throw new AuthException( $message->HTTP_ERROR_MESSAGE['6'] ); }
			Log::debug(__FILE__, __LINE__, 'DEBUG ユーザ情報の存在チェック、OK');
			
			// トークンのインデックスキーを作成
			$config = new Config(include CONF_PATH);
			$index = sprintf($config->token->key, $identity->login_id);
				
			// DBからトークン情報を取得
			$aryData = array( 
							  'user_id'  => $identity->user_id,
							  'index_key' => $index,
						  );
			$userTable = $this->service_locator->get('ContentsMonitor\Service\Data\UserTable');
			$arySaveToken = $userTable->getUserToToken( $aryData );
			if( empty($arySaveToken) ) { throw new AuthException( $message->HTTP_ERROR_MESSAGE['3'] ); }
			if( empty($arySaveToken['token']) ) { throw new AuthException( $message->HTTP_ERROR_MESSAGE['3'] ); }
			Log::debug(__FILE__, __LINE__, 'DEBUG トークン情報の存在チェック、OK');
			
			// トークンをチェック
			if( !Token::checkOneTimeToken($index, $arySaveToken['token'], $arySaveToken['token_expire']) ) {
				// トークンがNGの場合
				// DBからトークン情報を削除
				$aryData = array(
						'login_id'  => $identity->login_id,
						'index_key' => $index,
				);
				$userTable->deleteUserToToken( $aryData );
				throw new AuthException( $message->HTTP_ERROR_MESSAGE['8'] );
			}
			
			// トークンの有効期限を延長
			$this->updateTokenExpire($identity->login_id, $index);
			
		}
		catch( \DbAccessException $de ) {
			Log::debug(__FILE__, __LINE__, 'ERROR checkToken() --  Token Data check failed. checked false.');
			Log::error(__FILE__, __LINE__, $de->getMessage());
			throw new Exception(); //上位へスロー
		}
		catch( \AuthException $ae) {
			Log::debug(__FILE__, __LINE__, 'ERROR checkToken() --  Token Data check failed. checked false.');
			Log::error(__FILE__, __LINE__, $ae->getMessage());
			return false; //チェック失敗したため、falseを返却
		}
		catch( \Exception $e ) {
			Log::debug(__FILE__, __LINE__, 'ERROR checkToken() --  Token Data check failed. checked false.');
			Log::error(__FILE__, __LINE__, $e->getMessage());
			throw $e; //上位へスロー
		}

        $diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
		Log::debug(__FILE__, __LINE__, 'INFO   checkToken() --  have Token Data valid. checked true. ('.$diff_time.')');
		return true;
	}

	/**
	 * ワンタイムトークンを作成
	 * @param string $index　ワンタイムトークンの種別
	 * @throws Exception
	 */
	public function createToken()
	{
		$start_time=microtime(true);
		
		try {
			$message = new Message();

			$ratestTkn = Token::createOneTimeToken();
			Log::debug(__FILE__, __LINE__, 'DEBUG トークン情報生成');

			$identity = $this->getLoginUser();
			if( empty( $identity->login_id ) ) { throw new AuthException( $message->HTTP_ERROR_MESSAGE['7'] ); }
			Log::debug(__FILE__, __LINE__, 'DEBUG ユーザ情報の存在チェック、OK');

			// トークンのインデックスキーを作成
			$config = new Config(include CONF_PATH);
			$index = sprintf($config->token->key, $identity->login_id);
				
			//トークン情報をDBに保管
			$aryData = array( 
							  'login_id'  => $identity->login_id,
							  'index_key' => $index,
							  'token'     => $ratestTkn['key'],
							  'token_expire' => $ratestTkn['expire'],
							  'insert_date'  => null,
							  'user_id'   => $identity->user_id,
					  );
			$userTable = $this->service_locator->get('ContentsMonitor\Service\Data\UserTable');
			$userTable->insertUserToToken( $aryData );
				
		}
		catch( \DbAccessException $de ) {
			Log::debug(__FILE__, __LINE__, 'ERROR createToken() --  failed create Token Data.');
			Log::error(__FILE__, __LINE__, $de->getMessage());
			throw new Exception(); //上位へスロー
		}
		catch( \AuthException $ae) {
			Log::debug(__FILE__, __LINE__, 'ERROR createToken() --  failed create Token Data.');
			Log::error(__FILE__, __LINE__, $ae->getMessage());
			return false; //チェック失敗したため、falseを返却
		}
		catch( \Exception $e ) {
			Log::debug(__FILE__, __LINE__, 'ERROR createToken() --  failed create Token Data.');
			Log::error(__FILE__, __LINE__, $e->getMessage());
			throw $e; //上位へスロー
		}
		
        $diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
		Log::debug(__FILE__, __LINE__, 'INFO   createToken() --  successed create Token Data. ('.$diff_time.')');
	}

	/**
	 * ワンタイムトークンを削除
	 * @param string $index ワンタイムトークンの種別
	 */
	public function deleteToken( $index=null )
	{
		// ユーザ情報を取得
		$identity = $this->getLoginUser();

		// DBからトークン情報を削除
		$aryData = array(
				'login_id'  => $identity->login_id,
				'index_key' => $index,
		);
		$userTable = $this->service_locator->get('ContentsMonitor\Service\Data\UserTable');
		$userTable->deleteUserToToken( $aryData );
		Token::deleteOneTimeToken( $index );
		
		Log::debug(__FILE__, __LINE__, 'INFO   deleteToken() --  let go Access Onetime Token.');
	}

	/**
	 * ワンタイムトークンの使用期限を延長
	 * @param string $index ワンタイムトークンの種別
	 */
	public function updateTokenExpire( $login_id, $index )
	{
		// DBからトークン情報を削除
		$aryData = array(
				'login_id'  => $login_id,
				'index_key' => $index,
				'token_expire' => Token::createOneTimeTokenExpire()
		);
		$userTable = $this->service_locator->get('ContentsMonitor\Service\Data\UserTable');
		$userTable->updateUserToTokenExpire( $aryData );
	
		Log::debug(__FILE__, __LINE__, 'INFO   updateTokenExpire() --  successed update for token expire.');
	}
	
}
