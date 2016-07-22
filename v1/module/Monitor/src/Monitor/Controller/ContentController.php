<?php
namespace Monitor\Controller;

use Zend\View\Model\ViewModel;
use Zend\Json\Json;
use Zend\Http\Client;
use Zend\Http\Client\Adapter\Socket;
use Zend\Http\Client\Adapter\Curl;

use Monitor\Model\ServiceForm;
use Monitor\Model\ContentForm;
use ContentsMonitor\Common\LogClass as Log;
use ContentsMonitor\Common\UtilityClass as Utility;
use ContentsMonitor\Exception\AuthExceptionClass as AuthException;
use ContentsMonitor\Exception\DbAccessException as DbAccessException;
use ContentsMonitor\Exception\FormExceptionClass as FormException;
use SimpleXMLElement;
use DOMDocument;


/**
 * コンテンツ閲覧用コントローラ
 *
 * @package ContentsMonitor
 * @author  FST goto
 * @since   PHP 5.6, Zendframwork 2.4.9
 * @version 1.0.0
 */
class ContentController extends CommonController
{
    public function indexAction()
    {	// /contents/search/にリダイレクト
    	return( $this->redirect()->toUrl( APP_CONTENTS_SEARCH_PATH ) );
	}

	/**
	 * コンテンツの検索画面 初期設定
	 * URL:/monitor/content/search/
	 * 
	 * @return \Zend\View\Model\ViewModel
	 */
    public function searchAction()
    {
    	Log::debug(__FILE__, __LINE__, 'BEGIN searchAction()');
    	$start_time=microtime(true);
    	 
        try {
    		$param = $this->params()->fromQuery('formvalue');

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
				$this->layout('layout/cm_accessck_layout');
				return new ViewModel(array('error_message' => $this->addErrorComment()['11']));
			}
			// サービス選択状況チェック
	    	if( false == ($service_cd = $this->checkService()) ) {
				return( $this->redirect()->toUrl( APP_HOME_PATH.'?er=s_none' ) );
			}
			
			// EOF --(共通処理)
			//----------------------------------------------------------------------------------------------
	    	
	    	// サービス情報を取得
			$sform = new ServiceForm($this->getServiceLocator());
    		$this->setService( $service_cd ); 
    		$sform->getServiceData( $service_cd );

    		//　表示する検索項目を設定
    		$cform = new ContentForm($this->getServiceLocator());
    		
    		// PostBack情報	
	    	$request = $this->getRequest();
	    	if($request->isPost()){
	    		
	    		//この画面からPOSTはされない、そのため、POSTされた場合は、検索結果に強制リダイレクト
	    		return( $this->redirect()->toUrl( APP_CONTENTS_SEARCH_PATH ) );
	    	}
	

	    	//テンプレートセット
			$this->layout('layout/cm_main_layout');
			return $this->display(array('sform' => $sform, 'cform' => $cform));

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
        	Log::debug(__FILE__, __LINE__, 'END searchAction() -- ('.$diff_time.')');
        }
    }
    
	/**
	 * コンテンツの検索画面  検索結果
	 * URL:/monitor/content/result/
	 * 
	 * @return \Zend\View\Model\ViewModel
	 */
    public function resultAction()
    {
    	Log::debug(__FILE__, __LINE__, 'BEGIN resultAction()');
    	$start_time=microtime(true);
    	 
        try {
    		$param = $this->params()->fromQuery('formvalue');

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
				$this->layout('layout/cm_accessck_layout');
				return new ViewModel(array('error_message' => $this->addErrorComment()['11']));
			}
			// サービス選択状況チェック
	    	if( false == ($service_cd = $this->checkService()) ) {
				return( $this->redirect()->toUrl( APP_HOME_PATH.'?er=s_none' ) );
			}
					
			// EOF --(共通処理)
			//----------------------------------------------------------------------------------------------
	    	
	    	// サービス情報を取得
			$sform = new ServiceForm($this->getServiceLocator());
    		$this->setService( $service_cd ); 
    		$sform->getServiceData( $service_cd );
    		
    		//　表示する検索項目を設定
    		$cform = new ContentForm($this->getServiceLocator());
    		
    		$pagination;
    		$search_msg = "";
    		$msg_none = "";
	    	$request = $this->getRequest();
	    	if($request->isPost()){
	    		// 検索結果情報を取得
	    		if( 'selectItem'==$param ) {
	    			// 検索ボタンクリック時
		    		$aryData = array(
		    					'service_id'      => $service_cd,
		    					'contents_type'   => $this->params()->fromPost( 'content_list_type', "" ),
		    					'check_state1'    => $this->params()->fromPost( 'content_list_mode1', "" ),
		    					'check_state2'    => $this->params()->fromPost( 'content_list_mode2', "" ),
		    					'check_state3'    => $this->params()->fromPost( 'content_list_mode3', "" ),
		    					'check_result1'   => $this->params()->fromPost( 'content_list_stats1', "" ),
		    					'check_result2'   => $this->params()->fromPost( 'content_list_stats2', "" ),
		    					'check_result3'   => $this->params()->fromPost( 'content_list_stats3', "" ),
		    					'import_date_min' => $this->params()->fromPost( 'content_text_impDate_str', "" ),
		    					'import_date_max' => $this->params()->fromPost( 'content_text_impDate_end', "" ),
		    					'check_date_min'  => $this->params()->fromPost( 'content_text_ckDate_str', "" ),
		    					'check_date_max'  => $this->params()->fromPost( 'content_text_ckDate_end', "" ),
		    					'display_cnt'     => $this->params()->fromPost( 'content_list_display_cnt', "" ),
		    					'page_no'         => $this->params()->fromPost( 'p',"")
		    				);
	    		}
	    		elseif( 'pagination'==$param ) {
	    			// ページャリンク クリック時
	    			$aryData = array(
		    					'service_id'      => $service_cd,
	    						'contents_type'   => $this->params()->fromPost( 'hd_content_list_type', "" ),
	    						'check_state1'    => $this->params()->fromPost( 'hd_content_list_mode1', "" ),
	    						'check_state2'    => $this->params()->fromPost( 'hd_content_list_mode2', "" ),
	    						'check_state3'    => $this->params()->fromPost( 'hd_content_list_mode3', "" ),
	    						'check_result1'   => $this->params()->fromPost( 'hd_content_list_stats1', "" ),
	    						'check_result2'   => $this->params()->fromPost( 'hd_content_list_stats2', "" ),
	    						'check_result3'   => $this->params()->fromPost( 'hd_content_list_stats3', "" ),
	    						'import_date_min' => $this->params()->fromPost( 'hd_content_text_impDate_str', "" ),
	    						'import_date_max' => $this->params()->fromPost( 'hd_content_text_impDate_end', "" ),
	    						'check_date_min'  => $this->params()->fromPost( 'hd_content_text_ckDate_str', "" ),
	    						'check_date_max'  => $this->params()->fromPost( 'hd_content_text_ckDate_end', "" ),
	    						'display_cnt'     => $this->params()->fromPost( 'hd_content_list_display_cnt', "" ),
	    						'page_no'         => $this->params()->fromPost('p',"")
	    			);
	    		}
	    		else{
	    		}
	    		// 検索入力情報がある場合
	    		if(!empty($aryData)) {
	    			// バリデーションチェック
		    		$aryRet = $cform->searchItemChecker( $aryData );
		    		if( false === $aryRet) {
		    			// チェックエラー
		    			$search_msg = $this->addErrorComment()['101'];
		    		}
		    		elseif( false === $aryRet[0] ) {
		    			// チェックエラー
		    			$search_msg = $aryRet[1];
		    		}
		    		elseif( true === $aryRet ) {
		    			// 検索処理
		    			$cform->searchItem( $aryData );
		    		}
		    		
		    		//コンテンツIDリスト取得
		    		$aryIdList = $cform->searchItemIdList( $aryData );
		    		if(!empty($aryIdList)) {
			    		$_SESSION['search_item'] = array();
			    		foreach ($aryIdList as $list){
			    			array_push($_SESSION['search_item'], $list["contents_id"]);
			    		}
		    		}
		    		
		    		// POSTデータをセット
		    		$cform->current_list_type = $aryData['contents_type'];
		    		$cform->current_list_mode1 = $aryData['check_state1'];
		    		$cform->current_list_mode2 = $aryData['check_state2'];
		    		$cform->current_list_mode3 = $aryData['check_state3'];
		    		$cform->current_list_stats1 = $aryData['check_result1'];
		    		$cform->current_list_stats2 = $aryData['check_result2'];
		    		$cform->current_list_stats3 = $aryData['check_result3'];
		    		$cform->current_text_impDate_str = $aryData['import_date_min'];
		    		$cform->current_text_impDate_end = $aryData['import_date_max'];
		    		$cform->current_text_ckDate_str = $aryData['check_date_min'];
		    		$cform->current_text_ckDate_end = $aryData['check_date_max'];
		    		if( 'selectItem'==$param ) {
		    			if(!empty($this->params()->fromPost( 'content_list_display_cnt', "" ))) {
		    				$cform->current_list_display_cnt = $cform->content_list_display_cnt[$this->params()->fromPost( 'content_list_display_cnt', "" )];
		    			} else {
		    				$cform->current_list_display_cnt = "";
		    			}
		    			$cform->current_page_no = $aryData['page_no'];
		    		}
		    		elseif( 'pagination'==$param ) {
		    			$cform->current_list_display_cnt = $this->params()->fromPost( 'hd_content_list_display_cnt', "" );
		    			$cform->current_page_no = $aryData['page_no'];
		    		}
		    		// ページャ設定
		    		$pagination = $cform->setPagination($aryData['display_cnt']);

	    		} //EOF --　if(!empty($aryData)) {
	    	}
	    	else {
	    		// ポストでのリクエストでない場合は、検索を行わない
	    		$msg_none = $this->_comment->HTTP_DISPLAY_MESSAGE['content_result_no_post'];
	    	}
	    	
			//テンプレートセット
			$this->layout('layout/cm_main_layout');
			return $this->display(array('sform' => $sform, 'cform' => $cform, 'search_msg' => $search_msg, 'display_msg' => $msg_none, 'pagination' => $pagination));

    	}
        catch( \DbAccessException $de)
        {	// DB処理エラー （エラーページを表示）
        	Log::error(__FILE__, __LINE__, $de->getMessage());
    		return( $this->redirect()->toUrl( APP_ERROR_PATH.'?er=500' ) );
        }
        catch( \AuthException $ae)
        {	// 認証系処理エラー （エラーページを表示）
        	Log::error(__FILE__, __LINE__, $ae->getMessage());
    		return( $this->redirect()->toUrl( APP_ERROR_PATH.'?er=500' ) );
        }
        catch( \FormException $fe)
        {	// データ処理エラー （エラーページを表示）
        	Log::error(__FILE__, __LINE__, $fe->getMessage());
    		return( $this->redirect()->toUrl( APP_ERROR_PATH.'?er=500' ) );
        }
        catch( \Exception $e)
        {	// 通常エラー （エラーページを表示）
        	Log::error(__FILE__, __LINE__, $e->getMessage());
    		return( $this->redirect()->toUrl( APP_ERROR_PATH.'?er=500' ) );
        }
        finally 
        {
        	$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
        	Log::debug(__FILE__, __LINE__, 'END resultAction() -- ('.$diff_time.')');
        }
    }
    
    /**
	 * コンテンツの検索画面  検索結果
	 * URL:/monitor/content/detail/?cid=:contents_id
	 * 
	 * @return \Zend\View\Model\ViewModel
     */
    public function detailAction()
    {
    	Log::debug(__FILE__, __LINE__, 'BEGIN detailAction()');
    	$start_time=microtime(true);
    	 
        try {
        	//　表示するコンテンツ項目を設定
    		$cform = new ContentForm($this->getServiceLocator());
    		
    		$contents_id = $this->params()->fromQuery('cid');
    		$param = $this->params()->fromQuery('formvalue');

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
    			$this->layout('layout/cm_accessck_layout');
				return new ViewModel(array('error_message' => $this->addErrorComment()['11']));
    		}
    		// サービス選択状況チェック
    		if( false == ($service_cd = $this->checkService()) ) {
    			return( $this->redirect()->toUrl( APP_HOME_PATH.'?er=s_none' ) );
    		}
    		// GETパラメータのチェック
    		$ch_err = $cform->checkRequesrParam( array('content_id'=>$contents_id) );
    		if($ch_err == false) {
    			return( $this->redirect()->toUrl( APP_ERROR_PATH.'?er=102' ) );
    		}
    		elseif( false === $ch_err[0] ) {
    			// チェックエラー
        		Log::error(__FILE__, __LINE__, $ch_err[1]);
    			return( $this->redirect()->toUrl( APP_ERROR_PATH.'?er=102' ) );
    		}
    		
    		// EOF --(共通処理)
    		//----------------------------------------------------------------------------------------------

    		$msg_none = "";
    		
    		// サービス情報を取得
    		$sform = new ServiceForm($this->getServiceLocator());
    		$this->setService( $service_cd );
    		$sform->getServiceData( $service_cd );

    		//$request = $this->getRequest();
    		//if($request->isPost()){ //POST
    		//}
    		
    		// ロック解除に使うため、変数セット
    		$cform->content_id = $contents_id;
    		// 表示項目を設定
    		$cform->addAttribute('content_detail_ck_stats' , array( '1'=>'OK', '2'=>'NG', '3'=>'保留', ));
    		// 対象のコンテンツ情報を取得
    		$cform->getContentsDetailData($contents_id);
    		
    		//　対象のコンテンツのロック情報を取得し、ロック処理を実施
    		$aryUser = $this->getUserData();
    		$aryRet = $cform->checkContentsLock($contents_id);
    		if( true == $aryRet[0] ) {
    			//　自身がコンテンツをロック
    			$lock_ret = $this->setContentsLock($contents_id, $aryUser['user_id'], $cform->content_detail_inner_id);
    			
    			//if($lock_ret === true) echo 'ロック成功';
    			//else echo 'ロック失敗';
    		}
    		elseif( $aryUser['user_id'] != $aryRet[1] ) {
    			// 自身以外がロックしていた場合
    			$cform->lock_state  = false;
    		}    		
    		
    		//表示コンテンツの前後IDセット
    		if(!empty($_SESSION['search_item'])){
    			//表示しているコンテンツのID取得
    			$list_no = array_search($contents_id, (array)$_SESSION["search_item"]);
    			if($list_no > 0){
    				//前ページセット
    				$cform->content_detail_prev_page = $_SESSION["search_item"][$list_no - 1];
    			}
    			else{
    				//前ページは無いっていう処理
    				$cform->content_detail_prev_page = null;
    			}
    			if($list_no < count($_SESSION["search_item"]) - 1){
    				//次ページセット
    				$cform->content_detail_next_page = $_SESSION["search_item"][$list_no + 1];
    			}
    			else{
    				//次ページは無いっていう処理
    				$cform->content_detail_next_page = null;
    			}
    		}
    		else{
    		}

    		//テンプレートセット
    		$this->layout('layout/cm_main_layout');
			return $this->display(array('sform' => $sform, 'cform' => $cform, 'cid' => $contents_id, 'display_msg' => $msg_none));

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
        	Log::debug(__FILE__, __LINE__, 'END detailAction() -- ('.$diff_time.')');
        }
    }
    
    /**
     * コンテンツのロック解除
     * URL:/monitor/content/unlock/
     *
     * @return \Zend\Http\Response
     */
    public function unlockAction()
    {
    	Log::debug(__FILE__, __LINE__, 'BEGIN unlockAction()');
    	$start_time=microtime(true);
    	 
    	try {
    		
    		$id = $this->params()->fromPost( 'id', "" );
    		
    		$aryUser = $this->getUserData();
    		
    		//ロック解除
    		$ret = $this->setContentsUnlock($id, $aryUser['user_id']);

    		//正常に終了したか判断
    		if($ret == true){
    			Log::debug(__FILE__, __LINE__, 'INFO   unlockAction() unlocked successed.');
    		}else{
    			Log::debug(__FILE__, __LINE__, 'INFO   unlockAction() unlocked failed.');
    		}
    		
    
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
    
    /**
     * コンテンツの更新
     * URL:/monitor/content/update/
     *
     * @return \Zend\Http\Response
     */
    public function updateAction()
    {
    	Log::debug(__FILE__, __LINE__, 'BEGIN updateAction()');
    	$start_time=microtime(true);
    	 
    	$ret = false;
    	try {
        	//　表示するコンテンツ項目を設定
    		$cform = new ContentForm($this->getServiceLocator());
    		
    		$request = $this->getRequest();
    		if($request->isPost()){ 
    			//　POSTのみ処理を行う

    			//　ユーザ情報を取得
	    		$aryUser = $this->getUserData();
	    		$check = array( 'check_state'	=> MONITOR_STATE_ON,
								'check_result'	=> $this->params()->fromPost( 'check_result', "" ),
	    						'check_note'	=> $this->params()->fromPost( 'content_detail_ck_comment', "" ),
	    						'check_user'	=> $aryUser['user_id'],
	    						'check_date'	=> date("Y-m-d H:i:s"),
	    						'update_user'	=> $aryUser['user_id'],
	    						'update_date'	=> date("Y-m-d H:i:s"),
	    						'contents_id'	=> $this->params()->fromPost( 'cid', "" ),
	    						'contents_type'	=> $this->params()->fromPost( 'ctype', "" ),
	    						'contents_parent_id'	=> $this->params()->fromPost( 'parent_id', "" ),
	    		);
    			// 監視結果内容を更新	
	    		$Result_ret = $cform->setCheckResult($check);
	    		
	    		//　監視結果がNGの場合
	    		$NG_ret;
	    		if( $Result_ret && $check['check_result'] == CONTENTS_CHECK_RESULT_NG ){
	    			Log::debug(__FILE__, __LINE__, 'Dedub::ContentController -- check_result NG.');
	    			$token = $this->createAccessToken();
	    			$check['token'] = $token;
	    			$NG_ret = $cform->setCheckResult_NG($check);
	    		}
	    		
	    		//更新処理正常に完了したか
	    		if(isset($NG_ret)){
	    			if($Result_ret == true && $NG_ret == true){
	    				$ret = true;
	    			}
	    		}else{
	    			if($Result_ret == true){
	    				$ret = true;
	    			}
	    		}
    		}
    		else {
    			//　GET時、エラーログ
    			Log::error(__FILE__, __LINE__, 'GET通信のため、更新処理は行いませんでした。');
    		}
    		
    		Log::debug(__FILE__, __LINE__, 'Dedub::ContentController -- updateAction End1.');
    
    	}
    	catch( \DbAccessException $de )
    	{	// DB処理エラー （エラーページを表示）
    		Log::error(__FILE__, __LINE__, $de->getMessage());
    	}
    	catch( \AuthException $ae )
    	{	// 認証系処理エラー （エラーページを表示）
    		Log::error(__FILE__, __LINE__, $ae->getMessage());
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
        	Log::debug(__FILE__, __LINE__, 'END updateAction() -- ('.$diff_time.')');

        	$responseArray = array();
    		$responseArray['result'] = $ret;
    		$response = $this->getResponse();
    		$response->getHeaders()->addHeaders(array('Content-type' => 'application/json; charset=UTF-8'));
    		$response->setContent(Json::encode($responseArray));
    	}
    	return $response;
    }
    
    /**
     * アクセストークン (監視結果報告用のリクエストを送信)
     * URL:/monitor/content/report/
     *
     * @return \Zend\Http\Response
     */
    public function reportAction()
    {
    	Log::debug(__FILE__, __LINE__, 'BEGIN reportAction()');
    	$start_time=microtime(true);
    	 
    	$ret = false;
    	$ret_msg = "";
    	try {
			$service_cd = $this->checkService();
    		// サービス情報を取得
    		$sform = new ServiceForm($this->getServiceLocator());
    		$this->setService( $service_cd );
    		$sform->getServiceData( $service_cd );
    		
    		//　表示するコンテンツ項目を設定
    		$cform = new ContentForm($this->getServiceLocator());
    		$aryUser = $this->getUserData();
    		// 報告対象のコンテンツがあるかチェック
    		$repcount = $cform->getReportCount(array('user_id' => $aryUser['user_id']));
    		
    		if( 0 < $repcount ) {
    			
    			//　報告準備用のアクセストークン  リクエスト処理
    			
    			$config = array(
    				'sslverifypeer' => false,
    				'sslallowselfsigned' => false,
	    		);
    			$api_type = $sform->service_data->api_type; //API種別
	    		$url = 'https://ip128.ip140.faith-sol-tech.local/stub/api/setAccessToken_json_ok'; //(暫定的)
	    		$client = new Client($url, $config);
    			$resp = $client->send();
    			
    			// レスポンスのステータスコードをチェック
    			$code = $resp->getStatusCode();
    			if($resp->isSuccess()){
    				//200番台
    				$ret = true;
    				Log::debug(__FILE__, __LINE__, 'Dedub::ContentController -- reportAction status_code = 2** ('.$code.')');
    			}
    			elseif ($resp->isClientError()){
    				//400番台
    				Log::debug(__FILE__, __LINE__, 'Dedub::ContentController -- reportAction status_code = 4** ('.$code.')');
    			}
    			elseif ($resp->isServerError()){
    				//500番台
    				Log::debug(__FILE__, __LINE__, 'Dedub::ContentController -- reportAction status_code = 5** ('.$code.')');
    			}
    			
    			//　レスポンス内容チェック
    			$ary_result = array();
    			if( API_TYPE_XML == $api_type ) {
    				// XMLフォーマットの場合
    				$ary_result = Utility::simplexml_load_string($resp->getBody());
    			}
    			elseif( API_TYPE_JSON == $api_type ) {
    				//JSONフォーマットの場合
    				$ary_result = Utility::json_decode($resp->getBody(), true);
    			}
    			
    			if( $sform->service_data->api_status == $ary_result['ResultCode'] ) {
    				//処理正常
    				$ret = true;
    			}
    			else {
    				$ret = false;
    				Log::error(__FILE__, __LINE__, 'エラーレスポンス(result_code='.$ary_result['ResultCode'].')');
    			}
    			
//[Debug]	    			 
    		/*    		$options = array(
    		 'ssl' => array(
    		 // Verify server side certificate,
    		 // do not accept invalid or self-signed SSL certificates
    		 'verify_peer' => false,
    		 'verify_peer_name' => false,
    		 'allow_self_signed' => true,
    
    		 // Capture the peer's certificate
    		 'capture_peer_cert' => false
    		 )
    		 );
    		$context = stream_context_create($options);
    		*/
    		/*    		$adapter = new Socket();
    		 $adapter->setStreamContext($context);
    		 var_dump(stream_context_get_options($adapter->getStreamContext()));
    		 $url = 'https://goto.cm.ip128.ip140.faith-sol-tech.local/stub/api/setAccessToken_ok';
    		 //$url = 'http://cafe.76bit.com/feed/';
    		 $client = new Client();
    		 $client->setUri($url);
    		 $client->setAdapter($adapter);
    		 try {
    		 $resp = $client->send();
    		 } catch (\Exception $e){
    		 var_dump($e);
    		 }
    		 */
    		/*
    		 $options = array(
    		 'curloptions' => array(
    		 CURLOPT_SSL_VERIFYPEER => false,
    		 )
    		 );
    		 $adapter = new Curl();
    		 $url = 'https://ip128.ip140.faith-sol-tech.local/stub/api/setAccessToken_ok';
    		 //$url = 'http://cafe.76bit.com/feed/';
    		 $client = new Client();
    		 $client->setUri($url);
    		 $client->setAdapter($adapter);
    		 $adapter->setOptions($options);
    		 try {
    		 $resp = $client->send();
    		 } catch (\Exception $e){
    		 var_dump($e);
    		 }
    		 */
    
    		}
    		else {
    			Log::debug(__FILE__, __LINE__, 'Dedub::ContentController -- 報告対象のコンテンツがありません。');
    			$ret_msg = "報告対象のコンテンツがありません。";
    		}
    
    		Log::debug(__FILE__, __LINE__, 'Dedub::ContentController -- reportAction End');
    
    	}
    	catch( \DbAccessException $de )
    	{	// DB処理エラー （エラーページを表示）
    		Log::error(__FILE__, __LINE__, $de->getMessage());
    	}
    	catch( \AuthException $ae )
    	{	// 認証系処理エラー （エラーページを表示）
    		Log::error(__FILE__, __LINE__, $ae->getMessage());
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
        	Log::debug(__FILE__, __LINE__, 'END reportAction() -- ('.$diff_time.')');

        	$responseArray = array();
    		$responseArray['result'] = $ret;
    		if(empty($ret_msg)) $responseArray['getBody'] = $resp->getBody();
    		$responseArray['result_message'] = $ret_msg;
    		$response = $this->getResponse();
    		$response->getHeaders()->addHeaders(array('Content-type' => 'application/json; charset=UTF-8'));
    		$response->setContent(Json::encode($responseArray));
    	}
    	return $response;
    }
    
    /**
     * XML処理確認用
     * URL:/monitor/content/sample/
     *
     * @return \Zend\View\Model\ViewModel|\Zend\Http\Response
     */
    public function sampleAction()
    {
    	Log::debug(__FILE__, __LINE__, 'Dedub::ContentController -- sampleAction Start.');
    	try {
    		var_dump(http_response_code());
    		
    		$token = $this->params()->fromQuery('token');
    		//データ取得
    		$contentNGReportTable = $this->getServiceLocator()->get('ContentsMonitor\Service\Data\contentNGReportTable');//formに記述
    		$report_array = $contentNGReportTable->getReportDate($token);//formに
    		
    		echo("<pre>");
    		var_dump($report_array);
    		echo("</pre>");
    		
    		//xmlようにarrayを整形
    		$array = array(
						'ResultCode' => http_response_code(),
						'TotalCount' => count($report_array),
						'ServiceCode' => $report_array[0]['service_id'],
						'StartDate' => $report_array[0]['monitoring_start_date'],
						'EndDate' => $report_array[0]['monitoring_end_date'],
						'ImportDate' => $report_array[0]['import_date'],
						'NgContents' => array()
						);
    		
    		$count = 1;
    		$type = array(
    				1 => 'MV',
    				2 => 'IMG',
    				3 => 'CMT'
    		);
    		foreach ($report_array as $list){
    			$array['NgContents'] += array(
    						'Content no = '.$count => array(
    								'Id' => $list['contents_id'],
    								'Type' => $type[$list['contents_type']],
    								'Target' => $list['url'],
    								'Date' => $list['create_date'],
    								'Result_code' => $list['check_result'],
    								'Result_msg' => '?',
    								'Note' => $list['check_note'],
    					)
    			);
    			$count++;
    		}
    		
    		
    		//XML生成
    		$xmlstr = '<?xml version="1.0" encoding="UTF-8" ?><root></root>';
    		$xml = new SimpleXMLElement($xmlstr);
    			foreach($array as $key => $value){
    				if(is_array($value)){
    					$xmlitem = $xml -> addChild($key);
    					foreach($value as $key1 => $value1){
    						if(is_array($value1)){
    							$xmlitem1 = $xmlitem -> addChild($key1);
    							foreach($value1 as $key2 => $value2){
    								$xmlitem1 -> addChild($key2, $value2);
    							}
    						}
    						else{
    							$xmlitem -> addChild($key1, $value1);
    						}
    					}
    				}
    				else{
    					$xml -> addChild($key, $value);
    				}
    			}
    		
    		//XML整形：DOMDocument利用
    		$dom = new DOMDocument('1.0', 'UTF-8');
    		$node = $dom->importNode(dom_import_simplexml($xml), TRUE);
    		$dom->appendChild($node);
    		$dom->preserveWhiteSpace = FALSE;		//余分な空白を除去
    		$dom->formatOutput = TRUE;				//整形出力
    		$str3 = $dom->saveXML();
    		
    		var_dump($str3);
    		echo("xml=<pre>");
    		echo htmlspecialchars($str3);
    		echo("</pre>");
    		
    		
    		//JSON生成
    		$encode_array = json::encode($array);
    		print_r($encode_array);
    		
    		Log::debug(__FILE__, __LINE__, 'Dedub::ContentController -- reportAction End1.');
    	}
    	catch( \DbAccessException $de )
    	{	// DB処理エラー （エラーページを表示）
    		Log::error(__FILE__, __LINE__, 'Dedub::ContentController -- reportAction End2.');
    	}
    	catch( \AuthException $ae )
    	{	// 認証系処理エラー （エラーページを表示）
    		Log::error(__FILE__, __LINE__, 'Dedub::ContentController -- reportAction End3.');
    	}
    	catch( \FormException $fe )
    	{	// データ処理エラー （エラーページを表示）
    		Log::error(__FILE__, __LINE__, 'Dedub::ContentController -- reportAction End4.');
    	}
    	catch( \Exception $e )
    	{	// 通常エラー （エラーページを表示）
    		Log::error(__FILE__, __LINE__, 'Dedub::ContentController -- reportAction End5.');
    	}
    	finally {
    	}
    	return;
    	
    }

}
