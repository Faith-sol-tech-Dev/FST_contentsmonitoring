<?php
namespace Monitor\Controller;

use Zend\View\Model\ViewModel;

use Monitor\Model\ReportForm;
use ContentsMonitor\Common\LogClass as Log;
use ContentsMonitor\Common\UtilityClass as Utility;
use ContentsMonitor\Exception\AuthExceptionClass as AuthException;
use ContentsMonitor\Exception\DbAccessException as DbAccessException;
use ContentsMonitor\Exception\FormExceptionClass as FormException;
use Monitor\Model\ServiceForm;
use Monitor\Model\ContentForm;

use PHPExcel;
use PHPExcel\PHPExcel_IOFactory;
use PHPExcel\PHPExcel_Style_Border;
use PHPExcel_Style_Alignment;

/**
 * コンテンツ集計用コントローラ
 *
 * @package ContentsMonitor
 * @author  FST goto
 * @since   PHP 5.6, Zendframwork 2.4.9
 * @version 1.0.0
 */
class ReportController extends CommonController
{
	/**
	 * コンテンツの集計画面 初期設定
	 * URL：/monitor/report/
	 * 
	 * @return \Zend\View\Model\ViewModel
	 */
    public function indexAction()
    {
    	Log::debug(__FILE__, __LINE__, 'BEGIN indexAction()');
    	$start_time=microtime(true);
    	 
    	try {
    		//　表示する検索項目を設定
    		$rform = new ReportForm($this->getServiceLocator());
    		
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

    		$search_msg = "";
    		$msg_none = "";
    		$request = $this->getRequest();
    		$param = $this->params()->fromQuery('formvalue');
    		if($request->isPost()){
	    		// 検索結果情報を取得
	    		if( 'selectItem'==$param ) {
		    		$aryData = array( 
		    					'service_id' => $service_cd,
		    					'contents_type_1' => $this->params()->fromPost( 'content_list_type_1', "" ),
		    					'contents_type_2' => $this->params()->fromPost( 'content_list_type_2', "" ),
		    					'contents_type_3' => $this->params()->fromPost( 'content_list_type_3', "" ),
		    					'total_date_min' => $this->params()->fromPost( 'content_text_totDate_str', "" ),
		    					'total_date_max' => $this->params()->fromPost( 'content_text_totDate_end', "" ),
		    					//'page_no' => $this->params()->fromQuery('page')
		    					'page_no' => $this->params()->fromPost('p', "")
		    				);
	    		}
	    		elseif( 'pagination'==$param ) {
	    			$aryData = array(
	    					'service_id' => $service_cd,
	    					'contents_type_1' => $this->params()->fromPost( 'hd_content_list_type_1', "" ),
	    					'contents_type_2' => $this->params()->fromPost( 'hd_content_list_type_2', "" ),
	    					'contents_type_3' => $this->params()->fromPost( 'hd_content_list_type_3', "" ),
	    					'total_date_min' => $this->params()->fromPost( 'hd_content_text_totDate_str', "" ),
	    					'total_date_max' => $this->params()->fromPost( 'hd_content_text_totDate_end', "" ),
	    					//'page_no' => $this->params()->fromQuery('page')
	    					'page_no' => $this->params()->fromPost('p', "")
	    			);
	    		}
	    		else{
	    		
	    		}

	    		// 検索入力情報がある場合
	    		if(!empty($aryData)) {
	    			// バリデーションチェック
		    		if( false == ( $aryRet = $rform->surveyItemChecker( $aryData ) )) {
		    			// チェックエラー
		    			$search_msg = $this->addErrorComment()['101'];
		    		}
		    		
		    		// POSTデータをセット
		    		$rform->current_list_type_1 = $aryData['contents_type_1'];
		    		$rform->current_list_type_2 = $aryData['contents_type_2'];
		    		$rform->current_list_type_3 = $aryData['contents_type_3'];
		    		$rform->current_text_totDate_str = $aryData['total_date_min'];
		    		$rform->current_text_totDate_end = $aryData['total_date_max'];
		    		$rform->current_page_no = $aryData['page_no'];
		    		
		    		// 集計情報を取得
		    		$rform->surveyItem( $aryData );
		    		//　ページャ作成
		    		$pagination = $rform->setPagination();
	    		}
		    		
    		}
    		else {
    			$rform->surveyItem( $service_cd );
    			$pagination = $rform->setPagination();
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
    		Log::debug(__FILE__, __LINE__, 'END indexAction() -- ('.$diff_time.')');
    	}
    }

	/**
	 * コンテンツの集計画面 EXCEL出力
	 * URL：/monitor/report/
	 * 
	 * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
	 */
    public function outputAction()
    {
    	Log::debug(__FILE__, __LINE__, 'BEGIN outputAction()');
        $start_time=microtime(true);
        
    	try {
    		
    		//　表示する検索項目を設定
    		$rform = new ReportForm($this->getServiceLocator());
    		
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
    		
    		// EOF --(共通処理)
    		//----------------------------------------------------------------------------------------------
    		
    		// サービス情報を取得
    		$sform = new ServiceForm($this->getServiceLocator());
    		$this->setService( $service_cd );
    		$sform->getServiceData( $service_cd );
    		
    		//　表示する検索項目を設定
    		$cform = new ContentForm($this->getServiceLocator());
    		
    		// EOF --(データ取得)
    		//----------------------------------------------------------------------------------------------

    		//結果取得
    		$request = $this->getRequest();
    		if($request->isPost()){
    		
    			// 検索結果情報を取得
    				$aryData = array(
    						'service_id' => $service_cd,
    						'contents_type_1' => $this->params()->fromPost( 'content_list_type_1', "" ),
    						'contents_type_2' => $this->params()->fromPost( 'content_list_type_2', "" ),
    						'contents_type_3' => $this->params()->fromPost( 'content_list_type_3', "" ),
    						'total_date_min' => $this->params()->fromPost( 'content_text_totDate_str', "" ),
    						'total_date_max' => $this->params()->fromPost( 'content_text_totDate_end', "" ),
    				);
    				if( false == ( $aryRet = $rform->surveyItemChecker( $aryData ) )) {
    					return( $this->redirect()->toUrl( APP_HOME_PATH.'?er=99999' ) );
    				}
    		
    				// POSTデータをセット
    				$rform->current_list_type_1 = $this->params()->fromPost( 'content_list_type_1', "" );
    				$rform->current_list_type_2 = $this->params()->fromPost( 'content_list_type_2', "" );
    				$rform->current_list_type_3 = $this->params()->fromPost( 'content_list_type_3', "" );
    				$rform->current_text_impDate_str = $this->params()->fromPost( 'content_text_totDate_str', "" );
    				$rform->current_text_impDate_end = $this->params()->fromPost( 'content_text_totDate_end', "" );
    		
    				$rform->surveyItem( $aryData );
    		}
    		else {
    		}
    		
    		//エクセルオブジェクトの生成
    		$xl = new \PHPExcel();
    		$filepath = EXCEL_PATH.'template.xlsx';
    		//シートの設定
    		$xl->setActiveSheetIndex(0);
    		$sheet = $xl->getActiveSheet();
    		$sheet->setTitle('集計');
    		
    		
    		//セルの値を設定
    		$sheet->setCellValue('A1', 'NO');
    		$sheet->setCellValue('B1', '取込日');
    		$sheet->setCellValue('C1', '監視総件数');
    		$sheet->setCellValue('D1', '動画');
    		$sheet->setCellValue('E1', '画像');
    		$sheet->setCellValue('F1', 'コメント');
    		$sheet->setCellValue('G1', '未監視');
    		
    		$count = 2;
    		foreach ($rform->content_report as $row){
    			$sheet->setCellValue('A'.$count, (string)$row['ROW_NUM']);
    			$sheet->setCellValue('B'.$count, (string)$row['import_date']);
    			$sheet->setCellValue('C'.$count, (string)$row['sumcnt']);
    			$sheet->setCellValue('D'.$count, (string)$row['m_sumcnt_already'].'/'.$row['m_sumcnt']);
    			$sheet->setCellValue('E'.$count, (string)$row['i_sumcnt_already'].'/'.$row['i_sumcnt']);
    			$sheet->setCellValue('F'.$count, (string)$row['c_sumcnt_already'].'/'.$row['c_sumcnt']);
    			$sheet->setCellValue('G'.$count, (string)$row['yet_check_sumcnt']);
    			$count++;
    		}
    		
    		/*
    		$sheet->setCellValue('A1', 'PHPExcelテストaa'); //文字列
    		$sheet->setCellValue('B2', 123);            //数値
    		$sheet->setCellValue('C3', '=B2-100');      //計算式
    		$sheet->setCellValue('D4', true);           //真偽値
    		$sheet->setCellValue('E5', false);          //真偽値
    		*/
    		
    		//スタイルの設定(標準フォント、罫線、中央揃え)
    		$sheet->getDefaultStyle()->getFont()->setName('ＭＳ Ｐゴシック');
    		$sheet->getDefaultStyle()->getFont()->setSize(11);
    		//$sheet->getStyle('C3')->getBorders()->getBottom()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
    		//$sheet->getStyle('C3')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    		
    		//Excel5形式で保存
    		$writer = \PHPExcel_IOFactory::createWriter($xl, 'Excel2007');
    		$writer->save($filepath);
    		
    		
    		//パス
			$fpath = EXCEL_PATH.'template.xlsx';
			//ファイル名
			$fname = 'Aggregate.xlsx';

			header('Content-Type: application/force-download');
			ob_end_clean();//バッファのゴミ捨て
			header('Content-Length: '.filesize($fpath));
			header('Content-disposition: attachment; filename="'.$fname.'"');
			readfile($fpath);
    		
    		exit;
    		
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
    		Log::debug(__FILE__, __LINE__, 'END outputAction() -- ('.$diff_time.')');
    	}
    	 
    }
}
