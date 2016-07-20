<?php
namespace Monitor\Controller;

use Zend\View\Model\ViewModel;

use Monitor\Model\ServiceForm;
use ContentsMonitor\Common\LogClass as Log;
use ContentsMonitor\Exception\AuthExceptionClass as AuthException;
use ContentsMonitor\Exception\DbAccessException as DbAccessException;
use ContentsMonitor\Exception\FormExceptionClass as FormException;


/**
 * ユーザ閲覧用コントローラ
 *
 * @package ContentsMonitor
 * @author  FST goto
 * @since   PHP 5.6, Zendframwork 2.4.9
 * @version 1.0.0
 */
class UserController extends CommonController
{
    public function indexAction()
    {
        try {
	    	// セッションチェック処理
	    	$this->checkSession( 'userLogin' , 'userIndex' );
			// アクセス権限チェック処理
			$this->checkUserPriv();
	    	
	    	// ユーザ情報取得
	    	$aryUser = $this->getUserData();
	    	
	    	// フォームデータ
	    	$form;
	
	    	// リクエスト情報を取得
	    	$request = $this->getRequest();
	    	if($request->isPost()){
	    	}
	
			//テンプレートセット
			$this->layout('layout/cm_main_layout');
			return array('form' => $form, 'userData' => $aryUser, 'const' => $this->_constform);
		}
		catch( \DbAccessException $de )
		{	// DB処理エラー （エラーページを表示）
			$this->layout('layout/cm_error_auth_layout');
			return new ViewModel(array('error' => array()));
		}
		catch( \AuthException $ae )
		{	// 認証系処理エラー （エラーページを表示）
			$this->layout('layout/cm_error_auth_layout');
			return new ViewModel(array('error' => array()));
		}
		catch( \FormException $fe )
		{	// データ処理エラー （エラーページを表示）
			$this->layout('layout/cm_error_layout');
			return new ViewModel(array('error' => array()));
		}
		catch( \Exception $e )
		{	// 通常エラー （エラーページを表示）
			$this->layout('layout/cm_error_layout');
			return new ViewModel(array('error' => array()));
		}
		
    }

    
    public function resultAction()
    {
    	
    }
    
    public function registAction()
    {
    	
    }

    public function updateAction()
    {
    	
    }

}
