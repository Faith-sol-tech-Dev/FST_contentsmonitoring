<?php
namespace Monitor\Model;

use Zend\Form\Form;

use ContentsMonitor\Service\Data\ContentTable as ContentTable;
use ContentsMonitor\Common\LogClass as Log;
use ContentsMonitor\Common\PaginationClass as Pagination;
use ContentsMonitor\Common\ValidationClass as Validation;
use ContentsMonitor\Common\UtilityClass as Utility;


/**
 * コンテンツ集計情報フォーム
 *
 * @package ContentsMonitor
 * @author  FST goto
 * @since   PHP 5.6, Zendframwork 2.4.9
 * @version 1.0.0
 */
class ReportForm extends Form
{
	/**
	 * サービスロケーター
	 * @var $service_locator
	 */
	public $service_locator;
	
	/**
	 * コンテンツ情報へのアクセスクラス
	 * @var $contentTable
	 */
	public $contentTable;
	
	/**
	 * コンテンツ詳細情報へのアクセスクラス
	 * @var $contentDetailTable
	 */
	public $contentDetailTable;
	
	/**
	 * 検索項目 コンテンツの種類リスト
	 * @var $content_list_type
	 */
	public $content_list_type = array();
	
	/**
	 * 検索項目 集計期間（開始）のリスト
	 * @var $content_text_totDate_str
	 */
	public $content_text_totDate_str = '';
	
	/**
	 * 検索項目 集計期間（終了）のリスト
	 * @var $content_text_totDate_end
	 */
	public $content_text_totDate_end = '';
	
	/**
	 * 検索項目 (選択データ) コンテンツの種類リスト
	 * @var $content_list_type
	 */
	public $current_list_type_1 = '';
	public $current_list_type_2 = '';
	public $current_list_type_3 = '';
	
	/**
	 * 検索項目 (選択データ) 集計期間（開始）のリスト
	 * @var $content_text_totDate_str
	 */
	public $current_text_totDate_str = '';
	
	/**
	 * 検索項目 (選択データ) 集計期間（終了）のリスト
	 * @var $content_text_totDate_end
	 */
	public $current_text_totDate_end = '';
	
	/**
	 * 検索項目 (選択データ) 集計結果
	 * @var $content_report
	 */
	public $content_report = array();
	
	/**
	 * 検索項目 (選択データ) 集計結果のカウント
	 * @var $content_report_count
	 */
	public $content_report_count = array();
	
	/**
	 * 現在の選択しているページ番号
	 * @var $current_page_no
	 */
	public $current_page_no = 1;
	
	
    /**
     * コンストラクタ
     * @param unknown $service_locator サービスロケーター
     */
    public function __construct( $service_locator )
    {
    	$this->getServiceLocator = $service_locator;
    	$this->contentTable = $this->getServiceLocator->get('ContentsMonitor\Service\Data\ContentTable');
    	$this->contentDetailTable = $this->getServiceLocator->get('ContentsMonitor\Service\Data\ContentDetailTable');
    	$this->initElement();
    	parent::__construct();
    }
    
    /**
     * 画面に表示する検索項目の初期値をセット
     */
    protected function initElement()
    {
    	// コンテンツ種類
    	$this->content_list_type = array( '1'=>'動画', '2'=>'画像', '3'=>'コメント', );
    }

    /**
     * コンテンツ集計画面へのリクエストにつくパラメータのチェック
     *
     * @param array $request
     * @return boolean[]|string[]|boolean
     */
    public function checkRequesrParam( $request )
    {
    	Log::debug(__FILE__, __LINE__, 'Dedub::checkRequesrParam() Start.');
    
    	try {
    		 
    		$message = "";
    		//コンテンツID
    		if( "" != ($message= Validation::validateForm($request, "content_id", "コンテンツID", false, 1, null, null)) ) { return array(false, $message); }
    		 
    	}
    	catch( \FormException $fe ) {
    		Log::error(__FILE__, __LINE__, $fe->getMessage());
    		Log::error(__FILE__, __LINE__, $fe);
    		return false;
    		 
    	}
    	catch( \Exception $e ) {
    		Log::error(__FILE__, __LINE__, $e->getMessage());
    		Log::error(__FILE__, __LINE__, $e);
    		return false;
    	}
    
    	Log::debug(__FILE__, __LINE__, 'Dedub::checkRequesrParam() End.');
    	return true;
    }
    
    /**
     * 画面に表示する項目の属性を追加
     *
     * @param string  $elm 項目変数名
     * @param string  $val　設定値
     */
    public function addAttribute( $elm, $val )
    {
    	$this->$elm = $val;
    }
    
    /**
     * 集計処理
     *
     * @param array $request 画面で入力された値リスト
     */
    public function surveyItem( $request )
    {
    	$start_time=microtime(true);
    	 
    	try {
    
    		// 検索結果の件数を取得する
    		$sumcnt = $this->contentTable->getAggregateCount( $request );
    		if( 0 < $sumcnt ) {
    			// 検索結果の情報を取得する
    			$this->content_report = $this->contentTable->getAggregate( $request );
    		}
    		$this->content_report_count = $sumcnt;
    
    	}
    	catch( \DbAccessException $de ) {
    		Log::error(__FILE__, __LINE__, $de->getMessage());
    
    	}
    	catch( \FormException $fe ) {
    		Log::error(__FILE__, __LINE__, $fe->getMessage());
    
    	}
    	catch( \Exception $e ) {
    		Log::error(__FILE__, __LINE__, $e->getMessage());
    
    	}
    	
    	$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   surveyItem() --  has completed. ('.$diff_time.')');
    }
    
    /**
     * 画面から入力された検索項目の値をチェック
     *
     * @param array $request 画面で入力された値リスト
     * @return bool true:チェックOK ／ false:チェックNG
     */
    public function surveyItemChecker( $request )
    {
    	$start_time=microtime(true);
    	 
    	try {
    
    		$message = "";
    		//コンテンツの種類
    		if( "" != ($message= Validation::validateForm($request, "contents_type_1", "コンテンツの種類", false, 1, null, null)) ) { return array(false, $message); }
    	    if( "" != ($message= Validation::validateForm($request, "contents_type_2", "コンテンツの種類", false, 1, null, null)) ) { return array(false, $message); }
    	    if( "" != ($message= Validation::validateForm($request, "contents_type_3", "コンテンツの種類", false, 1, null, null)) ) { return array(false, $message); }
    	    //取込期間(開始)
    		if( "" != ($message= Validation::validateForm($request, "totle_date_min", "集計期間(開始)", false, 4, null, null)) ) { return array(false, $message); }
    		//取込期間(終了)
    		if( "" != ($message= Validation::validateForm($request, "totle_date_max", "集計期間(終了)", false, 4, null, null)) ) { return array(false, $message); }
    		//ページ数
    		if( "" != ($message= Validation::validateForm($request, "page_no", "ページャ数", false, 1, null, null)) ) { return array(false, $message); }
    
    	}
    	catch( \FormException $fe ) {
    		Log::error(__FILE__, __LINE__, $fe->getMessage());
    		return false;
    
    	}
    	catch( \Exception $e ) {
    		Log::error(__FILE__, __LINE__, $e->getMessage());
    		return false;
    	}
    	 
    	$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   surveyItemChecker() --  has completed. ('.$diff_time.')');
    	return true;
    }
    
    /**
     * 画面に表示するページャをセット
     *
     * @return boolean|\ContentsMonitor\Common\PaginationClass
     */
    public function setPagination()
    {
    	$start_time=microtime(true);
    	
    	$pagination = new Pagination( APP_CONTENTS_REPORT_PATH, 2);
    	try {
    
    		if($this->content_report_count > 0) {
    			// 最大件数をセット
    			$pagination->maxcount($this->content_report_count);
    			// 現在のページNoをセット
    			$pagination->currentpage($this->current_page_no);
    			// ページャ設定
    			$pagination->CalcPage();
    		}
    		else {
    			return false;
    		}
    
    	}
    	catch( \FormException $fe ) {
    		Log::error(__FILE__, __LINE__, $fe->getMessage());
    		Log::error(__FILE__, __LINE__, $fe);
    		return false;
    
    	}
    	catch( \Exception $e ) {
    		Log::error(__FILE__, __LINE__, $e->getMessage());
    		Log::error(__FILE__, __LINE__, $e);
    		return false;
    	}
    	 
    	$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   setPagination() --  has completed. ('.$diff_time.')');
    	return $pagination;
    }
    
}
