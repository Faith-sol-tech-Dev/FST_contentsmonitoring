<?php
namespace Monitor\Controller;

use Zend\View\Model\ViewModel;

use Monitor\Model\ServiceForm;
use ContentsMonitor\Common\LogClass as Log;
use ContentsMonitor\Common\UtilityClass as Utility;
use ContentsMonitor\Exception\AuthExceptionClass as AuthException;
use ContentsMonitor\Exception\DbAccessException as DbAccessException;
use ContentsMonitor\Exception\FormExceptionClass as FormException;
use Monitor\Model\RecoveryForm;


/**
 * リカバリー処理専用コントローラ
 *
 * @package ContentsMonitor
 * @author  FST goto
 * @since   PHP 5.6, Zendframwork 2.4.9
 * @version 1.0.0
 */
class RecoveryController extends CommonController
{
    public function indexAction()
    {	// /recovery/search/にリダイレクト
    	return( $this->redirect()->toUrl( APP_RECOVERY_SEARCH_PATH ) );
    }
    
    /**
     * コンテンツの検索画面 初期設定
     * URL:/monitor/recovery/search/
     *
     * @return \Zend\View\Model\ViewModel|\Zend\Http\Response
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
    		$aryUser = $this->getUserData();
    		if( 1==$aryUser['user_auth'] ) {
    			$this->layout('layout/cm_accessck_layout');
    			return new ViewModel(array('error_message' => $this->addErrorComment()['11']));
    		}
    			
    		// EOF --(共通処理)
    		//----------------------------------------------------------------------------------------------
    
    		// サービス情報を取得
    		$sform = new ServiceForm($this->getServiceLocator());
    		$sform->getServiceListData();  // サービス情報一覧
    		
    		//　表示する検索項目を設定
    		$rform = new RecoveryForm($this->getServiceLocator());
    
    		// PostBack情報
    		$request = $this->getRequest();
    		if($request->isPost()){
    	   
    			//この画面からPOSTはされない、そのため、POSTされた場合は、検索結果に強制リダイレクト
    			return( $this->redirect()->toUrl( APP_CONTENTS_SEARCH_PATH ) );
    		}
    
    		//テンプレートセット
			$this->layout('layout/cm_main_layout');
    		return $this->display(array('rform' => $rform, 'sform' => $sform));
    
    	}
    	catch( \DbAccessException $de )
    	{	// DB処理エラー （エラーページを表示）
        	Log::error(__FILE__, __LINE__, $de->getMessage());
    		return( $this->redirect()->toUrl( APP_ERROR_PATH.'?er=500' ) );
    	}
    	catch( \AuthException $ae )
    	{	// 認証系処理エラー （エラーページを表示）
        	Log::error(__FILE__, __LINE__, $de->getMessage());
    		return( $this->redirect()->toUrl( APP_ERROR_PATH.'?er=500' ) );
    	}
    	catch( \FormException $fe )
    	{	// データ処理エラー （エラーページを表示）
        	Log::error(__FILE__, __LINE__, $de->getMessage());
    		return( $this->redirect()->toUrl( APP_ERROR_PATH.'?er=500' ) );
    	}
    	catch( \Exception $e )
    	{	// 通常エラー （エラーページを表示）
        	Log::error(__FILE__, __LINE__, $de->getMessage());
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
     * @return \Zend\View\Model\ViewModel|\Zend\Http\Response
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
    		$aryUser = $this->getUserData();
    		if( 1==$aryUser['user_auth'] ) {
    			$this->layout('layout/cm_accessck_layout');
    			return new ViewModel(array('error_message' => $this->addErrorComment()['11']));
    		}
    		
    		// EOF --(共通処理)
    		//----------------------------------------------------------------------------------------------
    
    		// サービス情報を取得
    		$sform = new ServiceForm($this->getServiceLocator());
    		$sform->getServiceListData();  // サービス情報一覧
    
    		//　表示する検索項目を設定
    		$rform = new RecoveryForm($this->getServiceLocator());
    
    		$pagination;
    		$msg_none = "";
    		$search_msg = "";
    		$request = $this->getRequest();
    		if($request->isPost()){
    			// 検索結果情報を取得
    			if( 'selectItem'==$param ) {
	    			$min_date = empty($this->params()->fromPost( 'content_import_s', "" )) ? 
	    									$this->params()->fromPost( 'content_import_s', "" ) : $this->params()->fromPost( 'content_import_s', "" ).DATE_MIN_TIME;
	    			$max_date = empty($this->params()->fromPost( 'content_import_e', "" )) ? 
	    									$this->params()->fromPost( 'content_import_e', "" ) : $this->params()->fromPost( 'content_import_e', "" ).DATE_MAX_TIME;
    				$aryData = array(
    						'check_state1'    => $this->params()->fromPost( 'content_import_type1', "" ),
    						'check_state2'    => $this->params()->fromPost( 'content_import_type2', "" ),
    						'check_state3'    => $this->params()->fromPost( 'content_import_type3', "" ),
    						'content_state'   => $this->params()->fromPost( 'content_state', "" ),
    						'import_date_min' => $min_date,
    						'import_date_max' => $max_date,
    						'service_list'    => $this->params()->fromPost( 'service_list', "" ),
    						'page_no'         => $this->params()->fromPost( 'p',"")
    				);
    			}
    			elseif( 'pagination'==$param ) {
	    			$min_date = empty($this->params()->fromPost( 'hd_content_impDate_str', "" )) ? 
	    									$this->params()->fromPost( 'hd_content_impDate_str', "" ) : $this->params()->fromPost( 'hd_content_impDate_str', "" ).DATE_MIN_TIME;
	    			$max_date = empty($this->params()->fromPost( 'hd_content_impDate_end', "" )) ? 
	    									$this->params()->fromPost( 'hd_content_impDate_end', "" ) : $this->params()->fromPost( 'hd_content_impDate_end', "" ).DATE_MAX_TIME;
    				$aryData = array(
    						'check_state1'    => $this->params()->fromPost( 'hd_content_import_type1', "" ),
    						'check_state2'    => $this->params()->fromPost( 'hd_content_import_type2', "" ),
    						'check_state3'    => $this->params()->fromPost( 'hd_content_import_type3', "" ),
    						'content_state'   => $this->params()->fromPost( 'hd_content_state', "" ),
    						'import_date_min' => $min_date,
    						'import_date_max' => $max_date,
    						'service_list'    => $this->params()->fromPost( 'hd_service_list', "" ),
    						'page_no'         => $this->params()->fromPost( 'p',"")
    				);
    			}
    			else{
    			}

    			if(!empty($aryData)) {
	    			// バリデーションチェック
	    			$aryRet = $rform->searchItemChecker( $aryData );
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
	    				$rform->searchItem( $aryData );
	    			}
	    			 
	    			// POSTデータをセット
	    			$rform->current_import_type1 = $aryData['check_state1'];
	    			$rform->current_import_type2 = $aryData['check_state2'];
	    			$rform->current_import_type3 = $aryData['check_state3'];
	    			$rform->current_state = $aryData['content_state'];
	    			$rform->current_impDate_str = $aryData['import_date_min'];
	    			$rform->current_impDate_end = $aryData['import_date_max'];
	    			$rform->current_list_service = $aryData['service_list'];
	    			$rform->current_list_display_cnt = 100;
	    			$rform->current_page_no = $aryData['page_no'];
	    			// ページャ設定
	    			$pagination = $rform->setPagination(100);
    			}
    		}
    		else {
    			// ポストでのリクエストでない場合は、検索を行わない
    			$msg_none = $this->_comment->HTTP_DISPLAY_MESSAGE['content_result_no_post'];
    		}

    		//テンプレートセット    		
    		$this->layout('layout/cm_main_layout');
    		return $this->display(array('sform' => $sform, 'rform' => $rform, 'search_msg' => $search_msg, 'display_msg' => $msg_none, 'pagination' => $pagination));
    
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
        	Log::debug(__FILE__, __LINE__, 'END resultAction() -- ('.$diff_time.')');
        }
    }
    
    /**
     * コンテンツの検索画面  検索結果
     * URL:/monitor/content/list/?bid=:batch_id
     *
     * @return \Zend\View\Model\ViewModel|\Zend\Http\Response
     */
    public function listAction()
    {
    	Log::debug(__FILE__, __LINE__, 'BEGIN listAction()');
    	$start_time=microtime(true);
    	
    	try {
    		//　表示する検索項目を設定
    		$rform = new RecoveryForm($this->getServiceLocator());
    		
    		$batch_log_id = $this->params()->fromQuery('blid');
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
    		// GETパラメータのチェック
    		$ch_err = $rform->checkRequesrParam( array('batch_log_id'=>$batch_log_id) );
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
    		
    		// サービス情報を取得
    		$sform = new ServiceForm($this->getServiceLocator());
    		$sform->getServiceListData();  // サービス情報一覧
    
    		//　表示する件数
    		$count = 100;
    
    		$pagination;
    		$msg_none = "";
    		$search_msg = "";
    		$request = $this->getRequest();
    		if($request->isPost()){
    			// 検索結果情報を取得
    			if( 'selectItem'==$param ) {
    				$aryData = array(
    						'batch_log_id'    => $batch_log_id,
    						'content_state'   => $this->params()->fromPost( 'status', "" ),
    						'page_no'         => $this->params()->fromPost( 'p',"")
    				);
    			}
    			elseif( 'pagination'==$param ) {
    				$aryData = array(
    						'batch_log_id'    => $batch_log_id,
    						'content_state'   => $this->params()->fromPost( 'hd_status', "" ),
    						'page_no' 		  => $this->params()->fromPost( 'p',"")
    				);
     			}
     			
     			if(!empty($aryData)) {
     				$aryRet = $rform->searchStatusChecker( $aryData );
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
		    		    // バッチ情報取得
		    		    $aryBatch = $rform->searchBatch( $aryData );
		    		    if( false === $aryBatch) {
			    			// チェックエラー
			    			$search_msg = $this->addDisplayComment()['batch_log_nodata'];
		    			}
		    			if( !empty( $aryBatch[0] ) ) {
		    				// 取込コンテンツリストを取得
		    				$rform->current_batch = $aryBatch[0];
		    				$rform->searchBatchDetailList( $aryData );
		    			}
		    		}
     				
	    			// POSTデータをセット
	    			$rform->current_list_display_cnt = $count;
		    		$rform->current_status = $aryData['content_state'];
	    			$rform->current_page_no = $aryData['page_no'];
	    			$pagination = $rform->setPagination($count);
	    		
     			}
    		}
    		else {
	    		// バッチ情報取得
    			$aryData = array(
    					'batch_log_id' => $batch_log_id,
    					'page_no' => 1
    			);
    			$aryBatch = $rform->searchBatch( $aryData );
     			if( false === $aryBatch) {
		    		// チェックエラー
		    		$search_msg = $this->addDisplayComment()['batch_log_nodata'];
	    		}
	    		if( !empty( $aryBatch[0] ) ) {
    				$rform->current_batch = $aryBatch[0];
    				$rform->searchBatchDetailList( $aryData );
	    		}
    			
    			// ポストでのリクエストでない場合は、検索を行わない
    			$rform->current_list_display_cnt = $count;
    			$pagination = $rform->setPagination($count);

    		}
    
    		//テンプレートセット    		
    		$this->layout('layout/cm_main_layout');
    		return $this->display(array('sform' => $sform, 'rform' => $rform, 'search_msg' => $search_msg, 'display_msg' => $msg_none, 'pagination' => $pagination));
    
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
        	Log::debug(__FILE__, __LINE__, 'END listAction() -- ('.$diff_time.')');
        }
    }
    
    /**
     * コンテンツの検索画面  検索結果
     * URL:/monitor/content/detail/?cid=:contents_id
     *
     * @return \Zend\View\Model\ViewModel|\Zend\Http\Response
     */
    public function detailAction()
    {
    	Log::debug(__FILE__, __LINE__, 'BEGIN detailAction()');
    	$start_time=microtime(true);
    	
    	try {
    		$param = array(
    				'batch_log_id' => $this->params()->fromQuery('bid'),
    				'contents_id' => $this->params()->fromQuery('cid'),
    		);
    		$param_reload = $this->params()->fromQuery('formvalue');
    		
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
    			
    		// EOF --(共通処理)
    		//----------------------------------------------------------------------------------------------
    		
    		// サービス情報を取得
    		$sform = new ServiceForm($this->getServiceLocator());
    		$sform->getServiceListData();  // サービス情報一覧
    
    		//　表示する検索項目を設定
    		$rform = new RecoveryForm($this->getServiceLocator());
    
    		$msg_none = "";
    		$request = $this->getRequest();
    		
    		//BATCH_DETAIL取得
    		$rform->searchBatchDetail( $param );
    		
    		$rform->insert_result = null;
    		if($rform->content_search_result['recovery_state'] != 54 && isset($rform->content_search_result['recovery_state'])){
    			//再取込処理
    			if($param_reload == 'reload'){
    				 echo("再取込です。");
    				 $user = $this->getUserData();
    				 $param["user_id"] = $user["user_id"];
    				 $param["batch_contents_id"] = $rform->content_search_result["batch_contents_id"];
    				 $rform->insert_result = $rform->contentsReuptake($param);
    			}
    		}else{
    		 echo("22再取込です。");
    		//	return( $this->redirect()->toUrl( str_replace(APP_RECOVERY_LIST_PATH, $param['batch_log_id']) ) );
    		}
    		    		
    		if($request->isPost()){
    			// 検索結果情報を取得
    				$aryData = array(
    						'contents_type' => $this->params()->fromPost( 'content_list_type', "" ),
    						'check_state1'   => $this->params()->fromPost( 'content_import_type1', "" ),
    						'check_state2'   => $this->params()->fromPost( 'content_import_type2', "" ),
    						'check_state3'   => $this->params()->fromPost( 'content_import_type3', "" ),
    						'content_state'  => $this->params()->fromPost( 'content_state', "" ),
    						'import_date_min' => $this->params()->fromPost( 'content_import_s', "" ),
    						'import_date_max' => $this->params()->fromPost( 'content_import_e', "" ),
    						'service_list'  => $this->params()->fromPost( 'service_list', "" ),
    				);
    		}
    		else {
    			// ポストでのリクエストでない場合は、検索を行わない
    			//$msg_none = $this->_comment->HTTP_DISPLAY_MESSAGE['content_result_no_post'];
    		}
    
    		//テンプレートセット    		
    		$this->layout('layout/cm_main_layout');
    		return $this->display(array('sform' => $sform, 'rform' => $rform, 'display_msg' => $msg_none, 'display_msg' => $msg_none));
    
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
    
}
