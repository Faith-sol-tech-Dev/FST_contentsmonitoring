<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Monitor\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\MvcEvent;
use Zend\I18n\Translator\Translator;

use Monitor\Model\CommonForm;
use ContentsMonitor\Common\LogClass as Log;
use ContentsMonitor\Common\UtilityClass as Utility;
use ContentsMonitor\Common\MessageClass as MessageClass;
use ContentsMonitor\Service\Auth\AuthClass as AuthClass;

use ContentsMonitor\Service\Data\CornerTable as CornerTable;
use ContentsMonitor\Common\lockClass as lockClass;
use ContentsMonitor\Common\TokenClass;


/**
 * 共通処理コントローラ
 *
 * @package ContentsMonitor
 * @author  FST goto
 * @since   PHP 5.6, Zendframwork 2.4.9
 * @version 1.0.0
 */
class CommonController extends AbstractActionController
{

	public $_config;
	public $_auth;
	public $_comment;
	public $_constform;
	public $_token;
	protected $_translator;

	public function __construct()
	{
	}
	
	public function onBootstrap(MvcEvent $ev)
	{
		//Log::debug(__FILE__, __LINE__, 'BEGIN onDispatch ---------- Application Start ----------');
		return parent::onBootstrap($ev);
	}

	public function onDispatch(MvcEvent $ev)
	{
		//　定義ファイルを読み込み
		require 'vendor/ContentsMonitor/Common/loaclConst.php';
		
		// 設定ファイルの読み込み
		if(empty($this->_config)) {
			$this->_config = $this->getServiceLocator()->get('config');
		}
		if(empty($this->_auth)) {
			$this->_auth = new AuthClass( $this->getServiceLocator() );
		}
		if(empty($this->_comment)) {
			$this->_comment = new MessageClass();
		}
		if(empty($this->_constform)) {
			$this->_constform = new CommonForm();
		}
		if(empty($this->_token)) {
			$this->_token = new TokenClass();
		}

		Log::debug(__FILE__, __LINE__, 'BEGIN onDispatch ---------- Application onDispatch ----------');
		return parent::onDispatch($ev);
	}
	
	/**
	 * Zend Framwork 翻訳オブジェクトを取得
	 * @return object
	 */
	protected function getTranslator() 
	{
		if(!$this->_translator) {
			$this->_translator = $this->getServiceLocator()->get('Translator');
		}
		return $this->_translator;
	}
	
	/**
	 * エラーメッセージ（画面表示用）
	 * 
	 * @return array エラーメッセージリスト
	 */
	public function addErrorComment()
	{
		return $this->_comment->HTTP_ERROR_MESSAGE;
	}

	/**
	 * 操作関連のメッセージ（画面表示用）
	 * 
	 * @return array 操作関連のメッセージリスト
	 */
	public function addDisplayComment()
	{
		return $this->_comment->HTTP_DISPLAY_MESSAGE;
	}
	
	/**
	 * セッションチェック処理
	 * 
	 * @param string $index     ワンタイムトークンで使用している種別
	 * @param string $newindnex ワンタイムトークンで使用する新しい種別
	 */
	public function checkSession()
	{
		$start_time=microtime(true);
		
		$ret = array();
		try {
			if( $this->_auth->checkLogin() ) {

					// トークンチェック [ログイン済（ログイン情報あり）]
				if( $this->_auth->checkToken() ) {
					$ret = array(true, null);
				}
				else
				{
					//未ログイン、セッション情報削除
					Log::error(__FILE__, __LINE__, $this->_comment->HTTP_ERROR_MESSAGE['3']);
					$ret = array(false, 3);
				}
			}
			else {
				//未ログイン、セッション情報削除
				Log::error(__FILE__, __LINE__, $this->_comment->HTTP_ERROR_MESSAGE['2']);
				$ret = array(false, 2);
			}
		}
		catch( \Exception $ae ) {
			$usr_id = (false != ($idt = $this->_auth->getLoginUser())) ?  $idt->user_id : "";
			$login_id = (false != ($idt = $this->_auth->getLoginUser())) ?  $idt->login_id : "";
			$token = (!empty($_SESSION['token'][$index])) ?  $_SESSION['token'][$index] : "";
			$logmsg = sprintf($this->_comment->HTTP_ERROR_MESSAGE['4'], $usr_id, $login_id, $token);
			Log::error(__FILE__, __LINE__, $logmsg);
			$ret = array(false, 4);
		}

        $diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
		Log::debug(__FILE__, __LINE__, 'INFO   checkSession() --  has completed. ('.$diff_time.')');
		return $ret;
	}
	
	/**
	 * セッションの削除
	 * 
	 * @param string $index　ワンタイムトークンで使用している種別
	 */
	public function deleteSession( $index= null )
	{
		$start_time=microtime(true);
		
		$ret = array();
		try {
			$this->_auth->deleteToken( $index );
			$ret = array(true, null);
		}
		catch( \Exception $ae ) {
			$usr_id = (false != ($idt = $this->_auth->getLoginUser())) ?  $idt->user_id : "";
			$login_id = (false != ($idt = $this->_auth->getLoginUser())) ?  $idt->login_id : "";
			$token = (!empty($_SESSION['token'][$index])) ?  $_SESSION['token'][$index] : "";
			$logmsg = sprintf($this->_comment->HTTP_ERROR_MESSAGE['10'], $usr_id, $login_id, $token);
			Log::error(__FILE__, __LINE__, $logmsg);
			$ret = array(false, 10);
		}

        $diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
		Log::debug(__FILE__, __LINE__, 'INFO   deleteSession() --  has completed. ('.$diff_time.')');
		return $ret;
	}

	/**
	 * ページへのアクセス許可チェック
	 * 
	 * @return boolean true:アクセス許可／false:不許可の場合、エラーページへ遷移
	 */
	public function checkUserPriv()
	{
		$start_time=microtime(true);
		
		try{
			$identity = $this->_auth->getLoginUser();
			
			// URLからコーナーIDを抽出
			$cornerId = '';
			$cnrTable = $this->getServiceLocator()->get('ContentsMonitor\Service\Data\CornerTable');
			$cnrRet = $cnrTable->fetchAll();
			$cnrRet->buffer();
			foreach ( $cnrRet as $item ) {
				preg_match($item->url_regx, $_SERVER["REQUEST_URI"], $m);
				if(count($m) > 0) {
					//一致したため、コーナーIDを取得
					$cornerId = $item->corner_id;
					break;
				}
			} 

			Log::debug(__FILE__, __LINE__, '--  cornerid ='.$cornerId);
			Log::debug(__FILE__, __LINE__, '--  user_id ='.$identity->user_id);
				
			// アクセス権限をチェック
			$userPrvTable = $this->getServiceLocator()->get('ContentsMonitor\Service\Data\UserPrivTable');
			$prvRet = $userPrvTable->getUserPriv( $identity->user_id, $cornerId);
			if( 1 == $prvRet->authority ) {
		        $diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
				Log::debug(__FILE__, __LINE__, 'INFO   checkUserPriv() --  has access authority. has completed. ('.$diff_time.')');
				return true;
			}
			else {
				Log::error(__FILE__, __LINE__, 'アクセス権限がありません。user_id='.$identity->user_id);
		        $diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
				Log::debug(__FILE__, __LINE__, 'NOTICE checkUserPriv() --  dont has access authority. has completed. ('.$diff_time.')');
				//アクセス権限なし
				return false;
			}
				
		}
		catch( \DbAccessException $de ) {
			Log::debug(__FILE__, __LINE__, 'ERROR checkUserPriv() --  check failed. ');
			Log::error(__FILE__, __LINE__, $de->getMessage());
			return false; //チェック失敗したため、falseを返却
		}
		catch( \Exception $e ) {
			Log::debug(__FILE__, __LINE__, 'ERROR checkUserPriv() --  check failed. ');
			Log::error(__FILE__, __LINE__, $e->getMessage());
			return false; //チェック失敗したため、falseを返却
		}

        $diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
		Log::debug(__FILE__, __LINE__, 'INFO   checkUserPriv() --  has completed. ('.$diff_time.')');
	}
	
	/**
	 * サービスが選択されているかどうかチェック
	 * 
	 * @return boolean|string 選択されていない場合、falseを返却。選択されている場合は、サービスIDを返却。
	 */
	public function checkService()
	{
		Log::debug(__FILE__, __LINE__, 'INFO   checkService() --  checked session (Service Data).');

		// サービスを選択済みかどうかチェック
		if( empty($_SESSION['service']['current']) ) { return false; }
		if( $_SESSION['service']['current'] == 0 ) { return false; }
		return $_SESSION['service']['current'];
	}

	/**
	 * 選択したサービス情報をセッションに格納
	 * 
	 * @param string $servicecd サービスID
	 */
	public function setService( $servicecd )
	{
		$_SESSION['service']['current'] = $servicecd;
	}

	/**
	 * 選択したサービス情報をセッションから取得
	 *
	 * @param string $servicecd サービスID
	 */
	public function getServicefromSession()
	{
		if(isset($_SESSION['service']['current'])) {
			return $_SESSION['service']['current'];
		} else {
			return "";
		}
	}
	
	/**
	 * ログインユーザ情報を取得
	 * 
	 * @return array ユーザ情報 (user_id, user_name, user_mail, user_auth)
	 */
	public function getUserData()
	{
		$aryUser = array();
		if(false != ($identity = $this->_auth->getLoginUser())) {
			$aryUser = array(
					'user_id' => $identity->user_id,
					'user_name' => $identity->user_name,
					'user_mail' => $identity->user_mail,
					'user_auth' => $identity->user_auth,
					'user_invalid' => $identity->invalid,
			);
		}
		return $aryUser;
	}
	
	/**
	 * Viewに表示する項目をセット（ユーザ情報・設定情報を合わせる）
	 * 
	 * @param array $form　Viewに引き渡すオブジェクト
	 */
	public function display(array $form)
	{
		return array_merge($form, array('userData' => $this->getUserData(), 'const' => $this->_constform, 'v' => $this->_config['v']['v'] ));
	}
	
	/**
	 * コンテンツをロック
	 * 
	 * @param int $content_id 	選択したコンテンツID
	 * @param int $user_id		ログインしたユーザID
	 * @param int $inner_id     選択したコンテンツの内部ID (指定なしの時はNULL）
	 * @return boolean 			成功した場合true
	 */
	public function setContentsLock($content_id, $user_id, $inner_id=null)
	{
		$start_time=microtime(true);
		
		$obj = new lockClass($this->getServiceLocator());
		$ret = $obj->setContentsLock($content_id, $user_id, $inner_id);
		
        $diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
		Log::debug(__FILE__, __LINE__, 'INFO   setContentsLock() --  has completed. ('.$diff_time.')');
		return $ret;
	}
	
	/**
	 * コンテンツのロックを解除(ユーザーがコンテンツ詳細画面でつかんでいるもの)
	 * 
	 * @param int $content_id	選択したコンテンツID
	 * @param int $user_id		ログインしたユーザID
	 * @return boolean 			成功した場合true
	 */
	public function setContentsUnlock($content_id, $user_id)
	{
		$start_time=microtime(true);
		
		$obj = new lockClass($this->getServiceLocator());
		$ret = $obj->setContentsUnlock($content_id, $user_id);
		
        $diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
		Log::debug(__FILE__, __LINE__, 'INFO   setContentsUnlock() --  has completed. ('.$diff_time.')');
		return $ret;
	}
	
	/**
	 * コンテンツのロックを解除(ユーザーがつかんでいるもの全て)
	 * 
	 * @param int $user_id	ログインしたユーザID
	 * @return boolean 		成功した場合true
	 */
	public function setContentsUnlockUserAll($user_id)
	{
		$start_time=microtime(true);
		
		$obj = new lockClass($this->getServiceLocator());
		$ret = $obj->setContentsUnlockUserAll($user_id);
		
        $diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
		Log::debug(__FILE__, __LINE__, 'INFO   setContentsUnlockUserAll() --  has completed. ('.$diff_time.')');
		return $ret;
	}
	
	/**
	 * トークン生成
	 * @return string トークンキー
	 */
	public function createAccessToken()
	{
		$key = $this->_token->createAccessToken();
		return $key;
	}
}
