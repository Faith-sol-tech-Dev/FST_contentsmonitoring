<?php
namespace Monitor\Controller;

use Zend\View\Model\ViewModel;
use Zend\Json\Json;

use Monitor\Model\ServiceForm;
use Monitor\Model\ContentForm;
use ContentsMonitor\Common\LogClass as Log;
use ContentsMonitor\Common\UtilityClass as Utility;
use ContentsMonitor\Exception\AuthExceptionClass as AuthException;
use ContentsMonitor\Exception\DbAccessException as DbAccessException;
use ContentsMonitor\Exception\FormExceptionClass as FormException;


/**
 * HOME画面コントローラ
 *
 * @package ContentsMonitor
 * @author  FST goto
 * @since   PHP 5.6, Zendframwork 2.4.9
 * @version 1.0.0
 */
class IndexController extends CommonController
{
	/**
	 * コンテンツのHOME画面 
	 * URL:/monitor/
	 * 
	 * @return \Zend\View\Model\ViewModel
	 */
	public function indexAction()
    {
    	Log::debug(__FILE__, __LINE__, 'BEGIN indexAction()');
    	$start_time=microtime(true);
    	 
    	try {

    		$param = $this->params()->fromQuery('formvalue');
    		$none_msg = $this->params()->fromQuery('s_none');
    		
	    	// セッションチェック処理
    		$ret = $this->checkSession();
    		if(false == $ret) { 
    			//処理エラーのため、500エラーを表示
    			return( $this->redirect()->toUrl( APP_ERROR_PATH.'?er=500' ) );
    		}
	    	if(false == $ret[0]) {
	    		$errno = $ret[1];
	    		return( $this->redirect()->toUrl( APP_ERROR_PATH.'?er='.$errno ) );
	    	}
			// アクセス権限チェック処理
			if( !$this->checkUserPriv() ) { 
	    		return( $this->redirect()->toUrl( APP_ERROR_PATH.'?er=11' ) );
			}
			
			// EOF --(共通処理[セッションチェック])
			//----------------------------------------------------------------------------------------------
	    	
	    	//ユーザ情報取得
	    	$aryUser = $this->getUserData();

	    	$alert = array();
	    	
	    	// サービス情報を取得
	    	$form = new ServiceForm($this->getServiceLocator());
	    	$form->getServiceListData();  // サービス情報一覧
	    	if(empty($form->service_list)) {
	    		$alert['2'] =  $this->addDisplayComment()['s_noset'];
	    	}
	    	else {
	    	
		    	// 現在選択しているサービスを取得
		    	if( false != ($service_cd = $this->checkService()) ) {
		    		$form->getServiceData( $service_cd );
		    	}
		    	if( 's_none' == $none_msg ) {
		    		$alert['1'] = $this->addDisplayComment()['s_none'];
		    	}
				// ユーザ権限（編集権限）で処理分け
		    	if(1 == $aryUser['user_auth']) {
		    		//一般
		    		$form->getServiceToUserData($aryUser['user_id']);
		    		if(empty($form->service_list)) {
			    		$alert['2'] =  $this->addDisplayComment()['s_noset'];
		    		}
		    		else {
		    			$select_srv = $this->params()->fromPost( 'service_list', "" );
		    			if( !empty($select_srv) && $select_srv!=0 )
		    			{
		    				$this->setService( $select_srv );
		    				$form->getServiceData( $select_srv );
		    			}
		    			else {
		    				$select_srv = $this->getServicefromSession();
		    				if( !empty($select_srv) && $select_srv!=0 )
		    				{
		    					$this->setService( $select_srv );
		    					$form->getServiceData( $select_srv );
		    				}
		    				else {
			    				foreach($form->service_list as $key => $item) {
			    					$this->setService( $key );
			    					$form->getServiceData( $key );
			    					//複数紐づいている場合は、先頭のサービスをセット。
			    					break;
			    				}
		    				}
		    			}
		    		}
		    	}
		    	elseif(2== $aryUser['user_auth']) {
		    		//管理
		    		$request = $this->getRequest();
			    	if($request->isPost()){
			    		// サービス情報を取得・セッション格納
			    		$select_srv = $this->params()->fromPost( 'service_list', "" );
			    		if( !empty($select_srv) && $select_srv!=0 )
			    		{ 
			    			$this->setService( $select_srv ); 
			    			$form->getServiceData( $select_srv );
			    		}
			    		else {
			    			$select_srv = $this->getServicefromSession();
			    			if( !empty($select_srv) && $select_srv!=0 )
			    			{
			    				$this->setService( $select_srv );
			    				$form->getServiceData( $select_srv );
			    			}
			    		}
			    	}
		    	}
	    	}

	    	// ロック全解除
	    	if(!$this->setContentsUnlockUserAll($aryUser['user_id'])){
	    		Log::error(__FILE__, __LINE__, 'INFO   IndexController() -- ロック解除に失敗しました。(/monitor/index/unlock/)');
	    	}
	    	
	    	$cform = new ContentForm($this->getServiceLocator());
	    	$count = $cform->getMonitoringCount();
	    	
			//テンプレートセット
			$this->layout('layout/cm_main_layout');
			return $this->display(array('form' => $form, 'alert' => $alert, 'user_auth' => $aryUser['user_auth'], 'rpo_count' => $count ));

    	}
        catch( \DbAccessException $de )
        {	// DB処理エラー （エラーページを表示）
        	Log::error(__FILE__, __LINE__, $de->getMessage());
    		return( $this->redirect()->toUrl( APP_ERROR_PATH.'?er=500' ) );
        }
        catch( \AuthException $ae )
        {	// 認証系処理エラー （エラーページを表示）
    		Log::error(__FILE__, __LINE__, $ae->getMessage());
    		return( $this->redirect()->toUrl( APP_ERROR_PATH.'?er=500' ) );
        }
        catch( \FormException $fe )
        {	// データ処理エラー （エラーページを表示）
    		Log::error(__FILE__, __LINE__, $fe->getMessage());
    		return( $this->redirect()->toUrl( APP_ERROR_PATH.'?er=500' ) );
        }
        catch( \Exception $e )
        {	// 通常エラー （エラーページを表示）
    		Log::error(__FILE__, __LINE__, $e->getMessage());
    		return( $this->redirect()->toUrl( APP_ERROR_PATH.'?er=500' ) );
        }
	    finally 
        {
        	$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
        	Log::debug(__FILE__, __LINE__, 'END indexAction() -- ('.$diff_time.')');
        }
	}
    
	/**
	 * コンテンツのロック解除
	 * URL:/monitor/index/unlock/
	 * 
	 * @return \Zend\Http\Response
	 */
    public function unlockAction()
    {
    	Log::debug(__FILE__, __LINE__, 'BEGIN unlockAction()');
    	$start_time=microtime(true);
    	 
    	try {
    		$user_id = $this->params()->fromPost( 'user_id', "" );
    		$aryUser = $this->getUserData();
    		
    		//ロック解除
    		$ret = $this->setContentsUnlockUserAll($user_id);

    		//正常に終了したか判断
    		if($ret == true){
    			Log::debug(__FILE__, __LINE__, 'INFO   unlockAction() --  Contents unlock successed.');
    		}else{
    			Log::error(__FILE__, __LINE__, 'INFO   unlockAction() --  ロック解除に失敗しました。(/monitor/index/unlock/)');
    		}
    
    	}
    	catch( \DbAccessException $de )
    	{	// DB処理エラー （エラーページを表示）
    		Log::error(__FILE__, __LINE__, $de->getMessage());
    	}
    	catch( \AuthException $ae )
    	{	// 認証系処理エラー （エラーページを表示）
    		Log::error(__FILE__, __LINE__, $ade->getMessage());
    	}
    	catch( \FormException $fe )
    	{	// データ処理エラー （エラーページを表示）
    		Log::error(__FILE__, __LINE__, $fe->getMessage());
    	}
    	catch( \Exception $e )
    	{	// 通常エラー （エラーページを表示）
    		Log::error(__FILE__, __LINE__, $e->getMessage());
    	}
    	finally {
    		$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    		Log::debug(__FILE__, __LINE__, 'END unlockAction() -- ('.$diff_time.')');
    		
    		$responseArray = array();
    		$responseArray['resultMessage'] = $ret;
    		$response = $this->getResponse();
    		$response->getHeaders()->addHeaders(array('Content-type' => 'application/json; charset=UTF-8'));
    		$response->setContent(Json::encode($responseArray));
    	}
    	return $response;
    }

}
