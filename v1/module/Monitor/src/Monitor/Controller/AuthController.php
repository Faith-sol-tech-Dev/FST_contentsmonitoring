<?php
namespace Monitor\Controller;

use Zend\View\Model\ViewModel;

use Monitor\Model\AuthForm;
use Monitor\Model\AuthFilter;
use ContentsMonitor\Common\LogClass as Log;
use ContentsMonitor\Common\UtilityClass as Utility;
use ContentsMonitor\Exception\AuthExceptionClass as AuthException;
use ContentsMonitor\Exception\DbAccessException as DbAccessException;
use ContentsMonitor\Exception\FormExceptionClass as FormException;

/**
 * ログイン認証コントローラ
 *
 * @package ContentsMonitor
 * @author  FST goto
 * @since   PHP 5.6, Zendframwork 2.4.9
 * @version 1.0.0
 */
class AuthController extends CommonController
{
    public function indexAction()
    {
    	// ログイン画面へリダイレクト
		return( $this->redirect()->toUrl( APP_LOGIN_PATH ) );
    }

	/**
	 * ログイン処理
	 *
	 */
    public function loginAction()
    {
    	Log::debug(__FILE__, __LINE__, 'BEGIN loginAction()');
    	$start_time=microtime(true);
    	// PHP_SELFを利用したXSSおよびHTTPレスポンス分割攻撃への対策
    	$_SERVER['PHP_SELF'] = strtr( @$_SERVER['PHP_SELF'] ,
    			array('<'=>'%3C','>'=>'%3E',"'"=>'%27','"'=>'%22' , "\r" => '' , "\n" => '' ) ) ;
    	Utility::setCookie(CHK_COOKIE_SET);
    	
    	$request = $this->getRequest();
        
        try {
        	
        	// ログイン画面に表示する項目情報を取得
        	$form = new AuthForm();
        	$form->setInputFilter(new AuthFilter());
        	 
        	// ポストでのリクエストの場合
        	if($request->isPost()){
			
        		if(!Utility::isCookie(CHK_COOKIE_SET)) {
        			//クッキーが無効の場合、ログイン画面にもどる
					Log::debug(__FILE__, __LINE__, 'ERROR Utility::isCookie() --  Cookie is　not valid. check falied.');
        			return( $this->redirect()->toUrl( APP_LOGIN_PATH.'?er=12' ) );
        		}
        		
				$form->setData($request->getPost());
	            if ($form->isValid()) {
	
					// パラメータを取得する
					$login_name = $this->params()->fromPost( 'id', "" );
					$login_pass = $this->params()->fromPost( 'password', "" );
					$form->error_no = $this->params()->fromQuery( 'er', "" );
	
					if( empty($login_name) || empty($login_pass) ) {
						// ログイン情報がない場合は、ログイン画面にもどる
						return( $this->redirect()->toUrl( APP_LOGIN_PATH ) );
					}
					else{
						//値チェック
						$aryRet = $form->loginItemChecker(array('id' => $login_name, 'password' => $login_pass));
						if( false === $aryRet) {
					    	Log::debug(__FILE__, __LINE__, 'ERROR loginItemChecker() --  Login Data is not valid. check falied.');
							return( $this->redirect()->toUrl( APP_LOGIN_PATH.'?er=1' ) );
						}
						elseif( false === $aryRet[0] ) {
					    	Log::debug(__FILE__, __LINE__, 'ERROR loginItemChecker() --  Login Data is not valid. check falied.');
							Log::error(__FILE__, __LINE__, 'ERROR loginErr - ERR_MESSAGE='.$aryRet[1]);
							return( $this->redirect()->toUrl( APP_LOGIN_PATH.'?er=1' ) );
						}
						
						//ログインチェック
						if( $this->_auth->login( $login_name, $login_pass ) ){
							Log::debug(__FILE__, __LINE__, 'INFO   login() successed.');
							
							// トークン生成
							$this->_auth->createToken();
							// 会員画面へリダイレクト
							return( $this->redirect()->toUrl( APP_HOME_PATH ) );
						}
						else{
						  // ログイン画面へリダイレクト
						  return( $this->redirect()->toUrl( APP_LOGIN_PATH.'?er=1' ) );
						}
					}
				} //EOF -- if ($form->isValid()) 
			}
			else {
	
			}
	
			//テンプレートセット
			$this->layout('layout/cm_login_layout');
			return array('form' => $form, 'error' => $this->addErrorComment());
        }
        catch( \DbAccessException $de )
        {	// DB処理エラー （エラーページを表示）
			Log::error(__FILE__, __LINE__, 'Dedub::loginErr -'.$de->getMessage());
    		Log::error(__FILE__, __LINE__, $de);
			return( $this->redirect()->toUrl( APP_ERROR_PATH.'?er=500' ) );
    	}
        catch( \AuthException $ae )
        {	// 認証系処理エラー （エラーページを表示）
			Log::error(__FILE__, __LINE__, 'Dedub::loginErr -'.$ae->getMessage());
    		Log::error(__FILE__, __LINE__, $ae);
			return( $this->redirect()->toUrl( APP_ERROR_PATH.'?er=500' ) );
        }
        catch( \FormException $fe )
        {	// データ処理エラー （エラーページを表示）
			Log::error(__FILE__, __LINE__, 'Dedub::loginErr -'.$fe->getMessage());
    		Log::error(__FILE__, __LINE__, $fe);
			return( $this->redirect()->toUrl( APP_ERROR_PATH.'?er=500' ) );
        }
        catch( \Exception $e )
        {	// 通常エラー （エラーページを表示）
			Log::error(__FILE__, __LINE__, 'Dedub::loginErr -'.$e->getMessage());
    		Log::error(__FILE__, __LINE__, $e);
			return( $this->redirect()->toUrl( APP_ERROR_PATH.'?er=500' ) );
        }
        finally {
        	$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
        	Log::debug(__FILE__, __LINE__, 'END loginAction() -- ('.$diff_time.')');
        }
    }

	/**
	 * ログアウト処理
	 *
	 */
	public function logoutAction()
	{
    	Log::debug(__FILE__, __LINE__, 'BEGIN logoutAction()');
    	
    	//ロック解除
    	$aryUser = $this->getUserData();
    	$ret = $this->setContentsUnlockUserAll($aryUser['user_id']);

		// ログアウト処理
		$this->_auth->logout();

		//テンプレートセット
		$this->layout('layout/cm_login_layout');
		return new ViewModel();
	}
}
